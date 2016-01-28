<?php



/**
 * Skeleton subclass for representing a row from the 'vacation' table.
 *
 * @package    propel.generator.tymio
 */
class Holiday extends BaseHoliday {

	/**
	 *
	 * Counts holidays per week for a given timeframe.
	 * @param int $domain
	 * @param int $start
	 * @param int $end
	 * @return multitype:number
	 */
	public static function getCountPerWeek($domain, $start, $end) {
		$holidays = HolidayQuery::create()
							-> filterByDomain($domain)
							-> filterByDate($start, Criteria::GREATER_EQUAL)
							-> filterByDate($end, Criteria::LESS_EQUAL)
							-> find();
		$holidaysWeeks = array();
		foreach ($holidays as $holiday) {
			$date = createDate($holiday->getDate());
			$key = $date->format('W-Y');
			if (array_key_exists($key, $holidaysWeeks)) {
				$holidaysWeeks[$key] = $holidaysWeeks[$key] + 1;
			}
			else {
				$holidaysWeeks[$key] = 1;
			}
		}
		return $holidaysWeeks;
	}

} // Holiday
