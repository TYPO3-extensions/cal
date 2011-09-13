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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_svbase.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_location_model.php');

/**
 * Base model for the calendar location.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location_address extends tx_cal_location_model {
 	 
 	 var $type = "tx_tt_address";
 	 
 	 /**
 	  * Constructor
 	  * @param	object		$cObj		The content object
	  * @param	integer		$uid		The uid to search for
	  * @param	string		$pidList	The pid-list to search in
 	  */
 	 function tx_cal_location_address(&$cObj, &$rightsObj, $row, $pidList){
 	 	
 	 	$this->tx_cal_location_model($cObj, $rightsObj, $this->type);
		$this->createLocation($row);
 	 }
	 
	 function renderLocation(){

		$lastview = $this->cObj->conf['lastview'];
		$uid  = $this->cObj->conf['uid'];
		$type = $this->cObj->conf['type'];
		$monitoring  = $this->cObj->conf['monitor'];
		$getdate  = $this->cObj->conf['getdate'];
		$page = $this->cObj->fileResource($this->cObj->conf["view."]["location."]["locationTemplate4Address"]);
		if ($page=="") {
			return "<h3>calendar: no location template file found:</h3>".$this->cObj->conf["view."]["location."]["locationTemplate4Address"];
		}

		$rems = array();
		$sims = array();
		$postlocation = $this->cObj->getSubpart($page, "###POST_LOCATION###");
		$locationloop = $this->cObj->getSubpart($page, "###LOCATION###");
		$phone = $this->getPhone();
		$mobilephone = $this->getMobilephone();
		$fax = $this->getFax();
		$email = $this->getEmail();
		$link = $this->getLink();

		if($phone!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###PHONE_DATA_LOOP###");
			$phonerems['###PHONE_LOOP###'] = $phone;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###PHONE_DATA_LOOP###'] = $loop;
			$sims['phone_label'] = $this->shared->lang('l_location_phone');	
		}else{
			$rems['###PHONE_DATA_LOOP###'] = "";
		}
		if($mobilephone!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###MOBILEPHONE_DATA_LOOP###");
			$phonerems['###MOBILEPHONE_LOOP###'] = $mobilephone;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###MOBILEPHONE_DATA_LOOP###'] = $loop;
			$sims['mobilephone_label'] = $this->shared->lang('l_location_mobilephone');	
		}else{
			$rems['###MOBILEPHONE_DATA_LOOP###'] = "";
		}
		if($fax!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###FAX_DATA_LOOP###");
			$phonerems['###FAX_LOOP###'] = $fax;
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###FAX_DATA_LOOP###'] = $loop;
			$sims['fax_label'] = $this->shared->lang('l_location_fax');	
		}else{
			$rems['###FAX_DATA_LOOP###'] = "";
		}
		if($email!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###EMAIL_DATA_LOOP###");
			$phonerems['###EMAIL_LOOP###'] = $this->cObj->parseFunc("<link ".$this->getEmail().">".$this->getEmail()."</link>",$this->cObj->conf["parseFunc."]);
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###EMAIL_DATA_LOOP###'] = $loop;
			$sims['email_label'] = $this->shared->lang('l_location_email');	
		}else{
			$rems['###EMAIL_DATA_LOOP###'] = "";
		}
		if($link!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###LINK_DATA_LOOP###");
			$phonerems['###LINK_LOOP###'] = $this->cObj->parseFunc("<link ".$this->getLink().">".$this->getLink()."</link>",$this->cObj->conf["parseFunc."]);
			$loop = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$rems['###LINK_DATA_LOOP###'] = $loop;
			$sims['link_label'] = $this->shared->lang('l_location_link');	
		}else{
			$rems['###LINK_DATA_LOOP###'] = "";
		}
		
		if($this->getName()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###NAME_DATA_LOOP###");

			$sims['name'] = $this->getName();
			$sims['name_label'] = $this->shared->lang('l_location_name');
		}else{
			$rems['###NAME_DATA_LOOP###'] = "";
		}
		if($this->getDescription()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###DESCRIPTION_DATA_LOOP###");
			$sims['description'] = nl2br($this->cObj->parseFunc($this->getDescription(),$this->cObj->conf["parseFunc."]));
			$sims['description_label'] = $this->shared->lang('l_location_description');	
		}else{
			$rems['###DESCRIPTION_DATA_LOOP###'] = "";
		}
		if($this->getStreet()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###STREET_DATA_LOOP###");
			$sims['street'] = $this->getStreet();
			$sims['street_label'] = $this->shared->lang('l_location_street');
		}else{
			$rems['###STREET_DATA_LOOP###'] = "";
		}
		if($this->getPostalCode()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###ZIP_DATA_LOOP###");
			$sims['zip'] = $this->getPostalCode();
			$sims['zip_label'] = $this->shared->lang('l_location_zip');	
		}else{
			$rems['###ZIP_DATA_LOOP###'] = "";
		}
		if($this->getCity()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###CITY_DATA_LOOP###");
			$sims['city'] = $this->getCity();
			$sims['city_label'] = $this->shared->lang('l_location_city');	
		}else{
			$rems['###CITY_DATA_LOOP###'] = "";
		}
		if($this->getImage()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###IMAGE_DATA_LOOP###");
			$sims['image_src'] = 'uploads/pics/' .$this->getImage();
			$sims['image_label'] = $this->shared->lang('l_location_image');	
		}else{
			$rems['###IMAGE_DATA_LOOP###'] = "";
		}

		$locationloop = $this->cObj->substituteMarkerArrayCached($locationloop,  array (), $rems, array ());
		
		$sims["heading"] = $this->shared->lang('l_event_location');
		$sims["editlink"] = "";

		$prelocation = $this->cObj->getSubpart($page, "###PRE_LOCATION###");
		$page = $postlocation.$locationloop.$prelocation;
		return $this->shared->replace_tags($sims, $page);
	 }
	 

	function updateLocation($rightsObj, &$conf, $uid){

		$this->rightsObj = $rightsObj;
		$insertFields = array("tstamp" => time());
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$insertFields = array();
		$table = "tt_address";
		$where = "uid = ".$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
	}
	
	function removeLocation(&$rightsObj, &$conf, $uid){
		$this->rightsObj = $rightsObj;		
		if($rightsObj->isAllowedToDeleteLocations()){
			$updateFields = array("tstamp" => time(), "deleted" => 1);
			$table = "tt_address";
			$where = "uid = ".$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=="true" && 
				($this->rightsObj->isAllowedToEditLocationHidden() || $this->rightsObj->isAllowedToCreateLocationHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditLocationName() || $this->rightsObj->isAllowedToCreateLocationName()){
			$insertFields['name'] = $this->controller->piVars['name'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationDescription() || $this->rightsObj->isAllowedToCreateLocationDescription()){
			$insertFields['title'] = $this->controller->piVars['description'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationStreet() || $this->rightsObj->isAllowedToCreateLocationStreet()){
			$insertFields['address'] = $this->controller->piVars['street'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationZip() || $this->rightsObj->isAllowedToCreateLocationZip()){
			$insertFields['zip'] = $this->controller->piVars['zip'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationCity() || $this->rightsObj->isAllowedToCreateLocationCity()){
			$insertFields['city'] = $this->controller->piVars['city'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationPhone() || $this->rightsObj->isAllowedToCreateLocationPhone()){
			$insertFields['phone'] = $this->controller->piVars['phone'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationEmail() || $this->rightsObj->isAllowedToCreateLocationEmail()){
			$insertFields['email'] = $this->controller->piVars['email'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationImage() || $this->rightsObj->isAllowedToCreateLocationImage()){
			$insertFields['image'] = $this->controller->piVars['image'];
		}
		
		if($this->rightsObj->isAllowedToEditLocationLink() || $this->rightsObj->isAllowedToCreateLocationLink()){
			$insertFields['www'] = $this->controller->piVars['link'];
		}

	}
	
	function saveLocation($rightsObj, $conf=array(), $pid){

		$crdate = time();
		$insertFields = array("pid" => $pid, "tstamp" => $crdate, "crdate" => $crdate);
		//TODO: Check if all values are correct
		
		$hidden = 0;
		if($this->controller->piVars['hidden']=="true")
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
		if($this->controller->piVars['name']!=''){
			$insertFields['name'] = $this->controller->piVars['name'];
		}
		if($this->controller->piVars['description']!=''){
			$insertFields['title'] = $this->controller->piVars['description'];
		}
		if($this->controller->piVars['street']!=''){
			$insertFields['address'] = $this->controller->piVars['street'];
		}
		if($this->controller->piVars['zip']!=''){
			$insertFields['zip'] = $this->controller->piVars['zip'];
		}
		if($this->controller->piVars['city']!=''){
			$insertFields['city'] = $this->controller->piVars['city'];
		}
		if($this->controller->piVars['phone']!=''){
			$insertFields['phone'] = $this->controller->piVars['phone'];
		}
		if($this->controller->piVars['email']!=''){
			$insertFields['email'] = $this->controller->piVars['email'];
		}
		if($this->controller->piVars['image']!=''){
			$insertFields['image'] = $this->controller->piVars['image'];
		}
		if($this->controller->piVars['link']!=''){
			$insertFields['www'] = $this->controller->piVars['link'];
		}
		
		// Creating DB records
		$insertFields['cruser_id'] = $rightsObj->getUserId();
		$table = "tt_address";
						
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
	
	function search(&$cObj, $pidList=''){
		$this->cObj = $cObj;
		$this->cObj->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->cObj);
		$sw = $this->controller->piVars['query'];
		$location = array();
		if($sw!=""){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tt_address", " pid IN (".$pidList.") AND hidden = 0 AND deleted = 0 ".$this->searchWhere($sw));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$location[] = $this->createLocation($row);
			}
		}
		return $location;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->cObj->conf["view."]["search."]["searchLocationFieldList"], 'tt_address');
		return $where;
	}
	
	/**
	 *  
	 */
	function getCalLegendDescription() { 
		return array(); 
	}
	
	function createLocation($row){
	 	$this->setUid($row['uid']);
		$this->setName($row['name']);
		$this->setDescription($row['title']);
		$this->setStreet($row['address']);
		$this->setPostalCode($row['zip']);
		$this->setCity($row['city']);
		$this->setPhone($row['phone']);
		$this->setEmail($row['email']);
		$this->setImage($row['image']);
		$this->setLink($row['link']);
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_address.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_address.php']);
}
?>