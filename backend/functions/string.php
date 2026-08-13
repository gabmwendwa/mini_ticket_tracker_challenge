<?php

// Escape special characters
function escape($string) {
	return htmlentities($string, ENT_QUOTES, 'UTF-8');
}

// Decode URL encoded string
function decodeurl($string) {
	return urldecode($string);
}

?>
