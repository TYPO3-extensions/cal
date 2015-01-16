<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2008 Christian Technology Ministries International Inc.
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
 * *************************************************************
 */
class tx_cal_wecmap {
	
	/**
	 * Hook to post process map markers for Calendar Base locations.
	 *
	 * @param
	 *        	array		Main parameters. 'table' contains the table name,
	 *        	'data' contains the current row, and 'markerObj'
	 *        	contains the marker object
	 */
	function getMarkerContent(&$params) {
		$table = $params ['table'];
		$data = $params ['data'];
		$markerObj = $params ['markerObj'];
		
		$confArray = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$locationStructure = $this->confArr ['useLocationStructure'] ? $this->confArr ['useLocationStructure'] : 'tx_cal_location';
		
		if ($table == $locationStructure && is_object ($markerObj)) {
			$tx_cal_api = new tx_cal_api();
			
			$cObj = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
			$conf = $GLOBALS ['TSFE']->tmpl->setup ['plugin.'] ['tx_cal_controller.'];
			$conf ['view.'] ['allowedViews'] = 'location';
			
			$tx_cal_api = &$tx_cal_api->tx_cal_api_with ($cObj, $conf);
			$location = $tx_cal_api->modelObj->findLocation ($data ['uid'], $locationStructure, $data ['pid']);
			
			if (is_object ($location)) {
				$events = array_slice ((array) $tx_cal_api->controller->findRelatedEvents ('location', ' AND location_id = ' . $location->getUid ()), 0, 8);
				// $events = array_slice((array) $location->getEventLinks(), 0, 8);
				$eventsHTMLArray = Array ();
				
				foreach ($events as $eventTimeArray) {
					foreach ($eventTimeArray as $eventArray) {
						foreach ($eventArray as $event) {
							$eventsHTMLArray [] = $event->getLinkToEvent ($event->getTitle (), 'loaction', $event->getStart ()->format ('%Y%m%d'));
						}
					}
				}
				
				$tabLabel = '%%%events%%%';
				$tx_cal_api->controller->translateLanguageMarker ($tabLabel);
				
				$eventsHTMLArray = array_slice ($eventsHTMLArray, 0, 8);
				$eventsHTML = $this->stripNL (implode ('', $eventsHTMLArray));
				$markerObj->addTab ($tabLabel, '', $eventsHTML);
			}
		}
	}
	
	/**
	 * strip newlines
	 *
	 * @access private
	 * @param
	 *        	string		The input string to filtered.
	 * @return string converted string.
	 */
	function stripNL($input) {
		$order = array (
				"\r\n",
				"\n",
				"\r" 
		);
		$replace = '';
		$newstr = str_replace ($order, $replace, $input);
		
		return $newstr;
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/hooks/class.tx_cal_wecmap.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/hooks/class.tx_cal_wecmap.php']);
}
?>