<?php
require('config/config.php');

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$db->set_charset("utf8");

$sql = "INSERT INTO " . $config['db_table_prefix'] . "statuses (userid, statusid, rtstatusid, time, text, source, favorite, coordinates, geo, place, contributors, pick) VALUES " .
       "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$cmd = $db->stmt_init();
$cmd->prepare($sql);

// If it's a retweet, we store all the details for the original tweet, with the exception that the original's id
// is stored in the field rtstatusid and the retweet's id is stored as the actual id
if(isset($_POST['status']['retweeted_status']))
{
    $cmd->bind_param("sssississss", $_POST['status']['retweeted_status']['user']['id_str'],
                                    $_POST['status']['id_str'],
                                    $_POST['status']['retweeted_status']['id_str'],
                                    strtotime($_POST['status']['retweeted_status']['created_at']),
                                    $_POST['status']['retweeted_status']['text'],
                                    $_POST['status']['retweeted_status']['source'],
                                    $_POST['status']['retweeted_status']['favorited'],
                                    $_POST['status']['retweeted_status']['coordinates'],
                                    $_POST['status']['retweeted_status']['geo'],
                                    $_POST['status']['retweeted_status']['created_at'],
                                    $_POST['status']['retweeted_status']['contributors']);

}
else
{
    $noid = "0";

    $cmd->bind_param("sssississss", $_POST['status']['user']['id_str'],
                                    $_POST['status']['id_str'],
                                    $noid,
                                    strtotime($_POST['status']['created_at']),
                                    $_POST['status']['text'],
                                    $_POST['status']['source'],
                                    $_POST['status']['favorited'],
                                    $_POST['status']['coordinates'],
                                    $_POST['status']['geo'],
                                    $_POST['status']['created_at'],
                                    $_POST['status']['contributors']);
}

$cmd->execute();

$cmd->close();

echo '{ "success": 1 }';
?>