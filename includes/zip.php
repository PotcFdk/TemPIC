<?php /*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2016

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

function createZipFile ($file_paths, $target_path) {
	mkdir(dirname($target_path), 0775, true);
	$zip = new ZipArchive;
	$zip->open($target_path, ZipArchive::CREATE);
	foreach ($file_paths as $filen => $file) {
		$zip->addFile($file, $filen);
	}
	$zip->close();
	return $zip;
}

function createZipJob ($file_paths, $target_path) {
	$job_entry = array('files' => $file_paths, 'ziptarget' => $target_path);
	$offset = rand(0,20);
	$uid = substr (md5(time().mt_rand()), $offset, 12);
	file_put_contents (PATH_JOBQUEUE_ZIP.'/'.$uid.'.job', serialize ($job_entry));
	return true;
}
?>
