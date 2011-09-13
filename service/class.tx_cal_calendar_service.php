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

require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_calendar_model.php');

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_calendar_service extends tx_cal_base_service {
	
	var $calendarSearchString;
	var $fnbCalendarSearchString;
	var $calendarOwner;	
	var $calendarIds;

	function tx_cal_calendar_service(){
		$this->tx_cal_base_service();
	}
	
	function createCalendar($row){
		$tx_cal_calendar_model = &t3lib_div::makeInstanceClassName('tx_cal_calendar_model');
		$calendar = &new $tx_cal_calendar_model('', $row, $this->getServiceKey());	
		return $calendar;	
	}
	
	/**
	 * Looks for a calendar with a given uid on a certain pid-list
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array		An array ($row)
	 */
	function find($uid, $pidList){
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' uid='.$uid.' '.$this->cObj->enableFields('tx_cal_calendar'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' pid IN ('.$pidList.') AND uid='.$uid.' '.$this->cObj->enableFields('tx_cal_calendar'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			return $this->createCalendar($row);
		}
	}
	
	
	/**
	 * Looks for all calendars on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	An array of array (array of $rows)
	 */
	function findAll($pidList){
		$return = array();
		$orderBy = getOrderBy('tx_cal_calendar');
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' 0 = 0'.$this->cObj->enableFields('tx_cal_calendar'), '', $orderBy);
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_calendar'), '', $orderBy);
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$return[] = $this->createCalendar($row);
		}
		return $return;
	}
	
	function updateCalendar($uid){

		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'calendar',false);
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$table = 'tx_cal_calendar';
		$where = 'uid = '.$uid;

		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
		if($this->rightsObj->isAllowedToEditCalendarOwner()){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_user_group_mm','uid_local ='.$uid);
			$owner_single = strip_tags($this->controller->piVars['owner_single']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',explode(',', $owner_single),$uid,'fe_users');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_user_group_mm','uid_local ='.$uid);
			$owner_group = strip_tags($this->controller->piVars['owner_group']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',explode(',', $owner_group),$uid,'fe_groups');
		}
		if($this->rightsObj->isAllowedToEditCalendarFreeAndBusyUser()){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_fnb_user_group_mm','uid_local ='.$uid);
			$freeAndBusyUser_single = strip_tags($this->controller->piVars['freeAndBusyUser_single']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',explode(',', $freeAndBusyUser_single),$uid,'fe_users');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_fnb_user_group_mm','uid_local ='.$uid);
			$freeAndBusyUser_group = strip_tags($this->controller->piVars['freeAndBusyUser_group']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',explode(',', $freeAndBusyUser_group),$uid,'fe_groups');
		}
		$this->unsetPiVars();
	}
	
	function removeCalendar($uid){
		if($this->rightsObj->isAllowedToDeleteCalendar()){
			// 'delete' the calendar object
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tx_cal_calendar';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
			
			// 'delete' all the events related to the calendar
			$table = 'tx_cal_event';
			$where = 'calendar_id = '.$uid;
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
		$this->unsetPiVars();
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=='true' && 
				($this->rightsObj->isAllowedToEditCalendarHidden() || $this->rightsObj->isAllowedToCreateCalendarHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
		
		if($this->rightsObj->isAllowedToEditCalendarTitle() || $this->rightsObj->isAllowedToCreateCalendarTitle()){
			$insertFields['title'] = strip_tags($this->controller->piVars['title']);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarType() || $this->rightsObj->isAllowedToCreateCalendarType()){
			$insertFields['type'] = strip_tags($this->controller->piVars['calendarType']);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarType() || $this->rightsObj->isAllowedToCreateCalendarType()){
			$insertFields['ext_url'] = strip_tags($this->controller->piVars['exturl']);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarType() || $this->rightsObj->isAllowedToCreateCalendarType()){
			$insertFields['ics_file'] = strip_tags($this->controller->piVars['icsFile']);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarType() || $this->rightsObj->isAllowedToCreateCalendarType()){
			$insertFields['refresh'] = strip_tags($this->controller->piVars['refresh']);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarActivateFreeAndBusy() || $this->rightsObj->isAllowedToCreateCalendarActivateFreeAndBusy()){
			$insertFields['activate_fnb'] = strip_tags($this->controller->piVars['activateFreeAndBusy']);
		}
		
	}
	
	function saveCalendar($pid){

		$crdate = time();
		$insertFields = array('pid' => $this->conf['rights.']['create.']['calendar.']['saveCalendarToPid']?$this->conf['rights.']['create.']['calendar.']['saveCalendarToPid']:$pid, 'tstamp' => $crdate, 'crdate' => $crdate);
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'calendar');
		$this->retrievePostData($insertFields);

		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$table = 'tx_cal_calendar';

		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		if($this->rightsObj->isAllowedToCreateCalendarOwner()){
			$owner_single = strip_tags($this->controller->piVars['owner_single']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',explode(',', $owner_single),$uid,'fe_users');
			$owner_group = strip_tags($this->controller->piVars['owner_group']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',explode(',', $owner_group),$uid,'fe_groups');
		}
		if($this->rightsObj->isAllowedToCreateCalendarFreeAndBusyUser()){
			$freeAndBusyUser_single = strip_tags($this->controller->piVars['freeAndBusyUser_single']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',explode(',', $freeAndBusyUser_single),$uid,'fe_users');
			$freeAndBusyUser_group = strip_tags($this->controller->piVars['freeAndBusyUser_group']);
			$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',explode(',', $freeAndBusyUser_group),$uid,'fe_groups');
		}
		$this->unsetPiVars();
	}
	
	function getCalendarSearchString($pidList, $includePublic, $linkIds){
	
		if($this->conf['view.']['freeAndBusy.']['enable'] && $this->conf['option']=='freeandbusy'){
			return $this->getFreeAndBusyCalendarSearchString($pidList, $includePublic, $linkIds);
		}
		
		if($this->calendarSearchString){
			return $this->calendarSearchString;
		}

		$idArray = $this->getIdsFromTable($linkIds,$pidList, $includePublic);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar_user_group_mm', '');

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid_local'];
			$calendarOwner[$row['uid_local']][$row['tablenames']][] = $row['uid_foreign'];
		}
		
		if(is_array($ids)){
			$idString = implode(',',array_unique($ids));
			$calendarSearchString = ' AND tx_cal_calendar.uid NOT IN ('.$idString.')';
		}
		if($pidList>0){
			$calendarSearchString .= ' AND tx_cal_calendar.pid IN ('.$pidList.')';
		}

		// Check the results
		if(empty($idArray)){
			// No calendar ids specified for this user -> show default		
		}else if($linkIds!=''){
			// compair the allowed ids with the ids available and retrieve the intersects
			$calendarIds = array_intersect($idArray,split(',',$linkIds));
			if(empty($calendarIds)){
				// No intersects -> show default
			}else{
				// create a string for the query
				$calendarIds = implode(',',$calendarIds);
				$calendarSearchString = ' AND tx_cal_calendar.uid IN ('.$calendarIds.')';
			}
		}else{
			$calendarIds = implode(',',$idArray);
			$calendarSearchString = ' AND tx_cal_calendar.uid IN ('.$calendarIds.')';
		}
		$this->calendarOwner = $calendarOwner;

		$this->calendarSearchString = $calendarSearchString;
//debug($calendarSearchString);
		return $calendarSearchString;
	}
	
	function getFreeAndBusyCalendarSearchString($pidList, $includePublic, $linkIds){
//debug($this->fnbCalendarSearchString);		
		if($this->fnbCalendarSearchString){
			return $this->fnbCalendarSearchString;
		}
		
		$calendarSearchString = '';
		$freeNBusyCalendar = array();
		$calendarOwner = array();
		$ids = array();
		$excludeIds = array();
		$idArray = $this->getIdsFromTable($linkIds,$pidList, $includePublic);

		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar_fnb_user_group_mm', '');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$excludeIds[] = $row['uid_local'];
			$calendarOwner[$row['uid_local']][$row['tablenames']][] = $row['uid_foreign'];
		}
		
		if($this->rightsObj->isLoggedIn()){
			$groups = $this->rightsObj->getUserGroups();
			$userId = $this->rightsObj->getUserId();
			$where = '(tablenames = "fe_users" AND uid_foreign = '.$userId.') OR (tablenames = "fe_groups" AND uid_foreign in ('.implode(',',$groups).'))';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar_fnb_user_group_mm', $where);
	
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$ids[] = $row['uid_local'];
				$calendarOwner[$row['uid_local']][$row['tablenames']][] = $row['uid_foreign'];
			}
		}
		
		$where = 'tx_cal_calendar.activate_fnb = 1';
		if(!empty($excludeIds)){
			$where .= ' AND uid not in ('.implode(',',$excludeIds).')';
		}
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', $where);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid'];
		}
		
//debug($ids);
		$calendarSearchString = '';		
//		$idString = implode(',',$ids);
		if($linkIds!=''){
			// compair the allowed ids with the ids available and retrieve the intersects
			$calendarIds = array_intersect($ids,split(',',$linkIds));
			if(empty($calendarIds)){
				// No intersects -> show default
				$calendarIds = implode(',',$ids);
				$calendarSearchString = ' AND tx_cal_calendar.uid IN ('.$calendarIds.')';
			}else{
				// create a string for the query
				$calendarIds = implode(',',$calendarIds);
				$calendarSearchString = ' AND tx_cal_calendar.uid IN ('.$calendarIds.')';
			}
		}else{
			$calendarSearchString = ' AND tx_cal_calendar.uid IN ('.implode(',',$ids).')';
		}
		
//debug($calendarSearchString);	
		$this->calendarOwner = $calendarOwner;
		$this->fnbCalendarSearchString = $calendarSearchString;
		return $calendarSearchString;
	}
	
	function getIdsFromTable($list, $pidList, $includePublic, $includeData=false, $onlyPublic=false){
		
		$this->calendarIds = array();
		$collectedIds = array();
		
		//Logged in? Show public & private calendar
		
		// calendar ids specified? show these calendar only - if allowed - else show public calendar
	
		$limitationList='';
		if($list!=''){ //$this->conf['calendar']
			$limitationList = $list;
		}
		
		// Lets see if the user is logged in
		if($this->rightsObj->isLoggedIn() && !$onlyPublic){
			$userId = $this->rightsObj->getUserId();
			$groupIds = implode(',',$this->rightsObj->getUserGroups());	
		}	
	
		$ids = array();
		if($userId===''){ //  && !$includePublic
			return $ids;
		}
		if($includeData){
			$select = 'tx_cal_calendar.*';
		}else{
			$select = 'tx_cal_calendar.uid';
		}
		
		$orderBy = getOrderBy('tx_cal_calendar');
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local', 'tx_cal_calendar, tx_cal_calendar_user_group_mm', 'tx_cal_calendar.uid=tx_cal_calendar_user_group_mm.uid_local '.$this->cObj->enableFields('tx_cal_calendar'), '',  $orderBy);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid_local'];
		}
		$ids=array_unique($ids);
//debug($ids);	
		if($includePublic){
			
			if(!empty($ids)){
				$where = 'uid NOT IN ('.implode(',',$ids).') '.$this->cObj->enableFields('tx_cal_calendar');
			}else{
				$where = '0=0 '.$this->cObj->enableFields('tx_cal_calendar');
			}
			if($pidList!=''){
				$where .= ' AND pid IN ('.$pidList.')';
			}
			
			if($includeData){
				$select = '*';
			}else{
				$select = 'uid';
			}
			$table = 'tx_cal_calendar';
			
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, '', $orderBy);

			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(!in_array($row['uid'],$collectedIds)){
					if($includeData){
						$this->calendarIds[] = $row;
					}else{
						$this->calendarIds[] = $row['uid'];
					}
					$collectedIds[] = $row['uid'];
				}
			}
		}
		if($this->conf['view.']['freeAndBusy.']['enable']){
			$where = 'activate_fnb = 1 '.$this->cObj->enableFields('tx_cal_calendar');
			if($pidList!=''){
				$where .= ' AND pid IN ('.$pidList.')';
			}
			$table = 'tx_cal_calendar';
			//TODO: Limitation by fe_users or fe_groups
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, '', $orderBy);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(!in_array($row['uid'],$collectedIds)){
					if($includeData){
						$this->calendarIds[] = $row;
					}else{
						$this->calendarIds[] = $row['uid'];
					}
					$collectedIds[] = $row['uid'];
				}
			}
		}

		if(!$onlyPublic){
			if(!empty($ids)){
				$where = 'uid NOT IN ('.implode(',',$ids).')';
			}else{
				$where = '';
			}
			$table = 'tx_cal_calendar';
			if($includeData){
				$select = '*';
			}else{
				$select = 'uid';
			}
			if($userId){
				$where = '((tx_cal_calendar_user_group_mm.uid_foreign IN ('.$userId.') AND tx_cal_calendar_user_group_mm.tablenames="fe_users" AND tx_cal_calendar_user_group_mm.uid_local=tx_cal_calendar.uid)';
				$where .= 'OR (tx_cal_calendar_user_group_mm.uid_foreign IN ('.$groupIds.') AND tx_cal_calendar_user_group_mm.tablenames="fe_groups" AND tx_cal_calendar_user_group_mm.uid_local=tx_cal_calendar.uid))';
				$table .= ', tx_cal_calendar_user_group_mm';
			}
			
			if($pidList!=''){
				$where .= strlen($where)? ' AND pid IN ('.$pidList.')' : ' pid IN ('.$pidList.')';
			}
			if($where==''){
				$where .= ' 0=0 '.$this->cObj->enableFields('tx_cal_calendar');
			}else{
				$where .= $this->cObj->enableFields('tx_cal_calendar');
			}
			if($limitationList!=''){
				$where .= ' AND uid IN ('.$limitationList.')';
			}
			$groupBy = 'tx_cal_calendar.uid';
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where.' GROUP BY '.$groupBy);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table ,$where, $groupBy, $orderBy);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(!in_array($row['uid'],$collectedIds)){
					if($includeData){
						$this->calendarIds[] = $row;
					}else{
						$this->calendarIds[] = $row['uid'];
					}
					$collectedIds[] = $row['uid'];
				}
			}
		}
		
		if($limitationList!='' && !empty($this->calendarIds)){
			$limitationArray = split(',',$limitationList);
			$this->calendarIds = array_intersect($this->calendarIds,$limitationArray);
		}
//t3lib_div::debug($this->calendarIds);
		return $this->calendarIds;
	}
	
	/**
	 * Call this after you have called getCalendarSearchString or getFreeAndBusyCalendarSearchString
	 */
	function getCalendarOwner(){
		return $this->calendarOwner;
	}
	
	function isFreeAndBusyViewAllowed($calendarIds){
		if(!$calendarIds) return false;

		if(is_array($calendarIds)){
			$calendarIds = implode(',',$calendarIds);
		}
		$privateFreeAndBusyCalendar = array();
		
		if($this->rightsObj->isLoggedIn()){
			// get all ids from fnb relation table
			$select = 'uid';
			$table = 'tx_cal_calendar_fnb_user_group_mm, tx_cal_calendar';
			$where = 'tx_cal_calendar_fnb_user_group_mm.uid_local = tx_cal_calendar.uid' .
					' AND tx_cal_calendar.activate_fnb = 1 '.$this->cObj->enableFields('tx_cal_calendar').
					' AND ((uid_foreign = '.$this->rightsObj->getUserId().' AND tablenames="fe_users")' .
					' OR (uid_foreign in ('.implode(',',$this->rightsObj->getUserGroups()).')' .
							' AND tablenames="fe_groups"))';
			$groupby = 'uid';
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where.' GROUP BY '.$groupby);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby);
		
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$privateFreeAndBusyCalendar[] = $row['uid'];
			}		
		}
		// get all ids from fnb relation table
		$select = 'uid_local';
		$table = 'tx_cal_calendar_fnb_user_group_mm';
		$where = '';
		$groupby = 'uid_local';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where, $groupby);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$freeAndBusyCalendar[] = $row['uid_local'];
		}		
		$publicFreeAndBusyCalendar = array();
		$select = 'uid';
		$table = 'tx_cal_calendar';
		$where = 'tx_cal_calendar.activate_fnb = 1 '.$this->cObj->enableFields('tx_cal_calendar');
		if(!empty($freeAndBusyCalendar)){
			$where .= ' AND tx_cal_calendar.uid not in ('.implode(',',$freeAndBusyCalendar).')';
		}
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$publicFreeAndBusyCalendar[] = $row['uid'];
		}
		if(empty($publicFreeAndBusyCalendar) && empty($privateFreeAndBusyCalendar)){
			return false;
		}
		$allowedFnbCalendars = array_merge($privateFreeAndBusyCalendar, $publicFreeAndBusyCalendar);
		$calendarIdsWantedAndAllowed = array_intersect($allowedFnbCalendars, split(',',$calendarIds));
		if(empty($calendarIdsWantedAndAllowed)){
			return false;
		}
		return true;
	}
	
	function unsetPiVars(){
		unset($this->controller->piVars['hidden']);
		unset($this->controller->piVars['uid']);
		unset($this->controller->piVars['calendar']);
		unset($this->controller->piVars['type']);
		unset($this->controller->piVars['calendarType']);
		unset($this->controller->piVars['owner']);
		unset($this->controller->piVars['owner_single']);
		unset($this->controller->piVars['owner_group']);
		unset($this->controller->piVars['freeAndBusyUser_single']);
		unset($this->controller->piVars['freeAndBusyUser_group']);
		unset($this->controller->piVars['freeAndBusyUser']);
		unset($this->controller->piVars['refresh']);
		unset($this->controller->piVars['title']);
		unset($this->controller->piVars['activateFreeAndBusy']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_calendar_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_calendar_service.php']);
}
?>