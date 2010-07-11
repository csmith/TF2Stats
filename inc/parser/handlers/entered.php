<?PHP

 class EnteredHandler {

  /*-------------------------------------------------------------------------*\
   * Handles players entering the game.                                      *
   *                                                                         *
   * Line sample: the game                                                   *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $player->openSession($date, $playerdetails['uid'], $playerdetails['alias']); 
  }

 }

?>
