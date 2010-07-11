<?PHP

require_once(dirname(__FILE__) . '/config.php');
require_once(STATS_DIR . '/inc/database.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title>TF2 Stats :: <?PHP echo TITLE; ?></title>
  <link rel="stylesheet" href="res/style.css" type="text/css">
 </head>
 <body>
  <h1 id="header">TF2 Stats</h1>
  <div id="menu">
   <ul>
    <li id="first"><a href="<?PHP echo URL_BASE; ?>">Overview</a></li>
    <li><a href="<?PHP echo URL_BASE; ?>players.php">Players</a></li>
    <li><a href="<?PHP echo URL_BASE; ?>maps.php">Maps</a></li>
    <li><a href="<?PHP echo URL_BASE; ?>weapons.php">Weapons</a></li>
<?PHP if (ENABLE_AWARDS) { ?>    <li><a href="<?PHP echo URL_BASE; ?>awards.php">Awards</a></li> <?PHP } ?>
   </ul>
  </div>
  <div id="content">
<?PHP

 $sql = 'SELECT COUNT(*) FROM config WHERE config_key = \'updating\' AND config_value = \'true\'';
 $res = mysql_query($sql);
 $num = mysql_result($res, 0);

 if ($num > 0) {
  echo '<div id="updating">TF2 Stats is currently being updated. Please check back later for the latest stats.</div>';
 }

?>
