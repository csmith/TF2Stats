<?PHP

 require_once('inc/config.php');
 require_once('inc/grouptable.php');
 require_once(STATS_DIR . '/inc/database.php');

 define('TITLE', 'Groups');

 require_once('inc/header.php');

 echo '<h2>Groups</h2>';

 echo '<div class="left">', "\n";

 echo '</div>', "\n";
 echo '<div class="right">', "\n";

 echo '<h3>Top groups</h3>';
 showGroupsTable();
 
 echo '</div>', "\n";

 require_once('inc/footer.php');

?>
