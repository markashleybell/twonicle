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

$sql = "select t.id, t.time, t.text, u.screenname, u.realname, u.profileimage " .
       "from tweets as t " .
       "inner join tweetusers as u " .
       "on u.userid = t.userid " .
       "order by time desc limit 20";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <style type="text/css">
            
            body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 50px; }
            
            .tweet { width: 600px; overflow: auto; margin: 0 0 20px 0; }
            .tweet img { float: left; }
            .tweet span { display: block; }
            .details { width: 542px; float: right; }
            .username { float: left; font-size: 16px; font-weight: bold; margin-right: 10px; }
            .realname { float: left; font-size: 16px; color: #999; }
            .text { font-size: 18px; clear: left; }
            .date { font-size: 11px; color: #999; }
            
        </style>
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
        if ($result = $mysqli->query($sql, MYSQLI_USE_RESULT)) {
            while ($row = $result->fetch_object()) {
                echo '<div class="tweet"><img src="' . $row->profileimage . '" /> <div class="details">' .
                     '<span class="username">' . $row->screenname . '</span> <span class="realname">' . $row->realname . '</span><span class="text">' . $row->text . '</span>' . 
                     '<span class="date">' . date('d/m/y H:i', $row->time) . '</span></div></div>';
            }
            $result->close();
        }
        ?>
	</body>
</html>