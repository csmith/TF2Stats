#!/usr/bin/php -q
<?PHP

 require_once(dirname(__FILE__) . '/config.php');
 require_once(dirname(__FILE__) . '/inc/database.php');
 require_once(dirname(__FILE__) . '/inc/game.php');
 require_once(dirname(__FILE__) . '/inc/updateranks.php');
 require_once(dirname(__FILE__) . '/inc/parser/parser.php');

 define('SCRIPT_HEAD1', 'bulkimport.php v0.1');
 define('SCRIPT_HEAD3', 'Imports log files in bulk');

 require(dirname(__FILE__) . '/inc/cliheader.php');

 $ranks = $erase = $noupdate = false;

 foreach ($argv as $arg) {
  if ($arg == '--erase') {
   $erase = true;
  } else if ($arg == '--ranks') {
   $ranks = true;
  } else if ($arg == '--noupdate') {
   $noupdate = true;
  }
 }

 mysql_query('UPDATE config SET config_value = \'true\' WHERE config_key = \'updating\'');

 if ($erase) {
  echo "*** Erasing previous data ... ";

  foreach (array('games', 'maps', 'players', 'roleperiods', 'sessions', 
	         'kills', 'events', 'files', 'awardwinners', 'groups',
                 'groupmemberships') as $table) {
   mysql_query('TRUNCATE ' . $table);
  }

  echo "Done\n";
 }

 if (!$ranks && !$noupdate) {
  $sql = 'SELECT server_id, server_logdir FROM servers';
  $servers = mysql_query($sql);
  while ($server = mysql_fetch_assoc($servers)) {
   if ($server['server_logdir'] === null || empty($server['server_logdir'])) {
    // No log dir for this server
    continue;
   }

   foreach (glob($server['server_logdir'] . '/*') as $file) {
    $offset = $pos = 0;

    $sql = 'SELECT file_size, file_offset FROM files WHERE file_name = \'' . s($file) . '\'';
    $res = mysql_query($sql);

    if (mysql_num_rows($res) > 0) {
     $row = mysql_fetch_assoc($res);
     if ((int) $row['file_size'] == filesize($file)) {
      continue;
     } else {
      $offset = (int) $row['file_offset'];
     }
    }

    Game::setServer($server['server_id']);

    echo "Reading $file...\n";

    $fh = fopen($file, 'r');
    fseek($fh, $offset);

    $line = null;

    while (!feof($fh)) {
     if ($line != null) {
      Parser::parseLine($line);
     }
     $pos = ftell($fh);
     $line = fgets($fh);
    }

    if ($offset > 0) {
     $sql = 'UPDATE files SET file_size = ' . filesize($file) . ', file_offset  = ' . $pos . ' WHERE file_name = \'' . s($file) . '\'';
     mysql_query($sql);
    } else {
     $sql = 'INSERT INTO files (file_size, file_offset, file_name) VALUES (' . filesize($file) . ', ' . $pos . ', \'' . s($file) . '\')';
     mysql_query($sql);
    }
   
    fclose($fh);
   }
  }
 }

 if (!$noupdate) {
  mysql_query('UPDATE groups SET group_score = (SELECT SUM(player_score) FROM groupmemberships NATURAL JOIN players WHERE groupmemberships.group_id = groups.group_id GROUP BY groupmemberships.group_id), group_members = (SELECT COUNT(*) FROM groupmemberships NATURAL JOIN players WHERE groupmemberships.group_id = groups.group_id GROUP BY groupmemberships.group_id)');

  updateRanks();
 }
 
 mysql_query('UPDATE config SET config_value = \'false\' WHERE config_key = \'updating\'');

?>
