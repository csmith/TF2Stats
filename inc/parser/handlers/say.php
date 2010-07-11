<?PHP

 class SayHandler {

  /*-------------------------------------------------------------------------*\
   * Handles chat events.                                                    *
  \*-------------------------------------------------------------------------*/

  public function parseLine($date, $player, $playerdetails, $line) {
   if (!ENABLE_SAY_TRIGGERS || !Parser::hasDaemon()) {
    return;
   }

   $line = substr(trim($line), 1, -1); // Cut off quotes
   
   if (strtolower($line) == 'kpd') {
    Parser::getDaemon()->showKPD($player);
   }
  }

 }

?>
