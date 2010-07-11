<?PHP

 require_once(dirname(__FILE__) . '/database.php');

 function updateRanks() {
  $sql = 'SELECT player_id, player_rank, player_score, SUM(roleperiod_kills) AS kills FROM players NATURAL JOIN roleperiods GROUP BY player_id ORDER BY player_score DESC';
  $res = mysql_query($sql);

  echo "Updating player ranks... ";

  $i = $updated = $limited = 0;
  $left = array();

  while ($row = mysql_fetch_assoc($res)) {
   if ($row['kills'] < MIN_KILLS) {
    $left[$row['player_id']] = $row['player_score'];
   } else {
    if ($row['player_rank'] != ++$i) {
     mysql_query('UPDATE players SET player_rank = ' . $i . ' WHERE player_id = ' . $row['player_id']);
     $updated++;
    }
   }
  }

  arsort($left);
  foreach ($left as $player => $score) {
   mysql_query('UPDATE players SET player_rank = ' . (++$i) . ' WHERE player_id = ' . $player);
   $updated++; $limited++;
  }

  echo "Done.\n\t$updated player records updated.\n\t$limited players rank-limited by their number of kills.\n";
 }

?>
