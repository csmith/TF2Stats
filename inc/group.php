<?PHP

 require_once(dirname(__FILE__) . '/database.php');

 class Group {

  private $id;
  private $players = array();
  private static $cache = array();

  protected function __construct($id) {
   $this->id = $id;
  }

  public function ensureMember(&$player) {
   if (!isset($this->players[$player->getID()])) {
    $sql = 'SELECT membership_id FROM groupmemberships WHERE player_id = ' . $player->getID() . ' AND group_id = ' . $this->id;
    $res = mysql_query($sql);

    if (mysql_num_rows($res) == 0) {
     $sql = 'INSERT INTO groupmemberships (player_id, group_id) VALUES (' . $player->getID() . ', ' . $this->id . ')';
     mysql_query($sql);
    }

    $this->players[$player->getID()] = true;
   }
  }

  public static function getGroup($tag) {
   if (!isset(self::$cache[$tag])) {
    $sql = 'SELECT group_id FROM groups WHERE group_name = \'' . s($tag) . '\' AND group_type = \'tag\'';
    $res = mysql_query($sql);
 
    if (mysql_num_rows($res) > 0) {
     $row = mysql_fetch_assoc($res);
     $id  = (int) $row['group_id'];
    } else {
     $sql = 'INSERT INTO groups (group_name, group_type) VALUES (\'' . s($tag) . '\', \'tag\')';
     $res = mysql_query($sql);
     $id  = mysql_insert_id();
    }

    self::$cache[$tag] = new Group($id);
   }

   return self::$cache[$tag];
  }

}

?>
