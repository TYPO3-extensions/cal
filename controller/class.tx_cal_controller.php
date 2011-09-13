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
require_once ('class.tx_cal_rightscontroller.php');

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

	/**
	 *  Main controller function that serves as the entry point from TYPO3.
	 *  @param		array		The content array.
	 *	@param		array		The conf array.
	 *	@return		string		HTML-representation of calendar data.	
	 */
	function main($content, $conf) {
	
//debug("Start:".microtime());
		$this->conf = $conf;

		//Jan 18032006 start		
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
		$piFlexForm = $this->cObj->data['pi_flexform'];

		$this->updateConfWithFlexform($piFlexForm);
		
		$this->pi_setPiVarDefaults(); // Set default piVars from TS

		if ($this->conf["language"])
			$this->LLkey = $this->conf["language"];
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
			$this->conf['getdate'] = date('Ymd');
		}else{
			$this->conf['getdate'] = intval($this->piVars['getdate']);
		}
		$this->conf['view'] = strip_tags($this->piVars['view']);
		$this->conf['lastview'] = strip_tags($this->piVars['lastview']);
		$this->conf['uid'] = intval($this->piVars['uid']);
		$this->conf['type'] = strip_tags($this->piVars['type']);
		$this->conf['category'] = $category;
		$this->conf['calendar'] = $calendar;
		$this->conf['monitor'] = strip_tags($this->piVars['monitor']);
		$this->conf['gettime'] = intval($this->piVars['gettime']);
		$this->conf['preview'] = intval($this->piVars['preview']);
		$this->conf['page_id'] = intval($this->piVars['page_id']);
		$this->conf['option'] = strip_tags($this->piVars['option']);
		$this->conf['switch_calendar'] = intval($this->piVars['switch_calendar']);
		$this->conf['location'] = $location;
		$this->conf["view."]['allowedViews'] = array_unique(array_merge($this->conf["view."]['allowedViews'],split(',',$this->conf["view."]['customViews'])));
		if($this->conf['view.']['freeAndBusy.']['enable']){
			$this->conf['option'] = "freeandbusy";
			if(!$this->conf['calendar']){
				$this->conf['calendar'] = $this->conf['view.']['freeAndBusy.']['defaultCalendarUid'];
			}
		}	

		$tx_cal_rightscontroller = t3lib_div :: makeInstanceClassName('tx_cal_rightscontroller');
		$this->rightsObj = new $tx_cal_rightscontroller ($this);
		
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$this->modelObj = new $tx_cal_modelcontroller ($this);

		$tx_cal_viewcontroller = t3lib_div :: makeInstanceClassName('tx_cal_viewcontroller');
		$this->viewObj = new $tx_cal_viewcontroller ($this);

		
		$this->conf['pidList'] = $this->pi_getPidList($this->conf['pages'], $this->conf["recursive"]);

//debug($view);
		$unix_time = strtotime($this->conf['getdate']);
		if ($this->conf['view'] == 'day' || $this->conf['view'] == 'week' || $this->conf['view'] == 'month' || $this->conf['view'] == 'year' || $this->conf['view'] == 'event' || $this->conf['view'] == 'location' || $this->conf['view'] == 'organizer' || $this->conf['view'] == 'list' || $this->conf['view'] == 'icslist' || $this->conf['view'] == 'ics' || $this->conf['view'] == 'single_ics' || $this->conf['view'] == 'search_all' || $this->conf['view'] == 'search_event' || $this->conf['view'] == 'search_location' || $this->conf['view'] == 'search_organizer') {
			// catch all allowed standard view types
		} else
		if ($this->conf['view'] == 'admin' && $this->rightsObj->isCalAdmin()){
		} else
		if (($this->conf['view'] == 'save_calendar' || $this->conf['view'] == 'edit_calendar' || $this->conf['view'] == 'confirm_calendar' || $this->conf['view'] == 'delete_calendar' || $this->conf['view'] == 'remove_calendar' || $this->conf['view'] == 'create_calendar') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateCalendar() 
					|| $this->rightsObj->isAllowedToEditCalendar()
					|| $this->rightsObj->isAllowedToDeleteCalendar())) {
		} else
		if (($this->conf['view'] == 'save_category' || $this->conf['view'] == 'edit_category' || $this->conf['view'] == 'confirm_category' || $this->conf['view'] == 'delete_category' || $this->conf['view'] == 'remove_category' || $this->conf['view'] == 'create_category') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateCalendar() 
					|| $this->rightsObj->isAllowedToEditCalendar()
					|| $this->rightsObj->isAllowedToDeleteCalendar())) {
		} else
		if (($this->conf['view'] == 'save_event' || $this->conf['view'] == 'edit_event' || $this->conf['view'] == 'confirm_event' || $this->conf['view'] == 'delete_event' || $this->conf['view'] == 'remove_event' || $this->conf['view'] == 'create_event') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateEvents() 
					|| $this->rightsObj->isAllowedToEditEvents()
					|| $this->rightsObj->isAllowedToDeleteEvents())) {
		} else
		if (($this->conf['view'] == 'save_exception_event' || $this->conf['view'] == 'edit_exception_event' || $this->conf['view'] == 'confirm_exception_event' || $this->conf['view'] == 'delete_exception_event' || $this->conf['view'] == 'remove_exception_event' || $this->conf['view'] == 'create_exception_event') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateExceptionEvents() 
					|| $this->rightsObj->isAllowedToEditExceptionEvents()
					|| $this->rightsObj->isAllowedToDeleteExceptionEvents())) {
		} else
		if (($this->conf['view'] == 'save_location' || $this->conf['view'] == 'confirm_location' || $this->conf['view'] == 'create_location' || $this->conf['view'] == 'edit_location')
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateLocations()
					|| $this->rightsObj->isAllowedToEditLocations()
					|| $this->rightsObj->isAllowedToDeleteLocations())) {
		// catch create_location view type and check all conditions
		} else
		if (($this->conf['view'] == 'save_organizer' || $this->conf['view'] == 'confirm_organizer' || $this->conf['view'] == 'create_organizer' || $this->conf['view'] == 'edit_organizer') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateOrganizer()
					|| $this->rightsObj->isAllowedToOrganizer()
					|| $this->rightsObj->isAllowedToDeleteOrganizer())) {
		// catch create_organizer view type and check all conditions
		} else {
			// a not wanted view type -> convert it
			$this->conf['view'] = $this->conf["view."]['allowedViews'][0];
			if ($this->conf['view'] == '') {
				$this->conf['view'] = 'month';
			}
			$this->conf['type'] = "";
			$this->piVars['type']=null;
		}

		if(count($this->conf["view."]['allowedViews'])==1){
			$this->conf['view'] = $this->conf["view."]['allowedViews'][0];
			if($this->conf["view."]['allowedViews'][0]!="event"){
				$this->conf['uid'] = "";
				$this->piVars['uid']=null;
				$this->conf['type'] = "";
				$this->piVars['type']=null;
			}else if($this->conf["view."]['allowedViews'][0]=="event" && (($this->piVars['view']=='location' && !in_array('location', $this->conf["view."]['allowedViews'])) || ($this->piVars['view']=='organizer' && !in_array('organizer', $this->conf["view."]['allowedViews'])))){
				return;
			}
		}else if(!($this->conf['view'] == 'admin'&& $this->rightsObj->isCalAdmin()) && !in_array($this->conf['view'], $this->conf["view."]['allowedViews'])){
			$this->conf['view'] = $this->conf["view."]['allowedViews'][0];
		}
		$return = "";
		$count = 0;
//debug($this->conf['view']);
		while ($return == "" && $count<4) {
			$count++; //Just to make sure we are not getting an endless loop

			switch ($this->conf['view']) {
				case 'create_event' :
					$return = $this->createEvent($this->conf['getdate'], $this->conf['pidList']);
					break;
				case 'edit_event' :
					$return = $this->editEvent($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'confirm_event' :
					$return = $this->confirmEvent($this->conf['pidList']);
					break;
				case 'delete_event' :
					$return = $this->deleteEvent($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'save_event' :
					$this->saveEvent();
					unset($this->piVars['type']);
					unset($this->conf['type']);
					$this->conf['type'] = "";
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'remove_event' :
					$this->removeEvent();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
//				case 'create_exception_event' :
//					$return = $this->createExceptionEvent($this->conf['getdate'], $this->conf['pidList']);
//					break;
//				case 'edit_exception_event' :
//					$return = $this->editExceptionEvent($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
//					break;
//				case 'confirm_exception_event' :
//					$return = $this->confirmExceptionEvent($this->conf['pidList']);
//					break;
//				case 'delete_exception_event' :
//					$return = $this->deleteExceptionEvent($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
//					break;
				case 'save_exception_event' :
					$this->saveExceptionEvent();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
//				case 'remove_exception_event' :
//					$this->removeExceptionEvent();
//					$this->conf['view'] = $this->conf['lastview'];
//					$this->conf['lastview'] = "";
//					$this->conf['view'] = $this->conf['view'];
//					break;
				case 'create_location' :
					$return = $this->createLocation($this->conf['getdate'], $this->conf['pidList']);
					break;
				case 'edit_location' :
					$return = $this->editLocation($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'confirm_location' :
					$return = $this->confirmLocation($this->conf['pidList']);
					break;
				case 'save_location' :
					$this->saveLocation();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'create_organizer' :
					$return = $this->createOrganizer($this->conf['getdate'], $this->conf['pidList']);
					break;
				case 'confirm_organizer' :
					$return = $this->confirmOrganizer($this->conf['pidList']);
					break;
				case 'edit_organizer' :
					$return = $this->editOrganizer($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'save_organizer' :
					$this->saveOrganizer();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'create_calendar' :
					$return = $this->createCalendar($this->conf['getdate'], $this->conf['pidList']);
					break;
				case 'edit_calendar' :
					$return = $this->editCalendar($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'confirm_calendar' :
					$return = $this->confirmCalendar($this->conf['pidList']);
					break;
				case 'save_calendar' :
					$this->saveCalendar();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'delete_calendar' :
					$return = $this->deleteCalendar($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'remove_calendar' :
					$this->removeCalendar();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'create_category' :
					$return = $this->createCategory($this->conf['getdate'], $this->conf['pidList']);
					break;
				case 'edit_category' :
					$return = $this->editCategory($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'confirm_category' :
					$return = $this->confirmCategory($this->conf['pidList']);
					break;
				case 'save_category' :
					$this->saveCategory();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'delete_category' :
					$return = $this->deleteCategory($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'remove_category' :
					$this->removeCategory();
					$this->conf['view'] = $this->conf['lastview'];
					$this->conf['lastview'] = "";
					$this->conf['view'] = $this->conf['view'];
					break;
				case 'event' :
					$return = $this->event($this->conf['uid'], $this->conf['type'], $this->conf['pidList'], $this->conf['getdate']);
					break;
				case 'day' :
					$return = $this->day($unix_time, $this->conf['type'], $this->conf['pidList'], $this->conf['getdate']);
					break;
				case 'week' :
					$return = $this->week($unix_time, $this->conf['type'], $this->conf['pidList'], $this->conf['getdate']);
					break;
				case 'month' :
					$return = $this->month($unix_time, $this->conf['type'], $this->conf['pidList'], $this->conf['getdate']);
					break;
				case 'year' :
					$return = $this->year($unix_time, $this->conf['type'], $this->conf['pidList'], $this->conf['getdate']);
					break;
				case 'ics' :
					return $this->ics($unix_time, $this->conf['type'], $this->conf['getdate'], $this->conf['pidList']);
				case 'single_ics' :
					return $this->singleIcs($this->conf['uid'], $this->conf['type'], $this->conf['getdate'], $this->conf['pidList']);
				case 'location' :
					$return = $this->location($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'organizer' :
					$return = $this->organizer($this->conf['uid'], $this->conf['type'], $this->conf['pidList']);
					break;
				case 'list' :
					$return = $this->listview($this->conf['type'], $this->conf['pidList']);
					break;
				case 'icslist' :
					$return = $this->icslistview($this->conf['type'], $this->conf['pidList']);
					break;
				case 'search_all' :
					$return = $this->searchAllView($this->conf['type'], $this->conf['pidList']);
					break;
				case 'search_event' :
					$return = $this->searchEventView($this->conf['type'], $this->conf['pidList']);
					break;
				case 'search_organizer' :
					$return = $this->searchOrganizerView($this->conf['type'], $this->conf['pidList']);
					break;
				case 'search_location' :
					$return = $this->searchLocationView($this->conf['type'], $this->conf['pidList']);
					break;
				case 'admin' :
					$return = $this->adminview();
					break;
			}
		}
//debug("Done:".microtime());
		return $this->pi_wrapInBaseClass($return);
	}

	function saveEvent() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveEvent')) {
				$hookObj->postSaveEvent($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["event."]['saveEventToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveEvent($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveEvent')) {
				$hookObj->preSaveEvent($this);
			}
		}
	}
	
	function removeEvent() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['removeEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['removeEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postRemoveEvent
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postRemoveEvent')) {
				$hookObj->postRemoveEvent($this);
			}
		}
		$ok = $this->modelObj->removeEvent($this->piVars['uid'], $this->piVars['type']);

		// Hook: preRemoveEvent
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preRemoveEvent')) {
				$hookObj->preRemoveEvent($this);
			}
		}
	}
	
	function createExceptionEvent($getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createExceptionEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createExceptionEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateExceptionEventRendering')) {
				$hookObj->postCreateExceptionEventRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateExceptionEvent = $this->viewObj->drawCreateExceptionEvent($getdate, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateExceptionEventRendering')) {
				$hookObj->preCreateExceptionEventRendering($drawnCreateExceptionEvent, $this);
			}
		}

		return $drawnCreateExceptionEvent;
	}
	
	function saveExceptionEvent() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveExceptionEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveExceptionEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveExceptionEvent')) {
				$hookObj->postSaveExceptionEvent($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["exception_event."]['saveExceptionEventToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveExceptionEvent($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveExceptionEvent')) {
				$hookObj->preSaveExceptionEvent($this);
			}
		}
	}
	
	function removeCalendar() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['removeEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['removeEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postRemoveCalendar
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postRemoveCalendar')) {
				$hookObj->postRemoveEvent($this);
			}
		}
		$ok = $this->modelObj->removeCalendar($this->piVars['uid'], $this->piVars['type']);

		// Hook: preRemoveCalendar
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preRemoveCalendar')) {
				$hookObj->preRemoveCalendar($this);
			}
		}
	}

	function saveLocation() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveLocation')) {
				$hookObj->postSaveLocation($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["location."]['saveLocationToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveLocation($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveLocation')) {
				$hookObj->preSaveLocation($this);
			}
		}
	}

	function saveOrganizer() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveOrganizer')) {
				$hookObj->postSaveOrganizer($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["organizer."]['saveOrganizerToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveOrganizer($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveOrganizer')) {
				$hookObj->preSaveOrganizer($this);
			}
		}
	}
	
	function saveCalendar() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveCalendarClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveCalendarClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postSaveCalendar
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveCalendar')) {
				$hookObj->postSaveCalendar($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["calendar."]['saveCalendarToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveCalendar($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preSaveCalendar
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveCalendar')) {
				$hookObj->preSaveCalendar($this);
			}
		}
	}
	
	function saveCategory() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveCategoryClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['saveCategoryClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postSaveCategory
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveCategory')) {
				$hookObj->postSaveCategory($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["category."]['saveCategoryToPid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveCategory($this->piVars['uid'], $this->piVars['type'], $pid);

		// Hook: preSaveCategory
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveCategory')) {
				$hookObj->preSaveCategory($this);
			}
		}
	}

	function event($uid, $type, $pidList, $getdate) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['draweventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['draweventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$event = $this->modelObj->findEvent($uid, $type, $pidList);

		// Hook: postEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEventRendering')) {
				$hookObj->postEventRendering($event, $this);
			}
		}

		$drawnEvent = $this->viewObj->drawEvent($event, $getdate);

		// Hook: preEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEventRendering')) {
				$hookObj->preEventRendering($drawnEvent, $event, $this);
			}
		}

		return $drawnEvent;
	}

	function day($unix_time, $type, $pidList, $getdate) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawdayClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawdayClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = $this->modelObj->findEventsForDay($unix_time, $type, $pidList);

		// Hook: postDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDayRendering')) {
				$hookObj->postDayRendering($master_array, $this);
			}
		}
		$drawnDay = $this->viewObj->drawDay($master_array, $getdate);

		// Hook: preDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDayRendering')) {
				$hookObj->preDayRendering($drawnDay, $master_array, $this);
			}
		}

		return $drawnDay;
	}

	function week($unix_time, $type, $pidList, $getdate) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawweekClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawweekClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = $this->modelObj->findEventsForWeek($unix_time, $type, $pidList);

		// Hook: postWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postWeekRendering')) {
				$hookObj->postWeekRendering($master_array, $this);
			}
		}

		$drawnWeek = $this->viewObj->drawWeek($master_array, $getdate);

		// Hook: preWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preWeekRendering')) {
				$hookObj->preWeekRendering($drawnWeek, $master_array, $this);
			}
		}

		return $drawnWeek;
	}

	function month($unix_time, $type, $pidList, $getdate) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawmonthClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawmonthClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$master_array = $this->modelObj->findEventsForMonth($unix_time, $type, $pidList);

		// Hook: postMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postMonthRendering')) {
				$hookObj->postMonthRendering($master_array, $this);
			}
		}

		$drawnMonth = $this->viewObj->drawMonth($master_array, $getdate);

		// Hook: preMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preMonthRendering')) {
				$hookObj->preMonthRendering($drawnMonth, $master_array, $this);
			}
		}

		return $drawnMonth;
	}

	function year($unix_time, $type, $pidList, $getdate) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawyearClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawyearClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$master_array = $this->modelObj->findEventsForYear($unix_time, $type, $pidList);

		// Hook: postYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postYearRendering')) {
				$hookObj->postYearRendering($master_array, $this);
			}
		}

		$drawnYear = $this->viewObj->drawYear($master_array, $getdate);

		// Hook: preYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preYearRendering')) {
				$hookObj->preYearRendering($drawnYear, $master_array, $this);
			}
		}

		return $drawnYear;
	}

	function ics($unix_time, $type, $getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = $this->modelObj->findEventsForIcs($unix_time, $type, $pidList); //$this->conf['pid_list']);

		// Hook: postIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postIcsRendering')) {
				$hookObj->postIcsRendering($master_array, $this);
			}
		}

		$drawnIcs = $this->viewObj->drawIcs($master_array, $getdate);

		// Hook: preIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preIcsRendering')) {
				$hookObj->preIcsRendering($drawnIcs, $master_array, $this);
			}
		}

		return $drawnIcs;
	}

	function singleIcs($uid, $type, $getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = array ($this->modelObj->findEvent($uid, $type, $pidList)); //$this->conf['pid_list']));

		// Hook: postIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postIcsRendering')) {
				$hookObj->postIcsRendering($master_array, $this);
			}
		}

		$drawnIcs = $this->viewObj->drawIcs($master_array, $getdate);

		// Hook: preIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preIcsRendering')) {
				$hookObj->preIcsRendering($drawnIcs, $master_array, $this);
			}
		}

		return $drawnIcs;
	}

	function location($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$location = $this->modelObj->findLocation($uid, $type, $pidList);

		// Hook: postLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postLocationRendering')) {
				$hookObj->postLocationRendering($location, $this);
			}
		}

		$drawnLocation = $this->viewObj->drawLocation($location);

		// Hook: preLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preLocationRendering')) {
				$hookObj->preLocationRendering($drawnLocation, $location, $this);
			}
		}

		return $drawnLocation;
	}

	function organizer($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['draworganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['draworganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$organizer = $this->modelObj->findOrganizer($uid, $type, $pidList);

		// Hook: postOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postOrganizerRendering')) {
				$hookObj->postOrganizerRendering($organizer, $this);
			}
		}
		$drawnOrganizer = $this->viewObj->drawOrganizer($organizer);

		// Hook: preOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preOrganizerRendering')) {
				$hookObj->preOrganizerRendering($drawnOrganizer, $organizer, $this);
			}
		}
		return $drawnOrganizer;
	}

	function listview($type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = $this->modelObj->findEventsForList(date("Ymd", strtotime($this->conf["view."]["list."]['starttime'])), $type, $pidList);

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postListRendering')) {
				$hookObj->postListRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawList($list);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preListRendering')) {
				$hookObj->preListRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function icslistview($type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = $this->modelObj->findCategoriesForList(date("Ymd", strtotime($this->conf["view."]["list."]['starttime'])), $type, $pidList);

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postListRendering')) {
				$hookObj->postListRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawIcsList($list, date("Ymd", strtotime($this->conf["view."]["list."]['starttime'])));

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preListRendering')) {
				$hookObj->preListRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function adminview() {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
//		$list = $this->modelObj->findCategoriesForList(date("Ymd", strtotime($this->conf["view."]["list."]['starttime'])), $type, $pidList);
//
//		// Hook: postListRendering
//		foreach ($hookObjectsArr as $hookObj) {
//			if (method_exists($hookObj, 'postListRendering')) {
//				$hookObj->postListRendering($list, $this);
//			}
//		}
		$drawnPage = $this->viewObj->drawAdminPage();

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preListRendering')) {
				$hookObj->preListRendering($drawnPage, $this);
			}
		}

		return $drawnPage;
	}
	
	function searchEventView($type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = $this->modelObj->searchEvents($type, $pidList);

		// Hook: postSearchEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchEventRendering')) {
				$hookObj->postSearchEventRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchEventResult($list);

		// Hook: preSearchEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchEventRendering')) {
				$hookObj->preSearchEventRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}

	function createEvent($getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateEventRendering')) {
				$hookObj->postCreateEventRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateEvent = $this->viewObj->drawCreateEvent($getdate, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateEventRendering')) {
				$hookObj->preCreateEventRendering($drawnCreateEvent, $this);
			}
		}

		return $drawnCreateEvent;
	}

	function confirmEvent($pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmEventRendering')) {
				$hookObj->postConfirmEventRendering($this, $pidList);
			}
		}

		$drawnConfirmEvent = $this->viewObj->drawConfirmEvent($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmEventRendering')) {
				$hookObj->preConfirmEventRendering($drawnConfirmEvent, $this);
			}
		}

		return $drawnConfirmEvent;
	}

	function editEvent($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$event = $this->modelObj->findEvent($uid, $type, $pidList);

		// Hook: postEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditEventRendering')) {
				$hookObj->postEditEventRendering($this, $event, $pidList);
			}
		}

		$drawnEditEvent = $this->viewObj->drawEditEvent($event, $pidList);

		// Hook: preEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditEventRendering')) {
				$hookObj->preEditEventRendering($drawnEditEvent, $this);
			}
		}

		return $drawnEditEvent;
	}
	
	function deleteEvent($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$event = $this->modelObj->findEvent($uid, $type, $pidList);

		// Hook: postDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteEventRendering')) {
				$hookObj->postDeleteEventRendering($this, $event, $pidList);
			}
		}

		$drawnDeleteEvent = $this->viewObj->drawDeleteEvent($event, $pidList);

		// Hook: preDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteEventRendering')) {
				$hookObj->preDeleteEventRendering($drawnDeleteEvent, $this);
			}
		}

		return $drawnDeleteEvent;
	}

	function createLocation($getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateLocationRendering')) {
				$hookObj->postCreateLocationRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateLocation = $this->viewObj->drawCreateLocation($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateLocationRendering')) {
				$hookObj->preCreateLocationRendering($drawnCreateLocation, $this);
			}
		}

		return $drawnCreateLocation;
	}

	function confirmLocation($pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmLocationRendering')) {
				$hookObj->postConfirmLocationRendering($this, $pidList);
			}
		}

		$drawnConfirmLocation = $this->viewObj->drawConfirmLocation($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmLocationRendering')) {
				$hookObj->preConfirmLocationRendering($drawnConfirmLocation, $this);
			}
		}

		return $drawnConfirmLocation;
	}
	
	function editLocation($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$location = $this->modelObj->findLocation($uid, $type, $pidList);

		// Hook: postEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditLocationRendering')) {
				$hookObj->postEditLocationRendering($this, $location, $pidList);
			}
		}

		$drawnEditLocation = $this->viewObj->drawEditLocation($location, $pidList);

		// Hook: preEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditLocationRendering')) {
				$hookObj->preEditLocationRendering($drawnEditLocation, $this);
			}
		}

		return $drawnEditLocation;
	}
	
	function deleteLocation($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$location = $this->modelObj->findLocation($uid, $type, $pidList);

		// Hook: postDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteLocationRendering')) {
				$hookObj->postDeleteLocationRendering($this, $location, $pidList);
			}
		}

		$drawnDeleteLocation = $this->viewObj->drawDeleteLocation($location, $pidList);

		// Hook: preDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteLocationRendering')) {
				$hookObj->preDeleteLocationRendering($drawnDeleteLocation, $this);
			}
		}

		return $drawnDeleteLocation;
	}

	function createOrganizer($getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateOrganizerRendering')) {
				$hookObj->postCreateOrganizerRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateOrganizer = $this->viewObj->drawCreateOrganizer($pidList);

		// Hook: preCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateOrganizerRendering')) {
				$hookObj->preCreateOrganizerRendering($drawnCreateOrganizer, $this);
			}
		}

		return $drawnCreateOrganizer;
	}

	function confirmOrganizer($pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmOrganizerRendering')) {
				$hookObj->postConfirmOrganizerRendering($this, $pidList);
			}
		}

		$drawnConfirmOrganizer = $this->viewObj->drawConfirmOrganizer($pidList);

		// Hook: preConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmOrganizerRendering')) {
				$hookObj->preConfirmOrganizerRendering($drawnConfirmOrganizer, $this);
			}
		}

		return $drawnConfirmOrganizer;
	}
	
	function editOrganizer($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$organizer = $this->modelObj->findOrganizer($uid, $type, $pidList);

		// Hook: postEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditOrganizerRendering')) {
				$hookObj->postEditOrganizerRendering($this, $organizer, $pidList);
			}
		}

		$drawnEditOrganizer = $this->viewObj->drawEditOrganizer($organizer, $pidList);

		// Hook: preEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditOrganizerRendering')) {
				$hookObj->preEditOrganizerRendering($drawnEditOrganizer, $this);
			}
		}

		return $drawnEditOrganizer;
	}
	
	function deleteOrganizer($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$organizer = $this->modelObj->findOrganizer($uid, $type, $pidList);

		// Hook: postDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteOrganizerRendering')) {
				$hookObj->postDeleteOrganizerRendering($this, $organizer, $pidList);
			}
		}

		$drawnDeleteOrganizer = $this->viewObj->drawDeleteOrganizer($organizer, $pidList);

		// Hook: preDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteOrganizerRendering')) {
				$hookObj->preDeleteOrganizerRendering($drawnDeleteOrganizer, $this);
			}
		}

		return $drawnDeleteOrganizer;
	}
	
	function createCalendar($getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createCalendarClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createCalendarClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateCalendarRendering')) {
				$hookObj->postCreateCalendarRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateCalendar = $this->viewObj->drawCreateCalendar($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateCalendarRendering')) {
				$hookObj->preCreateCalendarRendering($drawnCreateCalendar, $this);
			}
		}

		return $drawnCreateCalendar;
	}

	function confirmCalendar($pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmCalendarClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmCalendarClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmCalendarRendering')) {
				$hookObj->postConfirmCalendarRendering($this, $pidList);
			}
		}

		$drawnConfirmCalendar = $this->viewObj->drawConfirmCalendar($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmCalendarRendering')) {
				$hookObj->preConfirmCalendarRendering($drawnConfirmCalendar, $this);
			}
		}

		return $drawnConfirmCalendar;
	}
	
	function editCalendar($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editCalendarClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editCalendarClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$calendar = $this->modelObj->findCalendar($uid, $type, $pidList);

		// Hook: postEditCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditCalendarRendering')) {
				$hookObj->postEditCalendarRendering($this, $calendar, $pidList);
			}
		}

		$drawnEditCalendar = $this->viewObj->drawEditCalendar($calendar, $pidList);

		// Hook: preEditCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditCalendarRendering')) {
				$hookObj->preEditCalendarRendering($drawnEditCalendar, $this);
			}
		}

		return $drawnEditCalendar;
	}
	
	function deleteCalendar($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteCalendarClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteCalendarClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$calendar = $this->modelObj->findCalendar($uid, $type, $pidList);

		// Hook: postDeleteCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteCalendarRendering')) {
				$hookObj->postDeleteCalendarRendering($this, $calendar, $pidList);
			}
		}
		$drawnDeleteCalendar = $this->viewObj->drawDeleteCalendar($calendar, $pidList);

		// Hook: preDeleteCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteCalendarRendering')) {
				$hookObj->preDeleteCalendarRendering($drawnDeleteCalendar, $this);
			}
		}

		return $drawnDeleteCalendar;
	}
	
	function createCategory($getdate, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createCategoryClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['createCategoryClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateCategoryRendering')) {
				$hookObj->postCreateCategoryRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateCategory = $this->viewObj->drawCreateCategory($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateCategoryRendering')) {
				$hookObj->preCreateCategoryRendering($drawnCreateCategory, $this);
			}
		}

		return $drawnCreateCategory;
	}

	function confirmCategory($pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmCategoryClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['confirmCategoryClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmCategoryRendering')) {
				$hookObj->postConfirmCategoryRendering($this, $pidList);
			}
		}

		$drawnConfirmCategory = $this->viewObj->drawConfirmCategory($pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmCategoryRendering')) {
				$hookObj->preConfirmCategoryRendering($drawnConfirmCategory, $this);
			}
		}

		return $drawnConfirmCategory;
	}
	
	function editCategory($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editCategoryClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['editCategoryClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$category = $this->modelObj->findCategory($uid, $type, $pidList);

		// Hook: postEditCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditCategoryRendering')) {
				$hookObj->postEditCategoryRendering($this, $category, $pidList);
			}
		}

		$drawnEditCategory = $this->viewObj->drawEditCategory($category, $pidList);

		// Hook: preEditCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditCategoryRendering')) {
				$hookObj->preEditCategoryRendering($drawnEditCategory, $this);
			}
		}

		return $drawnEditCategory;
	}
	
	function deleteCategory($uid, $type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteCategoryClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['deleteCategoryClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$category = $this->modelObj->findCategory($uid, $type, $pidList);

		// Hook: postDeleteCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteCategoryRendering')) {
				$hookObj->postDeleteCategoryRendering($this, $category, $pidList);
			}
		}
		$drawnDeleteCategory = $this->viewObj->drawDeleteCategory($category, $pidList);

		// Hook: preDeleteCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteCategoryRendering')) {
				$hookObj->preDeleteCategoryRendering($drawnDeleteCategory, $this);
			}
		}

		return $drawnDeleteCategory;
	}

	function searchAllView($type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = array();
		$list["phpicalendar_event"] = $this->modelObj->searchEvents($type, $pidList);
		$list["location"] = $this->modelObj->searchLocation($type, $pidList);
		$list["organizer"] = $this->modelObj->searchOrganizer($type, $pidList);

		// Hook: postSearchAllRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchAllRendering')) {
				$hookObj->postSearchAllRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchAllResult($list);

		// Hook: preSearchAllRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchAllRendering')) {
				$hookObj->preSearchAllRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function searchLocationView($type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = $this->modelObj->searchLocation($type, $pidList);

		// Hook: postSearchLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchLocationRendering')) {
				$hookObj->postSearchLocationRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchLocationResult($list);

		// Hook: preSearchLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchLocationRendering')) {
				$hookObj->preSearchLocationRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function searchOrganizerView($type, $pidList) {
		global $TYPO3_CONF_VARS;
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['EXTCONF']['ext/cal/controller/class.tx_cal_controller.php']['drawsearchClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = $this->modelObj->searchOrganizer($this->cObj, $type, $pidList);

		// Hook: postSearchOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchOrganizerRendering')) {
				$hookObj->postSearchOrganizerRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawSearchOrganizerResult($list);

		// Hook: preSearchOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSearchOrganizerRendering')) {
				$hookObj->preSearchOrganizerRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}
	
	function updateConfWithFlexform(&$piFlexForm){
		$this->updateIfNotEmpty($this->conf['pages'], $this->pi_getFFvalue($piFlexForm, 'pages'));
		$this->updateIfNotEmpty($this->conf["recursive"], $this->pi_getFFvalue($piFlexForm, 'recursive'));
		$this->updateIfNotEmpty($this->conf['calendarName'], $this->pi_getFFvalue($piFlexForm, 'calendarName'));
		$this->updateIfNotEmpty($this->conf['allowSubscribe'] , $this->pi_getFFvalue($piFlexForm, 'allowSubscribe'));
		$this->updateIfNotEmpty($this->conf['subscribeFeUser'] , $this->pi_getFFvalue($piFlexForm, 'subscribeFeUser'));
		$this->updateIfNotEmpty($this->conf['subscribeWithCaptcha'] , $this->pi_getFFvalue($piFlexForm, 'subscribeWithCaptcha'));
		$this->updateIfNotEmpty($this->conf['view.']['allowedViews'] , $this->pi_getFFvalue($piFlexForm, 'allowedViews'));
		$this->conf['view.']['allowedViews'] = array_unique(split(',',$this->conf['view.']['allowedViews']));
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
		if($newConfVar!=""){
			$confVar = $newConfVar;
		}
	}
	
	function convertLinkVarArrayToList($linkVar){
		if(is_array($linkVar)){
			$first = true;
			foreach($linkVar as $key => $value){
				if($first){
					if($value=="on"){
						$value = intval($key);
					}
					$new .= $value;
					$first = false;
				}else{
					if($value=="on"){
						$value = intval($key);
					}
					$new .= ",".$value;
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']);
}
?>