<?php
class urlCleaner {
	/*
	 * Substitute special characters from string for url
	 */
	static function encodeUrl($s){
        $s = str_replace(" ", "_32", $s);
        $s = str_replace("+", "_43", $s);
		return str_replace(':', '_58', $s);
	}
	 /*
	  * Substitute encoded characters from url with special characters
	  */
	static function decodeUrl($s){
		$s = str_replace("_32", " ", $s);
        $s = str_replace("_43", "+", $s);
		return str_replace("_58", ":", $s);
	}
}
?>
