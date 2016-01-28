<?php



/**
 * Skeleton subclass for performing query and update operations on the 'user' table.
 *
 * @package    propel.generator.tymio
 */
class UserPeer extends BaseUserPeer {

	const LDAP_SETTING_HOST       = 'System.LDAP.Host';
	const LDAP_SETTING_LOGIN_NAME = 'System.LDAP.LoginName'; // Template for login name
	const LDAP_SETTING_OPTIONS    = 'System.LDAP.Options';
	const LDAP_SETTING_PORT       = 'System.LDAP.Port';

	const ALGORITHM = 'sha1';

	static private $salt = 'oFZt9r10L623QAFGy9qo';

	static private $uniqueIdCounter = 0;

	static public function setSystemSalt($salt) {
		self::$salt = $salt;
	}

	/**
	 * Returns the password hash encrypted using the specified salt.
	 *
	 * Do not use this function directly unless you know what you are doing.
	 * Use {@link getPasswordHash()} and {@link verifyPassword()} instead.
	 *
	 * @return string The hexadecimal representation of the hash.
	 * @see getPasswordHash()
	 */
	static public function encryptPassword($password, $salt) {
		return hash_hmac(self::ALGORITHM, $password, $salt.':'.self::$salt);
	}

	/**
	 * Returns a JSON string encoding the encrypted password and the used salt.
	 *
	 * @param string $password The password to encrypt.
	 * @param string $salt Optional. The salt to use.
	 * @return string
	 * @see encryptPassword()
	 * @see verifyPassword()
	 */
	static public function getPasswordHash($password, $salt = null) {
		if ( $salt === null )
			$salt = sha1(uniqid((self::$uniqueIdCounter++).microtime(true), true));

		return json_encode(array($salt, self::encryptPassword($password, $salt)));
	}

	/**
	 * Locks the password to disable *local* authentication.
	 *
	 * Returns the hash as is if it is already locked.
	 *
	 * @param string $passwordHash
	 * @return string The locked password hash.
	 * @see passwordLocked()
	 */
	static public function getLockedPassword($passwordHash) {
		return (
			self::passwordLocked($passwordHash)
			? $passwordHash
			: '!'.$passwordHash
		);
	}

	/**
	 * Checks if the password is locked.
	 *
	 * @param string $passwordHash
	 * @return bool
	 */
	static public function passwordLocked($passwordHash) {
		return ( substr($passwordHash, 0, 1) === '!' );
	}

	/**
	 * Compares the supplied password against the hash.
	 * This is the counterpart to {@link getPasswordHash()}.
	 *
	 * @param string $passwordHash The JSON-encoded password hash with its salt.
	 * @return bool Returns TRUE if the password matches the hash, otherwise
	 *     FALSE.
	 * @see getPasswordHash()
	 */
	static public function verifyPassword($password, $passwordHash) {
		if ( self::passwordLocked($passwordHash) )
			return false;

		$hashData = json_decode($passwordHash, true);
		if ( !isset($hashData[0], $hashData[1]) or !is_array($hashData) )
			throw new Exception('Invalid password hash found in database. Please contact Groupion support to report the affected user.');

		$salt     = $hashData[0];
		$expected = $hashData[1];

		return ( self::encryptPassword($password, $salt) === $expected );
	}

	/**
	 * Returns the user authenticated by the supplied login name and password.
	 *
	 * @param string $loginName
	 * @param string $password The password.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return User|null The {@link User} object, or NULL on failure.
	 * @uses verifyPassword()
	 * @see UserQuery
	 */
	static public function getAuthenticatedUser($loginName, $password, PropelPDO $con = null) {
		$user = UserQuery::create()->findOneByFQN($loginName, $con);
		if ( $user === null )
			$user = UserQuery::create()->findOneByEmail($loginName, $con);

		if ( $user === null )
			return null;
		elseif ( \Xily\Config::get('app.useldap', 'bool', false) && self::ldapAuthenticate($user, $password, $con) )
			return $user;
		else
			return ( self::verifyPassword($password, $user->getPasswordHash()) ? $user : null );
	}

	static public function ldapAuthenticate(User $user, $password, PropelPDO $con = null) {
		$account = $user->getAccount($con);
		if ( $account === null )
			throw new Exception('Could not determine account of user #'.$user->getId().' "'.$user->getName.'".');

		$ldapSettings = PropertyPeer::getAll($account, null, null, array(
			self::LDAP_SETTING_HOST,
			self::LDAP_SETTING_LOGIN_NAME,
			self::LDAP_SETTING_OPTIONS,
			self::LDAP_SETTING_PORT,
		), $con);

		if ( !isset($ldapSettings[self::LDAP_SETTING_HOST], $ldapSettings[self::LDAP_SETTING_LOGIN_NAME]) )
			return false;

		$ldapLoginName = KeyReplace::replace($ldapSettings[self::LDAP_SETTING_LOGIN_NAME], array(
			'user'    => $user->getName(),
			'account' => $account->getIdentifier(),
		));

		if ( (string)$password === '' )
			return false;//throw new Exception('Password must not be empty for LDAP authentication.');

		try {
			new LDAP(
				$ldapSettings[self::LDAP_SETTING_HOST],
				$ldapLoginName,
				$password,
				(
					( isset($ldapSettings[self::LDAP_SETTING_OPTIONS]) and is_array($ldapSettings[self::LDAP_SETTING_OPTIONS]) )
					? $ldapSettings[self::LDAP_SETTING_OPTIONS]
					: array()
				),
				( isset($ldapSettings[self::LDAP_SETTING_PORT]) ? $ldapSettings[self::LDAP_SETTING_PORT] : null )
			);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

} // UserPeer
