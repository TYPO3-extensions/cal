<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
 * (http://evangelize.org). The WEC is developing TYPO3-based
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
 * A service which renders a form to create / edit a location or organizer.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_delete_location_organizer_view extends tx_cal_fe_editing_base_view {
	
	var $isLocation = true;
	var $objectString = 'location';
	
	function tx_cal_delete_location_organizer_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a delete form for a location or an organizer.
	 *  @param      boolean     True if a location should be deleted
	 *  @param		object		The object to be deleted
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteLocationOrOrganizer($isLocation=true, &$object){
		
		$page = $this->cObj->fileResource($this->conf['view.']['delete_location.']['template']);
		if ($page=='') {
			return '<h3>category: no delete location template file found:</h3>'.$this->conf['view.']['delete_location.']['template'];
		}
		
		$this->isLocation = $isLocation;
		$this->object = $object;
		if($isLocation){
			$this->objectString = 'location';
		}else{
			$this->objectString = 'organizer';
		}
		
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'remove_'.$this->objectString;
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_DELETE_LOCATION###'] = $this->controller->pi_getLL('l_delete_'.$this->objectString);
		$sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'remove_'.$this->objectString));
		$this->getTemplateSubpartMarker($page, $sims, $rems);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$wrapped = array();
		$this->object->getMarker($page, $sims, $rems, $wrapped);

		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, $wrapped);;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_location_organizer_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_location_organizer_view.php']);
}
?>