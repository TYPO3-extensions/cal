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
 * A service which renders a form to create / edit a phpicategory event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_delete_category_view extends tx_cal_fe_editing_base_view {
	
	var $category;
	
	function tx_cal_delete_category_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a delete form for a category.
	 *  @param      boolean     True if a location should be deleted
	 *  @param		object		The object to be deleted
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteCategory2(&$object){
		$page = $this->cObj->fileResource($this->conf['view.']['category.']['confirmCategoryTemplate']);
		if ($page=='') {
			return '<h3>category: no confirm category template file found:</h3>'.$this->conf['view.']['category.']['confirmCategoryTemplate'];
		}

		$languageArray = array();
		$form 	= $this->cObj->getSubpart($page, '###FORM_START###');
		$languageArray ['l_confirm_category'] = $this->controller->pi_getLL('l_delete_category');
		if($this->rightsObj->isAllowedToEditCategoryHidden() || $this->rightsObj->isAllowedToCreateCategoryHidden()){
			$form 	.= $this->cObj->getSubpart($page, '###FORM_HIDDEN###');
			if ($object['hidden'] == 1) {
				$hidden = 'true';
			} else {
				$hidden = 'false';
			}
			$languageArray['l_hidden'] = $this->controller->pi_getLL('l_hidden');
			$languageArray['hidden'] = $this->controller->pi_getLL('l_'.$hidden);
		}
		
		if($this->rightsObj->isAllowedToEditCategoryTitle() || $this->rightsObj->isAllowedToCreateCategoryTitle()){
			$form 	.= $this->cObj->getSubpart($page, '###FORM_TITLE###');
			$languageArray['l_title'] = $this->controller->pi_getLL('l_event_title');
			$languageArray['title'] = $object->title;
		}
		if($this->rightsObj->isAllowedToEditCategoryHeaderstyle() || $this->rightsObj->isAllowedToCreateCategoryHeaderstyle()){
			$form 	.= $this->cObj->getSubpart($page, '###FORM_HEADERSTYLE###');
			$languageArray['l_headerstyle'] = $this->controller->pi_getLL('l_category_headerstyle');
			$languageArray['headerstyle'] = $object->headerstyle;
		}
		
		if($this->rightsObj->isAllowedToEditCategoryBodystyle() || $this->rightsObj->isAllowedToCreateCategoryBodystyle()){
			$form 	.= $this->cObj->getSubpart($page, '###FORM_BODYSTYLE###');
			$languageArray['l_bodystyle'] = $this->controller->pi_getLL('l_category_bodystyle');
			$languageArray['bodystyle'] = $object->bodystyle;
		}
		
		$form 	.= $this->cObj->getSubpart($page, '###FORM_END###');
		$languageArray['uid'] = $object->uid;
		$languageArray['type'] = 'tx_cal_category';
		$languageArray['view'] = 'remove_category';
		$languageArray['l_submit'] = $this->controller->pi_getLL('l_delete');
		$languageArray['l_cancel'] = $this->controller->pi_getLL('l_cancel');
		$languageArray['action_url'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'remove_category'));
		
		$page = $this->controller->replace_tags($languageArray,$form);		
		return $page;
	}
	
	/**
	 *  Draws a delete form for a calendar.
	 *  @param      boolean     True if a location should be deleted
	 *  @param		object		The object to be deleted
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteCategory(&$category){
		
		$page = $this->cObj->fileResource($this->conf['view.']['category.']['deleteCategoryTemplate']);
		if ($page=='') {
			return '<h3>category: no delete category template file found:</h3>'.$this->conf['view.']['category.']['deleteCategoryTemplate'];
		}
		
		$this->category = $category;
		
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'remove_category';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_DELETE_CATEGORY###'] = $this->controller->pi_getLL('l_delete_category');
		$sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'remove_category'));
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
	
	function getFormStartMarker(& $template, & $rems, & $sims) {
		$rems['###FORM_START###'] = $this->cObj->getSubpart($template, '###FORM_START###');
	}
	
	function getHiddenMarker(& $template, & $rems, & $sims) {
		$sims['###HIDDEN###'] = $this->cObj->stdWrap($this->category->isHidden()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
	}
	
	function getTitleMarker(& $template, & $rems, & $sims) {
		$sims['###TITLE###'] = $this->cObj->stdWrap($this->category->getTitle(), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);
	}
	
	function getCalendarMarker(& $template, & $rems, & $sims) {
		$calendarUid = $this->category->getCalendarUid();
		if($calendarUid) {
			$calendar = $this->modelObj->findCalendar($calendarUid, 'tx_cal_calendar', $this->conf['pidList']);
			$calendarTitle = $calendar->getTitle();
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendarTitle, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);
		} else {
			$sims['###CALENDAR###'] = '';
		}
	}
	
	function getHeaderStyleMarker(& $template, & $rems, & $sims) {
		$sims['###HEADERSTYLE###'] = $this->cObj->stdWrap($this->category->getHeaderStyle(), $this->conf['view.'][$this->conf['view'].'.']['headerStyle_stdWrap.']);
	}
	
	function getBodyStyleMarker(& $template, & $rems, & $sims) {
		$sims['###BODYSTYLE###'] = $this->cObj->stdWrap($this->category->getBodyStyle(), $this->conf['view.'][$this->conf['view'].'.']['bodyStyle_stdWrap.']);
	}
	
	function getParentCategoryMarker(& $template, &$rems, & $sims) {
		$parentUid = $this->category->getParentUid();

		if($parentUid) {
			/* Get parent category title */
			$category = $this->modelObj->findCategory($parentUid,'tx_cal_category',$this->conf['pidList']);
			$parentCategory = $category->getTitle();
			$sims['###PARENT_CATEGORY###'] = $this->cObj->stdWrap($parentCategory, $this->conf['view.'][$this->conf['view'].'.']['parentCategory_stdWrap.']);
		} else {
			$sims['###PARENT_CATEGORY###'] = '';
		}
	}
	
	function getSharedUserAllowedMarker(& $template, & $rems, & $sims) {
		$sims['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap($this->category->isSharedUserAllowed()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), $this->conf['view.'][$this->conf['view'].'.']['sharedUserAllowed_stdWrap.']);
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_category_view.php']);
}
?>