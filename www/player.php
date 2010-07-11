<?PHP

 require_once('inc/config.php');
 require_once('inc/common.php');
 require_once('inc/ext.php');
 require_once('inc/classestable.php');
 require_once('inc/eventhistory.php');
 require_once('inc/mostmaps.php');
 require_once('inc/playertable.php');
 require_once('inc/weaponslist.php');
 require_once(STATS_DIR . '/inc/database.php');

 $sql = 'SELECT player_steamid, player_rank, player_score, session_alias FROM players NATURAL JOIN sessions WHERE player_id = \'' . s($_GET['id']) . '\' ORDER BY session_endtime DESC LIMIT 0,1';
 $res = mysql_query($sql);

 if (mysql_num_rows($res) == 0) {
  require('404.php'); 
  exit;
 }

 $row = mysql_fetch_assoc($res);

 define('PLAYER', (int) $_GET['id']); 
 define('RANK', (int) $row['player_rank']);
 define('STEAMID', $row['player_steamid']);
 define('NAME', $row['session_alias']); 
 define('SCORE', (int) $row['player_score']);

 if (isset($_GET['class'])) {
  $sql = 'SELECT class_id, class_displayname FROM classes WHERE class_name = \'' . s($_GET['class']) . '\'';
  $res = mysql_query($sql);

  if (mysql_num_rows($res) == 0) {
   require('404.php');
   exit;
  }

  $row = mysql_fetch_assoc($res);

  define('CLASSID', $row['class_id']);
  define('CLASSNAME', $row['class_displayname']);
 }

 define('TITLE', 'Player information :: ' . htmlentities(NAME, ENT_COMPAT, 'UTF-8')
	 . (defined('CLASSID') ? ' :: ' . CLASSNAME : ''));
 require_once('inc/header.php');

 echo '<h2>Player Information: ', htmlentities(NAME, ENT_COMPAT, 'UTF-8'), 
	defined('CLASSID') ? ' &raquo; <img src="' . sprintf(URL_CLASS, 'blue', $_GET['class']) . '">' . CLASSNAME : '', '</h2>';


 if (!isset($_GET['eventhistory'])) {

  echo '<div class="left">';

  echo '<h3>Player links</h3>';

  if (defined('CLASSID')) {
   echo '<a class="blocklink" href="?id=', htmlentities(PLAYER), '">Player overview</a>';
  }
  
  echo '<a class="blocklink" href="?id=', htmlentities(PLAYER), '&amp;eventhistory">Event History</a>';

  if (ENABLE_COMMUNITY_LINKS) {
   require_once('inc/community.php');

   echo '<a class="blocklink" href="http://steamcommunity.com/profiles/', getCommunityID(STEAMID), '">Steam Community Profile</a>';
  }

  if (ENABLE_GROUPS) {
   $sql = 'SELECT group_id, group_name FROM groupmemberships NATURAL JOIN groups WHERE player_id = ' . PLAYER;
   $res = mysql_query($sql);

   if (mysql_num_rows($res) > 0) {
    $header = false;

    while ($row = mysql_fetch_assoc($res)) {

     $sql2 = 'SELECT COUNT(*) FROM groupmemberships WHERE group_id = ' . $row['group_id'];
     $res2 = mysql_query($sql2);
     $row2 = mysql_fetch_array($res2);
     $total = $row2[0];

     if ($total == 1) { continue; }

     if (!$header) {
      echo '<h3>Group affiliations</h3>';
      echo '<ul>';
      $header = true;
     }

     $sql2 = 'SELECT COUNT(*) FROM groupmemberships NATURAL JOIN players WHERE group_id = ' . $row['group_id'] . ' AND player_score > ' . SCORE;
     $res2 = mysql_query($sql2);
     $row2 = mysql_fetch_array($res2);
     $place = $row2[0];

     echo '<li><a href="', 'group.php?group=', $row['group_id'], '">', htmlentities($row['group_name'], ENT_QUOTES, 'UTF-8'), '</a> &mdash; ';
     echo 'ranked ', number_format($place), '<sup>', getSuffix($place), '</sup> out of ', number_format($total), '</li>';
    }

    if ($header) {
     echo '</ul>';
    }
   }
  }

  echo '<h3>Favourite maps</h3>';

  showMostMaps(defined('CLASSID') ? 'INNER JOIN roleperiods ON (roleperiods.session_id = sessions.session_id)' : '', 'sessions.player_id = ' . PLAYER . (defined('CLASSID') ? ' AND roleperiods.class_id = ' . CLASSID : ''));

  $extra = true;

  echo '<h3 class="extra">Top Weapons</h3>';
  showWeaponsList('INNER JOIN roleperiods ON roleperiod_id = kill_killer WHERE player_id = ' . PLAYER . (defined('CLASSID') ? ' AND class_id = ' . CLASSID : ''));

  echo '</div><div class="right">';

  echo '<h3>Ranking</h3>';

  $min = max(0, RANK - 3); $max = 5 + min(RANK - 3, 0); 
  showPlayerTable('', '1=1', $min . ',' . $max, false, false, PLAYER);

  echo '<h3>Class Stats</h3>';

  $classes = array();

  $sql = 'SELECT rp1.class_id, class_name, COUNT(*) as num FROM classes NATURAL JOIN roleperiods AS rp1 INNER JOIN kills AS k1 ON kill_victim = roleperiod_id INNER JOIN roleperiods AS rp2 ON kill_killer = rp2.roleperiod_id WHERE rp2.player_id = ' . PLAYER . (defined('CLASSID') ? ' AND rp2.class_id = ' . CLASSID : '') . ' GROUP BY class_id';
  $res = mysql_query($sql) or print(mysql_error());

 while ($row = mysql_fetch_assoc($res)) {
  $classes[$row['class_id']]['name'] = $row['class_name'];
  $classes[$row['class_id']]['data1'] = $row['num'];
 }

 $sql = 'SELECT rp1.class_id, class_name, COUNT(*) as num FROM classes NATURAL JOIN roleperiods AS rp1 INNER JOIN kills AS k1 ON kill_killer = rp1.roleperiod_id INNER JOIN roleperiods AS rp2 ON kill_victim = rp2.roleperiod_id WHERE rp2.player_id = ' . PLAYER . (defined('CLASSID') ? ' AND rp2.class_id = ' . CLASSID : '') . ' GROUP BY class_id';
 $res = mysql_query($sql) or print(mysql_error());

 while ($row = mysql_fetch_assoc($res)) {
  $classes[$row['class_id']]['data2'] = $row['num'];
  $classes[$row['class_id']]['name'] = $row['class_name'];
 }

 if (!defined('CLASSID')) {
  $sql = 'SELECT rp1.class_id, class_name, COUNT(*) AS num FROM classes NATURAL JOIN roleperiods AS rp1 INNER JOIN kills AS k1 ON kill_killer = rp1.roleperiod_id WHERE rp1.player_id = ' . PLAYER . ' GROUP BY class_id';
  $res = mysql_query($sql) or print(mysql_error());

  while ($row = mysql_fetch_assoc($res)) {
   $classes[$row['class_id']]['data3'] = $row['num'];
   $classes[$row['class_id']]['name'] = $row['class_name'];
  }

  $sql = 'SELECT rp1.class_id, class_name, COUNT(*) AS num FROM classes NATURAL JOIN roleperiods AS rp1 INNER JOIN kills AS k1 ON kill_victim = rp1.roleperiod_id WHERE rp1.player_id = ' . PLAYER . ' GROUP BY class_id';
  $res = mysql_query($sql) or print(mysql_error());

  while ($row = mysql_fetch_assoc($res)) {
   $classes[$row['class_id']]['data4'] = $row['num'];
   $classes[$row['class_id']]['name'] = $row['class_name'];
  }
 
  showClassesTable($classes, 'Victim of ' . htmlentities(NAME, ENT_COMPAT, 'UTF-8'), 
			    'Killer of ' . htmlentities(NAME, ENT_COMPAT, 'UTF-8'), true,
                            htmlentities(NAME, ENT_COMPAT, 'UTF-8') . '\'s kills',
                            htmlentities(NAME, ENT_COMPAT, 'UTF-8') . '\'s deaths', true);
 } else {
  showClassesTable($classes, 'Victim of ' . htmlentities(NAME, ENT_COMPAT, 'UTF-8'),
                            'Killer of ' . htmlentities(NAME, ENT_COMPAT, 'UTF-8'), true);
 }

 echo '</div>';

 } else {
  echo '<h3>Event History</h3>';
  showEventHistory(PLAYER);
 }

 require_once('inc/footer.php');

?>
