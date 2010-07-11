#!/usr/bin/php -q
<?PHP

 require_once(dirname(__FILE__) . '/inc/database.php');
 require_once(dirname(__FILE__) . '/inc/server.class.php');

 define('SCRIPT_HEAD1', 'updateservers.php v0.1');
 define('SCRIPT_HEAD3', 'Updates server information');

 require(dirname(__FILE__) . '/inc/cliheader.php');

 $sql = 'SELECT server_id, server_ip, server_port FROM servers';
 $res = mysql_query($sql);
 while ($row = mysql_fetch_assoc($res)) {
  try {
   $server = new Server($row['server_ip'], $row['server_port']);
   $info = $server->getInfo();
   mysql_query('UPDATE servers SET server_name = \'' . s($info['name']) . '\' WHERE server_id = ' . $row['server_id']);
  } catch (Exception $ex) {
   // TODO: Record the fact that it's down
  }
 }

?>
