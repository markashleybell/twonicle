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

updateCompleteCallback = function() {
    
    $.ajax({
        url: 'get_new.php',
        data: { since: mostRecentId },
        dataType: 'json',
        type: 'POST',
        success: function(data, status, request) {
            
            var container = $('#tweets');
            
            $.each(data, function(i, item) {
                container.prepend(displayStatus(item));
            });
            
            $('#update-status').remove();
            
        },
        error: function(request, status, error) {
            // Show an error indicator
        }
    });
    
};

$(function(){
    
    $('body').append('<div id="update-status">Getting new tweets...</div>');
    retrieveStatusData();
    
});