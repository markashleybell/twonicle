<?php 
require('config/config.php');

error_reporting(E_ALL);

$db = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// mysqli_report(MYSQLI_REPORT_ERROR);

$db->set_charset("utf8");

// Get the last time the archive was updated
$lastupdate = 1000;

if ($result = $db->query("select v as lastupdate from system where k = 'lastupdated'")) {
    $row = $result->fetch_object();
    if($row->lastupdate) $lastupdate = $row->lastupdate;
    $result->close();
}

$now = time();
$hours = ($now - $lastupdate) / 3600;
$updateneeded = false;

// If the archive hasn't been updated for our specified number of hours
if($hours > $config['update_interval_hours'])
{
    // Do an update (showing progress with AJAX dialog) and load in new data (also AJAX)
    // Eventually we'll do this automatically, but for now just show a warning and a link to the updater
    $updateneeded = true;
}

$diff = 0;

// Getting database time offset
if ($result = $db->query("SELECT TIME_FORMAT(NOW() - UTC_TIMESTAMP(), '%H%i') AS 'diff'")) {
    $row = $result->fetch_object();
    if($row->diff) $diff = $row->diff;
    $result->close();
}
	
$dbOffset          = date("Z") - ($diff * 36); if(!is_numeric($dbOffset)){ $dbOffset = 0; }
$dbOffset          = $dbOffset >= 0 ? "+" . $dbOffset : $dbOffset; // Explicit positivity/negativity

$sql = "select s.id, s.time, s.text, p.screenname, p.realname, p.profileimage " .
       "from statuses as s " .
       "inner join people as p " .
       "on p.userid = s.userid " .
       "where YEAR(FROM_UNIXTIME(time)) = ? AND MONTH(FROM_UNIXTIME(time)) = ? AND DAY(FROM_UNIXTIME(time)) = ? " . 
       "order by time desc";

$cmd = $db->stmt_init();
$cmd->prepare($sql);
$cmd->bind_param("iii", $_GET['y'],
                       $_GET['m'],
                       $_GET['d']);
$cmd->execute();

$cmd->bind_result($_id, $_time, $_text, $_screenname, $_realname, $_profileimage);

//$cmd->store_result();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="/css/screen.css" />
		<script src="http://platform.twitter.com/anywhere.js?id=<?php echo $config['anywhere_api_key']; ?>&amp;v=1"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript">
            
            twttr.anywhere(function (T) {
                T.hovercards();
            });
            
        </script>
	</head>
	<body>
		<?php
        if($updateneeded)
            echo '<h2 class="warning">Archive has not been updated since ' . date('d/m/y H:i', $lastupdate) . '. <a href="updater.php">Update Now</a>.</h2>';

        while ($row = $cmd->fetch()) {
            echo '<div class="tweet"><img src="' . $_profileimage . '" /> <div class="details">' .
                 '<span class="username">' . $_screenname . '</span> <span class="realname">' . $_realname . '</span><span class="text">' . $_text . '</span>' . 
                 '<span class="date">' . date('d/m/y H:i', $_time) . '</span></div></div>';
        }
        $cmd->close();
        ?>
	</body>
</html>