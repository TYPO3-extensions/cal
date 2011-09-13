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
 * * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_calendar_model extends tx_cal_base_model {
	
	var $row = array();
	var $uid = 0;
	var $title = '';
	var $owner = array('fe_users'=>array(),'fe_groups'=>array());
	var $activateFreeAndBusy = 0;
	var $freeAndBusyUser = array('fe_users'=>array(),'fe_groups'=>array());
	var $calendarType = 0;
	var $extUrl = '';
	var $icsFile = '';
	var $refresh = 30;
	var $md5 = '';
	var $isPublic = true;
	var $calendarService;
	var $type = 'tx_cal_calendar';
	var $hidden = 0;
	
	
	/**
	 *  Constructor.
	 */
	function tx_cal_calendar_model($controller, &$row ,$serviceKey){
		$this->tx_cal_base_model($controller, $serviceKey);
		$this->init($row);
	}
	
	function init(&$row){
		$this->row = $row;
		$this->setUid($row['uid']);
		$this->setTitle($row['title']);
		$this->setActivateFreeAndBusy($row['activate_fnb']);
		$this->setCalendarType($row['type']);
		$this->setExtUrl($row['ext_url']);
		$this->setIcsFile($row['ics_file']);
		$this->setRefresh($row['refresh']);
		$this->setMD5($row['md5']);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_calendar_fnb_user_group_mm.*', 'tx_cal_calendar_fnb_user_group_mm,fe_users,fe_groups', 'uid_local='.$this->getUid().' AND ((uid_foreign = fe_users.uid AND tablenames="fe_users") OR (uid_foreign = fe_groups.uid AND tablenames="fe_groups"))'.$this->cObj->enableFields('fe_users').$this->cObj->enableFields('fe_groups'));
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->addFreeAndBusyUser($row['tablenames'],$row['uid_foreign']);
		}
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_calendar_user_group_mm.*', 'tx_cal_calendar_user_group_mm,fe_users,fe_groups', 'uid_local='.$this->getUid().' AND ((uid_foreign = fe_users.uid AND tablenames="fe_users") OR (uid_foreign = fe_groups.uid AND tablenames="fe_groups"))'.$this->cObj->enableFields('fe_users').$this->cObj->enableFields('fe_groups'));
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->addOwner($row['tablenames'],$row['uid_foreign']);
		}
		
	}
	
	function setUid($uid){
		$this->uid = $uid;
	}
	
	function getUid(){
		return $this->uid;
	}
	
	function setTitle($title){
		$this->title = $title;
	}
	
	function getTitle(){
		return $this->title;
	}
	
	function isActivateFreeAndBusy(){
		return $this->activateFreeAndBusy;
	}
	
	function getActivateFreeAndBusy(){
		return $this->activateFreeAndBusy;
	}
	
	function setActivateFreeAndBusy($activateFreeAndBusy){
		$this->activateFreeAndBusy = $activateFreeAndBusy;
	}
	
	function getCalendarType(){
		return $this->calendarType;
	}
	
	function setCalendarType($calendarType){
		$this->calendarType = $calendarType;
	}
		
	function getExtUrl(){
		return $this->extUrl;
	}
	
	function setExtUrl($extUrl){
		$this->extUrl = $extUrl;
	}

	function getIcsFile(){
		return $this->icsFile;
	}
	
	function setIcsFile($icsFile){
		$this->icsFile = $icsFile;
	}
	
	function getRefresh(){
		return $this->refresh;
	}
	
	function setRefresh($refresh){
		$this->refresh = $refresh;
	}
	
	function getMD5(){
		return $this->md5;
	}
	
	function setMD5($md5){
		$this->md5 = $md5;
	}
	
	function getType(){
		return $this->type;
	}
	
	function setType($type){
		$this->type = $type;
	}
	
	function getHidden(){
		return $this->hidden;
	}
	
	function isHidden(){
		return $this->hidden;
	}
	
	function setHidden($hidden){
		$this->hidden = $hidden;
	}
	
	function getFreeAndBusyUser($table,$index=0){
		if($index>0 && count($this->freeAndBusyUser[$table])>$index){
			return $this->freeAndBusyUser[$table][$index];
		}
		return $this->freeAndBusyUser[$table];
	}
	
	function setFreeAndBusyUser($table, $freeAndBusyUser){
		$this->freeAndBusyUser[$table] = $freeAndBusyUser;
	}
	
	function addFreeAndBusyUser($table, $freeAndBusyUser){
		$this->freeAndBusyUser[$table][] = $freeAndBusyUser;
	}
	
	function getOwner($table,$index=0){
		if($index>0 && count($this->owner[$table])>$index){
			return $this->owner[$table][$index];
		}
		return $this->owner[$table];
	}
	
	function setOwner($table, $owner){
		$this->owner[$table] = $owner;
		$isPublic = false;
	}
	
	function addOwner($table, $owner){
		$this->owner[$table][] = $owner;
		$isPublic = false;
	}
	
	
	function getCalendarMarker(& $template, & $rems, & $sims, & $wrapped){
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'TITLE':
					$sims['###TITLE###'] = $this->getTitle();
					break;
				case 'UID':
					$sims['###UID###'] = $this->getUid();
					break;
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_calendar_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.']['calendar.']['stdWrap_'.strtolower($marker)]);
					}
					break;
			}
		}
	}
	
	function getExtUrlMarker(& $template, & $rems, & $sims){
		$sims['###EXTURL###'] = $this->cObj->stdWrap($this->getExtUrl(), $this->conf['view.'][$this->conf['view'].'.']['exturl_stdWrap.']);
	}
	
	function getIcsFileMarker(& $template, & $rems, & $sims){
		$sims['###ICSFILE###'] = $this->cObj->stdWrap($this->getIcsFile(), $this->conf['view.'][$this->conf['view'].'.']['icsfile_stdWrap.']);
	}
	
	function getRefreshMarker(& $template, & $rems, & $sims){
		$sims['###REFRESH###'] = $this->cObj->stdWrap($this->getRefresh(), $this->conf['view.'][$this->conf['view'].'.']['refresh_stdWrap.']);
	}
	
	function getTitleMarker(& $template, & $rems, & $sims){
		$sims['###TITLE###'] = $this->cObj->stdWrap($this->getTitle(), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);
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
		$isEventOwner = $this->isCalendarOwner($this->rightsObj->getUserId(), $this->rightsObj->getUserGroups());

		$isAllowedToEditCalendars = $this->rightsObj->isAllowedToEditCalendar();
		$isAllowedToEditOwnCalendarsOnly = $this->rightsObj->isAllowedToEditOnlyOwnCalendar();
		$isAllowedToEditPublicCalendars = $this->rightsObj->isAllowedToEditPublicCalendar();

		if ($isAllowedToEditOwnCalendarsOnly) {
			return $isCalendarOwner;
		}
		return $isAllowedToEditCalendars && ($isCalendarOwner || ($this->isPublic && $isAllowedToEditPublicCalendars));
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
		$isCalendarOwner = $this->isCalendarOwner($this->rightsObj->getUserId(), $this->rightsObj->getUserGroups());

		$isAllowedToDeleteCalendars = $this->rightsObj->isAllowedToDeleteCalendar();
		$isAllowedToDeleteOwnCalendarsOnly = $this->rightsObj->isAllowedToDeleteOnlyOwnCalendar();
		$isAllowedToDeletePublicCalendars = $this->rightsObj->isAllowedToDeletePublicCalendar();

		if ($isAllowedToDeleteOwnCalendarsOnly) {
			return $isCalendarOwner;
		}
		return $isAllowedToDeleteCalendars && ($isCalendarOwner || ($this->isPublic && $isAllowedToDeletePublicCalendars));
	}
	
	function isCalendarOwner($userId, $groupIdArray){
		if(is_array($this->owner['fe_users']) && in_array($userId, $this->owner['fe_users'])){
			return true;
		}
		foreach($groupIdArray as $id){
			if(is_array($this->owner['fe_groups']) && in_array($id, $this->owner['fe_groups'])){
				return true;
			}
		}
		return false;
	}
	
	function getEditLink(){
		$editlink = '';
		if ($this->isUserAllowedToEdit()) {
			$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_edit_calendar') . '" alt="' . $this->controller->pi_getLL('l_edit_calendar') . '"';
			$editlink = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['calendar.']['editIcon'], array (
				'view' => 'edit_calendar',
			'type' => $this->getType(), 'uid' => $this->getUid(),'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['editCalendarViewPid']);
		}
		if ($this->isUserAllowedToDelete()) {
			$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_delete_calendar') . '" alt="' . $this->controller->pi_getLL('l_delete_calendar') . '"';
			$editlink .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['calendar.']['deleteIcon'], array (
				'view' => 'delete_calendar',
			'type' => $this->getType(), 'uid' => $this->getUid(), 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['deleteCalendarViewPid']);
		}
		return $editlink;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_calendar_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_calendar_model.php']);
}
?>