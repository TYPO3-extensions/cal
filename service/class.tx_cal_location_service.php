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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_location.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');

/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location_service extends tx_cal_base_service {
 	
 	var $keyId = 'tx_cal_location';
 	
	function tx_cal_location_service(){
		$this->tx_cal_base_service();
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	object	A tx_cal_organizer object
	 */
	function find($uid, $pidList){
		if(!$this->isAllowedService()) return;
		$locationArray = $this->getLocationFromTable($pidList, ' AND tx_cal_location.uid='.$uid);
		return $locationArray[0];
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	A tx_cal_organizer object array
	 */
	function findAll($pidList){
		if(!$this->isAllowedService()) return;
		return $this->getLocationFromTable($pidList);
	}
	
	/**
	 * Search for locations
	 * @param	string	$pidList	The pid-list to search in
	 * @return	array	Array containing the location objects
	 */
	function search($pidList='', $searchword){
		if(!$this->isAllowedService() || strlen($searchword) < 2) {
			return array();
		} 
		$where = $this->searchWhere($searchword);
		return $this->getLocationFromTable($pidList, $where);
	}
	
	/**
	 * Generates the sql query and builds location objects out of the result rows
	 * @param	string	$pidList	The pid-list to search in
	 * @param	string	$additionalWhere	An additional where clause
	 * @return	array	Array conatining the location objects
	 */
	function getLocationFromTable($pidList='', $additionalWhere=''){
		$locations = array();
		$orderBy = getOrderBy('tx_cal_location');
		if($pidList!=''){
			$additionalWhere .= ' AND tx_cal_location.pid IN ('.$pidList.')';
		}
		$additionalWhere .= $this->getAdditionalWhereForLocalizationAndVersioning('tx_cal_location');
		$table = 'tx_cal_location,tx_cal_event';
		$select = 'tx_cal_location.*, tx_cal_event.uid AS event_uid, tx_cal_event.title AS event_title, tx_cal_event.start_date AS event_start_date';
		/* @fixme	Including events breaks the query for organizers if they're not already in an event */
		if(!$this->conf['view.'][$this->conf['view'].'.']['location.']['includeEventsInResult']){
			$table = 'tx_cal_location';
			$select = '*';
			$where = ' 1=1 ';
		}else{
			$where = 'tx_cal_location.uid = tx_cal_event.location_id AND tx_cal_event.l18n_parent = 0'.$this->cObj->enableFields('tx_cal_event');
		}
		$uids = array();
		$lastLocation;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where.$additionalWhere.$this->cObj->enableFields('tx_cal_location'), '', $orderBy);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			if ($GLOBALS['TSFE']->sys_language_content) {
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_cal_location', $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL, '');
			}
			if ($this->versioningEnabled) {
				// get workspaces Overlay
				$GLOBALS['TSFE']->sys_page->versionOL('tx_cal_location',$row);
			}
			if(in_array($row['uid'],$uids)){
				$this->_addEventLinkToLocation($lastLocation, $row['event_uid']);
				continue;
			}
			$uids[] = $row['uid'];
			$tx_cal_location = t3lib_div::makeInstanceClassName('tx_cal_location');
			$lastLocation = new $tx_cal_location($row, $pidList);
			/* @fixme	Including events breaks the query for organizers if they're not already in an event */
			if($this->conf['view.'][$this->conf['view'].'.']['location.']['includeEventsInResult']){
				$this->_addEventLinkToLocation($lastLocation, $row['event_uid']);
			}
			$locations[] = $lastLocation;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		return $locations;
	}
	
	function _addEventLinkToLocation(&$location, $event_uid){
		$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		$event_s = $modelObj->findAllEventInstances($event_uid, 'tx_cal_phpicalendar', $this->conf['pidList'], false, false, true);
		if(is_array($event_s)){
			foreach($event_s as $date=>$time){
				foreach($time as $eventArray){
					foreach($eventArray as $event){
						$location->addEventLink($event->renderEventForLocation());
					}
				}
			}
		}else{
			$location->addEventLink($event->renderEventForLocation());
		}
	}
	
	
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		if(!$this->isAllowedService()) {
			$where = '';
		} else {
			$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchLocationFieldList'], 'tx_cal_location');
		}
		return $where;
	}
	
	function updateLocation($uid){
		if(!$this->isAllowedService()) return;
		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'location',false);
		$this->retrievePostData($insertFields);
		$uid = $this->checkUidForLanguageOverlay($uid,'tx_cal_location');
		// Creating DB records
		$table = 'tx_cal_location';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
		$this->unsetPiVars();
	}
	
	function removeLocation($uid){
		if(!$this->isAllowedService()) return;
		if($this->rightsObj->isAllowedToDeleteLocations()){
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tx_cal_location';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
		$this->unsetPiVars();
	}
	
	function retrievePostData(&$insertFields){

		$hidden = 0;
		if($this->conf['rights.']['create.']['location.']['fields.']['hidden.']['default'] && !$this->rightsObj->isAllowedTo('create','location','hidden') && !$this->rightsObj->isAllowedTo('create','location','hidden')){
			$hidden = $this->conf['rights.']['create.']['location.']['fields.']['hidden.']['default'];
		}else if($this->conf['rights.']['edit.']['location.']['fields.']['hidden.']['default'] && !$this->rightsObj->isAllowedTo('edit','location','hidden') && !$this->rightsObj->isAllowedTo('create','location','hidden')){
			$hidden = $this->conf['rights.']['location.']['event.']['fields.']['hidden.']['default'];
		}else if($this->controller->piVars['hidden'] == 'true' && ($this->rightsObj->isAllowedTo('edit','location','hidden') || $this->rightsObj->isAllowedTo('create','location','hidden'))){
			$hidden = 1;
		}
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditLocationName() || $this->rightsObj->isAllowedToCreateLocationName()){
			$insertFields['name'] = strip_tags($this->controller->piVars['name']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationDescription() || $this->rightsObj->isAllowedToCreateLocationDescription()){
			$insertFields['description'] = $this->cObj->removeBadHTML($this->controller->piVars['description'],$this->conf);
		}
		
		if($this->rightsObj->isAllowedToEditLocationStreet() || $this->rightsObj->isAllowedToCreateLocationStreet()){
			$insertFields['street'] = strip_tags($this->controller->piVars['street']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationZip() || $this->rightsObj->isAllowedToCreateLocationZip()){
			$insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationCity() || $this->rightsObj->isAllowedToCreateLocationCity()){
			$insertFields['city'] = strip_tags($this->controller->piVars['city']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationCountryZone() || $this->rightsObj->isAllowedToCreateLocationCountryZone()) {
			$insertFields['country_zone'] = strip_tags($this->controller->piVars['countryzone']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationCountry() || $this->rightsObj->isAllowedToCreateLocationCountry()){
			$insertFields['country'] = strip_tags($this->controller->piVars['country']);
		}
				
		if($this->rightsObj->isAllowedToEditLocationPhone() || $this->rightsObj->isAllowedToCreateLocationPhone()){
			$insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationEmail() || $this->rightsObj->isAllowedToCreateLocationEmail()){
			$insertFields['email'] = strip_tags($this->controller->piVars['email']);
		}
		
		if($this->rightsObj->isAllowedTo('edit','event','image') || $this->rightsObj->isAllowedTo('create','event','image')){
			$this->checkOnNewOrDeletableFiles('tx_cal_location', 'image', $insertFields);
			$insertFields['imagecaption'] = $this->cObj->removeBadHTML($this->controller->piVars['image_caption'],$this->conf);
			$insertFields['imagealttext'] = $this->cObj->removeBadHTML($this->controller->piVars['image_alt'],$this->conf);
			$insertFields['imagetitletext'] = $this->cObj->removeBadHTML($this->controller->piVars['image_title'],$this->conf);
		}
		
		if($this->rightsObj->isAllowedTo('edit','location','link') || $this->rightsObj->isAllowedTo('create','location','link')){
			$insertFields['link'] = strip_tags($this->controller->piVars['link']);
		}

	}
	
	function saveLocation($pid){
		if(!$this->isAllowedService()) return;
		$crdate = time();
		$insertFields = array('pid' => $pid, 'tstamp' => $crdate, 'crdate' => $crdate);
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'location');
		$this->retrievePostData($insertFields);
		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$this->_saveLocation($insertFields);
		$this->unsetPiVars();
	}
	
	function _saveLocation(&$insertFields){
		$table = 'tx_cal_location';
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
	
	function isAllowedService(){
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');
		if($useLocationStructure==$this->keyId){
			return true;
		}
		return false;		
	}
	
	function createTranslation($uid, $overlay){
		$table = 'tx_cal_location';
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
			$this->_saveLocation($row);
			return;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
	}
	
	function unsetPiVars(){
		unset($this->controller->piVars['hidden']);
		unset($this->controller->piVars['_TRANSFORM_description']);
		unset($this->controller->piVars['uid']);
		unset($this->controller->piVars['type']);
		unset($this->controller->piVars['formCheck']);
		unset($this->controller->piVars['name']);
		unset($this->controller->piVars['description']);
		unset($this->controller->piVars['street']);
		unset($this->controller->piVars['zip']);
		unset($this->controller->piVars['city']);
		unset($this->controller->piVars['country']);
		unset($this->controller->piVars['countryzone']);
		unset($this->controller->piVars['phone']);
		unset($this->controller->piVars['email']);
		unset($this->controller->piVars['link']);
		unset($this->controller->piVars['image']);
		unset($this->controller->piVars['image_caption']);
		unset($this->controller->piVars['image_title']);
		unset($this->controller->piVars['image_alt']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_service.php']);
}
?>