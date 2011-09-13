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
class tx_cal_create_location_organizer_view extends t3lib_svbase {

	var $local_pibase;
	var $cObj;
	var $conf;
	var $shared;
	var $prefixId = 'tx_cal_controller';

	/**
	 *  Draws a create location or organizer form.
	 *  @param		boolean		True if a location should be confirmed
	 *  @param		object		The cObject of the mother-class
	 *  @param		object		The rights object
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateLocationOrOrganizer($isLocation=true, &$cObj, $rightsObj, $pidList, $object=''){	
		$this->cObj = $cObj;
		$tx_cal_shared = t3lib_div::makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared($this->cObj);
		$page = $this->cObj->fileResource($cObj->conf["view."]["location."]["createLocationTemplate"]);
		if ($page=="") {
			return "<h3>calendar: no create location template file found:</h3>".$cObj->conf["view."]["location."]["createLocationTemplate"];
		}
		
		if($isLocation){
			$url = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, array("no_cache"=>1,"tx_cal_controller[view]"=>"confirm_location","tx_cal_controller[lastview]"=>$cObj->conf['lastview'],"tx_cal_controller[getdate]"=>$cObj->conf['getdate']));
		}else{
			$url = $this->shared->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre, array("no_cache"=>1,"tx_cal_controller[view]"=>"confirm_organizer","tx_cal_controller[lastview]"=>$cObj->conf['lastview'],"tx_cal_controller[getdate]"=>$cObj->conf['getdate']));
		}
		if(is_object($object)){
			$name = $object->getName();
			$description = $object->getDescription();
			$street = $object->getStreet();
			$zip = $object->getZip();
			$city = $object->getCity();
			$phone = $object->getPhone();
			$email = $object->getEmail();
			$image = $object->getImage();
			$link = $object->getLink();
		}
		$hidden = "checked";	
		$languageArray = array(
			'type'					=> $cObj->conf['type'],
			'l_hidden'				=> $this->shared->lang('l_hidden'),
			'hidden'				=> $hidden,
			'l_name'				=> $this->shared->lang('l_location_name'),
			'name'					=> $name,
			'l_description'			=> $this->shared->lang('l_location_description'),
			'description'			=> $description,
			'l_street'				=> $this->shared->lang('l_location_street'),
			'street'				=> $street,
			'l_zip'					=> $this->shared->lang('l_location_zip'),
			'zip'					=> $zip,
			'l_city'				=> $this->shared->lang('l_location_city'),
			'city'					=> $city,
			'l_phone'				=> $this->shared->lang('l_location_phone'),
			'phone'					=> $phone,
			'l_email'				=> $this->shared->lang('l_location_email'),
			'email'					=> $email,
			'l_image'				=> $this->shared->lang('l_location_image'),
			'image_url'				=> $image,
			'l_link'				=> $this->shared->lang('l_location_link'),
			'link'					=> $link,
			'l_submit'				=> $this->shared->lang('l_submit'),
			'l_cancel'				=> $this->shared->lang('l_cancel'),
			'action_url'			=> $url,
		);

		$page = $this->shared->replace_tags($languageArray,$page);		
		return $page;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_location_organizer_view.php']);
}
?>