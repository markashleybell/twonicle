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

$userid = $mysqli->real_escape_string($_POST['status']['user']['id_str']);
$tweetid = $mysqli->real_escape_string($_POST['status']['id_str']);
$type = 0; // $_POST['status']['type'] Not quite sure what this was used for?
$time = strtotime($_POST['status']['created_at']);
$text = $mysqli->real_escape_string($_POST['status']['text']);
$source = $mysqli->real_escape_string($_POST['status']['source']);
$favorite = $mysqli->real_escape_string($_POST['status']['favorited']);
$extra = ''; // $_POST['status']['extra'] Or this
$coordinates = $mysqli->real_escape_string($_POST['status']['coordinates']);
$geo = $mysqli->real_escape_string($_POST['status']['geo']);
$place = $mysqli->real_escape_string($_POST['status']['place']);
$contributors = $mysqli->real_escape_string($_POST['status']['contributors']);

$sql = "INSERT INTO tweets (userid, tweetid, type, time, text, source, favorite, extra, coordinates, geo, place, contributors, pick) VALUES " .
       "(" . $userid . ", " . $tweetid . ", " . $type . ", " . $time . ", '" . $text . "', '" . $source . "', " . $favorite . ", '" . $extra . "', " . $coordinates . ", " . $geo . ", " . $place. ", " . $contributors . ", 0)";

//echo $sql;

$mysqli->query($sql);

echo '{ "success": 1 }';
?>