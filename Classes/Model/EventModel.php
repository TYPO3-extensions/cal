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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class EventModel extends \TYPO3\CMS\Cal\Model\Model {
	var $location;
	var $isException;
	var $createUserId;
	var $isPreview = false;
	var $isTomorrow = false;
	var $teaser;
	var $limitAttendeeToThisEmail = '';
	var $timezone = 'UTC';
	var $cachedValueArray = Array ();
	var $sendOutInvitation = false;
	var $markerCache = Array ();
	
	public function __construct($row, $isException, $serviceKey) {
		parent::__construct($serviceKey);
		
		if (is_array ($row)) {
			$this->createEvent ($row, $isException);
		}
		
		$this->isException = $isException;
		$this->setType ($serviceKey);
		$this->setObjectType ('event');
	}
	public function updateWithPIVars(&$piVars) {
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelController');
		$cObj = &$this->controller->cObj;
		$startDateIsSet = false;
		$endDateIsSet = false;
		
		$customFieldArray = Array ();
		if ($this->conf ['view'] == 'create_event' || $this->conf ['view'] == 'edit_event') {
			$customFieldArray = GeneralUtility::trimExplode (',', $this->conf ['rights.'] [$this->conf ['view'] == 'create_event' ? 'create.' : 'edit.'] ['event.'] ['additionalFields'], 1);
		} else if ($this->conf ['view'] == 'confirm_event') {
			if ($this->row ['uid'] > 0) {
				$customFieldArray = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['edit.'] ['event.'] ['additionalFields'], 1);
			} else {
				$customFieldArray = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['create.'] ['event.'] ['additionalFields'], 1);
			}
		}
		
		if ($piVars ['formCheck'] == '1') {
			$this->setAllday (false);
		}
		
		foreach ($piVars as $key => $value) {
			switch ($key) {
				case 'hidden' :
					$this->setHidden ($value);
					unset ($piVars ['hidden']);
					break;
				case 'calendar_id' :
					$this->setCalendarUid (intval ($piVars ['calendar_id']));
					unset ($piVars ['calendar_id']);
					break;
				case 'category' :
				case 'category_ids' :
					$this->setCategories (Array ());
					$categories = Array ();
					$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
					$categoryService = &$modelObj->getServiceObjByKey ('cal_category_model', 'category', $confArr ['categoryService']);
					$categoryService->getCategoryArray ($this->conf ['pidList'], $categories);
					$piVarsCaregoryArray = explode (',', \TYPO3\CMS\Cal\Controller\Controller::convertLinkVarArrayToList ($piVars [$key]));
					if (! empty ($piVarsCaregoryArray)) {
						foreach ($piVarsCaregoryArray as $categoryId) {
							$this->addCategory ($categories [0] [0] [$categoryId]);
						}
					}
					unset ($piVars ['category']);
					unset ($piVars ['category_ids']);
					break;
				case 'allday_checkbox' :
				case 'allday' :
					if (intval ($piVars [$key]) == 1) {
						$this->setAllday (true);
					} else if (strlen ($piVars [$key]) > 0) {
						$this->setAllday (false);
					}
					break;
				case 'start_date' :
				case 'start_time' :
					$start = new \TYPO3\CMS\Cal\Model\CalDate ($piVars ['start_date'] . '000000');
					$start->addSeconds ($piVars ['start_time']);
					$this->setStart ($start);
					unset ($piVars ['start_date']);
					unset ($piVars ['start_time']);
					break;
				case 'startdate' :
				case 'starttime' :
				case 'startminutes' :
					$start = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getYmdFromDateString ($this->conf, strip_tags ($piVars ['startdate'] ? $piVars ['startdate'] : $piVars ['getdate'])) . '000000');
					if (strlen ($piVars ['starttime']) == 4) {
						$tempArray = Array ();
						preg_match ('/([0-9]{2})([0-9]{2})/', $piVars ['starttime'], $tempArray);
						$start->setHour (intval ($tempArray [1]));
						$start->setMinute (intval ($tempArray [2]));
					} else {
						$start->setHour (intval ($piVars ['starttime']));
						$start->setMinute (intval ($piVars ['startminutes']));
					}
					$start->setSecond (0);
					$start->setTZbyId ('UTC');
					$this->setStart ($start);
					unset ($piVars ['startdate']);
					unset ($piVars ['starttime']);
					unset ($piVars ['startminutes']);
					$startDateIsSet = true;
					break;
				case 'end_date' :
				case 'end_time' :
					$end = new \TYPO3\CMS\Cal\Model\CalDate ($piVars ['end_date'] . '000000');
					$end->addSeconds ($piVars ['end_time']);
					$this->setEnd ($end);
					unset ($piVars ['end_date']);
					unset ($piVars ['end_time']);
					break;
				case 'enddate' :
				case 'endtime' :
				case 'endminutes' :
					$end = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getYmdFromDateString ($this->conf, strip_tags ($piVars ['enddate'] ? $piVars ['enddate'] : $piVars ['getdate'])) . '000000');
					if (strlen ($piVars ['endtime']) == 4) {
						$tempArray = Array ();
						preg_match ('/([0-9]{2})([0-9]{2})/', $piVars ['endtime'], $tempArray);
						$end->setHour (intval ($tempArray [1]));
						$end->setMinute (intval ($tempArray [2]));
					} else {
						$end->setHour (intval ($piVars ['endtime']));
						$end->setMinute (intval ($piVars ['endminutes']));
					}
					$end->setSecond (0);
					$end->setTzById ('UTC');
					$this->setEnd ($end);
					unset ($piVars ['enddate']);
					unset ($piVars ['endtime']);
					unset ($piVars ['endminutes']);
					$endDateIsSet = true;
					break;
				case 'organizer' :
					$this->setOrganizer (strip_tags ($piVars ['organizer']));
					unset ($piVars ['organizer']);
					break;
				case 'location' :
					$this->setLocation (strip_tags ($piVars ['location']));
					unset ($piVars ['location']);
					break;
				case 'cal_organizer' :
					$this->setOrganizerId (intval ($piVars ['cal_organizer']));
					unset ($piVars ['cal_organizer']);
					break;
				case 'cal_location' :
					$this->setLocationId (intval ($piVars ['cal_location']));
					unset ($piVars ['cal_location']);
					break;
				case 'title' :
					$this->setTitle (strip_tags ($piVars ['title']));
					unset ($piVars ['title']);
					break;
				case 'description' :
					$this->setDescription ($cObj->removeBadHTML ($piVars ['description'], array ()));
					unset ($piVars ['description']);
					unset ($piVars ['_TRANSFORM_description']);
					break;
				case 'teaser' :
					$this->setTeaser ($cObj->removeBadHTML ($piVars ['teaser'], array ()));
					unset ($piVars ['teaser']);
					break;
				case 'image' :
					if (is_array ($piVars ['image'])) {
						foreach ($piVars ['image'] as $image) {
							$this->addImage (strip_tags ($image));
						}
					}
					break;
				case 'attachment' :
					$this->setAttachment (array ());
					if (is_array ($piVars ['attachment'])) {
						foreach ($piVars ['attachment'] as $attachment) {
							$this->addAttachment (strip_tags ($attachment));
						}
					}
					break;
				case 'frequency_id' :
					$valueArray = array (
							'none',
							'day',
							'week',
							'month',
							'year' 
					);
					$this->setFreq (in_array ($piVars ['frequency_id'], $valueArray) ? $piVars ['frequency_id'] : 'none');
					unset ($piVars ['frequency_id']);
					break;
				case 'by_day' :
					if (is_array ($piVars ['by_day'])) {
						$this->setByDay (strtolower (strip_tags (implode (',', $piVars ['by_day']))));
					} else {
						$this->setByDay (strtolower (strip_tags ($piVars ['by_day'])));
					}
					unset ($piVars ['by_day']);
					break;
				case 'by_monthday' :
					$this->setByMonthDay (strtolower (strip_tags ($piVars ['by_monthday'])));
					unset ($piVars ['by_monthday']);
					break;
				case 'by_month' :
					$this->setByMonth (strtolower (strip_tags ($piVars ['by_month'])));
					unset ($piVars ['by_month']);
					break;
				case 'until' :
					if ($piVars ['until'] != 0) {
						$until = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getYmdFromDateString ($this->conf, strip_tags ($piVars ['until'])) . '000000');
					} else {
						$until = new \TYPO3\CMS\Cal\Model\CalDate ('00000000000000');
					}
					$until->setTzById ('UTC');
					$this->setUntil ($until);
					unset ($piVars ['until']);
					break;
				case 'count' :
					$this->setCount (intval ($piVars ['count']));
					unset ($piVars ['count']);
					break;
				case 'interval' :
					$this->setInterval (intval ($piVars ['interval']));
					unset ($piVars ['interval']);
					break;
				case 'rdate' :
					$this->setRdate (strtolower (strip_tags ($piVars ['rdate'])));
					unset ($piVars ['rdate']);
					break;
				case 'rdate_type' :
					$this->setRdateType (strtolower (strip_tags ($piVars ['rdate_type'])));
					unset ($piVars ['rdate_type']);
					break;
				case 'exception_ids' :
					$this->setExceptionSingleIds (array ());
					$this->setExceptionGroupIds (array ());
					foreach (GeneralUtility::trimExplode (',', $piVars ['exception_ids'], 1) as $valueInner) {
						preg_match ('/(^[a-z])_([0-9]+)/', $valueInner, $idname);
						if ($idname [1] == 'u') {
							$this->addExceptionSingleId ($idname [2]);
						} else if ($idname [1] == 'g') {
							$this->addExceptionGroupId ($idname [2]);
						}
					}
					break;
				case 'shared' :
				case 'shared_ids' :
					$this->setSharedGroups (array ());
					$this->setSharedUsers (array ());
					$values = $piVars [$key];
					if (! is_array ($piVars [$key])) {
						$values = GeneralUtility::trimExplode (',', $piVars [$key], 1);
					}
					foreach ($values as $entry) {
						preg_match ('/(^[a-z])_([0-9]+)/', $entry, $idname);
						if ($idname [1] == 'u') {
							$this->addSharedUser ($idname [2]);
						} else if ($idname [1] == 'g') {
							$this->addSharedGroup ($idname [2]);
						}
					}
					break;
				case 'event_type' :
					$this->setEventType (intval ($piVars ['event_type']));
					unset ($piVars ['event_type']);
					break;
				case 'attendee' :
					$attendeeIndex = Array ();
					$attendeeServices = $this->getAttendees ();
					$emptyAttendeeArray = Array ();
					$this->setAttendees ($emptyAttendeeArray);
					$attendeeServiceKeys = array_keys ($attendeeServices);
					foreach ($attendeeServiceKeys as $serviceKey) {
						$attendeeKeys = array_keys ($attendeeServices [$serviceKey]);
						foreach ($attendeeKeys as $attendeeKey) {
							$attendeeIndex [$serviceKey . '_' . ($attendeeServices [$serviceKey] [$attendeeKey]->getFeUserId () ? $attendeeServices [$serviceKey] [$attendeeKey]->getFeUserId () : $attendeeServices [$serviceKey] [$attendeeKey]->getEmail ())] = &$attendeeServices [$serviceKey] [$attendeeKey];
						}
					}
					$servKey = 'tx_cal_attendee';
					$newAttendeeArray = Array (
							$servKey => Array () 
					);
					
					$values = $piVars [$key];
					if (! is_array ($piVars [$key])) {
						$values = GeneralUtility::trimExplode (',', $piVars [$key], 1);
					}
					$attendance = $piVars ['attendance'];
					if (! is_array ($piVars ['attendance'])) {
						$attendanceTemp = GeneralUtility::trimExplode (',', $piVars ['attendance'], 1);
						$attendance = Array ();
						$i = 0;
						foreach ($values as $entry) {
							$attendance [$entry] = $attendanceTemp [$i];
							$i ++;
						}
					}
					foreach ($values as $entry) {
						preg_match ('/(^[emailu]+)_([^*]+)/', $entry, $idname);
						
						if (is_object ($attendeeIndex [$serviceKey . '_' . $idname [2]])) {
							// Attendee has been already assigned -> updating attendance
							$attendeeIndex [$serviceKey . '_' . $idname [2]]->setAttendance ($attendance [$entry]);
							$this->addAttendee ($attendeeIndex [$serviceKey . '_' . $idname [2]]);
						} else {
							$initRow = Array ();
							$attendee = new \TYPO3\CMS\Cal\Model\AttendeeModel($initRow, 'cal_attendee_model');
							if ($idname [1] == 'u') {
								$attendee->setFeUserId ($idname [2]);
								$attendee->setAttendance ($attendance [$entry]);
							} else if ($idname [1] == 'email') {
								$attendee->setEmail ($idname [2]);
								$attendee->setAttendance ('OPT-PARTICIPANT');
							}
							$this->addAttendee ($attendee);
						}
					}
					foreach (GeneralUtility::trimExplode (',', $piVars ['attendee_external'], 1) as $emailAddress) {
						if (is_object ($attendeeIndex [$serviceKey . '_' . $emailAddress])) {
							// Attendee has been already assigned -> updating attendance
							$attendeeIndex [$serviceKey . '_' . $emailAddress]->setAttendance ($attendance [$entry]);
							$this->addAttendee ($attendeeIndex [$serviceKey . '_' . $emailAddress]);
						} else {
							$initRow = Array ();
							$attendee = new \TYPO3\CMS\Cal\Model\AttendeeModel($initRow, 'cal_attendee_model');
							$attendee->setEmail ($emailAddress);
							$attendee->setAttendance ('OPT-PARTICIPANT');
							$this->addAttendee ($attendee);
						}
					}
					unset ($piVars ['attendee']);
					unset ($piVars ['attendance']);
					break;
				case 'sendout_invitation' :
					$this->setSendoutInvitation ($piVars ['sendout_invitation'] == 1);
					unset ($piVars ['sendout_invitation']);
					break;
				default :
					if (in_array ($key, $customFieldArray)) {
						$this->row [$key] = $value;
					}
			}
		}
		
		if ($this->getEventType () != \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_MEETING) {
			$newAttendeeArray = Array ();
			$this->setAttendees ($newAttendeeArray);
		}
		
		if ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['dynamicStarttimeOffset']) {
			$now = new \TYPO3\CMS\Cal\Model\CalDate ();
			
			$now->addSeconds (intval ($this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['dynamicStarttimeOffset']));
			
			if (is_object ($start)) {
				$start->setHour ($now->getHour ());
				$start->setMinute ($now->getMinute ());
				$this->setStart ($start);
			}
		}
		
		if (! $startDateIsSet && $piVars ['mygetdate']) {
			$startDay = strip_tags ($piVars ['mygetdate']);
			$startHour = '00';
			$startMinutes = '00';
			if ($piVars ['gettime']) {
				$startHour = substr (strip_tags ($piVars ['gettime']), 0, 2);
				$startMinutes = substr (strip_tags ($piVars ['gettime']), 2, 2);
			}
			
			$start = new \TYPO3\CMS\Cal\Model\CalDate ($startDay . ' ' . $startHour . ':' . $startMinutes . ':00');
			$start->setTzById ('UTC');
			$this->setStart ($start);
		}
		if (! $endDateIsSet && $piVars ['mygetdate']) {
			$end = new \TYPO3\CMS\Cal\Model\CalDate ();
			$end->copy ($start);
			$end->addSeconds ($this->conf ['view.'] ['event.'] ['event.'] ['defaultEventLength']);
			$this->setEnd ($end);
		}
	}
	function createEvent(&$row, $isException) {
		$this->row = &$row;
		$this->setType ($this->serviceKey);
		$this->setUid ($row ['uid']);
		$this->setPid ($row ['pid']);
		$this->setCreationDate ($row ['crdate']);
		$this->setCreateUserId ($row ['cruser_id']);
		$this->setHidden ($row ['hidden']);
		$this->setTstamp ($row ['tstamp']);
		
		$this->setCalendarUid ($row ['calendar_id']);
		
		$this->setTimezone ($row ['timezone']);
		
		if ($row ['allday']) {
			$row ['start_time'] = 0;
			$row ['end_time'] = 0;
		} else if ($row ['start_time'] == 0 && $row ['end_time'] == 0) {
			$row ['allday'] = 1;
		}
		$tempDate = new \TYPO3\CMS\Cal\Model\CalDate ($row ['start_date'] . '000000');
		$tempDate->setTZbyId ('UTC');
		$tempDate->addSeconds ($row ['start_time']);
		$this->setStart ($tempDate);
		$tempDate = new \TYPO3\CMS\Cal\Model\CalDate ($row ['end_date'] . '000000');
		$tempDate->setTZbyId ('UTC');
		$tempDate->addSeconds ($row ['end_time']);
		$this->setEnd ($tempDate);
		
		$this->setAllday ($row ['allday']);
		$eventStart = $this->getStart ();
		$eventEnd = $this->getEnd ();
		if (! $this->isAllday () && $eventStart->equals ($this->getEnd ()) || $eventStart->after ($this->getEnd ())) {
			$tempDate = new \TYPO3\CMS\Cal\Model\CalDate ($row ['start_date']);
			$tempDate->setTZbyId ('UTC');
			$tempDate->addSeconds ($row ['start_time'] + $this->conf ['view.'] ['event.'] ['event.'] ['defaultEventLength']);
			$this->setEnd ($tempDate);
		}
		
		if ($this->isAllday ()) {
			$eventEnd->addSeconds (86399);
			$this->setEnd ($eventEnd);
		}
		
		$this->setTitle ($row ['title']);
		$this->setCategories ($row ['categories']);
		
		$this->setFreq ($row ['freq']);
		$this->setByDay ($row ['byday']);
		$this->setByMonthDay ($row ['bymonthday']);
		$this->setByMonth ($row ['bymonth']);
		
		$tempDate = new \TYPO3\CMS\Cal\Model\CalDate ($row ['until'] . '000000');
		$tempDate->setTZbyId ('UTC');
		$this->setUntil ($tempDate);
		
		$cnt = $row ['cnt'];
		if ($row ['rdate']) {
			$cnt += count (explode (',', $row ['rdate']));
		}
		$this->setCount ($cnt);
		
		$this->setInterval ($row ['intrval']);
		
		$this->setRdateType ($row ['rdate_type']);
		$this->setRdate ($row ['rdate']);
		
		/* new */
		$this->setEventType ($row ['type']);
		
		if ($row ['type'] == 3) { // meeting
			$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
			$this->setAttendees ($modelObj->findEventAttendees ($this->getUid ()));
		}
		
		$this->setPage ($row ['page']);
		$this->setExtUrl ($row ['ext_url']);
		/* new */
		
		$this->setImage (GeneralUtility::trimExplode (',', $row ['image'], 1));
		
		if ($row ['attachment']) {
			$fileArr = explode (',', $row ['attachment']);
			while (list (, $val) = each ($fileArr)) {
				// fills the marker ###FILE_LINK### with the links to the attached files
				$this->addAttachment ($val);
			}
		}
		
		if ($row ['exception_single_ids']) {
			$ids = explode (',', $row ['exception_single_ids']);
			foreach ($ids as $id) {
				$this->addExceptionSingleId ($id);
			}
		}
		if ($row ['exceptionGroupIds']) {
			$ids = explode (',', $row ['exceptionGroupIds']);
			foreach ($ids as $id) {
				$this->addExceptionGroupId ($id);
			}
		}
		if ($row ['calendar_headerstyle'] != '') {
			$this->setHeaderStyle ($row ['calendar_headerstyle']);
		}
		
		if ($row ['calendar_bodystyle'] != '') {
			$this->setBodyStyle ($row ['calendar_bodystyle']);
		}
		
		$this->setEventOwner ($row ['event_owner']);
		
		if (! $isException) {
			$this->setTeaser ($row ['teaser']);
			$this->setDescription ($row ['description']);
			
			$this->setLocationId ($row ['location_id']);
			$this->setLocation ($row ['location']);
			$this->setLocationPage ($row ['location_pid']);
			$this->setLocationLinkUrl ($row ['location_link']);
			
			$this->setOrganizerId ($row ['organizer_id']);
			$this->setOrganizer ($row ['organizer']);
			$this->setOrganizerPage ($row ['organizer_pid']);
			$this->setOrganizerLinkUrl ($row ['organizer_link']);
		}
		
		$this->sharedUsers = Array ();
		$this->sharedGroups = Array ();
		$table = 'tx_cal_event_shared_user_mm';
		$select = 'uid_foreign,tablenames';
		$where = 'uid_local = ' . $this->getUid ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($result) {
			while ($row1 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if ($row1 ['tablenames'] == 'fe_users') {
					$this->addSharedUser ($row1 ['uid_foreign']);
				} else if ($row1 ['tablenames'] == 'fe_groups') {
					$this->addSharedGroup ($row1 ['uid_foreign']);
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		
		$this->notifyUserIds = Array ();
		$this->notifyGroupIds = Array ();
		$table = 'tx_cal_fe_user_event_monitor_mm';
		$select = 'uid_foreign,tablenames,offset';
		$where = 'uid_local = ' . $this->getUid ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($result) {
			while ($row1 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if ($row1 ['tablenames'] == 'fe_users') {
					$this->addNotifyUser ($row1 ['uid_foreign'] . '|' . $row1 ['offset']);
				} else if ($row1 ['tablenames'] == 'fe_groups') {
					$this->addNotifyGroup ($row1 ['uid_foreign'] . '|' . $row1 ['offset']);
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
	}
	function cloneEvent() {
		$thisClass = get_class ($this);
		$event = new $thisClass($this->getValuesAsArray (), $this->isException, $this->getType ());
		$event->markerCache = $this->markerCache;
		$event->setIsClone (true);
		return $event;
	}
	
	/**
	 * Gets the location of the event.
	 * Location does not exist in the default
	 * model, only in calexample.
	 *
	 * @return string location.
	 */
	function getLocation() {
		return $this->location;
	}
	
	/**
	 * Sets the location of the event.
	 * Location does not exist in the default
	 * model, only in calexample.
	 *
	 * @param
	 *        	string		The location.
	 * @return void
	 */
	function setLocation($location) {
		$this->location = $location;
	}
	
	/**
	 * Gets the teaser of the event.
	 *
	 * @return string teaser.
	 */
	function getTeaser() {
		return $this->teaser;
	}
	
	/**
	 * Sets the teaser of the event.
	 *
	 * @param
	 *        	string		The location.
	 * @return void
	 */
	function setTeaser($teaser) {
		$this->teaser = $teaser;
	}
	function getLocationLink($view) {
		$locationLink = '';
		/*
		 * 26.01.2009 the URL link is handled now by the getLocationMarker method, just like the page link /* if($this->getLocationLinkUrl()!='' && $this->getLocation()){ $tempArray = $this->getValuesAsArray(); $tempArray['link'] = $this->getLocationLinkUrl(); $this->initLocalCObject($tempArray); unset($tempArray); $this->local_cObj->setCurrentVal($this->getLocation()); $locationLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['location'],$this->conf['view.'][$view.'.']['event.']['location.']); } else
		 */
		if ($this->getLocationId () > 0) {
			$location = $this->getLocationObject ();
			
			if (is_object ($location)) {
				$tempData = $location->getValuesAsArray ();
				$this->initLocalCObject ($tempData);
				unset ($tempData);
				$this->local_cObj->setCurrentVal ($location->getName ());
				/* If a specific location page is defined, link to it */
				if ($this->getLocationPage () > 0) {
					$this->local_cObj->data ['link_parameter'] = $this->getLocationPage ();
				} else {
					/* If location view is allowed, link to it */
					$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
					if ($rightsObj->isViewEnabled ($this->conf ['view.'] ['locationLinkTarget']) || $this->conf ['view.'] ['location.'] ['locationViewPid']) {
						$location->getLinkToLocation ('|');
						$this->local_cObj->data ['link_parameter'] = $this->controller->cObj->lastTypoLinkUrl;
					} else {
						/* Just show the name of the location */
						$this->local_cObj->data ['link_parameter'] = '';
					}
				}
				$locationLink = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['location'], $this->conf ['view.'] [$view . '.'] ['event.'] ['location.']);
			} else {
				GeneralUtility::devLog ('getLocationLink: no location object found', 'cal', 1);
				$locationLink = '';
			}
		}
		return $locationLink;
	}
	function getOrganizerLink($view) {
		$organizerLink = '';
		/*
		 * 26.01.2009 the URL link is handled now by the getOrganizerMarker method, just like the page link /* if($this->getOrganizerLinkUrl()!='' && $this->getOrganizer()){ $tempArray = $this->getValuesAsArray(); $tempArray['link'] = $this->getOrganizerLinkUrl(); $this->initLocalCObject($tempArray); unset($tempArray); $this->local_cObj->setCurrentVal($this->getOrganizer()); $organizerLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['organizer'],$this->conf['view.'][$view.'.']['event.']['organizer.']); } else
		 */
		if ($this->getOrganizerId () > 0) {
			$organizer = $this->getOrganizerObject ();
			
			if (is_object ($organizer)) {
				$tempData = $organizer->getValuesAsArray ();
				$this->initLocalCObject ($tempData);
				unset ($tempData);
				$this->local_cObj->setCurrentVal ($organizer->getName ());
				
				/* If a specific organizer page is defined, link to it */
				if ($this->getOrganizerPage () > 0) {
					$this->local_cObj->data ['link_parameter'] = $this->getOrganizerPage ();
				} else {
					/* If organizer view is allowed, link to it */
					$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
					if ($rightsObj->isViewEnabled ($this->conf ['view.'] ['organizerLinkTarget']) || $this->conf ['view.'] ['organizer.'] ['organizerViewPid']) {
						$organizer->getLinkToOrganizer ('|');
						$this->local_cObj->data ['link_parameter'] = $this->controller->cObj->lastTypoLinkUrl;
					} else {
						/* Just show the name of the organizer */
						$this->local_cObj->data ['link_parameter'] = '';
					}
				}
				$organizerLink = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['organizer'], $this->conf ['view.'] [$view . '.'] ['event.'] ['organizer.']);
			} else {
				GeneralUtility::devLog ('getOrganizerLink: no organizer object found', 'cal', 1);
				$organizerLink = '';
			}
		}
		return $organizerLink;
	}
	
	/**
	 * Returns the headerstyle name
	 */
	function getHeaderStyle() {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if ($this->row ['isFreeAndBusyEvent'] == 1) {
			return $this->conf ['view.'] ['freeAndBusy.'] ['headerStyle'];
		}
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['differentStyleIfOwnEvent'] && $rightsObj->getUserId () == $this->getCreateUserId ()) {
			return $this->conf ['view.'] ['event.'] ['event.'] ['headerStyleOfOwnEvent'];
		} else if (count ($this->categories) && is_object ($this->categories [0])) {
			if ($this->categories [0]->getHeaderStyle () != '') {
				return $this->categories [0]->getHeaderStyle ();
			}
		}
		return $this->headerstyle;
	}
	
	/**
	 * Returns the bodystyle name
	 */
	function getBodyStyle() {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if ($this->row ['isFreeAndBusyEvent'] == 1) {
			return $this->conf ['view.'] ['freeAndBusy.'] ['bodyStyle'];
		}
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['differentStyleIfOwnEvent'] && $rightsObj->getUserId () == $this->getCreateUserId ()) {
			return $this->conf ['view.'] ['event.'] ['event.'] ['bodyStyleOfOwnEvent'];
		} else if (count ($this->categories) && is_object ($this->categories [0])) {
			if ($this->categories [0]->getBodyStyle () != '') {
				return $this->categories [0]->getBodyStyle ();
			}
		}
		
		return $this->bodystyle;
	}
	
	/**
	 * Gets the createUserId of the event.
	 *
	 * @return string create user id.
	 */
	function getCreateUserId() {
		return $this->createUserId;
	}
	function getTimezone() {
		return $this->timezone;
	}
	function setTimezone($timezone) {
		$this->timezone = $timezone;
	}
	
	/**
	 * Sets the createUserId of the event.
	 *
	 * @param
	 *        	string		The create user id.
	 * @return void
	 */
	function setCreateUserId($createUserId) {
		$this->createUserId = $createUserId;
	}
	function renderEventForOrganizer() {
		return $this->renderEventFor ('ORGANIZER');
	}
	function renderEventForLocation() {
		return $this->renderEventFor ('LOCATION');
	}
	function renderEventForDay() {
		return $this->renderEventFor ('DAY');
	}
	function renderEventForWeek() {
		return $this->renderEventFor ('WEEK');
	}
	function renderEventForAllDay() {
		return $this->renderEventFor ('ALLDAY');
	}
	function renderEventForMonth() {
		if ($this->isAllday ()) {
			return $this->renderEventFor ('MONTH_ALLDAY');
		}
		return $this->renderEventFor ('MONTH');
	}
	function renderEventForMiniMonth() {
		if ($this->isAllday ()) {
			return $this->renderEventFor ('MONTH_MINI_ALLDAY');
		}
		return $this->renderEventFor ('MONTH_MINI');
	}
	function renderEventForMediumMonth() {
		if ($this->isAllday ()) {
			return $this->renderEventFor ('MONTH_MEDIUM_ALLDAY');
		}
		return $this->renderEventFor ('MONTH_MEDIUM');
	}
	function renderEventForYear() {
		return $this->renderEventFor ('year');
	}
	function renderEvent() {
		return $this->fillTemplate ('###TEMPLATE_PHPICALENDAR_EVENT###');
	}
	function renderEventForList($subpartSuffix = 'LIST_ODD') {
		return $this->renderEventFor ($subpartSuffix);
	}
	function renderEventFor($viewType, $subpartSuffix = '') {
		if ($this->row ['isFreeAndBusyEvent'] == 1) {
			$viewType .= '_FNB';
		}
		if (substr ($viewType, - 6) != 'ALLDAY' && ($this->isAllday () || $this->getStart ()->format ('%Y%m%d') != $this->getEnd ()->format ('%Y%m%d'))) {
			$subpartSuffix .= 'ALLDAY';
		}
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_phpicalendar_model', 'eventModelClass', 'model');
		
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preFillTemplate')) {
				$hookObj->preFillTemplate ($this, $viewType, $subpartSuffix);
			}
		}
		
		return $this->fillTemplate ('###TEMPLATE_PHPICALENDAR_EVENT_' . strtoupper ($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
	}
	function fillTemplate($subpartMarker) {
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		
		$templatePath = $this->conf ['view.'] ['event.'] ['eventModelTemplate'];
		
		$page = $cObj->fileResource ($templatePath);
		
		if ($page == '') {
			return '<h3>calendar: no event model template file found:</h3>' . $templatePath;
		}
		$page = $cObj->getSubpart ($page, $subpartMarker);
		if (! $page) {
			return 'could not find the >' . str_replace ('###', '', $subpartMarker) . '< subpart-marker in ' . $templatePath;
		}
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		$this->getMarker ($page, $sims, $rems, $wrapped);
		return $this->finish (\TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, $rems, $wrapped));
	}
	function renderEventPreview() {
		$this->isPreview = true;
		return $this->fillTemplate ('###TEMPLATE_PHPICALENDAR_EVENT_PREVIEW###');
	}
	function renderTomorrowsEvent() {
		$this->isTomorrow = true;
		return $this->fillTemplate ('###TEMPLATE_PHPICALENDAR_EVENT_TOMORROW###');
	}
	function getSubscriptionMarker(& $template, & $sims, & $rems, &$wrapped, $view) {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$monitoring = $this->conf ['monitor'];
		$getdate = $this->conf ['getdate'];
		$captchaStr = 0;
		$rems ['###SUBSCRIPTION###'] = '';
		$sims ['###NOTLOGGEDIN_NOMONITORING_HEADING###'] = '';
		$sims ['###NOTLOGGEDIN_NOMONITORING_SUBMIT###'] = '';
		$sims ['###NOTLOGGEDIN_MONITORING_HEADING###'] = '';
		$sims ['###NOTLOGGEDIN_MONITORING_SUBMIT###'] = '';
		$sims_temp ['L_CAPTCHA_START_SUCCESS'] = '';
		$sims_temp ['L_CAPTCHA_STOP_SUCCESS'] = '';
		
		// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		// cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','cobj');
		$cObj = &$this->controller->cObj;
		if (($this->conf ['allowSubscribe'] == 1 || ($this->conf ['subscribeFeUser'] == 1 && $rightsObj->isLoggedIn ())) && $uid) {
			if ($monitoring != null && $monitoring != '') {
				$user_uid = $rightsObj->getUserId ();
				switch ($monitoring) {
					case 'start' :
						{
							if ($user_uid > 0) {
								$table = 'tx_cal_fe_user_event_monitor_mm';
								$fields_values = array (
										'uid_local' => $uid,
										'uid_foreign' => $user_uid,
										'tablenames' => 'fe_users',
										'sorting' => 1,
										'pid' => $this->conf ['rights.'] ['create.'] ['event.'] ['saveEventToPid'],
										'offset' => $this->conf ['view.'] ['event.'] ['remind.'] ['time'] 
								);
								$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $fields_values);
								if (FALSE === $result){
									throw new \RuntimeException('Could not write '.$table.' record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458137);
								}
								
								$pageTSConf = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig ($this->conf ['rights.'] ['create.'] ['event.'] ['saveEventToPid']);
								$offset = is_numeric ($pageTSConf ['options.'] ['tx_cal_controller.'] ['view.'] ['event.'] ['remind.'] ['time']) ? $pageTSConf ['options.'] ['tx_cal_controller.'] ['view.'] ['event.'] ['remind.'] ['time'] * 60 : 0;
								$date = new \TYPO3\CMS\Cal\Model\CalDate ($insertFields ['start_date'] . '000000');
								$date->setTZbyId ('UTC');
								$reminderTimestamp = $date->getTime () + $insertFields ['start_time'] - $offset;
								$reminderService = &\TYPO3\CMS\Cal\Utility\Functions::getReminderService ();
								$reminderService->scheduleReminder ($uid);
							} else {
								if ($this->conf ['subscribeWithCaptcha'] == 1 && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('captcha')) {
									session_start ();
									$captchaStr = $_SESSION ['tx_captcha_string'];
									$_SESSION ['tx_captcha_string'] = '';
								} else {
									$captchaStr = - 1;
								}
								
								if (($captchaStr && $this->controller->piVars ['captcha'] === $captchaStr) || ($this->conf ['subscribeWithCaptcha'] == 0)) {
									// send confirm email!!
									$email = $this->controller->piVars ['email'];
									
									$mailer = $mail = new \TYPO3\CMS\Core\Mail\MailMessage();
									
									if (GeneralUtility::validEmail ($this->conf ['view.'] ['event.'] ['notify.'] ['emailAddress'])) {
										$mailer->setFrom (Array (
												$this->conf ['view.'] ['event.'] ['notify.'] ['emailAddress'] => $this->conf ['view.'] ['event.'] ['notify.'] ['fromName'] 
										));
									}
									
									if (GeneralUtility::validEmail ($this->conf ['view.'] ['event.'] ['notify.'] ['emailReplyAddress'])) {
										$mailer->setReplyTo (Array (
												$this->conf ['view.'] ['event.'] ['notify.'] ['emailReplyAddress'] => $this->conf ['view.'] ['event.'] ['notify.'] ['replyToName'] 
										));
									}
									
									$mailer->getHeaders ()->addTextHeader ('Organization: ', $this->conf ['view.'] ['event.'] ['notify.'] ['organisation']);
									
									$local_template = $cObj->fileResource ($this->conf ['view.'] ['event.'] ['notify.'] ['confirmTemplate']);
									
									$htmlTemplate = $cObj->getSubpart ($local_template, '###HTML###');
									$plainTemplate = $cObj->getSubpart ($local_template, '###PLAIN###');
									
									$local_switch = Array ();
									$local_rems = Array ();
									$local_wrapped = Array ();
									$this->getMarker ($htmlTemplate, $local_switch, $local_rems, $local_wrapped, 'event');
									$this->getMarker ($plainTemplate, $local_switch, $local_rems, $local_wrapped, 'event');
									
									$local_switch ['###CONFIRM_LINK###'] = GeneralUtility::getIndpEnv ('TYPO3_SITE_URL') . $this->controller->pi_getPageLink ($this->conf ['view.'] ['event.'] ['notify.'] ['subscriptionViewPid'], '', array (
											'tx_cal_controller[view]' => 'subscription',
											'tx_cal_controller[monitor]' => 'start',
											'tx_cal_controller[email]' => $email,
											'tx_cal_controller[uid]' => $this->getUid (),
											'tx_cal_controller[sid]' => md5 ($this->getUid () . $email . $this->getCreationDate ()) 
									));
									
									$local_switch ['###EVENT_LINK###'] = GeneralUtility::getIndpEnv ('TYPO3_SITE_URL') . $this->controller->pi_getPageLink ($this->conf ['view.'] ['event.'] ['eventViewPid'], '', array (
											'tx_cal_controller[view]' => 'event',
											'tx_cal_controller[uid]' => $this->getUid(),
											'tx_cal_controller[type]' => $this->getType(),
											'tx_cal_controller[getdate]' => $this->getStart()->format('%Y%m%d')));
									$htmlTemplate = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$htmlTemplate = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									$plainTemplate = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($plainTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$mailer->setSubject ($this->conf ['view.'] ['event.'] ['notify.'] ['confirmTitle']);
									
									$rems ['###SUBSCRIPTION###'] = $this->controller->pi_getLL ('l_monitor_start_thanks');
									$this->controller->finish ($htmlTemplate);
									$this->controller->finish ($plainTemplate);
									$mailer->setTo (array (	$email ));
									$mailer->setBody (strip_tags ($plainTemplate), 'text/plain');
									$mailer->addPart (\TYPO3\CMS\Cal\Utility\Functions::fixURI ($htmlTemplate), 'text/html');
									$mailer->send ();

									return;
								} else {
									$sims_temp ['L_CAPTCHA_START_SUCCESS'] = $this->controller->pi_getLL ('l_monitor_wrong_captcha');
								}
							}
							break;
						}
					case 'stop' :
						{
							if ($user_uid > 0) {
								$table = 'tx_cal_fe_user_event_monitor_mm';
								$where = 'uid_foreign = ' . $user_uid . ' AND uid_local = ' . $uid . ' AND tablenames = "fe_users"';
								$GLOBALS ['TYPO3_DB']->exec_DELETEquery ($table, $where);
							} else {
								if ($this->conf ['subscribeWithCaptcha'] == 1 && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('captcha')) {
									session_start ();
									$captchaStr = $_SESSION ['tx_captcha_string'];
									$_SESSION ['tx_captcha_string'] = '';
								} else {
									$captchaStr = - 1;
								}
								
								if (($captchaStr && $this->controller->piVars ['captcha'] === $captchaStr) || ($this->conf ['subscribeWithCaptcha'] == 0)) {
									$email = $this->controller->piVars ['email'];
									$table = 'tx_cal_unknown_users';
									$select = 'crdate';
									$where = 'email = "' . $email . '"';
									$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
									$crdate = 0;
									if ($result) {
										while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
											$crdate = $row ['crdate'];
											break;
										}
										$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
									}

									$mailer = $mail = new \TYPO3\CMS\Core\Mail\MailMessage();
									$mailer->setFrom (array (
											$this->conf ['view.'] ['event.'] ['notify.'] ['emailAddress'] => $this->conf ['view.'] ['event.'] ['notify.'] ['fromName'] 
									));
									$mailer->setReplyTo (array (
											$this->conf ['view.'] ['event.'] ['notify.'] ['emailReplyAddress'] => $this->conf ['view.'] ['event.'] ['notify.'] ['replyToName'] 
									));
									$mailer->getHeaders ()->addTextHeader ('Organization: ', $this->conf ['view.'] ['event.'] ['notify.'] ['organisation']);
									
									$local_template = $cObj->fileResource ($this->conf ['view.'] ['event.'] ['notify.'] ['unsubscribeConfirmTemplate']);
									
									$htmlTemplate = $cObj->getSubpart ($local_template, '###HTML###');
									$plainTemplate = $cObj->getSubpart ($local_template, '###PLAIN###');
									
									$local_switch = Array ();
									$local_rems = Array ();
									$local_wrapped = Array ();
									$this->getMarker ($htmlTemplate, $local_switch, $local_rems, $local_wrapped, 'event');
									$local_switch ['###CONFIRM_LINK###'] = GeneralUtility::getIndpEnv ('TYPO3_SITE_URL') . $this->controller->pi_getPageLink ($this->conf ['view.'] ['event.'] ['notify.'] ['subscriptionViewPid'], '', array (
											'tx_cal_controller[view]' => 'subscription',
											'tx_cal_controller[monitor]' => 'stop',
											'tx_cal_controller[email]' => $email,
											'tx_cal_controller[uid]' => $this->getUid (),
											'tx_cal_controller[sid]' => md5 ($this->getUid () . $email . $crdate) 
									));
									$htmlTemplate = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$local_switch = Array ();
									$local_rems = Array ();
									$local_wrapped = Array ();
									$this->getMarker ($plainTemplate, $local_switch, $local_rems, $local_wrapped, 'event');
									$local_switch ['###CONFIRM_LINK###'] = GeneralUtility::getIndpEnv ('TYPO3_SITE_URL') . $this->controller->pi_getPageLink ($this->conf ['view.'] ['event.'] ['notify.'] ['subscriptionViewPid'], '', array (
											'tx_cal_controller[view]' => 'subscription',
											'tx_cal_controller[monitor]' => 'stop',
											'tx_cal_controller[email]' => $email,
											'tx_cal_controller[uid]' => $this->getUid (),
											'tx_cal_controller[sid]' => md5 ($this->getUid () . $email . $crdate) 
									));
									$plainTemplate = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($plainTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$mailer->setSubject ($this->conf ['view.'] ['event.'] ['notify.'] ['unsubscribeConfirmTitle']);
									
									$rems ['###SUBSCRIPTION###'] = $this->controller->pi_getLL ('l_monitor_stop_thanks');
									$this->controller->finish ($htmlTemplate);
									$this->controller->finish ($plainTemplate);
									
									$mailer->setTo (array (
											$email 
									));
									$mailer->setBody (strip_tags ($plainTemplate), 'text/plain');
									$mailer->addPart (\TYPO3\CMS\Cal\Utility\Functions::fixURI ($htmlTemplate), 'text/html');
									$mailer->send ();

									return;
								} else {
									$sims_temp ['L_CAPTCHA_STOP_SUCCESS'] = $this->controller->pi_getLL ('l_monitor_wrong_captcha');
								}
							}
							break;
						}
				}
			}
			
			/* If we have a logged in user */
			if ($rightsObj->isLoggedIn ()) {
				$select = '*';
				$from_table = 'tx_cal_fe_user_event_monitor_mm';
				$whereClause = 'uid_foreign = ' . $rightsObj->getUserId () . ' AND uid_local = ' . $uid;
				
				$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $from_table, $whereClause, $groupBy = '', $orderBy = '', $limit = '');
				$found_one = false;
				// create a local cObj with a customized data array, that is allowed to be changed
				$this->initLocalCObject ($this->getValuesAsArray ());
				$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_monitor_event_logged_in_monitoring'));
				if ($result) {
					while ($row1 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
						$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
								'view' => 'event',
								'monitor' => 'stop',
								'type' => $type,
								'uid' => $uid 
						), $this->conf ['cache'], $this->conf ['clear_anyway']);
						$rems ['###SUBSCRIPTION###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['isMonitoringEventLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['isMonitoringEventLink.']);
						$found_one = true;
					}
					$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
				}
				
				if (! $found_one) {
					$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_monitor_event_logged_in_nomonitoring'));
					$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
							'view' => 'event',
							'monitor' => 'start',
							'type' => $type,
							'uid' => $uid 
					), $this->conf ['cache'], $this->conf ['clear_anyway']);
					$rems ['###SUBSCRIPTION###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['isNotMonitoringEventLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['isNotMonitoringEventLink.']);
				}
			} else { /* Not a logged in user */
					
				/* If a CAPTCHA is required to subscribe, add a couple extra markers */
								if ($this->conf ['subscribeWithCaptcha'] == 1 && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('captcha')) {
					$sims_temp ['CAPTCHA_SRC'] = '<img src="' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath ('captcha') . 'captcha/captcha.php' . '" alt="" />';
					$sims_temp ['L_CAPTCHA_TEXT'] = $this->controller->pi_getLL ('l_captcha_text');
					$sims_temp ['CAPTCHA_TEXT'] = '<input type="text" size=10 name="tx_cal_controller[captcha]" value="">';
				} else {
					$sims_temp ['CAPTCHA_SRC'] = '';
					$sims_temp ['L_CAPTCHA_TEXT'] = '';
					$sims_temp ['CAPTCHA_TEXT'] = '';
				}
				
				$notLoggedinNoMonitoring = $cObj->getSubpart ($template, '###NOTLOGGEDIN_NOMONITORING###');
				$parameter = array (
						'no_cache' => 1,
						'view' => 'event',
						'monitor' => 'start',
						'type' => $type,
						'uid' => $uid 
				);
				$actionUrl = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url ($parameter));
				
				$parameter2 = array (
						'no_cache' => 1,
						'getdate' => $getdate,
						'view' => 'event',
						'monitor' => 'stop' 
				);
				
				$actionUrl2 = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url ($parameter2));
				$sims_temp ['NOTLOGGEDIN_NOMONITORING_HEADING'] = $this->controller->pi_getLL ('l_monitor_event_logged_in_nomonitoring');
				$sims_temp ['NOTLOGGEDIN_NOMONITORING_SUBMIT'] = $this->controller->pi_getLL ('l_submit');
				$sims_temp ['L_ENTER_EMAIL'] = $this->controller->pi_getLL ('l_enter_email');
				$sims_temp ['ACTIONURL'] = $actionUrl;
				$monitor = \TYPO3\CMS\Cal\Controller\Controller::replace_tags ($sims_temp, $notLoggedinNoMonitoring);
				
				$sims_temp ['ACTIONURL'] = $actionUrl2;
				$notLoggedinMonitoring = $cObj->getSubpart ($template, '###NOTLOGGEDIN_MONITORING###');
				$sims_temp ['NOTLOGGEDIN_MONITORING_HEADING'] = $this->controller->pi_getLL ('l_monitor_event_logged_in_monitoring');
				$sims_temp ['NOTLOGGEDIN_MONITORING_SUBMIT'] = $this->controller->pi_getLL ('l_submit');
				$sims_temp ['L_ENTER_EMAIL'] = $this->controller->pi_getLL ('l_enter_email');
				
				$monitor .= \TYPO3\CMS\Cal\Controller\Controller::replace_tags ($sims_temp, $notLoggedinMonitoring);
				$rems ['###SUBSCRIPTION###'] = $monitor;
			}
		} else {
			$rems ['###SUBSCRIPTION###'] = '';
		}
	}
	function getStartAndEndMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
		$this->initLocalCObject ();
		
		$eventStart = $this->getStart ();
		$eventEnd = $this->getEnd ();
		if ($eventStart->equals ($eventEnd)) {
			$sims ['###STARTTIME_LABEL###'] = '';
			$sims ['###ENDTIME_LABEL###'] = '';
			$sims ['###STARTTIME###'] = '';
			$sims ['###ENDTIME###'] = '';
			$this->local_cObj->setCurrentVal ($eventStart->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
			$sims ['###STARTDATE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['startdate'], $this->conf ['view.'] [$view . '.'] ['event.'] ['startdate.']);
			
			$sims ['###STARTDATE_LABEL###'] = $this->controller->pi_getLL ('l_event_allday');
			if ($this->conf ['view.'] [$view . '.'] ['event.'] ['dontShowEndDateIfEqualsStartDateAllday'] == 1) {
				$sims ['###ENDDATE###'] = '';
				$sims ['###ENDDATE_LABEL###'] = '';
			} else {
				$this->local_cObj->setCurrentVal ($eventEnd->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
				$sims ['###ENDDATE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['enddate'], $this->conf ['view.'] [$view . '.'] ['event.'] ['enddate.']);
				$sims ['###ENDDATE_LABEL###'] = $this->controller->pi_getLL ('l_event_enddate');
			}
		} else {
			if ($this->isAllday ()) {
				$sims ['###STARTTIME_LABEL###'] = '';
				$sims ['###STARTTIME###'] = '';
			} else {
				$sims ['###STARTTIME_LABEL###'] = $this->controller->pi_getLL ('l_event_starttime');
				$this->local_cObj->setCurrentVal ($eventStart->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['timeFormat']));
				$sims ['###STARTTIME###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['starttime'], $this->conf ['view.'] [$view . '.'] ['event.'] ['starttime.']);
			}
			if ($this->isAllday ()) {
				$sims ['###ENDTIME_LABEL###'] = '';
				$sims ['###ENDTIME###'] = '';
			} else {
				$sims ['###ENDTIME_LABEL###'] = $this->controller->pi_getLL ('l_event_endtime');
				$this->local_cObj->setCurrentVal ($eventEnd->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['timeFormat']));
				$sims ['###ENDTIME###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['endtime'], $this->conf ['view.'] [$view . '.'] ['event.'] ['endtime.']);
			}
			
			$this->local_cObj->setCurrentVal ($eventStart->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
			$sims ['###STARTDATE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['startdate'], $this->conf ['view.'] [$view . '.'] ['event.'] ['startdate.']);
			if ($this->conf ['view.'] [$view . '.'] ['event.'] ['dontShowEndDateIfEqualsStartDate'] && $eventEnd->format ('%Y%m%d') == $eventStart->format ('%Y%m%d')) {
				$sims ['###STARTDATE_LABEL###'] = $this->controller->pi_getLL ('l_date');
				$sims ['###ENDDATE_LABEL###'] = '';
				$sims ['###ENDDATE###'] = '';
			} else {
				$sims ['###STARTDATE_LABEL###'] = $this->controller->pi_getLL ('l_event_startdate');
				$sims ['###ENDDATE_LABEL###'] = $this->controller->pi_getLL ('l_event_enddate');
				$this->local_cObj->setCurrentVal ($eventEnd->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
				$sims ['###ENDDATE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['enddate'], $this->conf ['view.'] [$view . '.'] ['event.'] ['enddate.']);
			}
		}
	}
	function getTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($this->getTitle ());
		if ($this->isTomorrow && ! in_array ($view, array (
				'create_event',
				'edit_event' 
		)) && $this->conf ['view.'] ['other.'] ['tomorrowsEvents']) {
			$sims ['###TITLE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['other.'] ['tomorrowsEvents'], $this->conf ['view.'] ['other.'] ['tomorrowsEvents.']);
		} else if ($this->isAllday () && $this->conf ['view.'] [$view . '.'] ['event.'] ['alldayTitle']) {
			$sims ['###TITLE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['alldayTitle'], $this->conf ['view.'] [$view . '.'] ['event.'] ['alldayTitle.']);
		} else if ($this->conf ['view.'] [$view . '.'] ['event.'] ['title']) {
			$sims ['###TITLE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['title'], $this->conf ['view.'] [$view . '.'] ['event.'] ['title.']);
		} else {
			$sims ['###TITLE###'] = $this->getTitle ();
		}
	}
	function getTitleFnbMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###TITLE_FNB###'] = $this->conf ['view.'] ['freeAndBusy.'] ['eventTitle'];
	}
	function getOrganizerMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->getOrganizerId () > 0) {
			$sims ['###ORGANIZER###'] = $this->getOrganizerLink ($view);
		} else {
			$this->initLocalCObject ($this->getValuesAsArray ());
			if ($this->getOrganizerPage () > 0) {
				$this->local_cObj->data ['link_parameter'] = $this->getOrganizerPage ();
			} else {
				$this->local_cObj->data ['link_parameter'] = $this->getOrganizerLinkUrl ();
			}
			$this->local_cObj->setCurrentVal ($this->getOrganizer ());
			$sims ['###ORGANIZER###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['organizer'], $this->conf ['view.'] [$view . '.'] ['event.'] ['organizer.']);
		}
		if ($view == 'ics' || $view == 'ics_single') {
			$sims ['###ORGANIZER###'] = \TYPO3\CMS\Cal\Utility\Functions::replaceLineFeed ($sims ['###ORGANIZER###']);
		}
	}
	function getLocationMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->getLocationId () > 0) {
			$sims ['###LOCATION###'] = $this->getLocationLink ($view);
		} else {
			$this->initLocalCObject ($this->getValuesAsArray ());
			if ($this->getLocationPage () > 0) {
				$this->local_cObj->data ['link_parameter'] = $this->getLocationPage ();
			} else {
				$this->local_cObj->data ['link_parameter'] = $this->getLocationLinkUrl ();
			}
			$this->local_cObj->setCurrentVal ($this->getLocation ());
			$sims ['###LOCATION###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['location'], $this->conf ['view.'] [$view . '.'] ['event.'] ['location.']);
		}
		if ($view == 'ics' || $view == 'ics_single') {
			$sims ['###LOCATION###'] = \TYPO3\CMS\Cal\Utility\Functions::replaceLineFeed ($sims ['###LOCATION###']);
		}
	}
	function getTeaserMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		if ($confArr ['useTeaser']) {
			$this->initLocalCObject ();
			$this->local_cObj->setCurrentVal ($this->getTeaser ());
			$sims ['###TEASER###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['teaser'], $this->conf ['view.'] [$view . '.'] ['event.'] ['teaser.']);
		} else {
			$sims ['###TEASER###'] = '';
		}
	}
	function getIcsLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->conf ['view.'] ['ics.'] ['showIcsLinks'] == 1) {
			$this->initLocalCObject ($tempArray);
			$uid = $this->getUid ();
			if ($this->row ['l18n_parent'] > 0) {
				$uid = $this->row ['l18n_parent'];
			}
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'type' => $this->getType (),
					'view' => 'single_ics',
					'uid' => $uid 
			), $this->conf ['cache'], $this->conf ['clearAnyway'], $GLOBALS ['TSFE']->id);
			$wrapped ['###ICS_LINK###'] = explode ('$5&xs2', $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['ics'], $this->conf ['view.'] [$view . '.'] ['event.'] ['ics.']));
		} else {
			$rems ['###ICS_LINK###'] = '';
		}
	}
	function getCategoryMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($this->getCategoriesAsString (false));
		$sims ['###CATEGORY###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['category'], $this->conf ['view.'] [$view . '.'] ['event.'] ['category.']);
	}
	function getCategoryLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($this->getCategoriesAsString ());
		$sims ['###CATEGORY_LINK###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['category_link'], $this->conf ['view.'] [$view . '.'] ['event.'] ['category_link.']);
	}
	function getHeaderstyleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###HEADERSTYLE###'] = $this->getHeaderStyle ();
	}
	function getBodystyleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###BODYSTYLE###'] = $this->getBodyStyle ();
	}
	
	/**
	 * Returns the calendar style name
	 */
	function getCalendarStyle(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($this->getCalendarUid ());
		$sims ['###CALENDARSTYLE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['calendarStyle'], $this->conf ['view.'] [$view . '.'] ['event.'] ['calendarStyle.']);
	}
	function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###MAP###'] = '';
		if ($this->conf ['view.'] [$view . '.'] ['event.'] ['showMap'] && $this->getLocationId ()) {
			/* Pull values from Flexform object into individual variables */
			$this->confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
			$useLocationStructure = ($this->confArr ['useLocationStructure'] ? $this->confArr ['useLocationStructure'] : 'tx_cal_location');
			$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
			$location = $modelObj->findLocation ($this->getLocationId (), $useLocationStructure);
			$local_sims = Array ();
			$local_rems = Array ();
			$local_wrapped = Array ();
			$location->getMarker ('###MAP###', $local_sims, $local_rems, $local_wrapped);
			$sims ['###MAP###'] = $local_sims ['###MAP###'];
		}
	}
	function getAttachmentMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###ATTACHMENT###'] = '';
		$tempData = $this->getValuesAsArray ();
		$this->initLocalCObject ($tempData);
		$sims ['###ATTACHMENT###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['attachment'], $this->conf ['view.'] [$view . '.'] ['event.'] ['attachment.']);
	}
	function getAttachmentUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###ATTACHMENT_URL###'] = '';
		$tempData = $this->getValuesAsArray ();
		$this->initLocalCObject ($tempData);
		$sims ['###ATTACHMENT_URL###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['attachment_url'], $this->conf ['view.'] [$view . '.'] ['event.'] ['attachment_url.']);
	}
	function getEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart ();
		$wrapped ['###EVENT_LINK###'] = explode ('$5&xs2', $this->getLinkToEvent ('$5&xs2', $view, $eventStart->format ('%Y%m%d')));
	}
	function getEventUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart ();
		$sims ['###EVENT_URL###'] = htmlspecialchars ($this->getLinkToEvent ('$5&xs2', $view, $eventStart->format ('%Y%m%d'), true));
	}
	function getAbsoluteEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart ();
		$wrapped ['###ABSOLUTE_EVENT_LINK###'] = explode ('$5&xs2', $this->getLinkToEvent ('$5&xs2', $view, $eventStart->format ('%Y%m%d')));
	}
	function getStartdate() {
		$start = $this->getStart ();
		return $start->format (\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf ($this->conf));
	}
	function getEnddate() {
		$end = $this->getEnd ();
		return $end->format (\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf ($this->conf));
	}
	function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart ();
		$sims ['###EDIT_LINK###'] = '';
		
		if ($this->isUserAllowedToEdit ()) {
			// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
			$linkConf = $this->getValuesAsArray ();
			if ($this->conf ['view.'] ['enableAjax']) {
				$temp = sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['editLinkOnClick'], $this->getUid (), $this->getType ());
				$linkConf ['link_ATagParams'] = ' onclick="' . $temp . '"';
			}
			$linkConf ['link_no_cache'] = 0;
			$linkConf ['link_useCacheHash'] = 0;
			$linkConf ['link_additionalParams'] = '&tx_cal_controller[view]=edit_event&tx_cal_controller[type]=' . $this->getType () . '&tx_cal_controller[uid]=' . $this->getUid () . '&tx_cal_controller[getdate]=' . $eventStart->format ('%Y%m%d') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView ();
			$linkConf ['link_section'] = 'default';
			$linkConf ['link_parameter'] = $this->conf ['view.'] ['event.'] ['editEventViewPid'] ? $this->conf ['view.'] ['event.'] ['editEventViewPid'] : $GLOBALS ['TSFE']->id;
			
			$this->initLocalCObject ($linkConf);
			$this->local_cObj->setCurrentVal ($this->conf ['view.'] [$view . '.'] ['event.'] ['editIcon']);
			$sims ['###EDIT_LINK###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['editLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['editLink.']);
		}
		if ($this->isUserAllowedToDelete ()) {
			// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
			$linkConf = $this->getValuesAsArray ();
			if ($this->conf ['view.'] ['enableAjax']) {
				$temp = sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['deleteLinkOnClick'], $this->getUid (), $this->getType ());
				$linkConf ['link_ATagParams'] = ' onclick="' . $temp . '"';
			}
			$linkConf ['link_no_cache'] = 0;
			$linkConf ['link_useCacheHash'] = 0;
			$linkConf ['link_additionalParams'] = '&tx_cal_controller[view]=delete_event&tx_cal_controller[type]=' . $this->getType () . '&tx_cal_controller[uid]=' . $this->getUid () . '&tx_cal_controller[getdate]=' . $eventStart->format ('%Y%m%d') . '&tx_cal_controller[lastview]=' . $this->controller->extendLastView ();
			$linkConf ['link_section'] = 'default';
			$linkConf ['link_parameter'] = $this->conf ['view.'] ['event.'] ['deleteEventViewPid'] ? $this->conf ['view.'] ['event.'] ['deleteEventViewPid'] : $GLOBALS ['TSFE']->id;
			
			$this->initLocalCObject ($linkConf);
			$this->local_cObj->setCurrentVal ($this->conf ['view.'] [$view . '.'] ['event.'] ['deleteIcon']);
			$sims ['###EDIT_LINK###'] .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['deleteLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['deleteLink.']);
		}
	}
	function getMoreLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###MORE_LINK###'] = '';
		if ($this->conf ['view.'] ['event.'] ['isPreview'] && $this->conf ['preview']) {
			$this->initLocalCObject ();
			$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_more'));
			
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'page_id' => $GLOBALS ['TSFE']->id,
					'preview' => null,
					'view' => event,
					'uid' => $this->getUid (),
					'type' => $this->getType () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['event.'] ['eventViewPid']);
			
			$sims ['###MORE_LINK###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['moreLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['moreLink.']);
		}
	}
	function getStartdateMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->getStartAndEndMarker ($template, $sims, $rems, $wrapped, $view);
	}
	function getEnddateMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->getStartdateMarker ($template, $sims, $rems, $wrapped, $view);
	}
	function getStarttimeMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->getStartdateMarker ($template, $sims, $rems, $wrapped, $view);
	}
	function getEndtimeMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->getStartdateMarker ($template, $sims, $rems, $wrapped, $view);
	}
	function getDescriptionStriptagsMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->striptags = true;
		$this->getDescriptionMarker ($template, $sims, $rems, $wrapped, $view);
		$this->striptags = false;
	}
	function getIconMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$event_status = strtolower ($this->getStatus ());
		$confirmed = '';
		if ($event_status != '') {
			$confirmed = sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['todoIcon'], $event_status);
		} else if (is_array ($this->getCalRecu ()) && count ($this->getCalRecu ()) > 0) {
			$confirmed = $this->conf ['view.'] [$view . '.'] ['event.'] ['recurringIcon'];
		}
		$sims ['###ICON###'] = $confirmed;
	}
	function getAdditionalValuesAsArray() {
		$values = parent::getAdditionalValuesAsArray ();
		$values ['event_owner'] = $this->getEventOwner ();
		$values ['cruser_id'] = $this->getCreateUserId ();
		if ($this->conf ['view.'] ['enableAjax']) {
			$template = '';
			$sims = Array ();
			$rems = Array ();
			$wrapped = Array ();
			$this->getEventUrlMarker ($template, $sims, $rems, $wrapped, 'event');
			$values ['event_link'] = $wrapped ['###EVENT_URL###'];
		}
		return $values;
	}
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = Array ()) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		
		if (! $rightsObj->isViewEnabled ('edit_event')) {
			return false;
		}
		
		if ($rightsObj->isCalAdmin ()) {
			return true;
		}
		$editOffset = $this->conf ['rights.'] ['edit.'] ['event.'] ['timeOffset'] * 60;
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId ();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups ();
		}
		$isEventOwner = $this->isEventOwner ($feUserUid, $feGroupsArray);
		$isSharedUser = $this->isSharedUser ($feUserUid, $feGroupsArray);
		if ($rightsObj->isAllowedToEditStartedEvent ()) {
			$eventHasntStartedYet = true;
		} else {
			$temp = new \TYPO3\CMS\Cal\Model\CalDate ();
			$temp->setTZbyId ('UTC');
			$temp->addSeconds ($editOffset);
			$eventStart = $this->getStart ();
			$eventHasntStartedYet = $eventStart->after ($temp);
		}
		$isAllowedToEditEvent = $rightsObj->isAllowedToEditEvent ();
		$isAllowedToEditOwnEventsOnly = $rightsObj->isAllowedToEditOnlyOwnEvent ();
		$isPublicAllowed = $rightsObj->isPublicAllowedToEditEvents ();
		
		if ($isAllowedToEditOwnEventsOnly) {
			return ($isEventOwner || $isSharedUser) && ($eventHasntStartedYet || $rightsObj->isAllowedToEditEventInPast ());
		}
		return $isAllowedToEditEvent && ($isEventOwner || $isSharedUser || $isPublicAllowed) && ($eventHasntStartedYet || $rightsObj->isAllowedToEditEventInPast ());
	}
	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = Array ()) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if (! $rightsObj->isViewEnabled ('delete_event')) {
			return false;
		}
		if ($rightsObj->isCalAdmin ()) {
			return true;
		}
		$deleteOffset = $this->conf ['rights.'] ['delete.'] ['event.'] ['timeOffset'] * 60;
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId ();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups ();
		}
		$isEventOwner = $this->isEventOwner ($feUserUid, $feGroupsArray);
		$isSharedUser = $this->isSharedUser ($feUserUid, $feGroupsArray);
		if ($rightsObj->isAllowedToDeleteStartedEvents ()) {
			$eventHasntStartedYet = true;
		} else {
			$temp = new \TYPO3\CMS\Cal\Model\CalDate ();
			$temp->setTZbyId ('UTC');
			$temp->addSeconds ($editOffset);
			$eventStart = $this->getStart ();
			$eventHasntStartedYet = $eventStart->after ($temp);
		}
		$isAllowedToDeleteEvents = $rightsObj->isAllowedToDeleteEvents ();
		$isAllowedToDeleteOwnEventsOnly = $rightsObj->isAllowedToDeleteOnlyOwnEvents ();
		
		if ($isAllowedToDeleteOwnEventsOnly) {
			return ($isEventOwner || $isSharedUser) && ($eventHasntStartedYet || $rightsObj->isAllowedToDeleteEventInPast ());
		}
		return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && ($eventHasntStartedYet || $rightsObj->isAllowedToDeleteEventInPast ());
	}
	function __toString() {
		return 'Phpicalendar ' . (is_object ($this) ? 'object' : 'something') . ': ' . implode (',', $this->row);
	}
	function getAttendees() {
		return $this->attendees;
	}
	function getAttendeeMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###ATTENDEE###'] = '';
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$globalAttendeeArray = $this->getAttendees ();
		
		$isChairMan = false;
		$chairmanEmail = 'none';
		foreach ($globalAttendeeArray as $serviceKey => $attendeeArray) {
			foreach ($attendeeArray as $attendee) {
				if ($attendee->getAttendance () == 'CHAIR') {
					$chairmanEmail = $attendee->getEmail ();
				}
				if ($attendee->getAttendance () == 'CHAIR' && $rightsObj->getUserId () == $attendee->getFeUserId ()) {
					$isChairMan = true;
					break;
				}
			}
		}
		
		if (in_array ($view, Array (
				'ics',
				'ics_single' 
		))) {
			if (! empty ($globalAttendeeArray)) {
				foreach ($globalAttendeeArray as $serviceType => $attendeeArray) {
					foreach ($attendeeArray as $attendee) {
						if ($attendee->getAttendance () == 'CHAIR') {
							$sims ['###ORGANIZER###'] = 'ORGANIZER;ROLE=' . $attendee->getAttendance () . ':MAILTO:' . $attendee->getEmail ();
						}
						if ($this->limitAttendeeToThisEmail != '' && $attendee->getEmail () != $this->limitAttendeeToThisEmail) {
							continue;
						}
						if ($attendee->getStatus () == 0) {
							$attendee->setStatus ('NEEDS-ACTION');
						}
						$sims ['###ATTENDEE###'] .= 'ATTENDEE;ROLE=' . $attendee->getAttendance () . ';PARTSTAT=' . $attendee->getStatus () . ';RSVP=TRUE:MAILTO:' . $attendee->getEmail ();
					}
				}
			}
		} else {
			if ($rightsObj->isLoggedIn () && ! empty ($globalAttendeeArray)) {
				$formattedArray = Array ();
				$partOf = false;
				// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
				foreach ($globalAttendeeArray as $serviceKey => $attendeeArray) {
					foreach ($attendeeArray as $attendee) {
						if ($attendee->getFeUserId ()) {
							$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', 'pid in (' . $this->conf ['pidList'] . ')' . $cObj->enableFields ('fe_users') . ' AND uid =' . $attendee->getFeUserId ());
							if ($result) {
								while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
									$this->initLocalCObject ($row);
									$displayConfig = $this->conf ['view.'] [$view . '.'] ['event.'] ['attendeeFeUserDisplayName'] ? 'attendeeFeUserDisplayName' : 'defaultFeUserDisplayName';
									$attendee->setName ($this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] [$displayConfig], $this->conf ['view.'] [$view . '.'] ['event.'] [$displayConfig . '.']));
									$attendee->setEmail ($row ['email']);
								}
								$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
							}
							$finalString = $attendee->getName () . ' ';
						} else {
							$finalString = $attendee->getEmail () . ' ';
						}
						
						if ($attendee->getAttendance () == 'CHAIR') {
							$finalString .= sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['attendeeIcon'], $attendee->getAttendance (), $this->controller->pi_getLL ('l_event_attendee_' . $attendee->getAttendance ()), $this->controller->pi_getLL ('l_event_attendee_' . $attendee->getAttendance ()));
						} else {
							$finalString .= sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['attendeeIcon'], $attendee->getStatus (), $this->controller->pi_getLL ('l_event_attendee_' . $attendee->getStatus ()), $this->controller->pi_getLL ('l_event_attendee_' . $attendee->getStatus ()));
						}
						if ($rightsObj->getUserId () == $attendee->getFeUserId () || $isChairMan) {
							$partOf = true;
							$this->initLocalCObject ($this->getValuesAsArray ());
							if ($attendee->getAttendance () != 'CHAIR') {
								$finalString .= $this->controller->pi_getLL ('l_meeting_changestatus');
							}
							if ($attendee->getAttendance () != 'CHAIR' && ($attendee->getStatus () == 'ACCEPTED' || $attendee->getStatus () == '0' || $attendee->getStatus () == 'NEEDS-ACTION')) {
								$this->local_cObj->setCurrentVal (sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['attendeeIcon'], 'DECLINE', $this->controller->pi_getLL ('l_meeting_decline'), $this->controller->pi_getLL ('l_meeting_decline')));
								$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
										'view' => 'meeting',
										'attendee' => $attendee->getUid (),
										'uid' => $this->getUid (),
										'status' => 'decline',
										'sid' => md5 ($this->getUid () . $attendee->getEmail () . $attendee->row ['crdate']) 
								), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['event.'] ['meeting.'] ['statusViewPid']) . ' ';
								$finalString .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['declineMeetingLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['declineMeetingLink.']);
							}
							if ($attendee->getAttendance () != 'CHAIR' && ($attendee->getStatus () == 'DECLINE' || $attendee->getStatus () == '0' || $attendee->getStatus () == 'NEEDS-ACTION')) {
								$this->local_cObj->setCurrentVal (sprintf ($this->conf ['view.'] [$view . '.'] ['event.'] ['attendeeIcon'], 'ACCEPTED', $this->controller->pi_getLL ('l_meeting_accept'), $this->controller->pi_getLL ('l_meeting_accept')));
								$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
										'view' => 'meeting',
										'attendee' => $attendee->getUid (),
										'uid' => $this->getUid (),
										'status' => 'accept',
										'sid' => md5 ($this->getUid () . $attendee->getEmail () . $attendee->row ['crdate']) 
								), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['event.'] ['meeting.'] ['statusViewPid']);
								$finalString .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['acceptMeetingLink'], $this->conf ['view.'] [$view . '.'] ['event.'] ['acceptMeetingLink.']);
							}
						}
						$formattedArray [] = $finalString;
					}
				}
				if ($partOf) {
					$this->initLocalCObject ();
					$this->local_cObj->setCurrentVal (implode ('<br/>', $formattedArray));
					$sims ['###ATTENDEE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['attendee'], $this->conf ['view.'] [$view . '.'] ['event.'] ['attendee.']);
				}
			}
		}
	}
	function getLinkToEvent($linktext, $view, $date, $urlOnly = false) {
		/* new */
		// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
		if ($linktext == '') {
			$linktext = $this->controller->pi_getLL ('l_no_title');
		}
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		
		if ($this->getEventType () == \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_SHORTCUT || $this->getEventType () == \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_EXTERNAL || $rightsObj->isViewEnabled ($this->getObjectType ()) || $this->conf ['view.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'ViewPid']) {
			$this->initLocalCObject ($this->getValuesAsArray ());
			$this->local_cObj->setCurrentVal ($linktext);
			
			if (! $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'Link']) {
				$view = $this->getObjectType ();
			}
			
			/* new */
			if ($this->isExternalPluginEvent ()) {
				return $this->getExternalPluginEventLink ();
			}
			
			// create the link if the event points to a page or external URL
			// determine the link type
			switch ($this->getEventType ()) {
				// shortcut to page - create the link
				case \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_SHORTCUT :
					$this->local_cObj->data ['link_parameter'] = $this->page;
					break;
				// external url
				case \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_EXTERNAL :
					$this->local_cObj->data ['link_parameter'] = $this->ext_url;
					$param = $this->page;
					break;
				// regular event or custom type
				default :
					$linkParams = array (
							'page_id' => $GLOBALS ['TSFE']->id,
							'getdate' => $date,
							'view' => $this->getObjectType (),
							'type' => $this->getType (),
							'uid' => $this->getUid () 
					);
					$this->addAdditionalSingleViewUrlParams ($linkParams);
					if ($this->conf ['view.'] [$this->getObjectType () . '.'] ['isPreview']) {
						$linkParams ['preview'] = 1;
					}
					$pid = $this->conf ['view.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'ViewPid'];
					$categories = &$this->getCategories ();
					if (is_array ($categories) && count ($categories)) {
						foreach ($this->getCategories () as $category) {
							if (is_object ($category)) {
								if ($category->getSinglePid ()) {
									$pid = $category->getSinglePid ();
									break;
								}
							}
						}
					}
					
					if (! $this->isClone ()) {
						$linkParams ['lastview'] = $this->controller->extendLastView (array (
								'getdate' => $this->conf ['getdate'] 
						));
					}
					$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, $linkParams, $this->conf ['cache'], $this->conf ['clear_anyway'], $pid);
					break;
			}
			
			if ($urlOnly) {
				return $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'Url'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'Url.']);
			}
			
			// create & return the link
			$this->local_cObj->data ['link'] = $param;
			return $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'Link'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'Link.']);
		} else {
			return $linktext;
		}
	}
	function getEventIdMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$start = $this->getStart ();
		$sims ['###EVENT_ID###'] = $this->getType () . $this->getUid () . $start->format ('%Y%m%d%H%M');
	}
	function getGuidMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->row ['icsUid'] != '') {
			$sims ['###GUID###'] = $this->row ['icsUid'];
		} else {
			$sims ['###GUID###'] = $this->conf ['view.'] ['ics.'] ['eventUidPrefix'] . '_' . $this->getCalendarUid () . '_' . $this->getUid ();
		}
	}
	function getDtstampMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###DTSTAMP###'] = 'DTSTAMP:' . gmdate ('Ymd', $this->getCreationDate ()) . 'T' . gmdate ('His', $this->getCreationDate ());
	}
	function getDtstartYearMonthDayHourMinuteMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart ();
		if ($this->isAllday ()) {
			$sims ['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART;VALUE=DATE:' . $eventStart->format ('%Y%m%d');
		} else if ($this->conf ['view.'] ['ics.'] ['timezoneId'] != '') {
			$sims ['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART;TZID=' . $this->conf ['view.'] ['ics.'] ['timezoneId'] . ':' . $eventStart->format ('%Y%m%dT%H%M%S');
		} else {
			$offset = \TYPO3\CMS\Cal\Utility\Functions::strtotimeOffset ($eventStart->getTime ());
			$eventStart->subtractSeconds ($offset);
			$sims ['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART:' . $eventStart->format ('%Y%m%dT%H%M%SZ');
			$eventStart->addSeconds ($offset);
		}
	}
	function getDtendYearMonthDayHourMinuteMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart ();
		$eventEnd = $this->getEnd ();
		if ($this->isAllday ()) {
			$eventEnd->addSeconds (84600);
			$sims ['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND;VALUE=DATE:' . $eventEnd->format ('%Y%m%d');
		} else if ($this->conf ['view.'] ['ics.'] ['timezoneId'] != '') {
			$sims ['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND;TZID=' . $this->conf ['view.'] ['ics.'] ['timezoneId'] . ':' . $eventEnd->format ('%Y%m%dT%H%M%S');
		} else {
			$offset = \TYPO3\CMS\Cal\Utility\Functions::strtotimeOffset ($eventEnd->getTime ());
			$eventEnd->subtractSeconds ($offset);
			$sims ['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND:' . $eventEnd->format ('%Y%m%dT%H%M%SZ');
			$eventEnd->addSeconds ($offset);
		}
	}
	function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view) {
		$sims ['###RRULE###'] = '';
		$rrule = $this->getRRule ($this);
		if ($rrule) {
			$sims ['###RRULE###'] = 'RRULE:' . $rrule;
		}
	}
	function getRRule(&$event) {
		$rrule = '';
		$allowedValues = array (
				'second',
				'minute',
				'hour',
				'day',
				'week',
				'month',
				'year' 
		);
		if (in_array ($event->getFreq (), $allowedValues)) {
			$rruleConfiguration = Array ();
			$rruleConfiguration ['FREQ'] = 'FREQ=' . $event->getIcsFreqLabel ($event->getFreq ());
			$rruleConfiguration ['INTERVAL'] = 'INTERVAL=' . $event->getInterval ();
			
			if ($event->getCount () != 0) {
				$rruleConfiguration ['COUNT'] = 'COUNT=' . $event->getCount ();
			}
			if (count ($event->getByDay ()) > 0) {
				$byDay = Array ();
				foreach ($event->getByDay () as $day) {
					$byDay [] = $day;
				}
				$rruleConfiguration ['BYDAY'] = 'BYDAY=' . implode (',', $byDay);
			}
			if (count ($event->getByWeekNo ()) > 0) {
				$byWeekNo = Array ();
				foreach ($event->getByWeekNo () as $week) {
					$byWeekNo [] = $week;
				}
				$rruleConfiguration ['BYWEEKNO'] = 'BYWEEKNO=' . implode (',', $byWeekNo);
			}
			if (count ($event->getByMonth ()) > 0) {
				$byMonth = Array ();
				foreach ($event->getByMonth () as $month) {
					$byMonth [] = $month;
				}
				$rruleConfiguration ['BYMONTH'] = 'BYMONTH=' . implode (',', $byMonth);
			}
			if (count ($event->getByYearDay ()) > 0) {
				$byYearDay = Array ();
				foreach ($event->getByYearDay () as $yearday) {
					$byYearDay [] = $yearday;
				}
				$rruleConfiguration ['BYYEARDAY'] = 'BYYEARDAY=' . implode (',', $byYearDay);
			}
			if (count ($event->getByMonthDay ()) > 0) {
				$byMonthDay = Array ();
				foreach ($event->getByMonthDay () as $monthday) {
					$byMonthDay [] = $monthday;
				}
				$rruleConfiguration ['BYMONTHDAY'] = 'BYMONTHDAY=' . implode (',', $byMonthDay);
			}
			if (count ($event->getByWeekDay ()) > 0) {
				$byWeekDay = Array ();
				foreach ($event->getByWeekDay () as $weekday) {
					$byWeekDay [] = $weekday;
				}
				$rruleConfiguration ['BYWEEKDAY'] = 'BYWEEKDAY=' . implode (',', $byWeekDay);
			}
			$until = $event->getUntil ();
			if (is_object ($until) && $until->format ('%Y%m%d') > 19700101) {
				$eventEnd = $this->getEnd ();
				$offset = \TYPO3\CMS\Cal\Utility\Functions::strtotimeOffset ($eventEnd->getTime ());
				$eventEnd->subtractSeconds ($offset);
				$rruleConfiguration ['UNTIL'] = 'UNTIL=' . $until->format ('%Y%m%dT') . $eventEnd->format ('%H%M%SZ');
				$eventEnd->addSeconds ($offset);
			}
			$rrule = implode (';', $rruleConfiguration);
		}
		return strtoupper ($rrule);
	}
	function getRdateMarker(&$template, &$sims, &$rems, &$wrapped, $view) {
		$sims ['###RDATE###'] = '';
		if ($this->getRdateType () != '' && $this->getRdateType () != 'none' && $this->getRdate () != '') {
			$sims ['###RDATE###'] = 'RDATE;VALUE=' . strtoupper ($this->getRdateType () . ':' . $this->getRdate ());
		}
	}
	function getExdateMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###EXDATE###'] = '';
		$exceptionDates = Array ();
		$daySeconds = $this->getStart ()->getHour () * 3600 + $this->getStart ()->getMinute () * 60;
		$offset = $daySeconds - \TYPO3\CMS\Cal\Utility\Functions::strtotimeOffset ($this->getStart ()->getTime ());
		$exceptionEventStart = new \TYPO3\CMS\Cal\Model\CalDate ();
		foreach ($this->getExceptionEvents () as $exceptionEvent) {
			// if ($exceptionEvent->getFreq() == 'none') {
			$exceptionEventStart->copy ($exceptionEvent->getStart ());
			$exceptionEventStart->addSeconds ($offset);
			$exceptionDates [] = 'EXDATE:' . $exceptionEventStart->format ('%Y%m%dT%H%M%SZ');
			// }
		}
		
		if (count ($exceptionDates)) {
			$sims ['###EXDATE###'] = implode (chr (10), $exceptionDates);
		}
	}
	function getExruleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###EXRULE###'] = '';
		return; // multiple exrules are not supported
		$exceptionRules = Array ();
		foreach ($this->getExceptionEvents () as $exceptionEvent) {
			if ($exceptionEvent->getFreq () != 'none') {
				$exceptionRules .= $this->getRRule ($exceptionEvent);
			}
		}
		
		if (count ($exceptionRules)) {
			$sims ['###EXRULE###'] = 'EXRULE:' . implode (',', $exceptionRules);
		}
	}
	function getIcsFreqLabel($eventFreq) {
		$freq_type = '';
		switch ($eventFreq) {
			case 'year' :
				$freq_type = 'YEARLY';
				break;
			case 'month' :
				$freq_type = 'MONTHLY';
				break;
			case 'week' :
				$freq_type = 'WEEKLY';
				break;
			case 'day' :
				$freq_type = 'DAILY';
				break;
			case 'hour' :
				$freq_type = 'HOURLY';
				break;
			case 'minute' :
				$freq_type = 'MINUTELY';
				break;
			case 'second' :
				$freq_type = 'SECONDLY';
				break;
		}
		return $freq_type;
	}
	function getCruserNameMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$sims ['###CRUSER_NAME###'] = '';
		$feUser = $modelObj->findFeUser ($this->getCreateUserId ());
		if (is_array ($feUser)) {
			$this->initLocalCObject ();
			$this->local_cObj->setCurrentVal ($feUser [$this->conf ['view.'] [$view . '.'] ['event.'] ['cruser_name.'] ['db_field']]);
			$sims ['###CRUSER_NAME###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['cruser_name'], $this->conf ['view.'] [$view . '.'] ['event.'] ['cruser_name.']);
		}
	}
	function getCalendarTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CALENDAR_TITLE###'] = '';
		$calendarObject = &$this->getCalendarObject ();
		$this->initLocalCObject ($calendarObject->getValuesAsArray ());
		$this->local_cObj->setCurrentVal ($calendarObject->getTitle ());
		$sims ['###CALENDAR_TITLE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['calendar_title'], $this->conf ['view.'] [$view . '.'] ['event.'] ['calendar_title.']);
	}
	function getTopMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###TOP###'] = '';
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($this->getStart ()->getHour () + $this->getStart ()->getMinute () / 60 - $this->getStartOffset ());
		$sims ['###TOP###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['top'], $this->conf ['view.'] [$view . '.'] ['event.'] ['top.']);
	}
	function getStartOffset() {
		if (! isset ($this->startOffset)) {
			$dayStart = $this->conf ['view.'] ['day.'] ['dayStart']; // '0700'; // Start time for day grid
			while (strlen ($dayStart) < 6) {
				$dayStart .= '0';
			}
			$d_start = new \TYPO3\CMS\Cal\Model\CalDate ('01012000' . $dayStart);
			$this->startOffset = $d_start->getHour () + $d_start->getMinute () / 60;
		}
		return $this->startOffset;
	}
	function getLengthMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###LENGTH###'] = '';
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($this->getEnd ()->getTime () - $this->getStart ()->getTime ());
		$sims ['###LENGTH###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['length'], $this->conf ['view.'] [$view . '.'] ['event.'] ['length.']);
	}
	function getNow() {
		$now = new \TYPO3\CMS\Cal\Model\CalDate ();
		$now->setTZbyId ('UTC');
		return $now;
	}
	function getToday() {
		$today = new \TYPO3\CMS\Cal\Model\CalDate ();
		$today->setTZbyId ('UTC');
		$today->setHour (0);
		$today->setMinute (0);
		$today->setSecond (0);
		return $today;
	}
	function setSendOutInvitation($sendOut) {
		$this->sendOutInvitation = $sendOut;
	}
	function getSendOutInvitation() {
		return $this->sendOutInvitation;
	}
	function getCreatedMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CREATED###'] = 'CREATED:' . gmdate ('Ymd', $this->getCreationDate ()) . 'T' . gmdate ('His', $this->getCreationDate ()) . 'Z';
	}
	function getLastModifiedMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###LAST_MODIFIED###'] = 'LAST_MODIFIED:' . gmdate ('Ymd', $this->getTstamp ()) . 'T' . gmdate ('His', $this->getTstamp ()) . 'Z';
	}
	function getAjaxEditLink() {
		if ($this->isUserAllowedToEdit () && $this->conf ['view.'] ['enableAjax']) {
			return 'dragZones[\'dragZone' . $this->getUid () . '\'] = new CalEvent.dd.MyDragZone(' . '\'cal_event_' . $this->getUid () . '\',' . '{ddGroup: \'cal_event\',' . 'scroll: false,' . 'start_time:\'' . ($this->getStart ()->getHour () * 3600 + $this->getStart ()->getMinute () * 60) . '\',' . 'start_day:\'' . $this->getStart ()->format ('%Y%m%d') . '\',' . 'end_time:\'' . ($this->getEnd ()->getHour () * 3600 + $this->getEnd ()->getMinute () * 60) . '\',' . 'end_day:\'' . $this->getEnd ()->format ('%Y%m%d') . '\',' . 'uid:\'' . $this->getUid () . '\',' . 'eventType:\'' . $this->getType () . '\'});';
		}
	}
	function getCategoryIconMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		$arr = &$this->getCategories ();
		foreach ($arr as $cat) {
			$icon = $cat->getIcon ();
			if ($icon) {
				$search = array (
						'%%%CATICON%%%',
						'%%%CATTITLE%%%',
						'%%%EVENTTITLE%%%',
						'%%%STARTDATE%%%',
						'%%%STARTTIME%%%' 
				);
				
				$eventStart = $this->getStart ();
				// Startdate
				$this->local_cObj->setCurrentVal ($eventStart->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['dateFormat']));
				$startdate = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['startdate'], $this->conf ['view.'] [$view . '.'] ['event.'] ['startdate.']);
				
				// Starttime
				$this->local_cObj->setCurrentVal ($eventStart->format ($this->conf ['view.'] [$view . '.'] ['event.'] ['timeFormat']));
				$starttime = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['event.'] ['starttime'], $this->conf ['view.'] [$view . '.'] ['event.'] ['starttime.']);
				
				$replace = array (
						$icon,
						$cat->getTitle (),
						$this->getTitle (),
						$startdate,
						$starttime 
				);
				$sims ['###CATEGORY_ICON###'] .= str_replace ($search, $replace, $this->conf ['view.'] [$view . '.'] ['event.'] ['categoryIcon']);
			} else {
				$sims ['###CATEGORY_ICON###'] .= '&bull;';
			}
		}
	}
}

?>