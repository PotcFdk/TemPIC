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

require_once('../includes/baseconfig.php');
@include_once('config.php');

require_once('../includes/helpers.php');

define ('STATUS_SUCCESS', 'success');
define ('STATUS_FAIL',    'fail');
define ('STATUS_ERROR',   'error');

$resp = array (
	'status' => STATUS_ERROR
);

function reply ()
{
	global $resp;
	header ('Content-Type: application/json');
	echo json_encode ($resp);
	exit;
}

if (empty ($_SERVER['QUERY_STRING']))
{
	http_response_code (400); // Bad Request
	$resp['status'] = STATUS_FAIL;
	$resp['data'] = array ('error' => 'Empty request.');
	reply();
}

// Parse request.

$output = array ();
$chunks = explode ('/', $_SERVER['QUERY_STRING']);

// / Parse request.

switch ($chunks[0])
{
	case 'v1':
		array_shift ($chunks);
		API_V1 ($chunks);
		break;
		
	default:
		http_response_code (400); // Bad Request
		$resp['status'] = STATUS_FAIL;
		$resp['data'] = array ('error' => 'Bad API version.');
}

reply();

// End of processing.

// API handlers

function API_V1_BAD_REQUEST ($reason = NULL)
{
	global $resp;
	http_response_code (400); // Bad Request
	$resp['status'] = STATUS_FAIL;
	$resp['data'] = array ('error' => !empty ($reason) ? $reason : 'Invalid action.');
}

function API_V1_ALBUM_INFO ($album_id)
{
	global $LIFETIMES, $PATH_ALBUM, $resp;
	//http_response_code (501); // Not Implemented
	
	$album_id = strip_album_id($album_id);
	if (empty($album_id)) return API_V1_BAD_REQUEST ("Invalid album ID");
	
	$_a = explode (":", $album_id, 2);
	if (!empty ($_a[0]))
		$album_lifetime = $_a[0];
	if (!empty ($_a[1]))
		$album_hash = $_a[1];
	
	if (!empty ($LIFETIMES[$album_lifetime]) && file_exists ($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'))
		$album_data = unserialize(file_get_contents($PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'));
	else
		return API_V1_BAD_REQUEST ("Album can not be found");
	
	$resp['status'] = STATUS_SUCCESS;
	$resp['data'] = array ('rawdata', $album_data);
}

function API_V1 (&$chunks)
{
	global $resp;
	$resp['version'] = 'v1';
	
	if (!empty ($chunks[0]))
	{
		switch ($chunks[0])
		{
			case 'system': {
				if (!empty ($chunks[1]))
				{
					switch ($chunks[1])
					{
						case 'test':
							$resp['status'] = STATUS_SUCCESS;
							break;
						default: API_V1_BAD_REQUEST();
					}
				}
				else
					API_V1_BAD_REQUEST();
				break;
			}
			case 'albums': {
				if (!empty ($chunks[1]) && !empty ($chunks[2]))
				{
					switch ($chunks[2])
					{
						case 'info':
							API_V1_ALBUM_INFO ($chunks[1]);
							break;
						default: API_V1_BAD_REQUEST();
					}
				}
				else API_V1_BAD_REQUEST();
				break;
			}
			default: API_V1_BAD_REQUEST();
		}
	}
	else API_V1_BAD_REQUEST();
}
?>