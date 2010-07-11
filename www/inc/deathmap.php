<?PHP

 // Co-ordinates for deathmaps

 $coords = array(
        'yaaargh' => array(
                528, 143, -1814, -6,
                364, 325, -71, 2016,
                190, 331, 1823, 2057
        ),

	'ctf_casbah' => array(
                494, 433, 1905, -4733,  // Middle corner
		123, 89, -3925, 512, // Red cap cliffs -3925 x, 512 y, 
		421, 78, 660, 764, // Blue cap
	),

	'cp_king_of_the_hill_b1' => array(
		188, 69, 1433, 1615, // Blue door
		540, 378, -1439, -1642, // Red door
		141, 446, -2040, 2047, // BL corner
	),

	'cp_granary' => array(
		92, 226, -1472, -5178, // Red cap
		690, 361, 1022, 6336, // BR corner
		363, 155, -2751, -17, // Middle ramp
	),
 );

 function hasDeathmap($map) {
  global $coords;
  return isset($coords[$map]);
 }

?>
