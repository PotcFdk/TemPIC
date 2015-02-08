<?php /*
	TemPIC - Copyright 2014 - 2015; PotcFdk, ukgamer

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
	require_once ($PATH_INCLUDES . '/helpers.php');
	
	$time  = time ();
	
	//
	
	function safe_scandir($dir) {
		return array_diff(scandir ($dir), array('.', '..'));
	}

	// cleanup uploaded files
	
	echo "FILE CLEANUP\n";
	
	foreach ($LIFETIMES as $lifetime => $data) {
		$basedir = $PATH_TEMPIC . '/' . $PATH_UPLOAD . '/' . $lifetime;
		echo '* scanning basedir: ' . $basedir . "\n";
		if (is_dir ($basedir)) {
			$subdirs = safe_scandir ($basedir);
			foreach ($subdirs as $subdir) {
				$subdir = $basedir . '/' . $subdir;
				if (is_dir ($subdir)) {
					$files = safe_scandir($subdir);
					$empty = true;
					foreach ($files as $file) {
						$file = $subdir . '/' . $file;
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
			$files = safe_scandir($basedir);
			$empty = true;
			foreach ($files as $file) {
				$file = $basedir . '/' . $file;
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