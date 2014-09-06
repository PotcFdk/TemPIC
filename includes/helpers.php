<?php
	function strip_album_id ($string) {
	  preg_match_all ("/[^0-9^a-z^:]/", $string, $matches);
	  foreach ($matches[0] as $value) {
		$string = str_replace($value, "", $string);
	  }
	  return $string;
	}
?>