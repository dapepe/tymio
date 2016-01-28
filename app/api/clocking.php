<?php

// Register plugin events
PluginPeer::registerEvent('clocking', 'create');
PluginPeer::registerEvent('clocking', 'update');
PluginPeer::registerEvent('clocking', 'remove');
PluginPeer::registerEvent('clocking', 'restore');
PluginPeer::registerEvent('clocking', 'calculate');

/**
 * API functions to manage CLOCKINGS
 *
 * @author Huy Hoang Nguyen <hoang.nguyen@groupion.com>
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class ClockingAPI extends API {

	/**
	 * Start date is after end date.
	 * Data: { "start": ..., "end": ... }
	 */
	const ERROR_INTERVAL   = 'interval';

	/**
	 * Another clocking is already open and must be closed first.
	 * Data: Open clocking
	 */
	const ERROR_OPEN       = 'open';

	/**
	 * Invalid break time - exceeds interval between start and end dates.
	 * Data: -
	 */
	const ERROR_BREAK      = 'break';

	/**
	 * The clocking is too far in the future.
	 * Data: -
	 */
	const ERROR_FUTURE     = 'future';

	/**
	 * Last change date or end date are earlier than the allowed time limit.
	 * Data: { "changed": ..., "end": ..., "limit": ... }
	 */
	const ERROR_TIME_LIMIT = 'timelimit';

	/**
	 * The clocking is locked for booking.
	 * Data: -
	 */
	const ERROR_LOCKED     = 'locked';

	/**
	 * The clocking overlaps with another non-whole-day clocking.
	 * Data: Conflicting clocking
	 */
	const ERROR_OVERLAP    = 'overlap';

	const SHOW_BOOKED_ALL  = 0;
	const SHOW_BOOKED_ONLY = 1;
	const SHOW_BOOKED_HIDE = 2;

	const PROPERTY_CLOCKING_TIME_LIMIT_DEFAULT = 'System.Clocking.Limit.Time.Editable';
	const PROPERTY_CLOCKING_TIME_LIMIT = 'System.Clocking.Limit.Time.Editable.{{type}}';

	/**
	 * @var array An associative array mapping actions to their definitions.
	 *     Action format:    { <name> => [ <method>, <function>, <parameters> ] }
	 *       Methods:        POST, GET, REQUEST
	 *     Parameter format: [ <name>, <type>, <default> = '', <required> = true ]
	 *       Types:          int, float, bool, array, object, string
	 */
	public $actions = array(
		/*!
		 * @cmd types
		 * @method any
		 * @description Lists the available clocking types
		 * @param {bool} wholedayonly If true, restrict results to whole-day types
		 * @return {array}
		 * @demo
		 */
		'types' => array('ANY', 'types', array(
			array('wholedayonly', 'bool', false, false),
		)),
		/*!
		 * @cmd list
		 * @method any
		 * @description Lists all clocking entries
		 * @param {timestamp} start Start date
		 * @param {timestamp} end End date
		 * @param {int) user Filter by User ID
		 * @param {int) domain Filter by Domain ID
		 * @param {bool} showdeleted Show deleted clocking entries
		 * @param {int} showbooked Show booked (1: Only show booked entries; 2: Hide all booked entries)
		 * @param {bool} wholedayonly Restrict results to whole-day clockings. Default is false.
		 * @param {string} orderby Order result by Start, End, User, Domain, etc.
		 * @param {string} ordermode Order mode "asc" (default) or "desc"
		 * @return {array} List of clocking entries [{Id, Start, ...}, ...]
		 * @demo
		 */
		'list' => array('ANY', 'list', array(
			array('start', 'int', null),
			array('end', 'int', null),
			array('user', 'int', null, false),
			array('domain', 'int', null, false),
			array('showdeleted', 'bool', false, false),
			array('showbooked', 'int', false, false),
			array('wholedayonly', 'bool', false, false),
			array('orderby', 'string', false, false),
			array('ordermode', 'string', false, false),
		)),
		/*!
		 * @cmd current
		 * @method any
		 * @description Shows the details for the currently open clocking entry
		 * @param {int} user The ID of the user to check
		 * @return {array}
		 * @demo
		 */
		'current' => array('ANY', 'current', array(
			array('user', 'int', null, false),
		)),
		/*!
		 * @cmd previous
		 * @method any
		 * @description Returns details on the most recently ending closed clocking entry (restricted to past clockings)
		 * @param {int} user The ID of the user to check
		 * @param {array} types An array of clocking type identifier names to filter by
		 * @return {array}
		 * @demo
		 */
		'previous' => array('ANY', 'previous', array(
			array('user', 'int', null, false),
			array('types', 'array', null, false),
		)),
		/*!
		 * @cmd details
		 * @method any
		 * @description Shows the details for a single clocking entries
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
		 * @description Adds a new clocking entry
		 * @param {array} data* The clocking details
		 * @return {int} The clocking database ID
		 * @demo
		 */
		'add' => array('POST', 'add', array(
			array('data', 'array', false),
		)),
		/*!
		 * @cmd update
		 * @method post
		 * @description Updates a clocking entry
		 * @param {int} id*
		 * @param {array} data* The clocking details
		 * @return {bool}
		 * @demo
		 */
		'update' => array('POST', 'update', array(
			array('id', 'int', null),
			array('data', 'array', false),
		)),
		/*!
		 * @cmd restore
		 * @method post
		 * @description Reactivates a clocking entry (after it as been removed)
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
		 * @description Removes a clocking entry
		 * @param {int} id*
		 * @return {bool}
		 * @demo
		 */
		'remove' => array('POST', 'remove', array(
			array('id', 'int', false)
		)),
	);

	public $auth_exceptions = array();

	/** @var array Basic filter settings */
	public $filter_basic = array(
	/*
		'Name' => array(
			'filter' => FILTER_VALIDATE_MIN_LENGTH,
			'len'    => 2,
			'field'  => 'entity.domain.singular'
		),
	*/
	);

	/** @var array Filter settings for domain properties */
	public $filter_properties = array();

	/**
	 * Returns a new {@link ClockingQuery} instance.
	 *
	 * The query will automatically populate the related {@link ClockingType},
	 * {@link User} and {@link Domain} objects and will join on the "transaction"
	 * table without fetching its records.
	 *
	 * @return ClockingQuery
	 */
	static public function createClockingQuery(User $authUser, PropelPDO $con = null) {
		$account = $authUser->getAccount($con);
		if ( $account === null )
			throw new Exception('Could not determine company account for user "'.$authUser->getName().'" #'.$authUser->getId().'.');

		$query = ClockingQuery::create()
			->setDistinct()
			->joinWith('ClockingType')
			->joinWith('UserRelatedByUserId')
			->joinWith('UserRelatedByUserId.Domain')
			->leftJoinTransactionClocking()
			// LEFT JOIN transaction t ON transaction_clocking.transaction_id=t.id AND (t.deleted=0 OR t.deleted IS NULL)
			->leftJoin('TransactionClocking.Transaction')
			->addJoinCondition('Transaction', 'Transaction.Deleted=0')
			->add(UserPeer::DELETED, 0)
			->add(DomainPeer::VALID, null, Criteria::NOT_EQUAL)
			->add(DomainPeer::ACCOUNT_ID, $account->getId())
			// "transaction.deleted = 0" yields NULL if "transaction.deleted" is
			// NULL but combined with "transaction.id IS NOT NULL" it works.
			->withColumn('(TransactionClocking.ClockingId IS NOT NULL AND (Transaction.Deleted=0 AND Transaction.Id IS NOT NULL))', 'Booked');

		if ( !$authUser->getIsAdmin() ) {
			$authUserId = $authUser->getId();
			$query->add(
				$query->getNewCriterion(ClockingPeer::CREATOR_ID, $authUserId)
					->addOr($query->getNewCriterion(ClockingPeer::USER_ID, $authUserId))
			);
		}

		return $query;
	}

	/**
	 * Retrieves a clocking by ID and checks the user's permission
	 *
	 * @param int $id The Clocking ID
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return Clocking
	 */
	private function getClockingById($id, PropelPDO $con = null) {
		$user = $this->requireUser(); /* @var $user User */

		/* @var $clocking Clocking */
		$clocking = $this->createClockingQuery($user, $con)
			->findOneById($id, $con);
		if ( $clocking === null )
			throw new Exception('Clocking with ID "'.$id.'" not found or no permission to access it!');

		return $clocking;
	}

	/**
	 * Finds the first open clocking.
	 *
	 * A clocking is considered open only if start and end dates are equal and
	 * if it does not have a whole-day clocking type.
	 *
	 * @param User $authUser The {@link User} object to use for authentication.
	 * @param User $user Optional. The {@link User} object. If NULL, the
	 *     authenticated user will be used. Default is NULL.
	 * @param Clocking $currentClocking Optional. The reference clocking to
	 *     exclude from the search. Default is NULL.
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return Clocking|null
	 */
	private function getOpenClocking(User $authUser, User $user = null, Clocking $currentClocking = null, PropelPDO $con = null) {
		$query = self::createClockingQuery($authUser)
			->joinClockingType()
			->filterByFrozen(0)
			->filterByDeleted(0, Criteria::EQUAL) // Ignore deleted / canceled clockings
			->add(ClockingTypePeer::WHOLE_DAY, 0) // Ignore whole-day clockings
			->add(ClockingPeer::START, ClockingPeer::START.'='.ClockingPeer::END, Criteria::CUSTOM)
			->having('NOT Booked'); // Ignore entries assigned to transactions

		if ( $currentClocking !== null )
			$query->filterById($currentClocking->getId(), Criteria::NOT_EQUAL);

		if ( $user !== null ) {
			if ( !$authUser->isAdmin() and
			     ((string)$user->getAccountId() !== (string)$authUser->getAccountId()) )
				throw new Exception('User "'.$authUser->getFQN($con).'" does not have administrative privileges to access data of user #'.$user->getId().'.');

			$query->filterByUserRelatedByUserId($user);
		}

		return $query->findOne($con);
	}

	private function pastGraceTimeExceeded(ClockingType $type, $time, $now = null) {
		$timeOffset    = abs($time - ( $now === null ? time() : $now ));
		$pastGraceTime = $type->getPastGraceTime();
		return ( ($pastGraceTime !== null) and ($timeOffset >= $pastGraceTime) );
	}

	/**
	 * Checks whether the clocking's start and end dates are within the time limit.
	 * Throws an exception if the time limit is exceeded.
	 *
	 * @return void
	 * @see pastGraceTimeExceeded()
	 */
	private function validateTimeLimits(Account $account, User $authUser, Clocking $clocking, PropelPDO $con = null) {
		$type          = $clocking->getClockingType($con);
		if ( $type === null )
			throw new Exception('Could not get clocking type with ID #'.$clocking->getTypeId().' for clocking #'.$clocking->getId().'.');

		// Check time limit in seconds
		$propertyName  = KeyReplace::replace(
			self::PROPERTY_CLOCKING_TIME_LIMIT,
			array('type' => $type->getIdentifier())
		);
		$domain        = $authUser->getDomain($con);
		$lastChanged   = $clocking->getLastChanged('U');
		$end           = $clocking->getEnd('U');

		// Check clocking-type-specific limit first, fall back to default
		$editTimeLimit = PropertyPeer::get($propertyName, $account, $domain, $authUser, $con);
		if ( $editTimeLimit === null )
			$editTimeLimit = PropertyPeer::get(self::PROPERTY_CLOCKING_TIME_LIMIT_DEFAULT, $account, $domain, $authUser, $con);

		$errorData = array(
			'changed' => $lastChanged,
			'end'     => $end,
			'limit'   => $editTimeLimit,
		);

		if ( ($editTimeLimit !== null) and !is_numeric($editTimeLimit) )
			throw new APIException(self::ERROR_TIME_LIMIT, 'Invalid non-numeric value '.json_encode($editTimeLimit).' encountered for property "'.$propertyName.'".', $errorData);

		$minTimeAllowed = time() - $editTimeLimit;

		$result = (
			((double)$end > $minTimeAllowed) and
			($clocking->isNew() or ((double)$lastChanged > $minTimeAllowed))
		);

		if ( $result )
			return;

		throw new APIException(self::ERROR_TIME_LIMIT, 'Clocking cannot be edited any more after '.round($editTimeLimit / 3600.0, 2).' hours.', $errorData);
	}

	/**
	 * User or token authentication
	 *
	 * @see API::auth()
	 * @return bool
	 * @todo Implement MASTER authentication
	 */
	public function auth() {
		if ( $this->authUser() )
			return true;

		return false;
	}

	/**
	 * Lists all available clocking types.
	 *
	 * @param bool $wholeDayOnly Optional. Default is FALSE.
	 * @return array
	 */
	public function do_types($wholeDayOnly = false) {
		$user    = $this->requireUser();
		$account = $user->getAccount();
		if ( $account === null )
			throw new Exception('Could not determine account the authenticated user belongs to.');

		$query = ClockingTypeQuery::create()
			->filterByAccount($account);

		if ( $wholeDayOnly )
			$query->filterByWholeDay(true);

		$result = array();
		foreach ($query->find() as $type)
			$result[] = EntityArray::from($type);

		return $result;
	}

	/**
	 * Lists all clocking entries
	 *
	 * @param int $intStart
	 * @param int $intEnd
	 * @param int $intUserId Optional. Default is FALSE.
	 * @param int $intDomainId Optional. Default is FALSE.
	 * @param bool $showDeleted Optional. Default is FALSE.
	 * @param int $intShowBooked Optional. Default is {@link SHOW_BOOKED_ALL}.
	 * @param bool $wholeDayOnly Optional. Restricts results to whole-day
	 *     clocking types. Default is FALSE.
	 * @param string $strOrderby
	 * @param string $strOrderMode
	 */
	public function do_list($intStart, $intEnd, $intUserId = false, $intDomainId = false, $showDeleted = false, $intShowBooked = self::SHOW_BOOKED_ALL, $wholeDayOnly = false, $strOrderby = 'Name', $strOrderMode = 'asc') {
		$user = $this->requireUser();

		$query = ClockingQuery::create()
			->setDistinct()
			->joinWith('ClockingType')
			->joinWith('UserRelatedByUserId')
			->joinWith('UserRelatedByUserId.Domain')
			->add(UserPeer::DELETED, 0)
			->add(DomainPeer::VALID, null, Criteria::NOT_EQUAL)
			->add(DomainPeer::ACCOUNT_ID, $user->getAccount()->getId())
			->filterByStart($intEnd, Criteria::LESS_EQUAL)     // Clocking must begin on or before the span's end
			->filterByEnd($intStart, Criteria::GREATER_EQUAL); // Clocking must end on or after the span's start

		if ( !$user->getIsAdmin() ) {
			$userId = $user->getId();
			$query->add(
				$query->getNewCriterion(ClockingPeer::CREATOR_ID, $userId)
					->addOr($query->getNewCriterion(ClockingPeer::USER_ID, $userId))
			);
		}

		if ( (string)$intUserId !== '' )
			$query->filterByUserId($intUserId);

		if ( $wholeDayOnly )
			$query->add(ClockingTypePeer::WHOLE_DAY, 0, Criteria::NOT_EQUAL);

		if ( $strOrderMode != 'asc' )
			$strOrderMode = 'desc';

		switch ( $strOrderby ) {
			case 'Type':
				$query->orderBy('ClockingType.Identifier', $strOrderMode);
				break;
			case 'User':
				$query->orderBy('UserRelatedByUserId.Name', $strOrderMode);
				break;
			case 'ApprovalStatus':
			case 'End':
				$query->{'orderBy'.$strOrderby}($strOrderMode);
				break;
			default: // Start
				$query->orderByStart($strOrderMode);
				break;
		}

		if ( !$showDeleted )
			$query->filterByDeleted(0);

		switch ( $intShowBooked ) {
			case self::SHOW_BOOKED_ONLY:
				$query
					->joinTransactionClocking()
					->join('TransactionClocking.Transaction')
					->add(TransactionPeer::DELETED, 0)
					->withColumn('TRUE', 'Booked');
				break;

			case self::SHOW_BOOKED_HIDE:
				$query
					->leftJoinTransactionClocking()
					->addMultipleJoin(array(
						array(TransactionClockingPeer::TRANSACTION_ID, TransactionPeer::ID),
						array(TransactionPeer::DELETED, 0),
					), Criteria::LEFT_JOIN)
					->withColumn('FALSE', 'Booked')
					->filterByFrozen(0)
					->groupByClass('Clocking')
					->having('(COUNT('.TransactionPeer::ID.') = 0)');
				break;

			case self::SHOW_BOOKED_ALL:
			default:
				$query
					->leftJoinTransactionClocking()
					->addMultipleJoin(array(
						array(TransactionClockingPeer::TRANSACTION_ID, TransactionPeer::ID),
						array(TransactionPeer::DELETED, 0),
					), Criteria::LEFT_JOIN)
					->withColumn('(COUNT('.TransactionPeer::ID.') > 0)', 'Booked')
					->groupByClass('Clocking');
				break;
		}

		$clockings = $query->find();

		$result = array();

		foreach ($clockings as $clocking) /* @var Clocking $clocking */
			$result[] = EntityArray::from($clocking) + array('Booked' => (bool)$clocking->getBooked());

		return $result;
	}

	/**
	 * Returns a clocking's details.
	 *
	 * @param int $id The clocking ID.
	 * @return array
	 */
	public function do_details($id = null) {
		$clocking = $this->getClockingById($id);
		return EntityArray::from($clocking) + array('Booked' => (bool)$clocking->getBooked());
	}

	/**
	 * Returns the details of the currently open clocking.
	 *
	 * @param int $userId Optional. The ID of the user to check. Default is NULL.
	 * @return array|null The currently open clocking, or NULL if there is none.
	 */
	public function do_current($userId = null) {
		$authUser = $this->requireUser();
		$user     = ( $userId === null ? $authUser : UserQuery::create()->findPk($userId) );
		$clocking = $this->getOpenClocking($authUser, $user);
		return (
			$clocking === null
			? null
			: EntityArray::from($clocking) + array('Booked' => (bool)$clocking->getBooked())
		);
	}

	/**
	 * Returns the details of the newest non-open, non-whole-day clocking that is not in the future.
	 *
	 * @param int $userId Optional. The ID of the user to check. Default is NULL.
	 * @param array|null $clockingTypeIdentifiers Optional. An array of clocking type
	 *     text identifiers to filter by. Default is NULL.
	 * @return array|null The clocking, or NULL if there is none.
	 */
	public function do_previous($userId = null, array $clockingTypeIdentifiers = null) {
		$authUser = $this->requireUser();
		$user     = ( $userId === null ? $authUser : UserQuery::create()->findPk($userId) );

		$query    = $this->createClockingQuery($authUser)
			->add(ClockingTypePeer::WHOLE_DAY, 0) // Skip whole-day types
			->add(ClockingPeer::START, ClockingPeer::START.'<>'.ClockingPeer::END, Criteria::CUSTOM) // Skip open clockings
			->filterByUserRelatedByUserId($user)
			->filterByEnd(time(), Criteria::LESS_EQUAL)
			->addDescendingOrderByColumn(ClockingPeer::END)
			->addDescendingOrderByColumn(ClockingPeer::START)
			->addDescendingOrderByColumn(ClockingPeer::LAST_CHANGED)
			->addDescendingOrderByColumn(ClockingPeer::CREATIONDATE);

		if ( $clockingTypeIdentifiers !== null )
			$query->add(ClockingTypePeer::IDENTIFIER, $clockingTypeIdentifiers, Criteria::IN);

		$clocking = $query->findOne();

		return (
			$clocking === null
			? null
			: EntityArray::from($clocking) + array('Booked' => (bool)$clocking->getBooked())
		);
	}

	/**
	 * Adds a new domain
	 *
	 * @param array $data
	 * @return int The domain ID
	 */
	public function do_add($data) {
		return $this->do_update(null, $data);
	}

	/**
	 * Creates or updates a clocking.
	 *
	 * @param int $id
	 * @param array $data
	 * @return int The clocking ID
	 */
	public function do_update($id, $data) {
		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		$clocking = null;

		try {
			$authUser = $this->requireUser();

			// Validate input data
			$validator = new KickstartValidator();
			$locale = Localizer::getInstance();

			// Cut off seconds to get time in full minutes
			if ( isset($data['Start']) and is_numeric($data['Start']) )
				$data['Start'] -= date('s', $data['Start']);
			if ( isset($data['End']) and is_numeric($data['End']) )
				$data['End'] -= date('s', $data['End']);

			$warnings = $validator->filterErrors($data, $this->initFilter($this->filter_basic, $locale));
			if ( $warnings )
				return array('result' => false, 'warnings' => $warnings);

			if ( (string)$id === '' ) {
				$event    = 'create';
				$clocking = new Clocking();
			} else {
				$event    = 'update';
				$clocking = $this->getClockingById($id, $con);
				if ( $clocking->getBooked() or $clocking->getFrozen() )
					throw new Exception('Cannot change clocking entry #'.$id.' because it already has bookings or is locked for booking.');
			}

			$isAdmin        = $authUser->getIsAdmin();

			$allowedColumns = array(
				// Do not allow plugins to modify the user
				'TypeId'    => true,
				'Start'     => true,
				'End'       => true,
				'Breaktime' => true,
				'Comment'   => true,
			);

			if ( $isAdmin )
				$allowedColumns['ApprovalStatus'] = true;

			$clocking->fromArray(array_intersect_key($data, array('UserId' => true) + $allowedColumns));

			$clockingUser   = $clocking->getUserRelatedByUserId($con);
			$clockingUserId = $clocking->getUserId();

			$authUserAccountId = $authUser->getAccountId();

			// Check if authenticated user may access clocking's user
			if ( ($clockingUser === null) or
			     ((string)$clockingUser->getAccountId() !== (string)$authUserAccountId) or
			     (!$isAdmin and ($clockingUser !== $authUser)) )
				throw new Exception('Invalid user #'.$clockingUserId.' specified for clocking or no permission to access that user\'s data.');

			$type = $clocking->getClockingType($con);
			if ( $type === null )
				throw new Exception('Clocking #'.$id.' has no clocking type assigned.');

			$account = $authUser->getAccount($con);
			if ( $account === null )
				throw new Exception('Could not load account of user #'.$authUser->getId().' "'.$authUser->getFQN($con).'".');

			// Check hard time limit for non-admin users
			if ( !$isAdmin )
				$this->validateTimeLimits($account, $authUser, $clocking, $con);

			$isNew = $clocking->isNew();

			// Save first to obtain an ID which may be referenced by a plugin
			$clocking->save($con);

			$clockingData = EntityArray::from($clocking, $con) + array(
				'IsNew' => $isNew,
				'Type'  => EntityArray::from($type, $con),
			);

			if ( !$isAdmin and
			     ($type->getApprovalRequired() or $this->pastGraceTimeExceeded($type, min((int)$clocking->getStart('U'), (int)$clocking->getEnd('U')))) )
				$clocking->setApprovalStatus(ClockingPeer::APPROVAL_STATUS_REQUIRED);

			$clocking->fromArray(array_intersect_key(PluginPeer::fireEvent($clockingUser, 'clocking', $event, $clockingData, $con), $allowedColumns));

			$type = $clocking->getClockingType($con); // Plugins may have changed this
			if ( ($type === null) or
			     ((string)$type->getAccountId() !== (string)$authUserAccountId) )
				throw new Exception('Clocking #'.$id.' has an invalid or unknown clocking type #'.$clocking->getTypeId().' assigned.');

			$start = (int)$clocking->getStart('U');
			$end   = (int)$clocking->getEnd('U');

			if ( $start > $end ) {
				throw new APIException(self::ERROR_INTERVAL, 'Start time ('.$clocking->getStart('Y-m-d H:i:s').') must be before end time ('.$clocking->getEnd('Y-m-d H:i:s').').', array('start' => $start, 'end' => $end));
			} elseif ( $type->getWholeDay() ) {
				// Set time of day for start and end to 00:00:00
				$clocking->setStart(strtotime(date('Y-m-d 00:00:00', $start)));
				$clocking->setEnd(strtotime(date('Y-m-d 00:00:00', $end)));
				// Set break time to 0
				$clocking->setBreaktime(0);
			} elseif ( $start === $end ) {
				// Create an open clocking entry (i.e. sign on for work).
				// Fail if there are other open entries.
				if ( ($openClocking = $this->getOpenClocking($authUser, $clockingUser, $clocking, $con)) !== null ) {
					$openComment = $openClocking->getComment();
					throw new APIException(self::ERROR_OPEN, 'Clocking #'.$openClocking->getId().( (string)$openComment === '' ? '' : ' "'.$openComment.'"' ).' from '.$openClocking->getStart('r').' to '.$openClocking->getEnd('r').' is already open. Please close that entry first.'.$openClocking->getId().' '.$clocking->getId(), $openClocking);
				}
			} elseif ( $clocking->getTime() < $clocking->getBreaktime() ) {
				throw new APIException(self::ERROR_BREAK, 'Break ('.($clocking->getBreaktime() / 60).' minutes) must be less than the specified work time ('.$clocking->getTime().' = '.$clocking->getStart('Y-m-d H:i:s').' - '.$clocking->getEnd('Y-m-d H:i:s').').');
			}

			$futureGraceTime = $type->getFutureGraceTime();
			if ( ($futureGraceTime !== null) and ($end > time() + $futureGraceTime) )
				throw new APIException(self::ERROR_FUTURE, 'Clocking type "'.$type->getIdentifier().'" #'.$type->getId().' does not allow entries in the future ('.$clocking->getStart('Y-m-d H:i:s').' - '.$clocking->getEnd('Y-m-d H:i:s').').');

			$clocking->save($con);

			$clocking->reload(false, $con);
			if ( $clocking->getFrozen() )
				throw new APIException(self::ERROR_LOCKED, 'The clocking #'.$clocking->getId().' is currently locked for booking.');

			// Check for other non-whole-day clockings with overlapping time
			if ( !$type->getWholeDay() ) {
				$firstConflict = self::createClockingQuery($authUser, $con)
					->filterById($clocking->getId(), Criteria::NOT_EQUAL)
					->filterByUserId($clockingUserId)
					->add(ClockingTypePeer::WHOLE_DAY, 0, Criteria::EQUAL)
					->filterByStart($end, Criteria::LESS_THAN)     // Clocking must begin on or before the span's end
					->filterByEnd($start, Criteria::GREATER_THAN)  // Clocking must end on or after the span's start
					->filterByDeleted(0, Criteria::EQUAL)
					->findOne($con);

				if ( $firstConflict !== null )
					throw new APIException(self::ERROR_OVERLAP, $clocking->__toString().' overlaps with '.$firstConflict->__toString().'.', $firstConflict);
			}

			SystemLogPeer::add('clocking.'.$event, $clocking, SystemLogPeer::CODE_SUCCESSFUL, null, $authUser, array(
				'clocking' => $clocking->toArray(),
			), $con);

		} catch (Exception $e) {
			$con->rollBack();
			SystemLogPeer::add('clocking.'.$event, $clocking, SystemLogPeer::CODE_FAILED, $e->getMessage(), $authUser, array(
				'exception' => $e->__toString(),
				'clocking' => $clocking->toArray(),
			), $con);
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return $clocking->getId();
	}

	/**
	 * Restores a deleted clocking entry
	 *
	 * @param int $id The domain ID
	 */
	public function do_restore($id) {
		$this->getClockingById($id)
			->setDeleted(0)
			->save();

		return true;
	}

	/**
	 * Removes a clocking entry
	 *
	 * @param int $id The clocking ID
	 */
	public function do_remove($id) {
		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$authUser = $this->requireUser($con);

			$clocking = $this->getClockingById($id, $con);

			if ( $clocking->getBooked() )
				throw new Exception('Cannot remove clocking entry #'.$id.' because it already has bookings. Please remove the transactions instead.');

			if ( !$clocking->isOpen() and !$authUser->isAdmin() ) {
				$account = $authUser->getAccount($con);
				if ( $account === null )
					throw new Exception('Could not get account of user #'.$authUser->getId().' "'.$authUser->getFQN($con).'".');

				$type = $clocking->getClockingType($con);
				if ( $type === null )
					throw new Exception('Could not get clocking type with ID #'.$clocking->getTypeId().'.');

				$this->validateTimeLimits($account, $authUser, $clocking, $con);
			}

			$clockingUser = $clocking->getUserRelatedByUserId($con);
			if ( $clockingUser === null )
				throw new Exception('Could not determine clocking\'s assigned user #'.$clocking->getUserId().'.');

			PluginPeer::fireEvent($clockingUser, 'clocking', 'remove', EntityArray::from($clocking, $con), $con);

			$clocking
				->setDeleted(1)
				->save($con);

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return true;
	}

}
