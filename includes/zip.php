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

function zip ($name, $files) {
	$zip = new ZipArchive;
	$zip->open($name, ZipArchive::CREATE);
	foreach ($files as $filen => $file) {
		$zip->addFile($file, $filen);
	}
	$zip->close();
	return $zip;
}

function createZipFile ($album_path, $target_path) {
	$album_data = unserialize(file_get_contents($album_path));
	zip($target_path, $album_data['files']);
}

function createZipJob ($album_path, $target_path) {
	$job_entry = array('albumdata' => $album_path, 'ziptarget' => $target_path);
	$offset = rand(0,20);
	$uid = substr (md5(time().mt_rand()), $offset, 12);
	file_put_contents (PATH_JOBQUEUE_ZIP.'/'.$uid.'.job', serialize ($job_entry));
	return true;
}
?>
