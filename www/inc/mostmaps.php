<?PHP

 require_once(dirname(__FILE__) . '/config.php');

 if (ENABLE_DEATHMAPS) {
  require_once(dirname(__FILE__) . '/deathmap.php');
 }

 function showMostMaps($joins = '', $where = '1=1') {
  $sql = "SELECT map_name, SUM(session_endtime - session_starttime) AS time FROM maps NATURAL JOIN games NATURAL JOIN sessions $joins WHERE session_endtime > '0000-00-00' AND $where GROUP BY map_name ORDER BY time desc LIMIT 0,3";
  showMostMapsSQL($sql);
 }

 function showMostMapsSQL($sql, $showbig = true) {
  $class = $showbig ? 'large' : 'medium';
  $res = mysql_query($sql);

  while ($row = mysql_fetch_assoc($res)) {
   echo '<div class="map ', $class, '">';

   if ($row !== false) {
    echo '<a href="map.php?map=', $row['map_name'], '"><img src="';
    echo sprintf(URL_MAP, ($class == 'large' ? 'large' : 'small'), $row['map_name']);
    echo '" class="map ', $class, '"></a>', "\n";

    if (ENABLE_DEATHMAPS && function_exists('hasDeathmap') && hasDeathmap($row['map_name'])) {
     echo '<img src="res/dead.png" alt="Death" title="This map has a death map" class="deathmap">';
    }

    echo $row['map_name'];
   }
   echo '</div>', "\n";
   $class = 'medium';
  }
 }

?>
