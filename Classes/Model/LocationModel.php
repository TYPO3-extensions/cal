<?php
namespace TYPO3\CMS\Cal\Model;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Base model for the calendar location.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class LocationModel extends \TYPO3\CMS\Cal\Model\BaseModel {
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
	var $eventLinks = array ();
	var $sharedUsers = Array ();
	var $sharedGroups = Array ();
	
	public function __construct($serviceKey) {
		parent::__construct ($serviceKey);
	}
	function getName() {
		return $this->name;
	}
	function setName($t) {
		$this->name = $t;
	}
	function getDescription() {
		return $this->description;
	}
	function setDescription($d) {
		$this->description = $d;
	}
	function getStreet() {
		return $this->street;
	}
	function setStreet($t) {
		$this->street = $t;
	}
	function getZip() {
		return $this->zip;
	}
	function setZip($t) {
		$this->zip = $t;
	}
	function getCity() {
		return $this->city;
	}
	function setCity($t) {
		$this->city = $t;
	}
	function getCountryZone() {
		return $this->countryzone;
	}
	function setCountryZone($t) {
		if ($t) {
			$this->countryzone = $t;
		}
	}
	function getCountry() {
		return $this->country;
	}
	function setCountry($t) {
		if ($t) {
			$this->country = $t;
		}
	}
	function getPhone() {
		return $this->phone;
	}
	function setPhone($t) {
		$this->phone = $t;
	}
	function getMobilephone() {
		return $this->mobilephone;
	}
	function setMobilephone($t) {
		$this->mobilephone = $t;
	}
	function getFax() {
		return $this->fax;
	}
	function setFax($t) {
		$this->fax = $t;
	}
	function getLink() {
		return $this->link;
	}
	function setLink($t) {
		$this->link = $t;
	}
	function getEmail() {
		return $this->email;
	}
	function setEmail($t) {
		$this->email = $t;
	}
	function getLongitude() {
		return $this->longitude;
	}
	function getLatitude() {
		return $this->latitude;
	}
	function setLongitude($l) {
		$this->longitude = $l;
	}
	function setLatitude($l) {
		$this->latitude = $l;
	}
	function createLocation($row) {
		$this->row = $row;
		$this->setUid ($row ['uid']);
		$this->setName ($row ['name']);
		$this->setDescription ($row ['description']);
		$this->setStreet ($row ['street']);
		$this->setZip ($row ['zip']);
		$this->setCity ($row ['city']);
		$this->setCountryZone ($row ['country_zone']);
		$this->setCountry ($row ['country']);
		$this->setPhone ($row ['phone']);
		$this->setEmail ($row ['email']);
		$this->setImage (GeneralUtility::trimExplode (',', $row ['image'], 1));
		$this->setLink ($row ['link']);
		$this->setLatitude ($row ['latitude']);
		$this->setLongitude ($row ['longitude']);
	}
	function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###MAP###'] = '';
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['showMap'] && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('wec_map')) {
			
			$apiKey = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['apiKey'];
			$width = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['mapWidth'];
			$height = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['mapHeight'];
			
			$centerLat = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['centerLat'];
			$centerLong = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['centerLong'];
			$zoomLevel = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['zoomLevel'];
			$initialMapType = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['initialMapType'];
			
			$showMapType = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['showMapType'];
			$showScale = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['showScale'];
			$showInfoOnLoad = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['showInfoOnLoad'];
			$showDirections = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['showDirections'];
			$showWrittenDirections = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['showWrittenDirections'];
			$prefillAddress = $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['map.'] ['prefillAddress'];
			
			$mapName = 'map' . $this->getUid ();
            /** @var \JBartels\WecMap\MapService\Google\Map $map */
            $map = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JBartels\WecMap\MapService\Google\Map::class, null, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);
			
			// evaluate config to see which map controls we need to show
			$map->addControl ('zoom');
			
			if ($showScale)
				$map->addControl ('scale');
			if ($showMapType)
				$map->addControl ('mapType');
			if ($initialMapType)
				$map->setType ($initialMapType);
				
				// check whether to show the directions tab and/or prefill addresses and/or written directions
			if ($showDirections && $showWrittenDirections && $prefillAddress)
				$map->enableDirections (true, 'directions-' . $mapName);
			if ($showDirections && $showWrittenDirections && ! $prefillAddress)
				$map->enableDirections (false, '-directions-' . $mapName);
			if ($showDirections && ! $showWrittenDirections && $prefillAddress)
				$map->enableDirections (true);
			if ($showDirections && ! $showWrittenDirections && ! $prefillAddress)
				$map->enableDirections ();
				
				// see if we need to open the marker bubble on load
			if ($showInfoOnLoad)
				$map->showInfoOnLoad ();
			
			$map->addMarkerByAddress ($this->getStreet (), $this->getCity (), $this->getCountryZone (), $this->getZip (), $this->getCountry (), '<h3>' . $this->getName () . '</h3>', '<p>' . $this->getDescription () . '</p>');
			
			/* Draw the map */
			$sims ['###MAP###'] = $map->drawMap ();
		}
	}
	function getCountryMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('static_info_tables')) {
			$staticInfo = \TYPO3\CMS\Cal\Utility\Functions::makeInstance('SJBR\\StaticInfoTables\\PiBaseApi');
			$staticInfo->init ();
			$current = \SJBR\StaticInfoTables\Utility\LocalizationUtility::translate(array('uid' => $this->getCountry () ), 'static_countries', FALSE);
			$this->local_cObj->setCurrentVal ($current);
			$sims ['###COUNTRY###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['countryStaticInfo'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['countryStaticInfo.']);
		} else {
			$current = $this->getCountry ();
			$this->local_cObj->setCurrentVal ($current);
			$sims ['###COUNTRY###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['country'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['country.']);
		}
	}
	function getCountryZoneMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject ();
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('static_info_tables')) {
			$staticInfo = \TYPO3\CMS\Cal\Utility\Functions::makeInstance('SJBR\\StaticInfoTables\\PiBaseApi');
			$staticInfo->init ();
			$current = \SJBR\StaticInfoTables\Utility\LocalizationUtility::translate(array('uid' => $this->getCountryzone () ), 'static_country_zones', FALSE);
			$this->local_cObj->setCurrentVal ($current);
			$sims ['###COUNTRYZONE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['countryzoneStaticInfo'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['countryzoneStaticInfo.']);
		} else {
			$current = $this->getCountryzone ();
			$this->local_cObj->setCurrentVal ($current);
			$sims ['###COUNTRYZONE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['countryzone'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['countryzone.']);
		}
	}
	function getLocationLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$wrapped ['###LOCATION_LINK###'] = explode ('$5&xs2', $this->getLinkToLocation ('$5&xs2'));
	}
	function getOrganizerLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$wrapped ['###ORGANIZER_LINK###'] = explode ('$5&xs2', $this->getLinkToOrganizer ('$5&xs2'));
	}
	function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###EDIT_LINK###'] = '';
		if ($this->isUserAllowedToEdit ()) {
			$linkConf = $this->getValuesAsArray ();
			if ($this->conf ['view.'] ['enableAjax']) {
				$temp = sprintf ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['editLinkOnClick'], $this->getUid (), $this->getType ());
				$linkConf ['link_ATagParams'] = ' onclick="' . $temp . '"';
			}
			$this->initLocalCObject ($linkConf);
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'edit_' . $this->getObjectType (),
					'type' => $this->getType (),
					'uid' => $this->getUid () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] [$this->getObjectType () . '.'] ['edit' . ucwords ($this->getObjectType ()) . 'ViewPid']);
			$this->local_cObj->setCurrentVal ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['editIcon']); // controller->pi_getLL('l_edit_'.$this->getObjectType())
			$sims ['###EDIT_LINK###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['editLink'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['editLink.']);
		}
		if ($this->isUserAllowedToDelete ()) {
			$linkConf = $this->getValuesAsArray ();
			if ($this->conf ['view.'] ['enableAjax']) {
				$temp = sprintf ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['deleteLinkOnClick'], $this->getUid (), $this->getType ());
				$linkConf ['link_ATagParams'] = ' onclick="' . $temp . '"';
			}
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'delete_' . $this->getObjectType (),
					'type' => $this->getType (),
					'uid' => $this->getUid () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] [$this->getObjectType () . '.'] ['delete' . ucwords ($this->getObjectType ()) . 'ViewPid']);
			$this->initLocalCObject ($linkConf);
			$this->local_cObj->setCurrentVal ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['deleteIcon']); // controller->pi_getLL('l_delete_'.$this->getObjectType())
			$sims ['###EDIT_LINK###'] .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['deleteLink'], $this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->getObjectType () . '.'] ['deleteLink.']);
		}
	}
	function addEventLink($key, $link) {
		$this->eventLinks [$key] = $link;
	}
	function getEventLinks() {
		return $this->eventLinks;
	}
	function getLinkToOrganizer($linktext) {
		return $this->getLinkToLocation ($linktext);
	}
	function getLinkToLocation($linktext) {
		if ($linktext == '') {
			$linktext = 'no title';
		}
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if ($rightsObj->isViewEnabled ($this->conf ['view.'] [$this->getObjectType () . 'LinkTarget']) || $this->conf ['view.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'ViewPid']) {
			return $this->controller->pi_linkTP_keepPIvars ($linktext, array (
					'view' => $this->getObjectType (),
					'uid' => $this->getUid (),
					'type' => $this->getType () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] [$this->getObjectType () . '.'] [$this->getObjectType () . 'ViewPid']);
		}
		return $linktext;
	}
	function updateWithPIVars(&$piVars) {
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelController');
		$cObj = &$this->controller->cObj;
		
		foreach ($piVars as $key => $value) {
			switch ($key) {
				case 'uid' :
					$this->setUid (intval ($piVars ['uid']));
					unset ($piVars ['uid']);
					break;
				case 'hidden' :
					$this->setHidden (intval ($piVars ['hidden']));
					unset ($piVars ['hidden']);
					break;
				case 'name' :
					$this->setName (strip_tags ($piVars ['name']));
					unset ($piVars ['name']);
					break;
				case 'description' :
					$this->setDescription ($cObj->removeBadHTML ($piVars ['description'], array ()));
					unset ($piVars ['description']);
					break;
				case 'street' :
					$this->setStreet (strip_tags ($piVars ['street']));
					unset ($piVars ['street']);
					break;
				case 'zip' :
					$this->setZip (strip_tags ($piVars ['zip']));
					unset ($piVars ['zip']);
					break;
				case 'city' :
					$this->setCity (strip_tags ($piVars ['city']));
					unset ($piVars ['city']);
					break;
				case 'phone' :
					$this->setPhone (strip_tags ($piVars ['phone']));
					unset ($piVars ['phone']);
					break;
				case 'fax' :
					$this->setFax (strip_tags ($piVars ['fax']));
					unset ($piVars ['fax']);
					break;
				case 'email' :
					$this->setEmail (strip_tags ($piVars ['email']));
					unset ($piVars ['email']);
					break;
				case 'image' :
					foreach ((array) $piVars ['image'] as $image) {
						$this->addImage (strip_tags ($image));
					}
					unset ($piVars ['image']);
					break;
				case 'country' :
					$this->setCountry (strip_tags ($piVars ['country']));
					unset ($piVars ['country']);
					break;
				case 'country_static_info' :
					$this->setCountry (strip_tags ($piVars ['country_static_info']));
					unset ($piVars ['country_static_info']);
					break;
				case 'countryzone' :
					$this->setCountryZone (strip_tags ($piVars ['countryzone']));
					unset ($piVars ['countryzone']);
					break;
				case 'countryzone_static_info' :
					$this->setCountryZone (strip_tags ($piVars ['countryzone_static_info']));
					unset ($piVars ['countryzone_static_info']);
					break;
				case 'link' :
					$this->setLink (strip_tags ($piVars ['link']));
					unset ($piVars ['link']);
					break;
				case 'longitude' :
					$this->setLongitude (strip_tags ($piVars ['longitude']));
					unset ($piVars ['longitude']);
					break;
				case 'latitude' :
					$this->setLatitude (strip_tags ($piVars ['latitude']));
					unset ($piVars ['latitude']);
					break;
				case 'shared' :
				case 'shared_ids' :
					$this->setSharedGroups (array ());
					$this->setSharedUsers (array ());
					$values = $piVars [$key];
					if (! is_array ($piVars [$key])) {
						$values = GeneralUtility::trimExplode (',', $piVars [$key], 1);
					}
					foreach ($values as $entry) {
						preg_match ('/(^[a-z])_([0-9]+)/', $entry, $idname);
						if ($idname [1] == 'u') {
							$this->addSharedUser ($idname [2]);
						} else if ($idname [1] == 'g') {
							$this->addSharedGroup ($idname [2]);
						}
					}
					break;
			}
		}
	}
	function __toString() {
		return get_class ($this) . ': ' . implode (', ', $this->row);
	}
	function addSharedUser($id) {
		$this->sharedUsers [] = $id;
	}
	function addSharedGroup($id) {
		$this->sharedGroups [] = $id;
	}
	function getSharedUsers() {
		return ($this->sharedUsers);
	}
	function getSharedGroups() {
		return ($this->sharedGroups);
	}
	function setSharedUsers($userIds) {
		$this->sharedUsers = $userIds;
	}
	function setSharedGroups($groupIds) {
		$this->sharedGroups = $groupIds;
	}
	function isSharedUser($userId, $groupIdArray) {
		if (is_array ($this->getSharedUsers ()) && in_array ($userId, $this->getSharedUsers ())) {
			return true;
		}
		foreach ($groupIdArray as $id) {
			if (is_array ($this->getSharedGroups ()) && in_array ($id, $this->getSharedGroups ())) {
				return true;
			}
		}
		
		return false;
	}
}

?>