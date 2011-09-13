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
	var $conf;
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
		$this->conf = $conf; //store configuration
		$this->cObj->conf = $conf;
		//Jan 18032006 start		
		$this->pi_initPIflexForm(); // Init and get the flexform data of the plugin
//		$this->lConf = array (); // Setup our storage array...
		$piFlexForm = $this->cObj->data['pi_flexform'];
		$this->updateConfWithFlexform($piFlexForm);
		
		if($this->conf['anonymousUserUid']==""){
			return "Please define an anonymousUserUid";
		}
		
		$this->local_cObj = t3lib_div :: makeInstance('tslib_cObj');
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		if ($this->conf["locale_all"])
			setlocale(LC_ALL, $this->conf["locale_all"]);
		if ($this->conf["language"])
			$this->LLkey = $this->conf["language"];
		$this->pi_loadLL();
		$this->cObj->ux_language = $this->LOCAL_LANG;
		$this->cObj->ux_llkey = $this->LLkey;
		$this->link_vars = t3lib_div :: GPvar($this->prefixId);
		$view = $this->link_vars['view']; // single, day, week, month, list, etc
		$lastview = $this->link_vars['lastview'];
		$getdate = $this->link_vars['getdate'];
		$type = $this->link_vars['type'];
		$uid = $this->link_vars['uid'];
		if ($getdate == '') {
			$getdate = date('Ymd');
		}
		//TODO: alle Eigenschaften sollten via conf-array übergeben werden
		$this->conf['getdate'] = $getdate;
		$this->conf['view'] = $view;
		$this->conf['lastview'] = $lastview;
		$this->conf['uid'] = $uid;
		$this->conf['type'] = $type;
		$this->conf['monitor'] = $this->link_vars['monitor'];
		$this->conf['gettime'] = $this->link_vars['gettime'];
		
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$this->modelObj = new $tx_cal_modelcontroller ();

		$tx_cal_viewcontroller = t3lib_div :: makeInstanceClassName('tx_cal_viewcontroller');
		$this->cObj->conf = $this->conf;
		$this->viewObj = new $tx_cal_viewcontroller ($this->cObj);

		$tx_cal_rightscontroller = t3lib_div :: makeInstanceClassName('tx_cal_rightscontroller');
		$this->rightsObj = new $tx_cal_rightscontroller ($this->cObj);
		$pidList = $this->pi_getPidList($this->conf['pages'], $this->conf["recursive"]);
		$this->conf['pidList'] = $pidList;

		$unix_time = strtotime($this->conf['getdate']);
		if ($view == 'day' || $view == 'week' || $view == 'month' || $view == 'year' || $view == 'event' || $view == 'location' || $view == 'organizer' || $view == 'list' || $view == 'ics' || $view == 'single_ics') {
			// catch all allowed standard view types
		} else
		if ($view == 'admin' && $this->rightsObj->isCalEditable()){
		} else
		if (($view == 'save_event' || $view == 'edit_event' || $view == 'confirm_event' || $view == 'delete_event' || $view == 'remove_event' || $view == 'create_event') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateEvents() 
					|| $this->rightsObj->isAllowedToEditEvents()
					|| $this->rightsObj->isAllowedToDeleteEvents())) {
		} else
		if (($view == 'save_location' || $view == 'confirm_location' || $view == 'create_location' || $view == 'edit_location')
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateLocations()
					|| $this->rightsObj->isAllowedToEditLocations()
					|| $this->rightsObj->isAllowedToDeleteLocations())) {
		// catch create_location view type and check all conditions
		} else
		if (($view == 'save_organizer' || $view == 'confirm_organizer' || $view == 'create_organizer' || $view == 'edit_organizer') 
				&& $this->rightsObj->isCalEditable() 
				&& (   $this->rightsObj->isAllowedToCreateOrganizer()
					|| $this->rightsObj->isAllowedToOrganizer()
					|| $this->rightsObj->isAllowedToDeleteOrganizer())) {
		// catch create_organizer view type and check all conditions
		} else {
		// a not wanted view type -> convert it
		$view = $this->conf["view."]['defaultView'];
			if ($view == '') {
				$view = 'month';
			}
		}
	
		$this->conf['view'] = $view;
		$return = "";
		$count = 0;

		while ($return == "" && $count<4) {
			$count++; //Just to make sure we are not getting an endless loop
			switch ($view) {
				case 'create_event' :
					$return = $this->createEvent($getdate, $pidList);
					break;
				case 'edit_event' :
					$return = $this->editEvent($uid, $type, $pidList);
					break;
				case 'confirm_event' :
					$return = $this->confirmEvent($pidList);
					break;
				case 'delete_event' :
					$return = $this->deleteEvent($uid, $type, $pidList);
					break;
				case 'save_event' :
					$this->saveEvent();
					$view = $lastview;
					$this->conf['lastview'] = "";
					$this->conf['view'] = $view;
					break;
				case 'remove_event' :
					$this->removeEvent();
					$view = $lastview;
					$this->conf['lastview'] = "";
					$this->conf['view'] = $view;
					break;
				case 'create_location' :
					$return = $this->createLocation($getdate, $pidList);
					break;
				case 'edit_location' :
					$return = $this->editLocation($uid, $type, $pidList);
					break;
				case 'confirm_location' :
					$return = $this->confirmLocation($pidList);
					break;
				case 'save_location' :
					$this->saveLocation();
					$view = $lastview;
					$this->conf['lastview'] = "";
					$this->conf['view'] = $view;
					break;
				case 'create_organizer' :
					$return = $this->createOrganizer($getdate, $pidList);
					break;
				case 'confirm_organizer' :
					$return = $this->confirmOrganizer($pidList);
					break;
				case 'edit_organizer' :
					$return = $this->editOrganizer($uid, $type, $pidList);
					break;
				case 'save_organizer' :
					$this->saveOrganizer();
					$view = $lastview;
					$this->conf['lastview'] = "";
					$this->conf['view'] = $view;
					break;
				case 'event' :
					$return = $this->event($uid, $type, $pidList, $getdate);
					break;
				case 'day' :
					$return = $this->day($unix_time, $type, $pidList, $getdate);
					break;
				case 'week' :
					$return = $this->week($unix_time, $type, $pidList, $getdate);
					break;
				case 'month' :
					$return = $this->month($unix_time, $type, $pidList, $getdate);
					break;
				case 'year' :
					$return = $this->year($unix_time, $type, $pidList, $getdate);
					break;
				case 'ics' :
					return $this->ics($unix_time, $type, $getdate, $pidList);
				case 'single_ics' :
					return $this->singleIcs($uid, $type, $getdate, $pidList);
				case 'location' :
					$return = $this->location($uid, $type, $pidList);
					break;
				case 'organizer' :
					$return = $this->organizer($uid, $type, $pidList);
					break;
				case 'list' :
					$return = $this->listview($type, $pidList);
					break;
			}
		}
		return $this->pi_wrapInBaseClass($return);
	}

	function saveEvent() {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['saveEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['saveEventClass'] as $classRef) {
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
		$ok = $this->modelObj->saveEvent($this->rightsObj, $this->conf, $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveEvent')) {
				$hookObj->preSaveEvent($this);
			}
		}
	}
	
	function removeEvent() {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['removeEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['removeEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postRemoveEvent
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postRemoveEvent')) {
				$hookObj->postRemoveEvent($this);
			}
		}
		$ok = $this->modelObj->removeEvent($this->rightsObj, $this->conf);

		// Hook: preRemoveEvent
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preRemoveEvent')) {
				$hookObj->preRemoveEvent($this);
			}
		}
	}

	function saveLocation() {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['saveLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['saveLocationClass'] as $classRef) {
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
		$ok = $this->modelObj->saveLocation($this->rightsObj, $this->conf, $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveLocation')) {
				$hookObj->preSaveLocation($this);
			}
		}
	}

	function saveOrganizer() {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['saveOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['saveOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSaveOrganizer')) {
				$hookObj->postSaveOrganizer($this);
			}
		}
		$pid = $this->conf["rights."]["create."]["organizer."]['save_organizer_to_pid'];
		if (!is_numeric($pid)) {
			$pid = $GLOBALS['TSFE']->id;
		}
		$ok = $this->modelObj->saveOrganizer($this->rightsObj, $this->conf, $pid);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preSaveOrganizer')) {
				$hookObj->preSaveOrganizer($this);
			}
		}
	}

	function event($uid, $type, $pidList, $getdate) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['draweventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['draweventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$event = $this->modelObj->findEvent($this->conf, $uid, $type, $pidList);
		if ($event == null) {
			return "No event found";
		}
		// Hook: postEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEventRendering')) {
				$hookObj->postEventRendering($event, $this);
			}
		}

		$drawnEvent = $this->viewObj->drawEvent($event, $getdate, $this->rightsObj);

		// Hook: preEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEventRendering')) {
				$hookObj->preEventRendering($drawnEvent, $event, $this);
			}
		}

		return $drawnEvent;
	}

	function day($unix_time, $type, $pidList, $getdate) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawdayClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawdayClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = $this->modelObj->findEventsForDay($this->conf, $unix_time, $type, $pidList);

		// Hook: postDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDayRendering')) {
				$hookObj->postDayRendering($master_array, $this);
			}
		}
		$drawnDay = $this->viewObj->drawDay($master_array, $getdate, $this->rightsObj);

		// Hook: preDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDayRendering')) {
				$hookObj->preDayRendering($drawnDay, $master_array, $this);
			}
		}

		return $drawnDay;
	}

	function week($unix_time, $type, $pidList, $getdate) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawweekClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawweekClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = $this->modelObj->findEventsForWeek($this->conf, $unix_time, $type, $pidList);

		// Hook: postWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postWeekRendering')) {
				$hookObj->postWeekRendering($master_array, $this);
			}
		}

		$drawnWeek = $this->viewObj->drawWeek($master_array, $getdate, $this->rightsObj);

		// Hook: preWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preWeekRendering')) {
				$hookObj->preWeekRendering($drawnWeek, $master_array, $this);
			}
		}

		return $drawnWeek;
	}

	function month($unix_time, $type, $pidList, $getdate) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawmonthClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawmonthClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$master_array = $this->modelObj->findEventsForMonth($this->conf, $unix_time, $type, $pidList);

		// Hook: postMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postMonthRendering')) {
				$hookObj->postMonthRendering($master_array, $this);
			}
		}

		$drawnMonth = $this->viewObj->drawMonth($master_array, $getdate, $this->rightsObj);

		// Hook: preMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preMonthRendering')) {
				$hookObj->preMonthRendering($drawnMonth, $master_array, $this);
			}
		}

		return $drawnMonth;
	}

	function year($unix_time, $type, $pidList, $getdate) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawyearClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawyearClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$master_array = $this->modelObj->findEventsForYear($this->conf, $unix_time, $type, $pidList);

		// Hook: postYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postYearRendering')) {
				$hookObj->postYearRendering($master_array, $this);
			}
		}

		$drawnYear = $this->viewObj->drawYear($master_array, $getdate, $this->rightsObj);

		// Hook: preYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preYearRendering')) {
				$hookObj->preYearRendering($drawnYear, $master_array, $this);
			}
		}

		return $drawnYear;
	}

	function ics($unix_time, $type, $getdate, $pidList) {

		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = $this->modelObj->findEventsForIcs($this->conf, $unix_time, $type, $pidList); //$this->conf['pid_list']);

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
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawicsClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$master_array = array ($this->modelObj->findEvent($this->conf, $uid, $type, $pidList)); //$this->conf['pid_list']));

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
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawlocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawlocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$location = $this->modelObj->findLocation($this->conf, $uid, $type, $pidList);

		// Hook: postLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postLocationRendering')) {
				$hookObj->postLocationRendering($location, $this);
			}
		}

		$drawnLocation = $this->viewObj->drawLocation($location, $this->rightsObj);

		// Hook: preLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preLocationRendering')) {
				$hookObj->preLocationRendering($drawnLocation, $location, $this);
			}
		}

		return $drawnLocation;
	}

	function organizer($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['draworganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['draworganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$organizer = $this->modelObj->findOrganizer($this->conf, $uid, $type, $pidList);

		// Hook: postOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postOrganizerRendering')) {
				$hookObj->postOrganizerRendering($organizer, $this);
			}
		}
		$drawnOrganizer = $this->viewObj->drawOrganizer($organizer, $this->rightsObj);

		// Hook: preOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preOrganizerRendering')) {
				$hookObj->preOrganizerRendering($drawnOrganizer, $organizer, $this);
			}
		}

		return $drawnOrganizer;
	}

	function listview($type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['drawlistClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		$list = $this->modelObj->findEventsForList($this->conf, date("Ymd", strtotime($this->conf["view."]["list."]['starttime'])), $type, $pidList);

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postListRendering')) {
				$hookObj->postListRendering($list, $this);
			}
		}
		$drawnList = $this->viewObj->drawList($list, date("Ymd", strtotime($this->conf["view."]["list."]['starttime'])));

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preListRendering')) {
				$hookObj->preListRendering($drawnList, $list, $this);
			}
		}

		return $drawnList;
	}

	function createEvent($getdate, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['createEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['createEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateEventRendering')) {
				$hookObj->postCreateEventRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateEvent = $this->viewObj->drawCreateEvent($getdate, $this->rightsObj, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateEventRendering')) {
				$hookObj->preCreateEventRendering($drawnCreateEvent, $this);
			}
		}

		return $drawnCreateEvent;
	}

	function confirmEvent($pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['confirmEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['confirmEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmEventRendering')) {
				$hookObj->postConfirmEventRendering($this, $pidList);
			}
		}

		$drawnConfirmEvent = $this->viewObj->drawConfirmEvent($this->rightsObj, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmEventRendering')) {
				$hookObj->preConfirmEventRendering($drawnConfirmEvent, $this);
			}
		}

		return $drawnConfirmEvent;
	}

	function editEvent($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['editEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['editEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$event = $this->modelObj->findEvent($this->conf, $uid, $type, $pidList);

		// Hook: postEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditEventRendering')) {
				$hookObj->postEditEventRendering($this, $event, $pidList);
			}
		}

		$drawnEditEvent = $this->viewObj->drawEditEvent($event, $this->rightsObj, $pidList);

		// Hook: preEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditEventRendering')) {
				$hookObj->preEditEventRendering($drawnEditEvent, $this);
			}
		}

		return $drawnEditEvent;
	}
	
	function deleteEvent($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['deleteEventClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['deleteEventClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$event = $this->modelObj->findEvent($this->conf, $uid, $type, $pidList);

		// Hook: postDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteEventRendering')) {
				$hookObj->postDeleteEventRendering($this, $event, $pidList);
			}
		}

		$drawnDeleteEvent = $this->viewObj->drawDeleteEvent($event, $this->rightsObj, $pidList);

		// Hook: preDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteEventRendering')) {
				$hookObj->preDeleteEventRendering($drawnDeleteEvent, $this);
			}
		}

		return $drawnDeleteEvent;
	}

	function createLocation($getdate, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['createLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['createLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateLocationRendering')) {
				$hookObj->postCreateLocationRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateLocation = $this->viewObj->drawCreateLocation($this->rightsObj, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateLocationRendering')) {
				$hookObj->preCreateLocationRendering($drawnCreateLocation, $this);
			}
		}

		return $drawnCreateLocation;
	}

	function confirmLocation($pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['confirmLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['confirmLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmLocationRendering')) {
				$hookObj->postConfirmLocationRendering($this, $pidList);
			}
		}

		$drawnConfirmLocation = $this->viewObj->drawConfirmLocation($this->rightsObj, $pidList);

		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmLocationRendering')) {
				$hookObj->preConfirmLocationRendering($drawnConfirmLocation, $this);
			}
		}

		return $drawnConfirmLocation;
	}
	
	function editLocation($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['editLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['editLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$location = $this->modelObj->findLocation($this->conf, $uid, $type, $pidList);

		// Hook: postEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditLocationRendering')) {
				$hookObj->postEditLocationRendering($this, $location, $pidList);
			}
		}

		$drawnEditLocation = $this->viewObj->drawEditLocation($location, $this->rightsObj, $pidList);

		// Hook: preEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditLocationRendering')) {
				$hookObj->preEditLocationRendering($drawnEditLocation, $this);
			}
		}

		return $drawnEditLocation;
	}
	
	function deleteLocation($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['deleteLocationClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['deleteLocationClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$location = $this->modelObj->findLocation($this->conf, $uid, $type, $pidList);

		// Hook: postDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteLocationRendering')) {
				$hookObj->postDeleteLocationRendering($this, $location, $pidList);
			}
		}

		$drawnDeleteLocation = $this->viewObj->drawDeleteLocation($location, $this->rightsObj, $pidList);

		// Hook: preDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteLocationRendering')) {
				$hookObj->preDeleteLocationRendering($drawnDeleteLocation, $this);
			}
		}

		return $drawnDeleteLocation;
	}

	function createOrganizer($getdate, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['createOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['createOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postCreateOrganizerRendering')) {
				$hookObj->postCreateOrganizerRendering($this, $getdate, $pidList);
			}
		}

		$drawnCreateOrganizer = $this->viewObj->drawCreateOrganizer($this->rightsObj, $pidList);

		// Hook: preCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preCreateOrganizerRendering')) {
				$hookObj->preCreateOrganizerRendering($drawnCreateOrganizer, $this);
			}
		}

		return $drawnCreateOrganizer;
	}

	function confirmOrganizer($pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['confirmOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['confirmOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		// Hook: postConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postConfirmOrganizerRendering')) {
				$hookObj->postConfirmOrganizerRendering($this, $pidList);
			}
		}

		$drawnConfirmOrganizer = $this->viewObj->drawConfirmOrganizer($this->rightsObj, $pidList);

		// Hook: preConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preConfirmOrganizerRendering')) {
				$hookObj->preConfirmOrganizerRendering($drawnConfirmOrganizer, $this);
			}
		}

		return $drawnConfirmOrganizer;
	}
	
	function editOrganizer($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['editOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['editOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}

		$organizer = $this->modelObj->findOrganizer($this->conf, $uid, $type, $pidList);

		// Hook: postEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postEditOrganizerRendering')) {
				$hookObj->postEditOrganizerRendering($this, $organizer, $pidList);
			}
		}

		$drawnEditOrganizer = $this->viewObj->drawEditOrganizer($organizer, $this->rightsObj, $pidList);

		// Hook: preEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preEditOrganizerRendering')) {
				$hookObj->preEditOrganizerRendering($drawnEditOrganizer, $this);
			}
		}

		return $drawnEditOrganizer;
	}
	
	function deleteOrganizer($uid, $type, $pidList) {
		$hookObjectsArr = array ();
		if (is_array($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['deleteOrganizerClass'])) {
			foreach ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['deleteOrganizerClass'] as $classRef) {
				$hookObjectsArr[] = & t3lib_div :: getUserObj($classRef);
			}
		}
		
		$organizer = $this->modelObj->findOrganizer($this->conf, $uid, $type, $pidList);

		// Hook: postDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postDeleteOrganizerRendering')) {
				$hookObj->postDeleteOrganizerRendering($this, $organizer, $pidList);
			}
		}

		$drawnDeleteOrganizer = $this->viewObj->drawDeleteOrganizer($organizer, $this->rightsObj, $pidList);

		// Hook: preDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preDeleteOrganizerRendering')) {
				$hookObj->preDeleteOrganizerRendering($drawnDeleteOrganizer, $this);
			}
		}

		return $drawnDeleteOrganizer;
	}
	
	function updateConfWithFlexform(&$piFlexForm){
		$this->updateIfNotEmpty($this->conf['pages'], $this->pi_getFFvalue($piFlexForm, 'pages'));
		$this->updateIfNotEmpty($this->conf["recursive"], $this->pi_getFFvalue($piFlexForm, 'recursive'));
		$this->updateIfNotEmpty($this->conf['calendarName'], $this->pi_getFFvalue($piFlexForm, 'calendarName'));
		$this->updateIfNotEmpty($this->conf['anonymousUserUid'], $this->pi_getFFvalue($piFlexForm, 'anonymousUserUid'));
		$this->updateIfNotEmpty($this->conf['allowSubscribe'] , $this->pi_getFFvalue($piFlexForm, 'allowSubscribe'));
		$this->updateIfNotEmpty($this->conf['subscribeFeUser'] , $this->pi_getFFvalue($piFlexForm, 'subscribeFeUser'));
		$this->updateIfNotEmpty($this->conf['subscribeWithCaptcha'] , $this->pi_getFFvalue($piFlexForm, 'subscribeWithCaptcha'));
		$this->updateIfNotEmpty($this->conf['view.']['defaultView'] , $this->pi_getFFvalue($piFlexForm, 'viewmode'));
		//$this->updateIfNotEmpty($this->conf['view.']['minicalview'] , $this->pi_getFFvalue($piFlexForm, 'minicalview'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayViewPid'] , $this->pi_getFFvalue($piFlexForm, 'dayViewPid','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayStart'] , $this->pi_getFFvalue($piFlexForm, 'dayStart','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayEnd'] , $this->pi_getFFvalue($piFlexForm, 'dayEnd','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['day.']['gridLength'] , $this->pi_getFFvalue($piFlexForm, 'gridLength','s_Day_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['day.']['dayTemplate'], $this->pi_getFFvalue($piFlexForm, 'dayTemplate','s_Day_View'));
		$this->updateIfNotEmpty($this->conf['view.']['week.']['weekViewPid'] , $this->pi_getFFvalue($piFlexForm, 'weekViewPid','s_Week_View'));
		$this->updateIfNotEmpty($this->conf['view.']['week.']['weekStartDay'] , $this->pi_getFFvalue($piFlexForm, 'weekStartDay','s_Week_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['week.']['weekTemplate'] , $this->pi_getFFvalue($piFlexForm, 'weekTemplate','s_Week_View'));
	
		$this->updateIfNotEmpty($this->conf['view.']['month.']['monthViewPid'] , $this->pi_getFFvalue($piFlexForm, 'monthViewPid','s_Month_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['month.']['monthTemplate'] , $this->pi_getFFvalue($piFlexForm, 'monthTemplate','s_Month_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['month.']['monthSmallTemplate'] , $this->pi_getFFvalue($piFlexForm, 'monthSmallTemplate','s_Month_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['month.']['monthMediumTemplate'] , $this->pi_getFFvalue($piFlexForm, 'monthMediumTemplate','s_Month_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['month.']['monthLargeTemplate'] , $this->pi_getFFvalue($piFlexForm, 'monthLargeTemplate','s_Month_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['month.']['thisMonthsEvents'] , $this->pi_getFFvalue($piFlexForm, 'thisMonthsEvents','s_Month_View'));
		$this->updateIfNotEmpty($this->conf['view.']['year.']['yearViewPid'] , $this->pi_getFFvalue($piFlexForm, 'yearViewPid','s_Year_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['year.']['yearTemplate'] , $this->pi_getFFvalue($piFlexForm, 'yearTemplate','s_Year_View'));
		$this->updateIfNotEmpty($this->conf['view.']['event.']['eventViewPid'] , $this->pi_getFFvalue($piFlexForm, 'eventViewPid','s_Event_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['event.']['eventTemplate'] , $this->pi_getFFvalue($piFlexForm, 'eventTemplate','s_Event_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['event.']['phpicalendarEventTemplate'] , $this->pi_getFFvalue($piFlexForm, 'phpicalendarEventTemplate','s_Event_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['event.']['createEventTemplate'] , $this->pi_getFFvalue($piFlexForm, 'createEventTemplate','s_Event_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['event.']['confirmEventTemplate'] , $this->pi_getFFvalue($piFlexForm, 'confirmEventTemplate','s_Event_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['event.']['deleteEventTemplate'] , $this->pi_getFFvalue($piFlexForm, 'deleteEventTemplate','s_Event_View'));
		$this->updateIfNotEmpty($this->conf['view.']['event.']['defaultCategoryText'] , $this->pi_getFFvalue($piFlexForm, 'defaultCategoryText','s_Event_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['location.']['locationTemplate'] , $this->pi_getFFvalue($piFlexForm, 'locationTemplate','s_Location_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['location.']['createLocationTemplate'] , $this->pi_getFFvalue($piFlexForm, 'createLocationTemplate','s_Location_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['location.']['confirmLocationTemplate'] , $this->pi_getFFvalue($piFlexForm, 'confirmLocationTemplate','s_Location_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['organizer.']['organizerTemplate'] , $this->pi_getFFvalue($piFlexForm, 'organizerTemplate','s_Organizer_View'));
		$this->updateIfNotEmpty($this->conf['view.']['list.']['starttime'] , $this->pi_getFFvalue($piFlexForm, 'starttime','s_List_View'));
		$this->updateIfNotEmpty($this->conf['view.']['list.']['endtime'] , $this->pi_getFFvalue($piFlexForm, 'endtime','s_List_View'));
		$this->updateIfNotEmpty($this->conf['view.']['list.']['maxEvents'] , $this->pi_getFFvalue($piFlexForm, 'maxEvents','s_List_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['list.']['listTemplate'] , $this->pi_getFFvalue($piFlexForm, 'listTemplate','s_List_View'));
		$this->updateIfNotEmpty($this->conf['view.']['ics.']['calUid'] , $this->pi_getFFvalue($piFlexForm, 'calUid','s_Ics_View'));
		$this->updateIfNotEmpty($this->conf['view.']['ics.']['showIcsLinks'] , $this->pi_getFFvalue($piFlexForm, 'showIcsLinks','s_Ics_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['ics.']['icsTemplate'] , $this->pi_getFFvalue($piFlexForm, 'icsTemplate','s_Ics_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showLogin'] , $this->pi_getFFvalue($piFlexForm, 'showLogin','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showSearch'] , $this->pi_getFFvalue($piFlexForm, 'showSearch','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showGoto'] , $this->pi_getFFvalue($piFlexForm, 'showGoto','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showMultiple'] , $this->pi_getFFvalue($piFlexForm, 'showMultiple','s_Other_View'));
		$this->updateIfNotEmpty($this->conf['view.']['other.']['showTomorrowEvents'] , $this->pi_getFFvalue($piFlexForm, 'showTomorrowEvents','s_Other_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['other.']['sidebarTemplate'] , $this->pi_getFFvalue($piFlexForm, 'sidebarTemplate','s_Other_View'));
//		$this->updateIfNotEmpty($this->conf['view.']['other.']['searchBoxTemplate'] , $this->pi_getFFvalue($piFlexForm, 'searchBoxTemplate','s_Other_View'));
	}

	function updateIfNotEmpty(&$confVar, $newConfVar){
		if($newConfVar!=""){
			$confVar = $newConfVar;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']);
}
?>