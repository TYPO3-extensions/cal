<?php
/**
 * Class representing vFreebusy components.
 *
 * $Horde: framework/iCalendar/iCalendar/vfreebusy.php,v 1.16.10.9 2006/02/06 17:53:39 mrubinsk Exp $
 *
 * Copyright 2003-2006 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you did
 * not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Mike Cochrane <mike@graftonhall.co.nz>
 * @since Horde 3.0
 * @package Horde_iCalendar
 */
class tx_iCalendar_vfreebusy extends tx_model_iCalendar {
	var $_busyPeriods = array ();
	function getType() {
		return 'vFreebusy';
	}
	
	/**
	 * Parses a string containing vFreebusy data.
	 *
	 * @param string $data
	 *        	The data to parse.
	 */
	function parsevCalendar($data) {
		parent::parsevCalendar ($data, 'VFREEBUSY');
		
		// Do something with all the busy periods.
		foreach ($this->_attributes as $key => $attribute) {
			if ($attribute ['name'] == 'FREEBUSY') {
				foreach ($attribute ['values'] as $value) {
					if (isset ($value ['duration'])) {
						$this->addBusyPeriod ('BUSY', $value ['start'], null, $value ['duration']);
					} else {
						$this->addBusyPeriod ('BUSY', $value ['start'], $value ['end']);
					}
				}
				unset ($this->_attributes [$key]);
			}
		}
	}
	function exportvCalendar() {
		foreach ($this->_busyPeriods as $start => $end) {
			$periods = array (
					array (
							'start' => $start,
							'end' => $end 
					) 
			);
			$this->setAttribute ('FREEBUSY', $periods);
		}
		
		$res = parent::_exportvData ('VFREEBUSY');
		
		foreach ($this->_attributes as $key => $attribute) {
			if ($attribute ['name'] == 'FREEBUSY') {
				unset ($this->_attributes [$key]);
			}
		}
		
		return $res;
	}
	
	/**
	 * Returns a display name for this object.
	 *
	 * @return string A clear text name for displaying this object.
	 */
	function getName() {
		$name = '';
		$method = ! empty ($this->_container) ? $this->_container->getAttribute ('METHOD') : 'PUBLISH';
		
		if (is_a ($method, 'PEAR_Error') || $method == 'PUBLISH') {
			$attr = 'ORGANIZER';
		} elseif ($method == 'REPLY') {
			$attr = 'ATTENDEE';
		}
		
		$name = $this->getAttribute ($attr, true);
		if (isset ($name [0] ['CN'])) {
			return $name [0] ['CN'];
		}
		
		$name = $this->getAttribute ($attr);
		if (is_a ($name, 'PEAR_Error')) {
			return '';
		} else {
			$name = parse_url ($name);
			return $name ['path'];
		}
	}
	
	/**
	 * Returns the email address for this object.
	 *
	 * @return string The email address of this object's owner.
	 */
	function getEmail() {
		$name = '';
		$method = ! empty ($this->_container) ? $this->_container->getAttribute ('METHOD') : 'PUBLISH';
		
		if (is_a ($method, 'PEAR_Error') || $method == 'PUBLISH') {
			$attr = 'ORGANIZER';
		} elseif ($method == 'REPLY') {
			$attr = 'ATTENDEE';
		}
		
		$name = $this->getAttribute ($attr);
		if (is_a ($name, 'PEAR_Error')) {
			return '';
		} else {
			$name = parse_url ($name);
			return $name ['path'];
		}
	}
	function getBusyPeriods() {
		return $this->_busyPeriods;
	}
	
	/**
	 * Returns all the free periods of time in a given period.
	 *
	 * @param integer $startStamp
	 *        	The start timestamp.
	 * @param integer $endStamp
	 *        	The end timestamp.
	 *        	
	 * @return array A hash with free time periods, the start times as the
	 *         keys and the end times as the values.
	 */
	function getFreePeriods($startStamp, $endStamp) {
		$this->simplify ();
		$periods = array ();
		
		// Check that we have data for some part of this period.
		if ($this->getEnd () < $startStamp || $this->getStart () > $endStamp) {
			return $periods;
		}
		
		// Locate the first time in the requested period we have data for.
		$nextstart = max ($startStamp, $this->getStart ());
		
		// Check each busy period and add free periods in between.
		foreach ($this->_busyPeriods as $start => $end) {
			if ($start <= $endStamp && $end >= $nextstart) {
				if ($nextstart <= $start) {
					$periods [$nextstart] = min ($start, $endStamp);
				}
				$nextstart = min ($end, $endStamp);
			}
		}
		
		// If we didn't read the end of the requested period but still have
		// data then mark as free to the end of the period or available data.
		if ($nextstart < $endStamp && $nextstart < $this->getEnd ()) {
			$periods [$nextstart] = min ($this->getEnd (), $endStamp);
		}
		
		return $periods;
	}
	
	/**
	 * Adds a busy period to the info.
	 *
	 * @param string $type
	 *        	The type of the period. Either 'FREE' or
	 *        	'BUSY'; only 'BUSY' supported at the moment.
	 * @param integer $start
	 *        	The start timestamp of the period.
	 * @param integer $end
	 *        	The end timestamp of the period.
	 * @param integer $duration
	 *        	The duration of the period. If specified, the
	 *        	$end parameter will be ignored.
	 */
	function addBusyPeriod($type, $start, $end = null, $duration = null) {
		if ($type == 'FREE') {
			// Make sure this period is not marked as busy.
			return false;
		}
		
		// Calculate the end time if duration was specified.
		$tempEnd = is_null ($duration) ? $end : $start + $duration;
		
		// Make sure the period length is always positive.
		$end = max ($start, $tempEnd);
		$start = min ($start, $tempEnd);
		
		if (isset ($this->_busyPeriods [$start])) {
			// Already a period starting at this time. Extend to the length of
			// the longest of the two.
			$this->_busyPeriods [$start] = max ($end, $this->_busyPeriods [$start]);
		} else {
			// Add a new busy period.
			$this->_busyPeriods [$start] = $end;
		}
		
		return true;
	}
	
	/**
	 * Returns the timestamp of the start of the time period this free busy
	 * information covers.
	 *
	 * @return integer A timestamp.
	 */
	function getStart() {
		if (! is_a ($this->getAttribute ('DTSTART'), 'PEAR_Error')) {
			return $this->getAttribute ('DTSTART');
		} elseif (count ($this->_busyPeriods)) {
			return min (array_keys ($this->_busyPeriods));
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the timestamp of the end of the time period this free busy
	 * information covers.
	 *
	 * @return integer A timestamp.
	 */
	function getEnd() {
		if (! is_a ($this->getAttribute ('DTEND'), 'PEAR_Error')) {
			return $this->getAttribute ('DTEND');
		} elseif (count ($this->_busyPeriods)) {
			return max (array_values ($this->_busyPeriods));
		} else {
			return false;
		}
	}
	
	/**
	 * Merges the busy periods of another Horde_iCalendar_vfreebusy object into
	 * this one.
	 *
	 * @param Horde_iCalendar_vfreebusy $freebusy
	 *        	A freebusy object.
	 * @param boolean $simplify
	 *        	If true, simplify() will
	 *        	called after the merge.
	 */
	function merge($freebusy, $simplify = true) {
		if (! is_a ($freebusy, 'Horde_iCalendar_vfreebusy')) {
			return false;
		}
		
		foreach ($freebusy->getBusyPeriods () as $start => $end) {
			$this->addBusyPeriod ('BUSY', $start, $end);
		}
		
		$thisattr = $this->getAttribute ('DTSTART');
		$thatattr = $freebusy->getAttribute ('DTSTART');
		if (is_a ($thisattr, 'PEAR_Error') && ! is_a ($thatattr, 'PEAR_Error')) {
			$this->setAttribute ('DTSTART', $thatattr);
		} elseif (! is_a ($thatattr, 'PEAR_Error')) {
			if ($thatattr > $thisattr) {
				$this->setAttribute ('DTSTART', $thatattr);
			}
		}
		
		$thisattr = $this->getAttribute ('DTEND');
		$thatattr = $freebusy->getAttribute ('DTEND');
		if (is_a ($thisattr, 'PEAR_Error') && ! is_a ($thatattr, 'PEAR_Error')) {
			$this->setAttribute ('DTEND', $thatattr);
		} elseif (! is_a ($thatattr, 'PEAR_Error')) {
			if ($thatattr < $thisattr) {
				$this->setAttribute ('DTEND', $thatattr);
			}
		}
		
		if ($simplify) {
			$this->simplify ();
		}
		
		return true;
	}
	
	/**
	 * Removes all overlaps and simplifies the busy periods array as much as
	 * possible.
	 */
	function simplify() {
		$checked = array ();
		$checkedEmpty = true;
		
		foreach ($this->_busyPeriods as $start => $end) {
			if ($checkedEmpty) {
				$checked [$start] = $end;
				$checkedEmpty = false;
			} else {
				$added = false;
				foreach ($checked as $testStart => $testEnd) {
					if ($start == $testStart) {
						$checked [$testStart] = max ($testEnd, $end);
						$added = true;
					} elseif ($end <= $testEnd && $end >= $testStart) {
						unset ($checked [$testStart]);
						$checked [min ($testStart, $start)] = max ($testEnd, $end);
						$added = true;
					}
					if ($added) {
						break;
					}
				}
				if (! $added) {
					$checked [$start] = $end;
				}
			}
		}
		
		ksort ($checked, SORT_NUMERIC);
		$this->_busyPeriods = $checked;
	}
}
if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/iCalendar/class.tx_iCalendar_vfreebusy.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/iCalendar/class.tx_iCalendar_vfreebusy.php']);
}
?>