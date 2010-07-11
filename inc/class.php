<?PHP

 require_once(dirname(__FILE__) . '/database.php');

 class PlayerClass {

  private static $classes = array();
  private static $modifiers = array();

  public static function getID($name) {
   if (is_int($name) || ctype_digit($name)) { return $name; }

   return self::$classes[$name]['class_id'];
  }

  public static function getModifier($victim, $killer) {
   if (isset(self::$modifiers[$victim][$killer])) {
    return self::$modifiers[$victim][$killer];
   }

   if ($victim == NULL || $killer == NULL) {
    throw new Exception("Victim or killer was null");
   }

   $sql = 'SELECT modifier_modifier FROM class_modifiers WHERE modifier_victimclass = ' . $victim . ' AND modifier_killerclass = ' . $killer;
   $res = mysql_query($sql) or die(mysql_error());

   return self::$modifiers[$victim][$killer] = mysql_result($res, 0);
  }

  public static function init() {
   $sql = 'SELECT class_id, class_name, class_displayname FROM classes';
   $res = mysql_query($sql);
   while ($row = mysql_fetch_assoc($res)) {
    self::$classes[$row['class_name']] = $row;
   }
  }

 }

 PlayerClass::init();

?>
