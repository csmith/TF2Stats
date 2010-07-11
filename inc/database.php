<?PHP

 require_once(dirname(dirname(__FILE__)) . '/config.php');

 mysql_connect(DB_HOST, DB_USER, DB_PASS);
 mysql_select_db(DB_DBNAME);

 mysql_query("SET NAMES 'utf8'");

 function s($sql) {
  return mysql_real_escape_string($sql);
 }

?>
