<?php
require('config/config.php');

$jsbasepath = $config['app_base_path'];
$basepath = ($config['app_base_path'] == '') ? '' : $config['app_base_path'] . '/';

$db = new mysqli($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database']);

if (mysqli_connect_errno()) 
{
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$db->set_charset("utf8");

$lastupdatestarttime = strtotime("now");

if ($result = $db->query("select v as lastupdatestarted from " . $config['db_table_prefix'] . "system where k = 'lastupdatestarted'")) 
{
    $row = $result->fetch_object();
    if($row->lastupdatestarted) $lastupdatestarttime = $row->lastupdatestarted;
    $result->close();
}

// Check if there is already an update in progress
$processing = false;

if ($result = $db->query("select v as processing from " . $config['db_table_prefix'] . "system where k = 'processing'")) 
{
    $row = $result->fetch_object();
    if($row->processing) $processing = $row->processing;
    $result->close();
}

// If a certain amount of time has elapsed since the current update was started, chances are the processing flag got 'stuck'
// for some reason (perhaps the user refreshed the page halfway through, there was a temporary communication error etc)
// Here we check how long it's been and ignore the flag if enough time has passed (it will get reset if the process is
// successful this time around anyway)
$minutessinceupdatestarted = (strtotime("now") - $lastupdatestarttime) / 60;

if($minutessinceupdatestarted > $config['app_update_lock_timeout_minutes'])
    $processing = false;

// If another client is already performing the update process, hold off until that process is complete
if($processing)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="/<?php echo $basepath; ?>css/updater.css" />
        <link rel="shortcut icon" href="/<?php echo $basepath; ?>img/site/favicon.ico" />
	</head>
	<body>
        <h1>Update Already In Progress</h1>
        <div id="output">
            <p>There is already an update in progress. Please try again in 5 minutes.</p>
        </div>
	</body>
</html>
<?php
}
else
{
// Set the flag that tells everyone else there's an update in progress
$db->query("update " . $config['db_table_prefix'] . "system set v = 1 where k = 'processing'");
// Set the start time for this update in case it gets interrupted
$db->query("update " . $config['db_table_prefix'] . "system set v = " . strtotime("now") . " where k = 'lastupdatestarted'");

// Get the last status id stored, so that we only retrieve new tweets
$since = 100;

if ($result = $db->query("select max(statusid) as since from " . $config['db_table_prefix'] . "statuses")) {
    $row = $result->fetch_object();
    if($row->since) $since = $row->since;
    $result->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="/<?php echo $basepath; ?>css/updater.css" />
        <link rel="shortcut icon" href="/<?php echo $basepath; ?>img/site/favicon.ico" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/jquery.jsonp-2.1.4.min.js"></script>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/json2.js"></script>
        <script type="text/javascript">
            var twitterUserName = '<?php echo $config['twitter_username']; ?>';
            var mostRecentId = '<?php echo $since; ?>';
			var appBaseUrl = '<?php echo $jsbasepath; ?>';
        </script>
        <script type="text/javascript" src="script/updater.js"></script>
	</head>
	<body>
        <h1>Updating Tweet Archive</h1>
        <div id="output">
            <p>Fetching tweets...</p>
        </div>
	</body>
</html>
<?php
}
?>