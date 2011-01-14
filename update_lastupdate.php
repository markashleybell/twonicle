<?php
require('config/config.php');

$db = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$db->set_charset("utf8");

$now = strtotime("now");

// Store the last updated date in the database
$cmd = $db->stmt_init();
$cmd->prepare("update " . $config['table_prefix'] . "system set v = ? where k = 'lastupdated'");
$cmd->bind_param("i", $now);
$cmd->execute();
$cmd->close();

// Reset the flag so that other updates can be performed
$db->query("update " . $config['table_prefix'] . "system set v = 0 where k = 'processing'");

echo '{ "lastupdate": "' . date('d/m/y H:i', $now) . '" }';
?>
