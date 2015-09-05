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
class NewWeekView extends \TYPO3\CMS\Cal\View\NewTimeView {
	
	protected $week;
	protected $year;
	protected $month;
	protected $days;
	protected $alldays;
	protected $dayNums;
	protected $weekStart;
	protected $weekEnd;
	protected $rowspan = false;
	protected $content = false;
	protected $weekHasEvent = false;
	protected $dayHasEvent = Array (
			false,
			false,
			false,
			false,
			false,
			false,
			false 
	);
	protected $currentDayIndex = 9;
	protected $initialized = false;
	
	/**
	 * Constructor.
	 */
	public function __construct($week, $year, $parentMonth = -1) {
		parent::__construct();
		$this->mySubpart = 'WEEK_SUBPART';
		if (DATE_CALC_BEGIN_WEEKDAY == 0) {
			$this->mySubpart = 'SUNDAY_WEEK_SUBPART';
		}
		$this->week = intval ($week);
		$this->year = intval ($year);
		$this->setParentMonth ($parentMonth);
		$this->generateDays ();
	}
	private function generateDays() {
		$weekStart = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getDayByWeek ($this->year, $this->week, DATE_CALC_BEGIN_WEEKDAY));
		
		$this->weekStart = $weekStart->format ('%Y%m%d');
		$this->month = $weekStart->getMonth ();
		
		if ($this->getParentMonth () < 0) {
			$this->setParentMonth ($this->month);
		}
		
		$this->days = Array ();
		$this->alldays = Array ();
		$this->dayNums = Array ();
		for ($i = 0; $i < 7; $i ++) {
			$this->dayNums [$i] = $weekStart->day;
			$weekStartFormated = $weekStart->format ('%Y%m%d');
			$this->days [$weekStartFormated] = new \TYPO3\CMS\Cal\View\NewDayView ($weekStart->day, $weekStart->month, $weekStart->year, $this->getParentMonth ());
			$this->alldays [$weekStartFormated] = Array ();
			$weekStart->addSeconds (86400);
		}
		$this->weekEnd = $weekStart->format ('%Y%m%d');
	}
	public function addEvent(&$event) {
		$eventStart = new \TYPO3\CMS\Cal\Model\CalDate ();
		$eventStart->copy ($event->getStart ());
		$eventStartFormatted = $eventStart->format ('%Y%m%d');
		$eventStartYear = $eventStart->year;
		$eventEndFormatted = $event->getEnd ()->format ('%Y%m%d');
		$eventEndYear = $event->getEnd ()->year;
		$eventStartWeek = $event->getStart ()->getWeekOfYear ();
		$eventEndWeek = $event->getEnd ()->getWeekOfYear ();
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
		if ($event->isAllday () || $eventStartFormatted != $eventEndFormatted) {
			$eventYearEnd = $event->getEnd ()->year;
			if ($event->getEnd ()->month == 12 && $event->getEnd ()->getWeekOfYear () == 1) {
				$eventYearEnd ++;
			}
			
			if (! ($eventStartYear == $this->year && $eventStart->getWeekOfYear () == $this->week) && $eventStart->year . sprintf ("%02d", $eventStart->getWeekOfYear ()) < $this->year . sprintf ("%02d", $this->week) && $eventYearEnd . sprintf ("%02d", $event->getEnd ()->getWeekOfYear ()) >= $this->year . sprintf ("%02d", $this->week)) {
				do {
					$eventStart->addSeconds (86400);
					$eventStartYear = $eventStart->year;
					$eventWeek = $eventStart->getWeekOfYear ();
					if ($eventStart->month == 1 && $eventWeek > 50) {
						$eventStartYear --;
					}
				} while ($eventStartYear . sprintf ("%02d", $eventWeek) < $this->year . sprintf ("%02d", $this->week));
				$eventStartFormatted = $eventStart->format ('%Y%m%d');
			}
			if ($eventStartYear == $this->year && $eventStart->getWeekOfYear () == $this->week) {
				$this->alldays [$eventStartFormatted] [] = $event;
				$this->weekHasEvent = true;
				$first = true;
				do {
					$this->dayHasEvent [$eventStart->getDayOfWeek ()] = true;
					if (is_object ($this->days [$eventStart->format ('%Y%m%d')])) {
						$this->days [$eventStart->format ('%Y%m%d')]->hasAlldayEvents = true;
						if ($first) {
							$this->days [$eventStart->format ('%Y%m%d')]->addEvent ($event);
							$first = false;
						}
					}
					$eventStart->addSeconds (86400);
					$eventStartYear = $eventStart->year;
					$eventWeek = $eventStart->getWeekOfYear ();
					if ($eventStart->month == 1 && $eventWeek > 50) {
						$eventStartYear --;
					}
				} while ($eventStart->format ('%Y%m%d') <= $eventEndFormatted && $eventStartYear . sprintf ("%02d", $eventStart->getWeekOfYear ()) <= $this->year . sprintf ("%02d", $this->week));
			}
		} else {
			do {
				if ($eventStartYear == $this->year && $eventStartWeek == $this->week) {
					$this->dayHasEvent [$eventStart->getDayOfWeek ()] = true;
					if (is_object ($this->days [$eventStart->format ('%Y%m%d')])) {
						$this->days [$eventStart->format ('%Y%m%d')]->addEvent ($event);
					}
					$this->weekHasEvent = true;
				}
				$eventStart->addSeconds (86400);
				$eventStartYear = $eventStart->year;
				if ($eventStart->month == 1 && $eventWeek > 50) {
					$eventStartYear --;
				}
				$eventStartWeek = $eventStart->getWeekOfYear ();
			} while ($eventStart->format ('%Y%m%d') <= $eventEndFormatted && $eventStartWeek <= $this->week);
		}
	}
	public function getRowspanMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->rowspan === false) {
			if ($view == 'month') {
				$this->getEventsMarker ($template, $sims, $rems, $wrapped, $view);
			} else {
				$this->getAlldaysMarker ($template, $sims, $rems, $wrapped, $view);
			}
		}
		$sims ['###ROWSPAN###'] = $this->rowspan + 1;
	}
	public function getAlldaysMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->content === false) {
			
			$this->content = '';
			
			$cobj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
			$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
			
			// 1. find out the start and length of each event in relation to this week
			// 2. sort by length
			// 3. start with the larges and position it in a yx-matrix with x = 7 and y = size of alldays
			// 3a. find an empty spot starting with y = 0;
			// 3b. reserve the spots and write the position in a 2 dimensional Array
			// 4. go through the result array and create cells (TS) for each entry
			
			$lengthArray = Array ();
			$alldaysKeys = array_keys ($this->alldays);
			
			foreach ($alldaysKeys as $alldaysKey) {
				$entryKeys = array_keys ($this->alldays [$alldaysKey]);
				foreach ($entryKeys as $entryKey) {
					
					$event = &$this->alldays [$alldaysKey] [$entryKey];
					$this->fillLengthArray ($lengthArray, $event);
				}
			}
			
			$this->renderLengthArray ($lengthArray, $view);
		}
		
		$sims ['###ALLDAYS###'] = $this->content;
	}
	public function getEventsMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->content === false) {
			
			$this->content = '';
			
			$cobj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
			$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
			
			// 1. find out the start and length of each event in relation to this week
			// 2. sort by length
			// 3. start with the larges and position it in a yx-matrix with x = 7 and y = size of alldays
			// 3a. find an empty spot starting with y = 0;
			// 3b. reserve the spots and write the position in a 2 dimensional Array
			// 4. go through the result array and create cells (TS) for each entry
			
			$lengthArray = Array ();
			
			$dayKeys = array_keys ($this->days);
			$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
			$currentMonth = $controller->getDateTimeObject->getMonth ();
			
			for ($i = 0; $i < 7; $i ++) {
				$timeKeys = array_keys ($this->days [$dayKeys [$i]]->events);
				
				foreach ($timeKeys as $timeKey) {
					$entryKeys = array_keys ($this->days [$dayKeys [$i]]->events [$timeKey]);
					foreach ($entryKeys as $entryKey) {
						
						$event = &$this->days [$dayKeys [$i]]->events [$timeKey] [$entryKey];
						$this->fillLengthArray ($lengthArray, $event);
					}
				}
			}
			
			$this->renderLengthArray ($lengthArray, $view);
		}
		
		$sims ['###EVENTS###'] = $this->content;
	}
	private function fillLengthArray(&$lengthArray, &$event) {
		$eventStart = $event->getStart ()->format ('%Y%m%d');
		$eventEnd = $event->getEnd ()->format ('%Y%m%d');
		if ($eventStart <= $this->weekStart) {
			if ($eventEnd >= $this->weekEnd) {
				// lasts the whole week
				$length = 7;
				$start = 0;
			} else {
				$length = intval ($event->getEnd ()->getDayOfWeek ());
				if ($length == 0) {
					if (DATE_CALC_BEGIN_WEEKDAY == 1) {
						$length = 7;
					} else {
						$length = 1;
					}
				}
				$start = 0;
			}
		} else {
			$start = intval ($event->getStart ()->getDayOfWeek ());
			if (DATE_CALC_BEGIN_WEEKDAY == 1) {
				$start --;
				if ($start == - 1) {
					$start = 6;
				}
			}
			if ($eventEnd >= $this->weekEnd) {
				$length = 7 - $start;
			} else {
				$weekEnd = intval ($event->getEnd ()->getDayOfWeek ());
				if (DATE_CALC_BEGIN_WEEKDAY == 1) {
					$weekEnd --;
					if ($weekEnd == - 1) {
						$weekEnd = 6;
					}
				}
				$length = ($weekEnd - $start) + 1;
			}
		}
		$lengthArray [$length . '_' . $start] [] = &$event;
	}
	private function renderLengthArray(&$lengthArray, $view) {
		krsort ($lengthArray);
		
		$theMatrix = Array ();
		$resultMatrix = Array ();
		$lengthArrayKeys = array_keys ($lengthArray);
		$this->rowspan = 1;
		
		foreach ($lengthArrayKeys as $lengthArrayKey) {
			$values = explode ('_', $lengthArrayKey);
			
			$eventArrayKeys = array_keys ($lengthArray [$lengthArrayKey]);
			foreach ($eventArrayKeys as $eventArrayKey) {
				$done = false;
				for ($i = $values [1]; $i < 7 && ! $done; $i ++) {
					for ($j = 0; $j < 1000 && ! $done; $j ++) {
						if (! $theMatrix [$i] [$j] && $values [0] + $i < 8) {
							// Found an empty start spot
							$empty = true;
							for ($k = $i; $k < $values [0] + $i && $empty; $k ++) {
								if ($theMatrix [$k] [$j]) {
									$empty = false;
								}
							}
							if ($empty) {
								$theMatrix [$i] [$j] = &$lengthArray [$lengthArrayKey] [$eventArrayKey];
								$theMatrix [$i] [$j]->matrixValue = $values [0];
								// fill it
								for ($k = $i + 1; $k < $values [0] + $i && $empty; $k ++) {
									$theMatrix [$k] [$j] = true;
								}
								$done = true;
							}
						}
					}
					if ($this->rowspan < $j) {
						$this->rowspan = $j;
					}
				}
			}
		}
		switch ($view) {
			case 'month' :
				$this->renderAlldaysForMonth ($theMatrix);
				break;
			case 'week' :
				$this->renderAlldaysForWeek ($theMatrix);
				break;
		}
	}
	private function renderAlldaysForWeek(&$theMatrix) {
		$classes = $this->getWeekClasses ();
		$daysKeys = array_keys ($this->days);
		
		for ($j = 0; $j < $this->rowspan; $j ++) {
			$this->content .= '<tr class="alldays ' . $classes . '">';
			for ($i = 0; $i < 7; $i ++) {
				$currentDayClass = ' weekday' . $this->days [$daysKeys [$i]]->weekdayNumber;
				if ($this->currentDayIndex == $i) {
					$currentDayClass .= ' currentDay';
				}
				if ($theMatrix [$i] [$j] == false) {
					$this->content .= '<td class="' . $classes . $currentDayClass . '">&nbsp;</td>';
				} else if (is_object ($theMatrix [$i] [$j])) {
					$this->content .= '<td class="' . $classes . $currentDayClass . '" colspan="' . ($theMatrix [$i] [$j]->matrixValue) . '">' . $theMatrix [$i] [$j]->renderEventFor ('week') . '</td>';
					$this->days [$daysKeys [$i]]->hasAlldayEvents = true;
				}
			}
			$this->content .= '</tr>';
		}
	}
	private function renderAlldaysForMonth(&$theMatrix) {
		$classes = $this->getWeekClasses ();
		$daysKeys = array_keys ($this->days);
		$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$currentMonth = $controller->getDateTimeObject->getMonth ();
		
		$sims = $rems = $wrapped = Array ();
		$template = '';
		
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		for ($j = 0; $j < $this->rowspan; $j ++) {
			$this->content .= '<tr class="' . $classes . '">';
			for ($i = 0; $i < 7; $i ++) {
				$currentDayClass = ' weekday' . $this->days [$daysKeys [$i]]->weekdayNumber;
				if ($this->currentDayIndex == $i) {
					$currentDayClass = ' currentDay';
				}
				if ($currentMonth != $this->days [$daysKeys [$i]]->month) {
					$currentDayClass .= ' monthOff';
				}
				if ($theMatrix [$i] [$j] == false) {
					$this->content .= '<td class="empty ' . $classes . $currentDayClass . '">';
				} else if (is_object ($theMatrix [$i] [$j])) {
					$this->content .= '<td class="event ' . $classes . $currentDayClass . '" colspan="' . ($theMatrix [$i] [$j]->matrixValue) . '">' . $theMatrix [$i] [$j]->renderEventFor ('month');
					$this->days [$daysKeys [$i]]->hasAlldayEvents = true;
				}
				$this->content .= '</td>';
			}
			$this->content .= '</tr>';
		}
		$this->content .= '<tr class="create">';
		for ($i = 0; $i < 7; $i ++) {
			$this->days [$daysKeys [$i]]->getCreateEventLinkMarker ($template, $sims, $rems, $wrapped, $conf ['view']);
			$this->content .= '<td>' . $sims ['###CREATE_EVENT_LINK###'] . '</td>';
		}
		$this->content .= '</tr>';
		$this->rowspan ++;
	}
	public function getWeekClassesMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###WEEK_CLASSES###'] = $this->getWeekClasses ();
	}
	private function getWeekClasses() {
		$classes = '';
		if ($this->current) {
			$classes .= ' currentWeek';
		}
		if ($this->selected) {
			$classes .= ' selectedWeek';
		}
		
		if ($this->weekHasEvent) {
			$classes .= ' withEventWeek';
		}
		return trim ($classes);
	}
	public function getWeekLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$weekLinkViewTarget = $conf ['view.'] ['weekLinkTarget'];
		
		$local_cObj = &$this->getLocalCObject ();
		$local_cObj->setCurrentVal ($this->week);
		$local_cObj->data ['view'] = $weekLinkViewTarget;
		$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		
		if (($rightsObj->isViewEnabled ($weekLinkViewTarget) || $conf ['view.'] [$weekLinkViewTarget . '.'] [$weekLinkViewTarget . 'ViewPid']) && $this->weekHasEvent) {
			$controller->getParametersForTyposcriptLink ($local_cObj->data, Array (
					'getdate' => $this->weekStart,
					'view' => $weekLinkViewTarget,
					$controller->getPointerName () => NULL 
			), $conf ['cache'], $conf ['clear_anyway'], $conf ['view.'] [$weekLinkViewTarget . '.'] [$weekLinkViewTarget . 'ViewPid']);
		}
		$sims ['###WEEK_LINK###'] = $local_cObj->cObjGetSingle ($conf ['view.'] [$view . '.'] [$weekLinkViewTarget . 'ViewLink'], $conf ['view.'] [$view . '.'] [$weekLinkViewTarget . 'ViewLink.']);
	}
	public function getDaysMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$content = '';
		foreach ($this->days as $day) {
			$content .= $day->render ($this->template);
		}
		$sims ['###DAYS###'] = $content;
	}
	public function getDaynum0Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM0###'] = $this->getDayLink (0);
	}
	public function getDaynum1Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM1###'] = $this->getDayLink (1);
	}
	public function getDaynum2Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM2###'] = $this->getDayLink (2);
	}
	public function getDaynum3Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM3###'] = $this->getDayLink (3);
	}
	public function getDaynum4Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM4###'] = $this->getDayLink (4);
	}
	public function getDaynum5Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM5###'] = $this->getDayLink (5);
	}
	public function getDaynum6Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DAYNUM6###'] = $this->getDayLink (6);
	}
	public function getClasses0Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES0###'] = $this->getDayClasses (0);
	}
	public function getClasses1Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES1###'] = $this->getDayClasses (1);
	}
	public function getClasses2Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES2###'] = $this->getDayClasses (2);
	}
	public function getClasses3Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES3###'] = $this->getDayClasses (3);
	}
	public function getClasses4Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES4###'] = $this->getDayClasses (4);
	}
	public function getClasses5Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES5###'] = $this->getDayClasses (5);
	}
	public function getClasses6Marker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CLASSES6###'] = $this->getDayClasses (6);
	}
	private function getDayClasses($weekdayIndex) {
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		if ($this->initialized === false) {
			$this->getAlldaysMarker ($template, $sims, $rems, $wrapped, $conf ['view']);
			$this->initialized = true;
		}
		$classes = '';
		if ($this->dayHasEvent [$weekdayIndex] == 1) {
			$classes .= ' ' . $conf['view.']['month.']['eventDayStyle'];
		}
		
		$localDayIndex = $this->currentDayIndex + DATE_CALC_BEGIN_WEEKDAY;
		if ($localDayIndex == 7) {
			$localDayIndex = 0;
		}
		
		if ($localDayIndex == $weekdayIndex) {
			$classes .= ' ' . $conf['view.']['month.']['monthTodayStyle'];
		}
		
		$localDayIndex = $weekdayIndex - DATE_CALC_BEGIN_WEEKDAY;
		if ($localDayIndex == - 1) {
			$localDayIndex = 6;
		}
		$daysKeys = array_keys ($this->days);
		if (intval ($this->getParentMonth ()) != intval ($this->days [$daysKeys [$localDayIndex]]->month)) {
			$classes .= ' ' . $conf['view.']['month.']['monthOffStyle'];
		}
		
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray('NewWeekView','postDayClassesViewMarker','view');
		// Hook: postDayClassesViewMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDayClassesViewMarker')) {
				$hookObj->postDayClassesViewMarker($this, $weekdayIndex, $classes);
			}
		}
		
		return $classes;
	}
	private function getDayLink($weekdayIndex) {
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		if ($this->initialized === false) {
			// initializing!!
			$this->getAlldaysMarker ($template, $sims, $rems, $wrapped, $conf ['view']);
			$this->initialized = true;
		}
		
		$daysKeys = array_keys ($this->days);
		return $this->days [$daysKeys [$weekdayIndex]]->getDayLink ($conf ['view'], $this->days [$daysKeys [$weekdayIndex]]->time, $this->dayHasEvent [$this->days [$daysKeys [$weekdayIndex]]->weekdayNumber]);
	}
	public function setSelected(&$dateObject) {
		if ($dateObject->getWeekOfYear () == $this->week && $dateObject->year == $this->year) {
			$this->selected = true;
			
			$day = $this->days [$dateObject->format ('%Y%m%d')];
			if (is_object ($day)) {
				$day->setSelected ($dateObject);
			}
		}
	}
	public function setCurrent(&$dateObject) {
		if ($dateObject->getWeekOfYear () == $this->week && $dateObject->year == $this->year) {
			$this->current = true;
			
			$day = $this->days [$dateObject->format ('%Y%m%d')];
			if (is_object ($day)) {
				$this->currentDayIndex = $dateObject->getDayOfWeek ();
				if (DATE_CALC_BEGIN_WEEKDAY == 1) {
					$this->currentDayIndex --;
					if ($this->currentDayIndex == - 1) {
						$this->currentDayIndex = 6;
					}
				}
				$day->setCurrent ($dateObject);
			}
		}
	}
}

?>