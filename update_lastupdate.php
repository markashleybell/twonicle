<?php
require('config.php');

error_reporting(E_ALL);

$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// mysqli_report(MYSQLI_REPORT_ERROR);

$mysqli->set_charset("utf8");

$d = strtotime("now");

$mysqli->query("update tweetsystem set v = '" . $d . "' where k = 'lastupdated'");

echo '{ "lastupdate": "' . date('d/m/y H:i', $d) . '" }';
?>