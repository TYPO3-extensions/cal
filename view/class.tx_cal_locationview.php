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


require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_locationview extends tx_cal_base_view {

	function tx_cal_locationview(){
		$this->tx_cal_base_view();
	}
	
	/**
	 *  Draws a location.
	 *  @param		object		The location to be drawn.
	 *	@return		string		The HTML output.
	 */
	function drawLocation($location, $relatedEvents=Array()) {
		$this->_init($relatedEvents);
		$lastview = $this->controller->extendLastView();
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$page = $this->cObj->fileResource($this->conf['view.']['location.']['locationTemplate']);
		if ($page == '') {
			return $this->createErrorMessage(
				'No location template file found at: >'.$this->conf['view.']['location.']['locationTemplate'].'<.',
				'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.'
			);
		}
		$rems = Array();
		$sims = Array();
		$wrapped = Array();
		if(is_object($location)){
			$rems['###LOCATION###'] = $location->renderLocation();
			if($this->conf['view.']['location.']['substitutePageTitle']==1){
				$GLOBALS['TSFE']->page['title'] = $location->getName();
				$GLOBALS['TSFE']->indexedDocTitle = $location->getName();
			}
		}else{
			$rems['###LOCATION###'] = $this->cObj->cObjGetSingle($this->conf['view.']['location.']['noLocationFound'],$this->conf['view.']['location.']['noLocationFound.']);
		}
		return $this->finish($page, $rems);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_locationview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_locationview.php']);
}
?>