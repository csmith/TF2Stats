#!/usr/bin/php -q
<?PHP

 define('SCRIPT_HEAD1', 'awards.php v0.1');
 define('SCRIPT_HEAD3', 'Assigns awards to players');
 require(dirname(__FILE__) . '/inc/cliheader.php');

 require_once(dirname(__FILE__) . '/config.php');
 require_once(dirname(__FILE__) . '/inc/database.php');

 if (!ENABLE_AWARDS) {
  die("Error: Awards are not enabled\n");
 }

 function giveAward($award, $player, $value, $time) {
  if (empty($player)) {
   echo "\tNo winner :(";
   return;
  }

  echo "\tPlayer $player won!";

  $sql  = 'INSERT INTO awardwinners (award_id, player_id, winner_awarded, winner_value)';
  $sql .= ' VALUES (' . $award . ', ' . $player . ', FROM_UNIXTIME(';
  $sql .= $time . '), \'' . s($value) . '\')';
  mysql_query($sql) or die(mysql_error() . "\n\n" . $sql . "\n");
 }

 function doEventAward($id, $field, $from, $to) {
  $sql = '
	SELECT	player_id, COUNT(*) AS res
	FROM 	events
		NATURAL JOIN roleperiods
	WHERE	event_timestamp > FROM_UNIXTIME(' . $from . ')
		AND event_timestamp < FROM_UNIXTIME(' . $to . ')
		AND event_type = \'' . s($field) . '\'
	GROUP BY player_id
	ORDER BY res DESC
	LIMIT 0,1';
  $res = mysql_query($sql) or die(mysql_error() . "\n\n" . $sql . "\n");
  $row = mysql_fetch_assoc($res);
  giveAward($id, $row['player_id'], $row['res'] . ' events', $to);
 }

 function doWeaponAward($id, $field, $from, $to) {
  $sql = '
	SELECT	player_id, COUNT(*) AS res
	FROM	kills
		INNER JOIN roleperiods ON kill_killer = roleperiod_id
	WHERE	kill_timestamp > FROM_UNIXTIME(' . $from . ')
		AND kill_timestamp < FROM_UNIXTIME(' . $to . ')
		AND weapon_id = ' . $field . '
	GROUP BY player_id
	ORDER BY res DESC
	LIMIT 0,1';
  $res = mysql_query($sql) or die(mysql_error() . "\n\n" . $sql . "\n");
  $row = mysql_fetch_assoc($res);
  giveAward($id, $row['player_id'], $row['res'] . ' kills', $to);
 }

 function doWeaponclassAward($id, $field, $from, $to) {
  $sql = '
        SELECT  player_id, COUNT(*) AS res
        FROM    kills
                INNER JOIN roleperiods ON kill_killer = roleperiod_id
		NATURAL JOIN weapons
        WHERE   kill_timestamp > FROM_UNIXTIME(' . $from . ')
                AND kill_timestamp < FROM_UNIXTIME(' . $to . ')
                AND weapon_class = \'' . s($field) . '\'
        GROUP BY player_id
        ORDER BY res DESC
        LIMIT 0,1';
  $res = mysql_query($sql) or die(mysql_error() . "\n\n" . $sql . "\n");
  $row = mysql_fetch_assoc($res);
  giveAward($id, $row['player_id'], $row['res'] . ' kills', $to);
 }

 $sql  = 'SELECT MIN(UNIX_TIMESTAMP(game_starttime)) AS start FROM games';
 $res  = mysql_query($sql);
 $first = mysql_result($res, 0);

 $sql  = 'SELECT awards.award_id, award_type, award_field, ';
 $sql .= 'MAX(UNIX_TIMESTAMP(winner_awarded)) AS lasttime FROM awards ';
 $sql .= 'LEFT OUTER JOIN awardwinners ON awardwinners.award_id ';
 $sql .= '= awards.award_id GROUP BY awards.award_id';
 $res  = mysql_query($sql) or die(mysql_error() . "\n\n" . $sql . "\n");

 while ($row = mysql_fetch_assoc($res)) {
  if ($row['lasttime'] > 60 + strtotime('-' . AWARD_FREQUENCY . ' days')) {
   // Already awarded.
   continue;
  }

  $from = $row['lasttime'] == null ? $first : $row['lasttime'];
  $to = $from + AWARD_FREQUENCY * 60 * 60 * 24;

  do {
   echo "\nAward ", $row['award_id'], " from $from to $to:";

   call_user_func('do' . ucfirst($row['award_type']) . 'Award', $row['award_id'], $row['award_field'], $from, $to);

   $from += AWARD_FREQUENCY * 60 * 60 * 24;
   $to   += AWARD_FREQUENCY * 60 * 60 * 24;
  } while ($to < time());
 }

 echo "\n";

?>
