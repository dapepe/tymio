<?php

	include_once 'lib/tymio/util.inc.php';
	include_once 'lib/tymio/settings.php';
	include_once 'app/api/clocking.php';

	class Calculator {
		private $dayCalc = array();
		private $dayTotal = array();
		private $dayBreak = array();
		private $dayTasks = array();

		private $currentWeek = 0;
		private $worktime = 0; // Accumulated worktime for current week
		private $holidaysWeeks = array();
		private $holidays = array();
		private $flexitime = 0;
		private $autocalc = false;
		private $employee = null; // The user to whose items are calculated

		/**
		 *
		 * Creates a new Calculater instance.
		 * @param bool $autocalc true, if suggestions should be saved.
		 * @param int $flexitime The base amount of flexitime before the calculation starts.
		 */
		function __construct($autocalc) {
			$this->autocalc = $autocalc;
		}

		function addDayTotal($day, $value) {
			$this->dayTotal[$day] + $value;
		}

		public function calcItem(&$item, &$previousItem, $nextItem, $autocalc=false) {
			$skip = false;
			if ($item->getVisibility() == true) {
				$skip = true;
			}

			// Item in transaction
			if ($item->countTransactionClockingss() > 0) {
				$skip = true;
			}
			if ($item->getEnd() == null) {
				$item->setType(Clocking::$ACTIVE);
				$skip = true;
			}

			$itemDay = dayKey($item->getStart());
			$today = date('N', $item->getStart());

			if (!array_key_exists($itemDay, $this->dayCalc)) {
				$this->dayCalc[$itemDay] = 0;
			}
			if (!array_key_exists($itemDay, $this->dayTotal)) {
				$this->dayTotal[$itemDay] = 0;
			}
			if (!array_key_exists($itemDay, $this->dayBreak)) {
				$this->dayBreak[$itemDay] = 0;
			}
			if (!array_key_exists($itemDay, $this->dayTasks)) {
				$this->dayTasks[$itemDay] = 0;
			}

			if ($skip == false) {

				$itemWeek = date('W', $item->getStart());
				$weekYear = date('Y', $item->getStart());

				if ($itemWeek > $this->currentWeek) {
					//Reset week
					$key = $itemWeek.'-'.$weekYear;
					if (!array_key_exists($key, $this->holidaysWeeks)) {
						$this->holidaysWeeks[$key] = 0;
					}
					$this->worktime = $this->holidaysWeeks[$key] * $item->getUser()->getDailyTime();
				}
				$this->currentWeek = $itemWeek;

				// Initialize the amount of worktime for this item
				$item->initTime();

				// %dayTotal[%itemDay] + %item[time] - %item[denied]
				$this->dayTotal[$itemDay] = $this->dayTotal[$itemDay] + $item->getTime() - $item->getDenied();

				// TODO calculate task sum
				// <math:calc var="taskSum[%itemDay]">%taskSum[%itemDay] + %item[task_sum]</math:calc>

				if ($item->getChecked()) {
					// Item is checked - stopping here!
					$this->worktime = $this->worktime + $item->getTime() + $item->getResttime();
					$item->setSuggestedFlexitime($item->getFlexitime());
				}
				elseif ($item->isAbsence()) {
					//Item is absence - stopping here!
					$this->worktime = $this->worktime + $item->getTime();
					$item->setSuggestedFlexitime($item->getFlexitime());
				}
				else {
					// Item is regular work, start calculating suggestions
					if ($previousItem != null) {
						$breakBetween = $previousItem->calculateBreakTo($item);
						$this->dayBreak[$itemDay] = $this->dayBreak[$itemDay] + $breakBetween;
						$previousItem = null;
					}
					// Calculate break suggestion (30 min for more than 6.5 h, 45 min for more than 9.45 h)
					if ($this->dayTotal[$itemDay] > 585) {
						if ($this->dayBreak[$itemDay]  < 45) {
							$item->setSuggestedResttime(45 - $this->dayBreak[$itemDay]);
						}
					}
					elseif ($this->dayTotal[$itemDay] > 390) {
						if ($this->dayBreak[$itemDay]  < 30) {
							$item->setSuggestedResttime(30 - $this->dayBreak[$itemDay]);
						}
					}
					if ($item->getResttime() > $item->getSuggestedResttime()) {
						$item->setSuggestedResttime($item->getResttime());
					}
					$this->dayBreak[$itemDay] = $this->dayBreak[$itemDay] + $item->getSuggestedResttime();

					// Calculate flextime <-> overtime
					$flexFactor = 1;
					if ($today == 7) {
						$flexFactor = 2; // Sunday
					}
					elseif ($today == 6) {
						$flexFactor = 1.5; // Saturday
					}
					elseif (array_key_exists($itemDay, $this->holidays)) {
						$flexFactor = 2;
					}

					if ($flexFactor == 1) {
						// Normal workday (Mo-Fr)
						$this->worktime = $this->worktime + $item->getTime() - $item->getSuggestedResttime();
						if ($this->worktime > $this->employee->getTimePerWeek()) {
							$item->setSuggestedFlexitime($this->worktime - $this->employee->getTimePerWeek());
						}
						else {
							$duty = $this->employee->getDailyTime() - $this->dayCalc[$itemDay];
							if ($duty < 0) {
								$duty = 0;
							}
							$item->setSuggestedFlexitime($item->getTime() - $item->getSuggestedResttime() - $duty);
						}
					}
					else {
						// Don't count day to weektime
						$item->setSuggestedOvertime(($item->getTime() - $item->getSuggestedResttime()) * $flexFactor);
					}
				}
			}

			$this->dayCalc[$itemDay] = $this->dayCalc[$itemDay]
												+ $item->getTime()
												- $item->getSuggestedResttime()
												- $item->getFlexitime()
												- $item->getOvertime()
												- $item->getDenied();
			if ($nextItem == null) {
				$nextItem = ClockingQuery::create()
								-> filterById($item->getId(), Criteria::NOT_EQUAL)
								-> filterByUser($this->employee)
								-> filterByStart($item->getEnd(), Criteria::GREATER_EQUAL)
								-> filterByEnd(null, Criteria::ISNOTNULL)
								-> filterByVisibility(0)
								-> orderByStart('asc')
								-> findOne();
				if ($nextItem != null) {
					$week1 = date('W', $item->getEnd());
					$week2 = date('W', $nextItem->getStart());
					//Check if week is over before weekly time is met
					if ($week1 < $week2 && $this->worktime < $this->employee->getTimePerWeek()) {
						$item->setSuggestedFlexitime($this->worktime - $this->employee->getTimePerWeek());
					}
					$skippedWeeks = $week2 - $week1 - 1;
					if ($skippedWeeks > 0) {
						// Skipping weeks
						$item->setSuggestedFlexitime($item->getSuggestedFlexitime() - $this->employee->getTimePerWeek() * $skippedWeeks);
					}
				}
			}

			// Check if item is consitent
			$checkIfConsistent = $item->getSuggestedOvertime() + $item->getSuggestedFlexitime() - $item->getFlexitime() - $item->getOvertime() - $item->getDenied();

			$skip = false;

			// Is the day over?
			if ($nextItem != null) {
				$nextDay = dayKey($nextItem->getStart());
				if ($itemDay == $nextDay) {
					if ($checkIfConsistent != 0) {
						$skip = true;
						$item->setSuggestedFlexitime(0); // Item is not consistent, but the day is not over yet.
					}
				}
			}


			if ($checkIfConsistent == 0) {
				$skip = true;
			}
			if ($item->getTime() == 0) {
				$skip = true;
			}
			$this->worktime = $this->worktime - $item->getSuggestedFlexitime();
			if ($skip) {
				$item->setSuggestedFlexitime(0);
			}
			else {
				// Item is not consistent, calculating...
				if ($today == 6) {
					if ($item->getSuggestedFlexitime() > 0) {
						$item->setSuggestedFlexitime(0);
					}
				}

				if ($item->getSuggestedFlexitime() > 0) {
					if ($item->getOvertime() > 0) {
						$item->setSuggestedFlexitime($item->getSuggestedFlexitime() - $item->getOvertime());
						if ($item->getSuggestedFlexitime() < 0) {
							$item->setOvertime($item->getOvertime() + $item->getSuggestedFlexitime());
							$item->setSuggestedFlexitime(0);
						}
						else {
							$item->setSuggestedOvertime($item->getOvertime());
						}
					}
					else {
						// If the flexitime exceeds the tollerance level, assign it all to overtime
						if ($item->getSuggestedFlexitime() > Settings::$OVERTIME) {
							$item->setSuggestedOvertime($item->getSuggestedFlexitime() - Settings::$OVERTIME);
							$item->setSuggestedFlexitime(Settings::$OVERTIME);
						}
					}
				}

				if ($item->getDenied() > 0) {
					$item->setSuggestedFlexitime($item->getSuggestedFlexitime() - $item->getDenied());
					if ($item->getSuggestedFlexitime() < 0) {
						$item->setSuggestedDenied($item->getDenied() + $item->getSuggestedFlexitime());
						$item->setSuggestedFlexitime(0);
					}
					else {
						$item->setSuggestedDenied($item->getDenied());
					}
				}
				else {

					$newFlexitime = $this->flexitime + $item->getSuggestedFlexitime();
					if ($newFlexitime > Settings::$FLEXITIME_LIMIT) {
						if ($item->getSuggestedFlexitime() > 0) {
							$item->setSuggestedDenied($item->getSuggestedFlexitime());
						}
					}
				}

				$this->flexitime = $this->flexitime + $item->getSuggestedFlexitime();

				// TODO add tasksum for day
				$this->taskSum[$itemDay] = 0;
				// Overtime needs at least the same time spent on tasks
				if ($item->getSuggestedOvertime() > $this->taskSum[$itemDay]) {
					$delta = $item->getSuggestedOvertime() - $this->taskSum[$itemDay];
					$item->setSuggestedOvertime($item->getSuggestedOvertime() - $delta);
					$item->setSuggestedDenied($item->getSuggestedDenied() + $item->getSuggestedOvertime() + $delta);
				}

				$item->setSuggestedFlexitime(round($item->getSuggestedFlexitime()));
				$item->setSuggestedOvertime(round($item->getSuggestedOvertime()));
				$item->setSuggestedDenied(round($item->getSuggestedDenied()));
				$item->setSuggestedResttime(round($item->getSuggestedResttime()));

				if ($autocalc) {
					$item->takeSuggestions();
					$item->save();
				}
			}

		}

		private function calcList(&$list) {
			$length = count($list);
			for ($index = 0; $index < $length; $index++) {
				$currentItem = $list[$index];
				$previousItem = null;
				if ($index > 0 ) {
					if ($currentItem->startsOnSameDay($list[$index - 1])) {
						$previousItem = $list[$index - 1];
					}
				}
				$nextItem = null;
				if (array_key_exists($index + 1, $list)) {
					$nextItem = $list[$index + 1];
				}
				$this->calcItem($currentItem, $previousItem, $nextItem, $this->autocalc);
			}
		}

		public function calcClockings(&$list, $userid, $start, $end) {

			$this->employee  = UserQuery::create()
									-> filterById($userid)
									-> findOne();

			$domain = $this->employee->getDomain();

			$vacationDays = HolidayQuery::create()
							-> filterByDomain($domain)
							-> filterByDate($start, Criteria::GREATER_EQUAL)
							-> filterByDate($end, Criteria::LESS_EQUAL)
							-> find();
			$this->holidays = array();
			foreach ($vacationDays as $day) {
				$key = dayKey($day->getDate());
				$this->holidays[$key] = $day;
			}

			// Get flexitime
			$this->flexitime = ClockingQuery::create()
									-> filterByUserId($userid)
									-> filterByStart($start, Criteria::LESS_THAN)
									-> filterByVisibility(0)
									-> withColumn('SUM(flexitime)', 'flexitimeSum')
									-> findOne()
									-> getFlexitimeSum();

			$this->holidaysWeeks = Holiday::getCountPerWeek($domain, $start, $end);

			// Calculate weektime for first item
			$first = $list->getFirst();
			if ($first == null) {
				// No items at all, stop here
				return;
			}
			$weekday = date('N', $first->getStart());
			$currentWeek = date('W', $first->getStart());
			$currentYear = date('Y', $first->getStart());

			// Count any holiday as 'work done'
			$weekKey = $currentWeek.'-'.$currentYear;
			if (!array_key_exists($weekKey, $this->holidaysWeeks)) {
				$this->holidaysWeeks[$weekKey] = 0;
			}
			$this->worktime = $this->holidaysWeeks[$weekKey] * $this->employee->getDailyTime();
			if ($weekday > 1) {
				$weekstart = createDate($first->getStart());
				$weekstart->modify('midnight this week');
				$weekend = createDate($first->getStart());
				$weekend->modify('midnight this week +7 days');
				$week = ClockingQuery::create()
							-> filterByStart($weekstart->getTimestamp(), Criteria::GREATER_THAN) // Should be 'GREATER_EQUAL'?
							-> filterByStart($weekend->getTimestamp(), Criteria::LESS_THAN)
							-> filterByUser($this->employee)
							-> filterByVisibility(0)
							-> find();
				$this->calcList($week);
			}

			$connection = Propel::getConnection(ClockingPeer::DATABASE_NAME);
			$connection->beginTransaction();
			try {
				$this->calcList($list);
				$connection->commit();
			}
			catch (Exception $e) {
				$connection->rollBack();
				throw $e;
			}
		}
	}

?>