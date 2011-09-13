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
		if ($table == 'tx_cal_event' && count($fieldArray)>1) {
			if($status!='new'){
				require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
				$row = t3lib_BEfunc::getRecord ("tx_cal_event", $id);
				$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
				$notificationService =& getNotificationService();
				$notificationService->notifyOfChanges($row, $fieldArray, $pageTSConf['options.']['tx_cal_controller.']['event.']['notify.']);
			}
   		} 
	}
	
	function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$tcemain) {
		if ($table == 'tx_cal_event' && count($fieldArray)>1) {
			if($status=='new'){
				require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
				$row = t3lib_BEfunc::getRecord ("tx_cal_event", $tcemain->substNEWwithIDs[$id]);
				$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
				$notificationService =& getNotificationService();
				$notificationService->notify($row, $pageTSConf['options.']['tx_cal_controller.']['event.']['notify.']);
			}
   		} 
	}
	
	
	function processDatamap_preProcessFieldArray(&$incommingFieldArray, $table, $id, &$this) {

		if($table == 'tx_cal_calendar' && array_key_exists("type",$incommingFieldArray) && !strstr($id,'NEW')){
   			$row = t3lib_BEfunc::getRecord ("tx_cal_calendar", $id);
   			// Here we have to check if the calendar belongs to the type
   			// problem with case 2 & 3 -> what to do with events of type database? delete them without warning? keep them and assign them to a default category?
   			switch ($incommingFieldArray['type']){
   				// database
   				case 0:
   					// delete all former events
   					$where = " calendar_id=".$row['uid']." AND isTemp=1 AND pid=".$row['pid'];
   					$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_cal_event", $where);
   					$where = " uid_local=".$row['uid'];
   					$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_cal_event_category_mm", $where);
   				break;
   				// external URL
   				case 1:
   					// delete all former events
   					$where = " calendar_id=".$row['uid']." AND isTemp=1 AND pid=".$row['pid'];
   					$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_cal_event", $where);
   					$where = " uid_local=".$row['uid'];
   					$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_cal_event_category_mm", $where);
   					// parse external URL
   					if($row['ext_url']!=""){
   						//$absFile = t3lib_div::getFileAbsFileName($row['ext_url']);
   						$text = t3lib_div::getURL($row['ext_url']);
   						$iCalendar = tx_cal_tcemain_processdatamap::getiCalendarFromIcsFile($text);
   					}
					//insert events into db
//debug($iCalendar->_components, "iCalendar");
					tx_cal_tcemain_processdatamap::insertCalEventsIntoDB($iCalendar->_components, $row['uid'], $row['pid'], $row['cruser_id']);
   				break;
   				// file
   				case 2:
   					// delete all former events
   					$where = " calendar_id=".$row['uid']." AND isTemp=1 AND pid=".$row['pid'];
   					$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_cal_event", $where);
   					$where = " uid_local=".$row['uid'];
   					$GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_cal_event_category_mm", $where);
   					// parse local file
   					if($row['ics_file']!=""){
   						$absFile = t3lib_div::getFileAbsFileName("uploads/tx_cal/ics/".$row['ics_file']);
   						$text = t3lib_div::getURL($absFile);
   						$iCalendar = tx_cal_tcemain_processdatamap::getiCalendarFromIcsFile($text);
   					}

					//insert events into db
					tx_cal_tcemain_processdatamap::insertCalEventsIntoDB($iCalendar->_components, $row['uid'], $row['pid'], $row['cruser_id']);
   				break;
   			}
		}
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
	function insertCalEventsIntoDB($iCalendarComponentArray=array(), $calId, $pid="", $cruserId=""){
		if(empty($iCalendarComponentArray)){
			return;
		}
		$table = "tx_cal_event";
		foreach($iCalendarComponentArray as $component){

			$insertFields = array();
			$insertFields['isTemp']=1;
			$insertFields['tstamp']=time();
			$insertFields['crdate']=time();
			$insertFields['pid']=$pid;
			if (is_a($component,"tx_iCalendar_vevent")){

				$insertFields['cruser_id'] = $cruserId;
				$insertFields['calendar_id'] = $calId;
				if($component->getAttribute('DTSTART')){
					$startdate = $component->getAttribute('DTSTART');
					if(is_array($startdate)){
						$insertFields['start_date'] = mktime(0,0,0,$startdate['month'],$startdate['mday'],$startdate['year']);
					}else{
						$year = date("Y",$startdate);
						$month = date("m",$startdate);
						$mday = date("d",$startdate);
						$hour = date("H",$startdate);
						$min = date("i",$startdate);
						$insertFields['start_date'] = mktime(0,0,0,$month,$mday,$year);
						$insertFields['start_time'] = mktime($hour,$min,0,0,0,1)-975538800; // seconds of one year
					}				
				}else{
					continue;
				}
				if($component->getAttribute('DTEND')){
					$enddate = $component->getAttribute('DTEND');
					if(is_array($enddate)){
						$insertFields['end_date'] = mktime(0,0,0,$enddate['month'],$enddate['mday'],$enddate['year']);
					}else{
						$year = date("Y",$enddate);
						$month = date("m",$enddate);
						$mday = date("d",$enddate);
						$hour = date("H",$enddate);
						$min = date("i",$enddate);
						$insertFields['end_date'] = mktime(0,0,0,$month,$mday,$year);
						$insertFields['end_time'] = mktime($hour,$min,0,0,0,1)-975538800; // seconds of one year
					}				
				}
				if($component->getAttribute('DURATION')){
					$enddate = $insertFields['start_date']+$component->getAttribute('DURATION');
					$year = date("Y",$enddate);
					$month = date("m",$enddate);
					$mday = date("d",$enddate);
					$hour = date("H",$enddate);
					$min = date("i",$enddate);
					$insertFields['end_date'] = mktime(0,0,0,$month,$mday,$year);
					$insertFields['end_time'] = mktime($hour,$min,0,0,0,1)-975538800; // seconds of one year
				}

				$insertFields['title'] = $component->getAttribute('SUMMARY');
//				$insertFields['organizer'] = ??;
				$insertFields['location'] = $component->getAttribute('LOCATION');
				$insertFields['description'] = $component->getAttribute('DESCRIPTION');
				$categoryString = $component->getAttribute('CATEGORY');
				
				$categories = split(',',$categoryString);

				$categoryUids = array();
				foreach($categories as $category){
					$category = trim($category);
					$categorySelect = "*";
					$categoryTable = "tx_cal_category";
					$categoryWhere = "calendar_id = ".$calId." AND title ='".$category."'";
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
    	$event = t3lib_div::makeInstance(get_class("tx_cal_phpicalendar_model"));
    	$event->setType("tx_cal_phpicalendar");
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processdatamap.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processdatamap.php']);
}
?>