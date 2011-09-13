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
	 * @return	object	A tx_cal_organizer_partner object
	 */
	function find($uid, $pidList){
		if(!$this->isAllowedService()) return;
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_location', ' uid='.$uid.' '.$this->cObj->enableFields('tx_cal_location'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_location', ' pid IN ('.$pidList.') AND uid='.$uid.' '.$this->cObj->enableFields('tx_cal_location'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$tx_cal_location = t3lib_div::makeInstanceClassName('tx_cal_location');
			return new $tx_cal_location('', $row, $pidList);
		}
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	A tx_cal_organizer_partner object array
	 */
	function findAll($pidList){
		if(!$this->isAllowedService()) return;
		$locations = array();
		$orderBy = getOrderBy('tx_cal_location');
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_location', ' 0=0 '.$this->cObj->enableFields('tx_cal_location'), '', $orderBy);
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_location', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_location'), '', $orderBy);
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$tx_cal_location = t3lib_div::makeInstanceClassName('tx_cal_location');
			$locations[] = new $tx_cal_location('', $row, $pidList);
		}
		return $locations;
	}
	
	/**
	 * Search for locations
	 * @param	string	$pidList	The pid-list to search in
	 */
	function search($pidList='', $searchword){
		if(!$this->isAllowedService()) return;
		$locationArray = array();
		$location = t3lib_div::makeInstanceClassName('tx_cal_location');
		if($searchword!=''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_location', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_location').$this->searchWhere($searchword));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$locationArray[] = new $location('', $row, $pidList);
			}
		}
		return $locationArray;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		if(!$this->isAllowedService()) return;
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchLocationFieldList'], 'tx_cal_location');
		return $where;
	}
	
	function updateLocation($uid){
		if(!$this->isAllowedService()) return;
		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData($insertFields,'location',false);
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$table = 'tx_cal_location';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeLocation($uid){
		if(!$this->isAllowedService()) return;
		if($this->rightsObj->isAllowedToDeleteLocations()){
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tx_cal_location';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->conf['rights.']['create.']['location.']['fields.']['allowedToCreateHidden.']['default'] && !$this->rightsObj->isAllowedTo('create','location','hidden') && !$this->rightsObj->isAllowedTo('create','location','hidden')){
			$hidden = $this->conf['rights.']['create.']['location.']['fields.']['allowedToCreateHidden.']['default'];
		}else if($this->conf['rights.']['edit.']['location.']['fields.']['allowedToEditHidden.']['default'] && !$this->rightsObj->isAllowedTo('edit','location','hidden') && !$this->rightsObj->isAllowedTo('create','location','hidden')){
			$hidden = $this->conf['rights.']['location.']['event.']['fields.']['allowedToEditHidden.']['default'];
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
		
		if($this->rightsObj->isAllowedToEditLocationCountryZone() || $this->rightsObj->isAllowedToCreatedLocationCountryZone()) {
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
		
		if($this->rightsObj->isAllowedTo('edit','location','image') || $this->rightsObj->isAllowedTo('create','location','image')){
			$insertFields['image'] = $this->controller->piVars['image'];
			$this->checkOnTempImage($insertFields);
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_service.php']);
}
?>