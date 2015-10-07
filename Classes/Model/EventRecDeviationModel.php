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

	protected $row;
	
	public function __construct($event, $row, $start, $end) {
		$this->row = $row;
		parent::__construct ($event->serviceKey);
		$deviationId = $this->row ['uid'];
		unset ($this->row ['uid']);
		unset ($this->row ['pid']);
		unset ($this->row ['parentid']);
		unset ($this->row ['tstamp']);
		unset ($this->row ['crdate']);
		unset ($this->row ['cruser_id']);
		unset ($this->row ['deleted']);
		unset ($this->row ['hidden']);
		unset ($this->row ['starttime']);
		unset ($this->row ['endtime']);
		// storing allday in a temp var, in case it is set from 1 to 0
		$allday = $this->row ['allday'];
		$this->row = array_merge ($event->row, array_filter ($this->row));
		$this->row ['allday'] = $allday;
		$this->row ['deviationId'] = $deviationId;
		$this->createEvent ($this->row, false);
		
		$this->setStart ($start);
		$this->setEnd ($end);
		
		$this->setAllday ($this->row ['allday']);
		$this->origStartDate = new  \TYPO3\CMS\Cal\Model\CalDate ($this->row ['orig_start_date']);
		$this->origStartDate->addSeconds ($this->row ['orig_start_time']);
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
		return $this->row ['deviationId'];
	}
	
	public function setDeviationId($deviationId) {
		$this->row ['deviationId'] = $deviationId;
	}
}

?>