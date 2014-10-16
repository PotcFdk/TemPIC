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

@include_once('config.php');
require_once('../includes/configcheck.php');
require_once('../includes/baseconfig.php');

function createZipFile ($name, $files) {
	$zip = new ZipArchive;
	$zip->open($name, ZipArchive::CREATE);
	foreach ($files as $filen => $file) {
		$zip->addFile($file, $filen);
	}
	$zip->close();
	return $zip;
}

function isImage($file) {
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $file);
	finfo_close($finfo);

	return ($mime == 'image/gif')
		|| ($mime == 'image/jpeg')
		|| ($mime == 'image/jpg')
		|| ($mime == 'image/pjpeg')
		|| ($mime == 'image/x-png')
		|| ($mime == 'image/png')
		|| ($mime == 'image/svg+xml');
}

function rearrange($arr) {
	foreach ($arr as $key => $all) {
		foreach($all as $i => $val) {
			$new[$i][$key] = $val;    
		}    
	}
	return $new;
}

if (is_uploaded_file($_FILES['file']['tmp_name'][0])) {
	session_start();
	$files = array();
	$file_paths = array();
	$lifetime = $_POST['lifetime'];

	// Because PHP structures the array in a retarded format
	$_FILES['file'] = rearrange($_FILES['file']);

	foreach ($_FILES['file'] as $file) {
		$files[$file['name']] = array();

		if ($file['size'] <= $SIZE_LIMIT) {
			$fileinfo = pathinfo($file['name']);

			if (!empty($fileinfo['extension']) && in_array($fileinfo['extension'], $DISALLOWED_EXTS)) {
				$files[$file['name']]['error'] = 'Disallowed file type!';
			} elseif ($file['error'] > 0) {
				$files[$file['name']]['error'] = 'Return Code: ' . $file['error'];
			} elseif (!isset($lifetime) || !array_key_exists($lifetime, $LIFETIMES)) {
				$files[$file['name']]['error'] = 'Invalid or no file lifetime specified.';
			} else {
				$path_destination = $PATH_UPLOAD . '/' . $lifetime;
				
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

					if (!empty($URL_UPLOAD)) // $URL_UPLOAD lifetime / uid / filename
						$link = $URL_UPLOAD . $lifetime . '/' . $uid . '/' . $fileinfo['basename'];
					else // $URL_BASE / upload / lifetime / uid / filename
						$link = $URL_BASE . '/' . $path;

					$files[$file['name']]['link'] = $link;
					$files[$file['name']]['image'] = isImage($path);
					if (!empty($fileinfo['extension']))
						$files[$file['name']]['extension'] = $fileinfo['extension'];
				}
			}
		} else {
			$files[$file['name']]['error'] = 'File too large!';
		}
	}
	
	// generate album
	
	if (isset($lifetime) && array_key_exists($lifetime, $LIFETIMES)) {
		$album_data = array();
		$album_data['name'] = $_POST['album_name'];
		$album_data['files'] = array();

		foreach ($files as $filen => $file) {
			if (!isset($file['error'])) { // no errors, file is ok
				$album_data['files'][$filen] = $file;
			}
		}
		if (count($album_data['files']) >= 1) {
			$album_bare_id = substr(md5(time()),12);
			
			$path_destination = $PATH_ALBUM.'/'.$lifetime;
			if (!file_exists($path_destination)) {
				mkdir($path_destination, 0775);
				chmod($path_destination, 0775);
			}
			
			file_put_contents($path_destination.'/'.$album_bare_id.'.txt', serialize($album_data));
			
			$_SESSION['album_lifetime'] = $lifetime;
			$_SESSION['album_id'] = $lifetime.':'.$album_bare_id;
			
			// create album zip file
			if (isset($ENABLE_ALBUM_ZIP) && $ENABLE_ALBUM_ZIP && count($album_data['files']) >= 2) {
				$zip_path = $path_destination.'/'.$album_bare_id.'.zip';
				$zip_file = createZipFile($zip_path, $file_paths);
			}
		}
	}

	$_SESSION['files'] = $files;
}

if (isset($_POST['nojs'])) {
	if (!empty($_SESSION['album_id']))
		header('Location: '.$URL_BASE.'/index_nojs.php?album='.$_SESSION['album_id']);
	else
		header('Location: '. $URL_BASE.'/index_nojs.php');
} elseif (isset($_POST['ajax'])) {
	if (!empty($_SESSION['album_id']))
		echo ($_SESSION['album_id']);
} else {
	if (!empty($_SESSION['album_id']))
		header('Location: '.$URL_BASE);
	else
		header('Location: '.$URL_BASE.'?album='.$_SESSION['album_id']);
}
?>
