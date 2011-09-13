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
class tx_cal_confirm_calendar_view extends tx_cal_fe_editing_base_view {
	
	function tx_cal_confirm_calendar_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a create calendar form.
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawConfirmCalendar(){		
		$this->objectString = 'calendar';
		$this->isConfirm = true;
		$page = $this->cObj->fileResource($this->conf['view.']['calendar.']['confirmCalendarTemplate']);
		if ($page=='') {
			return '<h3>calendar: no create calendar template file found:</h3>'.$this->conf['view.']['calendar.']['confirmCalendarTemplate'];
		}
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		
		if($lastViewParams['view']=='edit_calendar'){
			$this->editMode = true;
		}
		
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_calendar';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_CONFIRM_CALENDAR###'] = $this->controller->pi_getLL('l_confirm_calendar');
		$sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'save_calendar'));
		$this->getTemplateSubpartMarker($page, $rems, $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$this->getTemplateSingleMarker($page, $rems, $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function getCalendarTypeMarker(& $template, & $rems, & $sims){
		$sims['###CALENDARTYPE###'] = '';
		$calendarTypeArray = array($this->controller->pi_getLL('l_calendar_type0'),$this->controller->pi_getLL('l_calendar_exturl'),$this->controller->pi_getLL('l_calendar_icsfile'));
		if(($this->editMode && $this->rightsObj->isAllowedToEditCalendarType()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCalendarType())){
			$sims['###CALENDARTYPE###'] = $this->cObj->stdWrap($calendarTypeArray[intval($this->controller->piVars['calendarType'])], $this->conf['view.'][$this->conf['view'].'.']['calendarType_stdWrap.']);;
			$sims['###CALENDARTYPE_VALUE###'] = intval($this->controller->piVars['calendarType']);
		}
	}
	
	function getExtUrlMarker(& $template, & $rems, & $sims){
		$sims['###EXTURL###'] = '';
		if(intval($this->controller->piVars['calendarType'])==1 && (($this->editMode && $this->rightsObj->isAllowedToEditCalendarType()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCalendarType()))){
			$sims['###EXTURL###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['exturl']), $this->conf['view.'][$this->conf['view'].'.']['exturl_stdWrap.']);;
			$sims['###EXTURL_VALUE###'] = strip_tags($this->controller->piVars['exturl']);
		}
	}
	
	function getRefreshMarker(& $template, & $rems, & $sims){
		$sims['###REFRESH###'] = '';
		if(intval($this->controller->piVars['calendarType'])>0 && (($this->editMode && $this->rightsObj->isAllowedToEditCalendarType()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCalendarType()))){
			$sims['###REFRESH###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['refresh']), $this->conf['view.'][$this->conf['view'].'.']['refresh_stdWrap.']);;
			$sims['###REFRESH_VALUE###'] = strip_tags($this->controller->piVars['refresh']);
		}
	}
	
	function getIcsFileMarker(& $template, & $rems, & $sims){
		$sims['###ICSFILE###'] = '';
		if(intval($this->controller->piVars['calendarType'])==2 && (($this->editMode && $this->rightsObj->isAllowedToEditCalendarType()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCalendarType()))){
			$sims['###ICSFILE###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['icsfile']), $this->conf['view.'][$this->conf['view'].'.']['icsfile_stdWrap.']);;
			$sims['###ICSFILE_VALUE###'] = strip_tags($this->controller->piVars['icsfile']);
		}
	}
	
	function getActivateFreeAndBusyMarker(& $template, & $rems, & $sims){
		$sims['###ACTIVATE_FREEANDBUSY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditCalendarActivateFreeAndBusy()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCalendarActivateFreeAndBusy())){
			$activateFreeAndBusy = 'false';
			if ($this->controller->piVars['activateFreeAndBusy'] == 'on') {
				$activateFreeAndBusy = 'true';
			}
			$sims['###ACTIVATE_FREEANDBUSY###'] = $this->cObj->stdWrap($activateFreeAndBusy, $this->conf['view.'][$this->conf['view'].'.']['activateFreeAndBusy_stdWrap.']);;
			$sims['###ACTIVATE_FREEANDBUSY_VALUE###'] = $activateFreeAndBusy=='true'?1:0;
		}
	}
	
	function getFreeAndBusyUserMarker(& $template, & $rems, & $sims){
		$sims['###FREEANDBUSYUSER###'] = '';
		if(($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarFreeAndBusyUser()) || (!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarFreeAndBusyUser())){
			if (is_array($this->controller->piVars['freeAndBusyUser'])) {
				$displaylist = array();
				$single_list = array();
				$group_list = array();
				foreach ($this->controller->piVars['freeAndBusyUser'] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					if ($idname[1] == 'u') {
						$single_list[] = $idname[2];
					} else {
						$group_list[] = $idname[2];
					}
					$displaylist[] = $idname[3];
				}
				$sims['###FREEANDBUSYUSER###'] = $this->cObj->stdWrap(implode(',',$displaylist), $this->conf['view.'][$this->conf['view'].'.']['freeAndBusyUser_stdWrap.']);
				$temp_sims = array();
				$temp_sims['###FREEANDBUSYUSER_SINGLE_VALUE###'] = implode(',',$single_list);
				$temp_sims['###FREEANDBUSYUSER_GROUP_VALUE###'] = implode(',',$group_list);
				$sims['###FREEANDBUSYUSER###'] = $this->cObj->substituteMarkerArrayCached($sims['###FREEANDBUSYUSER###'], $temp_sims, array(), array ());
			}
		}
	}
	
	function getOwnerMarker(& $template, & $rems, & $sims){
		$sims['###OWNER###'] = '';
		if(($this->isEditMode && $this->rightsObj->isAllowedToEditCalendarOwner()) || (!$this->isEditMode && $this->rightsObj->isAllowedToCreateCalendarOwner())){
			if (is_array($this->controller->piVars['owner'])) {
				$displaylist = array();
				$single_list = array();
				$group_list = array();
				foreach ($this->controller->piVars['owner'] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					if ($idname[1] == 'u') {
						$single_list[] = $idname[2];
					} else {
						$group_list[] = $idname[2];
					}
					$displaylist[] = $idname[3];
				}
				$sims['###OWNER###'] = $this->cObj->stdWrap(implode(',',$displaylist), $this->conf['view.'][$this->conf['view'].'.']['owner_stdWrap.']);
				$temp_sims = array();
				$temp_sims['###OWNER_SINGLE_VALUE###'] = implode(',',$single_list);
				$temp_sims['###OWNER_GROUP_VALUE###'] = implode(',',$group_list);
				$sims['###OWNER###'] = $this->cObj->substituteMarkerArrayCached($sims['###OWNER###'], $temp_sims, array(), array ());
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_calendar_view.php']);
}
?>