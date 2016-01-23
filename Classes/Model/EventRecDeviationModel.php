<?php
namespace TYPO3\CMS\Cal\Model;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class EventRecDeviationModel extends EventModel {
	
	private $origStartDate;

	public function __construct($event, $row, $start, $end) {
		Model::__construct ($event->serviceKey);
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
		
		$this->setCategories($event->getCategories());
		$this->setSharedGroups($event->getSharedGroups());
		$this->setSharedUsers($event->getSharedUsers());
	}
	
	public function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view) {
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
		return $this->row ['deviationId'];
	}
	
	public function setDeviationId($deviationId) {
		$this->row ['deviationId'] = $deviationId;
	}
}

?>