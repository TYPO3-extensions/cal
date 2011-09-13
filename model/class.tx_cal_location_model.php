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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

/**
 * Base model for the calendar location.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location_model extends tx_cal_base_model{
 	 
 	 var $row;
 	 var $uid;
 	 var $name;
 	 var $description;
 	 var $street;
 	 var $zip;
 	 var $city;
     var $countryzone;
	 var $country;
 	 var $phone;
 	 var $fax;
 	 var $mobilephone;
 	 var $email;
 	 var $image;
 	 var $link;
 	 var $longitude;
 	 var $latitude;
 	 
 	 function tx_cal_location_model(&$controller, $serviceKey){
 	 	$this->tx_cal_base_model($controller, $serviceKey);
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
	 
	 function getPostalCode(){
	 	return $this->zip;	
	 }
	 
	 function setPostalCode($t){
	 	$this->zip = $t;
	 }
	 
	 function getCity(){
	 	return $this->city;	
	 }
	 
	 function setCity($t){
	 	$this->city = $t;
	 }
	
	 function getCountryZone() {
	 	return $this->countryzone;
	 }
	
	 function setCountryZone($t) {
		$this->countryzone = $t;
	 }
	 
	 function getCountry() {
	 	return $this->countryzone;
	 }
	
	 function setCountry($t) {
		$this->country = $t;
	 }
	
	 function getPhone(){
	 	return $this->phone;	
	 }
	 
	 function setPhone($t){
	 	$this->phone = $t;
	 }
	 
	 function getMobilephone(){
	 	return $this->mobilephone;	
	 }
	 
	 function setMobilephone($t){
	 	$this->mobilephone = $t;
	 }
	 
	 function getFax(){
	 	return $this->fax;	
	 }
	 
	 function setFax($t){
	 	$this->fax = $t;
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

	 function getLongitude(){
	 	return $this->longlitude;
	 }
	 
	 function setLongitude($l){
	 	$this->longlitude = $l;
	 }
	 
	 function getPhoneMarker(&$page, &$rems, &$sims){
	 	$phone = $this->getPhone();
		if($phone!=''){
			$loop = $this->cObj->getSubpart($page, '###PHONE_DATA_LOOP###');
			$phonerems['###PHONE_LOOP###'] = $phone;
			$phonesims['###PHONE_LABEL###'] = $this->controller->pi_getLL('l_location_phone');
			$rems['###PHONE_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $phonesims, $phonerems, array ());
		}else{
			$rems['###PHONE_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getFaxMarker(&$page, &$rems, &$sims){
	 	$fax = $this->getFax();
		if($fax!=''){
			$loop = $this->cObj->getSubpart($page, '###FAX_DATA_LOOP###');
			$phonerems['###FAX_LOOP###'] = $fax;
			$faxrems['###FAX_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, array (), $phonerems, array ());
			$faxsims['###FAX_LABEL###'] = $this->controller->pi_getLL('l_location_fax');	
			$rems['###FAX_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $faxsims, $faxrems, array ());
		}else{
			$rems['###FAX_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getMobilePhoneMarker(&$page, &$rems, &$sims){
	 	$mobilephone = $this->getMobilephone();
		if($mobilephone!=''){
			$loop = $this->cObj->getSubpart($page, '###MOBILEPHONE_DATA_LOOP###');
			$phonerems['###MOBILEPHONE_LOOP###'] = $mobilephone;
			$phonerems['###MOBILEPHONE###'] = $mobilephone;
			$phonesims['###MOBILEPHONE_LABEL###'] = $this->controller->pi_getLL('l_location_mobilephone');
			$rems['###MOBILEPHONE_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $phonesims, $phonerems, array ());
				
		}else{
			$rems['###MOBILEPHONE_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getEmailMarker(&$page, &$rems, &$sims){
	 	$email = $this->getEmail();
		if($email!=''){
			$loop = $this->cObj->getSubpart($page, '###EMAIL_DATA_LOOP###');
			$emailrems['###EMAIL_LOOP###'] = $this->cObj->stdWrap($this->getEmail(),$this->conf['view.']['location.']['emailStdWrap.']);
			$emailsims['###EMAIL###'] = $emailrems['###EMAIL_LOOP###'];
			$emailsims['###EMAIL_LABEL###'] = $this->controller->pi_getLL('l_location_email');
			$rems['###EMAIL_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $emailsims, $emailrems, array ());
				
		}else{
			$rems['###EMAIL_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getLinkMarker(&$page, &$rems, &$sims){
	 	$link = $this->getLink();
		if($link!=''){
			$loop = $this->cObj->getSubpart($page, '###LINK_DATA_LOOP###');
			$linkrems['###LINK_LOOP###'] = $this->cObj->stdWrap($this->getLink(),$this->conf['view.']['location.']['linkStdWrap.']);
			$linksims['###LINK###'] = $linkrems['###LINK_LOOP###'];
			$linksims['###LINK_LABEL###'] = $this->controller->pi_getLL('l_location_link');	
			$rems['###LINK_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $linksims, $linkrems, array ());
		}else{
			$rems['###LINK_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getNameMarker(&$page, &$rems, &$sims){
	 	if($this->getName()!=''){
			$loop = $this->cObj->getSubpart($page, '###NAME_DATA_LOOP###');
			$mySims = array();
			$mySims['###NAME###'] = $this->getName();
			$mySims['###NAME_LABEL###'] = $this->controller->pi_getLL('l_location_name');
			$rems['###NAME_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $mySims, array(), array ());
		}else{
			$rems['###NAME_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getDescriptionMarker(&$page, &$rems, &$sims){
	 	if($this->getDescription()!=''){
			$loop = $this->cObj->getSubpart($page, '###DESCRIPTION_DATA_LOOP###');
			$descsims['###DESCRIPTION###'] = $this->cObj->stdWrap($this->getDescription(),$this->conf['view.'][$this->conf['view'].'.']['description.']['stdWrap.']);
			$descsims['###DESCRIPTION_LABEL###'] = $this->controller->pi_getLL('l_location_description');
			$rems['###DESCRIPTION_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $descsims, array(), array ());
		}else{
			$rems['###DESCRIPTION_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getStreetMarker(&$page, &$rems, &$sims){
	 	if($this->getStreet()!=''){
			$loop = $this->cObj->getSubpart($page, '###STREET_DATA_LOOP###');
			$streetsims['###STREET###'] = $this->getStreet();
			$streetsims['###STREET_LABEL###'] = $this->controller->pi_getLL('l_location_street');
			$rems['###STREET_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $streetsims, array(), array ());
		}else{
			$rems['###STREET_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getPostalCodeMarker(&$page, &$rems, &$sims){
	 	if($this->getPostalCode()!=''){
			$loop = $this->cObj->getSubpart($page, '###ZIP_DATA_LOOP###');
			$zipsims['###ZIP###'] = $this->getPostalCode();
			$zipsims['###ZIP_LABEL###'] = $this->controller->pi_getLL('l_location_zip');
			$rems['###ZIP_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $zipsims, array(), array ());
		}else{
			$rems['###ZIP_DATA_LOOP###'] = '';
		}
	 }
	 
	 function getCityMarker(&$page, &$rems, &$sims){
	 	if($this->getCity()!=''){
			$loop = $this->cObj->getSubpart($page, '###CITY_DATA_LOOP###');
			$citysims['###CITY###'] = $this->getCity();
			$citysims['###CITY_LABEL###'] = $this->controller->pi_getLL('l_location_city');
			$rems['###CITY_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $citysims, array(), array ());	
		}else{
			$rems['###CITY_DATA_LOOP###'] = '';
		}
	 }
	
	 function getCountryZoneMarker(&$page, &$rems, &$sims){
	 	if($this->getCountryZone()!=''){
			$loop = $this->cObj->getSubpart($page, '###COUNTRYZONE_DATA_LOOP###');
			$countryzonesims['###COUNTRYZONE###'] = $this->getCountryZone();
			$countryzonesims['###COUNTRYZONE_LABEL###'] = $this->controller->pi_getLL('l_location_countryzone');
			$rems['###COUNTRYZONE_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $countryzonesims, array(), array ());	
		}else{
			$rems['###COUNTRYZONE_DATA_LOOP###'] = '';
		}
	 }
	
	 function getCountryMarker(&$page, &$rems, &$sims){
	 	if($this->getCountryZone()!=''){
			$loop = $this->cObj->getSubpart($page, '###COUNTRY_DATA_LOOP###');
			$countryzonesims['###COUNTRY###'] = $this->getCountryZone();
			$countryzonesims['###COUNTRY_LABEL###'] = $this->controller->pi_getLL('l_location_country');
			$rems['###COUNTRY_DATA_LOOP###'] = $this->cObj->substituteMarkerArrayCached($loop, $countryzonesims, array(), array ());	
		}else{
			$rems['###COUNTRY_DATA_LOOP###'] = '';
		}
	 }
	 
	
	 function getMapMarker(&$page, &$rems, &$sims){
	 	$sims['###MAP###'] = '';

		if($this->conf['view.']['location.']['showMap'] && t3lib_extMgm::isLoaded('wec_map')){
			   
			$apiKey = $this->conf['view.']['location.']['map.']['apiKey'];
			$width = $this->conf['view.']['location.']['map.']['mapWidth'];
			$height = $this->conf['view.']['location.']['map.']['mapHeight'];

			include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
 	        $className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');	        
			$map = new $className($apiKey, $width, $height);
 	        $map->addMarkerByAddress($this->getStreet(), $this->getCity(), $this->getCountryZone(), $this->getPostalCode(), $this->getCountry(), '<h3>'.$this->getName().'</h3>', '<p>'.$this->getDescription().'</p>');
			
 	        /* Draw the map */
 	        $sims['###MAP###'] = $map->drawMap();
		}
	 }
	 
	 function getLocationMarker(&$page, &$sims, &$rems){

	 	preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $page, $match);

		$allMarkers = array_unique($match[1]);

		foreach ($allMarkers as $marker) {
			switch ($marker) {
				case 'PHONE_DATA_LOOP' :
					$this->getPhoneMarker($page, $rems, $sims);
					break;
				case 'FAX_DATA_LOOP':
					$this->getFaxMarker($page, $rems, $sims);
					break;
				case 'MOBILEPHONE_DATA_LOOP':
					$this->getMobilePhoneMarker($page, $rems, $sims);
					break;
				case 'EMAIL_DATA_LOOP':
					$this->getEmailMarker($page, $rems, $sims);
					break;
				case 'LINK_DATA_LOOP':
					$this->getLinkMarker($page, $rems, $sims);
					break;
				case 'NAME_DATA_LOOP':
					$this->getNameMarker($page, $rems, $sims);
					break;
				case 'DESCRIPTION_DATA_LOOP':
					$this->getDescriptionMarker($page, $rems, $sims);
					break;
				case 'STREET_DATA_LOOP':
					$this->getStreetMarker($page, $rems, $sims);
					break;
				case 'ZIP_DATA_LOOP':
					$this->getPostalCodeMarker($page, $rems, $sims);
					break;
				case 'CITY_DATA_LOOP':
					$this->getCityMarker($page, $rems, $sims);
					break;
				case 'COUNTRYZONE_DATA_LOOP':
					$this->getCountryZoneMarker($page, $rems, $sims);
					break;
				case 'COUNTRY_DATA_LOOP':
					$this->getCountryMarker($page, $rems, $sims);
					break;
				case 'IMAGE_DATA_LOOP' :
					$res = $this->getImageMarkers($sims, $this->conf['view.'][$this->conf['view'].'.']['image_stdWrap.'], true);
					$rems['###IMAGE_DATA_LOOP###'] = $res['###IMAGE###'];
					break;
				case 'ABS_IMAGE_DATA_LOOP' :
					$res = $this->getImageMarkers($sims, $this->conf['view.'][$this->conf['view'].'.']['image_stdWrap.'], true, true);
					$rems['###ABS_IMAGE_DATA_LOOP###'] = $res['###ABS_IMAGE###'];
					break;
				case 'POST_LOCATION':
					$rems['###POST_LOCATION###'] = $this->cObj->getSubpart($page, '###POST_LOCATION###');
					break;
				case 'LOCATION':	
					$rems['###LOCATION###'] = $this->cObj->getSubpart($page, '###LOCATION###');
					break;
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
                    if(method_exists($this,$funcFromMarker)) {
                        $this->$funcFromMarker($page, $rems, $sims);
                    }
					break;
			}
		}
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $page, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);

		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
		 		case 'MAP':
					$this->getMapMarker($page, $rems, $sims);
					break;
				case 'HEADING':
					$sims['###HEADING###'] = $this->controller->pi_getLL('l_event_location');
					break;
				case 'BACKLINK':
					$this->controller->addBacklink($sims);
					break;
				case 'EDITLINK':
					$sims['###EDITLINK###'] = '';//tx_partner_div::getEditPartnerLink($this->getUid());
					break;
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_location_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(!$sims['###'.$marker.'###']){
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.']['location.'][strtolower($marker).'_stdWrap']);
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
				    if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($page, $rems, $sims);
				    }
					break;
			}
		}
	 }
	 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_model.php']);
}
?>