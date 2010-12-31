<?php
require('config.php');

error_reporting(E_ALL);

// connect to the database
$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR);

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

$sql = "INSERT INTO tweetusers (userid, screenname, realname, location, description, profileimage, url, extra, enabled) VALUES " .
       "(" . $userid . ", '" . $screenname . "', '" . $realname . "', '" . $location . "', '" . $description . "', '" . $profileimage . "', '" . $url . "', '" . $extra . "', " . $enabled . ")";

//echo $sql;

$mysqli->query($sql);

echo '{ "success": 1 }';
?>