<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Steffen Kamper
 * (c) 2005-2008 Christian Technology Ministries International Inc.
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
 * International (http://CTMIinc.org). The WEC is developing TYPO3-based
 * (http://typo3.org) free software for churches around the world. Our desire
 * is to use the Internet to help offer new life through Jesus Christ. Please
 * see http://WebEmpoweredChurch.org/Jesus.
 *
 * You can redistribute this file and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This file is distributed in the hope that it will be useful for ministry,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the file!
 ***************************************************************/

require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_timeview.php');
require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_dayview.php');

/**
 * Base model for the day.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_new_weekview extends tx_cal_new_timeview {

	var $week;
	var $year;
	var $month;
	var $days;
	var $alldays;
	var $dayNums;
	var $weekStart;
	var $weekEnd;
	var $rowspan = false;
	var $content = false;
	var $weekHasEvent = false;
	var $dayHasEvent = Array(false,false,false,false,false,false,false);
	var $currentDayIndex = 9;
	var $initialized = false;

	/**
	 *  Constructor.
	 */
	public function tx_cal_new_weekview($week, $year, $parentMonth = -1){
		$this->tx_cal_new_timeview();
		$this->mySubpart = 'WEEK_SUBPART';
		if(DATE_CALC_BEGIN_WEEKDAY == 0){
			$this->mySubpart = 'SUNDAY_WEEK_SUBPART';
		}
		$this->week = intval($week);
		$this->year = intval($year);
		$this->setParentMonth($parentMonth);
		$this->generateDays();
	}

	private function generateDays(){
		$date = new tx_cal_date($this->year.'0101');
		$oldYearWeek = ($date->format('%U') > 1 )?0:1;
		$offset = $date->format('%w');
		if($offset==0){
			$offset = 7;
		}
		$days = Date_Calc::dateToDays($date->day,$date->month,$date->year);
		$daysTotal = ($this->week - $oldYearWeek) * 7 - $offset + $days + DATE_CALC_BEGIN_WEEKDAY;
		$weekStart = new tx_cal_date(Date_Calc::daysToDate($daysTotal,'%Y%m%d'));
		$this->weekStart = $weekStart->format('%Y%m%d');
		$this->month = $weekStart->getMonth();

		if ($this->getParentMonth() < 0) {
			$this->setParentMonth($this->month);
		} 		
		
		$this->days = Array();
		$this->alldays = Array();
		$this->dayNums = Array();
		for($i = 0; $i < 7; $i++){
			$this->dayNums[$i] = $weekStart->day;
			$weekStartFormated = $weekStart->format('%Y%m%d');
			$this->days[$weekStartFormated] = new tx_cal_new_dayview($weekStart->day, $weekStart->month,$weekStart->year, $this->getParentMonth());
			$this->alldays[$weekStartFormated] = Array();
			$weekStart->addSeconds(86400);
		}
		$this->weekEnd = $weekStart->format('%Y%m%d');
	}

	public function addEvent(&$event){
		$eventStart = new tx_cal_date();
		$eventStart->copy($event->getStart());
		$eventStartFormatted = $eventStart->format('%Y%m%d');
		$eventStartYear = $eventStart->year;
		$eventEndFormatted = $event->getEnd()->format('%Y%m%d');
		$eventEndYear = $event->getEnd()->year;
		$eventStartWeek = $event->getStart()->getWeekOfYear();
		$eventEndWeek = $event->getEnd()->getWeekOfYear();

		if($event->isAllday() || $eventStartFormatted != $eventEndFormatted){
			if($eventStart->year.sprintf("%02d",$eventStart->getWeekOfYear()) < $this->year.sprintf("%02d",$this->week) && $event->getEnd()->year.sprintf("%02d",$event->getEnd()->getWeekOfYear()) >= $this->year.sprintf("%02d",$this->week)){
				do {
					$eventStart->addSeconds(86400);
					$eventStartYear = $eventStart->year;
					$eventWeek = $eventStart->getWeekOfYear();
					if($eventStart->month == 1 && $eventWeek > 50){
						$eventStartYear--;
					}
				} while($eventStartYear.sprintf("%02d",$eventWeek) < $this->year.sprintf("%02d",$this->week));
				$eventStartFormatted = $eventStart->format('%Y%m%d');
			}
			if($eventStartYear == $this->year && $eventStart->getWeekOfYear() == $this->week) {
				$this->alldays[$eventStartFormatted][] = $event;
				$this->weekHasEvent = true;
				do{
					if(is_object($this->dayHasEvent[$eventStart->getDayOfWeek()])){
						$this->dayHasEvent[$eventStart->getDayOfWeek()] = true;
					}
					if(is_object($this->days[$eventStart->format('%Y%m%d')])){
						$this->days[$eventStart->format('%Y%m%d')]->hasAlldayEvents = true;
					}
					$eventStart->addSeconds(86400);
					$eventStartYear = $eventStart->year;
					$eventWeek = $eventStart->getWeekOfYear();
					if($eventStart->month == 1 && $eventWeek > 50){
						$eventStartYear--;
					}
				} while($eventStart->format('%Y%m%d') <= $eventEndFormatted && $eventStartYear.sprintf("%02d",$eventStart->getWeekOfYear()) <= $this->year.sprintf("%02d",$this->week));
			}
		} else {
			do{
				if($eventStartYear == $this->year && $eventStartWeek == $this->week) {
					$this->dayHasEvent[$eventStart->getDayOfWeek()] = true;
					if (is_object($this->days[$eventStart->format('%Y%m%d')])) {
						$this->days[$eventStart->format('%Y%m%d')]->addEvent($event);
					}
					$this->weekHasEvent = true;
				}
				$eventStart->addSeconds(86400);
				$eventStartYear = $eventStart->year;
				if($eventStart->month == 1 && $eventWeek > 50){
					$eventStartYear--;
				}
				$eventStartWeek = $eventStart->getWeekOfYear();
			} while($eventStart->format('%Y%m%d') <= $eventEndFormatted && $eventStartWeek <= $this->week);
		}
	}

	public function getRowspanMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		if($this->rowspan === false){
			$this->getAlldaysMarker($template, $sims, $rems, $wrapped, $view);
		}
		$sims['###ROWSPAN###'] = $this->rowspan + 2;
	}

	public function getAlldaysMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		if($this->content === false){
			$this->content = '';

			$cobj = &tx_cal_registry::Registry('basic','cobj');
			$conf = &tx_cal_registry::Registry('basic','conf');
				
			// 1. find out the start and length of each event in relation to this week
			// 2. sort by length
			// 3. start with the larges and position it in a yx-matrix with x = 7 and y = size of alldays
			// 3a. find an empty spot starting with y = 0;
			// 3b. reserve the spots and write the position in a 2 dimensional Array
			// 4. go through the result array and create cells (TS) for each entry

			$lengthArray = Array();
			$alldaysKeys = array_keys($this->alldays);

			foreach($alldaysKeys as $alldaysKey){
				$entryKeys = array_keys($this->alldays[$alldaysKey]);
				foreach($entryKeys as $entryKey){
						
					$event = &$this->alldays[$alldaysKey][$entryKey];
					$eventStart = $event->getStart()->format('%Y%m%d');
					$eventEnd = $event->getEnd()->format('%Y%m%d');
					if($eventStart <= $this->weekStart){
						if($eventEnd >= $this->weekEnd){
							//lasts the whole week
							$length = 7;
							$start = 0;
						} else {
							$length = intval($event->getEnd()->getDayOfWeek());
							if($length == 0){
								if(DATE_CALC_BEGIN_WEEKDAY==1){
									$length = 7;
								} else {
									$length = 1;
								}
							}
							$start = 0;
						}
					} else {
						$start = intval($event->getStart()->getDayOfWeek());
						if(DATE_CALC_BEGIN_WEEKDAY==1){
							$start--;
							if($start == -1){
								$start = 6;
							}
						}
						if($eventEnd >= $this->weekEnd){
							$length = 7 - $start;
						} else {
							$weekEnd = intval($event->getEnd()->getDayOfWeek());
							if(DATE_CALC_BEGIN_WEEKDAY==1){
								$weekEnd--;
								if($weekEnd == -1){
									$weekEnd = 6;
								}
							}
							$length = ($weekEnd - $start) + 1;
						}
					}
					$lengthArray[$length.'_'.$start][] = &$event;
				}
			}
				
			krsort($lengthArray);

			$theMatrix = Array();
			$resultMatrix = Array();
			$lengthArrayKeys = array_keys($lengthArray);
			$this->rowspan = 1;
			$alldaysKeys = array_keys($this->alldays);

			foreach($lengthArrayKeys as $lengthArrayKey){
				$values = explode('_',$lengthArrayKey);

				$eventArrayKeys = array_keys($lengthArray[$lengthArrayKey]);
				foreach($eventArrayKeys as $eventArrayKey){
					$done = false;
					for($i = $values[1]; $i < 7 && !$done; $i++){
						for($j = 0; $j < 1000 && !$done; $j++){
							if(!$theMatrix[$i][$j] && $values[0] + $i < 8){
								//Found an empty start spot
								$empty = true;
								for($k = $i; $k < $values[0] + $i && $empty; $k++){
									if($theMatrix[$k][$j]){
										$empty = false;
									}
								}
								if($empty){
									$theMatrix[$i][$j] = &$lengthArray[$lengthArrayKey][$eventArrayKey];
									$theMatrix[$i][$j]->matrixValue = $values[0];
									// fill it
									for($k = $i+1; $k < $values[0] + $i && $empty; $k++){
										$theMatrix[$k][$j] = true;
									}
									$done = true;
								}
							}
						}
						if($this->rowspan < $j){
							$this->rowspan = $j;
						}
					}
				}
			}

			switch($view){
				case 'month':
					$this->renderAlldaysForMonth($theMatrix);
					break;
				case 'week':
					$this->renderAlldaysForWeek($theMatrix);
					break;
			}
				
		}

		$sims['###ALLDAYS###'] = $this->content;
	}

	private function renderAlldaysForWeek(&$theMatrix){
		$classes = $this->getWeekClasses();
		$daysKeys = array_keys($this->days);

		for($j = 0; $j < $this->rowspan; $j++){
			$this->content .= '<tr class="alldays '.$classes.'">';
			for($i = 0; $i < 7; $i++){
				$currentDayClass = ' weekday'.$this->days[$daysKeys[$i]]->weekdayNumber;
				if($this->currentDayIndex == $i){
					$currentDayClass .= ' currentDay';
				}
				if($theMatrix[$i][$j] == false){
					$this->content .= '<td class="'.$classes.$currentDayClass.'">&nbsp;</td>';
				} else if(is_object($theMatrix[$i][$j])){
					$this->content .= '<td class="'.$classes.$currentDayClass.'" colspan="'.($theMatrix[$i][$j]->matrixValue).'">'.$theMatrix[$i][$j]->renderEventFor('week').'</td>';
					$this->days[$daysKeys[$i]]->hasAlldayEvents = true;
				}
			}
			$this->content .= '</tr>';
		}
	}

	private function renderAlldaysForMonth(&$theMatrix){
		$classes = $this->getWeekClasses();
		$daysKeys = array_keys($this->days);
		$controller = &tx_cal_registry::Registry('basic','controller');
		$currentMonth = $controller->getDateTimeObject->getMonth();

		for($j = 0; $j < $this->rowspan; $j++){
			$this->content .= '<tr class="alldays '.$classes.'">';
			for($i = 0; $i < 7; $i++){
				$currentDayClass = ' weekday'.$this->days[$daysKeys[$i]]->weekdayNumber;
				if($this->currentDayIndex == $i){
					$currentDayClass = ' currentDay';
				}
				if($currentMonth != $this->days[$daysKeys[$i]]->month){
					$currentDayClass .= ' monthOff';
				}
				if($theMatrix[$i][$j] == false){
					$this->content .= '<td class="empty '.$classes.$currentDayClass.'"></td>';
				} else if(is_object($theMatrix[$i][$j])){
					$this->content .= '<td class="event '.$classes.$currentDayClass.'" colspan="'.($theMatrix[$i][$j]->matrixValue).'">'.$theMatrix[$i][$j]->renderEventFor('month').'</td>';
					$this->days[$daysKeys[$i]]->hasAlldayEvents = true;
				}
			}
			$this->content .= '</tr>';
		}
	}

	public function getWeekClassesMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###WEEK_CLASSES###'] = $this->getWeekClasses();
	}

	private function getWeekClasses(){
		$classes = '';
		if($this->current){
			$classes .= ' currentWeek';
		}
		if($this->selected){
			$classes .= ' selectedWeek';
		}

		if($this->weekHasEvent){
			$classes .= ' withEventWeek';
		}
		return trim($classes);
	}

	public function getWeekLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$conf = &tx_cal_registry::Registry('basic','conf');
		$weekLinkViewTarget = $conf['view.']['weekLinkTarget'];

		$local_cObj = &$this->getLocalCObject();
		$local_cObj->setCurrentVal($this->week);
		$local_cObj->data['view'] = $weekLinkViewTarget;
		$controller = &tx_cal_registry::Registry('basic','controller');

		if(($rightsObj->isViewEnabled($weekLinkViewTarget) || $conf['view.'][$weekLinkViewTarget.'.'][$weekLinkViewTarget.'ViewPid']) && $this->weekHasEvent){
			$controller->getParametersForTyposcriptLink($local_cObj->data, array ('getdate' => $this->weekStart, 'view' => $weekLinkViewTarget,  $controller->getPointerName() => NULL), $conf['cache'], $conf['clear_anyway'], $conf['view.'][$weekLinkViewTarget.'.'][$weekLinkViewTarget.'ViewPid']);
		}
		$sims['###WEEK_LINK###'] = $local_cObj->cObjGetSingle($conf['view.'][$view.'.'][$weekLinkViewTarget.'ViewLink'],$conf['view.'][$view.'.'][$weekLinkViewTarget.'ViewLink.']);
	}

	public function getDaysMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$content = '';
		foreach($this->days as $day){
			$content .= $day->render($this->template);
		}
		$sims['###DAYS###'] = $content;
	}

	public function getDaynum0Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM0###'] = $this->getDayLink(0);
	}

	public function getDaynum1Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM1###'] = $this->getDayLink(1);
	}

	public function getDaynum2Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM2###'] = $this->getDayLink(2);
	}

	public function getDaynum3Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM3###'] = $this->getDayLink(3);
	}

	public function getDaynum4Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM4###'] = $this->getDayLink(4);
	}

	public function getDaynum5Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM5###'] = $this->getDayLink(5);
	}

	public function getDaynum6Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAYNUM6###'] = $this->getDayLink(6);
	}

	public function getClasses0Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES0###'] = $this->getDayClasses(0);
	}

	public function getClasses1Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES1###'] = $this->getDayClasses(1);
	}

	public function getClasses2Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES2###'] = $this->getDayClasses(2);
	}

	public function getClasses3Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES3###'] = $this->getDayClasses(3);
	}

	public function getClasses4Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES4###'] = $this->getDayClasses(4);
	}

	public function getClasses5Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES5###'] = $this->getDayClasses(5);
	}

	public function getClasses6Marker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###CLASSES6###'] = $this->getDayClasses(6);
	}

	private function getDayClasses($weekdayIndex){
		if($this->initialized === false){
			$conf = &tx_cal_registry::Registry('basic','conf');
			$this->getAlldaysMarker($template, $sims, $rems, $wrapped, $conf['view']);
			$this->initialized = true;
		}
		$classes = '';
		if($this->dayHasEvent[$weekdayIndex] == 1){
			$classes .= ' withEventsDay';
		}
		if($this->currentDayIndex == $weekdayIndex){
			$classes .= ' currentDayHeader';
		}
		$controller = &tx_cal_registry::Registry('basic','controller');
		$daysKeys = array_keys($this->days);
		if(intval($this->getParentMonth()) != intval($this->days[$daysKeys[$weekdayIndex]]->month)){
			$classes .= ' monthOff';
		}

		return $classes;
	}

	private function getDayLink($weekdayIndex){
		$conf = &tx_cal_registry::Registry('basic','conf');
		if($this->initialized === false){
			//initializing!!
			$this->getAlldaysMarker($template, $sims, $rems, $wrapped, $conf['view']);
			$this->initialized = true;
		}

		$daysKeys = array_keys($this->days);
		return $this->days[$daysKeys[$weekdayIndex]]->getDayLink($conf['view'],$this->days[$daysKeys[$weekdayIndex]]->time, $this->dayHasEvent[$this->days[$daysKeys[$weekdayIndex]]->weekdayNumber]);
	}

	public function setSelected(&$dateObject){
		if($dateObject->getWeekOfYear() == $this->week && $dateObject->year == $this->year){
			$this->selected = true;

			$day = $this->days[$dateObject->format('%Y%m%d')];
			if(is_object($day)){
				$day->setSelected($dateObject);
			}
		}
	}

	public function setCurrent(&$dateObject){
		if($dateObject->getWeekOfYear() == $this->week && $dateObject->year == $this->year){
			$this->current = true;

			$day = $this->days[$dateObject->format('%Y%m%d')];
			if(is_object($day)){
				$this->currentDayIndex = $dateObject->getDayOfWeek();
				if(DATE_CALC_BEGIN_WEEKDAY==1){
					$this->currentDayIndex--;
					if($this->currentDayIndex==-1){
						$this->currentDayIndex = 6;
					}
				}
				$day->setCurrent($dateObject);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_new_weekview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_new_weekview.php']);
}
?>