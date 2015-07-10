<?php
namespace TYPO3\CMS\Cal\View;
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
class CreateLocationOrganizerView extends \TYPO3\CMS\Cal\View\FeEditingBaseView {
	
	var $isLocation;
	
	public function __construct() {
		parent::__construct();
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
	public function drawCreateLocationOrOrganizer($isLocation = true, $pidList, $object = '') {
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
				$this->object = new \TYPO3\CMS\Cal\Model\Location(null, '');
			} else {
				$this->object = new \TYPO3\CMS\Cal\Model\Organizer(null, '');
			}
			$allValues = array_merge ($this->getDefaultValues (), $this->controller->piVars);
			$this->object->updateWithPIVars ($allValues);
		}
		
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		
		$sims ['###TYPE###'] = $this->object->getType ();
		$this->getTemplateSubpartMarker ($page, $sims, $rems, $wrapped, $this->conf ['view']);
		
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		
		$sims ['###L_CREATE_LOCATION###'] = $this->controller->pi_getLL ('l_' . $this->conf ['view']);
		$this->getTemplateSingleMarker ($page, $sims, $rems, $this->conf ['view']);
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url (array (
				'view' => $this->conf ['view'],
				'formCheck' => '1' 
		)));
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $requiredFieldsSims, Array (), Array ());
	}
	
	public function getCountryMarker(& $template, & $sims, & $rems, $view) {
		// Initialise static info library
		$sims ['###COUNTRY###'] = '';
		if ($this->isAllowed ('country')) {
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('static_info_tables')) {
				$staticInfo = \TYPO3\CMS\Cal\Utility\Functions::makeInstance('tx_staticinfotables_pi1');
				$staticInfo->init ();
				$sims ['###COUNTRY###'] = $this->applyStdWrap ($staticInfo->buildStaticInfoSelector ('COUNTRIES', 'tx_cal_controller[country]', '', $this->object->getCountry ()), 'country_static_info_stdWrap');
			} else {
				$sims ['###COUNTRY###'] = $this->applyStdWrap ($this->object->getCountry (), 'country_stdWrap');
				$sims ['###COUNTRY_VALUE###'] = $this->object->getCountry ();
			}
		}
	}
	
	public function getCountryzoneMarker(& $template, & $sims, & $rems, $view) {
		// Initialise static info library
		$sims ['###COUNTRYZONE###'] = '';
		if ($this->isAllowed ('countryzone')) {
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('static_info_tables')) {
				$staticInfo = \TYPO3\CMS\Cal\Utility\Functions::makeInstance('tx_staticinfotables_pi1');
				$staticInfo->init ();
				$sims ['###COUNTRYZONE###'] = $this->applyStdWrap ($staticInfo->buildStaticInfoSelector ('SUBDIVISIONS', 'tx_cal_controller[countryzone]', '', $this->object->getCountryZone (), $this->object->getCountry ()), 'countryzone_static_info_stdWrap');
			} else {
				$sims ['###COUNTRYZONE###'] = $this->applyStdWrap ($this->object->getCountryZone (), 'countryzone_stdWrap');
				$sims ['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone ();
			}
		}
	}
}

?>