<?php /*
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

	@include_once('config.php');
	require_once (PATH_INCLUDES . '/config.php');
	require_once (PATH_TEMPIC   . '/config.php');
	require_once (PATH_INCLUDES . '/helpers.php');
	require_once (PATH_INCLUDES . '/thumbnails.php');

	//

	function safe_scandir($dir) {
		return preg_grep('/^([^.])/', array_diff(scandir ($dir), array('.', '..')));
	}

	// check and generate missing animated thumbnails

	echo "Finding and reading job entries...\n";

	$jobs = array();

	if (is_dir (PATH_JOBQUEUE_THUMBNAILS)) {
		$files = safe_scandir(PATH_JOBQUEUE_THUMBNAILS);
		foreach ($files as $file) {
			$file = PATH_JOBQUEUE_THUMBNAILS . '/' . $file;
			if (is_file ($file)) {
				echo ' - found: ' . $file;
				$job = unserialize(file_get_contents($file));
				if (!empty($job) && !empty($job['src']) && !empty($job['dest'])) {
					$jobs[$file] = $job;
				}
				echo "\n";
			}
		}
	}

	chdir (PATH_TEMPIC);
	echo "Generating thumbnails...\n";

	foreach ($jobs as $job_file => $job)
	{
		unlink ($job_file);
		if (file_exists($job['src'])) {
			createThumbnail ($job['src'], $job['dest'], true);
			echo " * Generated new thumbnail, by job $job_file\n";
		}
		else
			echo " ! Couldn't generate thumbnail: $job_file: src missing\n";
	}
?>