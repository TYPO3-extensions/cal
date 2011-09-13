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

	/**
	 * Constructor
	 * @param	array		$row		The value array
	 * @param	string		$pidList	The pid-list to search in
	 */
	function tx_cal_location($row, $pidList){
		$this->setObjectType('location');
		$this->setType('tx_cal_location');
		$this->tx_cal_location_model($this->controller, $this->getType());
		$this->createLocation($row);
		$this->templatePath = $this->conf['view.']['location.']['locationModelTemplate'];
	}


	function renderLocation(){
		return $this->fillTemplate('###TEMPLATE_LOCATION_LOCATION###');
	}
	
	function renderOrganizer(){
		return $this->fillTemplate('###TEMPLATE_ORGANIZER_ORGANIZER###');
	}
	
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if(!$rightsObj->isViewEnabled('edit_location')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups();
		}
		
		$isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
		$isAllowedToEditLocations = $rightsObj->isAllowedToEditLocation();
		$isAllowedToEditOwnLocationsOnly = $rightsObj->isAllowedToEditOnlyOwnLocation();

		if ($isAllowedToEditOwnLocationsOnly) {
			return $isSharedUser;
		}
		return $isAllowedToEditLocations;
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if(!$rightsObj->isViewEnabled('delete_location')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups();
		}
		$isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
		$isAllowedToDeleteLocation = $rightsObj->isAllowedToDeleteLocation();
		$isAllowedToDeleteOwnLocationsOnly = $rightsObj->isAllowedToDeleteOnlyOwnLocation();
		
		if ($isAllowedToDeleteOwnLocationsOnly) {
			return $isSharedUser;
		}
		return $isAllowedToDeleteLocation;
	}

	function getEditLinkMarker(& $template, & $sims, & $rems, $view){
		$editlink = '';
		if ($this->isUserAllowedToEdit()) {
			$this->initLocalCObject($this->getValuesAsArray());
			
			$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_edit_location'));
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array (
				'view' => 'edit_location',
				'type' => $this->getType(), 'uid' => $this->getUid()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['editLocationViewPid']);
			$editlink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['location.']['editLink'],$this->conf['view.'][$view.'.']['location.']['editLink.']);
		}
		if ($this->isUserAllowedToDelete()) {
			$this->initLocalCObject($this->getValuesAsArray());

			$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_delete_location'));
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array (
				'view' => 'delete_location',
				'type' => $this->getType(), 'uid' => $this->getUid()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['location.']['deleteLocationViewPid']);
			$editlink .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['location.']['deleteLink'],$this->conf['view.'][$view.'.']['location.']['deleteLink.']);
		}
		return $editlink;
	}
	
	function renderLocationFor($viewType, $subpartSuffix=''){
		return $this->fillTemplate('###TEMPLATE_LOCATION_'.strtoupper($viewType).($subpartSuffix?'_':'').$subpartSuffix.'###');
	}
	
	function renderOrganizerFor($viewType, $subpartSuffix=''){
		return $this->fillTemplate('###TEMPLATE_ORGANIZER_'.strtoupper($viewType).($subpartSuffix?'_':'').$subpartSuffix.'###');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php']);
}
?>