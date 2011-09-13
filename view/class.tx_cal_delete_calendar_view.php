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
class tx_cal_delete_calendar_view extends tx_cal_fe_editing_base_view {
	
	var $calendar;
	
	function tx_cal_delete_calendar_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a delete form for a calendar.
	 *  @param      boolean     True if a location should be deleted
	 *  @param		object		The object to be deleted
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteCalendar(&$calendar){
		
		$page = $this->cObj->fileResource($this->conf['view.']['calendar.']['deleteCalendarTemplate']);
		if ($page=='') {
			return '<h3>calendar: no confirm calendar template file found:</h3>'.$this->conf['view.']['calendar.']['deleteCalendarTemplate'];
		}
		
		$this->calendar = $calendar;
		
		if(!$this->calendar->isUserAllowedToDelete()){
			return 'You are not allowed to delete this calendar!';
		}
		
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_event';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_DELETE_CALENDAR###'] = $this->controller->pi_getLL('l_delete_calendar');
		$sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'remove_calendar'));
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
	
	function getTemplateSubpartMarker(& $template, & $rems, & $sims) {
		
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

		$allMarkers = array_unique($match[1]);

		foreach ($allMarkers as $marker) {
			switch ($marker) {
				case 'FORM_START' :
					$this->getFormStartMarker($template, $rems, $sims);
					break;
				case 'FORM_END' :
					$this->getFormEndMarker($template, $rems, $sims);
					break;
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
				    if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $rems, $sims);
				    } 
					break;
			}
		}
	}
	
	function getTemplateSingleMarker(& $template, & $rems, & $sims) {
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_calendar_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $rems, $sims);
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
					}
					break;
			}
		}
	}
	
	function getFormStartMarker(& $template, & $rems, & $sims){
		
		$rems['###FORM_START###'] = $this->cObj->getSubpart($template, '###FORM_START###');
	}
	
	function getHiddenMarker(& $template, & $rems, & $sims){
		$sims['###HIDDEN###'] = $this->cObj->stdWrap($this->calendar->isHidden()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
	}
	
	function getCalendarTypeMarker(& $template, & $rems, & $sims){
		$calendarTypeArray = array($this->controller->pi_getLL('l_calendar_type0'),$this->controller->pi_getLL('l_calendar_exturl'),$this->controller->pi_getLL('l_calendar_icsfile'));
		$sims['###CALENDARTYPE###'] = $this->cObj->stdWrap($calendarTypeArray[$this->calendar->getCalendarType()], $this->conf['view.'][$this->conf['view'].'.']['calendarType_stdWrap.']);;
	}
	
	function getExtUrlMarker(& $template, & $rems, & $sims){
		$sims['###EXTURL###'] = '';
		if($this->calendar->getCalendarType()==1){
			$sims['###EXTURL###'] = $this->cObj->stdWrap($this->calendar->getExtUrl(), $this->conf['view.'][$this->conf['view'].'.']['exturl_stdWrap.']);;
		}
	}
	
	function getRefreshMarker(& $template, & $rems, & $sims){
		$sims['###REFRESH###'] = '';
		if($this->calendar->getCalendarType()>0){
			$sims['###REFRESH###'] = $this->cObj->stdWrap($this->calendar->getRefresh(), $this->conf['view.'][$this->conf['view'].'.']['refresh_stdWrap.']);;
		}
	}
	
	function getIcsFileMarker(& $template, & $rems, & $sims){
		$sims['###ICSFILE###'] = '';
		if($this->calendar->getCalendarType()==2){
			$sims['###ICSFILE###'] = $this->cObj->stdWrap($this->calendar->getIcsFile(), $this->conf['view.'][$this->conf['view'].'.']['icsfile_stdWrap.']);;
		}
	}
	
	function getTitleMarker(& $template, & $rems, & $sims){
		$sims['###TITLE###'] = $this->cObj->stdWrap($this->calendar->getTitle(), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);;
	}
	
	function getActivateFreeAndBusyMarker(& $template, & $rems, & $sims){
		$sims['###ACTIVATE_FREEANDBUSY###'] = $this->cObj->stdWrap($this->calendar->isActivateFreeAndBusy()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), $this->conf['view.'][$this->conf['view'].'.']['activateFreeAndBusy_stdWrap.']);;
	}
	
	function getFreeAndBusyUserMarker(& $template, & $rems, & $sims){
		$displaylist = array();
		$user = $this->calendar->getFreeAndBusyUser('fe_users');
		if(!empty($user)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_users.name', 'fe_users', 'uid IN ('.implode(',',$user).')'.$this->cObj->enableFields('fe_users'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist = $row['name'];
			}
		}
		$groups = $this->calendar->getFreeAndBusyUser('fe_groups');
		if(!empty($groups)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_groups.title', 'fe_groups', 'uid IN ('.implode(',',$groups).')'.$this->cObj->enableFields('fe_groups'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist = $row['title'];
			}
		}
		$sims['###FREEANDBUSYUSER###'] = $this->cObj->stdWrap(implode(',',$displaylist), $this->conf['view.'][$this->conf['view'].'.']['freeAndBusyUser_stdWrap.']);
	}
	
	function getOwnerMarker(& $template, & $rems, & $sims){
		$displaylist = array();
		$user = $this->calendar->getOwner('fe_users');
		if(!empty($user)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_users.name', 'fe_users', 'uid IN ('.implode(',',$user).')'.$this->cObj->enableFields('fe_users'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist = $row['name'];
			}
		}
		$group = $this->calendar->getOwner('fe_groups');
		if(!empty($group)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_groups.title', 'fe_groups', 'uid IN ('.implode(',',$group).')'.$this->cObj->enableFields('fe_groups'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist = $row['title'];
			}
		}
		$sims['###OWNER###'] = $this->cObj->stdWrap(implode(',',$displaylist), $this->conf['view.'][$this->conf['view'].'.']['owner_stdWrap.']);
	}
	
	function getFormEndMarker(& $template, & $rems, & $sims){	
		$temp = $this->cObj->getSubpart($template, '###FORM_END###');
		$temp_sims = array();
		$temp_sims['###BACK_LINK###'] = $this->controller->pi_linkTP_keepPIvars_url( $this->controller->shortenLastViewAndGetTargetViewParameters());
		$temp_sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$temp_sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$rems['###FORM_END###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_calendar_view.php']);
}
?>