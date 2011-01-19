<?php

require('config/config.php');

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) {
    
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
    
}

$db->set_charset("utf8");

$now = strtotime("now");

// Store the last updated date in the database
$cmd = $db->stmt_init();
$cmd->prepare("update " . $config['db_table_prefix'] . "system set v = ? where k = 'lastupdatecompleted'");
$cmd->bind_param("i", $now);
$cmd->execute();
$cmd->close();

// Reset the flag so that other updates can be performed
$db->query("update " . $config['db_table_prefix'] . "system set v = 0 where k = 'processing'");

echo '{ "lastupdate": "' . date('d/m/y H:i', $now) . '" }';

?>
