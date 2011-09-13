<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
* (http://evangelize.org). The WEC is developing TYPO3-based
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

require_once ('class.tx_cal_calendar.php');

/**
 * Back controller for the calendar base.  Takes requests from the main
 * controller and starts processing in the appropriate calendar models by
 * utilizing TYPO3 services.
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_modelcontroller {
	
	var $cObj;
	var $rightsObj;
	var $controller;
	var $prefixId = 'tx_cal_controller';
	
	function tx_cal_modelcontroller(&$cObj){
		$this->cObj = &$cObj;
		$this->controller = &$cObj->conf[$this->prefixId];
		$this->rightsObj = &$this->controller->rightsObj;
	}
	
	function findEvent($uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		$event = $this->find('cal_event_model', $uid, $type, 'event', $pidList);
		return $event;
	}
	
	function saveEvent($pid='') {
		$model = $this->getServiceObjByKey('cal_event_model', 'event', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->updateEvent($this->controller->piVars['uid']);
		}
		return $model->saveEvent($pid);
	}
	
	function removeEvent() {
		$model = $this->getServiceObjByKey('cal_event_model', 'event', $this->controller->piVars['type']);

		if(is_numeric($this->controller->piVars['uid'])){
			return $model->removeEvent($this->controller->piVars['uid']);
		}
		return;
	}
	
	function saveExceptionEvent($pid='') {
		$model = $this->getServiceObjByKey('cal_event_model', 'event', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->updateExceptionEvent($this->controller->piVars['uid']);
		}
		return $model->saveExceptionEvent($pid);
	}
	
	function findLocation($uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_location_model', 'location', $type);
		/* Look up an event with a specific ID inside the model */
		$location = $model->find($uid, $pidList);
		
		return $location;
	}
	
	function findAllLocations($type='',$pidList='') {

		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_location_model', 'location', $type);
		/* Look up an event with a specific ID inside the model */
		$locations = $model->findAll($pidList);
		
		return $locations;
	}
	
	function saveLocation($pid='') {
		$model = $this->getServiceObjByKey('cal_location_model', 'location', $this->controller->piVars['type'], $pid);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->updateLocation($this->controller->piVars['uid']);
		}
		return $model->saveLocation($pid);
	}
	
	function removeLocation() {
		$model = $this->getServiceObjByKey('cal_location_model', 'location', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->removeLocation($this->controller->piVars['uid']);
		}
		return;
	}
	
	function findOrganizer($uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);		
		/* Look up an event with a specific ID inside the model */
		$organizer = $model->find($uid, $pidList);	
		return $organizer;
	}
	
	function findCalendar($uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);		
		/* Look up an event with a specific ID inside the model */
		$calendar = $model->find($uid, $pidList);	
		return $calendar;
	}
	
	function findAllCalendar($type='',$pidList='') {

		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $type);
		/* Look up an event with a specific ID inside the model */
		$calendar = $model->findAll($pidList);
		
		return $calendar;
	}
	
	function findAllOrganizer($type='',$pidList='') {

		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $type);
		/* Look up an event with a specific ID inside the model */
		$organizer = $model->findAll($pidList);
		
		return $organizer;
	}
	
	function saveOrganizer($pid='') {
		$model = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $this->controller->piVars['type'], $pid='');
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->updateOrganizer($this->controller->piVars['uid']);
		}
		return $model->saveOrganizer($pid);
	}
	
	function removeOrganizer() {
		$model = $this->getServiceObjByKey('cal_organizer_model', 'organizer', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->removeOrganizer($this->controller->piVars['uid']);
		}
		return;
	}
	
	function saveCalendar($pid='') {
		$model = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->updateCalendar($this->controller->piVars['uid']);
		}
		return $model->saveCalendar($pid);
	}
	
	function removeCalendar() {
		$model = $this->getServiceObjByKey('cal_calendar_model', 'calendar', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->removeCalendar($this->controller->piVars['uid']);
		}
		return;
	}
	
	function saveCategory($pid='') {
		$model = $this->getServiceObjByKey('cal_category_model', 'category', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->updateCategory($this->controller->piVars['uid']);
		}
		return $model->saveCategory($pid);
	}
	
	function removeCategory() {
		$model = $this->getServiceObjByKey('cal_category_model', 'category', $this->controller->piVars['type']);
		if(is_numeric($this->controller->piVars['uid'])){
			return $model->removeCategory($this->controller->piVars['uid']);
		}
		return;
	}
	
	function findEventsForDay($timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartDayTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndDayTime($timestamp);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList);
	}
	
	function findEventsForWeek($timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartWeekTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndWeekTime($timestamp);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList);
	}
	
	function findEventsForMonth($timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartMonthTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndMonthTime($timestamp);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList);
	}
	
	function findEventsForYear( $timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartYearTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndYearTime($timestamp);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList);
	}
	
	function findEventsForList($timestamp='', $type='', $pidList='') {
		$starttime = strtotime($this->cObj->conf["view."]["list."]['starttime']);
		$endtime = strtotime($this->cObj->conf["view."]["list."]['endtime']);
		return $this->findAllWithin('cal_event_model', $starttime, $endtime, $type, 'event', $pidList);
	}
	
	function findCategoriesForList($timestamp='', $type='', $pidList='') {
		return $this->findAllCategories('cal_category_model', $type, $pidList);
	}
	
	function findEventsForIcs( $timestamp='', $type='', $pidList='') {
		return $this->findAll('cal_event_model', $type, 'event', $pidList);
	}
	
	function searchEvents( $type='', $pidList='') {
		return $this->_searchEvents('cal_event_model', $type, $pidList);
	}
	
	function searchLocation( $type='', $pidList='') {
		return $this->_searchAddress('cal_location_model', $type, 'location', $pidList);
	}
	
	function searchOrganizer( $type='', $pidList='') {
		return $this->_searchAddress('cal_organizer_model', $type, 'organizer', $pidList);
	}

	/*
	 * Returns events from all calendar models or a specified model. 
	 * 
	 * @param		key		The optional service key to return events for.  If no key is given, all events are returned.
	 * @return		array		Array of events.
	 */
	function findAllWithin($service='', $starttime='', $endtime='', $type='', $subtype='', $pidList='') {
//debug("findallWithin Start:".microtime());
		/* No key provided so return all events */
		if ($type == '') {		
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($model = t3lib_div::makeInstanceService($service, $subtype, $serviceChain))) {
				$model->setCObj($this->cObj);
				$serviceChain.=','.$model->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $model->findAllWithin($starttime, $endtime, $pidList);
				$events['legend'][$model->getCalNumber()] = $model->getCalLegendDescription();
				if(count($eventsFromService)>0){
					if(count($events)==0){
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
//debug("findallWithin end:".microtime());
			return $return;
		} 
		/* Operate on the provided key only */
		else {
			$events = array();
			
			/* Get the model represented by $key */
			$model = $this->getServiceObjByKey($service, '', $type);
			if(!is_object($model)){
				return $this->findAllWithin($service, $starttime, $endtime, '', $subtype, $pidList);
			}
			/* Get all events from the model as an array */
			$events = $model->findAllWithin($starttime, $endtime, $pidList);
			ksort($events);
			$return = array();
			foreach($events as $key => $obj){
				ksort($obj);
				$return[$key] = $obj;
			}
			$return['legend'][$model->getCalNumber()] = $model->getCalLegendDescription();
			return $return;
		}
	}
	
	/*
	 * Returns events from all calendar models or a specified model. 
	 * 
	 * @param		key		The optional service key to return events for.  If no key is given, all events are returned.
	 * @return		array		Array of events.
	 */
	function findAll($service='',$type='', $subtype='', $pidList='') {	
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($model = t3lib_div::makeInstanceService($service, $subtype, $serviceChain))) {
				$model->setCObj($this->cObj);
				$serviceChain.=','.$model->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $model->findAll($pidList);
				if(count($eventsFromService)>0){
					$events['legend'][$model->getCalNumber()] = $model->getCalLegendDescription();
					if(count($events)==0){
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
			$model = $this->getServiceObjByKey($service, $subtype, $type);
			/* Get all events from the model as an array */
			
			$events = $model->findAll($pidList);

			return $events;
		}
	}
	
	function findCategory($uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_category_model', 'category', $type);		
		/* Look up an event with a specific ID inside the model */
		$category = $model->find($uid, $pidList);	
		return $category;
	}
	
	function findAllCategories($service, $type,$pidList) {
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey($service, 'category', $type);
		/* Look up an event with a specific ID inside the model */
		$categories = $model->findAll($pidList);
		
		return $categories;
	}
	
	function _searchEvents($service='',$type='', $pidList='') {
		
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($model = t3lib_div::makeInstanceService($service, 'event', $serviceChain))) {
				$model->setCObj($this->cObj);
				$serviceChain.=','.$model->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $model->search($pidList);
				if(count($eventsFromService)>0){
					$events['legend'][$model->getCalNumber()] = $model->getCalLegendDescription();
					if(count($events)==0){
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
			return $events;
		} 
		/* Operate on the provided key only */
		else {
			$events = array();
			
			/* Get the model represented by $key */
			$model = $this->getServiceObjByKey($service, 'event', $type);
			
			/* Get all events from the model as an array */
			
			$events = $model->findAll($pidList);

			return $events;
		}
	}
	
	function _searchAddress($service='', $type='', $subtype='', $pidList='') {
		
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$addressFromService = array();
			/* Iterate over all classes providing the cal_model service */
			while (is_object($model = t3lib_div::makeInstanceService($service, $subtype, $serviceChain))) {
				$model->setCObj($this->cObj);

				$serviceChain.=','.$model->getServiceKey();
				/* Gets all events from the current model as an array*/
				$addressFromService[] = $model->search($pidList);
			}
			return $addressFromService;
		} 
		/* Operate on the provided key only */
		else {

			/* Get the model represented by $key */
			$model = $this->getServiceObjByKey($service, $subtype, $type);
			
			/* Get all events from the model as an array */
			
			$addressFromService = $model->findAll($pidList);

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
	function find($service, $uid, $type, $subtype, $pidList='') {
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey($service, $subtype, $type, $pidList);		
		/* Look up an event with a specific ID inside the model */
		$event = $model->find($uid, $pidList);
		
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
	function getServiceObjByKey($type, $subtype, $key) {
		$serviceChain = '';
		/* Loop over all services providign the specified service type and subtype */
		while (is_object($obj = t3lib_div::makeInstanceService($type, $subtype, $serviceChain))) {
			$obj->setCObj($this->cObj);
			$serviceChain.=','.$obj->getServiceKey();
			/* If the key of the current service matches what we're looking for, return the object */
			if($key == $obj->getServiceKey()) {
				return $obj;
			}
		}
		
		//debug("No Service Object for model type ".$type." with key ".$key." found.", "Error!");
	}
	
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_modelcontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_modelcontroller.php']);
}

?>
