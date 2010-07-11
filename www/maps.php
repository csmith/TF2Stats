<?PHP

 require_once('inc/config.php');
 require_once('inc/mostmaps.php');
 require_once(STATS_DIR . '/inc/database.php');

 define('TITLE', 'Maps');

 require_once('inc/header.php');

 echo '<h2>Maps</h2>';
 echo '<div class="left">';
 echo '<h3>Most played</h3>';
 showMostMaps();
 echo '<h3 class="extra">Most kills per minute</h3>';
 showMostMapsSQL('SELECT map_name, (COUNT(*)/SUM(TIMESTAMPDIFF(MINUTE, game_starttime, game_endtime))) AS kills FROM maps NATURAL JOIN games NATURAL JOIN sessions NATURAL JOIN roleperiods INNER JOIN kills ON roleperiod_id = kill_killer WHERE session_endtime > \'0000-00-00\' GROUP BY map_name ORDER BY kills DESC LIMIT 0,3');
 echo '<h3 class="extra">Most recently played</h3>';
 showMostMapsSQL('SELECT map_name FROM maps NATURAL JOIN games ORDER BY game_endtime DESC LIMIT 0,3');

 echo '</div>';

 echo '<div class="right">';
 echo '<h3>Full map list</h3>';

 echo '<table>';
 echo '<tr><th>Map</th><th>Times Played</th><th>Average Session Length</th><th>Last Played</th></tr>';

 $sql = 'SELECT map_name, COUNT(*) AS times, AVG(TIMESTAMPDIFF(MINUTE, game_starttime, game_endtime)) AS length, MAX(UNIX_TIMESTAMP(game_endtime)) AS last FROM maps NATURAL JOIN games GROUP BY map_name ORDER BY map_name';
 $res = mysql_query($sql);
 $i = 0;

 while ($row = mysql_fetch_assoc($res)) {
  $i++;
  echo '<tr class="', ($i & 1 ? '' : 'even'), '">';
  echo '<td><a href="map.php?map=', htmlentities($row['map_name']), '">';
  echo htmlentities($row['map_name']), '</a></td>';
  echo '<td>', $row['times'], '</td>';
  echo '<td>', $row['length'], '</td>';
  echo '<td>', date('r', $row['last']), '</td>';
  echo '</tr>';
 }
 echo '</table>';

 echo '</div>';

 require_once('inc/footer.php');

?>
