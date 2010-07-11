<?PHP

function showPlayerTable($joins = '', $where = '1=1', $limit = 20, $hideclass = false, $userank = false, $highlight = 0) {

 $more = false;

 echo '<table>', "\n";
 echo '<tr>', "\n";
 echo '<th rowspan="2">Rank</th><th rowspan="2">Alias</th><th rowspan="2">Kills</th><th rowspan="2">Deaths</th><th rowspan="2"><abbr title="Kills per Death">KPD</abbr></th><th rowspan="2">Score</th>';
 echo '<th colspan="', $hideclass ? '1' : '2', '">Mostly seen &hellip;</th></tr>', "\n";
 echo '<tr>', $hideclass ? '' : '<th>Playing as</th>', '<th>Killing with</th></tr>', "\n";

 $sql = "SELECT players.player_id, player_rank, player_score, session_alias, SUM(roleperiod_kills) AS kills, SUM(roleperiod_deaths) AS deaths, SUM(roleperiod_kills)/SUM(roleperiod_deaths) AS kpd FROM players NATURAL JOIN sessions INNER JOIN roleperiods ON roleperiods.session_id = sessions.session_id $joins WHERE player_rank > 0 AND $where GROUP BY player_id ORDER BY " . (($where == '1=1' || $userank) ? 'player_rank' : 'kpd DESC') . " LIMIT $limit";
 $res = mysql_query($sql) or print(mysql_error());
 $i = 0;

 if (mysql_num_rows($res) == 0) {
  echo '<tr><td colspan="8" class="none">No results found</td></tr>';
 }

 while ($row = mysql_fetch_assoc($res)) {
  if (!$hideclass) {
   $sql = "SELECT class_name, class_displayname FROM roleperiods NATURAL JOIN classes NATURAL JOIN sessions WHERE roleperiod_endtime > '0000-00-00' AND player_id = " . $row['player_id'] . " GROUP BY class_id ORDER BY SUM(roleperiod_endtime - roleperiod_starttime) DESC LIMIT 0,1";
   $re2 = mysql_query($sql) or print(mysql_error());
  }

  $sql = "SELECT weapon_name, COUNT(*) AS times FROM roleperiods INNER JOIN kills ON kills.kill_killer = roleperiod_id NATURAL JOIN weapons NATURAL JOIN sessions WHERE player_id = " .$row['player_id'] . " GROUP BY weapon_displayname ORDER BY times DESC LIMIT 0,1";
  $re3 = mysql_query($sql) or print($sql . '<br>' . mysql_error());

  $i++;

  $hclass = $row['player_id'] == $highlight ? 'highlight' : '';

  if ($i % 2 == 0) {
   $class = ' class="even ' . $hclass . '"';
  } else if ($hclass != '') {
   $class = ' class="' . $hclass . '"';
  } else {
   $class = '';
  }

  echo '<tr', $class, '><td>', ($where == '1=1' || $userank) ? $row['player_rank'] : $i, '</td>';
  echo '<td>';

  if ($highlight != $row['player_id']) {
    echo '<a href="player.php?id=', $row['player_id'], '">';
  }

  echo htmlentities($row['session_alias'], ENT_COMPAT, 'UTF-8');

  if ($highlight != $row['player_id']) {
   echo '</a>';
  }

  echo '</td><td class="num">', number_format($row['kills']), '</td><td class="num">', number_format($row['deaths']), '</td>';

  echo '<td class="num">', number_format($row['kpd'], 2), '</td>';
  echo '<td class="num">', number_format($row['player_score'], 0), '</td>';

  if (!$hideclass) {
   echo '<td class="class">';
   echo '<a href="', URL_BASE, 'class.php?class=', mysql_result($re2, 0), '">';
   echo '<img src="', sprintf(URL_CLASS, 'blue', mysql_result($re2, 0)), '"></a></td>';
  }
  echo '<td class="weapon">';

  if (mysql_num_rows($re3) > 0) {
   echo '<img src="/stats/res/weapons/', mysql_result($re3, 0), '.png">';
  } else {
   echo 'Nothing';
  }

  echo '</td>';
  echo '</tr>', "\n";
 }

 echo '</table>', "\n";


}
