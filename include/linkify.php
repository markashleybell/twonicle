<?php

function linkify($text)
{
	return preg_replace('/(https?:\/\/\S+)/', '<a href="\1">\1</a>', $text);

}

?>