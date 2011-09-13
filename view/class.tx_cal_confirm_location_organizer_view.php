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
//debug($this->controller->piVars);		
		$this->isLocation = $isLocation;
		$this->isConfirm = true;
		if($isLocation){
			$this->objectString = 'location';
		}else{
			$this->objectString = 'organizer';
		}
		
		$page = $this->cObj->fileResource($this->conf['view.']['confirm_location.']['template']);
		if ($page=='') {
			return '<h3>calendar: no confirm '.$this->objectString.' template file found:</h3>'.$this->conf['view.']['confirm_location.']['template'];
		}
		
		require_once (t3lib_extMgm :: extPath('cal').'model/class.tx_cal_'.$this->objectString.'.php');
		
		if($isLocation){
			$this->object = new tx_cal_location(null, '');
		}else{
			$this->object = new tx_cal_organizer(null, '');
		}
		$this->object->updateWithPIVars($this->controller->piVars);
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		
		if(substr($lastViewParams['view'],0,4)=='edit'){
			$this->isEditMode = true;
		}
			
		$rems = array();
		$sims = array();
		$wrapped = array();
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_'.$this->objectString;
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_CONFIRM_LOCATION###'] = $this->controller->pi_getLL('l_confirm_'.$this->objectString);
		$sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url( array('view'=>'save_'.$this->objectString,'category'=>null)));
		
		$this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, array(), $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$wrapped = array();
		$this->getTemplateSingleMarker($page, $sims, $rems, $wrapped);
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, array(), $rems, $wrapped);;
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, array(), array ());
		return tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, array(), array ());
	}
	
	function getCountryMarker(& $template, & $sims, & $rems){
		// Initialise static info library
		$sims['###COUNTRY###'] = '';
		$sims['###COUNTRY_VALUE###'] = '';
		if($this->isAllowed('country')){
			if(t3lib_extMgm::isLoaded('static_info_tables')) {
				require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
				$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
				$this->staticInfo->init();			
				$sims['###COUNTRY###'] = $this->applyStdWrap($this->staticInfo->getStaticInfoName('COUNTRIES', $this->object->getCountry()), 'country_static_info_stdWrap');
				$sims['###COUNTRY_VALUE###'] = strip_tags($this->object->getCountry());
			} else {
				$sims['###COUNTRY###'] = $this->applyStdWrap($this->object->getCountry(), 'country_stdWrap');
				$sims['###COUNTRY_VALUE###'] = $this->object->getCountry();
			}
		}
	}
	
	function getCountryzoneMarker(& $template, & $sims, & $rems){
		// Initialise static info library
		$sims['###COUNTRYZONE###'] = '';
		$sims['###COUNTRYZONE_VALUE###'] = '';
		if($this->isAllowed('countryzone')){
			if(t3lib_extMgm::isLoaded('static_info_tables')) {
				require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
				$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
				$this->staticInfo->init();
				$sims['###COUNTRYZONE###'] = $this->applyStdWrap($this->staticInfo->getStaticInfoName('SUBDIVISIONS', $this->object->getCountryZone(), $this->object->getCountry()), 'countryzone_static_info_stdWrap');
				$sims['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone();
			} else {
				$sims['###COUNTRYZONE###'] = $this->applyStdWrap($this->object->getCountryZone(), 'countryzone_stdWrap');
				$sims['###COUNTRYZONE_VALUE###'] = $this->object->getCountryZone();
			}
		}
	}
	
	function getSharedMarker(& $template, & $sims, & $rems){
		$sims['###SHARED###'] = '';
		if($this->isAllowed('shared') && is_array($this->controller->piVars['shared'])) {
			$shareddisplaylist = Array();
			$sharedids = Array();
			foreach ($this->controller->piVars['shared'] as $value) {
				preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
				if($idname[1]=='u' || $idname[1]=='g'){
					$sharedids[] = $idname[1].'_'.$idname[2];
					$shareddisplaylist[] = $idname[3];
				}
			}
			$sims['###SHARED###'] = $this->applyStdWrap(implode(',',$shareddisplaylist), 'shared_stdWrap');
			$sims['###SHARED_VALUE###'] = htmlspecialchars(implode(',',$sharedids));
		}
	}	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_location_organizer_view.php']);
}
?>
