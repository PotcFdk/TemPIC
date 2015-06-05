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

@include_once('config.php');
require_once('../includes/config.php');

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

// Shared internal logic
function _API_V1_GET_ALBUM_DATA ($album_id)
{
	global $LIFETIMES;
	
	$album_id = strip_album_id ($album_id);
	if (empty($album_id)) return array (false, "Invalid album ID");
	
	$_a = explode (":", $album_id, 2);
	if (!empty ($_a[0]))
		$album_lifetime = $_a[0];
	if (!empty ($_a[1]))
		$album_hash = $_a[1];
	
	if (!empty ($LIFETIMES[$album_lifetime]) && file_exists (PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt'))
		return array (true, unserialize(file_get_contents(PATH_ALBUM.'/'.$album_lifetime.'/'.$album_hash.'.txt')));
	else
		return array (false, "Album can not be found");
}
// -

function API_V1_BAD_REQUEST ($reason = NULL)
{
	global $resp;
	http_response_code (400); // Bad Request
	$resp['status'] = STATUS_FAIL;
	$resp['data'] = array ('error' => !empty ($reason) ? $reason : 'Invalid action.');
}

function API_V1_ALBUM_INFO ($album_id)
{
	global $resp;
	
	$adata_resp = _API_V1_GET_ALBUM_DATA ($album_id);
	if ($adata_resp[0])
		$album_data = $adata_resp[1];
	else
		return API_V1_BAD_REQUEST ($adata_resp[1]);
	
	$resp['status'] = STATUS_SUCCESS;
	$resp['data'] = array ('albums' => array ($album_id => $album_data));
}

function API_V1_ALBUM_FILES ($album_id, $filename, $action = NULL)
{
	global $resp;
	
	$adata_resp = _API_V1_GET_ALBUM_DATA ($album_id);
	if ($adata_resp[0])
		$album_data = $adata_resp[1];
	else
		return API_V1_BAD_REQUEST ($adata_resp[1]);
	
	if (!array_key_exists ('files', $album_data) || !array_key_exists ($filename, $album_data['files']))
	{
		API_V1_BAD_REQUEST ('File not found in album.');
		return;
	}
	
	$file_info = $album_data['files'][$filename];
	
	switch ($action)
	{
		case 'info':
			$resp['status'] = STATUS_SUCCESS;
			$resp['data'] = array ($filename => $file_info);
			break;
		case 'url':
			$resp['status'] = STATUS_SUCCESS;
			$resp['data'] = array ($filename => array ('url' => $file_info['url']));
			break;
		default:
			API_V1_BAD_REQUEST ('Invalid albums/files action.');
	}
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
						case 'files':
							if (!empty ($chunks[3]))
							{
								if (!empty ($chunks[4]))
									API_V1_ALBUM_FILES ($chunks[1], $chunks[3], $chunks[4]);
								else
									API_V1_ALBUM_FILES ($chunks[1], $chunks[3]);
							}
							else
								API_V1_BAD_REQUEST();
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