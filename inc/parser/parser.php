<?PHP

 function sqr($x) {
  return $x * $x;
 }

 require_once(dirname(dirname(__FILE__)) . '/player.php');

 class Parser {

  private static $handlers = array();
  private static $daemon = null;

  public static function setDaemon(&$daemon) {
   self::$daemon =& $daemon;
  }

  public static function hasDaemon() {
   return self::$daemon != null;
  }

  public static function getDaemon() {
   return self::$daemon;
  }

  public static function parseDate(&$line) {
   $index = strpos($line, ': ');
   $date = substr($line, 0, $index);
   $line = substr($line, $index + 2);
   return strtotime(str_replace(' - ', ' ', $date));
  }

  public static function parsePlayerString(&$line) {
   if (preg_match('/^"(.*?<[0-9]+><(BOT|Console|STEAM_.*?)><.*?>)"/', $line, $m)) {
    $line = substr($line, strlen($m[0]) + 1);
    return $m[1];
   }

   if (!preg_match('/^".*?" = .*$/', $line)) {
    echo "Unable to parse player string: $line";
   }

   return self::parseString($line);
  }

  public static function parseString(&$line) {
   $index = strpos($line, '"', 1);
   $data = substr($line, 1, $index - 1);
   $line = substr($line, $index + 2);
   return $data;
  }

  public static function parsePlayer(&$line, $fromLine = true) {
   if ($fromLine) {
    $player = self::parsePlayerString($line);
   } else {
    $player = $line;
   }

   if (preg_match('((.*?)<([0-9]+)><([A-Za-z_:0-9]+)><([A-Za-z]*)>)', $player, $m)) {
    return array('alias' => $m[1], 'uid' => $m[2], 'steamid' => $m[3], 'team' => $m[4]);
   } else {
    // Unable to parse player
    return null;
   }
  }

  public static function parseProps(&$line) {
   preg_match_all('(\((.*?) "(.*?)"\))', $line, $matches, PREG_SET_ORDER);
   $res = array();

   foreach ($matches as $match) {
    $res[$match[1]] = $match[2];
   }

   if (isset($res['attacker_position']) && isset($res['victim_position'])) {
    // Calculate distance
    $a = explode(" ", $res['attacker_position']); 
    $v = explode(" ", $res['victim_position']);

    $res['distance'] = sqrt(sqr($a[0] - $v[0]) + sqr($a[1] - $v[1]) + sqr($a[2] - $v[2]));
   }

   return $res;
  }

  public static function parseLine($line) {
   if ($line[0] == 'L') {
    $line = substr($line, 2);
   } else if (substr($line, 4, 2) == 'RL') {
    $line = substr($line, 7);
   } else {
    // TODO: log me 
    return; 
   }

   $date = self::parseDate($line);
   $player = null;

   // Broken admin mod
   if (substr($line, 0, 2) == 'L ') {
    $line = substr($line, 2);
    $date = self::parseDate($line);
   }

   if ($line[0] == '"') {
    // Player event

    if (($player = self::parsePlayer($line)) == null) {
     // Or not?
     return;
    }
   }

   $parts = explode(' ', $line);
   $handler = strtolower(str_replace(',', '', $parts[0]));

   if (isset(self::$handlers[$handler])) {
    self::$handlers[$handler]->parseLine($date, Player::getBySteamID($player['steamid']), $player, implode(' ', array_slice($parts, 1)));
   } else {
    // TODO: log me
    return;
   }
  }

  public static function init() {
   foreach (glob(dirname(__FILE__) . '/handlers/*.php') as $file) {
    require($file);
    
    $handler = basename($file, '.php');
    $name = str_replace(' ', '', ucwords(str_replace('_', ' ', $handler))) . 'Handler';
    self::$handlers[$handler] = new $name();
   }
  }

 }

 Parser::init();

?>
