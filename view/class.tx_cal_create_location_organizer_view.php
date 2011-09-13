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
 * A service which renders a form to create / edit an event location / organizer.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_location_organizer_view extends tx_cal_fe_editing_base_view {

	var $isLocation;
	
	function tx_cal_create_location_organizer_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a create location or organizer form.
	 *  @param		boolean		True if a location should be confirmed
	 *  @param		string		Comma separated list of pids.
	 *  @param		object		A location or organizer object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateLocationOrOrganizer($isLocation=true, $pidList, $object=''){	

		$page = $this->cObj->fileResource($this->conf['view.']['location.']['createLocationTemplate']);
		if ($page=='') {
			return '<h3>calendar: no create location template file found:</h3>'.$this->conf['view.']['location.']['createLocationTemplate'];
		}
		$this->isLocation = $isLocation;
		if($isLocation){
			$this->objectString = 'location';
		}else{
			$this->objectString = 'organizer';
		}
		if(is_object($object) && !$object->isUserAllowedToEdit()){
			return $this->controller->pi_getLL('l_not_allowed_edit').$this->objectString;
		}else if(!is_object($object) && !$this->rightsObj->isAllowedTo('create',$this->objectString,'')){
			return $this->controller->pi_getLL('l_not_allowed_create').$this->objectString;
		}
		
		$sims = array();
		$rems = array();
		
		$sims['###TYPE###'] = 'tx_cal_'.$this->objectString;
		$sims['###L_CREATE_LOCATION###'] = $this->controller->pi_getLL('l_create_'.$this->objectString);
		
		
		if(is_object($object)){
			$this->isEditMode = true;
			$this->object = $object;
			$sims['###UID###'] = $this->object->getUid();
			$sims['###TYPE###'] = $this->object->getType();
			$sims['###L_CREATE_LOCATION###'] = $this->controller->pi_getLL('l_edit_'.$this->objectString);
		}
		
		$this->getTemplateSubpartMarker($page, $rems, $sims);

		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
                
		$sims = array();
		$rems = array();
		
		$this->getTemplateSingleMarker($page, $rems, $sims);

		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'confirm_'.$this->objectString));
			
        $page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());

		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	
	function getCountryMarker(& $template, & $rems, & $sims){
		// Initialise static info library
		if(t3lib_extMgm::isLoaded('static_info_tables')) {
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
		
			$sims['###COUNTRY###'] = $this->cObj->stdWrap($this->staticInfo->buildStaticInfoSelector('COUNTRIES', 'tx_cal_controller[country]','', $this->isEditMode?$this->object->getCountry():$country), $this->conf['view.'][$this->conf['view'].'.']['country_static_info_stdWrap.']);
		} else {
			$sims['###COUNTRY###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['country_stdWrap.']);
		}
	}
	
	function getCountryzoneMarker(& $template, & $rems, & $sims){
		// Initialise static info library
		if(t3lib_extMgm::isLoaded('static_info_tables')) {
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$this->staticInfo = t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
			$sims['###COUNTRYZONE###'] = $this->cObj->stdWrap($this->staticInfo->buildStaticInfoSelector('SUBDIVISIONS', 'tx_cal_controller[countryzone]','',$this->isEditMode?$this->object->getCountryZone():$countryzone,$country), $this->conf['view.'][$this->conf['view'].'.']['countryzone_static_info_stdWrap.']);
		} else {
			$sims['###COUNTRYZONE###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['countryzone_stdWrap.']);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_location_organizer_view.php']);
}
?>