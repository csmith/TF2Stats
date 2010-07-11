<?PHP

 require_once(dirname(dirname(__FILE__)) . '/config.php');
 require_once(dirname(__FILE__) . '/player.php');
 require_once(dirname(__FILE__) . '/rcon.class.php');

 class Daemon {

  private $rcon;

  public function __construct($server, $port, $rconpass) {
   $this->rcon = new Rcon($server, $port, $rconpass);
  }

  public function showKPD(&$player) {
   $message  = 'Average kills per death: ';
   $message .= $player->getKPD();
   $this->sendMessage($player, $message);
  }

  private function sendMessage(&$player, $message) {
   $this->rcon->execute(sprintf(RCON_COMMAND,
                $player->getOpenSession()->getAlias(), $message));
  }

 }


?>
