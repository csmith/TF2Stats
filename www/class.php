<?PHP

 require_once('inc/config.php');
 require_once('inc/ext.php');
 require_once('inc/classestable.php');
 require_once('inc/mostmaps.php');
 require_once('inc/playertable.php');
 require_once('inc/weaponslist.php');
 require_once(STATS_DIR . '/inc/database.php');

 $sql = 'SELECT class_id, class_displayname FROM classes WHERE class_name = \'' . s($_GET['class']) . '\'';
 $res = mysql_query($sql);

 if (mysql_num_rows($res) == 0) {
  require('404.php'); 
  exit;
 }

 $row = mysql_fetch_assoc($res);

 define('CLASSID', $row['class_id']); 
 define('NAME', $row['class_displayname']); 

 define('TITLE', 'Class information :: ' . NAME);
 require_once('inc/header.php');

 echo '<h2>Class Information: ', NAME, '</h2>';
 echo '<div class="left">';

 echo '<h3>Most Played On</h3>';

 showMostMaps('NATURAL JOIN roleperiods', 'class_id = ' . CLASSID);

 echo '<h3 class="extra">Top Weapons</h3>';
 showWeaponsList('INNER JOIN roleperiods ON roleperiod_id = kill_killer WHERE class_id = ' . CLASSID);
 show_extra_map_info($_GET['map']);

 echo '</div>';

 echo '<div class="right"><h3>Top Players</h3>';

 showPlayerTable('', 'class_id = ' . CLASSID, 10, true);

 echo '<h3>Class Relationships</h3>';

 $classes = array();
 $sql = 'SELECT rp1.class_id, class_name, COUNT(*) as num FROM classes NATURAL JOIN roleperiods AS rp1 INNER JOIN kills AS k1 ON kill_killer = roleperiod_id INNER JOIN roleperiods AS rp2 ON kill_victim = rp2.roleperiod_id WHERE rp2.class_id = ' . CLASSID . ' GROUP BY class_id ORDER BY class_name';
 $res = mysql_query($sql) or print(mysql_error());

 while ($row = mysql_fetch_assoc($res)) {
  $classes[$row['class_id']] = array(
   'name' => $row['class_name'],
   'data1' => $row['num']
  );
 }

 $sql = 'SELECT rp1.class_id, class_name, COUNT(*) as num FROM classes NATURAL JOIN roleperiods AS rp1 INNER JOIN kills AS k1 ON kill_victim = roleperiod_id INNER JOIN roleperiods AS rp2 ON kill_killer = rp2.roleperiod_id WHERE rp2.class_id = ' . CLASSID . ' GROUP BY class_id ORDER BY class_name';
 $res = mysql_query($sql) or print(mysql_error());

 while ($row = mysql_fetch_assoc($res)) {
  $classes[$row['class_id']]['data2'] = $row['num'];
  $classes[$row['class_id']]['name'] = $row['class_name'];
 }

 showClassesTable($classes, 'Killer of ' . NAME, 'Victim of ' . NAME, true);

 echo '</div>';

 require_once('inc/footer.php');

?>
