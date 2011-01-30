<?php

function link_callback($matches) {
    
    // '<a href="\1">\1</a>'
    
    return '<a href="' . $matches[1] . '">' . ((strlen($matches[1]) > 35) ? substr($matches[1], 0, 32) . '...' : $matches[1]) . '</a>';
    
}

function hashtag_callback($matches) {
    
    return '<a href="http://search.twitter.com/search?q=' . urlencode($matches[1]) . '">' . $matches[1] . '</a>';
    
}

function username_callback($matches) {
    
    return '@<a class="twitter-anywhere-user" href="http://twitter.com/' . urlencode($matches[2]) . '">' . $matches[2] . '</a>';

}

function linkify($text) {
    
    $output = preg_replace_callback('/(https?:\/\/\S+)/', "link_callback", $text);
    $output = preg_replace_callback('/(\#\S+)/', "hashtag_callback", $output);
    $output = preg_replace_callback('/(@([a-zA-Z0-9_]+))/', "username_callback", $output);
    return $output;

}

?>