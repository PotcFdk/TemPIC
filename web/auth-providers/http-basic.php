<?php
require_once('../includes/auth-provider-interface.php');

class HttpBasicAuth implements IAuthProvider
{
	public function isAuthed()
	{
		return false; // TODO
	}
	
	public function doAuth()
	{
		header('WWW-Authenticate: Basic realm="A TemPIC Instance"');
		header('HTTP/1.0 401 Unauthorized');
	}
	
	public function getAuthLocation()
	{
		return false;
	}
	
	public function deAuth()
	{}
}
?>
