<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<script src="http://platform.twitter.com/anywhere.js?id=idyTlCoEihlkLSC0ezJ1Q&amp;v=1"></script>
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
        <script type="text/javascript">
            
            var mostRecentId = 100; // We'll get this from db eventually
            var currentPage = 1;
            var statuses = [];
            var users = [];

            function addUser(user)
            {
                $.ajax({
                    url: 'http://api.twitter.com/1/users/show.json?callback=?',
                    data: { user_id: user.id },
                    dataType: 'jsonp',
                    type: 'GET',
                    async: false,
                    success: function(data, status, request) { 
                        users.push(data);
                        users.sort(function(a, b) {
                            return a.id - b.id;
                        });
                    },
                    error: function(request, status, error)
                    {
                        console.log(status);
                        console.log(error);
                    }
                });
            }

            function findUser(users, id)
            {
                if(users.length < 1) return false;

                var high = users.length - 1;
                var low = 0;
                                
                while(low <= high)
                {
                    mid = parseInt((low + high) / 2);
                    if(users[mid].id == id) return true;
                    else if(users[mid].id > id) high = (mid - 1); // Search lower values
                    else if(users[mid].id < id) low = (mid + 1); // Search upper values
                }
                
                return false; // Value not found
            }
            
            function getTweets()
            {
                $.ajax({
                    url: 'http://api.twitter.com/1/statuses/user_timeline.json?callback=?',
                    data: { screen_name: 'markeebee', include_rts: 1, trim_user: 1, count: 10, since_id: mostRecentId, page: currentPage },
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

                            if(!findUser(users, user.id))
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
                            console.log(statuses.length);
                            console.log(users);
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