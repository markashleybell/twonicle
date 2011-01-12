<?php
require('config/config.php');

$db = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$db->set_charset("utf8");

// Reset the flag so that other updates can be performed
$db->query("update system set v = 0 where k = 'processing'");

echo '{ "reset": true }';
?>
