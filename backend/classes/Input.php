<?php

class Input {
	// Handle both $_GET and $_POST in one function
	public static function get($item) {
		if(isset($_POST[$item])){
			// Escape special characters and decode url encoded string from input
			return trim(escape(decodeurl($_POST[$item])));
		}
		else if (isset($_GET[$item])){
			// Escape special characters in string from input
			return trim(escape($_GET[$item]));
		}
		return '';
	}
}

?>
