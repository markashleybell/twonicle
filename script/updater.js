var userIds = [];
var currentPage = 1;
var statuses = [];
var users = [];

var requestTimeout = 5000; // Time out Twitter API calls after 5 seconds

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

// Check if an id is newer than the most recent saved
function isIdNew(id)
{
    if(id.length > mostRecentId.length) return true;
    if(id.length < mostRecentId.length) return false;
    return (id > mostRecentId);
}

function handleGenericError(request, status, error)
{
    log('There was an error communicating with the server');
}

function handleJSONPError(opts, status)
{
    // If we got here, there's been an error retrieving the API feed; we need to reset the
    // processing flag so the update can be re-run         
    $.ajax({
        url: 'reset_processing_flag.php',
        dataType: 'json',
        type: 'POST',
        success: function(data, status, request) { 
            log('There was an error retrieving the data, please reload this page to try again');
        },
        error: handleGenericError
    });
}

function handleAjaxError(request, status, error)
{
    // If we got here, there's been an error retrieving the API feed; we need to reset the
    // processing flag so the update can be re-run         
    $.ajax({
        url: 'reset_processing_flag.php',
        dataType: 'json',
        type: 'POST',
        success: function(data, status, request) { 
            log('There was an error saving the data, please reload this page to try again');
        },
        error: handleGenericError
    });
}

function retrieveStatusData()
{
    $.jsonp({
        url: 'http://api.twitter.com/1/statuses/user_timeline.json?callback=?',
        data: { screen_name: twitterUserName, include_rts: 1, trim_user: 1, count: 200, since_id: mostRecentId, page: currentPage },
        timeout: requestTimeout,
        success: function(data, status, request) { 
            
            for(var i = 0; i<data.length; i++)
            {
                var item = data[i];
                var user = item.user;

                // For some reason, 'id_str' can actually be slightly different than 'id' for the same tweet(!?!?)
                // This means that the since_id parameter of the user_timeline call doesn't tie up with what we have stored
                // We need to manually check our max id against the 'id_str' parameter to avoid odd duplicates here and there
                if(isIdNew(item.id_str))
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
                // We only need to test the last element because that's the only one that might be duped
                if(isIdNew(data[data.length - 1].id_str))
                    log('Fetched ' + data.length + ' tweets');
                else
                    log('Fetched ' + (data.length - 1) + ' tweets');

                currentPage++;
                retrieveStatusData();
            }
            else
            {
                retrieveUserData(userIds);
            }

        },
        error: handleJSONPError
    });
}

function saveStatus()
{
    if(statuses.length > 0)
    {
        var s = statuses.pop();

        $.ajax({
            url: 'save_status.php',
            data: { status: JSON.stringify(s) },
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
            error: handleJSONPError
        });
    }
    else
    {
        // Loop through the statuses and users and insert them all into the db
        saveStatus();
    }
}

function saveUser()
{
    if(users.length > 0)
    {
        var u = users.pop();

        $.ajax({
            url: 'save_user.php',
            data: { user: JSON.stringify(u) },
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

// Store a timestamp so we can tell how long it's been since the archive was updated
function updateLastUpdateTime()
{
    $.ajax({
        url: 'update_lastupdate.php',
        dataType: 'json',
        type: 'POST',
        success: function(data, status, request) { 
            log('Update finished at ' + data.lastupdate);
            log('<a href="/' + appBaseUrl + '">Return to archive</a>');
        },
        error: handleGenericError
    });
}

$(function(){
    outputPanel = $('#output');
    retrieveStatusData();
});
