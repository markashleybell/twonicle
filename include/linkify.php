<?php

function linkify_callback($matches) {
    
    return '<a href="http://search.twitter.com/search?q=' . urlencode($matches[1]) . '">' . $matches[1] . '</a>';
    
}

function username_callback($matches) {
    
    return '@<a class="twitter-anywhere-user" href="http://twitter.com/' . urlencode($matches[2]) . '">' . $matches[2] . '</a>';

}

function linkify($text) {
    
    $output = preg_replace('/(https?:\/\/\S+)/', '<a href="\1">\1</a>', $text);
    $output = preg_replace_callback('/(\#\S+)/', "linkify_callback", $output);
    $output = preg_replace_callback('/(@([a-zA-Z0-9_]+))/', "username_callback", $output);
    return $output;

}

?>