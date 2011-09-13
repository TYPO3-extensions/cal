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

require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_calendar_model.php');
require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_calendar_service extends tx_cal_base_service {
	
	var $calendarSearchStringCache = Array();
	var $calendarOwner;	
	var $calendarIds;

	function tx_cal_calendar_service(){
		$this->tx_cal_base_service();
	}
	
	function createCalendar($row){
		$calendar = &tx_cal_functions::makeInstance('tx_cal_calendar_model',$row, $this->getServiceKey());	
		return $calendar;	
	}
	
	/**
	 * Looks for a calendar with a given uid on a certain pid-list
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array		An array ($row)
	 */
	function find($uid, $pidList){
		$calendarArray = $this->getCalendarFromTable($pidList, ' AND uid='.$uid);
		return $calendarArray[0];
	}
	
	
	/**
	 * Looks for all calendars on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	An array of array (array of $rows)
	 */
	function findAll($pidList){
		return 	$this->getCalendarFromTable($pidList, $this->getCalendarSearchString($pidList, true, $this->conf['calendar']));
	}
	
	function getCalendarFromTable($pidList='',$additionalWhere=''){
		$return = array();
		$orderBy = tx_cal_functions::getOrderBy('tx_cal_calendar');
		if($pidList!=''){
			$additionalWhere .= ' AND pid IN ('.$pidList.')';
		}
		$additionalWhere .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_calendar');

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', '1=1'.$this->cObj->enableFields('tx_cal_calendar').$additionalWhere, '', $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				if ($GLOBALS['TSFE']->sys_language_content) {
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cal_calendar', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
				}
				if(!$row['uid']){
					continue;
				}
				
				$GLOBALS['TSFE']->sys_page->versionOL('tx_cal_calendar',$row);
				$GLOBALS['TSFE']->sys_page->fixVersioningPid('tx_cal_calendar', $row);

				if(!$row['uid']){
					continue;
				}
				$return[] = $this->createCalendar($row);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
		return $return;
	}
	
	function updateCalendar($uid){

		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'calendar',false);
		$this->retrievePostData($insertFields);
		$uid = $this->checkUidForLanguageOverlay($uid,'tx_cal_calendar');
		// Creating DB records
		$table = 'tx_cal_calendar';
		$where = 'uid = '.$uid;

		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
		require_once(t3lib_extMgm::extPath('cal').'hooks/class.tx_cal_tcemain_processdatamap.php');
		$service = t3lib_div::makeInstance('tx_cal_icalendar_service');

		if(($insertFields['type'] == 1 && $insertFields['ext_url']) or ($insertFields['type'] == 2 && $insertFields['ics_file'])) {
			tx_cal_tcemain_processdatamap::processICS(t3lib_BEfunc::getRecord ('tx_cal_calendar', $uid), $insertFields, $service);
			
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($extConf['useNewRecurringModel']){
				$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',$GLOBALS['TSFE']->id);
				$rgc->generateIndexForCalendarUid($uid);
			}
		} else {
				$service->deleteTemporaryEvents($uid);
				
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				if($extConf['useNewRecurringModel']){
					require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
					tx_cal_recurrence_generator::cleanIndexTableOfCalendarUid($uid);
				}
		}

		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
		if($this->rightsObj->isAllowedToEditCalendarOwner()){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_user_group_mm','uid_local ='.$uid);
			if($this->controller->piVars['owner_ids']!=''){
				$user = Array();
				$group = Array();
				$this->splitUserAndGroupIds(explode(',',strip_tags($this->controller->piVars['owner_ids'])),$user,$group);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',$user,$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',$group,$uid,'fe_groups');
			}
		}
		if($this->rightsObj->isAllowedToEditCalendarFreeAndBusyUser()){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_fnb_user_group_mm','uid_local ='.$uid);
			if($this->controller->piVars['freeAndBusyUser_ids']!=''){
				$user = Array();
				$group = Array();
				$this->splitUserAndGroupIds(explode(',',strip_tags($this->controller->piVars['freeAndBusyUser_ids'])),$user,$group);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',$user,$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',$group,$uid,'fe_groups');
			}
		}
		$this->unsetPiVars();
		tx_cal_functions::clearCache();
		return $this->find($uid, $this->conf['pidList']);
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
		tx_cal_functions::clearCache();
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if($extConf['useNewRecurringModel']){
			tx_cal_recurrence_generator::cleanIndexTableOfCalendarUid($uid);
		}
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
			$this->checkOnNewOrDeletableFiles('tx_cal_calendar', 'ics_file', $insertFields);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarType() || $this->rightsObj->isAllowedToCreateCalendarType()){
			$insertFields['refresh'] = strip_tags($this->controller->piVars['refresh']);
		}
		
		if($this->rightsObj->isAllowedToEditCalendarActivateFreeAndBusy() || $this->rightsObj->isAllowedToCreateCalendarActivateFreeAndBusy()){
			$insertFields['activate_fnb'] = strip_tags($this->controller->piVars['activateFreeAndBusy']);
		}
		
		if($this->rightsObj->isAllowedTo('edit','calendar','headerstyle') || $this->rightsObj->isAllowedTo('create','calendar','headerstyle')){
			$insertFields['headerstyle'] = strip_tags($this->controller->piVars['headerstyle']);
		}
		
		if($this->rightsObj->isAllowedTo('edit','calendar','bodystyle') || $this->rightsObj->isAllowedTo('create','calendar','bodystyle')){
			$insertFields['bodystyle'] = strip_tags($this->controller->piVars['bodystyle']);
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
		$insertFields['owner_ids'] = strip_tags($this->controller->piVars['owner_ids']);
		$insertFields['freeAndBusyUser_ids'] = strip_tags($this->controller->piVars['freeAndBusyUser_ids']);

		$uid = $this->_saveCalendar($insertFields);
		$this->unsetPiVars();
		tx_cal_functions::clearCache();
		return $this->find($uid, $this->conf['pidList']);
	}
	
	function _saveCalendar(&$insertFields){
		$tempValues = array();
		$tempValues['owner_ids'] = $insertFields['owner_ids'];
		unset($insertFields['owner_ids']);
		$tempValues['freeAndBusyUser_ids'] = $insertFields['freeAndBusyUser_ids'];
		unset($insertFields['freeAndBusyUser_ids']);
		
		
		$table = 'tx_cal_calendar';
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		if($insertFields['type'] == 1 or $insertFields['type'] == 2) {
			require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
			require_once(t3lib_extMgm::extPath('cal').'hooks/class.tx_cal_tcemain_processdatamap.php');

			$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
			tx_cal_tcemain_processdatamap::processICS(t3lib_BEfunc::getRecord ('tx_cal_calendar', $uid), $insertFields, $service);
			
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($extConf['useNewRecurringModel']){
				$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',$GLOBALS['TSFE']->id);
				$rgc->generateIndexForCalendarUid($uid);
			}
		}
		
		if($this->rightsObj->isAllowedToCreateCalendarOwner()){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_user_group_mm','uid_local ='.$uid);
			if($tempValues['owner_ids']!=''){
				$user = Array();
				$group = Array();
				$this->splitUserAndGroupIds(explode(',',strip_tags($tempValues['owner_ids'])),$user,$group);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',$user,$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_user_group_mm',$group,$uid,'fe_groups');
			}
		}
		if($this->rightsObj->isAllowedToCreateCalendarFreeAndBusyUser()){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_calendar_fnb_user_group_mm','uid_local ='.$uid);
			if($tempValues['freeAndBusyUser_ids']!=''){
				$user = Array();
				$group = Array();
				$this->splitUserAndGroupIds(explode(',',strip_tags($tempValues['freeAndBusyUser_ids'])),$user,$group);
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',$user,$uid,'fe_users');
				$this->insertIdsIntoTableWithMMRelation('tx_cal_calendar_fnb_user_group_mm',$group,$uid,'fe_groups');
			}
		}
		return $uid;
	}
	
	function getCalendarSearchString($pidList, $includePublic, $linkIds){
		
		$hash = md5($pidList.' '.$includePublic.' '.$linkIds);
		if($this->calendarSearchStringCache[$hash]){
			return $this->calendarSearchStringCache[$hash];
		}
		
		$calendarSearchString = '';

		$idArray = $this->getIdsFromTable($linkIds,$pidList, $includePublic);
		
		$ids = array_keys($this->getCalendarOwner());
		
		if(is_array($ids) && !empty($ids)){
			$idString = implode(',',array_unique($ids));
			$calendarSearchString = ' AND tx_cal_calendar.uid NOT IN ('.$idString.')';
		}
		if($pidList>0){
			$calendarSearchString .= ' AND tx_cal_calendar.pid IN ('.$pidList.')';
		}
		
		$calendarSearchString .= ' AND tx_cal_calendar.activate_fnb = 0';

		// Check the results
		if(empty($idArray)){
			// No calendar ids specified for this user -> show default		
		}else if($linkIds!=''){
			// compair the allowed ids with the ids available and retrieve the intersects
			$calendarIds = array_intersect($idArray,explode(',',$linkIds));
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
		
		$this->calendarSearchStringCache[$hash] = $calendarSearchString;

//debug($calendarSearchString);
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
		
		$orderBy = tx_cal_functions::getOrderBy('tx_cal_calendar');
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_calendar_user_group_mm.uid_local', 'tx_cal_calendar_user_group_mm LEFT JOIN tx_cal_calendar ON tx_cal_calendar.uid=tx_cal_calendar_user_group_mm.uid_local', '1=1 '.$this->cObj->enableFields('tx_cal_calendar'), '',  $orderBy);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$ids[] = $row['uid_local'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}

		$ids=array_unique($ids);
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
			if($result) {
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
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
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
				$where .= 'OR (tx_cal_calendar_user_group_mm.uid_foreign IN ('.$groupIds.') AND tx_cal_calendar_user_group_mm.tablenames="fe_groups"))';
				$table .= ' LEFT JOIN tx_cal_calendar_user_group_mm ON tx_cal_calendar_user_group_mm.uid_local=tx_cal_calendar.uid';
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
			if($result) {
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
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
			}
		}
		
		if($limitationList!='' && !empty($this->calendarIds)){
			$limitationArray = explode(',',$limitationList);
			$this->calendarIds = array_intersect($this->calendarIds,$limitationArray);
		}
//t3lib_div::debug($this->calendarIds);
		return $this->calendarIds;
	}
	
	/**
	 * Call this after you have called getCalendarSearchString or getFreeAndBusyCalendarSearchString
	 */
	function getCalendarOwner(){
		if($this->calendarOwner == null){
			$this->calendarOwner = Array();
			$table = 'tx_cal_calendar_user_group_mm';
			if($this->conf['option']=='freeandbusy'){
				$table = 'tx_cal_calendar_fnb_user_group_mm';
			}
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, '');
			if($result) {
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$ids[] = $row['uid_local'];
					$this->calendarOwner[$row['uid_local']][$row['tablenames']][] = $row['uid_foreign'];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
			}
		}
		return $this->calendarOwner;
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
	
	function createTranslation($uid, $overlay){
		$table = 'tx_cal_calendar';
		$select = $table.'.*';
		$where = $table.'.uid = '.$uid;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
		if($result) {
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				unset($row['uid']);
				$crdate = time();
				$row['tstamp'] = $crdate;
				$row['crdate'] = $crdate;
				$row['l18n_parent'] = $uid;
				$row['sys_language_uid'] = $overlay; 
				$this->_saveCalendar($row);
				return;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_calendar_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_calendar_service.php']);
}
?>