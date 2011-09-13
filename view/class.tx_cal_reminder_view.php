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

/**
 * 
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_reminder_view extends t3lib_svbase {
	
	function tx_cal_reminder_view(){
	}
	
	function remind(&$event, $conf){
				
	   	$marker = array();
	   	$marker['###TITLE###'] = '';
	   	$marker['###START_DATE###'] = '';
	   	$marker['###START_TIME###'] = '';
	   	$marker['###END_DATE###'] = '';
	   	$marker['###END_TIME###'] = '';
	   	$marker['###DESCRIPTION###'] = '';
	   	$marker['###LOCATION###'] = '';
	   	$marker['###ORGANIZER###'] = '';
	
		foreach($event as $key => $value){
			switch($key){
				case('calendar_id'):
	
				break;
				case('organizer'):
					$marker['###ORGANIZER###'] = $event['organizer'];
				break;
				case('location'):
					$marker['###LOCATION###'] = $event['location'];
				break;
				case('description'):
					$marker['###DESCRIPTION###'] = $event['description'];
				break;
				case('title'):
					$marker['###TITLE###'] = $event['title'];
				break;
				case('start_date'):
					if($event['start_date']>0){
						$marker['###START_DATE###'] = strftime($conf['dateFormat'],$event['start_date']);
					}
				break;
				case('start_time'):
					if($event['start_time']>0){
						$marker['###START_TIME###'] = strftime($conf['timeFormat'],$event['start_time']);
					}
				break;
				case('end_date'):
					if($event['end_date']>0){
						$marker['###END_DATE###'] = strftime($conf['dateFormat'],$event['end_date']);
					}
				break;
				case('end_time'):
					if($event['end_time']>0){
						$marker['###END_TIME###'] = strftime($conf['timeFormat'],$event['end_time']);
					}
				break;
			}
		}
		
		require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
		$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
		$mailer->start();
		
		$mailer->from_email = $conf['emailAddress'];
		$mailer->from_name = $conf['fromName'];
		$mailer->replyto_email = $conf['emailReplyAddress'];
		$mailer->replyto_name = $conf['replyToName'];
		$mailer->organisation = $conf['organisation'];
		
		$select = 'fe_users.*';
		$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
		$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.deleted = 0 AND tx_cal_event.uid = '.$event['uid'];
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($user['email']!='' && t3lib_div::validEmail($user['email'])){
				$template = $conf[$user['uid'].'.']['template'];
				if(!$template){
					$template = $conf['all.']['template'];
				}
				$titleText = $conf[$user.'.']['emailTitle'];
				if(!$titleText){
					$titleText = $conf['all.']['emailTitle'];
				}
				$absFile = t3lib_div::getFileAbsFileName($template);
	   			$text = t3lib_div::getURL($absFile);
				$text = replace_tags($marker, $text);
				$titleText = replace_tags($marker, $titleText);
				$mailer->subject = $titleText;
				$mailer->setPlain($text);
				$mailer->send($user['email']);
			}
		}
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