<?PHP

 require_once(dirname(__FILE__) . '/database.php');

 class Weapon {

  private static $weapons = array();

  public static function getID($name) {
   if (!isset(self::$weapons[$name])) {
    $ename = '\'' . s($name) . '\'';

    $sql  = 'INSERT INTO weapons (weapon_name, weapon_modifier, weapon_displayname) VALUES (';
    $sql .= $ename . ', 1, ' . $ename . ')';
    $res  = mysql_query($sql);

    self::$weapons[$name] = array(
     'weapon_name' => $name,
     'weapon_modifier' => 1,
     'weapon_displayname' => $name,
     'weapon_id' => mysql_insert_id()
    ); 

    echo "** Added new weapon: $name\n";
   }

   return self::$weapons[$name]['weapon_id'];
  }

  public static function getModifier($name) {
   if (!isset(self::$weapons[$name])) {
    die("No such weapon: $name");
   }

   return self::$weapons[$name]['weapon_modifier'];
  }

  public static function init() {
   $sql = 'SELECT weapon_id, weapon_name, weapon_modifier, weapon_displayname FROM weapons';
   $res = mysql_query($sql);
   while ($row = mysql_fetch_assoc($res)) {
    self::$weapons[$row['weapon_name']] = $row;
   }
  }

 }

 Weapon::init();

?>
