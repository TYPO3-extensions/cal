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
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class AttendeeModel extends \TYPO3\CMS\Cal\Model\BaseModel {
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
	public function __construct($row, $serviceKey) {
		$this->setType ('tx_cal_attendee');
		$this->setObjectType ('attendee');
		parent::__construct ($serviceKey);
		$this->init ($row);
	}
	
	private function init(&$row) {
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
	
	public function setEventUid($uid) {
		$this->eventUid = $uid;
	}
	
	public function getEventUid() {
		return $this->eventUid;
	}
	
	public function setFeUserId($uid) {
		$this->feUserId = $uid;
	}
	
	public function getFeUserId() {
		return $this->feUserId;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setAttendance($attendance) {
		$this->attendance = $attendance;
	}
	
	public function getAttendance() {
		return $this->attendance;
	}
	
	public function setStatus($status) {
		$this->status = $status;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		if (! $this->rightsObj->isViewEnabled ('edit_attendee')) {
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
	
	public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		if (! $this->rightsObj->isViewEnabled ('delete_attendee')) {
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
	
	public function __toString() {
		return 'Attendee ' . (is_object ($this) ? 'object' : 'something') . ': ' . implode (',', $this->row);
	}
}

?>