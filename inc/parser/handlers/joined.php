<?PHP

 class JoinedHandler {

  /*-------------------------------------------------------------------------*\
   * Handles team joining events.                                            *
   *                                                                         *
   * Line sample: team "Red"                                                 *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $line = substr($line, 5); // team
   $team = Parser::parseString($line);

   if ($player->getOpenSession() == null) {
    echo "Player " . $player->getSteamID() . " has no open session but joined a team\n";
    $player->openSession($date, $playerdetails['uid'], $playerdetails['alias']);
   }

   $player->getOpenSession()->changeTeam($date, $team);
  }

 }

?>
