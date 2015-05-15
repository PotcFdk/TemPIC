<?php
	function formatTime ($time) {
		$ret = "";
		$first = true;
		
		$hours = intval (intval ($time) / 3600);
		if ($hours > 0) {
			if ($first)
				$first = false;
			else
				$ret .= ' ';
			$ret .= $hours . ' hours';
		}
		
		$minutes = bcmod ((intval ($time) / 60), 60);
		if ($hours > 0 || $minutes > 0) {
			if ($first)
				$first = false;
			else
				$ret .= ' ';
			$ret .= $minutes . ' minutes';
		}
		
		if ($first)
			$first = false;
		else
			$ret .= ' ';
		
		$time = bcmod (intval ($time), 60);
		$ret .= $time . ' seconds';

		return $ret;
	}
	
	function strip_album_id ($string) {
	  preg_match_all ("/[^0-9^a-z^:]/", $string, $matches);
	  foreach ($matches[0] as $value) {
		$string = str_replace ($value, "", $string);
	  }
	  return $string;
	}
	
	function get_album_url ($album_id = "") {
		global $URL_BASE;
		global $URL_ALBUM;
		if (empty ($URL_ALBUM)) {
			return $URL_BASE.'/?album='.$album_id; 
		} else {
			return $URL_ALBUM.$album_id; 
		}
	}
?>