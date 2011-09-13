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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_phpicalendar_model extends tx_cal_model {
	
	var $location;
	var $isException;

	
	function tx_cal_phpicalendar_model(&$cObj, &$rightsObj, $row, $isException, $serviceKey){
		$this->tx_cal_model($cObj, $rightsObj, $serviceKey);		
		$this->createEvent($row, $isException);
		$this->isException = $isException;
	}
	
	function createEvent($row, $isException){
		$this->setType($this->serviceKey);
		$this->setUid($row['uid']);
		$this->setTstamp($row['tstamp']);
		$this->setStartHour($row['start_time']);
		$this->setEndHour($row['end_time']);
		$this->setStartDate($row['start_date']);
		$this->setEndDate($row['end_date']);
		
		$this->setTitle($row['title']);
		$this->setCategories($row['categories']);
		$this->setCalendarUid($row['calendar_id']);
		$this->setFreq($row['freq']);
		$this->setByDay($row['byday']);
		$this->setByMonthDay($row['bymonthday']);
		$this->setByMonth($row['bymonth']);
		$this->setUntil($row['until']);
		$this->setCount($row['cnt']);
		$this->setInterval($row['intrval']);
		
		/* new */
		$this->setEventType($row['type']);
		$this->setPage($row['page']);
		$this->setExtUrl($row['ext_url']);
		/* new */
		
		$this->setImage($row['image']);
		$this->setImageTitleText($row['imagetitletext']);
		$this->setImageAltText($row['imagealttext']);
		$this->setImageCaption($row['imagecaption']);
		
		$this->exception_single_ids = $row['exception_single_ids'];
		$this->exception_group_ids = $row['exception_group_ids'];
		
		if(!$isException){
		
			$this->setOrganizer($row['organizer']);
			$this->setLocation($row['location']);
			$this->setDescription($row['description']);
			
			if($row['location_id']!=0){
			
				$this->cObj->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useLocationStructure = ($this->cObj->confArr['useLocationStructure']?$this->cObj->confArr['useLocationStructure']:'tx_cal_location');
				$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
				$modelcontroller = new $tx_cal_modelcontroller ($this->cObj, $this->rightsObj);
				$location = $modelcontroller->findLocation($row['location_id'], $useLocationStructure);
				$this->setLocationLink($this->controller->pi_linkTP_keepPIvars( $location->getName(), array("view" => "location","uid" => $location->getUid(),"type" => $useLocationStructure)), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);	
				$this->setLocationId($location->getUid());
			}
			
			if($row['organizer_id']!=0){				
				$this->cObj->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useOrganizerStructure = ($this->cObj->confArr['useOrganizerStructure']?$this->cObj->confArr['useOrganizerStructure']:'tx_cal_location');
				$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
				$modelcontroller = new $tx_cal_modelcontroller ($this->cObj, $this->rightsObj);
				$organizer = $modelcontroller->findOrganizer($row['organizer_id'], $useOrganizerStructure);
				$this->setOrganizerLink($this->controller->pi_linkTP_keepPIvars( $organizer->getName(), array("view" => "organizer","uid" => $organizer->getUid(),"type" => $useOrganizerStructure)), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
				$this->setOrganizerId($organizer->getUid());

			}		
		}
	}
	
	function cloneEvent(){
		$tx_cal_phpicalendar_model = t3lib_div :: makeInstanceClassName('tx_cal_phpicalendar_model');
		$event = new $tx_cal_phpicalendar_model ($this->cObj,$this->rightsObj,$this->getValuesAsArray(),$this->isException,$this->getType());
		$event->setIsClone(true);
		return $event;
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
	 
	 /**
	  * Returns the headerstyle name
	  */
	 function getHeaderStyle(){
	 	if(!empty($this->categories) && $this->categories[0]['headerstyle']!=""){
	 		return $this->categories[0]['headerstyle'];
	 	}
	 	return $this->headerstyle;	
	 }
	 
	 /**
	  * Returns the bodystyle name
	  */
	 function getBodyStyle(){
	 	if(!empty($this->categories) && $this->categories[0]['bodystyle']!=""){
	 		return $this->categories[0]['bodystyle'];
	 	}
	 	return $this->bodystyle;	
	 }

	function renderEventForDay() {
		return "<div>".$this->getTitle()."</div>";
	}
	
	function renderEventForAllDay(){
		return '<div class="'.$this->getHeaderStyle().'">'.$this->title.'</div>';
	}
	
	function renderEventForMonth(){
		return $this->formatStr($this->getTitle(),$this->cObj->conf['view.']['month.']['titleCrop']);
	}
	
	/**
	 * Format string with general_stdWrap from configuration
	 *
	 * @param	string		$string to wrap
	 * @return	string		wrapped string
	 */
	function formatStr($str,$cropConf) {
		return $this->cObj->crop($str, $cropConf);
	}

	function renderEvent(){
		$page = $this->cObj->fileResource($this->cObj->conf["view."]["event."]["phpicalendarEventTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no event template file found:</h3>".$this->cObj->conf["view."]["event."]["phpicalendarEventTemplate"];
		}
		$rems = array();
		$sims = array();
		$this->getEventMarker($page,$rems,$sims);
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array());
	}
	
	function renderEventPreview(){
		$page = $this->cObj->fileResource($this->cObj->conf["view."]["event."]["phpicalendarEventTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no event template file found:</h3>".$this->cObj->conf["view."]["event."]["phpicalendarEventTemplate"];
		}
		$rems = array();
		$sims = array();
		$this->getEventMarker($page,$rems,$sims);
		$sims["###DESCRIPTION###"] = $this->formatStr($sims["###DESCRIPTION###"],$this->cObj->conf['view.']['event.']['preview.']['descriptionCrop']);		
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array());
	}

	function getEventMarker(&$template, &$rems, &$sims){
		$lastview = $this->cObj->conf['lastview'];
		$uid  = $this->cObj->conf['uid'];
		$type = $this->cObj->conf['type'];
		$monitoring  = $this->cObj->conf['monitor'];
		$getdate = $this->cObj->conf['getdate'];
		
		$rems["###MONITOR_LOOP###"] = "";
		if($this->cObj->conf['allowSubscribe']==1 && $uid){
		
			if($monitoring!= null && $monitoring!=''){
				
				$user_uid = $this->rightsObj->getUserId();
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
							if ($captchaStr && $this->controller->piVars['captcha']===$captchaStr){
								$table = "tx_cal_unknown_users";
								$select = "uid";
								$where = 'email = "'.$this->controller->piVars['email'].'"';
								$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
								$already_exists = false;
								$user_uid = 0;
								while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
									$already_exists = true;
									$user_uid = $row['uid'];
									break;
								}
								if(!$already_exists){
									$fields_values = array('tstamp' => time(), 'email' => $this->controller->piVars['email']);
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
							if ($captchaStr && $this->controller->piVars['captcha']===$captchaStr){
								$table = "tx_cal_unknown_users";
								$select = "uid";
								$where = 'email = "'.$this->controller->piVars['email'].'"';
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
			
			if($this->rightsObj->isLoggedIn() && $this->cObj->conf['subscribeFeUser']==1){
				
				$select = "*";
				$from_table = "tx_cal_fe_user_event_monitor_mm";
				$whereClause = "uid_foreign = ".$this->rightsObj->getUserId().
						" AND uid_local = ".$uid.
						" AND tablenames = 'fe_users'";
		
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$from_table,$whereClause,$groupBy='',$orderBy='',$limit='');
				$found_one = false;		
				while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_monitor_event_logged_in_monitoring').'" alt="'.$this->shared->lang('l_monitor_event_logged_in_monitoring').'"';
					$rems["###MONITOR_LOOP###"] = "<br />".$this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_monitor_event_logged_in_monitoring'), array("view" => "event", "monitor" => "stop"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'])."<br /><br />";
					$found_one = true;
				}
				if(!$found_one){
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_monitor_event_logged_in_nomonitoring').'" alt="'.$this->shared->lang('l_monitor_event_logged_in_nomonitoring').'"';
					$rems["###MONITOR_LOOP###"] = "<br />".$this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_monitor_event_logged_in_nomonitoring'), array("view" => "event", "monitor" => "start"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'])."<br /><br />";	
				}
			}else if($this->cObj->conf['subscribeWithCaptcha']==1){
				if (t3lib_extMgm::isLoaded('captcha')){
					$notLoggedinNoMonitoring 	= $this->cObj->getSubpart($template, "###NOTLOGGEDIN_NOMONITORING###");
					$parameter = array("no_cache" => 1, "view" => "event", "monitor" => "start", "[type]" => $type, "uid" => $uid);
					$actionUrl = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter);
					$parameter2 = array("no_cache" => 1, "[getdate]" => $getdate, "[lastview]" => $lastview, "[view]" => "event", "[monitor]" => "stop");
					$actionUrl2 = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter2);
					$sims_temp["NOTLOGGEDIN_NOMONITORING_HEADING"] = $this->shared->lang('l_monitor_event_logged_in_nomonitoring');
					$sims_temp["CAPTCHA_SRC"] = t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php';
					$sims_temp["NOTLOGGEDIN_NOMONITORING_SUBMIT"] = $this->shared->lang('l_submit');
					$sims_temp["L_ENTER_EMAIL"] = $this->shared->lang('l_enter_email');
					$sims_temp["L_CAPTCHA_TEXT"] = $this->shared->lang('l_captcha_text');
					$monitor = $this->shared->replace_tags($sims_temp,$notLoggedinNoMonitoring);
					$sims_temp = array();
					$notLoggedinMonitoring 	= $this->cObj->getSubpart($template, "###NOTLOGGEDIN_MONITORING###");
					$sims_temp["CAPTCHA_SRC"] = t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php';
					$sims_temp["NOTLOGGEDIN_MONITORING_HEADING"] = $this->shared->lang('l_monitor_event_logged_in_monitoring');
					$sims_temp["NOTLOGGEDIN_MONITORING_SUBMIT"] = $this->shared->lang('l_submit');
					$sims_temp["L_ENTER_EMAIL"] = $this->shared->lang('l_enter_email');
					$sims_temp["L_CAPTCHA_TEXT"] = $this->shared->lang('l_captcha_text');
					$monitor .= $this->shared->replace_tags($sims_temp,$notLoggedinMonitoring);
					$rems["###MONITOR_LOOP###"] = $monitor;
				} else {
					$rems["###MONITOR_LOOP###"] = '';
				}
				//$rems["###MONITOR###"] = $this->shared->lang('l_monitor_event_not_logged_in');
			}
		}
		$sims['###TITLE###'] = $this->getTitle();
		if($this->getStarttime()==$this->getEndtime()||$this->getEndtime()==0){
			$sims['###STARTTIME_LABEL###'] = "";
			$sims['###ENDTIME_LABEL###'] = "";
			$sims['###STARTTIME###'] = "";
			$sims['###ENDTIME###'] = "";
			if($this->getFreq()=="none"){
				$sims['###STARTDATE###'] = tx_cal_calendar::localizeDate ($this->shared->lang('l_dateFormat_day'), $this->getStartDate(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
				$sims['###STARTDATE_LABEL###'] = $this->shared->lang('l_event_allday');
			}else{
				$sims['###STARTDATE###'] = "";
				$sims['###STARTDATE_LABEL###'] = "";
			}
			$sims['###ENDDATE###'] = "";
			$sims['###ENDDATE_LABEL###'] = "";
		}else{
			$sims['###STARTTIME_LABEL###'] = $this->shared->lang('l_event_starttime');
			$sims['###ENDTIME_LABEL###'] = $this->shared->lang('l_event_endtime');
			$sims['###STARTTIME###'] = date($this->shared->lang('l_timeFormat'), $this->getStarttime());
			$sims['###ENDTIME###'] = date($this->shared->lang('l_timeFormat'), $this->getEndtime());
			$sims['###STARTDATE###'] = tx_cal_calendar::localizeDate ($this->shared->lang('l_dateFormat_day'), $this->getStartDate(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			$sims['###ENDDATE###'] = tx_cal_calendar::localizeDate ($this->shared->lang('l_dateFormat_day'), $this->getEndDate(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			$sims['###STARTDATE_LABEL###'] = $this->shared->lang('l_event_startdate');
			$sims['###ENDDATE_LABEL###'] = $this->shared->lang('l_event_enddate');
		}
		if($this->getOrganizerLink()!=''){
			$sims['###ORGANIZER###'] = $this->getOrganizerLink();
		}else{
			$sims['###ORGANIZER###'] = $this->getOrganizer();
		}
		if($this->getLocationLink()!=''){
			$sims['###LOCATION###'] = $this->getLocationLink();
		}else{
			$sims['###LOCATION###'] = $this->getLocation();
		}
		$sims['###DESCRIPTION###'] = nl2br($this->cObj->parseFunc($this->getDescription(),$this->cObj->conf["parseFunc."]));
		
		$sims['###TITLE_LABEL###'] = $this->shared->lang('l_event_title');
		
		$sims['###ORGANIZER_LABEL###'] = $this->shared->lang('l_event_organizer');
		$sims['###LOCATION_LABEL###'] = $this->shared->lang('l_event_location');
		$sims['###DESCRIPTION_LABEL###'] = $this->shared->lang('l_event_description');
		$sims['###ICSLINK###'] = "";
		if($this->cObj->conf["view."]["ics."]['showIcsLinks']==1){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_ics_view').'" alt="'.$this->shared->lang('l_ics_view').'"';
			$sims['###ICSLINK###'] = $this->controller->pi_linkTP($this->shared->lang('l_event_icslink'), array($this->prefixId."[type]" => $type, $this->prefixId."[view]" => "single_ics", $this->prefixId."[uid]" => $uid, "type" => "150"));
		}
		$sims["###EDITLINK###"] = "";
		if($this->rightsObj->isAllowedToEditEvents()){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_edit_event').'" alt="'.$this->shared->lang('l_edit_event').'"';
			$sims["###EDITLINK###"] = $this->controller->pi_linkTP_keepPIvars('<img src="typo3/gfx/edit2.gif" border="0"/>', array("view" => 'edit_event', "type" => $this->getType(), "uid" => $uid), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
		}
		if($this->rightsObj->isAllowedToDeleteEvents()){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_delete_event').'" alt="'.$this->shared->lang('l_delete_event').'"';
			$sims["###EDITLINK###"] .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm::siteRelPath('cal').'template/img/delete.png" border="0"/>', array("view" => 'delete_event', "type" => $this->getType(), "uid" => $uid), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
		}
		$sims['###MORE_LINK###'] = "";
		if($this->cObj->conf["view."]["event."]["isPreview"]){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_more').'" alt="'.$this->shared->lang('l_more').'"';
			if (!empty ($this->cObj->conf["view."]["event."]["eventViewPid"])) {
				$sims['###MORE_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_more'), array ("page_id" => t3lib_div :: _GP("id"),"preview" => null, 'view' => event, 'uid' => $this->getUid(), 'type' => $this->getType(), 'lastview' => $this->cObj->conf['view']), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'],  $this->cObj->conf["view."]["event."]["eventViewPid"]);
			}else{
				$sims['###MORE_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_more'), array("preview" => null, 'view' => event, 'uid' => $this->getUid(), 'type' => $this->getType(), 'lastview' => $this->cObj->conf['view']),$this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			}
		}
		$sims['###HEADING###'] = $this->shared->lang('l_event');
		$this->getImageMarkers($sims, $this->cObj->conf['view.']['event.'],true);
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=="true" && 
				($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;
//		if($this->controller->piVars['start']!=''){
//			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['start'], $time);
//			$insertFields['start'] = mktime(0,0,0,$time[2],$time[3],$time[1]);;
//		}
//		if($this->controller->piVars['end']!=''){
//			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['end'], $time);
//			$insertFields['end'] = mktime(0,0,0,$time[2],$time[3],$time[1]);;
//		}
		if($this->rightsObj->isAllowedToEditEventCategory() || $this->rightsObj->isAllowedToCreateEventCategory()){
			$insertFields['category_id'] = $this->controller->piVars['category_id'];
		}
		if($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()){
			if($this->controller->piVars['event_start_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_start_day'], $time);
				$insertFields['start_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}else{
				return;
			}
			if($this->controller->piVars['event_start_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_start_time'], $time);
				$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
			}
			if($this->controller->piVars['event_end_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_end_day'], $time);
				$insertFields['end_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['event_end_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_end_time'], $time);
				$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
			}
		}
		if($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()){
			$insertFields['title'] = $this->controller->piVars['title'];
		}
		
		if($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$insertFields['organizer'] = $this->controller->piVars['organizer'];
			if($this->controller->piVars['organizer_id']!=''){
				$insertFields['organizer_id'] = $this->controller->piVars['organizer_id'];
			}
		}
		if($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()){
			$insertFields['location'] = $this->controller->piVars['location'];
			if($this->controller->piVars['location_id']!=''){
				$insertFields['location_id'] = $this->controller->piVars['location_id'];
			}
		}
		if($this->controller->piVars['description']!='' && ($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription())){
			$insertFields['description'] = $this->controller->piVars['description'];
		}
		if($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()){
			if($this->controller->piVars['frequency_id']!=''){
				$insertFields['freq'] = $this->controller->piVars['frequency_id'];
			}
			if($this->controller->piVars['by_day']!=''){
				$insertFields['byday'] = $this->controller->piVars['by_day'];
			}
			if($this->controller->piVars['by_monthday']!=''){
				$insertFields['bymonthday'] = $this->controller->piVars['by_monthday'];
			}
			if($this->controller->piVars['by_month']!=''){
				$insertFields['bymonth'] = $this->controller->piVars['by_month'];
			}
			if($this->controller->piVars['until']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['until'], $time);
				$insertFields['until'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['count']!=''){
				$insertFields['cnt'] = $this->controller->piVars['count'];
			}
			if($this->controller->piVars['interval']!=''){
				$insertFields['intrval'] = $this->controller->piVars['interval'];
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
	
	function search(&$cObj, $pidList=''){
		$this->cObj = $cObj;
		$this->cObj->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->cObj);
		
		$categoryIds = $this->arrayToCommaseparatedString($this->getCategoryArray());
		$sw = $this->controller->piVars['query'];
		$events=array();
		if($sw!=""){
			$additionalWhere = "AND tx_cal_category.uid IN (".$categoryIds.") AND tx_cal_event.pid IN (".$pidList.") AND tx_cal_event.hidden = 0 AND tx_cal_event.deleted = 0 ".$this->searchWhere($sw);
			$events = $this->getEventsFromTable(true, $additionalWhere);
		}
		return $events;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->cObj->conf["view."]["search."]["searchEventFieldList"], 'tx_cal_event');
		return $where;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']);
}
?>