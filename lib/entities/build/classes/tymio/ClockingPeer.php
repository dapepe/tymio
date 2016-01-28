<?php



/**
 * Skeleton subclass for performing query and update operations on the 'clocking' table.
 *
 * @package    propel.generator.tymio
 */
class ClockingPeer extends BaseClockingPeer {

	/**
	 * Not approved; considered valid but pending validation.
	 */
	const APPROVAL_STATUS_PRELIMINARY = 0;

	/**
	 * Needs approval.
	 */
	const APPROVAL_STATUS_REQUIRED    = 1;

	/**
	 * Disapproved.
	 */
	const APPROVAL_STATUS_DENIED      = 2;

	/**
	 * Explicitly approved. iXML-based rules may be applied.
	 */
	const APPROVAL_STATUS_CONFIRMED   = 3;

	/**
	 * Approved; use as is; do not recalculate.
	 */
	const APPROVAL_STATUS_AS_IS       = 4;

	const DELETED_VISIBLE             = 0;
	const DELETED_DELETED             = 1;

} // ClockingPeer
