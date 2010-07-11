<?PHP

 require_once('inc/config.php');
 require_once('inc/mostmaps.php');
 require_once('inc/playertable.php');
 require_once('inc/weaponslist.php');
 require_once(STATS_DIR . '/inc/database.php');

 define('TITLE', 'Overview');

 require_once('inc/header.php');

 echo '<h2>Overview</h2>';

 echo '<div class="left">', "\n";

 /** -- Output most played maps -- **/
 echo '<h3>Most played maps</h3>', "\n";
 showMostMaps(); 

 /** -- Output overall statistics -- **/
 echo '<h3 class="extra">Overall statistics</h3>', "\n";
 echo '<ul class="stats">', "\n";

 $sql = 'SELECT COUNT(*) FROM players';
 $res = mysql_query($sql);
 $pcount = mysql_result($res, 0);
 
 $sql = 'SELECT COUNT(*) FROM kills';
 $res = mysql_query($sql);
 $kcount = mysql_result($res, 0);

 $sql = 'SELECT HOUR(kill_timestamp) AS thehour, COUNT(*) AS num FROM kills GROUP BY thehour ORDER BY num DESC LIMIT 0,1';
 $res = mysql_query($sql);
 $tophour = mysql_result($res, 0);

 $sql = "SELECT class_displayname, SUM(roleperiod_endtime - roleperiod_starttime) AS time FROM classes NATURAL JOIN roleperiods WHERE roleperiod_endtime > '0000-00-00' GROUP BY class_name ORDER BY time DESC LIMIT 0,1";
 $res = mysql_query($sql);
 $topclass = mysql_result($res, 0);

 $sql = "SELECT SUM(session_endtime - session_starttime) FROM sessions WHERE session_endtime > '0000-00-00'";
 $res = mysql_query($sql);
 $time = mysql_result($res, 0);

 echo '<li><em>', number_format($pcount), '</em> players tracked.</li>', "\n";
 echo '<li><em>', number_format($kcount), '</em> kills logged.</li>', "\n";
 echo '<li>Most popular time of day: <em>', $tophour, '-', (++$tophour % 24), ':00</em>.</li>', "\n";
 echo '<li>Most played class: <em>', $topclass, '</em>.</li>', "\n";
 echo '<li><em>', number_format(round($time/(60*60),0)), '</em> hours of play time.</li>', "\n";
 echo '</ul>', "\n";

 /** -- Output top weapons -- **/
 echo '<h3>Top weapons</h3>', "\n";
 
 showWeaponsList();

 echo '</div>', "\n";
 echo '<div class="right">', "\n";

 /** -- Output server list -- **/
 if (ENABLE_SERVER_LIST) {
  echo '<h3>Participating servers</h3>';
  
  echo '<table><tr><th>Server name</th><th>Address</th><th>Connect</th></tr>';

  $sql = 'SELECT server_name, server_ip, server_port FROM servers ORDER BY server_name';
  $res = mysql_query($sql);

  $i = 0;
  while ($row = mysql_fetch_assoc($res)) {
   $i++;
   echo '<tr class="', ($i & 1) ? '' : 'even', '"><td>', htmlentities($row['server_name'], ENT_COMPAT, 'UTF-8'), '</td>';
   echo '<td>', $row['server_ip'], ':', $row['server_port'], '</td>';
   echo '<td class="connect"><a href="steam://connect/', $row['server_ip'], ':', $row['server_port'], '">';
   echo '<img src="res/steam.png" alt="Steam" title="Connect with Steam">using steam</a>';
   echo '<a href="hlsw://', $row['server_ip'], ':', $row['server_port'], '">';
   echo '<img src="res/hlsw.png" alt="HLSW" title="Connect with HLSW">using hlsw</a>';
   echo '</td></tr>';
  }

  echo '</table>';
 }

 /** -- Output top players -- **/
 echo '<h3>Top players</h3>', "\n";

 showPlayerTable('', '1=1', OVERVIEW_PLAYERS);
 
 echo '</div>', "\n";

 require_once('inc/footer.php');

?>
