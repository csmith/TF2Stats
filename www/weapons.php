<?PHP

 require_once('inc/config.php');
 require_once('inc/weaponslist.php');
 require_once(STATS_DIR . '/inc/database.php');

 define('TITLE', 'Weapons');

 require_once('inc/header.php');

 echo '<h2>Weapons</h2>';

 echo '<div class="left">';

 echo '<h3>Weapon types</h3>';

 $sql = 'SELECT COUNT(*) AS kill_count, weapon_class FROM weapons NATURAL JOIN kills GROUP BY weapon_class ORDER BY kill_count DESC';
 $res = mysql_query($sql) or print(mysql_error());
 $data = array();

 while ($row = mysql_fetch_assoc($res)) {
  $data[$row['weapon_class'] == '' ? 'other' : $row['weapon_class']] = (int) $row['kill_count'];
 }

 echo '<img src="http://chart.apis.google.com/chart?cht=p&amp;chbh=60&amp;chd=t:';

 $total = array_sum($data);
 $first = true;

 foreach ($data as $value) {
  if ($first) { $first = false; } else { echo ','; }
  echo round(100 * $value / $total, 2);
 }

 echo '&amp;chs=350x250&amp;chl=', implode('|', array_keys($data));
 echo '&amp;chtt=Kills by weapon type&amp;chco=9D5321';
 echo '" alt="Graph of kills by weapon type">';


 echo '</div>';

 echo '<div class="right">';

 echo '<h3>Weapon information</h3>';

 echo '<table>';
 echo '<tr><th>Rank</th><th>Weapon</th><th>Modifier</th><th>Kills</th><th>Avg Distance</th></tr>';

 $sql = "SELECT weapon_name, weapon_modifier, weapon_displayname, COUNT(*) as kills, AVG(kill_distance) AS distance FROM weapons NATURAL JOIN kills GROUP BY weapon_displayname ORDER BY kills DESC";
 $res = mysql_query($sql);

 $rank = 1;
 while ($row = mysql_fetch_assoc($res)) {
  echo '<tr', $rank % 2 == 0 ? ' class="even"' : '', '><td>', $rank++, '</td>';
  echo '<td><img src="/stats/res/weapons/', $row['weapon_name'], '.png" alt="';
  echo htmlspecialchars($row['weapon_displayname']), '"> ', htmlspecialchars($row['weapon_displayname']);
  echo '</td><td class="num">', sprintf('%01.1f', $row['weapon_modifier']), '&times;</td><td class="num">', number_format($row['kills']);
  echo '</td><td class="num">', number_format($row['distance']), '</td></tr>';
 }

 echo '</table>';

 echo '</div>';

 require_once('inc/footer.php');

?>
