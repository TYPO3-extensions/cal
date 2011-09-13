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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_base_controller.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_registry.php');
/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_base_service extends t3lib_svbase {
	
	var $cObj; // The backReference to the mother cObj object set at call time
	var $rightsObj;
	var $modelObj;
	var $controller;
	var $conf;
	var $prefixId = 'tx_cal_controller';
	var $calendarService;
	var $categoryService;
	var $eventService;
	var $locationService;
	var $locationAddressService;
	var $locationPartnerService;
	var $organizerService;
	var $organizerAddressService;
	var $organizerPartnerService;
	var $fileFunc;
	
	function tx_cal_base_service(){
		$this->controller = &tx_cal_registry::Registry('basic','controller');
		$this->conf = &tx_cal_registry::Registry('basic','conf');
		$this->rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$this->cObj = &tx_cal_registry::Registry('basic','cobj');
		$this->modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
	}
	
	/**
	*	@deprecated	use the registry to retrieve the controller
	*/
	function setController(&$controller){
	}
	
	function insertIdsIntoTableWithMMRelation($mm_table,$idArray,$uid,$tablename){
		foreach($idArray as $foreignid){
			if(is_numeric ($foreignid)){
				$insertFields = array('uid_local'=>$uid, 'uid_foreign' => $foreignid, 'tablenames' =>$tablename);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($mm_table,$insertFields);
			}
		}
	}
	
	function _notifyOfChanges(&$event, &$insertFields){
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		$notificationService =& getNotificationService();
		$valueArray = $event->getValuesAsArray();
		$notificationService->notifyOfChanges($valueArray, $insertFields);
		
		$this->scheduleReminder($valueArray);
	}
	
	function _notify(&$insertFields){
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		$notificationService =& getNotificationService();
		$notificationService->notify($insertFields);
	}
	
	function scheduleReminder(&$eventAttributeRow){
		/* Schedule reminders for new and changed events */
		$offset = is_numeric($this->conf['view.']['event.']['remind.']['time']) ? $this->conf['view.']['event.']['remind.']['time'] * 60 : 0;
		$reminderTimestamp = $eventAttributeRow['start_time'] - $offset;
		if($reminderTimestamp>time()){
			require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
			$reminderService = &getReminderService();
			$reminderService->scheduleReminder($eventAttributeRow['uid'], $reminderTimestamp);
		}
	}
	
	function stopReminder($uid){
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		$reminderService = &getReminderService();
		$reminderService->deleteReminder($uid);
	}
	
	function &getEventService(){
		if(is_object($this->eventService)){
			return $this->eventService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_event_service.php');
		$this->eventService = & t3lib_div :: makeInstanceClassName('tx_cal_event_service');
		$this->eventService = & new $this->eventService();
		return $this->eventService;
	}
	
	function &getCalendarService(){
		$this->calendarService = &tx_cal_registry::Registry('service','calendar');
		
		if(is_object($this->calendarService)){
			return $this->calendarService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_calendar_service.php');
		$this->calendarService = t3lib_div :: makeInstanceClassName('tx_cal_calendar_service');
		$this->calendarService = new $this->calendarService();
		return $this->calendarService;
	}
	
	function &getCategoryService(){
		if(is_object($this->categoryService)){
			return $this->categoryService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_category_service.php');
		$this->categoryService = & t3lib_div :: makeInstanceClassName('tx_cal_category_service');
		$this->categoryService = & new $this->categoryService();
		return $this->categoryService;
	}
	
	function &getLocationService(){
		if(is_object($this->locationService)){
			return $this->locationService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_location_service.php');
		$this->locationService = t3lib_div :: makeInstanceClassName('tx_cal_location_service');
		$this->locationService = &new $this->locationService();
		return $this->locationService;
	}
	
	function &getLocationAddressService(){
		if(is_object($this->locationAddressService)){
			return $this->locationAddressService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_location_address_service.php');
		$this->locationAddressService = t3lib_div :: makeInstanceClassName('tx_cal_location_address_service');
		$this->locationAddressService = &new $this->locationAddressService();
		return $this->locationAddressService;
	}
	
	function &getLocationPartnerService(){
		if(is_object($this->locationPartnerService)){
			return $this->locationPartnerService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_location_partner_service.php');
		$this->locationPartnerService = t3lib_div :: makeInstanceClassName('tx_cal_location_partner_service');
		$this->locationPartnerService = &new $this->locationPartnerService();
		return $this->locationPartnerService;
	}
	
	function &getOrganizerService(){
		if(is_object($this->organizerService)){
			return $this->organizerService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_organizer_service.php');
		$this->organizerService = t3lib_div :: makeInstanceClassName('tx_cal_organizer_service');
		$this->organizerService = &new $this->organizerService();
		return $this->organizerService;
	}
	
	function &getOrganizerAddressService(){
		if(is_object($this->organizerAddressService)){
			return $this->organizerAddressService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_organizer_address_service.php');
		$this->organizerService = t3lib_div :: makeInstanceClassName('tx_cal_organizer_address_service');
		$this->organizerAddressService = &new $this->organizerAddressService();
		return $this->organizerAddressService;
	}
	
	function &getOrganizerPartnerService(){
		if(is_object($this->organizerPartnerService)){
			return $this->organizerPartnerService;	
		}
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_organizer_partner_service.php');
		$this->organizerService = t3lib_div :: makeInstanceClassName('tx_cal_organizer_partner_service');
		$this->organizerPartnerService = &new $this->organizerPartnerService();
		return $this->organizerPartnerService;
	}
	
	function start(){
		return 'Overwrite this: start() funtion of base_service';
	}
	
	function searchForAdditionalFieldsToAddFromPostData(&$insertFields,$object, $isSave=true){
		$fields = t3lib_div::trimExplode(',',$this->conf['rights.'][$isSave?'create.':'edit.'][$object.'.']['additionalFields'],1);
		foreach($fields as $field){
			if(($isSave && $this->rightsObj->isAllowedTo('create',$object,$field)) || (!$isSave && $this->rightsObj->isAllowedTo('edit',$object,$field))){
				if($this->conf['view.'][$this->conf['view'].'.']['additional_fields.'][$field.'_stdWrap.']){
					$insertFields[$field] = $this->cObj->stdWrap($this->controller->piVars[$field], $this->conf['view.'][$this->conf['view'].'.']['additional_fields.'][$field.'_stdWrap.']);
				}else{
					$insertFields[$field] = $this->controller->piVars[$field];
				}
			}
		}
	}
	
	function checkOnTempImage(&$insertFields){
		if(is_array($insertFields['image'])){
			$return = Array();
			foreach($insertFields['image'] as $image){
				$value = $this->_checkOnTempImage($image);
				if($value){
					$return[] = $value;
				}
			}
			$insertFields['image'] = implode(',',$return);
		}else{
			$insertFields['image'] = $this->_checkOnTempImage($insertFields['image']);
		}
	}
	
	function _checkOnTempImage($image){
		if(!$this->fileFunc){
			require_once (PATH_t3lib . 'class.t3lib_basicfilefunc.php');
			$this->fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			$all_files = Array();
			$all_files['webspace']['allow'] = '*';
			$all_files['webspace']['deny'] = '';
			$this->fileFunc->init('', $all_files);
		}
				
		if(substr($image,0,7)=='__NEW__'){
			$image = substr($image,7);
			$theDestFile = $this->fileFunc->getUniqueName($this->fileFunc->cleanFileName($image), 'uploads/tx_cal/pics');
			rename('typo3temp/'.$image,$theDestFile);
			return basename($theDestFile);
		}else if(substr($image,0,10)=='__DELETE__'){
			$image = substr($image,10);
			unlink('uploads/tx_cal/pics/'.$image);
			return false;
		}else{
			return $image;
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_base_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_base_service.php']);
}
?>