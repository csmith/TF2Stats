<?PHP

 function eventSort($a, $b) {
  return strtotime($b['time']) - strtotime($a['time']);
 }

 function showEventHistory($playerid) {

  $events = array();

  // Kills
  $sql = '
	SELECT
		weapon_name,
		kill_timestamp,

		rp1.player_id AS killerID,
		rp1.roleperiod_team AS killerTeam,
		c1.class_name AS killerClass,
		s1.session_alias AS killerName,

		rp2.player_id AS victimID,
		rp2.roleperiod_team AS victimTeam,
		c2.class_name AS victimClass,
		s2.session_alias AS victimName,

		rp3.player_id AS assistID,
		rp3.roleperiod_team AS assistTeam,
		c3.class_name AS assistClass,
		s3.session_alias AS assistName
	FROM
		kills
		NATURAL JOIN weapons

		INNER JOIN roleperiods AS rp1 ON kill_killer = rp1.roleperiod_id
		INNER JOIN sessions AS s1 ON rp1.session_id = s1.session_id
		INNER JOIN classes AS c1 ON rp1.class_id = c1.class_id

		INNER JOIN roleperiods AS rp2 ON kill_victim = rp2.roleperiod_id
		INNER JOIN sessions AS s2 ON rp2.session_id = s2.session_id
		INNER JOIN classes AS c2 ON rp2.class_id = c2.class_id

		LEFT OUTER JOIN roleperiods AS rp3 ON kill_assist = rp3.roleperiod_id
		LEFT OUTER JOIN sessions AS s3 ON rp3.session_id = s3.session_id
		LEFT OUTER JOIN classes AS c3 ON rp3.class_id = c3.class_id
	WHERE
		(
			rp1.player_id = ' . $playerid .'
			OR rp2.player_id = ' . $playerid . '
			OR rp3.player_id = ' . $playerid . '
		)
	ORDER BY kill_timestamp DESC
	LIMIT 0,100';

  $res = mysql_query($sql) or print(mysql_error());
  while ($row = mysql_fetch_assoc($res)) {
   $row['time'] = $mintime = $row['kill_timestamp'];
   $events[] = $row;
  }

  // Sessions
  $sql = '
	SELECT
		session_starttime,
		session_endtime,
		session_alias,
		map_name
	FROM
		sessions
		NATURAL JOIN games
		NATURAL JOIN maps
	WHERE
		(
			session_starttime > \'' . $mintime . '\'
			OR session_endtime > \'' . $mintime . '\'
		)

		AND player_id = ' . $playerid . '
	ORDER BY session_starttime DESC
	LIMIT 0,100';

  $res = mysql_query($sql) or print(mysql_error());
  while ($row = mysql_fetch_assoc($res)) {
   if (strtotime($row['session_starttime']) > strtotime($mintime)) {
    $row['time'] = $row['session_starttime'];
    $events[] = $row;
   } 

   if (strtotime($row['session_endtime']) > strtotime($mintime)) {
    $row['time'] = $row['session_endtime'];
    $events[] = $row;
   } 
  }

  // Roles
  $sql = 'SELECT class_name, class_displayname, session_alias, roleperiod_team, roleperiod_starttime FROM roleperiods NATURAL JOIN sessions NATURAL JOIN classes WHERE player_id = ' . $playerid . ' AND roleperiod_starttime > \'' . $mintime . '\' ORDER BY roleperiod_starttime DESC LIMIT 0,100';
  $res = mysql_query($sql);
  while ($row = mysql_fetch_assoc($res)) {
   $row['time'] = $row['roleperiod_starttime'];
   $events[] = $row;
  }

  usort($events, 'eventSort');

  echo '<table class="events"><tr><th>Time</th><th colspan="7">Event</th></tr>';

  $i = 0;
  foreach (array_slice($events, 0, 100) as $event) {
   echo '<tr', ++$i % 2 == 0 ? ' class="even"' : '', '><td class="time">', str_replace(' ', '<br>', $event['time']), '</td>';

   if (isset($event['kill_timestamp'])) {
    displayKill($event, $playerid);
   } else if (isset($event['session_starttime'])) {
    displaySession($event);
   } else if (isset($event['roleperiod_starttime'])) {
    displayRoleChange($event);
   }

   echo '</tr>', "\n";
  }

  echo '</table>';
 }

 function displayRoleChange($event) {
  $text = getTeam($event['roleperiod_team']) . ' ' . strtolower($event['class_displayname']);

  echo '<td colspan="7"><img src="', sprintf(URL_CLASS, getTeam($event['roleperiod_team']), $event['class_name']);
  echo '" alt="', $text, '"> ', htmlentities($event['session_alias'], ENT_COMPAT, 'UTF-8');
  echo ' changed to a ', $text, '</td>';
 }

 function displayKill($event, $playerid) {
  $me = null; 

  if ($event['killerID'] == $playerid) {
   $me = 'killer';
  } else if ($event['victimID'] == $playerid) {
   $me = 'victim';
  } else {
   $me = 'assist';
  }

  call_user_func('displayKill' . ucfirst($me), $event);
 }

 function getTeam($team) {
  switch ((int) $team) {
   case 1: return 'red';
   case 2: return 'blue';
  }
 }

 function displayPerson($event, $who, $link = true, $link2 = true) {
  echo '<td', ($who != 'killer' || $event['assistID'] == 0) ? ' colspan="2"' : '', '><img src="', sprintf(URL_CLASS, getTeam($event[$who . 'Team']), $event[$who . 'Class']);
  echo '" alt="' , getTeam($event[$who . 'Team']), ' ', $event[$who . 'Class'], '"> ';

  if ($link) {
   echo '<a href="player.php?id=' . $event[$who . 'ID'] . '">';
  }

  echo htmlentities($event[$who . 'Name'], ENT_COMPAT, 'UTF-8');

  if ($link) {
   echo '</a>';
  }

  if ($who == 'killer' && $event['assistID'] > 0) {
   echo ',</td><td><img src="', sprintf(URL_CLASS, getTeam($event['assistTeam']), $event['assistClass']);
   echo '" alt="' , getTeam($event['assistTeam']), ' ', $event['assistClass'], '"> ';

   if ($link2) {
    echo '<a href="player.php?id=' . $event['assistID'] . '">';
   }

   echo htmlentities($event['assistName'], ENT_COMPAT, 'UTF-8');

   if ($link2) {
    echo '</a>';
   }

  }

  echo '</td>';
 }

 function displayWeapon($event) {
  echo '<td><img src="', sprintf(URL_WEAPON, $event['weapon_name']), '"></td>';
 }

 function displayKillKiller($event) {
  displayPerson($event, 'killer', false);
  echo '<td>killed</td>';
  displayPerson($event, 'victim');
  echo '<td>with</td>';
  displayWeapon($event);
 }

 function displayKillVictim($event) {
  displayPerson($event, 'killer');
  echo '<td>killed</td>';
  displayPerson($event, 'victim', false);
  echo '<td>with</td>';
  displayWeapon($event);
 }

 function displayKillAssist($event) {
  displayPerson($event, 'killer', true, false);
  echo '<td>killed</td>';
  displayPerson($event, 'victim');
  echo '<td>with</td>';
  displayWeapon($event);
 }

 function displaySession($event) {
  echo '<td colspan="7">';

  echo '<img src="', sprintf(URL_MAP, 'tiny', $event['map_name']);
  echo '" alt="', $event['map_name'], '" title="Map: ', $event['map_name'], '"> ';

  if ($event['session_starttime'] == $event['time']) {
   echo htmlentities($event['session_alias'], ENT_COMPAT, 'UTF-8'), ' joined the server';
  } else {
   echo htmlentities($event['session_alias'], ENT_COMPAT, 'UTF-8'), ' left the server';
  }

  echo '</td>';
 }

?>
