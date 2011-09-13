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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_location_address.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');

/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location_address_service extends tx_cal_base_service {
	
	var $keyId = 'tx_tt_address';
	
	function tx_cal_location_address_service(){
		$this->tx_cal_base_service();
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	array		$conf		The configuration array
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	object	A tx_cal_organizer_partner object
	 */
	function find($uid, $pidList){
		if(!$this->isAllowedService()) return;
		$orderBy = tx_cal_functions::getOrderBy('tt_address');
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', ' tx_cal_controller_islocation=1 AND uid='.$uid.' '.$this->cObj->enableFields('tt_address'), '', $orderBy);
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', ' pid IN ('.$pidList.') AND tx_cal_controller_islocation=1 AND uid='.$uid.' '.$this->cObj->enableFields('tt_address'), '', $orderBy);
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			return tx_cal_functions::makeInstance('tx_cal_location_address',$row, $pidList);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	A tx_cal_organizer_partner object array
	 */
	function findAll($pidList){
		if(!$this->isAllowedService()) return;
		$locations = array();
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', ' tx_cal_controller_islocation=1 '.$this->cObj->enableFields('tt_address'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', ' pid IN ('.$pidList.') AND tx_cal_controller_islocation=1 '.$this->cObj->enableFields('tt_address'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$locations[] = &tx_cal_functions::makeInstance('tx_cal_location_address',$row, $pidList);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		return $locations;
	}
	
	/**
	 * Search for organizer
	 * @param	string	$pidList	The pid-list to search in
	 */
	function search($pidList=''){
		if(!$this->isAllowedService()) return;
		$sw = strip_tags($this->controller->piVars['query']);
		$organizerArray = array();
		if($sw!=''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', ' pid IN ('.$pidList.') AND tx_cal_controller_islocation = 1 '.$this->cObj->enableFields('tt_address').$this->searchWhere($sw));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$organizerArray[] = &tx_cal_functions::makeInstance('tx_cal_location_address',$row, $pidList);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			
		}
		return $organizerArray;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		if(!$this->isAllowedService()) return;
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchLocationFieldList'], 'tt_address');
		return $where;
	}
	
	function updateLocation($uid){

		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		$uid = $this->checkUidForLanguageOverlay($uid,'tt_address');
		// Creating DB records
		$table = 'tt_address';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeLocation($uid){
		if(!$this->isAllowedService()) return;
		if($this->rightsObj->isAllowedToDeleteLocations()){
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tt_address';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=='true' && 
				($this->rightsObj->isAllowedToEditLocationHidden() || $this->rightsObj->isAllowedToCreateLocationHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditLocationName() || $this->rightsObj->isAllowedToCreateLocationName()){
			$insertFields['name'] = strip_tags($this->controller->piVars['name']);
		}
		
		if($this->rightsObj->isAllowedToEditLocationDescription() || $this->rightsObj->isAllowedToCreateLocationDescription()){
			$insertFields['title'] = strip_tags($this->controller->piVars['description']);
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
		
		if($this->rightsObj->isAllowedToEditLocationCountry() || $this->rightsObj->isAllowedToCreateLocationCountry()){
			$insertFields['country'] = strip_tags($this->controller->piVars['country']);
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
			$insertFields['title'] = strip_tags($this->controller->piVars['description']);
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
		$this->_saveLocation($insertFields);
	}
	
	function _saveLocation(&$insertFields){
		$table = 'tt_address';
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
		$table = 'tt_address';
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_address_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_location_address_service.php']);
}
?>
