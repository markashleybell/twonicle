<?php

require('config/config.php');

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) {
    
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
    
}

$db->set_charset("utf8");

$sql = "update " . $config['db_table_prefix'] . "statuses set pick = ? where statusid = ?";

$cmd = $db->stmt_init();
$cmd->prepare($sql);

$cmd->bind_param('is', $_POST["pick"], $_POST["id"]);

$cmd->execute();

$cmd->close();

echo '{ "success": 1 }';

?>
