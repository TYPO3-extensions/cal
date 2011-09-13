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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_todo_model.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_todo_rec_model.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_event_service.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_todo_service extends tx_cal_event_service {

	function tx_cal_todo_service(){
		$this->tx_cal_event_service();
	}

	/**
	 *  Finds all todos within a given range.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAllWithin(&$start_date, &$end_date, $pidList, $eventType='4') {
		return parent::findAllWithin($start_date, $end_date, $pidList, '4');
	}

	/**
	 *  Finds all events.
	 *
	 *  @return		array			The array of todos represented by the model.
	 */
	function findAll($pidList, $eventType='4') {
		return parent::findAll($pidList, '4');
	}

	function createEvent($row, $isException){
		$todo = tx_cal_functions::makeInstance('tx_cal_todo_model',$row, $this->getServiceKey());
		return $todo;
	}

	/**
	 *  Finds a single event.
	 *
	 *  @return		object			The todo represented by the model.
	 */
	function find($uid, $pidList, $showHiddenEvents=false, $showDeletedEvents=false, $getAllInstances=false, $disableCalendarSearchString=false, $disableCategorySearchString=false, $eventType='4') {
		return parent::find($uid, $pidList, $showHiddenEvents, $showDeletedEvents, $getAllInstances, $disableCalendarSearchString, $disableCategorySearchString, $eventType);
	}
	
	function findCurrentTodos(){
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$this->starttime = new tx_cal_date($confArr['recurrenceStart']);
		$this->endtime = new tx_cal_date($confArr['recurrenceEnd']);
		$categories = &$this->modelObj->findAllCategories('cal_category_model', '', $this->conf['pidList']);
		$categories = array();

		$categoryService = &$this->modelObj->getServiceObjByKey('cal_category_model', 'category', 'tx_cal_category');
		$categoryService->getCategoryArray($this->conf['pidList'], $categories);

		$calendarSearchString = ''; 
		if(!$disableCalendarSearchString){ 
			$calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar'); 
			$calendarSearchString = $calendarService->getCalendarSearchString($this->conf['pidList'], true, $this->conf['calendar']?$this->conf['calendar']:''); 
		}
		
		// categories specified? show only those categories
		$categorySearchString = '';
		if($disableCategorySearchString){
			$categorySearchString = $categoryService->getCategorySearchString($this->conf['pidList'],true);
		}

		// putting everything together
		$additionalWhere = $calendarSearchString.' AND tx_cal_event.completed < 100 AND tx_cal_event.pid IN ('.$this->conf['pidList'].') '.$this->cObj->enableFields('tx_cal_event');
		$getAllInstances = true;
		$eventType = tx_cal_model::EVENT_TYPE_TODO;
		
		return $this->getEventsFromTable($categories[0][0], $getAllInstances, $additionalWhere, $this->getServiceKey(), $categorySearchString, false, $eventType);
	}


	function saveEvent($pid){
		$object = $this->modelObj->createEvent('tx_cal_todo');
		$object->updateWithPIVars($this->controller->piVars);

		$crdate = time();
		$insertFields = Array();
		$insertFields['pid'] = $pid;
		$insertFields['tstamp'] = $crdate;
		$insertFields['crdate'] = $crdate;
		
		if($GLOBALS['TSFE']->sys_language_content > 0 
				&& $this->conf['showRecordsWithoutDefaultTranslation']==1 
				&& $this->rightsObj->isAllowedTo('create', 'translation')) {
			$insertFields['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_content;
		}
		
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'event');
		$this->filterDataToBeSaved($insertFields, $object);

		if(!$insertFields['calendar_id'] && $this->conf['rights.']['create.']['todo.']['fields.']['calendar_id.']['default']){
			$insertFields['calendar_id'] = $this->conf['rights.']['create.']['todo.']['fields.']['calendar_id.']['default'];
		}


		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		
		if(is_array($this->controller->piVars['notify'])){
			$insertFields['notify_ids'] = implode(',',$this->controller->piVars['notify']);
		}else{
			$insertFields['notify_ids'] = $this->controller->piVars['notify_ids'];
		}
		if(is_array($this->controller->piVars['exception_ids'])){
			$insertFields['exception_ids'] = implode(',',$this->controller->piVars['exception_ids']);
		}else{
			$insertFields['exception_ids'] = $this->controller->piVars['exception_ids'];
		}

		$uid = $this->_saveEvent($insertFields, $object);

		$this->conf['category'] = $this->conf['view.']['allowedCategories'];
		$this->conf['calendar'] = $this->conf['view.']['allowedCalendar'];

		$this->unsetPiVars();
		$insertFields['uid'] = $uid;
		$insertFields['category'] = $this->controller->piVars['category_ids'];
		$this->_notify($insertFields);
		if($object->getSendoutInvitation()){
			$object->setUid($uid);
			$this->_invite($object);
		}
		$this->scheduleReminder($uid);
		
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if($extConf['useNewRecurringModel']){
			$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',$GLOBALS['TSFE']->id);
			$rgc->generateIndexForUid($uid, 'tx_cal_event');
		}
		
		// Hook: saveEvent
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_todo_service','todoServiceClass');
		tx_cal_functions::executeHookObjectsFunction($hookObjectsArr, 'saveTodo', $this, $object);

		tx_cal_functions::clearCache();
		return $this->find($uid,$pid);
	}

	function _saveEvent(&$eventData, $object){
		$tempValues = array();
		$tempValues['notify_ids'] = $eventData['notify_ids'];
		unset($eventData['notify_ids']);
		$tempValues['exception_ids'] = $eventData['exception_ids'];
		unset($eventData['exception_ids']);
		$tempValues['attendee_ids'] = $eventData['attendee_ids'];
		unset($eventData['attendee_ids']);

		// Creating DB records
		$table = 'tx_cal_event';
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$eventData);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		//creating relation records
		if($this->rightsObj->isAllowedTo('create','todo','notify')){
			if($tempValues['notify_ids']!=''){
				$user = Array();
				$group = Array();
				$this->splitUserAndGroupIds(explode(',',strip_tags($tempValues['notify_ids'])),$user,$group);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',$user,$uid,'fe_users');
				$ignore = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['todo.']['addFeGroupToNotify.']['ignore'],1);
				$groupArray = array_diff($group,$ignore);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array_unique($groupArray),$uid,'fe_groups');
			}
		}else if($this->conf['rights.']['create.']['todo.']['fields.']['notify.']['defaultUser'] || $this->conf['rights.']['create.']['todo.']['fields.']['notify.']['defaultGroup']){
			$idArray = t3lib_div::trimExplode(',', $this->conf['rights.']['create.']['event.']['fields.']['notify.']['defaultUser'],1);
			if($this->conf['rights.']['create.']['event.']['addFeUserToNotify']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array_unique($idArray),$uid,'fe_users');
			$idArray = t3lib_div::trimExplode(',', $this->conf['rights.']['create.']['todo.']['fields.']['notify.']['defaultGroup'],1);
			if($this->conf['rights.']['create.']['todo.']['addFeGroupToNotify']){
				$idArray = array_merge($idArray, $this->rightsObj->getUserGroups());
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array_unique($idArray),$uid,'fe_groups');
		}else if($this->rightsObj->isLoggedIn() && $this->conf['rights.']['create.']['todo.']['addFeUserToNotify']){
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array($this->rightsObj->getUserId()),$uid,'fe_users');
		}
		if($this->conf['rights.']['create.']['todo.']['public']){
			$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['todo.']['notifyUsersOnPublicCreate'],1),$uid,'fe_users');
		}

		if($this->rightsObj->isAllowedTo('create','todo','shared')){
			$user = $object->getSharedUsers();
			$group = $object->getSharedGroups();
			if($this->conf['rights.']['create.']['todo.']['addFeUserToShared']){
				$user[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($user),$uid,'fe_users');
			$ignore = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['todo.']['addFeGroupToShared.']['ignore'],1);
			$groupArray = array_diff($group,$ignore);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($groupArray),$uid,'fe_groups');
		}else{
			$idArray = explode(',',$this->conf['rights.']['create.']['todo.']['fields.']['shared.']['defaultUser']);
			if($this->conf['rights.']['create.']['todo.']['addFeUserToShared']){
				$idArray[] = $this->rightsObj->getUserId();
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($idArray),$uid,'fe_users');
			
			$groupArray = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['todo.']['fields.']['shared.']['defaultGroup'],1);
			if($this->conf['rights.']['create.']['todo.']['addFeGroupToShared']){
				$idArray = $this->rightsObj->getUserGroups();
				$ignore = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['todo.']['addFeGroupToShared.']['ignore'],1);
				$groupArray = array_diff($idArray,$ignore);
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($groupArray),$uid,'fe_groups');
		}

		if($this->rightsObj->isAllowedTo('create','todo','category')){
			$categoryIds = Array();
			foreach((Array)$object->getCategories() as $category){
				if(is_object($category)){
					$categoryIds[] = $category->getUid();
				}
			}
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_category_mm',$categoryIds,$uid,'');
		}else{
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_category_mm',array($this->conf['rights.']['create.']['todo.']['fields.']['category.']['default']),$uid,'');
		}
		
		return $uid;
	}

	function updateEvent($uid){
		$insertFields = array('tstamp' => time());
		$tempCategoryConf = $this->conf['category'];
		

		$event = $this->find($uid, $this->conf['pidList'], true, true, false, false, false, '0,1,2,3,4');
		$event_old = $this->find($uid, $this->conf['pidList'], true, true, false, false, false, '0,1,2,3,4');
		//$event = new tx_cal_phpicalendar_model(null, false, '');
		$this->conf['category'] = $this->conf['view.']['allowedCategories'];
		$this->conf['calendar'] = $this->conf['view.']['allowedCalendar'];

		$event->updateWithPIVars($this->controller->piVars);
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'event',false);
		
		$this->filterDataToBeUpdated($insertFields, $event);

		$uid = $this->checkUidForLanguageOverlay($uid,'tx_cal_event');

		if(isset($this->controller->piVars['notify_ids'])) {
			$insertFields['notify_ids'] = strip_tags($this->controller->piVars['notify_ids']);
		}else if(is_array($this->controller->piVars['notify'])){
			$insertFields['notify_ids'] = strip_tags(implode(',',$this->controller->piVars['notify']));
		}
		if(isset($this->controller->piVars['exception_ids'])) {
			if(is_array($this->controller->piVars['exception_ids'])){
				$insertFields['exception_ids'] = strip_tags(implode(',',$this->controller->piVars['exception_ids']));
			}else{
				$insertFields['exception_ids'] = strip_tags($this->controller->piVars['exception_ids']);
			}
		}

		$this->_updateEvent($uid, $insertFields, $event);

		$this->_notifyOfChanges($event_old,$insertFields);
		if($event->getSendoutInvitation()){
			$this->_invite($event);
		}
		$this->unsetPiVars();
		
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if($extConf['useNewRecurringModel']){
			$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',$GLOBALS['TSFE']->id);
			$rgc->generateIndexForUid($uid, 'tx_cal_event');
		}
		
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

		// Hook: updateEvent
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_todo_service','todoServiceClass');
		tx_cal_functions::executeHookObjectsFunction($hookObjectsArr, 'updateTodo', $this, $event);
		
		tx_cal_functions::clearCache();
		return $event;
	}

	function _updateEvent($uid, $eventData, $object){
		$tempValues = array();
		$tempValues['notify_ids'] = $eventData['notify_ids'];
		unset($eventData['notify_ids']);
		$tempValues['exception_ids'] = $eventData['exception_ids'];
		unset($eventData['exception_ids']);
		$tempValues['attendee_ids'] = $eventData['attendee_ids'];
		unset($eventData['attendee_ids']);
		
		// Creating DB records
		$table = 'tx_cal_event';
		$where = 'uid = '.$uid;
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$eventData);

		$cal_user_ids = array();
		$where = ' AND tx_cal_event.uid='.$uid.' AND tx_cal_fe_user_category_mm.tablenames="fe_users" '.$this->cObj->enableFields('tx_cal_event');
		$orderBy = '';
		$groupBy = '';
		$limit = '';

		if($this->rightsObj->isAllowedTo('edit','todo','category')){
			$categoryIds = Array();
			foreach($object->getCategories() as $category){
				if(is_object($category)){
					$categoryIds[] = $category->getUid();
				}
			}
			$table = 'tx_cal_event_category_mm';
			$where = 'uid_local = '.$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			$this->insertIdsIntoTableWithMMRelation($table,$categoryIds,$uid,'');
		}

		if($this->rightsObj->isAllowedTo('edit','todo','notify') && !is_null($tempValues['notify_ids'])){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_fe_user_event_monitor_mm','uid_local ='.$uid.' AND tablenames in ("fe_users","fe_groups")');
			if($tempValues['notify_ids']!=''){
				$user = Array();
				$group = Array();
				$this->splitUserAndGroupIds(explode(',',strip_tags($tempValues['notify_ids'])),$user,$group);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',$user,$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',$group,$uid,'fe_groups');
			}
		}else{
			$userIdArray = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['todo.']['fields.']['notify.']['defaultUser'],1);
			if($this->conf['rights.']['edit.']['event.']['addFeUserToNotify']){
				$userIdArray[] = $this->rightsObj->getUserId();
			}
			
			$groupIdArray = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['todo.']['fields.']['notify.']['defaultGroup'],1);
			if($this->conf['rights.']['edit.']['todo.']['addFeGroupToNotify']){
				$groupIdArray = $this->rightsObj->getUserGroups();
				$ignore = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['todo.']['addFeGroupToNotify.']['ignore'],1);
				$groupIdArray = array_diff($groupIdArray,$ignore);
			}
			if(!empty($userIdArray) || !empty($groupIdArray)){
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_fe_user_event_monitor_mm','uid_local ='.$uid.' AND tablenames in ("fe_users","fe_groups")');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array_unique($userIdArray),$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_fe_user_event_monitor_mm',array_unique($groupIdArray),$uid,'fe_groups');
			}
		}
		
		if($this->rightsObj->isAllowedTo('edit','todo','shared')){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event_shared_user_mm','uid_local ='.$uid);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($object->getSharedUsers()),$uid,'fe_users');
			$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($object->getSharedGroups()),$uid,'fe_groups');
		}else{
			$userIdArray = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['todo.']['fields.']['shared.']['defaultUser'],1);
			if($this->conf['rights.']['edit.']['todo.']['addFeUserToShared']){
				$userIdArray[] = $this->rightsObj->getUserId();
			}
			
			$groupIdArray = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['todo.']['fields.']['shared.']['defaultGroup'],1);
			if($this->conf['rights.']['edit.']['event.']['addFeGroupToShared']){
				$groupIdArray = $this->rightsObj->getUserGroups();
				$ignore = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['todo.']['addFeGroupToShared.']['ignore'],1);
				$groupIdArray = array_diff($groupIdArray,$ignore);
			}
			if(!empty($userIdArray) || !empty($groupIdArray)){
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event_shared_user_mm','uid_local ='.$uid);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($userIdArray),$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_event_shared_user_mm',array_unique($groupIdArray),$uid,'fe_groups');
			}
		}
	}

	function removeEvent($uid){
		$event = $this->find($uid, $this->conf['pidList'], true, true);
		if (is_object($event) && $event->isUserAllowedToDelete()) {
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
			$fields['deleted'] = 1;
			$fields['tstamp'] = $updateFields['tstamp'];
			$this->_notify($fields);
			$this->stopReminder($uid);
			
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($extConf['useNewRecurringModel']){
				tx_cal_recurrence_generator::cleanIndexTableOfUid($uid, $table);
			}

			// Hook: removeEvent
			$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_todo_service','todoServiceClass');
			tx_cal_functions::executeHookObjectsFunction($hookObjectsArr, 'removeTodo', $this, $event);			
			tx_cal_functions::clearCache();
			$this->unsetPiVars();
		}
	}

	function filterDataToBeSaved(&$insertFields, &$object){
		$hidden = 0;
		if(isset($this->conf['rights.']['create.']['todo.']['fields.']['hidden.']['default']) && !$this->rightsObj->isAllowedTo('edit','todo','hidden')&& !$this->rightsObj->isAllowedTo('create','todo','hidden')){
			$hidden = $this->conf['rights.']['create.']['todo.']['fields.']['hidden.']['default'];
		} else if($object->isHidden() && $this->rightsObj->isAllowedTo('create','todo','hidden')){
			$hidden = 1;
		}
		$insertFields['hidden'] = $hidden;
		$insertFields['type'] = $object->getEventType();

		$insertFields['allday'] = $object->isAllday()?'1':'0';
		if(!$this->rightsObj->isAllowedTo('create','todo','allday')){
			$insertFields['allday'] = $this->conf['rights.']['create.']['todo.']['fields.']['allday.']['default'];
		}
		if($this->rightsObj->isAllowedTo('create','todo','calendar')){
			if($object->getCalendarUid()!=''){
				$insertFields['calendar_id'] = $object->getCalendarUid();
			}else if($this->conf['rights.']['create.']['todo.']['fields.']['calendar.']['default']){
				$insertFields['calendar_id'] = $this->conf['rights.']['create.']['todo.']['fields.']['calendar_id.']['default'];
			}else{
				$insertFields['calendar_id'] = ''; //TODO: Set the calendar_id to some value
			}
		}
		
		if($this->rightsObj->isAllowedTo('create','todo','starttime') || $this->rightsObj->isAllowedTo('create','todo','startdate')){
			if(is_object($object->getStart())){
				$start = $object->getStart();
				$insertFields['start_date'] = $start->format('%Y%m%d');
				$insertFields['start_time'] = intval($start->format('%H'))*3600+intval($start->format('%M'))*60;
			}else{
				return;
			}
			if(is_object($object->getEnd())){
				$end = $object->getEnd();
				$insertFields['end_date'] = $end->format('%Y%m%d');
				$insertFields['end_time'] = intval($end->format('%H'))*3600+intval($end->format('%M'))*60;
			}else{
				return;
			}
		}
		if($this->rightsObj->isAllowedTo('create','todo','title')){
			$insertFields['title'] = $object->getTitle();;
		}

		if($this->rightsObj->isAllowedTo('create','todo','organizer')){
			$insertFields['organizer'] = $object->getOrganizer();
		}
		if($this->rightsObj->isAllowedTo('create', 'todo', 'cal_organizer')){
			$insertFields['organizer_id'] = $object->getOrganizerId();
		}
		if($this->rightsObj->isAllowedTo('create','todo','location')){
			$insertFields['location'] = $object->getLocation();
		}
		if($this->rightsObj->isAllowedTo('create', 'todo', 'cal_location')){
			$insertFields['location_id'] = $object->getLocationId();
		}
		if($object->getDescription()!='' && $this->rightsObj->isAllowedTo('create','todo','description')){
			$insertFields['description'] = $object->getDescription();
		}
		if($this->rightsObj->isAllowedTo('create','todo','recurring')){
			$insertFields['freq'] = $object->getFreq();
			$insertFields['byday'] = strtolower(implode(',',$object->getByDay()));
			$insertFields['bymonthday'] = implode(',',$object->getByMonthDay());
			$insertFields['bymonth'] = implode(',',$object->getByMonth());
			$until = $object->getUntil();
			if(is_object($until)) {
				$insertFields['until'] = $until->format('%Y%m%d');
			}
			$insertFields['cnt'] = $object->getCount();
			$insertFields['intrval'] = $object->getInterval();
		}
		if($this->rightsObj->isAllowedTo('create','todo','image')){
			$this->checkOnNewOrDeletableFiles('tx_cal_event', 'image', $insertFields);
			$insertFields['imagecaption'] = implode(chr(10),$object->getImageCaption());
			$insertFields['imagealttext'] = implode(chr(10),$object->getImageAltText());
			$insertFields['imagetitletext'] = implode(chr(10),$object->getImageTitleText());
		}
		
		if($this->rightsObj->isAllowedTo('create','todo','attachment')){
			$this->checkOnNewOrDeletableFiles('tx_cal_event', 'attachment', $insertFields);
			$insertFields['attachmentcaption'] = implode(chr(10),$object->getAttachmentCaption());
		}
		
		if($this->rightsObj->isAllowedTo('create','todo','status')){
			$insertFields['status'] = $object->getStatus();
		}
		
		if($this->rightsObj->isAllowedTo('create','todo','priority')){
			$insertFields['priority'] = $object->getPriority();
		}
		
		if($this->rightsObj->isAllowedTo('create','todo','completed')){
			$insertFields['completed'] = $object->getCompleted();
		}

		// Hook initialization:
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_todo_service.php']['addAdditionalField'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_todo_service.php']['addAdditionalField'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		foreach($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'addAdditionalField')) {
				$hookObj->addAdditionalField($insertFields, $this);
			}
		}
	}
	
	function filterDataToBeUpdated(&$insertFields, &$object){
		$hidden = 0;
		if(isset($this->conf['rights.']['edit.']['todo.']['fields.']['hidden.']['default']) && !$this->rightsObj->isAllowedTo('edit','todo','hidden') && !$this->rightsObj->isAllowedTo('create','todo','hidden')){
			$hidden = $this->conf['rights.']['edit.']['todo.']['fields.']['hidden.']['default'];
		}else if($object->isHidden() && $this->rightsObj->isAllowedToEditEventHidden()){
			$hidden = 1;
		}
		$insertFields['hidden'] = $hidden;
		
		if($this->rightsObj->isAllowedTo('edit','todo','type')){
			$insertFields['type'] = $object->getEventType();
		}

		$insertFields['allday'] = $object->isAllday()?'1':'0';
		if(!$this->rightsObj->isAllowedTo('edit','todo','allday')){
			$insertFields['allday'] = $this->conf['rights.']['edit.']['todo.']['fields.']['allday.']['default'];
		}

		if($this->rightsObj->isAllowedTo('edit','todo','calendar')){
			if($object->getCalendarUid()!=''){
				$insertFields['calendar_id'] = $object->getCalendarUid();
			}else if($this->conf['rights.']['edit.']['todo.']['fields.']['calendar.']['default']){
				$insertFields['calendar_id'] = $this->conf['rights.']['edit.']['todo.']['fields.']['calendar_id.']['default'];
			}else{
				$insertFields['calendar_id'] = ''; //TODO: Set the calendar_id to some value
			}
		}
		
		if($this->rightsObj->isAllowedTo('edit','todo','starttime') || $this->rightsObj->isAllowedTo('edit','todo','startday')){
			if(is_object($object->getStart())){
				$start = $object->getStart();
				$insertFields['start_date'] = $start->format('%Y%m%d');
				$insertFields['start_time'] = intval($start->format('%H'))*3600+intval($start->format('%M'))*60;
			}else{
				return;
			}
			if(is_object($object->getEnd())){
				$end = $object->getEnd();
				$insertFields['end_date'] = $end->format('%Y%m%d');
				$insertFields['end_time'] = intval($end->format('%H'))*3600+intval($end->format('%M'))*60;
			}else{
				return;
			}
		}
		if($this->rightsObj->isAllowedTo('edit','todo','title')){
			$insertFields['title'] = $object->getTitle();
		}

		if($this->rightsObj->isAllowedTo('edit','todo','organizer')){
			$insertFields['organizer'] = $object->getOrganizer();
		}
		if($this->rightsObj->isAllowedTo('edit', 'todo', 'cal_organizer')){
			$insertFields['organizer_id'] = $object->getOrganizerId();
		}
		if($this->rightsObj->isAllowedTo('edit','todo','location')){
			$insertFields['location'] = $object->getLocation();
		}
		if($this->rightsObj->isAllowedTo('edit', 'todo', 'cal_location')){
			$insertFields['location_id'] = $object->getLocationId();
		}
		if($object->getDescription()!='' && $this->rightsObj->isAllowedTo('edit','todo','description')){
			$insertFields['description'] = $object->getDescription();
		}
		if($this->rightsObj->isAllowedTo('edit','todo','recurring')){
			$insertFields['freq'] = $object->getFreq();
			$insertFields['byday'] = strtolower(implode(',',$object->getByDay()));
			$insertFields['bymonthday'] = implode(',',$object->getByMonthDay());
			$insertFields['bymonth'] = implode(',',$object->getByMonth());
			$until = $object->getUntil();
			$insertFields['until'] = $until->format('%Y%m%d');
			$insertFields['cnt'] = $object->getCount();
			$insertFields['intrval'] = $object->getInterval();
			$insertFields['rdate_type'] = $object->getRdateType();
			$insertFields['rdate'] = $object->getRdate();
		}
		if($this->rightsObj->isAllowedTo('edit','todo','image')){
			$this->checkOnNewOrDeletableFiles('tx_cal_event', 'image', $insertFields);
			$insertFields['imagecaption'] = implode(chr(10),$object->getImageCaption());
			$insertFields['imagealttext'] = implode(chr(10),$object->getImageAltText());
			$insertFields['imagetitletext'] = implode(chr(10),$object->getImageTitleText());
		}
		
		if($this->rightsObj->isAllowedTo('edit','todo','attachment')){
			$this->checkOnNewOrDeletableFiles('tx_cal_event', 'attachment', $insertFields);
			$insertFields['attachmentcaption'] = implode(chr(10),$object->getAttachmentCaption());
		}
		
		if($this->rightsObj->isAllowedTo('edit','todo','status')){
			$insertFields['status'] = $object->getStatus();
		}
		
		if($this->rightsObj->isAllowedTo('edit','todo','priority')){
			$insertFields['priority'] = $object->getPriority();
		}
		
		if($this->rightsObj->isAllowedTo('edit','todo','completed')){
			$insertFields['completed'] = $object->getCompleted();
		}

		// Hook initialization:
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_todo_service.php']['addAdditionalField'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/service/class.tx_cal_todo_service.php']['addAdditionalField'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		foreach($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'addAdditionalField')) {
				$hookObj->addAdditionalField($insertFields, $this);
			}
		}
	}
	
	function search($pidList='', $start_date, $end_date, $searchword, $locationIds='', $organizerIds='', $eventType='0,1,2,3'){
		return parent::search($pidList, $start_date, $end_date, $searchword, $locationIds, $organizerIds, '4');
	}
	
	function getRecurringEventsFromIndex($event) {
		$master_array = Array();
		$startDate = $event->getStart();
		$master_array[$startDate->format('%Y%m%d')][$event->isAllday()?'-1':($startDate->format('%H%M'))][$event->getUid()] = &$event;
		$select = '*';
		$table = 'tx_cal_index';
		$where = 'event_uid = '.$event->getUid().' AND start_datetime >= '.$this->starttime->format('%Y%m%d%H%M%S').' AND start_datetime <= '.$this->endtime->format('%Y%m%d%H%M%S');
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
		if($result) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$nextOccuranceTime = new tx_cal_date($row['start_datetime']);
				$nextOccuranceEndTime = new tx_cal_date($row['end_datetime']);
				$new_event = &tx_cal_functions::makeInstance('tx_cal_todo_rec_model',$event,$nextOccuranceTime,$nextOccuranceEndTime);
				if($new_event->isAllday()){
					$master_array[$nextOccuranceTime->format('%Y%m%d')]['-1'][$event->getUid()] = $new_event;
				}else{
					$master_array[$nextOccuranceTime->format('%Y%m%d')][$nextOccuranceTime->format('%H%M')][$event->getUid()] = $new_event;
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
		return $master_array;
	}

	function unsetPiVars(){
		parent::unsetPivars();
		unset($this->controller->piVars['priority']);
		unset($this->controller->piVars['completed']);
		unset($this->controller->piVars['status']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_todo_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_todo_service.php']);
}
?>