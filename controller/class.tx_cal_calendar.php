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
	
	// localizeDate() - similar to strftime but uses our preset arrays of localized
	// months and week days and only supports %A, %a, %B, %b, %e, and %Y
	// more can be added as needed but trying to keep it small while we can
	function localizeDate($format, $timestamp, $daysofweek_lang = array(), $daysofweekshort_lang = array(), $daysofweekreallyshort_lang = array(), $monthsofyear_lang = array(), $monthsofyearshort_lang = array()) {
		$year = date("Y", $timestamp);
		$month = date("n", $timestamp)-1;
		$day = date("j", $timestamp);
		$dayofweek = date("w", $timestamp);
		$hour = date("g", $timestamp);
		$hour2 = date("H", $timestamp);
		$minute = date("i", $timestamp);
		
		$date = str_replace('%Y', $year, $format);
		$date = str_replace('%e', $day, $date);
		$date = str_replace('%B', $monthsofyear_lang[$month], $date);
		$date = str_replace('%b', $monthsofyearshort_lang[$month], $date);
		$date = str_replace('%A', $daysofweek_lang[$dayofweek], $date);
		$date = str_replace('%a', $daysofweekshort_lang[$dayofweek], $date);
		$date = str_replace('%g', $hour, $date);
		$date = str_replace('%H', $hour2, $date);
		$date = str_replace('%i', $minute, $date);
		
		return $date;	
		
	}
	// calcOffset takes an offset (ie, -0500) and returns it in the number of seconds
	function calcOffset($offset_str) {
		$sign = substr($offset_str, 0, 1);
		$hours = substr($offset_str, 1, 2);
		$mins = substr($offset_str, 3, 2);
		$secs = ((int)$hours * 3600) + ((int)$mins * 60);
		if ($sign == '-') $secs = 0 - $secs;
		return $secs;
	}
	
	// calcTime calculates the unixtime of a new offset by comparing it to the current offset
	// $have is the current offset (ie, '-0500')
	// $want is the wanted offset (ie, '-0700')
	// $time is the unixtime relative to $have
	function calcTime($have, $want, $time) {
		if ($have == 'none' || $want == 'none') return $time;
		$have_secs = calcOffset($have);
		$want_secs = calcOffset($want);
		$diff = $want_secs - $have_secs;
		$time += $diff;
		return $time;
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
	
	function openevent($event_date, $time, $uid, $arr, $lines = 0, $length = 0, $link_class = '', $pre_text = '', $post_text = '') {
		global $cpath;
		$event_text = stripslashes(urldecode($arr["event_text"]));
		if (empty($start)) {
			$title = $event_text;
		} else {
			$title = $arr['event_start'].' - '.$arr['event_end'].': '.$event_text;
		}
		# for iCal pseudo tag <http> comptability
		if (ereg("<([[:alpha:]]+://)([^<>[:space:]]+)>",$event_text,$matches)) {
			$full_event_text = $matches[1] . $matches[2];
			$event_text = $matches[2];
		} else {
			$full_event_text = $event_text;
			$event_text = strip_tags($event_text, '<b><i><u>');
		}
	
		if (!empty($event_text)) {
			if ($lines > 0) {
				$event_text = word_wrap($event_text, $length, $lines);
			}
	
			if ((!(ereg("([[:alpha:]]+://[^<>[:space:]]+)", $full_event_text, $res))) || ($arr['description'])) {
				$escaped_date = addslashes($event_date);
				$escaped_time = addslashes($time);
				$escaped_uid = addslashes($uid);
				// fix for URL-length bug in IE: populate and submit a hidden form on click
				static $popup_data_index = 0;
	$return = "
	    <script language=\"Javascript\" type=\"text/javascript\"><!--
	    var eventData = new EventData('$escaped_date', '$escaped_time', '$escaped_uid','$cpath');
	    document.popup_data[$popup_data_index] = eventData;
	    // --></script>";
	
				$return .= '<a class="'.$link_class.'" title="'.$title.'" href="#" onclick="openEventWindow('.$popup_data_index.'); return false;">';
				$popup_data_index++;
			} else {
				$return .= '<a class="'.$link_class.'" title="'.$title.'" href="'.$res[1].'">';
			}
			$return .= $pre_text.$event_text.$post_text.'</a>'."\n";
		}
	
		return $return;
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
	
	// Returns an array of the date and time extracted from the data
	// passed in. This array contains (unixtime, date, time, allday).
	//
	// $data		= A string representing a date-time per RFC2445.
	// $property	= The property being examined, e.g. DTSTART, DTEND.
	// $field		= The full field being examined, e.g. DTSTART;TZID=US/Pacific
	function extractDateTime($data, $property, $field, $tz_array) {
		
		// Initialize values.
		unset($unixtime, $date, $time, $allday);
			
		// Check for zulu time.
		$zulu_time = false;
		if (substr($data,-1) == 'Z') $zulu_time = true;
		$data = str_replace('Z', '', $data);
		
		// Remove some substrings we don't want to look at.
		$data = str_replace('T', '', $data);
		$field = str_replace(';VALUE=DATE-TIME', '', $field); 
		
		// Extract date-only values.
		if ((preg_match('/^'.$property.';VALUE=DATE/i', $field)) || (ereg ('^([0-9]{4})([0-9]{2})([0-9]{2})$', $data)))  {
			// Pull out the date value. Minimum year is 1970.
			ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $data, $dt_check);
			if ($dt_check[1] < 1970) { 
				$data = '1971'.$dt_check[2].$dt_check[3];
			}
			
			// Set the values.
			$unixtime = strtotime($data);
			$date = date('Ymd', $unixtime);
			$allday = $data;
		}
		
		// Extract date-time values.
		else {
			// Pull out the timezone, or use GMT if zulu time was indicated.
			if (preg_match('/^'.$property.';TZID=/i', $field)) {
				$tz_tmp = explode('=', $field);
				$tz_dt = $tz_tmp[1];
				unset($tz_tmp);
			} elseif ($zulu_time) {
				$tz_dt = 'GMT';
			}
	
			// Pull out the date and time values. Minimum year is 1970.
			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{0,2})([0-9]{0,2})/', $data, $regs);
			if ($regs[1] < 1970) { 
				$regs[1] = '1971';
			}
			$date = $regs[1] . $regs[2] . $regs[3];
			$time = $regs[4] . $regs[5];
			$unixtime = mktime($regs[4], $regs[5], 0, $regs[2], $regs[3], $regs[1]);
	
			// Check for daylight savings time.
			$dlst = date('I', $unixtime);
			$server_offset_tmp = chooseOffset($unixtime);
			if (isset($tz_dt)) {
				if (array_key_exists($tz_dt, $tz_array)) {
					$offset_tmp = $tz_array[$tz_dt][$dlst];
				} else {
					$offset_tmp = '+0000';
				}
			} elseif (isset($calendar_tz)) {
				if (array_key_exists($calendar_tz, $tz_array)) {
					$offset_tmp = $tz_array[$calendar_tz][$dlst];
				} else {
					$offset_tmp = '+0000';
				}
			} else {
				$offset_tmp = $server_offset_tmp;
			}
			
			// Set the values.
			$unixtime = calcTime($offset_tmp, $server_offset_tmp, $unixtime);
			$date = date('Ymd', $unixtime);
			$time = date('Hi', $unixtime);
		}
		
		// Return the results.
		return array($unixtime, $date, $time, $allday);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_calendar.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_calendar.php']);
}

?>