<?PHP

 require_once(dirname(__FILE__) . '/database.php');
 require_once(dirname(__FILE__) . '/class.php');

 class Player {

  private static $players = array();

  private $id;
  private $steamid;
  private $score;
  private $session = null;

  private function __construct($id, $steamid, $score, $session = null) {
   $this->id = $id;
   $this->steamid = $steamid;
   $this->score = $score;

   if ($session != null) {
    $this->session = new Session($this, $session);
   }
  }

  public function getKPD() {
   $sql  = 'SELECT SUM(roleperiod_kills)/SUM(roleperiod_deaths) AS kpd FROM ';
   $sql .= 'sessions NATURAL JOIN roleperiods WHERE player_id = ' . $this->id;
   $res  = mysql_query($sql);
   $row  = mysql_fetch_assoc($res);
   return $row['kpd'];
  }

  public function getScore() {
   return $this->score;
  }

  public function addScore($score) {
   $this->score += $score;

   $sql = 'UPDATE players SET player_score = ' . $this->score . ' WHERE player_id = ' . $this->id;
   $res = mysql_query($sql);
  }

  public function getID() {
   return $this->id;
  }

  public function getSteamID() {
   return $this->steamid;
  }

  public function getOpenSession() {
   return $this->session;
  }

  public function closeSession($timestamp) {
   if ($this->session == null) {
    // Do nothing - broken? 
   } else {
    $this->session->close($timestamp);
    $this->session = null;
   }
  }

  public function openSession($timestamp, $uid, $alias) {
   if ($this->session != null) { $this->closeSession($timestamp); }
   
   $this->session = new Session($this, null, $timestamp + 1, $uid, $alias);
  }

  public static function getBySteamID($steamid) {
   if (isset(self::$players[$steamid])) {
    // We already have one cached.
    return self::$players[$steamid];
   }

   $sql = 'SELECT player_id, player_score FROM players WHERE player_steamid = \'' . s($steamid) . '\'';
   $res = mysql_query($sql);

   if (mysql_num_rows($res) > 0) {
    // Player already known, grab ID from db.

    $row = mysql_fetch_assoc($res);
    $id = $row['player_id'];
    $score = $row['player_score']; 
    $session = null;

    // Check for open sessions
    $sql = 'SELECT session_id FROM sessions WHERE player_id = ' . $id . ' AND session_endtime = \'0000-00-00\'';
    $res = mysql_query($sql);

    if (mysql_num_rows($res) > 0) {
     $session = (int) mysql_result($res, 0);
    }
    
    return self::$players[$steamid] = new Player($id, $steamid, $score, $session);
   } else {
    // New player, insert them into the db.

    $sql = 'INSERT INTO players (player_steamid) VALUES (\'' . s($steamid) . '\')';
    $res = mysql_query($sql);

    return self::$players[$steamid] = new Player(mysql_insert_id(), $steamid, 1000);
   }
  }

 }

?>
