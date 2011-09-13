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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_shared.php');

/**
 * Base model for the calendar organizer.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_organizer extends t3lib_svbase {
 	 
 	 var $local_cObj;
 	 var $shared;
 	 var $prefixId = "tx_cal_controller";
 	 
 	 var $uid;
 	 var $name;
 	 var $description;
 	 var $street;
 	 var $zip;
 	 var $city;
 	 var $phone;
 	 var $email;
 	 var $image;
 	 var $link;
 	 
	 function find($uid, $pidList){
	 	$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->local_cObj);
		
		$organizer = t3lib_div::makeInstance(get_class($this));
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tt_address", " pid IN (".$pidList.") AND hidden = 0 AND deleted = 0 AND uid=".$uid);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$organizer->setUid($row['uid']);
			$organizer->setName($row['name']);
			$organizer->setDescription($row['title']);
			$organizer->setStreet($row['address']);
			$organizer->setZip($row['zip']);
			$organizer->setCity($row['city']);
			$organizer->setPhone($row['phone']);
			$organizer->setEmail($row['email']);
			$organizer->setImage($row['image']);
			$organizer->setLink($row['www']);
		}
		
		return $organizer;
	 }
	 
	 function renderOrganizer($cObj){
	 	$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;	
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->cObj);

		$lastview = $this->conf['lastview'];
		$uid  = $this->conf['uid'];
		$type = $this->conf['type'];
		$monitoring  = $this->conf['monitor'];
		$getdate  = $this->conf['getdate'];
		$page = $this->cObj->fileResource($this->conf["view."]["organizer."]["organizerTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no organizer template file found:</h3>".$this->conf["view."]["organizer."]["organizerTemplate"];
		}
		$sims = array();
		$sims['name'] = $this->getName();
		$sims['description'] = nl2br($cObj->parseFunc($this->getDescription(),$cObj->conf["parseFunc."]));
		$sims['street'] = $this->getStreet();
		$sims['zip'] = $this->getZip();
		$sims['city'] = $this->getCity();
		$sims['phone'] = $this->getPhone();
		$sims['email'] = $cObj->parseFunc("<link ".$this->getEmail().">".$this->getEmail()."</link>",$cObj->conf["parseFunc."]);
		$sims['link'] = $cObj->parseFunc("<link ".$this->getLink().">".$this->getLink()."</link>",$cObj->conf["parseFunc."]);
		$sims['image_src'] = 'uploads/pics/' . $this->getImage();
		
		$sims['name_label'] = $this->shared->lang('l_organizer_name');
		$sims['description_label'] = $this->shared->lang('l_organizer_description');
		$sims['street_label'] = $this->shared->lang('l_organizer_street');
		$sims['zip_label'] = $this->shared->lang('l_organizer_zip');
		$sims['city_label'] = $this->shared->lang('l_organizer_city');
		$sims['phone_label'] = $this->shared->lang('l_organizer_phone');
		$sims['email_label'] = $this->shared->lang('l_organizer_email');
		$sims['link_label'] = $this->shared->lang('l_organizer_link');
		$sims['image_label'] = $this->shared->lang('l_organizer_image');
		
		$sims["backlink"] = $this->shared->pi_linkToPage($this->shared->lang('l_back'), array($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => $lastview));
		return $this->shared->replace_tags($sims, $page);
	 }
	 
	 function getUid(){
	 	return $this->uid;	
	 }
	 
	 function setUid($t){
	 	$this->uid = $t;
	 }
	 
	 function getName(){
	 	return $this->name;	
	 }
	 
	 function setName($t){
	 	$this->name = $t;
	 }
	 
	 function getDescription(){
	 	return $this->description;	
	 }
	 
	 function setDescription($d){
	 	$this->description = $d;
	 }
	 
	 function getStreet(){
	 	return $this->street;	
	 }
	 
	 function setStreet($t){
	 	$this->street = $t;
	 }
	 
	 function getZip(){
	 	return $this->zip;	
	 }
	 
	 function setZip($t){
	 	$this->zip = $t;
	 }
	 
	 function getCity(){
	 	return $this->city;	
	 }
	 
	 function setCity($t){
	 	$this->city = $t;
	 }
	 
	 function getPhone(){
	 	return $this->phone;	
	 }
	 
	 function setPhone($t){
	 	$this->phone = $t;
	 }
	 
	 function getImage(){
	 	return $this->image;	
	 }
	 
	 function setImage($t){
	 	$this->image = $t;
	 }
	 
	 function getLink(){
	 	return $this->link;	
	 }
	 
	 function setLink($t){
	 	$this->link = $t;
	 }
	 
	 function getEmail(){
	 	return $this->email;	
	 }
	 
	 function setEmail($t){
	 	$this->email = $t;
	 }
	
	function updateOrganizer($rightsObj, &$conf, $uid){

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
	
	function removeOrganizer(&$rightsObj, &$conf, $uid){
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
		if($GLOBALS['HTTP_POST_VARS']['hidden']=="true" && 
				($this->rightsObj->isAllowedToEditOrganizerHidden() || $this->rightsObj->isAllowedToCreateOrganizerHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditOrganizerName() || $this->rightsObj->isAllowedToCreateOrganizerName()){
			$insertFields['name'] = $GLOBALS['HTTP_POST_VARS']['name'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerDescription() || $this->rightsObj->isAllowedToCreateOrganizerDescription()){
			$insertFields['title'] = $GLOBALS['HTTP_POST_VARS']['description'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerStreet() || $this->rightsObj->isAllowedToCreateOrganizerStreet()){
			$insertFields['address'] = $GLOBALS['HTTP_POST_VARS']['street'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerZip() || $this->rightsObj->isAllowedToCreateOrganizerZip()){
			$insertFields['zip'] = $GLOBALS['HTTP_POST_VARS']['zip'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerCity() || $this->rightsObj->isAllowedToCreateOrganizerCity()){
			$insertFields['city'] = $GLOBALS['HTTP_POST_VARS']['city'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerPhone() || $this->rightsObj->isAllowedToCreateOrganizerPhone()){
			$insertFields['phone'] = $GLOBALS['HTTP_POST_VARS']['phone'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerEmail() || $this->rightsObj->isAllowedToCreateOrganizerEmail()){
			$insertFields['email'] = $GLOBALS['HTTP_POST_VARS']['email'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerImage() || $this->rightsObj->isAllowedToCreateOrganizerImage()){
			$insertFields['image'] = $GLOBALS['HTTP_POST_VARS']['image'];
		}
		
		if($this->rightsObj->isAllowedToEditOrganizerLink() || $this->rightsObj->isAllowedToCreateOrganizerLink()){
			$insertFields['www'] = $GLOBALS['HTTP_POST_VARS']['link'];
		}

	}
	
	function saveOrganizer($rightsObj, $conf=array(), $pid){

		$crdate = time();
		$insertFields = array("pid" => $pid, "tstamp" => $crdate, "crdate" => $crdate);
		//TODO: Check if all values are correct
		
		$hidden = 0;
		if($GLOBALS['HTTP_POST_VARS']['hidden']=="true")
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
		if($GLOBALS['HTTP_POST_VARS']['name']!=''){
			$insertFields['name'] = $GLOBALS['HTTP_POST_VARS']['name'];
		}
		if($GLOBALS['HTTP_POST_VARS']['description']!=''){
			$insertFields['title'] = $GLOBALS['HTTP_POST_VARS']['description'];
		}
		if($GLOBALS['HTTP_POST_VARS']['street']!=''){
			$insertFields['address'] = $GLOBALS['HTTP_POST_VARS']['street'];
		}
		if($GLOBALS['HTTP_POST_VARS']['zip']!=''){
			$insertFields['zip'] = $GLOBALS['HTTP_POST_VARS']['zip'];
		}
		if($GLOBALS['HTTP_POST_VARS']['city']!=''){
			$insertFields['city'] = $GLOBALS['HTTP_POST_VARS']['city'];
		}
		if($GLOBALS['HTTP_POST_VARS']['phone']!=''){
			$insertFields['phone'] = $GLOBALS['HTTP_POST_VARS']['phone'];
		}
		if($GLOBALS['HTTP_POST_VARS']['email']!=''){
			$insertFields['email'] = $GLOBALS['HTTP_POST_VARS']['email'];
		}
		if($GLOBALS['HTTP_POST_VARS']['image']!=''){
			$insertFields['image'] = $GLOBALS['HTTP_POST_VARS']['image'];
		}
		if($GLOBALS['HTTP_POST_VARS']['link']!=''){
			$insertFields['www'] = $GLOBALS['HTTP_POST_VARS']['link'];
		}
		
		// Creating DB records
		$insertFields['cruser_id'] = $rightsObj->getUserId();
		$table = "tt_address";
						
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_organizer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_organizer.php']);
}
?>