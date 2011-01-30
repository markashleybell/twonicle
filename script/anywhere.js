twttr.anywhere(function (T) {
    
    T(".tweets .username, .tweets .text .twitter-anywhere-user").hovercards({ linkify: false });
    
    T(".tweets a.avatar-link").hovercards({
        username: function(node) {
            return node.alt;
        }
    });
    
});