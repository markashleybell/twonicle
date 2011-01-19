<?php

require('config/config.php');

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) {
    
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
    
}

$db->set_charset("utf8");

$sql =  "INSERT INTO " . $config['db_table_prefix'] . "people (screenname, realname, location, description, profileimage, url, enabled, userid) VALUES " .
        "(?, ?, ?, ?, ?, ?, ?, ?)";

$userJSON = $_POST["user"];

// Replace ID's with string representations
$userJSON = preg_replace("/\"([a-z_]+_)?id\":(\d+)(,|\}|\])/", "\"$1id\":\"$2\"$3", $userJSON);

$user = json_decode($userJSON, true);

$userid = $user['id_str'];
$geo = ($user['geo_enabled'] == true) ? 1 : 0;
$isnew = true;

$check = $db->stmt_init();
$check->prepare("select id from " . $config['db_table_prefix'] . "people where userid = ?");
$check->bind_param("i", $userid);
$check->execute();
$check->store_result();

// If we already have data for this user, just update it
if($check->num_rows > 0) {
    
    $sql = "UPDATE " . $config['db_table_prefix'] . "people set screenname = ?, realname = ?, location = ?, description = ?, profileimage = ?, url = ?, enabled = ? where userid = ?";
    $isnew = false;
    
}

$check->close();

$cmd = $db->stmt_init();
$cmd->prepare($sql);
$cmd->bind_param("ssssssis", $user['screen_name'],
                             $user['name'],
                             $user['location'],
                             $user['description'],
                             $user['profile_image_url'],
                             $user['url'],
                             $geo,
                             $user['id_str']);
$cmd->execute();

$cmd->close();

echo '{ "success": 1, "new": ' . (($isnew) ? 1 : 0) . ' }';

?>