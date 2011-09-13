<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
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


/**
 * 
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_reminder_view extends tx_cal_base_service {
	
	function tx_cal_reminder_view(){
		$this->tx_cal_base_service();
	}
	
	function remind(&$event){
		require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
		$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
		$mailer->start();
		
		$mailer->from_email = $this->conf['view.']['event.']['remind.']['emailAddress'];
		$mailer->from_name = $this->conf['view.']['event.']['remind.']['fromName'];
		$mailer->replyto_email = $this->conf['view.']['event.']['remind.']['emailReplyAddress'];
		$mailer->replyto_name = $this->conf['view.']['event.']['remind.']['replyToName'];
		$mailer->organisation = $this->conf['view.']['event.']['remind.']['organisation'];
		
		$select = 'fe_users.*';
		$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
		$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.deleted = 0 AND tx_cal_event.uid = '.$event->getUid();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($user['email']!='' && t3lib_div::validEmail($user['email'])){
				$template = $this->conf[$user['uid'].'.']['template'];
				if(!$template){
					$template = $this->conf['view.']['event.']['remind.']['all.']['template'];
				}
				$titleText = $this->conf['view.']['event.']['remind.'][$user.'.']['emailTitle'];
				if(!$titleText){
					$titleText = $this->conf['view.']['event.']['remind.']['all.']['emailTitle'];
				}
				$absFile = t3lib_div::getFileAbsFileName($template);
	   			$text = t3lib_div::getURL($absFile);
	   			$rems = array();
	   			$switch = array();
	   			$wrapped = array();
	   			$event->getMarker($text,$switch,$rems,$wrapped);
	   			$text = strtr($text, $switch);
	   			$rems = array();
	   			$switch = array();
	   			$wrapped = array();
	   			$event->getMarker($titleText,$switch,$rems,$wrapped);
	   			$titleText = strtr($titleText,$switch);
				$mailer->subject = $titleText;
				$mailer->setPlain($text);
				$mailer->send($user['email']);
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
	}
	
	
	/* @todo	Figure out where this should live */
	function scheduleReminder($calEventUID, $reminderTimestamp) {
		if (t3lib_extMgm::isLoaded('gabriel')) {
			$eventUID = 'tx_cal_event:'.$calEventUID;

			/* Check for existing gabriel events and remove them */
			$this->deleteReminder($calEventUID);

			/* Set up the gabriel event */
			$cron = t3lib_div::getUserObj('EXT:cal/cron/class.tx_cal_reminder_cron.php:tx_cal_reminder_cron');
			$cron->setUID($calEventUID);

			/* Schedule the gabriel event */ 
			$cron->registerSingleExecution($reminderTimestamp);
			$gabriel = t3lib_div::getUserObj('EXT:gabriel/class.tx_gabriel.php:&tx_gabriel');
			$gabriel->addEvent($cron,$eventUID);
		}
	}

	/* @todo	Figure out where this should live */
	function deleteReminder($calEventUID) {
		if (t3lib_extMgm::isLoaded('gabriel')) {
			$eventUID = 'tx_cal_event:'.$calEventUID;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_gabriel',' crid="'.$eventUID.'"');
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_reminder_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_reminder_view.php']);
}
?>