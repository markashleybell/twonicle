<?php

require('config/config.php');
require('include/db.php');

$basepath = ($config['app_base_path'] == '') ? '' : $config['app_base_path'] . '/';

$db = new DB($config['db_server'], $config['db_username'], $config['db_password'], $config['db_database'], $config['db_table_prefix']);

// If another client is already performing the update process, hold off until that process is complete
if(!$db->runUpdate($config['app_update_lock_timeout_minutes'])) {
    
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
    
} else {
    
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
                var mostRecentId = '<?php echo $db->getLastStoredStatusId(); ?>';
                var appBaseUrl = '<?php echo $basepath; ?>';
            </script>
            <script type="text/javascript" src="script/updater.js"></script>
            <script type="text/javascript">
                                
                $(function() {
                    
                    outputPanel = $('#output');
                    retrieveStatusData();
                    
                });
                
            </script>
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