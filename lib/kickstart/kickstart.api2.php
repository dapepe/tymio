<?php

/**
 * Basic application API class
 *
 * The API's run() function should be designed to perform the authentication before executing the function
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @version 1.0 (2010-09-07)
 * @copyright Copyright (c) 2011, Groupion GmbH & Co. KG
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
abstract class KickstartAPI2 extends RESTserver2 {
	/** @var array Array containing all API tasks which require no authentication */
	public $auth_exceptions = array();
	/** @var locator */
	public $locator;
	/** @var bool Date format for the token generation */
	public $dateformat='dmY';
	/** @var string The application salt */
	public $salt='RANDOMKEYGOESHERE';

	/**
	 * @param string $strSalt The token salt
	 */
	public function __construct($strSalt=false) {
		parent::__construct();

		if ($strSalt)
			$this -> salt = $strSalt;

		// Add the gettime function (for token generation) to the action list
		$this -> actions['time'] = array('ANY', 'time');
	}

	public function do_time() {
		return gmdate('U');
	}

	/**
	 * Only runs a function, if a valid authentication token has been sent.
	 */
	public function run() {
		if (!isset($_REQUEST['do']))
			throw new Exception('No task specified.');

		if (!in_array($_REQUEST['do'], $this -> auth_exceptions) && !$this -> auth())
			throw new Exception('Authentication required.');

		$this -> runJSON();
	}

	/**
	 * The authentication method specifies if an API task may be executed or not.
	 * This method may be different in subordinate classes
	 *
	 * @return bool
	 */
	public function auth() {
		return true;
	}

	/**
	 * Validates a Cookie Token
	 *
	 * @param string $strUsername
	 * @param int $intDay The number of days the cookie should be valid
	 * @param string $strSalt Token salt
	 */
	public function createCookieToken($strUsername, $intDays=14, $strSalt=false) {
		return cryptastic::encrypt(array(
			'username' => $strUsername,
			'expiration' => time() + 8600 * $intDays
		), ($strSalt ? $strSalt : $this -> salt));
	}

	/**
	 * Validates a Cookie Token
	 *
	 * @param string $strCookieToken
	 * @param string $strSalt Token salt
	 * @return array|bool The username or FALSE
	 */
	public function validateCookieToken($strCookieToken, $strSalt=false) {
		$t = cryptastic::decrypt($strCookieToken, ($strSalt ? $strSalt : $this -> salt));

		if (isset($t['username']) && isset($t['expiration']) && $t['expiration'] > time())
			return $t['username'];

		return false;
	}

	/**
	 * Authenticates a user by using the session cookie
	 *
	 * @param unknown_type $strCookieToken
	 * @return bool
	 */
	public function authCookieToken($strCookieToken) {
		if ($username = self::validateCookieToken($strCookieToken)) {
			self::setUserSession(array('username' => $user));
			return true;
		}

		return false;
	}

	/**
	 * Validates a token
	 *
	 * @param string $strToken The client token
	 * @param string $strSalt Token salt
	 * @return bool
	 */
	public function validateToken($strToken, $strSalt=false) {
		return self::createToken($strSalt) == $strToken;
	}

	/**
	 * Creates a token
	 *
	 * @param string $strSalt Token salt
	 * @return string
	 */
	public function createToken($strSalt=false) {
		return md5(date($this -> dateformat).' '.($strSalt ? $strSalt : $this -> salt));
	}

	/**
	 * Authenticates an API call by token
	 *
	 * @return bool
	 */
	public function authToken() {
		if (isset($_REQUEST['token']))
			return self::validateToken($_REQUEST['token']);

		return false;
	}


	/**
	 * Sets the user session information
	 *
	 * @param array $arrUser The user details, a derived from dbuser::select()
	 * @return void
	 */
	public function setUserSession($arrUser) {
		if (!isset($arrUser['username']) || $arrUser['username'] == '')
			throw new Exception('Invalid username');

		foreach ($arrUser as $strKey => $strValue)
			$_SESSION['user_'.$strKey] = $strValue;
	}

	/**
	 * Returns the current user session
	 *
	 * @return array|bool
	 */
	public function getUserSession() {
		if (isset($_SESSION['user_username'])) {
			$arrUser = array();
			foreach ($_SESSION as $strKey => $strValue)
				if (substr($strKey, 0, 4) == 'user_')
					$arrUser[substr($strKey, 4)] = $strValue;
			return $arrUser;
		}

		return false;
	}

	/**
	 * Unsets the current user session
	 *
	 * @return array|bool
	 */
	public function unsetUserSession() {
		foreach ($_SESSION as $strKey => $strValue)
			if (substr($strKey, 0, 4) == 'user_')
				unset($_SESSION[$strKey]);
	}

	/**
	 * Initializes and checks a server result
	 *
	 * @param array $res
	 */
	public function initResult($res) {
		if (!is_array($res))
			throw new Exception('Invalid datatype. Array expected!');

		if (isset($res['error']))
			throw new Exception('Server error: '.$res['error']);

		if (!isset($res['result']))
			throw new Exception('Server returned no result.');

		return $res['result'];
	}
}
