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

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_calendar_view extends tx_cal_base_view {

	
	/**
	 *  Draws a create calendar form.
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateCalendar($pidList, $object=''){	

		$page = $this->cObj->fileResource($this->conf["view."]["calendar."]["createCalendarTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no create calendar template file found:</h3>".$this->conf["view."]["calendar."]["createCalendarTemplate"];
		}
		
		// creating options for organizer
		$cal_organizer = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		$select = "uid,username";
		$table = "fe_users";
		$where = "";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			
			if($object && $object['fe_user_id']==$row['uid']){
				$users .= '<option value="'.$row['uid'].'" selected="selected">'.$row['username'].'</option>';
			}else{
				$users .= '<option value="'.$row['uid'].'" >'.$row['username'].'</option>';
			}
		}
		$form 	= $this->cObj->getSubpart($page, "###FORM_START###");
		
		$languageArray = array();
		
		if(!$object){
			$languageArray['uid'] = "";
			if($this->rightsObj->isAllowedToCreateCalendarHidden()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
				$languageArray['l_hidden'] = $this->controller->pi_getLL('l_hidden');
				$languageArray['hidden'] = "";
			}
			if($this->rightsObj->isAllowedToCreateCalendarTitle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
				$languageArray['l_title'] = $this->controller->pi_getLL('l_event_title');
				$languageArray['title'] = "";
			}
			if($this->rightsObj->isAllowedToCreateCalendarFeUser()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_FEUSER###");
				$languageArray['l_fe_user'] = $this->controller->pi_getLL('l_fe_user');
				$languageArray['fe_user_ids'] = $users;
			}
			
		}else{
			$languageArray['uid'] = $object['uid'];
			if($this->rightsObj->isAllowedToEditCalendarHidden()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
				$languageArray['l_hidden'] = $this->controller->pi_getLL('l_hidden');
				if($object['hidden']==1){
					$languageArray['hidden'] = "checked";
				}else{
					$languageArray['hidden'] = "";
				}
			}
			if($this->rightsObj->isAllowedToEditCalendarTitle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
				$languageArray['l_title'] = $this->controller->pi_getLL('l_event_title');
				$languageArray['title'] = $object['title'];
			}
			if($this->rightsObj->isAllowedToEditCalendarFeUser()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_FEUSER###");
				$languageArray['l_fe_user'] = $this->controller->pi_getLL('l_fe_user');
				$languageArray['fe_user_ids'] = $users;
			}
		}
		$form 	.= $this->cObj->getSubpart($page, "###FORM_END###");
		
		$languageArray ['l_create_calendar'] = $this->controller->pi_getLL('l_create_calendar');
		$languageArray['type'] = "tx_cal_calendar";
		$languageArray['l_submit'] = $this->controller->pi_getLL('l_submit');
		$languageArray['l_cancel'] = $this->controller->pi_getLL('l_cancel');
		$languageArray['action_url'] = $this->controller->pi_linkTP_keepPIvars_url(array("view"=>"confirm_calendar"));
			
		return $this->controller->replace_tags($languageArray,$form);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_calendar_view.php']);
}
?>