<?PHP

 define('COMMUNITY_KEY', '76561197960265729');

 function getCommunityID($steamID) {
  $parts = explode(':', $steamID);
  $id = array_pop($parts);

  return stringAdd(COMMUNITY_KEY, (string) ($id * 2));
 }

 function stringAdd($stringA, $stringB) {
  $carry = 0;
  for ($i = 1; $i <= strlen($stringB) || $carry > 0; $i++) {
   $val = ord($stringA[strlen($stringA) - $i]) + $carry - 48;

   if ($i <= strlen($stringB)) {
    $val += ord($stringB[strlen($stringB) - $i]) - 48;
   }

   $carry = floor($val / 10);
   $val = 48 + ($val % 10);
   $stringA[strlen($stringA) - $i] = chr($val);
  }
  
  return $stringA;
 }

?>
