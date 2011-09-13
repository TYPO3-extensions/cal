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

require_once (PATH_t3lib.'class.t3lib_svbase.php');
require_once (PATH_tslib."class.tslib_pibase.php");
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_shared.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_event_view extends t3lib_svbase {

	var $local_pibase;
	var $cObj;
	var $conf;
	var $shared;
	var $prefixId = 'tx_cal_controller';

	/**
	 *  Draws a confirm event form.
	 *  @param      object      The cObject of the mother-class
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawConfirmEvent($cObj, $rightsObj) {
		$this->cObj = $cObj;
		
		//TODO: change the format of date for data and parseDate functions
		$dateFormat = $cObj->conf["view."]["dateformat"];
		$timeFormat = $cObj->conf["view."]["timeformat"];
		$dateFormat = "%d.%m.%Y";

		$page = $this->cObj->fileResource($cObj->conf["view."]["event."]["confirmEventTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no confirm event template file found:</h3>".$cObj->conf["view."]["event."]["confirmEventTemplate"];
		}
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);
		
		$form 	= $this->cObj->getSubpart($page, "###FORM_START###");
		$languageArray = array ();	
		if($rightsObj->isAllowedToEditEventHidden() || $rightsObj->isAllowedToCreateEventHidden()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
			if ($GLOBALS['HTTP_POST_VARS']["hidden"] == "on") {
				$hidden = "true";
			} else {
				$hidden = "false";
			}
			$languageArray['l_hidden'] = $this->shared->lang('l_hidden');
			$languageArray['hidden'] = $this->shared->lang('l_'.$hidden);
		}

		if($rightsObj->isAllowedToEditEventCategory() || $rightsObj->isAllowedToCreateEventCategory()){
			if ($GLOBALS['HTTP_POST_VARS']["category_id"]) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_CATEGORY###");
				$languageArray['l_category'] = $this->shared->lang('l_event_category');
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tx_cal_category","uid = ".$GLOBALS['HTTP_POST_VARS']["category_id"]."");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$languageArray['category_id'] = $row['uid'];
					$languageArray['category_title'] = $row['title'];
				}
			}
		}
		if($rightsObj->isAllowedToEditEventDateTime() || $rightsObj->isAllowedToCreateEventDateTime()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_DATETIME###");
			$languageArray['event_start_day'] = $GLOBALS['HTTP_POST_VARS']["event_start_day"];
			$languageArray['event_end_day'] = $GLOBALS['HTTP_POST_VARS']["event_end_day"];
			$languageArray['event_start_time'] = $GLOBALS['HTTP_POST_VARS']["event_start_hour"].':'.$GLOBALS['HTTP_POST_VARS']["event_start_minutes"];
			$languageArray['event_end_time'] = $GLOBALS['HTTP_POST_VARS']["event_end_hour"].':'.$GLOBALS['HTTP_POST_VARS']["event_end_minutes"];

			$start_day = parseDate($GLOBALS['HTTP_POST_VARS']["event_start_day"], $dateFormat);
			$end_day = parseDate($GLOBALS['HTTP_POST_VARS']["event_end_day"], $dateFormat);
			$start_time = $GLOBALS['HTTP_POST_VARS']["event_start_hour"].$GLOBALS['HTTP_POST_VARS']["event_start_minutes"];
			$end_time = $GLOBALS['HTTP_POST_VARS']["event_end_hour"].$GLOBALS['HTTP_POST_VARS']["event_end_minutes"];

			$languageArray['event_start_day_value'] = $start_day;
			$languageArray['event_end_day_value'] = $end_day;
			$languageArray['event_start_time_value'] = $start_time;
			$languageArray['event_end_time_value'] = $end_time;

			$languageArray['l_event_start_day'] = $this->shared->lang('l_event_edit_startdate');
			$languageArray['l_event_start_time'] = $this->shared->lang('l_event_edit_starttime');
			$languageArray['l_event_end_day'] = $this->shared->lang('l_event_edit_enddate');
			$languageArray['l_event_end_time'] = $this->shared->lang('l_event_edit_endtime');
		}
		if($rightsObj->isAllowedToEditEventTitle() || $rightsObj->isAllowedToCreateEventTitle()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
			$languageArray['l_title'] = $this->shared->lang('l_event_title');
			$languageArray['title'] = $GLOBALS['HTTP_POST_VARS']["title"];
		}
		
//		if($rightsObj->isAllowedToEditEventException() || $rightsObj->isAllowedToEditEventNotify() ||
//			 $rightsObj->isAllowedToCreateEventException() || $rightsObj->isAllowedToCreateEventNotify()){
			
			
//			$userlist = "";
//			$grouplist = "";
//			$usergrouplist = "";
//			if(is_array($GLOBALS['HTTP_POST_VARS']["user_ids_selected"]) && count($GLOBALS['HTTP_POST_VARS']["user_ids_selected"])>0){
//				foreach ($GLOBALS['HTTP_POST_VARS']["user_ids_selected"] as $value) {
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
//		if($rightsObj->isAllowedToEditEventCreator() || $rightsObj->isAllowedToCreateEventCreator()){
//			$form 	.= $this->cObj->getSubpart($page, "###FORM_CREATOR###");
//			$languageArray['l_user'] = $this->shared->lang('l_event_user');
//			$languageArray['l_user_selected'] = $this->shared->lang('l_event_user_selected');
//		}
		
		if($rightsObj->isAllowedToEditEventOrganizer() || $rightsObj->isAllowedToCreateEventOrganizer()){
			if ($GLOBALS['HTTP_POST_VARS']["organizer_id"]) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_ORGANIZER###");
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid = ".$GLOBALS['HTTP_POST_VARS']["organizer_id"]."");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$languageArray['organizer_id']    = $row['uid'];
					$languageArray['organizer_title'] = $row['name'];
				}
				$languageArray['l_organizer'] = $this->shared->lang('l_event_organizer');
				$languageArray['l_cal_organizer'] = $this->shared->lang('l_event_cal_organizer');
				$languageArray['organizer'] = $GLOBALS['HTTP_POST_VARS']["organizer"];
			}

		}
		if($rightsObj->isAllowedToEditEventLocation() || $rightsObj->isAllowedToCreateEventLocation()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_LOCATION###");
			if ($GLOBALS['HTTP_POST_VARS']["location_id"]) {
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*","tt_address","uid = ".$GLOBALS['HTTP_POST_VARS']["location_id"]."");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$languageArray['location_id']    = $row['uid'];
					$languageArray['location_title'] = $row['name'];
				}
			}else{
				$languageArray['location_id']    = "";
				$languageArray['location_title'] = "";
			}
			$languageArray['l_cal_location'] = $this->shared->lang('l_event_cal_location');
			$languageArray['l_location'] = $this->shared->lang('l_event_location');
			$languageArray['location'] = $GLOBALS['HTTP_POST_VARS']["location"];
		}
		if($rightsObj->isAllowedToEditEventDescription() || $rightsObj->isAllowedToCreateEventDescription()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_DESCRIPTION###");
			$languageArray['l_description'] = $this->shared->lang('l_event_description');
			$languageArray['description'] = $GLOBALS['HTTP_POST_VARS']["description"];	
		}	
		if($rightsObj->isAllowedToEditEventRecurring() || $rightsObj->isAllowedToCreateEventRecurring()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_FREQ###");
			$languageArray['l_frequency'] = $this->shared->lang('l_event_frequency');
			$languageArray['frequency_id'] = $GLOBALS['HTTP_POST_VARS']["frequency_id"];
			$languageArray['l_by_day'] = $this->shared->lang('l_event_edit_byday');
			$languageArray['by_day'] = $GLOBALS['HTTP_POST_VARS']["by_day"];
			$languageArray['l_by_monthday'] = $this->shared->lang('l_event_edit_bymonthday');
			$languageArray['by_monthday'] = $GLOBALS['HTTP_POST_VARS']["by_monthday"];
			$languageArray['l_by_month'] = $this->shared->lang('l_event_edit_bymonth');
			$languageArray['by_month'] = $GLOBALS['HTTP_POST_VARS']["by_month"];
			$languageArray['l_until'] = $this->shared->lang('l_event_edit_until');
			$languageArray['until'] = $GLOBALS['HTTP_POST_VARS']["until"];
			$languageArray['l_count'] = $this->shared->lang('l_event_count');
			$languageArray['count'] = $GLOBALS['HTTP_POST_VARS']["count"];
			$languageArray['l_interval'] = $this->shared->lang('l_event_interval');
			$languageArray['interval'] = $GLOBALS['HTTP_POST_VARS']["interval"];
		}
		if($rightsObj->isAllowedToEditEventNotify() || $rightsObj->isAllowedToCreateEventNotify()){
			if (is_array($GLOBALS['HTTP_POST_VARS']["notify_ids_selected"])) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_NOTIFY###");
				$notifydisplaylist = "";
				$notifyids = "";
				foreach ($GLOBALS['HTTP_POST_VARS']["notify_ids_selected"] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					$notifyids .= ",".$idname[2];
					$notifydisplaylist .= ",".$idname[3];
				}
				$notifydisplaylist = substr($notifydisplaylist, 1, strlen($notifydisplaylist));
				$languageArray['notify_ids'] = $notifyids;
				$languageArray['notify_display_ids'] = $notifydisplaylist;
				$languageArray['l_notify_on_change'] = $this->shared->lang('l_notify_on_change');
			}
			
		}
		
		if($rightsObj->isAllowedToEditEventException() || $rightsObj->isAllowedToCreateEventException()){
			if (is_array($GLOBALS['HTTP_POST_VARS']["exception_ids_selected"])) {
				$form 	.= $this->cObj->getSubpart($page, "###FORM_EXCEPTION###");
				$exceptiondisplaylist = "";
				$single_exception_list = "";
				$group_exception_list = "";
				foreach ($GLOBALS['HTTP_POST_VARS']["exception_ids_selected"] as $value) {
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
				$languageArray['l_exception'] = $this->shared->lang('l_exception');
			}
		}
		
		$languageArray['uid'] = $GLOBALS['HTTP_POST_VARS']["uid"];
		$languageArray['type'] = $GLOBALS['HTTP_POST_VARS']["type"];
		$languageArray['l_confirm_event'] = $this->shared->lang('l_confirm_event');
		$languageArray['l_save'] = $this->shared->lang('l_save');
		$languageArray['l_cancel'] = $this->shared->lang('l_cancel');
		$parameter = array ("no_cache" => 1, "tx_cal_controller[view]" => "save_event", "tx_cal_controller[lastview]" => $cObj->conf['lastview'], "tx_cal_controller[getdate]" => $cObj->conf['getdate']);
		$languageArray['action_url'] = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, $parameter);
		$form 	.= $this->cObj->getSubpart($page, "###FORM_END###");
		return $this->shared->replace_tags($languageArray, $form);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_event_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_event_view.php']);
}
?>