<?php
namespace TYPO3\CMS\Cal\Model;
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

use \TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use \TYPO3\CMS\Cal\Utility\Registry;
/**
 * Extends the PEAR date class and adds a compareTo method.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class CalDate extends \TYPO3\CMS\Cal\Model\Pear\Date {
	/**
	 * define the default monthname abbreviation length
	 * used by ::format()
	 * 
	 * @var int
	 */
	private $getMonthAbbrnameLength = 3;
	private $conf;
	
	// @override constructor
	public function __construct($date = null) {
		if (class_exists (Registry)) {
			if (is_object ($GLOBALS ['TSFE'])) {
				if (! is_array ($GLOBALS ['TSFE']->register ['cal_shared_conf'])) {
					$GLOBALS ['TSFE']->register ['cal_shared_conf'] = &Registry::Registry ('basic', 'conf');
				}
				$this->conf = &$GLOBALS ['TSFE']->register ['cal_shared_conf'];
				$this->cObj = &$GLOBALS ['TSFE']->cObj;
			} else {
				$this->conf = &Registry::Registry ('basic', 'conf');
				$this->cObj = &Registry::Registry ('basic', 'cobj');
			}
		}
		parent::date ($date);
	}
	
	/**
	 * Compare function.
	 * 
	 * @return int => less, equals, greater
	 */
	public function compareTo($object) {
		if (is_subclass_of ($object, 'TYPO3\CMS\Cal\Model\Pear\Date')) {
			return $this->compare ($this, $object);
		}
		return - 1;
	}
	
	// @override
	public function equals($compareDate) {
		$a = doubleval ($compareDate->format ('%Y%m%d%H%M%S'));
		$b = doubleval ($this->format ('%Y%m%d%H%M%S'));
		if ($a == $b) {
			return true;
		}
		return false;
	}
	
	// @override
	public function before($compareDate) {
		$a = doubleval ($compareDate->format ('%Y%m%d%H%M%S'));
		$b = doubleval ($this->format ('%Y%m%d%H%M%S'));
		if ($a > $b) {
			return true;
		}
		return false;
	}
	
	// @override
	public function after($compareDate) {
		$a = doubleval ($compareDate->format ('%Y%m%d%H%M%S'));
		$b = doubleval ($this->format ('%Y%m%d%H%M%S'));
		if ($a < $b) {
			return true;
		}
		return false;
	}
	
	// @override
	public function compare($compareDateA, $compareDateB) {
		$a = doubleval ($compareDateA->format ('%Y%m%d%H%M%S'));
		$b = doubleval ($compareDateB->format ('%Y%m%d%H%M%S'));
		if ($a == $b) {
			return 0;
		}
		if ($a < $b) {
			return - 1;
		}
		return 1;
	}
	
	// @override
	public function subtractSeconds($seconds = 0) {
		if ($seconds != 0) {
			parent::subtractSeconds ($seconds);
		}
	}
	
	// @override
	public function addSeconds($seconds = 0) {
		if ($seconds != 0) {
			parent::addSeconds ($seconds);
		}
	}
	
	/**
	 * Date pretty printing, similar to strftime()
	 *
	 * Formats the date in the given format, much like
	 * strftime(). Most strftime() options are supported.<br><br>
	 *
	 * Formatting options:<br><br>
	 *
	 * <code>%a </code> abbreviated weekday name (Sun, Mon, Tue) <br>
	 * <code>%A </code> full weekday name (Sunday, Monday, Tuesday) <br>
	 * <code>%b </code> abbreviated month name (Jan, Feb, Mar) <br>
	 * <code>%B </code> full month name (January, February, March) <br>
	 * <code>%C </code> century number (the year divided by 100 and truncated
	 * to an integer, range 00 to 99) <br>
	 * <code>%d </code> day of month (range 00 to 31) <br>
	 * <code>%D </code> equivalent to "%m/%d/%y" <br>
	 * <code>%e </code> day of month without leading noughts (range 0 to 31) <br>
	 * <code>%E </code> Julian day - no of days since Monday, 24th November,
	 * 4714 B.C. (in the proleptic Gregorian calendar) <br>
	 * <code>%g </code> like %G, but without the century <br>
	 * <code>%G </code> the 4-digit year corresponding to the ISO week
	 * number (see %V). This has the same format and value
	 * as %Y, except that if the ISO week number belongs
	 * to the previous or next year, that year is used
	 * instead. <br>
	 * <code>%h </code> hour as decimal number without leading noughts (0
	 * to 23) <br>
	 * <code>%H </code> hour as decimal number (00 to 23) <br>
	 * <code>%i </code> hour as decimal number on 12-hour clock without
	 * leading noughts (1 to 12) <br>
	 * <code>%I </code> hour as decimal number on 12-hour clock (01 to 12) <br>
	 * <code>%j </code> day of year (range 001 to 366) <br>
	 * <code>%m </code> month as decimal number (range 01 to 12) <br>
	 * <code>%M </code> minute as a decimal number (00 to 59) <br>
	 * <code>%n </code> newline character ("\n") <br>
	 * <code>%o </code> raw timezone offset expressed as '+/-HH:MM' <br>
	 * <code>%O </code> dst-corrected timezone offset expressed as '+/-HH:MM' <br>
	 * <code>%p </code> either 'am' or 'pm' depending on the time <br>
	 * <code>%P </code> either 'AM' or 'PM' depending on the time <br>
	 * <code>%r </code> time in am/pm notation; equivalent to "%I:%M:%S %p" <br>
	 * <code>%R </code> time in 24-hour notation; equivalent to "%H:%M" <br>
	 * <code>%s </code> seconds including the micro-time (the decimal
	 * representation less than one second to six decimal
	 * places<br>
	 * <code>%S </code> seconds as a decimal number (00 to 59) <br>
	 * <code>%t </code> tab character ("\t") <br>
	 * <code>%T </code> current time; equivalent to "%H:%M:%S" <br>
	 * <code>%u </code> day of week as decimal (1 to 7; where 1 = Monday) <br>
	 * <code>%U </code> week number of the current year as a decimal
	 * number, starting with the first Sunday as the first
	 * day of the first week (i.e. the first full week of
	 * the year, and the week that contains 7th January)
	 * (00 to 53) <br>
	 * <code>%V </code> the ISO 8601:1988 week number of the current year
	 * as a decimal number, range 01 to 53, where week 1
	 * is the first week that has at least 4 days in the
	 * current year, and with Monday as the first day of
	 * the week. (Use %G or %g for the year component
	 * that corresponds to the week number for the
	 * specified timestamp.)
	 * <code>%w </code> day of week as decimal (0 to 6; where 0 = Sunday) <br>
	 * <code>%W </code> week number of the current year as a decimal
	 * number, starting with the first Monday as the first
	 * day of the first week (i.e. the first full week of
	 * the year, and the week that contains 7th January)
	 * (00 to 53) <br>
	 * <code>%y </code> year as decimal (range 00 to 99) <br>
	 * <code>%Y </code> year as decimal including century (range 0000 to
	 * 9999) <br>
	 * <code>%Z </code> Abbreviated form of time zone name, e.g. 'GMT', or
	 * the abbreviation for Summer time if the date falls
	 * in Summer time, e.g. 'BST'. <br>
	 * <code>%% </code> literal '%' <br>
	 * <br>
	 *
	 * The following codes render a different output to that of 'strftime()':
	 *
	 * <code>%e</code> in 'strftime()' a single digit is preceded by a space
	 * <code>%h</code> in 'strftime()' is equivalent to '%b'
	 * <code>%U</code> '%U' and '%W' are different in 'strftime()' in that
	 * if week 1 does not start on 1st January, '00' is
	 * returned, whereas this function returns '53', that is,
	 * the week is counted as the last of the previous year.
	 * <code>%W</code>
	 *
	 * @param string $format
	 *        	the format string for returned date/time
	 *        	
	 * @return string date/time in given format
	 * @access public
	 */
	public function format($format) {
		$output = "";
		
		$hn_isoyear = null;
		$hn_isoweek = null;
		$hn_isoday = null;
		
		if ($format == '%Y%m%d') {
			return $this->year . sprintf ('%02d%02d', $this->month, $this->day);
		} else if ($format == '%Y%m%d%H%M%S') {
			return $this->year . sprintf ('%02d%02d%02d%02d%02d', $this->month, $this->day, $this->hour, $this->minute, $this->second);
		}
		
		for ($strpos = 0; $strpos < strlen ($format); $strpos ++) {
			$char = substr ($format, $strpos, 1);
			if ($char == "%") {
				$nextchar = substr ($format, $strpos + 1, 1);
				switch ($nextchar) {
					case "a" :
						$output .= self::getDayName (TRUE);
						break;
					case "A" :
						$output .= self::getDayName ();
						break;
					case "b" :
						$output .= self::getMonthName (TRUE);
						break;
					case "B" :
						$output .= self::getMonthName ();
						break;
					case "C" :
						$output .= sprintf ("%02d", intval ($this->year / 100));
						break;
					case "d" :
						$output .= sprintf ("%02d", $this->day);
						break;
					case "D" :
						$output .= sprintf ("%02d/%02d/%02d", $this->month, $this->day, $this->year);
						break;
					case "e" :
						$output .= $this->day * 1;
						break;
					case "E" :
						$output .= Calc::dateToDays ($this->day, $this->month, $this->year);
						break;
					case "g" :
						if (is_null ($hn_isoyear))
							list ($hn_isoyear, $hn_isoweek, $hn_isoday) = Calc::isoWeekDate ($this->day, $this->month, $this->year);
						
						$output .= sprintf ("%02d", $hn_isoyear % 100);
						break;
					case "G" :
						if (is_null ($hn_isoyear))
							list ($hn_isoyear, $hn_isoweek, $hn_isoday) = Calc::isoWeekDate ($this->day, $this->month, $this->year);
						
						$output .= sprintf ("%04d", $hn_isoyear);
						break;
					case 'h' :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= sprintf ("%d", $this->hour);
						break;
					case "H" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= sprintf ("%02d", $this->hour);
						break;
					case "i" :
					case "I" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$hour = $this->hour + 1 > 12 ? $this->hour - 12 : $this->hour;
						$output .= $hour == 0 ? 12 : ($nextchar == "i" ? $hour : sprintf ('%02d', $hour));
						break;
					case "j" :
						$output .= sprintf ("%03d", Calc::dayOfYear ($this->day, $this->month, $this->year));
						break;
					case "m" :
						$output .= sprintf ("%02d", $this->month);
						break;
					case "M" :
						$output .= sprintf ("%02d", $this->minute);
						break;
					case "n" :
						$output .= "\n";
						break;
					case "N" :
						$output .= $this->month;
						break;
					case "O" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$offms = $this->getTZOffset ();
						$direction = $offms >= 0 ? "+" : "-";
						$offmins = abs ($offms) / 1000 / 60;
						$hours = $offmins / 60;
						$minutes = $offmins % 60;
						
						$output .= sprintf ("%s%02d:%02d", $direction, $hours, $minutes);
						break;
					case "o" :
						$offms = $this->tz->getRawOffset ($this);
						$direction = $offms >= 0 ? "+" : "-";
						$offmins = abs ($offms) / 1000 / 60;
						$hours = $offmins / 60;
						$minutes = $offmins % 60;
						
						$output .= sprintf ("%s%02d:%02d", $direction, $hours, $minutes);
						break;
					case "p" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= $this->hour >= 12 ? "pm" : "am";
						break;
					case "P" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= $this->hour >= 12 ? "PM" : "AM";
						break;
					case "r" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$hour = $this->hour + 1 > 12 ? $this->hour - 12 : $this->hour;
						$output .= sprintf ("%02d:%02d:%02d %s", $hour == 0 ? 12 : $hour, $this->minute, $this->second, $this->hour >= 12 ? "PM" : "AM");
						break;
					case "R" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= sprintf ("%02d:%02d", $this->hour, $this->minute);
						break;
					case "s" :
						$output .= str_replace (',', '.', sprintf ("%09f", (float) ((float) $this->second + $this->partsecond)));
						break;
					case "S" :
						$output .= sprintf ("%02d", $this->second);
						break;
					case "t" :
						$output .= "\t";
						break;
					case "T" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= sprintf ("%02d:%02d:%02d", $this->hour, $this->minute, $this->second);
						break;
					case "u" :
						$hn_dayofweek = $this->getDayOfWeek ();
						$output .= $hn_dayofweek == 0 ? 7 : $hn_dayofweek;
						break;
					case "U" :
						$output .= Calc::weekOfYear ($this->day, $this->month, $this->year);
						break;
					case "V" :
						if (is_null ($hn_isoyear))
							list ($hn_isoyear, $hn_isoweek, $hn_isoday) = Calc::isoWeekDate ($this->day, $this->month, $this->year);
						
						$output .= $hn_isoweek;
						break;
					case "w" :
						$output .= $this->getDayOfWeek ();
						break;
					case 'y' :
						$output .= sprintf ('%0' . ($this->year < 0 ? '3' : '2') . 'd', $this->year % 100);
						break;
					case "Y" :
						$output .= sprintf ('%0' . ($this->year < 0 ? '5' : '4') . 'd', $this->year);
						break;
					case "Z" :
						if ($this->ob_invalidtime)
							return $this->_getErrorInvalidTime ();
						$output .= $this->getTZShortName ();
						break;
					case "%" :
						$output .= "%";
						break;
					case 'x' : // add English day suffix
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
					default :
						$output .= $char . $nextchar;
				}
				$strpos ++;
			} else {
				$output .= $char;
			}
		}
		return $this->applyStdWrap ($output);
	}
	
	// @override
	public function getDayName($abbr = false, $length = false) {
		$dayName = parent::getDayName ();
		if ($abbr) {
			if ($length === false) {
				$length = $this->getWeekdayAbbreviationLength ();
			}
			$dayName = self::crop ($dayName, $length);
		}
		return $this->applyStdWrap ($dayName);
	}
	
	// @override
	public function getMonthName($abbr = false, $length = false) {
		$monthName = Calc::getMonthFullname ($this->month);
		if ($abbr) {
			if ($length === false) {
				$length = $this->getMonthAbbreviationLength ();
			}
			$monthName = self::crop ($monthName, $length);
		}
		return $this->applyStdWrap ($monthName);
	}
	
	/**
	 * Returns the length that should be used for month name abbreviation
	 *
	 * @access public
	 * @return int
	 */
	public function getMonthAbbreviationLength() {
		if ($this->conf ['dateConfig.'] ['monthAbbreviationLength']) {
			return intval ($this->conf ['dateConfig.'] ['monthAbbreviationLength']);
		}
		return intval ($this->getMonthAbbrnameLength);
	}
	
	/**
	 * Returns the length that should be used for month name abbreviation
	 *
	 * @access public
	 * @return int
	 */
	public function getWeekdayAbbreviationLength() {
		if ($this->conf ['dateConfig.'] ['weekdayAbbreviationLength']) {
			return intval ($this->conf ['dateConfig.'] ['weekdayAbbreviationLength']);
		}
		return intval ($this->getWeekdayAbbrnameLength);
	}
	
	/**
	 * Applys the default date_stdWrap to the given string.
	 *
	 * @access public
	 * @param string $value
	 *        	string that should be processed
	 * @return processed string
	 */
	public function applyStdWrap($value = '') {
		// only apply if actually configured
		if (is_array ($this->conf ['date_stdWrap.']) && count ($this->conf ['date_stdWrap.']) && $value != '' && is_object ($this->cObj) && is_object ($GLOBALS ['TSFE'])) {
			$value = $this->cObj->stdWrap ($value, $this->conf ['date_stdWrap.']);
		}
		return $value;
	}
	
	// FIX for: getWeekOfYear doesn't recognize sunday as weekstartday.
	// @override
	public function getWeekOfYear() {
		if (DATE_CALC_BEGIN_WEEKDAY == 0 && $this->getDayOfWeek () == 0) {
			$this->addSeconds (86400);
			$week = parent::getWeekOfYear ();
			$this->subtractSeconds (86400);
			return $week;
		}
		return parent::getWeekOfYear ();
	}
	
	/**
	 * uses a bytesafe cropping function if possible in order to not destroy multibyte chars from strings (e.g.
	 * names in UTF-8)
	 * 
	 * @access public
	 * @param string $value
	 *        	The value to crop
	 * @param integer $length
	 *        	The length
	 * @return the cropped string
	 */
	public function crop($value = '', $length = FALSE) {
		if ($length === FALSE) {
			return $value;
		}
		if (TYPO3_MODE == 'FE') {
			return $GLOBALS ['TSFE']->csConvObj->substr ($GLOBALS ['TSFE']->renderCharset, $value, 0, $length);
		} else {
			return $GLOBALS ['LANG']->csConvObj->substr ($GLOBALS ['LANG']->charSet, $value, 0, $length);
		}
	}
}

?>