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

$sql = "select t.id, t.time, t.text, u.screenname, u.profileimage " .
       "from tweets as t " .
       "inner join tweetusers as u " .
       "on u.userid = t.userid " .
       "order by time desc";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<script src="http://platform.twitter.com/anywhere.js?id=idyTlCoEihlkLSC0ezJ1Q&amp;v=1"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript">
            
            $(function(){
               
               $('#import').bind('click', getTweets);
               
            });
            
        </script>
	</head>
	<body>
		<?php
        if ($result = $mysqli->query($sql, MYSQLI_USE_RESULT)) {
            while ($row = $result->fetch_object()) {
                //echo '<p><img src="' . $row->profileimage . '" /> ' . $row->text . '</p>';
                echo '<p>' . date('d m y h:m', $row->time) . ': ' . $row->text . '</p>';
            }
            $result->close();
        }
        ?>
	</body>
</html>