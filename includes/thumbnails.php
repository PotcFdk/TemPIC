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

function getThumbnailTargetSize ($src_x, $src_y, $target_max) {
	$src_max = max ($src_x, $src_y);
	if ($src_max <= $target_max) return false;
	
	$scale_factor = $target_max / $src_max;
	return array (
		'width' => $scale_factor * $src_x,
		'height' => $scale_factor * $src_y
	);
}

function createThumbnailNative ($src, $dest) {
	$type = exif_imagetype($src);
	$limit = THUMBNAIL_MAX_RES;
	
	$is_animated = false;
	
	switch ($type) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($src);
			$is_animated = isAnimated($src);
			break;
        case IMAGETYPE_JPEG:
			$image = imagecreatefromjpeg($src);
			break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($src);
			break;
		default:
			return false;
    }
	
	$width = imagesx($image);
	$height = imagesy($image);
	
	$new_geometry = getThumbnailTargetSize ($width, $height, $limit);
	
	if ($new_geometry)
	{
		$new_width = $new_geometry['width'];
		$new_height = $new_geometry['height'];
	}
	else
	{
		$new_width = $width;
		$new_height = $height;
	}
	
	$target = imagecreatetruecolor($new_width, $new_height);
	imagealphablending($target, false);
	imagesavealpha($target, true);
	$transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
	imagefilledrectangle($target, 0, 0, $new_width, $new_height, $transparent);
	
	imagecopyresampled($target, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	
	switch ($type) {
        case IMAGETYPE_GIF:
			if ($is_animated && $new_width >= 100 && $new_height >= 100)
			{
				$overlay = imagecreatefrompng('img/info_animated.png');
				imagealphablending($target, true);
				imagecopy($target, $overlay, 0, 0, 0, 0, imagesx($overlay), imagesy($overlay));
			}
			imagegif($target, $dest);
			break;
        case IMAGETYPE_JPEG:
			imagejpeg($target, $dest);
			break;
        case IMAGETYPE_PNG:
			imagepng($target, $dest);
			break;
		default:
			return false;
    }
	
	return true;
}

function createThumbnailImagick ($src, $dest) {
	$image = new Imagick($src);
	
	$limit = $image->getNumberImages() > 1 ? THUMBNAIL_MAX_ANIMATED_RES : THUMBNAIL_MAX_RES;

	$image = $image->coalesceImages();
	
	$geometry = $image->getImageGeometry();
	$new_geometry = getThumbnailTargetSize($geometry['width'], $geometry['height'], $limit) ?: $geometry;

	if (THUMBNAIL_ENABLE_ANIMATED)
	{
		foreach ($image as $frame) {
			$frame->thumbnailImage($new_geometry['width'], $new_geometry['height']);
			$frame->setImagePage($new_geometry['width'], $new_geometry['height'], 0, 0);
		}
		$image = $image->deconstructImages();
		$image->writeImages($dest, true);
	}
	else
	{
		$is_animated = $image->getNumberImages() > 1;
		
		iterator_to_array($image)[0]->thumbnailImage($new_geometry['width'], $new_geometry['height']);
		if ($is_animated && $new_geometry['width'] >= 100 && $new_geometry['height'] >= 100)
			$image->compositeImage(new Imagick('img/info_animated.png'), imagick::COMPOSITE_DEFAULT, 0, 0);
		iterator_to_array($image)[0]->writeImage($dest);
	}
	
	return true;
}

function createThumbnail ($src, $dest) {
	if (THUMBNAIL_USE_IMAGICK && extension_loaded('imagick'))
		$ret = createThumbnailImagick ($src, $dest);
	else
		$ret = createThumbnailNative  ($src, $dest);
	
	if ($ret && file_exists ($dest))
	{
		if (filesize ($dest) >= filesize ($src)) // It's not worth saving.
		{
			return unlink ($dest) && symlink (basename ($src), $dest);
		}
		return true;
	}
	return false;
}

function createThumbnailJob ($src, $dest) {
	$job_entry = array ('src' => $src, 'dest' => $dest);
	$offset = rand(0,20);
	$uid = substr (md5(time().mt_rand()), $offset, 12);
	file_put_contents (PATH_JOBQUEUE_THUMBNAILS.'/'.$uid.'.job', serialize ($job_entry));
	return true;
}

function getMimeType ($file) {
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$err_lvl = error_reporting(E_ALL & ~E_WARNING);
	$mime = finfo_file($finfo, $file);
	error_reporting($err_lvl);
	finfo_close($finfo);
	return $mime;
}

function isImage ($file) {
	$mime = getMimeType ($file);

	return ($mime == 'image/gif')
		|| ($mime == 'image/jpeg')
		|| ($mime == 'image/jpg')
		|| ($mime == 'image/pjpeg')
		|| ($mime == 'image/x-png')
		|| ($mime == 'image/png')
		|| ($mime == 'image/svg+xml');
}

function isAnimated ($file) { // Thanks to ZeBadger ( http://it.php.net/manual/en/function.imagecreatefromgif.php#59787 )
	$filecontents=file_get_contents($file);

	$str_loc = 0;
	$count = 0;
	while ($count < 2)
	{
		$where1 = strpos ($filecontents, "\x00\x21\xF9\x04", $str_loc);
		if ($where1 === false)
			break;
		else
		{
			$str_loc = $where1 + 1;
			$where2 = strpos ($filecontents, "\x00\x2C", $str_loc);
			if ($where2 === false)
				break;
			else
			{
				if ($where1 + 8 == $where2)
					$count ++;
				$str_loc = $where2 + 1;
			}
		}
	}
	return $count > 1;
}
?>