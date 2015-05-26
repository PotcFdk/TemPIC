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
	$limit = $THUMBNAIL_MAX_RES;
	
	switch ($type) {
        case IMAGETYPE_GIF:
			$ext = '.gif';
            $image = imagecreatefromgif($src);
			break;
        case IMAGETYPE_JPEG:
			$ext = '.jpg';
			$image = imagecreatefromjpeg($src);
			break;
        case IMAGETYPE_PNG:
			$ext = '.png';
            $image = imagecreatefrompng($src);
			break;
		default:
			return false;
    }
	
	$dest = $dest.$ext;
	
	$width = imagesx($image);
	$height = imagesy($image);
	
	$new_geometry = getThumbnailTargetSize ($width, $height, $limit);
	$new_width = $new_geometry['width'];
	$new_height = $new_geometry['height'];
	
	$target = imagecreatetruecolor($new_width, $new_height);
	imagealphablending($target, false);
	imagesavealpha($target, true);
	$transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
	imagefilledrectangle($target, 0, 0, $nWidth, $nHeight, $transparent);
	
	imagecopyresampled($target, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	
	switch ($type) {
        case IMAGETYPE_GIF:
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
	
	return $ext;
}

function createThumbnailImagick ($src, $dest) {
	global $THUMBNAIL_MAX_ANIMATED_RES, $THUMBNAIL_MAX_RES;
	
	if (exif_imagetype($src) == IMAGETYPE_GIF)
		$ext = '.gif';
	elseif (exif_imagetype($src) == IMAGETYPE_PNG)
		$ext = '.png';
	else
		$ext = '.jpg';
	
	$dest = $dest.$ext;
	
	$image = new Imagick($src);
	
	$limit = $image->getNumberImages() > 1 ? $THUMBNAIL_MAX_ANIMATED_RES : $THUMBNAIL_MAX_RES;

	$geometry = $image->getImageGeometry();
	$new_geometry = getThumbnailTargetSize($geometry['width'], $geometry['height'], $limit);
	
	$image = $image->coalesceImages();

	foreach ($image as $frame) {
		$frame->thumbnailImage($new_geometry['width'], $new_geometry['height']);
		$frame->setImagePage($new_geometry['width'], $new_geometry['height'], 0, 0);
	}

	$image = $image->deconstructImages();
	$image->writeImages($dest, true);
	return $ext;
}

function createThumbnail ($src, $dest) {
	if (extension_loaded('imagick'))
		return createThumbnailImagick ($src, $dest);
	else
		return createThumbnailNative  ($src, $dest);
}
?>