<?PHP

function showClassCompTable($classes) {

 echo '<table class="classcomp">';
 echo '<tr><th rowspan="2">Killer</th><th colspan="9">Victim</th></tr>';
 echo '<tr>';

 foreach ($classes as $killer => $data) {
  echo '<th>';
  echo '<a href="', URL_BASE, 'class.php?class=', $killer, '">';
  echo '<img src="', sprintf(URL_CLASS, 'blue', $killer), '" alt="', $killer, '">';
  echo '</a></th>';  
 }

 echo '</tr>';


 foreach ($classes as $killer => $data) {
  echo '<tr>';
  echo '<th>';
  echo '<a href="', URL_BASE, 'class.php?class=', $killer, '">';
  echo '<img src="', sprintf(URL_CLASS, 'red', $killer), '" alt="', $killer, '">';
  echo '</a></th>';

  foreach ($data as $victim => $count) {
   echo '<td>', $count, '</td>';
  }

  echo '</tr>';
 }
 echo '</table>';

}

?>
