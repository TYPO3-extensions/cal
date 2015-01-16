<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
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
 * *************************************************************
 */
require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('cal') . 'controller/class.tx_cal_functions.php');

/**
 * This class combines all the time related functions
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_calendar {
	
	// takes iCalendar 2 day format and makes it into 3 characters
	// if $txt is true, it returns the 3 letters, otherwise it returns the
	// integer of that day; 0=Sun, 1=Mon, etc.
	function two2threeCharDays($day, $txt = true) {
		switch ($day) {
			case 'SU' :
				return ($txt ? 'sun' : '0');
			case 'MO' :
				return ($txt ? 'mon' : '1');
			case 'TU' :
				return ($txt ? 'tue' : '2');
			case 'WE' :
				return ($txt ? 'wed' : '3');
			case 'TH' :
				return ($txt ? 'thu' : '4');
			case 'FR' :
				return ($txt ? 'fri' : '5');
			case 'SA' :
				return ($txt ? 'sat' : '6');
		}
	}
	function getYear($date) {
		$day_array2 = array ();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date, $day_array2);
		return $day_array2 [1];
	}
	function getMonth($date) {
		$day_array2 = array ();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date, $day_array2);
		return $day_array2 [2];
	}
	function getDay($date) {
		$day_array2 = array ();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date, $day_array2);
		return $day_array2 [3];
	}
	function calculateStartDayTime($dateObject = '') {
		$timeObj = new tx_cal_date ();
		$timeObj->setTZbyId ('UTC');
		if ($dateObject) {
			$timeObj->copy ($dateObject);
		}
		$timeObj->setHour (0);
		$timeObj->setMinute (0);
		$timeObj->setSecond (0);
		return $timeObj;
	}
	function calculateEndDayTime($dateObject = '') {
		$timeObj = new tx_cal_date ();
		$timeObj->setTZbyId ('UTC');
		if ($dateObject) {
			$timeObj->copy ($dateObject);
		}
		$timeObj->setHour (23);
		$timeObj->setMinute (59);
		$timeObj->setSecond (59);
		return $timeObj;
	}
	function calculateStartWeekTime($dateObject = '') {
		$timeObj = tx_cal_calendar::calculateStartDayTime ($dateObject);
		$timeObj = new tx_cal_date (Date_Calc::beginOfWeek ($timeObj->getDay (), $timeObj->getMonth (), $timeObj->getYear ()));
		$timeObj->setTZbyId ('UTC');
		return $timeObj;
	}
	function calculateEndWeekTime($dateObject = '') {
		$timeObj = tx_cal_calendar::calculateStartWeekTime ($dateObject);
		$timeObj->addSeconds (604799);
		return $timeObj;
	}
	function calculateStartMonthTime($dateObject = '') {
		$timeObj = tx_cal_calendar::calculateStartDayTime ($dateObject);
		$timeObj->setDay (1);
		return $timeObj;
	}
	function calculateEndMonthTime($dateObject = '') {
		$timeObj = tx_cal_calendar::calculateStartDayTime ($dateObject);
		$timeObj = new tx_cal_date (Date_Calc::endOfNextMonth ($timeObj->getDay (), $timeObj->getMonth (), $timeObj->getYear ()));
		$timeObj->setDay (1);
		$timeObj->subtractSeconds (1);
		$timeObj->setTZbyId ('UTC');
		return $timeObj;
	}
	function calculateStartYearTime($dateObject = '') {
		$timeObj = tx_cal_calendar::calculateStartMonthTime ($dateObject);
		$timeObj->setMonth (1);
		return $timeObj;
	}
	function calculateEndYearTime($dateObject = '') {
		$timeObj = tx_cal_calendar::calculateStartYearTime ($dateObject);
		$timeObj->setYear ($timeObj->getYear () + 1);
		$timeObj->subtractSeconds (1);
		return $timeObj;
	}
	function getHourFromTime($time) {
		$time = str_replace (':', '', $time);
		
		if ($time) {
			$retVal = substr ($time, 0, strlen ($time) - 2);
		}
		return $retVal;
	}
	function getMinutesFromTime($time) {
		$time = str_replace (':', '', $time);
		if ($time) {
			$retVal = substr ($time, - 2);
		}
		return $retVal;
	}
	function getTimeFromTimestamp($timestamp = 0) {
		if ($timestamp > 0) {
			// gmdate and gmmktime are ok, as long as the timestamp just holds information about 24h.
			return gmmktime (gmdate ('H', $timestamp), gmdate ('i', $timestamp), 0, 0, 0, 1) - gmmktime (0, 0, 0, 0, 0, 1);
		}
		return 0;
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/controller/class.tx_cal_calendar.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/controller/class.tx_cal_calendar.php']);
}

?>