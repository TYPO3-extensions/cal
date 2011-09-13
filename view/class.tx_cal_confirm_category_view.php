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
		unset($this->controller->piVars['formCheck']);
		$page = $this->cObj->fileResource($this->conf['view.']['confirm_category.']['template']);
		if ($page=='') {
			return '<h3>category: no create category template file found:</h3>'.$this->conf['view.']['confirm_category.']['template'];
		}

		$a = Array();
		$this->object = new tx_cal_category_model($a, '');
		$this->object->updateWithPIVars($this->controller->piVars);
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		
		if($lastViewParams['view']=='edit_category'){
			$this->isEditMode = true;
		}
		
		$rems = array();
		$sims = array();
		$wrapped = array();
		$sims['###L_CONFIRM_CATEGORY###'] = $this->controller->pi_getLL('l_confirm_category');
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_category';
		$sims['###L_SUBMIT###'] = $this->controller->pi_getLL('l_submit');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_category'));

		$this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$wrapped = array();
		$this->getTemplateSingleMarker($page, $sims, $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, $wrapped);;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function getCalendarIdMarker(& $template, & $sims, & $rems){
		$sims['###CALENDAR_ID###'] = '';
		$sims['###CALENDAR_ID_VALUE###'] = '';
		if($this->isAllowed('calendar_id')) {			
			if($calendar = $this->object->getCalendarObject()){
				$sims['###CALENDAR_ID###'] = $this->applyStdWrap($calendar->getTitle(),'calendar_id_stdWrap');
				$sims['###CALENDAR_ID_VALUE###'] = htmlspecialchars($calendar->getUID());
			}
		}
	}
	
	function getHeaderstyleMarker(& $template, & $sims, & $rems) {
		$sims['###HEADERSTYLE###'] = '';
		$sims['###HEADERSTYLE_VALUE###'] = '';
		if($this->isAllowed('headerstyle')) {
			$headerStyleValue = $this->object->getHeaderStyle();
			$sims['###HEADERSTYLE###'] = $this->applyStdWrap($headerStyleValue, 'headerstyle_stdWrap');
			$sims['###HEADERSTYLE_VALUE###'] = $headerStyleValue;
		}
	}
	
	function getBodystyleMarker(& $template, & $sims, & $rems) {
		$sims['###BODYSTYLE###'] = '';
		$sims['###BODYSTYLE_VALUE###'] = '';
		if($this->isAllowed('bodystyle')) {
			$bodyStyleValue = $this->object->getBodyStyle();
			$sims['###BODYSTYLE###'] = $this->applyStdWrap($bodyStyleValue, 'bodystyle_stdWrap');
			$sims['###BODYSTYLE_VALUE###'] = $bodyStyleValue;
		}
	}
	
	function getParentCategoryMarker(& $template, &$sims, & $rems) {
		$sims['###PARENT_CATEGORY###'] = '';
		$sims['###PARENT_CATEGORY_VALUE###'] = '';
		if($this->isAllowed('parent_category')) {
			$parentUid = $this->object->getParentUid();
			if($parentUid) {
				/* Get parent category title */
				$category = $this->modelObj->findCategory($parentUid,'tx_cal_category',$this->conf['pidList']);
				$sims['###PARENT_CATEGORY###'] = $this->applyStdWrap($category->getTitle(), 'parent_category_stdWrap');
				$sims['###PARENT_CATEGORY_VALUE###'] = $parentUid;
			}
		}
	}
	
	function getSharedUserAllowedMarker(& $template, & $sims, & $rems) {
		$sims['###SHARED_USER_ALLOWED###'] = '';
		$sims['###SHARED_USER_ALLOWED_VALUE###'] = '';
		if($this->isAllowed('shared_user_allowed')) {
			if ($this->object->isSharedUserAllowed()) {
				$value = 1;
				$label = $this->controller->pi_getLL('l_true');
			} else {
				$value = 0;
				$label = $this->controller->pi_getLL('l_false');
			}
		
			$sims['###SHARED_USER_ALLOWED###'] = $this->applyStdWrap($label, 'shared_user_allowed_stdWrap');
			$sims['###SHARED_USER_ALLOWED_VALUE###'] = $value;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_category_view.php']);
}
?>