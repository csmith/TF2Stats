<?PHP

// Displays extra information about a map in the left-hand column
// of the map page. You may want to include information on class
// limits, the times the map is played, or links to downloads or
// reviews.
function show_extra_map_info($name) {

 $cfgdir = '/opt/sourceds/orangebox/tf/cfg/beetlesmod';

 if (file_exists($cfgdir.'/'.$name.'.cfg')) {
  $cfg = array();

  foreach (file($cfgdir . '/' . $name . '.cfg') as $line) {
   if (!empty($line) && strstr($line,'//') != $line) {
    $bits = explode(' ', $line, 2);
    $cfg[strtolower($bits[0])][] = $bits[1];
   }
  }

  if (isset($cfg['setclasslimit'])) {
   echo '<h3>Class Limits</h3>';
   echo '<ul class="classlimits">';

   $all = count($cfg['setclasslimit']) == 10;
   foreach ($cfg['setclasslimit'] as $limit) {
     preg_match('/\"(.*)\" \"(.*)\" \"(.*)\"/', $limit, $details);

     if (strtolower($details[1]) != "random") {
      if ($all && $details[2] == "0") { continue; }

      if ($details[2] == "-1") {
       $details[2] = 'No Restriction';
      } else if ($details[2] == "0") {
       $details[2] = 'Not Permitted';
      } else {
       $details[2] = 'Maximum of '.$details[2].' per team';
      }

      echo '<li><img src="' . sprintf(URL_CLASS, 'blue', $details[1]) . '"> ' . $details[2];
       
      if ($details[3] != "0.0") {
       $num = ($maxplayers/2) * ((float)$details[3]);
       echo ' ('.$num.')';
      }
     }
    }

    if ($all) { echo '<li> No other class permitted'; }

    echo '</ul>';
   }
  }
}

?>
