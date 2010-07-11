<?PHP

 class TriggeredHandler {

  /*-------------------------------------------------------------------------*\
   * Handles triggered events.                                               *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $type = Parser::parseString($line);

   switch($type) {
    case 'chargedeployed':
     $this->chargeDeployed($date, $player);
     break;
    case 'domination':
     $this->domination($date, $player, $line, 'domination');
     break;
    case 'revenge':
     $this->domination($date, $player, $line, 'revenge');
     break;
    case 'kill assist':
     $this->assist($date, $player, $line);
     break;
    case 'builtobject':
     $this->builtObject($date, $player, $line);
     break;
    case 'killedobject':
     $this->killedObject($date, $player, $line);
     break;
    case 'flagevent':
     $this->flagEvent($date, $player, $line);
     break;
    default: 
     //echo "No support for trigger type $type\n";
   }
  }

  private function flagEvent($date, $player, $line) {
   $props = Parser::parseProps($line);

   switch ($props['event']) {
    case 'defended':
     $this->doFlagEvent($date, $player, $line, 'intel defended', $props);
     $player->addScore(2);
     break;
    case 'picked up':
     $this->doFlagEvent($date, $player, $line, 'intel picked up', $props);
     $player->addScore(2);
     break;
    case 'dropped':
     $this->doFlagEvent($date, $player, $line, 'intel dropped', $props);
     $player->addScore(-2);
     break;
    case 'captured':
     $this->doFlagEvent($date, $player, $line, 'intel captured', $props);
     $player->addScore(5);
     break;
    default:
     // echo ...
   }
  }

  private function doFlagEvent($date, $player, $line, $type, $props) {
   if ($player->getOpenSession() == null || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player ", $player->getSteamID(), " has no session/roleperiod. Dropping flag event\n";
    return;
   }

   $playerID = $player->getOpenSession()->getRolePeriod()->getID();

   $sql  = 'INSERT INTO events (event_timestamp, roleperiod_id, event_type, ';
   $sql .= 'event_location) VALUES (FROM_UNIXTIME(' . $date . '), ' . $playerID;
   $sql .= ', \'' . $type . '\', \'' . s($props['position']) . '\')';
   mysql_query($sql);
  }

  private function killedObject($date, $player, $line) {
   $props = Parser::parseProps($line);

   if (isset($props['assist'])) {
    // TODO!
    return;
   }

   $arg = s($props['object']);
   $victim = Parser::parsePlayer($props['objectowner'], false);
   $victim = Player::getBySteamID($victim['steamid']);

   if ($victim == null || $victim->getOpenSession() == null
       || $victim->getOpenSession()->getRolePeriod() == null) {
    echo "Victim invalid\n";
    return;
   }

   if ($player == null || $player->getOpenSession() == null
       || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player invalid\n";
    return;
   }


   $victimID = $victim->getOpenSession()->getRolePeriod()->getID();
   $playerID = $player->getOpenSession()->getRolePeriod()->getID();
   $location = s($props['attacker_position']);
   $weapon = Weapon::getID($props['weapon']);

   $sql  = 'INSERT INTO events (event_timestamp, roleperiod_id, event_type, ';
   $sql .= 'event_location, event_arg, event_victim, event_weapon) VALUES (';
   $sql .= 'FROM_UNIXTIME(' . $date . '), ' . $playerID . ', \'object killed\', ';
   $sql .= '\'' . $location . '\', \'' . $arg . '\', ' . $victimID . ', ';
   $sql .= $weapon . ')';
   mysql_query($sql) or die(mysql_error());

   $player->addScore( 2);
   $victim->addScore(-1);
  }

  private function builtObject($date, $player, $line) {
   $props = Parser::parseProps($line);

   if ($player == null || $player->getOpenSession() == null
       || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player invalid\n";
    return;
   }

   $sql  = 'INSERT INTO events (event_timestamp, roleperiod_id, event_type, ';
   $sql .= 'event_arg, event_location) VALUES (FROM_UNIXTIME(' . $date . '), ';
   $sql .= $player->getOpenSession()->getRolePeriod()->getID() . ', ';
   $sql .= '\'object built\', \'' . s($props['object']) . '\', \'';
   $sql .= s($props['position']) . '\')';
   $res  = mysql_query($sql);

   $player->addScore(2);
  }

  private function assist($date, $player, $line) {
   $line = substr($line, 8); // against
   $victim = Parser::parsePlayer($line);
   $victim = Player::getBySteamID($victim['steamid']);
   $props = Parser::parseProps($line);

   if ($victim == null || $victim->getOpenSession() == null
       || $victim->getOpenSession()->getRolePeriod() == null) {
    echo "Victim invalid\n";
    return;
   }

   if ($player == null || $player->getOpenSession() == null
       || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player invalid\n";
    return;
   }

   $sql  = 'SELECT kill_id FROM kills WHERE kill_victim = ';
   $sql .= $victim->getOpenSession()->getRolePeriod()->getID() . ' ORDER BY kill_timestamp DESC LIMIT 0,1';
   $res  = mysql_query($sql);

   if (mysql_num_rows($res) == 0) {
    echo "Couldn't find kill for assist\n";
    return;
   }

   $num  = mysql_result($res, 0);

   $sql  = 'UPDATE kills SET kill_assist = ';
   $sql .= $player->getOpenSession()->getRolePeriod()->getID();
   $sql .= ', kill_assist_position = \'' . $props['assister_position'] . '\' WHERE kill_id = ' . $num;
   $res  = mysql_query($sql);

   $score = ($victim->getScore() / $player->getScore()) 
                 * PlayerClass::getModifier($victim->getOpenSession()->getRolePeriod()->getClass(),
                                            $player->getOpenSession()->getRolePeriod()->getClass());
   $player->addScore($score);
  }

  private function chargeDeployed($date, $player) {
   if ($player == null || $player->getOpenSession() == null
       || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player invalid\n";
    return;
   }

   $sql  = 'INSERT INTO events (event_timestamp, roleperiod_id, event_type) ';
   $sql .= 'VALUES (FROM_UNIXTIME(' . $date . '), ' . $player->getOpenSession()->getRolePeriod()->getID() . ', \'ubercharge\')';
   $res = mysql_query($sql);

   $player->addScore(2);
  }

  private function domination($date, $player, $line, $type) {
   $line = substr($line, 8); // against

   $victim = Parser::parsePlayer($line);
   $victim = Player::getBySteamID($victim['steamid']);

   if ($victim == null || $victim->getOpenSession() == null
       || $victim->getOpenSession()->getRolePeriod() == null) {
    echo "Victim invalid\n";
    return;
   }

   if ($player == null || $player->getOpenSession() == null
       || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player invalid\n";
    return;
   }

   $sql  = 'INSERT INTO events (event_timestamp, roleperiod_id, event_type, ';
   $sql .= 'event_victim) VALUES (FROM_UNIXTIME(' . $date . '), ';
   $sql .= $player->getOpenSession()->getRolePeriod()->getID() . ', \'' . $type;
   $sql .= '\', ' . $victim->getOpenSession()->getRolePeriod()->getID() . ')';
   $res = mysql_query($sql);

   $player->addScore(5);
  }

 }

?>
