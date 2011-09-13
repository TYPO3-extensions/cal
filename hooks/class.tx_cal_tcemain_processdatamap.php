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

define('ICALENDAR_PATH', 	t3lib_extMgm::extPath('cal').'model/class.tx_model_iCalendar.php');

/**
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_tcemain_processdatamap {
	
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$this) {
		
		/* If we have an existing calendar event */
		if ($table == 'tx_cal_event' && count($fieldArray)>1) {
						
			if($status != 'new'){
				require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
				$event = t3lib_BEfunc::getRecord ('tx_cal_event', $id);
				$pageTSConf = t3lib_befunc::getPagesTSconfig($event['pid']);
				
				/* Notify of changes to existing event */
				$notificationService =& getNotificationService();
				$notificationService->notifyOfChanges($event, $fieldArray, $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['notify.']);
			}			
   		} 
	}
	
	function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$tcemain) {
		
		/* If we have a new calendar event */
		if ($table == 'tx_cal_event' && count($fieldArray)>1) {
			require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
			$event = t3lib_BEfunc::getRecord ('tx_cal_event', $status=='new'?$tcemain->substNEWwithIDs[$id]:$id);
			
			$pageTSConf = t3lib_befunc::getPagesTSconfig($event['pid']);
			
			if($status=='new'){				
				/* Notify of new event */
				$notificationService =& getNotificationService();
				$notificationService->notify($event, $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['notify.']);
			}
			
			/* Schedule reminders for new and changed events */
			$offset = is_numeric($pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['remind.']['time']) ? $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['remind.']['time'] * 60 : 0;
			$reminderTimestamp = $event['start_date'] + $event['start_time'] - $offset;
			$reminderService = &getReminderService();
			$reminderService->scheduleReminder($event['uid'], $reminderTimestamp);
		} 
	}
	
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$this) {		
		/* If an existing calendar is updated */
		if($table == 'tx_cal_calendar' && array_key_exists('type',$incomingFieldArray) && !strstr($id,'NEW')){
   			
			/* Get the calendar info from the db */
			$calendar = t3lib_BEfunc::getRecord ('tx_cal_calendar', $id);
						
			require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
			$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
			
   			// Here we have to check if the calendar belongs to the type
   			// problem with case 2 & 3 -> what to do with events of type database? delete them without warning? keep them and assign them to a default category?
   			switch ($incomingFieldArray['type']){
   				case 0: /* Standard */
					/* Delete any temporary events previously associated with this calendar */
					$service->deleteTemporaryEvents($id);
   				break;
   				case 1: /* External URL */
					$url = $incomingFieldArray['ext_url'];
					
					$newMD5 = $service->updateEvents($id, $calendar['pid'], $url, $calendar['md5'], $calendar['cruser_id']);
					if($newMD5) {
						$incomingFieldArray['md5'] = $newMD5;						
					}
						
					$service->scheduleUpdates($incomingFieldArray['refresh'], $id);
   				break;
   				case 2: /* ICS File */
					$url = t3lib_div::getFileAbsFileName('uploads/tx_cal/ics/'.$incomingFieldArray['ics_file']);

					$newMD5 = $service->updateEvents($id, $calendar['pid'], $url, $calendar['md5'], $calendar['cruser_id']);
					if($newMD5) {
						$incomingFieldArray['md5'] = $newMD5;						
					}
						
					$service->scheduleUpdates($incomingFieldArray['refresh'], $id);
				break;
   			}
		}
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processdatamap.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processdatamap.php']);
}
?>