<?php
	/*
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

	// Internal configuration.
	// Don't edit this file.
	// You can override these settings in the config.php in your web directory.

	if (!defined('PATH_ALBUM'))
		define ('PATH_ALBUM', 'album');

	if (!defined('PATH_UPLOAD'))
		define ('PATH_UPLOAD', 'upload');

	if (!defined('PATH_JOBQUEUE_THUMBNAILS'))
		define ('PATH_JOBQUEUE', '../internal/thumbnail_queue'))

	if (!defined('PATH_JOBQUEUE_CHECKSUMS'))
		define ('PATH_JOBQUEUE', '../internal/checksum_queue');

	if (!defined('THUMBNAIL_PREFIX'))
		define ('THUMBNAIL_PREFIX', '_thumb_');

	if (!defined('MAX_ALBUM_NAME_LENGTH'))
		define ('MAX_ALBUM_NAME_LENGTH', 150);

	if (!defined('MAX_ALBUM_DESCRIPTION_LENGTH'))
		define ('MAX_ALBUM_DESCRIPTION_LENGTH', 5000);
?>