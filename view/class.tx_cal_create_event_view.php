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

require_once(PATH_t3lib.'class.t3lib_svbase.php');
require_once(PATH_tslib."class.tslib_pibase.php");
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_shared.php');


/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_event_view extends t3lib_svbase{
	
	var $local_pibase;
	var $cObj;
	var $conf;
	var $shared;
	var $prefixId = 'tx_cal_controller';
	

	/**
	 *  Draws a create event form.
	 *  @param      int         A date to create the event for. Format: yyyymmdd
	 *  @param      object      The cObject of the mother-class
	 *  @param      object      The rights object
	 *  @param		string		Comma separated list of pids.
	 *  @param      object      A phpicalendar object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateEvent($getdate, $cObj, $rightsObj, $pidList, $event=''){
		$this->cObj = $cObj;
		
		//TODO: check if extension rlmp_dateselectlib is loaded
		require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');
		tx_rlmpdateselectlib::includeLib();
		
		//TODO: Get the global FE-COnfiguration for date/time-format
		$dateSelectorConf = array('calConf.' => array (
                                           //'dateTimeFormat' => '%Y%m%d',
                                           //'inputFieldDateTimeFormat' => '%Y%m%d',
                                           'toolTipDateTimeFormat' => '%Y%m%d',
                                           //'showMethod' => 'absolute',
                                           //'showPositionAbsolute' => '100,150',
                                           
                                           //'stylesheet' => 'fileadmin/mystyle.css'
                              )
    );

		$page = $this->cObj->fileResource($cObj->conf["view."]["event."]["createEventTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no create event template file found:</h3>".$cObj->conf["view."]["event."]["createEventTemplate"];
		}
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->cObj);
		
		$form 	= $this->cObj->getSubpart($page, "###FORM_START###");
		$validation = "";
		$languageArray ['type'] = "tx_cal_phpicalendar";
		
		
		// If an event has been passed on the form is a edit form
		if(is_object($event)){
			$languageArray ['l_edit_event'] = $this->shared->lang('l_edit_event');
			if($rightsObj->isAllowedToEditEventHidden()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
				$languageArray['l_hidden'] = $this->shared->lang('l_hidden');
				$languageArray['hidden'] = "";
			}
			if($rightsObj->isAllowedToEditEventCategory()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_CATEGORY###");
				
				// creating options for category
				$category = "";//'<option value="">'.$this->shared->lang('l_select').'</option>';
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_category","pid IN (".$pidList.") AND deleted = 0 AND hidden = 0");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if($event->getCategoryId()==$row['uid']){
						$category .= '<option value="'.$row['uid'].'" selected>'.$row['title'].'</option>';
					}else{
						$category .= '<option value="'.$row['uid'].'">'.$row['title'].'</option>';
					}
				}

				$languageArray['l_category'] = $this->shared->lang('l_event_category');
				$languageArray['category_ids'] = $category;
			}
			if($rightsObj->isAllowedToEditEventDateTime()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_DATETIME###");
				$event_start_day = date("Ymd",$event->getStartdate());
				$event_end_day = date("Ymd",$event->getEnddate());
				$event_start_time = date("Hi",$event->getStarttime());
				$event_end_time = date("Hi",$event->getEndtime());
				
				$start_hour = $this->getHourFromTime($this->cObj->conf['gettime']);
				$start_minute = $this->getMinutesFromTime($this->cObj->conf['gettime']);
				
				$start_time_minute = $this->getMinutesFromTime($event_start_time);
				$start_time_hour = $this->getHourFromTime($event_start_time);
				$end_time_minute = $this->getMinutesFromTime($event_end_time);
				$end_time_hour = $this->getHourFromTime($event_end_time);
				if($start_hour == "23") {
					$end_hour = "00";
				}
				$end_hour = $start_hour + 1;
				$end_minute = $start_minute;
				$start_hours = '';
				$end_hours = '';
				for ($i=0;$i<24;$i++) {
					$value = str_pad($i, 2, "0", STR_PAD_LEFT);
					$start_hours .= '<option value="'.$value.'"'.($start_time_hour==$value?' selected="selected""':'').'>'.$value.'</option>';
					$end_hours .= '<option value="'.$value.'"'.($end_time_hour==$value?' selected="selected""':'').'>'.$value.'</option>';
				}

				$start_minutes = '';
				$end_minutes = '';
				for ($i=0;$i<60;$i++) {
					$value = str_pad($i, 2, "0", STR_PAD_LEFT);
					$start_minutes .= '<option value="'.$value.'"'.($start_time_minute==$value?' selected="selected""':'').'>'.$value.'</option>';
					$end_minutes .= '<option value="'.$value.'"'.($end_time_minute==$value?' selected="selected""':'').'>'.$value.'</option>';
				}
				$languageArray['start_hours'] = $start_hours;
				$languageArray['end_hours'] = $end_hours;
				$languageArray['start_minutes'] = $start_minutes;
				$languageArray['end_minutes'] = $end_minutes;
				
				$languageArray['l_event_start_day'] = $this->shared->lang('l_event_edit_startdate');
				$languageArray['event_start_day'] = $event_start_day;
				$languageArray['start_day_selector'] = tx_rlmpdateselectlib::getInputButton ('event_start_day',$dateSelectorConf);
				$languageArray['l_event_start_time'] = $this->shared->lang('l_event_edit_starttime');
				$languageArray['event_start_time'] = $event_start_time;
				$languageArray['l_event_end_day'] = $this->shared->lang('l_event_edit_enddate');
				$languageArray['event_end_day'] = $event_end_day;
				$languageArray['end_day_selector'] = tx_rlmpdateselectlib::getInputButton ('event_end_day',$dateSelectorConf);
				$languageArray['l_event_end_time'] = $this->shared->lang('l_event_edit_endtime');
				$languageArray['event_end_time'	] = $event_end_time;
			}
			if($rightsObj->isAllowedToEditEventTitle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
				$languageArray['l_title'] = $this->shared->lang('l_event_title');
				$languageArray['title'] = $event->getTitle();
			}
			
			if($rightsObj->isAllowedToEditEventException() || $rightsObj->isAllowedToEditEventNotify()){
				
				// selecting uids of available creator & -groups
//				$cal_user = "";
//				$cal_user_selected = "";
//				$cal_user_ids = array();
//				$where = " AND tx_cal_event.uid=".$event->getUid()." AND tx_cal_fe_user_event_fe_user_mm.tablenames='fe_users' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0";
//				$orderBy = "";
//				$groupBy = "";
//				$limit = "";
//
//				$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("fe_users.*","tx_cal_event","tx_cal_fe_user_event_fe_user_mm","fe_users",$where,$groupBy ,$orderBy,$limit);
//				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {			
//					array_push($cal_user_ids,$row['uid']);
//				}				
//				$cal_group_ids = array();
//				$where = " AND tx_cal_event.uid=".$event->getUid()." AND tx_cal_fe_user_event_fe_user_mm.tablenames='fe_groups' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0";
//				$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("fe_groups.*","tx_cal_event","tx_cal_fe_user_event_fe_user_mm","fe_groups",$where,$groupBy ,$orderBy,$limit);
//				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//					array_push($cal_group_ids,$row['uid']);
//				}
				
				// selectionf uids of available notify/monitor users & -groups
				$cal_notify_user = "";
				$cal_notify_user_ids = array();
				$where = " AND tx_cal_event.uid=".$event->getUid()." AND tx_cal_fe_user_event_monitor_mm.tablenames='fe_users' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0";
				$orderBy = "";
				$groupBy = "";
				$limit = "";
				$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("fe_users.*","tx_cal_event","tx_cal_fe_user_event_monitor_mm","fe_users",$where,$groupBy ,$orderBy,$limit);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					array_push($cal_notify_user_ids,$row['uid']);
				}
	
				$cal_notify_group_ids = array();
				$where = " AND tx_cal_event.uid=".$event->getUid()." AND tx_cal_fe_user_event_monitor_mm.tablenames='fe_groups' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0";
				$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query("fe_groups.*","tx_cal_event","tx_cal_fe_user_event_monitor_mm","fe_groups",$where,$groupBy ,$orderBy,$limit);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					array_push($cal_notify_group_ids,$row['uid']);
				}
				
				if($rightsObj->isAllowedToEditEventException()){
					
					$validation .= "case 'exception_ids_selected[]': selectAll(form.elements[i]);break;";
					// creating options for exception events & -groups
					$exception = "";
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_exception_event","pid in (".$pidList.")");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						if(array_search($row['uid'], $event->getExceptionSingleIds())!==false){
							$exception_selected .= '<option value="s_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_exception_ids\',\''.$row['title'].'\',\'s_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_exception_ids_selected\');removeOption(\'tx_cal_controller_exception_ids_selected\',this.index);">'.$row['title'].'</option>';
						}else{
							$exception .= '<option value="s_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_exception_ids_selected\',\''.$row['title'].'\',\'s_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_exception_ids\');removeOption(\'tx_cal_controller_exception_ids\',this.index);">'.$row['title'].'</option>';
						}
					}			
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_exception_event_group","pid in (".$pidList.")");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						if(array_search($row['uid'], $event->getExceptionGroupIds())!==false){
							$exception_selected .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_exception_ids\',\''.$row['title'].'\',\'g_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_exception_ids_selected\');removeOption(\'tx_cal_controller_exception_ids_selected\',this.index);">'.$row['title'].'</option>';
						}else{
							$exception .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_exception_ids_selected\',\''.$row['title'].'\',\'g_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_exception_ids\');removeOption(\'tx_cal_controller_exception_ids\',this.index);">'.$row['title'].'</option>';
						}
					}
					$languageArray['l_exception'] = $this->shared->lang('l_event_exception');
					$languageArray['exception_ids'] = $exception;
					$languageArray['l_exception_selected'] = $this->shared->lang('l_event_exception_selected');
					$languageArray['exception_ids_selected']= $exception_selected;
				}
	
				if($rightsObj->isAllowedToEditEventNotify()){
					
					if($cObj->conf["rights."]['allowedUsers']!=""){
						$validation .= "case 'notify_ids_selected[]': selectAll(form.elements[i]);break;";								
						// creating options for exceptions and monitoring - users
						$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_users","uid in (".$cObj->conf["rights."]['allowedUsers'].")");
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
							if(array_search($row['uid'],$cal_notify_user_ids)!==false){
								$cal_notify_user_selected .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_notify_ids\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_notify_ids_selected\');removeOption(\'tx_cal_controller_notify_ids_selected\',this.index);">'.$row['username'].' (U)</option>';
							}else{
								$cal_notify_user .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_notify_ids_selected\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_notify_ids\');removeOption(\'tx_cal_controller_notify_ids\',this.index);">'.$row['username'].' (U)</option>';
							}
//							if($rightsObj->isAllowedToEditEventCreator()){
//								if(array_search($row['uid'],$cal_user_ids)!==false){
//								$cal_user_selected .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_user_ids\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_user_ids_selected\');removeOption(\'tx_cal_controller_user_ids_selected\',this.index);">'.$row['username'].' (U)</option>';
//								}else{
//									$cal_user .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_user_ids_selected\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_user_ids\');removeOption(\'tx_cal_controller_user_ids\',this.index);">'.$row['username'].' (U)</option>';
//								}
//							}
						}
						$only_notify_user = $cal_notify_user;
					}
					
					if($cObj->conf["rights."]['allowedGroups']!=""){
						// creating options for exception groups
						$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_groups","uid in (".$cObj->conf["rights."]['allowedGroups'].")");
//						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			//				if(array_search($row['uid'],$cal_notify_group_ids)!==false){
			//					$cal_notify_user .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" selected>'.$row['title'].' (G)</option>';
			//				}else{
			//					$cal_notify_user .= '<option value="g_'.$row['uid'].'_'.$row['title'].'">'.$row['title'].' (G)</option>';
			//				}
//							if($rightsObj->isAllowedToEditEventCreator()){
//								if(array_search($row['uid'],$cal_group_ids)!==false){
//									$cal_user_selected .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_user_ids\',\''.$row['title'].' (G)\',\'g_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_user_ids_selected\');removeOption(\'tx_cal_controller_user_ids_selected\',this.index);">'.$row['title'].' (G)</option>';
//								}else{
//									$cal_user .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_user_ids_selected\',\''.$row['title'].' (G)\',\'g_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_user_ids\');removeOption(\'tx_cal_controller_user_ids\',this.index);">'.$row['title'].' (G)</option>';
//								}
//							}
//						}
						
					}else{
						
					}
					$languageArray['l_notify_on_change'] = $this->shared->lang('l_event_monitor');
					$languageArray['notify_ids'] = $cal_notify_user;
					$languageArray['l_notify_on_change_selected'] = $this->shared->lang('l_event_monitor_selected');
					$languageArray['notify_ids_selected'] = $cal_notify_user_selected;
				}
				
			}
//			if(($cal_notify_user!="" || $cal_notify_user_selected!="")){
//				$form 	.= $this->cObj->getSubpart($page, "###FORM_CREATOR###");
//				$validation .= "case 'user_ids_selected[]':	if(!atLeastOne(form.elements[i])){error = true;	break;}	selectAll(form.elements[i]);break;";
//				$languageArray['l_edit_event_atleastone'] = $this->shared->lang('l_edit_event_atleastone');
//				$languageArray['l_user'] = $this->shared->lang('l_event_user');
//				$languageArray['user_ids'] = $cal_user;
//				$languageArray['l_user_selected'] = $this->shared->lang('l_event_user_selected');
//				$languageArray['user_ids_selected'] = $cal_user_selected;
//			}
			
			if($rightsObj->isAllowedToEditEventOrganizer()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_ORGANIZER###");
				// creating options for organizer
				$cal_organizer = '<option value="">'.$this->shared->lang('l_select').'</option>';
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","pid in (".$pidList.")");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if($event->getOrganizer_id()==$row['uid']){
						$cal_organizer .= '<option value="'.$row['uid'].'" selected>'.$row['name'].'</option>';
					}else{
						$cal_organizer .= '<option value="'.$row['uid'].'">'.$row['name'].'</option>';
					}
				}
				$languageArray['l_organizer'] = $this->shared->lang('l_event_organizer');
				$languageArray['organizer'] = $event->getOrganizer();
				$languageArray['l_cal_organizer'] = $this->shared->lang('l_event_cal_organizer');
				$languageArray['organizer_ids'] = $cal_organizer;
			}
			if($rightsObj->isAllowedToEditEventLocation()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_LOCATION###");
				// creating options for location
				$cal_location = '<option value="">'.$this->shared->lang('l_select').'</option>';
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","pid in (".$pidList.")");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if($event->getLocationId()==$row['uid']){
						$cal_location .= '<option value="'.$row['uid'].'" selected>'.$row['name'].'</option>';
					}else{
						$cal_location .= '<option value="'.$row['uid'].'">'.$row['name'].'</option>';
					}
				}
				$languageArray['l_location'] = $this->shared->lang('l_event_location');
				$languageArray['location_ids'] = $cal_location;
				$languageArray['l_cal_location'] = $this->shared->lang('l_event_cal_location');
				$languageArray['location'] = $event->getLocation();
			}
			if($rightsObj->isAllowedToEditEventDescription()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_DESCRIPTION###");
				$languageArray['l_description'] = $this->shared->lang('l_event_description');
				$languageArray['description'] = $event->getDescription();	
			}
			
			if($rightsObj->isAllowedToEditEventRecurring()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_RECURRING###");

				// creating dropdown options for frequency
				$freqency_values = array("none", "year", "month", "week", "day");
				$frequency = "";
				foreach ($freqency_values as $freq) {
					$frequency .= '<option value="'.$freq.'"'.($freq==$event->getFreq()?" selected":"").'>'.$this->shared->lang('l_'.$freq).'</option>';
				}

				$until = $event->getUntil();
				if($until>0){
					$until = date("Ymd", $until);
				}else{
					$until = "";
				}
				$languageArray['l_frequency'] = $this->shared->lang('l_event_frequency');
				$languageArray['frequency_ids'	] = $frequency;
				$languageArray['l_by_day'] = $this->shared->lang('l_event_edit_byday');

				$by_day = array("MON","TUE","WED","THU","FRI","SAT","SUN");

				foreach ($by_day as $day) {
					if (strpos($event->getByDay(),$day))
						$languageArray['BY_DAY_CHECKED_'.$day] = "checked ";
					else
						$languageArray['BY_DAY_CHECKED_'.$day] = "";
					$languageArray['L_DAYSOFWEEKSHORT_LANG_'.$day] = $this->shared->lang('l_daysofweekshort_lang_'.strtolower($day));
				}
				$languageArray['l_by_monthday'] = $this->shared->lang('l_event_edit_bymonthday');
				$languageArray['by_monthday'] = $event->getByMonthDay();
				$languageArray['l_by_month'] = $this->shared->lang('l_event_edit_bymonth');
				$languageArray['by_month'] = $event->getByMonth();
				$languageArray['l_until'] = $this->shared->lang('l_event_edit_until');
				$languageArray['until'] = $until;
				$languageArray['until_selector'] = tx_rlmpdateselectlib::getInputButton ('event_until',$dateSelectorConf);
				$languageArray['l_count'] = $this->shared->lang('l_event_count');
				$languageArray['count'] = $event->getCount();
				$languageArray['l_interval'] = $this->shared->lang('l_event_interval');
				$languageArray['interval'] = $event->getInterval();
				
			}
			
			if($rightsObj->isAllowedToEditEventNotify() && ($cal_notify_user!="" || $cal_notify_user_selected!="")){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_NOTIFY###");
			}
			
			if($rightsObj->isAllowedToEditEventException()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_EXCEPTION###");
			}
			
			$languageArray['validation'] = $validation;
			$languageArray['uid'] = $event->getUid();
			$languageArray['l_submit'] = $this->shared->lang('l_submit');
			$languageArray['l_cancel'] = $this->shared->lang('l_cancel');
			$parameter = array("no_cache"=>1,"tx_cal_controller[view]"=>"confirm_event","tx_cal_controller[lastview]"=>$cObj->conf['lastview'],"tx_cal_controller[getdate]"=>$cObj->conf['getdate']);
			$languageArray['action_url'] = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter);
			
		// no event has been passed on -> create event form
		}else{
				$languageArray ['l_edit_event'] = $this->shared->lang('l_create_event');
			if($rightsObj->isAllowedToCreateEventHidden()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
				$languageArray['l_hidden'] = $this->shared->lang('l_hidden');
				$languageArray['hidden'] = "";
			}
			if($rightsObj->isAllowedToCreateEventCategory()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_CATEGORY###");
				// JH: check vs conf list and conf default value(s) of Categories
				$uidList = $cObj->conf["rights."]["create."]["event."]["allowedToCreateCategory."]["uidList"];
				$uidDefault = $cObj->conf["rights."]["create."]["event."]["allowedToCreateCategory."]["uidDefault"];
				if(!empty($uidList)) {
					if(empty($uidDefault)) {
						//$category = '<option value="">'.$this->shared->lang('l_select').'</option>';
						return $this->shared->lang('l_error_noCategory');
					}
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_category","uid IN (".$uidList.") AND deleted = 0 AND hidden = 0");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						$category .= '<option value="'.$row['uid'].'"';;
						if($row['uid'] == $uidDefault) {
							$category .= ' selected="selected"';
						}
						$category .= '>'.$row['title'].'</option>';
					}
				}
				// if no default values found
				else {
					// creating options for category by standard fe plugin entry point
					//$category = '<option value="">'.$this->shared->lang('l_select').'</option>';
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_category","pid IN (".$pidList.") AND deleted = 0 AND hidden = 0");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						$category .= '<option value="'.$row['uid'];
						if($row['uid'] == $uidDefault) {
							$category .= ' selected="selected"';
						}
						$category .= '">'.$row['title'].'</option>';
					}
				}				
				$languageArray['l_category'] = $this->shared->lang('l_event_category');
				$languageArray['category_ids'] = $category;
			}
			if($rightsObj->isAllowedToCreateEventDateTime()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_DATETIME###");
				$event_start_day = $getdate;
				$event_end_day = $getdate;
				$languageArray['l_event_start_day'] = $this->shared->lang('l_event_edit_startdate');
				$languageArray['event_start_day'] = $event_start_day;
				$languageArray['l_event_start_time'] = $this->shared->lang('l_event_edit_starttime');
//				$languageArray['event_start_time'] = $this->cObj->conf['gettime'];
				$start_hour = $this->getHourFromTime($this->cObj->conf['gettime']);
				$start_minute = $this->getMinutesFromTime($this->cObj->conf['gettime']);
				if($start_hour == "23") {
					$end_hour = "00";
				}
				else {
				}
				$end_hour = $start_hour + 1;
				$end_minute = $start_minute;
				$start_hours = '';
				$end_hours = '';
				for ($i=0;$i<24;$i++) {
					$value = str_pad($i, 2, "0", STR_PAD_LEFT);
					$start_hours .= '<option value="'.$value.'"'.($start_hour==$value?' selected="selected"':'').'>'.$value.'</option>';
					$end_hours .= '<option value="'.$value.'"'.($end_hour==$value?' selected="selected"':'').'>'.$value.'</option>';
				}
				$start_minutes = '';
				$end_minutes = '';
				for ($i=0;$i<60;$i++) {
					$value = str_pad($i, 2, "0", STR_PAD_LEFT);
					$start_minutes .= '<option value="'.$value.'"'.($start_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
					$end_minutes .= '<option value="'.$value.'"'.($end_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
				}
				$languageArray['start_hours'] = $start_hours;
				$languageArray['end_hours'] = $end_hours;
				$languageArray['start_minutes'] = $start_minutes;
				$languageArray['end_minutes'] = $end_minutes;
				$languageArray['l_event_end_day'] = $this->shared->lang('l_event_edit_enddate');
				$languageArray['event_end_day'] = $event_end_day;
				$languageArray['l_event_end_time'] = $this->shared->lang('l_event_edit_endtime');
//				$languageArray['event_end_time'	] = "";
				$languageArray['start_day_selector'] = tx_rlmpdateselectlib::getInputButton ('event_start_day',$dateSelectorConf);
				$languageArray['end_day_selector'] = tx_rlmpdateselectlib::getInputButton ('event_end_day',$dateSelectorConf);
			}
			if($rightsObj->isAllowedToCreateEventTitle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
				$languageArray['l_title'] = $this->shared->lang('l_event_title');
				if(!empty($cObj->conf["rights."]["create."]["event."]["fields."]["allowedToCreateTitle."]["default"])) {
					$languageArray['title'] = $cObj->conf["rights."]["create."]["event."]["fields."]["allowedToCreateTitle."]["default"];
				}
				else {
					$languageArray['title'] = "";
				}	
			}
		
//			if(($cObj->conf["rights."]['allowedUsers']!="" || $cObj->conf["rights."]['allowedGroups']!="")){

//				$form 	.= $this->cObj->getSubpart($page, "###FORM_CREATOR###");
				// selecting uids of available creator & -groups
//				$validation .= "case 'user_ids_selected[]':	if(!atLeastOne(form.elements[i])){error = true;	break;}	selectAll(form.elements[i]);break;";
//				$cal_user = "";
//				$uidList = $cObj->conf["rights."]["create."]["event."]["allowedToCreateCreator."]["uidList"];
//				$uidDefault = $cObj->conf["rights."]["create."]["event."]["allowedToCreateCreator."]["uidDefault"];
//				if(!empty($uidDefault)) {
//					$notUidDefaultSQL = ' AND uid NOT IN ('.$uidDefault.') ';
//				}
//				if(!empty($uidList)) {
//					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_users","uid IN (".$uidList.") ".$notUidDefaultSQL." AND deleted = 0 AND disable = 0");
//					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//						$cal_user .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_user_ids_selected\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_user_ids\');removeOption(\'tx_cal_controller_user_ids\',this.index);"';
//						$cal_user .= '>'.$row['username'].' (U)</option>';
//					}
//				}
//				else {
//					if(!empty($cObj->conf["rights."]['allowedUsers'])) {
//						$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_users","uid IN (".$cObj->conf["rights."]['allowedUsers'].") ".$notUidDefaultSQL." AND deleted = 0 AND disable = 0");
//						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//							$cal_user .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_user_ids_selected\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_user_ids\');removeOption(\'tx_cal_controller_user_ids\',this.index);">'.$row['username'].' (U)</option>';
//						}
//					}
//					$only_user = $cal_user;
//					if(!empty($cObj->conf["rights."]['allowedGroups'])) {
//						$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_groups","uid IN (".$cObj->conf["rights."]['allowedGroups'].") AND deleted = 0 AND hidden = 0");
//						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//							$cal_user .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_user_ids_selected\',\''.$row['title'].' (G)\',\'g_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_user_ids\');removeOption(\'tx_cal_controller_user_ids\',this.index);">'.$row['title'].' (G)</option>';
//						}
//					}
//				}
//				if(!empty($uidDefault)) {
//					$cal_user_selected = "";
//					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_users","uid IN (".$uidDefault.") AND deleted = 0");
//					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//						$cal_user_selected .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_user_ids\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_user_ids_selected\');removeOption(\'tx_cal_controller_user_ids_selected\',this.index);"';
//						$cal_user_selected .= '>'.$row['username'].' (U)</option>';
//					}
//				}
//				$languageArray['l_edit_event_atleastone'] = $this->shared->lang('l_edit_event_atleastone');
//				$languageArray['l_user'] = $this->shared->lang('l_event_user');
//				$languageArray['user_ids'] = $cal_user;
//				$languageArray['l_user_selected'] = $this->shared->lang('l_event_user_selected');
//				$languageArray['user_ids_selected'] = $cal_user_selected;
//			}
//			
			if($rightsObj->isAllowedToCreateEventOrganizer()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_ORGANIZER###");
				// creating options for organizer
				$cal_organizer = '<option value="">'.$this->shared->lang('l_select').'</option>';
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","pid in (".$pidList.")");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$cal_organizer .= '<option value="'.$row['uid'].'">'.$row['name'].'</option>';
				}
				$languageArray['l_organizer'] = $this->shared->lang('l_event_organizer');
				$languageArray['organizer'] = "";
				$languageArray['l_cal_organizer'] = $this->shared->lang('l_event_cal_organizer');
				$languageArray['organizer_ids'] = $cal_organizer;
			}
			if($rightsObj->isAllowedToCreateEventLocation()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_LOCATION###");
				// JH: check vs conf list and conf default value(s) of Loactions
				$uidList = $cObj->conf["rights."]["create."]["event."]["allowedToCreateLocation."]["uidList"];
				$uidDefault = $cObj->conf["rights."]["create."]["event."]["allowedToCreateLocation."]["uidDefault"];
				if(!empty($uidList)) {
					if(empty($uidDefault)) {
						$cal_location = '<option value="">'.$this->shared->lang('l_select').'</option>';
					}
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid IN (".$uidList.") AND deleted = 0 AND hidden = 0");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						$cal_location .= '<option value="'.$row['uid'].'"';
						if($row['uid'] == $uidDefault) {
							$cal_location .= ' selected="selected"';
						}
						$cal_location .= '>'.$row['name'].'</option>';
					}
				}
				// if no default values found
				else {
					// creating options for location by standard fe plugin entry point
					$cal_location = '<option value="">'.$this->shared->lang('l_select').'</option>';
					$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","pid IN (".$pidList.") AND deleted = 0 AND hidden = 0");
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
						$cal_location .= '<option value="'.$row['uid'];
						if($row['uid'] == $uidDefault) {
							$cal_location .= ' selected="selected"';
						}
						$cal_location .= '">'.$row['name'].'</option>';
					}
				}				
				$languageArray['l_location'] = $this->shared->lang('l_event_location');
				$languageArray['location_ids'] = $cal_location;
				$languageArray['l_cal_location'] = $this->shared->lang('l_event_cal_location');
				$languageArray['location'] = "";
			}
			if($rightsObj->isAllowedToCreateEventDescription()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_DESCRIPTION###");
				$languageArray['l_description'] = $this->shared->lang('l_event_description');
				$languageArray['description'] = "";	
			}
			
			if($rightsObj->isAllowedToCreateEventRecurring()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_RECURRING###");
				// creating dropdown options for frequency
				$freqency_values = array("none", "year", "month", "week", "day");
				$frequency = "";
				foreach ($freqency_values as $freq) {
					$frequency .= '<option value="'.$freq.'">'.$this->shared->lang('l_'.$freq).'</option>';
				}
				$languageArray['l_frequency'] = $this->shared->lang('l_event_frequency');
				$languageArray['frequency_ids'] = $frequency;
				$languageArray['l_by_day'] = $this->shared->lang('l_event_edit_byday');
				$by_day = array("MON","TUE","WED","THU","FRI","SAT","SUN");
				
				foreach ($by_day as $day) {
					$languageArray['BY_DAY_CHECKED_'.$day] = "";
					$languageArray['L_DAYSOFWEEKSHORT_LANG_'.$day] = $this->shared->lang('l_daysofweekshort_lang_'.strtolower($day));
				}
				$languageArray['l_by_monthday'] = $this->shared->lang('l_event_edit_bymonthday');
				$languageArray['by_monthday'] = "";
				$languageArray['l_by_month'] = $this->shared->lang('l_event_edit_bymonth');
				$languageArray['by_month'] = "";
				$languageArray['l_until'] = $this->shared->lang('l_event_edit_until');
				$languageArray['until'] = "";
				$languageArray['until_selector'] = tx_rlmpdateselectlib::getInputButton ('until',$dateSelectorConf);
				$languageArray['l_count'] = $this->shared->lang('l_event_count');
				$languageArray['count'] = "";
				$languageArray['l_interval'] = $this->shared->lang('l_event_interval');
				$languageArray['interval'] = "1";
				
			}
			
			if($rightsObj->isAllowedToCreateEventNotify() && ($cObj->conf["rights."]['allowedUsers']!="" || $cObj->conf["rights."]['allowedGroups']!="")){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_NOTIFY###");
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","fe_users","uid in (".$cObj->conf["rights."]['allowedUsers'].")");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$cal_notify_user .= '<option value="u_'.$row['uid'].'_'.$row['username'].'" onclick="addOption(\'tx_cal_controller_notify_ids_selected\',\''.$row['username'].' (U)\',\'u_'.$row['uid'].'_'.$row['username'].'\',\'tx_cal_controller_notify_ids\');removeOption(\'tx_cal_controller_notify_ids\',this.index);">'.$row['username'].' (U)</option>';
				}
				$languageArray['l_notify_on_change'] = $this->shared->lang('l_event_monitor');
				$languageArray['notify_ids'] = $cal_notify_user;
				$languageArray['l_notify_on_change_selected'] = $this->shared->lang('l_event_monitor_selected');
			}
			
			if($rightsObj->isAllowedToCreateEventException()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_EXCEPTION###");
				$exception = "";
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_exception_event","pid in (".$pidList.")");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$exception .= '<option value="s_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_exception_ids_selected\',\''.$row['title'].'\',\'s_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_exception_ids\');removeOption(\'tx_cal_controller_exception_ids\',this.index);">'.$row['title'].'</option>';
				}
				// adding exception group options
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_exception_event_group","pid in (".$pidList.")");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$exception .= '<option value="g_'.$row['uid'].'_'.$row['title'].'" onclick="addOption(\'tx_cal_controller_exception_ids_selected\',\''.$row['title'].'\',\'g_'.$row['uid'].'_'.$row['title'].'\',\'tx_cal_controller_exception_ids\');removeOption(\'tx_cal_controller_exception_ids\',this.index);">'.$row['title'].'</option>';
				}
				$validation .= "case 'exception_ids_selected[]': selectAll(form.elements[i]);break;";
					
				$languageArray['l_exception'] = $this->shared->lang('l_event_exception');
				$languageArray['exception_ids'] = $exception;
				$languageArray['l_exception_selected'] = $this->shared->lang('l_event_exception_selected');
				$languageArray['exception_ids_selected']= "";
			}
			
			$languageArray['validation'] = $validation;
			$languageArray['uid'] = "";
			$languageArray['l_submit'] = $this->shared->lang('l_submit');
			$languageArray['l_cancel'] = $this->shared->lang('l_cancel');
			$parameter = array("no_cache"=>1,"tx_cal_controller[view]"=>"confirm_event","tx_cal_controller[lastview]"=>$cObj->conf['lastview'],"tx_cal_controller[getdate]"=>$cObj->conf['getdate']);
			$languageArray['action_url'] = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter);
		}
		
		$form 	.= $this->cObj->getSubpart($page, "###FORM_END###");	
		
		// replacing all values at the template
		return $this->shared->replace_tags($languageArray,$form);
	}
	
	function getHourFromTime($time) {
		$time = str_replace(':', '', $time);
		if ($time) {
			$retVal = substr($time, 0, 2);
		}
		return $retVal;
	}
	function getMinutesFromTime($time) {
		$time = str_replace(':', '', $time);
		if ($time) {
			$retVal = substr($time, 2, 2);
		}
		return $retVal;
	}
}
	

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_event_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_event_view.php']);
}
?>