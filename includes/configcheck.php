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

	if (   !isset ($INSTANCE_NAME)
		|| !isset ($URL_BASE)
		|| !isset ($DISALLOWED_EXTS)
		|| !isset ($SIZE_LIMIT)
		|| !isset ($LIFETIMES)
		|| !isset ($PATH_ALBUM)
		|| !isset ($PATH_UPLOAD)
		
		|| !isset ($THUMBNAIL_PREFIX)
		|| !isset ($THUMBNAIL_MAX_RES)
		
		|| !isset ($MAX_ALBUM_NAME_LENGTH)
		|| !isset ($MAX_ALBUM_DESCRIPTION_LENGTH)
	) {
		include ('error.php');
		exit ();
	}
?>