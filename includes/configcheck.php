<?php
/*
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

function req_is_array   ($o) { return isset ($o) && is_array    ($o); }
function req_is_bool    ($o) { return isset ($o) && is_bool     ($o); }
function req_is_numeric ($o) { return isset ($o) && is_numeric  ($o); }
function req_is_string  ($o) { return isset ($o) && is_string   ($o); }

function opt_is_string  ($o) { return (isset ($o) ? is_string ($o) : true); }

	if (// instance config
		   !req_is_string  ($INSTANCE_NAME)
		|| !opt_is_string  ($INSTANCE_DESCRIPTION)
		|| !req_is_string  ($URL_BASE)
		|| !req_is_array   ($DISALLOWED_EXTS)
		|| !req_is_numeric ($SIZE_LIMIT)
		|| !req_is_array   ($LIFETIMES)
		|| !req_is_string  ($DEFAULT_LIFETIME)
		|| !req_is_bool    ($ENABLE_ALBUM_ZIP)
		
		// advanced
		|| !req_is_bool    ($ENABLE_THUMBNAILS)
		|| !req_is_bool    ($THUMBNAIL_USE_IMAGICK)
		|| !req_is_bool    ($THUMBNAIL_ENABLE_ANIMATED)
		|| !req_is_numeric ($THUMBNAIL_MAX_RES)
		|| !req_is_numeric ($THUMBNAIL_MAX_ANIMATED_RES)
		
		// extras
		|| !opt_is_string  ($CSS_OVERRIDE)
		|| !opt_is_string  ($URL_UPLOAD)
		|| !opt_is_string  ($URL_ALBUM)
		
		// internal config
		|| !req_is_string  ($PATH_ALBUM)
		|| !req_is_string  ($PATH_UPLOAD)
		|| !req_is_string  ($PATH_JOBQUEUE)
		|| !req_is_string  ($THUMBNAIL_PREFIX)
		|| !req_is_numeric ($MAX_ALBUM_NAME_LENGTH)
		|| !req_is_numeric ($MAX_ALBUM_DESCRIPTION_LENGTH)
	) {
		include ('error.php');
		exit ();
	}
?>