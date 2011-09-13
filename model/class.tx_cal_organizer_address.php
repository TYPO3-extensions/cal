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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_location_model.php');

/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_organizer_address extends tx_cal_location_model {
 	 
 	 var $type = 'tx_tt_address';
 	 
 	 /**
 	  * Constructor
 	  * @param	object		$controller	The controller object
	  * @param	integer		$uid		The uid to search for
	  * @param	string		$pidList	The pid-list to search in
 	  */
 	 function tx_cal_organizer_address(&$controller, $row, $pidlist){
 	 	
 	 	$this->tx_cal_location_model($controller, $this->type);
		$this->createOrganizer($row);
 	 }
	 
	 function renderOrganizer(){

		$page = $this->cObj->fileResource($this->conf['view.']['organizer.']['organizerTemplate4Address']);
		if ($page=='') {
			return '<h3>calendar: no organizer template file found:</h3>'.$this->conf['view.']['organizer.']['organizerTemplate4Address'];
		}
		$rems = array();
		$sims = array();
		$this->getLocationMarker($page, $sims, $rems);

		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	 }
	
	function updateOrganizer($uid){

		$insertFields = array('tstamp' => time());
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$insertFields = array();
		$table = 'tt_address';
		$where = 'uid = '.$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeOrganizer($uid){
	
		if($this->rightsObj->isAllowedToDeleteOrganizers()){
			$updateFields = array('tstamp' => time(), 'deleted' => 1);
			$table = 'tt_address';
			$where = 'uid = '.$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=='true' && 
				($this->rightsObj->isAllowedToEditOrganizerHidden() || $this->rightsObj->isAllowedToCreateOrganizerHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditOrganizerName() || $this->rightsObj->isAllowedToCreateOrganizerName()){
			$insertFields['name'] = strip_tags($this->controller->piVars['name']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerDescription() || $this->rightsObj->isAllowedToCreateOrganizerDescription()){
			$insertFields['title'] = strip_tags($this->controller->piVars['description']);
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerStreet() || $this->rightsObj->isAllowedToCreateOrganizerStreet()){
			$insertFields['address'] = strip_tags($this->controller->piVars['street']);
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
			$insertFields['www'] = strip_tags($this->controller->piVars['link']);
		}

	}
	
	function saveOrganizer($pid){

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
		$insertFields['cruser_id'] = $rightsObj->getUserId();
		$table = 'tt_address';
						
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
	
	function search($pidList=''){

		$sw = strip_tags($this->controller->piVars['query']);
		$organizer = array();
		if($sw!=''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tt_address', ' pid IN ('.$pidList.') '.$this->cObj->enableFields('tt_address').$this->searchWhere($sw));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$organizer[] = $this->createOrganizer($row);
			}
		}
		return $organizer;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchOrganizerFieldList'], 'tt_address');
		return $where;
	}

	
	function createOrganizer($row){
	 	$this->setUid($row['uid']);
		$this->setName($row['name']);
		$this->setDescription($row['title']);
		$this->setStreet($row['address']);
		$this->setPostalCode($row['zip']);
		$this->setCity($row['city']);
		$this->setPhone($row['phone']);
		$this->setEmail($row['email']);
		$this->setImage($row['image']);
		$this->setLink($row['www']);
		$this->row = $row;
	 }
	 
	 
	/**
	 * Returns the type attribute
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute. This should be the service type
	 * @param	$type	string	The service type
	 */
	function setType($type) {
		$this->type = $type;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_organizer_address.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_organizer_address.php']);
}
?>