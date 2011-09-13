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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_organizer_feuser.php');
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');

/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_organizer_feuser_service extends tx_cal_base_service {
 	
 	var $keyId = 'tx_feuser';
 	
	function tx_cal_organizer_feuser_service(){
		$this->tx_cal_base_service();
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	array		$conf		The configuration array
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	object		A tx_cal_organizer_feuser object
	 */
	function find($uid, $pidList){
		if(!$this->isAllowedService()) return;
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid='.$uid.' '.$this->cObj->enableFields('fe_users'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', ' pid IN ('.$pidList.') AND uid='.$uid.' '.$this->cObj->enableFields('fe_users'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$tx_cal_organizer_feuser = t3lib_div::makeInstanceClassName('tx_cal_organizer_feuser');
			return new $tx_cal_organizer_feuser($row, $pidList);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array		A tx_cal_organizer_feuser object array
	 */
	function findAll($pidList){
		if(!$this->isAllowedService()) return;
		$organizer = array();
		$orderBy = getOrderBy('fe_users');
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', ' tx_cal_controller_isorganizer = 1 '.$this->cObj->enableFields('fe_users'), '', $orderBy);
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('fe_users'), '', $orderBy);
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$tx_cal_organizer_feuser = t3lib_div::makeInstanceClassName('tx_cal_organizer_feuser');
			$organizer[] = new $tx_cal_organizer_feuser($row, $pidList);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		return $organizer;
	}
	
	/**
	 * Search for organizer
	 * @param	string	$pidList	The pid-list to search in
	 */
	function search($pidList=''){
		if(!$this->isAllowedService()) return;
		$sw = strip_tags($this->controller->piVars['query']);
		$organizerArray = array();
		$organizer = t3lib_div::makeInstanceClassName('tx_cal_organizer_feuser');
		if($sw!=''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('fe_users').$this->searchWhere($sw));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$organizerArray[] = new $organizer($row, $pidList);
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
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchOrganizerFieldList'], 'fe_users');
		return $where;
	}
	
	function updateOrganizer($uid){

		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		$uid = $this->checkUidForLanguageOverlay($uid,'fe_users');
		// Creating DB records
		$table = 'fe_users';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeOrganizer($uid){
		if(!$this->isAllowedService()) return;
		if($this->rightsObj->isAllowedToDeleteOrganizer()){
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'fe_users';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		if(!$this->isAllowedService()) return;
		$hidden = 0;
		if($this->controller->piVars['hidden']=='true' && 
				($this->rightsObj->isAllowedToEditOrganizerHidden() || $this->rightsObj->isAllowedToCreateOrganizerHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditOrganizerName() || $this->rightsObj->isAllowedToCreateOrganizerName()){
			$insertFields['name'] = strip_tags($this->controller->piVars['name']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerDescription() || $this->rightsObj->isAllowedToCreateOrganizerDescription()){
			$insertFields['title'] = $this->cObj->removeBadHTML($this->controller->piVars['title'],$this->conf);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerStreet() || $this->rightsObj->isAllowedToCreateOrganizerStreet()){
			$insertFields['address'] = strip_tags($this->controller->piVars['address']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerZip() || $this->rightsObj->isAllowedToCreateOrganizerZip()){
			$insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerCity() || $this->rightsObj->isAllowedToCreateOrganizerCity()){
			$insertFields['city'] = strip_tags($this->controller->piVars['city']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerPhone() || $this->rightsObj->isAllowedToCreateOrganizerPhone()){
			$insertFields['phone'] = strip_tags($this->controller->piVars['phone']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerEmail() || $this->rightsObj->isAllowedToCreateOrganizerEmail()){
			$insertFields['email'] = strip_tags($this->controller->piVars['email']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerImage() || $this->rightsObj->isAllowedToCreateOrganizerImage()){
			$insertFields['image'] = strip_tags($this->controller->piVars['image']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerLink() || $this->rightsObj->isAllowedToCreateOrganizerLink()){
			$insertFields['www'] = strip_tags($this->controller->piVars['www']);
		}

	}
	
	function saveOrganizer($pid){
		if(!$this->isAllowedService()) return;
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
			$insertFields['title'] = $this->cObj->removeBadHTML($this->controller->piVars['title']);
		}
		if($this->controller->piVars['street']!=''){
			$insertFields['address'] = strip_tags($this->controller->piVars['address']);
		}
		if($this->controller->piVars['zip']!=''){
			$insertFields['zip'] = strip_tags($this->controller->piVars['zip']);
		}
		if($this->controller->piVars['city']!=''){
			$insertFields['city'] = strip_tags($this->controller->piVars['city']);
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
			$insertFields['www'] = strip_tags($this->controller->piVars['www']);
		}
		
		// Creating DB records
		$insertFields['cruser_id'] = $this->rightsObj->getUserId();
		$this->_saveOrganizer($insertFields);
	}
	
	function _saveOrganizer(&$insertFields){
		$table = 'fe_users';
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
	
	function isAllowedService(){
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_location');
		if($useOrganizerStructure==$this->keyId){
			return true;
		}
		return false;		
	}
	
	function createTranslation($uid, $overlay){
		$table = 'fe_users';
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
			$this->_saveOrganizer($row);
			return;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_organizer_feuser_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_organizer_feuser_service.php']);
}
?>