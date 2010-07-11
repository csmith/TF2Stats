<?PHP

 require_once(dirname(dirname(dirname(__FILE__))) . '/game.php');

 class LoadingHandler {

  /*-------------------------------------------------------------------------*\
   * Handles map loading events.                                             *
   *                                                                         *
   * Line sample: map "cp_dustbowl"                                          *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $line = substr($line, 4); // map
   $map = Parser::parseString($line);

   Game::changeMap($date, $map);
  }

 }

?>
