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
require_once (t3lib_extMgm :: extPath('cal').'model/class.tx_cal_category_model.php');

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
//debug($this->controller->piVars);
		$this->objectString = 'category';	
		
		if(!$this->rightsObj->isAllowedToCreateGeneralCategory()){
			$this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['calendar_id.']['required'] = 1;
		}
		if(!$this->rightsObj->isAllowedToEditGeneralCategory()){
			$this->conf['rights.']['edit.'][$this->objectString.'.']['fields.']['calendar_id.']['required'] = 1;
		}
		
		if(is_object($object)){
			$this->conf['view'] = 'edit_'.$this->objectString;
		}else{
			$this->conf['view'] = 'create_'.$this->objectString;
			unset($this->controller->piVars['uid']);
		}
		
		$requiredFieldSims = Array();
		$allRequiredFieldsAreFilled = $this->checkRequiredFields($requiredFieldsSims);

		if($allRequiredFieldsAreFilled){
			
			$this->conf['lastview'] = $this->controller->extendLastView();

			$this->conf['view'] = 'confirm_'.$this->objectString;
			return $this->controller->confirmCategory();
		}
		
		//Needed for translation options:
		$this->serviceName = 'cal_'.$this->objectString.'_model';
		$this->table = 'tx_cal_'.$this->objectString;
		
		$page = $this->cObj->fileResource($this->conf['view.']['create_category.']['template']);
		if ($page=='') {
			return '<h3>category: no create category template file found:</h3>'.$this->conf['view.']['create_category.']['template'];
		}
		if(is_object($object) && !$object->isUserAllowedToEdit()){
			return $this->controller->pi_getLL('l_not_allowed_edit').$this->objectString;
		}else if(!is_object($object) && !$this->rightsObj->isAllowedTo('create',$this->objectString,'')){
			return $this->controller->pi_getLL('l_not_allowed_create').$this->objectString;
		}
		
		$sims = array();
		$rems = array();
		$wrapped = array();
		
		$sims['###TYPE###'] = 'tx_cal_'.$this->objectString;
		
		// If an event has been passed on the form is a edit form
		if(is_object($category) && $category->isUserAllowedToEdit()){
			$this->isEditMode = true;
			$this->object = $category;
			$sims['###UID###'] = $this->object->getUid();
			$sims['###TYPE###'] = $this->object->getType();
			$sims['###L_EDIT_CATEGORY###'] = $this->controller->pi_getLL('l_edit_category');
		}else{
			$a = Array();
			$this->object = new tx_cal_category_model($a, '');
			$allValues = array_merge($this->getDefaultValues(),$this->controller->piVars);
			$this->object->updateWithPIVars($allValues);
		}
		
		$sims['###THIS_VIEW###'] = $this->conf['view'];
		
		$this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped, $this->conf['view']);

		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
                
		$sims = array();
		$rems = array();
		
		$this->getTemplateSingleMarker($page, $sims, $rems, $this->conf['view']);
		$linkParams = array();
		$linkParams['formCheck'] = '1';

		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url($linkParams);
		$sims['###CHANGE_CALENDAR_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url();

		$this->getTemplateSubpartMarker($page, $sims, $rems, $this->conf['view']);
        $page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
        $page = $this->cObj->substituteMarkerArrayCached($page, $sims, array (), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $requiredFieldsSims, array(), array ());
	}
	
	function getHeaderstyleMarker(& $template, & $sims, & $rems, $view){
		$sims['###HEADERSTYLE###'] = '';
		if($this->isAllowed('headerstyle')){
			$selectedStyle = $this->object->getHeaderStyle();
			$allowedStyles = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['category.']['fields.']['headerstyle.']['available'],1);
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
			
				$sims['###HEADERSTYLE###'] = $this->applyStdWrap($headerStyle, 'headerstyle_stdWrap');
			}
		}
	}
	
	function getBodystyleMarker(& $template, & $sims, & $rems, $view){
		$sims['###BODYSTYLE###'] = '';
		if($this->isAllowed('bodystyle')){
			$selectedStyle = $this->object->getBodyStyle();
			$allowedStyles = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['category.']['fields.']['bodystyle.']['available'],1);
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
			
				$sims['###BODYSTYLE###'] = $this->applyStdWrap($bodyStyle, 'bodystyle_stdWrap');
			}
		}
	}
	
	function getParentCategoryMarker(& $template, & $sims, & $rems, $view){
		$sims['###PARENT_CATEGORY###'] = '';
		$isAllowed = $this->isAllowed('parent_category');
		
		if($isAllowed && ($this->object->getCalendarUid() || $this->rightsObj->isAllowedToCreateGeneralCategory() || $this->rightsObj->isAllowedToEditGeneralCategory())){
			
			$tempCalendarConf = $this->conf['calendar'];
			$tempCategoryConf = $this->conf['category'];
			$this->conf['calendar'] = $this->object->getCalendarUid();

			if($this->controller->piVars['calendar_id']==='0'){
				$this->conf['calendar'] = $this->conf['calendar_id'];
			}else if($this->conf['calendar_id']){
				$this->conf['calendar'] = $this->conf['calendar_id'];
			}
			$ids = array();
			$this->conf['category'] = $this->object->getParentUID();
			if($this->conf['category']=='0'){
				$this->conf['category'] = '-1';
			}
			$this->conf['view.']['edit_category.']['tree.']['calendar'] = $this->conf['calendar'];
			$this->conf['view.']['edit_category.']['tree.']['category'] = $this->conf['category'];
			
			$categoryArray = $this->modelObj->findAllCategories('','tx_cal_category',$this->conf['pidList']);

			$sims['###PARENT_CATEGORY###'] = $this->applyStdWrap($this->getCategorySelectionTree($this->conf['view.']['edit_category.']['tree.'], $categoryArray, true), 'parent_category_stdWrap');
			
			$this->conf['calendar'] = $tempCalendarConf;
			if($this->conf['category'] == 'a'){
				$this->conf['category'] = $tempCategoryConf;
			}
		}
	}
	
	function getSharedUserAllowedMarker(& $template, & $sims, & $rems, $view){
		$sims['###SHARED_USER_ALLOWED###'] = '';
		if($this->isAllowed('shared_user_allowed')){
			$value = '';
			if($this->conf['rights.']['edit.']['category.']['fields.']['shared_user_allowed.']['default']){
				$value = 'checked';
			}
			$sims['###SHARED_USER_ALLOWED###'] = $this->applyStdWrap($value, 'shared_user_allowed_stdWrap');
		}
	}
	
	
	function getFormStartMarker(& $template, & $sims, & $rems, & $wrapped){
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