<?php
/***************************************************************
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
 ***************************************************************/

require_once(t3lib_extMgm::extPath('cal').'res/pearLoader.php');
/**
 * Extends the PEAR date class and adds a compareTo method.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_date extends Date {
	/**
	 * define the default monthname abbreviation length
	 * used by ::format()
	 * @var int
	 */
	var $getMonthAbbrnameLength = 3;
	var $conf;

	# @override constructor
	function tx_cal_date($date = null) {
		if(class_exists(tx_cal_registry)) {
			if(is_object($GLOBALS['TSFE'])) {
				if(!is_array($GLOBALS['TSFE']->register['cal_shared_conf'])) {
					$GLOBALS['TSFE']->register['cal_shared_conf'] = &tx_cal_registry::Registry('basic','conf');
				}
				$this->conf = &$GLOBALS['TSFE']->register['cal_shared_conf'];
				$this->cObj = &$GLOBALS['TSFE']->cObj;
			} else {
				$this->conf = &tx_cal_registry::Registry('basic','conf');
				$this->cObj = &tx_cal_registry::Registry('basic','cobj');
			}
		}
		parent::date($date);
	}

	/**
	 * Compare function.
	 * @return int	-1,0,1 => less, equals, greater
	 */
	function compareTo($object){
		if(is_subclass_of($object, 'Date')){
			return $this->compare($this,$object);
		}
		return -1;
	}

	# @override
	function equals($compareDate){
		$a = doubleval($compareDate->format('%Y%m%d%H%M%S'));
		$b = doubleval($this->format('%Y%m%d%H%M%S'));
		if($a==$b){
			return true;
		}
		return false;
	}

	# @override
	function before($compareDate){
		$a = doubleval($compareDate->format('%Y%m%d%H%M%S'));
		$b = doubleval($this->format('%Y%m%d%H%M%S'));
		if($a>$b){
			return true;
		}
		return false;
	}

	# @override
	function after($compareDate){
		$a = doubleval($compareDate->format('%Y%m%d%H%M%S'));
		$b = doubleval($this->format('%Y%m%d%H%M%S'));
		if($a<$b){
			return true;
		}
		return false;
	}

	# @override
	function compare($compareDateA, $compareDateB){
		$a = doubleval($compareDateA->format('%Y%m%d%H%M%S'));
		$b = doubleval($compareDateB->format('%Y%m%d%H%M%S'));
		if($a==$b){
			return 0;
		}
		if($a<$b){
			return -1;
		}
		return 1;
	}

	# @override
	function subtractSeconds($seconds=0){
		if($seconds!=0){
			return parent::subtractSeconds($seconds);
		}
	}

	# @override
	function addSeconds($seconds=0){
		if($seconds!=0){
			return parent::addSeconds($seconds);
		}
	}

	/**
	 *  Date pretty printing, similar to strftime()
	 *
	 *  Formats the date in the given format, much like
	 *  strftime().  Most strftime() options are supported.<br><br>
	 *
	 *  formatting options:<br><br>
	 *
	 *  <code>%a  </code>  abbreviated weekday name (Sun, Mon, Tue) <br>
	 *  <code>%A  </code>  full weekday name (Sunday, Monday, Tuesday) <br>
	 *  <code>%b  </code>  abbreviated month name (Jan, Feb, Mar) <br>
	 *  <code>%B  </code>  full month name (January, February, March) <br>
	 *  <code>%C  </code>  century number (the year divided by 100 and truncated to an integer, range 00 to 99) <br>
	 *  <code>%d  </code>  day of month (range 00 to 31) <br>
	 *  <code>%D  </code>  same as "%m/%d/%y" <br>
	 *  <code>%e  </code>  day of month, single digit (range 0 to 31) <br>
	 *  <code>%E  </code>  number of days since unspecified epoch (integer, Date_Calc::dateToDays()) <br>
	 *  <code>%H  </code>  hour as decimal number (00 to 23) <br>
	 *  <code>%I  </code>  hour as decimal number on 12-hour clock (01 to 12) <br>
	 *  <code>%j  </code>  day of year (range 001 to 366) <br>
	 *  <code>%m  </code>  month as decimal number (range 01 to 12) <br>
	 *  <code>%M  </code>  minute as a decimal number (00 to 59) <br>
	 *  <code>%n  </code>  newline character (\n) <br>
	 *  <code>%O  </code>  dst-corrected timezone offset expressed as "+/-HH:MM" <br>
	 *  <code>%o  </code>  raw timezone offset expressed as "+/-HH:MM" <br>
	 *  <code>%p  </code>  either 'am' or 'pm' depending on the time <br>
	 *  <code>%P  </code>  either 'AM' or 'PM' depending on the time <br>
	 *  <code>%r  </code>  time in am/pm notation, same as "%I:%M:%S %p" <br>
	 *  <code>%R  </code>  time in 24-hour notation, same as "%H:%M" <br>
	 *  <code>%s  </code>  seconds including the decimal representation smaller than one second <br>
	 *  <code>%S  </code>  seconds as a decimal number (00 to 59) <br>
	 *  <code>%t  </code>  tab character (\t) <br>
	 *  <code>%T  </code>  current time, same as "%H:%M:%S" <br>
	 *  <code>%w  </code>  weekday as decimal (0 = Sunday) <br>
	 *  <code>%U  </code>  week number of current year, first sunday as first week <br>
	 *  <code>%y  </code>  year as decimal (range 00 to 99) <br>
	 *  <code>%Y  </code>  year as decimal including century (range 0000 to 9999) <br>
	 *  <code>%%  </code>  literal '%' <br>
	 * <br>
	 *
	 * @access public
	 * @param string format the format string for returned date/time
	 * @return string date/time in given format
	 * @override
	 */
	function format($format)
	{
		$output = '';
		$strlen = strlen($format);
		for ($strpos = 0; $strpos < $strlen; ++$strpos) {
			$char = $format[$strpos];
			if ($char == '%') {
				$nextchar = $format[$strpos + 1];
				switch ($nextchar) {
					case 'a':
						$output .= Date_Calc::getWeekdayAbbrname($this->day,$this->month,$this->year, $this->getWeekdayAbbreviationLength());
						break;
					case 'A':
						$output .= Date_Calc::getWeekdayFullname($this->day,$this->month,$this->year);
						break;
					case 'b':
						$output .= Date_Calc::getMonthAbbrname($this->month,$this->getMonthAbbreviationLength());
						break;
					case 'B':
						$output .= Date_Calc::getMonthFullname($this->month);
						break;
					case 'C':
						$output .= sprintf('%02d',intval($this->year/100));
						break;
					case 'd':
						$output .= sprintf('%02d',$this->day);
						break;
					case 'D':
						$output .= sprintf("%02d/%02d/%02d",$this->month,$this->day,$this->year);
						break;
					case 'e':
						$output .= $this->day * 1; // get rid of leading zero
						break;
					case 'E':
						$output .= Date_Calc::dateToDays($this->day,$this->month,$this->year);
						break;
					case 'H':
						$output .= sprintf('%02d', $this->hour);
						break;
					case 'h':
						$output .= sprintf('%d', $this->hour);
						break;
					case 'I':
						$hour = ($this->hour + 1) > 12 ? $this->hour - 12 : $this->hour;
						$output .= sprintf('%02d', $hour==0 ? 12 : $hour);
						break;
					case 'i':
						$hour = ($this->hour + 1) > 12 ? $this->hour - 12 : $this->hour;
						$output .= sprintf('%d', $hour==0 ? 12 : $hour);
						break;
					case 'j':
						$output .= Date_Calc::julianDate($this->day,$this->month,$this->year);
						break;
					case 'm':
						$output .= sprintf('%02d', $this->month);
						break;
					case 'M':
						$output .= sprintf('%02d', $this->minute);
						break;
					case 'n':
						$output .= "\n";
						break;
					case 'N':
						$output .= $this->month;
						break;
					case 'O':
						$offms = $this->tz->getOffset($this);
						$direction = $offms >= 0 ? '+' : '-';
						$offmins = abs($offms) / 1000 / 60;
						$hours = $offmins / 60;
						$minutes = $offmins % 60;
						$output .= sprintf('%s%02d:%02d', $direction, $hours, $minutes);
						break;
					case 'o':
						$offms = $this->tz->getRawOffset($this);
						$direction = $offms >= 0 ? '+' : '-';
						$offmins = abs($offms) / 1000 / 60;
						$hours = $offmins / 60;
						$minutes = $offmins % 60;
						$output .= sprintf('%s%02d:%02d', $direction, $hours, $minutes);
						break;
					case 'p':
						$output .= $this->hour >= 12 ? 'pm' : 'am';
						break;
					case 'P':
						$output .= $this->hour >= 12 ? 'PM' : 'AM';
						break;
					case 'r':
						$hour = ($this->hour + 1) > 12 ? $this->hour - 12 : $this->hour;
						$output .= sprintf('%02d:%02d:%02d %s', $hour==0 ?  12 : $hour, $this->minute, $this->second, $this->hour >= 12 ? "PM" : "AM");
						break;
					case 'R':
						$output .= sprintf('%02d:%02d', $this->hour, $this->minute);
						break;
					case 's':
						$output .= str_replace(',', '.', sprintf('%09f', (float)((float)$this->second + $this->partsecond)));
						break;
					case 'S':
						$output .= sprintf('%02d', $this->second);
						break;
					case 't':
						$output .= "\t";
						break;
					case 'T':
						$output .= sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
						break;
					case 'w':
						$output .= Date_Calc::dayOfWeek($this->day,$this->month,$this->year);
						break;
					case 'U':
						$output .= Date_Calc::weekOfYear($this->day,$this->month,$this->year);
						break;
					case 'y':
						$output .= substr($this->year,2,2);
						break;
					case 'Y':
						$output .= $this->year;
						break;
					case 'Z':
						$output .= $this->tz->inDaylightTime($this) ? $this->tz->getDSTShortName() : $this->tz->getShortName();
						break;
					case '%':
						$output .= "%";
						break;
					case 'x':    // add English day suffix
						if (1 == $this->day) {
							$output .= 'st';
						} else if (2 == $this->day) {
							$output .= 'nd';
						} else if (3 == $this->day) {
							$output .= 'rd';
						} else {
							$output .= 'th';
						}
						break;
					default:
						$output .= $char.$nextchar;
				}
				++$strpos;
			} else {
				$output .= $char;
			}
		}
		return $this->applyStdWrap($output);
	}

	# @override
	function getDayName($abbr = false, $length = false)
	{
		if($length === false) {
			$length = $this->getWeekdayAbbreviationLength();
		}
		$dayName = parent::getDayName($abbr,$length);
		return $this->applyStdWrap($dayName);
	}

	# @override
	function getMonthName($abbr = false,$length = false)
	{
		if($length === false) {
			$length = $this->getMonthAbbreviationLength();
		}
		if ($abbr) {
			$monthName = Date_Calc::getMonthAbbrname($this->month,$length);
		} else {
			$monthName = Date_Calc::getMonthFullname($this->month);
		}
		return $this->applyStdWrap($monthName);
	}

	/**
	 * Returns the length that should be used for month name abbreviation
	 *
	 * @access public
	 * @return int
	 */
	function getMonthAbbreviationLength() {
		if($this->conf['dateConfig.']['monthAbbreviationLength']) {
			return intval($this->conf['dateConfig.']['monthAbbreviationLength']);
		}
		return intval($this->getMonthAbbrnameLength);
	}

	/**
	 * Returns the length that should be used for month name abbreviation
	 *
	 * @access public
	 * @return int
	 */
	function getWeekdayAbbreviationLength() {
		if($this->conf['dateConfig.']['weekdayAbbreviationLength']) {
			return intval($this->conf['dateConfig.']['weekdayAbbreviationLength']);
		}
		return intval($this->getWeekdayAbbrnameLength);
	}

	/**
	 * Applys the default date_stdWrap to the given string.
	 *
	 * @access public
	 * @param string $value string that should be processed
	 * @return processed string
	 */
	function applyStdWrap($value='') {
		// only apply if actually configured
		if(is_array($this->conf['date_stdWrap.']) && count($this->conf['date_stdWrap.']) && $value!='' && is_object($this->cObj)) {
			$value = $this->cObj->stdWrap($value,$this->conf['date_stdWrap.']);
		}
		return $value;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_date.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_date.php']);
}
?>