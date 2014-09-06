<?php
	function strip_album_id ($string) {
	  preg_match_all ("/[^0-9^a-z^:]/", $string, $matches);
	  foreach ($matches[0] as $value) {
		$string = str_replace($value, "", $string);
	  }
	  return $string;
	}
	
	function get_album_url ($album_id = "") {
		global $URL_BASE;
		global $URL_ALBUM;
		if (empty($URL_ALBUM)) {
			return $URL_BASE.'/?album='.$album_id; 
		} else {
			return $URL_ALBUM.$album_id; 
		}
	}
?>