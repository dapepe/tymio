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

 /**
  * Provides user authentication functions.
  */
class Authenticator {

	/**
	 * @var string The application salt.
	 */
	public $salt = 'Zgds623kHjd235Gshdw';

	/**
	 * @var int The duration for the autologin cookie in days.
	 */
	public $cookieDuration = 14;

	/**
	 * @var User
	 */
	private $user = null;

	/**
	 * Validates a Cookie Token
	 *
	 * @param string $strUsername
	 * @param int $intDay The number of days the cookie should be valid
	 * @param string $salt Token salt
	 */
	public function createCookieToken($userName, $days = 14, $salt = false) {
		return cryptastic::encrypt(array(
			'username' => $userName,
			'expiration' => time() + 8600 * $days
		), ( $salt ? $salt : $this->salt ));
	}

	/**
	 * Validates a user user login
	 *
	 * @param string $strUsername
	 * @param string $strPassword
	 * @param bool $bolSetLocal Optional. Stores the validated domain and user.
	 *     Default is TRUE.
	 * @param bool $bolSetSession Optional. Sets the user session. Default is FALSE.
	 * @param bool $bolSetCookie Optional. Default is FALSE.
	 * @return User
	 */
	public function validateUser($strUsername, $strPassword = false, $bolSetLocal = true, $bolSetSession = false, $bolSetCookie = false, PropelPDO $con = null) {
		$defaultAccountName = \Xily\Config::get('app.account', 'string', '');
		if ( ($defaultAccountName !== '') and (strpos($strUsername, '/') === false) )
			$strUsername = $defaultAccountName.'/'.$strUsername;

		$user = UserPeer::getAuthenticatedUser($strUsername, $strPassword, $con);

		if ( ($user === null) or $user->getDeleted() )
			return null;

		if ($bolSetLocal)
			$this->user = $user;
		if ($bolSetSession)
			$this->setUserSession($user->getId());
		if ($bolSetCookie)
			setcookie('autologin', $this->createCookieToken($user->getFQN($con), $this->cookieDuration), time() + (86400 * $this->cookieDuration));

		return $user;
	}

	public function getUser() {
		return $this->user;
	}

	/**
	 * Sets or removes the user session information
	 *
	 * @param int $intId The user ID
	 * @return void
	 */
	public function setUserSession($intId = false) {
		if ($intId)
			$_SESSION['user_id'] = $intId;
		else {
			unset($_SESSION['user_id']);
			setcookie('autologin', '', time() - 3600);
			unset($_COOKIE['autologin']);
		}
	}

	/**
	 * Returns the current user session
	 *
	 * @return User
	 */
	public function getUserSession(PropelPDO $con = null) {
		if (isset($_SESSION['user_id']))
			return UserQuery::create()->findOneById($_SESSION['user_id'], $con);

		return null;
	}

	/**
	 * Validates a Cookie Token
	 *
	 * @param string $cookieToken
	 * @param string $salt Token salt
	 * @return array|bool The username or FALSE
	 */
	public function validateCookieToken($cookieToken, $salt = false) {
		$t = cryptastic::decrypt($cookieToken, ($salt ? $salt : $this->salt));

		if ( isset($t['username'], $t['expiration']) and ($t['expiration'] > time()) )
			return $t['username'];

		return false;
	}

	/**
	 * Authenticates a user by using the session cookie
	 *
	 * @param string $cookieToken
	 * @param bool $bolSetLocal Store the validated domain and user
	 * @param bool $bolSetSession Sets the user session
	 * @return bool
	 */
	public function authCookieToken($cookieToken, $bolSetLocal = true, $bolSetSession = false) {
		$strUsername = self::validateCookieToken($cookieToken);
		if ( !$strUsername )
			return false;

		$user = UserQuery::create()->findOneByFQN($strUsername);
		if ( $user === null )
			return false;

		if ( $bolSetLocal )
			$this->user = $user;
		if ( $bolSetSession )
			$this->setUserSession($user->getId());

		return true;
	}

	/**
	 * Authenticates a user user loging
	 *
	 * @param bool $bolSetLocal Store the validated domain and user
	 * @param bool $bolSetSession Sets the user session
	 * @return bool
	 */
	public function authUser($bolSetLocal = true, $bolSetSession = false, $bolSetCookie = false) {
		if ( $this->user !== null )
			return $this->user;

		$this->user = $this->getUserSession();
		if ( $this->user !== null )
			;
		elseif ( isset($_REQUEST['username']) && isset($_REQUEST['password']) && $_REQUEST['password'] !== false )
			$this->validateUser($_REQUEST['username'], $_REQUEST['password'], $bolSetLocal, $bolSetSession, $bolSetCookie);
		elseif ( isset($_COOKIE['autologin']) )
			$this->authCookieToken($_COOKIE['autologin'], $bolSetLocal, $bolSetSession);

		return $this->user;
	}

	/**
	 * Requires a user for local option
	 *
	 * @param int $intId The user ID
	 * @param Domain $domain The user's domain
	 * @return User
	 */
	public function requireUser(PropelPDO $con = null) {
		if ( $this->user )
			return $this->user;
		elseif ( $user = $this->getUserSession($con) ) {
			$this->user = $user;
			return $user;
		} if ( isset($_REQUEST['username']) && isset($_REQUEST['password']) && $user = $this->validateUser($_REQUEST['username'], $_REQUEST['password'], true, false, false, $con) ) {
			return $user;
		}

		throw new Exception('User reference is required for this operation!');
	}

}

/**
 * Provides a special exception to pass additional error information to the API service dispatcher.
 */
class APIException extends Exception {

	private $errorName;
	private $data;

	public function __construct($errorName, $message, $data = null, PropelPDO $con = null) {
		parent::__construct($message);

		$this->errorName = $errorName;
		$this->data      = $this->toErrorData($data, $con);
	}

	private function toErrorData($data, PropelPDO $con = null) {
		if ( is_resource($data) )
			return null;
		elseif ( !is_object($data) )
			return $data;

		$class   = get_class($data);
		$toArray = array('EntityArray', 'from'.$class);
		if ( is_callable($toArray) )
			return call_user_func($toArray, $data, $con);

		$toArray = array($data, 'toArray');
		if ( is_callable($toArray) )
			return call_user_func($toArray);

		$toString = array($data, '__toString');
		if ( is_callable($toString) )
			return call_user_func($toString);

		throw new Exception('Unsupported data supplied. Original error was: '.$this->getMessage());
	}

	public function getName() {
		return $this->errorName;
	}

	public function getData() {
		return $this->data;
	}

}

class APIPermissionDeniedException extends APIException {
}

/**
 * Basic application API class.
 *
 * The API's run() function should be designed to perform the authentication before executing the function.
 */
class API extends KickstartAPI {

	const ERROR_PERMISSION_DENIED = 'api_permission_denied';

	/**
	 * @var Authenticator
	 */
	private $authenticator;

	/** @var array Array containing all API tasks which require no authentication */
	public $auth_exceptions = array();
	/** @var User The currently selected agent */
	public $user;
	/** @var Domain The currently selected domain */
	public $domain;

	public function __construct(Authenticator $authenticator) {
		$this->showtrace = \Xily\Config::get('app.apitrace', 'bool', false);

		$this->authenticator = $authenticator;

		parent::__construct();
	}

	public function exceptionToResult(Exception $e) {
		$result = parent::exceptionToResult($e);

		if ( is_array($result) and ($e instanceof APIException) ) {
			$result['errorname'] = $e->getName();
			$result['errordata'] = $e->getData();
		}

		return $result;
	}

	/**
	 * Only runs a function, if a valid authentication token has been sent.
	 */
	public function run() {
		if (!isset($_REQUEST['do']))
			throw new Exception('No task specified.');

		if (!in_array($_REQUEST['do'], $this->auth_exceptions) && !$this->auth())
			throw new APIPermissionDeniedException(self::ERROR_PERMISSION_DENIED, 'Authentication required.');

		$this->runJSON();
	}

	/**
	 * The default authentication method specifies if an API task may be executed or not.
	 * This method may be different in subordinate classes
	 *
	 * @return bool
	 */
	public function auth() {
		return $this->authToken();
	}

	/**
	 * Authenticates a user user loging
	 *
	 * @param bool $bolSetLocal Store the validated domain and user
	 * @param bool $bolSetSession Sets the user session
	 * @return bool
	 */
	public function authUser($bolSetLocal = true, $bolSetSession = false, $bolSetCookie = false) {
		return $this->authenticator->authUser($bolSetLocal, $bolSetSession, $bolSetCookie);
	}

	/**
	 * Requires a user for local option
	 *
	 * @return User
	 */
	public function requireUser(PropelPDO $con = null) {
		return $this->authenticator->requireUser($con);
	}

	/**
	 * Sets or removes the user session information
	 *
	 * @param int $intId The user ID
	 * @return void
	 */
	public function setUserSession($intId = false) {
		$this->authenticator->setUserSession($intId);
	}

	/**
	 * Returns the current user session
	 *
	 * @return User
	 */
	public function getUserSession() {
		return $this->authenticator->getUserSession();
	}

	/**
	 * Initializes localization for the filter array
	 *
	 * @param array $arrFilter
	 * @param Localizer $locale
	 * @return array
	 */
	public function initFilter($arrFilter, $locale = false) {
		if (!$locale)
			$locale = Localizer::getInstance();

		foreach ($arrFilter as &$F)
			if (isset($F['field']))
				$F['field'] = $locale->get($F['field']);

		return $arrFilter;
	}

}

/**
 * Provides a factory to instantiate web service APIs.
 */
class APIFactory {

	/**
	 * @var Authenticator
	 */
	static private $authenticator = null;

	/**
	 * @var array An associative array mapping API class names to {@link API} objects.
	 */
	static private $instances = array();

	/**
	 * Returns the specified API instance.
	 *
	 * Throws an exception on errors.
	 *
	 * @param string $apiName
	 * @return API
	 */
	static public function get($apiName) {
		if ( !file_exists(API_DIR.$apiName.'.php') )
			throw new Exception('Unknown API "'.$apiName.'".');

		$apiClass = ucfirst($apiName).'API';

		if ( isset(self::$instances[$apiClass]) )
			return self::$instances[$apiClass];

		// Load and instantiate API class

		require_once(API_DIR.$apiName.'.php');

		if ( !class_exists($apiClass) )
			throw new Exception('Unknown API "'.$apiName.'".');

		$api = new $apiClass(self::getAuthenticator());
		if ( !($api instanceof API) )
			throw new Exception('Invalid API "'.$apiName.'".');

		self::$instances[$apiClass] = $api;

		return $api;
	}

	/**
	 * @return Authenticator
	 */
	static public function getAuthenticator() {
		if ( self::$authenticator === null ) {
			self::$authenticator = new Authenticator();
			$cookieSalt = \Xily\Config::get('app.salt', 'string', '');
			if ( (string)$cookieSalt !== '' )
				self::$authenticator->salt = $cookieSalt;
		}

		return self::$authenticator;
	}

}
