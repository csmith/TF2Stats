<?PHP

 require_once('inc/config.php');
 require_once('inc/ext.php');
 require_once('inc/classestable.php');
 require_once('inc/mostmaps.php');
 require_once('inc/playertable.php');
 require_once('inc/weaponslist.php');
 require_once(STATS_DIR . '/inc/database.php');

 $sql = 'SELECT group_name FROM groups WHERE group_id = ' . ((int) $_GET['group']);
 $res = mysql_query($sql);

 if (mysql_num_rows($res) == 0) {
  require('404.php'); 
  exit;
 }

 $row = mysql_fetch_assoc($res);

 define('GROUPID', (int) $_GET['group']); 
 define('NAME', $row['group_name']); 

 define('TITLE', 'Group information :: ' . htmlentities(NAME, ENT_QUOTES, 'UTF-8'));
 require_once('inc/header.php');

 echo '<h2>Group information: ', htmlentities(NAME, ENT_QUOTES, 'UTF-8'), '</h2>';
 echo '<div class="left">';

 echo '<h3>Favourite maps</h3>';

 showMostMaps('NATURAL JOIN players LEFT OUTER JOIN groupmemberships ON (groupmemberships.player_id = players.player_id)', 'group_id = ' . GROUPID);

 echo '<h3 class="extra">Top Weapons</h3>';
 showWeaponsList(', sessions NATURAL JOIN players LEFT OUTER JOIN groupmemberships ON (groupmemberships.player_id = players.player_id) WHERE kill_killer = sessions.session_id AND group_id = ' . GROUPID);

 echo '</div>';

 echo '<div class="right"><h3>Top Players</h3>';

 showPlayerTable('LEFT OUTER JOIN groupmemberships ON (groupmemberships.player_id = players.player_id)', 'group_id = ' . GROUPID, 15, false, true);

 echo '</div>';

 require_once('inc/footer.php');

?>
