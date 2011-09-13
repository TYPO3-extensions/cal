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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_event_view extends tx_cal_base_view {

	/**
	 *  Draws a confirm event form.
	 *  @param      object      The cObject of the mother-class
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawConfirmEvent() {
		
		$modelObj = $this->controller->modelObj;
		
		
		//TODO: change the format of date for data and parseDate functions
		$dateFormat = $this->conf["view."]["dateformat"];
		$timeFormat = $this->conf["view."]["timeformat"];
		$dateFormat = "%d.%m.%Y";

		$page = $this->cObj->fileResource($this->conf["view."]["event."]["confirmEventTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no confirm event template file found:</h3>".$this->conf["view."]["event."]["confirmEventTemplate"];
		}
		
		$form 	= $this->cObj->getSubpart($page, "###FORM_START###");
		$languageArray = array ();	
		if($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
			if ($this->controller->piVars["hidden"] == "on") {
				$hidden = "true";
			} else {
				$hidden = "false";
			}
			$languageArray['l_hidden'] = $this->controller->pi_getLL('l_hidden');
			$languageArray['hidden'] = $this->controller->pi_getLL('l_'.$hidden);
		}

		if($this->rightsObj->isAllowedToEditEventCalendar() || $this->rightsObj->isAllowedToCreateEventCalendar()){
			if ($this->controller->piVars["calendar_id"]) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_CALENDAR###");
				$languageArray['l_calendar'] = $this->controller->pi_getLL('l_calendar');
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_calendar","uid = ".$this->controller->piVars["calendar_id"]."");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$languageArray['calendar_id'] = $row['uid'];
					$languageArray['calendar_title'] = $row['title'];
				}
			}
		}
	
		if($this->rightsObj->isAllowedToEditEventCategory() || $this->rightsObj->isAllowedToCreateEventCategory()){
			if ($this->controller->piVars["category_ids_selected"]) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_CATEGORY###");
				$first = true;
				$idname = array();
				foreach ($this->controller->piVars["category_ids_selected"] as $value) {
					preg_match('/([0-9]+)_(.*)/', $value, $idname);
					if($first){
						$category_list .= $idname[1];
						$categorydisplaylist .= $idname[2];
						$first = false;
					}else{
						$category_list .= ",".$idname[1];
						$categorydisplaylist .= ",".$idname[2];
					}
				}
				
				$languageArray['category_display_ids'] = $categorydisplaylist;
				$languageArray['category_ids'] = $category_list;
				$languageArray['l_category'] = $this->controller->pi_getLL('l_event_category');
				
//				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_category","uid = ".$this->controller->piVars["category_id"]."");
//				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//					$languageArray['category_id'] = $row['uid'];
//					$languageArray['category_title'] = $row['title'];
//				}
			}
		}
		if($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_DATETIME###");
			$languageArray['event_start_day'] = $this->controller->piVars["event_start_day"];
			$languageArray['event_end_day'] = $this->controller->piVars["event_end_day"];
			$languageArray['event_start_time'] = $this->controller->piVars["event_start_hour"].':'.$this->controller->piVars["event_start_minutes"];
			$languageArray['event_end_time'] = $this->controller->piVars["event_end_hour"].':'.$this->controller->piVars["event_end_minutes"];

			$start_day = parseDate($this->controller->piVars["event_start_day"], $dateFormat);
			$end_day = parseDate($this->controller->piVars["event_end_day"], $dateFormat);
			$start_time = $this->controller->piVars["event_start_hour"].$this->controller->piVars["event_start_minutes"];
			$end_time = $this->controller->piVars["event_end_hour"].$this->controller->piVars["event_end_minutes"];

			$languageArray['event_start_day_value'] = $start_day;
			$languageArray['event_end_day_value'] = $end_day;
			$languageArray['event_start_time_value'] = $start_time;
			$languageArray['event_end_time_value'] = $end_time;

			$languageArray['l_event_start_day'] = $this->controller->pi_getLL('l_event_edit_startdate');
			$languageArray['l_event_start_time'] = $this->controller->pi_getLL('l_event_edit_starttime');
			$languageArray['l_event_end_day'] = $this->controller->pi_getLL('l_event_edit_enddate');
			$languageArray['l_event_end_time'] = $this->controller->pi_getLL('l_event_edit_endtime');
		}
		if($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
			$languageArray['l_title'] = $this->controller->pi_getLL('l_event_title');
			$languageArray['title'] = $this->controller->piVars["title"];
		}
		
//		if($this->rightsObj->isAllowedToEditEventException() || $this->rightsObj->isAllowedToEditEventNotify() ||
//			 $this->rightsObj->isAllowedToCreateEventException() || $this->rightsObj->isAllowedToCreateEventNotify()){
			
			
//			$userlist = "";
//			$grouplist = "";
//			$usergrouplist = "";
//			if(is_array($this->controller->piVars["user_ids_selected"]) && count($this->controller->piVars["user_ids_selected"])>0){
//				foreach ($this->controller->piVars["user_ids_selected"] as $value) {
//					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
//					if ($idname[1] == "u") {
//						$userlist .= ",".$idname[2];
//					} else {
//						$grouplist .= ",".$idname[2];
//					}
//					$usergrouplist .= $idname[3].",";
//				}
//				$usergrouplist = substr($usergrouplist, 0, strlen($usergrouplist) - 1);
//			}
//			
//			$languageArray['usergroup_ids'] = $usergrouplist; 
//			$languageArray['user_ids'] = $userlist; 
//			$languageArray['group_ids'] = $grouplist;
//		}
//		if($this->rightsObj->isAllowedToEditEventCreator() || $this->rightsObj->isAllowedToCreateEventCreator()){
//			$form 	.= $this->cObj->getSubpart($page, "###FORM_CREATOR###");
//			$languageArray['l_user'] = $this->controller->pi_getLL('l_event_user');
//			$languageArray['l_user_selected'] = $this->controller->pi_getLL('l_event_user_selected');
//		}
		
		if($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_ORGANIZER###");
			if ($this->controller->piVars["organizer_id"]) {
				$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer');
				$organizer = $this->modelObj->findOrganizer($this->controller->piVars["organizer_id"],$useOrganizerStructure);
				$languageArray['organizer_id']    = $organizer->getUid();
				$languageArray['organizer_title'] = $organizer->getName();
			}else{
				$languageArray['organizer_id']    = "";
				$languageArray['organizer_title'] = "";
			}
			$languageArray['l_organizer'] = $this->controller->pi_getLL('l_event_organizer');
			$languageArray['l_cal_organizer'] = $this->controller->pi_getLL('l_event_cal_organizer');
			$languageArray['organizer'] = $this->controller->piVars["organizer"];

		}
		if($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_LOCATION###");
			if ($this->controller->piVars["location_id"]) {
				$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');
				$location = $this->modelObj->findLocation($this->controller->piVars["location_id"],$useLocationStructure);
				$languageArray['location_id']    = $location->getUid();
				$languageArray['location_title'] = $location->getName();
			}else{
				$languageArray['location_id']    = "";
				$languageArray['location_title'] = "";
			}
			$languageArray['l_cal_location'] = $this->controller->pi_getLL('l_event_cal_location');
			$languageArray['l_location'] = $this->controller->pi_getLL('l_event_location');
			$languageArray['location'] = $this->controller->piVars["location"];
		}
		if($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_DESCRIPTION###");
			$languageArray['l_description'] = $this->controller->pi_getLL('l_event_description');
			$languageArray['description'] = $this->controller->piVars["description"];	
		}	
		if($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_FREQ###");
			$languageArray['l_frequency'] = $this->controller->pi_getLL('l_event_frequency');
			$languageArray['frequency_id'] = $this->controller->piVars["frequency_id"];
			$languageArray['l_by_day'] = $this->controller->pi_getLL('l_event_edit_byday');
			$languageArray['by_day'] = $this->controller->piVars["by_day"];
			$languageArray['l_by_monthday'] = $this->controller->pi_getLL('l_event_edit_bymonthday');
			$languageArray['by_monthday'] = $this->controller->piVars["by_monthday"];
			$languageArray['l_by_month'] = $this->controller->pi_getLL('l_event_edit_bymonth');
			$languageArray['by_month'] = $this->controller->piVars["by_month"];
			$languageArray['l_until'] = $this->controller->pi_getLL('l_event_edit_until');
			$languageArray['until'] = $this->controller->piVars["until"];
			$languageArray['l_count'] = $this->controller->pi_getLL('l_event_count');
			$languageArray['count'] = $this->controller->piVars["count"];
			$languageArray['l_interval'] = $this->controller->pi_getLL('l_event_interval');
			$languageArray['interval'] = $this->controller->piVars["interval"];
		}
		if($this->rightsObj->isAllowedToEditEventNotify() || $this->rightsObj->isAllowedToCreateEventNotify()){
			if (is_array($this->controller->piVars["notify_ids_selected"])) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_NOTIFY###");
				$notifydisplaylist = "";
				$notifyids = "";
				foreach ($this->controller->piVars["notify_ids_selected"] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					$notifyids .= ",".$idname[2];
					$notifydisplaylist .= ",".$idname[3];
				}
				$notifydisplaylist = substr($notifydisplaylist, 1, strlen($notifydisplaylist));
				$languageArray['notify_ids'] = $notifyids;
				$languageArray['notify_display_ids'] = $notifydisplaylist;
				$languageArray['l_notify_on_change'] = $this->controller->pi_getLL('l_notify_on_change');
			}
			
		}
		
		if($this->rightsObj->isAllowedToEditEventException() || $this->rightsObj->isAllowedToCreateEventException()){
			if (is_array($this->controller->piVars["exception_ids_selected"])) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_EXCEPTION###");
				$exceptiondisplaylist = "";
				$single_exception_list = "";
				$group_exception_list = "";
				foreach ($this->controller->piVars["exception_ids_selected"] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					if ($idname[1] == "s") {
						$single_exception_list .= ",".$idname[2];
					} else {
						$group_exception_list .= ",".$idname[2];
					}
					$exceptiondisplaylist .= $idname[3].",";
				}
				$exceptiondisplaylist = substr($exceptiondisplaylist, 0, strlen($exceptiondisplaylist) - 1);
				$languageArray['exception_ids'] = $exceptiondisplaylist;
				$languageArray['single_exception_ids'] = $single_exception_list;
				$languageArray['group_exception_ids'] = $group_exception_list;
				$languageArray['l_exception'] = $this->controller->pi_getLL('l_exception');
			}
		}
		
		$languageArray['uid'] = $this->controller->piVars["uid"];
		$languageArray['type'] = $this->controller->piVars["type"];
		$languageArray['view'] = "save_event";
		$languageArray['lastview'] = $this->conf['lastview'];
		$languageArray['option'] = $this->conf['option'];
		$languageArray['calendar'] = $this->conf['calendar'];
		$languageArray['l_confirm_event'] = $this->controller->pi_getLL('l_confirm_event');
		$languageArray['l_save'] = $this->controller->pi_getLL('l_save');
		$languageArray['l_cancel'] = $this->controller->pi_getLL('l_cancel');
		$languageArray['action_url'] = $this->controller->pi_linkTP_keepPIvars_url( array("view"=>"save_event"));
		$form 	.= $this->cObj->getSubpart($page, "###FORM_END###");
		return $this->controller->replace_tags($languageArray, $form);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_event_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_event_view.php']);
}
?>