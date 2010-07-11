<?PHP

echo '<a href="#">';
echo '<img src="', sprintf('/stats/res/maps/large/%s/Overview.png', htmlentities($_GET['map'])), '" ismap="true">';
