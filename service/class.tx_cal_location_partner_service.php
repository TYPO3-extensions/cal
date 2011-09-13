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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_organizer_partner.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');


/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location_partner_service extends tx_cal_base_service {
 
 	var $extensionIsNotLoaded = false;
 	var $keyId = 'tx_partner_main';
 	
 	function tx_cal_location_partner_service($controller){
		$this->tx_cal_base_service();
 		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_organizer');
 	 	if($useLocationStructure!='tx_partner_main'){
 	 		$this->extensionIsNotLoaded = true;
 	 		return;
 	 	}
 	 	require_once(t3lib_extMgm::extPath('partner').'api/class.tx_partner_main.php');
	}
 	
	/**
	 * Looks for an location with a given uid on a certain pid-list
	 * @param	array		$conf		The configuration array
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	object	A tx_cal_location_partner object
	 */
	function find($uid, $pidList){
		if(!$this->isAllowedService()) return;
		if($this->extensionIsNotLoaded){
			return;
		}
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_partner_main', ' uid='.$uid.' '.$this->cObj->enableFields('tx_partner_main'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_partner_main', ' pid IN ('.$pidList.') AND uid='.$uid.' '.$this->cObj->enableFields('tx_partner_main'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$tx_cal_location_partner = t3lib_div::makeInstanceClassName('tx_cal_location_partner');
			return new $tx_cal_location_partner('', $row['uid'], $pidList);
		}	
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	A tx_cal_organizer_partner object array
	 */
	function findAll($pidList){
		if(!$this->isAllowedService()) return;
		if($this->extensionIsNotLoaded){
			return;
		}
		$orderBy = getOrderBy('tx_partner_main');
		$locations = array();
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_partner_main', ' 0=0 '.$this->cObj->enableFields('tx_partner_main'), '', $orderBy);
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_partner_main', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_partner_main'), '', $orderBy);
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$tx_cal_location = t3lib_div::makeInstanceClassName('tx_cal_location_partner');
			$locations[] = new $tx_cal_location('', $row['uid'], $pidList);
		}
		return $locations;
	}
	
	/**
	 * Search for location
	 * @param	string	$pidList	The pid-list to search in
	 */
	function search($pidList=''){
		if(!$this->isAllowedService()) return;
		if($this->extensionIsNotLoaded){
			return;
		}
		$sw = strip_tags($this->controller->piVars['query']);
		$locationArray = array();
		$location = t3lib_div::makeInstanceClassName('tx_cal_location');
		if($sw!=''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_partner_main', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_partner_main').$this->searchWhere($sw));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$locationArray[] = new $location('', $row['uid'], $pidList);
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
		if($this->extensionIsNotLoaded){
			return;
		}
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchLocationFieldList'], 'tx_partner_main');
		return $where;
	}
	
	function updateLocation($uid){
		if(!$this->isAllowedService()) return;
		if($this->extensionIsNotLoaded){
			return;
		}
		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$table = 'tx_partner_main';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeLocation($uid){
		if(!$this->isAllowedService()) return;
		if($this->extensionIsNotLoaded){
			return;
		}
		if($this->rightsObj->isAllowedToDeleteLocations()){
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tx_partner_main';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		if(!$this->isAllowedService()) return;
		if($this->extensionIsNotLoaded){
			return;
		}
		$hidden = 0;
		if($this->controller->piVars['hidden']=='true' && 
				($this->rightsObj->isAllowedToEditLocationHidden() || $this->rightsObj->isAllowedToCreateLocationHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditLocationName() || $this->rightsObj->isAllowedToCreateLocationName()){
			$insertFields['name'] = strip_tags($this->controller->piVars['name']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationDescription() || $this->rightsObj->isAllowedToCreateLocationDescription()){
			$insertFields['title'] = $this->cObj->removeBadHTML($this->controller->piVars['description'],$this->conf);
		}
		
		if($this->rightsObj->isAllowedToEditLocationStreet() || $this->rightsObj->isAllowedToCreateLocationStreet()){
			$insertFields['address'] = strip_tags($this->controller->piVars['street']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationZip() || $this->rightsObj->isAllowedToCreateLocationZip()){
			$insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationCity() || $this->rightsObj->isAllowedToCreateLocationCity()){
			$insertFields['city'] = strip_tags($this->controller->piVars['city']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationCountryZone() || $this->rightsObj->isAllowedToCreateLocationCountryZone()){
			$insertFields['countryzone'] = strip_tags($this->controller->piVars['countryzone']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationCountry() || $this->rightsObj->isAllowedToCreateLocationCountry()){
			$inserFields['country'] = strip_tags($this->controller->piVars['country']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationPhone() || $this->rightsObj->isAllowedToCreateLocationPhone()){
			$insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationEmail() || $this->rightsObj->isAllowedToCreateLocationEmail()){
			$insertFields['email'] = strip_tags($this->controller->piVars['email']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationImage() || $this->rightsObj->isAllowedToCreateLocationImage()){
			$insertFields['image'] = strip_tags($this->controller->piVars['image']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationLink() || $this->rightsObj->isAllowedToCreateLocationLink()){
			$insertFields['www'] = strip_tags($this->controller->piVars['link']);
		}

	}
	
	function saveLocation($pid){
		if($this->extensionIsNotLoaded){
			return;
		}
		$crdate = time();
		$insertFields = array('pid' => $pid, 'tstamp' => $crdate, 'crdate' => $crdate);
		//TODO: Check if all values are correct
		
		$hidden = 0;
		if($this->controller->piVars['hidden']=='true')
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
		if($this->controller->piVars['name']!=''){
			$insertFields['name'] = strip_tags($this->controller->piVars['name']);
		}
		if($this->controller->piVars['description']!=''){
			$insertFields['title'] = $this->cObj->removeBadHTML($this->controller->piVars['description'],$this->conf);
		}
		if($this->controller->piVars['street']!=''){
			$insertFields['address'] = strip_tags($this->controller->piVars['street']);
		}
		if($this->controller->piVars['zip']!=''){
			$insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
		}
		if($this->controller->piVars['city']!=''){
			$insertFields['city'] = strip_tags($this->controller->piVars['city']);
		}
		if($this->controller->piVars['countryzone']!=''){
			$insertFields['countryzone'] = strip_tags($this->controller->piVars['countryzone']);
		}
		if($this->controller->piVars['country']!=''){
			$insertFields['country'] = strip_tags($this->controller->piVars['country']);
		}
		if($this->controller->piVars['phone']!=''){
			$insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
		}
		if($this->controller->piVars['email']!=''){
			$insertFields['email'] = strip_tags($this->controller->piVars['email']);
		}
		if($this->controller->piVars['image']!=''){
			$insertFields['image'] = strip_tags($this->controller->piVars['image']);
		}
		if($this->controller->piVars['link']!=''){
			$insertFields['www'] = strip_tags($this->controller->piVars['link']);
		}
		
		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$table = 'tx_partner_main';
						
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_partner_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_partner_service.php']);
}
?>