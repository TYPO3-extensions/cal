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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

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
	// and returns the date of that day. (ie: 'sun' or 'sunday' would be acceptable values of $day but not 'su')
	function dateOfWeek($date, $day, $weekStartDay='Sunday') {
		$unixtime = gmmktime(gmdate('H',$date),gmdate('i',$date),0,gmdate('m',$date),gmdate('d',$date),gmdate('y',$date));
		$num = gmdate('w', strtotime($weekStartDay)	+ strtotimeOffset($unixtime));
		//$start_day_time = strtotime(((gmdate('w',$date)==$num)==1 ? $weekStartDay : 'last '.$weekStartDay), $date);
		$temp_unixtime = $unixtime;
		$weekday = gmdate('w',$temp_unixtime);
		$i = 0;
		while($num != $weekday && i<7){
		        $temp_unixtime -= 86400;
		        $weekday = gmdate('w',$temp_unixtime);
		        $i++;
		}
		$start_day_time = $temp_unixtime;
		//$start_day_time	+= strtotimeOffset($start_day_time);
		$ret_unixtime = strtotime($day,$start_day_time);
		$ret_unixtime += strtotimeOffset($ret_unixtime);
		// Fix for 992744
		// $ret_unixtime = strtotime('+12 hours', $ret_unixtime);
		$ret_unixtime += (12 * 60 * 60);
		$ret = gmdate('Ymd',$ret_unixtime);
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
		$now = gmdate('Ymd',$now);
		$then = gmdate('Ymd',$then);
		$date_now = array();
		$date_then = array();
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $now, $date_now);
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $then, $date_then);
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
		$now = date('Ymd',$now);
		$then = date('Ymd',$then);
		$date_now = array();
		$date_then = array();
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $now, $date_now);
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $then, $date_then);
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

	function getYear($date){
		$day_array2 = array();
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $date, $day_array2);
		return $day_array2[1];
	}
	
	function getMonth($date) {
		$day_array2 = array();
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $date, $day_array2);
		return $day_array2[2];
	}
	
	function getDay($date) {
		$day_array2 = array();
		ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $date, $day_array2);
		return $day_array2[3];
	}
	
	function calculateStartDayTime($timestamp=''){
		return gmmktime(0, 0, 0, gmdate('m',$timestamp), gmdate('d',$timestamp), gmdate('Y',$timestamp));
	}
	
	function calculateEndDayTime($timestamp=''){
		return gmmktime(0, 0, 0, gmdate('m',$timestamp), gmdate('d',$timestamp)+1, gmdate('Y',$timestamp));
	}
	
	function calculateStartWeekTime($timestamp='', $weekStartDay=''){
		if($weekStartDay == 'Monday') {
			$dayOffset = 1;
		} else {
			$dayOffset = 0;
		}
		
		//return gmmktime(0, 0, 0, gmdate('m',$timestamp), gmdate('d',$timestamp)-gmdate('w',$timestamp)-6, gmdate('Y',$timestamp));
		return gmmktime(0, 0, 0, gmdate('m',$timestamp), gmdate('d',$timestamp)-gmdate('w',$timestamp)+$dayOffset, gmdate('Y',$timestamp));
	}
	
	function calculateEndWeekTime($timestamp='', $weekStartDay=''){
		if($weekStartDay == 'Monday') {
			$dayOffset = 1;
		} else {
			$dayOffset = 0;
		}
		
		return gmmktime(0, 0, 0, gmdate('m',$timestamp), gmdate('d',$timestamp)-gmdate('w',$timestamp)+7+$dayOffset, gmdate('Y',$timestamp));
	}
	
	function calculateStartMonthTime($timestamp=''){
		return gmmktime(0, 0, 0, gmdate('m',$timestamp), 1, gmdate('Y',$timestamp));
	}
	
	function calculateEndMonthTime($timestamp=''){
		return gmmktime(0, 0, 0, gmdate('m',$timestamp)+1,1, gmdate('Y',$timestamp));
	}
	
	function calculateStartYearTime($timestamp=''){
		return gmmktime(0, 0, 0, 1, 1, gmdate('Y',$timestamp));
	}
	
	function calculateEndYearTime($timestamp=''){
		return gmmktime(0, 0, 0, 12, 31, gmdate('Y',$timestamp));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_calendar.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_calendar.php']);
}

?>