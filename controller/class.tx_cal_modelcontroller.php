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
	
	function tx_cal_modelcontroller(){
	}
	
	function findEvent(&$conf, $uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		$event = $this->find('cal_event_model',$conf, $uid, $type, $pidList);
		return $event;
	}
	
	function saveEvent(&$rightsObj, &$conf, $pid='') {
		$model = $this->getServiceObjByKey('cal_event_model', '', $GLOBALS['HTTP_POST_VARS']['type']);
		if(is_numeric($GLOBALS['HTTP_POST_VARS']['uid'])){
			return $model->updateEvent($rightsObj, $conf, $GLOBALS['HTTP_POST_VARS']['uid']);
		}
		return $model->saveEvent($rightsObj, $conf, $pid);
	}
	
	function removeEvent(&$rightsObj, &$conf) {
		$model = $this->getServiceObjByKey('cal_event_model', '', $GLOBALS['HTTP_POST_VARS']['type']);
		if(is_numeric($GLOBALS['HTTP_POST_VARS']['uid'])){
			return $model->removeEvent($rightsObj, $conf, $GLOBALS['HTTP_POST_VARS']['uid']);
		}
		return;
	}
	
	function findLocation(&$conf, $uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_location_model', '', $type, $pidList);
	
		/* Look up an event with a specific ID inside the model */
		$location = $model->find($uid, $pidList);
		
		return $location;
	}
	
	function saveLocation(&$rightsObj, &$conf, $pid='') {
		$model = $this->getServiceObjByKey('cal_location_model', '', $GLOBALS['HTTP_POST_VARS']['type'], $pid);
		if(is_numeric($GLOBALS['HTTP_POST_VARS']['uid'])){
			return $model->updateLocation($rightsObj, $conf, $GLOBALS['HTTP_POST_VARS']['uid']);
		}
		return $model->saveLocation($rightsObj, $conf, $pid);
	}
	
	function removeLocation(&$rightsObj, &$conf) {
		$model = $this->getServiceObjByKey('cal_location_model', $GLOBALS['HTTP_POST_VARS']['type']);
		if(is_numeric($GLOBALS['HTTP_POST_VARS']['uid'])){
			return $model->removeLocation($rightsObj, $conf, $GLOBALS['HTTP_POST_VARS']['uid']);
		}
		return;
	}
	
	function findOrganizer(&$conf, $uid='', $type='', $pidList='') {
		if($uid==''){
			return;
		}
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey('cal_organizer_model', '', $type);		
		/* Look up an event with a specific ID inside the model */
		$organizer = $model->find($uid, $pidList);	
		return $organizer;
	}
	
	function saveOrganizer(&$rightsObj, &$conf, $pid='') {
		$model = $this->getServiceObjByKey('cal_organizer_model', $GLOBALS['HTTP_POST_VARS']['type'], $pid='');
		if(is_numeric($GLOBALS['HTTP_POST_VARS']['uid'])){
			return $model->updateOrganizer($rightsObj, $conf, $GLOBALS['HTTP_POST_VARS']['uid']);
		}
		return $model->saveOrganizer($rightsObj, $conf, $pid);
	}
	
	function removeOrganizer(&$rightsObj, &$conf) {
		$model = $this->getServiceObjByKey('cal_organizer_model', '', $GLOBALS['HTTP_POST_VARS']['type']);
		if(is_numeric($GLOBALS['HTTP_POST_VARS']['uid'])){
			return $model->removeOrganizer($rightsObj, $conf, $GLOBALS['HTTP_POST_VARS']['uid']);
		}
		return;
	}
	
	function findEventsForDay(&$conf, $timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartDayTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndDayTime($timestamp);
		return $this->findAllWithin('cal_event_model',$conf, $starttime, $endtime, $type, $pidList);
	}
	
	function findEventsForWeek(&$conf, $timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartWeekTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndWeekTime($timestamp);
		return $this->findAllWithin('cal_event_model',$conf, $starttime, $endtime, $type, $pidList);
	}
	
	function findEventsForMonth(&$conf, $timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartMonthTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndMonthTime($timestamp);
		return $this->findAllWithin('cal_event_model',$conf, $starttime, $endtime, $type, $pidList);
	}
	
	function findEventsForYear(&$conf, $timestamp='', $type='', $pidList='') {
		$starttime = tx_cal_calendar::calculateStartYearTime($timestamp);
		$endtime = tx_cal_calendar::calculateEndYearTime($timestamp);
		return $this->findAllWithin('cal_event_model',$conf, $starttime, $endtime, $type, $pidList);
	}
	
	function findEventsForList(&$conf, $timestamp='', $type='', $pidList='') {
		$starttime = strtotime($conf["view."]["list."]['starttime']);
		$endtime = strtotime($conf["view."]["list."]['endtime']);
		return $this->findAllWithin('cal_event_model',$conf, $starttime, $endtime, $type, $pidList);
	}
	
	function findEventsForIcs($conf=array(), $timestamp='', $type='', $pidList='') {

		return $this->findAll('cal_event_model',$conf, $type, $pidList);
	}
	
	
	
	
	
	/*
	 * Returns events from all calendar models or a specified model. 
	 * 
	 * @param		key		The optional service key to return events for.  If no key is given, all events are returned.
	 * @return		array		Array of events.
	 */
	function findAllWithin($service='',$conf=array(), $starttime='', $endtime='', $type='', $pidList='') {
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($model = t3lib_div::makeInstanceService($service, '', $serviceChain))) {
				$serviceChain.=','.$model->getServiceKey();
				/* Gets all events from the current model as an array*/
				$eventsFromService = $model->findAllWithin($conf, $starttime, $endtime, $pidList, $this->master_array);
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
				/* Flattens the array returned by the current model into the top level array */

			}
			ksort($events);
			return $events;
		} 
		/* Operate on the provided key only */
		else {
			$events = array();
			
			/* Get the model represented by $key */
			$model = $this->getServiceObjByKey($service, '', $type);
			
			/* Get all events from the model as an array */
			$model->findAllWithin($conf, $starttime, $endtime, $pidList, $this->master_array, $this->overlap_array);

			return $this->master_array;
		}
	}
	
	/*
	 * Returns events from all calendar models or a specified model. 
	 * 
	 * @param		key		The optional service key to return events for.  If no key is given, all events are returned.
	 * @return		array		Array of events.
	 */
	function findAll($service='',&$conf, $type='', $pidList='') {
		
		/* No key provided so return all events */
		if ($type == '') {
			
			$serviceChain='';
			$events = array();
			$eventsFromService = array();
		
			/* Iterate over all classes providing the cal_model service */
			while (is_object($model = t3lib_div::makeInstanceService($service, '', $serviceChain))) {
				$serviceChain.=','.$model->getServiceKey();
	
				/* Gets all events from the current model as an array*/
				$eventsFromService = $model->findAll($conf, $pidList);
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
			$model = $this->getServiceObjByKey($service, '', $type);
			
			/* Get all events from the model as an array */
			
			$events = $model->findAll($conf, $pidList);

			return $events;
		}
	}

	
	/*
	 * Returns a specific event with a given serviceKey and UID.
	 *
	 * @param	string	The serviceKey to be searched in.
	 * @param	integer	The UID to look up.
	 * @return	event		The event object matching the serviceKey and UID.
	 */
	function find($service, $conf=array(), $uid, $type, $pidList='') {
		/* Gets the model for the provided service key */
		$model = $this->getServiceObjByKey($service, '', $type, $pidList);
		
		/* Look up an event with a specific ID inside the model */
		$event = $model->find($conf, $uid, $pidList);
		
		return $event;
	}
	
	/*
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
			$serviceChain.=','.$obj->getServiceKey();				
			/* If the key of the current service matches what we're looking for, return the object */
			if($key == $obj->getServiceKey()) {
				return $obj;
			}
		}
		
		debug("No Service Object for model with key ".$key." found.", "Error!");
	}	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_modelcontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_modelcontroller.php']);
}

?>
