<?php

// Register Plugin-Events
PluginPeer::registerEvent('user', 'create');
PluginPeer::registerEvent('user', 'modify');
PluginPeer::registerEvent('user', 'remove');
PluginPeer::registerEvent('user', 'restore');

/**
 * API functions to manage USERS
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package ZfxSupport
 * @version 1.1 (2012-01-17)
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class UserAPI extends API {
	/**
	 * @var array An associative array mapping actions to their definitions.
	 *     Action format:    { <name> => [ <method>, <function>, <parameters> ] }
	 *       Methods:        POST, GET, REQUEST
	 *     Parameter format: [ <name>, <type>, <default> = '', <required> = true ]
	 *       Types:          int, float, bool, array, object, string
	 */
	public $actions = array(
		/*!
		 * @cmd auth
		 * @method any
		 * @description Authenticates a user
		 * @param {string} username*
		 * @param {string} password*
		 * @param {bool} session Create a session
		 * @param {bool} cookie Create an auto-login cookie
		 * @return {bool}
		 * @demo
		 */
		'auth' => array('ANY', 'auth', array(
			array('username', 'string', false),
			array('password', 'string', false),
			array('session', 'bool', false, false),
			array('cookie', 'bool', false, false)
		)),
		/*!
		 * @cmd session
		 * @method any
		 * @description Returns whether or not a user has an active session
		 * @return {bool}
		 */
		'session' => array('ANY', 'session', array()),
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all users of a domain
		 * @param {string} search Search query
		 * @param {int} domain Filter by domain
		 * @param {bool} showdeleted Show deleted users
		 * @param {string} orderby Order result by "Name" (default), "Lastname", "Firstname", "DomainId" or "Email"
		 * @param {string} ordermode Order mode "asc" (default) or "desc"
		 * @return {array} List of users [{id, name}, ...]
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('search', 'string', null, false),
			array('domain', 'int', null, false),
			array('showdeleted', 'bool', false, false),
			array('orderby', 'string', null, false),
			array('ordermode', 'string', null, false)
		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Shows the details for a single user
		 * @param {string} id* The user ID or full login name
		 * @param {bool} allowpeers false to restrict to subordinate users, true to yield any company-related user (only available to plugins)
		 * @return {array}
		 * @demo
		 */
		'details' => array('ANY', 'details', array(
			array('id', 'string', null, false),
			array('allowpeers', 'bool', false, false),
		)),
		/*!
		 * @cmd add
		 * @method any
		 * @description Adds a new user to a domain
		 * @param {array} data* The user details
		 * @return {int} The user database ID
		 * @demo
		 */
		'add' => array('ANY', 'add', array(
			array('data', 'array', null),
		)),
		/*!
		 * @cmd add
		 * @method any
		 * @description Adds a new user to a domain
		 * @param {int} id*
		 * @param {array} data* The user name
		 * @return {bool}
		 * @demo
		 */
		'update' => array('ANY', 'update', array(
			array('id', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd set_pwd
		 * @method any
		 * @description Sets the password for an user
		 * @param {int} id*
		 * @param {int} new_password*
		 * @return {bool}
		 * @demo
		 */
		'set_pwd' => array('ANY', 'set_pwd', array(
			array('id', 'int', null, false),
			array('new_password', 'string', null),
		)),
		/*!
		 * @cmd restore
		 * @method any
		 * @description Restores a deleted user
		 * @param {int} id*
		 * @return {array}
		 * @demo
		 */
		'restore' => array('ANY', 'restore', array(
			array('id', 'int', null),
		)),
		/*!
		 * @cmd remove
		 * @method any
		 * @description Deletes a single user from a domain
		 * @param {int} id*
		 * @return {array}
		 * @demo
		 */
		'remove' => array('ANY', 'remove', array(
			array('id', 'int', null),
		))
	);

	public $auth_exceptions = array('auth');

	/** @var array Basic filter settings */
	public $filter_basic = array(
		'Name' => array(
			'filter' => FILTER_VALIDATE_USERNAME_CHARS,
			'field'  => 'field.username',
		),
		'DomainId' => array(
			'filter' => FILTER_VALIDATE_INT,
			'field'  => 'entity.domain.singular',
			'message' => 'error.not_set',
		),
		'Firstname' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 2,
			'field'  => 'field.firstname',
		),
		'Lastname' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 2,
			'field'  => 'field.lastname',
		),
		'Password' => array(
			'filter' => FILTER_VALIDATE_PASSWORD,
		),
		'Email' => array(
			'filter' => FILTER_VALIDATE_EMAIL,
		)
	);

	/** @var array Filter settings for user properties */
	public $filter_properties = array(
		'HoursPerWeek' => array(
			'filter' => FILTER_VALIDATE_INT,
			'field'  => 'entity.hours_per_week'
		)
	);

	/**
	 * Retrieves the user with the specified ID from a particular company.
	 *
	 * @param int $accountId
	 * @param int $peerUserId The ID of the user to retrieve.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return User
	 */
	private function getPeerUser($accountId, $peerUserId, PropelPDO $con = null) {
		$user = UserQuery::create()
			->filterByAccountId($accountId)
			->filterByDeleted(0, Criteria::EQUAL)
			->findOneByFQN($peerUserId, $con);

		if ( $user === null )
			throw new Exception('Could not find user #'.$peerUserId.'.');

		return $user;
	}

	/**
	 * User or token authentication
	 *
	 * @see API::auth()
	 * @return bool
	 */
	public function auth() {
		if ( $this->authUser() )
			return true;

		return false;
	}

	/**
	 * Authenticates a user agent
	 *
	 * @param string $strUsername
	 * @param string $password
	 */
	public function do_auth($strUsername, $password, $bolSession = false, $bolSetCookie = false) {
		return (bool)$this->validateUser($strUsername, $password, true, $bolSession, $bolSetCookie);
	}

	/**
	 * Returns whether or not a user has an active session
	 *
	 * @return bool
	 */
	public function do_session() {
		return isset($_SESSION['user_id']);
	}

	/**
	 * Lists users of a domain.
	 *
	 * For non-administrative users, the list will contain the authenticated user only.
	 *
	 * @param string $strSearch Search query
	 * @param string $intDomain Filter by domain
	 * @param bool $showDeleted Show deleted users
	 * @param string $strOrderby OrderBy Column
	 * @param string $strOrderMode Order mode (asc or desc)
	 * @return array
	 */
	public function do_list($strSearch = false, $intDomain = false, $showDeleted = false, $strOrderby = 'Name', $strOrderMode = 'asc') {
		$user    = $this->requireUser();
		$account = $user->getAccount();

		$query = UserQuery::create()
			->joinWith('Domain')
			->joinWith('Domain.Account')
			->add(AccountPeer::ID, $account->getId());

		if ( !$user->getIsAdmin() )
			$query->filterById($user->getId());

		Search::addSearchCriteria($query, $strSearch, array(
			UserPeer::NAME,
			UserPeer::FIRSTNAME,
			UserPeer::LASTNAME,
			UserPeer::PHONE,
			UserPeer::EMAIL,
		));

		if ( $intDomain ) {
			if ( $domain = DomainQuery::create()->findOneById($intDomain) )
				$query->filterByDomain($domain);
			else
				throw new Exception('Domain ID "'.$intDomain.'" not found!');
		}

		if ( $strOrderMode != 'asc' )
			$strOrderMode = 'desc';

		switch ( $strOrderby ) {
			case 'Firstname':
				$query->orderByFirstname($strOrderMode);
				break;
			case 'Lastname':
				$query->orderByLastname($strOrderMode);
				break;
			case 'Domain':
				$query->orderBy(DomainPeer::NAME, $strOrderMode);
				break;
			case 'Email':
				$query->orderByEmail($strOrderMode);
				break;
			default: // Name
				$query->orderByName($strOrderMode);
				break;
		}

		if ( !$showDeleted )
			$query->filterByDeleted(0);

		$res = array();
		foreach ($query->find() as $user) {
			$domain = $user->getDomain();
			$item   =
				EntityArray::from($user) +
				array('Domain' => array('Name' => $domain->getName(), 'Id' => $domain->getId())) +
				array('Account' => array('Name' => $domain->getAccount()->getName(), 'Id' => $domain->getAccountId()));
			$res[]  = $item;
		}

		return $res;
	}

	/**
	 * Returns the user details
	 *
	 * @param string|int $id The user ID or full login name
	 * @param bool $allowPeerUsers Optional. If TRUE and the method was called
	 *     by a plugin, users from the same company account but not managed by
	 *     the authenticated user can be returned as well. If FALSE, only
	 *     subordinate users will be returned. Default is FALSE.
	 * @return array An associative array of user properties.
	 */
	public function do_details($id = null, $allowPeerUsers = false) {
		$user = $this->requireUser(); /* @var $user User */

		if ( (string)$id !== '' ) {
			// Check if it is a plugin execution
			$user = (
				( PluginIXml::inPlugin() and $allowPeerUsers )
				? $this->getPeerUser($user->getAccountId(), $id) // Allow plugins to fetch peer users (e.g. supervisors etc.)
				: $user->getSubordinate($id)
			);
		}

		return EntityArray::from($user) + array('Properties' => $user->getProperties());
	}

	/**
	 * Adds a new agent to a domain
	 *
	 * @param array $arrData
	 * @return int The user ID
	 */
	public function do_add($arrData) {
		return $this->do_update(false, $arrData);
	}

	/**
	 * Updates a user
	 *
	 * @param int $intId The user ID
	 * @param array $arrData The data array
	 * @throws Exception
	 * @return int The user ID
	 */
	public function do_update($intId = null, $arrData) {
		$user = null;

		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$authUser  = $this->requireUser();
			$accountId = $authUser->getAccountId();

			$validator = new KickstartValidator();
			$locale    = Localizer::getInstance();

			if ( $intId and
			     (!isset($arrData['Password']) or $arrData['Password'] == '') ) {
				unset($this->filter_basic['Password']);
				unset($arrData['Password']);
				unset($arrData['Password2']);
			}

			$warnings = $validator->filterErrors($arrData, $this->initFilter($this->filter_basic, $locale));

			if ( $warnings )
				return array('result' => false, 'warnings' => $warnings);

			if ( $intId ) {
				$user = $authUser->getSubordinate($intId);
			} else {
				$user = new User();
				$user
					->setAccountId($accountId)
					->setDomainId($authUser->getDomainId());
			}

			if ( isset($arrData['Password']) )
				$user->setPassword($arrData['Password']);

			$allowedFields = array(
				'Name'      => true,
				'Firstname' => true,
				'Lastname'  => true,
				'Phone'     => true,
				'Email'     => true,
				'Number'    => true,
			);
			if ( $authUser->getIsAdmin() ) {
				$allowedFields += array(
					'DomainId'  => true,
					'ManagerOf' => true,
					'IsAdmin'   => true,
				);
			}

			$user->fromArray(array_intersect_key($arrData, $allowedFields));

			// Fail if domain does not belong to authenticated account
			$domain = $user->getDomain($con);
			if ( ($domain === null) or ($domain->getAccountId() !== $accountId) )
				throw new Exception('Invalid domain ID #'.$user->getDomainId());

			$user->save($con);

			if ( !empty($arrData['Properties']) )
				$user->setProperties($arrData['Properties'], $con);

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $user->getId();
	}


	/**
	 * Sets a new password for an agent
	 *
	 * @param int $intId The user ID
	 * @param string $password The new agent password
	 */
	public function do_set_pwd($intId = false, $password) {
		$user = $this->requireUser();

		if ( strlen($password) < 6 )
			throw new Exception('Password must be longer than 5 characters');

		if ( $intId )
			$user = $user->getSubordinate($intId);

		$user
			->setPassword($password)
			->save();

		return true;
	}

	/**
	 * Restores an agent
	 *
	 * @param int $intId The agent ID
	 */
	public function do_restore($intId) {
		$user = $this->requireUser();
		$user
			->getSubordinate($intId)
			->setDeleted(0)
			->save();

		return true;
	}

	/**
	 * Removes an agent
	 *
	 * @param int $intId The agent ID
	 */
	public function do_remove($intId) {
		$user = $this->requireUser();
		$user
			->getSubordinate($intId)
			->setDeleted(1)
			->save();

		return true;
	}
}
