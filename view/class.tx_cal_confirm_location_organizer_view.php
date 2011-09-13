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
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_location_organizer_view extends tx_cal_base_view {
	
	/**
	 *  Draws a confirm form for a location or an organizer.
	 *  @param      boolean     True if a location should be confirmed
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawConfirmLocationOrOrganizer($isLocation=true){

		$page = $this->cObj->fileResource($this->conf['view.']['location.']['confirmLocationTemplate']);
		if ($page=='') {
			return '<h3>calendar: no confirm event template file found:</h3>'.$this->conf['view.']['location.']['confirmLocationTemplate'];
		}

		if($isLocation){
			$url = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_location','lastview'=>$this->controller->extendLastView(),'getdate'=>$this->conf['getdate']));
		}else{
			$url = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_organizer','lastview'=>$this->controller->extendLastView(),'getdate'=>$this->conf['getdate']));
		}		
		
		if($this->controller->piVars['hidden']=='on'){
			$hidden = 'true';
		}else{
			$hidden = 'false';
		}
		
		$languageArray = array(
			'type'					=> $this->conf['type'],
			'l_hidden'				=> $this->controller->pi_getLL('l_location_hidden'),
			'hidden'				=> $hidden,
			'l_name'				=> $this->controller->pi_getLL('l_location_name'),
			'name'					=> strip_tags($this->controller->piVars['name']),
			'l_description'			=> $this->controller->pi_getLL('l_location_description'),
			'description'			=> $this->cObj->removeBadHTML($this->controller->piVars['description']),
			'l_street'				=> $this->controller->pi_getLL('l_location_street'),
			'street'				=> strip_tags($this->controller->piVars['street']),
			'l_zip'					=> $this->controller->pi_getLL('l_location_zip'),
			'zip'					=> strip_tags($this->controller->piVars['zip']),
			'l_city'				=> $this->controller->pi_getLL('l_location_city'),
			'city'					=> strip_tags($this->controller->piVars['city']),
			'l_phone'				=> $this->controller->pi_getLL('l_location_phone'),
			'phone'					=> strip_tags($this->controller->piVars['phone']),
			'l_email'				=> $this->controller->pi_getLL('l_location_email'),
			'email'					=> strip_tags($this->controller->piVars['email']),
			'l_image'				=> $this->controller->pi_getLL('l_location_image'),
			'image_url'				=> strip_tags($this->controller->piVars['image_url']),
			'l_link'				=> $this->controller->pi_getLL('l_location_link'),
			'link'					=> strip_tags($this->controller->piVars['link']),
			'l_save'				=> $this->controller->pi_getLL('l_save'),
			'l_cancel'				=> $this->controller->pi_getLL('l_cancel'),
			'action_url'			=> $url,
		);

		$page = $this->controller->replace_tags($languageArray,$page);		
		return $page;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']);
}
?>