<?php
namespace TYPO3\CMS\Cal\View;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * Base model for the day.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class NewMonthView extends \TYPO3\CMS\Cal\View\NewTimeView {
	
	protected $weeks;
	protected $maxWeeksInYear = 52;
	protected $monthStartWeekdayNum;
	protected $monthLength;
	
	/**
	 * Constructor.
	 */
	public function __construct($month, $year) {
		parent::__construct();
		$this->setMySubpart('MONTH_SUBPART');
		$this->setMonth(intval ($month));
		$this->setYear(intval ($year));
		$this->generateWeeks ();
		$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$controller->cache->set ($month . '_' . $year, serialize ($this), 'month', 60 * 60 * 24 * 365 * 100);
	}
	
	public static function getMonthView($month, $year) {
		$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$cache = $controller->cache->get ($month . '_' . $year);
// 		if ($cache != '') {
// 			try {
// 				$return = unserialize ($cache);
// 				if ($return === FALSE) {
// 					// debug('could not unserialize cache for month:'.$month.'_'.$year);
// 				}
// 				return $return;
// 			} catch (\Exception $e){
				
// 			}
// 		}
		return new NewMonthView ($month, $year);
	}
	private function generateWeeks() {
		$date = new \TYPO3\CMS\Cal\Model\CalDate ();
		$date->setDay (1);
		$date->setMonth ($this->getMonth());
		$date->setYear ($this->getYear());
		$this->monthStartWeekdayNum = $date->format ('%w');
		$this->monthLength = $date->getDaysInMonth ();
		$monthEnd = \TYPO3\CMS\Cal\Controller\Calendar::calculateEndMonthTime ($date);
		
		$weekEnd = $monthEnd->getWeekOfYear ();
		$newDate = \TYPO3\CMS\Cal\Controller\Calendar::calculateStartWeekTime ($date);
		
		$this->weeks = Array ();
		$weekNumber = $newDate->getWeekOfYear ();
		
		if ($this->getMonth() == 12 && $weekEnd == 1) {
			do {
				if ($weekNumber == $weekEnd) {
					$this->weeks [($newDate->getYear () + 1) . '_' . $weekNumber] = new \TYPO3\CMS\Cal\View\NewWeekView ($weekNumber, $newDate->getYear () + 1, $this->getMonth());
				} else {
					$this->weeks [$newDate->getYear () . '_' . $weekNumber] = new \TYPO3\CMS\Cal\View\NewWeekView ($weekNumber, $newDate->getYear (), $this->getMonth());
				}
				$newDate->addSeconds (86400 * 7);
				$weekNumber = $newDate->getWeekOfYear ();
				$weekNumberTmp = $weekNumber;
				if ($weekNumber != $weekEnd) {
					$weekNumberTmp = 0;
				}
			} while ($weekNumberTmp <= $weekEnd && $newDate->year == $this->getYear());
		} else if ($this->getMonth() == 1) {
			do {
				if ($weekNumber > 6) {
					$this->weeks [$newDate->getYear () . '_' . $weekNumber] = new \TYPO3\CMS\Cal\View\NewWeekView ($weekNumber, $newDate->getYear (), $this->getMonth());
				} else {
					$this->weeks [$this->getYear() . '_' . $weekNumber] = new \TYPO3\CMS\Cal\View\NewWeekView ($weekNumber, $this->getYear(), $this->getMonth());
				}
				$newDate->addSeconds (86400 * 7);
				$weekNumber = $newDate->getWeekOfYear ();
			} while ($weekNumber <= $weekEnd && $newDate->year == $this->getYear());
		} else {
			do {
				$this->weeks [$this->getYear() . '_' . $weekNumber] = new \TYPO3\CMS\Cal\View\NewWeekView ($weekNumber, $newDate->getYear (), $this->getMonth());
				$newDate->addSeconds (86400 * 7);
				$weekNumber = $newDate->getWeekOfYear ();
			} while ($weekNumber <= $weekEnd && $newDate->getYear() == $this->getYear());
		}
		$this->maxWeeksInYear = max ($this->maxWeeksInYear, $weekNumber);
	}
	public function addEvent(&$event) {
		$eventStartWeek = $event->getStart ()->getWeekOfYear ();
		$eventEndWeek = $event->getEnd ()->getWeekOfYear ();
		$eventStartYear = $event->getStart ()->year;
		$eventEndYear = $event->getEnd ()->year;
		if (($eventStartWeek == 52 || $eventStartWeek == 53) && $event->getStart ()->month == 1) {
			$eventStartYear --;
		}
		if (($eventEndWeek == 52 || $eventEndWeek == 53) && $event->getEnd ()->month == 1) {
			$eventEndYear --;
		}
		if ($eventStartWeek == 1 && $event->getStart ()->month == 12) {
			$eventStartYear ++;
		}
		if ($eventEndWeek == 1 && $event->getEnd ()->month == 12) {
			$eventEndYear ++;
		}
		do {
			if ($this->weeks [$eventStartYear . '_' . $eventStartWeek]) {
				$this->weeks [$eventStartYear . '_' . $eventStartWeek]->addEvent ($event);
			}
			$eventStartWeek ++;
			if ($eventStartWeek > $this->maxWeeksInYear) {
				$eventStartWeek = 1;
				$eventStartYear ++;
			}
		} while (! (($eventStartYear == $eventEndYear && $eventStartWeek > $eventEndWeek) || ($eventStartYear > $eventEndYear)));
	}
	public function getWeeksMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$content = '';
		foreach ($this->weeks as $week) {
			$content .= $week->render ($this->getTemplate());
		}
		$sims ['###WEEKS###'] = $content;
	}
	public function getWeekdaysMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->setMySubpart('MONTH_WEEKDAYS_SUBPART');
		if (DATE_CALC_BEGIN_WEEKDAY == 0) {
			$this->setMySubpart('SUNDAY_MONTH_WEEKDAYS_SUBPART');
		}
		$sims ['###WEEKDAYS###'] = $this->render ($this->getTemplate());
		$this->setMySubpart('MONTH_SUBPART');
	}
	public function setSelected(&$dateObject) {
		if ($dateObject->year == $this->getYear() && $dateObject->month == $this->getMonth()) {
			$this->selected = true;
			
			$week = $this->weeks [$dateObject->year . '_' . $dateObject->getWeekOfYear ()];
			if (is_object ($week)) {
				$week->setSelected ($dateObject);
			}
		}
	}
	public function setCurrent(&$dateObject) {
		if ($dateObject->year == $this->getYear() && $dateObject->month == $this->getMonth()) {
			$this->current = true;
			
			$week = $this->weeks [$dateObject->year . '_' . $dateObject->getWeekOfYear ()];
			if (is_object ($week)) {
				$week->setCurrent ($dateObject);
			}
		}
	}
	public function getMonthTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$current_month = new \TYPO3\CMS\Cal\Model\CalDate ();
		$current_month->setMonth ($this->getMonth());
		$current_month->setYear ($this->getYear());
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$sims ['###MONTH_TITLE###'] = $current_month->format ($conf ['view.'] [$view . '.'] ['dateFormatMonth']);
	}
	
	public function hasEvents() {
	    return !empty($this->getEvents()) || $this->getHasAlldayEvents();
	}
}

?>