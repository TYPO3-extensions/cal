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
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_location_model.php');

/**
 * Base model for the calendar location.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location extends tx_cal_location_model {
 	 
 	 var $type = "tx_cal_location";
 	 
 	 /**
 	  * Constructor
 	  * @param	object		$this->cObj		The content object
	  * @param	integer		$uid		The uid to search for
	  * @param	string		$pidList	The pid-list to search in
 	  */
 	 function tx_cal_location(&$cObj, &$rightsObj, $row, $pidList){
	 	
		$this->tx_cal_location_model($cObj, $rightsObj, $this->type);
		$this->createLocation($row);
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
	 
	 function renderLocation(){	
		$lastview = $this->cObj->conf['lastview'];
		$uid  = $this->cObj->conf['uid'];
		$type = $this->cObj->conf['type'];
		$getdate  = $this->cObj->conf['getdate'];
		$page = $this->cObj->fileResource($this->cObj->conf["view."]["location."]["locationTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no location template file found:</h3>".$this->cObj->conf["view."]["location."]["locationTemplate"];
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
			$sims['description'] = nl2br($this->cObj->parseFunc($this->getDescription(),$this->conf["parseFunc."]));
			$sims['description_label'] = $this->shared->lang('l_location_description');	
		}else{
			$rems['###DESCRIPTION_DATA_LOOP###'] = "";
		}
		if($this->getStreet()!=""){
			$loop = $this->cObj->getSubpart($locationloop, "###STREET_DATA_LOOP###");
			$sims['street'] = $this->data['street'];
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
			$sims['image_src'] = 'uploads/pics/tx_cal/' .$this->getImage();
			$sims['image_label'] = $this->shared->lang('l_location_image');	
		}else{
			$rems['###IMAGE_DATA_LOOP###'] = "";
		}

		$locationloop = $this->cObj->substituteMarkerArrayCached($locationloop,  array (), $rems, array ());
		
		$sims["heading"] = $this->shared->lang('l_event_location');
		$sims["backlink"] = $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_back'), array("view" => $lastview), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
		$sims["editlink"] = "";//tx_partner_div::getEditPartnerLink($this->getUid());
//debug($sims["editlink"]);		
		$prelocation = $this->cObj->getSubpart($page, "###PRE_LOCATION###");
		$page = $postlocation.$locationloop.$prelocation;
		return $this->shared->replace_tags($sims, $page);
	 }

	
	/**
	 *  
	 */
	function getCalLegendDescription() { 
		return array(); 
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php']);
}
?>