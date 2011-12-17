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


require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

/**
 * 
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_notification_view extends tx_cal_base_service {
	
	var $mailer;
	var $baseUrl;
	
	function tx_cal_notification_view(){
		$this->tx_cal_base_service();
		$this->baseUrl = '';//t3lib_div::getIndpEnv('TYPO3_SITE_URL');
	}
	
	function notifyOfChanges($oldEventDataArray, $newEventDataArray){
		unset($oldEventDataArray['starttime']);
		unset($oldEventDataArray['endtime']);
		unset($newEventDataArray['starttime']);
		unset($newEventDataArray['endtime']);
		
		$pidArray = t3lib_div::trimExplode(',',$this->conf['pidList'],1); 
		if(!in_array($oldEventDataArray['pid'],$pidArray)){ 
			t3lib_div::sysLog('Event PID ('.$oldEventDataArray['pid'].') is outside the configured pidList ('.$this->conf['pidList'].') so notifications cannot be sent.', 'cal', 2); 
			return; 
		}
		$eventDataArray = array_merge($oldEventDataArray,$newEventDataArray);
		$event_old = $this->modelObj->findEvent($oldEventDataArray['uid'],'tx_cal_phpicalendar', $this->conf['pidList'], true, true, false, true, true);
		$event_new = $this->modelObj->findEvent($oldEventDataArray['uid'],'tx_cal_phpicalendar', $this->conf['pidList'], true, true, false, true, true);
		
			// Make sure we have an old event and new event before notifying.
		if (is_object($event_old) && is_object($event_new)) {
			$event_old->updateWithPiVars($oldEventDataArray); 
			$event_new->updateWithPiVars($eventDataArray);

			$this->startMailer();
			
			$select = 'fe_users.*';
			$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
			$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND tx_cal_fe_user_event_monitor_mm.tablenames = "fe_users" AND tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.uid = '.$oldEventDataArray['uid'];
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
					$template = $this->conf['view.']['event.']['notify.'][$row1['uid'].'.']['onChangeTemplate'];
					if(!$template){
						$template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
					}
					$titleText = $this->conf['view.']['event.']['notify.'][$row1['uid'].'.']['onChangeEmailTitle'];
					if(!$titleText){
						$titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
					}

					$unsubscribeLink = $this->baseUrl.$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event_old->getUid().$row1['email'].$row1['crdate'])));
					$this->sendNotificationOfChanges($event_old, $event_new, $row1['email'], $template, $titleText, $unsubscribeLink);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			
			$select = 'tx_cal_unknown_users.*';
			$table = 'tx_cal_unknown_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
			$where = 'tx_cal_unknown_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND tx_cal_fe_user_event_monitor_mm.tablenames = "tx_cal_unknown_users" AND tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.uid = '.$oldEventDataArray['uid'];
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
					$template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
					$titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
					$unsubscribeLink = $this->baseUrl.$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event_old->getUid().$row1['email'].$row1['crdate'])));
					$this->sendNotificationOfChanges($event_old, $event_new, $row1['email'], $template, $titleText, $unsubscribeLink);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
			foreach($event_new->getCategories() as $category){
				if (is_object($category)) {
					foreach($category->getNotificationEmails() as $emailAddress){
						if($emailAddress!='' && t3lib_div::validEmail($emailAddress)){
							$template = $this->conf['view.']['category.']['notify.'][$category->getUid().'.']['onChangeTemplate'];
							if(!$template){
								$template = $this->conf['view.']['category.']['notify.']['all.']['onChangeTemplate'];
							}
							$titleText = $this->conf['view.']['category.']['notify.'][$category->getUid().'.']['onChangeEmailTitle'];
							if(!$titleText){
								$titleText = $this->conf['view.']['category.']['notify.']['all.']['onChangeEmailTitle'];
							}
							$unsubscribeLink = '';
							$this->sendNotificationOfChanges($event_old, $event_new, $emailAddress, $template, $titleText, $unsubscribeLink);
						}
					}
				}
			}
			
			$subType = 'getGroupsFE';
			$groups = array();
			$serviceObj = null;
			$serviceObj = t3lib_div::makeInstanceService('auth', $subType);
			if($serviceObj == null){
				return;
			}
			
			$select = 'tx_cal_fe_user_event_monitor_mm.uid_foreign';
			$table = 'tx_cal_fe_user_event_monitor_mm';
			$where = 'tx_cal_fe_user_event_monitor_mm.uid_local = '.$oldEventDataArray['uid'].' AND tx_cal_fe_user_event_monitor_mm.tablenames = "fe_groups"';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$serviceObj->getSubGroups($row1['uid_foreign'],'',$groups);
				
				$select = 'DISTINCT fe_users.email';
				$table = 'fe_groups, fe_users';
				$where = 'fe_groups.uid IN ('.implode(',',$groups).') 
						AND FIND_IN_SET(fe_groups.uid, fe_users.usergroup)
						AND fe_users.email != \'\' 
						AND fe_groups.deleted = 0 
						AND fe_groups.hidden = 0 
						AND fe_users.disable = 0
						AND fe_users.deleted = 0';
				$result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
				while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
	
					if($row2['email']!='' && t3lib_div::validEmail($row2['email'])){
						$template = $this->conf['view.']['event.']['notify.'][$row2['uid'].'.']['onChangeTemplate'];
						if(!$template){
							$template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
						}
						$titleText = $this->conf['view.']['event.']['notify.'][$row2['uid'].'.']['onChangeEmailTitle'];
						if(!$titleText){
							$titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
						}
	
						$unsubscribeLink = $this->baseUrl.$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row2['email'], 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event_old->getUid().$row2['email'].$row2['crdate'])));
						$this->sendNotificationOfChanges($event_old, $event_new, $row2['email'], $template, $titleText, $unsubscribeLink);
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result2);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
	}
	
	function sendNotificationOfChanges(&$event_old, &$event_new, $email, $templatePath, $titleText, $unsubscribeLink, $acceptLink='', $declineLink=''){
		$absFile = t3lib_div::getFileAbsFileName($templatePath);
		$template = t3lib_div::getURL($absFile);
		$htmlTemplate = $this->cObj->getSubpart($template,'###HTML###');
		$oldEventHTMLSubpart = $this->cObj->getSubpart($htmlTemplate,'###OLD_EVENT###');
		$newEventHTMLSubpart = $this->cObj->getSubpart($htmlTemplate,'###NEW_EVENT###');
		
		$plainTemplate = $this->cObj->getSubpart($template,'###PLAIN###');
		$oldEventPlainSubpart = $this->cObj->getSubpart($plainTemplate,'###OLD_EVENT###');
		$newEventPlainSubpart = $this->cObj->getSubpart($plainTemplate,'###NEW_EVENT###');

		$this->fillTemplate($event_old, $oldEventHTMLSubpart,$oldEventPlainSubpart);
		$this->fillTemplate($event_new, $newEventHTMLSubpart,$newEventPlainSubpart);

		$switch = array();
		$switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
		$switch['###ACCEPT_LINK###'] = $acceptLink;
		$switch['###DECLINE_LINK###'] = $declineLink;

		$htmlTemplate = tx_cal_functions::substituteMarkerArrayNotCached($htmlTemplate, $switch, array('###OLD_EVENT###' => $oldEventHTMLSubpart,'###NEW_EVENT###' => $newEventHTMLSubpart), array());
		$plainTemplate = tx_cal_functions::substituteMarkerArrayNotCached($plainTemplate, $switch, array('###OLD_EVENT###' => $oldEventPlainSubpart,'###NEW_EVENT###' => $newEventPlainSubpart), array());

		$plainTemplate = $event_new->finish($plainTemplate);
		$htmlTemplate = $event_new->finish($htmlTemplate);

		$switch = array();
		$rems = array();
		$wrapped = array();
		$event_new->getMarker($titleText,$switch,$rems, $wrapped, 'title');
		$this->mailer->subject = tx_cal_functions::substituteMarkerArrayNotCached($titleText, $switch, $rems, $wrapped);
		$this->sendEmail($email, $htmlTemplate, $plainTemplate);
	}
	
	function fillTemplate(&$event, &$eventHTMLSubpart,&$eventPlainSubpart){
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getMarker($eventHTMLSubpart,$switch,$rems, $wrapped, 'notification');
		$eventHTMLSubpart = tx_cal_functions::substituteMarkerArrayNotCached($eventHTMLSubpart, $switch, $rems, $wrapped);
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getMarker($eventPlainSubpart,$switch,$rems, $wrapped, 'notification');
		$eventPlainSubpart = tx_cal_functions::substituteMarkerArrayNotCached($eventPlainSubpart, $switch, $rems, $wrapped);
	}

	
	function notify(&$newEventDataArray){
		$event = $this->modelObj->findEvent($newEventDataArray['uid'],'tx_cal_phpicalendar', $this->conf['pidList'], true, true, false, true, true);
		
		if(is_object($event)) {
			$this->startMailer();
			$select = 'fe_users.*';
			$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
			$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.deleted = '.intval($newEventDataArray['deleted']).' AND tx_cal_event.uid = '.$newEventDataArray['uid'];
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
					if($newEventDataArray['deleted']){
						$template = $this->conf['view.']['event.']['notify.']['fe_users_'.$row1['uid'].'.']['onDeleteTemplate'];
						if(!$template){
							$template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
						}
						$titleText = $this->conf['view.']['event.']['notify.']['fe_users_'.$row1['uid'].'.']['onDeleteEmailTitle'];
						if(!$titleText){
							$titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
						}
					}else{
						$template = $this->conf['view.']['event.']['notify.']['fe_users_'.$row1['uid'].'.']['onCreateTemplate'];
						if(!$template){
							$template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
						}
						$titleText = $this->conf['view.']['event.']['notify.']['fe_users_'.$row1['uid'].'.']['onCreateEmailTitle'];
						if(!$titleText){
							$titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
						}
					}
					
					$unsubscribeLink = $this->baseUrl.$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event->getUid().$row1['email'].$row1['crdate'])));
					$this->sendNotification($event, $row1['email'], $template, $titleText, $unsubscribeLink);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
	
		
			$select = 'tx_cal_unknown_users.*';
			$table = 'tx_cal_unknown_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
			$where = 'tx_cal_unknown_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.uid = '.$event->getUid();
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
					$template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
					$titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
					if($newEventDataArray['deleted']){
						$template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
						$titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
					}
					$unsubscribeLink = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event->getUid().$row1['email'].$row1['crdate'])));
					$this->sendNotification($event, $row1['email'], $template, $titleText, $unsubscribeLink);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
	
			foreach($event->getCategories() as $category){
				foreach($category->getNotificationEmails() as $emailAddress){
					if($emailAddress!='' && t3lib_div::validEmail($emailAddress)){
						$template = $this->conf['view.']['category.']['notify.'][$category->getUid().'.']['onCreateTemplate'];
						if(!$template){
							$template = $this->conf['view.']['category.']['notify.']['all.']['onCreateTemplate'];
						}
						$titleText = $this->conf['view.']['category.']['notify.'][$category->getUid().'.']['onCreateEmailTitle'];
						if(!$titleText){
							$titleText = $this->conf['view.']['category.']['notify.']['all.']['onCreateEmailTitle'];
						}
						$unsubscribeLink = '';
						$this->sendNotification($event, $emailAddress, $template, $titleText, $unsubscribeLink);
					}
				}
			}
			
			$subType = 'getGroupsFE';
			$groups = array();
			$serviceObj = null;
			$serviceObj = t3lib_div::makeInstanceService('auth', $subType);
			if($serviceObj == null){
				return;
			}
			
			$select = 'tx_cal_fe_user_event_monitor_mm.uid_local';
			$table = 'tx_cal_fe_user_event_monitor_mm';
			$where = 'tx_cal_fe_user_event_monitor_mm.uid_foreign = '.$event->getUid().' AND tx_cal_fe_user_event_monitor_mm.tablenames = "fe_groups"';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$serviceObj->getSubGroups($row1['uid_local'],'',$groups);
				
				$select = 'DISTINCT fe_users.email';
				$table = 'fe_groups, fe_users';
				$where = 'fe_groups.uid IN ('.implode(',',$groups).') 
						AND FIND_IN_SET(fe_groups.uid, fe_users.usergroup)
						AND fe_users.email != \'\' 
						AND fe_groups.deleted = 0 
						AND fe_groups.hidden = 0 
						AND fe_users.disable = 0
						AND fe_users.deleted = 0';
				$result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
				while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
	
					if($row2['email']!='' && t3lib_div::validEmail($row2['email'])){
						$template = $this->conf['view.']['event.']['notify.']['fe_groups_'.$row2['uid'].'.']['onCreateTemplate'];
						if(!$template){
							$template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
						}
						$titleText = $this->conf['view.']['event.']['notify.']['fe_groups_'.$row2['uid'].'.']['onCreateEmailTitle'];
						if(!$titleText){
							$titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
						}
	
						$unsubscribeLink = $this->baseUrl.$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row2['email'], 'tx_cal_controller[uid]' => $event->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event->getUid().$row2['email'].$row2['crdate'])));
						$this->sendNotification($event, $row2['email'], $template, $titleText, $unsubscribeLink);
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result2);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
	}
	
	function sendNotification(&$event, $email, $templatePath, $titleText, $unsubscribeLink, $acceptLink='', $declineLink='', $ics=''){
		$absFile = t3lib_div::getFileAbsFileName($templatePath);
		$template = t3lib_div::getURL($absFile);
		$htmlTemplate = $this->cObj->getSubpart($template,'###HTML###');
		$plainTemplate = $this->cObj->getSubpart($template,'###PLAIN###');
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getMarker($htmlTemplate,$switch, $rems,$wrapped, 'notification');
		
		$switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
		$switch['###ACCEPT_LINK###'] = $acceptLink;
		$switch['###DECLINE_LINK###'] = $declineLink;
		$htmlTemplate = tx_cal_functions::substituteMarkerArrayNotCached($htmlTemplate, $switch, $rems, $wrapped);
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getMarker($plainTemplate,$switch,$rems, $wrapped, 'notification');
		$switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
		$switch['###ACCEPT_LINK###'] = $acceptLink;
		$switch['###DECLINE_LINK###'] = $declineLink;
		$plainTemplate = tx_cal_functions::substituteMarkerArrayNotCached($plainTemplate, $switch, $rems, $wrapped);
		
		$plainTemplate = $event->finish($plainTemplate);
		$htmlTemplate = $event->finish($htmlTemplate);

		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getMarker($titleText,$switch,$rems, $wrapped, 'title');
		
		$this->mailer->subject = tx_cal_functions::substituteMarkerArrayNotCached($titleText, $switch, $rems, $wrapped);
		$this->sendEmail($email, $htmlTemplate, $plainTemplate);
	}
	
	function invite($oldEventDataArray, $newEventDataArray=array()){
		unset($oldEventDataArray['starttime']);
		unset($oldEventDataArray['endtime']);
		unset($newEventDataArray['starttime']);
		unset($newEventDataArray['endtime']);

		$event_new = $event_old = $this->modelObj->findEvent($oldEventDataArray['uid'],'tx_cal_phpicalendar', $this->conf['pidList'], false, false, false, true, true);
		# no need for executing the same query twice, is it?
		#$event_new = $this->modelObj->findEvent($oldEventDataArray['uid'],'tx_cal_phpicalendar', $this->conf['pidList'], false, false, false, true, true);
		if(count($newEventDataArray)>0){
			$event_new->updateWithPiVars(array_merge($oldEventDataArray,$newEventDataArray));
		}

		$this->startMailer();
		
		$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		$globalAttendeeArray = $modelObj->findEventAttendees($event_new->getUid());

		$eventService = $modelObj->getServiceObjByKey('cal_event_model', 'event', $event_new->getType());

		$this->setChairmanAsMailer($globalAttendeeArray);
		$template = $this->conf['view.']['event.']['meeting.']['onChangeTemplate'];
		$viewObj = &tx_cal_registry::Registry('basic','viewcontroller');
		$eventArray = Array($event_new);

		foreach($globalAttendeeArray as $serviceType => $attendeeArray){
			foreach($attendeeArray as $uid => $attendee){
				if($attendee->getFeUserId()){
					$eventService->updateAttendees($event_new->getUid());
				}
				if($attendee->getEmail()){
					
					$conf = Array();
					$conf['parameter'] = $this->conf['view.']['event.']['meeting.']['statusViewPid'];
					$conf['forceAbsoluteUrl'] = 1;
					$urlParameters = Array ('tx_cal_controller[view]' => 'meeting', 'tx_cal_controller[attendee]' => $attendee->getUid(), 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[status]' => 'accept', 'tx_cal_controller[sid]' => md5($event_old->getUid().$attendee->getEmail().$attendee->row['crdate']));
					$conf['additionalParams'] .= t3lib_div::implodeArrayForUrl('', $urlParameters);
					$this->controller->cObj->typolink('', $conf);
					$acceptLink = $this->controller->cObj->lastTypoLinkUrl;
					
					$urlParameters = Array ('tx_cal_controller[view]' => 'meeting', 'tx_cal_controller[attendee]' => $attendee->getUid(), 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[status]' => 'decline', 'tx_cal_controller[sid]' => md5($event_old->getUid().$attendee->getEmail().$attendee->row['crdate']));
					$conf['additionalParams'] .= t3lib_div::implodeArrayForUrl('', $urlParameters);
					$this->controller->cObj->typolink('', $conf);
					$declineLink = $this->controller->cObj->lastTypoLinkUrl;
					
					$ics = $viewObj->drawIcs($eventArray, $this->conf['getdate'], false, $attendee->getEmail());

					$title = $event_new->getTitle().'.ics';
					$title = strtr($title,array(' '=>'',','=>'_',));
					$icsAttachmentFile = $this->createTempIcsFile($ics, $title);
					$this->mailer->addAttachment($icsAttachmentFile);

					if(count($newEventDataArray)>0){
						$this->sendNotificationOfChanges($event_old, $event_new, $attendee->getEmail(), $template, '###TITLE###', '', $acceptLink, $declineLink);
					} else {
						$this->sendNotification($event_old, $attendee->getEmail(), $template, '###TITLE###', '', $acceptLink, $declineLink);
					}
					unlink($icsAttachmentFile);
					$this->mailer->theParts['attach'] = array();
				}
			}
		}
	}
	
	function setChairmanAsMailer(&$globalAttendeeArray){
		
		foreach(array_keys($globalAttendeeArray) as $serviceType){
			foreach(array_keys($globalAttendeeArray[$serviceType]) as $uid){
				$attendee = &$globalAttendeeArray[$serviceType][$uid];
				if($attendee->getAttendance()=='CHAIR'){
					$this->mailer->from_email = $attendee->getEmail();
					$this->mailer->from_name = $attendee->getName();
					$this->mailer->replyto_email = $attendee->getEmail();
					$this->mailer->replyto_name = $attendee->getName();
					
					//do not invite the chairman
					unset($globalAttendeeArray[$serviceType][$uid]);
					break;
				}
			}
		}
	}
	
	function startMailer(){
		require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
		$this->mailer =t3lib_div::makeInstance('t3lib_htmlmail');
		$this->mailer->start();
		$this->mailer->from_email = $this->conf['view.']['event.']['notify.']['emailAddress'];
		$this->mailer->from_name = $this->conf['view.']['event.']['notify.']['fromName'];
		$this->mailer->replyto_email = $this->conf['view.']['event.']['notify.']['emailReplyAddress'];
		$this->mailer->replyto_name = $this->conf['view.']['event.']['notify.']['replyToName'];
		$this->mailer->organisation = $this->conf['view.']['event.']['notify.']['organisation'];
	}
	
	function sendEmail($email, $htmlTemplate, $plainTemplate){
		$this->controller->finish($htmlTemplate);
		$this->controller->finish($plainTemplate);
		$plainTemplate = str_replace('&nbsp;',' ',strip_tags($plainTemplate));

		$this->mailer->theParts['html']['content'] = $htmlTemplate;
		$this->mailer->theParts['html']['path'] = '';
		$this->mailer->extractMediaLinks();
		$this->mailer->extractHyperLinks();
		$this->mailer->fetchHTMLMedia();
		$this->mailer->substMediaNamesInHTML(0); // 0 = relative
		$this->mailer->substHREFsInHTML();
			
		$this->mailer->setHTML($this->mailer->encodeMsg($this->mailer->theParts['html']['content']));

		$this->mailer->substHREFsInHTML();
	
		$this->mailer->setPlain(strip_tags($plainTemplate));
		$this->mailer->setHeaders();
		$this->mailer->setContent();
		$this->mailer->setRecipient($email);
		$this->mailer->sendtheMail();
	}
	
	function createTempIcsFile($content, $filename){
		require_once (PATH_t3lib . 'class.t3lib_basicfilefunc.php');
		$fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$all_files = Array();
		$all_files['webspace']['allow'] = '*';
		$all_files['webspace']['deny'] = '';
		$fileFunc->init('', $all_files);
		$theDestFile = t3lib_div::getFileAbsFileName('uploads/tx_cal/'.$filename);
		//$theDestFile = $fileFunc->getUniqueName($filename, 'uploads/tx_cal');
		$fh = fopen($theDestFile, 'w');
		fwrite($fh, $content);
		fclose($fh);
		return $theDestFile;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_notification_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_notification_view.php']);
}
?>