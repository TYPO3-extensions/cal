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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');

/** 
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_category_model extends tx_cal_base_model {
	
	var $row = array();
	var $parentUid = 0;
	var $calendarUid = 0;
	var $title = '';
	var $headerStyle = '';
	var $bodyStyle = '';
	var $sharedUserAllowed = false;
	var $categoryService;
	var $singlePid = 0;
	var $notificationEmails = array();
	
	
	/**
	 *  Constructor.
	 */
	function tx_cal_category_model(&$row ,$serviceKey){
		$this->setType('tx_cal_category');
		$this->setObjectType('category');
		$this->tx_cal_base_model($serviceKey);
		if(is_array($row) && !empty($row)){
			$this->init($row);
		}
	}
	
	function init(&$row){
		$this->row = $row;
		$this->setUid($row['uid']);
		$this->setParentUid($row['parent_category']);
		if($row['title']) {
			$this->setTitle($row['title']);
		} else {
			$this->setTitle('[No Title]');
		}
		$this->setHidden($row['hidden']);
		$this->setHeaderStyle($row['headerstyle']);
		$this->setBodyStyle($row['bodystyle']);
		$this->setSharedUserAllowed($row['shared_user_allowed']);
		$this->setCalendarUid($row['calendar_id']);
		$this->setSinglePid($row['single_pid']);
		$this->setNotificationEmails(t3lib_div::trimExplode(',',$row['notification_emails'],1));
	}
	
	function setParentUid($uid){
		$this->parentUid = $uid;
	}
	
	function getParentUid(){
		return $this->parentUid;
	}
	
	function setTitle($title){
		$this->title = $title;
	}
	
	function getTitle(){
		return $this->title;
	}
	
	function setHeaderStyle($headerStyle){
		$this->headerStyle = $headerStyle;
	}
	
	function getHeaderStyle(){
		return $this->headerStyle;
	}
	
	function setBodyStyle($bodyStyle){
		$this->bodyStyle = $bodyStyle;
	}
	
	function getBodyStyle(){
		return $this->bodyStyle;
	}
	
	function setSharedUserAllowed($boolean){
		$this->sharedUserAllowed = $boolean;
	}
	
	function isSharedUserAllowed(){
		return $this->sharedUserAllowed;
	}
	
	function setCalendarUid($uid) {
		$this->calendarUid = $uid;
	}
	
	function getCalendarUid() {
		return $this->calendarUid;
	}
	
	function getHeaderstyleMarker(& $template, & $sims, & $rems, $view){
		$sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
	}
	
	function getBodystyleMarker(& $template, & $sims, & $rems, $view){
		$sims['###BODYSTYLE###'] = $this->getBodyStyle();
	}
	
	function getSinglePid(){
		return $this->singlePid;
	}
	
	function setSinglePid($singlePid){
		$this->singlePid = $singlePid;
	}
	
	function getNotificationEmails(){
		return $this->notificationEmails;
	}
	
	function setNotificationEmails($emailArray){
		if(is_array($emailArray)){
			$this->notificationEmails = $emailArray;
		}
	}
	
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightsController');
		if(!$rightsObj->isViewEnabled('edit_category')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups();
		}
		
		$isCategoryOwner = false;
		$isAllowedToEditPublicCategory = false;
		if($this->getCalendarUid()){
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$calendar = $modelObj->findCalendar($this->getCalendarUid(), 'tx_cal_calendar', $this->conf['pidList']);
			$isCategoryOwner = $calendar->isCalendarOwner($feUserUid, $feGroupsArray);
			if($calendar->isPublic()){
				$isAllowedToEditPublicCategory = $rightsObj->isAllowedToEditPublicCategory();
			}
		}
		
		
		$isAllowedToEditCategory = $rightsObj->isAllowedToEditCategory();
		$isAllowedToEditOwnCategoryOnly = $rightsObj->isAllowedToEditOnlyOwnCategory();
		$isAllowedToEditGeneralCategory = $rightsObj->isAllowedToEditGeneralCategory();

		if ($isAllowedToEditOwnCategoryOnly) {
			return $isCategoryOwner;
		}
		return $isAllowedToEditCategory && ($isCategoryOwner || ($this->getCalendarUid()==0 && $isAllowedToEditGeneralCategory) || $isAllowedToEditPublicCategory);
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightsController');
		if(!$rightsObj->isViewEnabled('delete_category')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups();
		}
		$isCategoryOwner = false;
		$isAllowedToDeletePublicCategory = false;
		if($this->getCalendarUid()){
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$calendar = $modelObj->findCalendar($this->getCalendarUid(), 'tx_cal_calendar', $this->conf['pidList']);
			$isCategoryOwner = $calendar->isCalendarOwner($feUserUid, $feGroupsArray);
			if($calendar->isPublic()){
				$isAllowedToDeletePublicCategory = $rightsObj->isAllowedToDeletePublicCategory();
			}
		}
		
		$isAllowedToDeleteCategory = $rightsObj->isAllowedToDeleteCategory();
		$isAllowedToDeleteOwnCategoryOnly = $rightsObj->isAllowedToDeleteOnlyOwnCategory();
		$isAllowedToDeleteGeneralCategory = $rightsObj->isAllowedToDeleteGeneralCategory();

		if ($isAllowedToDeleteOwnCategoryOnly) {
			return $isCalendarOwner;
		}
		return $isAllowedToDeleteCategory && ($isCategoryOwner || ($this->getCalendarUid()==0 && $isAllowedToDeleteGeneralCategory) || $isAllowedToDeletePublicCategory);
	}
	
	function getCalendarObject() {
		if(!$this->calendarObject) {
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$this->calendarObject = $modelObj->findCalendar($this->getCalendarUid());
		}
		
		return $this->calendarObject;
	}
	
	function getEditLink(& $template, & $sims, & $rems, $view){
		$editlink = '';
		if ($this->isUserAllowedToEdit()) {
			$this->initLocalCObject($this->getValuesAsArray());

			$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_edit_category'));
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array (
				'view' => 'edit_category',
				'type' => $this->getType(), 'uid' => $this->getUid()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['editCategoryViewPid']);
			$editlink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['category.']['editLink'],$this->conf['view.'][$view.'.']['category.']['editLink.']);
		}
		if ($this->isUserAllowedToDelete()) {
			$this->initLocalCObject($this->getValuesAsArray());
			
			$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_delete_category'));
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array (
				'view' => 'delete_category',
				'type' => $this->getType(), 'uid' => $this->getUid()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['category.']['deleteCategoryViewPid']);
			$editlink .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['category.']['deleteLink'],$this->conf['view.'][$view.'.']['category.']['deleteLink.']);
		}
		return $editlink;
	}
	
	function updateWithPIVars(&$piVars) {
		#$cObj = &tx_cal_registry::Registry('basic','cobj');
		$modelObj = &tx_cal_registry::Registry('basic','modelController');
		#$controller = &tx_cal_registry::Registry('basic','controller');
		$cObj = &$this->controller->cObj;
				
		foreach($piVars as $key => $value) {
			switch($key) {
				case 'uid':
					$this->setUid(intval($piVars['uid']));
					unset($piVars['uid']);
					break;
				case 'hidden':
					$this->setHidden(intval($piVars['hidden']));
					unset($piVars['hidden']);
					break;
				case 'title':
					$this->setTitle(strip_tags($piVars['title']));
					unset($piVars['title']);
					break;
				case 'calendar_id':
					$this->setCalendarUid(strip_tags($piVars['calendar_id'], array()));
					unset($piVars['calendar_id']);
					break;
				case 'headerstyle':
					$this->setHeaderStyle(strip_tags($piVars['headerstyle']));
					unset($piVars['headerstyle']);
					break;
				case 'bodystyle':
					$this->setBodystyle(strip_tags($piVars['bodystyle']));
					unset($piVars['bodystyle']);
					break;
				case 'parent_category':
					$this->setParentUid(intval($piVars['parent_category']));
					unset($piVars['parentCategory']);
					break;
				case 'shared_user_allowed':
					$this->setSharedUserAllowed(intval($piVars['shared_user_allowed']));
					unset($piVars['shared_user_allowed']);
					break;
			}
		}
	}
	
	function __toString(){
		return 'Category '.(is_object($this)?'object':'something').': '.implode(',',$this->row);
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_category_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_category_model.php']);
}
?>