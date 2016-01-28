<?php

class EntityArray {

	static private $typeColors = array();

	static private function getTypeColor($id) {
		if ( !isset(self::$typeColors[$id]) )
			self::$typeColors[$id] = substr(md5($id), 0, 6);

		return self::$typeColors[$id];
	}

	static public function from($obj, PropelPDO $con = null) {
		if ( $obj === null )
			throw new Exception('Object must not be NULL.');

		if ( is_array($obj) or ($obj instanceof ArrayAccess) ) {
			$result = array();

			foreach ($obj as $key => $value)
				$result[$key] = self::from($value);

			return $result;
		}

		$method = array(__CLASS__, 'from'.get_class($obj));
		if ( is_callable($method) )
			return call_user_func($method, $obj, $con);

		$toArray = array($obj, 'toArray');
		if ( is_callable($toArray) )
			return call_user_func($toArray);

		throw new Exception('Unsupported object "'.$obj.'".');
	}

	static public function fromUser(User $user, PropelPDO $con = null) {
		$result = $user->toArray();
		unset($result['PasswordHash']);
		return $result;
	}

	static public function fromClocking(Clocking $clocking, PropelPDO $con = null) {
		return array(
            'Id'             => $clocking->getId(),
            'UserId'         => $clocking->getUserId(),
            'TypeId'         => $clocking->getTypeId(),
            'Creationdate'   => $clocking->getCreationdate(),
            'LastChanged'    => $clocking->getLastChanged(),
            'Start'          => $clocking->getStart('U'),
            'End'            => $clocking->getEnd('U'),
            'Breaktime'      => $clocking->getBreaktime(),
            'Comment'        => $clocking->getComment(),
            'ApprovalStatus' => $clocking->getApprovalStatus(),
            'Deleted'        => $clocking->getDeleted(),
            'Frozen'         => $clocking->getFrozen(),
		);
	}

	static public function fromClockingType(ClockingType $clockingType, PropelPDO $con = null) {
		return $clockingType->toArray() + array('Color' => self::getTypeColor($clockingType->getIdentifier()));
	}

	static public function fromBookingType(BookingType $bookingType, PropelPDO $con = null) {
		return $bookingType->toArray() + array('Color' => self::getTypeColor($bookingType->getIdentifier()));
	}

	static public function fromTransaction(Transaction $transaction, PropelPDO $con = null) {
		$transactionId = $transaction->getId();

		$bookingData = array();
		$bookings = BookingQuery::create()
			->filterByTransactionId($transactionId)
			->find($con);
		foreach ($bookings as $booking)
			$bookingData[] = self::from($booking, $con);

		$clockingData = array();
		$clockings = ClockingQuery::create()
			->joinTransactionClocking()
			->joinWith('ClockingType')
			->add(TransactionClockingPeer::TRANSACTION_ID, $transactionId)
			->find($con);
		foreach ($clockings as $clocking)
			$clockingData[] = self::from($clocking, $con) + array('Type' => self::from($clocking->getClockingType($con), $con));

		return array(
			'Start'     => $transaction->getStart('U'),
			'End'       => $transaction->getEnd('U'),
			'Bookings'  => $bookingData,
			'Clockings' => $clockingData,
			'User'      => self::from($transaction->getUserRelatedByUserId($con), $con),
		) + $transaction->toArray();
	}

}