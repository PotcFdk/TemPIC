<?php
require_once('../includes/auth-provider-interface.php');

class HttpDigestAuth implements IAuthProvider
{
	private $realm = 'A TemPIC Instance';
	
	private function digestParse($raw)
	{
		$req_akeys = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1,
			'username'=>1, 'uri'=>1, 'response'=>1);
		$matches = array();
		$key = implode('|', array_keys ($req_akeys));

		preg_match_all('@('.$key.')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@',
			$raw, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$matches[$match[1]] = $match[3] ? $match[3] : $match[4];
			unset($req_akeys[$match[1]]);
		}

		return $req_akeys ? false : $matches;
	}
	
	public function isAuthed()
	{
		if (!isset($_SERVER['PHP_AUTH_DIGEST']))
			return false;
		else
		{
			if (!($data = $this->digestParse($_SERVER['PHP_AUTH_DIGEST'])))
				return false;

			$A1 = md5($data['username'].':'.$this->realm.':'. /* TODO: USER PASSWORD HERE */);
			$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
			$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce']
				.':'.$data['qop'].':'.$A2);

			return $data['response'] === $valid_response;
		}
	}
	
	public function doAuth()
	{
		header('WWW-Authenticate: Digest realm="'.$this->realm.'",qop="auth",nonce="'.uniqid()
				.'",opaque="'.md5($this->realm).'"');
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
