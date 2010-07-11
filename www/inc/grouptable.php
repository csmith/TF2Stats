<?PHP

function showGroupsTable() {
 $sql = 'SELECT group_id, group_name, group_members, group_score / group_members AS group_avgscore FROM groups WHERE group_members > 1 ORDER BY group_avgscore DESC LIMIT 0,20';
 $re1 = mysql_query($sql);

 echo '<table>';
 echo '<tr>';
 echo '<th>Rank</th>';
 echo '<th>Group</th>';
 echo '<th>Top player</th>';
 echo '<th>Members</th>';
 echo '<th>Avg Score</th>';
 echo '</tr>';

 if (mysql_num_rows($re1) == 0) {
  echo '<tr><td colspan="5" class="none">No results found</td></tr>';
 }

 $rank = 1;
 while ($row = mysql_fetch_assoc($re1)) {
  $sql = "SELECT player_id, player_score, session_alias FROM groupmemberships NATURAL JOIN players NATURAL JOIN sessions WHERE group_id = " . $row['group_id'] . " ORDER BY player_score DESC LIMIT 0,1";
  $re2 = mysql_query($sql) or print(mysql_error());
  $ro2 = mysql_fetch_assoc($re2);

  echo '<tr', $rank % 2 == 0 ? ' class="even"' : '', '><td>', number_format($rank++), '</td><td>';
  echo '<a href="group.php?group=', $row['group_id'], '">';
  echo htmlentities($row['group_name'], ENT_QUOTES, 'UTF-8'), '</a>';
  echo '</td>';
  echo '<td><a href="player.php?id=', $ro2['player_id'], '">', htmlentities($ro2['session_alias'], ENT_QUOTES, 'UTF-8');
  echo '</a></td>';
  echo '<td class="num">', number_format($row['group_members']), '</td>';
  echo '<td class="num">', number_format($row['group_avgscore']), '</td>';

  echo '</tr>';
 }

 echo '</table>';
}

?>
