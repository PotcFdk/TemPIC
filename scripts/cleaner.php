<?php /*
	TemPIC - Copyright 2014 PotcFdk, ukgamer

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
	require_once ('config.php');
	require_once ($PATH_TEMPIC . '/config.php');
	require_once ($PATH_INCLUDES . '/baseconfig.php');
	
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
	
	$time  = time ();

	// cleanup uploaded files
	
	echo "FILE CLEANUP\n";
	
	foreach ($LIFETIMES as $lifetime => $data) {
		$basedir = $PATH_TEMPIC . '/' . $PATH_UPLOAD . '/' . $lifetime;
		echo '* scanning basedir: ' . $basedir . "\n";
		if (is_dir ($basedir)) {
			$subdirs = glob ($basedir . '/*');
			foreach ($subdirs as $subdir) {
				if (is_dir ($subdir)) {
					$files = glob ($subdir . '/*');
					$empty = true;
					foreach ($files as $file) {
						if (is_file ($file)) {
							$remaining = $data['time']*60 - ($time - filemtime ($file));
							echo ' - found: ' . $file;
							if ($remaining < 0) {
								echo ' (deleted)';
								unlink($file);
							}
							else {
								echo ' (remaining: ' . formatTime ($remaining) . ')';
								$empty = false;
							}
							echo "\n";
						}
					}
					if ($empty) {
						rmdir ($subdir);
					}
				}
			}
		}
	}
	
	// cleanup album metadata
	
	echo "ALBUM CLEANUP\n";
	
	foreach ($LIFETIMES as $lifetime => $data) {
		$basedir = $PATH_TEMPIC . '/' . $PATH_ALBUM . '/' . $lifetime;
		echo '* scanning basedir: ' . $basedir . "\n";
		if (is_dir ($basedir)) {
			$files = glob ($basedir . '/*');
			$empty = true;
			foreach ($files as $file) {
				if (is_file ($file)) {
					$remaining = $data['time']*60 - ($time - filemtime ($file));
					echo ' - found: ' . $file;
					if ($remaining < 0) {
						echo ' (deleted)';
						unlink($file);
					}
					else {
						echo ' (remaining: ' . formatTime ($remaining) . ')';
						$empty = false;
					}
					echo "\n";
				}
			}
			if ($empty) {
				rmdir ($basedir);
			}
		}
	}
?>