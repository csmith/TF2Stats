<?PHP

 require_once(dirname(__FILE__) . '/database.php');
 require_once(dirname(__FILE__) . '/session.php');

 class Game {

  private static $current = null;
  private static $maps = array();
  private static $server = null;

  private $id;

  private function __construct($id = null, $map = null, $starttime = null) {
   if ($id != null) {
    $this->id = $id;
   } else {
    assert($map != null && $starttime != null);

    $sql = 'INSERT INTO games (map_id, game_starttime, server_id) VALUES (' . self::resolveMap($map) . ', FROM_UNIXTIME(' . $starttime . '), ' . self::$server . ')';
    $res = mysql_query($sql);
    $this->id = mysql_insert_id();
   }
  }

  public static function setServer($server) {
   self::$server = $server;
  }

  public function getID() {
   return $this->id;
  }

  private function end($timestamp) {
   $sql = 'UPDATE games SET game_endtime = FROM_UNIXTIME(' . $timestamp . ') WHERE game_id = ' . $this->id;
   $res = mysql_query($sql);
  }

  public static function init() {
   $sql = 'SELECT game_id FROM games WHERE game_endtime = \'0000-00-00\'';
   $res = mysql_query($sql);

   if (mysql_num_rows($res) > 0) {
    self::$current = new Game((int) mysql_result($res, 0));
   }
  }

  public static function changeMap($timestamp, $map) {
   if (self::$current != null) {
    self::$current->end($timestamp);
   }

   self::$current = new Game(null, $map, $timestamp);
  }

  public static function resolveMap($name) {
   if (isset(self::$maps[$name])) { return self::$maps[$name]; }

   $sql = 'SELECT map_id FROM maps WHERE map_name = \'' . s($name) . '\''; 
   $res = mysql_query($sql);

   if (mysql_num_rows($res) > 0) {
    return self::$maps[$name] = (int) mysql_result($res, 0);
   } else {
    $sql = 'INSERT INTO maps (map_name) VALUES (\'' . s($name) . '\')';
    $res = mysql_query($sql);

    return self::$maps[$name] = mysql_insert_id();
   }
  }

  public static function getCurrent() {
   return self::$current;
  }

 }

 Game::init();

?>
