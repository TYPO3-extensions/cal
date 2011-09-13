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
	var $link;
	var $longitude;
	var $latitude;
	var $eventLinks = array();

	function tx_cal_location_model($serviceKey){
		$this->tx_cal_base_model($serviceKey);
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

	function getCountryZone() {
		return $this->countryzone;
	}

	function setCountryZone($t) {
		if($t) {
			$this->countryzone = $t;
		}
	}

	function getCountry() {
		return $this->country;
	}

	function setCountry($t) {
		if($t) {
			$this->country = $t;
		}
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

	function createLocation($row){
		$this->row = $row;
		$this->setUid($row['uid']);
		$this->setName($row['name']);
		$this->setDescription($row['description']);
		$this->setStreet($row['street']);
		$this->setZip($row['zip']);
		$this->setCity($row['city']);
		$this->setCountryZone($row['country_zone']);
		$this->setCountry($row['country']);
		$this->setPhone($row['phone']);
		$this->setEmail($row['email']);
		$this->setImage(t3lib_div::trimExplode(',',$row['image'],1));
		$this->setImageTitleText(t3lib_div::trimExplode(chr(10),$row['imagetitletext']));
		$this->setImageAltText(t3lib_div::trimExplode(chr(10),$row['imagealttext']));
		$this->setImageCaption(t3lib_div::trimExplode(chr(10),$row['imagecaption']));
		$this->setLink($row['link']);
	}

	function getMapMarker(& $template, & $sims, & $rems, $view){
		$sims['###MAP###'] = '';
		if($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['showMap'] && t3lib_extMgm::isLoaded('wec_map')){

			$apiKey = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['apiKey'];
			$width = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['mapWidth'];
			$height = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['mapHeight'];

			$centerLat = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['centerLat'];
			$centerLong = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['centerLong'];
			$zoomLevel = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['zoomLevel'];

			$controlSize = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['controlSize'];
			$showOverviewMap = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['showOverviewMap'];
			$showMapType = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['showMapType'];
			$showScale = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['showScale'];
			$showInfoOnLoad = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['showInfoOnLoad'];
			$showDirections = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['showDirections'];
			$showWrittenDirections = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['showWrittenDirections'];
			$prefillAddress = $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['map.']['prefillAddress'];
			
			include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
			$className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
			$mapName = 'map'.$this->getUid();
			$map = new $className($apiKey, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);

			// evaluate config to see which map controls we need to show
			if($controlSize == 'large') {
				$map->addControl('largeMap');	
			} else if ($controlSize == 'small') {
				$map->addControl('smallMap');	
			} else if ($controlSize == 'zoomonly') {
				$map->addControl('smallZoom');	
			}

			if($showScale) $map->addControl('scale');
			if($showOverviewMap) $map->addControl('overviewMap');
			if($showMapType) $map->addControl('mapType');

			// check whether to show the directions tab and/or prefill addresses and/or written directions
			if($showDirections && $showWrittenDirections && $prefillAddress) $map->enableDirections(true, 'directions-'.$mapName);
			if($showDirections && $showWrittenDirections && !$prefillAddress) $map->enableDirections(false, '-directions-'.$mapName);
			if($showDirections && !$showWrittenDirections && $prefillAddress) $map->enableDirections(true);
			if($showDirections && !$showWrittenDirections && !$prefillAddress) $map->enableDirections();

			// see if we need to open the marker bubble on load
			if($showInfoOnLoad) $map->showInfoOnLoad();
			
			$map->addMarkerByAddress($this->getStreet(), $this->getCity(), $this->getCountryZone(), $this->getZip(), $this->getCountry(), '<h3>'.$this->getName().'</h3>', '<p>'.$this->getDescription().'</p>');

			/* Draw the map */
			$sims['###MAP###'] = $map->drawMap();
		}
	}

	function getCountryMarker(& $template, & $sims, & $rems, $view){
		$this->initLocalCObject();
		if(t3lib_extMgm::isLoaded('static_info_tables')) {
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$staticInfo->init();
			$current = $staticInfo->getStaticInfoName('COUNTRIES', $this->getCountry());
			$this->local_cObj->setCurrentVal($current);
			$sims['###COUNTRY###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['countryStaticInfo'], $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['countryStaticInfo.']);
		} else {
			$current = $this->getCountry();
			$this->local_cObj->setCurrentVal($current);
			$sims['###COUNTRY###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['country'], $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['country.']);
		}
	}

	function getCountryZoneMarker(& $template, & $sims, & $rems, $view){
		$this->initLocalCObject();
		if(t3lib_extMgm::isLoaded('static_info_tables')) {
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$staticInfo->init();
			$current = $staticInfo->getStaticInfoName('SUBDIVISIONS', $this->getCountryzone(), $this->getCountry());
			$this->local_cObj->setCurrentVal($current);
			$sims['###COUNTRYZONE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['countryzoneStaticInfo'], $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['countryzoneStaticInfo.']);
		} else {
			$current = $this->getCountryzone();
			$this->local_cObj->setCurrentVal($current);
			$sims['###COUNTRYZONE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['countryzone'], $this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['countryzone.']);
		}
	}
	
	function getLocationLinkMarker(& $template, & $sims, & $rems, $view){
		$wrapped['###LOCATION_LINK###'] = explode('|',$this->getLinkToLocation('|'));
	}
	
	function getOrganizerLinkMarker(& $template, & $sims, & $rems, $view){
		$wrapped['###ORGANIZER_LINK###'] = explode('|',$this->getLinkToOrganizer('|'));
	}
	
	function getEditLinkMarker(& $template, & $sims, & $rems, $view){
		$controller = &tx_cal_registry::Registry('basic','controller');
		$sims['###EDIT_LINK###'] = '';
		if ($this->isUserAllowedToEdit()) {
			#$GLOBALS['TSFE']->ATagParams = 'title="' . $controller->pi_getLL('l_edit_'.$this->getObjectType()) . '" alt="' . $controller->pi_getLL('l_edit_'.$this->getObjectType()) . '"';
			$linkConf = $this->getValuesAsArray();
			if($this->conf['view.']['enableAjax']){
				$temp = sprintf($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['editLinkOnClick'],$this->getUid(),$this->getType());
				$linkConf['ATagParams'] = ' onclick="'.$temp.'"';
			}
			$linkConf['link'] = $controller->pi_linkTP_keepPIvars_url(array (
			'view' => 'edit_'.$this->getObjectType(),
			'type' => $this->getType(), 'uid' => $this->getUid(), 'lastview' => $controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$this->getObjectType().'.']['edit'.ucwords($this->getObjectType()).'ViewPid']);
			$this->initLocalCObject($linkConf);
			$this->local_cObj->setCurrentVal($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['editIcon']);#$controller->pi_getLL('l_edit_'.$this->getObjectType())
			$sims['###EDIT_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['editLink'],$this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['editLink.']);
		}
		if ($this->isUserAllowedToDelete()) {
			#$GLOBALS['TSFE']->ATagParams = 'title="' . $controller->pi_getLL('l_delete_'.$this->getObjectType()) . '" alt="' . $controller->pi_getLL('l_delete_'.$this->getObjectType()) . '"';
			$linkConf = $this->getValuesAsArray();
			if($this->conf['view.']['enableAjax']){
				$temp = sprintf($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['deleteLinkOnClick'],$this->getUid(),$this->getType());
				$linkConf['ATagParams'] = ' onclick="'.$temp.'"';
			}
			$linkConf['link'] = $controller->pi_linkTP_keepPIvars_url(array (
			'view' => 'delete_'.$this->getObjectType(),
			'type' => $this->getType(), 'uid' => $this->getUid(), 'lastview' => $controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$this->getObjectType().'.']['delete'.ucwords($this->getObjectType()).'ViewPid']);
			$this->initLocalCObject($linkConf);
			$this->local_cObj->setCurrentVal($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['deleteIcon']);#$controller->pi_getLL('l_delete_'.$this->getObjectType())
			$sims['###EDIT_LINK###'] .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['deleteLink'],$this->conf['view.'][$this->conf['view'].'.'][$this->getObjectType().'.']['deleteLink.']);
		}
	}

	function addEventLink($link){
		$this->eventLinks[] = $link;
	}

	function getEventLinks(){
		return $this->eventLinks;
	}

	function getEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###EVENT_LINK###'] = '';
		if($this->conf['view.'][$view.'.'][$this->getObjectType().'.']['includeEventsInResult']){
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal(implode('',$this->eventLinks));
			$sims['###EVENT_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->getObjectType().'.']['eventLink'],$this->conf['view.'][$view.'.'][$this->getObjectType().'.']['eventLink.']);
		}
	}
	
	function getLinkToOrganizer($linktext) {
		return $this->getLinkToLocation($linktext);
	}

	function getLinkToLocation($linktext) {
		if($linktext==''){
			$linktext = 'no title';
		}
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$controller = &tx_cal_registry::Registry('basic','controller');
		if($rightsObj->isViewEnabled($this->conf['view.'][$this->getObjectType().'LinkTarget']) || $this->conf['view.'][$this->getObjectType().'.'][$this->getObjectType().'ViewPid']){
			$controller = &tx_cal_registry::Registry('basic','controller');
			return $controller->pi_linkTP_keepPIvars($linktext, array (
							'view' => $this->getObjectType(),
							'lastview' => $controller->extendLastView(), 
							'uid' => $this->getUid(), 
							'type' => $this->getType()), 
				$this->conf['cache'], 
				$this->conf['clear_anyway'], 
				$this->conf['view.'][$this->getObjectType().'.'][$this->getObjectType().'ViewPid']
			);
		}
		return $linktext;
	}
	
	function updateWithPIVars(&$piVars) {
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$modelObj = &tx_cal_registry::Registry('basic','modelController');
		$controller = &tx_cal_registry::Registry('basic','controller');

		foreach($piVars as $key => $value) {
			switch($key) {
				case 'uid':
					$this->setUid(intval($piVars['uid']));
					unset($piVars['uid']);
					break;
				case 'hidden':
					$this->setHidden(intval($piVars['hidden']));
					unset($piVars['hidden']);
					break;
				case 'name':
					$this->setName(strip_tags($piVars['name']));
					unset($piVars['name']);
					break;
				case 'description':
					$this->setDescription($cObj->removeBadHTML($piVars['description'], array()));
					unset($piVars['description']);
					break;
				case 'street':
					$this->setStreet(strip_tags($piVars['street']));
					unset($piVars['street']);
					break;
				case 'zip':
					$this->setZip(strip_tags($piVars['zip']));
					unset($piVars['zip']);
					break;
				case 'city':
					$this->setCity(strip_tags($piVars['city']));
					unset($piVars['city']);
					break;
				case 'phone':
					$this->setPhone(strip_tags($piVars['phone']));
					unset($piVars['phone']);
					break;
				case 'email':
					$this->setEmail(strip_tags($piVars['email']));
					unset($piVars['email']);
					break;
				case 'image':
					foreach((Array)$piVars['image'] as $image){
						$this->addImage(strip_tags($image));
					}
					unset($piVars['image']);
					break;
				case 'image_caption':
					$this->setImageCaption(explode(chr(10),strip_tags($piVars['image_caption'])));
					unset($piVars['image_caption']);
					break;
				case 'image_alt':
					$this->setImageAltText(explode(chr(10),strip_tags($piVars['image_alt'])));
					unset($piVars['image_alt']);
					break;
				case 'image_title':
					$this->setImageTitleText(explode(chr(10),strip_tags($piVars['image_title'])));
					unset($piVars['image_title']);
					break;
				case 'country':
					$this->setCountry(strip_tags($piVars['country']));
					unset($piVars['country']);
					break;
				case 'country_static_info':
					$this->setCountry(strip_tags($piVars['country_static_info']));
					unset($piVars['country_static_info']);
					break;
				case 'countryzone':
					$this->setCountryZone(strip_tags($piVars['countryzone']));
					unset($piVars['countryzone']);
					break;
				case 'countryzone_static_info':
					$this->setCountryZone(strip_tags($piVars['countryzone_static_info']));
					unset($piVars['countryzone_static_info']);
					break;
				case 'link':
					$this->setLink(strip_tags($piVars['link']));
					unset($piVars['link']);
					break;
			}
		}
	}
	
	function __toString(){
		return get_class($this).': '.implode(', ',$this->row);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location_model.php']);
}
?>