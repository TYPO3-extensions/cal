<?php
/**
 * *************************************************************
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
 * *************************************************************
 */

/**
 * A service which renders a form to create / edit an event location / organizer.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_location_organizer_view extends tx_cal_fe_editing_base_view {
	var $isLocation;
	function tx_cal_create_location_organizer_view() {
		$this->tx_cal_fe_editing_base_view ();
	}
	
	/**
	 * Draws a create location or organizer form.
	 * 
	 * @param
	 *        	boolean		True if a location should be confirmed
	 * @param
	 *        	string		Comma separated list of pids.
	 * @param
	 *        	object		A location or organizer object to be updated
	 * @return string HTML output.
	 */
	function drawCreateLocationOrOrganizer($isLocation = true, $pidList, $object = '') {
		$this->isLocation = $isLocation;
		if ($isLocation) {
			$this->objectString = 'location';
		} else {
			$this->objectString = 'organizer';
		}
		if (is_object ($object)) {
			$this->conf ['view'] = 'edit_' . $this->objectString;
		} else {
			$this->conf ['view'] = 'create_' . $this->objectString;
			unset ($this->controller->piVars ['uid']);
		}
		$requiredFieldSims = Array ();
		$allRequiredFieldsAreFilled = $this->checkRequiredFields ($requiredFieldsSims);
		
		if ($allRequiredFieldsAreFilled) {
			
			$this->conf ['lastview'] = $this->controller->extendLastView ();
			
			$this->conf ['view'] = 'confirm_' . $this->objectString;
			if ($isLocation) {
				return $this->controller->confirmLocation ();
			}
			return $this->controller->confirmOrganizer ();
		}
		
		// Needed for translation options:
		$this->serviceName = 'cal_' . $this->objectString . '_model';
		$this->table = 'tx_cal_' . $this->objectString;
		
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['create_location.'] ['template']);
		if ($page == '') {
			return '<h3>calendar: no create location template file found:</h3>' . $this->conf ['view.'] ['create_location.'] ['template'];
		}
		
		if (is_object ($object) && ! $object->isUserAllowedToEdit ()) {
			return $this->controller->pi_getLL ('l_not_allowed_edit') . $this->objectString;
		} else if (! is_object ($object) && ! $this->rightsObj->isAllowedTo ('create', $this->objectString, '')) {
			return $this->controller->pi_getLL ('l_not_allowed_create') . $this->objectString;
		}
		
		// If an object has been passed on the form is a edit form
		if (is_object ($object) && $object->isUserAllowedToEdit ()) {
			$this->isEditMode = true;
			$this->object = $object;
		} else {
			if ($isLocation) {
				$this->object = new tx_cal_location (null, '');
			} else {
				$this->object = new tx_cal_organizer (null, '');
			}
			$allValues = array_merge ($this->getDefaultValues (), $this->controller->piVars);
			$this->object->updateWithPIVars ($allValues);
		}
		
		$sims = array ();
		$rems = array ();
		$wrapped = array ();
		
		$sims ['###TYPE###'] = $this->object->getType ();
		$this->getTemplateSubpartMarker ($page, $sims, $rems, $wrapped, $this->conf ['view']);
		
		$page = tx_cal_functions::substituteMarkerArrayNotCached ($page, array (), $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached ($page, $sims, array (), array ());
		
		$sims = array ();
		$rems = array ();
		$wrapped = array ();
		
		$sims ['###L_CREATE_LOCATION###'] = $this->controller->pi_getLL ('l_' . $this->conf ['view']);
		$this->getTemplateSingleMarker ($page, $sims, $rems, $this->conf ['view']);
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url (array (
				'view' => $this->conf ['view'],
				'formCheck' => '1' 
		)));
		$page = tx_cal_functions::substituteMarkerArrayNotCached ($page, array (), $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached ($page, $sims, array (), array ());
		return tx_cal_functions::substituteMarkerArrayNotCached ($page, $requiredFieldsSims, array (), array ());
	}
	function getCountryMarker(& $template, & $sims, & $rems, $view) {
		// Initialise static info library
		$sims ['###COUNTRY###'] = '';
		if ($this->isAllowed ('country')) {
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('static_info_tables')) {
				require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');
				$this->staticInfo = new tx_staticinfotables_pi1();
				$this->staticInfo->init ();
				$sims ['###COUNTRY###'] = $this->applyStdWrap ($this->staticInfo->buildStaticInfoSelector ('COUNTRIES', 'tx_cal_controller[country]', '', $this->object->getCountry ()), 'country_static_info_stdWrap');
			} else {
				$sims ['###COUNTRY###'] = $this->applyStdWrap ($this->object->getCountry (), 'country_stdWrap');
				$sims ['###COUNTRY_VALUE###'] = $this->object->getCountry ();
			}
		}
	}
	function getCountryzoneMarker(& $template, & $sims, & $rems, $view) {
		// Initialise static info library
		$sims ['###COUNTRYZONE###'] = '';
		if ($this->isAllowed ('countryzone')) {
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('static_info_tables')) {
				require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('static_info_tables') . 'pi1/class.tx_staticinfotables_pi1.php');
				$this->staticInfo = new tx_staticinfotables_pi1();
				$this->staticInfo->init ();
				$sims ['###COUNTRYZONE###'] = $this->applyStdWrap ($this->staticInfo->buildStaticInfoSelector ('SUBDIVISIONS', 'tx_cal_controller[countryzone]', '', $this->object->getCountryZone (), $this->object->getCountry ()), 'countryzone_static_info_stdWrap');
			} else {
				$sims ['###COUNTRYZONE###'] = $this->applyStdWrap ($this->object->getCountryZone (), 'countryzone_stdWrap');
				$sims ['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone ();
			}
		}
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_create_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_create_location_organizer_view.php']);
}
?>