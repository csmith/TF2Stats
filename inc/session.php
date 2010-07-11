<?PHP

 require_once(dirname(__FILE__) . '/database.php');
 require_once(dirname(__FILE__) . '/group.php');
 require_once(dirname(__FILE__) . '/roleperiod.php');
 require_once(dirname(__FILE__) . '/player.php');

 class Session {

  private $id;
  private $player;
  private $alias;

  private $roleperiod;

  public function __construct(Player &$player, $id = null, $timestamp = null, $uid = null, $alias = null) {
   $this->player =& $player;

   if ($id == null) {
    assert($timestamp != null && $uid != null && $alias != null);

    $sql  = 'INSERT INTO sessions (session_starttime, player_id, session_uid, session_alias, game_id) VALUES (';
    $sql .= 'FROM_UNIXTIME(' . $timestamp . '), ' . $player->getID() . ', ' . $uid . ', ';
    $sql .= '\'' . s($alias) . '\', ' . Game::getCurrent()->getID() . ')';
    $res  = mysql_query($sql);

    $this->id = mysql_insert_id();
    $this->alias = $alias;
    $this->checkGroups();
   } else {
    $this->id = $id;

    $sql = 'SELECT roleperiod_id FROM roleperiods WHERE session_id = ' . $id . ' AND roleperiod_endtime = \'0000-00-00\'';
    $res = mysql_query($sql);
    
    if (mysql_num_rows($res) > 0) {
     $this->roleperiod = new RolePeriod($this->player, $this, (int) mysql_result($res, 0));
    }

    $sql = 'SELECT session_alias FROM sessions WHERE session_id = ' . $id;
    $res = mysql_query($sql);
    $this->alias = mysql_result($res, 0);
   }
  }

  public function getAlias() {
   return $this->alias;
  }

  protected function checkGroups() {
   foreach ($this->getGroups() as $group) {
    Group::getGroup($group)->ensureMember($this->player);
   }
  }

  protected function getGroups() {
   $results = array();

   if (preg_match_all('/^\[.*?\]|\[.*?\]$/', $this->alias, $m)) {
    $results = array_merge($results, $m[0]);
   }

   if (preg_match_all('/^\|.*?\||\|.*?\|$/', $this->alias, $m)) {
    $results = array_merge($results, $m[0]);
   }

   if (preg_match('/@.*?$/', $this->alias, $m)) {
    $results[] = $m[0];
   }

   return $results;
  }

  public function getID() {
   return $this->id;
  }

  public function close($timestamp) {
   $sql = 'UPDATE sessions SET session_endtime = FROM_UNIXTIME(' . $timestamp . ') WHERE session_id = ' . $this->id;
   $res = mysql_query($sql);

   if ($this->roleperiod != null) {
    $this->roleperiod->close($timestamp);
   }
  }

  public function getRolePeriod() {
   return $this->roleperiod;
  }

  public function changeRole($timestamp, $team, $class) {
   if ($this->roleperiod != null) {
    $this->roleperiod->close($timestamp);
   }

   $this->roleperiod = new RolePeriod($this->player, $this, null, $timestamp, $team, $class);
  }

  public function changeTeam($timestamp, $team) {
   if ($this->roleperiod != null) {
    $this->roleperiod->close($timestamp);
    $this->roleperiod = new RolePeriod($this->player, $this, null, $timestamp, $team, $this->roleperiod->getClass());
   }
  }

 }

?>
