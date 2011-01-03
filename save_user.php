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

$userid = $mysqli->real_escape_string($_POST['user']['id_str']);
$screenname = $mysqli->real_escape_string($_POST['user']['screen_name']);
$realname = $mysqli->real_escape_string($_POST['user']['name']);
$location = $mysqli->real_escape_string($_POST['user']['location']);
$description = $mysqli->real_escape_string($_POST['user']['description']);
$profileimage = $mysqli->real_escape_string($_POST['user']['profile_image_url']);
$url = $mysqli->real_escape_string($_POST['user']['url']);
$extra = ''; //$mysqli->real_escape_string($_POST['user']['favorited']);
$enabled = $mysqli->real_escape_string($_POST['user']['geo_enabled']);

$rows = 0;
$sql = "INSERT INTO people (userid, screenname, realname, location, description, profileimage, url, extra, enabled) VALUES " .
       "(" . $userid . ", '" . $screenname . "', '" . $realname . "', '" . $location . "', '" . $description . "', '" . $profileimage . "', '" . $url . "', '" . $extra . "', " . $enabled . ")";

if ($result = $mysqli->query("select id from people where userid = " . $userid)) {

    $rows = $result->num_rows;
    $result->close();
}

// If we already have data for this user, update it
if($rows > 0)
{
    $sql = "update people set screenname = '" . $screenname . "', realname = '" . $realname . "', location = '" . $location . "', description = '" . $description . "', profileimage = '" . $profileimage . "', url = '" . $url . "', extra = '" . $extra . "', enabled = " . $enabled . " where userid = " . $userid;
}

//echo $sql;

$mysqli->query($sql);

echo '{ "success": 1, "new": ' . (($rows > 0) ? 0 : 1) . ' }';
?>