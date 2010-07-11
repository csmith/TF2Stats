<?PHP

 require_once('inc/config.php');
 require_once('inc/ext.php');
 require_once('inc/classestable.php');
 require_once('inc/playertable.php');
 require_once('inc/weaponslist.php');
 require_once(STATS_DIR . '/inc/database.php');

 $sql = 'SELECT map_id FROM maps WHERE map_name = \'' . s($_GET['map']) . '\'';
 $res = mysql_query($sql);

 if (mysql_num_rows($res) == 0) {
  require('404.php'); 
  exit;
 }

 define('MAP', (int) mysql_result($res, 0));

 define('TITLE', 'Map information :: ' . htmlentities($_GET['map']));
 require_once('inc/header.php');

 echo '<h2>Map Information: ', htmlentities($_GET['map']), '</h2>';

 echo '<div class="left"><h3>Map Preview</h3>';
 echo '<div class="map large">';
 echo '<img src="', sprintf(URL_MAP, 'large', $_GET['map']), '" ';
 echo 'alt="Image of ', htmlentities($_GET['map']), '" class="map large">';
 echo htmlentities($_GET['map']) . '</div>';

 echo '<h3 class="extra">Map Statistics</h3>';

 $sql = 'SELECT COUNT(*) AS num, AVG(TIMESTAMPDIFF(MINUTE, game_starttime, game_endtime)) AS time FROM games WHERE map_id = ' . MAP;
 $sql .= ' GROUP BY map_id';
 $res = mysql_query($sql) or print(mysql_error());
 $row = mysql_fetch_assoc($res);
 $num = $row['num']; $time = $row['time'];

 echo '<ul class="stats">';
 echo '<li>Played <em>', $num, '</em> time', ($num != 1 ? 's' : ''), '.</li>';
 echo '<li>Average map length: <em>', round($time,0), '</em> mins.</li>';
 echo '</ul>';

 echo '<h3>Top Weapons</h3>';
 showWeaponsList('INNER JOIN roleperiods ON roleperiod_id = kill_killer NATURAL JOIN sessions NATURAL JOIN games WHERE map_id = ' . MAP);
 show_extra_map_info($_GET['map']);

 echo '</div>';

 echo '<div class="right"><h3>Top Players</h3>';

 showPlayerTable('NATURAL JOIN games NATURAL JOIN maps', 'map_id = ' . MAP, 10);

 if (ENABLE_DEATHMAPS && file_exists('deathmap.php') && file_exists('inc/deathmap.php')) {
  require('inc/deathmap.php');
  if (isset($coords[$_GET['map']])) {
   echo '<h3>Death Map</h3>';
   echo '<div class="deathmap">';
   echo ' <img src="deathmap.php', sprintf(DM_ARGS, htmlentities($_GET['map'])), '" alt="Death map">';
   echo '</div>';
  }
 }

 echo '<h3>Class Performance</h3>';

 $classes = array();
 $sql = 'SELECT class_id, class_name, COUNT(*) as num FROM classes NATURAL JOIN roleperiods NATURAL JOIN sessions NATURAL JOIN games WHERE map_id = ' . MAP . ' GROUP BY class_id ORDER BY class_name';
 $res = mysql_query($sql) or print(mysql_error());

 while ($row = mysql_fetch_assoc($res)) {
  $classes[$row['class_id']] = array(
   'name' => $row['class_name'],
   'data1' => $row['num']
  );
 }

 $sql = 'SELECT class_id, COUNT(*) AS num FROM roleperiods NATURAL JOIN classes NATURAL JOIN sessions NATURAL JOIN games INNER JOIN kills ON kill_killer = roleperiod_id WHERE map_id = ' . MAP . ' GROUP BY class_id';
 $res = mysql_query($sql) or print(mysql_error());

 while ($row = mysql_fetch_assoc($res)) {
  $classes[$row['class_id']]['data2'] = $row['num'];
 }

 showClassesTable($classes, 'Times played', 'Kills made');

 echo '</div>';

 require_once('inc/footer.php');

?>
