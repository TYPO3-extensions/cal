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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * A service which renders a form to create / edit a phpicategory event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_category_view extends tx_cal_base_view {

	
	/**
	 *  Draws a create category form.
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateCategory($pidList, $object=''){	

		$page = $this->cObj->fileResource($this->conf["view."]["category."]["createCategoryTemplate"]);
		if ($page=="") {
			return "<h3>category: no create category template file found:</h3>".$this->conf["view."]["category."]["createCategoryTemplate"];
		}
		
		// creating options for organizer
		$cal_organizer = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		$select = "uid,username";
		$table = "fe_users";
		$where = "";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			
			if($object && $object['fe_user_id']==$row['uid']){
				$users .= '<option value="'.$row['uid'].'" selected="selected">'.$row['username'].'</option>';
			}else{
				$users .= '<option value="'.$row['uid'].'" >'.$row['username'].'</option>';
			}
		}
		$form 	= $this->cObj->getSubpart($page, "###FORM_START###");
		
		$languageArray = array();
		
		if(!$object){
			$languageArray ['l_create_category'] = $this->controller->pi_getLL('l_create_category');
			$languageArray['uid'] = "";
			if($this->rightsObj->isAllowedToCreateCategoryHidden()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
				$languageArray['l_hidden'] = $this->controller->pi_getLL('l_hidden');
				$languageArray['hidden'] = "";
			}
			if($this->rightsObj->isAllowedToCreateCategoryTitle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
				$languageArray['l_title'] = $this->controller->pi_getLL('l_event_title');
				$languageArray['title'] = "";
			}
			if($this->rightsObj->isAllowedToCreateCategoryHeaderstyle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HEADRSTYLE###");
				$languageArray['l_headerstyle'] = $this->controller->pi_getLL('l_category_headerstyle');
				$languageArray['headerstyle'] = "";
			}
			
			if($this->rightsObj->isAllowedToCreateCategoryBodystyle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_BODYSTYLE###");
				$languageArray['l_bodystyle'] = $this->controller->pi_getLL('l_category_bodystyle');
				$languageArray['bodystyle'] = "";
			}
			if($this->rightsObj->isAllowedToCreateCategoryFeUser()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_FEUSER###");
				$languageArray['l_fe_user'] = $this->controller->pi_getLL('l_fe_user');
				$languageArray['fe_user_ids'] = $users;
			}
			
		}else{
			$languageArray ['l_create_category'] = $this->controller->pi_getLL('l_edit_category');
			$languageArray['uid'] = $object['uid'];
			if($this->rightsObj->isAllowedToEditCategoryHidden()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HIDDEN###");
				$languageArray['l_hidden'] = $this->controller->pi_getLL('l_hidden');
				if($object['hidden']==1){
					$languageArray['hidden'] = "checked";
				}else{
					$languageArray['hidden'] = "";
				}
			}
			if($this->rightsObj->isAllowedToEditCategoryTitle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_TITLE###");
				$languageArray['l_title'] = $this->controller->pi_getLL('l_event_title');
				$languageArray['title'] = $object['title'];
			}
			if($this->rightsObj->isAllowedToCreateCategoryHeaderstyle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_HEADERSTYLE###");
				$languageArray['l_headerstyle'] = $this->controller->pi_getLL('l_category_headerstyle');
				$languageArray['headerstyle'] = $object['headerstyle'];
			}
			
			if($this->rightsObj->isAllowedToCreateCategoryBodystyle()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_BODYSTYLE###");
				$languageArray['l_bodystyle'] = $this->controller->pi_getLL('l_category_bodystyle');
				$languageArray['bodystyle'] = $object['bodystyle'];
			}
			if($this->rightsObj->isAllowedToEditCategoryFeUser()){
				$form 	.= $this->cObj->getSubpart($page, "###FORM_FEUSER###");
				$languageArray['l_fe_user'] = $this->controller->pi_getLL('l_fe_user');
				$languageArray['fe_user_ids'] = $users;
			}
		}
		$form 	.= $this->cObj->getSubpart($page, "###FORM_END###");
		
		
		$languageArray['type'] = "tx_cal_category";
		$languageArray['l_submit'] = $this->controller->pi_getLL('l_submit');
		$languageArray['l_cancel'] = $this->controller->pi_getLL('l_cancel');
		$languageArray['action_url'] = $this->controller->pi_linkTP_keepPIvars_url(array("view"=>"confirm_category"));
			
		return $this->controller->replace_tags($languageArray,$form);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_category_view.php']);
}
?>