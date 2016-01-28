<?php
/**
 * Migrates the old clocking database to the new tymio database.
 *
 * IMPORTANT: This script clears ALL data from several tymio tables!
 *
 *
 *
 */

class Status {

	public $notes = array();

	private function send($html) {
		print $html;
		flush();
		ob_flush();
	}

	public function start($what) {
		$this->send('<p>'.$what.'&hellip; ');
	}

	public function done() {
		$this->send('done.<p>');
	}

	public function info($message) {
		$this->send('</p><p>&mdash; '.$message.'</p><p>');
	}

	public function takeNote($note) {
		$this->notes[] = $note;
	}

	public function showNotes() {
		print '<ul>';
		foreach ($this->notes as $note) {
			print '<li>'.$note.'</li>';
		}
		print '</ul>';
	}

}

class ClockingData {

	/**
	 * @var Clocking
	 */
	public $clocking;

	/**
	 * @var array
	 */
	public $row;

	public function __construct(Clocking $clocking, array $row) {
		$this->clocking = $clocking;
		$this->row      = $row;
	}

}

class MigrationController extends Controller {

	public function __construct($locale) {
		if ( !isset($_POST['timetoken'], $_POST['password'], $_POST['import']) or
			 ($_POST['password'] !== 'm') or
			 (abs(strtotime(($_POST['timetoken'])) - time()) > 60) )
			$this->showConfirmationForm();
		else
			$this->import();
	}

	private function showConfirmationForm() {
			?>
<html>
	<head>
		<title>Import</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	</head>
	<body>
		<p>
			Server time is <?php echo date('H:i'); ?>
		</p>
		<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
			<p>
				<label>Server time from above (import fails if time is more than a minute off):</label>
				<input type="text" name="timetoken" value="" autocomplete="off" autofocus="autofocus" />
			</p>
			<p>
				<label>Password:</label>
				<input type="password" name="password" value="" autocomplete="off" />
			</p>
			<p>
				<input type="submit" name="import" value="Daten löschen und neu importieren" />
			</p>
		</form>
	</body>
</html>
			<?php
	}

	private function import() {
		session_write_close();

		$SOURCE_DB = 'groupion_new';
		$USERNAME = 'root';
		$PASSWORD = '';

		set_time_limit(0);

		$status = new Status();

		print '<br><br><br><br><br><div style="margin-left: 3em">';
		print '<p>Starting database migration</p>';

		flush();

		try {
			$con = Propel::getConnection();

			$status->start('Connecting to database');
			$dbh = new PDO('mysql:host=localhost;dbname='.$SOURCE_DB, $USERNAME, $PASSWORD);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbh->exec('SET NAMES utf8');
			$status->done();

			$debugUser = UserQuery::create()
				->findOneByFQN('cms/test', $con);
			if ( $debugUser === null )
				throw new Exception('Could not find debug user "cms/test".');

			$status->start('Clearing tables');

			// Mark transactions created by old system as deleted
			TransactionQuery::create()
				->filterByCreatorId(null, Criteria::NOT_EQUAL)
				->filterById(5814, Criteria::LESS_EQUAL)
				->filterByCreationdate(1347446069, Criteria::LESS_EQUAL) // 2012-09-12 12:34:29
				->update(array('Deleted' => 1), $con);

// Clockings created by old system => max. clocking ID = 365913

// Transactions created by new system with old clockings
// SELECT distinct min(tc.clocking_id) FROM transaction t join transaction_clocking tc on t.id=tc.transaction_id where tc.clocking_id<=365913 and ;
// => min clocking ID
/*
			// Delete clockings without new system transactions
			ClockingQuery::create()
				->joinTransactionClocking()
				->join('TransactionClocking.Transaction')
				->filterByCreatorId(null, Criteria::NOT_EQUAL)
				->add(TransactionPeer::ID, 5814, Criteria::LESS_EQUAL)
				->add(TransactionPeer::CREATOR_ID, null, Criteria::NOT_EQUAL)
				->update(array('Deleted' => 1), $con);
*/
			// Mark clockings with new system transactions as deleted
			ClockingQuery::create()
				->filterById(365913, Criteria::LESS_EQUAL)
				->filterByCreatorId(null)
				->update(array('Deleted' => 1), $con);
/*
			TransactionClockingQuery::create()->deleteAll($con);
			TransactionQuery::create()->deleteAll($con);

			ClockingQuery::create()
				->filterByUserRelatedByUserId($debugUser, Criteria::NOT_EQUAL)
				->delete($con);
			HolidayQuery::create()->deleteAll($con);
			UserQuery::create()
				->filterByName('test', Criteria::NOT_EQUAL)
				->delete($con);
*/

/*
			TransactionClockingQuery::create()->deleteAll();
			TransactionQuery::create()->deleteAll();
			ClockingQuery::create()->deleteAll();
			DomainQuery::create()->deleteAll();
			AccountQuery::create()->deleteAll();

			$status->done();

			$status->start('Create default Account');

			$account = new Account();
			$account->setName('Default account');
			$account->save();

			$status->done();

			$status->start('Create default domain');

			$domain = new Domain();
			$domain->setName('default');
			$domain->setDescription('Default domain created while migrating to the new system.');
			$domain->setAccount($account);
			$domain->save();

			$status->done();
*/
$account = AccountQuery::create()
	->findOneByIdentifier('cms', $con);
$domain = DomainQuery::create();
/*
			$status->start('Create holidays');

			$holidaysUrl = \Xily\Config::get('migration.holidays_url', 'string', 'http://10.10.10.5/groupion/data/holidays');

			$filenames = array('Bayern2009', 'Bayern2010', 'Bayern2011', 'Bayern2012', 'Bayern2013', 'Bayern2014');
			foreach ($filenames as $filename) {
				$file = fopen($holidaysUrl.'/'.$filename.'.csv', 'r');
				if ( !is_resource($file) )
					throw new Exception('Could not open file');

				while ( is_array($row = fgetcsv($file, 1000, ';')) ) {
					$date  = strtotime($row[0]);
					$name  = $row[1];
					$state = $row[2];
					if ( $date ) {
						$holidayDomain = new HolidayDomain();
						$holidayDomain->setDomain($domain);

						$holiday = new Holiday();
						$holiday
							->setAccount($account)
							->setDate($date)
							->setName(trim($name))
							->addHolidayDomain($holidayDomain)
							->save();
					}
				}
			}

			$status->done();

			$status->start('Migrating Users');
			$this->importUsers($status, $account, $domain, $dbh, $con);
			$status->done();
*/
			$usersByName     = $this->getUsers($account, $con);
			$clockingTypeMap = $this->getClockingTypes($account, $con);

			$status->start('Migrating Clockings');
			$clockingDataByOldID = $this->importClockings($status, $clockingTypeMap, $usersByName, $dbh, $con);
			$status->done();

			$bookingTypesByIdentifier = $this->getBookingTypes($account, $con);

			$status->start('Migrating Transactions');
			$this->importTransactions($status, $clockingTypeMap, $bookingTypesByIdentifier, $usersByName, $clockingDataByOldID, $dbh, $con);
			$status->done();
echo ('#INCONSISTENCIES: '.$this->inconsistencies);
			$dbh = null;

		} catch (Exception $e) {
			echo 'Error: '.nl2br(htmlspecialchars($e->getMessage())).'<br/>';
			$status->showNotes($e->getMessage());
			die();
		}

		print '<p>Finished migration!</p></div>';

		$status->showNotes();
	}

	private function getApprovalStatus(array $row) {
		if ( $row['denied'] )
			return ClockingPeer::APPROVAL_STATUS_DENIED;
		elseif ( $row['checked'] )
			return ClockingPeer::APPROVAL_STATUS_AS_IS;
		elseif ( $row['approved'] )
			return ClockingPeer::APPROVAL_STATUS_CONFIRMED;
		else
			return ( $row['needs_approval'] ? ClockingPeer::APPROVAL_STATUS_REQUIRED : ClockingPeer::APPROVAL_STATUS_PRELIMINARY );
	}

	private function getUsers($domainOrAccount, PropelPDO $con) {
		if ( $domainOrAccount instanceof Account ) {
			return $domainOrAccount->getUsers(null, $con)
				->getArrayCopy('Name');
		} elseif ( $domainOrAccount instanceof Domain ) {
			return $domainOrAccount->getUsers(null, $con)
				->getArrayCopy('Name');
		} else {
			throw new Exception('Invalid parameter. Expected domain or account.');
		}
	}

	private function getClockingTypes(Account $account, PropelPDO $con) {
		$typesByIdentifier = $account->getClockingTypes(null, $con)
			->getArrayCopy('Identifier');

		return array(
			0 => $typesByIdentifier['regular'],
			1 => $typesByIdentifier['reduce_overtime'], // Whole day if start === end
			2 => $typesByIdentifier['reduce_overtime'], // Whole day if start === end
			3 => $typesByIdentifier['vacation'],        // Multi-day vacation
			4 => $typesByIdentifier['vacation'],        // Vacation or sick leave; whole day
			5 => $typesByIdentifier['sick_leave'],      // Sometimes vacation; whole day if start === end
			9 => $typesByIdentifier['education'],       //
		);
	}

	private function getBookingTypes(Account $account, PropelPDO $con) {
		return $account->getBookingTypes(null, $con)
			->getArrayCopy('Identifier');
	}

	private function importUsers(Status $logger, Account $account, Domain $domain, PDO $source, PropelPDO $dest) {
		$statement = $source->query('SELECT * FROM platform_users');
		$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		$statement->closeCursor();
		$statement = null;

		if ( !is_array($rows) )
			throw new Exception('Could not fetch users');

		if ( !$dest->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			foreach ($rows as $row) {
				$user = new User();
				$user
					->setAccount($account)
					->setDomain($domain)
					->setName($row['username'])
					->setEmail($row['email'])
					->lockPassword() // Disable local authentication
					->save($dest);
			}

		} catch (Exception $e) {
			$dest->rollBack();

			throw $e;
		}

		if ( !$dest->commit() )
			throw new Exception('Could not commit transaction.');
	}

	private function importClockings(Status $logger, array $clockingTypeMap, array $usersByName, PDO $source, PropelPDO $dest) {
		$statement = $source->query('SELECT * FROM ext_clockings_clockings');

		$logger->info($statement->rowCount());

		if ( !$dest->beginTransaction() )
			throw new Exception('Could not start transaction.');

		$clockingDataByOldID = array();

		try {
			while ( is_array($row = $statement->fetch(PDO::FETCH_ASSOC)) ) {
				$oldId        = $row['ID'];
				$authUserName = $row['creator'];
				$userName     = $row['username'];
				if ( empty($usersByName[$userName]) ) {
					$logger->info('Clocking with old ID #'.$oldId.' references unknown user "'.$userName.'".');
					continue;
				}

				$type         = $row['type'];
				if ( !isset($clockingTypeMap[$type]) ) {
					$logger->info('Clocking with old ID #'.$oldId.' references unknown type '.$type.'.');
					continue;
				}

				$clockingType = $clockingTypeMap[$type];

				$start        = (int)$row['start'];
				$end          = (int)$row['end'];

				if ( $clockingType->getWholeDay() ) {
					$start = strtotime('today', $start);
					$end   = strtotime('today', $end);
				}

				$clocking = new Clocking();
				$clocking
					->setUserRelatedByCreatorId($usersByName[$authUserName])
					->setUserRelatedByUserId($usersByName[$userName])
					->setClockingType($clockingType)
					->setApprovalStatus($this->getApprovalStatus($row))
					->setStart($start)
					->setEnd($end)
					->setBreaktime((int)round($row['break'] * 60))
					->setCreationdate((int)$row['creationdate'])
					->setLastChanged((int)$row['changed'])
					->setComment($row['comment'])
					->setDeleted($row['visibility'])
					->save($dest);

				$clockingDataByOldID[$oldId] = new ClockingData($clocking, $row);
			}

		} catch (Exception $e) {
			$dest->rollBack();

			$statement->closeCursor();
			$statement = null;

			throw $e;
		}

		if ( !$dest->commit() )
			throw new Exception('Could not commit transaction.');

		$statement->closeCursor();
		$statement = null;

		return $clockingDataByOldID;
	}

private $inconsistencies=0;
	/**
	 * Checks a transaction time for consistency.
	 *
	 * @param array $oldTransaction The old transaction row.
	 * @param string $name The name of the time column.
	 * @param double $actualTime The actually logged total time.
	 * @param array $items Optional. An array of {@link ClockingData} or
	 *     {@link Booking} objects.
	 * @return void
	 */
	private function failTimeDelta(Status $logger, array $oldTransaction, $name, $actualTime, array $items = null, $breaks = null) {
		$expectedTime = $oldTransaction[$name];
		$timeDelta    = abs($actualTime - $expectedTime);

		if ( $timeDelta < 2 ) {
			if ( $timeDelta > 1 )
				$logger->info('Old transaction #'.$oldTransaction['ID'].' type '.$oldTransaction['type'].' has delta '.$timeDelta);
			return;
		}

		$timeDetails = array();
		if ( $items !== null ) {
			$timeDetails[] = '';
			foreach ($items as $item) {
				if ( $item instanceof Booking ) {
					$bookingType   = $item->getBookingType();
					$timeDetails[] =
						'Booking #'.$item->getId().
						' '.( $bookingType === null ? '['.$item->getBookingTypeId().']' : $bookingType->getIdentifier() ).
						' '.$item->getLabel().
						' '.$item->getValue();
				} elseif ( $item instanceof ClockingData ) {
					$row = $item->row;
					$duration = $row['end'] - $row['start'];
					$timeDetails[] =
						'Old clocking #'.$row['ID'].
						' type '.$row['type'].
						' '.date('Y-m-d H:i', $row['start']).
						' - '.date('Y-m-d H:i', $row['end']).
						' = '.round($duration / 60.0 - $row['break'], 3).' ['.$duration.']'.
						', break '.$row['break'].
						', flexi '.$row['flexitime'].
						', overtime '.$row['overtime'].
						', denied '.$row['denied'].
						( (int)$row['visibility'] === 0 ? '' : ' [DELETED]' );
				}
			}
		}

		$message =
			'Inconsistent transaction with old ID #'.$oldTransaction['ID'].
			' ['.$oldTransaction['username'].', '.date('Y-m-d', $oldTransaction['date']).']:'.
			' Expected '.$name.' '.$expectedTime.' but got '.$actualTime.
			implode("\n", $timeDetails);

		if ( $breaks !== null )
			$logger->info('NOTE: Inconsistency in old transaction #'.$oldTransaction['ID'].' for time without breaks '.$breaks.'; time without breaks: '.($actualTime - $breaks));
$this->inconsistencies++;
#		if ( $timeDelta < 2 )
			$logger->info('WARNING [delta '.$timeDelta.']: '.nl2br(htmlspecialchars($message)));
#		else
#			throw new Exception('ERROR [delta '.$timeDelta.']: '.$message);
	}

	private function getTransactionClockingDataItems(Status $logger, array $oldTransaction, array $clockingDataByOldID, PDO $source) {
		$oldTransactionId = $oldTransaction['ID'];

		$statement = $source->query(
			'SELECT clocking'.
			' FROM ext_clockings_transactions_clockings'.
			' WHERE transaction = '.(int)$oldTransactionId
		);
		$oldClockingIds = $statement->fetchAll(PDO::FETCH_COLUMN);
		$statement->closeCursor();
		$statement = null;

		if ( !is_array($oldClockingIds) )
			throw new Exception('Could not load clockings belonging to old transaction with ID #'.$oldTransactionId.'.');

		// Check for missing clockings
		$oldClockingIdMap   = array_fill_keys($oldClockingIds, true);
		$missingClockingIds = array_keys(array_diff_key($oldClockingIdMap, $clockingDataByOldID));
		if ( !empty($missingClockingIds) )
			throw new Exception('Transaction with old ID #'.$oldTransactionId.' references unknown clocking IDs '.implode(', ', $missingClockingIds));

		$clockingDataItems = array_intersect_key($clockingDataByOldID, $oldClockingIdMap);

		// Verify transaction's integrity and consistency

		$logger->start('Checking consistency of old transactions');

		$actualTime      = 0.0;
		$actualFlexitime = 0.0;
		$actualOvertime  = 0.0;
		$actualDenied    = 0.0;
		$breaks          = 0.0;

		$types = array(
			0 => true, //$typesByIdentifier['regular'],
			1 => true, //$typesByIdentifier['reduce_overtime'], // Whole day if start === end
			#2 =>true, // $typesByIdentifier[''],
			#3 => true, //$typesByIdentifier['vacation'],        // Multi-day vacation
			#4 => true, //$typesByIdentifier['vacation'],        // Vacation or sick leave; whole day
			#5 => true, //$typesByIdentifier['sick_leave'],      // Sometimes vacation; whole day if start === end
			#9 => true, //$typesByIdentifier['education'],       //
		);

		foreach ($clockingDataItems as $clockingDataItem) {
			$row = $clockingDataItem->row;
			if ( (int)$row['visibility'] !== 0 ) {
				// Skip canceled clockings
				$logger->info('Skipping deleted old clocking #'.$row['ID'].': '.date('Y-m-d H:i', $row['start']).' '.date('Y-m-d H:i', $row['end']).', break '.$row['break']);
				continue;
			}

			/*
			0 => $typesByIdentifier['regular'],
			1 => $typesByIdentifier['reduce_overtime'], // Whole day if start === end
			#2 => $typesByIdentifier[''],
			3 => $typesByIdentifier['vacation'],        // Multi-day vacation
			4 => $typesByIdentifier['vacation'],        // Vacation or sick leave; whole day
			5 => $typesByIdentifier['sick_leave'],      // Sometimes vacation; whole day if start === end
			9 => $typesByIdentifier['education'],       //
			 */
			if ( isset($types[$row['type']]) )
				$actualTime  += ($row['end'] - $row['start']) / 60.0;# - $row['break'];
			else
				$logger->info('Ignoring time of old clocking #'.$row['ID'].' due to unknown type '.$row['type'].': '.date('Y-m-d H:i', $row['start']).' '.date('Y-m-d H:i', $row['end']).', break '.$row['break']);

			$actualFlexitime += $row['flexitime'];
			$actualOvertime  += $row['overtime'];
			$actualDenied    += $row['denied'];
			$breaks          += $row['break'];
		}

		$this->failTimeDelta($logger, $oldTransaction, 'time', $actualTime, $clockingDataItems, $breaks);
		$this->failTimeDelta($logger, $oldTransaction, 'flexitime', $actualFlexitime, $clockingDataItems);
		$this->failTimeDelta($logger, $oldTransaction, 'overtime', $actualOvertime, $clockingDataItems);
		$this->failTimeDelta($logger, $oldTransaction, 'denied', $actualDenied, $clockingDataItems);

		$logger->done();

		return $clockingDataItems;
	}

	private function getClockings(array $clockingDataItems) {
		$clockings = array();

		foreach ($clockingDataItems as $clockingDataItem)
			$clockings[] = $clockingDataItem->clocking;

		return $clockings;
	}

	private function getClockingRange(array $clockings) {
		if ( empty($clockings) )
			throw new Exception('No clockings specified.');

		$start = PHP_INT_MAX;
		$end   = ~PHP_INT_MAX; // Assumes two's complement

		foreach ($clockings as $clocking) {
			$clockingStart = (int)$clocking->getStart('U');
			if ( $clockingStart < $start )
				$start = $clockingStart;

			$clockingEnd = (int)$clocking->getEnd('U');
			if ( $clockingEnd > $end )
				$end = $clockingEnd;
		}

		return array($start, $end);
	}

	private function createBooking(BookingType $bookingType, $label, $value) {
		$booking = new Booking();
		return $booking
			->setBookingType($bookingType)
			->setLabel($label)
			->setValue($value);
	}

	private function getBookingType($clockingTypeIdentifier, array $bookingTypesByIdentifier) {
		if ( $clockingTypeIdentifier === 'reduce_overtime' )
			$clockingTypeIdentifier = 'overtime';

		if ( isset($bookingTypesByIdentifier[$clockingTypeIdentifier]) )
			return $bookingTypesByIdentifier[$clockingTypeIdentifier];

		return null;
	}

	private function createBookings(Status $logger, array $clockingDataItems, array $bookingTypesByIdentifier, PropelPDO $con) {
		$bookings = array();

		foreach ($clockingDataItems as $itemIndex => $clockingDataItem) {
			$clocking     = $clockingDataItem->clocking;
			$row          = $clockingDataItem->row;

			$clockingType = $clocking->getClockingType($con);
			if ( $clockingType === null )
				throw new Exception('Could not find clocking type #'.$clocking->getTypeId().' for clocking #'.$clocking->getId().'.');

			$typeIdentifier = $clockingType->getIdentifier();
			$bookingType    = $this->getBookingType($typeIdentifier, $bookingTypesByIdentifier);

			if ( $bookingType === null ) {
				$logger->info('No booking type found for clocking type "'.$typeIdentifier.'" in clocking '.$clocking->getId().' "'.$clocking->getComment().'".');
				continue;
			}

			$flexitime = (int)round($row['flexitime'] * 60);
			$overtime  = (int)round($row['overtime'] * 60);

			$start     = (int)$clocking->getStart('U');
			$startDay  = strtotime('today', $start);
			$end       = (int)$clocking->getEnd('U');

			// Create bookings corresponding directly to clockings

			switch ( $typeIdentifier ) {
				case 'vacation':
				case 'sick_leave':
				case 'education':
					$start    = $startDay;
					$end      = strtotime('tomorrow', $end);
					$duration = BookingTypePeer::timeToDuration(
						$start,
						$end,
						0,
						$bookingType->getUnit(),
						BookingTypePeer::ROUND_NEAREST
					);

					$dateText = $clocking->getStart('Y-m-d');
					if ( $start !== $end )
						$dateText .= ' - '.$clocking->getEnd('Y-m-d');

					break;

				case 'regular':
				case 'reduce_overtime':
					if ( ($typeIdentifier === 'reduce_overtime') and
					     ($overtime !== 0) ) {
						$duration = BookingTypePeer::timeToDuration(
							0,
							$overtime,
							0,
							$bookingType->getUnit(),
							BookingTypePeer::ROUND_NEAREST
						);
					} else {
						// Do not subtract flexitime from regular work time
						// if it is negative. Negative values only reflect work
						// days where someone works less time than expected.
						$duration = BookingTypePeer::timeToDuration(
							$start,
							$end - ( $flexitime > 0 ? $flexitime : 0 ) - $overtime,
							$clocking->getBreaktime(),
							$bookingType->getUnit(),
							BookingTypePeer::ROUND_NEAREST
						);
					}

					$dateText = $clocking->getStart('Y-m-d H:i');
					if ( $start !== $end )
						$dateText .= ' - '.$clocking->getEnd(( $startDay === strtotime('today', $end) ? '' : 'Y-m-d ' ).'H:i');

					break;

				default:
					$logger->info('Skipping clocking #'.$clocking->getId().' with unknown type "'.$typeIdentifier.'".');
					continue;
			}

			$comment = trim($dateText.' '.$clocking->getComment());

			$bookings[] = $this->createBooking(
				$bookingType,
				$comment,
				$duration
			);

			// Add flexitime / over-time bookings

			if ( $flexitime !== 0 ) {
				$bookings[] = $this->createBooking(
					$bookingTypesByIdentifier['flexitime'],
					$comment,
					BookingTypePeer::timeToDuration(
						0,
						$flexitime,
						0,
						$bookingType->getUnit(),
						BookingTypePeer::ROUND_NEAREST
					)
				);
			}

			// "reduce_overtime" is booked as type "overtime" already,
			// so do not book it again
			if ( ($overtime !== 0) and ($bookingType->getIdentifier() !== 'overtime') ) {
				$bookings[] = $this->createBooking(
					$bookingTypesByIdentifier['overtime'],
					$comment,
					BookingTypePeer::timeToDuration(
						0,
						$overtime,
						0,
						$bookingType->getUnit(),
						BookingTypePeer::ROUND_NEAREST
					)
				);
			}
		}

		return $bookings;
	}

	private function importTransactions(Status $logger, array $clockingTypeMap, array $bookingTypesByIdentifier, array $usersByName, array $clockingDataByOldID, PDO $source, PropelPDO $dest) {
		if ( !$dest->beginTransaction() )
			throw new Exception('Could not start transaction.');

		try {
			$statement = $source->query('SELECT * FROM ext_clockings_transactions');

			$logger->info($statement->rowCount());

			$oldTransactions = $statement->fetchAll(PDO::FETCH_ASSOC);
			$statement->closeCursor();
			$statement = null;

			foreach ($oldTransactions as $rowIndex => $oldTransaction) {
				$authUserName = $oldTransaction['creator'];
				$userName     = $oldTransaction['username'];
				if ( empty($usersByName[$userName]) ) {
					$logger->info('Transaction #'.$oldTransaction['ID'].' references unknown user "'.$userName.'".');
					continue;
				}

				$transaction = new Transaction();

				$dataItems = $this->getTransactionClockingDataItems($logger, $oldTransaction, $clockingDataByOldID, $source);

				$clockings = $this->getClockings($dataItems);
				if ( empty($clockings) ) {
					$date  = (int)$oldTransaction['date'];
					$start = $date;
					$end   = $date;
				} else {
					list($start, $end) = $this->getClockingRange($clockings);
					foreach ($clockings as $clocking) {
						$transactionClocking = new TransactionClocking();
						$transaction->addTransactionClocking($transactionClocking
							->setTransaction($transaction)
							->setClocking($clocking)
						);
					}
				}

				// Check transaction / booking consistency

				$logger->start('Checking consistency of new transactions & bookings');

				$worktimeId      = $bookingTypesByIdentifier['regular']->getId();
				$flexitimeId     = $bookingTypesByIdentifier['flexitime']->getId();
				$overtimeId      = $bookingTypesByIdentifier['overtime']->getId();
				$vacationId      = $bookingTypesByIdentifier['vacation']->getId();
				$sickLeaveId     = $bookingTypesByIdentifier['sick_leave']->getId();
				$educationId     = $bookingTypesByIdentifier['education']->getId();

				$actualTimes     = array(
					$worktimeId   => 0.0,
					$flexitimeId  => 0.0,
					-$flexitimeId => 0.0,
					$overtimeId   => 0.0,
					$vacationId   => 0.0,
					$sickLeaveId  => 0.0,
					$educationId  => 0.0,
				);
				//$actualDenied    = 0;

				$bookings = $this->createBookings($logger, $dataItems, $bookingTypesByIdentifier, $dest);
				foreach ($bookings as $booking) {
					$bookingTypeId = $booking->getBookingTypeId();

					if ( !isset($actualTimes[$bookingTypeId]) )
						throw new Exception('Unknown booking type #'.$bookingTypeId.' specified for booking #'.$booking->getId().'.');

					$value = $booking->getValue();
					if ( ($bookingTypeId === $flexitimeId) and ($value < 0) )
						$actualTimes[-$bookingTypeId] += $value;
					else
						$actualTimes[$bookingTypeId] += $value;

					$transaction->addBooking($booking);
				}

				$transaction
					->setUserRelatedByCreatorId($usersByName[$authUserName])
					->setUserRelatedByUserId($usersByName[$userName])
					->setStart($start)
					->setEnd($end)
					->setCreationdate((int)$oldTransaction['creationdate'])
					->setComment($oldTransaction['comment'])
					->setDeleted($oldTransaction['visibility'])
					->save($dest);

				// Cross-check times of bookings and transaction

				#$this->failTimeDelta($logger, $oldTransaction, 'time', $actualTimes[$worktimeId] + $actualTimes[$flexitimeId] + $actualTimes[$overtimeId], $bookings);
				#$this->failTimeDelta($logger, $oldTransaction, 'time', $actualTimes[$worktimeId], $bookings);
				$this->failTimeDelta($logger, $oldTransaction, 'flexitime', $actualTimes[$flexitimeId] + $actualTimes[-$flexitimeId], $bookings);
				$this->failTimeDelta($logger, $oldTransaction, 'overtime', $actualTimes[$overtimeId], $bookings);
				//$this->failTimeDelta($logger, $oldTransaction, 'denied', $actualDenied, $bookings);

				$logger->done();
			}

		} catch (Exception $e) {
			$dest->rollBack();
			throw $e;
		}

		if ( !$dest->commit() )
			throw new Exception('Could not commit transaction.');
	}

}

?>
