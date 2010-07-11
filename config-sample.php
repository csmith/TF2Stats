<?PHP

 // --------------------------------------------- Database Settings -----------

 // MySQL host
 define('DB_HOST', '127.0.0.1');

 // MySQL username
 define('DB_USER', '');

 // MySQL password
 define('DB_PASS', '');

 // MySQL database name
 define('DB_DBNAME', '');

 // -------------------------------------------- Directory Settings -----------

 // Directory containing HLDS log files
 define('LOG_DIR', '/home/sourceds/orangebox/tf/logs/');

 // The directory that the main statistics codebase is located in
 define('STATS_DIR', '/home/sourceds/stats/');

 // ---------------------------------------------- Ranking Settings -----------

 // Minimum number of kills needed to be ranked
 define('MIN_KILLS', 50);

 // ------------------------------------------------ Server Details -----------

 // Command to use to send player messages
 define('RCON_COMMAND', 'admin_msay "%s" "%s"');

 // --------------------------------------------- Feature Selection -----------

 // Death maps
 define('ENABLE_DEATHMAPS', true);

 // Awards
 define('ENABLE_AWARDS', true);

 // Group links
 define('ENABLE_GROUPS', true);

 // Steam community links
 define('ENABLE_COMMUNITY_LINKS', true);

 // Respond to messages said in global chat (when in daemon mode)
 define('ENABLE_SAY_TRIGGERS', true);

 // Server list on the overview page
 define('ENABLE_SERVER_LIST', true);

 // ----------------------------------- Overview Page Configuration -----------

 // Number of top players to show on the overview page
 define('OVERVIEW_PLAYERS', 15);

 // ------------------------------------------ Awards Configuration -----------

 // How often to give out awards (in days)
 define('AWARD_FREQUENCY', 7);

 // Number of previous winners to show on the award page (per award)
 define('AWARD_NUMBER', 5);

 // --------------------------------------- Death Map Configuration -----------

 // The file name for overview images
 define('OVERVIEW_IMAGE', '/home/sourceds/www/stats/res/maps/large/%s/Overview.png');

 // Arguments for the default death map
 define('DM_ARGS', '?map=%s&alpha=50&noteams');

 // Cache dir for DM images
 define('DM_CACHE', STATS_DIR . 'cache/');

 // ---------------------------------------------------------- URLs -----------

 // The base URL for the stats websites
 define('URL_BASE', '/stats/');

 // The URL for map images. Passed image size (tiny, small, large) and map name.
 define('URL_MAP', '/stats/res/maps/%s/%s/Main.png');

 // The URL for class images. Passed team and class name.
 define('URL_CLASS', '/stats/res/classes/small/%s/%s.png');

 // The URL for weapon images. Passed weapon name.
 define('URL_WEAPON', '/stats/res/weapons/%s.png');

 // The URL for award images. Passed award name.
 define('URL_AWARD', '/stats/res/awards/%s.png');

?>
