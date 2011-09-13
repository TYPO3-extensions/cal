<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
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
require_once (t3lib_extMgm :: extPath('cal') . 'model/class.tx_cal_category_model.php');

/**
 * Base model for the category.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_category_service extends tx_cal_base_service {
	
	var $categoryArrayByEventUid = array();
	var $categoryArrayByCalendarUid = array();
	var $categoryArrayByUid = array();
	
	function tx_cal_category_service(){
		$this->tx_cal_base_service();
	}
		
	/**
	 * Looks for a category with a given uid on a certain pid-list
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array		An array ($row)
	 */
	function find($uid, $pidList){
		$categoryIds = array();
		$this->getCategoryArray($pidList, $categoryIds, true);
		return $this->categoryArrayByUid[$uid];
	}
	
	
	/**
	 * Looks for all categorys on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	An array of array (array of $rows)
	 */
	function findAll($pidList, &$categoryArrayToBeFilled){
		$this->getCategoryArray($pidList, $categoryArrayToBeFilled, true);
	}
	
	function updateCategory($uid){

		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'category',false);
		$this->retrievePostData($insertFields);
		$uid = $this->checkUidForLanguageOverlay($uid,'tx_cal_category');
		// Creating DB records
		$table = 'tx_cal_category';
		$where = 'uid = '.$uid;	

		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
		
		$this->unsetPiVars();
	}
	
	function removeCategory($uid){
		if($this->rightsObj->isAllowedToDeleteCategory()){
			// 'delete' the category object
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tx_cal_category';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
			
			// 'delete' all the events related to the category
	//		$table = 'tx_cal_event';
	//		$where = 'category_id = '.$uid;
	//		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);

			$this->unsetPiVars();
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=='1' && 
				($this->rightsObj->isAllowedToEditCategoryHidden() || $this->rightsObj->isAllowedToCreateCategoryHidden())) {
			$hidden = 1;
		}
		$insertFields['hidden'] = $hidden;
		
		if($this->rightsObj->isAllowedToEditCategoryTitle() || $this->rightsObj->isAllowedToCreateCategoryTitle()){
			$insertFields['title'] = strip_tags($this->controller->piVars['title']);
		}
		
		if($this->rightsObj->isAllowedToEditCategoryCalendar() || $this->rightsObj->isAllowedToCreateCategoryCalendar()){
			$insertFields['calendar_id'] = intval($this->controller->piVars['calendar_id']);
		}
		
		if($this->rightsObj->isAllowedToEditCategoryParent() || $this->rightsObj->isAllowedToCreateCategoryParent()){
			$insertFields['parent_category'] = intval($this->controller->piVars['parent_category']);
		}
		
		if($this->rightsObj->isAllowedToEditCategoryHeaderstyle() || $this->rightsObj->isAllowedToCreateCategoryHeaderstyle()){
			$insertFields['headerstyle'] = strip_tags($this->controller->piVars['headerstyle']);
		}
		
		if($this->rightsObj->isAllowedToEditCategoryBodystyle() || $this->rightsObj->isAllowedToCreateCategoryBodystyle()){
			$insertFields['bodystyle'] = strip_tags($this->controller->piVars['bodystyle']);
		}
		
		if($this->rightsObj->isAllowedToEditCategorySharedUser() || $this->rightsObj->isAllowedToCreateCategorySharedUser()){
			$insertFields['shared_user_allowed'] = intval($this->controller->piVars['shared_user_allowed']);
		}
	}
	
	function saveCategory($pid){
		$crdate = time();
		$insertFields = array('pid' => $this->conf['rights.']['create.']['calendar.']['saveCategoryToPid']?$this->conf['rights.']['create.']['calendar.']['saveCategoryToPid']:$pid, 'tstamp' => $crdate, 'crdate' => $crdate);
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'category');
		$this->retrievePostData($insertFields);

		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$this->_saveCategory($insertFields);
		$this->unsetPiVars();
	}
	
	function _saveCategory(&$insertFields){
		$table = 'tx_cal_category';
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
	
	function getCategorySearchString($pidList, $includePublic){
		if($this->conf['category']!=''){
			$categorySearchString .= ' AND tx_cal_event_category_mm.uid_foreign IN ('.$this->conf['category'].')';
		}
		return $categorySearchString;
	}
	
	/**
	 * Search for categories
	 */
	function getCategoryArray($pidList, &$categoryArrayToBeFilled, $showPublicCategories=true){
		if($this->rightsObj->isLoggedIn() && $showPublicCategories){
			$feUserId = $this->rightsObj->getUserId();
		}else if($this->rightsObj->isLoggedIn()){
			$feUserId = $this->rightsObj->getUserId();
		}
		
		$this->categoryArrayByUid = array();
		$this->categoryArrayByEventUid = array();
		$this->categoryArrayByCalendarUid = array();

		$categoryIds = array();
		$dbIds = array();
		$fileIds = array();
		$extUrlIds = array();
		$additionalWhere = ' AND tx_cal_category.pid IN ('.$pidList.')';
        //if($this->conf['category']!='' && $this->conf['category']!=0) {
		    $allowedCategories = t3lib_div::trimExplode(',',$this->conf['view.']['allowedCategory'],1);
		    if (!empty($allowedCategories)){
			    $additionalWhere .= ' AND tx_cal_category.uid IN ('.$this->conf['view.']['allowedCategory'].')';
		    }
        //}
        $calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
		$calendarSearchString = $calendarService->getCalendarSearchString($pidList, $showPublicCategories, $this->conf['calendar']?$this->conf['calendar']:'');				
		//Select all categories for the given pids
		$select = 'tx_cal_category.*,tx_cal_calendar.title AS calendar_title,tx_cal_calendar.uid AS calendar_uid';
		$table = 'tx_cal_category,tx_cal_calendar';
		$groupby = 'tx_cal_category.uid';
		$orderby = 'calendar_id,tx_cal_category.title ASC';
		$where = '(tx_cal_category.calendar_id=tx_cal_calendar.uid)';
		$where .= $calendarSearchString;
		$where .= $this->cObj->enableFields('tx_cal_calendar').' AND tx_cal_calendar.pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_category');
		$where .= $additionalWhere;

		$where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_category');
				
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where.' GROUP BY '.$groupby.' ORDER BY '.$orderby);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby,$orderby);
		$foundUids = array();
		$calendarUids = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if ($GLOBALS['TSFE']->sys_language_content) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cal_category', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
			}
			if ($this->versioningEnabled) {
				// get workspaces Overlay
				$GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category',$row);
			}
			$category = $this->createCategory($row);
			$foundUids[] = $row['uid'];
			$calendarUids[] = $row['calendar_uid'];

			$this->categoryArrayByUid[$row['uid']] = $category;
			$this->categoryArrayByCalendarUid[$row['calendar_uid'].'###'.$row['calendar_title'].'###tx_cal_calendar'][] = $category->getUid();
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		$calendarsWithoutCategory = array_diff(t3lib_div::intExplode(',',$this->conf['view.']['calendar']),array_unique($calendarUids));
		if(!empty($calendarsWithoutCategory)){
			$select = 'tx_cal_calendar.*';
			$table = 'tx_cal_calendar';
			$groupby = 'tx_cal_calendar.uid';
			$orderby = 'tx_cal_calendar.title ASC';
			$where = 'tx_cal_calendar.uid IN ('.implode(',',$calendarsWithoutCategory).')'.$calendarSearchString.$this->cObj->enableFields('tx_cal_calendar');
			$where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_calendar');

			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby,$orderby);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if ($GLOBALS['TSFE']->sys_language_content) {
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cal_calendar', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
				}
				if ($this->versioningEnabled) {
					// get workspaces Overlay
					$GLOBALS['TSFE']->sys_page->versionOL('tx_cal_calendar',$row);
				}
				$this->categoryArrayByCalendarUid[$row['uid'].'###'.$row['title'].'###tx_cal_calendar'] = array();
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			
		}
		$additionalWhere = '';
		//Select all global categories
		$select = 'tx_cal_category.*';
		$table = 'tx_cal_category';
		$groupby = 'tx_cal_category.uid';
		$orderby = 'tx_cal_category.title ASC';
		if(!empty($foundUids)){
			$additionalWhere .= ' AND tx_cal_category.uid NOT IN ('.implode(',',$foundUids).')';
		}
		$where = 'tx_cal_category.calendar_id = 0'.$this->cObj->enableFields('tx_cal_category').$additionalWhere;
		$where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_category');
				
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby,$orderby);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if ($GLOBALS['TSFE']->sys_language_content) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cal_category', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
			}
			if ($this->versioningEnabled) {
				// get workspaces Overlay
				$GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category',$row);
			}

			$category = $this->createCategory($row);
			$this->categoryArrayByUid[$row['uid']] = $category;
			$this->categoryArrayByCalendarUid['0###'.$this->controller->pi_getLL('l_global_category')][] = $category->getUid();
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		// Map styles
		foreach($this->categoryArrayByUid as $category){
			$this->checkStyles($category);
		}

		// Map categories to events
		$select = 'tx_cal_event_category_mm.*';
		$table = 'tx_cal_event_category_mm';
		$groupby = '';
		$orderby = 'uid_local ASC, sorting ASC';
		$where = '';
//t3lib_div::debug('SELECT '.$select.' FROM '.$table.' WHERE '.$where.' ORDER BY '.$orderby);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby,$orderby);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($this->categoryArrayByUid[$row['uid_foreign']]){
				$this->categoryArrayByEventUid[$row['uid_local']][] = $this->categoryArrayByUid[$row['uid_foreign']];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
	
		if($this->conf['view.']['freeAndBusy.']['enable']){
			$select = 'tx_cal_category.*, tx_cal_calendar.title AS calendar_title';
			$where = 'tx_cal_event.calendar_id = tx_cal_calendar.uid' .
				' AND tx_cal_calendar.uid = tx_cal_category.calendar_id' .
				' AND tx_cal_category.shared_user_allowed = 1' .
				' AND tx_cal_event.uid = tx_cal_event_shared_user_mm.uid_local';
			$where .= $calendarService->getCalendarSearchString($pidList, $showPublicCategories, $this->conf['view.']['calendar']?$this->conf['view.']['calendar']:'');
//				' AND tx_cal_event_shared_user_mm.uid_foreign = '.$this->rightsObj->getUserId();
			$where .= $this->cObj->enableFields('tx_cal_calendar').$this->cObj->enableFields('tx_cal_category').$this->cObj->enableFields('tx_cal_event');
			$where .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_category');
			$table = 'tx_cal_category, tx_cal_event, tx_cal_event_shared_user_mm, tx_cal_calendar';
//			$groupby = 'tx_cal_category.uid';

			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if ($GLOBALS['TSFE']->sys_language_content) {
					$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cal_category', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
				}
				if ($this->versioningEnabled) {
					// get workspaces Overlay
					$GLOBALS['TSFE']->sys_page->versionOL('tx_cal_category',$row);
				}

				$category = $this->createCategory($row);
				$this->categoryArrayByEventUid[$row['uid_local']][] = $category;
				$this->categoryArrayByUid[$row['uid']] = $category;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}

		$categoryArrayToBeFilled[] = array($this->categoryArrayByUid,$this->categoryArrayByEventUid,$this->categoryArrayByCalendarUid);
	}
	
	function getCategoriesForSharedUser(){
		$categories = array();
		$select = '*';
		$table = 'tx_cal_category, tx_cal_event, tx_cal_event_shared_user_mm, tx_cal_calendar';
		$where = 'tx_cal_event.calendar_id = tx_cal_calendar.uid' .
				$this->cObj->enableFields('tx_cal_calendar').$this->cObj->enableFields('tx_cal_category').$this->cObj->enableFields('tx_cal_event').
				' AND tx_cal_calendar.uid = tx_cal_category.calendar_id' .
				' AND tx_cal_category.shared_user_allowed = 1' .
				' AND tx_cal_event.uid = tx_cal_event_shared_user_mm.uid_local' .
				' AND tx_cal_event_shared_user_mm.uid_foreign = '.$this->rightsObj->getUserId();
		$groupby = '';

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where,$groupby);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$categories[$row['uid']] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		return $categories;
	}
	
	function createCategory($row){
		$tx_cal_category_model = &t3lib_div::makeInstanceClassName('tx_cal_category_model');
		$category = &new $tx_cal_category_model($row, $this->getServiceKey());	
		return $category;	
	}
	
	function getCategoriesForEvent($eventUid){
		if(count($this->categoryArrayByEventUid)==0){
			$cats = array();
			$this->findAll($this->conf['pidList'], $cats);
		}
		return $this->categoryArrayByEventUid[$eventUid];
	}

	function checkStyles(&$category){

		$headerStyle = $category->getHeaderStyle();
		if($headerStyle==''){
			$parentUid = $category->getParentUid();
			if($parentUid==0){
				$category->setHeaderStyle($this->conf['view.']['category.']['category.']['defaultHeaderStyle']);
				$category->setBodyStyle($this->conf['view.']['category.']['category.']['defaultBodyStyle']);
			}else{
				if($this->categoryArrayByUid[$parentUid]){
					$this->checkStyles($this->categoryArrayByUid[$parentUid]);
					$category->setHeaderStyle($this->categoryArrayByUid[$parentUid]->getHeaderStyle());
					$category->setBodyStyle($this->categoryArrayByUid[$parentUid]->getBodyStyle());
				}else{
					$category->setHeaderStyle($this->conf['view.']['category.']['category.']['defaultHeaderStyle']);
					$category->setBodyStyle($this->conf['view.']['category.']['category.']['defaultBodyStyle']);
				}
			}
		}
		$this->categoryArrayByUid[$category->getUid()] = $category;
	}
	
	function unsetPiVars(){
		unset($this->controller->piVars['hidden']);
		unset($this->controller->piVars['uid']);
		unset($this->controller->piVars['calendar']);
		unset($this->controller->piVars['type']);
		unset($this->controller->piVars['calendar_id']);
		unset($this->controller->piVars['category']);
		unset($this->controller->piVars['shared_user_allowed']);
		unset($this->controller->piVars['headerstyle']);
		unset($this->controller->piVars['bodystyle']);
		unset($this->controller->piVars['parent_category']);
		unset($this->controller->piVars['title']);
	}
	
	function createTranslation($uid, $overlay){
		$table = 'tx_cal_category';
		$select = $table.'.*';
		$where = $table.'.uid = '.$uid;
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			unset($row['uid']);
			$crdate = time();
			$row['tstamp'] = $crdate;
			$row['crdate'] = $crdate;
			$row['l18n_parent'] = $uid;
			$row['sys_language_uid'] = $overlay; 
			$this->_saveCategory($row);
			return;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_category_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_category_service.php']);
}
?>
