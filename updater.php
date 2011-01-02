<?php
require('config.php');

$mysqli = new mysqli($config['server'], $config['username'], $config['password'], $config['database']);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

// mysqli_report(MYSQLI_REPORT_ERROR);

$mysqli->set_charset("utf8");

// Get the last tweet id stored, so that we only retrieve new tweets
$since = 100;

if ($result = $mysqli->query("select max(tweetid) as since from tweets")) {
    $row = $result->fetch_object();
    if($row->since) $since = $row->since;
    $result->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
        <style type="text/css">

            #output { margin: 50px; }
            #output p { margin: 0; padding: 0; font-family: "Courier New", Courier, monospace; }

        </style>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript" src="script/jquery.jsonp-2.1.4.min.js"></script>
        <script type="text/javascript">
            
            var mostRecentId = <?php echo $since; ?>;
            var userIds = [];
            var currentPage = 1;
            var statuses = [];
            var users = [];

            var requestTimeout = 5000;
            
            var outputPanel;
            var tweetIdStatus;

            function log(msg, type)
            {
                cls = (typeof type == 'undefined') ? 'std' : type;
                outputPanel.append('<p class="' + cls + '">' + msg + '</p>');
            }

            function addUser(user)
            {
                userIds.push(user.id);
                userIds.sort(function(a, b) {
                    return a - b;
                });
            }

            function findUser(idList, id)
            {
                if(idList.length < 1) return false;

                var high = idList.length - 1;
                var low = 0;
                                
                while(low <= high)
                {
                    mid = parseInt((low + high) / 2);
                    if(idList[mid] == id) return true;
                    else if(idList[mid] > id) high = (mid - 1); // Search lower values
                    else if(idList[mid] < id) low = (mid + 1); // Search upper values
                }
                
                return false; // Value not found
            }

            function handleAjaxError(request, status, error)
            {   
                log('An error occurred while saving data');
                // log(status);
                // log(error);
            }
            
            function saveStatus()
            {
                if(statuses.length > 0)
                {
                    var s = statuses.pop();
                    
                    // If it's a retweet we want the data from the original tweet instead
                    if(typeof s.retweeted_status != 'undefined')
                        s = s.retweeted_status;

                    $.ajax({
                        url: 'save_status.php',
                        data: { status: s },
                        dataType: 'json',
                        type: 'POST',
                        success: function(data, status, request) { 
                            if(!$('#tweetid').length)
                            {
                                outputPanel.append('<p>Saving data for tweet <span id="tweetid"></span></p>');                       
                                tweetIdStatus = $('#tweetid');
                            }
                            tweetIdStatus.html(s.id_str);
                            saveStatus(); // Save the next tweet
                        },
                        error: handleAjaxError
                    });
                }
                else
                {
                    log('Finished saving tweets');
                    
                    // Kick off the user save
                    saveUser();
                }
            }

            function updateLastUpdateTime()
            {
                $.ajax({
                    url: 'update_lastupdate.php',
                    dataType: 'json',
                    type: 'POST',
                    success: function(data, status, request) { 
                        log('Update finished at ' + data.lastupdate);
                    },
                    error: handleAjaxError
                });
            }

            function saveUser()
            {
                if(users.length > 0)
                {
                    var u = users.pop();

                    $.ajax({
                        url: 'save_user.php',
                        data: { user: u },
                        dataType: 'json',
                        type: 'POST',
                        success: function(data, status, request) { 
                            log('Saved user data for ' + u.screen_name);
                            saveUser(); // Save the next tweet
                        },
                        error: handleAjaxError
                    });
                }
                else
                {
                    log('Finished saving users');
                    updateLastUpdateTime();
                }
            }

            function retrieveUserData()
            {
                if(userIds.length > 0)
                {
                    var id = userIds.pop();
                    
                    $.jsonp({
                        url: 'http://api.twitter.com/1/users/show.json?callback=?',
                        data: { user_id: id },
                        timeout: requestTimeout,
                        success: function(data, status, request) { 
                            log('Fetched user ' + data.screen_name);
                            users.push(data);
                            retrieveUserData();
                        },
                        error: function(opts, status)
                        {
                            log('There was an error retrieving the data, please reload this page to try again');
                        }
                    });
                }
                else
                {
                    // Loop through the statuses and users and insert them all into the db
                    saveStatus();
                }
            }
            
            function getTweets()
            {
                $.jsonp({
                    url: 'http://api.twitter.com/1/statuses/user_timeline.json?callback=?',
                    data: { screen_name: 'markeebee', include_rts: 1, trim_user: 1, count: 200, since_id: mostRecentId, page: currentPage },
                    timeout: requestTimeout,
                    success: function(data, status, request) { 
                        
                        for(var i = 0; i<data.length; i++)
                        {
                            var item = data[i];
                            var user = item.user;

                            // For some reason, 'id_str' can actually be slightly different than 'id' for the same tweet(!?!?)
                            // This means that the since_id parameter of the user_timeline call doesn't tie up with what we have stored
                            // We need to manually check our max id against the 'id_str' parameter to avoid odd duplicates here and there
                            if(item.id_str > mostRecentId)
                            {
                                if(item.retweeted_status)
                                    user = item.retweeted_status.user;

                                if(!findUser(userIds, user.id))
                                {
                                    addUser(user);
                                }

                                statuses.push(item);
                            }
                        }

                        if(data.length > 0)
                        {
                            // Again, a workaround for the fact that id can be different to its string representation
                            if(data.length == 1)
                            {
                                if(data[0].id_str > mostRecentId)
                                    log('Fetched ' + data.length + ' tweets');
                            }
                            else
                            {
                                log('Fetched ' + data.length + ' tweets');
                            }

                            currentPage++;
                            getTweets();
                        }
                        else
                        {
                            retrieveUserData(userIds);
                        }

                    },
                    error: function (opts, status)
                    {   
                        log('There was an error retrieving the data, please reload this page to try again');
                    }
                });
            }
            
            $(function(){
               outputPanel = $('#output');
               // $('#import').bind('click', getTweets);
               getTweets();
            });
            
        </script>
	</head>
	<body>
		<!-- <p><input type="button" value="IMPORT TWEETS" id="import" /></p> -->
        <div id="output"></div>
	</body>
</html>