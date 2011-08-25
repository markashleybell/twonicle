<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require('config/config.php');
require('include/db.php');

$db = new DB($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database'], $config['db_table_prefix']);

if($db->archiveNeedsUpdate($config['app_update_interval_hours']) && $db->runUpdate($config['app_update_lock_timeout_minutes'])) 
    print 1;
else
    print 0;

?>