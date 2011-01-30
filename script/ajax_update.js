function displayStatus(json) {
    
    return '<div class="tweet">' +
                '<a class="avatar-link twitter-anywhere-user" href="http://twitter.com/' + json.screenname + '"><img alt="@' + json.screenname + '" title="" src="' + json.profileimage + '" /></a> ' +
                '<div class="details">' +
                    '<span class="username"><a class="twitter-anywhere-user" href="http://twitter.com/' + json.screenname + '">' + json.screenname + '</a></span> ' +
                    '<span class="realname">' + json.realname + '</span>' +
                    '<span class="text">' + json.text + '</span>' +
                    '<span class="date">' +
                        '<a href="http://twitter.com/' + json.screenname + '/status/' + ((json.rtid != 0) ? json.rtid : json.id) + '">' + json.time + '</a>' +
                        ((json.inreplytoid != 0) ? ' in reply to <a href="http://twitter.com/' + json.inreplytouser + '/status/' + json.inreplytoid + '">this</a>' : '') +
                     '</span>' +
                '</div>' +
            '</div>';
            
}

function drawYearNavItem(m, y, count, current)
{
    var now = new Date();
    var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    
    return '<li' + ((current) ? ' class="current-month"' : '') + '>' +
           '<a href="/' + appBaseUrl + y + '/' + m + '">' +
           '<span>' + months[(parseInt(m, 10) - 1)] + ' ' + y +
           ' (<span class="tweet-count" id="count-' + m + '-' + y + '">' + count + '</span>)</span></a></li>';
}

updateCompleteCallback = function() {
    
    $.ajax({
        url: '/' + appBaseUrl + 'get_new.php',
        data: { since: mostRecentId },
        dataType: 'json',
        type: 'POST',
        success: function(data, status, request) {
            
            var link = '', dt, months = [];
            
            var container = $('.tweets:first');
            
            // Get the month/year/day of the page we are currently looking at from an ID
            // Indexes: 0 = 'tweets', 1 = Year, 2 = Month, 3 = Day
            var current = (container.attr('id') == 'recent') ? ['recent'] : container.attr('id').split('-');
            
            $.each(data, function(i, item) {
                
                // Get year/month/day of the status
                // Indexes: 0 = Day, 1 = Month, 2 = Year
                dt = item.time.substring(0, item.time.indexOf(' ')).split('/');
                link = dt[1] + '-' + dt[2];
                
                // Build up a 'dictionary' of months along with the number of new statuses for each
                if(typeof months[link] == 'undefined')
                    months[link] = 0;
                    
                months[link]++;
                
                if(current[0] == 'recent')
                {
                    // If we're on the recent/home page we don't care about the date, just prepend them all
                    container.prepend(displayStatus(item));
                }
                else
                {
                    // If year and month are the same
                    if(dt[2] == current[1] && dt[1] == current[2])
                        if(current[3] == '00' || dt[0] == current[3]) // If we're on a month page, we don't care about the day (which will be '00')
                            container.prepend(displayStatus(item));
                }
            });
            
            // Loop through the months
            for(var m in months)
            {
                var e = $('#count-' + m);
                var count = parseInt(e.html(), 10);
                
                // If there isn't already a year nav list item for this month, add one
                if(e.length == 0)
                {
                    var d = m.split('-');
                    $('#year-navigation').prepend(drawYearNavItem(d[0], d[1], months[m], false));
                }
                else // just update the tweet count of the existing nav item
                {
                    e.html((count + months[m]));
                }
            }
            
            $('#update-status').remove();
            
        },
        error: function(request, status, error) {
            // Show an error indicator
        }
    });
    
};

$(function(){
    
    $('body').append('<div id="update-status">Fetching new tweets <img src="/' + appBaseUrl + 'img/site/ajax-loader.gif" width="16" height="16" alt="Loading" /></div>');
    retrieveStatusData();
    
});