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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_event_service extends tx_cal_base_service {
	
	var $location;
	var $calnumber = 1;

	var $starttime = 0;
	var $endtime = 1924905600;//gmmktime(0,0,0,12,31,2030);
	
	function tx_cal_event_service(){
		$this->tx_cal_base_service();
	}
	
	function getCalNumber() { 
		return $this->calnumber; 
	}
	
	function setCalNumber($calnumber) { 
		$this->calnumber = $calnumber; 
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

		#used for displaying months in dayview
        $this->starttime = $start_date - $this->conf['view.'][$this->conf['view'].'.']['startPointCorrection'];
		$this->endtime = $end_date + $this->conf['view.'][$this->conf['view'].'.']['endPointCorrection'];

		$this->starttime -= strtotimeOffset($this->starttime);
		$this->endtime -= strtotimeOffset($this->endtime);

		if($this->endtime == $this->starttime){
			$this->endtime += 86400;
		}
        
        #calculate to day timestamp
        $this->starttime = getDayFromTimestamp($this->starttime);
        $this->endtime = getDayFromTimestamp($this->endtime);
		
		
		$service = &$this->getCalendarService();
		$calendarSearchString = $service->getCalendarSearchString($pidList, true, $this->conf['calendar']?$this->conf['calendar']:'');
		
		$service = &$this->getCategoryService();
		$categorySearchString = $service->getCategorySearchString($pidList, true);

		// putting everything together
		$additionalWhere = $calendarSearchString.' AND tx_cal_event.pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_event').' AND ((tx_cal_event.start_date>='.$this->starttime.' AND tx_cal_event.start_date<='.$this->endtime.') OR (tx_cal_event.end_date<='.$this->endtime.' AND tx_cal_event.end_date>='.$this->starttime.') OR (tx_cal_event.end_date>='.$this->endtime.' AND tx_cal_event.start_date<='.$this->starttime.') OR (tx_cal_event.start_date<='.$this->endtime.' AND tx_cal_event.freq IN ("day","week","month","year")))';
		
		// creating the arrays the user is allowed to see
		$categories = array();
		$service->getCategoryArray($pidList, $categories);	
		$includeRecurring = true;
		if($this->conf['view']=='ics' || $this->conf['view']=='single_ics'){
			$includeRecurring = false;
		}
		// creating events
		return $this->getEventsFromTable($categories, $includeRecurring, $additionalWhere, $this->getServiceKey(), $categorySearchString);
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
	function getEventsFromTable(&$categories, $includeRecurring=false, $additionalWhere='', $serviceKey='', $categoryWhere=''){
		
		$events = array();
		
		$select = 'tx_cal_calendar.uid AS calendar_uid, ' .
				'tx_cal_calendar.owner AS calendar_owner, ' .
				'tx_cal_event.*';
		$table = 'tx_cal_event, tx_cal_calendar';
		$where = 'tx_cal_calendar.uid = tx_cal_event.calendar_id'.$additionalWhere;
		$orderBy = ' tx_cal_event.start_date ASC, tx_cal_event.start_time ASC';
		$groupBy = '';
		if($categoryWhere!=''){
			$select .= 	', tx_cal_event_category_mm.uid_foreign AS category_uid ' ;
			$table .= ', tx_cal_event_category_mm';
			$where = 'tx_cal_calendar.uid = tx_cal_event.calendar_id AND tx_cal_event_category_mm.uid_local = tx_cal_event.uid'.$categoryWhere.$additionalWhere;
			$groupBy = 'category_uid, uid';
			$orderBy .= ', tx_cal_event_category_mm.sorting';
			$categoryIds = explode(',',$this->conf['category']);
		}
		
		
		$limit = '';
//t3lib_div::debug($select);
//t3lib_div::debug($table);
//t3lib_div::debug($where);
//t3lib_div::debug($orderBy);
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where.' GROUP BY '.$groupBy,'Select');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where,$groupBy ,$orderBy,$limit);
		$lastday = '';
		$currentday = ' ';
		$first = true;
		$lastUid = '';
		$calendarService = $this->getCalendarService();
		$categoryService = $this->getCategoryService();
		$eventOwnerArray = $calendarService->getCalendarOwner();			
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//debug($row);
			if($categoryWhere!='' && !in_array($row['category_uid'],$categoryIds)){
				continue;
			}
			if($row['uid']==$lastUid){
				$categoryArray = $categoryService->getCategoriesForEvent($row['uid']);
				foreach($categoryArray as $cat){
					$event->addCategory($cat);
				}
				continue;	
			}
			$lastUid = $row['uid'];
			
			$row['event_owner'] = $eventOwnerArray[$row['calendar_uid']];
			$row['start_date'] += strtotimeOffset($row['start_date']);
			if($row['end_date']>0){
				$row['end_date'] += strtotimeOffset($row['end_date']);
			}else{
				$row['end_date'] = $row['start_date'];
			}
			$event = $this->createEvent($row, false);
			$categoryArray = $categoryService->getCategoriesForEvent($row['uid']);
			if(is_array($categoryArray)){
				foreach($categoryArray as $cat){
					$event->addCategory($cat);
				}
			}
			$events_tmp = array();
			
			if($row['shared_user_cnt']>0){
				$select = 'uid_foreign';
				$table = 'tx_cal_event_shared_user_mm';
				$where = 'uid_local = '.$row['uid'];
				$sharedUserResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
				while ($sharedUserRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sharedUserResult)) {
					$event->addSharedUser($sharedUserRow['uid_foreign']);
				}
			}
			
			// get exception events:
			$where = 'AND tx_cal_event.uid = '.$event->getUid().' AND tx_cal_exception_event_mm.tablenames="tx_cal_exception_event_group" '.$this->cObj->enableFields('tx_cal_exception_event_group');
			$orderBy = '';
			$groupBy = '';
			$limit = '';	
			$ex_events_group = array();
			$result3 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_cal_exception_event_group.*','tx_cal_event','tx_cal_exception_event_mm','tx_cal_exception_event_group',$where,$groupBy ,$orderBy,$limit);
			while ($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result3)) {
				$event->addExceptionGroupId($row3['uid']);
				$where = 'AND tx_cal_exception_event_group.uid = '.$row3['uid'].$this->cObj->enableFields('tx_cal_exception_event');
				$result4 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_cal_exception_event.*','tx_cal_exception_event_group','tx_cal_exception_event_group_mm','tx_cal_exception_event',$where,$groupBy ,$orderBy,$limit);
				while ($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result4)) {
					$row4['start_date'] += strtotimeOffset($row4['start_date']);
					if($row4['end_date']>0){
						$row4['end_date'] += strtotimeOffset($row4['end_date']);
					}else{
						$row4['end_date'] = $row4['start_date'];
					}
					$ex_event = $this->createEvent($row4, true);
					$ex_events_group[] = $this->recurringEvent($ex_event);

				}
			}
			$where = 'AND tx_cal_event.uid = '.$event->getUid().' AND tx_cal_exception_event_mm.tablenames="tx_cal_exception_event" '.$this->cObj->enableFields('tx_cal_exception_event');
			$orderBy = '';//'tx_cal_exception_event.start_time ASC';
			$groupBy = '';
			$limit = '';
			$result2 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_cal_exception_event.*','tx_cal_event','tx_cal_exception_event_mm','tx_cal_exception_event',$where,$groupBy ,$orderBy,$limit);
			while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
				$event->addExceptionSingleId($row2['uid']);
				$row2['start_date'] += strtotimeOffset($row2['start_date']);
				if($row2['end_date']>0){
					$row2['end_date'] += strtotimeOffset($row2['end_date']);
				}else{
					$row2['end_date'] = $row2['start_date'];
				}
				$ex_event = $this->createEvent($row2, true);				
				$ex_events_group[] = $this->recurringEvent($ex_event);
			}

			if(!$includeRecurring){
				$events_tmp[date('Ymd',$event->getStartDate())][date('Hi',$event->getStartTime())][$event->getUid()] = $event;
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
																//TODO: checking the piVar is not a very good thing
		if($categoryWhere!='' && !($this->conf['view']=='ics' && $this->controller->piVars['category'])){ 
//debug($events,'events');
			$uidCollector = array();
			
			$select = 'tx_cal_event_category_mm.*, tx_cal_event.pid, tx_cal_event.uid';
			$table = 'tx_cal_event_category_mm,tx_cal_event';
			$groupby = 'tx_cal_event_category_mm.uid_local';
			$orderby = '';
			$where = 'tx_cal_event.uid = tx_cal_event_category_mm.uid_local AND tx_cal_event.pid IN ('.$this->conf['pidList'].')';
//	t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where.' GROUP BY '.$groupby);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby,$orderby);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$uidCollector[] = $row['uid_local'];
			}

			if(count($uidCollector)>0){
				$additionalWhere .= ' AND tx_cal_event.uid NOT IN ('.implode(',',$uidCollector).')';
			}
			$eventsWithoutCategory = $this->getEventsFromTable($categories, $includeRecurring, $additionalWhere, $serviceKey);
			if(!empty($eventsWithoutCategory)){
				$this->mergeEvents($events,$eventsWithoutCategory);
			}
		}
//debug($events,'events');
		return $events;
	}

	/**
	 *  Finds all events.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAll($pidList) {		
		// How to get the events
		// 1st get Calendar specified
		// 2nd get categories specified
		// 3rd get all related events
		// make an array out of the list, so we can handle it better
		
		$service = &$this->getCalendarService();
		$calendarSearchString = $service->getCalendarSearchString($pidList, true, $this->conf['calendar']?$this->conf['calendar']:'');
		
		$service = &$this->getCategoryService();
		$categorySearchString = $service->getCategorySearchString($pidList, true);

		// putting everything together
		$additionalWhere = $calendarSearchString.' AND tx_cal_event.pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_event').' AND (tx_cal_event.freq!="none" OR tx_cal_event.freq!="")';
		
		// creating the arrays the user is allowed to see

		$categories = array();
		$service->getCategoryArray($pidList, $categories);	
		// creating events
		
		if($this->conf['view']=='ics' || $this->conf['view']=='single_ics'){
			$includeRecurring = false;
		}else{
			$includeRecurring = true;
		}

		// creating events
        if($pidList)
		    return $this->getEventsFromTable($categories, $includeRecurring, $additionalWhere, $this->getServiceKey(), $categorySearchString);
        else
            return array();
	}
	
	function createEvent($row, $isException){
		$tx_cal_phpicalendar_model = &t3lib_div::makeInstanceClassName('tx_cal_phpicalendar_model');
		$event = &new $tx_cal_phpicalendar_model('',$row, $isException, $this->getServiceKey());
		return $event;	
	}
	
	/**
	 *  Finds a single event.
	 *
	 *  @return		object			The event represented by the model.
	 */	
	function find($uid, $pidList, $showHiddenEvents=false, $showDeletedEvents=false) {
		
		$this->endtime = $this->controller->unix_time + 86400;
		$categoryService = &$this->getCategoryService();
		$categories = array();
		$categoryService->getCategoryArray($pidList, $categories);
//debug($categories);

		$calendarService = &$this->getCalendarService();
		$calendarSearchString = $calendarService->getCalendarSearchString($pidList, true, $this->conf['view.']['calendar']?$this->conf['view.']['calendar']:'');
		
		// categories specified? show only those categories
		$categorySearchString = $categoryService->getCategorySearchString($pidList,true);

		// putting everything together
		if($showHiddenEvents){
			$additionalWhere = $calendarSearchString.' AND tx_cal_event.uid='.$uid;
		}else{
			$additionalWhere = $calendarSearchString.' AND tx_cal_event.uid='.$uid.' AND tx_cal_event.hidden = 0';
		}
		if(!$showDeletedEvents){
			$additionalWhere .= ' AND tx_cal_event.deleted = 0';
		}
		$includeRecurring = true;
		if($this->conf['view']=='ics' || $this->conf['view']=='single_ics'){
			$includeRecurring = false;
		}
		$events = $this->getEventsFromTable($categories, $includeRecurring, $additionalWhere, $this->getServiceKey(), $categorySearchString, $showHiddenEvents);
		
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
		$insertFields = array('pid' => $pid, 'tstamp' => $crdate, 'crdate' => $crdate);
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'event');
		$this->retrievePostData($insertFields);
		if(!$insertFields['calendar_id'] && $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault']){
			$insertFields['calendar_id'] = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
		}
		
		
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();

		$insertFields['notify_ids'] = $this->controller->piVars['notify_ids'];		
		$insertFields['single_exception_ids'] = $this->controller->piVars['single_exception_ids'];
		$insertFields['group_exception_ids'] = $this->controller->piVars['group_exception_ids'];
		$insertFields['shared_user_ids'] = $this->controller->piVars['shared_user_ids'];
		$insertFields['category_ids'] = $this->controller->piVars['category_ids'];

		$uid = $this->_saveEvent($insertFields);

		$this->unsetPiVars();
		$insertFields['uid'] = $uid;
		$insertFields['category'] = $this->controller->piVars['category_ids'];
		$this->_notify($insertFields);
		$this->scheduleReminder($insertFields);
		clearCache();
	}
	
	function _saveEvent(&$eventData){
		$tempValues = array();
		$tempValues['notify_ids'] = $eventData['notify_ids'];
		unset($eventData['notify_ids']);
		$tempValues['single_exception_ids'] = $eventData['single_exception_ids'];
		unset($eventData['single_exception_ids']);
		$tempValues['group_exception_ids'] = $eventData['group_exception_ids'];
		unset($eventData['group_exception_ids']);
		$tempValues['shared_user_ids'] = $eventData['shared_user_ids'];
		unset($eventData['shared_user_ids']);
		$tempValues['category_ids'] = $eventData['category_ids'];
		unset($eventData['category_ids']);	
		
		// Creating DB records
		$table = 'tx_cal_event';
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$eventData);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		//creating relation records
		if($this->rightsObj->isAllowedToCreateEventNotify()){
			if($tempValues['notify_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',explode(',', strip_tags($tempValues['notify_ids'])),$uid,'fe_users');
			}
		}else if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateNotify.']['uidDefault']){
			$idArray = explode(',', $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateNotify.']['uidDefault']);
			if($this->conf['rights.']['create.']['event.']['addFeUserToNotify']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',$idArray,$uid,'fe_users');
		}else if($this->rightsObj->isLoggedIn() && $this->conf['rights.']['create.']['event.']['addFeUserToNotify']){
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array($this->rightsObj->getUserId()),$uid,'fe_users');
		}else if($this->conf['rights.']['create.']['event.']['allowedToCreateEvents.']['public']){
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',explode(',',$this->conf['rights.']['create.']['event.']['notifyUsersOnPublicCreate']),$uid,'fe_users');
		}

		if($this->rightsObj->isAllowedToCreateEventException()){
			if($tempValues['single_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation('tx_cal_exception_event_mm',explode(',', strip_tags($tempValues['single_exception_ids'])),$uid,'tx_cal_exception_event');
			}
			if($tempValues['group_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation('tx_cal_exception_event_group_mm',explode(',', strip_tags($tempValues['group_exception_ids'])),$uid,'tx_cal_exception_event_group');
			}
		}
		
		if($this->rightsObj->isAllowedToCreateEventShared()){
			$idArray = explode(',',strip_tags($tempValues['shared_user_ids']));
			if($this->conf['rights.']['create.']['event.']['addFeUserToShared']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',$idArray,$uid,'fe_users');
		}else{
			$idArray = explode(',',$this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateShared.']['uidDefault']);
			if($this->conf['rights.']['create.']['event.']['addFeUserToShared']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',$idArray,$uid,'fe_users');
		}
		
		if($this->rightsObj->isAllowedToCreateEventCategory()){
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_category_mm',explode(',',strip_tags($tempValues['category_ids'])),$uid,'');
		}else{
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_category_mm',array($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCategory.']['uidDefault']),$uid,'');
		}
		return $uid;
	}
	
	function updateEvent($uid){
		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		$config = $this->conf['calendar'];
		$this->conf['calendar'] = intval($this->controller->piVars['calendar_id']);
		$event = $this->find($uid, $this->conf['pidList'], true, true);
		$this->conf['calendar'] = $config;
		
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'event',false);
		$this->retrievePostData($insertFields);
		
		if(isset($this->controller->piVars['category_ids'])) {
			$categoryIds = explode(',', strip_tags($this->controller->piVars['category_ids']));
		} else {
			$categoryIds = null;
		}
		if(isset($this->controller->piVars['notify_ids'])) {
			$notifyIds = explode(',', strip_tags($this->controller->piVars['notify_ids']));
		} else {
			$notifyIds = null;
		}
		if(isset($this->controller->piVars['single_exception_ids'])) {
			$singleExceptionIds = explode(',', strip_tags($this->controller->piVars['single_exception_ids']));
		} else {
			$singleExceptionIds = null;
		}
		if(isset($this->controller->piVars['group_exception_ids'])) {
			$groupExceptionIds = explode(',', strip_tags($this->controller->piVars['group_exception_ids']));
		} else {
			$groupExceptionIds = null;
		}
		
		$this->_updateEvent($uid, $insertFields, $categoryIds, $notifyIds, $singleExceptionIds, $groupExceptionIds);
		
		$this->_notifyOfChanges($event,$insertFields);
		$this->unsetPiVars();
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		clearCache();
	}
	
	function _updateEvent($uid, $eventData, $categoryIds, $notifyIds, $singleExceptionIds, $groupExceptionIds){
		// Creating DB records
		$table = 'tx_cal_event';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$eventData);
		
		
		$cal_user_ids = array();
		$where = ' AND tx_cal_event.uid='.$uid.' AND tx_cal_fe_user_category_mm.tablenames="fe_users" '.$this->cObj->enableFields('tx_cal_event');
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		if($this->rightsObj->isAllowedToEditEventCategory() && !is_null($categoryIds)){
			$table = 'tx_cal_event_category_mm';
			$where = 'uid_local = '.$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			$this->insertIdsIntoTableWithMMRelation($table,$categoryIds,$uid,'');
		}
		
		if($this->rightsObj->isAllowedToEditEventNotify() && !is_null($notifyIds)){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_fe_user_event_monitor_mm','uid_local ='.$uid);
			if(count($notifyIds)>0){
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',$notifyIds,$uid,'fe_users');
			}
		}else if($this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditNotify.']['uidDefault']){
			$idArray = explode(',', $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditNotify.']['uidDefault']);
			if($this->conf['rights.']['edit.']['event.']['addFeUserToNotify']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',$idArray,$uid,'fe_users');
		}else if($this->conf['rights.']['edit.']['event.']['addFeUserToNotify']){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_fe_user_event_monitor_mm','uid_local ='.$uid);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',explode(',', $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditNotify.']['uidDefault']),$uid,'fe_users');
		}
		
		if($this->rightsObj->isAllowedToEditEventException() && !is_null($singleExceptionIds)){
			$table = 'tx_cal_exception_event_mm';
			$where = 'uid_local = '.$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			if($this->controller->piVars['single_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation('tx_cal_exception_event_mm',$singleExceptionIds,$uid,'tx_cal_exception_event');
			}
			$table = 'tx_cal_exception_event_group_mm';
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			if($this->controller->piVars['group_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation('tx_cal_exception_event_group_mm',$groupExceptionIds,$uid,'tx_cal_exception_event_group');
			}
		}
	}
	
	function removeEvent($uid){
		$event = $this->find($uid, $this->conf['pidList'], true, true);
		if ($event->isUserAllowedToDelete()) {
			$config = $this->conf['calendar'];
			$this->conf['calendar'] = intval($this->controller->piVars['calendar_id']);
			$event = $this->find($uid, $this->conf['pidList'], true, true);
			$this->conf['calendar'] = $config;
			
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tx_cal_event';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
			
			require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
			$fields = $event->getValuesAsArray();
			$fields['delete'] = 1;
			$fields['tstamp'] = $updateFields['tstamp'];
			$this->_notify($fields);
			$this->stopReminder($uid);
			clearCache();
			$this->unsetPiVars();
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if(isset($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateHidden.']['default']) && !$this->rightsObj->isAllowedToEditEventHidden() && !$this->rightsObj->isAllowedToCreateEventHidden()){
			$hidden = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateHidden.']['default'];
		}else if(isset($this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditHidden.']['default']) && !$this->rightsObj->isAllowedToEditEventHidden() && !$this->rightsObj->isAllowedToCreateEventHidden()){
			$hidden = $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditHidden.']['default'];
		}else if($this->controller->piVars['hidden'] == 1 && ($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden())){
			$hidden = 1;
		}
		$insertFields['hidden'] = $hidden;
		
		$insertFields['allday'] = intval($this->controller->piVars['allday']);

		if($this->rightsObj->isAllowedToCreateEventCalendar()){
			if($this->controller->piVars['calendar_id']!=''){
				$insertFields['calendar_id'] = intval($this->controller->piVars['calendar_id']);
			}else if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault']){
				$insertFields['calendar_id'] = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
			}else{
				$insertFields['calendar_id'] = ''; //TODO: Set the calendar_id to some value
			}
		}else if($this->rightsObj->isAllowedToCreateEventCalendar()){
			if($this->controller->piVars['calendar_id']!=''){
				$insertFields['calendar_id'] = intval($this->controller->piVars['calendar_id']);
			}else if($this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditCalendar.']['uidDefault']){
				$insertFields['calendar_id'] = $this->conf['rights.']['edit.']['event.']['fields.']['allowedToEditCalendar.']['uidDefault'];
			}else{
				$insertFields['calendar_id'] = ''; //TODO: Set the calendar_id to some value
			}
		}
		
		if($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()){
			if($this->controller->piVars['event_start_day']!=''){
				$time = array();
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_start_day'], $time);
				$insertFields['start_date'] = gmmktime(0,0,0,$time[2],$time[3],$time[1])-strtotimeOffset($insertFields['start_date']);
			}else{
				return;
			}
			if($this->controller->piVars['event_start_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_start_time'], $time);
				$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
			}
			if($this->controller->piVars['event_end_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_end_day'], $time);
				$insertFields['end_date'] = gmmktime(0,0,0,$time[2],$time[3],$time[1])-strtotimeOffset($insertFields['end_date']);
			}
			if($this->controller->piVars['event_end_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_end_time'], $time);
				$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
			}
		}
		if($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()){
			$insertFields['title'] = strip_tags($this->controller->piVars['title']);
		}
		
		if($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$insertFields['organizer'] = strip_tags($this->controller->piVars['organizer']);
			if($this->controller->piVars['cal_organizer']!=''){
				$insertFields['organizer_id'] = intval($this->controller->piVars['cal_organizer']);
			}
		}
		if($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()){
			$insertFields['location'] = strip_tags($this->controller->piVars['location']);
			if($this->controller->piVars['cal_location']!=''){
				$insertFields['location_id'] = intval($this->controller->piVars['cal_location']);
			}
		}
		if($this->controller->piVars['teaser']!='' && ($this->rightsObj->isAllowedToEditEventTeaser() || $this->rightsObj->isAllowedToCreateEventTeaser())){
			$insertFields['teaser'] = $this->cObj->removeBadHTML($this->controller->piVars['teaser'],$this->conf);
		}
		if($this->controller->piVars['description']!='' && ($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription())){
			$insertFields['description'] = $this->cObj->removeBadHTML($this->controller->piVars['description'],$this->conf);
		}
		if($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()){
			if($this->controller->piVars['frequency_id']!=''){
				$valueArray = array('none','day','week','month','year');
				$insertFields['freq'] = in_array($this->controller->piVars['frequency_id'],$valueArray)?$this->controller->piVars['frequency_id']:'none';
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
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['until'], $time);
				$insertFields['until'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['count']!=''){
				$insertFields['cnt'] = intval($this->controller->piVars['count']);
			}
			if($this->controller->piVars['interval']!=''){
				$insertFields['intrval'] = intval($this->controller->piVars['interval']);
			}
			if($this->rightsObj->isAllowedTo('edit','event','image') || $this->rightsObj->isAllowedTo('create','event','image')){
				$insertFields['image'] = $this->controller->piVars['image'];
				$this->checkOnTempImage($insertFields);
			}
		}
		
		// Hook initialization:
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_event_service.php']['addAdditionalField'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_event_service.php']['addAdditionalField'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		foreach($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'addAdditionalField')) {
				$hookObj->addAdditionalField($insertFields, $this);
			}
		}
	}
	
	function search($pidList='', $starttime, $endtime, $searchword, $locationIds){
		$this->starttime = $starttime - $this->conf['view.'][$this->conf['view'].'.']['startPointCorrection'];
		$this->endtime = $endtime + $this->conf['view.'][$this->conf['view'].'.']['endPointCorrection'];
		
		// adjusting to db timestamps, which are stored in server timezone
		$this->endtime -= strtotimeOffset($this->endtime);
		$this->starttime -= strtotimeOffset($this->starttime);

		$service = &$this->getCategoryService();

		$events=array();
		$additionalSearch = '';
		if($searchword!=''){
			$additionalSearch = $this->searchWhere($searchword);
		}
		
		$linkIds = $this->conf['calendar']?$this->conf['calendar']:'';
		// Lets see if we shall display the public calendar too
		/*
		if(!$linkIds || in_array('public',explode(',',$linkIds))){
			$includePublic = 1;
		}else{
			$includePublic = 0;
		}
		*/
		
		/**
		 * @fixme	 Always include public events.  Do we really want to do this?  
		 *			 If so, find a prettier way than hardcoding it.
		 */
		$includePublic = 1;
		
		$categorySearchString = $service->getCategorySearchString($pidList, $includePublic);
		$calService = &$this->getCalendarService();
		$calendarSearchString = $calService->getCalendarSearchString($pidList, $includePublic, $linkIds,$this->conf['view.']['calendar']?$this->conf['view.']['calendar']:'');
		
		$timeSearchString = ' AND tx_cal_event.pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_event').' AND ((tx_cal_event.start_date>='.$this->starttime.' AND tx_cal_event.start_date<='.$this->endtime.') OR (tx_cal_event.end_date<='.$this->endtime.' AND tx_cal_event.end_date>='.$this->starttime.') OR (tx_cal_event.end_date>='.$this->endtime.' AND tx_cal_event.start_date<='.$this->starttime.') OR (tx_cal_event.start_date<='.$this->endtime.' AND tx_cal_event.freq IN ("day","week","month","year")))';
		
		if($locationIds!=''){
			$locationSearchString = ' AND location_id in ('.$locationIds.')';
		}

		// putting everything together
		$additionalWhere = $calendarSearchString.$timeSearchString.$locationSearchString.$additionalSearch;
		$categories = array();
		$this->getEventsFromTable($service->getCategoryArray($pidList, $categories), true, $additionalWhere, '', $categorySearchString);
		return $categories;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchEventFieldList'], 'tx_cal_event');
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

		$this->filterFalseCombinations($event);
		$this->checkRecurringSettings($event);

		$master_array = array();

		$until = $event->getUntil();
		$until += strtotimeOffset($until);
		$until += 86399;
		$rrule_array = $event->getRecurringRule();
		$count = $event->getCount();
		if($this->endtime < $until) { 
			$until = $this->endtime;
		}
		$byyear = array();
		for($i = gmdate('Y',$event->getStarttime()); $i < gmdate('Y',$until)+1; $i++){
			$byyear[] = $i;
		}
		
		if($event->isAllday()){
			$master_array[gmdate('Ymd',$event->getStarttime())]['-1'][$event->getUid()] = $event;
		}else{
			$master_array[gmdate('Ymd',$event->getStarttime())][gmdate('Hi',$event->getStarttime())][$event->getUid()] = $event;
		}
		$counter = 1;
		$total = 1;
		$nextOccuranceTime = $event->getStarttime()+86400;
		switch ($rrule_array['FREQ']) {
			case 'day':
				$this->findDailyWithin($master_array, $event, $nextOccuranceTime, $until, $event->getByDay(), $count, $counter, $total);
				break;
			case 'week':
			case 'month':
			case 'year':
				$bymonth = $event->getByMonth();
				$byday = $event->getByDay();
				$hour = gmdate('H',$event->getStarttime());
				$minute = gmdate('i',$event->getStarttime());

				// 2007, 2008...
				foreach($byyear as $year){
					if($counter < $count && $nextOccuranceTime <= $until){
						// 1,2,3,4,5,6,7,8,9,10,11,12
						foreach($bymonth as $month){
							if($counter < $count && $nextOccuranceTime <= $until && gmdate('Ym',gmmktime(0,0,0,$month,1,$year))>=gmdate('Ym',$nextOccuranceTime)){

								$bymonthday = $this->getMonthDaysAccordingly($event, $month, $year);

								// 1,2,3,4....31
								foreach($bymonthday as $day){
									$nextOccuranceTime = gmmktime($hour,$minute,0,$month,$day,$year);
									if($counter < $count && $nextOccuranceTime <= $until){
										$currentUntil = $nextOccuranceTime + 86399 ;
										if(intval(gmdate('m',$nextOccuranceTime))==$month && $nextOccuranceTime >= $event->getStarttime()){
											$this->findDailyWithin($master_array, $event, $nextOccuranceTime, $currentUntil, $byday, $count, $counter, $total);
										}else{
											continue;
										}
									}else{
										return $master_array;
									}
								}
							}
						}
					}else{
						return $master_array;
					}
				}
				break; // switch-case break
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
		$insertFields = array('pid' => $pid, 'tstamp' => $crdate, 'crdate' => $crdate);
		//TODO: Check if all values are correct
		
		if($this->controller->piVars['exception_start_day']!=''){
			$time = array();
			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['exception_start_day'], $time);
			$insertFields['start_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
		}else{
			return;
		}
		if($this->controller->piVars['exception_start_time']!=''){
			preg_match ('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['exception_start_time'], $time);
			$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
		}
		if($this->controller->piVars['exception_end_day']!=''){
			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['exception_end_day'], $time);
			$insertFields['end_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
		}
		if($this->controller->piVars['exception_end_time']!=''){
			preg_match ('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['exception_end_time'], $time);
			$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
		}
		
		if($this->controller->piVars['exception_title']!=''){
			$insertFields['title'] = strip_tags($this->controller->piVars['exception_title']);
		}

		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		if($insertFields['title']==''){
			$insertFields['title'] = strip_tags($this->controller->piVars['exception_start_day']).' exception';
		}
		$table = 'tx_cal_exception_event';

		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		$this->insertIdsIntoTableWithMMRelation('tx_cal_exception_event_mm',array($uid),intval($this->controller->piVars['event_uid']),'tx_cal_exception_event');
		$this->unsetPiVars();
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		clearCache();
	}
	
	function unsetPiVars(){
		unset($this->controller->piVars['hidden']);
		unset($this->controller->piVars['_TRANSFORM_description']);
		unset($this->controller->piVars['uid']);
		unset($this->controller->piVars['calendar_id']);
		unset($this->controller->piVars['calendar']);
		unset($this->controller->piVars['switch_calendar']);
		unset($this->controller->piVars['type']);
		unset($this->controller->piVars['allday']);
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
		unset($this->controller->piVars['category_display_ids']);
		unset($this->controller->piVars['user_ids']);
		unset($this->controller->piVars['group_ids']);
		unset($this->controller->piVars['single_exception_ids']);
		unset($this->controller->piVars['group_exception_ids']);
		unset($this->controller->piVars['gettime']);
		unset($this->controller->piVars['notify']);
		unset($this->controller->piVars['notify_ids']);
		unset($this->controller->piVars['teaser']);
		unset($this->controller->piVars['image']);
		unset($this->controller->piVars['image_old']);
		unset($this->controller->piVars['removeImage']);
	}
	
	function checkRecurringSettings(&$event){
		$this->checkFrequency($event);
		if($event->getFreq()=='none'){
			return;
		}
		$this->checkInterval($event);
		$this->checkByMonth($event);
		$this->checkByWeekno($event);
		$this->checkByYearday($event);
		$this->checkByMonthday($event);
		$this->checkByDay($event);
		$this->checkByHour($event);
		$this->checkByMinute($event);
		$this->checkBySecond($event);
		$this->checkBySetpos($event);
		$this->checkCount($event);
		$this->checkUntil($event);
		$this->checkWkst($event);
	}
	
	function filterFalseCombinations(&$event){
		switch ($event->getFreq()){
			case '':
			case 'none':
				break;
			case 'day':
				$event->setByMonth('');
				$event->setByWeekNo('');
				$event->setByYearDay('');
				$event->setByMonthDay('');
				$event->setByDay('');
				break;
			case 'week':
				$event->setByMonth('');
				$event->setByWeekNo('');
				$event->setByYearDay('');
				$event->setByMonthDay('');
				break;
			case 'month':
				$event->setByMonth('');
				$event->setByWeekNo('');
				$event->setByYearDay('');
				break;
			case 'year':
				if(count($event->getByMonth())>0){
					$event->setByWeekNo('');
					$event->setByYearDay('');
				}else if(count($event->getByWeekNo())>0){
					$event->setByYearDay('');
				}else if(count($event->getByYearDay())>0){
					$event->setByMonthDay('');
				}else if(count($event->getByMonthDay())>0){
					$event->setByDay('');
				}
				break;
		}
	}
	
	function checkFrequency(&$event){
		$allowedValues = array('second','minute','hour','day','week','month','year');
		if(!in_array($event->getFreq(),$allowedValues)){
			$event->setFreq('none');
		}
	}
	
	function checkInterval(&$event){
		if(!$event->getInterval() || $event->getInterval() < 1){
			$event->setInterval(1);
		}
	}
	
	function checkCount(&$event){
		if(!$event->getCount() || $event->getCount() < 1){
			$event->setCount(9999999);
		}
	}
	
	function checkUntil(&$event){
		if(!$event->getUntil() || $event->getUntil() < 1){
			$event->setUntil($this->endtime);
		}
	}
	
	function checkBySecond(&$event){
		if(intval($event->getBySecond()) < 0 || intval($event->getBySecond()) >59){
			$event->setBySecond(gmdate('s',$event->getStarttime()));
		}
	}
	
	function checkByMinute(&$event){
		if(intval($event->getByMinute()) < 0 || intval($event->getByMinute()) >59){
			$event->setByMinute(gmdate('i',$event->getStarttime()));
		}
	}
	
	function checkByHour(&$event){
		if(intval($event->getByHour()) < 0 || intval($event->getByHour()) >23){
			$event->setByHour(gmdate('H',$event->getStarttime()));
		}
	}
	
	function checkByDay(&$event){
		$byday_arr = array();
		$allowedValues = array();
		$allowedWeekdayValues = array('MO','TU','WE','TH','FR','SA','SU');
		// example: -2TU -> 2nd last Tuesday
		//  +1TU -> 1st Tuesday
		//  WE,FR -> Wednesday and Friday
		$byDayArray = $event->getByDay();
		if($event->getFreq()=='day'){
			$event->setByDay('all');
			return;
		}
		for($i=0; $i < count($byDayArray); $i++){
			$byDayArray[$i] = strtoupper($byDayArray[$i]);
			if(ereg ('([-\+]{0,1})?([0-9]{1})?([A-Z]{2})', $byDayArray[$i], $byDaySplit)){
				if(!in_array($byDaySplit[3],$allowedWeekdayValues)){
					continue;
				}else if (!($byDaySplit[2]>0 &&  ($event->getFreq()=='month' || $event->getFreq()=='year'))){
					// n-th values are not allowed for monthly and yearly
					unset($byDaySplit[1]);
					unset($byDaySplit[2]);
				}
				unset($byDaySplit[0]);
				$allowedValues[] = implode('',$byDaySplit);
			}else{
				// the current byday setting is not valid
			}
		}
		if(count($allowedValues)==0){
			if($event->getFreq()=='week'){
				$allowedValues = array(substr(gmdate('D',$event->getStartDate()),0,2));
			}else{
				$allowedValues = array('all');
			}
		}
		$event->setByDay(implode(',',$allowedValues));
	}
	
	function checkByMonth(&$event){
		$byMonth = $event->getByMonth();
		if(!is_array($byMonth) || count($byMonth) == 0){
			if($event->getFreq()=='year'){
				$event->setByMonth(gmdate('m',$event->getStartDate()));	
			}else{
				$event->setByMonth('all');
			}
			return;
		}
		$allowedValues = array();
		foreach($byMonth as $month){
			if($month > 0 && $month < 13){
				$allowedValues[] = $month;
			}
		}
		sort(array_unique($allowedValues));
		$event->setByMonth(implode(',',$allowedValues));
	}
	
	function checkByMonthday(&$event){
		/* If there's not a monthday set, pick a default value */
		if(count($event->getByMonthDay())==0){

			/**
			 * If there's no day of the week either, assume that we only want 
			 * to recur on the event start day.  If there is a day of the 
			 * week, assume that we want to recur anytime that day of the week
			 * occurs.
			 */
			if(count($event->getByDay())==0) {
				$event->setByMonthDay(gmdate('j',$event->getStarttime()));
			} else {
				$event->setByMonthDay('all');
			}
		}else{
			$event->setByMonthDay(implode(',',array_filter($event->getByMonthDay(),'getInbetweenMonthValues')));
		}
	}
	
	function checkByYearday(&$event){
		if(count($event->getByYearDay())==0){
// Wrong!
//			$values = array();
//			$until = $event->getUntil();
//			if($until < 1){
//				$until = gmmktime(0,0,0,12,31,2030);
//			}
//			for($i = gmdate('Y',$event->getStarttime()); $i < gmdate('Y',$until); $i++){
//				$values[] = $i;
//			}
//			$event->setByYearDay(implode(',',$values));
		}else{
			$event->setByYearDay(implode(',',array_filter($event->getByYearDay(),'getInbetweenYearValues')));
		}
	}
	
	function checkByWeekno(&$event){
		if($event->getFreq()=='yearly'){
			$event->setByWeekNo(implode(',',array_filter($event->getByWeekNo(),'getInbetweenWeekValues')));
		}else{
			$event->setByWeekNo('');
		}
	}
	
	function checkWkst(&$event){
		$allowedWeekdayValues = array('MO','TU','WE','TH','FR','SA','SU');
		$wkst = strtoupper($event->getWkst());
		if(!in_array($wkst,$allowedWeekdayValues)){
			$wkst = '';
		}
		$event->setWkst($wkst);
	}
	
	function checkBySetpos(&$event){
		$event->setBySetpos(intval($event->getBySetpos()));
	}

	function findDailyWithin(&$master_array, $event, $startRange, $endRange, $weekdays, $maxCount, &$currentCount, &$totalCount){
		$nextOccuranceTime = $startRange;
		while($currentCount < $maxCount && $nextOccuranceTime <= $endRange){
			if($nextOccuranceTime!=$event->getStarttime()){
				if(($totalCount % $event->getInterval()) == 0){
					$new_event = $event->cloneEvent();
					$new_event->setStarttime($nextOccuranceTime);
					$new_event->setEndtime(($nextOccuranceTime - $event->getStarthour()) + $event->getEndhour());
					if($new_event->isAllday()){
						$master_array[gmdate('Ymd',$nextOccuranceTime)]['-1'][$new_event->getUid()] = $new_event;
					}else{
						$master_array[gmdate('Ymd',$nextOccuranceTime)][gmdate('Hi',$new_event->getStarttime())][$new_event->getUid()] = $new_event;
					}
					$currentCount++;
				}
				$totalCount++;
			}
			$nextOccuranceTime+=86400;
		}
	}

	function getMonthDaysAccordingly(&$event, $month, $year){
		$byDayArray = $event->getByDay();
		$byMonthDays = $event->getByMonthDay();
		$resultDays = array();
		if(count($byDayArray)==0){
			$resultDays = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
			return $resultDays;
		}

		for($i=0; $i < count($byDayArray); $i++){
			if(ereg ('([-\+]{0,1})?([0-9]{1})?([A-Z]{2})', $byDayArray[$i], $byDaySplit)){
				$threeCharWeekday = tx_cal_calendar::two2threeCharDays($byDaySplit[3], true);
				$monthStartTime = gmmktime(0,0,0,$month,1,$year);
				$monthEndTime = gmmktime(0,0,0,$month+1,1,$year);
				if($byDaySplit[2]>0){
					if($byDaySplit[1]=='-'){
						$monthTime = gmstrtotime('last '.$threeCharWeekday, $monthEndTime) - (($byDaySplit[2]-1)*604800);						
					}else{
						$monthTime = gmstrtotime('next '.$threeCharWeekday, $monthStartTime) + (($byDaySplit[2]-1)*604800);
					}
					
					if(in_array(intval(gmdate('d',$monthTime)),$byMonthDays)){
						$resultDays[] = intval(gmdate('d',$monthTime));
					}
				} else {
					$monthTime = gmstrtotime('next '.$threeCharWeekday, $monthStartTime);
					
					while($monthTime < $monthEndTime){
						$resultDays[] = intval(gmdate('d',$monthTime));
						$monthTime += 604800;
					}
				}
			}
		}
		
		$resultDays = array_intersect($resultDays, $event->getByMonthDay());
		sort($resultDays);
		return $resultDays;
	}
	
}
function getInbetweenMonthValues($value){
	$value = intval($value);
	if($value < -31 || $value > 31 || $value == 0 ){
		return false;
	}
	return true;
}

function getInbetweenYearValues($value){
	$value = intval($value);
	if($value < -366 || $value > 366 || $value == 0 ){
		return false;
	}
	return true;
}

function getInbetweenWeekValues($value){
	$value = intval($value);
	if($value < -53 || $value > 53 || $value == 0 ){
		return false;
	}
	return true;
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_event_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_event_service.php']);
}
?>
