<?php
/*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2020

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
*/

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
		if (defined ('URL_ALBUM'))
			return URL_ALBUM.$album_id;
		else
			return URL_BASE.'/?album='.$album_id;
	}
?>