<?PHP

require_once(dirname(__FILE__) . '/class.php');

class RolePeriod {

 private static $teams = array('Red' => 1, 'Blue' => 2, 'Spectator' => 3, '' => 0, 'Unassigned' => 0);

 private $id;
 private $class;

 public function __construct(Player &$player, Session &$session, $id = null, $timestamp = null, $team = null, $class = null) {
  if ($id == null) {
   assert($timestamp != null && $team != null && $class != null);

   $team = self::$teams[$team];
   $class = $this->class = PlayerClass::getID($class);

   $sql  = 'INSERT INTO roleperiods (session_id, player_id, roleperiod_team, class_id, roleperiod_starttime)';
   $sql .= ' VALUES(' . $session->getID() . ', ' . $player->getID() . ', ' . $team . ', ' . $class . ', FROM_UNIXTIME(' . $timestamp . '))';
   $res  = mysql_query($sql);

   $this->id = mysql_insert_id();
  } else {
   $this->id = $id;

   $sql = 'SELECT class_id FROM roleperiods WHERE roleperiod_id = ' . $id;
   $res = mysql_query($sql);
   $this->class = mysql_result($res, 0);
  }
 }

 public function getID() {
  return $this->id;
 }

 public function getClass() {
  return $this->class;
 }

 public function close($timestamp) {
  $sql  = 'UPDATE roleperiods SET roleperiod_endtime = FROM_UNIXTIME(' . $timestamp . '),';
  $sql .= ' roleperiod_kills = (SELECT COUNT(*) FROM kills WHERE kill_killer = roleperiod_id),';
  $sql .= ' roleperiod_deaths = (SELECT COUNT(*) FROM kills WHERE kill_victim = roleperiod_id),';
  $sql .= ' roleperiod_assists = (SELECT COUNT(*) FROM kills WHERE kill_assist = roleperiod_id)';
  $sql .= ' WHERE roleperiod_id = ' . $this->id;
  $res = mysql_query($sql);
 }

}

?>
