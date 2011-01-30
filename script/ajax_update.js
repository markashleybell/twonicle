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

function drawYearNavItem(m, y, count)
{
    var now = new Date();
    var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    
    return '<li' + ((m == (now.getMonth() + 1) && y == now.getFullYear()) ? ' class="current-month"' : '') + '>' +
           '<a href="/' + appBaseUrl + y + '/' + m + '">' +
           '<span>' + months[(parseInt(m, 10) - 1)] + ' ' + y +
           ' (<span class="tweet-count" id="count-' + m + '-' + y + '">' + count + '</span>)</span></a></li>';
}

updateCompleteCallback = function() {
    
    $.ajax({
        url: 'get_new.php',
        data: { since: mostRecentId },
        dataType: 'json',
        type: 'POST',
        success: function(data, status, request) {
            
            var link = '', months = [];
            
            var container = $('#tweets');
            
            $.each(data, function(i, item) {
                
                link = item.time.substring(item.time.indexOf('/') + 1, item.time.indexOf(' ')).replace(/\//gi, '-');
                
                if(typeof months[link] == 'undefined')
                    months[link] = 0;
                    
                months[link]++;
                
                container.prepend(displayStatus(item));
            });
            
            for(var m in months)
            {
                var e = $('#count-' + m);
                var count = parseInt(e.html(), 10);
                
                if(e.length == 0)
                {
                    var d = m.split('-');
                    $('#year-navigation').prepend(drawYearNavItem(d[0], d[1], months[m]));
                }
                else
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