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

 
require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_meeting_manager_view extends tx_cal_base_view {
	
	function tx_cal_meeting_manager_view(){
		$this->tx_cal_base_view();
	}
	
	/**
	 * Main function to draw the meeting manager view.
	 * @return		string		HTML output of the meeting manager.
	 */
	function drawMeetingManager(){
		
		$rems = array();
		$sims = array();
		$wrapped = array();
		
		$sims['###HEADING###'] = $this->controller->pi_getLL('l_manage_meeting');
		$sims['###STATUS###'] = '';
		$rems['###USER_LOGIN###']='';
		$rems['###MEETING_CONTAINER###']='';

		/* Get the meeting manager template */
		$page = $this->cObj->fileResource($this->conf['view.']['event.']['meeting.']['managerTemplate']);
		if ($page == '') {
			return '<h3>calendar: no meeting manager template file found:</h3>' . $this->conf['view.']['meeting.']['managerTemplate'];
		}
		
		$eventUID = $this->conf['uid'];
		$attendeeUid = intval($this->controller->piVars['attendee']);
		$meetingHash = strip_tags($this->controller->piVars['sid']);
		$attendeeStatus = strip_tags($this->controller->piVars['status']);

		/* If we have an event, email, and meeting id, try to subscribe or unsubscribe */
		if($eventUID > 0 && $attendeeUid && $attendeeStatus && $meetingHash){
			$event = $this->modelObj->findEvent($eventUID, 'tx_cal_phpicalendar', $this->conf['pidList'], false, false, false, true, true);
			
			unset($this->controller->piVars['monitor']);
			unset($this->controller->piVars['attendee']);
			unset($this->controller->piVars['sid']);
			$local_rems = array();
			$local_sims = array();
			$local_wrapped = array();
			
			$status = $this->cObj->getSubpart($page, '###STATUS###');
			switch($attendeeStatus){
				case 'accept': /* user comes to the meeting */		
					if($this->changeStatus($attendeeUid, $event, $meetingHash, 'ACCEPTED')) {
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_meeting_accepted'), $event->getTitle());
					} else {
						/* No user to unsubscribe.  Output a message here? */
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_meeting_update_error'), $event->getTitle());
					}
					
					break;
				case 'decline': /* user does not come to the meeting */
					if($this->changeStatus($attendeeUid, $event, $meetingHash, 'DECLINE')) {
						$status = $this->cObj->getSubpart($page, '###STATUS_START###');
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_meeting_declined'), $event->getTitle());
					} else {
						/* No user to subscribe.  Output a message here? */
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_meeting_update_error'), $event->getTitle());
					}
					break;
			}
			
		} else {
			$sims['###STATUS###'] = $this->controller->pi_getLL('l_meeting_error');
		}
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);
		$rems = array();
		return $this->finish($page, $rems);
	}
	
	/**
	 * Attempts to change the status of a meeting participant of
	 * a particular event if the meeting hash matches.
	 * @param		string		The uid of the attendee.
	 * @param		object		Event object.
	 * @param		string		Unique hash of email and event.
	 * @return		string		The status to set the attendee to.
	 */
	function changeStatus($attendeeUid, $event, $meetingHash, $status) {
		$attendeeArray = $event->getAttendees();
		
		if($attendeeArray['tx_cal_attendee'][$attendeeUid]){

			$attendeeObject = $attendeeArray['tx_cal_attendee'][$attendeeUid];
			$md5 = md5($event->getUid().$attendeeObject->getEmail().$attendeeObject->row['crdate']);
			if($md5==$meetingHash){
				$table = 'tx_cal_attendee';
				$where = 'uid = '.$attendeeUid;			
				$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,array('status' => $status));
				return true;
			}
		}
		return false;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_meeting_manager_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_meeting_manager_view.php']);
}
?>