<?php

/**
 * Skeleton subclass for performing query and update operations on the 'booking_type' table.
 *
 * @package    propel.generator.tymio
 */
class BookingTypePeer extends BaseBookingTypePeer {

	const TYPE_TIME      = 0;
	const TYPE_FLEXITIME = 1;
	const TYPE_OVERTIME  = 2;
	const TYPE_VACATION  = 3;

	const UNIT_SECONDS   = 'seconds';
	const UNIT_MINUTES   = 'minutes';
	const UNIT_HOURS     = 'hours';
	const UNIT_DAYS      = 'days';
	const UNIT_WEEKS     = 'weeks';
	const UNIT_MONTHS    = 'months';
	const UNIT_YEARS     = 'years';

	const ROUND_NEAREST  = 'round';
	const ROUND_CEIL     = 'ceil';
	const ROUND_FLOOR    = 'floor';

	static private $roundingMethods = array(
		self::ROUND_NEAREST => true,
		self::ROUND_CEIL    => true,
		self::ROUND_FLOOR   => true,
	);

	/**
	 * Converts clocking start and end dates and break time to a duration in the specified time unit.
	 *
	 * @param int $start A UNIX timestamp specifying the start date.
	 * @param int $end A UNIX timestamp specifying the end date.
	 * @param int $break The duration of all breaks between start and end date
	 *     in seconds.
	 * @param string $targetUnit The target unit to convert to. Must be one of
	 *     the "UNIT_..." constants, e.g. {@link UNIT_DAYS}.
	 * @param string $round Optional. The rounding method to use. Default
	 *     is {@link ROUND_CEIL}.
	 * @return int
	 */
	static public function timeToDuration($start, $end, $break, $targetUnit, $round = self::ROUND_CEIL) {
		if ( !isset(self::$roundingMethods[$round]) )
			throw new Exception('Unknown rounding method "'.$round.'".');

		// Do not use "DateTime->modify()" or set a dummy time zone to
		// prevent it from corrupting the actual date and time. See also
		// - https://bugs.php.net/bug.php?id=62896
		// - http://stackoverflow.com/questions/12075821/datetime-modify0-days-modifies-datetime-object

		$end      -= $break;

		$startDate = new DateTime('@'.$start);
		$endDate   = new DateTime('@'.$end);

		$diff      = $endDate->diff($startDate);

		switch ( $targetUnit ) {
			case 'seconds':
				return $end - $start;

			case 'minutes':
				return $round(($end - $start) / 60.0);

			case 'hours':
				return $round(($end - $start) / 3600.0);

			case 'halfdays':
				$seconds = ($diff->h * 60.0 + $diff->i) * 60.0 + $diff->s;
				return 2 * $diff->days + $round($seconds / (86400.0 / 2.0));

			case 'days':
				$seconds = ($diff->h * 60.0 + $diff->i) * 60.0 + $diff->s;
				return $diff->days + $round($seconds / 86400.0);

			case 'weeks':
				return $round($diff->days / 7.0);

			case 'months':
				$seconds = (($diff->d * 24.0 + $diff->h) * 60.0 + $diff->i) * 60.0 + $diff->s;
				return $diff->y * 12 + $diff->m + $round($seconds / 30.0 / 24.0 / 86400.0); // Assume 30-day months for rounding

			case 'years':
				$fullYearEnd = clone $startDate;
				$fullYearEnd->modify($diff->y);
				$diff        = $endDate->diff($fullYearEnd);
				return $diff->y + $round($diff->days / 365.0); // Assume 365 days a year for rounding

			default:
				throw new Exception('Could not convert time interval to booking time: Unknown time unit "'.$targetUnit.'".');
		}
	}

} // BookingTypePeer
