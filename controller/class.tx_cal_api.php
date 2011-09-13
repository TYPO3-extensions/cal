<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Mario Matzulla (mario(at)matzullas.de)
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

require_once ('class.tx_cal_controller.php');
require_once ('class.tx_cal_modelcontroller.php');
require_once ('class.tx_cal_viewcontroller.php');

/**
 * API for calendar base (cal)
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_api {
	
	var $cObj;
	var $rightsObj;
	var $modelObj;
	var $viewObj;
	var $controller;
	var $conf;
	var $prefixId = 'tx_cal_controller';
	
	function tx_cal_api(){
	}
	/**
	 * Example:
	 * 		require_once ('class.tx_cal_api.php');
	 * 		$tx_cal_api = t3lib_div :: makeInstanceClassName('tx_cal_api');
	 *		$calAPI = new $tx_cal_api (&$this->cObj, &$conf);
	 *		$event = $calAPI->findEvent('2','tx_cal_phpicalendar');
	 */
	function tx_cal_api_with(&$cObj, &$conf){
		$this->cObj = &$cObj;
		$this->conf = &$conf;
		$tx_cal_controller = t3lib_div :: makeInstanceClassName('tx_cal_controller');
		$this->controller = new $tx_cal_controller ();
		$this->controller->cObj = &$this->cObj;
		$this->controller->conf = &$this->conf;
		
		$this->rightsObj = t3lib_div::makeInstanceService('cal_rights_model', 'rights');
		$this->rightsObj->setController($this->controller);
		$this->controller->rightsObj = &$this->rightsObj;
		
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$this->modelObj = new $tx_cal_modelcontroller ($this->controller);

		$tx_cal_viewcontroller = t3lib_div :: makeInstanceClassName('tx_cal_viewcontroller');
		$this->viewObj = new $tx_cal_viewcontroller ($this->controller);
		return $this;
		
	}
	
	function tx_cal_api_without($pid){
		require_once (PATH_tslib.'/class.tslib_content.php');
		require_once(PATH_t3lib.'class.t3lib_tsparser_ext.php');
		require_once(PATH_t3lib.'class.t3lib_befunc.php');
		require_once(PATH_t3lib.'class.t3lib_page.php');
		$cObj = t3lib_div :: makeInstance('tslib_cObj');
		//we need to get the plugin setup to create correct source URLs
		$template = t3lib_div::makeInstance('t3lib_tsparser_ext'); // Defined global here!
		$template->tt_track = 0; 
		// Do not log time-performance information
		$template->init();
		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($pid);
		$template->runThroughTemplates($rootLine); // This generates the constants/config + hierarchy info for the template.
		$template->generateConfig();//
		$conf = $template->setup['plugin.']['tx_cal_controller.'];
		
		// get the calendar plugin record where starting pages value is the same
		// as the pid
		$fields = 'tt_content.pi_flexform AS flex';
		$tables = 'tt_content';
		$where = 'tt_content.list_type="cal_controller" AND tt_content.deleted=0 AND pid='.$pid;
		
		list($tt_content_row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields,$tables,$where);
		
		// if starting point didn't return any records, look for general records
		// storage page.
		if (!$tt_content_row) {
			$tables = 'tt_content LEFT JOIN pages ON tt_content.pid = pages.uid';
			$where = 'tt_content.list_type="cal_controller" AND tt_content.deleted=0 AND tt_content.pid='.$pid;
			list($tt_content_row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields,$tables,$where);
		}
			
		if ($tt_content_row['flex']){ 
			$flex_arr = t3lib_div::xml2array($tt_content_row['flex']);
			$conf['pages'] = $flex_arr['data']['sDEF']['lDEF']['pages']['vDEF'];
		}
		return $this->tx_cal_api_with($cObj, $conf);
	}
	
	function findEvent($uid, $type, $pidList='') {
		return $this->modelObj->findEvent($uid, $type, $pidList);
	}
	
	function saveEvent($uid, $type , $pidList='') {
		return $this->modelObj->saveEvent($uid, $type, $pidList);
	}
	
	function removeEvent($uid, $type) {
		return $this->modelObj->removeEvent($uid, $type);
	}
	
	function saveExceptionEvent($uid, $type, $pidList='') {
		return $this->modelObj->saveExceptionEvent($uid, $type, $pidList);
	}
	
	function findLocation($uid, $type, $pidList='') {
		return $this->modelObj->findLocation($uid, $type, $pidList);
	}
	
	function findAllLocations($type='',$pidList='') {
		return $this->modelObj->findAllLocations($type, $pidList);
	}
	
	function saveLocation($uid, $type, $pidList='') {
		return $this->modelObj->saveLocation($uid, $type, $pidList);
	}
	
	function removeLocation($uid, $type) {
		return $this->modelObj->removeLocation($uid, $type);
	}
	
	function findOrganizer($uid, $type, $pidList='') {
		return $this->modelObj->findOrganizer($uid, $type, $pidList);
	}
	
	function findCalendar($uid, $type, $pidList='') {
		return $this->modelObj->findCalendar($uid, $type, $pidList);
	}
	
	function findAllCalendar($type='',$pidList='') {
		return $this->modelObj->findAllCalendar($type, $pidList);
	}
	
	function findAllOrganizer($type='',$pidList='') {
		return $this->modelObj->findAllOrganizer($type, $pidList);
	}
	
	function saveOrganizer($uid, $type, $pidList='') {
		return $this->modelObj->saveOrganizer($uid, $type, $pidList);
	}
	
	function removeOrganizer($uid, $type) {
		return $this->modelObj->removeOrganizer($uid, $type);
	}
	
	function saveCalendar($uid, $type, $pidList='') {
		return $this->modelObj->saveCalendar($uid, $type, $pidList);
	}
	
	function removeCalendar($uid, $type) {
		return $this->modelObj->removeCalendar($uid, $type);
	}
	
	function saveCategory($uid, $type, $pidList='') {
		return $this->modelObj->saveCategory($uid, $type, $pidList);
	}
	
	function removeCategory($uid, $type) {
		return $this->modelObj->removeCategory($uid, $type);
	}
	
	function findEventsWithin($startTimestamp, $endTimestamp, $type='', $pidList='') {
		return $this->modelObj->findAllWithin('cal_event_model', $startTimestamp, $endTimestamp, $type, 'event', $pidList);
	}
	
	function findEventsForDay($timestamp, $type='', $pidList='') {
		return $this->modelObj->findEventsForDay($timestamp, $type, $pidList);
	}
	
	function findEventsForWeek($timestamp, $type='', $pidList='') {
		return $this->modelObj->findEventsForWeek($timestamp, $type, $pidList);
	}
	
	function findEventsForMonth($timestamp, $type='', $pidList='') {
		return $this->modelObj->findEventsForMonth($timestamp, $type, $pidList);
	}
	
	function findEventsForYear( $timestamp, $type='', $pidList='') {
		return $this->modelObj->findEventsForYear($timestamp, $type, $pidList);
	}
	
	function findEventsForList($timestamp, $type='', $pidList='') {
		return $this->modelObj->findEventsForList($timestamp, $type, $pidList);
	}
	
	function findCategoriesForList($type='', $pidList='') {
		return $this->modelObj->findCategoriesForList($type, $pidList);
	}
	
	function findEventsForIcs( $timestamp, $type='', $pidList) {
		return $this->modelObj->findEventsForIcs($timestamp,$type, $pidList);
	}
	
	function searchEvents( $type='', $pidList='') {
		return $this->modelObj->searchEvents($type, $pidList);
	}
	
	function searchLocation( $type='', $pidList='') {
		return $this->modelObj->searchLocation($type, $pidList);
	}
	
	function searchOrganizer( $type='', $pidList='') {
		return $this->modelObj->searchOrganizer($type, $pidList);
	}
	
	function drawIcs($master_array, $getdate, $sendHeaders=true) {
		return $this->viewObj->drawIcs($master_array, $getdate, $sendHeaders);
	}
	
	 /*
	* !brief process the Typoscript array to final output
	* @param string The Typoscrypt Object to process
	* @param string The content between the tags to be merged with the TS Objected
	* @return string Processed ooutput of the TS
	* Note: Part of the code is taken from tsobj written by Jean-David Gadina (macmade@gadlab.net)
	*/
	function __processTSObject($tsObjPath, $tag_content) {
		// Check for a non empty value
		if ($tsObjPath) {
			
			// Get complete TS template
			$tsObj = & $this -> __TSTemplate -> setup;
			
			// Get TS object hierarchy in template
			$tmplPath = explode('.',$tsObjPath);
			// Process TS object hierarchy
			for($i = 0; $i < count($tmplPath); $i++) {
			
				// Try to get content type
				$cType = $tsObj[$tmplPath[$i]];
				
				// Try to get TS object configuration array
				$tsNewObj = $tsObj[$tmplPath[$i] . '.'];
				
				// Merge Configuration found in the tags with typoscript config
				if (count($tag_content)) {
					$tsNewObj = $this -> array_merge_recursive2($tsNewObj, $tag_content[$tsObjPath.'.']);
				}
				
				// Check object
				if (!$cType && !$tsNewObj) {
					// Object doesn't exist
					$error = 1;
					break;
				}
			}
			
			// DEBUG ONLY - Show TS object
//			t3lib_div::debug($cType, 'CONTENT TYPE');
//			t3lib_div::debug($tsObj, 'TS CONFIGURATION');
			
			// Check object and content type
			if ($error) {
			
				// Object not found
				return '<strong>Not Found</strong> (' . $tsObjPath . ')';
				
			} elseif ($this->cTypes[$cType]) {
				// Render Object
				$code = $this -> __local_cObj -> cObjGetSingle($cType,$tsNewObj);
		
			} else {
		
				// Invalid content type
				return '<strong>errors.invalid</strong> (' . $cType . ')';
			}


		// Return object
		return $code;
		}
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_api.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_api.php']);
}

?>