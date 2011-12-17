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

define('ICALENDAR_PATH', 	t3lib_extMgm::extPath('cal').'model/class.tx_model_iCalendar.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

/**
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_tcemain_processdatamap {
	
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$tce) {
		/* If we have an existing calendar event */
		if ($table == 'tx_cal_event' && count($fieldArray)>1) {

			if($fieldArray['start_date']){
				$fieldArray['start_date'] = $this->convertBackendDateToYMD($fieldArray['start_date']);
			}

			if($fieldArray['end_date']){
				$fieldArray['end_date'] = $this->convertBackendDateToYMD($fieldArray['end_date']);
			}
			
			/* If the end date is blank or earlier than the start date */
			if($fieldArray['end_date'] < $fieldArray['start_date']) {
				$fieldArray['end_date'] = $fieldArray['start_date'];
			}
			
			if($fieldArray['until']){
				$fieldArray['until'] = $this->convertBackendDateToYMD($fieldArray['until']);
			}
			
			if($status != 'new'){
				require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
				require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
				$event = t3lib_BEfunc::getRecord ('tx_cal_event', $id);
				
				// Do to our JS, these values get recalculated each time, but they may not have changed!
				if($event['start_date']==$fieldArray['start_date']){
					unset($fieldArray['start_date']);
				}
				if($event['end_date']==$fieldArray['end_date']){
					unset($fieldArray['end_date']);
				}
				if($event['until']==$fieldArray['until']){
					unset($fieldArray['until']);
				}
				/* If we're in a workspace, don't notify anyone about the event */
				if($event['pid'] > 0 && count($fieldArray)>1) {
					if($fieldArray['calendar_id'] && $event['calendar_id'] != $fieldArray['calendar_id']){
						$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event_category_mm', 'uid_local='.intval($id));
					}
				
					/* Check Page TSConfig for a preview page that we should use */
					$pageTSConf = t3lib_befunc::getPagesTSconfig($event['pid']);
					if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
						$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
					} else {
						$pageIDForPlugin = $event['pid'];
					}
					
					// @todo	Should we have an else case for notifying when the doktype is 254?
					$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), 'doktype');
					if($page['doktype'] != 254) {
						/* Notify of changes to existing event */
						$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
						$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);
						
						$notificationService =& tx_cal_functions::getNotificationService();
						
						if ($notificationService->conf['view.']['event.']['phpicalendarEventTemplate']) {
							$oldPath = &$notificationService->conf['view.']['event.']['phpicalendarEventTemplate'];
						} else {
							$oldPath = &$notificationService->conf['view.']['event.']['eventModelTemplate'];
						}
						$extPath=t3lib_extMgm::extPath('cal');
					
						$oldPath = str_replace('EXT:cal/', $extPath, $oldPath);
						//$oldPath = str_replace(PATH_site, '', $oldPath);
						$tx_cal_api->conf['view.']['event.']['phpicalendarEventTemplate'] = $oldPath;
						$tx_cal_api->conf['view.']['event.']['eventModelTemplate'] = $oldPath;
						$oldBackPath = $GLOBALS['TSFE']->tmpl->getFileName_backPath;
						$GLOBALS['TSFE']->tmpl->getFileName_backPath = '';
						$fileInfo = t3lib_div::split_fileref($oldPath);
						$GLOBALS['TSFE']->tmpl->allowedPaths[] = $fileInfo['path'];
						
						
						$notificationService->controller->getDateTimeObject = new tx_cal_date($event['start_date'].'000000');
						$notificationService->notifyOfChanges($event, $fieldArray);
						if($fieldArray['send_invitation']){
							$notificationService->invite($event);
							$fieldArray['send_invitation'] = 0;
						}
						
						$GLOBALS['TSFE']->tmpl->getFileName_backPath = $oldBackPath;
					}
				}
			}
   		}
   		
		if ($table == 'tx_cal_exception_event' && count($fieldArray)>1) {

			if($fieldArray['start_date']){
				$fieldArray['start_date'] = $this->convertBackendDateToYMD($fieldArray['start_date']);
			}
			
			if($fieldArray['end_date']){
				$fieldArray['end_date'] = $this->convertBackendDateToYMD($fieldArray['end_date']);
			}
			
			/* If the end date is blank or earlier than the start date */
			if($fieldArray['end_date'] < $fieldArray['start_date']) {
				$fieldArray['end_date'] = $fieldArray['start_date'];
			}
			
			if($fieldArray['until']){
				$fieldArray['until'] = $this->convertBackendDateToYMD($fieldArray['until']);
			}
		}
		
		/* If we're working with a calendar and an ICS file or URL has been posted, try to import it */
		if($table == 'tx_cal_calendar') {
			$calendar = t3lib_BEfunc::getRecord ('tx_cal_calendar', $id);
			
			require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
			$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
			
			if($calendar['type'] == 1 or $calendar['type'] == 2) {
				tx_cal_tcemain_processdatamap::processICS($calendar, $fieldArray, $service);
			}

		}
		
		if ($table == 'tx_cal_fe_user_event_monitor_mm') {
			$values = explode('_',$fieldArray['uid_foreign']);
			$fieldArray['uid_foreign'] = array_pop($values);
			$fieldArray['tablenames'] = implode('_',$values);
		}
		
		if ($table == 'tx_cal_location' && count($fieldArray) > 0 && t3lib_extMgm::isLoaded('wec_map')) {
			$location = t3lib_BEfunc::getRecord ('tx_cal_location', $id);
			if(is_array($location)){
				$location = array_merge($location,$fieldArray);
			} else {
				$location = $fieldArray;
			}

			/* Geocode the address */
			$lookupTable = t3lib_div::makeInstance('tx_wecmap_cache');
			$latlong = $lookupTable->lookup($location['street'], $location['city'], $location['state'], $location['zip'], $location['country']);
			$fieldArray['latitude'] = $latlong['lat'];
			$fieldArray['longitude'] = $latlong['long'];
		}
		
	}
	
	function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$tcemain) {
		
		/* If we have a new calendar event */
		if (($table == 'tx_cal_event' || $table == 'tx_cal_exception_event') && count($fieldArray)>1) {
			require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
			require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
			$event = t3lib_BEfunc::getRecord ($table, $status=='new'?$tcemain->substNEWwithIDs[$id]:$id);
			
			/* If we're in a workspace, don't notify anyone about the event */
			if($event['pid'] > 0) {
				/* Check Page TSConfig for a preview page that we should use */
				$pageTSConf = t3lib_befunc::getPagesTSconfig($event['pid']);
				if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
					$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
				} else {
					$pageIDForPlugin = $event['pid'];
				}
			
				$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), 'doktype');

				if($page['doktype'] != 254) {
					$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
					$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);

					if($event['event_type']==3 && !$event['ref_event_id']){
						$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
						$modelObj->updateEventAttendees($event['uid'],'tx_cal_phpicalendar');
					}

					if($table == 'tx_cal_event' && ($status=='new' || $fieldArray['send_invitation'])){				
						/* Notify of new event */
						$notificationService =& tx_cal_functions::getNotificationService();
						
						if ($notificationService->conf['view.']['event.']['phpicalendarEventTemplate']) {
							$oldPath = &$notificationService->conf['view.']['event.']['phpicalendarEventTemplate'];
						} else {
							$oldPath = &$notificationService->conf['view.']['event.']['eventModelTemplate'];
						}
						$extPath=t3lib_extMgm::extPath('cal');
					
						$oldPath = str_replace('EXT:cal/', $extPath, $oldPath);
						//$oldPath = str_replace(PATH_site, '', $oldPath);
						$tx_cal_api->conf['view.']['event.']['phpicalendarEventTemplate'] = $oldPath;
						$tx_cal_api->conf['view.']['event.']['eventModelTemplate'] = $oldPath;
						$oldBackPath = $GLOBALS['TSFE']->tmpl->getFileName_backPath;
						$GLOBALS['TSFE']->tmpl->getFileName_backPath = '';
						$fileInfo = t3lib_div::split_fileref($oldPath);
						$GLOBALS['TSFE']->tmpl->allowedPaths[] = $fileInfo['path'];
						
						$notificationService->controller->getDateTimeObject = new tx_cal_date($event['start_date'].'000000');
						
						if($status=='new'){
							$notificationService->notify($event);
						}
						if($fieldArray['send_invitation']){
							$notificationService->invite($fieldArray);
							$fieldArray['send_invitation'] = 0;
						}
						
						$GLOBALS['TSFE']->tmpl->getFileName_backPath = $oldBackPath;
					}

					$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
					if($extConf['useNewRecurringModel']){
						require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
						$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',$pageIDForPlugin);
						$rgc->generateIndexForUid($event['uid'], $table);
					}
					
					if($table == 'tx_cal_event' && $tx_cal_api->conf['view.']['event.']['remind']){
						/* Schedule reminders for new and changed events */
						$reminderService = &tx_cal_functions::getReminderService();
						$reminderService->scheduleReminder($event['uid']);
					}
				}
			}
		} 
		if ($table == 'pages' && $status == 'new' ) {
			$GLOBALS['BE_USER']->setAndSaveSessionData('cal_itemsProcFunc', array() );
		}
	}
	
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$tce) {
		
		/**
		 * Demo code for using TCE to do custom validation of form elements.  The record is still
		 * saved but a bad combination of start date and end date will generate an error message.
		 */
		/*
		if($table == 'tx_cal_event') {
			$startTimestamp = $incomingFieldArray['start_date'] + $incomingFieldArray['start_time'];
			$endTimestamp = $incomingFieldArray['end_date'] + $incomingFieldArray['end_time'];
			
			if ($startTimestamp > $endTimestamp) {
				$tce->log('tx_cal_event', 2, $id, 0, 1, "Event end (".t3lib_BEfunc::datetime($endTimestamp).") is earlier than event start (".t3lib_BEfunc::datetime($startTimestamp).").", 1);
			}
		}
		*/

		/* preview events on eventViewPid on "save and preview" calls. but only if it's a regular event and the user is in live workspace */
		if ($table == 'tx_cal_event' && isset($GLOBALS['_POST']['_savedokview_x']) && !$fieldArray['type'] && !$GLOBALS['BE_USER']->workspace)	{
			$pagesTSConfig = t3lib_BEfunc::getPagesTSconfig($GLOBALS['_POST']['popViewId']);
			if ($pagesTSConfig['options.']['tx_cal_controller.']['eventViewPid']) {
				$GLOBALS['_POST']['popViewId_addParams'] = ($fieldArray['sys_language_uid']>0?'&L='.$fieldArray['sys_language_uid']:'').'&no_cache=1&tx_cal_controller[view]=event&tx_cal_controller[type]=tx_cal_phpicalendar&tx_cal_controller[uid]='.$id;
				$GLOBALS['_POST']['popViewId'] = $pagesTSConfig['options.']['tx_cal_controller.']['eventViewPid'];
			}
		}

		if($table == 'tx_cal_event' || $table =="tx_cal_exeption_event") {
			
			$event = t3lib_BEfunc::getRecord($table, $id);
			if(intval($event['start_date'])==0 ){
				return;
			}

			/**
			 * If we have an event, check if a start and end time have been sent.
			 * If both are 0, then its an all day event.
			 */
			if (array_key_exists('start_time', $incomingFieldArray) && array_key_exists('end_time', $incomingFieldArray) &&
				$incomingFieldArray['start_time'] == 0 && $incomingFieldArray['end_time'] == 0) {
					
				$incomingFieldArray['allday'] = 1;
			}
			
			/** 
			 * If the recurring frequency has changed and recurrence rules are not
			 * already set, preset a reasonable value based on event start date/time.
			 * @todo 	Default date calculations do not take any timezone information into account.
			 */
			if($incomingFieldArray['freq'] != $event['freq']) {
				$date = $this->convertBackendDateToPear($incomingFieldArray['start_date']);
				$date->addSeconds($incomingFieldArray['start_time']);
				$dayArray = tx_cal_tcemain_processdatamap::getWeekdayOccurrence($date);
			
				/* If we're on the 4th occurrence or later, let's assume we want the last occurrence */
				if($dayArray[0] >= 4) {
					$dayArray[0] = -1;
				}
			
				switch($incomingFieldArray['freq']) {
					case 'week': /* Default Value = Day of the week when event starts. */
						if(!$incomingFieldArray['byday'] && !$event['byday']) {
							$incomingFieldArray['byday'] = strtolower($date->getDayName(true,2));
						}
						break;
					case 'month': /* Default Value = Day of the week and weekday occurrence when event starts */
						if(!$incomingFieldArray['byday'] && !$event['byday']) {
							$incomingFieldArray['byday'] = $dayArray[0].strtolower(substr($dayArray[1], 0, 2));
						}
						break;
					case 'year': /* Default Value = Day of the month and month when event starts */
						if(!$incomingFieldArray['bymonthday'] && !$event['bymonthday']) {
							$incomingFieldArray['bymonthday'] = $date->getDay();
						}
						
						if(!$incomingFieldArray['bymonth'] && !$event['bymonth']) {
							$incomingFieldArray['bymonth'] = $date->getMonth();
						}
						break;
				}
			}
		}
		
		if($table == 'tx_cal_category' && array_key_exists('calendar_id',$incomingFieldArray) && !strstr($id,'NEW')){
			$category = t3lib_BEfunc::getRecord ('tx_cal_category', $id);
			if($incomingFieldArray['calendar_id']!=$category['calendar_id']){
				$incomingFieldArray['parent_category']=0;
			}
		}
		
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
					if($calendar['type']!=0){
						$service->deleteTemporaryEvents($id);
						
						$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
						if($extConf['useNewRecurringModel']){
							require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
							tx_cal_recurrence_generator::cleanIndexTableOfCalendarUid($id);
						}
					}
   				break;
   				case 1: /* External URL or ICS file*/
   				case 2: /* ICS File */
					tx_cal_tcemain_processdatamap::processICS($calendar, $incomingFieldArray, $service);
				break;
   			}
		}
		
		if($table == 'tx_cal_exception_event_group' && !strstr($id,'NEW')){
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($extConf['useNewRecurringModel']){
				require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
				require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
				$exceptionEvent = t3lib_BEfunc::getRecord ('tx_cal_exception_event_group', $id);
				
				/* If we're in a workspace, don't notify anyone about the event */
				if($exceptionEvent['pid'] > 0) {
					/* Check Page TSConfig for a preview page that we should use */
					$pageTSConf = t3lib_befunc::getPagesTSconfig($exceptionEvent['pid']);
					if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
						$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
					} else {
						$pageIDForPlugin = $exceptionEvent['pid'];
					}
				
					$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), "doktype");
	
					if($page['doktype'] != 254) {
						$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
						$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pageIDForPlugin);
						require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
						tx_cal_recurrence_generator::cleanIndexTableOfExceptionGroupUid($id);
					}
				}
			}
		}
		
		if($table == 'tx_cal_attendee'){
			$incomingFieldArray['fe_user_id'] = str_replace(Array(',','fe_users_'),Array('',''),$incomingFieldArray['fe_user_id']);
			$incomingFieldArray['fe_group_id'] = str_replace(Array(',','fe_groups_'),Array('',''),$incomingFieldArray['fe_group_id']);
			if($incomingFieldArray['fe_group_id'] > 0){
				$subType = 'getGroupsFE';
				$groups = array(0);
				$serviceObj = null;
				$serviceObj = t3lib_div::makeInstanceService('auth', $subType);
				if($serviceObj == null){
					return;
				}
				
				$serviceObj->getSubGroups($incomingFieldArray['fe_group_id'],'',$groups);
				unset($incomingFieldArray['fe_group_id']);
					
				$select = 'DISTINCT fe_users.*';
				$table = 'fe_groups, fe_users';
				$where = 'fe_groups.uid IN ('.implode(',',$groups).') 
						AND FIND_IN_SET(fe_groups.uid, fe_users.usergroup)
						AND fe_users.email != \'\' 
						AND fe_groups.deleted = 0 
						AND fe_groups.hidden = 0 
						AND fe_users.disable = 0
						AND fe_users.deleted = 0';
				$result2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
				$attendeeUids = Array();
				while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
					$incomingFieldArray['fe_user_id'] = $row2['fe_users.uid'];
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_attendee', $incomingFieldArray);
					$attendeeUids[] = $GLOBALS['TYPO3_DB']->sql_insert_id();
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result2);
				//$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event', $incomingFieldArray);
				
				foreach($tce->datamap['tx_cal_event'] as $eventUid => $eventArray){
					$eventArray['attendee'] = array_unique(array_merge(t3lib_div::trimExplode(',',$eventArray['attendee'],1),$attendeeUids));
				}
			}
			unset($incomingFieldArray['fe_group_id']);
		}
	}
	
	function processICS($calendar, &$fieldArray, &$service) {
		if($fieldArray['ics_file'] or $fieldArray['ext_url']) {
			if($fieldArray['ics_file']) {
				$url = t3lib_div::getFileAbsFileName('uploads/tx_cal/ics/'.$fieldArray['ics_file']);
			} elseif($fieldArray['ext_url']) {
				$fieldArray['ext_url'] = trim($fieldArray['ext_url']);
				$url = $fieldArray['ext_url'];
			}
			
			$newMD5 = $service->updateEvents($calendar['uid'], $calendar['pid'], $url, $calendar['md5'], $calendar['cruser_id']);

			if($newMD5) {
				$fieldArray['md5'] = $newMD5;
				$pageTSConf = t3lib_befunc::getPagesTSconfig($calendar['pid']);
				if($pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin']) {
					$pageIDForPlugin = $pageTSConf['options.']['tx_cal_controller.']['pageIDForPlugin'];
				} else {
					$pageIDForPlugin = $calendar['pid'];
				}
			
				$page = t3lib_BEfunc::getRecord('pages', intval($pageIDForPlugin), "doktype");
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				if($extConf['useNewRecurringModel'] && $page['doktype'] != 254) {
					require_once(t3lib_extMgm::extPath('cal').'mod1/class.tx_cal_recurrence_generator.php');
					$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',$pageIDForPlugin);
					$rgc->generateIndexForCalendarUid($calendar['uid']);
				}
			}
			
			
			$service->scheduleUpdates($fieldArray['refresh'], $calendar['uid']);
		}
	}
	
	function getWeekdayOccurrence($date) { 
		return array(ceil($date->getDay() / 7), $date->getDayName());	
	}
	
	/**
	 * Converts a date from the backend (m-d-Y or d-m-Y) into a PEAR Date object.
	 *
	 * @param		string		The date to convert.
	 * @return		object		The date object.
	 */
	function convertBackendDateToPear($dateString) {
		$ymdString = $this->convertBackendDateToYMD($dateString);
		return new tx_cal_date($ymdString.'000000');
	}
	
	/**
	 * Converts a date from the backend (m-d-Y or d-m-Y or in TYPO3 v. >= 4.3 timestamp) into the Ymd format.
	 *
	 * @param		string		The date to convert.
	 * @return		string		The date in Ymd format.
	 */
	function convertBackendDateToYMD($dateString) {
		if (t3lib_div::int_from_ver(TYPO3_version) < 4003000){
			
			// simple fallback conversion if JS fails for some reason
			$dateString = strtr($dateString,' ;.:_=/\\','--------');
			$dateArray = explode('-',$dateString);
			if(count($dateArray) > 1) {
				if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] == '1'){
					$ymdString = sprintf("%04d", $dateArray[2]).sprintf("%02d", $dateArray[0]).sprintf("%02d", $dateArray[1]);
				}else{
					$ymdString = sprintf("%04d", $dateArray[2]).sprintf("%02d", $dateArray[1]).sprintf("%02d", $dateArray[0]);
				}
			} else {
				// We already had a YMD string
				$ymdString = $dateString;
			}
			return $ymdString;
		}
		$date = new tx_cal_date($dateString);
		return $date->format('%Y%m%d');
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processdatamap.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processdatamap.php']);
}
?>