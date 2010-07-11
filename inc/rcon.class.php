<?PHP

 class Rcon {

  const SERVERDATA_AUTH = 3; 
  const SERVERDATA_EXECCOMMAND = 2;

  private $host, $port, $password;
  private $request = 0;
  private $socket;

  public function __construct($host, $port, $password) {
   $this->host = $host;
   $this->port = $port;
   $this->password = $password;

   if (($this->socket = fsockopen($this->host, $this->port)) === false) {
    die("Unable to create RCON connection to server!\n");
   }

   $this->send(self::SERVERDATA_AUTH, $password);
   $this->receive();
  }

  public function getStatus() {
   $this->send(self::SERVERDATA_EXECCOMMAND, 'status');
   $data = $this->receive();
   return $data['string1'];
  }

  public function execute($command) {
   $this->send(self::SERVERDATA_EXECCOMMAND, $command);
  }

  private function send($type, $string1, $string2 = "") {
   $data = pack('V3a' . (strlen($string1) + 1) . 'a' . (strlen($string2) + 1),
		strlen($string1 . $string2) + 10, ++$this->request, $type, $string1, $string2); 
   fputs($this->socket, $data);
  }

  private function receive() {
   $size = unpack('V', fread($this->socket, 4));
   $size = $size[1];
   $data = fread($this->socket, $size);
   $data = unpack('Vid/Vresponse/a*string1/a*string2', $data);

   if ($data['id'] == -1) {
    throw new Exception('Authentication failed');
   } else if ($data['id'] < $this->request) {
    return $this->receive();
   }

   return $data;
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
