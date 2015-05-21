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
		API_v1 ($chunks);
		break;
		
	default:
		http_response_code (400); // Bad Request
		$resp['status'] = STATUS_FAIL;
		$resp['data'] = array ('error' => 'Bad API version.');
}

reply();

// End of processing.

// API handlers

function API_v1_BAD_REQUEST()
{
	global $resp;
	http_response_code (400); // Bad Request
	$resp['status'] = STATUS_FAIL;
	$resp['data'] = array ('error' => 'Invalid action.');
}

function API_v1 (&$chunks)
{
	global $resp;
	$resp['version'] = 'v1';
	
	switch ($chunks[0])
	{
		case 'system': {
			switch ($chunks[1])
			{
				case 'test':
					$resp['status'] = STATUS_SUCCESS;
					break;
				default: API_v1_BAD_REQUEST();
			}
		}
		default: API_v1_BAD_REQUEST();
	}
}
?>