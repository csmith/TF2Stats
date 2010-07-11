<?PHP

 class Server {

  private $host, $port;
  private $socket;

  public function __construct($host, $port) {
   $this->host = $host;
   $this->port = $port;

   $this->socket = fsockopen('udp://' . $this->host, $this->port);
   stream_set_timeout($this->socket, 5);
  }

  public function getInfo() {
   $this->send(pack('LAA*x', -1, 'T', 'Source Engine Query'));
   return $this->receiveInfo();
  }

  private function send($data) {
   fwrite($this->socket, $data);
  }

  private function readUntilNull() {
   do {
    $data .= $read = fread($this->socket, 1);
    $info = stream_get_meta_data($this->socket); 

    if ($info['timed_out']) {
     throw new Exception('Socket time out');
    }

   } while ($read !== false && $read !== "\0");

   return $data;
  }

  private function receiveInfo() {
   $res = unpack('Lheader/ctype/cversion/a*name', $this->readUntilNull());
   $res = array_merge($res, unpack('a*map', $this->readUntilNull()));
   $res = array_merge($res, unpack('a*game_dir', $this->readUntilNull()));
   $res = array_merge($res, unpack('a*game_name', $this->readUntilNull()));
   $res = array_merge($res, unpack('vappid/cplayers/cmaxplayers/cbots/cdedicated/cos/cpassword/csecure', fread($this->socket, 9)));
   $res = array_merge($res, unpack('a*game_version', $this->readUntilNull()));

   return $res;
  }

  private function hexdump($data) {
   $data = unpack('H*', $data);
   $data = $data[1];
   for ($i = 0; $i < strlen($data); $i += 2) {
    if ($i % 32 == 0) { echo "\n"; } else if ($i % 32 == 0) { echo ' '; }
    echo substr($data, $i, 2) . ' ';
   } 
   echo "\n";
  }

 }

?>
