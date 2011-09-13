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

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_calendar_service extends tx_cal_base_service {
	
	var $calendarSearchString;
	var $calendarIds;
	/**
	 * Looks for a calendar with a given uid on a certain pid-list
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array		An array ($row)
	 */
	function find($uid, $pidList){
		if($pidList==""){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tx_cal_calendar", " hidden = 0 AND deleted = 0 AND uid=".$uid);
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tx_cal_calendar", " pid IN (".$pidList.") AND hidden = 0 AND deleted = 0 AND uid=".$uid);
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			return $row;
		}
	}
	
	
	/**
	 * Looks for all calendars on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	An array of array (array of $rows)
	 */
	function findAll($pidList){
		$return = array();
		if($pidList==""){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tx_cal_calendar", " hidden = 0 AND deleted = 0");
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tx_cal_calendar", " pid IN (".$pidList.") AND hidden = 0 AND deleted = 0");
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$return[] = $row;
		}
		return $return;
	}
	
	function updateCalendar($uid){

		$insertFields = array("tstamp" => time());
		//TODO: Check if all values are correct
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$table = "tx_cal_calendar";
		$where = "uid = ".$uid;	

		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeCalendar($uid){
		if($this->rightsObj->isAllowedToDeleteCalendar()){
			// "delete" the calendar object
			$updateFields = array("tstamp" => time(), "deleted" => 1);
			$table = "tx_cal_calendar";
			$where = "uid = ".$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
			
			// "delete" all the events related to the calendar
			$table = "tx_cal_event";
			$where = "calendar_id = ".$uid;
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=="true" && 
				($this->rightsObj->isAllowedToEditCalendarHidden() || $this->rightsObj->isAllowedToCreateCalendarHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
		
		if($this->rightsObj->isAllowedToEditCalendarTitle() || $this->rightsObj->isAllowedToCreateCalendarTitle()){
			$insertFields['title'] = $this->controller->piVars['title'];
		}
			
		if($this->rightsObj->isAllowedToEditCalendarFeUser() || $this->rightsObj->isAllowedToCreateCalendarFeUser()){
			$insertFields['fe_user_id'] = $this->controller->piVars['fe_user_id'];
		}
	}
	
	function saveCalendar($pid){

		$crdate = time();
		$insertFields = array("pid" => $pid, "tstamp" => $crdate, "crdate" => $crdate);
		//TODO: Check if all values are correct
		
		$hidden = 0;
		if($this->controller->piVars['hidden']=="true")
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
		if($this->controller->piVars['title']!=''){
			$insertFields['title'] = $this->controller->piVars['title'];
		}
		if($this->controller->piVars['fe_user_id']!=''){
			$insertFields['fe_user_id'] = $this->controller->piVars['fe_user_id'];
		}
		
		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$table = "tx_cal_calendar";
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
	
	function getCalendarSearchString($pidList, $includePublic, $linkIds){
		
		if($this->calendarSearchString){
			return $this->calendarSearchString;
		}
		$idArray = $this->getIdsFromTable($this->cObj->conf['calendar'],$pidList, $includePublic);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid_local", "tx_cal_calendar_user_group_mm", "");
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid_local'];
		}
		
		$idArray = $this->getIdsFromTable($this->cObj->conf['calendar'],$pidList, $includePublic);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid_local", "tx_cal_calendar_user_group_mm", "");
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid_local'];
		}
		$idString = $this->arrayToCommaseparatedString($ids);
		if($idString!=""){
			$calendarSearchString = " AND tx_cal_calendar.uid NOT IN (".$idString.")";
		}
		// Check the results
		if(empty($idArray)){
			// No calendar ids specified for this user -> show default
			
		}else if(!empty($linkIds)){
			// compair the allowed ids with the ids available and retrieve the intersects
			$calendarIds = array_intersect($idArray,$linkIds);
			if(empty($calendarIds)){
				// No intersects -> show default
			}else{
				// create a string for the query
				$calendarIds = $this->arrayToCommaseparatedString($calendarIds);
				$calendarSearchString = " AND tx_cal_calendar.uid IN (".$calendarIds.")";
			}
		}else{
			$calendarIds = $this->arrayToCommaseparatedString($idArray);
			$calendarSearchString = " AND tx_cal_calendar.uid IN (".$calendarIds.")";
		}
		$this->calendarSearchString = $calendarSearchString;
		return $calendarSearchString;
	}
	
	function getIdsFromTable($list, $pidList, $includePublic, $includeData=false, $onlyPublic=false){
		
		if($this->calendarIds){
			return $this->calendarIds;
		}
		
		//Logged in? Show public & private calendar
		
		// calendar ids specified? show these calendar only - if allowed - else show public calendar
		
		$limitationList="";
		if($list){ //$this->cObj->conf['calendar']
			$limitationList = $list;
		}
		
		// Lets see if the user is logged in
		if($this->rightsObj->isLoggedIn() && !$onlyPublic){
			$userId = $this->rightsObj->getUserId();
			$groupIds = $this->arrayToCommaseparatedString($this->rightsObj->getUserGroups());	
		}	
		$ids = array();
		if($userId===""){ //  && !$includePublic
			return $ids;
		}
		if($includeData){
			$select = "tx_cal_calendar.*";
		}else{
			$select = "tx_cal_calendar.uid";
		}
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid_local", "tx_cal_calendar_user_group_mm", "");
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$ids[] = $row['uid_local'];
		}
	
		if($includePublic){
			
			if(!empty($ids)){
				$where = "uid NOT IN (".$this->arrayToCommaseparatedString($ids).")";
			}else{
				$where = "";
			}
			if($includeData){
				$select = "*";
			}else{
				$select = "uid";
			}
			$table = "tx_cal_calendar";
//t3lib_div::debug("SELECT ".$select." FROM ".$table." WHERE ".$where);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($includeData){
					$this->calendarIds[] = $row;
				}else{
					$this->calendarIds[] = $row['uid'];
				}
			}
			
		}

		if(!$onlyPublic){
			if(!empty($ids)){
				$where = "uid NOT IN (".$this->arrayToCommaseparatedString($ids).")";
			}else{
				$where = "";
			}
			$table = "tx_cal_calendar";
			if($includeData){
				$select = "*";
			}else{
				$select = "uid";
			}
			if($userId){
				$where = "((tx_cal_calendar_user_group_mm.uid_foreign IN (".$userId.") AND tx_cal_calendar_user_group_mm.tablenames='fe_users' AND tx_cal_calendar_user_group_mm.uid_local=tx_cal_calendar.uid)";
				$where .= "OR (tx_cal_calendar_user_group_mm.uid_foreign IN (".$groupIds.") AND tx_cal_calendar_user_group_mm.tablenames='fe_groups' AND tx_cal_calendar_user_group_mm.uid_local=tx_cal_calendar.uid))";
				$table .= ", tx_cal_calendar_user_group_mm";
			}
			
			if($pidList!=""){
				$where .= strlen($where)? " AND pid IN (".$pidList.")" : " pid IN (".$pidList.")";
			}
			$where .= " AND hidden=0 AND deleted=0";
			if($limitationList!=""){
				$where .= " AND uid IN (".$limitationList.")";
			}
			$groupBy = "tx_cal_calendar.uid";
//t3lib_div::debug("SELECT ".$select." FROM ".$table." WHERE ".$where." GROUP BY ".$groupBy);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table ,$where, $groupBy);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($includeData){
					$this->calendarIds[] = $row;
				}else{
					$this->calendarIds[] = $row['uid'];
				}
			}
		}
		if($limitationList!="" && !empty($this->calendarIds)){
			$limitationArray = split(',',$limitationList);
			$this->calendarIds = array_intersect($this->calendarIds,$limitationArray);
		}
//debug($this->calendarIds);
		return $this->calendarIds;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_calendar_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_calendar_service.php']);
}
?>