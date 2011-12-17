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

require_once (t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm::extPath('cal').'controller/class.tx_cal_base_controller.php');

/**
 * Back controller for the calendar base.  Takes requests from the main
 * controller and starts processing in the appropriate calendar models by
 * utilizing TYPO3 services.
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_modelcontroller extends tx_cal_base_controller {
	
	var $todoSubtype = 'event';
	
	function tx_cal_modelcontroller(){
		$this->tx_cal_base_controller();
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$this->todoSubtype = $confArr['todoSubtype'];
	}
	
	function findEvent($uid, $type='', $pidList='', $showHiddenEvents=false, $showDeletedEvents=false, $getAllInstances=false, $disableCalendarSearchString=false, $disableCategorySearchString=false, $eventType='0,1,2,3') {
		if($uid==''){
			return;
		}
		$event = $this->find('cal_event_model', $uid, $type, 'event', $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
		return $event;
	}
	
	function findTodo($uid, $type='', $pidList='', $showHiddenEvents=false, $showDeletedEvents=false, $getAllInstances=false, $disableCalendarSearchString=false, $disableCategorySearchString=false, $eventType='0,1,2,3') {
		if($uid==''){
			return;
		}
		$event = $this->find('cal_event_model', $uid, $type, $this->todoSubtype, $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
		return $event;
	}
	
	function createEvent($type) {
		$event = $this->create('cal_event_model', $type, 'event');
		return $event;
	}
	
	function findAllEventInstances($uid, $type='', $pidList='', $showHiddenEvents=false, $showDeletedEvents=false, $getAllInstances=false, $disableCalendarSearchString=false, $disableCategorySearchString=false, $eventType='0,1,2,3') {
		if($uid==''){
			return;
		}
		$event_s = $this->find('cal_event_model', $uid, $type, 'event', $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
		return $event_s;
	}
	
	function saveEvent($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
		if(is_numeric($uid) && $uid != 0 && ($uid > 0)){
			return $service->updateEvent($uid);
		}
		return $service->saveEvent($pid);
	}
	
	function removeEvent($uid, $type) {
		$service = $this->getServiceObjByKey('cal_event_model', 'event', $type);

		if(is_numeric($uid) && $uid != 0 && ($uid > 0)){
			return $service->removeEvent($uid);
		}
		return;
	}
	
	function saveTodo($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_event_model', $this->todoSubtype, $type);
		if(is_numeric($uid) && $uid != 0 && ($uid > 0)){
			return $service->updateEvent($uid);
		}
		return $service->saveEvent($pid);
	}
	
	function removeTodo($uid, $type) {
		$service = $this->getServiceObjByKey('cal_event_model', $this->todoSubtype, $type);

		if(is_numeric($uid) && $uid != 0 && ($uid > 0)){
			return $service->removeEvent($uid);
		}
		return;
	}
	
	function saveExceptionEvent($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
		if(is_numeric($uid) && $uid != 0 && ($uid > 0)){
			return $service->updateExceptionEvent($uid);
		}
		return $service->saveExceptionEvent($pid);
	}
	
	function findAllTodoInstances($uid, $type='', $pidList='', $showHiddenEvents=false, $showDeletedEvents=false, $getAllInstances=false, $disableCalendarSearchString=false, $disableCategorySearchString=false, $eventType='4') {
		return $this->find('cal_event_model', $uid, $type, $this->todoSubtype, $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
	}
	
	function findLocation($uid, $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
		/* Look up an event with a specific ID inside the model */
		$location = $service->find($uid, $pidList);
		
		return $location;
	}
	
	function findAllLocations($type='',$pidList='') {

		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
		/* Look up an event with a specific ID inside the model */
		$locations = $service->findAll($pidList);
		
		return $locations;
	}
	
	function saveLocation($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->updateLocation($uid);
		}
		return $service->saveLocation($pid);
	}
	
	function removeLocation($uid, $type) {
		$service = $this->getServiceObjByKey('cal_location_model', 'location', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->removeLocation($uid);
		}
		return;
	}
	
	function findOrganizer($uid, $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);		
		/* Look up an event with a specific ID inside the model */
		$organizer = $service->find($uid, $pidList);	
		return $organizer;
	}
	
	function findCalendar($uid, $type='tx_cal_calendar', $pidList='') {
		if($uid==''){
			return;
		}
		if($type==''){
			$type='tx_cal_calendar';
		}
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);		
		/* Look up an event with a specific ID inside the model */
		$calendar = $service->find($uid, $pidList);	
		return $calendar;
	}
	
	function findAllCalendar($type='',$pidList='') {
		/* No key provided so return all events */
		$serviceName = 'cal_calendar_model';
		$categoryArrayToBeFilled = array();
		$calendar = array();

		if ($type == '') {
			
			$serviceChain='';
			
			$calendarFromService = array();
	
			/* Iterate over all classes providing the cal_model service */
			while (is_object($service = &t3lib_div::makeInstanceService($serviceName, 'calendar', $serviceChain))) {
				$calendar[$service->getServiceKey()] = $service->findAll($pidList);
				$serviceChain.=','.$service->getServiceKey();
			}
		}else{
			/* Gets the model for the provided service key */
			$service = &$this->getServiceObjByKey($serviceName, 'calendar', $type);
			/* Look up an event with a specific ID inside the model */
			$calendar[$type] = $service->findAll($pidList);
		}

		return $calendar;
	}
	
	function findAllOrganizer($type='',$pidList='') {

		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
		/* Look up an event with a specific ID inside the model */
		$organizer = $service->findAll($pidList);
		
		return $organizer;
	}
	
	function saveOrganizer($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->updateOrganizer($uid);
		}
		return $service->saveOrganizer($pid);
	}
	
	function removeOrganizer($uid, $type) {
		$service = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->removeOrganizer($uid);
		}
		return;
	}
	
	function saveCalendar($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->updateCalendar($uid);
		}
		return $service->saveCalendar($pid);
	}
	
	function removeCalendar($uid, $type) {
		$service = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->removeCalendar($uid);
		}
		return;
	}
	
	function saveCategory($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_category_model', 'category', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->updateCategory($uid);
		}
		return $service->saveCategory($pid);
	}
	
	function removeCategory($uid, $type) {
		$service = $this->getServiceObjByKey('cal_category_model', 'category', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->removeCategory($uid);
		}
		return;
	}
	
	function findAttendee($uid, $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
		/* Look up an attendee with a specific ID inside the model */
		$attendee = $service->find($uid, $pidList);
		
		return $attendee;
	}
	
	function findAllAttendees($type='',$pidList='') {

		/* Look up an attendee with a specific ID inside the model */
		$attendees = $service->findAllObjects('attendee', $type, $pidList);
		
		return $attendees;
	}
	
	function findEventAttendees($eventUid,$type='',$pidList='') {

		/* Gets the model for the provided service key */
		$attendees = $this->findAllObjects('attendee', $type, $pidList,'findEventAttendees', $eventUid);
		return $attendees;
	}
	
	function updateEventAttendees($eventUid,$type='',$pidList='') {
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_event_model', 'event', $type);
		/* Look up an attendee with a specific ID inside the model */
		$service->updateAttendees($eventUid);
	}
	
	function saveAttendee($uid, $type, $pid='') {
		$service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->updateAttendee($uid);
		}
		return $service->saveAttendee($pid);
	}
	
	function removeAttendee($uid, $type) {
		$service = $this->getServiceObjByKey('cal_attendee_model', 'attendee', $type);
		if(is_numeric($uid) && $uid != 0){
			return $service->removeAttendee($uid);
		}
		return;
	}
	
	function findEventsForDay(&$dateObject, $type='', $pidList='', $eventType='0,1,2,3') {
		$starttime = tx_cal_calendar::calculateStartDayTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndDayTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList, $eventType);
	}
	
	function findEventsForWeek(&$dateObject, $type='', $pidList='', $eventType='0,1,2,3') {
		$starttime = tx_cal_calendar::calculateStartWeekTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndWeekTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList, $eventType);
	}
	
	function findEventsForMonth(&$dateObject, $type='', $pidList='', $eventType='0,1,2,3') {
		$starttime = tx_cal_calendar::calculateStartMonthTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndMonthTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList, $eventType);
	}
	
	function findEventsForYear(&$dateObject, $type='', $pidList='', $eventType='0,1,2,3') {
		$starttime = tx_cal_calendar::calculateStartYearTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndYearTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList, $eventType);
	}
	
	function findEventsForList(&$startDateObject, &$endDateObject, $type='', $pidList='', $eventType='0,1,2,3', $additionalWhere='') {
		return $this->findAllWithin('cal_event_model', $startDateObject, $endDateObject, $type, 'event', $pidList, $eventType, $additionalWhere);
	}
	
	function findTodosForDay(&$dateObject, $type='', $pidList='', $eventType='4') {
		$starttime = tx_cal_calendar::calculateStartDayTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndDayTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, $this->todoSubtype, $pidList, $eventType);
	}
	
	function findTodosForWeek(&$dateObject, $type='', $pidList='', $eventType='4') {
		$starttime = tx_cal_calendar::calculateStartWeekTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndWeekTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, $this->todoSubtype, $pidList, $eventType);
	}
	
	function findTodosForMonth(&$dateObject, $type='', $pidList='', $eventType='4') {
		$starttime = tx_cal_calendar::calculateStartMonthTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndMonthTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, $this->todoSubtype, $pidList, $eventType);
	}
	
	function findTodosForYear(&$dateObject, $type='', $pidList='', $eventType='4') {
		$starttime = tx_cal_calendar::calculateStartYearTime($dateObject);
		$endtime = tx_cal_calendar::calculateEndYearTime($dateObject);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, $this->todoSubtype, $pidList, $eventType);
	}
	
	function findTodosForList(&$startDateObject, &$endDateObject, $type='', $pidList='', $eventType='4') {
		return $this->findAllWithin('cal_event_model', $startDateObject, $endDateObject, $type, $this->todoSubtype, $pidList, $eventType);
	}
	
	function findCurrentTodos($type='',$pidList='') {
		/* Gets the model for the provided service key */
		return $this->findAllObjects($this->todoSubtype, $type, $pidList, 'findCurrentTodos');
	}
	
	function findCategoriesForList($type='', $pidList='') {
		return $this->findAllCategories('cal_category_model', $type, $pidList);
	}
	
	function findEventsForIcs($type='', $pidList) {
		return $this->findAll('cal_event_model', $type, 'event', $pidList, '0,1,2,3');
	}
	
	function findEventsForRss(&$startDateObject, &$endDateObject, $type='', $pidList) {
		return $this->findAllWithin('cal_event_model', $startDateObject, $endDateObject, $type, 'event', $pidList, '0,1,2,3');
	}
	
	function findTodosForIcs($type='', $pidList) {
		return $this->findAll('cal_event_model', $type, 'event', $pidList, '4');
	}
	
	function findTodosForRss(&$startDateObject, &$endDateObject, $type='', $pidList) {
		return $this->findAllWithin('cal_event_model', $startDateObject, $endDateObject, $type, $this->todoSubtype, $pidList, '4');
	}
	
	function searchEvents( $type, $pidList, &$startDateObject, &$endDateObject, $searchword, $locationIds, $organizerIds) {
		return $this->_searchEvents('cal_event_model', $type, $pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, '0,1,2,3');
	}
	
	function searchTodos( $type, $pidList, &$startDateObject, &$endDateObject, $searchword, $locationIds, $organizerIds) {
		return $this->_searchEvents('cal_event_model', $type, $pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, '4');
	}
	
	function searchLocation( $type='', $pidList='', $searchword) {
		return $this->_searchAddress('cal_location_model', $type, 'location', $pidList, $searchword);
	}
	
	function searchOrganizer( $type='', $pidList='', $searchword) {
		return $this->_searchAddress('cal_organizer_model', $type, 'organizer', $pidList, $searchword);
	}
	
	function createTranslation($uid,$overlay,$serviceName,$type,$subtype){
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey($serviceName, $subtype, $type);		
		/* Look up an event with a specific ID inside the model */
		$service->createTranslation($uid, $overlay);
	}
	
	function findFeUser($uid) {
		$feUser = Array();
		if($uid==''){
			return $feUser;
		}
		$table = 'fe_users';
		$select = '*';
		$where = 'uid = '.intval($uid);
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$feUser = $row;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
		return $feUser;
	}

	/*
	 * Returns events from all calendar models or a specified model. 
	 * 
	 * @param		key		The optional service key to return events for.  If no key is given, all events are returned.
	 * @return		array		Array of events.
	 */
	function findAllWithin($serviceName, &$startDateObject, &$endDateObject, $type='', $subtype='', $pidList='', $eventType='', $additionalWhere='') {
//t3lib_div::debug('findallWithin Start:'.microtime());
		/* No key provided so return all events */
		if ($type == '') {		
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
	
			/* Iterate over all classes providing the cal_model service */
			while (is_object($service = t3lib_div::makeInstanceService($serviceName, $subtype, $serviceChain))) {
				$serviceChain.=','.$service->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $service->findAllWithin($startDateObject, $endDateObject, $pidList, $eventType, $additionalWhere);
							
				if(!empty($eventsFromService)){
					if(empty($events)){
						$events = $eventsFromService;
					}else{
						foreach ($eventsFromService as $eventdaykey => $eventday) {
							if(array_key_exists($eventdaykey,$events)==1){
								foreach($eventday as $eventtimekey => $eventtime) {
									if(array_key_exists($eventtimekey,$events[$eventdaykey])){
										$events[$eventdaykey][$eventtimekey] = $events[$eventdaykey][$eventtimekey] + $eventtime;
									} else {
										$events[$eventdaykey][$eventtimekey] = $eventtime;
									}
								}
							} else {
								$events[$eventdaykey] = $eventday;
							}
						}
						$events = $events + $eventsFromService;
					}
				}
				/* Flattens the array returned by the current model into the top level array */

			}
			ksort($events);
			$return = array();
			foreach($events as $key => $obj){
				ksort($obj);
				$return[$key] = $obj;
			}
//debug('findallWithin end:'.microtime());
			return $return;
		} 
		/* Operate on the provided key only */
		else {
			$events = array();
			
			/* Get the model represented by $key */
			$service = $this->getServiceObjByKey($serviceName, $subtype, $type);
			if(!is_object($service)){
				return $this->findAllWithin($service, $startDateObject, $endDateObject, '', $subtype, $pidList, $eventType, $additionalWhere);
			}
			/* Get all events from the model as an array */
			$events = $service->findAllWithin($startDateObject, $endDateObject, $pidList, $eventType, $additionalWhere);
			ksort($events);
			$return = array();
			foreach($events as $key => $obj){
				ksort($obj);
				$return[$key] = $obj;
			}
			return $return;
		}
	}
	
	/*
	 * Returns events from all calendar models or a specified model. 
	 * 
	 * @param		key		The optional service key to return events for.  If no key is given, all events are returned.
	 * @return		array		Array of events.
	 */
	function findAll($serviceName, $type, $subtype, $pidList, $eventType='0,1,2,3') {
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($service = t3lib_div::makeInstanceService($serviceName, $subtype, $serviceChain))) {
				$serviceChain.=','.$service->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $service->findAll($pidList, $eventTypes);
				if(!empty($eventsFromService)){
					if(empty($events)){
						$events = $eventsFromService;
					}else{
						foreach ($eventsFromService as $eventdaykey => $eventday) {
							if(array_key_exists($eventdaykey,$events)==1){
								foreach($eventday as $eventtimekey => $eventtime) {
									if(array_key_exists($eventtimekey,$events[$eventdaykey])){
										$events[$eventdaykey][$eventtimekey] = $events[$eventdaykey][$eventtimekey] + $eventtime;
									} else {
										$events[$eventdaykey][$eventtimekey] = $eventtime;
									}
								}
							} else {
								$events[$eventdaykey] = $eventday;
							}
						}
						$events = $events + $eventsFromService;
					}
				}

			}
			ksort($events);
			$return = array();
			foreach($events as $key => $obj){
				ksort($obj);
				$return[$key] = $obj;
			}
			return $return;
		} 
		/* Operate on the provided key only */
		else {
			$events = array();
			
			/* Get the model represented by $key */
			$service = $this->getServiceObjByKey($serviceName, $subtype, $type);
			/* Get all events from the model as an array */
			
			$events = $service->findAll($pidList, $eventTypes);

			return $events;
		}
	}
	
	function findCategory($uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey('cal_category_model', 'category', $type);		
		/* Look up an event with a specific ID inside the model */
		$category = $service->find($uid, $pidList);	
		return $category;
	}
	
	function findAllCategories($serviceName, $type, $pidList) {
		/* No key provided so return all events */
		$serviceName = 'cal_category_model';
		$categoryArrayToBeFilled = array();
		$categories = array();

		if ($type == '') {
			
			$serviceChain='';
			
			$categoriesFromService = array();
	
			/* Iterate over all classes providing the cal_model service */
			while (is_object($service = &t3lib_div::makeInstanceService($serviceName, 'category', $serviceChain))) {
				$service->findAll($pidList, $categoryArrayToBeFilled);
				$categories[$service->getServiceKey()] = $categoryArrayToBeFilled;
				$categoryArrayToBeFilled = array();
				$serviceChain.=','.$service->getServiceKey();
			}
		}else{
			/* Gets the model for the provided service key */
			$service = &$this->getServiceObjByKey($serviceName, 'category', $type);
			/* Look up an event with a specific ID inside the model */
			$service->findAll($pidList, $categoryArrayToBeFilled);
			$categories[$type] = $categoryArrayToBeFilled;
		}

		return $categories;
	}
	
	function _searchEvents($serviceName,$type, $pidList, &$startDateObject, &$endDateObject, $searchword, $locationIds='', $organizerIds='', $eventType='0,1,2,3') {

		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($service = t3lib_div::makeInstanceService($serviceName, 'event', $serviceChain))) {
				$serviceChain.=','.$service->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $service->search($pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, $eventType);
				if(!empty($eventsFromService)){
					if(empty($events)){
						$events = $eventsFromService;
					}else{
						foreach ($eventsFromService as $eventdaykey => $eventday) {
							if(array_key_exists($eventdaykey,$events)==1){
								foreach($eventday as $eventtimekey => $eventtime) {
									if(array_key_exists($eventtimekey,$events[$eventdaykey])){
										$events[$eventdaykey][$eventtimekey] = $events[$eventdaykey][$eventtimekey] + $eventtime;
									} else {
										$events[$eventdaykey][$eventtimekey] = $eventtime;
									}
								}
							} else {
								$events[$eventdaykey] = $eventday;
							}
						}
						$events = $events + $eventsFromService;
					}
				}

			}
			ksort($events);
			$return = array();
			foreach($events as $key => $obj){
				ksort($obj);
				$return[$key] = $obj;
			}
			return $return;
		} 
		/* Operate on the provided key only */
		else {
			$events = array();
			
			/* Get the model represented by $key */
			$service = $this->getServiceObjByKey($serviceName, 'event', $type);
			
			/* Get all events from the model as an array */
			
			$events = $service->search($pidList, $startDateObject, $endDateObject, $searchword, $locationIds, $organizerIds, $eventType);

			return $events;
		}
	}
	
	function _searchAddress($serviceName='', $type='', $subtype='', $pidList='', $searchword) {
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$addressFromService = array();
			/* Iterate over all classes providing the cal_model service */
			while (is_object($service = t3lib_div::makeInstanceService($serviceName, $subtype, $serviceChain))) {

				$serviceChain.=','.$service->getServiceKey();
				/* Gets all events from the current model as an array*/
				$addressFromService[] = $service->search($pidList, $searchword);
			}
			return $addressFromService;
		} 
		/* Operate on the provided key only */
		else {

			/* Get the model represented by $key */
			$service = $this->getServiceObjByKey($serviceName, $subtype, $type);
			
			/* Get all events from the model as an array */
			
			$addressFromService = $service->search($pidList, $searchword);

			return $addressFromService;
		}
	}
	

	
	/**
	 * Returns a specific event with a given serviceKey and UID.
	 *
	 * @param	string	The serviceKey to be searched in.
	 * @param	integer	The UID to look up.
	 * @return	event		The event object matching the serviceKey and UID.
	 */
	function find($serviceName, $uid, $type, $subtype, $pidList='', $showHiddenEvents=false, $showDeletedEvents=false, $getAllInstances=false, $disableCalendarSearchString=false, $disableCategorySearchString=false, $eventType='0,1,2,3') {
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey($serviceName, $subtype, $type);
		if(!is_object($service)){
			return tx_cal_functions::createErrorMessage('Missing or wrong parameter. The object you are looking for could not be found.', 'Please verify your URL parameters: tx_cal_controller[type] and tx_cal_controller[uid].');
		}	
		/* Look up an event with a specific ID inside the model */
		$event = $service->find($uid, $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
		return $event;
	}
	
	/**
	 * Returns a specific event with a given serviceKey and UID.
	 *
	 * @param	string	The serviceKey to be searched in.
	 * @param	integer	The UID to look up.
	 * @return	event		The event object matching the serviceKey and UID.
	 */
	function create($serviceName, $type, $subtype) {
		/* Gets the model for the provided service key */
		$service = $this->getServiceObjByKey($serviceName, $subtype, $type);
		if(!is_object($service)){
			return tx_cal_functions::createErrorMessage('Missing or wrong parameter. The object you are looking for could not be found.', 'Please verify your URL parameters: tx_cal_controller[type].');
		}		
		/* Look up an event with a specific ID inside the model */
		$event = $service->createEvent(null, false);
		return $event;
	}
	
	/**
	 * Helper function to return a service object with the given type, subtype, and serviceKey
	 *
	 * @param	string	The type of the service.
	 * @param	string	The subtype of the service.
	 * @param	string	The serviceKey.
	 * @return	object	The service object.
	 */
	function &getServiceObjByKey($type, $subtype, $key) {
		$serviceChain = '';
		/* Loop over all services providign the specified service type and subtype */
		while (is_object($obj = &t3lib_div::makeInstanceService($type, $subtype, $serviceChain))) {
			$serviceChain.=','.$obj->getServiceKey();
			/* If the key of the current service matches what we're looking for, return the object */
			if($key == $obj->getServiceKey()) {
				return $obj;
			}
		}
		
		//debug('No Service Object for model type '.$type.' with key '.$key.' found.', 'Error!');
	}
	
	/**
	 * Helper function to return a service object with the given type, subtype, and serviceKey
	 *
	 * @param	string	The type of the service.
	 * @param	string	The subtype of the service.
	 * @return	object	The service object.
	 */
	function getServiceTypes($type, $subtype) {
		$serviceChain = '';
		$returnArray = array();
		/* Loop over all services providign the specified service type and subtype */
		while (is_object($obj = t3lib_div::makeInstanceService($type, $subtype, $serviceChain))) {
			$serviceChain.=','.$obj->getServiceKey();
			/* If the key of the current service matches what we're looking for, return the object */
			$returnArray[] = $obj->getServiceKey();
		}
		return $returnArray;
		//debug('No Service Object for model type '.$type.' with key '.$key.' found.', 'Error!');
	}
	
	function findAllObjects($key, $type, $pidList, $functionTobeCalled = '', $paramsToBePassedOn='') {
		/* No key provided so return all X */
		$serviceName = 'cal_'.$key.'_model';
		$objects = array();
		if ($type == '') {
			
			$serviceChain='';			
			/* Iterate over all classes providing the cal_X_model service */
			while (is_object($service = &t3lib_div::makeInstanceService($serviceName, $key, $serviceChain))) {
				if($functionTobeCalled){
					if(method_exists($service, $functionTobeCalled)){
						$objects[$service->getServiceKey()] = $service->$functionTobeCalled($paramsToBePassedOn);
					}
				}else{
					$objects[$service->getServiceKey()] = $service->findAll($pidList);
				}
				$serviceChain.=','.$service->getServiceKey();
			}
		}else{
			/* Gets the model for the provided service key */
			$service = &$this->getServiceObjByKey($serviceName, $key, $type);
			/* Look up a objects with a specific ID inside the model */
			if($functionTobeCalled){
				if(method_exists($service, $functionTobeCalled)){
					$objects[$type] = $service->$functionTobeCalled($paramsToBePassedOn);
				}
			}else{
				$objects[$type] = $service->findAll($pidList);
			}
		}

		return $objects;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_modelcontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_modelcontroller.php']);
}

?>