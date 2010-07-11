<?PHP

function getClassTableData($num) {
 if ($num < 1000) {
  return $num;
 } else if ($num < 10000) {
  return round($num/1000,1) . 'k';
 } else if ($num < 100000) {
  return round($num/1000,0) . 'k';
 } else {
  return round($num/1000000,1) . 'm';
 }
}

function showClassesTable($classes, $label1, $label2, $relative = false, $label3 = null, $label4 = null, $relative2 = false) {

 $max1 = $max2 = $max3 = $max4 = 0;
 foreach ($classes as $data) {
  $max1 = max($max1, $data['data1']);
  $max2 = max($max2, $data['data2']);

  if ($label3 != null && $label4 != null) {
   $max3 = max($max3, $data['data3']);
   $max4 = max($max4, $data['data4']);
  }
 }

 if ($relative) {
  $max1 = $max2 = max($max1, $max2);
 }

 if ($relative2) {
  $max3 = $max4 = max($max3, $max4);
 }

 echo '<table class="graph">';
 echo '<tr class="key even"><td colspan="', (count($classes) * 2), '">';
 echo '<div class="graphkey">';
 echo ' <div class="graphkey1">&nbsp;</div><p>', $label1, '</p>';
 echo ' <div class="graphkey2">&nbsp;</div><p>', $label2, '</p>';
 echo '</div>';
 echo '</td></tr>';

 echo '<tr class="data even">';
 foreach ($classes as $data) {
  if ($data['data1'] > 0) {
   echo '<td><div class="graphbar1" style="height: ', 150 * $data['data1'] / $max1, 'px;" title="', $data['name'], ': ', $label1, ': ', $data['data1'], '">&nbsp;</div></td>';
  } else {
   echo '<td></td>';
  }

  if ($data['data2'] > 0) {
   echo '<td><div class="graphbar2" style="height: ', 150 * $data['data2'] / $max2, 'px;" title="', $data['name'], ': ', $label2, ': ', $data['data2'], '">&nbsp;</div></td>';
  } else {
   echo '<td></td>';
  }
 }
 echo '</tr>';

 echo '<tr class="figures">';
 foreach ($classes as $data) {
  echo '<td>', getClassTableData((int) $data['data1']), '</td>';
  echo '<td>', getClassTableData((int) $data['data2']), '</td>';
 }
 echo '</tr>';

 echo '<tr>';
 foreach ($classes as $data) {
  echo '<th colspan="2">';
  echo '<a href="', URL_BASE, 'class.php?class=', $data['name'], '">';
  echo '<img src="', sprintf(URL_CLASS, 'blue', $data['name']), '" alt="', $data['name'], '">';
  echo '</a></th>';
 }
 echo '</tr>';

 if ($label3 != null && $label4 != null) {
  echo '<tr class="figures">';
  foreach ($classes as $data) {
   echo '<td>', getClassTableData((int) $data['data3']), '</td>';
   echo '<td>', getClassTableData((int) $data['data4']), '</td>';
  }
  echo '</tr>';

  echo '<tr class="data even bottom">';
  foreach ($classes as $data) {
   if ($data['data3'] > 0) {
    echo '<td><div class="graphbar1" style="height: ', 150 * $data['data3'] / $max3, 'px;" title="', $data['name'], ': ', $label3, ': ', $data['data3'], '">&nbsp;</div></td>';
   } else {
    echo '<td></td>';
   }

   if ($data['data4'] > 0) {
    echo '<td><div class="graphbar2" style="height: ', 150 * $data['data4'] / $max4, 'px;" title="', $data['name'], ': ', $label4, ': ', $data['data4'], '">&nbsp;</div></td>';
   } else {
    echo '<td></td>';
   }
  }
  echo '</tr>';

  echo '<tr class="key even bottom"><td colspan="', (count($classes) * 2), '">';
  echo '<div class="graphkey">';
  echo ' <div class="graphkey1">&nbsp;</div><p>', $label3, '</p>';
  echo ' <div class="graphkey2">&nbsp;</div><p>', $label4, '</p>';
  echo '</div>';
  echo '</td></tr>';
 }

 echo '</table>';

}

?>
