<?PHP

 require_once(dirname(dirname(dirname(__FILE__))) . '/weapon.php');

 class KilledHandler {

  /*-------------------------------------------------------------------------*\
   * Handles kill events.                                                    *
   *                                                                         *
   * Sample line: "Demented-Idiot<3><STEAM_0:1:2867409><Blue>" with          *
   *  "scattergun" (attacker_position "-76 -573 23") (victim_position        *
   *  "33 -853 24")                                                          *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $victim = Parser::parsePlayer($line);
   $victim = Player::getBySteamID($victim['steamid']);
   
   $line = substr($line, 5); // with
   $weapon = Parser::parseString($line);
   $props = Parser::parseProps($line);

   if (isset($props['customkill'])) {
    $weapon .= ' (' . $props['customkill'] . ')';
   }

   $weapon = Weapon::getID($weaponname = $weapon);

   if ($player == null || $player->getOpenSession() == null
     || $player->getOpenSession()->getRolePeriod() == null) {
    echo "Player " . $player->getSteamID() . " has no role, dropping kill\n";
    return;
   }

   if ($victim == null || $victim->getOpenSession() == null
     || $victim->getOpenSession()->getRolePeriod() == null) {
    echo "Victim " . $victim->getSteamID() . " has no role, dropping kill\n";
    return;
   }

   $sql  = 'INSERT INTO kills (kill_killer, kill_killer_position, kill_victim, ';
   $sql .= 'kill_victim_position, weapon_id, kill_distance, kill_timestamp) ';
   $sql .= 'VALUES (' . $player->getOpenSession()->getRolePeriod()->getID() . ', ';
   $sql .= '\'' . s($props['attacker_position']) . '\', ';
   $sql .= $victim->getOpenSession()->getRolePeriod()->getID() . ', ';
   $sql .= '\'' . s($props['victim_position']) . '\', ' . $weapon . ', ' . $props['distance'];
   $sql .= ', FROM_UNIXTIME(' . $date . '))';
   $res  = mysql_query($sql) or die("$sql\n\n" . mysql_error());

   $score = 5 * ($victim->getScore() / $player->getScore()) * Weapon::getModifier($weaponname)
		 * PlayerClass::getModifier($victim->getOpenSession()->getRolePeriod()->getClass(),
					    $player->getOpenSession()->getRolePeriod()->getClass());
   $player->addScore($score);
   $victim->addScore(-1 * $score);
  }

 }

?>
