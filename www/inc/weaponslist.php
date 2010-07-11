<?PHP

function showWeaponsList($extra = '', $limit = 'LIMIT 0,5') {
 echo '<ol class="weapons">', "\n";

 $sql = "SELECT weapon_name, weapon_displayname, COUNT(*) as kills FROM weapons NATURAL JOIN kills $extra GROUP BY weapon_displayname ORDER BY kills DESC $limit";
 $res = mysql_query($sql) or print(mysql_error());
 while ($row = mysql_fetch_assoc($res)) {
  echo '<li><img src="/stats/res/weapons/', $row['weapon_name'], '.png" alt="', "\n";
  echo $row['weapon_displayname'], '" title="', $row['weapon_displayname'], '"> ', "\n";
  echo number_format($row['kills']), ' kill', $row['kills'] == 1 ? '' : 's', '</li>', "\n";
 }

 echo '</ol>', "\n";
}

?>
