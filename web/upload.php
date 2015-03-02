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

function createThumbnail ($src, $dest, $new_width) {
	$type = exif_imagetype($src);
	
	switch ($type) {
        case 1:
            $image = imagecreatefromgif($src);
        break;
        case 2:
			$image = imagecreatefromjpeg($src);
        break;
        case 3:
            $image = imagecreatefrompng($src);
        break;
		default:
			return false;
    }
	
	$width = imagesx($image);
	$height = imagesy($image);
	
	$new_height = floor($height * ($new_width / $width));
	
	$target = imagecreatetruecolor($new_width, $new_height);
	
	imagecopyresampled($target, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	imagejpeg($target, $dest);
	return true;
}

function isImage ($file) {
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
		|| ($mime == 'image/png')
		|| ($mime == 'image/svg+xml');
}

function mb_pathinfo($filepath) {
	preg_match ('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $filepath, $m);
	if (!empty($m[1])) $ret['dirname']   = $m[1];
	if (!empty($m[2])) $ret['basename']  = $m[2];
	if (!empty($m[5])) $ret['extension'] = $m[5];
	if (!empty($m[3])) $ret['filename']  = $m[3];
	return $ret;
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
	session_start();
	$files = array();
	$file_paths = array();
	
	if (!empty($_POST['lifetime']))
		$lifetime = $_POST['lifetime'];
	elseif (!empty($DEFAULT_LIFETIME))
		$lifetime = $DEFAULT_LIFETIME;
	
	if (!empty($_POST['album_name']))
		$album_name = trim($_POST['album_name']);

	if (!empty($_POST['album_description']))
		$album_description = trim($_POST['album_description']);

	// unset if empty

	if (empty($album_name)) unset($album_name);
	if (empty($album_description)) unset($album_description);

	// Because PHP structures the array in a retarded format
	$_FILES['file'] = rearrange($_FILES['file']);

	foreach ($_FILES['file'] as $file) {
		$files[$file['name']] = array();

		if ($file['size'] <= $SIZE_LIMIT) {
			$fileinfo = mb_pathinfo($file['name']);

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
						$link_base = $URL_UPLOAD . $lifetime . '/' . $uid . '/';
					else // $URL_BASE / (upload / lifetime / uid) / filename
						$link_base = $URL_BASE . '/' . $path_destination . '/';
						
					$link = $link_base . rawurlencode($fileinfo['basename']);
						
					$files[$file['name']]['link'] = $link;
					$files[$file['name']]['image'] = isImage($path);
					if (!empty($fileinfo['extension']))
						$files[$file['name']]['extension'] = $fileinfo['extension'];
					$files[$file['name']]['crc'] = hash_file('crc32b', $path);
					$files[$file['name']]['md5'] = md5_file($path);
					$files[$file['name']]['sha1'] = sha1_file($path);
					
					if (hasThumbnailSupport($path)) {
						if (createThumbnail($path, $path_destination . '/' . $THUMBNAIL_PREFIX . $fileinfo['filename'] . '.jpg', 500))
							$files[$file['name']]['thumbnail'] = $link_base . $THUMBNAIL_PREFIX . rawurlencode($fileinfo['filename'] . '.jpg');
					}
				}
			}
		} else {
			$files[$file['name']]['error'] = 'File too large!';
		}
	}
	
	// generate album
	
	if (isset($lifetime) && array_key_exists($lifetime, $LIFETIMES)) {
		$album_data = array();
		
		if (isset($album_name)) {
			$album_data['name'] = $album_name;
		
			if (mb_strlen($album_data['name']) > $MAX_ALBUM_NAME_LENGTH)
				$album_data['name'] = mb_substr($album_data['name'], 0, $MAX_ALBUM_NAME_LENGTH);
		}
		
		if (isset($album_description)) {
			$album_data['description'] = $album_description;
			
			if (mb_strlen($album_data['description']) > $MAX_ALBUM_DESCRIPTION_LENGTH)
				$album_data['description'] = mb_substr($album_data['description'], 0, $MAX_ALBUM_DESCRIPTION_LENGTH);
		}
		
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
			
			if (!empty($album_data['name']))
				$_SESSION['album_name'] = $album_data['name'];
			if (!empty($album_data['description']))
				$_SESSION['album_description'] = $album_data['description'];
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
		header('Location: '.$URL_BASE.'?album='.$_SESSION['album_id']);
	else
		header('Location: '.$URL_BASE);
}
?>
