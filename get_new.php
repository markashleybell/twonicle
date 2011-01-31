<?php

require('config/config.php');
require('include/status.php');
require('include/db.php');

$db = new DB($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database'], $config['db_table_prefix']);

$result = $db->getNewTweets($_POST['since']);

header("Content-type: application/json; charset=utf-8");
header("Content-Transfer-Encoding: 8bit");

print json_encode($result);

?>