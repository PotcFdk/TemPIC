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

	if (// instance config
		   !(defined ('INSTANCE_NAME')                &&  is_string  (INSTANCE_NAME))
		||  (defined ('INSTANCE_DESCRIPTION')         && !is_string  (INSTANCE_DESCRIPTION))
		|| !(defined ('URL_BASE')                     &&  is_string  (URL_BASE))
		|| !(isset   ($DISALLOWED_EXTS)               &&  is_array   ($DISALLOWED_EXTS))
		|| !(defined ('SIZE_LIMIT')                   &&  is_numeric (SIZE_LIMIT))
		|| !(isset   ($LIFETIMES)                     &&  is_array   ($LIFETIMES))
		|| !(defined ('DEFAULT_LIFETIME')             &&  is_string  (DEFAULT_LIFETIME))
		|| !(defined ('ENABLE_ALBUM_ZIP')             &&  is_bool    (ENABLE_ALBUM_ZIP))

		// advanced
		|| !(defined ('ENABLE_THUMBNAILS')            && is_bool    (ENABLE_THUMBNAILS))
		|| !(defined ('THUMBNAIL_USE_IMAGICK')        && is_bool    (THUMBNAIL_USE_IMAGICK))
		|| !(defined ('THUMBNAIL_ENABLE_ANIMATED')    && is_bool    (THUMBNAIL_ENABLE_ANIMATED))
		|| !(defined ('THUMBNAIL_MAX_RES')            && is_numeric (THUMBNAIL_MAX_RES))
		|| !(defined ('THUMBNAIL_MAX_ANIMATED_RES')   && is_numeric (THUMBNAIL_MAX_ANIMATED_RES))

		// extras
		||  (defined ('CSS_OVERRIDE')                 && !is_string (CSS_OVERRIDE))
		||  (defined ('URL_UPLOAD')                   && !is_string (URL_UPLOAD))
		||  (defined ('URL_ALBUM')                    && !is_string (URL_ALBUM))

		// internal config
		|| !(defined ('PATH_ALBUM')                   && is_string  (PATH_ALBUM))
		|| !(defined ('PATH_UPLOAD')                  && is_string  (PATH_UPLOAD))
		|| !(defined ('PATH_JOBQUEUE_THUMBNAILS')     && is_string  (PATH_JOBQUEUE_THUMBNAILS))
		|| !(defined ('PATH_JOBQUEUE_CHECKSUMS')      && is_string  (PATH_JOBQUEUE_CHECKSUMS))
		|| !(defined ('PATH_JOBQUEUE_ZIP')            && is_string  (PATH_JOBQUEUE_ZIP))
		|| !(defined ('THUMBNAIL_PREFIX')             && is_string  (THUMBNAIL_PREFIX))
		|| !(defined ('MAX_ALBUM_NAME_LENGTH')        && is_numeric (MAX_ALBUM_NAME_LENGTH))
		|| !(defined ('MAX_ALBUM_DESCRIPTION_LENGTH') && is_numeric (MAX_ALBUM_DESCRIPTION_LENGTH))
	) {
		include ('error.php');
		exit ();
	}
?>
