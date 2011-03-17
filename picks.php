<?php

require('config/config.php');
require('include/status.php');
require('include/month.php');
require('include/db.php');
require('include/display_status.php');

$basepath = ($config['app_base_path'] == '') ? '' : $config['app_base_path'] . '/';

$db = new DB($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database'], $config['db_table_prefix']);

$plainText = isset($_GET["text"]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="/<?php echo $basepath; ?>css/screen.css" />
        <link rel="shortcut icon" href="/<?php echo $basepath; ?>img/site/favicon.ico" />
        <script src="http://platform.twitter.com/anywhere.js?id=<?php echo $config['app_anywhere_api_key']; ?>&amp;v=1"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/jquery.jsonp-2.1.4.min.js"></script>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/json2.js"></script>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/anywhere.js"></script>
        <script type="text/javascript">
            var updateCompleteCallback;
            var twitterUserName = '<?php echo $config['twitter_username']; ?>';
            var mostRecentId = '<?php echo $db->getLastStoredStatusId(); ?>';
            var appBaseUrl = '<?php echo $basepath; ?>';
        </script>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/updater.js"></script>
        <?php if($db->archiveNeedsUpdate($config['app_update_interval_hours']) && $db->runUpdate($config['app_update_lock_timeout_minutes'])) { ?>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/ajax_update.js"></script>
        <?php } ?>
        <script type="text/javascript" src="/<?php echo $basepath; ?>script/pick.js"></script>
    </head>
    <body>
        <?php require('include/head.php'); ?>
        <div id="container">
            <div id="navigation">
                <?php require('include/year_navigation.php'); ?>
            </div>
            <div id="recent" class="tweets<?php echo (($plainText) ? ' plain-text' : ''); ?>">
                <?php
                
                $result = $db->getPicks();

                foreach ($result as $status) {
                    
                    if($plainText)
                        echo displayStatusPlainText($status, $basepath);
                    else
                        echo displayStatus($status, $basepath);
                    
                }
                
                ?>
            </div>
        </div>
        <?php require('include/foot.php'); ?>     
    </body>
</html>
