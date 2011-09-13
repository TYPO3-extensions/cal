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

require_once (PATH_t3lib.'class.t3lib_svbase.php');
require_once (PATH_tslib."class.tslib_pibase.php");
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_shared.php');

/**
 * A service which renders a form to create / edit a phpicategory event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_delete_category_view extends t3lib_svbase {

	var $cObj;
	var $rightsObj;
	var $controller;
	var $shared;
	var $prefixId = 'tx_cal_controller';

	function setCObj(&$cObj){
		$this->cObj = &$cObj;
		$this->controller = &$cObj->conf[$this->prefixId];
		$this->rightsObj = &$this->controller->rightsObj;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);
	}
	
	function setRightsObj(&$rightsObj){
		$this->rightsObj = $rightsObj;
	}
	
	/**
	 *  Draws a delete form for a category.
	 *  @param      boolean     True if a location should be deleted
	 *  @param		object		The object to be deleted
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteCategory(&$object){
		
		$page = $this->cObj->fileResource($this->cObj->conf["view."]["category."]["confirmCategoryTemplate"]);
		if ($page=="") {
			return "<h3>category: no confirm category template file found:</h3>".$cObj->conf["view."]["category."]["confirmCategoryTemplate"];
		}

		$languageArray = array();
		$form 	= $this->cObj->getSubpart($page, "###FORM_START###");
		$languageArray ['l_confirm_category'] = $this->shared->lang('l_delete_category');
		if($this->rightsObj->isAllowedToEditCategoryHidden() || $this->rightsObj->isAllowedToCreateCategoryHidden()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
			if ($object['hidden'] == 1) {
				$hidden = "true";
			} else {
				$hidden = "false";
			}
			$languageArray['l_hidden'] = $this->shared->lang('l_hidden');
			$languageArray['hidden'] = $this->shared->lang('l_'.$hidden);
		}
		
		if($this->rightsObj->isAllowedToEditCategoryTitle() || $this->rightsObj->isAllowedToCreateCategoryTitle()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
			$languageArray['l_title'] = $this->shared->lang('l_event_title');
			$languageArray['title'] = $object['title'];
		}
		if($this->rightsObj->isAllowedToEditCategoryHeaderstyle() || $this->rightsObj->isAllowedToCreateCategoryHeaderstyle()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_HEADERSTYLE###");
			$languageArray['l_headerstyle'] = $this->shared->lang('l_category_headerstyle');
			$languageArray['headerstyle'] = $object['headerstyle'];
		}
		
		if($this->rightsObj->isAllowedToEditCategoryBodystyle() || $this->rightsObj->isAllowedToCreateCategoryBodystyle()){
			$form 	.= $this->cObj->getSubpart($page, "###FORM_BODYSTYLE###");
			$languageArray['l_bodystyle'] = $this->shared->lang('l_category_bodystyle');
			$languageArray['bodystyle'] = $object['bodystyle'];
		}
		
		if($this->rightsObj->isAllowedToEditCategoryFeUser() || $this->rightsObj->isAllowedToCreateCategoryFeUser()){
			if($object['fe_user_id']!=0){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_FEUSER###");
				$languageArray['l_fe_user'] = $this->shared->lang('l_fe_user');
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,username","fe_users","uid = ".$object["fe_user_id"]."");
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$languageArray['fe_user_id'] = $row['uid'];
					$languageArray['fe_user_name'] = $row['username'];
				}
			}
		}	
		$form 	.= $this->cObj->getSubpart($page, "###FORM_END###");
		$languageArray['uid'] = $object['uid'];
		$languageArray['type'] = "tx_cal_category";
		$languageArray['l_submit'] = $this->shared->lang('l_delete');
		$languageArray['l_cancel'] = $this->shared->lang('l_cancel');
		$languageArray['action_url'] = $this->controller->pi_linkTP_keepPIvars_url(array("view"=>"remove_category"));
		
		$page = $this->shared->replace_tags($languageArray,$form);		
		return $page;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_category_view.php']);
}
?>