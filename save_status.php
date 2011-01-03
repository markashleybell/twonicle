<?php
require('config.php');

error_reporting(E_ALL);

$db = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// mysqli_report(MYSQLI_REPORT_ERROR);

$db->set_charset("utf8");

$sql = "INSERT INTO statuses (userid, statusid, time, text, source, favorite, coordinates, geo, place, contributors, pick) VALUES " .
       "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$cmd = $db->stmt_init();
$cmd->prepare($sql);
$cmd->bind_param("ssississss", $_POST['status']['user']['id_str'],
                               $_POST['status']['id_str'],
                               strtotime($_POST['status']['created_at']),
                               $_POST['status']['text'],
                               $_POST['status']['source'],
                               $_POST['status']['favorited'],
                               $_POST['status']['coordinates'],
                               $_POST['status']['geo'],
                               $_POST['status']['created_at'],
                               $_POST['status']['contributors']);
$cmd->execute();

$cmd->close();

echo '{ "success": 1 }';
?>