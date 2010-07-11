<?PHP

 function getAwardDescription($type, $field) {
  switch($type) {
   case 'weaponclass':
    return 'Most kills with a ' . $field . ' weapon';
   case 'weapon':
    $sql = 'SELECT weapon_displayname FROM weapons WHERE weapon_id = ' .$field;
    $res = mysql_query($sql);
    return 'Most ' . mysql_result($res, 0) . ' kills';
   case 'event':
    $words = explode(' ', $field);
    $title = array_shift($words) . 's';
    $title .= ' ' . implode(' ', $words);
    return 'Most ' . trim($title);
   default:
    return 'Unknown';
  }
 }

?>
