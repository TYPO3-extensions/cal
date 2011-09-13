<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_location_model.php');

/**
 * Base model for the calendar location.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_location extends tx_cal_location_model {
 	 
 	 var $type = 'tx_cal_location';
 	 
 	 /**
 	  * Constructor
 	  * @param	object		$this->cObj		The content object
	  * @param	integer		$uid		The uid to search for
	  * @param	string		$pidList	The pid-list to search in
 	  */
 	 function tx_cal_location(&$controller, $row, $pidList){
	 	
		$this->tx_cal_location_model($controller, $this->type);
		$this->createLocation($row);
 	 }
 	 
	 
	 function createLocation($row){
	 	$this->row = $row;
	 	$this->setUid($row['uid']);
		$this->setName($row['name']);
		$this->setDescription($row['title']);
		$this->setStreet($row['street']);
		$this->setPostalCode($row['zip']);
		$this->setCity($row['city']);
		$this->setPhone($row['phone']);
		$this->setEmail($row['email']);
		$this->setImage($row['image']);
		$this->setLink($row['link']);
	 }
	 
	 function renderLocation(){	
		$page = $this->cObj->fileResource($this->conf['view.']['location.']['locationTemplate']);
		if ($page=='') {
			return '<h3>calendar: no location template file found:</h3>'.$this->conf['view.']['location.']['locationTemplate'];
		}		
		$rems = array();
		$sims = array();
		$this->getLocationMarker($page, $sims, $rems);

		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	 }
	 
	/**
	 *  
	 */
	function getCalLegendDescription() { 
		return array(); 
	}
	
		/**
	 * Returns the type attribute
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute. This should be the service type
	 * @param	$type	string	The service type
	 */
	function setType($type) {
		$this->type = $type;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_location.php']);
}
?>