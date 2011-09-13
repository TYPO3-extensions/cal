<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004 
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_phpicalendar_model.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_event_service extends tx_cal_base_service {
	
	var $location;
	var $calnumber = 1;
	var $descriptionFixForPhp4;

	
	function getCalNumber() { 
		return $this->calnumber; 
	}
	
	function setCalNumber($calnumber) { 
		$this->calnumber = $calnumber; 
	}
	
	/**
	 *  Gets the legend description.
	 *
	 *  @return		array		The legend array.
	 */
	function getCalLegendDescription() {
		return $this->descriptionFixForPhp4;
		$service = &$this->getCategoryService();
		return $service->callegenddescription;
	}
	 
	/**
	 *  Finds all events within a given range.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAllWithin($start_date, $end_date, $pidList) {
		// How to get the events
		// 1st get Calendar specified
		// 2nd get categories specified
		// 3rd get all related events
		// make an array out of the list, so we can handle it better
		
//		$linkIds = array();
//		if($this->conf['calendar']!=""){
//			$linkIds = split(",",$this->conf['calendar']);
//		}
		// Lets see if we shall display the public calendar too
//		if(in_array("public",$linkIds) || !$linkIds){
//			$includePublic = true;
//		}else{
//			$includePublic = false;
//		}
		$service = &$this->getCalendarService();
		$calendarSearchString = $service->getCalendarSearchString($pidList, true, $this->conf['calendar']?$this->conf['calendar']:'');
		
		$service = &$this->getCategoryService();
		$categorySearchString = $service->getCategorySearchString($pidList, true);

		// putting everything together
//		$additionalWhere = $categorySearchString.$calendarSearchString." AND tx_cal_event.pid IN (".$pidList.") AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0 AND (tx_cal_event.starttime>=".$start_date." OR tx_cal_event.endtime<".$end_date." OR ((tx_cal_event.freq!='none' OR tx_cal_event.freq!='') AND tx_cal_event.until <= ".$this->conf['getdate']."))";
		$additionalWhere = $calendarSearchString." AND tx_cal_event.pid IN (".$pidList.") AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0 AND (tx_cal_event.starttime>=".$start_date." OR tx_cal_event.endtime<".$end_date." OR tx_cal_event.freq!='none' OR tx_cal_event.freq!='')";
		
		// creating the arrays the user is allowed to see
//debug($categorySearchString);

		$categories = $service->getCategoryArray($pidList);	
		$this->descriptionFixForPhp4 = $service->callegenddescription;
		// creating events
		return $this->getEventsFromTable($categories, true, $additionalWhere, $this->getServiceKey(), $categorySearchString);
	}
	
	
	
	/**
	 * Search for events with an according category.uid
	 * @param	$categories			array of available categories
	 * @param	$includeRecurring	boolean	TRUE if recurring events should be included
	 * @param	$categoryIds		String	The category ids to search events for
	 * @param	$additionalWhere	String	Additional where string; will be added to the where-clause
	 * 
	 * @return	array				An array of tx_cal_phpcalendar_model events
	 */
	function getEventsFromTable(&$categories, $includeRecurring=false, $additionalWhere="", $serviceKey='', $categoryWhere=''){
		
		$events = array();
		
		$select = "tx_cal_calendar.uid AS calendar_uid, " .
				"tx_cal_calendar.owner AS calendar_owner, " .
				"tx_cal_event.*";
		$table = "tx_cal_event, tx_cal_calendar";
		$where = "tx_cal_calendar.uid = tx_cal_event.calendar_id".$additionalWhere;
		$orderBy = " tx_cal_event.start_date ASC, tx_cal_event.start_time ASC";
		$groupBy = "";
		if($categoryWhere!=''){
			$select .= 	", tx_cal_event_category_mm.uid_foreign AS category_uid " ;
			$table .= ", tx_cal_event_category_mm";
			$where = "tx_cal_event_category_mm.uid_local = tx_cal_event.uid".$categoryWhere.$additionalWhere;
			$groupBy = "category_uid, uid";
			$orderBy .= ", tx_cal_event_category_mm.sorting";
			$categoryIds = split(',',$this->conf['category']);
		}
		
		
		$limit = "";
//t3lib_div::debug($select);
//t3lib_div::debug($table);
//t3lib_div::debug($where);
//t3lib_div::debug($orderBy);
//t3lib_div::debug("SELECT ".$select." FROM ".$table." WHERE ".$where." GROUP BY ".$groupBy);

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where,$groupBy ,$orderBy,$limit);
		$lastday = '';
		$currentday = ' ';
		$first = true;
		$lastUid = "";
		$calendarService = $this->getCalendarService();
		$eventOwnerArray = $calendarService->getCalendarOwner();			
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($categoryWhere!='' && !in_array($row['category_uid'],$categoryIds)){
				continue;
			}
			if($row['uid']==$lastUid){
				$event->addCategory($categories[$row['uid']]);
				continue;	
			}
			$lastUid = $row['uid'];
			$row['event_owner'] = $eventOwnerArray[$row['calendar_uid']];
			$event = $this->createEvent($row, false);
			if(is_array($categories[$row['uid']])){
				foreach($categories[$row['uid']] as $cat){
					$event->addCategory($cat);
				}
			}
			$events_tmp = array();
			
			if($row['shared_user_cnt']>0){
				$select = "uid_foreign";
				$table = "tx_cal_event_shared_user_mm";
				$where = "uid_local = ".$row['uid'];
				$sharedUserResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
				while ($sharedUserRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sharedUserResult)) {
					$event->addSharedUser($sharedUserRow['uid_foreign']);
				}
			}
			
			// get exception events:
			$where = "AND tx_cal_event.uid = ".$event->getUid()." AND tx_cal_exception_event_mm.tablenames='tx_cal_exception_event_group'";
			$orderBy = "";
			$groupBy = "";
			$limit = "";	
			$ex_events_group = array();
			$result3 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("tx_cal_exception_event_group.*","tx_cal_event","tx_cal_exception_event_mm","tx_cal_exception_event_group",$where,$groupBy ,$orderBy,$limit);
			while ($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result3)) {
				$event->addExceptionGroupId($row3['uid']);
				$where = "AND tx_cal_exception_event_group.uid = ".$row3['uid'];
				$result4 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("tx_cal_exception_event.*","tx_cal_exception_event_group","tx_cal_exception_event_group_mm","tx_cal_exception_event",$where,$groupBy ,$orderBy,$limit);
				while ($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result4)) {
					$ex_event = $this->createEvent($row4, true);
					$ex_events_group[] = $this->recurringEvent($ex_event);

				}
			}
			$where = "AND tx_cal_event.uid = ".$event->getUid()." AND tx_cal_exception_event_mm.tablenames='tx_cal_exception_event'";
			$orderBy = "";//"tx_cal_exception_event.start_time ASC";
			$groupBy = "";
			$limit = "";
			$result2 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("tx_cal_exception_event.*","tx_cal_event","tx_cal_exception_event_mm","tx_cal_exception_event",$where,$groupBy ,$orderBy,$limit);
			while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
				$event->addExceptionSingleId($row2['uid']);
				$ex_event = $this->createEvent($row2, true);				
				$ex_events_group[] = $this->recurringEvent($ex_event);
			}

			if(!$includeRecurring){
				$events_tmp[date("Ymd",$event->getStartDate())][date("Hi",$event->getStartTime())][$event->getUid()] = $event;
			}else{
				$events_tmp = $this->recurringEvent($event);
			}
			
			foreach($ex_events_group as $ex_events){
				$this->removeEvents($events_tmp, $ex_events);
			}		
			if(!empty($events)){
				$this->mergeEvents($events,$events_tmp);
			}else{
				$events = $events_tmp;
			}
		}
		
//		if($categoryWhere!=''){
//			$eventsWithoutCategory = $this->getEventsFromTable($categories, $includeRecurring, $additionalWhere, $serviceKey);
//			if(!empty($eventsWithoutCategory)){
//				$this->mergeEvents($eventsWithoutCategory,$events);
//			}
//		}
//debug($events);
		return $events;
	}

	/**
	 *  Finds all events.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAll($pidList='') {		
		// How to get the events
		// 1st get Calendar specified
		// 2nd get categories specified
		// 3rd get all related events
		// make an array out of the list, so we can handle it better
		
		$linkIds = array();
		if($this->conf['calendar']!=""){
			$linkIds = split(",",$this->conf['calendar']);
		}
		// Lets see if we shall display the public calendar too
		if(in_array("public",$linkIds) || !$linkIds){
			$includePublic = true;
		}else{
			$includePublic = false;
		}
		$calendarService = &$this->getCalendarService();
		$idArray = $calendarService->getIdsFromTable($this->conf['calendar'],$pidList, $includePublic, false, true);

		// Check the results
		if(empty($idArray)){
			// No calendar ids specified for this user -> show default
			$calendarIds = "";
		}else if(!empty($linkIds)){
			// compair the allowed ids with the ids available and retrieve the intersects
			$calendarIds = array_intersect($idArray,$linkIds);
			if(empty($calendarIds)){
				// No intersects -> show default
				$calendarIds = "";
			}else{
				// create a string for the query
				$calendarIds = $this->arrayToCommaseparatedString($calendarIds);
			}
		}else{
			$calendarIds = $this->arrayToCommaseparatedString($idArray);
		}
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid_local", "tx_cal_calendar_user_group_mm", "");
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid_local'];
		}
		$idString = $this->arrayToCommaseparatedString($ids);
		if($idString!=""){
			$calendarSearchString = "tx_cal_calendar.uid NOT IN (".$idString.") AND";
		}
		
		$linkIds = split(",",$this->conf['category']);
		// categories specified? show only those categories
		$categoryService = $this->getCategoryService();
//		$idArray = $categoryService->getCategoryArray(false);
//		// Check the results
//		if(empty($idArray)){
//			// No category ids specified for this user -> show default
//			$categoryIds = "";
//		}else{
//			// compair the allowed ids with the ids available and retrieve the intersects
//			$categoryIds = array_intersect($idArray,$linkIds);
//			if(empty($categoryIds)){
//				// No intersects -> show default
//				$categoryIds = "";
//			}else{
//				// create a string for the query
//				$categoryIds = $this->arrayToCommaseparatedString($categoryIds);
//			}
//		}
//		if($categoryIds==""){
//			$categorySearchString = "";
//		}else{
//			$categorySearchString = " AND tx_cal_category.uid IN (".$categoryIds.")";
//		}
		$categorySearchString = $categoryService->getCategorySearchString($pidList, $includePublic);

		// putting everything together
		$additionalWhere = " AND ".$calendarSearchString." tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0";
		if($pidList===""){
			
		}else{
			$additionalWhere .= ' AND tx_cal_event.pid IN ('.$pidList.') ';
		}
		
		if($this->conf['view']=='ics'){
			$includeRecurring = false;
		}else{
			$includeRecurring = true;
		}
		
		// creating the arrays the user is allowed to see
		$categories = $categoryService->getCategoryArray($pidList);
		$this->descriptionFixForPhp4 = $categoryService->callegenddescription;
		// creating events
		return $this->getEventsFromTable($categories, $includeRecurring, $additionalWhere, $this->getServiceKey(), $categorySearchString);
	}
	
	function createEvent($row, $isException){
		$tx_cal_phpicalendar_model = &t3lib_div::makeInstanceClassName("tx_cal_phpicalendar_model");
		$event = &new $tx_cal_phpicalendar_model($this->controller, $row, $isException, $this->getServiceKey());	
		return $event;	
	}
	
	/**
	 *  Finds a single event.
	 *
	 *  @return		object			The event represented by the model.
	 */	
	function find($uid, $pidList, $showHiddenEvents=false, $showDeletedEvents=false) {

		$categoryService = &$this->getCategoryService();
		$categories = $categoryService->getCategoryArray($pidList);
//debug($categories);

		$calendarService = &$this->getCalendarService();
		$calendarSearchString = $calendarService->getCalendarSearchString($pidList, true, $this->conf['calendar']?$this->conf['calendar']:'');
		
		// categories specified? show only those categories
		$categorySearchString = $categoryService->getCategorySearchString($pidList,true);

		// putting everything together
		if($showHiddenEvents){
			$additionalWhere = $calendarSearchString." AND tx_cal_event.uid=".$uid;
		}else{
			$additionalWhere = $calendarSearchString." AND tx_cal_event.uid=".$uid." AND tx_cal_event.hidden = 0";
		}
		if(!$showDeletedEvents){
			$additionalWhere .= " AND tx_cal_event.deleted = 0";
		}
		$events = $this->getEventsFromTable($categories, true, $additionalWhere, $this->getServiceKey(), $categorySearchString, $showHiddenEvents);
		
		if($this->conf['getdate']){
			foreach($events as $date=>$time){
				foreach($time as $eventArray){
					foreach($eventArray as $event){
						if($event->getStartDate()<=strtotime($this->conf['getdate']) && $event->getEndDate()>=strtotime($this->conf['getdate']) && $event->getUid==$uid){
							return $event;
						}
					}
				}
			}
		}
		if(empty($events))
			return;
		if($this->conf['getdate'] && $events[$this->conf['getdate']]){
			$event = array_pop(array_pop($events[$this->conf['getdate']]));
			return $event;
		}else{
			return array_pop(array_pop(array_pop($events)));
		}
	}

	
	function saveEvent($pid){
		$crdate = time();
		$insertFields = array("pid" => $pid, "tstamp" => $crdate, "crdate" => $crdate);
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		
		if(!$insertFields['calendar_id'] && $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault']){
			$insertFields['calendar_id'] = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
		}

		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$table = "tx_cal_event";
						
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		if($this->rightsObj->isAllowedToCreateEventNotify()){
			if($this->controller->piVars['notify_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', strip_tags($this->controller->piVars['notify_ids'])),$uid,"fe_users");
			}
		}else if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateNotify.']['uidDefault']){
			$idArray = split(',', $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateNotify.']['uidDefault']);
			if($this->conf['rights.']['create.']['event.']['addFeUserToNotify']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",$idArray,$uid,"fe_users");
		}else if($this->conf['rights.']['create.']['event.']['addFeUserToNotify']){
			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateNotify.']['uidDefault']),$uid,"fe_users");
		}
		
		if($this->rightsObj->isAllowedToCreateEventException()){
			if($this->controller->piVars['single_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_mm",split(',', strip_tags($this->controller->piVars['single_exception_ids'])),$uid,"tx_cal_exception_event");
			}
			if($this->controller->piVars['group_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_group_mm",split(',', strip_tags($this->controller->piVars['group_exception_ids'])),$uid,"tx_cal_exception_event_group");
			}
		}
		if($this->rightsObj->isAllowedToCreateEventCategory()){
			$this->insertIdsIntoTableWithMMRelation("tx_cal_event_category_mm",split(",",strip_tags($this->controller->piVars['category_ids'])),$uid,"");
		}else{
			$this->insertIdsIntoTableWithMMRelation("tx_cal_event_category_mm",array($this->conf['rights.']['create.']['event.']['fields']['allowedToCreateCategory.']['uidDefault']),$uid,"");
		}

		if($this->conf['rights.']['create.']['event.']['addFeUserToShared']){
			$this->insertIdsIntoTableWithMMRelation("tx_cal_event_shared_user_mm",array($this->rightsObj->getUserId()),$uid,"fe_users");
		}

		$this->unsetPiVars();
		$insertFields['uid'] = $uid;
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		$notificationService =& getNotificationService();
		$notificationService->notify($insertFields, $this->conf['view.']['event.']['notify.']);
		clearCache();
	}
	
	function updateEvent($uid){
		$insertFields = array("tstamp" => time());
		//TODO: Check if all values are correct
		$config = $this->conf['calendar'];
		$this->conf['calendar'] = intval($this->controller->piVars['calendar_id']);
		$event = $this->find($uid, $this->conf['pidList'], true, true);
		$this->conf['calendar'] = $config;
		
		$this->retrievePostData($insertFields);
		
		// Creating DB records
//		$insertFields['relation_cnt'] = sizeof($this->controller->piVars['user_ids']);
		$table = "tx_cal_event";
		$where = "uid = ".$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
		
		
		$cal_user_ids = array();
		$where = " AND tx_cal_event.uid=".$uid." AND tx_cal_fe_user_category_mm.tablenames='fe_users' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0";
		$orderBy = "";
		$groupBy = "";
		$limit = "";
//		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("fe_users.*","tx_cal_event","tx_cal_fe_user_category_mm","fe_users",$where,$groupBy ,$orderBy,$limit);
//		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {	
//			array_push($cal_user_ids,$row['uid']);
//		}
		if($this->rightsObj->isAllowedToEditEventCategory()){
			$table = "tx_cal_event_category_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			$this->insertIdsIntoTableWithMMRelation($table,split(',', strip_tags($this->controller->piVars['category_ids'])),$uid,"");
		}
//		if($this->rightsObj->isAllowedToEditEventCreator()){
//			$table = "tx_cal_fe_user_category_mm";
//			$where = "uid_local = ".$uid;
//			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);	
//			if($this->controller->piVars['user_ids']!=''){
//				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",split(',', $this->controller->piVars['user_ids']),$uid,"fe_users");
//			}	
//			else if($this->controller->piVars['group_ids']!=''){
//				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",split(',', $this->controller->piVars['group_ids']),$uid,"fe_groups");
//			}
//			else{
//				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",array($this->conf['anonymousUserUid']),$uid,"fe_users");
//			}
//		}
		
		if($this->rightsObj->isAllowedToEditEventNotify()){
			if($this->controller->piVars['notify_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', strip_tags($this->controller->piVars['notify_ids'])),$uid,"fe_users");
			}
		}else if($this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditNotify.']['uidDefault']){
			$idArray = split(',', $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditNotify.']['uidDefault']);
			if($this->conf['rights.']['edit.']['event.']['addFeUserToNotify']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",$idArray,$uid,"fe_users");
		}else if($this->conf['rights.']['edit.']['event.']['addFeUserToNotify']){
			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditNotify.']['uidDefault']),$uid,"fe_users");
		}
		
//		if($this->rightsObj->isAllowedToEditEventNotify()){
//			$table = "tx_cal_fe_user_event_monitor_mm";
//			$where = "uid_local = ".$uid;
//			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
//			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', $this->controller->piVars['notify_ids']),$uid,"fe_users");
//		}
		
		if($this->rightsObj->isAllowedToEditEventException()){
			$table = "tx_cal_exception_event_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			if($this->controller->piVars['single_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_mm",split(',', strip_tags($this->controller->piVars['single_exception_ids'])),$uid,"tx_cal_exception_event");
			}
			$table = "tx_cal_exception_event_group_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			if($this->controller->piVars['group_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_group_mm",split(',', strip_tags($this->controller->piVars['group_exception_ids'])),$uid,"tx_cal_exception_event_group");
			}
		}
		$this->_notify($event->getValuesAsArray(),$insertFields);
		$this->unsetPiVars();
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		clearCache();
	}
	
	function removeEvent($uid){
		if($this->rightsObj->isAllowedToDeleteEvents()){
			
			$config = $this->conf['calendar'];
			$this->conf['calendar'] = intval($this->controller->piVars['calendar_id']);
			$event = $this->find($uid, $this->conf['pidList'], true, true);
			$this->conf['calendar'] = $config;
			
			$updateFields = array("tstamp" => time(), "deleted" => 1);
			$table = "tx_cal_event";
			$where = "uid = ".$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
			
			require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
			$fields = array('tstamp' => $updateFields['tstamp'], "delete" => 1);
			$this->_notify($event->getValuesAsArray(),$fields);
			clearCache();
			$this->unsetPiVars();
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateHidden.']['default'] && !$this->rightsObj->isAllowedToEditEventHidden() && !$this->rightsObj->isAllowedToCreateEventHidden()){
			$hidden = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateHidden.']['default'];
		}else if($this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditHidden.']['default'] && !$this->rightsObj->isAllowedToEditEventHidden() && !$this->rightsObj->isAllowedToCreateEventHidden()){
			$hidden = $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditHidden.']['default'];
		}else if($this->controller->piVars['hidden'] == "true" && ($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden())){
			$hidden = 1;
		}
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToCreateEventCalendar()){
			if($this->controller->piVars['calendar_id']!=""){
				$insertFields['calendar_id'] = intval($this->controller->piVars['calendar_id']);
			}else if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault']){
				$insertFields['calendar_id'] = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
			}else{
				$insertFields['calendar_id'] = ""; //TODO: Set the calendar_id to some value
			}
		}else if($this->rightsObj->isAllowedToCreateEventCalendar()){
			if($this->controller->piVars['calendar_id']!=""){
				$insertFields['calendar_id'] = intval($this->controller->piVars['calendar_id']);
			}else if($this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditCalendar.']['uidDefault']){
				$insertFields['calendar_id'] = $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditCalendar.']['uidDefault'];
			}else{
				$insertFields['calendar_id'] = ""; //TODO: Set the calendar_id to some value
			}
		}
		
		if($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()){
			if($this->controller->piVars['event_start_day']!=''){
				$time = array();
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_start_day']), $time);
				$insertFields['start_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}else{
				return;
			}
			if($this->controller->piVars['event_start_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_start_time']), $time);
				$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
			}
			if($this->controller->piVars['event_end_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_end_day']), $time);
				$insertFields['end_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['event_end_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_end_time']), $time);
				$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
			}
		}
		if($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()){
			$insertFields['title'] = strip_tags($this->controller->piVars['title']);
		}
		
		if($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$insertFields['organizer'] = strip_tags($this->controller->piVars['organizer']);
			if($this->controller->piVars['organizer_id']!=''){
				$insertFields['organizer_id'] = intval($this->controller->piVars['organizer_id']);
			}
		}
		if($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()){
			$insertFields['location'] = strip_tags($this->controller->piVars['location']);
			if($this->controller->piVars['location_id']!=''){
				$insertFields['location_id'] = intval($this->controller->piVars['location_id']);
			}
		}
		if($this->controller->piVars['description']!='' && ($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription())){
			$insertFields['description'] = $this->cObj->removeBadHTML($this->controller->piVars['description'],$this->conf);
		}
		if($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()){
			if($this->controller->piVars['frequency_id']!=''){
				$insertFields['freq'] = intval($this->controller->piVars['frequency_id']);
			}
			if($this->controller->piVars['by_day']!=''){
				$insertFields['byday'] = strip_tags($this->controller->piVars['by_day']);
			}
			if($this->controller->piVars['by_monthday']!=''){
				$insertFields['bymonthday'] = strip_tags($this->controller->piVars['by_monthday']);
			}
			if($this->controller->piVars['by_month']!=''){
				$insertFields['bymonth'] = strip_tags($this->controller->piVars['by_month']);
			}
			if($this->controller->piVars['until']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['until']), $time);
				$insertFields['until'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['count']!=''){
				$insertFields['cnt'] = intval($this->controller->piVars['count']);
			}
			if($this->controller->piVars['interval']!=''){
				$insertFields['intrval'] = intval($this->controller->piVars['interval']);
			}
		}
	}
	
	function insertIdsIntoTableWithMMRelation($mm_table,$idArray,$uid,$tablename){
		foreach($idArray as $foreignid){
			if(is_numeric ($foreignid)){
				$insertFields = array("uid_local"=>$uid, "uid_foreign" => $foreignid, "tablenames" =>$tablename);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($mm_table,$insertFields);
			}
		}
	}
	
	function search($pidList=''){
		$service = &$this->getCategoryService();
		$categoryIds = $this->arrayToCommaseparatedString($service->getCategoryArray($pidList));
		$sw = strip_tags($this->controller->piVars['query']);
		$start_day = intval($this->controller->piVars['start_day']);
		$end_day = intval($this->controller->piVars['end_day']);
		
		$events=array();
		$additionalSearch = "";
		if($sw!=""){
			$additionalSearch = $this->searchWhere($sw);
		}
		
		$linkIds = $this->conf['calendar']?$this->conf['calendar']:'';

		// Lets see if we shall display the public calendar too
		if(!$linkIds || in_array("public",split(',',$linkIds))){
			$includePublic = true;
		}else{
			$includePublic = false;
		}
		$categorySearchString = $service->getCategorySearchString($pidList, $includePublic);
		$calService = &$this->getCalendarService();
		$calendarSearchString = $calService->getCalendarSearchString($pidList, $includePublic, $linkIds);

		if($start_day>0){
			$day_array2 = array();
			ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $start_day, $day_array2);
			$day = $day_array2[3];
			$month = $day_array2[2];
			$year = $day_array2[1];
			$starttime = mktime(0,0,0,$month,$day,$year);
			$startDaySearchString = " AND start_date>=".$starttime;
		}
		if($end_day>0){
			$day_array2 = array();
			ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $end_day, $day_array2);
			$day = $day_array2[3];
			$month = $day_array2[2];
			$year = $day_array2[1];
			$endtime = mktime(0,0,0,$month,$day,$year);
			$endDaySearchString = " AND end_date<=".$endtime;
		}
		
		if($this->controller->piVars['location_ids']){
			$locationSearchString = " AND location_id in (".strip_tags($this->controller->piVars['location_ids']).")";
		}

		// putting everything together
		$additionalWhere = $calendarSearchString.$startDaySearchString.$endDaySearchString.$locationSearchString." AND tx_cal_event.pid IN (".$pidList.") AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0 ".$additionalSearch;
		return $this->getEventsFromTable($service->getCategoryArray($pidList), true, $additionalWhere, '', $categorySearchString);
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->conf["view."]["search."]["searchEventFieldList"], 'tx_cal_event');
		return $where;
	}
	
	/**
	 * This function looks, if the event is a recurring event
	 * and creates the recurrings events for a given time.
	 * The starting and ending dates are calculated from the conf 
	 * array ('gedate' and 'view').
	 * 
	 * @param		$event	object		Instance of this class (tx_cal_model)
	 */
	function recurringEvent($event){

		$master_array = array();
		$uid_counter = 0;
		$except_dates 	= array();
		$except_times 	= array();
		$first_duration = TRUE;
		if($event->getCount()!=0){
			$count 			=  $event->getCount();
		}else{
			$count 			= 1000;
		}
		$valarm_set 	= FALSE;
		$attendee		= array();
		$organizer		= array();

		$start_time			= $event->getStartHour();
		$end_time			= $event->getEndHour();
		$start_date			= $event->getStartDate();
		$end_date			= $event->getEndDate();
		

		$summary 			= $event->getSummary();
		$start_unixtime 	= $event->getStarttime();
		$the_duration		= $event->getDuration();
		$rrule_array		= $event->getRecurringRule();
		$description		= $event->getDescription();
		$url				= $event->getUrl();
		$valarm_description = $event->getVAlarmDescription();
		$end_unixtime		= $event->getEndtime();
		if($end_unixtime==0){
			$end_unixtime = $start_unixtime;
		}
		$recurrence_id		= $event->getRecurrance();
		$uid				= $event->getUid();
		$class				= $event->getClass();
		$location			= $event->getLocation();
		$until				= $event->getUntil();
		$bymonth			= $event->getByMonth();
		$byday				= $event->getByDay();
		$bymonthday			= $event->getByMonthDay();
		$byweek				= $event->getByWeekDay();
		$byweekno 			= $event->getByWeekNo();
		$byminute			= $event->getByMinute();
		$byhour				= $event->getByHour();
		$bysecond			= $event->getBySecond();
		$byyearday			= $event->getByYearDay();
		$bysetpos			= $event->getBySetPos();
		$wkst				= $event->getWkst();
		$number				= $event->getInterval();
		
		$current_view = $this->conf['view'];
		$getdate = $this->conf['getdate'];
		
		// what date we want to get data for (for day calendar)
		if (!isset($getdate) || $getdate == '') $getdate = date('Ymd');
		$day_array2 = array();
		preg_match ("/([0-9]{4})([0-9]{2})([0-9]{2})/", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
				
		if (!isset($url)) $url = '';
		if (!isset($type)) $type = '';

		// Handle DURATION
		if (!isset($end_unixtime) && isset($the_duration)) {
			$end_unixtime 	= $start_unixtime + $the_duration;
			$end_time 	= date ('Hi', $end_unixtime);
		}
			
		// CLASS support
		if (isset($class)) {
			if ($class == 'PRIVATE') {
				$summary ='**PRIVATE**';
				$description ='**PRIVATE**';
			} elseif ($class == 'CONFIDENTIAL') {
				$summary ='**CONFIDENTIAL**';
				$description ='**CONFIDENTIAL**';
			}
		}	 
		
		// make sure we have some value for $uid
		if (!isset($uid)) {
			$uid = $uid_counter;
			$uid_counter++;
			$uid_valid = false;
		} else {
			$uid_valid = true;
		}

		if ($uid_valid && isset($processed[$uid]) && isset($recurrence_id['date'])) {

			$old_start_date = $processed[$uid][0];
			$old_start_time = $processed[$uid][1];

			if ($recurrence_id['value'] == 'DATE') $old_start_time = '-1';
				$start_date_tmp = $recurrence_id['date'];

			if (isset($master_array[date("Ymd",$start_date_tmp)][$old_start_time][$uid])) {
				unset($master_array[date("Ymd",$start_date_tmp)][$old_start_time][$uid]);  // SJBO added $uid twice here
				if (sizeof($master_array[date("Ymd",$start_date_tmp)][$old_start_time]) == 0) {
					unset($master_array[date("Ymd",$start_date_tmp)][$old_start_time]);
				}
			}
			
			$write_processed = false;
		} else {
			$write_processed = true;
		}	
		$mArray_begin = gmmktime (0,0,0,12,21,($this_year - 1));
		$mArray_end = gmmktime (0,0,0,1,12,($this_year + 1));	

		if (isset($start_time) && isset($end_time)) {
			// Mozilla style all-day events or just really long events
			if (($end_time - $start_time) > 2345 || $end_time==$start_time) {
				$alldayStart = $start_date;
				$alldayEnd = ($start_date + 60*60*24);
			}
		}
		if (isset($start_unixtime,$end_unixtime) && (date('Ymd',$start_unixtime) == date('Ymd',$end_unixtime))==true && $start_time == $end_time) {
			$event->setSpansDay(1);
			$bleed_check = (($start_unixtime - $end_unixtime) < (60*60*24)) ? '-1' : '0';
		} else {
			$event->setSpansDay(0);
			$bleed_check = 0;
		}
		if (isset($start_time) && $start_time != '') {
			$time = array();
			$time2 = array();
			preg_match ('/([0-9]{2})([0-9]{2})/', $start_time, $time);
			preg_match ('/([0-9]{2})([0-9]{2})/', $end_time, $time2);
			if (isset($start_unixtime) && isset($end_unixtime)) {
				$length = $end_unixtime - $start_unixtime;
			} else {
				$length = ($time2[1]*60+$time2[2]) - ($time[1]*60+$time[2]);
			}	
			$drawKey = drawEventTimes($start_time, $end_time, $this->conf['view.']['day.']['gridLength']);
			$time3 = array(); 
			preg_match ('/([0-9]{2})([0-9]{2})/', $drawKey['draw_start'], $time3);
			$hour = $time3[1];
			$minute = $time3[2];
		}

		// RECURRENCE-ID Support
		if (isset($recurrence_d)) {		
			$recurrence_delete["$recurrence_d"]["$recurrence_t"] = $uid;
		}			
		// handle single changes in recurring events
		// Maybe this is no longer need since done at bottom of parser? - CL 11/20/02
		if ($uid_valid && $write_processed) {
			if (!isset($hour)) $hour = 00;
			if (!isset($minute)) $minute = 00;
			$processed[$uid] = array($start_date,($hour.$minute), $type);
		}
		// Handling of the all day events
		if ((isset($alldayStart) && $alldayStart != '')) {
			$start = $alldayStart;
			if ($event->getSpansDay()) {
				//$alldayEnd = $end_unixtime;
			}
			if (isset($alldayEnd)) {
				$end = $alldayEnd;
			} else {
				$end = strtotime('+1 day', $start);
			}
			// Changed for 1.0, basically write out the entire event if it starts while the array is written.
			if (($start < $mArray_end) && ($start < $end)) {

				while (($start != $end) && ($start < $mArray_end)) {
					$start_date2 = date('Ymd', $start);
					$master_array[$start_date2][('-1')][$uid]= $event;
					$start = strtotime('+1 day', $start);

				}
				if (!$write_processed) $master_array[date("Ymd",$start_date)]['-1'][$uid]['exception'] = true;
			}
		}	
		// Handling regular events
		if ((isset($start_time) && $start_time != '') && (!isset($alldayStart) || $alldayStart == '')) {

			if (($bleed_check == '-1')) {//($end_time >= $bleed_time) && 
				$start_tmp = strtotime(date('Ymd',$start_unixtime));
				$end_date_tmp = date('Ymd',$end_unixtime);
				while ($start_tmp <= $end_unixtime) {
					$start_date_tmp = date('Ymd',$start_tmp);
					if ($start_tmp == $start_date) {
//						$hour = "-1";
//						$minute = "";
						$time_tmp = $hour.$minute;
						$start_time_tmp = $start_time;
					} else {
						$time_tmp = '0000';
						$start_time_tmp = '0000';
					}
					if ($start_date_tmp == $end_date_tmp) {
						$end_time_tmp = $end_time;
					} else {
						$end_time_tmp = '2400';
						$display_end_tmp = $end_time;
					}
					$master_array[$start_date_tmp][$time_tmp][$uid] = $event;
					$start_tmp = strtotime('+1 day',$start_tmp);
				}
			} else {
				if ($bleed_check == '-1') {
					$display_end_tmp = $end_time;
					$end_time_tmp1 = '2400';	
				}
				if (!isset($end_time_tmp1)) $end_time_tmp1 = $end_time;
				// This if statement should prevent writing of an excluded date if its the first recurrance - CL
				if (!in_array($start_date, $except_dates)) {
					if($event->getEndHour()){
						$master_array[date("Ymd",$start_date)][($hour.$minute)][$uid] = $event;
					}else{
						$master_array[date("Ymd",$start_date)][('-1')][$uid] = $event;
					}
					if (!$write_processed) $master_array[date("Ymd",$start_date)][($hour.$minute)][$uid]['exception'] = true;
				}
			}
		}
		// Handling of the recurring events, RRULE
		if (isset($rrule_array) && is_array($rrule_array)) {

			if (isset($alldayStart) && $alldayStart != '') {
				$hour = '-';
				$minute = '1';
				$rrule_array['START_DAY'] = $alldayStart;
				$rrule_array['END_DAY'] = $alldayEnd;
				$rrule_array['END'] = 'end';
				$recur_start = $alldayStart;
				$start_date = $alldayStart;
				if (isset($alldayEnd)) {
					$diff_allday_days = tx_cal_calendar::dayCompare($alldayEnd, $alldayStart);
				 } else {
					$diff_allday_days = 1;
				}
			} else {
				$rrule_array['START_DATE'] = $start_date;
				$rrule_array['START_TIME'] = $start_time;
				$rrule_array['END_TIME'] = $end_time;
				$rrule_array['END'] = 'end';
			}
		
			$start_date_time = $start_date;
			$this_month_start_time = strtotime($this_year.$this_month.'01');
	
			if ($current_view == 'year'){// || ($save_parsed_cals == 'yes' && !$is_webcal)) {
				$start_range_time = strtotime($this_year.'-01-01 -2 weeks');
				$end_range_time = strtotime($this_year.'-12-31 +2 weeks');
			} else if ($current_view == 'list') {
				$start_range_time = strtotime($this->conf["view."]["list."]['starttime']);
				$end_range_time = strtotime($this->conf["view."]["list."]['endtime']);
			} else {
				$start_range_time = strtotime('-1 month -2 day', $this_month_start_time);
				$end_range_time = strtotime('+2 month +2 day', $this_month_start_time);
			}
			if($event->getEndHour()){
				$recur = $master_array[date("Ymd",$start_date)][($hour.$minute)][$uid]->getRecurringRule();
			}else{
				$recur = $master_array[date("Ymd",$start_date)][('-1')][$uid]->getRecurringRule();
			}
			// Modify the COUNT based on BYDAY
			if ((is_array($byday)) && !empty($byday) && (isset($count))) {
				$blah = sizeof($byday);
				$count = ($count / $blah);
				unset ($blah);
			}
			if (!isset($number)){
				$number = 1;
			}

			// if $until isn't set yet, we set it to the end of our range we're looking at		
			if (!isset($until) || $until=='' || $until==0) {
				$until = $end_range_time; 
		
			}
			if (!isset($abs_until)){
				$abs_until = date('YmdHis', $end_range_time);
			}
			$end_date_time = $until;
			$start_range_time_tmp = $start_range_time;
			$end_range_time_tmp = $end_range_time;

			// If the $end_range_time is less than the $start_date_time, or $start_range_time is greater
			// than $end_date_time, we may as well forget the whole thing
			// It doesn't do us any good to spend time adding data we aren't even looking at
			// this will prevent the year view from taking way longer than it needs to

			if ($end_range_time_tmp >= $start_date_time && $start_range_time_tmp <= $end_date_time) {
				// if the beginning of our range is less than the start of the item, we may as well set it equal to it
				if ($start_range_time_tmp < $start_date_time) $start_range_time_tmp = $start_date_time;
				if ($end_range_time_tmp > $end_date_time) $end_range_time_tmp = $end_date_time;
	
				// initialize the time we will increment
				$next_range_time = $start_range_time_tmp;
		
				// FIXME: This is a hack to fix repetitions with $interval > 1 
				if ($count > 1 && $number > 1) $count = 1 + ($count - 1) * $number; 
				$count_to = 0;
				$freq_type = $rrule_array['FREQ'];
				// start at the $start_range and go until we hit the end of our range.

				while ($freq_type > 0 && ($next_range_time >= $start_range_time_tmp) && ($next_range_time <= $end_range_time_tmp) && ($count_to != $count)) {
					$func = $freq_type.'Compare';
					$diff = tx_cal_calendar::$func($next_range_time, $start_date, $this->conf["view."]['weekStartDay']);
					if ($diff < $count) {
						if ($diff % $number == 0) {
							$interval = $number;
							switch ($rrule_array['FREQ']) {
								case 'day':
									$next_date_time = $next_range_time;
									$recur_data[] = $next_date_time;
									break;
								case 'week':
									// Populate $byday with the default day if it's not set.
									if (empty($byday)) {
										//$test = tx_cal_controller::getDaysOfWeekReallyShort();
										$daysofweek = $this->getDaysOfWeekShort();
										$byday[] = strtoupper(substr($daysofweek[date('w', $start_date_time)], 0, 2));//
									}
									if (is_array($byday)) {
										foreach($byday as $day) {
											$day = tx_cal_calendar::two2threeCharDays(strtoupper($day));									
											$next_date_time = strtotime($day,$next_range_time);// + (12 * 60 * 60);
											// Since this renders events from $next_range_time to $next_range_time + 1 week, I need to handle intervals
											// as well. This checks to see if $next_date_time is after $dayStart (i.e., "next week"), and thus
											// if we need to add $interval weeks to $next_date_time.
											if ($next_date_time > strtotime($this->conf["view."]['weekStartDay'], $next_range_time) && $interval > 1) {
												$next_date_time = strtotime('+'.($interval - 1).' '.$freq_type, $next_date_time);
											}
											$recur_data[] = $next_date_time;
										}
									}
									break;
								case 'month':
									if (empty($bymonth)){
										$bymonth = array(1,2,3,4,5,6,7,8,9,10,11,12);
									}
									$next_range_time = strtotime(date('Y-m-01', $next_range_time));
									$next_date_time = $next_range_time;
									if(empty($bymonthday) && empty($byday)){
										$bymonthday = array(date('d', $event->getStarttime()));
									}

									if (isset($bymonthday) && !empty($bymonthday) && ((empty($byday)))) {
										foreach($bymonthday as $day) {
											if ($day < 0) $day = ((date('t', $next_range_time)) + ($day)) + 1;
											$year = date('Y', $next_range_time);
											$month = date('m', $next_range_time);
											if (checkdate($month,$day,$year)) {
												$next_date_time = gmmktime(0,0,0,$month,$day,$year);
												$recur_data[] = $next_date_time;
											}
										}
									} elseif (is_array($byday)) {
										$testarray = array(1 => "first",2 => "second",3 => "third",4 => "fourth",5 => "fifth");
										foreach($byday as $day) {
											$day = strtoupper($day);
											$byday_arr = array();
											ereg ('([-\+]{0,1})?([0-9]{1})?([A-Z]{2})', $day, $byday_arr);
											//Added for 2.0 when no modifier is set
											if ($byday_arr[2] != '') {
												$nth = $byday_arr[2]-1;
											} else {
												$nth = 0;
											}
											$on_day = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]));
											$on_day_num = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]),false);
											if ((isset($byday_arr[1])) && ($byday_arr[1] == '-')) {
												$last_day_tmp = date('t',$next_range_time);

												// This supports MONTHLY where BYDAY and BYMONTH are both set
												if(empty($bymonthday)){
													$bymonthday = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
												}
												foreach($bymonthday as $day) {
													$year 	= gmdate('Y', $next_range_time);
													$month 	= gmdate('m', $next_range_time);

													if (checkdate($month,$day,$year)) {
														$next_date_time = gmmktime(0,0,0,$month,$day,$year);
														$daday = strtolower(gmdate("D", $next_date_time));
														$month = intval($month);
														$diff = $next_date_time - strtotime($testarray[$byday_arr[2]]." last ".$on_day,(gmmktime(0,0,0,$month,$last_day_tmp,$year)));
														if ($daday == $on_day && in_array($month, $bymonth) && ($diff == 0 || $diff == 3600)) {
															$recur_data[] = $next_date_time;
														}
													}
												}
												
											} elseif (isset($bymonthday) && (!empty($bymonthday))) {
												// This supports MONTHLY where BYDAY and BYMONTH are both set
												foreach($bymonthday as $day) {
													$year 	= date('Y', $next_range_time);
													$month 	= date('m', $next_range_time);
													if (checkdate($month,$day,$year)) {
														$next_date_time = gmmktime(0,0,0,$month,$day,$year);
														$daday = strtolower(strftime("%a", $next_date_time));
														if ($daday == $on_day && in_array($month, $bymonth)) {
															$recur_data[] = $next_date_time;
														}
													}
												}
											} elseif ((isset($byday_arr[1])) && ($byday_arr[1] != '-')) {
												$next_date_time = strtotime($on_day.' +'.$nth.' week', $next_range_time);
												$month = date('m', $next_date_time);
												if (in_array($month, $bymonth)) {
													$recur_data[] = $next_date_time;
												}
											}
											$next_date = date('Ymd', $next_date_time);
										}
									}
									break;
								case 'year':
									if (empty($bymonth)) {
										$m = date('m', $start_date_time);
										$bymonth = array($m);
									}	
									foreach($bymonth as $month) {
										// Make sure the month & year used is within the start/end_range.
										if ($month < date('m', $next_range_time)) {
											$year = date('Y', strtotime('+1 years', $next_range_time));
										} else {
											$year = date('Y', $next_range_time);
										}

										if (!empty($byday)) {
											$checkdate_time = gmmktime(0,0,0,$month,1,$year);
											foreach($byday as $day) {
												ereg ('([-\+]{0,1})?([0-9]{1})?([A-Z,a-z]{2})', $day, $byday_arr);
												if ($byday_arr[2] != '') {
													$nth = $byday_arr[2]-1;
												} else {
													$nth = 0;
												}
												$on_day = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]));
												$on_day_num = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]));
												if ($byday_arr[1] == '-') {
													$last_day_tmp = date('t',$checkdate_time);
													$checkdate_time = strtotime(date('Y-m-'.$last_day_tmp, $checkdate_time));
													$last_tmp = (date('w',$checkdate_time) == $on_day_num) ? '' : 'last ';
													$next_date_time = strtotime($last_tmp.$on_day.' -'.$nth.' week', $checkdate_time);
												} else {															
													$next_date_time = strtotime($on_day.' +'.$nth.' week', $checkdate_time);
												}
											}
										} else {
											$day 	= date('d', $start_date_time);
											$next_date_time = gmmktime(0,0,0,$month,$day,$year);
										}
										$recur_data[] = $next_date_time;
									}
										$byyearday_arr = array();
										//TODO: implement the possibility to define bymonthday and byday
										$yearday = date("Ymd",$event->getStartdate());
										ereg ('([-\+]{0,1})?([0-9]{1,3})', $yearday, $byyearday_arr);
										if ($byyearday_arr[1] == '-') {
											$ydtime = gmmktime(0,0,0,12,31,$this_year);
											$yearnum = $byyearday_arr[2] - 1;
											$next_date_time = strtotime('-'.$yearnum.' year', $ydtime);
										} else {
											$ydtime = gmmktime(0,0,0,1,1,$this_year);
											$yearnum = $byyearday_arr[2] - 1;
								
											$next_date_time = strtotime('+1 year', $next_date_time);
										}
										$recur_data[] = $next_date_time;

									break;
								default:
									// anything else we need to end the loop
									$next_range_time = $end_range_time_tmp + 100;
									$count_to = $count;
							}
						} else {
							$interval = 1;
						}
						$next_range_time = strtotime('+'.$interval.' '.$freq_type, $next_range_time);
					} else {
						// end the loop because we aren't going to write this event anyway
						$count_to = $count;
					}
					// use the same code to write the data instead of always changing it 5 times						
					if (isset($recur_data) && is_array($recur_data)) {
						$recur_data_hour = @substr($start_time,0,2);
						$recur_data_minute = @substr($start_time,2,2);
						foreach($recur_data as $recur_data_time) {
							$recur_data_year = date('Y', $recur_data_time);
							$recur_data_month = date('m', $recur_data_time);
							$recur_data_day = date('d', $recur_data_time);
							$recur_data_date = $recur_data_year.$recur_data_month.$recur_data_day;
							if (($recur_data_time > $start_date_time) && ($recur_data_time <= $end_date_time) && ($count_to != $count) && !in_array($recur_data_date, $except_dates)) {
								if (isset($alldayStart) && $alldayStart != '') {
									$start_time2 = $recur_data_time;
									$end_time2 = strtotime('+'.$diff_allday_days.' days', $recur_data_time);
									while ($start_time2 < $end_time2) {
										$start_date2 = date('Ymd', $start_time2);
										$master_array[$start_date2][('-1')][$uid] = $event;//array ('event_text' => $summary, 'description' => $description, 'location' => $location, 'organizer' => serialize($organizer), 'attendee' => serialize($attendee), 'calnumber' => $calnumber, 'calname' => $actual_calname, 'url' => $url, 'status' => $status, 'class' => $class, 'recur' => $recur );
										$start_time2 = strtotime('+1 day', $start_time2);
									}
								} else {
									$start_unixtime_tmp = mktime($recur_data_hour,$recur_data_minute,0,$recur_data_month,$recur_data_day,$recur_data_year);
									$end_unixtime_tmp = $start_unixtime_tmp + $length;
									if (($bleed_check == '-1')) { //($end_time >= $bleed_time) && 
										$start_tmp = strtotime(date('Ymd',$start_unixtime_tmp));
										$end_date_tmp = date('Ymd',$end_unixtime_tmp);
										while ($start_tmp < $end_unixtime_tmp) {
											$start_date_tmp = date('Ymd',$start_tmp);
											if ($start_date_tmp == $recur_data_year.$recur_data_month.$recur_data_day) {
												$time_tmp = $hour.$minute;
												$start_time_tmp = $start_time;
											} else {
												$time_tmp = '0000';
												$start_time_tmp = '0000';
											}
											if ($start_date_tmp == $end_date_tmp) {
												$end_time_tmp = $end_time;
											} else {
												$end_time_tmp = '2400';
												$display_end_tmp = $end_time;
											}
											
											// Let's double check the until to not write past it
											$until_check = $start_date_tmp.$time_tmp.'00';
											if ($abs_until > $until_check) {
												$new_event = $event->cloneEvent();
												$new_event->setStarttime($start_unixtime_tmp);
												if($event->getEndtime()!="0"){
													$new_event->setEndtime($end_unixtime_tmp);
												}else{
													$new_event->setEndtime($new_event->getStarttime());
												}
												$new_event->setDisplayEnd($display_end_tmp);
												$new_event->setOverlap(0);
												$new_event->setClass($class);
												$new_event->setSpansDay(true);
												
												if($new_event->getEndHour()){
													$master_array[$start_date_tmp][$time_tmp][$uid] = $new_event;
												}else{
													$master_array[$start_date_tmp][-1][$uid] = $new_event;
												} 
											}
											$start_tmp = strtotime('+1 day',$start_tmp);
										}
									} else {
										if ($bleed_check == '-1') {
											$display_end_tmp = $end_time;
											$end_time_tmp1 = '2400';
												
										}
										if (!isset($end_time_tmp1)) $end_time_tmp1 = $end_time;
									
										// Let's double check the until to not write past it
										$until_check = $recur_data_date.$hour.$minute.'00';
										if ($abs_until > $until_check) {
										  	$new_event = $event->cloneEvent();
											
											$new_event->setStarttime($start_unixtime_tmp);
											if($event->getEndtime()>0){
												$new_event->setEndtime($end_unixtime_tmp);
											}else{
												$new_event->setEndtime($new_event->getStarttime());
											}
											$new_event->setDisplayEnd($display_end_tmp);
											$new_event->setOverlap(0);
											$new_event->setClass($class);
											$new_event->setSpansDay(true);

											if($new_event->getEndHour()){
												$master_array[$recur_data_date][($hour.$minute)][$uid] = $new_event;
											}else{
												$master_array[$recur_data_date][-1][$uid] = $new_event;
											}
										}
										
									}
								}
							}
						}
						unset($recur_data);
					}
				}
			}
		}

		// This should remove any exdates that were missed.
		// Added for version 0.9.5
		if (is_array($except_dates)) {
			foreach ($except_dates as $key => $value) {
				$time = $except_times[$key];
				unset($master_array[date("Ymd",$value)][$time][$uid]);
				if (count($master_array[date("Ymd",$value)][$time]) < 1) {
					unset($master_array[date("Ymd",$value)][$time]);
					if (count($master_array[date("Ymd",$value)]) < 1) {
						unset($master_array[date("Ymd",$value)]);	
					}
				}
			}
		}
		return $master_array;
	}
	
	/**
	 * This function merges an array of events with another array of events.
	 * The structure is: [date][time][event]
	 * @param	$events		array where the events should be added into
	 * @param	$events_tmp	array which is supposed to be merged
	 */
	function mergeEvents(&$events, &$events_tmp){
		foreach ($events_tmp as $event_tmp_key => $event_tmp) {
			if(array_key_exists($event_tmp_key,$events)==1){
				foreach($event_tmp as $event_tmp_timekey => $event_tmp_time) {
					if(array_key_exists($event_tmp_timekey,$events[$event_tmp_key])){
						$events[$event_tmp_key][$event_tmp_timekey] = $events[$event_tmp_key][$event_tmp_timekey] + $event_tmp_time;
					} else {
						$events[$event_tmp_key][$event_tmp_timekey] = $event_tmp_time;
					}
				}
			} else {
				$events[$event_tmp_key] = $event_tmp;
			}
		}
	}
	
	/**
	 * This function removes an array of events from another array of events.
	 * The structure is: [date][time][event]
	 * @param	$events		array where the events should be deleted from
	 * @param	$events_tmp	array which is supposed to be deleted
	 */
	function removeEvents(&$events_tmp, &$ex_events){
		foreach ($events_tmp as $event_tmp_key => $event_tmp) {
			if(array_key_exists($event_tmp_key,$ex_events)==1){
				array_splice($events_tmp[$event_tmp_key], 0);
			}
		}
	}
	
	/**
	 * This function returns an array of weekdays (english)
	 */
	function getDaysOfWeekShort() {
		return array ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
	}
	
	function saveExceptionEvent($pid){

		$crdate = time();
		$insertFields = array("pid" => $pid, "tstamp" => $crdate, "crdate" => $crdate);
		//TODO: Check if all values are correct
		
		if($this->controller->piVars['exception_start_day']!=''){
			$time = array();
			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['exception_start_day']), $time);
			$insertFields['start_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
		}else{
			return;
		}
		if($this->controller->piVars['exception_start_time']!=''){
			preg_match ('/([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['exception_start_time']), $time);
			$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
		}
		if($this->controller->piVars['exception_end_day']!=''){
			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['exception_end_day']), $time);
			$insertFields['end_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
		}
		if($this->controller->piVars['exception_end_time']!=''){
			preg_match ('/([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['exception_end_time']), $time);
			$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
		}
		
		if($this->controller->piVars['exception_title']!=''){
			$insertFields['title'] = strip_tags($this->controller->piVars['exception_title']);
		}

		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		if($insertFields['title']==""){
			$insertFields['title'] = strip_tags($this->controller->piVars['exception_start_day'])." exception";
		}
		$table = "tx_cal_exception_event";
				
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_mm",array($uid),intval($this->controller->piVars['event_uid']),"tx_cal_exception_event");
		$this->unsetPiVars();
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		clearCache();
	}
	
	function gmstrtotime ($s)
	{
	   $t = strtotime($s);
	   $zone = intval(date("O"))/100;
	   $t += $zone*60*60;
	   return $t;
	}
	
	function unsetPiVars(){
		unset($this->controller->piVars['hidden']);
		unset($this->controller->piVars['_TRANSFORM_description']);
		unset($this->controller->piVars['uid']);
		unset($this->controller->piVars['calendar_id']);
		unset($this->controller->piVars['calendar']);
		unset($this->controller->piVars['switch_calendar']);
		unset($this->controller->piVars['type']);
		unset($this->controller->piVars['event_start_day']);
		unset($this->controller->piVars['event_start_time']);
		unset($this->controller->piVars['event_start_minutes']);
		unset($this->controller->piVars['event_start_hour']);
		unset($this->controller->piVars['event_end_day']);
		unset($this->controller->piVars['event_end_time']);
		unset($this->controller->piVars['event_end_minutes']);
		unset($this->controller->piVars['event_end_hour']);
		unset($this->controller->piVars['title']);
		unset($this->controller->piVars['organizer']);
		unset($this->controller->piVars['organizer_id']);
		unset($this->controller->piVars['location']);
		unset($this->controller->piVars['location_id']);
		unset($this->controller->piVars['description']);
		unset($this->controller->piVars['frequency_id']);
		unset($this->controller->piVars['by_day']);
		unset($this->controller->piVars['by_monthday']);
		unset($this->controller->piVars['by_month']);
		unset($this->controller->piVars['until']);
		unset($this->controller->piVars['count']);
		unset($this->controller->piVars['interval']);
		unset($this->controller->piVars['category_ids']);
		unset($this->controller->piVars['category_ids_selected']);
		unset($this->controller->piVars['user_ids']);
		unset($this->controller->piVars['group_ids']);
		unset($this->controller->piVars['single_exception_ids']);
		unset($this->controller->piVars['group_exception_ids']);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_event_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_event_service.php']);
}
?>