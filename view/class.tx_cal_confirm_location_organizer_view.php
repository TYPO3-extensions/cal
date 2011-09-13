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
 * A service which renders a form to confirm the location/organizer create/edit.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_location_organizer_view extends tx_cal_fe_editing_base_view {
	
	var $isLocation= true;
	
	function tx_cal_confirm_location_organizer_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a confirm form for a location or an organizer.
	 *  @param      boolean     True if a location should be confirmed
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawConfirmLocationOrOrganizer($isLocation=true){
		
		$this->isLocation = $isLocation;
		$this->isConfirm = true;
		if($isLocation){
			$this->objectString = 'location';
		}else{
			$this->objectString = 'organizer';
		}
		
		$page = $this->cObj->fileResource($this->conf['view.']['location.']['confirmLocationTemplate']);
		if ($page=='') {
			return '<h3>calendar: no confirm '.$this->objectString.' template file found:</h3>'.$this->conf['view.']['location.']['confirmLocationTemplate'];
		}
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		
		if(substr($lastViewParams['view'],0,4)=='edit'){
			$this->editMode = true;
		}
		
	
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_'.$this->objectString;
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_CONFIRM_EVENT###'] = $this->controller->pi_getLL('l_confirm_'.$this->objectString);
		$sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'save_'.$this->objectString,'category'=>null));
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
	
	function getCountryMarker(& $template, & $rems, & $sims){
		// Initialise static info library
		if(t3lib_extMgm::isLoaded('static_info_tables')) {
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
		
			$sims['###COUNTRY###'] = $this->cObj->stdWrap($this->staticInfo->getStaticInfoName('COUNTRIES', strip_tags($this->controller->piVars['country'])), $this->conf['view.'][$this->conf['view'].'.']['country_static_info_stdWrap.']);
			$sims['###COUNTRY_VALUE###'] = strip_tags($this->controller->piVars['country']);
		} else {
			$sims['###COUNTRY###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['country']), $this->conf['view.'][$this->conf['view'].'.']['country_stdWrap.']);
			$sims['###COUNTRY_VALUE###'] = strip_tags($this->controller->piVars['country']);
		}
	}
	
	function getCountryzoneMarker(& $template, & $rems, & $sims){
		// Initialise static info library
		if(t3lib_extMgm::isLoaded('static_info_tables')) {
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
			$sims['###COUNTRYZONE###'] = $this->cObj->stdWrap($this->staticInfo->getStaticInfoName('SUBDIVISIONS', strip_tags($this->controller->piVars['countryzone']), strip_tags($this->controller->piVars['country'])), $this->conf['view.'][$this->conf['view'].'.']['countryzone_static_info_stdWrap.']);
			$sims['###COUNTRYZONE_VALUE###'] = strip_tags($this->controller->piVars['countryzone']);
		} else {
			$sims['###COUNTRYZONE###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['countryzone']), $this->conf['view.'][$this->conf['view'].'.']['countryzone_stdWrap.']);
			$sims['###COUNTRYZONE_VALUE###'] = strip_tags($this->controller->piVars['countryzone']);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']);
}
?>