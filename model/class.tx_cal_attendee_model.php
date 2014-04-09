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

// equire_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_attendee_model extends tx_cal_base_model {
	var $row = array ();
	var $eventUid = 0;
	var $feUserId = 0;
	var $email = '';
	var $attendance = '';
	var $status = 0;
	var $name = '';
	var $eventId;
	
	/**
	 * Constructor.
	 */
	function tx_cal_attendee_model($row, $serviceKey) {
		$this->setType ('tx_cal_attendee');
		$this->setObjectType ('attendee');
		$this->tx_cal_base_model ($serviceKey);
		$this->init ($row);
	}
	function init(&$row) {
		$this->row = $row;
		if (isset ($row ['uid'])) {
			$this->setUid ($row ['uid']);
		}
		if (isset ($row ['event_id'])) {
			$this->setEventUid ($row ['event_id']);
		}
		if (isset ($row ['hidden'])) {
			$this->setHidden ($row ['hidden']);
		}
		if (isset ($row ['fe_user_id'])) {
			$this->setFeUserId ($row ['fe_user_id']);
		}
		if (isset ($row ['email'])) {
			$this->setEmail ($row ['email']);
		}
		if (isset ($row ['attendance'])) {
			$this->setAttendance ($row ['attendance']);
		}
		if (isset ($row ['status'])) {
			$this->setStatus ($row ['status']);
		}
		if (isset ($row ['name'])) {
			$this->setName ($row ['name']);
		}
	}
	function setEventUid($uid) {
		$this->eventUid = $uid;
	}
	function getEventUid() {
		return $this->eventUid;
	}
	function setFeUserId($uid) {
		$this->feUserId = $uid;
	}
	function getFeUserId() {
		return $this->feUserId;
	}
	function setEmail($email) {
		$this->email = $email;
	}
	function getEmail() {
		return $this->email;
	}
	function setAttendance($attendance) {
		$this->attendance = $attendance;
	}
	function getAttendance() {
		return $this->attendance;
	}
	function setStatus($status) {
		$this->status = $status;
	}
	function getStatus() {
		return $this->status;
	}
	function setName($name) {
		$this->name = $name;
	}
	function getName() {
		return $this->name;
	}
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		if (! $rightsObj->isViewEnabled ('edit_attendee')) {
			return false;
		}
		if ($this->rightsObj->isCalAdmin ()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $this->rightsObj->getUserId ();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $this->rightsObj->getUserGroups ();
		}
		
		$isAttendee = false;
		if ($this->getFeUserId () == $this->rightsObj->getUserId ()) {
			$isAttendee = true;
		}
		
		$isAllowedToEditAttendee = $this->rightsObj->isAllowedTo ('edit', 'attendee');
		
		return $isAllowedToEditAttendee && $isAttendee;
	}
	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		if (! $rightsObj->isViewEnabled ('delete_attendee')) {
			return false;
		}
		if ($this->rightsObj->isCalAdmin ()) {
			return true;
		}
		
		if ($feUserUid == '') {
			$feUserUid = $this->rightsObj->getUserId ();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $this->rightsObj->getUserGroups ();
		}
		$isAttendee = false;
		if ($this->getFeUserId () == $this->rightsObj->getUserId ()) {
			$isAttendee = true;
		}
		
		$isAllowedToDeleteAttendee = $this->rightsObj->isAllowedTo ('delete', 'attendee');
		
		return $isAllowedToDeleteAttendee && $isAttendee;
	}
	function __toString() {
		return 'Attendee ' . (is_object ($this) ? 'object' : 'something') . ': ' . implode (',', $this->row);
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_cal_attendee_model.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_cal_attendee_model.php']);
}
?>