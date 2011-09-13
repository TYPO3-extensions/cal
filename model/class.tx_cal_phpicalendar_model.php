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
	var $createUserId;
	
	function tx_cal_phpicalendar_model(&$controller, $row, $isException, $serviceKey){
		$this->tx_cal_model($controller, $serviceKey);		
		$this->createEvent($row, $isException);
		$this->isException = $isException;
	}
	
	function createEvent($row, $isException){
//debug($row);
		$this->setType($this->serviceKey);
		$this->setUid($row['uid']);
		$this->setCreateUserId($row['cruser_id']);
		$this->setHidden($row['hidden']);
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
		
		$this->eventOwner = $row['event_owner'];
		
		if(!$isException){
		
			$this->setOrganizer($row['organizer']);
			$this->setLocation($row['location']);
			$this->setDescription($row['description']);
			
			if($row['location_id']!=0){
			
				$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');
				$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
				$modelcontroller = new $tx_cal_modelcontroller ($this->controller);
				$location = $modelcontroller->findLocation($row['location_id'], $useLocationStructure);
				$this->setLocationLink($this->controller->pi_linkTP_keepPIvars( $location->getName(), array("view" => "location","uid" => $location->getUid(),"type" => $useLocationStructure)), $this->conf['cache'], $this->conf['clear_anyway']);	
				$this->setLocationId($location->getUid());
			}
			
			if($row['organizer_id']!=0){				
				$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_location');
				$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
				$modelcontroller = new $tx_cal_modelcontroller ($this->controller);
				$organizer = $modelcontroller->findOrganizer($row['organizer_id'], $useOrganizerStructure);
				$this->setOrganizerLink($this->controller->pi_linkTP_keepPIvars( $organizer->getName(), array("view" => "organizer","uid" => $organizer->getUid(),"type" => $useOrganizerStructure)), $this->conf['cache'], $this->conf['clear_anyway']);
				$this->setOrganizerId($organizer->getUid());

			}		
		}
		
		$this->sharedUsers = array();
		$table = "tx_cal_event_shared_user_mm";
		$select = "uid_foreign";
		$where = 'uid_local = '.$this->getUid();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->sharedUsers[] = $row['uid_foreign'];
		}

		//startdate but no enddate fix
		if($this->startdate && !$this->enddate){
			$this->setEnddate($this->startdate);
		} 
	}
	
	function cloneEvent(){
		$tx_cal_phpicalendar_model = t3lib_div :: makeInstanceClassName('tx_cal_phpicalendar_model');
		$event = new $tx_cal_phpicalendar_model ($this->controller,$this->getValuesAsArray(),$this->isException,$this->getType());
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
	 	if($this->conf['view.']['event.']['differentStyleIfOwnEvent'] && $this->rightsObj->getUserId()==$this->getCreateUserId()){
	 		return $this->conf['view.']['event.']['headerStyleOfOwnEvent'];
	 	}else if(!empty($this->categories) && $this->categories[0]['headerstyle']!=""){
	 		return $this->categories[0]['headerstyle'];
	 	}
	 	return $this->headerstyle;	
	 }
	 
	 /**
	  * Returns the bodystyle name
	  */
	 function getBodyStyle(){
	 	if($this->conf['view.']['event.']['differentStyleIfOwnEvent'] && $this->rightsObj->getUserId()==$this->getCreateUserId()){
	 		return $this->conf['view.']['event.']['bodyStyleOfOwnEvent'];
	 	}if(!empty($this->categories) && $this->categories[0]['bodystyle']!=""){
	 		return $this->categories[0]['bodystyle'];
	 	}
	 	return $this->bodystyle;	
	 }
	 
	 /**
	 *  Gets the createUserId of the event.
	 *  
	 *  @return		string		The create user id.
	 */
	function getCreateUserId() { 
		return $this->createUserId; 
	}
	
	
	/**
	 *  Sets the createUserId of the event.
	 *
	 *  @param		string		The create user id.
	 *  @return		void
	 */
	function setCreateUserId($createUserId) {
		$this->createUserId = $createUserId;
	}

	function renderEventForDay() {
		return $this->cObj->stdWrap($this->getTitle(), $this->conf['view.']['day.']['title_stdWrap.']);
	}
	
	function renderEventForWeek() {
		return $this->cObj->stdWrap($this->getTitle(), $this->conf['view.']['week.']['title_stdWrap.']);
	}
	
	function renderEventForAllDay(){
		$return = $this->cObj->stdWrap($this->getTitle(), $this->conf['view.']['day.']['alldayTitle_stdWrap.']);
		return str_replace('###STYLECLASS###',$this->getHeaderStyle(),$return);
	}
	
	function renderEventForMonth(){
		return $this->cObj->stdWrap($this->getTitle(), $this->conf['view.']['month.']['title_stdWrap.']);
	}
	
	function renderEventForList(){
		return $this->cObj->stdWrap($this->getTitle(), $this->conf['view.']['list.']['title_stdWrap.']);
	}

	function renderEvent(){
		$page = $this->cObj->fileResource($this->conf["view."]["event."]["phpicalendarEventTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no event template file found:</h3>".$this->conf["view."]["event."]["phpicalendarEventTemplate"];
		}
		$rems = array();
		$sims = array();
		$this->getEventMarker($page,$rems,$sims);
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array());
	}
	
	function renderEventPreview(){
		$page = $this->cObj->fileResource($this->conf["view."]["event."]["phpicalendarEventTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no event template file found:</h3>".$this->conf["view."]["event."]["phpicalendarEventTemplate"];
		}
		$rems = array();
		$sims = array();
		$this->getEventMarker($page,$rems,$sims);
		$sims["###DESCRIPTION###"] = $this->cObj->stdWrap($sims["###DESCRIPTION###"], $this->conf['view.']['event.']['preview.']['stdWrap.']);		
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array());
	}

	function getEventMarker(&$template, &$rems, &$sims){
		$lastview = $this->conf['lastview'];
		$uid  = $this->conf['uid'];
		$type = $this->conf['type'];
		$monitoring  = $this->conf['monitor'];
		$getdate = $this->conf['getdate'];
		
		$rems["###MONITOR_LOOP###"] = "";
		if($this->conf['allowSubscribe']==1 && $uid){
		
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
								$where = 'email = "'.strip_tags($this->controller->piVars['email']).'"';
								$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
								$already_exists = false;
								$user_uid = 0;
								while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
									$already_exists = true;
									$user_uid = $row['uid'];
									break;
								}
								if(!$already_exists){
									$fields_values = array('tstamp' => time(), 'email' => strip_tags($this->controller->piVars['email']));
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
								$where = 'email = "'.strip_tags($this->controller->piVars['email']).'"';
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
			
			if($this->rightsObj->isLoggedIn() && $this->conf['subscribeFeUser']==1){
				
				$select = "*";
				$from_table = "tx_cal_fe_user_event_monitor_mm";
				$whereClause = "uid_foreign = ".$this->rightsObj->getUserId().
						" AND uid_local = ".$uid.
						" AND tablenames = 'fe_users'";
		
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$from_table,$whereClause,$groupBy='',$orderBy='',$limit='');
				$found_one = false;		
				while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_monitor_event_logged_in_monitoring').'" alt="'.$this->controller->pi_getLL('l_monitor_event_logged_in_monitoring').'"';
					$rems["###MONITOR_LOOP###"] = "<br />".$this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_monitor_event_logged_in_monitoring'), array("view" => "event", "monitor" => "stop"), $this->conf['cache'], $this->conf['clear_anyway'])."<br /><br />";
					$found_one = true;
				}
				if(!$found_one){
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring').'" alt="'.$this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring').'"';
					$rems["###MONITOR_LOOP###"] = "<br />".$this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring'), array("view" => "event", "monitor" => "start"), $this->conf['cache'], $this->conf['clear_anyway'])."<br /><br />";	
				}
			}else if($this->conf['subscribeWithCaptcha']==1){
				if (t3lib_extMgm::isLoaded('captcha')){
					$notLoggedinNoMonitoring 	= $this->cObj->getSubpart($template, "###NOTLOGGEDIN_NOMONITORING###");
					$parameter = array("no_cache" => 1, "view" => "event", "monitor" => "start", "[type]" => $type, "uid" => $uid);
					$actionUrl = $this->controller->pi_linkTP_keepPIvars_url($parameter);
					$parameter2 = array("no_cache" => 1, "[getdate]" => $getdate, "[lastview]" => $lastview, "[view]" => "event", "[monitor]" => "stop");
					$actionUrl2 = $this->controller->pi_linkTP_keepPIvars_url($parameter2);
					$sims_temp["NOTLOGGEDIN_NOMONITORING_HEADING"] = $this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring');
					$sims_temp["CAPTCHA_SRC"] = t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php';
					$sims_temp["NOTLOGGEDIN_NOMONITORING_SUBMIT"] = $this->controller->pi_getLL('l_submit');
					$sims_temp["L_ENTER_EMAIL"] = $this->controller->pi_getLL('l_enter_email');
					$sims_temp["L_CAPTCHA_TEXT"] = $this->controller->pi_getLL('l_captcha_text');
					$monitor = $this->controller->replace_tags($sims_temp,$notLoggedinNoMonitoring);
					$sims_temp = array();
					$notLoggedinMonitoring 	= $this->cObj->getSubpart($template, "###NOTLOGGEDIN_MONITORING###");
					$sims_temp["CAPTCHA_SRC"] = t3lib_extMgm::siteRelPath('captcha').'captcha/captcha.php';
					$sims_temp["NOTLOGGEDIN_MONITORING_HEADING"] = $this->controller->pi_getLL('l_monitor_event_logged_in_monitoring');
					$sims_temp["NOTLOGGEDIN_MONITORING_SUBMIT"] = $this->controller->pi_getLL('l_submit');
					$sims_temp["L_ENTER_EMAIL"] = $this->controller->pi_getLL('l_enter_email');
					$sims_temp["L_CAPTCHA_TEXT"] = $this->controller->pi_getLL('l_captcha_text');
					$monitor .= $this->controller->replace_tags($sims_temp,$notLoggedinMonitoring);
					$rems["###MONITOR_LOOP###"] = $monitor;
				} else {
					$rems["###MONITOR_LOOP###"] = '';
				}
				//$rems["###MONITOR###"] = $this->controller->pi_getLL('l_monitor_event_not_logged_in');
			}
		}
		$sims['###TITLE###'] = $this->getTitle();
		if($this->getStarttime()==$this->getEndtime()||$this->getEndtime()==0){
			$sims['###STARTTIME_LABEL###'] = "";
			$sims['###ENDTIME_LABEL###'] = "";
			$sims['###STARTTIME###'] = "";
			$sims['###ENDTIME###'] = "";
			if($this->getFreq()=="none"){
				$sims['###STARTDATE###'] = strftime($this->conf['view.']['event.']['dateFormat'], $this->getStartDate());
				$sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_allday');
			}else{
				$sims['###STARTDATE###'] = "";
				$sims['###STARTDATE_LABEL###'] = "";
			}
			$sims['###ENDDATE###'] = "";
			$sims['###ENDDATE_LABEL###'] = "";
		}else{
			$sims['###STARTTIME_LABEL###'] = $this->controller->pi_getLL('l_event_starttime');
			$sims['###ENDTIME_LABEL###'] = $this->controller->pi_getLL('l_event_endtime');
			$sims['###STARTTIME###'] = strftime($this->conf['view.']['event.']['timeFormat'], $this->getStarttime());
			$sims['###ENDTIME###'] = strftime($this->conf['view.']['event.']['timeFormat'], $this->getEndtime());
			$sims['###STARTDATE###'] = strftime($this->conf['view.']['event.']['dateFormat'], $this->getStartDate());
			$sims['###ENDDATE###'] = strftime($this->conf['view.']['event.']['dateFormat'], $this->getEndDate());
			$sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_startdate');
			$sims['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
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
		$sims['###DESCRIPTION###'] = nl2br($this->cObj->parseFunc($this->getDescription(),$this->conf["parseFunc."]));
		
		$sims['###TITLE_LABEL###'] = $this->controller->pi_getLL('l_event_title');
		
		$sims['###ORGANIZER_LABEL###'] = $this->controller->pi_getLL('l_event_organizer');
		$sims['###LOCATION_LABEL###'] = $this->controller->pi_getLL('l_event_location');
		$sims['###DESCRIPTION_LABEL###'] = $this->controller->pi_getLL('l_event_description');
		$sims['###ICSLINK###'] = "";
		if($this->conf["view."]["ics."]['showIcsLinks']==1){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_ics_view').'" alt="'.$this->controller->pi_getLL('l_ics_view').'"';
			$sims['###ICSLINK###'] = $this->controller->pi_linkTP($this->controller->pi_getLL('l_event_icslink'), array($this->prefixId."[type]" => $type, $this->prefixId."[view]" => "single_ics", $this->prefixId."[uid]" => $uid, "type" => "150"));
		}
		$sims["###MAP###"] = '';

		if($this->conf["view."]["event."]['showMap'] && $this->getLocationId()){
			/* Pull values from Flexform object into individual variables */
			   
 	        $apiKey = $this->conf['view.']['event.']['map.']['apiKey'];
 	        $width = $this->conf['view.']['event.']['map.']['mapWidth'];
 	        $height = $this->conf['view.']['event.']['map.']['mapHeight'];

			include_once(t3lib_extMgm::extPath('cal').'wec_map/class.tx_wecmap_map_google.php');
 	        $className=t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
 	        
 	        
 	        $this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');
			$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
			$this->modelObj = new $tx_cal_modelcontroller ($this->controller);		
			$location = $this->modelObj->findLocation($this->getLocationId(),$useLocationStructure);
			
			$map = new $className($apiKey, $width, $height);
			$map->addMarkerByLatLong($location->getLongitude(), $location->getLatitude(), $location->getName(), $location->getDescription());
// 	        $country = '';longitude
// 	        $zone = '';latitude
// 	        $map->addMarkerByAddress($location->getStreet(), $location->getCity(), $zone, $location->getPostalCode(), $country, $location->getName(), $location->getDescription());
			
 	        /* Draw the map */
 	        $sims["###MAP###"] = $map->drawMap();
		}
		$sims["###EDITLINK###"] = "";
		if($this->isUserAllowedToEdit()){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_event').'" alt="'.$this->controller->pi_getLL('l_edit_event').'"';
			$sims["###EDITLINK###"] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['event.']['editIcon'], array("view" => 'edit_event', "type" => $this->getType(), "uid" => $uid), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		if($this->isUserAllowedToDelete()){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_delete_event').'" alt="'.$this->controller->pi_getLL('l_delete_event').'"';
			$sims["###EDITLINK###"] .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['event.']['deleteIcon'], array("view" => 'delete_event', "type" => $this->getType(), "uid" => $uid), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$sims['###MORE_LINK###'] = "";
		if($this->conf["view."]["event."]["isPreview"]){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_more').'" alt="'.$this->controller->pi_getLL('l_more').'"';
			if (!empty ($this->conf["view."]["event."]["eventViewPid"])) {
				$sims['###MORE_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_more'), array ("page_id" => $GLOBALS['TSFE']->id,"preview" => null, 'view' => event, 'uid' => $this->getUid(), 'type' => $this->getType(), 'lastview' => $this->conf['view']), $this->conf['cache'], $this->conf['clear_anyway'],  $this->conf["view."]["event."]["eventViewPid"]);
			}else{
				$sims['###MORE_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_more'), array("preview" => null, 'view' => event, 'uid' => $this->getUid(), 'type' => $this->getType(), 'lastview' => $this->conf['view']),$this->conf['cache'], $this->conf['clear_anyway']);
			}
		}
		$sims['###HEADING###'] = $this->controller->pi_getLL('l_event');
		$this->getImageMarkers($sims, $this->conf['view.']['event.'],true);
	}
	
	function retrievePostData(&$insertFields){
		$hidden = 0;
		if($this->controller->piVars['hidden']=="true" && 
				($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if($this->rightsObj->isAllowedToEditEventCategory() || $this->rightsObj->isAllowedToCreateEventCategory()){
			$insertFields['category_id'] = intval($this->controller->piVars['category_id']);
		}
		if($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()){
			if($this->controller->piVars['event_start_day']!=''){
				$time = array();
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_start_day']), $time);
				$insertFields['start_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}else{
				return;
			}
			if($this->controller->piVars['event_start_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_start_time']), $time);
				$insertFields['start_time'] = $time[1]*3600+$time[2]*60;
			}
			if($this->controller->piVars['event_end_day']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_end_day']), $time);
				$insertFields['end_date'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['event_end_time']!=''){
				preg_match ('/([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['event_end_time']), $time);
				$insertFields['end_time'] = $time[1]*3600+$time[2]*60;
			}
		}
		if($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()){
			$insertFields['title'] = strip_tags($this->controller->piVars['title']);
		}
		
		if($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$insertFields['organizer'] = strip_tags($this->controller->piVars['organizer']);
			if($this->controller->piVars['organizer_id']!=''){
				$insertFields['organizer_id'] = intval($this->controller->piVars['organizer_id']);
			}
		}
		if($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()){
			$insertFields['location'] = strip_tags($this->controller->piVars['location']);
			if($this->controller->piVars['location_id']!=''){
				$insertFields['location_id'] = intval($this->controller->piVars['location_id']);
			}
		}
		if($this->controller->piVars['description']!='' && ($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription())){
			$insertFields['description'] = $this->cObj->removeBadHTML($this->controller->piVars['description'],$this->conf);
		}
		if($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()){
			if($this->controller->piVars['frequency_id']!=''){
				$insertFields['freq'] = intval($this->controller->piVars['frequency_id']);
			}
			if($this->controller->piVars['by_day']!=''){
				$insertFields['byday'] = strip_tags($this->controller->piVars['by_day']);
			}
			if($this->controller->piVars['by_monthday']!=''){
				$insertFields['bymonthday'] = strip_tags($this->controller->piVars['by_monthday']);
			}
			if($this->controller->piVars['by_month']!=''){
				$insertFields['bymonth'] = strip_tags($this->controller->piVars['by_month']);
			}
			if($this->controller->piVars['until']!=''){
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', intval($this->controller->piVars['until']), $time);
				$insertFields['until'] = mktime(0,0,0,$time[2],$time[3],$time[1]);
			}
			if($this->controller->piVars['count']!=''){
				$insertFields['cnt'] = intval($this->controller->piVars['count']);
			}
			if($this->controller->piVars['interval']!=''){
				$insertFields['intrval'] = intval($this->controller->piVars['interval']);
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
	
	function search($pidList=''){
		
		$categoryIds = $this->arrayToCommaseparatedString($this->getCategoryArray($pidList));
		$sw = strip_tags($this->controller->piVars['query']);
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
		$where = $this->cObj->searchWhere($sw, $this->conf["view."]["search."]["searchEventFieldList"], 'tx_cal_event');
		return $where;
	}
	
	function getValuesAsArray(){
		$values = parent::getValuesAsArray();
		$values['event_owner'] = $this->eventOwner;
		return $values;
	}
	
	function isUserAllowedToEdit($feUserUid='', $feGroupsArray=array()){
		if($this->rightsObj->isCalAdmin()){
			return true;
		}
		$editOffset = $this->conf['rights.']['edit.']['event.']['timeOffset']*60;
			
		if($feUserUid==''){
			$feUserUid = $this->rightsObj->getUserId();
		}
		if(empty($feGroupsArray)){
			$feGroupsArray = $this->rightsObj->getUserGroups();
		}
		$isEventOwner = $this->isEventOwner($this->rightsObj->getUserId(),$this->rightsObj->getUserGroups()) ;
		$isSharedUser = $this->isSharedUser($this->rightsObj->getUserId());
		if($this->rightsObj->isAllowedToEditStartedEvents()){
			$eventHasntStartedYet = true;
		}else{
			$eventHasntStartedYet = $this->getStarttime()>time()+$editOffset;
		}
		$isAllowedToEditEvents = $this->rightsObj->isAllowedToEditEvents();
		$isAllowedToEditOwnEventsOnly = $this->rightsObj->isAllowedToEditOnlyOwnEvents();
		
		if($isAllowedToEditOwnEventsOnly){
			return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
		}
		return $isAllowedToEditEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
	}
	
	function isUserAllowedToDelete($feUserUid='', $feGroupsArray=array()){
		if($this->rightsObj->isCalAdmin()){
			return true;
		}
		$deleteOffset = $this->conf['rights.']['delete.']['event.']['timeOffset']*60;
		if($feUserUid==''){
			$feUserUid = $this->rightsObj->getUserId();
		}
		if(empty($feGroupsArray)){
			$feGroupsArray = $this->rightsObj->getUserGroups();
		}
		$isEventOwner = $this->isEventOwner($this->rightsObj->getUserId(),$this->rightsObj->getUserGroups()) ;
		$isSharedUser = $this->isSharedUser($this->rightsObj->getUserId());
		if($this->rightsObj->isAllowedToDeleteStartedEvents()){
			$eventHasntStartedYet = true;
		}else{
			$eventHasntStartedYet = $this->getStarttime()>time()+$deleteOffset;
		}
		$isAllowedToDeleteEvents = $this->rightsObj->isAllowedToDeleteEvents();
		$isAllowedToDeleteOwnEventsOnly = $this->rightsObj->isAllowedToDeleteOnlyOwnEvents();
		
		if($isAllowedToDeleteOwnEventsOnly){
			return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
		}
		return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']);
}
?>