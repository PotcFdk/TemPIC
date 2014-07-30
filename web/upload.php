<?php
@include_once('config.php');
require_once('../includes/configcheck.php');

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
	$lifetime = $_POST['lifetime'];

	// Because PHP structures the array in a retarded format
	$_FILES['file'] = rearrange($_FILES['file']);

	foreach ($_FILES['file'] as $file) {
		$files[$file['name']] = array();

		if ($file['size'] <= $SIZE_LIMIT) {
			$fileinfo = pathinfo($file['name']);
			$extension = $fileinfo['extension'];

			if (in_array($extension, $DISALLOWED_EXTS)) {
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

					$link = $URL_BASE . '/' . $path;

					$files[$file['name']]['link'] = $link;
					$files[$file['name']]['image'] = isImage($path);
					$files[$file['name']]['extension'] = $extension;
				}
			}
		} else {
			$files[$file['name']]['error'] = 'File too large!';
		}
	}

	$_SESSION['files'] = $files;
}

header('Location: ' . $URL_BASE);
