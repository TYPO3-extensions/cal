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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_fe_editing_base_view.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_calendar_view extends tx_cal_fe_editing_base_view {

	function tx_cal_create_calendar_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a create calendar form.
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateCalendar($pidList, $calendar=''){	
		
		$this->objectString = 'calendar';
		$page = $this->cObj->fileResource($this->conf['view.']['calendar.']['createCalendarTemplate']);
		if ($page=='') {
			return '<h3>calendar: no create calendar template file found:</h3>'.$this->conf['view.']['calendar.']['createCalendarTemplate'];
		}
		
		if(is_object($object) && !$object->isUserAllowedToEdit()){
			return $this->controller->pi_getLL('l_not_allowed_edit').$this->objectString;
		}else if(!is_object($object) && !$this->rightsObj->isAllowedTo('create',$this->objectString,'')){
			return $this->controller->pi_getLL('l_not_allowed_create').$this->objectString;
		}

		// If an event has been passed on the form is a edit form
		if(is_object($calendar) && $calendar->isUserAllowedToEdit()){
			$this->isEditMode = true;
			$this->object = $calendar;
		}

		$this->getTemplateSubpartMarker($page, $rems, $sims);

		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
                
		$sims = array();
		$rems = array();
		
		$this->getTemplateSingleMarker($page, $rems, $sims);
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'confirm_calendar'));	
        $page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());

		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	
	function getOwnerMarker(& $template, & $rems, & $sims){
		$sims['###OWNER###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarOwner()){
			$calOwner = '';
			if($this->conf['rights.']['edit.']['calendar.']['fields.']['allowedToEditOwner.']['allowedUsers']!=''){
				// creating options for owner - users
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_users'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if(array_search($row['uid'],$this->object->getOwner('fe_users'))!==false){
						$calOwner .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[owner][]" />'.$row['username'].'<br />';
					}else{
						$calOwner .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[owner][]"/>'.$row['username'].'<br />';
					}
				}
			}
			if($this->conf['rights.']['edit.']['calendar.']['fields.']['allowedToEditOwner.']['allowedGroups']!=''){
				
				// creating options for owner - groups
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_groups','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_groups'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if(array_search($row['uid'],$this->object->getOwner('fe_groups'))!==false){
						$calOwner .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" checked="checked" name="tx_cal_controller[owner][]" />'.$row['title'].'<br />';
					}else{
						$calOwner .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'"  name="tx_cal_controller[owner][]"/>'.$row['title'].'<br />';
					}
				}
			}
			$sims['###OWNER###'] = $this->cObj->stdWrap($calOwner, $this->conf['view.'][$this->conf['view'].'.']['owner_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarOwner() && ($this->conf['rights.']['allowedUsers']!='' || $this->conf['rights.']['allowedGroups']!='')){
			$calOwner = '';
			if($this->conf['rights.']['create.']['calendar.']['fields.']['allowedToCreateOwner.']['allowedUsers']!=''){
				// creating options for owner - users
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_users'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$calOwner .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[owner][]"/>'.$row['username'].'<br />';
				}
			}
			if($this->conf['rights.']['create.']['calendar.']['fields.']['allowedToCreateOwner.']['allowedGroups']!=''){
				// creating options for owner - groups
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_groups','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_groups'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$calOwner .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'"  name="tx_cal_controller[owner][]"/>'.$row['title'].'<br />';
				}
			}
			$sims['###OWNER###'] = $this->cObj->stdWrap($calOwner, $this->conf['view.'][$this->conf['view'].'.']['owner_stdWrap.']);
		}
	}
	
	function getActivateFreeAndBusyMarker(& $template, & $rems, & $sims){
		$sims['###ACTIVATE_FREEANDBUSY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarActivateFreeAndBusy()){
			$hidden = '';
			if($this->conf['rights.']['edit.']['calendar.']['fields.']['allowedToEditActivateFreeAndBusy.']['default']){
				$hidden = 'checked';
			}
			$sims['###ACTIVATE_FREEANDBUSY###'] = $this->cObj->stdWrap($hidden, $this->conf['view.'][$this->conf['view'].'.']['activateFreeAndBusy_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarActivateFreeAndBusy()){
			$hidden = '';
			if($this->conf['rights.']['create.']['calendar.']['fields.']['allowedToCreateActivateFreeAndBusy.']['default']){
				$hidden = 'checked';
			}
			$sims['###ACTIVATE_FREEANDBUSY###'] = $this->cObj->stdWrap($hidden, $this->conf['view.'][$this->conf['view'].'.']['activateFreeAndBusy_stdWrap.']);
		}
	}
	
	function getFreeAndBusyUserMarker(& $template, & $rems, & $sims){
		$sims['###FREEANDBUSYUSER###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarFreeAndBusyUser()){
			$calOwner = '';
			if($this->conf['rights.']['edit.']['calendar.']['fields.']['allowedToEditFreeAndBusyUser.']['allowedUsers']!=''){
				// creating options for free- and busy - users
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_users'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if(array_search($row['uid'],$this->object->getFreeAndBusyUser('fe_users'))!==false){
						$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[freeAndBusyUser][]" />'.$row['username'].'<br />';
					}else{
						$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[freeAndBusyUser][]"/>'.$row['username'].'<br />';
					}
				}
			}
			if($this->conf['rights.']['edit.']['calendar.']['fields.']['allowedToEditFreeAndBusyUser.']['allowedGroups']!=''){
				
				// creating options for free- and busy - groups
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_groups','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_groups'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if(array_search($row['uid'],$this->object->getFreeAndBusyUser('fe_groups'))!==false){
						$freeAndBusyUser .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" checked="checked" name="tx_cal_controller[freeAndBusyUser][]" />'.$row['title'].'<br />';
					}else{
						$freeAndBusyUser .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'"  name="tx_cal_controller[freeAndBusyUser][]"/>'.$row['title'].'<br />';
					}
				}
			}
			$sims['###FREEANDBUSYUSER###'] = $this->cObj->stdWrap($freeAndBusyUser, $this->conf['view.'][$this->conf['view'].'.']['freeAndBusyUser_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarFreeAndBusyUser()){
			$calOwner = '';
			if($this->conf['rights.']['create.']['calendar.']['fields.']['allowedToCreateFreeAndBusyUser.']['allowedUsers']!=''){
				// creating options for free- and busy - users
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_users'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[freeAndBusyUser][]"/>'.$row['username'].'<br />';
				}
			}
			if($this->conf['rights.']['create.']['calendar.']['fields.']['allowedToCreateFreeAndBusyUser.']['allowedGroups']!=''){
				// creating options for free- and busy - groups
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_groups','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('fe_groups'));
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$freeAndBusyUser .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'"  name="tx_cal_controller[freeAndBusyUser][]"/>'.$row['title'].'<br />';
				}
			}
			$sims['###FREEANDBUSYUSER###'] = $this->cObj->stdWrap($freeAndBusyUser, $this->conf['view.'][$this->conf['view'].'.']['freeAndBusyUser_stdWrap.']);
		}
	}
	
	function getCalendarTypeMarker(& $template, & $rems, & $sims){
		$sims['###CALENDARTYPE###'] = '';
		$calendarTypeArray = array($this->controller->pi_getLL('l_calendar_type0'),$this->controller->pi_getLL('l_calendar_exturl'),$this->controller->pi_getLL('l_calendar_icsfile'));
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarType()){
			$calendarType = '';
			foreach($calendarTypeArray as $index => $title){
				if($this->object->getCalendarType()==$index){
					$calendarType .= '<option value="'.$index.'" selected="selected">'.$title.'</option>';
				}else{
					$calendarType .= '<option value="'.$index.'">'.$title.'</option>';
				}
			}
			
			$sims['###CALENDARTYPE###'] = $this->cObj->stdWrap($calendarType, $this->conf['view.'][$this->conf['view'].'.']['calendarType_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarType()){
			$calendarType = '';
			foreach($calendarTypeArray as $index => $title){
				$calendarType .= '<option value="'.$index.'">'.$title.'</option>';
			}
			
			$sims['###CALENDARTYPE###'] = $this->cObj->stdWrap($calendarType, $this->conf['view.'][$this->conf['view'].'.']['calendarType_stdWrap.']);
		}
	}
	
	function getExtUrlMarker(& $template, & $rems, & $sims){
		$sims['###EXTURL###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarType()){
			$this->object->getExtUrlMarker($template, $rems, $sims);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarType()){
			$sims['###EXTURL###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['exturl_stdWrap.']);
		}
	}
	
	function getIcsFileMarker(& $template, & $rems, & $sims){
		$sims['###ICSFILE###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarType()){
			$this->object->getIcsFileMarker($template, $rems, $sims, 'edit_calendar');
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarType()){
			$sims['###ICSFILE###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['icsfile_stdWrap.']);
		}
	}
	
	function getRefreshMarker(& $template, & $rems, & $sims){
		$sims['###REFRESH###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarType()){
			$this->object->getRefreshMarker($template, $rems, $sims, 'edit_calendar');
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarType()){
			$sims['###REFRESH###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['refresh_stdWrap.']);
		}
	}
		
	function getFormStartMarker(& $template, & $rems, & $sims){
		$temp = $this->cObj->getSubpart($template, '###FORM_START###');
		$temp_sims = array();
		$temp_sims['###L_CREATE_CALENDAR###'] = $this->controller->pi_getLL('l_create_calendar');
		$temp_sims['###UID###'] = '';
		if($this->isEditMode){
			$temp_sims['###L_CREATE_CALENDAR###'] = $this->controller->pi_getLL('l_edit_calendar');
			$temp_sims['###UID###'] = $this->object->getUid();
		}
		$temp_sims['###TYPE###'] = 'tx_cal_calendar';

		$rems['###FORM_START###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
		
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_calendar_view.php']);
}
?>