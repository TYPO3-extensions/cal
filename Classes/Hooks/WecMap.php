<?php
namespace TYPO3\CMS\Cal\Hooks;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */
class WecMap {
	
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
			$tx_cal_api = new \TYPO3\CMS\Cal\Controller\Api ();
			
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

?>