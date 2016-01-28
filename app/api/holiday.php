<?php

// Register Plugin-Events
PluginPeer::registerEvent('holiday', 'create');
PluginPeer::registerEvent('holiday', 'modify');
PluginPeer::registerEvent('holiday', 'remove');

/**
 * API functions to manage HOLIDAYS
 *
 * @author Peter-Christoph Haider (Project Leader) et al.
 * @package ZfxSupport
 * @version 1.1 (2012-01-17)
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class HolidayAPI extends API {
	public $actions = array(
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all holidays of a domain
		 * @param {string} search Search query
		 * @param {timestamp} start Start date
		 * @param {timestamp} end End date
		 * @param {int} domain Filter by domain
		 * @param {string} orderby Order result by "Date" (default) or "Name"
		 * @param {string} ordermode Order mode "asc" (default) or "desc"
		 * @return {array} List of holidays [{id, name}, ...]
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('search', 'string', null, false),
			array('start', 'int', null, false),
			array('end', 'int', null, false),
			array('domain', 'int', null, false),
			array('orderby', 'string', null, false),
			array('ordermode', 'string', null, false),
		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Deletes a single holiday
		 * @param {int} id*
		 * @return {array} [Name:{string}, Date:{string}, Domains:{array}]
		 * @demo
		 */
		'details' => array('ANY', 'details', array(
			array('id', 'int', null),
		)),
		/*!
		 * @cmd add
		 * @method any
		 * @description Adds a new holiday to a domain
		 * @param {array} data* The holiday details (Name:{string}, Date:{string}, Domains:{array})
		 * @return {int} The user database ID
		 * @demo
		 */
		'add' => array('ANY', 'add', array(
			array('data', 'array', null),
		)),
		/*!
		 * @cmd update
		 * @method any
		 * @description Updates the holiday date and name
		 * @param {int} id*
		 * @param {int} date*
		 * @param {string} name*
		 * @return {bool}
		 * @demo
		 */
		'update' => array('ANY', 'update', array(
			array('id', 'int', null),
			array('data', 'array', null),
		)),
		/*!
		 * @cmd erase
		 * @method any
		 * @description Deletes a single holiday
		 * @param {int} id*
		 * @return {bool}
		 * @demo
		 */
		'erase' => array('ANY', 'erase', array(
			array('id', 'int', null),
		))
	);

	public $auth_exceptions = array('auth');

	/** @var array Basic filter settings */
	public $filter_basic = array(
		'Name' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 2,
			'field'  => 'field.name'
		),
		'Date' => array(
			'filter'  => FILTER_VALIDATE_INT,
			'message' => 'not_a_date',
			'field'   => 'field.start'
		),
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
	 * Lists the holidays (of a domain)
	 *
	 * @param string $strSearch Search query
	 * @param int $intStart The start timestamp for date range filtering
	 * @param int $intEnd The end timestamp for date range filtering
	 * @param int $intDomain Filter by domain
	 * @param string $strOrderby OrderBy Column
	 * @param string $strOrderMode Order mode (asc or desc)
	 * @return array
	 */
	public function do_list($strSearch = null, $intStart = null, $intEnd = null, $intDomain = null, $strOrderby = 'Name', $strOrderMode = 'asc') {
		$user = $this->requireUser();

		$account = $user->getAccount();

		$query = new HolidayQuery();
		$query->filterByAccount($account);

		if ( $strOrderMode != 'asc' )
			$strOrderMode = 'desc';

		switch ($strOrderby) {
			case 'Name':
				$query
					->orderByName($strOrderMode)
					->orderByDate($strOrderMode);
				break;
			default: // Date
				$query
					->orderByDate($strOrderMode)
					->orderByName($strOrderMode);
				break;
		}

		if ( $strSearch )
			$query->add(HolidayPeer::NAME, '%'.$strSearch.'%', Criteria::LIKE);

		if ( $intDomain ) {
			$query->joinHolidayDomain();
			if ( !$domain = DomainQuery::create()->findOneById($intDomain) )
				throw new Exception('Domain with ID '.$intDomain.' not found!');

			if ( $domain->getAccountId() != $account->getId() )
				throw new Exception('The domain does not belong to our account!');

			$query->add(HolidayDomainPeer::DOMAIN_ID, $intDomain);
		}

		if ( $intStart !== null )
			$query->filterByDate($intStart, Criteria::GREATER_EQUAL);
		if ( $intEnd !== null )
			$query->filterByDate($intEnd, Criteria::LESS_EQUAL);

		$res = array();
		foreach ($query->find() as $holiday) { /* @var Holiday $holiday */
			$domainData = array();

			$domains = DomainQuery::create()
				->joinHolidayDomain()
				->add(HolidayDomainPeer::HOLIDAY_ID, $holiday->getId())
				->find();
			foreach ($domains as $domain) { /* @var $domain Domain */
				$domainData[] = array(
					'Id'     => $domain->getId(),
					'Name'   => $domain->getName(),
					'Number' => $domain->getNumber(),
				);
			}

			$res[] = array(
				'Id'      => $holiday->getId(),
				'Name'    => $holiday->getName(),
				'Date'    => $holiday->getDate('U'),
				'Domains' => $domainData,
			);
		}

		return $res;
	}

	/**
	 * Displays the holiday details
	 *
	 * @param int $intId The holiday ID
	 * @return array
	 */
	public function do_details($intId) {
		$holiday = $this->getHolidayById($intId);

		$domains = array();
		foreach (DomainQuery::create()->joinHolidayDomain()->add(HolidayDomainPeer::HOLIDAY_ID, $holiday->getId())->find() as $domain) { /* @var $domain Domain */
			$domains[] = array(
				'Id'     => $domain->getId(),
				'Name'   => $domain->getName(),
				'Number' => $domain->getNumber()
			);
		}

		return array(
			'Date'    => $holiday->getDate('U'),
			'Domains' => $domains,
		) + $holiday->toArray();
	}

	/**
	 * Adds a new holiday to a domain
	 *
	 * @param array $arrData
	 * @return int The holiday ID
	 */
	public function do_add($arrData) {
		return $this->do_update(false, $arrData);
	}

	/**
	 * Updates a holiday
	 *
	 * @param int $intId The holiday ID
	 * @param array $arrData
	 * @return int The holiday ID
	 */
	public function do_update($intId, $arrData) {
		$user = $this->requireUser();

		// Validate input data
		$validator = new KickstartValidator();
		$locale = Localizer::getInstance();
		$warnings = $validator->filterErrors($arrData, $this->initFilter($this->filter_basic, $locale));
		if ( $warnings )
			return array('result' => false, 'warnings' => $warnings);

		if ( $intId ) {
			if ( !$holiday = HolidayQuery::create()->findOneById($intId) )
				throw new Exception('Holiday with ID '.$intId.' not found!');
		} else {
			$holiday = new Holiday();
		}

		$con = Propel::getConnection(HolidayPeer::DATABASE_NAME);
		$con->beginTransaction();

		try {

			$holiday->setName($arrData['Name'])
					 ->setDate($arrData['Date'])
					 ->setAccount($user->getAccount())
					 ->save($con);

			// Assign the domains
			if ( !(isset($arrData['Domains']) && is_array($arrData['Domains'])) )
				$arrData['Domains'] = array();

			$sub = array();
			foreach (
				HolidayDomainQuery::create()->filterByHoliday($holiday)->find()
				as $link /* @var $link HolidayDomain */
			) {
				if ( in_array($link->getDomainId(), $arrData['Domains']) )
					$sub[] = $link->getDomainId();
				else
					$link->delete($con);
			}

			$diff = array_diff($arrData['Domains'], $sub);
			if ( sizeof($diff) > 0 ) {
				// Get the account's domains
				$domainFilter = DomainQuery::create()->filterByAccount($user->getAccount())
				                                     ->add(DomainPeer::ID, $arrData['Domains'], Criteria::IN)
				                                     ->find();

				if ( sizeof($domainFilter) != sizeof($arrData['Domains']) ) {
					// Obviously there are some domains the user does not belong to
				}

				foreach (array_diff($arrData['Domains'], $sub) as $domainId) {
					$link = new HolidayDomain();
					$link->setHoliday($holiday)
					     ->setDomainId($domainId)
					     ->save($con);
				}
			}

			$con->commit();

		} catch (Exception $e) {

			$con->rollBack();
			throw $e;

		}

		return array('result' => $holiday->getId(), 'test' => $diff);
		// return $holiday->getId();
	}

	/**
	 * Deletes a holiday permanently
	 *
	 * @param int $intId The holiday ID
	 */
	public function do_erase($intId) {
		$holiday = $this->getHolidayById($intId);

		$con = Propel::getConnection(HolidayPeer::DATABASE_NAME);
		$con->beginTransaction();

		try {

			foreach ($holiday->getHolidayDomains() as $vacDomain)  /* @var $vacDomain HolidayDomain */
				$vacDomain->delete($con);

			$holiday->delete($con);

			$con->commit();

		} catch (Exception $e) {

			$con->rollBack();
			throw $e;

		}

		return true;
	}

	/**
	 * Loads a single holiday and checks the user's permissions
	 *
	 * @param int $intId
	 * @throws Exception
	 * @return Holiday
	 */
	private function getHolidayById($intId) {
		$user = $this->requireUser();

		$holiday = HolidayQuery::create()->findOneById($intId);

		if ( !$holiday )
			throw new Exception('Holiday with ID '.$intId.' not found!');

		// Check, if the holiday belongs to the user's account
		if ( $holiday->getAccountId() != $user->getAccount()->getId() )
			throw new Exception('Permission denied for Holiday ID "'.$intId.'"; Object belongs to another Account.');

		return $holiday;
	}
}
