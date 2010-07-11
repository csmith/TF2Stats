<?PHP

 class DisconnectedHandler {

  /*-------------------------------------------------------------------------*\
   * Handles players disconnecting                                           *
   *                                                                         *
   * Sample line: (reason "Jasprit timed out")                               *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   $player->closeSession($date); 
  }

 }

?>
