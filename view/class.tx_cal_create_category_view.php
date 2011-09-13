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
 * A service which renders a form to create / edit a category.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_category_view extends tx_cal_fe_editing_base_view {
	
	function tx_cal_create_category_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a create category form.
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateCategory($pidList, $category=''){	
		
		$this->objectString = 'category';
		$page = $this->cObj->fileResource($this->conf['view.']['category.']['createCategoryTemplate']);
		if ($page=='') {
			return '<h3>category: no create category template file found:</h3>'.$this->conf['view.']['category.']['createCategoryTemplate'];
		}
		if(is_object($object) && !$object->isUserAllowedToEdit()){
			return $this->controller->pi_getLL('l_not_allowed_edit').$this->objectString;
		}else if(!is_object($object) && !$this->rightsObj->isAllowedTo('create',$this->objectString,'')){
			return $this->controller->pi_getLL('l_not_allowed_create').$this->objectString;
		}
		
		$sims['###TYPE###'] = 'tx_cal_category';
		
		// If an event has been passed on the form is a edit form
		if(is_object($category) && $category->isUserAllowedToEdit()){
			$this->isEditMode = true;
			$this->object = $category;
			$sims['###UID###'] = $this->object->getUid();
			$sims['###TYPE###'] = $this->object->getType();
			$sims['###L_EDIT_CATEGORY###'] = $this->controller->pi_getLL('l_edit_category');
		}
		
		$sims['###THIS_VIEW###'] = $this->conf['view'];
		
		$this->getTemplateSubpartMarker($page, $rems, $sims);

		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
                
		$sims = array();
		$rems = array();
		
		$this->getTemplateSingleMarker($page, $rems, $sims);
		$sims['###CALENDAR_ID###'] = intval($this->controller->piVars['calendar']);
		if($this->isEditMode && !intval($this->controller->piVars['calendar'])){
			$sims['###CALENDAR_ID###'] = $this->object->getCalendarUid();
		}
		unset($this->controller->piVars['calendar']);
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'confirm_category'));
			
        $page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());

		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function getTemplateSingleMarker(& $template, & $rems, & $sims) {
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
            switch ($marker) {
                default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_category_'.strtolower(substr($marker,0,strlen($marker)-6)));
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
					break;
			}
		}
	}
	
	function getCalendarMarker(& $template, & $rems, & $sims){
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$modelObj = new $tx_cal_modelcontroller ($this->controller);
		$calendarService = $modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
		
		$sims['###CALENDAR###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCategoryCalendar()){
			$id = $this->object->getCalendarUid();

			if($this->controller->piVars['switch_calendar']==='0'){
				$id = $this->conf['switch_calendar'];
			}else if($this->conf['switch_calendar']){
				$id = $this->conf['switch_calendar'];
			}

			$calendarIds = $calendarService->getIdsFromTable('',$this->conf['pidList'],true,true);
			if (empty($calendarIds)) {
				return '<h3>You have to create a calendar before you can create events</h3>';
			}
			$calendar .= '<option value="0">'.$this->controller->pi_getLL('l_global').'</option>';
			foreach($calendarIds as $calendarRow){
				if($calendarRow['uid']==$id){
					$calendar .= '<option value="'.$calendarRow['uid'].'" selected="selected">'.$calendarRow['title'].'</option>';
				}else{
					$calendar .= '<option value="'.$calendarRow['uid'].'">'.$calendarRow['title'].'</option>';
				}
			}
			
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendar, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);
			
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCategoryCalendar()){
			// JH: check vs conf list and conf default value(s) of Categories
			$calendarIds = $calendarService->getIdsFromTable('',$this->conf['pidList'], true,true);
			if (empty($calendarIds)) {
				return '<h3>You have to create a calendar before you can create events</h3>';
			}
			$id = '';

			if($this->controller->piVars['switch_calendar']==='0'){
				$id = $this->conf['switch_calendar'];
			}else if($this->conf['switch_calendar']){
				$id = $this->conf['switch_calendar'];
			}

			$calendar .= '<option value="0">'.$this->controller->pi_getLL('l_global').'</option>';

			foreach($calendarIds as $calendarRow){
				if($id==$calendarRow['uid']){
					$calendar .= '<option value="'.$calendarRow['uid'].'" selected="selected">'.$calendarRow['title'].'</option>';
				}else{
					$calendar .= '<option value="'.$calendarRow['uid'].'">'.$calendarRow['title'].'</option>';
				}
			}
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendar, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);		
		}
	}
	
	function getHeaderStyleMarker(& $template, & $rems, & $sims){
		$sims['###HEADERSTYLE###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCategoryHeaderstyle()){
			$selectedStyle = $this->object->getHeaderStyle();
			$allowedStyles = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['category.']['allowedHeaderStyles'],1);
			$headerStyle = '';
			
			/* If there are allowed styles, draw the selector */
			if(count($allowedStyles) > 0) {
				foreach($allowedStyles as $style){
					if($style==$selectedStyle){
						$headerStyle .= '<option value="'.$style.'" selected="selected" class="'.$style.'">'.$style.'</option>';
					}else{
						$headerStyle .= '<option value="'.$style.'" class="'.$style.'">'.$style.'</option>';
					}
				}
			
				$sims['###HEADERSTYLE###'] = $this->cObj->stdWrap($headerStyle, $this->conf['view.'][$this->conf['view'].'.']['headerStyle_stdWrap.']);
			}
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCategoryHeaderstyle()){
			$allowedStyles = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['category.']['allowedHeaderStyles'],1);
			$headerStyle = '';
			
			/* If there are allowed styles, draw the selector */
			if(count($allowedStyles) > 0) {
				foreach($allowedStyles as $style){
					$headerStyle .= '<option value="'.$style.'" class="'.$style.'">'.$style.'</option>';
				}
			
				$sims['###HEADERSTYLE###'] = $this->cObj->stdWrap($headerStyle, $this->conf['view.'][$this->conf['view'].'.']['headerStyle_stdWrap.']);		
			}
		}
	}
	
	function getBodyStyleMarker(& $template, & $rems, & $sims){
		$sims['###BODYSTYLE###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCategoryBodystyle()){
			$selectedStyle = $this->object->getBodyStyle();
			$allowedStyles = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['category.']['allowedBodyStyles'],1);
			$bodyStyle = '';
			
			/* If there are allowed styles, draw the selector */
			if(count($allowedStyles) > 0 ) {
				foreach($allowedStyles as $style){
					if($style==$selectedStyle){
						$bodyStyle .= '<option value="'.$style.'" selected="selected" class="'.$style.'">'.$style.'</option>';
					}else{
						$bodyStyle .= '<option value="'.$style.'" class="'.$style.'">'.$style.'</option>';
					}
				}
			
				$sims['###BODYSTYLE###'] = $this->cObj->stdWrap($bodyStyle, $this->conf['view.'][$this->conf['view'].'.']['bodyStyle_stdWrap.']);
			}
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCategoryBodystyle()){
			$allowedStyles = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['category.']['allowedBodyStyles'],1);
			$bodyStyle = '';
			
			/* If there are allowed styles, draw the selector */
			if(count($allowedStyles) > 0) {
				foreach($allowedStyles as $style){
					$bodyStyle .= '<option value="'.$style.'" class="'.$style.'">'.$style.'</option>';
				}
			
				$sims['###BODYSTYLE###'] = $this->cObj->stdWrap($bodyStyle, $this->conf['view.'][$this->conf['view'].'.']['bodyStyle_stdWrap.']);		
			}
		}
	}
	
	function getParentCategoryMarker(& $template, & $rems, & $sims){
		$sims['###PARENT_CATEGORY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCategoryParent()){
			
			$tempCalendarConf = $this->conf['calendar'];
			$tempCategoryConf = $this->conf['category'];
			$this->conf['calendar'] = $this->object->getCalendarUid();

			if($this->controller->piVars['switch_calendar']==='0'){
				$this->conf['calendar'] = $this->conf['switch_calendar'];
			}else if($this->conf['switch_calendar']){
				$this->conf['calendar'] = $this->conf['switch_calendar'];
			}

			$ids = array();
			$this->conf['category'] = $this->object->getParentUID();

			$this->conf['view.']['edit_category.']['tree.']['calendar'] = $this->conf['calendar'];
			$this->conf['view.']['edit_category.']['tree.']['category'] = $this->conf['category'];
			
			$categoryArray = $this->modelObj->findAllCategories('','tx_cal_category',$this->conf['pidList']);

			$sims['###PARENT_CATEGORY###'] = $this->cObj->stdWrap($this->getCategorySelectionTree($this->conf['view.']['edit_category.']['tree.'], array($categoryArray), true), $this->conf['view.'][$this->conf['view'].'.']['parentCategory_stdWrap.']);
			
			$this->conf['calendar'] = $tempCalendarConf;
			if($this->conf['category'] == 'a'){
				$this->conf['category'] = $tempCategoryConf;
			}
			
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCategoryParent()){
			
			$tempCalendarConf = $this->conf['calendar'];
			$tempCategoryConf = $this->conf['category'];
			$this->conf['calendar'] = $this->conf['rights.']['create.']['category.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
		
			if($this->rightsObj->isAllowedToCreateCategoryCalendar()){
				$this->conf['calendar'] = intval($this->controller->piVars['switch_calendar']);
			}

			$this->conf['category'] = 'a';

			$this->conf['view.']['create_category.']['tree.']['calendar'] = $this->conf['calendar'];
			$this->conf['view.']['create_category.']['tree.']['category'] = $this->conf['category'];

			$categoryArray = $this->modelObj->findAllCategories('','tx_cal_category',$this->conf['pidList']);

			$sims['###PARENT_CATEGORY###'] = $this->cObj->stdWrap($this->getCategorySelectionTree($this->conf['view.']['create_category.']['tree.'], array($categoryArray), true), $this->conf['view.'][$this->conf['view'].'.']['parentCategory_stdWrap.']);

			$this->conf['calendar'] = $tempCalendarConf;
			if(!$this->conf['category']=='a'){
				$this->conf['category'] = $tempCategoryConf;
			}
			
		}
	}
	
	function getSharedUserAllowedMarker(& $template, & $rems, & $sims){
		$sims['###SHARED_USER_ALLOWED###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditCategorySharedUser()){
			$value = '';
			if($this->conf['rights.']['edit.']['category.']['fields.']['allowedToEditSharedUser.']['default']){
				$value = 'checked';
			}
			$sims['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap($value, $this->conf['view.'][$this->conf['view'].'.']['sharedUserAllowed_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateCategorySharedUser()){
			$value = '';
			if($this->conf['rights.']['create.']['category.']['fields.']['allowedToCreateSharedUser.']['default']){
				$value = 'checked';
			}
			$sims['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap($value, $this->conf['view.'][$this->conf['view'].'.']['sharedUserAllowed_stdWrap.']);
		}
	}
	
	
	function getFormStartMarker(& $template, & $rems, & $sims){
		$temp = $this->cObj->getSubpart($template, '###FORM_START###');
		$temp_sims = array();
		$temp_sims['###L_CREATE_CATEGORY###'] = $this->controller->pi_getLL('l_create_category');
		$temp_sims['###UID###'] = '';
		if($this->isEditMode){
			$temp_sims['###L_CREATE_CATEGORY###'] = $this->controller->pi_getLL('l_edit_category');
			$temp_sims['###UID###'] = $this->object->getUid();
		}
		$temp_sims['###TYPE###'] = 'tx_cal_category';

		$rems['###FORM_START###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_category_view.php']);
}
?>