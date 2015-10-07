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
 * * @author Mario Matzulla <mario@matzullas.de>
 * 
 * @package TYPO3
 * @subpackage cal
 */
class CalendarModel extends \TYPO3\CMS\Cal\Model\BaseModel {
	var $row = array ();
	var $title = '';
	var $owner = array (
			'fe_users' => array (),
			'fe_groups' => array () 
	);
	var $activateFreeAndBusy = 0;
	var $freeAndBusyUser = array (
			'fe_users' => array (),
			'fe_groups' => array () 
	);
	var $calendarType = 0;
	var $extUrl = '';
	var $icsFile = '';
	var $refresh = 30;
	var $md5 = '';
	var $isPublic = true;
	var $calendarService;
	var $noAutoFetchMethods = Array (
			'getOwner',
			'getFreeAndBusyUser' 
	); // array with method names as values, where the method has the naming scheme 'getCustomMethodName' (so, with 'get' prefix) and the method itself expects parameters and thus can not be fetched dynamically
	
	/**
	 * Constructor.
	 */
	function __construct($row, $serviceKey) {
		$this->setType ('tx_cal_calendar');
		$this->setObjectType ('calendar');
		parent::__construct ($serviceKey);
		if (is_array ($row) && ! empty ($row)) {
			$this->init ($row);
		}
	}
	function init(&$row) {
		$this->row = $row;
		$this->setUid ($row ['uid']);
		$this->setTitle ($row ['title']);
		$this->setActivateFreeAndBusy ($row ['activate_fnb']);
		$this->setCalendarType ($row ['type']);
		$this->setExtUrl ($row ['ext_url']);
		$this->setIcsFile ($row ['ics_file']);
		$this->setRefresh ($row ['refresh']);
		$this->setMD5 ($row ['md5']);
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_calendar_fnb_user_group_mm.*', 'tx_cal_calendar_fnb_user_group_mm, fe_users, fe_groups', 
				// join on fe_users
				'((uid_foreign = fe_users.uid AND tablenames="fe_users" ' . $cObj->enableFields ('fe_users') . ') OR ' . 
				// join on fe_groups
				'(uid_foreign = fe_groups.uid AND tablenames="fe_groups" ' . $cObj->enableFields ('fe_groups') . ')) AND ' . 
				// general conditions
				'uid_local=' . $this->getUid () . ' AND (fe_users.uid IS NOT NULL OR fe_groups.uid IS NOT NULL)');
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$this->addFreeAndBusyUser ($row ['tablenames'], $row ['uid_foreign']);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_calendar_user_group_mm.*', 'tx_cal_calendar_user_group_mm, fe_users, fe_groups', 
				// join on fe_users
				'((uid_foreign = fe_users.uid AND tablenames="fe_users" ' . $cObj->enableFields ('fe_users') . ') OR ' . 
				// join on fe_groups
				'(uid_foreign = fe_groups.uid AND tablenames="fe_groups" ' . $cObj->enableFields ('fe_groups') . ')) AND ' . 
				// general conditions
				'uid_local=' . $this->getUid () . ' AND (fe_users.uid IS NOT NULL OR fe_groups.uid IS NOT NULL)');
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$this->addOwner ($row ['tablenames'], $row ['uid_foreign']);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
	}
	function setTitle($title) {
		$this->title = $title;
	}
	function getTitle() {
		return $this->title;
	}
	function isActivateFreeAndBusy() {
		return $this->activateFreeAndBusy;
	}
	function getActivateFreeAndBusy() {
		return $this->activateFreeAndBusy;
	}
	function setActivateFreeAndBusy($activateFreeAndBusy) {
		$this->activateFreeAndBusy = $activateFreeAndBusy;
	}
	function getCalendarType() {
		return $this->calendarType;
	}
	function setCalendarType($calendarType) {
		$this->calendarType = $calendarType;
	}
	function getExtUrl() {
		return $this->extUrl;
	}
	function setExtUrl($extUrl) {
		$this->extUrl = $extUrl;
	}
	function getIcsFile() {
		return $this->icsFile;
	}
	function setIcsFile($icsFile) {
		$this->icsFile = $icsFile;
	}
	function getRefresh() {
		return $this->refresh;
	}
	function setRefresh($refresh) {
		$this->refresh = $refresh;
	}
	function getMD5() {
		return $this->md5;
	}
	function setMD5($md5) {
		$this->md5 = $md5;
	}
	function getFreeAndBusyUser($table, $index = 0) {
		if ($index > 0 && count ($this->freeAndBusyUser [$table]) > $index) {
			return $this->freeAndBusyUser [$table] [$index];
		}
		return $this->freeAndBusyUser [$table];
	}
	function setFreeAndBusyUser($table, $freeAndBusyUser) {
		$this->freeAndBusyUser [$table] = $freeAndBusyUser;
	}
	function addFreeAndBusyUser($table, $freeAndBusyUser) {
		$this->freeAndBusyUser [$table] [] = $freeAndBusyUser;
	}
	function getOwner($table, $index = 0) {
		if ($index > 0 && count ($this->owner [$table]) > $index) {
			return $this->owner [$table] [$index];
		}
		return $this->owner [$table];
	}
	function setOwner($table, $owner) {
		$this->owner [$table] = $owner;
		$this->isPublic = false;
	}
	function addOwner($table, $owner) {
		$this->owner [$table] [] = $owner;
		$this->isPublic = false;
	}
	function getExtUrlMarker(& $template, & $sims, & $rems, $view) {
		$this->initLocalCObject ();
		$sims ['###EXTURL###'] = $this->local_cObj->stdWrap ($this->getExtUrl (), $this->conf ['view.'] [$view . '.'] ['exturl_stdWrap.']);
	}
	function getIcsFileMarker(& $template, & $sims, & $rems, $view) {
		$this->initLocalCObject ();
		$sims ['###ICSFILE###'] = $this->local_cObj->stdWrap ($this->getIcsFile (), $this->conf ['view.'] [$view . '.'] ['icsfile_stdWrap.']);
	}
	function getRefreshMarker(& $template, & $sims, & $rems, $view) {
		$this->initLocalCObject ();
		$sims ['###REFRESH###'] = $this->local_cObj->stdWrap ($this->getRefresh (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['refresh_stdWrap.']);
	}
	function getTitleMarker(& $template, & $sims, & $rems, $view) {
		$this->initLocalCObject ();
		$sims ['###TITLE###'] = $this->local_cObj->stdWrap ($this->getTitle (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['title_stdWrap.']);
	}
	function isPublic() {
		return $this->isPublic;
	}
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if (! $rightsObj->isViewEnabled ('edit_calendar')) {
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
		$isCalendarOwner = $this->isCalendarOwner ($feUserUid, $feGroupsArray);
		
		$isAllowedToEditCalendars = $rightsObj->isAllowedToEditCalendar ();
		$isAllowedToEditOwnCalendarsOnly = $rightsObj->isAllowedToEditOnlyOwnCalendar ();
		$isAllowedToEditPublicCalendars = $rightsObj->isAllowedToEditPublicCalendar ();
		
		if ($isAllowedToEditOwnCalendarsOnly) {
			return $isCalendarOwner;
		}
		return $isAllowedToEditCalendars && ($isCalendarOwner || ($this->isPublic && $isAllowedToEditPublicCalendars));
	}
	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		if (! $rightsObj->isViewEnabled ('delete_calendar')) {
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
		$isCalendarOwner = $this->isCalendarOwner ($feUserUid, $feGroupsArray);
		
		$isAllowedToDeleteCalendars = $rightsObj->isAllowedToDeleteCalendar ();
		$isAllowedToDeleteOwnCalendarsOnly = $rightsObj->isAllowedToDeleteOnlyOwnCalendar ();
		$isAllowedToDeletePublicCalendars = $rightsObj->isAllowedToDeletePublicCalendar ();
		
		if ($isAllowedToDeleteOwnCalendarsOnly) {
			return $isCalendarOwner;
		}
		return $isAllowedToDeleteCalendars && ($isCalendarOwner || ($this->isPublic && $isAllowedToDeletePublicCalendars));
	}
	function isCalendarOwner($userId, $groupIdArray) {
		if (is_array ($this->owner ['fe_users']) && in_array ($userId, $this->owner ['fe_users'])) {
			return true;
		}
		foreach ($groupIdArray as $id) {
			if (is_array ($this->owner ['fe_groups']) && in_array ($id, $this->owner ['fe_groups'])) {
				return true;
			}
		}
		return false;
	}
	function getEditLink(& $template, & $sims, & $rems, $view) {
		$editlink = '';
		if ($this->isUserAllowedToEdit ()) {
			$this->initLocalCObject ($this->getValuesAsArray ());
			$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_edit_calendar'));
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'edit_calendar',
					'type' => $this->getType (),
					'uid' => $this->getUid () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['calendar.'] ['editCalendarViewPid']);
			$editlink = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['calendar.'] ['editLink'], $this->conf ['view.'] [$view . '.'] ['calendar.'] ['editLink.']);
		}
		if ($this->isUserAllowedToDelete ()) {
			$this->initLocalCObject ($this->getValuesAsArray ());
			$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_delete_calendar'));
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'delete_calendar',
					'type' => $this->getType (),
					'uid' => $this->getUid () 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] ['calendar.'] ['deleteCalendarViewPid']);
			$editlink .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] ['calendar.'] ['deleteLink'], $this->conf ['view.'] [$view . '.'] ['calendar.'] ['deleteLink.']);
		}
		return $editlink;
	}
	function updateWithPIVars(&$piVars) {
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelController');
		$cObj = &$this->controller->cObj;
		
		foreach ($piVars as $key => $value) {
			switch ($key) {
				case 'title' :
					$this->setTitle (strip_tags ($piVars ['title']));
					unset ($piVars ['title']);
					break;
				case 'calendarType' :
					$this->setCalendarType (strip_tags ($piVars ['calendarType'], array ()));
					unset ($piVars ['calendarType']);
					break;
				case 'owner' :
					foreach ((array) strip_tags ($this->controller->piVars ['owner']) as $valueInner) {
						preg_match ('/(^[a-z])_([0-9]+)_(.*)/', $valueInner, $idname);
						if ($idname [1] == 'u') {
							$this->setOwner ('fe_users', $idname [2]);
						} else {
							$this->setOwner ('fe_groups', $idname [2]);
						}
					}
					break;
				case 'activateFreeAndBusy' :
					$this->setActivateFreeAndBusy (intval ($piVars ['activateFreeAndBusy']));
					unset ($piVars ['activateFreeAndBusy']);
					break;
				case 'freeAndBusyUser' :
					foreach ((array) strip_tags ($this->controller->piVars ['freeAndBusyUser']) as $valueInner) {
						preg_match ('/(^[a-z])_([0-9]+)_(.*)/', $valueInner, $idname);
						if ($idname [1] == 'u') {
							$this->setOwner ('fe_users', $idname [2]);
						} else {
							$this->setOwner ('fe_groups', $idname [2]);
						}
					}
					break;
				case 'icsfile' :
					$this->setIcsFile (strip_tags ($piVars ['icsfile']));
					unset ($piVars ['icsfile']);
					break;
				case 'ics_file' :
					if (is_array ($piVars ['ics_file'])) {
						$this->setIcsFile (strip_tags ($piVars ['ics_file'] [0]));
					}
					unset ($piVars ['ics_file']);
					break;
				case 'exturl' :
					$this->setExtUrl (strip_tags ($piVars ['exturl']));
					unset ($piVars ['exturl']);
					break;
				case 'refresh' :
					$this->setRefresh (strip_tags ($piVars ['refresh']));
					unset ($piVars ['refresh']);
					break;
			}
		}
	}
	function __toString() {
		return 'Calendar ' . (is_object ($this) ? 'object' : 'something') . ': ' . implode (',', $this->row);
	}
	
	/**
	 * Returns a array with fieldname => value pairs, that should be additionally added to the values of the method getValuesAsArray
	 * @ return		array
	 */
	function getAdditionalValuesAsArray() {
		$values = parent::getAdditionalValuesAsArray ();
		$tables = array_keys ($this->owner);
		$values ['owner'] = Array ();
		foreach ($tables as $table) {
			foreach ($this->owner [$table] as $id) {
				$values ['owner'] [$table] [$id] = $id;
			}
		}
		$values ['headerstyle'] = $this->row ['headerstyle'];
		$values ['bodystyle'] = $this->row ['bodystyle'];
		return $values;
	}
}

?>