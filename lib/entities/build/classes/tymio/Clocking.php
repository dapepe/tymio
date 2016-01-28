<?php

include_once 'lib/tymio/settings.php';
include_once 'lib/tymio/util.inc.php';


/**
 * Skeleton subclass for representing a row from the 'clocking' table.
 *
 * @package    propel.generator.tymio
 */
class Clocking extends BaseClocking {

	private function formatBreaktime() {
		$break = $this->getBreaktime();
		$seconds = $break % 60;
		$minutes = (int)($break / 60 % 60);
		$hours   = (int)($break / 3600);
		return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
	}

	public function __toString() {
		try {
			$user     = $this->getUserRelatedByUserId();
			$account  = ( $user === null ? null : $user->getAccount() );
			$userName =
				( $account === null ? $this->getAccountId() : $account->getIdentifier() ).'/'.
				( $user === null ? $this->getUserId() : $user->getName() );
		} catch (Exception $e) {
			$userName = $this->getUserId();
		}

		try {
			$type     = $this->getClockingType();
			$typeText = $type->getIdentifier();
		} catch (Exception $e) {
			$typeText = $this->getTypeId();
		}

		return '['.__CLASS__.' '.$userName.' #'.$this->getId().' '.$typeText.' (status '.$this->getApprovalStatus().') '.$this->getTime().' = '.$this->getStart('Y-m-d H:i:s').' - '.$this->getEnd('Y-m-d H:i:s').', break '.$this->formatBreaktime().']';
	}

	/**
	 * Returns the time span between end and start.
	 *
	 * @return int
	 */
	public function getTime() {
		return $this->getEnd('U') - $this->getStart('U');
	}

	/**
	 * Checks if the clocking is still open and unbooked.
	 *
	 * This function is expensive as it performs a COUNT on the transaction
	 * clockings table and may also fetch the clocking type.
	 *
	 * @return bool Returns TRUE if the clocking is open and editable,
	 *     otherwise FALSE.
	 */
	public function isOpen(PropelPDO $con = null) {
		$type = $this->getClockingType($con);
		if ( $this->getFrozen() or
		     $type->getWholeDay() or
		     ($this->getStart('U') !== $this->getEnd('U')) )
			return false;

		return ( $this->countTransactionClockings(null, null, $con) === 0 );
	}

	/**
	 *
	 * @param Clocking $other
	 * @return boolean True, if start of this item and start of other are on the same day.
	 */
	public function startsOnSameDay(self $other) {
		$thisDay  = date('dmY', $this->start);
		$otherDay = date('dmY', $other->start);
		return $thisDay == $otherDay;
	}

	/**
	 * Denies the clocking.
	 *
	 * @return self
	 */
	public function disapprove() {
		return $this->setApprovalStatus(ClockingPeer::APPROVAL_STATUS_DENIED);
	}

	/**
	 * Marks the clocking as being approved.
	 *
	 * @return self
	 */
	public function approve() {
		return $this->setApprovalStatus(ClockingPeer::APPROVAL_STATUS_CONFIRMED);
	}

	/**
	 * Checks if this item can be updated.
	 *
	 * @throws Exception If the item belongs to any transaction or has been deleted.
	 */
	public function checkUpdate(PropelPDO $con = null) {
		if ( $this->deleted )
			throw new Exception('The entry is flagged as removed and therefore cannot be changed!');
		elseif ( $this->countTransactionClockings(null, null, $con) > 0 )
			throw new Exception('Entry is part of an existing transaction and therefore cannot be changed!');
	}

	public function preSave(PropelPDO $con = null) {
		$time = time();

		if ( $this->creationdate === null )
			$this->setCreationdate($time);

		if ( ($this->last_changed === null) or
		     !$this->isColumnModified(ClockingPeer::LAST_CHANGED) )
			$this->setLastChanged($time);

		return parent::preSave($con);
	}

} // Clocking
