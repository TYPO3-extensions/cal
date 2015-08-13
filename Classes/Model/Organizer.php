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


/**
 * Base model for the calendar organizer.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class Organizer extends \TYPO3\CMS\Cal\Model\LocationModel {
	
	/**
	 * Constructor
	 * 
	 * @param array $row
	 *        	array
	 * @param string $pidList
	 *        	to search in
	 */
	function __construct($row, $pidList) {
		$this->setObjectType ('organizer');
		$this->setType ('tx_cal_organizer');
		parent::__construct ($this->getType ());
		$this->createOrganizer ($row);
		$this->templatePath = $this->conf ['view.'] ['organizer.'] ['organizerModelTemplate'];
	}
	function createOrganizer($row) {
		$this->createLocation ($row);
	}
	function renderOrganizer() {
		return $this->fillTemplate ('###TEMPLATE_ORGANIZER_ORGANIZER###');
	}
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if (! $rightsObj->isViewEnabled ('edit_organizer')) {
			return false;
		}
		if ($rightsObj->isCalAdmin ()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId ();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups ();
		}
		
		$isSharedUser = $this->isSharedUser ($feUserUid, $feGroupsArray);
		$isAllowedToEditOrganizers = $rightsObj->isAllowedToEditOrganizer ();
		$isAllowedToEditOwnOrganizersOnly = $rightsObj->isAllowedToEditOnlyOwnOrganizer ();
		
		if ($isAllowedToEditOwnOrganizersOnly) {
			return $isSharedUser;
		}
		return $isAllowedToEditOrganizers;
	}
	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if (! $rightsObj->isViewEnabled ('delete_organizer')) {
			return false;
		}
		if ($rightsObj->isCalAdmin ()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId ();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups ();
		}
		$isSharedUser = $this->isSharedUser ($feUserUid, $feGroupsArray);
		$isAllowedToDeleteOrganizers = $rightsObj->isAllowedToDeleteOrganizer ();
		$isAllowedToDeleteOwnOrganizersOnly = $rightsObj->isAllowedToDeleteOnlyOwnOrganizer ();
		
		if ($isAllowedToDeleteOwnOrganizersOnly) {
			return $isSharedUser;
		}
		return $isAllowedToDeleteOrganizers;
	}
	function getEditLink(& $template, & $sims, & $rems, $view) {
		$editlink = '';
		if ($this->isUserAllowedToEdit ()) {
			$this->initLocalCObject ($this->getValuesAsArray ());
			$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_edit_organizer'));
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'edit_organizer',
					'type' => $this->getType (),
					'uid' => $this->getUid () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['calendar.'] ['editOrganizerViewPid']);
			$editlink = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['organizer.'] ['editLink'], $this->conf ['view.'] [$view . '.'] ['organizer.'] ['editLink.']);
		}
		if ($this->isUserAllowedToDelete ()) {
			$this->initLocalCObject ($this->getValuesAsArray ());
			
			$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_delete_organizer'));
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'delete_organizer',
					'type' => $this->getType (),
					'uid' => $this->getUid () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['organizer.'] ['deleteOrganizerViewPid']);
			$editlink .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['organizer.'] ['deleteLink'], $this->conf ['view.'] [$view . '.'] ['organizer.'] ['deleteLink.']);
		}
		return $editlink;
	}
	function renderOrganizerFor($viewType, $subpartSuffix = '') {
		return $this->fillTemplate ('###TEMPLATE_ORGANIZER_' . strtoupper ($viewType) . ($subpartSuffix ? '_' : '') . $subpartSuffix . '###');
	}
}

?>