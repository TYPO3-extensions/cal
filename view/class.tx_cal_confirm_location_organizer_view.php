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
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_location_organizer_view extends t3lib_svbase {

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
	
	/**
	 *  Draws a confirm form for a location or an organizer.
	 *  @param      boolean     True if a location should be confirmed
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawConfirmLocationOrOrganizer($isLocation=true){

		$page = $this->cObj->fileResource($this->cObj->conf["view."]["location."]["confirmLocationTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no confirm event template file found:</h3>".$this->cObj->conf["view."]["location."]["confirmLocationTemplate"];
		}

		if($isLocation){
			$url = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, array("no_cache"=>1,"tx_cal_controller[view]"=>"save_location","tx_cal_controller[lastview]"=>$this->cObj->conf['lastview'],"tx_cal_controller[getdate]"=>$this->cObj->conf['getdate']));
		}else{
			$url = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, array("no_cache"=>1,"tx_cal_controller[view]"=>"save_organizer","tx_cal_controller[lastview]"=>$this->cObj->conf['lastview'],"tx_cal_controller[getdate]"=>$this->cObj->conf['getdate']));
		}		
		
		if($this->controller->piVars["hidden"]=="on"){
			$hidden = "true";
		}else{
			$hidden = "false";
		}
		
		$languageArray = array(
			'type'					=> $this->controller->piVars["type"],
			'l_hidden'				=> $this->shared->lang('l_location_hidden'),
			'hidden'				=> $hidden,
			'l_name'				=> $this->shared->lang('l_location_name'),
			'name'					=> $this->controller->piVars["name"],
			'l_description'			=> $this->shared->lang('l_location_description'),
			'description'			=> $this->controller->piVars["description"],
			'l_street'				=> $this->shared->lang('l_location_street'),
			'street'				=> $this->controller->piVars["street"],
			'l_zip'					=> $this->shared->lang('l_location_zip'),
			'zip'					=> $this->controller->piVars["zip"],
			'l_city'				=> $this->shared->lang('l_location_city'),
			'city'					=> $this->controller->piVars["city"],
			'l_phone'				=> $this->shared->lang('l_location_phone'),
			'phone'					=> $this->controller->piVars["phone"],
			'l_email'				=> $this->shared->lang('l_location_email'),
			'email'					=> $this->controller->piVars["email"],
			'l_image'				=> $this->shared->lang('l_location_image'),
			'image_url'				=> $this->controller->piVars["image_url"],
			'l_link'				=> $this->shared->lang('l_location_link'),
			'link'					=> $this->controller->piVars["link"],
			'l_save'				=> $this->shared->lang('l_save'),
			'l_cancel'				=> $this->shared->lang('l_cancel'),
			'action_url'			=> $url,
		);

		$page = $this->shared->replace_tags($languageArray,$page);		
		return $page;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']);
}
?>