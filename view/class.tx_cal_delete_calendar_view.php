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
		
		$page = $this->cObj->fileResource($this->conf['view.']['delete_calendar.']['template']);
		if ($page=='') {
			return '<h3>calendar: no confirm calendar template file found:</h3>'.$this->conf['view.']['delete_calendar.']['template'];
		}
		
		$this->object = $calendar;
		
		if(!$this->object->isUserAllowedToDelete()){
			return 'You are not allowed to delete this calendar!';
		}
		
		$rems = array();
		$sims = array();
		$wrapped = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_event';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_DELETE_CALENDAR###'] = $this->controller->pi_getLL('l_delete_calendar');
		$sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'remove_calendar'));
		$this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
		$page = substituteMarkerArrayNotCached($page, array(), $rems, $wrapped);
		$page = substituteMarkerArrayNotCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$wrapped = array();
		$this->getTemplateSingleMarker($page, $sims, $rems, $wrapped);
		$page = substituteMarkerArrayNotCached($page, array(), $rems,$wrapped);
		$page = substituteMarkerArrayNotCached($page, $sims, array(), array ());
		return substituteMarkerArrayNotCached($page, $sims, array(), array ());
	}
	
	function getCalendarTypeMarker(& $template, & $sims, & $rems){
		$calendarTypeArray = array($this->controller->pi_getLL('l_calendar_type0'),$this->controller->pi_getLL('l_calendar_exturl'),$this->controller->pi_getLL('l_calendar_icsfile'));
		$sims['###CALENDARTYPE###'] = $this->applyStdWrap($calendarTypeArray[$this->object->getCalendarType()], 'calendarType_stdWrap');
	}
	
	function getExtUrlMarker(& $template, & $sims, & $rems){
		$sims['###EXTURL###'] = '';
		if($this->object->getCalendarType()==1){
			$sims['###EXTURL###'] = $this->applyStdWrap($this->object->getExtUrl(), $this->conf['view.'][$this->conf['view'].'.']['exturl_stdWrap.']);
		}
	}
	
	function getRefreshMarker(& $template, & $sims, & $rems){
		$sims['###REFRESH_LABEL###'] = '';
		if($this->object->getCalendarType()>0){
			$sims['###REFRESH_LABEL###'] = $this->applyStdWrap($this->object->getRefresh(), 'refresh_stdWrap');
		}
	}
	
	function getIcsFileMarker(& $template, & $sims, & $rems){
		$sims['###ICSFILE###'] = '';
		if($this->object->getCalendarType()==2){
			$sims['###ICSFILE###'] = $this->applyStdWrap($this->object->getIcsFile(), 'icsfile_stdWrap');
		}
	}
	
	function getTitleMarker(& $template, & $sims, & $rems){
		$sims['###TITLE###'] = $this->applyStdWrap($this->object->getTitle(), 'title_stdWrap');
	}
	
	function getActivateFreeAndBusyMarker(& $template, & $sims, & $rems){
		$sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap($this->object->isActivateFreeAndBusy()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), 'activateFreeAndBusy_stdWrap');
	}
	
	function getFreeAndBusyUserMarker(& $template, & $sims, & $rems){
		$displaylist = array();
		$user = $this->object->getFreeAndBusyUser('fe_users');
		if(!empty($user)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_users.name', 'fe_users', 'uid IN ('.implode(',',$user).')'.$this->cObj->enableFields('fe_users'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist[] = $row['name'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			
		}
		$groups = $this->object->getFreeAndBusyUser('fe_groups');
		if(!empty($groups)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_groups.title', 'fe_groups', 'uid IN ('.implode(',',$groups).')'.$this->cObj->enableFields('fe_groups'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist[] = $row['title'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			
		}
		$sims['###FREEANDBUSYUSER###'] = $this->applyStdWrap(implode(',',$displaylist), 'freeAndBusyUser_stdWrap');
	}
	
	function getOwnerMarker(& $template, & $sims, & $rems){
		$displaylist = array();
		$user = $this->object->getOwner('fe_users');
		if(!empty($user)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_users.name', 'fe_users', 'uid IN ('.implode(',',$user).')'.$this->cObj->enableFields('fe_users'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist[] = $row['name'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}
		$group = $this->object->getOwner('fe_groups');
		if(!empty($group)){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_groups.title', 'fe_groups', 'uid IN ('.implode(',',$group).')'.$this->cObj->enableFields('fe_groups'));
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$displaylist[] = $row['title'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			
		}
		$sims['###OWNER###'] = $this->applyStdWrap(implode(',',$displaylist), 'owner_stdWrap');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_calendar_view.php']);
}
?>