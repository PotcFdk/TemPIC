<?php
require_once('../includes/auth-provider-interface.php');

class HttpBasicAuth implements IAuthProvider
{
	public function isAuthed()
	{
		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="A TemPIC Instance"');
			header('HTTP/1.0 401 Unauthorized');
			return false;
		}
		else
		{
			return true;
		}
	}
	
	public function getAuthLocation()
	{
		return false;
	}
	
	public function deAuth()
	{}
}
?>
