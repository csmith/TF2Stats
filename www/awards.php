<?PHP

 require_once('inc/config.php');
 require_once('inc/awards.php');
 require_once(STATS_DIR . '/inc/database.php');

 if (!ENABLE_AWARDS) {
  require('404.php'); 
  exit;
 }

 define('TITLE', 'Awards');
 require_once('inc/header.php');


 echo '<h2>Awards</h2>';

 $sql = 'SELECT award_id, award_name, award_displayname, award_type, award_field FROM awards ORDER BY award_displayname';
 $res = mysql_query($sql);

 while ($row = mysql_fetch_assoc($res)) {
  echo '<h3 class="award">';
  echo '<img src="', sprintf(URL_AWARD, $row['award_name']), '" alt="', $row['award_displayname'], '">';
  echo $row['award_displayname'];
  echo '<span class="info"> &mdash; ' . getAwardDescription($row['award_type'], $row['award_field']) . '</span>';
  echo '</h3>';
  echo '<ul class="award">';

  $sql2 = '
	SELECT 	awardwinners.player_id,
		session_alias,
		winner_value,
		UNIX_TIMESTAMP(winner_awarded) AS ts
	FROM 	awardwinners 
		NATURAL JOIN players
		LEFT OUTER JOIN sessions ON players.player_id = sessions.player_id
	WHERE 	award_id = ' . $row['award_id'] . '
	GROUP BY winner_awarded
	ORDER BY winner_awarded DESC
 	LIMIT 0,' . AWARD_NUMBER;
  $res2 = mysql_query($sql2) or print(mysql_error());

  while ($row2 = mysql_fetch_assoc($res2)) {
   echo '<li>';
   echo date('F jS', $row2['ts']), ': ';
   echo '<a href="player.php?id=' . $row2['player_id'] . '">' . htmlentities($row2['session_alias'], ENT_COMPAT, 'UTF-8') . '</a>';
   echo ' with ' . htmlentities($row2['winner_value']) . '</li>';
  }

  echo '</ul>';
 }

 require_once('inc/footer.php');

?>
