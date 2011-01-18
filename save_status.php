<?php
require('config/config.php');

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$db->set_charset("utf8");

$sql = "INSERT INTO " . $config['db_table_prefix'] . "statuses (userid, statusid, rtstatusid, inreplytoid, inreplytouser, time, text, source, favorite, coordinates, geo, place, contributors, pick) VALUES " .
       "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$cmd = $db->stmt_init();
$cmd->prepare($sql);

$statusJSON = $_POST["status"];

// Replace ID's with string representations
$statusJSON = preg_replace("/\"([a-z_]+_)?id\":(\d+)(,|\}|\])/", "\"$1id\":\"$2\"$3", $statusJSON);

$status = json_decode($statusJSON, true);

$coordinates = NULL;
$geo = NULL;
$place = NULL;
$inreplytoid = "0";
$inreplytouser = NULL;

// If it's a retweet, we store all the details for the original tweet, with the exception that the original's id
// is stored in the field rtstatusid and the retweet's id is stored as the actual id
if(isset($status['retweeted_status']))
{
    if(isset($status['retweeted_status']['coordinates'])) $coordinates = json_encode($status['retweeted_status']['coordinates']);
    if(isset($status['retweeted_status']['geo'])) $geo = json_encode($status['retweeted_status']['geo']);
    if(isset($status['retweeted_status']['place'])) $place = json_encode($status['retweeted_status']['place']);
    if(isset($status['retweeted_status']['in_reply_to_status_id_str'])) $inreplytoid = $status['retweeted_status']['in_reply_to_status_id_str'];
    if(isset($status['retweeted_status']['in_reply_to_screen_name'])) $inreplytouser = $status['retweeted_status']['in_reply_to_screen_name'];
    
    $cmd->bind_param("sssssississss", $status['retweeted_status']['user']['id_str'],
                                    $status['id_str'],
                                    $status['retweeted_status']['id_str'],
                                    $inreplytoid,
                                    $inreplytouser,
                                    strtotime($status['retweeted_status']['created_at']),
                                    $status['retweeted_status']['text'],
                                    $status['retweeted_status']['source'],
                                    $status['retweeted_status']['favorited'],
                                    $coordinates,
                                    $geo,
                                    $place,
                                    $status['retweeted_status']['contributors']);

}
else
{
    $noid = "0";
    
    if(isset($status['coordinates'])) $coordinates = json_encode($status['coordinates']);
    if(isset($status['geo'])) $geo = json_encode($status['geo']);
    if(isset($status['place'])) $place = json_encode($status['place']);
    if(isset($status['in_reply_to_status_id_str'])) $inreplytoid = $status['in_reply_to_status_id_str'];
    if(isset($status['in_reply_to_screen_name'])) $inreplytouser = $status['in_reply_to_screen_name'];
    
    $cmd->bind_param("sssssississss", $status['user']['id_str'],
                                    $status['id_str'],
                                    $noid,
                                    $inreplytoid,
                                    $inreplytouser,
                                    strtotime($status['created_at']),
                                    $status['text'],
                                    $status['source'],
                                    $status['favorited'],
                                    $coordinates,
                                    $geo,
                                    $place,
                                    $status['contributors']);
}

$cmd->execute();

$cmd->close();

echo '{ "success": 1 }';
?>