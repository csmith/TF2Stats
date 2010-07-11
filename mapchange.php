#!/usr/bin/php -q
<?PHP

 require_once(dirname(__FILE__) . '/config.php');
 require_once(dirname(__FILE__) . '/inc/rcon.class.php');
 require_once(dirname(__FILE__) . '/inc/database.php');

 define('SCRIPT_HEAD1', 'mapchange.php v0.1');
 define('SCRIPT_HEAD3', 'Changes maps on an empty server');

 require(dirname(__FILE__) . '/inc/cliheader.php');

 $sql = 'SELECT server_id, server_ip, server_port, server_rcon FROM servers';
 $res = mysql_query($sql);

 while ($row = mysql_fetch_assoc($res)) {

  echo str_pad($row['server_ip'] . ':' . $row['server_port'], 80, '-', STR_PAD_BOTH);
  echo "\n\n";

  if (empty($row['server_rcon'])) {
   echo "No RCON password specified. Skipping.\n";
   continue;
  }

  $rcon = new Rcon($row['server_ip'], $row['server_port'], $row['server_rcon']);
  foreach (explode("\n", $rcon->getStatus()) as $line) {
   if (preg_match('/^players : ([0-9]+)/', $line, $m)) {
    $players = (int) $m[1];
   } else if (preg_match('/^map\s*: ([^\s]+) at/', $line, $m)) {
    $map = $m[1];
   } else if (preg_match('/^hostname\s*:\s*(.*?)\s*$/', $line, $m)) {
    $hostname = $m[1];
   }
  }

  if (isset($hostname)) {
   $sql = 'UPDATE servers SET server_name = \'' . s($hostname) . '\' WHERE server_id = ' . $row['server_id'];
   mysql_query($sql);
  }

  if (!isset($players)) {
   echo "Could not determine the number of players. Skipping.\n";
   continue;
  } else if ($players > 0) {
   echo "Players on the server; not interfering.\n";
   continue;
  }

  $sql = 'SELECT map_name, SUM(TIMESTAMPDIFF(MINUTE, session_starttime, session_endtime)) AS time FROM maps NATURAL JOIN games NATURAL JOIN sessions GROUP BY map_name ORDER BY time DESC LIMIT 0,10';
  $res = mysql_query($sql);

  $maps = array();
  while ($row = mysql_fetch_assoc($res)) {
   if ($row['map_name'] == $map) { continue; }

   $maps[] = $row['map_name'];
  }

  echo "Currently $players players on map $map.\n";
  $nextmap = $maps[rand(0, count($maps)-1)];
  echo "Selected map $nextmap. Changing... ";
  $rcon->execute('changelevel ' . $nextmap);
  echo "Done\n\n";
 }

 echo str_repeat('-', 80), "\n";

?>
