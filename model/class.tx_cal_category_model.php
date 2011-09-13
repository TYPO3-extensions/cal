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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');

/** 
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_category_model extends tx_cal_base_model {
	
	var $row = array();
	var $uid = 0;
	var $hidden = false;
	var $parentUid = 0;
	var $calendarUid = 0;
	var $title = '';
	var $headerStyle = '';
	var $bodyStyle = '';
	var $sharedUserAllowed = false;
	var $categoryService;
	var $type = 'tx_cal_category';
	var $singlePid = 0;
	
	
	/**
	 *  Constructor.
	 */
	function tx_cal_category_model($controller, &$row ,$serviceKey){
		$this->tx_cal_base_model($controller, $serviceKey);
		$this->init($row);
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
	}
	
	function setUid($uid){
		$this->uid = $uid;
	}
	
	function getUid(){
		return $this->uid;
	}
	
	function setParentUid($uid){
		$this->parentUid = $uid;
	}
	
	function getParentUid(){
		return $this->parentUid;
	}
	
	function getHidden() {
		return $this->hidden;
	}
	
	function isHidden() {
		return $this->hidden;
	}
	
	function setHidden($hidden) {
		$this->hidden = $hidden;
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
	
	function getCategoryMarker(& $template, & $rems, & $sims, & $wrapped){
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'HEADERSTYLE':
					$sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
					break;
				case 'BODYSTYLE':
					$sims['###BODYSTYLE###'] = $this->getBodyStyle();
					break;
				case 'PARENT_UID':
					$sims['###PARENT_UID###'] = $this->getParentUid();
					break;
				case 'TITLE':
					$sims['###TITLE###'] = $this->getTitle();
					break;
				case 'UID':
					$sims['###UID###'] = $this->getUid();
					break;
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_category_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.']['category.']['stdWrap_'.strtolower($marker)]);
					}
					break;
			}
		}
	}
	
	function getType(){
		return $this->type;
	}
	
	function setType($type){
		$this->type = $type;
	}
	
	function getSinglePid(){
		return $this->singlePid;
	}
	
	function setSinglePid($singlePid){
		$this->singlePid = $singlePid;
	}
	
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		if ($this->rightsObj->isCalAdmin()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $this->rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $this->rightsObj->getUserGroups();
		}
		
		$isCategoryOwner = false;
		if($this->getCalendarUid()){
			$calendar = $this->modelObj->findCalendar($this->getCalendarUid(), $this->conf['pidList']);
			$isCategoryOwner = $calendar->isCalendarOwner($this->rightsObj->getUserId(), $this->rightsObj->getUserGroups());
		}
		
		
		$isAllowedToEditCategory = $this->rightsObj->isAllowedToEditCategory();
		$isAllowedToEditOwnCategoryOnly = $this->rightsObj->isAllowedToEditOnlyOwnCategory();
		$isAllowedToEditGeneralCategory = $this->rightsObj->isAllowedToEditGeneralCategory();

		if ($isAllowedToEditOwnCategoryOnly) {
			return $isCategoryOwner;
		}
		return $isAllowedToEditCategory && ($isCategoryOwner || ($this->getCalendarUid()==0 && $isAllowedToEditGeneralCategory));
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		if ($this->rightsObj->isCalAdmin()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $this->rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $this->rightsObj->getUserGroups();
		}
		$isCategoryOwner = false;
		if($this->getCalendarUid()){
			$calendar = $this->modelObj->findCalendar($this->getCalendarUid(), $this->conf['pidList']);
			$isCategoryOwner = $calendar->isCalendarOwner($this->rightsObj->getUserId(), $this->rightsObj->getUserGroups());
		}
		
		$isAllowedToDeleteCategory = $this->rightsObj->isAllowedToDeleteCategory();
		$isAllowedToDeleteOwnCategoryOnly = $this->rightsObj->isAllowedToDeleteOnlyOwnCategory();
		$isAllowedToDeleteGeneralCategory = $this->rightsObj->isAllowedToDeleteGeneralCategory();

		if ($isAllowedToDeleteOwnCategoryOnly) {
			return $isCalendarOwner;
		}
		return $isAllowedToDeleteCategory && ($isCategoryOwner || ($this->getCalendarUid()==0 && $isAllowedToDeleteGeneralCategory));
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_category_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_category_model.php']);
}
?>