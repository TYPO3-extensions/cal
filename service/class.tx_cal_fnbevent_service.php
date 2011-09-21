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

require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_event_service.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_fnbevent_service extends tx_cal_event_service {
	
	var $fnbCalendarSearchString;
	var $calendarIds;
	var $calendarOwner;

	function tx_cal_fnbevent_service(){
		$this->tx_cal_event_service();
	}
	
	function findAllWithin(&$start_date, &$end_date, $pidList, $eventType='0,1,2,3', $additionalWhere='') {

		// How to get the events
		// 1st get Calendar specified
		// 2nd get categories specified
		// 3rd get all related events
		// make an array out of the list, so we can handle it better

		$includeRecurring = true;
		if($this->conf['view']=='ics' || $this->conf['view']=='single_ics'){
			$includeRecurring = false;
		}

		$this->setStartAndEndPoint($start_date, $end_date);
		$dontShowOldEvents = (integer)$this->conf['view.'][$this->conf['view'].'.']['dontShowOldEvents'];
		if($dontShowOldEvents>0){
			$now = new tx_cal_date();
			if ($dontShowOldEvents==2){
				$now->setHour(0);
				$now->setMinute(0);
				$now->setSecond(0);
			}

			if($start_date->getTime() <= $now->getTime()){
				$start_date->copy($now);
			}
			if($end_date->getTime() <= $now->getTime()){
				$end_date->copy($now);
				$end_date->addSeconds(86400);
			}
			$this->starttime->copy($start_date);
			$this->endtime->copy($end_date);
		}
		$formattedStarttime = $this->starttime->format('%Y%m%d');
		$formattedEndtime = $this->endtime->format('%Y%m%d');
		$calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
		$categoryService = &$this->modelObj->getServiceObjByKey('cal_category_model', 'category', 'tx_cal_category');
		
		$calendarSearchString = $this->getFreeAndBusyCalendarSearchString($pidList, true, $this->conf['calendar']?$this->conf['calendar']:'');
		$categorySearchString = $categoryService->getCategorySearchString($pidList, true);
		
		$recurringClause = '';
		// only include the recurring clause if we don't use the new recurring model or a view not needing recurring events.
		if($this->extConf['useNewRecurringModel'] && $includeRecurring) {
			// get the uids of recurring events from index
			$select = 'event_uid';
			$table = 'tx_cal_index';
			$where = 'start_datetime >= '.$this->starttime->format('%Y%m%d%H%M%S').' AND start_datetime <= '.$this->endtime->format('%Y%m%d%H%M%S');
			$group = 'event_uid';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where,$group);
			$tmpUids = array();
			if($result) {
				while($tmp = $GLOBALS['TYPO3_DB']->sql_fetch_row($result)) {
					$tmpUids[] = $tmp[0];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
			}
			if(count($tmpUids)) {
				$recurringClause = ' OR (tx_cal_event.uid IN (' . implode(',',$tmpUids) . ')) ';
			}
		} else if ($includeRecurring) {
			$recurringClause = ' OR (tx_cal_event.start_date<='.$formattedEndtime.' AND (tx_cal_event.freq IN ("day","week","month","year") AND (tx_cal_event.until>='.$formattedStarttime.' OR tx_cal_event.until=0))) OR (tx_cal_event.rdate AND tx_cal_event.rdate_type IN ("date_time","date","period")) ';
		}
		
		// putting everything together
		// Franz: added simple check/include for rdate events at the end of this where clause.
		// But we need to find a way to only include rdate events within the searched timerange
		// - otherwise we'll flood the results after some time. I think we need a mm-table for that!
		$additionalWhere = $calendarSearchString.' AND tx_cal_event.pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_event').' AND ((tx_cal_event.start_date>='.$formattedStarttime.' AND tx_cal_event.start_date<='.$formattedEndtime.') OR (tx_cal_event.end_date<='.$formattedEndtime.' AND tx_cal_event.end_date>='.$formattedStarttime.') OR (tx_cal_event.end_date>='.$formattedEndtime.' AND tx_cal_event.start_date<='.$formattedStarttime.')' . $recurringClause . ')'.$additionalWhere;
		//$additionalWhere = $calendarSearchString.' AND tx_cal_event.pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_event').' AND ((tx_cal_event.start_date>='.$formattedEndtime.' OR tx_cal_event.end_date>='.$formattedStarttime.')' . $recurringClause . ')'.$additionalWhere;

		// creating the arrays the user is allowed to see
		$categories = array();

		$categoryService->getCategoryArray($pidList, $categories);

		// creating events
		return $this->getEventsFromTable($categories[0][0], $includeRecurring, $additionalWhere, $this->getServiceKey(), $categorySearchString, false, $eventType);
	}
	
	function getFreeAndBusyCalendarSearchString($pidList, $includePublic, $linkIds){
	
		$hash = md5($pidList.' '.$includePublic.' '.$linkIds);
		if($this->fnbCalendarSearchStringCache[$hash]){
			return $this->fnbCalendarSearchStringCache[$hash];
		}
		
		$calendarSearchString = '';
		$freeNBusyCalendar = array();
		$calendarOwner = array();
		$ids = array();
		$excludeIds = array();
		$idArray = $this->getIdsFromTable($linkIds,$pidList, $includePublic);

		$excludeIds = array_keys($this->getCalendarOwner());
		
		if($this->rightsObj->isLoggedIn()){
			$groups = $this->rightsObj->getUserGroups();
			$userId = $this->rightsObj->getUserId();
			$where = '(tablenames = "fe_users" AND uid_foreign = '.$userId.') OR (tablenames = "fe_groups" AND uid_foreign in ('.implode(',',$groups).'))';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar_fnb_user_group_mm', $where);
			if($result) {
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$ids[] = $row['uid_local'];
					$this->calendarOwner[$row['uid_local']][$row['tablenames']][] = $row['uid_foreign'];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
			}
		}
		
		$where = 'tx_cal_calendar.activate_fnb = 1';
		if(!empty($excludeIds)){
			$where .= ' AND uid not in ('.implode(',',$excludeIds).')';
		}
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', $where);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$ids[] = $row['uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}

		$calendarSearchString = ' AND tx_cal_calendar.activate_fnb = 1';		
//		$idString = implode(',',$ids);
		if(!empty($idArray)){
			// compare the allowed ids with the ids available and retrieve the intersects
			$calendarIds = array_intersect($idArray,$ids);
			if(empty($calendarIds)){
				// No intersects -> show none
				$calendarSearchString .= ' AND tx_cal_calendar.uid IN (0)';
			}else{
				// create a string for the query
				$calendarIds = implode(',',$calendarIds);
				$calendarSearchString .= ' AND tx_cal_calendar.uid IN ('.$calendarIds.')';
			}
		}
		
		$this->fnbCalendarSearchStringCache[$hash] = $calendarSearchString;

		return $calendarSearchString;
	}
	
	/**
	 * Call this after you have called getCalendarSearchString or getFreeAndBusyCalendarSearchString
	 */
	function getCalendarOwner(){
		if($this->calendarOwner == null){
			$this->calendarOwner = Array();
			$table = 'tx_cal_calendar_fnb_user_group_mm';
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
	
	function getIdsFromTable($list, $pidList, $includePublic, $includeData=false, $onlyPublic=false){
		
		$this->calendarIds = array();
		$collectedIds = array();
		
		//Logged in? Show public & private calendar
		
		// calendar ids specified? show these calendar only - if allowed - else show public calendar
	
		$limitationList='';
		if($list!=''){
			$limitationList = $list;
		}
		
		// Lets see if the user is logged in
		if($this->rightsObj->isLoggedIn() && !$onlyPublic){
			$userId = $this->rightsObj->getUserId();
			$groupIds = implode(',',$this->rightsObj->getUserGroups());	
		}	
	
		$ids = array();

		if($includeData){
			$select = 'tx_cal_calendar.*';
		}else{
			$select = 'tx_cal_calendar.uid';
		}
		
		$orderBy = tx_cal_functions::getOrderBy('tx_cal_calendar');
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_cal_calendar_fnb_user_group_mm.uid_local', 
			'tx_cal_calendar_fnb_user_group_mm LEFT JOIN tx_cal_calendar ON tx_cal_calendar.uid=tx_cal_calendar_fnb_user_group_mm.uid_local', 
			'1=1 '.$this->cObj->enableFields('tx_cal_calendar'), 
			'',  
			$orderBy
		);
		if($result) {
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$ids[] = $row['uid_local'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}

		$ids=array_unique($ids);
		if($includePublic){
			$where = '';
			if(!empty($ids)){
				$where .= 'uid NOT IN ('.implode(',',$ids).') AND ';
			}
			$where .= 'tx_cal_calendar.activate_fnb = 1 '.$this->cObj->enableFields('tx_cal_calendar');
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
		
		if($limitationList!='' && !empty($this->calendarIds)){
			$limitationArray = explode(',',$limitationList);
			$this->calendarIds = array_intersect($this->calendarIds,$limitationArray);
		}
		return $this->calendarIds;
	}
	
	function createEvent($row, $isException){
		$event = tx_cal_functions::makeInstance('tx_cal_phpicalendar_model',$row, $isException, $this->getServiceKey());
		$event->row['isFreeAndBusyEvent'] = 1;
		return $event;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_fnbevent_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_fnbevent_service.php']);
}
?>