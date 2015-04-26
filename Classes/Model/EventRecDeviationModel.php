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

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class EventRecDeviationModel extends EventModel {
	
	private $origStartDate;
	
	public function __construct($event, $row, $start, $end) {
		
		parent::__construct ($event->serviceKey);
		$deviationId = $row ['uid'];
		unset ($row ['uid']);
		unset ($row ['pid']);
		unset ($row ['parentid']);
		unset ($row ['tstamp']);
		unset ($row ['crdate']);
		unset ($row ['cruser_id']);
		unset ($row ['deleted']);
		unset ($row ['hidden']);
		unset ($row ['starttime']);
		unset ($row ['endtime']);
		// storing allday in a temp var, in case it is set from 1 to 0
		$allday = $row ['allday'];
		$row = array_merge ($event->row, array_filter ($row));
		$row ['allday'] = $allday;
		$row ['deviationId'] = $deviationId;
		$this->createEvent ($row, false);
		
		$this->setStart ($start);
		$this->setEnd ($end);
		
		$this->setAllday ($row ['allday']);
		$this->origStartDate = new  \TYPO3\CMS\Cal\Model\CalDate ($row ['orig_start_date']);
		$this->origStartDate->addSeconds ($row ['orig_start_time']);
	}
	function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view) {
		$eventStart = $this->origStartDate;
		if ($this->isAllday ()) {
			$sims ['###RRULE###'] = 'RECURRENCE-ID;VALUE=DATE:' . $eventStart->format ('%Y%m%d');
		} else if ($this->conf ['view.'] ['ics.'] ['timezoneId'] != '') {
			$sims ['###RRULE###'] = 'RECURRENCE-ID;TZID=' . $this->conf ['view.'] ['ics.'] ['timezoneId'] . ':' . $eventStart->format ('%Y%m%dT%H%M%S');
		} else {
			$offset = \TYPO3\CMS\Cal\Utility\Functions::strtotimeOffset ($eventStart->getTime ());
			$eventStart->subtractSeconds ($offset);
			$sims ['###RRULE###'] = 'RECURRENCE-ID:' . $eventStart->format ('%Y%m%dT%H%M%SZ');
			$eventStart->addSeconds ($offset);
		}
	}
	
	public function getDeviationId() {
		return $row ['deviationId'];
	}
	
	public function setDeviationId($deviationId) {
		$row ['deviationId'] = $deviationId;
	}
}

?>