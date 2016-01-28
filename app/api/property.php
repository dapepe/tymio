<?php

/**
 * API functions to manage PROPERTIES
 *
 * @author Huy Hoang Nguyen et al.
 * @package tymio
 * @copyright Copyright (c) 2013, Zeyon GmbH & Co. KG
 */
class PropertyAPI extends API {

	public $actions = array(
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all properties of an account
		 * @param {string} search Search query
		 * @param {string} domain The domain to restrict the list to
		 * @param {string} orderby Order result by "Name" (default),
		 * @param {string} ordermode Order mode "asc" (default) or "desc"
		 * @return {array} List properties
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('search', 'string', null, false),
			array('domain', 'string', null, false),
			array('orderby', 'string', null, false),
			array('ordermode', 'string', null, false),
		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Yields a property's details
		 * @param {int} id* The property ID
		 * @return {array} [AccountId:{int}, Name:{string}, Label:{string}, Description:{string}, Type:{string}, DefaultValue:{json}, Fixed:{bool}]
		 * @demo
		 */
		'details' => array('ANY', 'details', array(
			array('id', 'int', null),
		)),
		/*!
		 * @cmd add
		 * @method post
		 * @description Adds a new property
		 * @param {array} data* The property details (AccountId:{int}, Name:{string}, Label:{string}, Description:{string}, Type:{string}, DefaultValue:{string}, Fixed:{bool})
		 * @return {int} The property ID
		 * @demo
		 */
		'add' => array('POST', 'add', array(
			array('data', 'array', null),
		)),
		/*!
		 * @cmd update
		 * @method post
		 * @description Updates a property
		 * @param {int} id* The property ID
		 * @param {array} data* The property details (AccountId:{int}, Name:{string}, Label:{string}, Description:{string}, Type:{string}, DefaultValue:{string}, Fixed:{bool})
		 * @return {bool}
		 * @demo
		 */
		'update' => array('POST', 'update', array(
			array('id', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd get
		 * @method any
		 * @description Sets a property's domain-specific value
		 * @param {int} id* The property ID
		 * @param {int} domain* The domain ID
		 * @param {array} data* The property details (AccountId:{int}, Name:{string}, Label:{string}, Description:{string}, Type:{string}, DefaultValue:{string}, Fixed:{bool})
		 * @return {int} The property value ID
		 * @demo
		 */
		'get' => array('ANY', 'get', array(
			array('id', 'int', null),
			array('domain', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd set
		 * @method post
		 * @description Sets a property's domain-specific value
		 * @param {int} id* The property ID
		 * @param {int} domain* The domain ID
		 * @param {array} data* The property details (AccountId:{int}, Name:{string}, Label:{string}, Description:{string}, Type:{string}, DefaultValue:{string}, Fixed:{bool})
		 * @return {int} The property value ID
		 * @demo
		 */
		'set' => array('POST', 'set', array(
			array('id', 'int', null),
			array('domain', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd unset
		 * @method post
		 * @description Unsets a property's domain-specific value
		 * @param {int} id* The property ID
		 * @param {int} domain* The domain ID
		 * @return {bool}
		 * @demo
		 */
		'unset' => array('POST', 'unset', array(
			array('id', 'int', null),
			array('domain', 'int', null),
		)),
		/*!
		 * @cmd remove
		 * @method post
		 * @description Deletes a property with all its domain-specific values
		 * @param {int} id* The property ID
		 * @return {array}
		 * @demo
		 */
		'remove' => array('POST', 'remove', array(
			array('id', 'int', null)
		)),
	);

	public $auth_exceptions = array();

	/** @var array Basic filter settings */
	public $filter_basic = array(
	);

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
	 * Loads a property and checks the user's permissions.
	 *
	 * @param int $id
	 * @throws Exception
	 * @return Property
	 */
	private function getPropertyById($id, PropelPDO $con = null) {
		$user = $this->requireUser();

		$property = PropertyQuery::create()
			->filterByAccount($user->getAccount($con))
			->findOneById($id, $con);

		if ( $property === null )
			throw new Exception('Property #'.$id.' does not exist or belongs to a different account.');

		return $property;
	}

	/**
	 * Lists the properties
	 *
	 * @param string $search Optional. Text to search for.
	 * @param string $domain Optional. The domain to restrict the list to.
	 * @param string $orderBy Optional. Sort column name.
	 * @param string $orderMode Optional. Order mode (asc or desc).
	 * @return array
	 */
	public function do_list($search = null, $domain = null, $orderBy = 'Name', $orderMode = 'asc') {
		$user = $this->requireUser();

		$account = $user->getAccount();

		$query = PropertyQuery::create()
			->setDistinct()
			->filterByAccount($account);

		if ( (string)$domain === '' ) {
			$query->leftJoinPropertyValue();
		} else {
			$query
				->joinPropertyValue()
				->join('PropertyValue.Domain')
				->add(DomainPeer::NAME, $domain);
		}

		if ( $orderMode != 'asc' )
			$orderMode = 'desc';

		switch ( $orderBy ) {
			default: // Name
				$query->orderByName($orderMode);
				break;
		}

		if ( (string)$search !== '' ) {
			$search = '%'.$search.'%';
			$searchCriterion = $query->getNewCriterion(PropertyPeer::NAME, $search, Criteria::LIKE);
			$searchCriterion->addOr($query->getNewCriterion(PropertyPeer::LABEL, $search, Criteria::LIKE));
			$searchCriterion->addOr($query->getNewCriterion(PropertyPeer::DESCRIPTION, $search, Criteria::LIKE));
			$searchCriterion->addOr($query->getNewCriterion(PropertyPeer::DEFAULT_VALUE, $search, Criteria::LIKE));
			$searchCriterion->addOr($query->getNewCriterion(PropertyValuePeer::VALUE, $search, Criteria::LIKE));
			$query->add($searchCriterion);
		}

		$result = array();

		$properties = $query->find();
		$properties->populateRelation('PropertyValue');
		foreach ($properties as $property) { /* @var $property Property */
			$values = array();
			foreach ($property->getPropertyValues() as $propertyValue)
				$values[] = $propertyValue->toArray();

			$result[] = $property->toArray() + array(
				'Values' => $values,
			);
		}

		return $result;
	}

	/**
	 * Display the property details
	 *
	 * @param int $id The property ID
	 * @return array
	 */
	public function do_details($id) {
		return $this->getPropertyById($id)
			->toArray();
	}

	/**
	 * Adds a new property
	 *
	 * @param array $data
	 * @return int The property ID
	 */
	public function do_add($data) {
		return $this->do_update(null, $data);
	}

	/**
	 * Updates a property
	 *
	 * @param int $id The property ID
	 * @param array $data
	 * @return int The property ID
	 */
	public function do_update($id, $data = null) {
		$user = $this->requireUser();
		if ( !$user->isAdmin() )
			throw new Exception('Only administrators are allowed to edit properties.');

		// Validate input data
		$validator = new KickstartValidator();
		$locale = Localizer::getInstance();
		$warnings = $validator->filterErrors($data, $this->initFilter($this->filter_basic, $locale));
		if ( $warnings )
			return array('result' => false, 'warnings' => $warnings);

		$query = PropertyQuery::create()->filterByAccount($user->getAccount());

		if ( $id !== null ) {
			$query->filterById($id, Criteria::NOT_EQUAL);

			$property = PropertyQuery::create()
				->filterByAccount($user->getAccount())
				->findOneById($id);

			if ( !$property )
				throw new Exception('Property not found; ID: '.$id);
		} else {
			$property = new Property();
		}

		// Check for duplicates
		if ( isset($data['Name']) and $query->findOneByName($data['Name']) )
			throw new Exception($locale->insert('error.taken', array('value' => '"'.$data['Name'].'"')));

		unset($data['Id']);

		$property->fromArray($data);
		$property->setAccount($user->getAccount());
		$property->save();

		return $property->getId();
	}

}
