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
	echo json_encode ($resp);
	exit;
}

if (empty ($_REQUEST['action']) || !is_string ($_REQUEST['action']))
{
	http_response_code (400); // Bad Request
	$resp['status'] = STATUS_FAIL;
	$resp['data'] = array ('error' => 'Missing action.');
	reply();
}

$action = $_REQUEST['action'];

if ($action == "test")
{
	$resp['status'] = 'success';
}
else
{
	http_response_code (400); // Bad Request
	$resp['status'] = 'fail';
	$resp['data'] = array ('error' => 'Invalid action.');
}

reply();
?>