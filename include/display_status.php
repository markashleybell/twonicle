<?php

function displayStatus($status, $basepath) {
    
    return '<div class="tweet">' .
               '<a class="avatar-link twitter-anywhere-user" href="http://twitter.com/' . $status->screenname . '"><img alt="@' . $status->screenname . '" title="" src="' . $status->profileimage . '" /></a> ' .
               '<div class="details">' .
                   '<span class="username"><a class="twitter-anywhere-user" href="http://twitter.com/' . $status->screenname . '">' . $status->screenname . '</a></span> ' .
                   '<span class="realname">' . $status->realname . '</span>' .
                   '<span class="text">' . $status->text . ((isset($status->rank)) ? ' (Rank ' . $status->rank . ')' : '') . '</span>' . 
                   '<span class="date">' .
                       '<a href="http://twitter.com/' . $status->screenname . '/status/' . (($status->rtid != 0) ? $status->rtid : $status->id) . '">' . date('d/m/Y H:i', $status->time) . '</a>' .
                       (($status->inreplytoid != 0) ? ' in reply to <a href="http://twitter.com/' . $status->inreplytouser . '/status/' . $status->inreplytoid . '">this</a>' : '') .
                   '</span>' .
                   '<span class="pick"><a id="pick-' . $status->id . '"' . (($status->pick == 0) ? '' : ' class="picked"') . ' href="#">' . (($status->pick == 0) ? 'NOT' : 'PICK') . '</a></span>' . 
               '</div>' .
           '</div>';
           
}

function displayStatusPlainText($status, $basepath) {
    
    return '<div class="tweet">' .
               '<div class="details">' .
                   '<span class="text">' . $status->text . '</span>' . 
                   '<span class="date">' .
                       '<a href="http://twitter.com/' . $status->screenname . '/status/' . (($status->rtid != 0) ? $status->rtid : $status->id) . '">' . date('d/m/Y H:i', $status->time) . '</a>' .
                       (($status->inreplytoid != 0) ? ' in reply to <a href="http://twitter.com/' . $status->inreplytouser . '/status/' . $status->inreplytoid . '">this</a>' : '') .
                   '</span>' .
               '</div>' .
           '</div>';
           
}

?>