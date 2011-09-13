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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
define('ICALENDAR_PATH', t3lib_extMgm::extPath('cal').'model/class.tx_model_iCalendar.php');

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_icalendar_service extends tx_cal_base_service {
		
	/**
	 * Looks for an external calendar with a given uid on a certain pid-list
	 * @param	integer		$uid		The uid to search for
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array		An array ($row)
	 */
	function find($uid, $pidList=''){
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' type IN (1,2) AND uid='.$uid.' '.$this->cObj->enableFields('tx_cal_calendar'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' type IN (1,2) pid IN ('.$pidList.') AND uid='.$uid.' '.$this->cObj->enableFields('tx_cal_calendar'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			return $row;
		}
	}
	
	
	/**
	 * Looks for all external calendars on a certain pid-list
	 * @param	string		$pidList	The pid-list to search in
	 * @return	array	An array of array (array of $rows)
	 */
	function findAll($pidList){
		$return = array();
		if($pidList==''){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' type IN (2,3) '.$this->cObj->enableFields('tx_cal_calendar'));
		}else{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', ' type IN (2,3) pid IN ('.$pidList.') '.$this->cObj->enableFields('tx_cal_calendar'));
		}
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
			$return[] = $row;
		}
		return $return;
	}
	
	
	function saveEvents($url) {
		/* Get the contents of the URL and calculate a checksum of those contents */
		$contents = t3lib_div::getURL($url);
		$md5 = md5($contents);
		
		/* Parse the contents into ICS data structure. */
		$iCalendar = $this->getiCalendarFromIcsFile($contents);
		
		/* Create events belonging to the specified calendar */
		/* @todo	Where do other arguments come from? */
		$this->insertCalEventsIntoDB($iCalendar, $calendar_id, $pid, $cruser_id, $md5);
	}
	
	function update($uid) {
		$calendar = $this->find($uid);
		
		if($calendar['type'] == 2) {
			$url = t3lib_div::getFileAbsFileName('uploads/tx_cal/ics/'.$calendar['ics_file']);
		} else {
			$url = $calendar['ext_url'];
		}
				
		$newMD5 = $this->updateEvents($uid, $calendar['pid'], $url, $calendar['md5'], $calendar['cruser_id']);
		
		/* If the events changed, update the calendar in the DB */
		if($newMD5) {
			/* Update the calendar */
			$insertFields = array('tstamp' => time(), 'md5' => $newMD5);
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_calendar','uid='.$uid, $insertFields);
		}
	}
	
	/* 
	 * Updates an existing calendar
	 *
	 */
	function updateEvents($uid, $pid, $url, $md5, $cruser_id) {
		/* If the calendar has a URL, get a checksum on the contents */
		if($url != '') {
			$contents = t3lib_div::getURL($url);
			$newMD5 = md5($contents);
		}

		/* If the calendar has changed */
		if($newMD5 != $md5) {
			/* Delete old events */
			$this->deleteTemporaryEvents($uid);
			
			/* Parse the contents into ICS data structure */
			$iCalendar = $this->getiCalendarFromICSFile($contents);

			/* Create new events belonging to the specified calendar */
			$this->insertCalEventsIntoDB($iCalendar->_components, $uid, $pid, $cruser_id);
			
			return $newMD5;
		} else {
			return false;
		}
	}
	
	/*
	 * Schedules future updates using the gabriel scheduling engine.
	 * @param	integer		Frequency (in minutes) between calendar updates.
	 * @param 	integer		UID of the calendar to be updated.
	 * @param 	integer		URL of the calendar to be updated.
	 * @return	none
	 */
	function scheduleUpdates($refreshInterval, $uid) {
		if (t3lib_extMgm::isLoaded('gabriel')) {
			$eventUID = 'tx_cal_calendar:'.$uid;
		
			/* Check for existing gabriel events and remove them */
			$this->deleteScheduledUpdates($uid);
		
			/* If calendar has a refresh time, schedule recurring gabriel event for refresh */
			$recurring = $refreshInterval * 60;
			if($recurring) {
				/* Set up the gabriel event */
				$cron = t3lib_div::getUserObj('EXT:cal/cron/class.tx_cal_calendar_cron.php:tx_cal_calendar_cron');
				$cron->setUID($uid);

				/* Schedule the gabriel event */ 
				$cron->registerRecurringExecution(time()+$recurring,$recurring,strtotime('+10 years'));
				$gabriel = t3lib_div::getUserObj('EXT:gabriel/class.tx_gabriel.php:&tx_gabriel');
				$gabriel->addEvent($cron,$eventUID);
			}
		}
	}
	
	function deleteScheduledUpdates($uid) {
		if (t3lib_extMgm::isLoaded('gabriel')) {
			$eventUID = 'tx_cal_calendar:'.$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_gabriel',' crid="'.$eventUID.'"');
		}
	}

	/*
	 * Deletes temporary events on a given calendar.
	 * @param	integer 	The uid of the calendar.
	 * @param	integer		The pid of the calendar.
	 * @return	none
	 */
	function deleteTemporaryEvents($uid) {
		/* Delete the calendar events */
		$where = ' calendar_id='.$uid.' AND isTemp=1';
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event', $where);
		
		/* Delete the relations */
		$where = ' uid_local='.$uid;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_event_category_mm', $where);
		
		/* Delete the categories */
		$where = ' calendar_id='.$uid;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_category', $where);

		/* Delete any scheduled events (tasks) in gabriel */
		$this->deleteScheduledUpdates($uid);
	}

	/**
	 * @param	String	Text of an ics file
	 * @return	object	tx_model_iCalendar object
	 */
	function getiCalendarFromIcsFile($text){
		require_once(ICALENDAR_PATH);
		$iCalendar = new tx_model_iCalendar();
		$boolean = $iCalendar->parsevCalendar($text);
		return $iCalendar;
	}

	/**
	 * @param	array	iCalendar component array
	 */
	function insertCalEventsIntoDB($iCalendarComponentArray=array(), $calId, $pid='', $cruserId=''){
		if(empty($iCalendarComponentArray)){
			return;
		}
		$table = 'tx_cal_event';
		$offsetArray = array();
		foreach($iCalendarComponentArray as $component){

			$insertFields = array();
			$insertFields['isTemp']=1;
			$insertFields['tstamp']=time();
			$insertFields['crdate']=time();
			$insertFields['pid']=$pid;
			if (is_a($component,'tx_iCalendar_vtimezone')){
				foreach($component->_components as $tzComponent){
					$to = $tzComponent->getAttribute('TZOFFSETTO');
					$i = +1;
					if($to['ahead']==1){
						$i = -1;
					}
					$offsetArray[$component->getAttribute('TZID')][$tzComponent->getAttribute('DTSTART')] = $i * ($to['hour']*3600 + $to['minute']*60); 				
				}
			}
			if (is_a($component,'tx_iCalendar_vevent')){

				$insertFields['cruser_id'] = $cruserId;
				$insertFields['calendar_id'] = $calId;
				if($component->getAttribute('DTSTART')){
					$startdate = $component->getAttribute('DTSTART');
					if($component->getAttributeParameters('DTSTART')){
						$tz = $component->getAttributeParameters('DTSTART');
						if(is_array($offsetArray[$tz['TZID']])){
							$offsetValue = 0;
							foreach($offsetArray[$tz['TZID']] as $tzperiod => $value){
								if($tzperiod < $startdate){
									$offsetValue = $value;
								}
							}
							$startdate += $offsetValue;
						}
					}
					if(is_array($startdate)){
						$insertFields['start_date'] = gmmktime(0,0,0,$startdate['month'],$startdate['mday'],$startdate['year']);
						$insertFields['start_date'] += strtotimeOffset($insertFields['start_date']);
					}else{
						$startdate += strtotimeOffset($startdate);
						$year = gmdate('Y',$startdate);
						$month = gmdate('m',$startdate);
						$mday = gmdate('d',$startdate);
						$hour = gmdate('H',$startdate);
						$min = gmdate('i',$startdate);
						
						$insertFields['start_date'] = gmmktime(0,0,0,$month,$mday,$year);
						$insertFields['start_time'] = gmmktime($hour,$min,0,0,0,1)-975538800; // seconds of one year
					}				
				}else{
					continue;
				}
				if($component->getAttribute('DTEND')){
					$enddate = $component->getAttribute('DTEND');
					if($component->getAttributeParameters('DTSTART')){
						$tz = $component->getAttributeParameters('DTSTART');
						if(is_array($offsetArray[$tz['TZID']])){
							$offsetValue = 0;
							foreach($offsetArray[$tz['TZID']] as $tzperiod => $value){
								if($tzperiod < $startdate){
									$offsetValue = $value;
								}
							}
							$enddate += $offsetValue;
						}
					}
					if(is_array($enddate)){
						$insertFields['end_date'] = gmmktime(0,0,0,$enddate['month'],$enddate['mday'],$enddate['year']);
						$insertFields['end_date'] += strtotimeOffset($insertFields['end_date']);
					}else{
						$enddate += strtotimeOffset($enddate);
						$year = gmdate('Y',$enddate);
						$month = gmdate('m',$enddate);
						$mday = gmdate('d',$enddate);
						$hour = gmdate('H',$enddate);
						$min = gmdate('i',$enddate);
						$insertFields['end_date'] = gmmktime(0,0,0,$month,$mday,$year);
						$insertFields['end_time'] = gmmktime($hour,$min,0,0,0,1)-975538800; // seconds of one year
					}				
				}
				if($component->getAttribute('DURATION')){
					$enddate = $insertFields['start_date']+$component->getAttribute('DURATION');
					$enddate += strtotimeOffset($enddate);

					$year = gmdate('Y',$enddate);
					$month = gmdate('m',$enddate);
					$mday = gmdate('d',$enddate);
					$hour = gmdate('H',$enddate);
					$min = gmdate('i',$enddate);
					$insertFields['end_date'] = gmmktime(0,0,0,$month,$mday,$year);
					$insertFields['end_time'] = $insertFields['start_time'] + $component->getAttribute('DURATION');
				}

				$insertFields['title'] = $component->getAttribute('SUMMARY');
//				$insertFields['organizer'] = ??;
				$insertFields['location'] = $component->getAttribute('LOCATION');
				$insertFields['description'] = $component->getAttribute('DESCRIPTION');
				$categoryString = $component->getAttribute('CATEGORY');

				$categories = t3lib_div::trimExplode(',',$categoryString,1);

				$categoryUids = array();
				foreach($categories as $category){
					$category = trim($category);
					$categorySelect = '*';
					$categoryTable = 'tx_cal_category';
					$categoryWhere = 'calendar_id = '.$calId.' AND title ="'.$category.'"';
					$foundCategory = false;
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($categorySelect,$categoryTable,$categoryWhere);
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						$foundCategory = true;
						$categoryUids[] = $row['uid'];
					}
					if(!$foundCategory){
						$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($categoryTable,array('tstamp'=>$insertFields['tstamp'],'crdate'=>$insertFields['crdate'], 'pid' => $pid, 'title' => $category, 'calendar_id' => $calId));
						$categoryUids[] = $GLOBALS['TYPO3_DB']->sql_insert_id();
					}
				}

				if($component->getAttribute('RRULE')){
					$rrule = $component->getAttribute('RRULE');

					$data = str_replace ('RRULE:', '', $rrule);
					$rrule = split (';', $data);
					foreach ($rrule as $recur) {
						ereg ('(.*)=(.*)', $recur, $regs);
						$rrule_array[$regs[1]] = $regs[2];
					}
					foreach ($rrule_array as $key => $val) {
						switch($key) {
							case 'FREQ':
								switch ($val) {
									case 'YEARLY':		$freq_type = 'year';	break;
									case 'MONTHLY':		$freq_type = 'month';	break;
									case 'WEEKLY':		$freq_type = 'week';	break;
									case 'DAILY':		$freq_type = 'day';		break;
									case 'HOURLY':		$freq_type = 'hour';	break;
									case 'MINUTELY':	$freq_type = 'minute';	break;
									case 'SECONDLY':	$freq_type = 'second';	break;
								}
								$insertFields['freq'] = strtolower($freq_type);
								break;
							case 'COUNT':
								$insertFields['cnt'] = $val;
								break;
							case 'UNTIL':
								$until = str_replace('T', '', $val);
								$until = str_replace('Z', '', $until);
								if (strlen($until) == 8) $until = $until.'235959';
								$abs_until = $until;
								ereg ('([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})', $until, $regs);
								$insertFields['until'] = mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]);
								break;
							case 'INTERVAL':
								$insertFields['intrval'] = $val;
								break;
							case 'BYSECOND':
//								$bysecond = $val;
//								$bysecond = split (',', $bysecond);
								break;
							case 'BYMINUTE':
//								$byminute = $val;
//								$byminute = split (',', $byminute);
								break;
							case 'BYHOUR':
//								$byhour = $val;
//								$byhour = split (',', $byhour);
								break;
							case 'BYDAY':
								$byday = $val;
								$byday = split (',', $byday);
								$insertFields['byday'] = strtolower(array_pop($byday));
								break;
							case 'BYMONTHDAY':
								$bymonthday = $val;
								$bymonthday = split (',', $bymonthday);
								$insertFields['bymonthday'] = strtolower(array_pop($bymonthday));
								break;					
							case 'BYYEARDAY':
//								$byyearday = $val;
//								$byyearday = split (',', $byyearday);
								break;
							case 'BYWEEKNO':
//								$byweekno = $val;
//								$byweekno = split (',', $byweekno);
								break;
							case 'BYMONTH':
								$bymonth = $val;
								$bymonth = split (',', $bymonth);
								$insertFields['bymonth'] = strtolower(array_pop($bymonth));
								break;
							case 'BYSETPOS':
//								$bysetpos = $val;
								break;
							case 'WKST':
//								$wkst = $val;
								break;
							case 'END':
//								??
								break;
						}
					}
				}
				$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
				$eventUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
				
				/* Schedule reminders for new and changed events */
				require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
				$pageTSConf = t3lib_befunc::getPagesTSconfig($pid);
				$offset = is_numeric($pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['remind.']['time']) ? $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['remind.']['time'] * 60 : 0;
				$reminderTimestamp = $insertFields['start_date'] + $insertFields['start_time'] - $offset;
				$reminderService = &getReminderService();
				$reminderService->scheduleReminder($eventUid, $reminderTimestamp);
				

				foreach($categoryUids as $uid){
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_cal_event_category_mm',array('uid_local'=>$eventUid, 'uid_foreign'=>$uid));
				}

			}
		}
	}

	/**
	 * @param	object	A tx_iCalendar_vevent object
	 */
	function convertvEventToCalEvent($component){
    	require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_phpicalendar_model.php');
    	$event = t3lib_div::makeInstance(get_class('tx_cal_phpicalendar_model'));
    	$event->setType('tx_cal_phpicalendar');
		$event->setTstamp($component['tstamp']);
		$event->setStartHour($component['start_time']);
		$event->setEndHour($component['end_time']);
		$event->setStartDate($component['start_date']);
		$event->setEndDate($component['end_date']);

		$event->setTitle($component['title']);
		$event->setFreq($component['freq']);
		$event->setByDay($component['byday']);
		$event->setByMonthDay($component['bymonthday']);
		$event->setByMonth($component['bymonth']);
		$event->setUntil($component['until']);
		$event->setCount($component['cnt']);

//		$event->setInterval(
    	return $event;
    }
	

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_icalendar_service.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/service/class.tx_cal_icalendar_service.php']);
}
?>