<?php
	require_once ('config.php');
	require_once ($PATH_TEMPIC . '/config.php');
	
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
?>