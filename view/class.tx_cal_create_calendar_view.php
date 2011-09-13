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
	function drawCreateCalendar($pidList, $object=''){	
		$this->objectString = 'calendar';
		if(is_object($object)){
			$this->conf['view'] = 'edit_'.$this->objectString;
		}else{
			$this->conf['view'] = 'create_'.$this->objectString;
			unset($this->controller->piVars['uid']);
		}
		
		$requiredFieldSims = Array();
		$allRequiredFieldsAreFilled = $this->checkRequiredFields($requiredFieldsSims);

		$sims = array();
		$rems = array();
		$wrapped = array();

		// If an event has been passed on the form is a edit form
		if(is_object($object) && $object->isUserAllowedToEdit()){
			$this->isEditMode = true;
			$this->object = $object;
		}else{
			$a = array();
			$this->object = new tx_cal_calendar_model($a, '');
			$allValues = array_merge($this->getDefaultValues(),$this->controller->piVars);
			$this->object->updateWithPIVars($allValues);
		}

		$constrainFieldSims = Array();
		$noComplains = $this->checkContrains($constrainFieldSims);

		if($allRequiredFieldsAreFilled && $noComplains){
			$this->conf['lastview'] = $this->controller->extendLastView();

			$this->conf['view'] = 'confirm_'.$this->objectString;
			return $this->controller->confirmCalendar();
		}
		
		//Needed for translation options:
		$this->serviceName = 'cal_'.$this->objectString.'_model';
		$this->table = 'tx_cal_'.$this->objectString;
		
		$page = $this->cObj->fileResource($this->conf['view.']['create_calendar.']['template']);
		if ($page=='') {
			return '<h3>calendar: no create calendar template file found:</h3>'.$this->conf['view.']['create_calendar.']['template'];
		}
		
		if(is_object($object) && !$object->isUserAllowedToEdit()){
			return $this->controller->pi_getLL('l_not_allowed_edit').$this->objectString;
		}else if(!is_object($object) && !$this->rightsObj->isAllowedTo('create',$this->objectString,'')){
			return $this->controller->pi_getLL('l_not_allowed_create').$this->objectString;
		}
		
		$this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped, $this->conf['view']);

		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, array(), $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, array(), array ());
                
		$sims = array();
		$rems = array();
		$wrapped = array();

		$this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
		$sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url(array('formCheck'=>'1')));	
        $page = tx_cal_functions::substituteMarkerArrayNotCached($page, array(), $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, array(), array ());
		return tx_cal_functions::substituteMarkerArrayNotCached($page, $requiredFieldsSims, array(), array ());
	}
	
	
	function getOwnerMarker(& $template, & $sims, & $rems, $view){
		$sims['###OWNER###'] = '';
		if($this->isAllowed('owner')){
			$cal_owner_user = '';
			$allowedUsers = t3lib_div::trimExplode(',',$this->conf['rights.']['allowedUsers'],1);
			$selectedUsers = $this->object->getOwner('fe_users');
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(!empty($allowedUsers) && array_search($row['uid'],$allowedUsers)){
					if(array_search($row['uid'],$selectedUsers)!==false){
						$cal_owner_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[owner][]" />'.$row['username'].'<br />';
					}else{
						$cal_owner_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[owner][]"/>'.$row['username'].'<br />';
					}
				}else if (empty($allowedUsers)){
					if(array_search($row['uid'],$selectedUsers)!==false){
						$cal_owner_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[owner][]" />'.$row['username'].'<br />';
					}else{
						$cal_owner_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[owner][]"/>'.$row['username'].'<br />';
					}
				}
				
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			$allowedGroups = t3lib_div::trimExplode(',',$this->conf['rights.']['allowedGroups'],1);
			$selectedGroups = $this->object->getOwner('fe_groups');
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_groups','pid in ('.$this->conf['pidList'].')');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(!empty($allowedGroups) && array_search($row['uid'],$allowedGroups)){
					if(array_search($row['uid'],$selectedGroups)!==false){
						$cal_owner_user .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" checked="checked" name="tx_cal_controller[owner][]" />'.$row['title'].'<br />';
					}else{
						$cal_owner_user .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'"  name="tx_cal_controller[owner][]"/>'.$row['title'].'<br />';
					}
				}else if (empty($allowedGroups)){
					if(array_search($row['uid'],$selectedGroups)!==false){
						$cal_owner_user .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" checked="checked" name="tx_cal_controller[owner][]" />'.$row['title'].'<br />';
					}else{
						$cal_owner_user .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'"  name="tx_cal_controller[owner][]"/>'.$row['title'].'<br />';
					}
				}
				
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			$sims['###OWNER###'] = $this->applyStdWrap($cal_owner_user, 'owner_stdWrap');
		}
	}
	
	function getActivateFreeAndBusyMarker(& $template, & $sims, & $rems, $view){
		$sims['###ACTIVATE_FREEANDBUSY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString, 'activateFreeAndBusy')){
			$activate = '';
			if($this->conf['rights.']['edit.'][$this->objectString.'.']['fields.']['activateFreeAndBusy.']['default'] || $this->object->isActivateFreeAndBusy()){
				$activate = ' checked="checked" ';
			}
			$sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap($activate, 'activateFreeAndBusy_stdWrap');
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString, 'activateFreeAndBusy')){
			$activate = '';
			if($this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['activateFreeAndBusy.']['default'] || $this->object->isActivateFreeAndBusy()){
				$activate = ' checked="checked" ';
			}
			$sims['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap($activate,'activateFreeAndBusy_stdWrap');
		}
	}
	
	function getFreeAndBusyUserMarker(& $template, & $sims, & $rems, $view){
		$sims['###FREEANDBUSYUSER###'] = '';
		if($this->isAllowed('freeAndBusyUser')){
			$freeAndBusyUser = '';
			$allowedUsers = t3lib_div::trimExplode(',',$this->conf['rights.']['allowedUsers'],1);
			$selectedUsers = $this->object->getFreeAndBusyUser('fe_users');
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(!empty($allowedUsers) && array_search($row['uid'],$allowedUsers)){
					if(array_search($row['uid'],$selectedUsers)!==false){
						$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[freeAndBusyUser][]" />'.$row['username'].'<br />';
					}else{
						$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[freeAndBusyUser][]"/>'.$row['username'].'<br />';
					}
				}else if (empty($allowedUsers)){
					if(array_search($row['uid'],$selectedUsers)!==false){
						$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[freeAndBusyUser][]" />'.$row['username'].'<br />';
					}else{
						$freeAndBusyUser .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[freeAndBusyUser][]"/>'.$row['username'].'<br />';
					}
				}
				
			}
			$sims['###FREEANDBUSYUSER###'] = $this->applyStdWrap($freeAndBusyUser,'freeAndBusyUser_stdWrap');
		}
	}
	
	function getCalendarTypeMarker(& $template, & $sims, & $rems, $view){
		$sims['###CALENDARTYPE###'] = '';
		$calendarTypeArray = array($this->controller->pi_getLL('l_calendar_type0'),$this->controller->pi_getLL('l_calendar_exturl'),$this->controller->pi_getLL('l_calendar_icsfile'));
		if($this->isAllowed('calendarType')){
			$calendarType = '';
			foreach($calendarTypeArray as $index => $title){
				if($this->object->getCalendarType()==$index){
					$calendarType .= '<option value="'.$index.'" selected="selected">'.$title.'</option>';
				}else{
					$calendarType .= '<option value="'.$index.'">'.$title.'</option>';
				}
			}
			
			$sims['###CALENDARTYPE###'] = $this->applyStdWrap($calendarType,'calendarType_stdWrap');
		}
	}
	
	function getExtUrlMarker(& $template, & $sims, & $rems, $view){
		$sims['###EXTURL###'] = '';
		if($this->isAllowed('exturl')){
			$this->object->getExtUrlMarker($template, $sims, $rems, $view);
		}
	}
	
	function getRefreshMarker(& $template, & $sims, & $rems, $view){
		$sims['###REFRESH###'] = '';
		if($this->isAllowed('refresh')){
			$this->object->getRefreshMarker($template, $sims, $rems, $view);
		}
	}
		
	function getFormStartMarker(& $template, & $sims, & $rems, & $wrapped){
		$temp = $this->cObj->getSubpart($template, '###FORM_START###');
		$temp_sims = array();
		$temp_sims['###L_CREATE_CALENDAR###'] = $this->controller->pi_getLL('l_create_calendar');
		$temp_sims['###UID###'] = '';
		if($this->isEditMode){
			$temp_sims['###L_CREATE_CALENDAR###'] = $this->controller->pi_getLL('l_edit_calendar');
			$temp_sims['###UID###'] = $this->object->getUid();
		}
		$temp_sims['###TYPE###'] = 'tx_cal_calendar';

		$rems['###FORM_START###'] = tx_cal_functions::substituteMarkerArrayNotCached($temp, $temp_sims, array(), array ());
		
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_calendar_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_calendar_view.php']);
}
?>
