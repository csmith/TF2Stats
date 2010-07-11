<?PHP

 class ChangedHandler {

  /*-------------------------------------------------------------------------*\
   * Handles role or name changing events.                                   *
   *                                                                         *
   * Line sample: role to "Sniper"                                           *
   *              name to "Foo"                                              *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $what = substr($line, 0, 4);
   $line = substr($line, 8); // role to
   $class = Parser::parseString($line);

   if ($what != 'role') {
    return;
   } 

   if (Game::getCurrent() == null) {
    echo "No game in progress; discarding class change\n";
    return;
   }

   if ($player->getOpenSession() == null) {
    $player->openSession($date, $playerdetails['uid'], $playerdetails['alias']);
   }

   $player->getOpenSession()->changeRole($date, $playerdetails['team'], $class);
  }

 }

?>
