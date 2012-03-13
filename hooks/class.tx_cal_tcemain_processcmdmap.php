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


/**
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_tcemain_processcmdmap {
	function processCmdmap_postProcess(&$command, &$table, &$id, &$value, &$tce) {
		switch($table) {
			case 'tx_cal_event' :
				$select = '*';
				$table = 'tx_cal_event';
				$where = 'uid = '.$id;
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
				if($result) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
						require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
						
						/* If we're in a workspace, don't notify anyone about the event */
						if($row['pid'] > 0) {
							/* Check Page TSConfig for a preview page that we should use */
							$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
							if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
								$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
							} else {
								$pageIDForPlugin = $row['pid'];
							}
						
							$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), "doktype");
							if($page['doktype'] != 254) {
								$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
								$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);
				
								$notificationService =& tx_cal_functions::getNotificationService();
								if($command=='delete'){
									/* If the deleted event is temporary, reset the MD5 of the parent calendar */
									if($row['isTemp']) {
										$calendar_id = $row['calendar_id'];
										$insertFields = array('md5' => '');
										$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_calendar','uid='.$calendar_id, $insertFields);
									}
									
									$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
									if($extConf['useNewRecurringModel']){
										require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
										tx_cal_recurrence_generator::cleanIndexTableOfUid($id,$table);
									}
									
									/* Delete all deviations of the event */
									$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event_deviation','parentid='.$id);
	
								}else{
									$notificationService->notifyOfChanges($row, array($command => $value));
								}
							}
						}
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($result);
				}
			break;
			case 'tx_cal_calendar' :
				/* If a calendar has been deleted, we might need to clean up. */

				if($command == 'delete') {
					/* Using getRecordRaw rather than getRecord since the record has already been deleted. */
					$calendarRow = t3lib_BEfunc::getRecordRaw('tx_cal_calendar', 'uid='.$id);					
					/* If the calendar is an External URL or ICS file, then we need to clean up */
					if (($calendarRow['type'] == 1) or ($calendarRow['type'] == 2)) {
						require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
						$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
						$service->deleteTemporaryEvents($id);
						$service->deleteTemporaryCategories($id);
						$service->deleteScheduledUpdates($id);
					}
				}
			break;
			case 'tx_cal_exception_event_group' :
			case 'tx_cal_exception_event' :
				$select = '*';
				$where = 'uid = '.$id;
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
				if($result) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
						require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
						
						/* If we're in a workspace, don't notify anyone about the event */
						if($row['pid'] > 0) {
							/* Check Page TSConfig for a preview page that we should use */
							$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
							if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
								$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
							} else {
								$pageIDForPlugin = $row['pid'];
							}
						
							$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), "doktype");
							if($page['doktype'] != 254) {
								$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
								$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);
				
								if($command=='delete'){
									$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
									if($extConf['useNewRecurringModel']){
										require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
										tx_cal_recurrence_generator::cleanIndexTableOfUid($id,$table);
									}
	
								}
							}
						}
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($result);
				}
			break;
		}
	}
	
	
	function processCmdmap_preProcess(&$command, &$table, &$id, &$value, &$tce) {
		switch($table) {
			case 'tx_cal_event' :
				if($command == 'delete'){

					$select = '*';
					$table = 'tx_cal_event';
					$where = 'uid = '.$id;
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
					if($result) {
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
							require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
							require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
							
							/* If we're in a workspace, don't notify anyone about the event */
							if($row['pid'] > 0) {
								/* Check Page TSConfig for a preview page that we should use */
								$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
								if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
									$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
								} else {
									$pageIDForPlugin = $row['pid'];
								}
							
								$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), "doktype");
								if($page['doktype'] != 254) {
									
									$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
									$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);
					
									$notificationService =& tx_cal_functions::getNotificationService();
									// Need to enforce deletion mode 
									$notificationService->notify($row,1);
								}
							}
						}
					}
					// We have to delete the gabriel/scheduler events BEFORE the tx_cal_events and
					// its related tx_cal_fe_user_event_monitor_mm records are gone

					/* Clean up any pending reminders for this event */
					$reminderService = &tx_cal_functions::getReminderService();
					try {
						$reminderService->deleteReminderForEvent($id);
					} catch (OutOfBoundsException $e){
						
					}
				}
				break;
			case 'tx_cal_fe_user_event_monitor_mm':
				if($command == 'delete'){
					$relationRecord = t3lib_BEfunc::getRecord ('tx_cal_fe_user_event_monitor_mm', $id);
					// We have to delete the gabriel events BEFORE the tx_cal_events and
					// its related tx_cal_fe_user_event_monitor_mm records are gone
					require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
					/* Clean up any pending reminders for this event */
					$reminderService = &tx_cal_functions::getReminderService();
					try {
						$reminderService->deleteReminder($relationRecord['uid_local']);
					} catch (OutOfBoundsException $e){
						
					}
				}
				break;
				break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processcmdmap.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processcmdmap.php']);
}
?>