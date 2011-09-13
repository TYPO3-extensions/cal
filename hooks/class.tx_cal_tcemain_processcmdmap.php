<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2004 
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
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
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
					require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
					$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
					
					$tx_cal_api = t3lib_div :: makeInstanceClassName('tx_cal_api');
					$tx_cal_api = new $tx_cal_api();
					$tx_cal_api = &$tx_cal_api->tx_cal_api_without($row['pid']);
					
					$notificationService =& getNotificationService();
					if($command=='delete'){
						$row['deleted'] = $value;
						$notificationService->notify($row);
						
						/* Clean up any pending reminders for this event */
						$reminderService = &getReminderService();
						$reminderService->deleteReminder($row['uid']);
						
						/* If the deleted event is temporary, reset the MD5 of the parent calendar */
						if($row['isTemp']) {
							$calendar_id = $row['calendar_id'];
							$insertFields = array('md5' => '');
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_calendar','uid='.$calendar_id, $insertFields);
						}

					}else{
						$notificationService->notifyOfChanges($row, array($command => $value));
					}
				}
			break;
			case 'tx_cal_calendar' :
				/* If a calendar has been deleted, we might need to clean up. */
				if($command == 'delete') {
					/* Using getRecordRaw rather than getRecord since the record has already been deleted. */
					$calendarRow = t3lib_BEfunc::getRecordRaw('tx_cal_calendar', $id);
					
					/* If the calendar is an External URL or ICS file, then we need to clean up */
					if (($calendarRow['type'] == 1) or ($calendarRow['type'] == 2)) {
						require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
						$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
						$service->deleteTemporaryEvents($id);
						$service->deleteScheduledUpdates($id);
					}
				}
			break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processcmdmap.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processcmdmap.php']);
}
?>