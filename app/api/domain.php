<?php

// Register Plugin-Events
PluginPeer::registerEvent('domain', 'create');
PluginPeer::registerEvent('domain', 'modify');
PluginPeer::registerEvent('domain', 'remove');
PluginPeer::registerEvent('domain', 'restore');

/**
 * API functions to manage DOMAINS
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package ZfxSupport
 * @version 1.1 (2012-01-17)
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class DomainAPI extends API {
	/**
	 * @var array An associative array mapping actions to their definitions.
	 *     Action format:    { <name> => [ <method>, <function>, <parameters> ] }
	 *       Methods:        POST, GET, REQUEST
	 *     Parameter format: [ <name>, <type>, <default> = '', <required> = true ]
	 *       Types:          int, float, bool, array, object, string
	 */
	public $actions = array(
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all account
		 * @param {string} search
		 * @param {bool} showdeleted Show deleted domains
		 * @param {string} orderby Order result by "Name" (default), "Description" or "Number"
		 * @param {string} ordermode Order mode "asc" (default) or "desc"
		 * @return {array} List of domains [{Id, Name}, ...]
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('search', 'string', null, false),
			array('showdeleted', 'bool', false, false),
			array('orderby', 'string', false, false),
			array('ordermode', 'string', false, false),
		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Shows the details for a single user
		 * @param {int} id*
		 * @return {array}
		 * @demo
		 */
		'details' => array('ANY', 'details', array(
			array('id', 'int', null),
		)),
		/*!
		 * @cmd add
		 * @method post
		 * @description Adds a new domain
		 * @param {array} data* The domain details (Name, Description, Number)
		 * @return {int} The domain database ID
		 * @demo
		 */
		'add' => array('POST', 'add', array(
			array('data', 'array', null),
		)),
		/*!
		 * @cmd update
		 * @method post
		 * @description Updates a domain
		 * @param {int} id*
		 * @param {array} data* The domain details (Name, Description, Number)
		 * @return {bool}
		 * @demo
		 */
		'update' => array('POST', 'update', array(
			array('id', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd restore
		 * @method post
		 * @description Reactivates a domain (after it as been removed)
		 * @param {int} id*
		 * @return {bool}
		 * @demo
		 */
		'restore' => array('POST', 'restore', array(
			array('id', 'int', null),
		)),
		/*!
		 * @cmd remove
		 * @method post
		 * @description Deactivates a domain (instead of really deleting it)
		 * @param {int} id*
		 * @return {bool}
		 * @demo
		 */
		'remove' => array('POST', 'remove', array(
			array('id', 'int', null),
		)),
	);

	public $auth_exceptions = array();

	/** @var array Basic filter settings */
	public $filter_basic = array(
		'Name' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 2,
			'field'  => 'entity.domain.singular'
		),
	);

	/** @var array Filter settings for domain properties */
	public $filter_properties = array();

	/**
	 * User or token authentication
	 *
	 * @see API::auth()
	 * @return bool
	 * @todo Implement MASTER authentication
	 */
	public function auth() {
		return $this->authUser();
	}

	/**
	 * Lists all domains
	 *
	 * @param string $strSearch Search query
	 * @param bool $showDeleted Show deleted domains
	 * @param string $strOrderby OrderBy Column
	 * @param string $strOrderMode Order mode (asc or desc)
	 * @return array
	 */
	public function do_list($strSearch = null, $showDeleted = false, $strOrderby='Name', $strOrderMode='asc') {
		$user = $this->requireUser();

		$query = DomainQuery::create()
			->filterByAccount($user->getAccount());

		if ( $strOrderMode != 'asc' )
			$strOrderMode = 'desc';

		switch ($strOrderby) {
			case 'Description':
				$query->orderByDescription($strOrderMode);
				break;
			case 'Number':
				$query->orderByNumber($strOrderMode);
				break;
			default: // Name
				$query->orderByName($strOrderMode);
				break;
		}

		if ( !$showDeleted )
			$query->filterByValid(null, Criteria::NOT_EQUAL);

		if ( (string)$strSearch !== '' )
			$query->add(DomainPeer::NAME, '%'.$strSearch.'%', Criteria::LIKE);

		$res = array();
		foreach ($query->find() as $domain)
			$res[] = $domain->toArray();

		return $res;
	}

	/**
	 * Returns the domain details
	 *
	 * @param int $intId The domain ID
	 * @return array
	 */
	public function do_details($intId = null) {
		$user = $this->requireUser(); /* @var $user User */

		/* @var $domain Domain */
		$domain = DomainQuery::create()
			->filterByAccount($user->getAccount())
			->findOneById($intId);

		return $domain->toArray() + array('Properties' => $domain->getProperties());
	}

	/**
	 * Adds a new domain
	 *
	 * @param array $arrData
	 * @return int The domain ID
	 */
	public function do_add($arrData) {
		return $this->do_update(null, $arrData);
	}

	/**
	 * Updates a domain
	 *
	 * @param int $intId
	 * @param array $arrData
	 * @return int The domain ID
	 */
	public function do_update($intId, $arrData) {
		$domain = null;

		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$user    = $this->requireUser();
			$account = $user->getAccount($con);

			// Validate input data
			$validator = new KickstartValidator();
			$locale    = Localizer::getInstance();
			$warnings  = $validator->filterErrors($arrData, $this->initFilter($this->filter_basic, $locale));
			if ( $warnings ) {
				$con->rollBack();
				return array('result' => false, 'warnings' => $warnings);
			}

			$query = DomainQuery::create()
				->filterByAccount($account);

			if ( $intId !== null ) {
				$domain = DomainQuery::create()
					->filterByAccount($account)
					->findOneById($intId, $con);

				if ( $domain === null )
					throw new Exception('Domain not found; ID: '.$intId);

				$query->filterById($intId, Criteria::NOT_EQUAL);

			} else {
				$domain = new Domain();
				$domain->setAccount($account);

			}

			// Check for duplicates
			if ( $query->findOneByName($arrData['Name'], $con) )
				throw new Exception($locale->insert('error.taken', array('value' => '"'.$arrData['Name'].'"')));

			$domain->fromArray(array_intersect_key($arrData, array(
				'AddressId'   => true,
				'Name'        => true,
				'Description' => true,
				'Number'      => true,
			)));
			$domain->save($con);

			if ( !empty($arrData['Properties']) )
				$domain->setProperties($arrData['Properties'], $con);

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $domain->getId();
	}

	/**
	 * Restores a domain
	 *
	 * @param int $intId The domain ID
	 */
	public function do_restore($intId) {
		$this->requireUser()
			->getOtherDomain($intId)
			->setValid(1)
			->save();

		return true;
	}

	/**
	 * Removes a domain
	 *
	 * @param int $intId The domain ID
	 */
	public function do_remove($intId) {
		$this->requireUser()
			->getOtherDomain($intId)
			->setValid(null)
			->save();

		return true;
	}
}
