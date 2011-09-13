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
 * A service which renders a form to confirm the category edit/create.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_category_view extends tx_cal_fe_editing_base_view {

	function tx_cal_confirm_category_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a create category form.
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawConfirmCategory(){	
		
		$this->objectString = 'category';
		$this->isConfirm = true;
		$page = $this->cObj->fileResource($this->conf['view.']['category.']['confirmCategoryTemplate']);
		if ($page=='') {
			return '<h3>category: no create category template file found:</h3>'.$this->conf['view.']['category.']['confirmCategoryTemplate'];
		}
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		
		if($lastViewParams['view']=='edit_category'){
			$this->editMode = true;
		}
		
		$rems = array();
		$sims = array();
		
		$sims['###L_CONFIRM_CATEGORY###'] = $this->controller->pi_getLL('l_confirm_category');
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_category';
		$sims['###L_SUBMIT###'] = $this->controller->pi_getLL('l_submit');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		

		$this->getTemplateSubpartMarker($page, $rems, $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$this->getTemplateSingleMarker($page, $rems, $sims);
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_category'));
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	
	function getCalendarMarker(& $template, & $rems, & $sims) {
		if(($this->editMode && $this->rightsObj->isAllowedToEditCategoryCalendar()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCategoryCalendar())) {
			$calendarUid = intval($this->controller->piVars['switch_calendar']);
			if($this->controller->piVars['switch_calendar']==='0'){
				$calendarTitle = $this->controller->pi_getLL('l_global');
				$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendarTitle, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);
				$sims['###CALENDAR_VALUE###'] = $calendarUid;
			}else if($calendarUid) {
				$calendar = $this->modelObj->findCalendar($calendarUid, 'tx_cal_calendar', $this->conf['pidList']);
				$calendarTitle = $calendar->getTitle();
				$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendarTitle, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);
				$sims['###CALENDAR_VALUE###'] = $calendarUid;
			} else {
				$sims['###CALENDAR###'] = '';
				$sims['###CALENDAR_VALUE###'] = '';
			}
		} else {
			$sims['###CALENDAR###'] = '';
			$sims['###CALENDAR_VALUE###'] = '';
		}
	}
	
	function getHeaderStyleMarker(& $template, & $rems, & $sims) {
		if(($this->editMode && $this->rightsObj->isAllowedToEditCategoryHeaderStyle()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCategoryHeaderStyle())) {
			$headerStyleValue = strip_tags($this->controller->piVars['headerstyle']);
			$sims['###HEADERSTYLE###'] = $this->cObj->stdWrap($headerStyleValue, $this->conf['view.'][$this->conf['view'].'.']['headerStyle_stdWrap.']);
			$sims['###HEADERSTYLE_VALUE###'] = $headerStyleValue;
		} else {
			$sims['###HEADERSTYLE###'] = '';
			$sims['###HEADERSTYLE_VALUE###'] = '';
		}
	}
	
	function getBodyStyleMarker(& $template, & $rems, & $sims) {
		if(($this->editMode && $this->rightsObj->isAllowedToEditCategoryBodyStyle()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCategoryBodyStyle())) {
			$bodyStyleValue = strip_tags($this->controller->piVars['bodystyle']);
			$sims['###BODYSTYLE###'] = $this->cObj->stdWrap($bodyStyleValue, $this->conf['view.'][$this->conf['view'].'.']['bodyStyle_stdWrap.']);
			$sims['###BODYSTYLE_VALUE###'] = $bodyStyleValue;
		} else {
			$sims['###BODYSTYLE###'] = '';
			$sims['###BODYSTYLE_VALUE###'] = '';
		}
	}
	
	function getParentCategoryMarker(& $template, &$rems, & $sims) {
		if(($this->editMode && $this->rightsObj->isAllowedToEditCategoryParent()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCategoryParent())) {
			$parentUid = 0;
			if(is_array($this->controller->piVars['parent_category'])){
				$parentUid = intval($this->controller->piVars['parent_category'][0]);
			}
			unset($this->controller->piVars['parent_category']);

			if($parentUid) {
				/* Get parent category title */
				$category = $this->modelObj->findCategory($parentUid,'tx_cal_category',$this->conf['pidList']);
				$parentCategory = $category->getTitle();
				$sims['###PARENT_CATEGORY###'] = $this->cObj->stdWrap($parentCategory, $this->conf['view.'][$this->conf['view'].'.']['parentCategory_stdWrap.']);
				$sims['###PARENT_CATEGORY_VALUE###'] = $parentUid;
			} else {
				$sims['###PARENT_CATEGORY###'] = '';
				$sims['###PARENT_CATEGORY_VALUE###'] = '';
			}
		} else {
			$sims['###PARENT_CATEGORY###'] = '';
			$sims['###PARENT_CATEGORY_VALUE###'] = '';
			
		}
	}
	
	function getSharedUserAllowedMarker(& $template, & $rems, & $sims) {
		if(($this->editMode && $this->rightsObj->isAllowedToEditCategorySharedUser()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateCategorySharedUser())) {
			if ($this->controller->piVars['shared_user_allowed'] == 'on') {
				$value = 1;
				$label = $this->controller->pi_getLL('l_true');
			} else {
				$value = 0;
				$label = $this->controller->pi_getLL('l_false');
			}
		
			$sims['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap($label, $this->conf['view.'][$this->conf['view'].'.']['sharedUserAllowed_stdWrap.']);
			$sims['###SHARED_USER_ALLOWED_VALUE###'] = $value;
		} else {
			$sims['###SHARED_USER_ALLOWED###'] = '';
			$sims['###SHARED_USER_ALLOWED###'] = '';
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_category_view.php']);
}
?>