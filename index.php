<?php 
require('config.php');

// connect to the database
$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR);

$mysqli->set_charset("utf8");

/* If we have to retrieve large amount of data we use MYSQLI_USE_RESULT */
if ($result = $mysqli->query("select * from tweets order by time desc", MYSQLI_USE_RESULT)) {
    while ($row = $result->fetch_object()) {
        echo '<p>'.$row->text.'</p>';
    }
    $result->close();
}
?>