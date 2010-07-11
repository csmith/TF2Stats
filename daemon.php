#!/usr/bin/php -q
<?PHP

 require_once(dirname(__FILE__) . '/config.php');
 require_once(dirname(__FILE__) . '/inc/database.php');
 require_once(dirname(__FILE__) . '/inc/daemon.class.php');
 require_once(dirname(__FILE__) . '/inc/parser/parser.php');

 define('SCRIPT_HEAD1', 'daemon.php v0.1');
 define('SCRIPT_HEAD3', 'Analyses server stats in real time ');
 require(dirname(__FILE__) . '/inc/cliheader.php');

 $port = false;
 $server = false;
 $force = false;

 foreach ($argv as $arg) {
  if ($arg == '--port') {
   $port = true;
  } else if ($port === true) {
   $port = (int) $arg;
  } if ($arg == '--server') {
   $server = true;
  } else if ($server === true) {
   $server = (int) $arg;
  } else if ($arg == '--force') {
   $force = true;
  }
 }

 if ($port === true || $port === false) {
  die('ERROR: Please specify a port with --port <port>' . "\n");
 } else if ($server === false) {
  $server = 1;
 } else if ($server === true) {
  die('ERROR: Please specify a valid server with --server <id>'. "\n");
 }

 $sql = 'SELECT server_ip, server_port, server_rcon, server_daemon FROM servers WHERE server_id = ' . $server;
 $res = mysql_query($sql);

 if (mysql_num_rows($res) == 0) {
  die('ERROR: Invalid server. Please specify a valid one with --server <id>' . "\n");
 } else {
  $row = mysql_fetch_assoc($res);

  if ($row['server_rcon'] === null || empty($row['server_rcon'])) {
   die('ERROR: No rcon password specified for that server.' . "\n");
  } else {
   define('SERVER_IP', $row['server_ip']);
   define('SERVER_PORT', (int) $row['server_port']);
   define('RCON_PW', $row['server_rcon']);
  }

  if ((int) $row['server_daemon'] == 0) {
   if ($force) {
    echo "Setting server to daemon mode...\n";
    $sql = 'UPDATE servers SET server_daemon = 1 WHERE server_id = ' . $server;
    mysql_query($sql);
   } else {
    echo str_pad(' WARNING ', 80, '@', STR_PAD_BOTH), "\n";
    echo "@                                                                              @\n";
    echo "@  That server is currently set to log parsing mode. If you switch to daemon   @\n";
    echo "@  mode you will no longer be able to use the 'bulk import' facility, and may  @\n";
    echo "@  lose data if the daemon script is not running constantly.                   @\n";
    echo "@                                                                              @\n";
    echo "@  If you're sure you want to start the daemon for this server, add the        @\n";
    echo "@  --force parameter to the arguments for this script and rerun it.            @\n";
    echo "@                                                                              @\n";
    echo str_repeat('@', 80), "\n";
    exit();
   }
  }
 }

 if (($socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false) {
  die('ERROR: Unable to create socket. Error: ' . socket_strerror(socket_last_error()) . "\n");
 }

 if (!@socket_bind($socket, '0.0.0.0', $port)) {
  die('ERROR: Unable to bind socket. Error: ' . socket_strerror(socket_last_error()) . "\n");
 }
 
 socket_set_block($socket);

 Parser::setDaemon(new Daemon(SERVER_IP, SERVER_PORT, RCON_PW));

 while (true) {
  $buf = $from = '';
  if (socket_recvfrom($socket, $buf, 1024, 0, $from, $port) > -1) {
   echo $buf;
   Parser::parseLine($buf); 
  }
 }
 
?>
