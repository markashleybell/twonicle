<?php
require('config.php');

// connect to the database
$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

// mysqli_report(MYSQLI_REPORT_ERROR);

$sql = "INSERT tweets (userid, tweetid, type, time, text, source, favorite, extra, coordinates, geo, place, contributors, pick) " + 
       "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"

// insert the new record into the database
if ($stmt = $mysqli->prepare($sql))
{
        $stmt->bind_param("iiiississsssi", $firstname, $lastname);
        $stmt->execute();
        $stmt->close();
}
// show an error if the query has an error
else
{
        echo "ERROR: Could not prepare SQL statement.";
}

?>