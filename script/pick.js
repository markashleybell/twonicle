$(function(){
    
   $('.pick a').live('click', function() {
    
        var link = $(this);
        var _id = link.attr('id').split('-')[1];
        var picked = 0;
        
        if(!link.hasClass('picked'))
        {
            link.addClass('picked');
            picked = 1;
        }
        else
        {
            link.removeClass('picked');
            picked = 0;
        }
        
        $.ajax({
            url: '/' + appBaseUrl + 'mark_pick.php',
            data: { id: _id, pick: picked },
            dataType: 'json',
            type: 'POST',
            success: function(data, status, request) {
               
                link.html((picked == 1) ? 'PICK' : 'NOT')
                
            },
            error: function(request, status, error) {
                // Show an error indicator
            }
        });
        
        return false;
    
   });
   
});