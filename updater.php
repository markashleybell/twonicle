<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<script src="http://platform.twitter.com/anywhere.js?id=idyTlCoEihlkLSC0ezJ1Q&amp;v=1"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript">
            
            var mostRecentId = 14671866010533887; //100; // We'll get this from db eventually
            var userIds = []; // We'll get these from the db eventually too, so we don't re-fetch users we already have data for every time
            var currentPage = 1;
            var statuses = [];
            var users = [];

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
            
            function saveStatus()
            {
                if(statuses.length > 0)
                {
                    var s = statuses.pop();

                    $.ajax({
                        url: 'save_status.php',
                        data: { status: s },
                        dataType: 'json',
                        type: 'POST',
                        success: function(data, status, request) { 
                            // $('#output').append('<p>Got user ' + data.screen_name + '</p>');
                            saveStatus(); // Save the next tweet
                        },
                        error: function(request, status, error)
                        {
                            console.log(status);
                            console.log(error);
                        }
                    });
                }
                else
                {
                    console.log('Saved tweets');
                    
                    // Kick off the user save
                }
            }

            function saveUser()
            {
            }

            function retrieveUserData()
            {
                if(userIds.length > 0)
                {
                    var id = userIds.pop();
                    console.log('get ' + id);
                    
                    $.ajax({
                        url: 'http://api.twitter.com/1/users/show.json?callback=?',
                        data: { user_id: id },
                        dataType: 'jsonp',
                        type: 'GET',
                        success: function(data, status, request) { 
                            $('#output').append('<p>Got user ' + data.screen_name + '</p>');
                            users.push(data);
                            retrieveUserData();
                        },
                        error: function(request, status, error)
                        {
                            console.log(status);
                            console.log(error);
                        }
                    });
                    
                }
                else
                {
                    console.log(statuses);
                    console.log(users);

                    // Loop through the statuses and users and insert them all into the db
                    saveStatus();
                }
            }
            
            function getTweets()
            {
                $.ajax({
                    url: 'http://api.twitter.com/1/statuses/user_timeline.json?callback=?',
                    data: { screen_name: 'markeebee', include_rts: 1, trim_user: 1, count: 200, since_id: mostRecentId, page: currentPage },
                    dataType: 'jsonp',
                    type: 'GET',
                    success: function(data, status, request) { 
                        $('#output').append('<p>Got ' + data.length + ' tweets</p>');
                        
                        var i;

                        for(i = 0; i<data.length; i++)
                        {
                            var item = data[i];
                            var user = item.user;

                            if(item.retweeted_status)
                                user = item.retweeted_status.user;

                            if(!findUser(userIds, user.id))
                            {
                                addUser(user);
                            }

                            statuses.push(item);
                        }

                        if(data.length > 0)
                        {
                            currentPage++;
                            getTweets();
                        }
                        else
                        {
                            retrieveUserData(userIds);
                        }
                    },
                    error: function(request, status, error)
                    {
                        console.log(status);
                        console.log(error);
                    }
                });
            }
            
            $(function(){
               
               $('#import').bind('click', getTweets);
               
            });
            
        </script>
	</head>
	<body>
		<p><input type="button" value="IMPORT TWEETS" id="import" /></p>
        <div id="output"></div>
	</body>
</html>