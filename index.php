<?php 
require('config/config.php');
require('include/status.php');
require('include/month.php');
require('include/db.php');

$basepath = ($config['app_base_path'] == '') ? '' : $config['app_base_path'] . '/';

$db = new DB($config['server'], $config['username'], $config['password'], $config['database'], $config['table_prefix']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <link rel="stylesheet" type="text/css" href="/<?php echo $basepath; ?>css/screen.css" />
		<script src="http://platform.twitter.com/anywhere.js?id=<?php echo $config['anywhere_api_key']; ?>&amp;v=1"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript">
            
            twttr.anywhere(function (T) {
                T.hovercards();
            });
            
        </script>
	</head>
	<body>
        <?php require('include/head.php'); ?>
        <div id="container">
            <div id="navigation">
                <?php require('include/year_navigation.php'); ?>
            </div>
            <div id="tweets">
                <?php
                if($db->needsUpdate($config['update_interval_hours']))
                    echo '<h2 class="warning">Archive has not been updated for more than ' . $config['update_interval_hours'] . ' hours. <a href="/'. $basepath . 'update">Update Now</a>.</h2>';
        
                $result = $db->getRecentTweets();

                foreach ($result as $status) {
                    echo '<div class="tweet"><img src="' . $status->profileimage . '" /> <div class="details">' .
                         '<span class="username">' . $status->screenname . '</span> <span class="realname">' . $status->realname . '</span><span class="text">' . $status->text . '</span>' . 
                         '<span class="date">' . date('d/m/y H:i', $status->time) . '</span></div></div>';
                }
                ?>
            </div>
            
        </div>
        <?php require('include/foot.php'); ?>     
	</body>
</html>
