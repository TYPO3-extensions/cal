<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
* (http://evangelize.org). The WEC is developing TYPO3-based
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
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_phpicalendar_model.php');
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
	
	function tx_cal_notification_view(){
		$this->tx_cal_base_service();
	}
	
	function notifyOfChanges($oldEventDataArray, $newEventDataArray){
		$tx_cal_phpicalendar_model = &t3lib_div::makeInstanceClassName('tx_cal_phpicalendar_model');
		
		$oldEventDataArray['start_date'] += strtotimeOffset($oldEventDataArray['start_date']);
		if($oldEventDataArray['end_date']>0){
			$oldEventDataArray['end_date'] += strtotimeOffset($oldEventDataArray['end_date']);
		}else{
			$oldEventDataArray['end_date'] = $oldEventDataArray['start_date'];
		}
		
		if($newEventDataArray['start_date']){
			$newEventDataArray['start_date'] += strtotimeOffset($newEventDataArray['start_date']);
		}
		if($newEventDataArray['end_date']){
			$newEventDataArray['end_date'] += strtotimeOffset($newEventDataArray['end_date']);
		}

		$event_old = &new $tx_cal_phpicalendar_model('',$oldEventDataArray, false, $this->getServiceKey());
		$event_new = &new $tx_cal_phpicalendar_model('',array_merge($oldEventDataArray,$newEventDataArray), false, $this->getServiceKey());

		$this->startMailer();
			
		$select = 'fe_users.*';
		$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
		$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.uid = '.$oldEventDataArray['uid'];
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

				$unsubscribeLink = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event_old->getUid().$row1['email'].$row1['crdate'])));
				$this->sendNotificationOfChanges($event_old, $event_new, $row1['email'], $template, $titleText, $unsubscribeLink);
			}
		}
		
		$select = 'tx_cal_unknown_users.*';
		$table = 'tx_cal_unknown_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
		$where = 'tx_cal_unknown_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.uid = '.$oldEventDataArray['uid'];
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
				$template = $this->conf['view.']['event.']['notify.']['all.']['onChangeTemplate'];
				$titleText = $this->conf['view.']['event.']['notify.']['all.']['onChangeEmailTitle'];
				$unsubscribeLink = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event_old->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event_old->getUid().$row1['email'].$row1['crdate'])));
				$this->sendNotificationOfChanges($event_old, $event_new, $row1['email'], $template, $titleText, $unsubscribeLink);
			}
		}
	}
	
	function sendNotificationOfChanges(&$event_old, &$event_new, $email, $templatePath, $titleText, $unsubscribeLink){
		
		
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

		$htmlTemplate = $this->cObj->substituteMarkerArrayCached($htmlTemplate, $switch, array('###OLD_EVENT###' => $oldEventHTMLSubpart,'###NEW_EVENT###' => $newEventHTMLSubpart), array());
		$plainTemplate = $this->cObj->substituteMarkerArrayCached($plainTemplate, $switch, array('###OLD_EVENT###' => $oldEventPlainSubpart,'###NEW_EVENT###' => $newEventPlainSubpart), array());
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event_new->getEventMarker($titleText,$rems,$switch, $wrapped, 'title');
		$this->mailer->subject = $this->cObj->substituteMarkerArrayCached($titleText, $switch, $rems, $wrapped);
	
		$this->sendEmail($email, $htmlTemplate, $plainTemplate);
	}
	
	function fillTemplate(&$event, &$eventHTMLSubpart,&$eventPlainSubpart){
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getEventMarker($eventHTMLSubpart,$rems,$switch, $wrapped, 'event');
		$eventHTMLSubpart = $this->cObj->substituteMarkerArrayCached($eventHTMLSubpart, $switch, $rems, $wrapped);
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getEventMarker($eventPlainSubpart,$rems,$switch, $wrapped, 'event');
		$eventPlainSubpart = $this->cObj->substituteMarkerArrayCached($eventPlainSubpart, $switch, $rems, $wrapped);
	}

	
	function notify(&$newEventDataArray){
		$tx_cal_phpicalendar_model = &t3lib_div::makeInstanceClassName('tx_cal_phpicalendar_model');
		
		$newEventDataArray['start_date'] += strtotimeOffset($newEventDataArray['start_date']);
		if($newEventDataArray['end_date']>0){
			$newEventDataArray['end_date'] += strtotimeOffset($newEventDataArray['end_date']);
		}else{
			$newEventDataArray['end_date'] = $newEventDataArray['start_date'];
		}
		
		$event = &new $tx_cal_phpicalendar_model('',$newEventDataArray, false, $this->getServiceKey());
			
		
		$this->startMailer();
		
		$select = 'fe_users.*';
		$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
		$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.deleted = 0 AND tx_cal_event.uid = '.$newEventDataArray['uid'];
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
				if($newEventDataArray['deleted']){
					$template = $this->conf['view.']['event.']['notify.'][$row1['uid'].'.']['onDeleteTemplate'];
					if(!$template){
						$template = $this->conf['view.']['event.']['notify.']['all.']['onDeleteTemplate'];
					}
					$titleText = $this->conf['view.']['event.']['notify.'][$row1['uid'].'.']['onDeleteEmailTitle'];
					if(!$titleText){
						$titleText = $this->conf['view.']['event.']['notify.']['all.']['onDeleteEmailTitle'];
					}
				}else{
					$template = $this->conf['view.']['event.']['notify.'][$row1['uid'].'.']['onCreateTemplate'];
					if(!$template){
						$template = $this->conf['view.']['event.']['notify.']['all.']['onCreateTemplate'];
					}
					$titleText = $this->conf['view.']['event.']['notify.'][$row1['uid'].'.']['onCreateEmailTitle'];
					if(!$titleText){
						$titleText = $this->conf['view.']['event.']['notify.']['all.']['onCreateEmailTitle'];
					}
				}
				
				$unsubscribeLink = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription', 'tx_cal_controller[email]' => $row1['email'], 'tx_cal_controller[uid]' => $event->getUid(), 'tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[sid]' => md5($event->getUid().$row1['email'].$row1['crdate'])));
				$this->sendNotification($event, $row1['email'], $template, $titleText, $unsubscribeLink);
			}
		}
		
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
	}
	
	function sendNotification(&$event, $email, $templatePath, $titleText, $unsubscribeLink){
		
		$absFile = t3lib_div::getFileAbsFileName($templatePath);
		$template = t3lib_div::getURL($absFile);
		$htmlTemplate = $this->cObj->getSubpart($template,'###HTML###');
		$plainTemplate = $this->cObj->getSubpart($template,'###PLAIN###');
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getEventMarker($htmlTemplate,$rems,$switch, $wrapped, 'event');

		$switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
		$htmlTemplate = $this->cObj->substituteMarkerArrayCached($htmlTemplate, $switch, $rems, $wrapped);
		
		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getEventMarker($plainTemplate,$rems,$switch, $wrapped, 'event');
		$switch['###UNSUBSCRIBE_LINK###'] = $unsubscribeLink;
		$plainTemplate = $this->cObj->substituteMarkerArrayCached($plainTemplate, $switch, $rems, $wrapped);
		

		$switch = array();
		$rems = array();
		$wrapped = array();
		$event->getEventMarker($titleText,$rems,$switch, $wrapped, 'title');
		$this->mailer->subject = $this->cObj->substituteMarkerArrayCached($titleText, $switch, $rems, $wrapped);
	
		$this->sendEmail($email, $htmlTemplate, $plainTemplate);
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_notification_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_notification_view.php']);
}
?>
