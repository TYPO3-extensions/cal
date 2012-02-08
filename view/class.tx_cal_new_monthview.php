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

require_once (t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm::extPath('cal').'model/class.tx_cal_date.php');
require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_timeview.php');
require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_weekview.php');

/**
 * Base model for the day.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_new_monthview extends tx_cal_new_timeview {
	
	var $month;
	var $year;
	var $weeks;
	
	var $maxWeeksInYear = 52;
	var $monthStartWeekdayNum;
	var $monthLength;
	
	/**
	 *  Constructor.
	 */
	public function tx_cal_new_monthview($month, $year){
		$this->tx_cal_new_timeview();
		$this->mySubpart = 'MONTH_SUBPART';
		$this->month = intval($month);
		$this->year = intval($year);
		$this->generateWeeks();
		$controller = &tx_cal_registry::Registry('basic','controller');
		$controller->cache->set($month.'_'.$year,serialize($this),'month',60*60*24*365*100);
	}
	
	public static function getMonth($month, $year){
		$controller = &tx_cal_registry::Registry('basic','controller');
		$cache = $controller->cache->get($month.'_'.$year);
		if($cache != ''){
			$return = unserialize($cache);
			if($return === FALSE){
debug('could not unserialize cache for month:'.$month.'_'.$year);
			}
			return $return;
		} else {
			return new tx_cal_new_monthview($month, $year);
		}
	}
	
	private function generateWeeks(){
		$date = new tx_cal_date();
		$date->setDay(1);
		$date->setMonth($this->month);
		$date->setYear($this->year);
		$this->monthStartWeekdayNum = $date->format('%w');
		$this->monthLength = $date->getDaysInMonth();
		$monthEnd = tx_cal_calendar::calculateEndMonthTime($date);

		$weekEnd = $monthEnd->getWeekOfYear();
		$newDate = tx_cal_calendar::calculateStartWeekTime($date);

		$this->weeks = Array();
		$weekNumber = $newDate->getWeekOfYear();

		if($this->month == 12 && $weekEnd == 1){
			do {
				if($weekNumber == $weekEnd){
					$this->weeks[($newDate->getYear() + 1).'_'.$weekNumber] = new tx_cal_new_weekview($weekNumber, $newDate->getYear() + 1, $this->month);
				} else {
					$this->weeks[$newDate->getYear().'_'.$weekNumber] = new tx_cal_new_weekview($weekNumber, $newDate->getYear(), $this->month);
				}
				$newDate->addSeconds(86400 * 7);
				$weekNumber = $newDate->getWeekOfYear();
				$weekNumberTmp = $weekNumber;
				if($weekNumber != $weekEnd){
					$weekNumberTmp = 0;
				}
			} while($weekNumberTmp <= $weekEnd && $newDate->year == $this->year);
		} else if($this->month == 1){
			do {
				if($weekNumber > 6){
					$this->weeks[$newDate->getYear().'_'.$weekNumber] = new tx_cal_new_weekview($weekNumber, $newDate->getYear(), $this->month);
				} else {
					$this->weeks[$this->year.'_'.$weekNumber] = new tx_cal_new_weekview($weekNumber, $this->year, $this->month);
				}
				$newDate->addSeconds(86400 * 7);
				$weekNumber = $newDate->getWeekOfYear();
			} while($weekNumber <= $weekEnd && $newDate->year == $this->year);
		} else {
			do {
				$this->weeks[$this->year.'_'.$weekNumber] = new tx_cal_new_weekview($weekNumber, $newDate->getYear(), $this->month);
				$newDate->addSeconds(86400 * 7);
				$weekNumber = $newDate->getWeekOfYear();
			} while($weekNumber <= $weekEnd && $newDate->year == $this->year);
		}
		$this->maxWeeksInYear = max($this->maxWeeksInYear,$weekNumber);
	}
	
	public function addEvent(&$event){
		
		$eventStartWeek = $event->getStart()->getWeekOfYear();
		$eventEndWeek = $event->getEnd()->getWeekOfYear();
		$eventStartYear = $event->getStart()->year;
		$eventEndYear = $event->getEnd()->year;
		if(($eventStartWeek == 52 || $eventStartWeek == 53) && $event->getStart()->month == 1){
			$eventStartYear--;
		}
		if(($eventEndWeek == 52 || $eventEndWeek == 53) && $event->getEnd()->month == 1){
			$eventEndYear--;
		}
		if($eventStartWeek == 1 && $event->getStart()->month == 12){
			$eventStartYear++;
		}
		if($eventEndWeek == 1 && $event->getEnd()->month == 12){
			$eventEndYear++;
		}
		do{
			if($this->weeks[$eventStartYear.'_'.$eventStartWeek]) {
				$this->weeks[$eventStartYear.'_'.$eventStartWeek]->addEvent($event);
			}
			$eventStartWeek++;
			if($eventStartWeek > $this->maxWeeksInYear){
				$eventStartWeek = 1;
				$eventStartYear++;
			}
		}while(!(($eventStartYear == $eventEndYear && $eventStartWeek > $eventEndWeek) || ($eventStartYear > $eventEndYear)));
	}
	
	public function getWeeksMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$content = '';
		foreach($this->weeks as $week){
			$content .= $week->render($this->template);
		}
		$sims['###WEEKS###'] = $content;
	}
	
	public function getWeekdaysMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->mySubpart = 'MONTH_WEEKDAYS_SUBPART';
		if(DATE_CALC_BEGIN_WEEKDAY == 0){
			$this->mySubpart = 'SUNDAY_MONTH_WEEKDAYS_SUBPART';
		}
		$controller = &tx_cal_registry::Registry('basic','controller');
		$cache = $controller->cache->get($this->weekDayLength.'_'.$this->mySubpart);
		if($cache != ''){
			$sims['###WEEKDAYS###'] = $cache;
		} else {
			$sims['###WEEKDAYS###'] = $this->render($this->template);
			$controller->cache->set($this->weekDayLength.'_'.$this->mySubpart,$sims['###WEEKDAYS###'],'month',60*60*24*365*100);
		}
		$this->mySubpart = 'MONTH_SUBPART';
	}
	
	public function setSelected(&$dateObject){
		if($dateObject->year == $this->year && $dateObject->month == $this->month){
			$this->selected = true;
		
			$week = $this->weeks[$dateObject->year.'_'.$dateObject->getWeekOfYear()];
			if(is_object($week)){
				$week->setSelected($dateObject);
			}
		}
	}
	
	public function setCurrent(&$dateObject){
		if($dateObject->year == $this->year && $dateObject->month == $this->month){
			$this->current = true;
		
			$week = $this->weeks[$dateObject->year.'_'.$dateObject->getWeekOfYear()];
			if(is_object($week)){
				$week->setCurrent($dateObject);
			}
		}
	}
	
	function getMonthTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$current_month = new tx_cal_date();
		$current_month->setMonth($this->month);
		$current_month->setYear($this->year);
		$conf = &tx_cal_registry::Registry('basic','conf');
		$sims['###MONTH_TITLE###'] = $current_month->format($conf['view.'][$view.'.']['dateFormatMonth']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_new_monthview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_new_monthview.php']);
}
?>