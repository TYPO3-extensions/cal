<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Christian Technology Ministries International Inc.
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries 
 * International (http://CTMIinc.org). The WEC is developing TYPO3-based
 * (http://typo3.org) free software for churches around the world. Our desire
 * is to use the Internet to help offer new life through Jesus Christ. Please
 * see http://WebEmpoweredChurch.org/Jesus.
 *
 * You can redistribute this file and/or modify it under the terms of the 
 * GNU General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This file is distributed in the hope that it will be useful for ministry,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the file!
 ***************************************************************/

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_fe_editing_base_view.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal').'model/class.tx_cal_calendar_model.php');

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
		unset($this->controller->piVars['formCheck']);
		$page = $this->cObj->fileResource($this->conf['view.']['confirm_calendar.']['template']);
		if ($page=='') {
			return '<h3>calendar: no create calendar template file found:</h3>'.$this->conf['view.']['confirm_calendar.']['template'];
		}
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		
		if($lastViewParams['view']=='edit_calendar'){
			$this->isEditMode = true;
		}
		
		$fakeArray = Array();
		$this->object = new tx_cal_calendar_model($fakeArray, '');
		$this->object->updateWithPIVars($this->controller->piVars);		
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
		
		$this->getTemplateSubpartMarker($page, $sims, $rems, $this->conf['view']);
		$page = substituteMarkerArrayNotCached($page, array(), $rems, array ());
		$page = substituteMarkerArrayNotCached($page, $sims, array(), array ());
		
		$sims = array();
		$rems = array();
		$this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
		$page = substituteMarkerArrayNotCached($page, array(), $rems, array ());;
		$page = substituteMarkerArrayNotCached($page, $sims, array(), array ());
		return substituteMarkerArrayNotCached($page, $sims, array(), array ());
	}
	
	function getTitleMarker(& $template, & $sims, & $rems) {
		$sims['###TITLE###'] = '';
		if($this->isAllowed('title')) {
			$sims['###TITLE###'] = $this->applyStdWrap($this->object->getTitle(),'title_stdWrap');
			$sims['###TITLE_VALUE###'] = htmlspecialchars($this->object->getTitle());
		}
	}
	
	function getCalendarTypeMarker(& $template, & $sims, & $rems){
		$sims['###CALENDARTYPE###'] = '';
		$calendarTypeArray = array($this->controller->pi_getLL('l_calendar_type0'),$this->controller->pi_getLL('l_calendar_exturl'),$this->controller->pi_getLL('l_calendar_icsfile'));
		if($this->isAllowed('calendarType')){
			$sims['###CALENDARTYPE###'] = $this->applyStdWrap($calendarTypeArray[$this->object->getCalendarType()],'calendarType_stdWrap');
			$sims['###CALENDARTYPE_VALUE###'] = intval($this->controller->piVars['calendarType']);
		}
	}
	
	function getExtUrlMarker(& $template, & $sims, & $rems){
		$sims['###EXTURL###'] = '';
		if($this->object->getCalendarType()==1 && $this->isAllowed('calendarType')){
			$sims['###EXTURL###'] = $this->applyStdWrap($this->object->getExtUrl(), 'exturl_stdWrap');
			$sims['###EXTURL_VALUE###'] = strip_tags($this->controller->piVars['exturl']);
		}
	}
	
	function getRefreshMarker(& $template, & $sims, & $rems){
		$sims['###REFRESH###'] = '';
		if($this->object->getCalendarType()>0 && $this->isAllowed('calendarType')){
			$sims['###REFRESH###'] = $this->applyStdWrap($this->object->getRefresh(), 'refresh_stdWrap');
			$sims['###REFRESH_VALUE###'] = strip_tags($this->controller->piVars['refresh']);
		}
	}
	
	function getIcsFileMarker(& $template, & $sims, & $rems){
		$sims['###ICSFILE###'] = '';
		if($this->object->getCalendarType()==2 && $this->isAllowed('calendarType')){
			$sims['###ICSFILE###'] = $this->applyStdWrap(strip_tags($this->controller->piVars['icsfile']), 'icsfile_stdWrap');
			$sims['###ICSFILE_VALUE###'] = strip_tags($this->controller->piVars['icsfile']);
		}
	}
	
	function getActivateFreeAndBusyMarker(& $template, & $sims, & $rems){
		$sims['###ACTIVATE_FREEANDBUSY###'] = '';
		if($this->isAllowed('activateFreeAndBusy')){
			$activateFreeAndBusy = 'false';
			if ($this->object->isActivateFreeAndBusy()) {
				$activateFreeAndBusy = 'true';
			}
			$sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap($activateFreeAndBusy, 'activateFreeAndBusy_stdWrap');
			$sims['###ACTIVATE_FREEANDBUSY_VALUE###'] = $activateFreeAndBusy=='true'?1:0;
		}
	}
	
	function getFreeAndBusyUserMarker(& $template, & $sims, & $rems){
		$sims['###FREEANDBUSYUSER###'] = '';
		if($this->isAllowed('freeAndBusyUser') && is_array($this->controller->piVars['freeAndBusyUser'])) {
			$displaylist = array();
			$idlist = array();
			foreach ($this->controller->piVars['freeAndBusyUser'] as $value) {
				preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
				if($idname[1]=='u' || $idname[1]=='g'){
					$idlist[] = $idname[1].'_'.$idname[2];
					$displaylist[] = $idname[3];
				}
			}
			$sims['###FREEANDBUSYUSER###'] = $this->applyStdWrap(implode(',',$displaylist), 'freeAndBusyUser_stdWrap');
			$sims['###FREEANDBUSYUSER_VALUE###'] = htmlspecialchars(implode(',',$idlist));
		}
	}
	
	function getOwnerMarker(& $template, & $sims, & $rems){
		$sims['###OWNER###'] = '';
		if($this->isAllowed('owner') && is_array($this->controller->piVars['owner'])) {
			$ownerdisplaylist = Array();
			$ownerids = Array();
			foreach ($this->controller->piVars['owner'] as $value) {
				preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
				if($idname[1]=='u' || $idname[1]=='g'){
					$ownerids[] = $idname[1].'_'.$idname[2];
					$ownerdisplaylist[] = $idname[3];
				}
			}
			$sims['###OWNER###'] = $this->applyStdWrap(implode(',', $ownerdisplaylist), 'owner_stdWrap');
			$sims['###OWNER_VALUE###'] = htmlspecialchars(implode(',',$ownerids));
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_calendar_view.php']);
}
?>