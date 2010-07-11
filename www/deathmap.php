<?PHP

 require_once('inc/config.php');
 require_once('inc/deathmap.php');
 require_once(STATS_DIR . '/inc/database.php');

 if (!isset($_GET['map'])) {
  require_once('404.php');
  return; 
 }

 $sql  = 'SELECT map_id, map_name, MAX(UNIX_TIMESTAMP(game_endtime)) AS time FROM maps ';
 $sql .= 'NATURAL JOIN games WHERE map_name = \'' . s($_GET['map']) . '\' GROUP BY map_name';
 $res = mysql_query($sql);

 if (mysql_num_rows($res) == 0) {
  require_once('404.php');
  return;
 }

 $row = mysql_fetch_assoc($res);

 define('MAP_NAME', $row['map_name']);
 define('MAP_ID', $row['map_id']);
 define('MAP_FILE', sprintf(OVERVIEW_IMAGE, MAP_NAME));

 if (!file_exists(MAP_FILE) || !isset($coords[MAP_NAME])) {
  require_once('404.php');
  return;
 }

 if (isset($_GET['killer'])) {
  define('WHAT', 'killer');
 } else if (isset($_GET['sentries'])) {
  define('WHAT', 'sentry');
 } else {
  define('WHAT', 'victim');
 }

 if (isset($_GET['noteams'])) {
  define('NOTEAMS', true);
 }

 define('PALE', isset($_GET['pale']) ? (max(1, (int) $_GET['pale'])) : 3);

 define('ALPHA', isset($_GET['alpha']) ? min(max((int) $_GET['alpha'], 0), 127) : 0);

 define('RADIUS', isset($_GET['radius']) ? max(1, (int) $_GET['radius']) : 15);

 define('FILE_NAME', DM_CACHE . MAP_NAME . '.pale-' . PALE . '.radius-' . RADIUS . '.alpha-' . ALPHA . '.target-' . WHAT . (defined('NOTEAMS') ? '.noteams' : '') . '.jpg');

 if (file_exists(FILE_NAME) && filemtime(FILE_NAME) > (int) $row['time']) {
  // Cached version

  header('Content-type: image/jpeg');
  readfile(FILE_NAME);
  exit;
 }

 // Our map co-ords, for convenience
 list($x1,$y1,$i1,$j1,$x2,$y2,$i2,$j2,$x3,$y3,$i3,$j3) = $coords[MAP_NAME];

 /* Simultaneous equestions for translation co-ordinates:
  *
  * x1 = a * i1 + b * j1 + c
  * x2 = a * i2 + b * j2 + c
  * x3 = a * i3 + b * j3 + c
  *
  * y1 = d * i1 + e * j1 + f ...
  *
  * x1 - x2 = a * (i1 - i2) + b * (j1 - j2)
  * x2 - x3 = a * (i2 - i3) + b * (j2 - j3)
  *       a = (x1 - x2 - b * (j1 - j2)) / (i1 - i2)
  *         = (x2 - x3 - b * (j2 - j3)) / (i2 - i3)
  *  
  * (i2 - i3) (x1 - x2 - b * (j1 - j2)) = (i1 - i2) (x2 - x3 - b * (j2 - j3))
  * (i2 - i3) (x1 - x2) - b * (j1 - j2) (i2 - i3) = (i1 - i2) (x2 - x3) - b * (j2 - j3) (i1 - i2)
  * b (((j2 - j3) (i1 - i2)) - ((j1 - j2) (i2 - i3))) = (i1 - i2) (x2 - x3) - (i2 - i3) (x1 - x2) 
  * b = ((i1 - i2) (x2 - x3) - (i2 - i3) (x1 - x2)) / ((j2 - j3) (i1 - i2) - (j1 - j2) (i2 - i3))
  */
 $i12 = $i1 - $i2;
 $i23 = $i2 - $i3;
 $j12 = $j1 - $j2;
 $j23 = $j2 - $j3;

 $b = ($i12 * ($x2 - $x3) - $i23 * ($x1 - $x2)) / ($j23 * $i12 - $j12 * $i23);
 $a = (($x1 - $x2) - $b * $j12) / $i12;
 $c = $x1 - $a * $i1 - $b * $j1;

 if (abs($c - ($x3 - $a * $i3 - $b * $j3)) > 0.1) {
  die('Inconsistency between x co-ord translations for 1 and 3');
 } else if (abs($c - ($x2 - $a * $i2 - $b * $j2)) > 0.1) {
  die('Inconsistency between x co-ord tranlsations for 1 and 2');
 }

 $e = ($i12 * ($y2 - $y3) - $i23 * ($y1 - $y2)) / ($j23 * $i12 - $j12 * $i23);
 $d = (($y1 - $y2) - $e * $j12) / $i12;
 $f = $y1 - $d * $i1 - $e * $j1;

 if (abs($f - ($y3 - $d * $i3 - $e * $j3)) > 0.1) {
  die('Inconsistency between y co-ord translations for 1 and 3');
 } else if (abs($f - ($y2 - $d * $i2 - $e * $j2)) > 0.1) {
  die('Inconsistency between y co-ord translations for 1 and 2');
 }

 function getScreenX($gameX, $gameY) {
  global $a, $b, $c;
  $res = ($a * $gameX + $b * $gameY + $c); 
  return (int) $res;
 }

 function getScreenY($gameX, $gameY) {
  global $d, $e, $f;
  $res = ($d * $gameX + $e * $gameY + $f);
  return (int) $res;
 }

 function checkCoords($set, $i1, $j1, $x1, $y1) {
  global $a, $b, $c, $d, $e, $f;
  if (abs(getScreenX($i1, $j1) - $x1) > 1 || abs(getScreenY($i1, $j1) - $y1) > 1) {
   die("Co-ordinate mapping failed for set $set.<br>(i,j) = ($i1,$j1)<br>(x,y) = ($x1,$y1)<br>(ix,iy) = (" . getScreenX($i1, $j1) . "," . getScreenY($i1, $j1) . ")<br>a, b, c = $a, $b, $c<br>d, e, f = $d, $e, $f");
  }
 }

 checkCoords(1, $i1, $j1, $x1, $y1);
 checkCoords(2, $i2, $j2, $x2, $y2);
 checkCoords(3, $i3, $j3, $x3, $y3);

 $pixels = array();
 $max = 0;

 $im = imagecreatefrompng(MAP_FILE);
 imagefill($im, 1, 1, imagecolorallocate($im, 0, 0, 0));
 imagefill($im, imagesx($im) - 2, imagesy($im) - 2, imagecolorallocate($im, 0, 0, 0));

 if (WHAT == 'killer' || WHAT == 'victim') {
  $sql = 'SELECT kill_id, kill_killer_position, kill_victim_position, roleperiod_team FROM games NATURAL JOIN sessions NATURAL JOIN roleperiods INNER JOIN kills ON kill_killer = roleperiod_id WHERE map_id = ' . MAP_ID;
  $loc = 'kill_' . WHAT . '_position';
 } else {
  $sql = 'SELECT roleperiod_team, event_location FROM games NATURAL JOIN sessions NATURAL JOIN roleperiods NATURAL JOIN events WHERE map_id = ' . MAP_ID;
  $loc = 'event_location'; 
 }

 $res = mysql_query($sql);

 while ($row = mysql_fetch_assoc($res)) {
  list($x, $y, $z) = explode(' ', $row[$loc]);

  $i = getScreenX($x, $y); $j = getScreenY($x, $y);
  for ($n = max(0, $i - RADIUS); $n < min(imagesx($im), $i + RADIUS); $n++) {
   for ($m = max(0, $j - RADIUS); $m < min(imagesy($im), $j + RADIUS); $m++) {
    $distance = sqrt(pow($n - $i, 2) + pow($m - $j, 2));
    if ($distance < RADIUS) {
     $pixels[$n][$m][$row['roleperiod_team']] += RADIUS - $distance;
     $max = max($max, ((int) $pixels[$n][$m][1]) + ((int) $pixels[$n][$m][2]));
    }
   }
  }
 }

 $x = imagesx($im);
 for($i = 0; $i < imagesy($im); $i++) {
  for($j = 0; $j < imagesx($im); $j++) {
   $pos = imagecolorat($im, $j, $i);
   $f = imagecolorsforindex($im, $pos);
   $gst = $f['red']*0.3 + $f['green']*0.3 + $f['blue']*0.3;
   $col = imagecolorresolve($im, ((PALE-1)*$gst + $f['red']) / PALE, ((PALE-1)*$gst + $f['green']) / PALE, ((PALE-1)*$gst + $f['blue']) / PALE);
   imagesetpixel($im, $j, $i, $col);
  }
 } 
 
 foreach ($pixels as $x => $dat) {
  foreach ($dat as $y => $count) {
   $red = (int) $count[1];
   $blue = (int) $count[2];

   // Not sure why they'd exist in this case, but meh
   if ($red + $blue == 0) { continue; }

   if (defined('NOTEAMS')) {
    $ratio = ($red + $blue) / $max;

    $c = imagecolorallocatealpha($im,
        max(0, min(255, 255 * sin(pi() * ($ratio - 0.5)))), // Red
        max(0, 255 * sin($ratio * pi())), // Green
        max(0, 128 * cos($ratio * pi())), ALPHA - ALPHA * 0.5 * $ratio); // Blue

    $count = 1;
   } else {
    $c = imagecolorallocatealpha($im, 255 * $red/($red + $blue), 0, 255 * $blue/($red + $blue), ALPHA);
    $count = RADIUS * ($red + $blue)/$max;
   }

   imagefilledellipse($im, $x, $y, $count, $count, $c);
  }
 }

 $im2 = imagecreatetruecolor(imagesx($im), imagesy($im) + 43);
 $im3 = imagecreatefrompng('res/dmheader.png');
 imagecopy($im2, $im3, 0, 0, 0, 0, imagesx($im), 43);
 imagecopy($im2, $im, 0, 43, 0, 0, imagesx($im), imagesy($im)); 
 $c = imagecolorallocate($im2, 157, 83, 33);
 imagestring($im2, 1, imagesx($im) - 310, 30, str_pad('Map of ' . WHAT . ' locations on ' . MAP_NAME, 60, ' ', STR_PAD_LEFT), $c); 
 imageline($im2, 0, 42, imagesx($im), 42, $c);

 imageinterlace($im2, 1);

 header('Content-type: image/jpeg');
 imagejpeg($im2, null, 100);

 imagefilledrectangle($im2, 1, imagesy($im2) - 5, 4, imagesy($im2) - 2, imagecolorallocate($im2, 157, 83, 33));
 imagejpeg($im2, FILE_NAME, 100);
?>
