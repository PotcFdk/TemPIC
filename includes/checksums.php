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

function createChecksums ($path) {
	$albumdata = unserialize(file_get_contents($path));
	foreach ($albumdata['files'] as $filen => $filed)
	{
		$filed['checksums'] = array(
			'crc'  => hash_file('crc32b', $filed['internal_path']),
			'md5'  => md5_file($filed['internal_path']),
			'sha1' => sha1_file($filed['internal_path'])
		);
	}
	return true;
}

function createChecksumJob ($path) {
	$job_entry = array('albumdata' => $path);
	$offset = rand(0,20);
	$uid = substr (md5(time().mt_rand()), $offset, 12);
	file_put_contents (PATH_JOBQUEUE_CHECKSUMS.'/'.$uid.'.job', serialize ($job_entry));
	return true;
}
?>