<?php

/**
 * API functions to manage USERS
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package ZfxSupport
 * @version 1.1 (2012-01-17)
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class AccountAPI extends API {
	public $actions = array(
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all account
		 * @param {string} search
		 * @return {array} List of users [{id, name}, ...]
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('search', 'string', null, false),
		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Shows details for the authenticated user's company account
		 * @param {int} id*
		 * @return {array}
		 * @demo
		 */
		'details' => array('ANY', 'details', array(
		)),
		/*!
		 * @cmd add
		 * @method any
		 * @description Adds a new account
		 * @param {string} name* The user name
		 * @return {int} The user database ID
		 * @demo
		 */
		'add' => array('POST', 'add', array(
			array('name', 'string', null),
		)),
		/*!
		 * @cmd update
		 * @method post
		 * @description Updates an account
		 * @param {int} id*
		 * @param {array} data* The account details (Name, AddressId, Properties)
		 * @return {bool}
		 * @demo
		 */
		'update' => array('POST', 'update', array(
			array('id', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd delete
		 * @method any
		 * @description Deletes an account
		 * @param {int} id*
		 * @return {array}
		 * @demo
		 */
		'delete' => array('POST', 'delete', array(
			array('id', 'int', null),
		))
	);

	public $auth_exceptions = array('auth');

	/** @var array Basic filter settings */
	public $filter_basic = array(
		'Name' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 1,
			'field'  => 'entity.account.singular'
		),
	);

	/**
	 * User or token authentication
	 *
	 * @see API::auth()
	 * @return bool
	 * @todo Implement MASTER authentication
	 */
	public function auth() {
		return true;
	}

	/**
	 * Lists accounts.
	 *
	 * @param string $strSearch Search query
	 * @return array
	 */
	public function do_list($strSearch = null) {
		$query = new AccountQuery();

		$criteria = new Criteria();
		if ( $strSearch )
			$criteria->add(AgentPeer::NAME, '%'.$strSearch.'%', Criteria::LIKE);

		$res = array();
		foreach ($query->find($criteria) as $account)
			$res[] = $account->toArray();

		return $res;
	}

	/**
	 * Returns the current user's company account details.
	 *
	 * @return array
	 */
	public function do_details() {
		$user = $this->requireUser(); /* @var User $user */
		if ( !$user->getIsAdmin() )
			throw new Exception('User "'.$user->getName().'" is not allowed to access company account.');

		$account = $user->getAccount();
		if ( $account === null )
			throw new Exception('Could not retrieve company account of user "'.$user->getName().'".');

		$address = $account->getAddress();

		return $account->toArray() + array(
			'Address'    => ( $address === null ? null : $address->toArray() ),
			'Properties' => $account->getProperties(),
		);
	}

	/**
	 * Adds a new acccount
	 *
	 * @param string $strName
	 * @param int $intDomainId
	 * @param string $strPassword
	 * @return int The user ID
	 */
	public function do_add($strName, $intDomainId, $strPassword) {
		$strName = strtolower($strName);
		// Only letters, numbers and the dash symbol are allowed!
		if ( preg_match('/[^a-z0-9\-]/', $strName) !== 0 || strlen($strName) > 45 )
			throw new Exception('Invalid account name');

		$account = new Account();
		$account
			->setName($strName)
			->save();

		return $account->getId();
	}

	/**
	 * Updates an account.
	 *
	 * @param int $id
	 * @param array $data
	 * @return int The account ID
	 */
	public function do_update($id, $data) {
		$account = null;

		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$user      = $this->requireUser();

			// Validate input data
			$validator = new KickstartValidator();
			$locale    = Localizer::getInstance();
			$warnings  = $validator->filterErrors($data, $this->initFilter($this->filter_basic, $locale));
			if ( $warnings ) {
				$con->rollBack();
				return array('result' => false, 'warnings' => $warnings);
			}

			if ( $id === null ) {
				$account = new Account();
			} else {
				$account = AccountQuery::create()->findOneById($id, $con);
				if ( ($account === null) or
				     ($account !== $user->getAccount($con)) or
				     !$user->getIsAdmin() )
					throw new Exception('Account #'.$id.' not found or no permission to update it.');

				// Check for duplicates
				if ( isset($data['Name']) ) {
					$otherAccount = AccountQuery::create()
						->filterById($account->getId(), Criteria::NOT_EQUAL)
						->findOneByName($data['Name'], $con);
					if ( $otherAccount !== null )
						throw new Exception($locale->insert('error.taken', array('value' => '"'.$data['Name'].'"')));
				}
			}

			$account->fromArray(array_intersect_key($data, array(
				'Name' => true,
			)));
			$account->save($con);

			if ( !empty($data['Address']) ) {
				$address = $account->getAddress($con);
				if ( $address === null ) {
					$address = new Address();
					$address->setAccount($account);
				}

				$address->fromArray(array_intersect_key($data['Address'], array(
					'Company'   => true,
					'Firstname' => true,
					'Lastname'  => true,
					'Address'   => true,
					'Zipcode'   => true,
					'City'      => true,
					'State'     => true,
					'Province'  => true,
					'Country'   => true,
					'Phone'     => true,
					'Fax'       => true,
					'Website'   => true,
					'Email'     => true,
					'Vatid'     => true,
				)));
				$address->save($con);
			}

			if ( !empty($data['Properties']) )
				$account->setProperties($data['Properties'], $con);

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $account->getId();
	}

	/**
	 * Deletes an account.
	 *
	 * @param int $id The account ID
	 * @return bool Returns TRUE if deletion was successful.
	 */
	public function do_delete($id) {
		$account = AccountQuery::create()->findOneById($id);
		if ( $account === null )
			throw new Exception('Account #'.$id.' not found or no permission to remove it.');

		$account->delete();
		return true;
	}
}
