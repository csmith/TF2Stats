<?PHP

 require_once('inc/config.php');
 require_once('inc/awards.php');
 require_once('inc/playertable.php');
 require_once(STATS_DIR . '/inc/database.php');

 define('TITLE', 'Players');

 require_once('inc/header.php');

 echo '<h2>Players</h2>';

 echo '<div class="left">', "\n";

 /** -- Search box -- **/
 echo '<h3>Find a player</h3>', "\n";

 echo '<p>Enter part of a player\'s name or Steam ID:</p>';

 echo '<form action="', URL_BASE, 'players.php" method="get">';
 echo '<table class="form">';
 echo '<tr><th><label for="alias">Alias:</label></th><td>';
 echo '<input type="text" name="alias"', (isset($_REQUEST['alias']) ? ' value="' . htmlentities($_REQUEST['alias'], ENT_COMPAT, 'UTF-8') . '"' : ''), '></td></tr>';
 echo '<tr><th><label for="steamid">Steam ID:</label></th><td>';
 echo '<input type="text" name="steamid"', (isset($_REQUEST['steamid']) ? ' value="' . htmlentities($_REQUEST['steamid']) . '"' : ''), '></td></tr>';
 echo '<tr><th></th><td><input type="submit" value="Search"></td></tr>';
 echo '</table>';
 echo '</form>';

 if (ENABLE_AWARDS) {
  echo '<h3>Award winners</h3>';

  $sql = 'SELECT award_id, award_name, award_displayname FROM awards ORDER BY award_displayname';
  $res = mysql_query($sql);

  echo '<ul class="awards">';
  while ($row = mysql_fetch_assoc($res)) {
   echo '<li>';
   echo '<img src="', sprintf(URL_AWARD, $row['award_name']), '" alt="', $row['award_displayname'], '">';
   echo '<strong>', $row['award_displayname'], '</strong>';

   $sql2 = '
        SELECT  awardwinners.player_id,
                session_alias
        FROM    awardwinners
                NATURAL JOIN players
                LEFT OUTER JOIN sessions ON players.player_id = sessions.player_id
        WHERE   award_id = ' . $row['award_id'] . '
        ORDER BY winner_awarded DESC
        LIMIT 0,1';

   $res2 = mysql_query($sql2) or print(mysql_error());
   $row2 = mysql_fetch_assoc($res2);
   echo '<a href="player.php?id=' . $row2['player_id'] . '">' . htmlentities($row2['session_alias'], ENT_COMPAT, 'UTF-8') . '</a>';
   echo '</li>';
  }

  echo '</ul>';
 }

 echo '</div>', "\n";
 echo '<div class="right">', "\n";

 /** -- Output top players -- **/

 if ((isset($_REQUEST['alias']) && !empty($_REQUEST['alias'])) || (isset($_REQUEST['steamid']) && !empty($_REQUEST['steamid']))) {
  echo '<h3>Search results</h3>';
  showPlayerTable('', 'session_alias LIKE \'%' . s($_REQUEST['alias']) . '%\' AND player_steamid LIKE \'%' . s($_REQUEST['steamid']) . '%\'', 20, false, true);
 } else {
  echo '<h3>Top players</h3>';
  showPlayerTable('', '1=1', ((isset($_GET['start']) && ctype_digit($_GET['start'])) ? $_GET['start'] : 0) . ',20');
 }
 
 echo '</div>', "\n";

 require_once('inc/footer.php');

?>
