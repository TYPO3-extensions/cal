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
	}


	function renderLocation(){
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$page = $cObj->fileResource($this->conf['view.']['location.']['locationTemplate']);
		if ($page=='') {
			return '<h3>calendar: no location template file found:</h3>'.$this->conf['view.']['location.']['locationTemplate'];
		}
			
		$rems = array();
		$sims = array();
		$wrapped = array();
		$this->getMarker($page, $sims, $rems, $wrapped);
		return $this->finish(tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped));
	}

	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if(!$rightsObj->isViewEnabled('edit_location')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		return $rightsObj->isAllowedToEditLocation();
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if(!$rightsObj->isViewEnabled('delete_location')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		return $rightsObj->isAllowedToDeleteLocations();
	}

	function getEditLink(& $template, & $sims, & $rems, $view){
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php']);
}
?>
