<?php

/***************************************************************
* Copyright notice
*
* (c) 2005 Mario Matzulla (mario(at)matzullas.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
require_once(t3lib_extMgm::extPath('cal').'static/timezones.php');

/**
 * This class combines all the time related functions
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_calendar {
			

	// takes iCalendar 2 day format and makes it into 3 characters
	// if $txt is true, it returns the 3 letters, otherwise it returns the
	// integer of that day; 0=Sun, 1=Mon, etc.
	function two2threeCharDays($day, $txt=true) {
		switch($day) {
			case 'SU': return ($txt ? 'sun' : '0');
			case 'MO': return ($txt ? 'mon' : '1');
			case 'TU': return ($txt ? 'tue' : '2');
			case 'WE': return ($txt ? 'wed' : '3');
			case 'TH': return ($txt ? 'thu' : '4');
			case 'FR': return ($txt ? 'fri' : '5');
			case 'SA': return ($txt ? 'sat' : '6');
		}
	}
	
	// dateOfWeek() takes a timestamp and a day of week in 3 letters or more
	// and returns the date of that day. (ie: "sun" or "sunday" would be acceptable values of $day but not "su")
	function dateOfWeek($date, $day, $weekStartDay) {
		if (!isset($weekStartDay)) $weekStartDay = 'Sunday';
		
		$num = date('w', strtotime($weekStartDay));
		$start_day_time = strtotime(((date('w',$date)==$num)==1 ? "$weekStartDay" : "last $weekStartDay"), $date);
		$ret_unixtime = strtotime($day,$start_day_time);
		// Fix for 992744
		// $ret_unixtime = strtotime('+12 hours', $ret_unixtime);
		$ret_unixtime += (12 * 60 * 60);
		$ret = date('Ymd',$ret_unixtime);
		return $ret;
	}
	
	// function to compare to dates in Ymd and return the number of weeks 
	// that differ between them. requires dateOfWeek()
	function weekCompare($now, $then, $weekStartDay) {
		$sun_now = tx_cal_calendar::dateOfWeek($now, $weekStartDay, $weekStartDay);
		$sun_then = tx_cal_calendar::dateOfWeek($then, $weekStartDay, $weekStartDay);
		$seconds_now = strtotime($sun_now);
		$seconds_then =  strtotime($sun_then);
		$diff_seconds = $seconds_now - $seconds_then;
		$diff_minutes = $diff_seconds/60;
		$diff_hours = $diff_minutes/60;
		$diff_days = round($diff_hours/24);
		$diff_weeks = $diff_days/7;
		return $diff_weeks;
	}
	
	// function to compare to dates in Ymd and return the number of days 
	// that differ between them.
	function dayCompare($now, $then) {
		$diff_seconds = $now - $then;
		$diff_minutes = $diff_seconds/60;
		$diff_hours = $diff_minutes/60;
		$diff_days = round($diff_hours/24);
		
		return $diff_days;
	}
	
	// function to compare to dates in Ymd and return the number of months 
	// that differ between them.
	function monthCompare($now, $then) {
		$now = date("Ymd",$now);
		$then = date("Ymd",$then);
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $now, $date_now);
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $then, $date_then);
		$diff_years = $date_now[1] - $date_then[1];
		$diff_months = $date_now[2] - $date_then[2];
		if ($date_now[2] < $date_then[2]) {
			$diff_years -= 1;
			$diff_months = ($diff_months + 12) % 12;
		}
		$diff_months = ($diff_years * 12) + $diff_months;
	
		return $diff_months;
	}
	
	function yearCompare($now, $then) {
		$now = date("Ymd",$now);
		$then = date("Ymd",$then);
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $now, $date_now);
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $then, $date_then);
		$diff_years = $date_now[1] - $date_then[1];
		return $diff_years;
	}
	
	function chooseOffset($time, $conf) {
		global $tz_array;
		$timezone = $conf['timezone'];
		if (!isset($timezone)) $timezone = '';
		switch ($timezone) {
			case '':
				$offset = 'none';
				break;
			case 'Same as Server':
				$offset = date('O', $time);
				break;
			default:
				if (is_array($tz_array) && array_key_exists($timezone, $tz_array)) {
					$dlst = date('I', $time);
					$offset = $tz_array[$timezone][$dlst];
				} else {
					$offset = '+0000';
				}
		}
		return $offset;
	}

	function getunixtime($date){
		return strtotime($date);	
	}
	
	function getYear($date){
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $date, $day_array2);
		return $day_array2[1];
	}
	
	function getMonth($date) {
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $date, $day_array2);
		return $day_array2[2];
	}
	
	function getDay($date) {
		ereg ("([0-9]{4})([0-9]{2})([0-9]{2})", $date, $day_array2);
		return $day_array2[3];
	}
	
	function calculateStartDayTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		$month = $time_array['mon'];
		$day = $time_array['mday'];
		return strtotime(date("Y-m-d",mktime(0, 0, 0, $month, $day, $year)));
	}
	
	function calculateEndDayTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		$month = $time_array['mon'];
		$day = $time_array['mday'];
		return strtotime(date("Y-m-d",mktime(0, 0, 0, $month, $day+1, $year)));
	}
	
	function calculateStartWeekTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		$month = $time_array['mon'];
		$day = $time_array['mday'];
		$weekday = $time_array['wday'];
		$x = 1;
   		return strtotime(date("Y-m-d", mktime(0, 0, 0, $month, $day-$weekday+$x, $year)));
	}
	
	function calculateEndWeekTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		$month = $time_array['mon'];
		$day = $time_array['mday'];
		$weekday = $time_array['wday'];
		$x = 1;
		return strtotime(date("Y-m-d", mktime(0, 0, 0, $month, $day-$weekday+$x+6, $year)));
	}
	
	function calculateStartMonthTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		$month = $time_array['mon'];
		return strtotime(date("Y-m-d",mktime(0, 0, 0, $month, 1, $year)));
	}
	
	function calculateEndMonthTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		$month = $time_array['mon'];
		return strtotime(date("Y-m-d",mktime(0, 0, 0, $month+1, +1, $year)));
	}
	
	function calculateStartYearTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		return strtotime(date("Y-m-d",mktime(0, 0, 0, 1, 1, $year)));
	}
	
	function calculateEndYearTime($timestamp=''){
		$time_array = getdate($timestamp);
		$year = $time_array['year'];
		return strtotime(date("Y-m-d",mktime(0, 0, 0, 12, 31, $year)));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_calendar.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_calendar.php']);
}

?>