<?php /*
	TemPIC - Copyright (c) PotcFdk, 2014 - 2015

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
require_once('../includes/config.php');
require_once('../includes/configcheck.php');
require_once('../includes/thumbnails.php');
require_once('../includes/checksums.php');

function createZipFile ($name, $files) {
	$zip = new ZipArchive;
	$zip->open($name, ZipArchive::CREATE);
	foreach ($files as $filen => $file) {
		$zip->addFile($file, $filen);
	}
	$zip->close();
	return $zip;
}

function mb_pathinfo($filepath) {
	preg_match ('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $filepath, $m);
	if (!empty($m[1])) $ret['dirname']   = $m[1];
	if (!empty($m[2])) $ret['basename']  = $m[2];
	if (!empty($m[5])) $ret['extension'] = $m[5];
	if (!empty($m[3])) $ret['filename']  = $m[3];
	return isset ($ret) ? $ret : array ();
}

function hasThumbnailSupport ($file) {
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$err_lvl = error_reporting(E_ALL & ~E_WARNING);
	$mime = finfo_file($finfo, $file);
	error_reporting($err_lvl);
	finfo_close($finfo);

	return ($mime == 'image/gif')
		|| ($mime == 'image/jpeg')
		|| ($mime == 'image/jpg')
		|| ($mime == 'image/pjpeg')
		|| ($mime == 'image/x-png')
		|| ($mime == 'image/png');
}

function rearrange ($arr) {
	foreach ($arr as $key => $all) {
		foreach($all as $i => $val) {
			$new[$i][$key] = $val;    
		}    
	}
	return $new;
}

if (!empty($_FILES) && is_uploaded_file($_FILES['file']['tmp_name'][0])) {
	if (!empty($_POST['lifetime']))
	{
		if (is_string ($_POST['lifetime']) && array_key_exists ($_POST['lifetime'], $LIFETIMES)) // OK
			$lifetime = $_POST['lifetime'];
		elseif ($_POST['lifetime'] == 'default')
		{
			if (!empty(DEFAULT_LIFETIME)) // Using the default lifetime.
				$lifetime = DEFAULT_LIFETIME;
			else // Client wanted the default lifetime, but DEFAULT_LIFETIME is empty.
			{
				http_response_code (400); // Bad Request
				exit;
			}
		}
		else // Unknown lifetime.
		{
			http_response_code (400); // Bad Request
			exit;
		}
	}
	elseif (!empty(DEFAULT_LIFETIME)) // Try the default lifetime.
		$lifetime = DEFAULT_LIFETIME;
	else
	{
		http_response_code (400); // Bad Request
		exit;
	}
	
	if (!empty($_POST['album_name']))
		$album_name = trim($_POST['album_name']);

	if (!empty($_POST['album_description']))
		$album_description = trim($_POST['album_description']);

	// unset if empty

	if (empty($album_name)) unset($album_name);
	if (empty($album_description)) unset($album_description);

	// Because PHP structures the array in a retarded format
	$_FILES['file'] = rearrange($_FILES['file']);

	$files = array();
	$file_paths = array();
	
	foreach ($_FILES['file'] as $file) {
		$files[$file['name']] = array();

		if ($file['size'] <= SIZE_LIMIT) {
			$fileinfo = mb_pathinfo($file['name']);

			if (!empty($fileinfo['extension']) && in_array($fileinfo['extension'], $DISALLOWED_EXTS)) {
				$files[$file['name']]['error'] = 'Disallowed file type!';
			} elseif (empty ($fileinfo['basename'])) {
				$files[$file['name']]['error'] = 'No file name!';
			} elseif ($file['error'] > 0) {
				$files[$file['name']]['error'] = 'Return Code: ' . $file['error'];
			} else {
				$path_destination = PATH_UPLOAD . '/' . $lifetime;
				
				if (!file_exists($path_destination)) {
					mkdir($path_destination, 0775);
					chmod($path_destination, 0775);
				}
				
				$offset = rand(0,20);
				$uid = substr(md5(time().mt_rand()), $offset, 12);
				
				$path_destination = $path_destination . '/' . $uid;
				
				if (!file_exists($path_destination)) {
					mkdir($path_destination, 0775);
					chmod($path_destination, 0775);
				}
				
				$path = $path_destination . '/' . $fileinfo['basename'];
				
				if (file_exists($path)) {
					$files[$file['name']]['error'] = $path . ' already exists.';
				} else {
					move_uploaded_file($file['tmp_name'], $path);
					chmod($path, 0664);
					$file_paths[$file['name']] = $path;

					if (defined ('URL_UPLOAD') && !empty (URL_UPLOAD)) // URL_UPLOAD lifetime / uid / filename
						$file_url_base = URL_UPLOAD . $lifetime . '/' . $uid . '/';
					else // URL_BASE / (upload / lifetime / uid) / filename
						$file_url_base = URL_BASE . '/' . $path_destination . '/';
						
					$file_url = $file_url_base . rawurlencode($fileinfo['basename']);
						
					$files[$file['name']]['url'] = $file_url;
					$files[$file['name']]['internal_path'] = $path;
					$files[$file['name']]['image'] = isImage($path);
					if ($files[$file['name']]['image']) // if isImage()
						$files[$file['name']]['animated'] = isAnimated($path);
					if (!empty($fileinfo['extension']))
						$files[$file['name']]['extension'] = $fileinfo['extension'];
					
					if (ENABLE_THUMBNAILS && hasThumbnailSupport($path)) {
						if (createThumbnailJob($path, $path_destination . '/' . THUMBNAIL_PREFIX . $fileinfo['basename']))
						{
							$files[$file['name']]['thumbnail'] = $file_url_base . THUMBNAIL_PREFIX . rawurlencode($fileinfo['basename']);
						}
					}
				}
			}
		} else {
			$files[$file['name']]['error'] = 'File too large!';
		}
	}
	
	// generate album
	
	$album_data = array();
	
	if (isset($album_name)) {
		$album_data['name'] = $album_name;
	
		if (mb_strlen($album_data['name']) > MAX_ALBUM_NAME_LENGTH)
			$album_data['name'] = mb_substr($album_data['name'], 0, MAX_ALBUM_NAME_LENGTH);
	}
	
	if (isset($album_description)) {
		$album_data['description'] = $album_description;
		
		if (mb_strlen($album_data['description']) > MAX_ALBUM_DESCRIPTION_LENGTH)
			$album_data['description'] = mb_substr($album_data['description'], 0, MAX_ALBUM_DESCRIPTION_LENGTH);
	}
	
	$album_data['files'] = array();

	// Move contents from $files to the final $album_data
	// Ignore all files with 'error', for now.
	
	foreach ($files as $filen => $file) {
		if (!isset($file['error'])) { // no errors, file is ok
			$album_data['files'][$filen] = $file;
		}
	}
	
	if (count($album_data['files']) >= 1) {
		$album_bare_id = substr(md5(time()),12);
		
		$path_destination = PATH_ALBUM.'/'.$lifetime;
		if (!file_exists($path_destination)) {
			mkdir($path_destination, 0775);
			chmod($path_destination, 0775);
		}
		
		$path = $path_destination.'/'.$album_bare_id.'.txt';
		
		createChecksumJob($path);
		
		// create album zip file
		if (ENABLE_ALBUM_ZIP && count($album_data['files']) >= 2) {
			$zip_path = $path_destination.'/'.$album_bare_id.'.zip';
			$zip_file = createZipFile($zip_path, $file_paths);
			
			$album_data['zip'] = URL_BASE . '/' . $zip_path;
		}
		
		file_put_contents($path, serialize($album_data));
		chmod($path, 0775);
		
		$album_id = $lifetime.':'.$album_bare_id;
	}
}

if (isset($_POST['nojs'])) {
	if (!empty($album_id))
		header('Location: '.URL_BASE.'/index_nojs.php?album='.$album_id);
	else
		header('Location: '. URL_BASE.'/index_nojs.php');
} elseif (isset($_POST['ajax'])) {
	if (!empty($album_id))
		echo ($album_id);
} else {
	if (!empty($album_id))
		header('Location: '.URL_BASE.'?album='.$album_id);
	else
		header('Location: '.URL_BASE);
}
?>
