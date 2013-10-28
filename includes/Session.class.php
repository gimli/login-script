<?php
if(!defined('IN_SCRIPT')){
   die("External access denied");
}

class SessionManager
{
	protected $aolProxies = array('195.93.', '205.188', '198.81.', '207.200', '202.67.', '64.12.9');
	static function sessionStart($name, $limit = 0, $path = '/', $domain = null, $secure = null)
	{
		session_name($name . '_Session');
		$https = isset($secure) ? $secure : isset($_SERVER['HTTPS']);

		session_set_cookie_params($limit, $path, $domain, $https, true);
		session_start();

		if(self::validateSession())
		{
			if(!self::preventHijacking())
			{
				$_SESSION = array();
				$_SESSION['IPaddress'] = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
							? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
				$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
				self::regenerateSession();

			// Give a 5% chance of the session id changing on any request
			}elseif(rand(1, 100) <= 5){
				self::regenerateSession();
			}
		}else{
			$_SESSION = array();
			session_destroy();
			session_start();
		}
	}

	static function regenerateSession()
	{
		if(isset($_SESSION['OBSOLETE']) || $_SESSION['OBSOLETE'] == true)
			return;

		$_SESSION['OBSOLETE'] = true;
		$_SESSION['EXPIRES'] = time() + 10;

		session_regenerate_id(false);

		$newSession = session_id();
		session_write_close();

		session_id($newSession);
		session_start();

		unset($_SESSION['OBSOLETE']);
		unset($_SESSION['EXPIRES']);
	}

	static protected function validateSession()
	{
		if( isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES']) )
			return false;

		if(isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time())
			return false;

		return true;
	}

	static protected function preventHijacking()
	{
		if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent']))
			return false;


		if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']
			&& !( strpos($_SESSION['userAgent'], ÔTridentÕ) !== false
				&& strpos($_SERVER['HTTP_USER_AGENT'], ÔTridentÕ) !== false))
		{
			return false;
		}

		$sessionIpSegment = substr($_SESSION['IPaddress'], 0, 7);

		$remoteIpHeader = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
			? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

		$remoteIpSegment = substr($remoteIpHeader, 0, 7);

		if($_SESSION['IPaddress'] != $remoteIpHeader
			&& !(in_array($sessionIpSegment, $this->aolProxies) && in_array($remoteIpSegment, $this->aolProxies)))
		{
			return false;
		}

		if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT'])
			return false;

		return true;
	}
}

?>
