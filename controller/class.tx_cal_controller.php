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

require_once (PATH_tslib.'class.tslib_pibase.php');
require_once ('class.tx_cal_modelcontroller.php');
require_once ('class.tx_cal_viewcontroller.php');

/**
 * Main controller for the calendar base.  All requests come through this class
 * and are routed to the model and view layers for processing.  
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_controller extends tslib_pibase {
	
	var $prefixId = 'tx_cal_controller'; // Same as class name
	var $scriptRelPath = 'controller/class.tx_cal_controller.php'; // Path to this script relative to the extension dir.
	var $extKey = 'cal'; // The extension key.
	var $pi_checkCHash = TRUE;
	var $dayStart;
	var $ext_path;
	var $cObj; // The backReference to the mother cObj object set at call time
	var $link_vars;
	var $rightsObj;
	var $modelObj;
	var $viewObj;
	
	var $unix_time;

	/**
	 *  Main controller function that serves as the entry point from TYPO3.
	 *  @param		array		The content array.
	 *	@param		array		The conf array.
	 *	@return		string		HTML-representation of calendar data.	
	 */
	function main($content, $conf) {
	
//debug('Start:'.microtime());
		$this->conf = $conf;

		//Jan 18032006 start		
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
		$piFlexForm = $this->cObj->data['pi_flexform'];

		$this->updateConfWithFlexform($piFlexForm);
		
		$this->pi_setPiVarDefaults(); // Set default piVars from TS

		if ($this->conf['language'])
			$this->LLkey = $this->conf['language'];
		$this->pi_loadLL();

		$this->conf['cache']=1;
		$GLOBALS['TSFE']->page_cache_reg1 = 77;

		$category = $this->convertLinkVarArrayToList($this->piVars['category']);
		unset($this->piVars['category']);
		$this->piVars['category']=$category;

		$calendar = $this->convertLinkVarArrayToList($this->piVars['calendar']);
		unset($this->piVars['calendar']);
		$this->piVars['calendar']=$calendar;
		
		$location = $this->convertLinkVarArrayToList($this->piVars['location_ids']);
		unset($this->piVars['location_ids']);
		$this->piVars['location_ids']=$location;

		if($this->piVars['view'] == $this->piVars['lastview']){
			unset($this->piVars['lastview']);
		}

		if ($this->piVars['getdate'] == '') {
			$this->conf['getdate'] = gmdate('Ymd');
		}else{
			$this->conf['getdate'] = intval($this->piVars['getdate']);
		}
		if($this->conf['getdate'] <= date('Ymd',strtotime($this->conf['view.']['startLinkRange'])) || $this->conf['getdate'] >= date('Ymd',strtotime($this->conf['view.']['endLinkRange']))){
			// Set additional META-Tag for google et al
			$GLOBALS['TSFE']->additionalHeaderData['cal'] = '<meta name="robots" content="index,nofollow" />';
			
			// Set / override no_search for current page-object
			$GLOBALS['TSFE']->page['no_search'] = 0; 
		}
		$this->conf['view'] = strip_tags($this->piVars['view']);
		$this->conf['lastview'] = strip_tags($this->piVars['lastview']);
		$this->conf['uid'] = intval($this->piVars['uid']);
		$this->conf['type'] = strip_tags($this->piVars['type']);
		$this->conf['category'] = $category;
		$this->conf['calendar'] = $calendar;
		$this->conf['monitor'] = strip_tags($this->piVars['monitor']);
		$this->conf['gettime'] = intval($this->piVars['gettime']);
		$this->conf['postview'] = intval($this->piVars['postview']);
		$this->conf['page_id'] = intval($this->piVars['page_id']);
		$this->conf['option'] = strip_tags($this->piVars['option']);
		$this->conf['switch_calendar'] = intval($this->piVars['switch_calendar']);
		$this->conf['location'] = $location;
		$this->conf['view.']['allowedViews'] = array_unique(array_merge($this->conf['view.']['allowedViews'],split(',',$this->conf['view.']['customViews'])));
		if($this->conf['view.']['freeAndBusy.']['enable']){
			$this->conf['option'] = 'freeandbusy';
			if(!$this->conf['calendar']){
				$this->conf['calendar'] = $this->conf['view.']['freeAndBusy.']['defaultCalendarUid'];
			}
		}	

		$this->rightsObj = t3lib_div::makeInstanceService('cal_rights_model', 'rights');
		$this->rightsObj->setController($this);
		
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$this->modelObj = new $tx_cal_modelcontroller ($this);

		$tx_cal_viewcontroller = t3lib_div :: makeInstanceClassName('tx_cal_viewcontroller');
		$this->viewObj = new $tx_cal_viewcontroller ($this);

		
		$this->conf['pidList'] = $this->pi_getPidList($this->conf['pages'], $this->conf['recursive']);

		$unix_time = strtotime($this->conf['getdate']);
		$this->unix_time = $unix_time;

		$this->conf['view'] = $this->rightsObj->checkView($this->conf['view']);
		//if($this->conf['lastview']){
			//$this->conf['lastview'] = $this->rightsObj->checkView($this->conf['lastview']);
		//}
		$return = '';
		$count = 0;		
		while ($return == '' && $count<4) {
			$count++; //Just to make sure we are not getting an endless loop
			
			/* Convert view names (search_event) to function names (searchevent) */
			$viewFunction = str_replace('_', '', $this->conf['view']);
			
			/* @todo  Hack!  List is a reserved name so we have to change the function name. */
			if ($viewFunction == 'list') {
				$viewFunction = 'listView';
			}
			
			if(method_exists($this, $viewFunction)) {
				/* Call appropriate view function */
				$return .= $this->$viewFunction();
			} else {
				$customModel = t3lib_div::makeInstanceService('cal_custom_model', $this->conf['view']);
				if(!is_object($customModel)){
					$return .= $this->conf['view.']['noViewFoundHelpText'].$viewFunction;
				}else{
					$customModel->setController($this);
					$return .= $customModel->start();
				}
			}
		}
		if($this->conf['view']=='ics' || $this->conf['view']=='single_ics'){
			return $return;
		}
		return $this->pi_wrapInBaseClass($return);
	}

	/* 
	 * Sets up a hook in the controller's PHP file with the specified name.
	 * @param	string	The name of the hook.
	 * @return	array	The array of objects implementing this hoook.
	 */
	function getHookObjectsArray($hookName) {
		global $TYPO3_CONF_VARS;
		
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php'][$hookName])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php'][$hookName] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		return $hookObjectsArr;
	}
	
	/*
	 * Executes the specified function for each item in the array of hook objects.
	 * @param	array	The array of hook objects.
	 * @param	string	The name of the function to execute.
	 * @return	none
	 */
	function executeHookObjectsFunction($hookObjectsArray, $function) {
		foreach ($hookObjectsArray as $hookObj) {
			if (method_exists($hookObj, $function)) {
				$hookObj->$function($this);
			}
		}
	}
	
	/*
	 * Clears $this-conf vars related to view and lastview.  Useful when calling save and remove functions.
	 * @return		none
	 */
	function clearConfVars() {
		$viewParams = $this->shortenLastViewAndGetTargetViewParameters(true);
		$this->conf['view'] = $viewParams['view'];
		$this->conf['lastview'] = '';
		$this->conf['view'] = $this->rightsObj->checkView($this->conf['view']);
		$this->conf['uid'] = $viewParams['uid'];
		$this->conf['type'] = $viewParams['type'];
	}

	function saveEvent() {
		$hookObjectsArr = $this->getHookObjectsArray('saveEventClass');
		// Hook: postListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'preSaveEvent');
		
		$pid = $this->conf['rights.']['create.']['event.']['saveEventToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveEvent($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'postSaveEvent');
		
		unset($this->piVars['type']);
		unset($this->conf['type']);
		$this->conf['type'] = '';
		$this->clearConfVars();
	}
	
	function removeEvent() {
		$hookObjectsArr = $this->getHookObjectsArray('removeEventClass');
		// Hook: postRemoveEvent
		$this->executeHookObjectsFunction($hookObjectsArr, 'preRemoveEvent');

		$ok = $this->modelObj->removeEvent($this->piVars['uid'], $this->piVars['type']);

		// Hook: preRemoveEvent
		$this->executeHookObjectsFunction($hookObjectsArr, 'postRemoveEvent');	
		
		$this->clearConfVars();
	}
	
	function createExceptionEvent() {
		$getdate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		$hookObjectsArr = $this->getHookObjectsArray('createExceptionEventClass');
		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateExceptionEventRendering')) {
				$hookObj->preCreateExceptionEventRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateExceptionEvent = $this->viewObj->drawCreateExceptionEvent($getdate, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateExceptionEventRendering')) {
				$hookObj->postCreateExceptionEventRendering($drawnCreateExceptionEvent, $this);
			}
		}

		return $drawnCreateExceptionEvent;
	}
	
	function saveExceptionEvent() {
		$hookObjectsArr = $this->getHookObjectsArray('saveExceptionEventClass');

		// Hook: postListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'preSaveExceptionEvent');

		$pid = $this->conf['rights.']['create.']['exception_event.']['saveExceptionEventToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveExceptionEvent($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'postSaveExceptionEvent');
		
		$this->clearConfVars();
	}
	
	function removeCalendar() {
		/* @todo Should this be removeCalendarClass? */
		$hookObjectsArr = $this->getHookObjectsArray('removeEventClass');
		// Hook: postRemoveCalendar
		$this->executeHookObjectsFunction($hookObjectsArr, 'preRemoveCalendar');

		$ok = $this->modelObj->removeCalendar($this->piVars['uid'], $this->piVars['type']);

		// Hook: preRemoveCalendar
		$this->executeHookObjectsFunction($hookObjectsArr, 'postRemoveCalendar');
		
		$this->clearConfVars();
	}

	function saveLocation() {
		$hookObjectsArr = $this->getHookObjectsArray('saveLocationClass');	

		// Hook: postListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'preSaveLocation');

		$pid = $this->conf['rights.']['create.']['location.']['saveLocationToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveLocation($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'postSaveEvent');
		
		$this->clearConfVars();
	}

	function saveOrganizer() {
		$hookObjectsArr = $this->getHookObjectsArray('saveOrganizerClass');
		// Hook: postListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'preSaveOrganizer');

		$pid = $this->conf['rights.']['create.']['organizer.']['saveOrganizerToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveOrganizer($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		$this->executeHookObjectsFunction($hookObjectsArr, 'postSaveOrganizer');
		
		$this->clearConfVars();
	}
	
	function saveCalendar() {
		$hookObjectsArr = $this->getHookObjectsArray('saveCalendarClass');
		// Hook: postSaveCalendar
		$this->executeHookObjectsFunction($hookObjectsArr, 'preSaveCalendar');

		$pid = $this->conf['rights.']['create.']['calendar.']['saveCalendarToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveCalendar($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preSaveCalendar
		$this->executeHookObjectsFunction($hookObjectsArr, 'postSaveCalendar');
		
		$this->clearConfVars();
	}
	
	function saveCategory() {
		$hookObjectsArr = $this->getHookObjectsArray('saveCategoryClass');

		// Hook: postSaveCategory
		$this->executeHookObjectsFunction($hookObjectsArr, 'preSaveCategory');

		$pid = $this->conf['rights.']['create.']['category.']['saveCategoryToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveCategory($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preSaveCategory
		$this->executeHookObjectsFunction($hookObjectsArr, 'postSaveCategory');
		
		$this->clearConfVars();
	}

	function event() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		$getdate = $this->conf['getdate'];
		
		/* @todo drawEventClass? */
		$hookObjectsArr = $this->getHookObjectsArray('draweventClass');

		$event = $this->modelObj->findEvent($uid, $type, $pidList);

		// Hook: postEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEventRendering')) {
				$hookObj->preEventRendering($event, $this);
			}
		}

		$drawnEvent = $this->viewObj->drawEvent($event, $getdate);

		// Hook: preEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEventRendering')) {
				$hookObj->postEventRendering($drawnEvent, $event, $this);
			}
		}

		return $drawnEvent;
	}

	function day() {
		$unix_time = $this->unix_time;
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		$getdate = $this->conf['getdate'];
		
		/* @todo drawDayClass? */
		$hookObjectsArr = $this->getHookObjectsArray('drawdayClass');

		$master_array = $this->modelObj->findEventsForDay($unix_time, $type, $pidList);

		// Hook: postDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDayRendering')) {
				$hookObj->preDayRendering($master_array, $this);
			}
		}
		$drawnDay = $this->viewObj->drawDay($master_array, $getdate);

		// Hook: preDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDayRendering')) {
				$hookObj->postDayRendering($drawnDay, $master_array, $this);
			}
		}

		return $drawnDay;
	}
	
	function week() {
		$unix_time = $this->unix_time;
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		$getdate = $this->conf['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray('drawweekClass');
	
		$master_array = $this->modelObj->findEventsForWeek($unix_time, $type, $pidList);

		// Hook: postWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preWeekRendering')) {
				$hookObj->preWeekRendering($master_array, $this);
			}
		}

		$drawnWeek = $this->viewObj->drawWeek($master_array, $getdate);

		// Hook: preWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postWeekRendering')) {
				$hookObj->postWeekRendering($drawnWeek, $master_array, $this);
			}
		}

		return $drawnWeek;
	}

	function month() {
		$unix_time = $this->unix_time;
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		$getdate = $this->conf['getdate'];
		
		
		$hookObjectsArr = $this->getHookObjectsArray('drawmonthClass');

		$master_array = $this->modelObj->findEventsForMonth($unix_time, $type, $pidList);

		// Hook: postMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preMonthRendering')) {
				$hookObj->preMonthRendering($master_array, $this);
			}
		}

		$drawnMonth = $this->viewObj->drawMonth($master_array, $getdate);

		// Hook: preMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postMonthRendering')) {
				$hookObj->postMonthRendering($drawnMonth, $master_array, $this);
			}
		}

		return $drawnMonth;
	}

	function year() {
		$unix_time = $this->unix_time;
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		$getdate = $this->conf['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray('drawyearClass');

		$master_array = $this->modelObj->findEventsForYear($unix_time, $type, $pidList);

		// Hook: postYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preYearRendering')) {
				$hookObj->preYearRendering($master_array, $this);
			}
		}

		$drawnYear = $this->viewObj->drawYear($master_array, $getdate);

		// Hook: preYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postYearRendering')) {
				$hookObj->postYearRendering($drawnYear, $master_array, $this);
			}
		}

		return $drawnYear;
	}

	function ics() {
		$unix_time = $this->unix_time;
		$type = $this->conf['type'];
		$getdata = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		
		/* @todo duplicated? drawICSClass? */
		$hookObjectsArr = $this->getHookObjectsArray('drawicsClass');

		$master_array = $this->modelObj->findEventsForIcs($unix_time, $type, $pidList); //$this->conf['pid_list']);

		// Hook: postIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preIcsRendering')) {
				$hookObj->preIcsRendering($master_array, $this);
			}
		}

		$drawnIcs = $this->viewObj->drawIcs($master_array, $this->conf['getdate']);

		// Hook: preIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postIcsRendering')) {
				$hookObj->postIcsRendering($drawnIcs, $master_array, $this);
			}
		}

		return $drawnIcs;
	}

	function singleIcs() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$getdate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];

		/* duplicated?  drawICSClass? */
		$hookObjectsArr = $this->getHookObjectsArray('drawicsClass');

		$master_array = array ($this->modelObj->findEvent($uid, $type, $pidList)); //$this->conf['pid_list']));

		// Hook: postIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preIcsRendering')) {
				$hookObj->preIcsRendering($master_array, $this);
			}
		}

		$drawnIcs = $this->viewObj->drawIcs($master_array, $getdate);

		// Hook: preIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postIcsRendering')) {
				$hookObj->postIcsRendering($drawnIcs, $master_array, $this);
			}
		}

		return $drawnIcs;
	}

	function location() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('drawLocationClass');

		$location = $this->modelObj->findLocation($uid, $type, $pidList);

		// Hook: postLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preLocationRendering')) {
				$hookObj->preLocationRendering($location, $this);
			}
		}

		$drawnLocation = $this->viewObj->drawLocation($location);

		// Hook: preLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postLocationRendering')) {
				$hookObj->postLocationRendering($drawnLocation, $location, $this);
			}
		}

		return $drawnLocation;
	}

	function organizer() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo drawOrganizerClass? */
		$hookObjectsArr = $this->getHookObjectsArray('draworganizerClass');

		$organizer = $this->modelObj->findOrganizer($uid, $type, $pidList);

		// Hook: postOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preOrganizerRendering')) {
				$hookObj->preOrganizerRendering($organizer, $this);
			}
		}
		$drawnOrganizer = $this->viewObj->drawOrganizer($organizer);

		// Hook: preOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postOrganizerRendering')) {
				$hookObj->postOrganizerRendering($drawnOrganizer, $organizer, $this);
			}
		}
		return $drawnOrganizer;
	}

	function listview() {
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo drawListClass? duplicated?*/
		$hookObjectsArr = $this->getHookObjectsArray('drawlistClass');
	
		$list = $this->modelObj->findEventsForList(date('Ymd', strtotime($this->conf['view.']['list.']['starttime'])), $type, $pidList);

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preListRendering')) {
				$hookObj->preListRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawList($list);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postListRendering')) {
				$hookObj->postListRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function icslist() {
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo drawListClass? duplicated? */
		$hookObjectsArr = $this->getHookObjectsArray('drawlistClass');

		$list = $this->modelObj->findCategoriesForList(date('Ymd', strtotime($this->conf['view.']['list.']['starttime'])), $type, $pidList);

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preListRendering')) {
				$hookObj->preListRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawIcsList($list, date('Ymd', strtotime($this->conf['view.']['list.']['starttime'])));

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postListRendering')) {
				$hookObj->postListRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function admin() {
		/* drawAdminClass?  duplicated? */
		$hookObjectsArr = $this->getHookObjectsArray('drawlistClass');

//		$list = $this->modelObj->findCategoriesForList(date('Ymd', strtotime($this->conf['view.']['list.']['starttime'])), $type, $pidList);
//
//		// Hook: postListRendering
//		foreach ($hookObjectsArr as $hookObj) {
//			if (method_exists($hookObj, 'preListRendering')) {
//				$hookObj->preListRendering($list, $this);
//			}
//		}
		$drawnPage = $this->viewObj->drawAdminPage();

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postListRendering')) {
				$hookObj->postListRendering($drawnPage, $this);
			}
		}

		return $drawnPage;
	}
	
	function searchEvent() {
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo drawSearchClass */
		$hookObjectsArr = $this->getHookObjectsArray('drawsearchClass');
	
		$list = $this->modelObj->searchEvents($type, $pidList);

		// Hook: postSearchEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchEventRendering')) {
				$hookObj->preSearchEventRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchEventResult($list);

		// Hook: preSearchEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchEventRendering')) {
				$hookObj->postSearchEventRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}

	function createEvent() {

		$getDate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('createEventClass');
		

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateEventRendering')) {
				$hookObj->preCreateEventRendering($this, $getDate, $pidList);
			}
		}

		$drawnCreateEvent = $this->viewObj->drawCreateEvent($getDate, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateEventRendering')) {
				$hookObj->postCreateEventRendering($drawnCreateEvent, $this);
			}
		}

		return $drawnCreateEvent;
	}

	function confirmEvent() {
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('confirmEventClass');

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmEventRendering')) {
				$hookObj->preConfirmEventRendering($this, $pidList);
			}
		}

		$drawnConfirmEvent = $this->viewObj->drawConfirmEvent($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmEventRendering')) {
				$hookObj->postConfirmEventRendering($drawnConfirmEvent, $this);
			}
		}

		return $drawnConfirmEvent;
	}

	function editEvent() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('editEventClass');

		$event = $this->modelObj->findEvent($uid, $type, $pidList);

		// Hook: postEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditEventRendering')) {
				$hookObj->preEditEventRendering($this, $event, $pidList);
			}
		}

		$drawnEditEvent = $this->viewObj->drawEditEvent($event, $pidList);

		// Hook: preEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditEventRendering')) {
				$hookObj->postEditEventRendering($drawnEditEvent, $this);
			}
		}

		return $drawnEditEvent;
	}
	
	function deleteEvent() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];		
		
		$hookObjectsArr = $this->getHookObjectsArray('deleteEventClass');
		
		$event = $this->modelObj->findEvent($uid, $type, $pidList);

		// Hook: postDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteEventRendering')) {
				$hookObj->preDeleteEventRendering($this, $event, $pidList);
			}
		}

		$drawnDeleteEvent = $this->viewObj->drawDeleteEvent($event, $pidList);

		// Hook: preDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteEventRendering')) {
				$hookObj->postDeleteEventRendering($drawnDeleteEvent, $this);
			}
		}

		return $drawnDeleteEvent;
	}

	function createLocation() {
		$getdate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('createLocationClass');

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateLocationRendering')) {
				$hookObj->preCreateLocationRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateLocation = $this->viewObj->drawCreateLocation($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateLocationRendering')) {
				$hookObj->postCreateLocationRendering($drawnCreateLocation, $this);
			}
		}

		return $drawnCreateLocation;
	}

	function confirmLocation() {
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('confirmLocationClass');

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmLocationRendering')) {
				$hookObj->preConfirmLocationRendering($this, $pidList);
			}
		}

		$drawnConfirmLocation = $this->viewObj->drawConfirmLocation($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmLocationRendering')) {
				$hookObj->postConfirmLocationRendering($drawnConfirmLocation, $this);
			}
		}

		return $drawnConfirmLocation;
	}
	
	function editLocation() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('editLocationClass');

		$location = $this->modelObj->findLocation($uid, $type, $pidList);

		// Hook: postEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditLocationRendering')) {
				$hookObj->preEditLocationRendering($this, $location, $pidList);
			}
		}

		$drawnEditLocation = $this->viewObj->drawEditLocation($location, $pidList);

		// Hook: preEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditLocationRendering')) {
				$hookObj->postEditLocationRendering($drawnEditLocation, $this);
			}
		}

		return $drawnEditLocation;
	}
	
	function deleteLocation() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('deleteLocationClass');

		
		$location = $this->modelObj->findLocation($uid, $type, $pidList);

		// Hook: postDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteLocationRendering')) {
				$hookObj->preDeleteLocationRendering($this, $location, $pidList);
			}
		}

		$drawnDeleteLocation = $this->viewObj->drawDeleteLocation($location, $pidList);

		// Hook: preDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteLocationRendering')) {
				$hookObj->postDeleteLocationRendering($drawnDeleteLocation, $this);
			}
		}

		return $drawnDeleteLocation;
	}

	function createOrganizer() {
		$getdate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('createOrganizerClass');

		// Hook: postCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateOrganizerRendering')) {
				$hookObj->preCreateOrganizerRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateOrganizer = $this->viewObj->drawCreateOrganizer($pidList);

		// Hook: preCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateOrganizerRendering')) {
				$hookObj->postCreateOrganizerRendering($drawnCreateOrganizer, $this);
			}
		}

		return $drawnCreateOrganizer;
	}

	function confirmOrganizer() {
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('confirmOrganizerClass');

		// Hook: postConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmOrganizerRendering')) {
				$hookObj->preConfirmOrganizerRendering($this, $pidList);
			}
		}

		$drawnConfirmOrganizer = $this->viewObj->drawConfirmOrganizer($pidList);

		// Hook: preConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmOrganizerRendering')) {
				$hookObj->postConfirmOrganizerRendering($drawnConfirmOrganizer, $this);
			}
		}

		return $drawnConfirmOrganizer;
	}
	
	function editOrganizer() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('editOrganizerClass');

		$organizer = $this->modelObj->findOrganizer($uid, $type, $pidList);

		// Hook: postEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditOrganizerRendering')) {
				$hookObj->preEditOrganizerRendering($this, $organizer, $pidList);
			}
		}

		$drawnEditOrganizer = $this->viewObj->drawEditOrganizer($organizer, $pidList);

		// Hook: preEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditOrganizerRendering')) {
				$hookObj->postEditOrganizerRendering($drawnEditOrganizer, $this);
			}
		}

		return $drawnEditOrganizer;
	}
	
	function deleteOrganizer() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('deleteOrganizerClass');

		
		$organizer = $this->modelObj->findOrganizer($uid, $type, $pidList);

		// Hook: postDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteOrganizerRendering')) {
				$hookObj->preDeleteOrganizerRendering($this, $organizer, $pidList);
			}
		}

		$drawnDeleteOrganizer = $this->viewObj->drawDeleteOrganizer($organizer, $pidList);

		// Hook: preDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteOrganizerRendering')) {
				$hookObj->postDeleteOrganizerRendering($drawnDeleteOrganizer, $this);
			}
		}

		return $drawnDeleteOrganizer;
	}
	
	function createCalendar() {
		$getdate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('createCalendarClass');


		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateCalendarRendering')) {
				$hookObj->preCreateCalendarRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateCalendar = $this->viewObj->drawCreateCalendar($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateCalendarRendering')) {
				$hookObj->postCreateCalendarRendering($drawnCreateCalendar, $this);
			}
		}

		return $drawnCreateCalendar;
	}

	function confirmCalendar() {
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('confirmCalendarClass');


		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmCalendarRendering')) {
				$hookObj->preConfirmCalendarRendering($this, $pidList);
			}
		}

		$drawnConfirmCalendar = $this->viewObj->drawConfirmCalendar($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmCalendarRendering')) {
				$hookObj->postConfirmCalendarRendering($drawnConfirmCalendar, $this);
			}
		}

		return $drawnConfirmCalendar;
	}
	
	function editCalendar() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('editCalendadrClass');

		$calendar = $this->modelObj->findCalendar($uid, $type, $pidList);

		// Hook: postEditCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditCalendarRendering')) {
				$hookObj->preEditCalendarRendering($this, $calendar, $pidList);
			}
		}

		$drawnEditCalendar = $this->viewObj->drawEditCalendar($calendar, $pidList);

		// Hook: preEditCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditCalendarRendering')) {
				$hookObj->postEditCalendarRendering($drawnEditCalendar, $this);
			}
		}

		return $drawnEditCalendar;
	}
	
	function deleteCalendar() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('deleteCalendarClass');

		
		$calendar = $this->modelObj->findCalendar($uid, $type, $pidList);

		// Hook: postDeleteCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteCalendarRendering')) {
				$hookObj->preDeleteCalendarRendering($this, $calendar, $pidList);
			}
		}
		$drawnDeleteCalendar = $this->viewObj->drawDeleteCalendar($calendar, $pidList);

		// Hook: preDeleteCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteCalendarRendering')) {
				$hookObj->postDeleteCalendarRendering($drawnDeleteCalendar, $this);
			}
		}

		return $drawnDeleteCalendar;
	}
	
	function createCategory() {
		$getdate = $this->conf['getdate'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('createCategoryClass');


		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateCategoryRendering')) {
				$hookObj->preCreateCategoryRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateCategory = $this->viewObj->drawCreateCategory($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateCategoryRendering')) {
				$hookObj->postCreateCategoryRendering($drawnCreateCategory, $this);
			}
		}

		return $drawnCreateCategory;
	}

	function confirmCategory() {
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('confirmCategoryClass');


		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmCategoryRendering')) {
				$hookObj->preConfirmCategoryRendering($this, $pidList);
			}
		}

		$drawnConfirmCategory = $this->viewObj->drawConfirmCategory($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmCategoryRendering')) {
				$hookObj->postConfirmCategoryRendering($drawnConfirmCategory, $this);
			}
		}

		return $drawnConfirmCategory;
	}
	
	function editCategory() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('editCategoryClass');


		$category = $this->modelObj->findCategory($uid, $type, $pidList);

		// Hook: postEditCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditCategoryRendering')) {
				$hookObj->preEditCategoryRendering($this, $category, $pidList);
			}
		}

		$drawnEditCategory = $this->viewObj->drawEditCategory($category, $pidList);

		// Hook: preEditCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditCategoryRendering')) {
				$hookObj->postEditCategoryRendering($drawnEditCategory, $this);
			}
		}

		return $drawnEditCategory;
	}
	
	function deleteCategory() {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray('deleteCategoryClass');

		
		$category = $this->modelObj->findCategory($uid, $type, $pidList);

		// Hook: postDeleteCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteCategoryRendering')) {
				$hookObj->preDeleteCategoryRendering($this, $category, $pidList);
			}
		}
		$drawnDeleteCategory = $this->viewObj->drawDeleteCategory($category, $pidList);

		// Hook: preDeleteCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteCategoryRendering')) {
				$hookObj->postDeleteCategoryRendering($drawnDeleteCategory, $this);
			}
		}

		return $drawnDeleteCategory;
	}

	function searchAll() {
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo drawSearchClass */
		$hookObjectsArr = $this->getHookObjectsArray('drawsearchClass');

		$list = array();
		$list['phpicalendar_event'] = $this->modelObj->searchEvents($type, $pidList);
		$list['location'] = $this->modelObj->searchLocation($type, $pidList);
		$list['organizer'] = $this->modelObj->searchOrganizer($type, $pidList);

		// Hook: postSearchAllRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchAllRendering')) {
				$hookObj->preSearchAllRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchAllResult($list);

		// Hook: preSearchAllRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchAllRendering')) {
				$hookObj->postSearchAllRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function searchLocation() {
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo duplicated? */
		$hookObjectsArr = $this->getHookObjectsArray('drawsearchClass');

		$list = $this->modelObj->searchLocation($type, $pidList);

		// Hook: postSearchLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchLocationRendering')) {
				$hookObj->preSearchLocationRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchLocationResult($list);

		// Hook: preSearchLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchLocationRendering')) {
				$hookObj->postSearchLocationRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function searchOrganizer() {
		$type = $this->conf['type'];
		$pidList = $this->conf['pidList'];
		
		/* @todo duplicated? */
		$hookObjectsArr = $this->getHookObjectsArray('drawsearchClass');

 		$list = $this->modelObj->searchOrganizer($this->cObj, $type, $pidList);

		// Hook: postSearchOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchOrganizerRendering')) {
				$hookObj->preSearchOrganizerRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchOrganizerResult($list);

		// Hook: preSearchOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchOrganizerRendering')) {
				$hookObj->postSearchOrganizerRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function updateConfWithFlexform(&$piFlexForm){
		$this->updateIfNotEmpty($this->conf['pages'], $this->pi_getFFvalue($piFlexForm, 'pages'));
		$this->updateIfNotEmpty($this->conf['recursive'], $this->pi_getFFvalue($piFlexForm, 'recursive'));
		$this->updateIfNotEmpty($this->conf['calendarName'], $this->pi_getFFvalue($piFlexForm, 'calendarName'));
		$this->updateIfNotEmpty($this->conf['allowSubscribe'] , $this->pi_getFFvalue($piFlexForm, 'allowSubscribe'));
		$this->updateIfNotEmpty($this->conf['subscribeFeUser'] , $this->pi_getFFvalue($piFlexForm, 'subscribeFeUser'));
		$this->updateIfNotEmpty($this->conf['subscribeWithCaptcha'] , $this->pi_getFFvalue($piFlexForm, 'subscribeWithCaptcha'));
		$this->updateIfNotEmpty($this->conf['view.']['allowedViews'] , $this->pi_getFFvalue($piFlexForm, 'allowedViews'));

		$this->conf['view.']['allowedViews'] = array_unique(split(',',str_replace('~',',',$this->conf['view.']['allowedViews'])));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayViewPid'] , $this->pi_getFFvalue($piFlexForm, 'dayViewPid','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayStart'] , $this->pi_getFFvalue($piFlexForm, 'dayStart','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayEnd'] , $this->pi_getFFvalue($piFlexForm, 'dayEnd','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['gridLength'] , $this->pi_getFFvalue($piFlexForm, 'gridLength','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['week.']['weekViewPid'] , $this->pi_getFFvalue($piFlexForm, 'weekViewPid','s_Week_View'));
		$this->updateIfNotEmpty($this->conf['view.']['weekStartDay'] , $this->pi_getFFvalue($piFlexForm, 'weekStartDay'));
	
		$this->updateIfNotEmpty($this->conf['view.']['month.']['monthViewPid'] , $this->pi_getFFvalue($piFlexForm, 'monthViewPid','s_Month_View'));
		$this->updateIfNotEmpty($this->conf['view.']['year.']['yearViewPid'] , $this->pi_getFFvalue($piFlexForm, 'yearViewPid','s_Year_View'));
		$this->updateIfNotEmpty($this->conf['view.']['event.']['eventViewPid'] , $this->pi_getFFvalue($piFlexForm, 'eventViewPid','s_Event_View'));
		$this->updateIfNotEmpty($this->conf['view.']['event.']['isPreview'] , $this->pi_getFFvalue($piFlexForm, 'isPreview','s_Event_View'));
		$this->updateIfNotEmpty($this->conf['view.']['list.']['starttime'] , $this->pi_getFFvalue($piFlexForm, 'starttime','s_List_View'));
		$this->updateIfNotEmpty($this->conf['view.']['list.']['endtime'] , $this->pi_getFFvalue($piFlexForm, 'endtime','s_List_View'));
		$this->updateIfNotEmpty($this->conf['view.']['list.']['maxEvents'] , $this->pi_getFFvalue($piFlexForm, 'maxEvents','s_List_View'));
		$this->updateIfNotEmpty($this->conf['view.']['ics.']['showIcsLinks'] , $this->pi_getFFvalue($piFlexForm, 'showIcsLinks','s_Ics_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showLogin'] , $this->pi_getFFvalue($piFlexForm, 'showLogin','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showSearch'] , $this->pi_getFFvalue($piFlexForm, 'showSearch','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showJumps'] , $this->pi_getFFvalue($piFlexForm, 'showJumps','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showGoto'] , $this->pi_getFFvalue($piFlexForm, 'showGoto','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showCalendarSelection'] , $this->pi_getFFvalue($piFlexForm, 'showCalendarSelection','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showCategorySelection'] , $this->pi_getFFvalue($piFlexForm, 'showCategorySelection','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showTomorrowEvents'] , $this->pi_getFFvalue($piFlexForm, 'showTomorrowEvents','s_Other_View'));
	}

	function updateIfNotEmpty(&$confVar, $newConfVar){
		if($newConfVar!=''){
			$confVar = $newConfVar;
		}
	}
	
	function convertLinkVarArrayToList($linkVar){
		if(is_array($linkVar)){
			$first = true;
			foreach($linkVar as $key => $value){
				if($first){
					if($value=='on'){
						$value = intval($key);
					}
					$new .= $value;
					$first = false;
				}else{
					if($value=='on'){
						$value = intval($key);
					}
					$new .= ','.$value;
				} 
			}
			return $new;
		}else{
			return strip_tags($linkVar);
		}	
	}
	
	function replace_tags($tags = array(), $page) 
	{
		if (sizeof($tags) > 0) 
		{
			$sims = array();
			foreach ($tags as $tag => $data) 
			{	
				// This replaces any tags
				$sims['###' . strtoupper($tag) . '###'] = $this->cObj->substituteMarkerArrayCached($data,'###' . strtoupper($tag) . '###', array(),array());			
			}

			$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array());

		}
		else
		{
			//die('No tags designated for replacement.');
		}
		return $page;
		
	}
	
	function shortenLastViewAndGetTargetViewParameters($takeFirstInsteadOfLast=false){
		$returnParams = array();
		if(count($this->conf['view.']['allowedViews'])==1){
			$returnParams['lastview'] = null;
			$returnParams['view'] = $this->conf['view.']['allowedViews'][0];
			
		}else{
			$views = explode('|',$this->conf['lastview']);
			if($takeFirstInsteadOfLast){
				$target = array_shift($views);
				$views = array();
			}else{
				$target = array_pop($views);
			}
			$lastview = t3lib_div::csvValues($views,$delim='|',$quote='');
			$viewParams = explode('-',$target);
			$returnParams['view'] = $viewParams[0];
			$returnParams['lastview'] = $lastview;
			switch($viewParams[0]){
				case 'event':
					if(count($viewParams>=3)){
						$returnParams['uid']=$viewParams[1];
						$returnParams['type']=$viewParams[2];
					}
					break;
				default:
					$returnParams['uid']=null;
					$returnParams['type']=null;
					$returnParams['gettime']=null;
					$returnParams['getdate']=$this->conf['getdate'];
					break;
			}
		}
		return $returnParams;
	}
	
	function extendLastView(){
		if(count($this->conf['view.']['allowedViews'])==1){
			$lastview = null;
			$view = $this->conf['view.']['allowedViews'][0];
			return null;
		}
		$views = explode('|',$this->conf['lastview']);
		if(in_array($this->conf['view'],$views)){
			return $this->conf['view'];
		}

		$params = array($this->conf['view']);
		switch($this->conf['view']){
			case 'event':
				$params[]=$this->conf['uid'];
				$params[]=$this->conf['type'];
				break;
			default:
				break;
		}
		return ($this->conf['lastview']!=null?$this->conf['lastview'].'|':'').t3lib_div::csvValues($params,$delim='-',$quote='');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']);
}
?>