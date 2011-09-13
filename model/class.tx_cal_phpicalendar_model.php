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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_model.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_shared.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_phpicalendar_model extends tx_cal_model {
	
	var $location;
	var $calnumber = 1;
	var $local_pibase;
	var $conf;
	var $shared;
	var $prefixId = "tx_cal_controller";
	var $cObj; // The backReference to the mother cObj object set at call time
	var $local_cObj;
	var $rightsObj;
	
	/**
	 *  Finds all events within a given range.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAllWithin($conf=array(), $start_date='', $end_date='', $pidList='') {
		
		$this->conf = $conf;
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->local_cObj);

		// if we have found a category.uid lets search for events with the according category_id
		$additionalWhere = "AND tx_cal_event.pid IN (".$pidList.") AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0 AND (tx_cal_event.starttime>=".$start_date." OR tx_cal_event.endtime<".$end_date." OR tx_cal_event.freq!='none' OR tx_cal_event.freq!='')";
		
		$categories = $this->getCategoryArray();
		return $this->getEventsFromTable(true, $this->arrayToCommaseparatedString($categories), $additionalWhere);
	}
	
	/**
	 * Search for categories
	 * @return	array	Array with the ids of the categories matching the condition
	 */
	function getCategoryArray(){
		if($GLOBALS['TSFE']->loginUser){
			$additional = " OR fe_users.uid = ".$GLOBALS['TSFE']->fe_user->user['uid'];
		}
		$categoryIds = array();
		$dbIds = array();
		$fileIds = array();
		$extUrlIds = array();
		
		// Searching for categories with the correct fe_users: anonymous + if logged in his fe_users.uid
		$select = "tx_cal_category.*";
		$table = "tx_cal_category";
		$foreigntable = "fe_users";
		$mmtable = "tx_cal_fe_user_category_mm";
		$where = " AND tx_cal_fe_user_category_mm.tablenames='fe_users' AND (fe_users.uid = ".$this->conf['anonymousUserUid'].$additional.") AND tx_cal_category.deleted = 0 AND tx_cal_category.hidden = 0";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$table,$mmtable,$foreigntable,$where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$categoryIds[] = $row['uid'];
			$this->addCalLegendDescription($row['headercolor'], $row['title']);
		}

		// if logged in we have to check for the fe_groups.uid(s) too
		if($GLOBALS['TSFE']->loginUser){
			$groups = split(',',$GLOBALS['TSFE']->fe_user->user['usergroup']);
			$additional = "";
			$first = true;		
			foreach($groups as $group_key => $group_uid){
				if($first){
					$additional .=$group_uid;
					$first = false;
				}else{
					$additional .= ",".$group_uid;
				}
			}
			$foreigntable = "fe_groups";
			$where = " AND tx_cal_fe_user_category_mm.tablenames='fe_groups' AND fe_groups.uid IN (".$additional.") AND tx_cal_category.deleted = 0 AND tx_cal_category.hidden = 0";
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$table,$mmtable,$foreigntable,$where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$categoryIds[] = $row['uid'];
				$this->addCalLegendDescription($row['headercolor'], $row['title']);
			}
		}
		return $categoryIds;
	}
	
	function arrayToCommaseparatedString($array){
		$string = "";
		foreach($array as $part){
			$string.=$part.",";
		}
		if(strlen($string)>1){
			$string = substr($string,0,strlen($string)-1);
		}
		return $string;
	}
	
	/**
	 * Search for events with an according category.uid
	 * @param	$includeRecurring	boolean	TRUE if recurring events should be included
	 * @param	$categoryIds		String	The category ids to search events for
	 * @param	$additionalWhere	String	Additional where string; will be added to the where-clause
	 * 
	 * @return	array				An array of tx_cal_phpcalendar_model events
	 */
	function getEventsFromTable($includeRecurring=false,$categoryIds, $additionalWhere=""){
		
		$events = array();
		
		$select = "tx_cal_event.*, tx_cal_category.title AS category_title, tx_cal_category.headercolor AS category_headercolor, tx_cal_category.bodycolor AS category_bodycolor, tx_cal_category.headertextcolor AS category_headertextcolor, tx_cal_category.bodytextcolor AS category_bodytextcolor";
		$table = "tx_cal_event, tx_cal_category";
		$where = " tx_cal_event.category_id IN (".$categoryIds.") AND tx_cal_event.category_id=tx_cal_category.uid ".$additionalWhere;
		$groupBy = "";
		$orderBy = " tx_cal_event.start_date ASC, tx_cal_event.start_time ASC";
		$limit = "";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where,$groupBy ,$orderBy,$limit);
		$lastday = '';
		$currentday = ' ';
		$first = true;
		while ($result!=null && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {			
			$event = $this->createEvent($row, false);
			$events_tmp = array();
			if(!$includeRecurring){
				$events_tmp[date("Ymd",$event->getStartDate())][date("Hi",$event->getStartTime())] = $event;
			}else{
				$events_tmp = $this->recurringEvent($this->conf, $event);
			}
			// get exception events:
			$where = "AND tx_cal_event.uid = ".$event->getUid();
			$orderBy = "";
			$groupBy = "";
			$limit = "";	

			$result3 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("tx_cal_exception_event_group.*","tx_cal_event","tx_cal_exception_event_group_mm","tx_cal_exception_event_group",$where,$groupBy ,$orderBy,$limit);
			while ($row3 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result3)) {
				$event->addExceptionGroupId($row3['uid']);
				$where = "AND tx_cal_exception_event_group.uid = ".$row3['uid'];
				$result4 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("tx_cal_exception_event.*","tx_cal_exception_event_group","tx_cal_exception_event_group_mm","tx_cal_exception_event",$where,$groupBy ,$orderBy,$limit);
				while ($row4 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result4)) {
					$ex_event = $this->createEvent($row4, true);
					$ex_events = $this->recurringEvent($this->conf, $ex_event);

					$this->removeEvents($events_tmp, $ex_events);
				}
			}

			$where = "AND tx_cal_event.uid = ".$event->getUid();
			$orderBy = "tx_cal_exception_event.start_time ASC";
			$groupBy = "";
			$limit = "";		
			$result2 = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("tx_cal_exception_event.*","tx_cal_event","tx_cal_exception_event_mm","tx_cal_exception_event",$where,$groupBy ,$orderBy,$limit);
			while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result2)) {
				$event->addExceptionSingleId($row2['uid']);
				$ex_event = $this->createEvent($row2, true);				
				$ex_events = $this->recurringEvent($this->conf, $ex_event);
				$this->removeEvents($events_tmp, $ex_events);	
			}
			$this->mergeEvents($events,$events_tmp);
		}

		return $events;
	}

	/**
	 *  Finds all events.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAll(&$conf, $pidList='') {

		$this->conf = $conf;
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->local_cObj);
		
		// if we have found a category.uid lets search for events with the according category_id
		$additionalWhere = "AND tx_cal_event.pid IN (".$pidList.") AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0";
		$categories = $this->getCategoryArray();
		return $this->getEventsFromTable(false, $this->arrayToCommaseparatedString($categories), $additionalWhere);
	}
	
	function createEvent($row, $isException){

		$event = t3lib_div::makeInstance(get_class($this));	
		$event->setType($this->getServiceKey());
		$event->setUid($row['uid']);
		$event->setTstamp($row['tstamp']);
		$event->setStartHour($row['start_time']);
		$event->setEndHour($row['end_time']);
		$event->setStartDate($row['start_date']);
		$event->setEndDate($row['end_date']);
		
		$event->setTitle($row['title']);
		$event->setFreq($row['freq']);
		$event->setByDay($row['byday']);
		$event->setByMonthDay($row['bymonthday']);
		$event->setByMonth($row['bymonth']);
		$event->setUntil($row['until']);
		$event->setCount($row['cnt']);
		$event->setInterval($row['intrval']);
		
		/* new */
		$event->setEventType($row['type']);
		$event->setPage($row['page']);
		$event->setExtUrl($row['ext_url']);

		/* new */
		
		if(!$isException){
		
			$event->setOrganizer($row['organizer']);
			$event->setLocation($row['location']);
			$event->setDescription($row['description']);
			$event->setCategoryId($row['category_id']);
			$event->setCategory($row['category_title']);
			$event->setHeaderColor($row['category_headercolor']);
			$event->setBodyColor($row['category_bodycolor']);
			$event->setHeaderTextColor($row['category_headertextcolor']);
			$event->setBodyTextColor($row['category_bodytextcolor']);
			
			if($row['organizer_id']!=0){
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid=".$row['organizer_id']);
				while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					//$event->setCategory($row2['title']);
					$prefixId = 'tx_cal_controller';
					$link_vars = t3lib_div::GPvar($prefixId);
					$lastview = $link_vars['lastview'];
					$getdate = $link_vars['getdate'];
					$event->setOrganizerLink($this->shared->pi_linkToPage( $row2['name'], array($prefixId."[getdate]" => $getdate,$prefixId."[lastview]" => $lastview, $prefixId."[view]" => "organizer",$prefixId."[uid]" => $row2['uid'],$prefixId."[type]" => "tx_default_organizer")));
					$event->setOrganizer_id($row2['uid']);
				}
			}
			
			if($row['location_id']!=0){
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid=".$row['location_id']);
				while ($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					//$event->setCategory($row2['title']);
					$prefixId = 'tx_cal_controller';
					$link_vars = t3lib_div::GPvar($prefixId);
					$lastview = $link_vars['lastview'];
					$getdate = $link_vars['getdate'];
					$event->setLocationLink($this->shared->pi_linkToPage( $row2['name'], array($prefixId."[getdate]" => $getdate,$prefixId."[lastview]" => $lastview, $prefixId."[view]" => "location",$prefixId."[uid]" => $row2['uid'],$prefixId."[type]" => "tx_default_location")));
					$event->setLocationId($row2['uid']);
				}
			}		
		}
		return $event;
	}
	
	/**
	 *  Finds a single event.
	 *
	 *  @return		object			The event represented by the model.
	 */	
	function find(&$conf, $uid, $pidList='') {

		$this->conf = $conf;
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->local_cObj);
		
		$additionalWhere = "AND tx_cal_event.uid=".$uid." AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0";
		
		$categories = $this->getCategoryArray();

		return array_pop(array_pop($this->getEventsFromTable(false, $this->arrayToCommaseparatedString($categories), $additionalWhere)));
	}
	
	/**
	 *  Gets the location of the event.  Location does not exist in the default
	 *  model, only in calexampl3.
	 *  
	 *  @return		string		The location.
	 */
	function getLocation() { 
		return $this->location; 
	}
	
	
	/**
	 *  Sets the location of the event.  Location does not exist in the default
	 *  model, only in calexampl3.
	 *
	 *  @param		string		The location.
	 *  @return		void
	 */
	function setLocation($location) {
		$this->location = $location;
	}

	function renderEventForDay() {
		return "<div>".$this->getTitle()."</div>";
	}
	
	function renderEventForAllDay(){
		return '<div style="background-color:'.$this->getHeaderColor().'; color:'.$this->getHeaderTextColor().';  text-align: center; width:100%;">'.$this->title.'</div>';
	}

	function renderEvent(&$cObj, $rightsObj=''){
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$this->rightsObj = $rightsObj;	
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->cObj);
		
		$page = $this->cObj->fileResource($this->conf["view."]["event."]["phpicalendarEventTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no location template file found:</h3>".$this->conf["view."]["event."]["phpicalendarEventTemplate"];
		}
//		$link_vars = t3lib_div::GPvar("tx_cal_controller");
		$lastview = $this->conf['lastview'];
		$uid  = $this->conf['uid'];
		$type = $this->conf['type'];
		$monitoring  = $this->conf['monitor'];
		$getdate = $this->conf['getdate'];
		
		$rems["###MONITOR###"] = "";
		if($this->conf['allowSubscribe']==1){
		
			if($monitoring!= null && $monitoring!=''){
				
				$user_uid = $GLOBALS['TSFE']->fe_user->user['uid'];
				switch ($monitoring){
					case 'start':{
						if(is_numeric($user_uid)){
							$table = "tx_cal_fe_user_event_monitor_mm";
							$fields_values = array('uid_local' => $uid, 'uid_foreign' => $user_uid, 'tablenames' => 'fe_users', 'sorting' => 1);
							$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$fields_values);
						}else{
							if (t3lib_extMgm::isLoaded('captcha')){
								session_start();
								$captchaStr = $_SESSION['tx_captcha_string'];
								$_SESSION['tx_captcha_string'] = '';
							} else {
								$captchaStr = -1;
							}
							if ($captchaStr && $GLOBALS['HTTP_POST_VARS']['captcha']===$captchaStr){
								$table = "tx_cal_unknown_users";
								$select = "uid";
								$where = 'email = "'.$GLOBALS['HTTP_POST_VARS']['email'].'"';
								$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
								$already_exists = false;
								$user_uid = 0;
								while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
									$already_exists = true;
									$user_uid = $row['uid'];
									break;
								}
								if(!$already_exists){
									$fields_values = array('tstamp' => time(), 'email' => $GLOBALS['HTTP_POST_VARS']['email']);
									$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$fields_values);
									$user_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
								}
								$select = "uid_local";
								$table = "tx_cal_fe_user_event_monitor_mm";
								$where = 'uid_local ="'.$uid.'" AND uid_foreign = "'.$user_uid.'" AND tablenames="tx_cal_unknown_users"';
								$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
								$already_exists = false;
								while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
									$already_exists = true;
									break;
								}
								if(!$already_exists){
									$table = "tx_cal_fe_user_event_monitor_mm";
									$fields_values = array('uid_local' => $uid, 'uid_foreign' => $user_uid, 'tablenames' => 'tx_cal_unknown_users', 'sorting' => 1);
									$GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$fields_values);
								}
							}
						}
						break;
					}
					case 'stop':{
						if(is_numeric($user_uid)){
							$table = "tx_cal_fe_user_event_monitor_mm";
							$where = "uid_foreign = ".$user_uid." AND uid_local = ".$uid;
							$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
						}else{
							if (t3lib_extMgm::isLoaded('captcha')){
								session_start();
								$captchaStr = $_SESSION['tx_captcha_string'];
								$_SESSION['tx_captcha_string'] = '';
							} else {
								$captchaStr = -1;
							}
							if ($captchaStr && $GLOBALS['HTTP_POST_VARS']['captcha']===$captchaStr){
								$table = "tx_cal_unknown_users";
								$select = "uid";
								$where = 'email = "'.$GLOBALS['HTTP_POST_VARS']['email'].'"';
								$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
								$already_exists = false;
								$user_uid = 0;
								while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
									$already_exists = true;
									$user_uid = $row['uid'];
									break;
								}
								if($already_exists){
									$table = "tx_cal_fe_user_event_monitor_mm";
									$where = 'uid_local ="'.$uid.'" AND uid_foreign = "'.$user_uid.'" AND tablenames="tx_cal_unknown_users"';
									$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
								}
							}
						}
						break;
					}
				}
			}
			
			if($GLOBALS['TSFE']->loginUser && $this->cObj->conf['subscribeFeUser']==1){
				
				$select = "*";
				$from_table = "tx_cal_fe_user_event_monitor_mm";
				$whereClause = "uid_foreign = ".$GLOBALS['TSFE']->fe_user->user['uid'].
						" AND uid_local = ".$uid.
						" AND tablenames = 'fe_users'";
		
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$from_table,$whereClause,$groupBy='',$orderBy='',$limit='');
				$found_one = false;		
				while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$rems["###MONITOR###"] = "<br />".$this->shared->pi_linkToPage($this->shared->lang('l_monitor_event_logged_in_monitoring'), array($this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => $lastview, $this->prefixId."[view]" => "event", $this->prefixId."[monitor]" => "stop", $this->prefixId."[type]" => $type, $this->prefixId."[uid]" => $uid))."<br /><br />";
					$found_one = true;
				}
				if(!$found_one){
					$rems["###MONITOR###"] = "<br />".$this->shared->pi_linkToPage($this->shared->lang('l_monitor_event_logged_in_nomonitoring'), array($this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => $lastview, $this->prefixId."[view]" => "event", $this->prefixId."[monitor]" => "start", $this->prefixId."[type]" => $type, $this->prefixId."[uid]" => $uid))."<br /><br />";	
				}
			}else if($this->cObj->conf['subscribeWithCaptcha']==1){
				if (t3lib_extMgm::isLoaded('captcha')){
					$notLoggedinNoMonitoring 	= $this->cObj->getSubpart($page, "###NOTLOGGEDIN_NOMONITORING###");
					$parameter = array("no_cache" => 1, $this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => $lastview, $this->prefixId."[view]" => "event", $this->prefixId."[monitor]" => "start", $this->prefixId."[type]" => $type, $this->prefixId."[uid]" => $uid);
					$actionUrl = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter);
					$parameter2 = array("no_cache" => 1, $this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => $lastview, $this->prefixId."[view]" => "event", $this->prefixId."[monitor]" => "stop", $this->prefixId."[type]" => $type, $this->prefixId."[uid]" => $uid);
					$actionUrl2 = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter2);
					$sims["NOTLOGGEDIN_NOMONITORING_HEADING"] = $this->shared->lang('l_monitor_event_logged_in_nomonitoring');
					$sims["CAPTCHA_SRC"] = t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php';
					$sims["NOTLOGGEDIN_NOMONITORING_SUBMIT"] = $this->shared->lang('l_submit');
					$sims["L_ENTER_EMAIL"] = $this->shared->lang('l_enter_email');
					$sims["L_CAPTCHA_TEXT"] = $this->shared->lang('l_captcha_text');
					$monitor = $this->shared->replace_tags($sims,$notLoggedinNoMonitoring);
					$sims = array();
					$notLoggedinMonitoring 	= $this->cObj->getSubpart($page, "###NOTLOGGEDIN_MONITORING###");
					$sims["CAPTCHA_SRC"] = t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php';
					$sims["NOTLOGGEDIN_MONITORING_HEADING"] = $this->shared->lang('l_monitor_event_logged_in_monitoring');
					$sims["NOTLOGGEDIN_MONITORING_SUBMIT"] = $this->shared->lang('l_submit');
					$sims["L_ENTER_EMAIL"] = $this->shared->lang('l_enter_email');
					$sims["L_CAPTCHA_TEXT"] = $this->shared->lang('l_captcha_text');
					$monitor .= $this->shared->replace_tags($sims,$notLoggedinMonitoring);
					$rems["###MONITOR###"] = $monitor;
				} else {
					$rems["###MONITOR###"] = '';
				}
				//$rems["###MONITOR###"] = $this->shared->lang('l_monitor_event_not_logged_in');
			}
		}
		$rems['###TITLE###'] = $this->getTitle();
		if($this->getStarttime()==$this->getEndtime()||$this->getEndtime()==0){
			$rems['###STARTTIME_LABEL###'] = "";
			$rems['###ENDTIME_LABEL###'] = "";
			$rems['###STARTTIME###'] = "";
			$rems['###ENDTIME###'] = "";
			if($this->getFreq()=="none"){
				$rems['###STARTDATE###'] = tx_cal_calendar::localizeDate ($this->shared->lang('l_dateFormat_day'), $this->getStarttime(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
				$rems['###STARTDATE_LABEL###'] = $this->shared->lang('l_event_allday');
			}else{
				$rems['###STARTDATE###'] = "";
				$rems['###STARTDATE_LABEL###'] = "";
			}
			$rems['###ENDDATE###'] = "";
			$rems['###ENDDATE_LABEL###'] = "";
		}else{
			$rems['###STARTTIME_LABEL###'] = $this->shared->lang('l_event_starttime');
			$rems['###ENDTIME_LABEL###'] = $this->shared->lang('l_event_endtime');
			$rems['###STARTTIME###'] = date($this->shared->lang('l_timeFormat'), $this->getStarttime());
			$rems['###ENDTIME###'] = date($this->shared->lang('l_timeFormat'), $this->getEndtime());
			$rems['###STARTDATE###'] = tx_cal_calendar::localizeDate ($this->shared->lang('l_dateFormat_day'), $this->getStarttime(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			$rems['###ENDDATE###'] = tx_cal_calendar::localizeDate ($this->shared->lang('l_dateFormat_day'), $this->getEndtime(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			$rems['###STARTDATE_LABEL###'] = $this->shared->lang('l_event_startdate');
			$rems['###ENDDATE_LABEL###'] = $this->shared->lang('l_event_enddate');
		}
		if($this->getOrganizerLink()!=''){
			$rems['###ORGANIZER###'] = $this->getOrganizerLink();
		}else{
			$rems['###ORGANIZER###'] = $this->getOrganizer();
		}
		if($this->getLocationLink()!=''){
			$rems['###LOCATION###'] = $this->getLocationLink();
		}else{
			$rems['###LOCATION###'] = $this->getLocation();
		}
		$rems['###DESCRIPTION###'] = nl2br($cObj->parseFunc($this->getDescription(),$cObj->conf["parseFunc."]));
		
		$rems['###TITLE_LABEL###'] = $this->shared->lang('l_event_title');
		
		$rems['###ORGANIZER_LABEL###'] = $this->shared->lang('l_event_organizer');
		$rems['###LOCATION_LABEL###'] = $this->shared->lang('l_event_location');
		$rems['###DESCRIPTION_LABEL###'] = $this->shared->lang('l_event_description');
		$rems['###ICSLINK###'] = "";
		if($this->conf["view."]["ics."]['showIcsLinks']==1){
			$rems['###ICSLINK###'] = $this->shared->pi_linkToPage($this->shared->lang('l_event_icslink'), array($this->prefixId."[type]" => $type, $this->prefixId."[view]" => "single_ics", $this->prefixId."[uid]" => $uid, "type" => "150"));
		}
		$rems["###EDITLINK###"] = "";
		if($rightsObj->isAllowedToEditEvents()){
			$rems["###EDITLINK###"] = $this->shared->pi_linkToPage('<img src="typo3/gfx/edit2.gif" border="0"/>', array($this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => $lastview, $this->prefixId."[view]" => 'edit_event', $this->prefixId."[type]" => $this->getType(), $this->prefixId."[uid]" => $uid));
		}
		if($rightsObj->isAllowedToDeleteEvents()){
			$rems["###EDITLINK###"] .= $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm::siteRelPath('cal').'template/img/delete.png" border="0"/>', array($this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => $lastview, $this->prefixId."[view]" => 'delete_event', $this->prefixId."[type]" => $this->getType(), $this->prefixId."[uid]" => $uid));
		}

		return $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array());
	}
	
	function saveEvent(&$rightsObj, &$conf, $pid){
		$this->rightsObj = $rightsObj;
		$crdate = time();
		$insertFields = array("pid" => $pid, "tstamp" => $crdate, "crdate" => $crdate);
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$insertFields['cruser_id'] = $rightsObj->getUserId();
//		$insertFields['relation_cnt'] = sizeof($GLOBALS['HTTP_POST_VARS']['user_ids']);
		$table = "tx_cal_event";
						
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
		$uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
//		if($this->rightsObj->isAllowedToCreateEventCreator()){
//			if($GLOBALS['HTTP_POST_VARS']['user_ids']!=''){
//				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",split(',', $GLOBALS['HTTP_POST_VARS']['user_ids']),$uid,"fe_users");
//			}	
//			else if($GLOBALS['HTTP_POST_VARS']['group_ids']!=''){
//				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",split(',', $GLOBALS['HTTP_POST_VARS']['group_ids']),$uid,"fe_groups");
//			}
//			else{
//				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",array($conf['anonymousUserUid']),$uid,"fe_users");
//			}
//		}else{
//			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",array($conf['anonymousUserUid']),$uid,"fe_users");	
//		}
		if($this->rightsObj->isAllowedToCreateEventNotify()){
			if($GLOBALS['HTTP_POST_VARS']['notify_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', $GLOBALS['HTTP_POST_VARS']['notify_ids']),$uid,"fe_users");
			}
		}
		if($this->rightsObj->isAllowedToCreateEventException()){
			if($GLOBALS['HTTP_POST_VARS']['single_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_mm",split(',', $GLOBALS['HTTP_POST_VARS']['single_exception_ids']),$uid,"tx_cal_exception_event");
			}
			if($GLOBALS['HTTP_POST_VARS']['group_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_group_mm",split(',', $GLOBALS['HTTP_POST_VARS']['group_exception_ids']),$uid,"tx_cal_exception_event_group");
			}
		}
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

		$event = ($this->find($conf, $uid, $conf['pidList']));
		notifyOfChanges($event->getValuesAsArray(), $insertFields, $conf);
	}
	
	function updateEvent($rightsObj, $conf=array(), $uid){

		$this->rightsObj = $rightsObj;
		$insertFields = array("tstamp" => time());
		//TODO: Check if all values are correct
		
		$this->retrievePostData($insertFields);
		
		// Creating DB records
		$insertFields['relation_cnt'] = sizeof($GLOBALS['HTTP_POST_VARS']['user_ids']);
		$table = "tx_cal_event";
		$where = "uid = ".$uid;			
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$insertFields);
		
		
		$cal_user_ids = array();
		$where = " AND tx_cal_event.uid=".$uid." AND tx_cal_fe_user_category_mm.tablenames='fe_users' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0";
		$orderBy = "";
		$groupBy = "";
		$limit = "";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("fe_users.*","tx_cal_event","tx_cal_fe_user_category_mm","fe_users",$where,$groupBy ,$orderBy,$limit);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {	
			array_push($cal_user_ids,$row['uid']);
		}
		if($this->rightsObj->isAllowedToEditEventCreator()){
			$table = "tx_cal_fe_user_category_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);	
			if($GLOBALS['HTTP_POST_VARS']['user_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",split(',', $GLOBALS['HTTP_POST_VARS']['user_ids']),$uid,"fe_users");
			}	
			else if($GLOBALS['HTTP_POST_VARS']['group_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",split(',', $GLOBALS['HTTP_POST_VARS']['group_ids']),$uid,"fe_groups");
			}
			else{
				$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_category_mm",array($conf['anonymousUserUid']),$uid,"fe_users");
			}
		}
		
		if($this->rightsObj->isAllowedToEditEventNotify()){
			$table = "tx_cal_fe_user_event_monitor_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			$this->insertIdsIntoTableWithMMRelation("tx_cal_fe_user_event_monitor_mm",split(',', $GLOBALS['HTTP_POST_VARS']['notify_ids']),$uid,"fe_users");
		}
		if($this->rightsObj->isAllowedToEditEventException()){
			$table = "tx_cal_exception_event_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			if($GLOBALS['HTTP_POST_VARS']['single_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_mm",split(',', $GLOBALS['HTTP_POST_VARS']['single_exception_ids']),$uid,"tx_cal_exception_event");
			}
			$table = "tx_cal_exception_event_group_mm";
			$where = "uid_local = ".$uid;
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,$where);
			if($GLOBALS['HTTP_POST_VARS']['group_exception_ids']!=''){
				$this->insertIdsIntoTableWithMMRelation("tx_cal_exception_event_group_mm",split(',', $GLOBALS['HTTP_POST_VARS']['group_exception_ids']),$uid,"tx_cal_exception_event_group");
			}
		}
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		$event = ($this->find($conf, $uid, $conf['pidList']));
		notifyOfChanges($event->getValuesAsArray(), $insertFields, $conf);
	}
	
	function removeEvent(&$rightsObj, $conf=array(), $uid){
		$this->rightsObj = $rightsObj;		
		if($rightsObj->isAllowedToDeleteEvents()){
			$updateFields = array("tstamp" => time(), "deleted" => 1);
			$table = "tx_cal_event";
			$where = "uid = ".$uid;	
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
		}
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($GLOBALS['HTTP_POST_VARS']['hidden']=="true" && 
				($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
//		if($GLOBALS['HTTP_POST_VARS']['start']!=''){
//			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['start'], $time);
//			$insertFields['start'] = mktime(0,0,0,$time[2],$time[3],$time[1]);;
//		}
//		if($GLOBALS['HTTP_POST_VARS']['end']!=''){
//			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['end'], $time);
//			$insertFields['end'] = mktime(0,0,0,$time[2],$time[3],$time[1]);;
//		}
		if($this->rightsObj->isAllowedToEditEventCategory() || $this->rightsObj->isAllowedToCreateEventCategory()){
			$insertFields['category_id'] = $GLOBALS['HTTP_POST_VARS']['category_id'];
		}
		if($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()){
			if($GLOBALS['HTTP_POST_VARS']['event_start_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['event_start_day'], $time);
				$insertFields['start_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}else{
				return;
			}
			if($GLOBALS['HTTP_POST_VARS']['event_start_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['event_start_time'], $time);
				$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
			}
			if($GLOBALS['HTTP_POST_VARS']['event_end_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['event_end_day'], $time);
				$insertFields['end_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($GLOBALS['HTTP_POST_VARS']['event_end_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['event_end_time'], $time);
				$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
			}
		}
		if($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()){
			$insertFields['title'] = $GLOBALS['HTTP_POST_VARS']['title'];
		}
		
		if($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$insertFields['organizer'] = $GLOBALS['HTTP_POST_VARS']['organizer'];
			if($GLOBALS['HTTP_POST_VARS']['organizer_id']!=''){
				$insertFields['organizer_id'] = $GLOBALS['HTTP_POST_VARS']['organizer_id'];
			}
		}
		if($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()){
			$insertFields['location'] = $GLOBALS['HTTP_POST_VARS']['location'];
			if($GLOBALS['HTTP_POST_VARS']['location_id']!=''){
				$insertFields['location_id'] = $GLOBALS['HTTP_POST_VARS']['location_id'];
			}
		}
		if($GLOBALS['HTTP_POST_VARS']['description']!='' && ($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription())){
			$insertFields['description'] = $GLOBALS['HTTP_POST_VARS']['description'];
		}
		if($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()){
			if($GLOBALS['HTTP_POST_VARS']['frequency_id']!=''){
				$insertFields['freq'] = $GLOBALS['HTTP_POST_VARS']['frequency_id'];
			}
			if($GLOBALS['HTTP_POST_VARS']['by_day']!=''){
				$insertFields['byday'] = $GLOBALS['HTTP_POST_VARS']['by_day'];
			}
			if($GLOBALS['HTTP_POST_VARS']['by_monthday']!=''){
				$insertFields['bymonthday'] = $GLOBALS['HTTP_POST_VARS']['by_monthday'];
			}
			if($GLOBALS['HTTP_POST_VARS']['by_month']!=''){
				$insertFields['bymonth'] = $GLOBALS['HTTP_POST_VARS']['by_month'];
			}
			if($GLOBALS['HTTP_POST_VARS']['until']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $GLOBALS['HTTP_POST_VARS']['until'], $time);
				$insertFields['until'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($GLOBALS['HTTP_POST_VARS']['count']!=''){
				$insertFields['cnt'] = $GLOBALS['HTTP_POST_VARS']['count'];
			}
			if($GLOBALS['HTTP_POST_VARS']['interval']!=''){
				$insertFields['intrval'] = $GLOBALS['HTTP_POST_VARS']['interval'];
			}
		}
	}
	
	function insertIdsIntoTableWithMMRelation($mm_table,$idArray,$uid,$tablename){
		foreach($idArray as $foreignid){
			if(is_numeric ($foreignid)){
				$insertFields = array("uid_local"=>$uid, "uid_foreign" => $foreignid, "tablenames" =>$tablename);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($mm_table,$insertFields);
			}
		}
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']);
}
?>