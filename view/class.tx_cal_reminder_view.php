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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;


/**
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_reminder_view extends tx_cal_notification_view {
	function tx_cal_reminder_view() {
		$this->tx_cal_notification_view ();
	}
	function remind(&$event, $eventMonitor) {
		$this->startMailer ();
		
		switch ($eventMonitor ['tablenames']) {
			case 'fe_users' :
				$feUserRec = BackendUtility::getRecord ('fe_users', $eventMonitor ['uid_foreign']);
				$this->process ($event, $feUserRec ['email'], $eventMonitor ['tablenames'] . '_' . $feUserRec ['uid']);
				break;
			case 'fe_groups' :
				$subType = 'getGroupsFE';
				$groups = array ();
				$serviceObj = null;
				$serviceObj = GeneralUtility::makeInstanceService ('auth', $subType);
				if ($serviceObj == null) {
					return;
				}
				
				$serviceObj->getSubGroups ($eventMonitor ['uid_foreign'], '', $groups);
				
				$select = 'DISTINCT fe_users.email';
				$table = 'fe_groups, fe_users';
				$where = 'fe_groups.uid IN (' . implode (',', $groups) . ') 
						AND FIND_IN_SET(fe_groups.uid, fe_users.usergroup)
						AND fe_users.email != \'\' 
						AND fe_groups.deleted = 0 
						AND fe_groups.hidden = 0 
						AND fe_users.disable = 0
						AND fe_users.deleted = 0';
				$result2 = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
				while ($row2 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result2)) {
					$this->process ($event, $row2 ['email'], $eventMonitor ['tablenames'] . '_' . $row2 ['uid']);
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result2);
				break;
			case 'tx_cal_unknown_users' :
				$feUserRec = BackendUtility::getRecord ('tx_cal_unknown_users', $eventMonitor ['uid_foreign']);
				$this->process ($event, $feUserRec ['email'], $eventMonitor ['tablenames'] . '_' . $feUserRec ['uid']);
				break;
		}
	}
	function process(&$event, $email, $userId) {
		if ($email != '' && GeneralUtility::validEmail ($email)) {
			$template = $this->conf ['view.'] ['event.'] ['remind.'] [$userId . '.'] ['template'];
			if (! $template) {
				$template = $this->conf ['view.'] ['event.'] ['remind.'] ['all.'] ['template'];
			}
			$titleText = $this->conf ['view.'] ['event.'] ['remind.'] [$userId . '.'] ['emailTitle'];
			if (! $titleText) {
				$titleText = $this->conf ['view.'] ['event.'] ['remind.'] ['all.'] ['emailTitle'];
			}
			$this->sendNotification ($event, $email, $template, $titleText, '');
		}
	}
	
	/* @todo	Figure out where this should live */
	function scheduleReminder($calEventUID) {
		global $TYPO3_CONF_VARS;
		
		// Get complete record
		$eventRecord = BackendUtility::getRecord ('tx_cal_event', $calEventUID);
		
		// get the related monitoring records
		$taskId = null;
		$offset = 0;
		
		$select = '*';
		$table = 'tx_cal_fe_user_event_monitor_mm';
		$where = 'uid_local = ' . $calEventUID;
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$taskId = $row ['schedulerId'];
			$offset = $row ['offset'];
			
			// maybe there is a recurring instance
			// get the uids of recurring events from index
			$now = new tx_cal_date ();
			$now->setTZbyId ('UTC');
			$now->addSeconds ($offset * 60);
			$startDateTimeObject = new tx_cal_date ($eventRecord ['start_date'] . '000000');
			$startDateTimeObject->setTZbyId ('UTC');
			$startDateTimeObject->addSeconds ($eventRecord ['start_time']);
			$start_datetime = $startDateTimeObject->format ('%Y%m%d%H%M%S');
			$select2 = '*';
			$table2 = 'tx_cal_index';
			$where2 = 'start_datetime >= ' . $now->format ('%Y%m%d%H%M%S') . ' AND event_uid = ' . $calEventUID;
			$orderby2 = 'start_datetime asc';
			$result2 = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select2, $table2, $where2, $orderby2);
			if ($result) {
				$tmp = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result2);
				if (is_array ($tmp)) {
					$start_datetime = $tmp ['start_datetime'];
					$nextOccuranceTime = new tx_cal_date ($tmp ['start_datetime']);
					$nextOccuranceTime->setTZbyId ('UTC');
					$nextOccuranceEndTime = new tx_cal_date ($tmp ['end_datetime']);
					$nextOccuranceEndTime->setTZbyId ('UTC');
					$eventRecord ['start_date'] = $nextOccuranceTime->format ('%Y%m%d');
					$eventRecord ['start_time'] = $nextOccuranceTime->getHour () * 3600 + $nextOccuranceTime->getMinute () * 60 + $nextOccuranceTime->getSecond ();
					$eventRecord ['end_date'] = $nextOccuranceEndTime->format ('%Y%m%d');
					$eventRecord ['end_time'] = $nextOccuranceEndTime->getHour () * 3600 + $nextOccuranceEndTime->getMinute () * 60 + $nextOccuranceEndTime->getSecond ();
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result2);
			}
			
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('scheduler')) {
				
				$scheduler = new \TYPO3\CMS\Scheduler\Scheduler();
				$date = new tx_cal_date ($start_datetime);
				$date->setTZbyId ('UTC');
				$timestamp = $date->getTime ();
				$offsetTime = new tx_cal_date ();
				$offsetTime->copy ($date);
				$offsetTime->setTZbyId ('UTC');
				$offsetTime->addSeconds (- 1 * $offset * 60);
				if ($taskId > 0) {
					if ($offsetTime->isFuture ()) {
						try {
							$task = $scheduler->fetchTask ($taskId);
							$execution = new \TYPO3\CMS\Scheduler\Execution();
							$execution->setStart ($timestamp - ($offset * 60));
							$execution->setIsNewSingleExecution (true);
							$execution->setMultiple (false);
							$execution->setEnd (time () - 1);
							$task->setExecution ($execution);
							$task->setDisabled (false);
							$scheduler->saveTask ($task);
						} catch (OutOfBoundsException $e) {
							$this->createSchedulerTask ($scheduler, $date, $calEventUID, $timestamp, $offset, $row ['uid']);
						}
					} else {
						$this->deleteReminder ($calEventUID);
					}
				} else {
					// taskId == 0 -> schedule task
					$this->createSchedulerTask ($scheduler, $date, $calEventUID, $timestamp, $offset, $row ['uid']);
				}
			} else if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('gabriel')) {
				
				$date = new tx_cal_date ($eventRecord ['start_date'] . '000000');
				$date->setTZbyId ('UTC');
				$date->addSeconds ($eventRecord ['start_time']);
				$timestamp = $date->getTime ();
				
				$monitoringUID = 'tx_cal_fe_user_event_monitor_mm:' . $calEventUID;
				/* Check for existing gabriel events and remove them */
				$this->deleteReminder ($calEventUID);
				
				// No need to remind someone about a past event, but we should delete the existing reminder records
				if ($date->isFuture ()) {
					
					/* Set up the gabriel event */
					$cron = GeneralUtility::getUserObj ('EXT:cal/cron/class.tx_cal_reminder_cron.php:tx_cal_reminder_cron');
					$cron->setUID ($calEventUID);
					
					/* Schedule the gabriel event */
					$cron->registerSingleExecution ($timestamp - ($offset * 60));
					$gabriel = GeneralUtility::getUserObj ('EXT:gabriel/class.tx_gabriel.php:&tx_gabriel');
					$gabriel->addEvent ($cron, $monitoringUID);
				}
			}
		}
	}
	function createSchedulerTask(&$scheduler, $date, $calEventUID, $timestamp, $offset, $uid) {
		if ($date->isFuture ()) {
			/* Set up the scheduler event */
			$task = GeneralUtility::getUserObj ('EXT:cal/cron/class.tx_cal_reminder_scheduler.php:tx_cal_reminder_scheduler');
			$task->setUID ($calEventUID);
			$taskGroup = BackendUtility::getRecordRaw ('tx_scheduler_task_group', 'groupName="cal"');
			if($taskGroup['uid']){
				$task->setTaskGroup($taskGroup['uid']);
			} else {
				$crdate = time ();
				$insertFields = Array ();
				$insertFields ['pid'] = 0;
				$insertFields ['tstamp'] = $crdate;
				$insertFields ['crdate'] = $crdate;
				$insertFields ['cruser_id'] = 0;
				$insertFields ['groupName'] = 'cal';
				$insertFields ['description'] = 'Calendar Base';
				$table = 'tx_scheduler_task_group';
				$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $insertFields);
				$uid = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
				$task->setTaskGroup($uid);
			}
			$task->setDescription('Reminder of a calendar event (id='.$calEventUID.')');
			/* Schedule the event */
			$execution = new \TYPO3\CMS\Scheduler\Execution();
			$execution->setStart ($timestamp - ($offset * 60));
			$execution->setIsNewSingleExecution (true);
			$execution->setMultiple (false);
			$execution->setEnd (time () - 1);
			$task->setExecution ($execution);
			$scheduler->addTask ($task);
			$GLOBALS ['TYPO3_DB']->exec_UPDATEquery ('tx_cal_fe_user_event_monitor_mm', 'uid=' . $uid, Array (
					'schedulerId' => $task->getTaskUid () 
			));
		} else {
		}
	}
	
	/* @todo	Figure out where this should live */
	function deleteReminder($eventUid) {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('scheduler')) {
			$eventRow = BackendUtility::getRecordRaw ('tx_cal_fe_user_event_monitor_mm', 'uid_local=' . $eventUid);
			$taskId = $eventRow ['schedulerId'];
			if ($taskId > 0) {
				$scheduler = new \TYPO3\CMS\Scheduler\Scheduler();
				try {
					$task = $scheduler->fetchTask ($taskId);
					$scheduler->removeTask ($task);
				} catch (OutOfBoundsException $e) {
				}
			}
		} else if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('gabriel')) {
			$monitoringUID = 'tx_cal_fe_user_event_monitor_mm:' . $eventUid;
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ('tx_gabriel', ' crid="' . $eventUid . '"');
		}
	}
	function deleteReminderForEvent($eventUid) {
		// get the related monitoring records
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('uid_local', 'tx_cal_fe_user_event_monitor_mm', 'uid_local = ' . $eventUid);
		while ($monitorRow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			/* Check for existing gabriel events and remove them */
			$this->deleteReminder ($monitorRow ['uid_local']);
		}
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_reminder_view.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_reminder_view.php']);
}
?>