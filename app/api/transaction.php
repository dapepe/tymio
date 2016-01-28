<?php

include_once 'lib/tymio/settings.php';
include_once 'lib/tymio/util.inc.php';

// Register plugin events
PluginPeer::registerEvent('transaction', 'add');

/**
 * API functions to manage TRANSACTIONs
 *
 * @author Huy Hoang Nguyen
 * @package tymio
 * @copyright Copyright (c) 2012, Groupion GmbH & Co. KG
 */
class TransactionAPI extends API {
	public $actions = array(
		/*!
			@cmd types
			@description Lists all booking types assigned to the authenticated user's company account, optionally with balances
			@param {int} user If supplied, include balances for the specified user ID
			@param {int} start Limit balances to transactions ending after this date
			@param {int} end Limit balances to transactions beginning before this date
			@param {bool} deleted If true, include deleted transactions in calculation. Default is false
			@return {array} List of booking types
			@demo
		*/
		'types' => array('ANY', 'types', array(
			array('user', 'int', null, false),
			array('start', 'int', null, false),
			array('end', 'int', null, false),
			array('deleted', 'bool', false, false),
		)),
		/*!
			@cmd list
			@description Lists all transactions
			@param {string} user
			@param {int} start Limit to transactions ending after this date
			@param {int} end Limit to transactions beginning before this date
			@param {bool} showdeleted Specifies whether to show deleted transactions
			@param {string} ordermode Order mode ("asc" or "desc"; default: desc)
			@param {string} orderby Order by key (start*, end, username, flexitime, overtime, denied)
			@param {int} limit The max. number of results to yield
			@param {int} offset The offset of the first result to return
			@return {array} List of all transactions [{ID, creator, username, start, end, creationdate, time, flexitime, overtime, denied, clockings}, ...]
			@demo
		*/
		'list' => array('ANY', 'list', array(
			array('user', 'int', null, false),
			array('start', 'int', null, false),
			array('end', 'int', null, false),
			array('showdeleted', 'bool', false, false),
			array('ordermode', 'string', false, false),
			array('orderby', 'string', null, false),
			array('limit', 'int', null, false),
		)),
		/*!
			@cmd add
			@description Creates transactions from clockings
			@param {array} clockings*
			@param {bool} commit
			@return {bool}
			@demo
		*/
		'add' => array('POST', 'add', array(
			array('clockings', 'array', null, true),
			array('commit', 'bool', true, false),
		)),
		/*!
			@cmd create
			@description Adds a new explicitly defined transaction
			@param {array} transaction* Transaction data: { "UserId": ..., "Start": ..., "Comment": ... }
			@param {array} bookings* An array of bookings. Existing booking are supplied by ID, new bookings must be specified as objects: { "BookingTypeId": ..., "Label": ..., "Value": ... }
			@param {array} clockings An array of clocking IDs
			@return {bool}
			@demo
		*/
		'create' => array('POST', 'create', array(
			array('transaction', 'array', false),
			array('bookings', 'array', null, false),
			array('clockings', 'array', null, false),
		)),
		/*!
		 * @cmd remove
		 * @method any
		 * @description Marks a single transaction as deleted.
		 * @param {int} id* The transaction's ID
		 * @demo
		 */
		'remove' => array('POST', 'remove', array(
			array('id', 'int'),
		)),
		/*!
			@cmd list_bookings
			@description Lists bookings
			@param {int} user
			@param {int} start Limit to transactions ending after this date
			@param {int} end Limit to transactions beginning before this date
			@param {array} types An array with IDs of the booking types to return
			@param {bool} showdeleted Specifies whether to show bookings of deleted transactions
			@param {string} ordermode Order mode ("asc" or "desc"; default: desc)
			@param {string} orderby Order by key (start*, end, username, flexitime, overtime, denied)
			@return {array} List of all transactions [{ID, creator, username, start, end, creationdate, time, flexitime, overtime, denied, clockings}, ...]
			@demo
		*/
		'list_bookings' => array('ANY', 'list_bookings', array(
			array('user', 'int', null, false),
			array('start', 'int', null, false),
			array('end', 'int', null, false),
			array('types', 'array', null, false),
			array('showdeleted', 'bool', false, false),
			array('ordermode', 'string', false, false),
			array('orderby', 'string', null, false)
		)),
	);

	public $auth_exceptions = array('auth');

	private function findUserById($id, PropelPDO $con = null) {
		$user    = $this->requireUser();
		$account = $user->getAccount($con);
		if ( $account === null )
			throw new Exception('Could not determine the account the authenticated user "'.$user->getName().'" #'.$user->getId().' belongs to.');

		return UserQuery::create()
			->innerJoinDomain()
			->add(DomainPeer::ACCOUNT_ID, $account->getId())
			->findOneById($id, $con);
	}

	/**
	 * Creates a query that is restricted to transactions belonging to the specified account.
	 * This function will LEFT JOIN to "transaction_booking", "booking" and
	 * "booking_type". Use {@link TransactionQuery::with()} if you wish to
	 * fetch records from the joined tables.
	 *
	 * @param User $user The user object.
	 * @return TransactionQuery
	 */
	private function createTransactionQuery(User $user, PropelPDO $con = null) {
		$account = $user->getAccount($con);
		if ( $account === null )
			throw new Exception('Could not determine account the user "'.$user->getName().'" belongs to.');

		$query = TransactionQuery::create()
			->leftJoinBooking()
			->leftJoin('Booking.BookingType');

		$accessCriterion = $query->getNewCriterion(TransactionPeer::USER_ID, $user->getId());

		if ( $user->isAdmin() )
			$accessCriterion->addOr($query->getNewCriterion(BookingTypePeer::ACCOUNT_ID, $account->getId()));

		return $query
			->add($accessCriterion);
	}

	/**
	 * Creates a query that is restricted to bookings belonging to the specified account.
	 * This function will INNER JOIN "transaction_booking", "transaction" and
	 * "booking_type" and will fetch the latter. Use {@link BookingQuery::with()}
	 * if you wish to fetch records from the joined tables.
	 *
	 * @param User $user The user object.
	 * @return BookingQuery
	 */
	private function createBookingQuery(User $user, PropelPDO $con = null) {
		$account = $user->getAccount($con);
		if ( $account === null )
			throw new Exception('Could not determine account the user "'.$user->getName().'" belongs to.');

		$query = BookingQuery::create()
			->joinTransaction()
			->joinWithBookingType();

		$accessCriterion = $query->getNewCriterion(TransactionPeer::USER_ID, $user->getId());

		if ( $user->isAdmin() )
			$accessCriterion->addOr($query->getNewCriterion(BookingTypePeer::ACCOUNT_ID, $account->getId()));

		return $query
			->add($accessCriterion);
	}

	/**
	 * Runs an SQL UPDATE on the specified clockings to set their "frozen" column.
	 *
	 * @param bool $freeze
	 * @param array|PropelObjectCollection $clockings
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return void
	 */
	private function freezeClockings($freeze, $clockings, PropelPDO $con) {
		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$clockingIds = array();
			foreach ($clockings as $clocking)
				$clockingIds[] = $clocking->getId();

			ClockingQuery::create()
				->filterById($clockingIds, Criteria::IN)
				->update(array('Frozen' => ( $freeze ? 1 : 0 )), $con);
		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');
	}

	/**
	 * Creates a {@link Booking} object from array data without saving it.
	 *
	 * @param array $bookingItem An associative array with data that can be
	 *     passed to {@link Booking::fromArray()}.
	 * @param array $bookingTypeIds An associative array with booking type IDs
	 *     (in the keys) to allow.
	 * @return Booking The created booking.
	 */
	private function createBooking(array $bookingItem, array $bookingTypeIds, PropelPDO $con) {
		$booking = new Booking();
		$booking->fromArray(array_intersect_key($bookingItem, array(
			'BookingTypeId' => true,
			'Label'         => true,
			'Value'         => true,
		)));

		$bookingTypeId = $booking->getBookingTypeId();
		if ( empty($bookingTypeIds[$bookingTypeId]) )
			throw new Exception('Invalid booking type ID #'.$bookingTypeId.' specified for booking: '.json_encode($bookingItem));

		return $booking;
	}

	/**
	 * Creates {@link Booking} objects from array data.
	 *
	 * @param array $bookingData An associative array mapping internal
	 *     identifiers to booking record data that can be passed to
	 *     {@link Booking::fromArray()}. The internal identifiers can be
	 *     referenced in the transaction data.
	 * @param array $bookingTypeIds An associative array with booking type IDs
	 *     (in the keys) to allow.
	 * @return array An associative array mapping internal booking identifiers
	 *     to {@link Booking} objects.
	 */
	private function createBookings(array $bookingData, array $bookingTypeIds, PropelPDO $con) {
		$bookingsByKey = array();

		foreach ($bookingData as $bookingKey => $bookingItem)
			$bookingsByKey[$bookingKey] = $this->createBooking($bookingItem, $bookingTypeIds, $con);

		return $bookingsByKey;
	}

	/**
	 * @param Transaction $transaction
	 * @param array $bookingsByKey
	 * @param array $bookingKeys An array of booking data or booking references,
	 *    which are arbitrary (non-database) indexes as specified in the
	 *    booking list passed to {@link createBookingsTransactions()}.
	 * @param array $bookingTypeIds
	 * @param PropelPDO $con Optional. The database connection to use.
	 *     Default is NULL.
	 * @return array The {@link $bookingKeys} array with only the booking
	 *     references but no immediate booking data.
	 */
	private function linkTransactionBookings(Transaction $transaction, array $bookingsByKey, array $bookingKeys, array $bookingTypeIds, PropelPDO $con) {
		$result = array();

		foreach ($bookingKeys as $bookingKey) {
			if ( is_array($bookingKey) ) {
				$booking = $this->createBooking($bookingKey, $bookingTypeIds, $con);
			} elseif ( !isset($bookingsByKey[$bookingKey]) ) {
				throw new Exception('Invalid reference to booking with internal identifier #'.$bookingKey.' (keys were '.json_encode($bookingKeys).').');
			} else {
				$booking = $bookingsByKey[$bookingKey];
				$result[$bookingKey] = true;
			}

			$booking
				->setTransaction($transaction)
				->save($con);
		}

		return array_keys($result);
	}

	/**
	 * Links a transaction with the specified clockings.
	 *
	 * @return void
	 */
	private function linkTransactionClockings(Transaction $transaction, $clockings, PropelPDO $con) {
		foreach ($clockings as $clocking) {
			/*
			$approvalStatus = $clocking->getApprovalStatus();
			switch ( $approvalStatus ) {
				case ClockingPeer::APPROVAL_STATUS_PRELIMINARY:
				case ClockingPeer::APPROVAL_STATUS_REQUIRED:
					$clocking->setApprovalStatus(ClockingPeer::APPROVAL_STATUS_CONFIRMED);
					break;

				case ClockingPeer::APPROVAL_STATUS_CONFIRMED:
				case ClockingPeer::APPROVAL_STATUS_AS_IS:
					break;

				case ClockingPeer::APPROVAL_STATUS_DENIED:
					throw new Exception($clocking->__toString().' has been denied and therefore cannot be used in a transaction.');

				default:
					throw new Exception($clocking->__toString().' has an invalid approval status '.$approvalStatus.'.');
			}
			*/

			$transactionClocking = new TransactionClocking();
			$transactionClocking
				->setTransaction($transaction)
				->setClocking($clocking)
				->save($con);
		}
	}

	/**
	 * Creates booking and transaction records from array data.
	 *
	 * @param User $authUser The user creating the transactions.
	 * @param User $user The user to associate the transactions with.
	 * @param array $bookingData An associative array mapping internal
	 *     identifiers to booking record data that can be passed to
	 *     {@link Booking::fromArray()}. The internal identifiers can be
	 *     referenced in the transaction data.
	 * @param array $bookingTypeIds An associative array with booking type IDs
	 *     (in the keys) to allow.
	 * @param array $transactionData An array of transaction record data
	 *     suitable for {@link Transaction::fromArray()} and with two additional
	 *     properties:
	 *     - "Bookings" with an array of internal booking identifiers and
	 *     - "Clockings" with an array of clocking IDs to link to the transaction.
	 * @return array An array with two elements:
	 *     1. An associative array mapping IDs of clockings for which
	 *        transactions were created to themselves.
	 *     2. An array of the created transactions.
	 */
	private function createBookingsTransactions(User $authUser, User $user, array $bookingData, array $bookingTypeIds, array $transactionData, PropelPDO $con) {
		$resultClockingIds = array();
		$transactions      = array();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			// Create bookings
			$bookingsByKey    = $this->createBookings($bookingData, $bookingTypeIds, $con);
			$orphanedBookings = $bookingsByKey;

			// Create transactions and link them to clockings and new bookings
			foreach ($transactionData as $transactionItem) {
				if ( !isset($transactionItem['Start'], $transactionItem['End']) )
					throw new Exception('Invalid transaction data (missing start and/or end date): '.json_encode($transactionItem));
				elseif ( empty($transactionItem['Clockings']) ) // This could be allowed to support artificial transactions
					throw new Exception('Plugins created a transaction not linked to any clockings: '.json_encode($transactionItem));

				$clockingIds = $transactionItem['Clockings'];
				if ( !is_array($clockingIds) )
					throw new Exception('"Clockings" property of transaction record must specify an array of clocking database IDs. Transaction record: '.json_encode($transactionItem));

				$transaction = new Transaction();
				$transaction->fromArray(array_intersect_key($transactionItem, array(
					'Start'   => true,
					'End'     => true,
					'Comment' => true,
					'Type'    => true,
				)));
				$transaction
					->setUserRelatedByCreatorId($authUser)
					->setUserRelatedByUserId($user)
					->save($con);

				if ( !empty($transactionItem['Bookings']) ) {
					$bookingKeys = $transactionItem['Bookings'];
					if ( !is_array($bookingKeys) )
						throw new Exception('"Bookings" property of transaction record must specify an array of internal booking identifiers. Transaction record: '.json_encode($transactionItem));

					$bookingKeys = $this->linkTransactionBookings($transaction, $bookingsByKey, $bookingKeys, $bookingTypeIds, $con);

					// Remove from orphans list
					$orphanedBookings = array_diff_key($orphanedBookings, array_fill_keys($bookingKeys, true));
				}

				if ( !empty($clockingIds) ) {
					$clockings = ClockingAPI::createClockingQuery($user, $con)
						->findPks($clockingIds, $con)
						->getArrayCopy('Id'); // Prevents duplicated rows - don't ask why, but this query produces duplicates.

					$missingClockingIds = array_diff_key(
						array_fill_keys($clockingIds, true),
						$clockings
					);
					if ( !empty($missingClockingIds) )
						throw new Exception('Could not find clockings with these IDs: '.implode(', ', array_keys($missingClockingIds)));

					$this->linkTransactionClockings($transaction, $clockings, $con);
					$resultClockingIds += array_combine($clockingIds, $clockingIds);
				}

				$transactions[] = $transaction;
			}

			if ( !empty($orphanedBookings) )
				throw new Exception('Plugins created '.count($orphanedBookings).' booking(s) not linked to any transactions.');

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return array($resultClockingIds, $transactions);
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
	 * Lists all booking types assigned to the authenticated user's company account.
	 *
	 * @param int $userId Optional. If supplied, include balances in the
	 *     results as a property "Balance" for each booking type.
	 * @param int $start Optional. The date of the earliest transactions to
	 *     yield. Default is NULL.
	 * @param int $end Optional. The date of the latest transactions to yield.
	 *     Default is NULL.
	 * @param bool $includeDeleted Optional. Default is FALSE.
	 * @return array An array of booking type details.
	 */
	public function do_types($userId = null, $start = null, $end = null, $includeDeleted = false) {
		$user = $this->requireUser();

		$types = BookingTypeQuery::create()
			->findByAccount($user->getAccount());

		if ( (string)$userId === '' )
			return EntityArray::from($types);

		$query = $this->createBookingQuery($user)
			->join('Transaction.UserRelatedByUserId')
			->join('UserRelatedByUserId.Domain')
			->join('Domain.Account');

		if ( !$includeDeleted )
			$query->add(TransactionPeer::DELETED, 0);

		if ( $end !== null )
			$query->add(TransactionPeer::START, $end, Criteria::LESS_EQUAL);
		if ( $start !== null )
			$query->add(TransactionPeer::END, $start, Criteria::GREATER_EQUAL);

		$employee = $this->findUserById($userId);
		if ( $employee === null )
			throw new Exception('Employee with ID '.$userId.' could not be found.');

		$query->add(TransactionPeer::USER_ID, $employee->getId());

		$balancesByTypeId = $query
			->withColumn('SUM(Booking.Value)', 'Balance')
			->select(array('Booking.BookingTypeId', 'Balance'))
			->groupBy('Booking.BookingTypeId')
			->find()
			->toKeyValue('Booking.BookingTypeId', 'Balance');

		$result = array();

		foreach ($types as $type) {
			$typeId = $type->getId();
			$result[] = array(
				'Balance' => ( isset($balancesByTypeId[$typeId]) ? (double)$balancesByTypeId[$typeId] : 0.0 ),
			) + EntityArray::from($type);
		}

		return $result;
	}

	/**
	 * Lists transactions.
	 *
	 * @param int userid
	 * @param int $start
	 * @param int $end
	 * @param string orderby Order by key (Start*, End, User)
	 * @param string ordermode Order mode ("asc" or "desc"; default: desc)
	 * @param int $limit Optional. The max. number of results to yield.
	 *     Max. value is 1000. Default is NULL.
	 * @param int $offset Optional. The offset of the first result to return.
	 *     Default is NULL.
	 * @return array An array of transaction items
	 */
	public function do_list($userId, $start, $end, $showDeleted = false, $ordermode = null, $orderBy = null, $limit = null, $offset = null) {
		$user = $this->requireUser();

		$query = $this->createTransactionQuery($user)
			->setDistinct()
			->join('UserRelatedByUserId')
			->join('UserRelatedByUserId.Domain')
			->join('Domain.Account');

		if ( (string)$userId !== '' ) {
			$employee = $this->findUserById($userId);
			if ( $employee === null )
				throw new Exception('Employee with ID '.$userId.' could not be found.');

			$query->filterByUserRelatedByUserId($employee);
		}

		if ( (string)$start !== '' )
			$query->filterByEnd($start, Criteria::GREATER_EQUAL);
		if ( (string)$end !== '' )
			$query->filterByStart($end, Criteria::LESS_EQUAL);

		if ( !$showDeleted )
			$query->filterByDeleted(0, Criteria::EQUAL);

		if ( $ordermode == 'asc' ) {
			$sortMethod = 'addAscendingOrderByColumn';
		} else {
			$ordermode = 'desc';
			$sortMethod = 'addDescendingOrderByColumn';
		}

		if ( (string)$limit !== '' )
			$query->setLimit(min(1000, $limit));

		if ( (string)$offset !== '' )
			$query->setOffset($offset);

		switch ( $orderBy ) {
			case 'User':
				$query->orderBy('Account.Name', $ordermode);
				$query->orderBy('Account.Identifier', $ordermode);
				$query->orderBy('UserRelatedByUserId.Name', $ordermode);
				$query->orderByStart($ordermode);
				$query->orderByEnd($ordermode);
				break;
			case 'End':
				$query->orderByEnd($ordermode);
				$query->orderByStart($ordermode);
				$query->orderBy('Account.Name', $ordermode);
				$query->orderBy('Account.Identifier', $ordermode);
				$query->orderBy('UserRelatedByUserId.Name', $ordermode);
				break;
			case 'Start':
			default:
				$query->orderByStart($ordermode);
				$query->orderByEnd($ordermode);
				$query->orderBy('Account.Name', $ordermode);
				$query->orderBy('Account.Identifier', $ordermode);
				$query->orderBy('UserRelatedByUserId.Name', $ordermode);
				break;
		}

		$query->orderById($ordermode);

		$transactions = $query->find();
		$transactions->populateRelation('Booking');

		$result = array();

		foreach ($transactions as $transaction)
			$result[] = EntityArray::from($transaction);

		return $result;
	}

	/**
	 * Creates transactions from clockings.
	 *
	 * Plugin event "transaction.add" data:
	 * - "user": { ... }      // The user object
	 * - "clockings": [ ... ]
	 * - "booking_types": { identifier: {...}, ... }
	 * - "bookings": {}        // Receives the bookings to be created
	 * - "transactions": {}    // Receives the transactions to be created
	 *
	 * @param array $clockingIds
	 * @param bool $commit Optional. If FALSE, only data about the transaction
	 *     will be returned without saving them to the database. That data can
	 *     be used to manually create a transaction.
	 *     Default is TRUE.
	 * @return array An array with IDs of clocking that have been booked.
	 * @throws Exception
	 */
	public function do_add(array $clockingIds, $commit = true) {
		if ( empty($clockingIds) )
			throw new Exception('No clockings specified.');

		$usedClockingIds = array();

		// Return value for $commit == FALSE:
		// An associative array mapping user IDs to associative arrays with the
		// items "bookings" and "transactions":
		// {
		//     [user-id-1]: {
		//         "bookings"    : {
		//             [ref-1]   : { ... }
		//         },
		//         "transactions": [
		//         ]
		//     }
		// }
		$resultData      = array();

		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$authUser        = $this->requireUser();
			$authUserId      = $authUser->getId();
			$isAdmin         = $authUser->isAdmin();
			if ( !$isAdmin )
				throw new Exception('Non-administrative user "'.$authUser->getFQN($con).'" cannot create transactions.');

 			$clockings = ClockingAPI::createClockingQuery($authUser, $con)
				->joinWith('Domain.Account')
				->joinWith('ClockingType')
				->filterById($clockingIds, Criteria::IN)
				->addAscendingOrderByColumn(ClockingPeer::START)
				->addAscendingOrderByColumn(ClockingPeer::END)
				->addAscendingOrderByColumn(ClockingTypePeer::IDENTIFIER)
				->addAscendingOrderByColumn(ClockingPeer::ID)
				->find($con);

			// Check for missing clockings
			$missingIds = array_diff_key(array_fill_keys($clockingIds, true), $clockings->getArrayCopy('Id'));
			if ( !empty($missingIds) )
				throw new Exception('Could not find clockings with the IDs '.implode(', ', $missingIds).'.');

			// Lock clocking records by writing to them
			$this->freezeClockings(true, $clockings, $con);

			// Group clockings by user
			$clockingDataByUserId = array();
			foreach ($clockings as $clocking) {
				$clockingDataByUserId[$clocking->getUserId()][] = EntityArray::from($clocking) + array(
					'Type'   => EntityArray::from($clocking->getClockingType()),
				);
			}

			$typeDataByAccount = array();

			foreach ($clockingDataByUserId as $userId => $userClockingData) {
				$user      = UserQuery::create()->findPk($userId, $con);
				$userData  = array_diff_key($user->toArray(), array('PasswordHash' => true));
				$account   = $user->getAccount($con);
				$accountId = $account->getId();

				if ( isset($typeDataByAccount[$accountId]) ) {
					list($typesById, $typeData) = $typeDataByAccount[$accountId];
				} else {
					$types     = BookingTypeQuery::create()->findByAccountId($account->getId(), $con);
					$typesById = $types->getArrayCopy('Id');
					$typeData  = $types->toArray('Identifier');
					$typeDataByAccount[$accountId] = array($typesById, $typeData);
				}

				$data = PluginPeer::fireEvent($authUser, 'transaction', 'add', array(
					'user'          => $userData,
					'clockings'     => $userClockingData,
					'booking_types' => $typeData,
					'bookings'      => array(),
					'transactions'  => array(),
				), $con);

				if ( !isset($data['bookings']) )
					$data['bookings'] = array();

				if ( empty($data['transactions']) ) {
					// Ignore if there are no bookings either, otherwise fail
					if ( !empty($data['bookings']) and is_array($data['bookings']) )
						throw new Exception('Plugins created '.count($data['bookings']).' booking(s) not linked to any transactions.');
				} elseif ( !is_array($data['bookings']) or !is_array($data['transactions']) ) {
					throw new Exception('Plugins must return array data in variables "bookings" and "transactions".');
				} else {
					// Create bookings and transactions
					list($userClockingIds, $transactions) = $this->createBookingsTransactions(
						$authUser,
						$user,
						$data['bookings'],
						$typesById,
						$data['transactions'],
						$con
					);
					$usedClockingIds += $userClockingIds;

					$resultData[$userId] = array(
						'bookings'     => array(),
						'transactions' => EntityArray::from($transactions, $con),
					);
				}
			}

			$this->freezeClockings(false, $clockings, $con);

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$commit ) {
			$con->rollBack();
			return $resultData;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return array_keys($usedClockingIds);
	}

	/**
	 * Creates a new transaction.
	 *
	 * @param array $transactionData An associative array with the properties
	 *     "UserId", "Start", "End" and "Comment".
	 * @param array $bookingData An array of booking data. Existing bookings are
	 *     specified by ID, new bookings must be specified as objects:
	 *     <code>{ "BookingTypeId": ..., "Label": ..., "Value": ... }</code>
	 * @param array $clockingIds Optional. An array of clocking IDs to assign
	 *     to the new transaction. Default is NULL.
	 * @throws Exception
	 * @return bool
	 */
	public function do_create(array $transactionData = null, array $bookingData = null, array $clockingIds = null) {
		$con = Propel::getConnection();

		if ( !$con->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$user    = $this->requireUser();
			if ( !$user->isAdmin() )
				throw new Exception('Non-administrative user "'.$user->getFQN($con).'" cannot create transactions.');

			$account = $user->getAccount($con);
			if ( $account === null )
				throw new Exception('Could not determine the account the authenticated user "'.$user->getName().'" #'.$user->getId().' belongs to.');

			if ( empty($bookingData) )
				throw new Exception('Transaction must contain at least one booking.');

			$transaction = new Transaction();
			$transaction->fromArray(array_intersect_key($transactionData, array(
				'UserId'  => true,
				'Start'   => true,
				'End'     => true,
				'Comment' => true,
			)));

			$employeeId = $transaction->getUserId();
			$employee   = $this->findUserById($employeeId, $con);
			if ( $employee === null )
				throw new Exception('Employee with ID '.$employeeId.' could not be found.');

			// Separate new and existing bookings (the latter are specified by ID)
			$bookingIds   = array();
			foreach ($bookingData as $bookingIndex => $bookingItem) {
				if ( !is_array($bookingItem) ) {
					$bookingIds[$bookingItem] = $bookingItem;
					unset($bookingData[$bookingIndex]);
				}
			}

			$bookingTypeIds = BookingTypeQuery::create()
				->findByAccountId($account->getId(), $con)
				->getArrayCopy('Id');

			$newBookings  = array_values($this->createBookings($bookingData, $bookingTypeIds, $con));

			// Load existing bookings and check for missing records
			$bookingsById = BookingQuery::create()
				->joinBookingType()
				->add(BookingTypePeer::ACCOUNT_ID, $user->getAccountId())
				->findPks($bookingIds)
				->getArrayCopy('Id');

			$missingBookingIds = array_diff_key($bookingIds, $bookingsById);
			if ( count($missingBookingIds) > 0 )
				throw new Exception('Could not find bookings with the following IDs: '.implode(', ', array_keys($missingBookingIds)));

			if ( empty($clockingIds) ) {
				$clockings = array();
			} else {
				$clockings = ClockingQuery::create()
					->filterByUserRelatedByUserId($employee)
					->addAscendingOrderByColumn(ClockingPeer::START)
					->addAscendingOrderByColumn(ClockingPeer::END)
					->addAscendingOrderByColumn(ClockingPeer::ID)
					->findPks($clockingIds, $con);
				$clockingsById = $clockings->getArrayCopy('Id');

				$missingClockingIds = array_diff_key(array_fill_keys($clockingIds, true), $clockingsById);
				if ( count($missingClockingIds) > 0 )
					throw new Exception('Could not find clockings of user "'.$employee->getName().'" #'.$employeeId.' with the following IDs: '.implode(', ', array_keys($missingClockingIds)));
			}

			$start = ( isset($transactionData['Start']) ? $transactionData['Start'] : null );
			if ( (string)$start === '' )
				throw new Exception('Start date must be specified for transaction.');

			$end   = ( isset($transactionData['End']) ? $transactionData['End'] : null );
			if ( (string)$end === '' )
				throw new Exception('End date must be specified for transaction.');

			$transaction->save($con);

			// Link bookings to transaction
			foreach (array_merge(array_values($bookingsById), $newBookings) as $booking) {
				$booking
					->setTransaction($transaction)
					->save($con);
			}

			// Link clockings
			$this->linkTransactionClockings($transaction, $clockings, $con);

		} catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}

		if ( !$con->commit() )
			throw new Exception('Could not commit transaction.');

		return true;
	}

	/**
	 * Deletes an existing transaction.
	 *
	 * @param int $id* The transaction ID
	 * @return bool
	 */
	public function do_remove($id) {
		$user = $this->requireUser();

		$transaction = $this->createTransactionQuery($user)
			->filterByDeleted(0)
			->findPk($id);
		if ( $transaction === null )
			throw new Exception('Transaction with ID '.$id.' not found');

		$transaction->setDeleted(true);
		$transaction->save();

		return true;
	}

	/**
	 * Lists bookings.
	 *
	 * @param int userid
	 * @param int $start
	 * @param int $end
	 * @param array typeIds Optional. An array with IDs of the booking types to
	 *     limit the results to. Default is NULL.
	 * @param bool $showDeleted Optional. Default is FALSE.
	 * @param string orderby Order by key (start*, end, username, flexitime, overtime, denied)
	 * @param string ordermode Order mode ("asc" or "desc"; default: desc)
	 * @return array An array of booking items
	 */
	public function do_list_bookings($userId, $start, $end, array $typeIds = null, $showDeleted = false, $ordermode = null, $orderBy = null) {
		$user = $this->requireUser();

		$query = $this->createBookingQuery($user)
			->join('Transaction.UserRelatedByUserId')
			->join('UserRelatedByUserId.Domain')
			->join('Domain.Account');

		if ( (string)$userId !== '' ) {
			$employee = $this->findUserById($userId);
			if ( $employee === null )
				throw new Exception('Employee with ID '.$userId.' could not be found.');

			$query->add(TransactionPeer::USER_ID, $employee->getId());
		}

		if ( (string)$start !== '' )
			$query->add(TransactionPeer::END, $start, Criteria::GREATER_EQUAL);
		if ( (string)$end !== '' )
			$query->add(TransactionPeer::START, $end, Criteria::LESS_EQUAL);

		if ( !empty($typeIds) )
			$query->filterByBookingTypeId($typeIds, Criteria::IN);

		if ( !$showDeleted )
			$query->add(TransactionPeer::DELETED, 0, Criteria::EQUAL);

		if ( $ordermode == 'asc' ) {
			$sortMethod = 'addAscendingOrderByColumn';
		} else {
			$ordermode = 'desc';
			$sortMethod = 'addDescendingOrderByColumn';
		}

		switch ( $orderBy ) {
			case 'User':
				$query->orderBy('Account.Name', $ordermode);
				$query->orderBy('Account.Identifier', $ordermode);
				$query->orderBy('UserRelatedByUserId.Name', $ordermode);
				$query->orderBy('Transaction.Start', $ordermode);
				$query->orderBy('Transaction.End', $ordermode);
				break;
			case 'End':
				$query->orderBy('Transaction.End', $ordermode);
				$query->orderBy('Transaction.Start', $ordermode);
				$query->orderBy('Account.Name', $ordermode);
				$query->orderBy('Account.Identifier', $ordermode);
				$query->orderBy('UserRelatedByUserId.Name', $ordermode);
				break;
			case 'Start':
			default:
				$query->orderBy('Transaction.Start', $ordermode);
				$query->orderBy('Transaction.End', $ordermode);
				$query->orderBy('Account.Name', $ordermode);
				$query->orderBy('Account.Identifier', $ordermode);
				$query->orderBy('UserRelatedByUserId.Name', $ordermode);
				break;
		}

		$query->orderById($ordermode);

		$result = array();
		foreach ($query->find() as $booking)
			$result[] = EntityArray::from($booking);

		return $result;
	}

}
