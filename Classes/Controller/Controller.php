<?php
namespace TYPO3\CMS\Cal\Controller;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Main controller for the calendar base.
 * All requests come through this class
 * and are routed to the model and view layers for processing.
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class Controller extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $prefixId = 'tx_cal_controller'; // Same as class name
	var $scriptRelPath = 'Classes/Controller/Controller.php'; // Path to this script relative to the extension dir.
	var $locallangPath = 'Resources/Private/Language/locallang.xml';
	var $extKey = 'cal'; // The extension key.
	var $pi_checkCHash = FALSE;
	var $dayStart;
	var $ext_path;
	var $cObj; // The backReference to the mother cObj object set at call time
	var $local_cObj;
	var $link_vars;
	var $pointerName = 'offset';
	var $error = false;
	var $getDateTimeObject;
	var $SIM_ACCESS_TIME = 0;
	
	/**
	 * Main controller function that serves as the entry point from TYPO3.
	 * 
	 * @param
	 *        	array		The content array.
	 * @param
	 *        	array		The conf array.
	 * @return string of calendar data.
	 */
	public function main($content, $conf) {
		$this->conf = &$conf;
		
		$this->conf ['useInternalCaching'] = 1;
		$this->conf ['cachingEngine'] = 'cachingFramework';
		$this->conf ['writeCachingInfoToDevlog'] = 0;
		
		$this->cacheHandling();
		
		// Set the week start day, and then include \TYPO3\CMS\Cal\Model\CalDate so that the week start day is already defined.
		$this->setWeekStartDay ();
		
		$this->cleanPiVarParam ($this->piVars);
		$this->clearPiVarParams ();
		$this->validateDateRanges();
		$this->getParamsFromSession ();
		$this->initCaching ();
		
		$hookObjectsArr = $this->getHookObjectsArray ('controllerClass');
		// Hook: initCal
		foreach ($hookObjectsArr as $hookObj) {
		    if (method_exists ($hookObj, 'initCal')) {
		        $hookObj->initCal ($this);
		    }
		}
		
		$return = $this->initConfigs ();
		if (! $this->error) {
			$return .= $this->getContent ();
		}
		
		return $return;
	}

	protected function cacheHandling(){
		// switch for more intelligent caching
		if ($this->conf ['isUserInt']) {
			// this->pi_USER_INT_obj=1;
		} else {
			$this->pi_checkCHash = TRUE;
			$requestedNoCache = GeneralUtility::_GP('no_cache');
			if ($requestedNoCache) {
				$this->pi_checkCHash = FALSE;
				$GLOBALS ['TSFE']->set_no_cache();
			}
			if (count ($this->piVars) && !$requestedNoCache) {
				$GLOBALS ['TSFE']->reqCHash ();
			}
			$this->pi_USER_INT_obj = 0;
		}
	}
	
	/**
	 * Cleans all piVars for XSS vulnerabilities using external library and
	 * updates values within $this->piVars as it cleans.
	 *
	 * @param
	 *        	mixed	Array of nested piVars or individual piVar value.
	 */
	public function cleanPiVarParam(&$param) {
		if (is_array ($param)) {
			$arrayKeys = array_keys ($param);
			foreach ($arrayKeys as $key) {
				$this->cleanPiVarParam ($param [$key]);
			}
		} else {
			// Don't use default replaceString of <x> because strip-tags will later remove it.
			$param = \TYPO3\CMS\Cal\Utility\Functions::removeXSS ($param, '--xxx--');
		}
	}
	
	/**
	 * Validates that various date piVars are within valid ranges.  Any dates outside a valid range have
	 * their piVars unset.
	 */
	public function validateDateRanges() {
		
		if (isset($this->piVars['day'])) {
			$this->piVars['day'] = intval($this->piVars['day']);
			if ($this->piVars['day'] < 1 || $this->piVars['day'] > 31) {
				unset($this->piVars['day']);
			}
		}
	
		if (isset($this->piVars['month'])) {
			$this->piVars['month'] = intval($this->piVars['month']);
			if ($this->piVars['month'] < 1 || $this->piVars['month'] > 12) {
				unset($this->piVars['month']);
			}
		}
	
		if (isset($this->piVars['year'])) {
			$this->piVars['year'] = intval($this->piVars['year']);
			if ($this->piVars['year'] < 1900 || $this->piVars['year'] > 5000) {
				unset($this->piVars['year']);
			}
		}
	
		if (isset($this->piVars['weekday'])) {
			$this->piVars['weekday'] = intval($this->piVars['weekday']);
			if ($this->piVars['weekday'] < 0 || $this->piVars['weekday'] > 6) {
				unset($this->piVars['weekday']);
			}
		}

		if (isset($this->piVars['getdate']) && strlen($this->piVars['getdate']) !== 8) {
			unset($this->piVars['getdate']);
		}
	}
	
	
	/**
	 * 
	 * @param boolean
	 * @return string
	 */
	public function getContent($notEmpty = true) {
		$return = '';
		$count = 0;
		do {
			// category check:
			$catArray = GeneralUtility::trimExplode (',', $this->conf ['category'], 1);
			$allowedCatArray = GeneralUtility::trimExplode (',', $this->conf ['view.'] ['allowedCategory'], 1);
			$compareResult = array_diff ($allowedCatArray, $catArray);
			if (empty ($compareResult) && $this->conf ['view'] != 'create_event' && $this->conf ['view'] != 'edit_event') {
				unset ($this->piVars ['category']);
			}
			$count ++; // Just to make sure we are not getting an endless loop
			/* Convert view names (search_event) to function names (searchevent) */
			$viewFunction = str_replace ('_', '', $this->conf ['view']);
			
			/* @todo Hack! List is a reserved name so we have to change the function name. */
			if ($viewFunction == 'list') {
				$viewFunction = 'listView';
			}
			
			if (method_exists ($this, $viewFunction)) {
				/* Call appropriate view function */
				$return .= $this->$viewFunction ();
			} else {
				$customModel = GeneralUtility::makeInstanceService ('cal_view', $this->conf ['view']);
				if (! is_object ($customModel)) {
					$return .= $this->conf ['view.'] ['noViewFoundHelpText'] . ' ' . $viewFunction;
				} else {
					$return .= $customModel->start ();
				}
			}
		} while ($return == '' && $count < 4 && $notEmpty);
		
		$return = $this->finish ($return);
		
		if ($this->conf ['view'] == 'rss' || $this->conf ['view'] == 'ics' || $this->conf ['view'] == 'single_ics' || $this->conf ['view'] == 'load_events' || $this->conf ['view'] == 'load_todos' || $this->conf ['view'] == 'load_rights') {
			return $return;
		}
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['sendOutWithXMLHeader']) {
			header ('Content-Type: text/xml');
		}
		
		$additionalWrapperClasses = GeneralUtility::trimExplode (',', $this->conf ['additionalWrapperClasses'], 1);
		
		if ($this->conf ['noWrapInBaseClass'] || $this->conf ['view.'] ['enableAjax']) {
			return $return;
		}
		return $this->pi_wrapInBaseClass ($return, $additionalWrapperClasses);
	}
	
	/**
	 * 
	 */
	public function initConfigs() {
		// If an event record has been added through Insert Records, set some defaults.
		if ($this->conf ['displayCurrentRecord']) {
			$data = &$this->cObj->data;
			$this->conf ['pidList'] = $data ['pid'];
			$this->conf ['view.'] ['allowedViews'] = 'event';
			$this->conf ['getdate'] = $this->conf ['_DEFAULT_PI_VARS.'] ['getdate'] = $data ['start_date'];
			$this->conf ['uid'] = $this->conf ['_DEFAULT_PI_VARS.'] ['uid'] = $data ['uid'];
			$this->conf ['type'] = $this->conf ['_DEFAULT_PI_VARS.'] ['type'] = 'tx_cal_phpicalendar';
			$this->conf ['view'] = $this->conf ['_DEFAULT_PI_VARS.'] ['view'] = 'event';
		}
		
		if (! $this->conf ['dontListenToPiVars']) {
			$this->pi_setPiVarDefaults (); // Set default piVars from TS
		}
		
		// Jan 18032006 start
		if ($this->cObj->data ['pi_flexform']) {
			$this->pi_initPIflexForm (); // Init and get the flexform data of the plugin
			$piFlexForm = $this->cObj->data ['pi_flexform'];
			$this->updateConfWithFlexform ($piFlexForm);
		}
		
		// apply stdWrap to pages and pidList
		$this->conf ['pages'] = $this->cObj->stdWrap ($this->conf ['pages'], $this->conf ['pages.']);
		$this->conf ['pidList'] = $this->cObj->stdWrap ($this->conf ['pidList'], $this->conf ['pidList.']);
		
		Controller::updateIfNotEmpty ($this->conf ['pages'], $this->cObj->data ['pages']);
		// don't use "updateIfNotEmpty" here, as the default value of "recursive" is 0 and thus not empty and will always override TS settings.
		if ($this->cObj->data ['recursive']) {
			$this->conf ['recursive'] = $this->cObj->data ['recursive'];
		}
		
		$this->conf ['pidList'] = $this->pi_getPidList ($this->conf ['pages'] . ',' . $this->conf ['pidList'], $this->conf ['recursive']);
		
		if (! $this->conf ['pidList'] || $this->conf ['pidList'] == '') {
			$this->error = true;
			return '<b>Calendar error: please configure the pidList (calendar plugin -> startingpoints or plugin.tx_cal_controller.pidList or for ics in constants)</b>';
		}
		
		if ($this->conf ['language']) {
			$this->LLkey = $this->conf ['language'];
		}
		$tempScriptRelPath = $this->scriptRelPath;
		$this->scriptRelPath = $this->locallangPath;
		$this->pi_loadLL ();
		$this->scriptRelPath = $tempScriptRelPath;
		
		$this->conf ['cache'] = 1;
		$GLOBALS ['TSFE']->addCacheTags (array (
				'cal' 
		));
		
		$location = Controller::convertLinkVarArrayToList ($this->piVars ['location_ids']);
		
		if ($this->piVars ['view'] == $this->piVars ['lastview']) {
			unset ($this->piVars ['lastview']);
		}

		if ($this->piVars ['getdate'] == '') {
			$this->conf ['getdate'] = date ('Ymd');
		} else {
			$this->conf ['getdate'] = intval ($this->piVars ['getdate']);
		}
		
		if ($this->piVars ['jumpto']) {
			$dp = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\DateParser');
			$dp->parse ($this->piVars ['jumpto'], $this->conf ['dateParserConf.']);
			$newGetdate = $dp->getDateObjectFromStack ();
			$this->conf ['getdate'] = $newGetdate->format ('%Y%m%d');
			unset ($this->piVars ['getdate']);
			unset ($this->piVars ['jumpto']);
		}
		
		// date and strtotime should be ok here
		if ($this->conf ['getdate'] <= date ('Ymd', strtotime ($this->conf ['view.'] ['startLinkRange'])) || $this->conf ['getdate'] >= date ('Ymd', strtotime ($this->conf ['view.'] ['endLinkRange']))) {
			// Set additional META-Tag for google et al
			$GLOBALS ['TSFE']->additionalHeaderData ['cal'] = '<meta name="robots" content="index,nofollow" />';
			
			// Set / override no_search for current page-object
			$GLOBALS ['TSFE']->page ['no_search'] = 0;
		}
		
		if (! $this->conf ['dontListenToPiVars']) {
			$this->conf ['view'] = htmlspecialchars (strip_tags ($this->piVars ['view']));
			$this->conf ['lastview'] = htmlspecialchars (strip_tags ($this->piVars ['lastview']));
			$this->conf ['uid'] = intval ($this->piVars ['uid']);
			$this->conf ['type'] = htmlspecialchars (strip_tags ($this->piVars ['type']));
			$this->conf ['monitor'] = htmlspecialchars (strip_tags ($this->piVars ['monitor']));
			$this->conf ['gettime'] = intval ($this->piVars ['gettime']);
			$this->conf ['postview'] = intval ($this->piVars ['postview']);
			$this->conf ['page_id'] = intval ($this->piVars ['page_id']);
			$this->conf ['option'] = htmlspecialchars (strip_tags ($this->piVars ['option']));
			$this->conf ['switch_calendar'] = intval ($this->piVars ['switch_calendar']);
			$this->conf ['location'] = $location;
			$this->conf ['preview'] = intval ($this->piVars ['preview']);
		}
		
		if (! is_array ($this->conf ['view.'] ['allowedViews'])) {
			$this->conf ['view.'] ['allowedViews'] = array_unique (GeneralUtility::trimExplode (',', str_replace ('~', ',', $this->conf ['view.'] ['allowedViews'])));
		}
		
		// only merge customViews if not empty. Otherwhise the array with allowedViews will have empty entries which will end up in wrong behavior in the rightsServies, which is checking for the number of allowed views.
		if (! empty ($this->conf ['view.'] ['customViews'])) {
			$this->conf ['view.'] ['allowedViews'] = array_unique (array_merge ($this->conf ['view.'] ['allowedViews'], GeneralUtility::trimExplode (',', $this->conf ['view.'] ['customViews'], 1)));
		}
		
		$allowedViewsByViewPid = $this->getAllowedViewsByViewPid ();
		$this->conf ['view.'] ['allowedViewsToLinkTo'] = array_unique (array_merge ($this->conf ['view.'] ['allowedViews'], $allowedViewsByViewPid));
		
		// change by Franz: if there is no view parameter given (empty), fall back to the first allowed view
		// This is necessary when you're not passing the viewParameter within the URL and like to handle the correct views based on seperate pages for each view.
		if (! $this->conf ['view'] && $this->conf ['view.'] ['allowedViews'] [0]) {
			$this->conf ['view'] = $this->conf ['view.'] ['allowedViews'] [0];
		}
		
		$this->getDateTimeObject = new \TYPO3\CMS\Cal\Model\CalDate($this->conf ['getdate'] . '000000');
		
		if ($this->getDateTimeObject->month > 12) {
			$this->getDateTimeObject->month = 12;
		} else if ($this->getDateTimeObject->month < 1) {
			$this->getDateTimeObject->month = 1;
		}
		while (! \TYPO3\CMS\Cal\Model\Pear\Date\Calc::isValidDate ($this->getDateTimeObject->day, $this->getDateTimeObject->month, $this->getDateTimeObject->year)) {
			if ($this->getDateTimeObject->day > 28) {
				$this->getDateTimeObject->day --;
			} else if ($this->getDateTimeObject->day < 1) {
				$this->getDateTimeObject->day = 1;
			}
		}
		
		$this->getDateTimeObject->setTZbyId ('UTC');
		$this->conf ['day'] = $this->getDateTimeObject->getDay ();
		$this->conf ['month'] = $this->getDateTimeObject->getMonth ();
		$this->conf ['year'] = $this->getDateTimeObject->getYear ();
		
		Controller::initRegistry ($this);
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$rightsObj = GeneralUtility::makeInstanceService ('cal_rights_model', 'rights');
		$rightsObj->setDefaultSaveToPage ();
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$modelObj = new \TYPO3\CMS\Cal\Controller\ModelController ();
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$viewObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\ViewController');
		
		$this->checkCalendarAndCategory ();
		
		$this->conf ['view'] = $rightsObj->checkView ($this->conf ['view']);
		
		$this->pointerName = $this->conf ['view.'] ['list.'] ['pageBrowser.'] ['pointer'] ? $this->conf ['view.'] ['list.'] ['pageBrowser.'] ['pointer'] : $this->pointerName;
		
		// links to files will be rendered with an absolute path
		if (in_array ($this->conf ['view'], array (
				'ics',
				'rss',
				'singl_ics' 
		))) {
			$GLOBALS ['TSFE']->absRefPrefix = GeneralUtility::getIndpEnv ('TYPO3_SITE_URL');
		}
		
		$hookObjectsArr = $this->getHookObjectsArray ('controllerClass');
		
		// Hook: configuration
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'configuration')) {
				$hookObj->configuration ($this);
			}
		}
	}
	
	/**
	 * 
	 */
	public function initCaching() {
		$this->SIM_ACCESS_TIME = $GLOBALS ['SIM_ACCESS_TIME'];
		// fallback for TYPO3 < 4.2
		if (! $this->SIM_ACCESS_TIME) {
			$simTime = $GLOBALS ['SIM_EXEC_TIME'];
			$this->SIM_ACCESS_TIME = $simTime - ($simTime % 60);
		}
		
		$lifetime = 0;
		
		if ($this->conf ['useInternalCaching']) {
			
			$cachingEngine = $this->conf ['cachingEngine'];
			
			if ($cachingEngine == 'cachingFramework') {
				
				if (! is_object ($GLOBALS ['typo3CacheFactory']) || ! isset ($GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['backend'])) {
					// if there's no cacheFactory object fall back to internal caching (TYPO3 < 4.3)
					$cachingEngine = 'internal';
				}
			}
			
			if (! $cachingEngine) {
				$cachingEngine = 'internal';
			}
			
			if ($this->conf ['writeCachingInfoToDevlog']) {
				$tmp = GeneralUtility::trimExplode ('|', $this->conf ['writeCachingInfoToDevlog'], 0);
				if ($tmp [1]) {
					$this->writeCachingInfoToDevlog = $tmp [1];
				}
			}
			
			switch ($this->conf ['cacheClearMode']) {
				case 'lifetime' :
					$lifetime = $this->conf ['cacheLifetime'];
					break;
				
				default : // normal
					if (method_exists ($GLOBALS ['TSFE'], 'get_cache_timeout')) { // TYPO3 >= 4.2
						$lifetime = $GLOBALS ['TSFE']->get_cache_timeout (); // seconds until a cached page is too old
					} else {
						$lifetime = 86400;
					}
					break;
				// the case 'never' uses the default: $lifetime = 0;
			}
			$this->cache = new \TYPO3\CMS\Cal\Utility\Cache ($cachingEngine);
			$this->cache->lifetime = $lifetime;
			$this->cache->ACCESS_TIME = $this->SIM_ACCESS_TIME;
		}
	}
	
	
	/**
	 * 
	 */
	public function checkCalendarAndCategory() {
		// new Mode - category can be configurred
		$category = '';
		$calendar = '';
		
		$this->confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		
		$allCategoryByParentId = array ();
		$catIDs = array ();
		$category = '';
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		
		// et all categories
		$categoryArray = $modelObj->findAllCategories ($this->confArr ['categoryService'], '', $this->conf ['pidList']);
		
		foreach ((Array) $categoryArray [$this->confArr ['categoryService']] [0] [0] as $category) {
			$row = $category->row;
			$allCategoryByParentId [$row ['parent_category']] [] = $row;
			$catIDs [] = $row ['uid'];
		}
		
		if ($this->piVars ['categorySelection'] == 1 && empty ($this->piVars ['category'])) {
			$catIDs = Array ();
		} else {
			unset ($this->piVars ['categorySelection']);
		}
		$this->conf ['view.'] ['category'] = implode(',', array_map(function ($v, $k) { return $v; }, $catIDs, array_keys($catIDs)));
// 		$this->conf ['view.'] ['category'] = implode (',', $catIDs);
		if (! $this->conf ['view.'] ['category']) {
			$this->conf ['view.'] ['category'] = '0';
		}
		$category = $this->conf ['view.'] ['category'];
		$this->conf ['view.'] ['allowedCategory'] = $this->conf ['view.'] ['category'];
		
		$piVarCategory = Controller::convertLinkVarArrayToList ($this->piVars ['category']);
		
		if ($piVarCategory) {
			if ($this->conf ['view.'] ['category']) {
				$categoryArray = explode (',', $category);
				$piVarCategoryArray = explode (',', $piVarCategory);
				$sameValues = array_intersect ($categoryArray, $piVarCategoryArray);
				if (empty ($sameValues)) {
					$category = $this->conf ['view.'] ['category'];
				} else {
					$category = Controller::convertLinkVarArrayToList ($sameValues);
				}
			} else {
				$category = $piVarCategory;
			}
			$category = is_array ($category) ? implode (',', $category) : $category;
		}
		
		// elect calendars
		// et all first
		$allCalendars = Array ();
		$calendarArray = $modelObj->findAllCalendar ('tx_cal_calendar', $this->conf ['pidList']);
		foreach ((array) $calendarArray ['tx_cal_calendar'] as $calendarObject) {
			$allCalendars [] = $calendarObject->getUid ();
		}
		
		// ompile calendar array
		switch ($this->conf ['view.'] ['calendarMode']) {
			case 0 : // show all
				$calendar = $this->conf ['view.'] ['calendar'] = $this->conf ['view.'] ['allowedCalendar'] = implode (',', $allCalendars);
				break;
			case 1 : // how selected
				if ($this->conf ['view.'] ['calendar']) {
					$calendar = $this->conf ['view.'] ['calendar'];
					$this->conf ['view.'] ['allowedCalendar'] = $this->conf ['view.'] ['calendar'];
				}
				break;
			case 2 : // xclude selected
				if ($this->conf ['view.'] ['calendar']) {
					$calendar = $this->conf ['view.'] ['calendar'] = implode (',', array_diff ($allCalendars, explode (',', $this->conf ['view.'] ['calendar'])));
					$this->conf ['view.'] ['allowedCalendar'] = $this->conf ['view.'] ['calendar'];
				} else {
					$calendar = $this->conf ['view.'] ['calendar'] = implode (',', $allCalendars);
					$this->conf ['view.'] ['allowedCalendar'] = $this->conf ['view.'] ['calendar'];
				}
				break;
		}
		
		if ($rightsObj->isLoggedIn ()) {
			$select = 'tx_cal_calendar_subscription';
			$table = 'fe_users';
			$where = 'uid = ' . $rightsObj->getUserId ();
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
			if ($result) {
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
					$this->conf ['view.'] ['calendar.'] ['subscription'] = $row ['tx_cal_calendar_subscription'];
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			}
		}
		
		if ($this->conf ['view.'] ['calendar.'] ['subscription'] != '') {
			$calendar = $this->conf ['view.'] ['allowedCalendar'] = $this->conf ['view.'] ['calendar'] = implode (',', array_diff (explode (',', $calendar), explode (',', $this->conf ['view.'] ['calendar.'] ['subscription'])));
		}
		
		$piVarCalendar = Controller::convertLinkVarArrayToList ($this->piVars ['calendar']);
		if ($piVarCalendar) {
			if ($this->conf ['view.'] ['calendar']) {
				$calendarArray = explode (',', $calendar);
				$piVarCalendarArray = explode (',', $piVarCalendar);
				$sameValues = array_intersect ($calendarArray, $piVarCalendarArray);
				$calendar = Controller::convertLinkVarArrayToList ($sameValues);
			} else {
				$calendar = $piVarCalendar;
			}
			$calendar = is_array ($calendar) ? implode (',', $calendar) : $calendar;
		}
		
		if ($this->conf ['view.'] ['freeAndBusy.'] ['enable']) {
			$this->conf ['option'] = 'freeandbusy';
			$this->conf ['view.'] ['calendarMode'] = 1;
			$calendar = intval ($this->piVars ['calendar']) ? intval ($this->piVars ['calendar']) : $this->conf ['view.'] ['freeAndBusy.'] ['defaultCalendarUid'];
			$this->conf ['view.'] ['calendar'] = $calendar;
		}
		
		$this->conf ['category'] = $category;
		$this->conf ['calendar'] = $calendar;
		$this->conf ['view.'] ['allowedCategories'] = $category;
		$this->conf ['view.'] ['allowedCalendar'] = $calendar;
	}
	
	/**
	 * Sets up a hook in the controller's PHP file with the specified name. 
	 * @param	string	The name of the hook. 
	 * @return	array	The array of objects implementing this hoook.
	 */
	public function getHookObjectsArray($hookName) {
		return \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ($this->prefixId, $hookName);
	}
	
	/**
	 * Executes the specified function for each item in the array of hook objects. 
	 * @param	array	The array of hook objects.
	 * @param	string	The name of the function to execute.
	 */
	public function executeHookObjectsFunction($hookObjectsArray, $function) {
		foreach ($hookObjectsArray as $hookObj) {
			if (method_exists ($hookObj, $function)) {
				$hookObj->$function ($this);
			}
		}
	}
	
	/**
	 * Clears $this-conf vars related to view and lastview. Useful when calling save and remove functions.
	 */
	public function clearConfVars() {
		$this->initConfigs ();
		$viewParams = $this->shortenLastViewAndGetTargetViewParameters (true);
		$this->conf ['view'] = $viewParams ['view'];
		$this->conf ['lastview'] = '';
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$this->conf ['view'] = $rightsObj->checkView ($this->conf ['view']);
		$this->conf ['uid'] = $viewParams ['uid'];
		$this->conf ['type'] = $viewParams ['type'];
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getAllowedViewsByViewPid() {
		// for now, ownly check basic views.
		$allowedViews = array ();
		$regularViews = array (
				'day',
				'week',
				'month',
				'year',
				'list',
				'event',
				'location',
				'organizer' 
		);
		$feEditingViews = array (
				'event',
				'location',
				'organizer',
				'calendar',
				'category' 
		);
		$editingTypes = array (
				'create',
				'edit',
				'delete' 
		);
		
		foreach ($regularViews as $view) {
			if ($this->conf ['view.'] [$view . '.'] [$view . 'ViewPid']) {
				$allowedViews [] = $view;
			}
		}
		
		foreach ($feEditingViews as $view) {
			foreach ($editingTypes as $type) {
				if ($this->conf ['view.'] [$view . '.'] [$type . ucfirst ($view) . 'ViewPid']) {
					$allowedViews [] = $type . '_' . $view;
				}
			}
		}
		
		return $allowedViews;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function saveEvent() {
		$hookObjectsArr = $this->getHookObjectsArray ('saveEventClass');
		// Hook: preSaveEvent
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preSaveEvent');
		
		$pid = $this->conf ['rights.'] ['create.'] ['event.'] ['saveEventToPid'];
		if (! is_numeric ($pid)) {
			$pid = $GLOBALS ['TSFE']->id;
		}
		
		$eventType = intval ($this->piVars ['event_type']);
		$uid = intval ($this->piVars ['uid']);
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		
		if ($GLOBALS ['TSFE']->fe_user->getKey ('ses', 'tx_cal_controller_creatingEvent') == '1') {
			
			$event = null;
			if ($eventType == \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_TODO) {
				$event = $modelObj->saveTodo ($this->conf ['uid'], $this->conf ['type'], $pid);
			} else {
				$event = $modelObj->saveEvent ($this->conf ['uid'], $this->conf ['type'], $pid);
			}
			
			// Hook: postSaveEvent
			$this->executeHookObjectsFunction ($hookObjectsArr, 'postSaveEvent');
			
			if ($this->conf ['view.'] ['enableAjax']) {
				if (is_object ($event)) {
					if (in_array ($event->getFreq (), Array (
							'year',
							'month',
							'week',
							'day' 
					)) || ($event->getRdate () && in_array ($event->getRdateType (), Array (
							'date',
							'datetime',
							'period' 
					)))) {
						$this->conf ['view.'] [$this->conf ['view'] . '.'] ['minDate'] = $event->start->format ('%Y%m%d');
						$this->conf ['view.'] [$this->conf ['view'] . '.'] ['maxDate'] = $this->piVars ['maxDate'];
						
						$eventArray = $modelObj->findEvent ($event->getUid (), $this->conf ['type'], $this->conf ['pidList'], false, false, true, false, true, '0,1,2,3,4');
						$ajaxStringArray = array();
						$dateKeys = array_keys ($eventArray);
						foreach ($dateKeys as $dateKey) {
							$timeKeys = array_keys ($eventArray [$dateKey]);
							foreach ($timeKeys as $timeKey) {
								$eventKeys = array_keys ($eventArray [$dateKey] [$timeKey]);
								foreach ($eventKeys as $eventKey) {
									$eventX = &$eventArray [$dateKey] [$timeKey] [$eventKey];
									$ajaxStringArray [] = '{' . $this->getEventAjaxString ($eventX) . '}';
								}
							}
						}
						$ajaxString = implode (',', $ajaxStringArray);
						echo '[' . $ajaxString . ']';
					} else {
						$ajaxString = $this->getEventAjaxString ($event);
						$ajaxString = str_replace (Array (
								chr (13),
								"\n" 
						), Array (
								"",
								"" 
						), $ajaxString);
						echo '[{' . $ajaxString . '}]';
					}
				} else {
					echo '{"success": false,"errors": {text:"event was not saved"}}';
				}
			}
		}
		
		unset ($this->piVars ['type']);
		unset ($this->conf ['type']);
		$this->conf ['type'] = '';
		$this->clearConfVars ();
		
		$GLOBALS ['TSFE']->fe_user->setKey ('ses', 'tx_cal_controller_creatingEvent', '0');
		$GLOBALS ['TSFE']->storeSessionData ();
		
		$this->checkRedirect ($uid ? 'edit' : 'create', 'event');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function removeEvent() {
		$eventType = intval ($this->piVars ['event_type']);
		$hookObjectsArr = $this->getHookObjectsArray ('removeEventClass');
		// Hook: preRemoveEvent
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preRemoveEvent');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		if ($eventType == \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_TODO) {
			$ok = $modelObj->removeTodo ($this->conf ['uid'], $this->conf ['type']);
		} else {
			$ok = $modelObj->removeEvent ($this->conf ['uid'], $this->conf ['type']);
		}
		
		// Hook: postRemoveEvent
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postRemoveEvent');
		
		if ($this->conf ['view.'] ['enableAjax']) {
			return 'true';
		}
		
		$this->clearConfVars ();
		$this->checkRedirect ('delete', 'event');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function createExceptionEvent() {
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		$hookObjectsArr = $this->getHookObjectsArray ('createExceptionEventClass');
		// Hook: preCreateExceptionEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preCreateExceptionEventRendering')) {
				$hookObj->preCreateExceptionEventRendering ($this, $getdate, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnCreateExceptionEvent = $viewObj->drawCreateExceptionEvent ($getdate, $pidList);
		
		// Hook: postCreateExceptionEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postCreateExceptionEventRendering')) {
				$hookObj->postCreateExceptionEventRendering ($drawnCreateExceptionEvent, $this);
			}
		}
		
		return $drawnCreateExceptionEvent;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function saveExceptionEvent() {
		$hookObjectsArr = $this->getHookObjectsArray ('saveExceptionEventClass');
		
		// Hook: preSaveExceptionEvent
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preSaveExceptionEvent');
		
		$pid = $this->conf ['rights.'] ['create.'] ['exceptionEvent.'] ['saveExceptionEventToPid'];
		if (! is_numeric ($pid)) {
			$pid = $GLOBALS ['TSFE']->id;
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ok = $modelObj->saveExceptionEvent ($this->conf ['uid'], $this->conf ['type'], $pid);
		
		// Hook: postSaveExceptionEvent
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postSaveExceptionEvent');
		
		$this->clearConfVars ();
		$this->checkRedirect ($this->piVars ['uid'] ? 'edit' : 'create', 'exceptionEvent');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function removeCalendar() {
		$hookObjectsArr = $this->getHookObjectsArray ('removeCalendarClass');
		// Hook: preRemoveCalendar
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preRemoveCalendar');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ok = $modelObj->removeCalendar ($this->conf ['uid'], $this->conf ['type']);
		
		// Hook: postRemoveCalendar
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postRemoveCalendar');
		
		$this->clearConfVars ();
		$this->checkRedirect ('delete', 'calendar');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function removeCategory() {
		$hookObjectsArr = $this->getHookObjectsArray ('removeCategoryClass');
		// Hook: preRemoveCategory
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preRemoveCategory');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ok = $modelObj->removeCategory ($this->conf ['uid'], $this->conf ['type']);
		
		// Hook: postRemoveCategory
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postRemoveCategory');
		
		$this->clearConfVars ();
		$this->checkRedirect ('delete', 'category');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function removeLocation() {
		$hookObjectsArr = $this->getHookObjectsArray ('removeLocationClass');
		// Hook: preRemoveLocation
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preRemoveLocation');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ok = $modelObj->removeLocation ($this->conf ['uid'], $this->conf ['type']);
		
		// Hook: postRemoveLocation
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postRemoveLocation');
		
		$this->clearConfVars ();
		$this->checkRedirect ('delete', 'location');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function removeOrganizer() {
		$hookObjectsArr = $this->getHookObjectsArray ('removeOrganizerClass');
		// Hook: preRemoveOrganizer
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preRemoveOrganizer');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ok = $modelObj->removeOrganizer ($this->conf ['uid'], $this->conf ['type']);
		
		// Hook: postRemoveOrganizer
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postRemoveOrganizer');
		
		$this->clearConfVars ();
		$this->checkRedirect ('delete', 'organizer');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function saveLocation() {
		$hookObjectsArr = $this->getHookObjectsArray ('saveLocationClass');
		
		// Hook: preSaveLocation
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preSaveLocation');
		
		$pid = $this->conf ['rights.'] ['create.'] ['location.'] ['saveLocationToPid'];
		if (! is_numeric ($pid)) {
			$pid = $GLOBALS ['TSFE']->id;
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$location = $modelObj->saveLocation ($this->conf ['uid'], $this->conf ['type'], $pid);
		
		if ($this->conf ['view.'] ['enableAjax']) {
			return '{' . $this->getEventAjaxString ($location) . '}';
		}
		
		// Hook: postSaveLocation
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postSaveLocation');
		
		$this->clearConfVars ();
		$this->checkRedirect ($this->piVars ['uid'] ? 'edit' : 'create', 'location');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function saveOrganizer() {
		$hookObjectsArr = $this->getHookObjectsArray ('saveOrganizerClass');
		// Hook: preSaveOrganizer
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preSaveOrganizer');
		
		$pid = $this->conf ['rights.'] ['create.'] ['organizer.'] ['saveOrganizerToPid'];
		if (! is_numeric ($pid)) {
			$pid = $GLOBALS ['TSFE']->id;
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$organizer = $modelObj->saveOrganizer ($this->conf ['uid'], $this->conf ['type'], $pid);
		
		if ($this->conf ['view.'] ['enableAjax']) {
			return '{' . $this->getEventAjaxString ($organizer) . '}';
		}
		
		// Hook: postSaveOrganizer
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postSaveOrganizer');
		
		$this->clearConfVars ();
		$this->checkRedirect ($this->piVars ['uid'] ? 'edit' : 'create', 'organizer');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function saveCalendar() {
		$hookObjectsArr = $this->getHookObjectsArray ('saveCalendarClass');
		// Hook: preSaveCalendar
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preSaveCalendar');
		
		$pid = $this->conf ['rights.'] ['create.'] ['calendar.'] ['saveCalendarToPid'];
		if (! is_numeric ($pid)) {
			$pid = $GLOBALS ['TSFE']->id;
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$calendar = $modelObj->saveCalendar ($this->conf ['uid'], $this->conf ['type'], $pid);
		
		if ($this->conf ['view.'] ['enableAjax']) {
			
			if (is_object ($calendar)) {
				$calendar = $modelObj->findCalendar ($calendar->getUid (), $this->conf ['type'], $pid);
				$ajaxString = $this->getEventAjaxString ($calendar);
				$ajaxString = str_replace (Array (
						chr (13),
						"\n" 
				), Array (
						"",
						"" 
				), $ajaxString);
				return '{' . $ajaxString . '}';
			} else {
				return '{"success": false,"errors": {text:"calendar was not saved"}}';
			}
		}
		
		// Hook: postSaveCalendar
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postSaveCalendar');
		
		$this->clearConfVars ();
		$this->checkRedirect ($this->piVars ['uid'] ? 'edit' : 'create', 'calendar');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function saveCategory() {
		$hookObjectsArr = $this->getHookObjectsArray ('saveCategoryClass');
		
		// Hook: preSaveCategory
		$this->executeHookObjectsFunction ($hookObjectsArr, 'preSaveCategory');
		
		$pid = $this->conf ['rights.'] ['create.'] ['category.'] ['saveCategoryToPid'];
		if (! is_numeric ($pid)) {
			$pid = $GLOBALS ['TSFE']->id;
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$category = $modelObj->saveCategory ($this->conf ['uid'], $this->conf ['type'], $pid);
		
		if ($this->conf ['view.'] ['enableAjax']) {
			return '{' . $this->getEventAjaxString ($category) . '}';
		}
		
		// Hook: postSaveCategory
		$this->executeHookObjectsFunction ($hookObjectsArr, 'postSaveCategory');
		
		$this->clearConfVars ();
		$this->checkRedirect ($this->piVars ['uid'] ? 'edit' : 'create', 'category');
	}
	
	/**
	 * 
	 * @return string
	 */
	public function event() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		$emtyEventPid = $this->conf ['emtyEventPid'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawEventClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = null;
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$event = $modelObj->findEvent ($uid, $type, $pidList);
		
		if (! is_object ($event)) {
			if (is_string ($event)) {
				return $event;
			}
			// do not display error mesages - redirect to list view | by nachVORNE
			if($emtyEventPid){
				// not in extabse context -> need object manager
				$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance( 'TYPO3\CMS\Extbase\Object\ObjectManager');
				// get uri builder
				$uriBuilder = $objectManager->get( 'TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder' );
				// initialize object manager
				$uriBuilder->initializeObject();
				// reset (just in case), resets some config stuff from eventually previously configured URI related calls
				// we would rather not like to include these settings here, you never know what was configured
				$uriBuilder->reset();
				// specify the page ID for the link (from cal typoscript)
				$uriBuilder->setTargetPageUid($emtyEventPid);
				// get url (let real_url / cooluri / whatever do the magic)
				$uri = $uriBuilder->build();
				header('Location: /'.$uri);
				// $objectManager->redirectToUri($uri); // …go!
			}
			else{
				// display error message if no redirect pid is defined
				return \TYPO3\CMS\Cal\Utility\Functions::createErrorMessage ('Missing or wrong parameter. The event you are looking for could not be found.', 'Please verify your URL parameter: tx_cal_controller[uid] or configure a fallback pid (list view or a more beautiful "sorry not available" page) via typoscript – called emtyEventPid)');
			}
		}
		
		$categoryArray = implode (',', $event->getCategoryUidsAsArray ());
		$relatedEvents = Array ();
		
		if ($categoryArray != '') {
			$tempCategoryMode = $this->conf ['view.'] ['categoryMode'];
			$tempCategory = $this->conf ['view.'] ['category'];
			
			if ($tempCategoryMode == 1 || $tempCategoryMode == 3) {
				// nothing to do here
			} else {
				$this->conf ['view.'] ['categoryMode'] = 1;
			}
			$this->conf ['view.'] ['category'] = $categoryArray;
			$this->conf ['category'] = $this->conf ['view.'] ['category'];
			$relatedEvents = &$this->findRelatedEvents ('event', ' AND tx_cal_event.uid != ' . $event->getUid ());
			
			$this->conf ['view.'] ['categoryMode'] = $tempCategoryMode;
			$this->conf ['view.'] ['category'] = $tempCategory;
			$this->conf ['category'] = $this->conf ['view.'] ['category'];
		}
		
		// Hook: preEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preEventRendering')) {
				$hookObj->preEventRendering ($event, $relatedEvents, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnEvent = $viewObj->drawEvent ($event, $getdate, $relatedEvents);
		
		// Hook: postEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postEventRendering')) {
				$hookObj->postEventRendering ($drawnEvent, $event, $relatedEvents, $this);
			}
		}
		
		return $drawnEvent;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function day() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawDayClass');
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		$timeObj = new \TYPO3\CMS\Cal\Model\CalDate ($this->conf ['getdate'] . '000000');
		$timeObj->setTZbyId ('UTC');
		$master_array = $modelObj->findEventsForDay ($timeObj, $type, $pidList);
		// Hook: preDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preDayRendering')) {
				$hookObj->preDayRendering ($master_array, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnDay = $viewObj->drawDay ($master_array, $getdate);
		// Hook: postDayRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postDayRendering')) {
				$hookObj->postDayRendering ($drawnDay, $master_array, $this);
			}
		}
		
		return $drawnDay;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function week() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawWeekClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		$timeObj = new \TYPO3\CMS\Cal\Model\CalDate ($this->conf ['getdate'] . '000000');
		$timeObj->setTZbyId ('UTC');
		$master_array = $modelObj->findEventsForWeek ($timeObj, $type, $pidList);
		// Hook: preWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preWeekRendering')) {
				$hookObj->preWeekRendering ($master_array, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnWeek = $viewObj->drawWeek ($master_array, $getdate);
		// Hook: postWeekRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postWeekRendering')) {
				$hookObj->postWeekRendering ($drawnWeek, $master_array, $this);
			}
		}
		
		return $drawnWeek;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function month() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawMonthClass');
		
		if ($this->conf ['view.'] ['enableAjax']) {
			$master_array = Array ();
		} else {
			
			$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
			$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
			if (! in_array ($type, $availableTypes)) {
				$type = '';
			}
			
			$timeObj = new \TYPO3\CMS\Cal\Model\CalDate ($this->conf ['getdate'] . '000000');
			$timeObj->setTZbyId ('UTC');
			$master_array = $modelObj->findEventsForMonth ($timeObj, $type, $pidList);
		}
		// Hook: preMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preMonthRendering')) {
				$hookObj->preMonthRendering ($master_array, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnMonth = $viewObj->drawMonth ($master_array, $getdate);
		// Hook: postMonthRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postMonthRendering')) {
				$hookObj->postMonthRendering ($drawnMonth, $master_array, $this);
			}
		}
		return $drawnMonth;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function year() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawYearClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		$timeObj = new \TYPO3\CMS\Cal\Model\CalDate ($this->conf ['getdate'] . '000000');
		$timeObj->setTZbyId ('UTC');
		$master_array = $modelObj->findEventsForYear ($timeObj, $type, $pidList);
		// Hook: preYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preYearRendering')) {
				$hookObj->preYearRendering ($master_array, $this);
			}
		}
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnYear = $viewObj->drawYear ($master_array, $getdate);
		// Hook: postYearRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postYearRendering')) {
				$hookObj->postYearRendering ($drawnYear, $master_array, $this);
			}
		}
		
		return $drawnYear;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function ics() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawIcsClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$master_array = $modelObj->findEventsForIcs ($type, $pidList);
		
		// Hook: preIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preIcsRendering')) {
				$hookObj->preIcsRendering ($master_array, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnIcs = $viewObj->drawIcs ($master_array, $this->conf ['getdate']);
		
		// Hook: postIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postIcsRendering')) {
				$hookObj->postIcsRendering ($drawnIcs, $master_array, $this);
			}
		}
		
		return $drawnIcs;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function singleIcs() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSingleIcsClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$master_array = array (
				$modelObj->findEvent ($uid, $type, $pidList) 
		); // $this->conf['pid_list']));
		                                                                     
		// Hook: preSingleIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSingleIcsRendering')) {
				$hookObj->preSingleIcsRendering ($master_array, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnIcs = $viewObj->drawIcs ($master_array, $getdate);
		
		// Hook: postSingleIcsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSingleIcsRendering')) {
				$hookObj->postSingleIcsRendering ($drawnIcs, $master_array, $this);
			}
		}
		
		return $drawnIcs;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function rss() {
		$type = $this->conf ['type'];
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		if ($pidList == 0) {
			return 'Please define plugin.tx_cal_controller.pidList in constants';
		}
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawRssClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$starttime = \TYPO3\CMS\Cal\Controller\Calendar::calculateStartDayTime ($this->getDateTimeObject);
		$endtime = new \TYPO3\CMS\Cal\Model\CalDate ();
		$endtime->copy ($starttime);
		$endtime->addSeconds ($this->conf ['view.'] ['rss.'] ['range'] * 86400);
		$master_array = $modelObj->findEventsForRss ($starttime, $endtime, $type, $pidList); // $this->conf['pid_list']);
		                                                                                    
		// Hook: preRssRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preRssRendering')) {
				$hookObj->preRssRendering ($master_array, $starttime, $endtime, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnIcs = $viewObj->drawRss ($master_array, $getdate);
		
		// Hook: postRssRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postRssRendering')) {
				$hookObj->postRssRendering ($drawnIcs, $master_array, $starttime, $endtime, $this);
			}
		}
		
		return $drawnIcs;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function location() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawLocationClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_location_model', 'location');
		
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$location = $modelObj->findLocation ($uid, $type, $pidList);
		if (! is_object ($location)) {
			if (is_string ($location)) {
				return $location;
			}
			return \TYPO3\CMS\Cal\Utility\Functions::createErrorMessage ('Missing or wrong parameter. The location you are looking for could not be found.', 'Please verify your URL parameter: tx_cal_controller[uid]');
		}
		
		if ($this->conf ['view.'] ['enableAjax']) {
			return '{' . $this->getEventAjaxString ($location) . '}';
		}
		$relatedEvents = &$this->findRelatedEvents ('location', ' AND location_id = ' . $uid);
		
		// Hook: preLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLocationRendering')) {
				$hookObj->preLocationRendering ($location, $relatedEvents, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnLocation = $viewObj->drawLocation ($location, $relatedEvents);
		
		// Hook: postLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLocationRendering')) {
				$hookObj->postLocationRendering ($drawnLocation, $location, $this);
			}
		}
		
		return $drawnLocation;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function organizer() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawOrganizerClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_organizer_model', 'organizer');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$organizer = $modelObj->findOrganizer ($uid, $type, $pidList);
		if (! is_object ($organizer)) {
			if (is_string ($organizer)) {
				return $organizer;
			}
			return \TYPO3\CMS\Cal\Utility\Functions::createErrorMessage ('Missing or wrong parameter. The organizer you are looking for could not be found.', 'Please verify your URL parameter: tx_cal_controller[uid]');
		}
		$relatedEvents = &$this->findRelatedEvents ('organizer', ' AND organizer_id = ' . $uid);
		
		// Hook: preOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preOrganizerRendering')) {
				$hookObj->preOrganizerRendering ($organizer, $relatedEvents, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnOrganizer = $viewObj->drawOrganizer ($organizer, $relatedEvents);
		
		// Hook: postOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postOrganizerRendering')) {
				$hookObj->postOrganizerRendering ($drawnOrganizer, $organizer, $this);
			}
		}
		return $drawnOrganizer;
	}
	
	/**
	 * Calculates the time for list view start and end times.
	 * 
	 * @param
	 *        	string		The string representing the relative time.
	 * @param
	 *        	integer		The starting point that timeString is relative to.
	 * @return integer for list view start or end time.
	 */
	public function getListViewTime($timeString, $timeObj = '') {
		$dp = new \TYPO3\CMS\Cal\Controller\DateParser ();
		$dp->parse ($timeString, $this->conf ['dateParserConf.'], $timeObj);
		return $dp->getDateObjectFromStack ();
	}
	
	/**
	 * 
	 * @return string
	 */
	public function listview() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawListClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$starttimePreset = $this->cObj->stdWrap ($this->conf ['view.'] ['list.'] ['starttime'], $this->conf ['view.'] ['list.'] ['starttime.']);
		$endtimePreset = $this->cObj->stdWrap ($this->conf ['view.'] ['list.'] ['endtime'], $this->conf ['view.'] ['list.'] ['endtime.']);
		
		$starttime = $this->getListViewTime ($starttimePreset);
		$endtime = $this->getListViewTime ($endtimePreset);
		
		if (! $this->conf ['view.'] ['list.'] ['useGetdate']) {
			// do nothing - removed "continue" at this point, due to #543
		} else if ($this->conf ['view'] == 'list' && ! $this->conf ['view.'] ['list.'] ['doNotUseGetdateTheFirstTime'] && $this->conf ['getdate']) {
			if ($this->conf ['view.'] ['list.'] ['useCustomStarttime']) {
				if ($this->conf ['view.'] ['list.'] ['customStarttimeRelativeToGetdate']) {
					$starttime = $this->getListViewTime ($starttimePreset, $this->getDateTimeObject);
				} // on't parse the starttime twice as it done just a few lines above
				/*
				 * else { $starttime = $this->getListViewTime($starttimePreset); }
				 */
			} else {
				$starttime = \TYPO3\CMS\Cal\Controller\Calendar::calculateStartDayTime ($this->getDateTimeObject);
			}
			
			if ($this->conf ['view.'] ['list.'] ['useCustomEndtime']) {
				if ($this->conf ['view.'] ['list.'] ['customEndtimeRelativeToGetdate']) {
					$endtime = $this->getListViewTime ($endtimePreset, $this->getDateTimeObject);
				} // on't parse the endtime twice as it done just a few lines above
				/*
				 * else { $endtime = $this->getListViewTime($endtimePreset); }
				 */
			} else {
				if ($this->conf ['view.'] ['list.'] ['useCustomStarttime']) {
					// if we have a custom starttime but use getdate, calculate the endtime based on the getdate and not on the changed startdate
					$endtime = \TYPO3\CMS\Cal\Controller\Calendar::calculateStartDayTime ($this->getDateTimeObject);
				} else {
					$endtime = new \TYPO3\CMS\Cal\Model\CalDate ();
					$endtime->copy ($starttime);
				}
				$endtime->addSeconds (86340);
			}
		}
		
		$list = $modelObj->findEventsForList ($starttime, $endtime, $type, $pidList);
		
		// Hook: preListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preListRendering')) {
				$hookObj->preListRendering ($list, $starttime, $endtime, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnList = $viewObj->drawList ($list, $starttime, $endtime);
		
		// Hook: postListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postListRendering')) {
				$hookObj->postListRendering ($drawnList, $list, $starttime, $endtime, $this);
			}
		}
		
		return $drawnList;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function icslist() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawIcsListClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$list = $modelObj->findCategoriesForList ($type, $pidList);
		
		// Hook: preIcsListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preIcsListRendering')) {
				$hookObj->preIcsListRendering ($list, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnList = $viewObj->drawIcsList ($list, $getdate);
		
		// Hook: postIcsListRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postIcsListRendering')) {
				$hookObj->postIcsListRendering ($drawnList, $list, $this);
			}
		}
		
		return $drawnList;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function admin() {
		$hookObjectsArr = $this->getHookObjectsArray ('drawAdminClass');
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnPage = $viewObj->drawAdminPage ();
		
		// Hook: postAdminRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postAdminRendering')) {
				$hookObj->postAdminRendering ($drawnPage, $this);
			}
		}
		
		return $drawnPage;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function searchEvent() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSearchClass');
		
		$start_day = $this->piVars ['start_day'];
		$end_day = $this->piVars ['end_day'];
		$searchword = preg_replace('/["\']/', '', strip_tags ($this->piVars ['query']));
		$this->piVars ['query'] = $searchword;
		
		if (! $start_day) {
			$start_day = $this->getListViewTime ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['start_day']);
			$start_day = \TYPO3\CMS\Cal\Controller\Calendar::calculateStartDayTime ($start_day);
		} else {
			$start_day = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getYmdFromDateString ($this->conf, $start_day) . '000000');
			$start_day->setHour (0);
			$start_day->setMinute (0);
			$start_day->setSecond (0);
			$start_day->setTZbyId ('UTC');
		}
		if (! $end_day) {
			$end_day = $this->getListViewTime ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['end_day']);
			$end_day = \TYPO3\CMS\Cal\Controller\Calendar::calculateEndDayTime ($end_day);
		} else {
			$end_day = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getYmdFromDateString ($this->conf, $end_day) . '000000');
			$end_day->setHour (23);
			$end_day->setMinute (59);
			$end_day->setSecond (59);
			$end_day->setTZbyId ('UTC');
		}
		if ($this->piVars ['single_date']) {
			$start_day = new \TYPO3\CMS\Cal\Model\CalDate (\TYPO3\CMS\Cal\Utility\Functions::getYmdFromDateString ($this->conf, $this->piVars ['single_date']));
			$start_day->setHour (0);
			$start_day->setMinute (0);
			$start_day->setSecond (0);
			$start_day->setTZbyId ('UTC');
			$end_day = new \TYPO3\CMS\Cal\Model\CalDate ();
			$end_day->copy ($start_day);
			$end_day->addSeconds (86399);
		}
		
		$minStarttime = new \TYPO3\CMS\Cal\Model\CalDate ($this->conf ['view.'] ['search.'] ['startRange'] . '000000');
		$maxEndtime = new \TYPO3\CMS\Cal\Model\CalDate ($this->conf ['view.'] ['search.'] ['endRange'] . '000000');
		
		if ($start_day->before ($minStarttime)) {
			$start_day->copy ($minStarttime);
		}
		if ($start_day->after ($maxEndtime)) {
			$start_day->copy ($maxEndtime);
		}
		
		if ($end_day->before ($minStarttime)) {
			$end_day->copy ($minStarttime);
		}
		if ($end_day->after ($maxEndtime)) {
			$end_day->copy ($maxEndtime);
		}
		if ($end_day->before ($start_day)) {
			$end_day->copy ($start_day);
		}
		
		$locationIds = strip_tags (Controller::convertLinkVarArrayToList ($this->piVars ['location_ids']));
		$organizerIds = strip_tags (Controller::convertLinkVarArrayToList ($this->piVars ['organizer_ids']));
		
		$this->getDateTimeObject->copy ($start_day);
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		
		$list = Array ();
		if ($this->piVars ['submit'] || ! $this->conf ['view.'] ['search.'] ['startSearchAfterSubmit']) {
			$list = $modelObj->searchEvents ($type, $pidList, $start_day, $end_day, $searchword, $locationIds, $organizerIds);
		}
		
		// Hook: preSearchEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSearchEventRendering')) {
				$hookObj->preSearchEventRendering ($list, $this);
			}
		}
		
		if ($this->conf ['view.'] ['enableAjax']) {
			$ajaxStringArray = Array ();
			foreach ($list as $event) {
				$ajaxStringArray [] = '{' . $this->getEventAjaxString ($event) . '}';
			}
			return '[' . implode (',', $ajaxStringArray) . ']';
		}
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnList = $viewObj->drawSearchEventResult ($list, $start_day, $end_day, $searchword, $locationIds, $organizerIds);
		
		// Hook: postSearchEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchEventRendering')) {
				$hookObj->postSearchEventRendering ($drawnList, $list, $this);
			}
		}
		
		return $drawnList;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function createEvent() {
		$getDate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('createEventClass');
		
		// Hook: preCreateEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preCreateEventRendering')) {
				$hookObj->preCreateEventRendering ($this, $getDate, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnCreateEvent = $viewObj->drawCreateEvent ($getDate, $pidList);
		
		// Hook: postCreateEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postCreateEventRendering')) {
				$hookObj->postCreateEventRendering ($drawnCreateEvent, $this);
			}
		}
		
		return $drawnCreateEvent;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function confirmEvent() {
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('confirmEventClass');
		
		// Hook: preConfirmEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preConfirmEventRendering')) {
				$hookObj->preConfirmEventRendering ($this, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnConfirmEvent = $viewObj->drawConfirmEvent ($pidList);
		
		// Hook: postConfirmEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postConfirmEventRendering')) {
				$hookObj->postConfirmEventRendering ($drawnConfirmEvent, $this);
			}
		}
		
		return $drawnConfirmEvent;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function editEvent() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('editEventClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$event = $modelObj->findEvent ($uid, $type, $pidList);
		
		// Hook: preEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preEditEventRendering')) {
				$hookObj->preEditEventRendering ($this, $event, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnEditEvent = $viewObj->drawEditEvent ($event, $pidList);
		
		// Hook: postEditEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postEditEventRendering')) {
				$hookObj->postEditEventRendering ($drawnEditEvent, $this);
			}
		}
		
		return $drawnEditEvent;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function deleteEvent() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('deleteEventClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$event = $modelObj->findEvent ($uid, $type, $pidList);
		
		// Hook: preDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preDeleteEventRendering')) {
				$hookObj->preDeleteEventRendering ($this, $event, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnDeleteEvent = $viewObj->drawDeleteEvent ($event, $pidList);
		
		// Hook: postDeleteEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postDeleteEventRendering')) {
				$hookObj->postDeleteEventRendering ($drawnDeleteEvent, $this);
			}
		}
		
		return $drawnDeleteEvent;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function createLocation() {
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('createLocationClass');
		
		// Hook: preCreateLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preCreateLocationRendering')) {
				$hookObj->preCreateLocationRendering ($this, $getdate, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnCreateLocation = $viewObj->drawCreateLocation ($pidList);
		
		// Hook: postCreateLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postCreateLocationRendering')) {
				$hookObj->postCreateLocationRendering ($drawnCreateLocation, $this);
			}
		}
		
		return $drawnCreateLocation;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function confirmLocation() {
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('confirmLocationClass');
		
		// Hook: preConfirmLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preConfirmLocationRendering')) {
				$hookObj->preConfirmLocationRendering ($this, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnConfirmLocation = $viewObj->drawConfirmLocation ($pidList);
		
		// Hook: postConfirmLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postConfirmLocationRendering')) {
				$hookObj->postConfirmLocationRendering ($drawnConfirmLocation, $this);
			}
		}
		
		return $drawnConfirmLocation;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function editLocation() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('editLocationClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$location = $modelObj->findLocation ($uid, $type, $pidList);
		
		// Hook: preEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preEditLocationRendering')) {
				$hookObj->preEditLocationRendering ($this, $location, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnEditLocation = $viewObj->drawEditLocation ($location, $pidList);
		
		// Hook: postEditLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postEditLocationRendering')) {
				$hookObj->postEditLocationRendering ($drawnEditLocation, $this);
			}
		}
		
		return $drawnEditLocation;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function deleteLocation() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('deleteLocationClass');
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$location = $modelObj->findLocation ($uid, $type, $pidList);
		
		// Hook: preDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preDeleteLocationRendering')) {
				$hookObj->preDeleteLocationRendering ($this, $location, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnDeleteLocation = $viewObj->drawDeleteLocation ($location, $pidList);
		
		// Hook: postDeleteLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postDeleteLocationRendering')) {
				$hookObj->postDeleteLocationRendering ($drawnDeleteLocation, $this);
			}
		}
		
		return $drawnDeleteLocation;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function createOrganizer() {
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('createOrganizerClass');
		
		// Hook: preCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preCreateOrganizerRendering')) {
				$hookObj->preCreateOrganizerRendering ($this, $getdate, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnCreateOrganizer = $viewObj->drawCreateOrganizer ($pidList);
		
		// Hook: postCreateOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postCreateOrganizerRendering')) {
				$hookObj->postCreateOrganizerRendering ($drawnCreateOrganizer, $this);
			}
		}
		
		return $drawnCreateOrganizer;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function confirmOrganizer() {
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('confirmOrganizerClass');
		
		// Hook: preConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preConfirmOrganizerRendering')) {
				$hookObj->preConfirmOrganizerRendering ($this, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnConfirmOrganizer = $viewObj->drawConfirmOrganizer ($pidList);
		
		// Hook: postConfirmOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postConfirmOrganizerRendering')) {
				$hookObj->postConfirmOrganizerRendering ($drawnConfirmOrganizer, $this);
			}
		}
		
		return $drawnConfirmOrganizer;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function editOrganizer() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('editOrganizerClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$organizer = $modelObj->findOrganizer ($uid, $type, $pidList);
		
		// Hook: preEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preEditOrganizerRendering')) {
				$hookObj->preEditOrganizerRendering ($this, $organizer, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnEditOrganizer = $viewObj->drawEditOrganizer ($organizer, $pidList);
		
		// Hook: postEditOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postEditOrganizerRendering')) {
				$hookObj->postEditOrganizerRendering ($drawnEditOrganizer, $this);
			}
		}
		
		return $drawnEditOrganizer;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function deleteOrganizer() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('deleteOrganizerClass');
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$organizer = $modelObj->findOrganizer ($uid, $type, $pidList);
		
		// Hook: preDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preDeleteOrganizerRendering')) {
				$hookObj->preDeleteOrganizerRendering ($this, $organizer, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnDeleteOrganizer = $viewObj->drawDeleteOrganizer ($organizer, $pidList);
		
		// Hook: postDeleteOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postDeleteOrganizerRendering')) {
				$hookObj->postDeleteOrganizerRendering ($drawnDeleteOrganizer, $this);
			}
		}
		
		return $drawnDeleteOrganizer;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function createCalendar() {
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('createCalendarClass');
		
		// Hook: preCreateCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preCreateCalendarRendering')) {
				$hookObj->preCreateCalendarRendering ($this, $getdate, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnCreateCalendar = $viewObj->drawCreateCalendar ($pidList);
		
		// Hook: postCreateCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postCreateCalendarRendering')) {
				$hookObj->postCreateCalendarRendering ($drawnCreateCalendar, $this);
			}
		}
		
		return $drawnCreateCalendar;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function confirmCalendar() {
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('confirmCalendarClass');
		
		// Hook: preConfirmCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preConfirmCalendarRendering')) {
				$hookObj->preConfirmCalendarRendering ($this, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnConfirmCalendar = $viewObj->drawConfirmCalendar ($pidList);
		
		// Hook: postConfirmCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postConfirmCalendarRendering')) {
				$hookObj->postConfirmCalendarRendering ($drawnConfirmCalendar, $this);
			}
		}
		
		return $drawnConfirmCalendar;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function editCalendar() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('editCalendarClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$calendar = $modelObj->findCalendar ($uid, $type, $pidList);
		
		// Hook: preEditCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preEditCalendarRendering')) {
				$hookObj->preEditCalendarRendering ($this, $calendar, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnEditCalendar = $viewObj->drawEditCalendar ($calendar, $pidList);
		
		// Hook: postEditCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postEditCalendarRendering')) {
				$hookObj->postEditCalendarRendering ($drawnEditCalendar, $this);
			}
		}
		
		return $drawnEditCalendar;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function deleteCalendar() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('deleteCalendarClass');
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$calendar = $modelObj->findCalendar ($uid, $type, $pidList);
		
		// Hook: preDeleteCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preDeleteCalendarRendering')) {
				$hookObj->preDeleteCalendarRendering ($this, $calendar, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnDeleteCalendar = $viewObj->drawDeleteCalendar ($calendar, $pidList);
		
		// Hook: postDeleteCalendarRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postDeleteCalendarRendering')) {
				$hookObj->postDeleteCalendarRendering ($drawnDeleteCalendar, $this);
			}
		}
		
		return $drawnDeleteCalendar;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function createCategory() {
		$getdate = $this->conf ['getdate'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('createCategoryClass');
		
		// Hook: preCreateCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preCreateCategoryRendering')) {
				$hookObj->preCreateCategoryRendering ($this, $getdate, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnCreateCategory = $viewObj->drawCreateCategory ($pidList);
		
		// Hook: postCreateCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postCreateCategoryRendering')) {
				$hookObj->postCreateCategoryRendering ($drawnCreateCategory, $this);
			}
		}
		
		return $drawnCreateCategory;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function confirmCategory() {
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('confirmCategoryClass');
		
		// Hook: preConfirmCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preConfirmCategoryRendering')) {
				$hookObj->preConfirmCategoryRendering ($this, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnConfirmCategory = $viewObj->drawConfirmCategory ($pidList);
		
		// Hook: postConfirmCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postConfirmCategoryRendering')) {
				$hookObj->postConfirmCategoryRendering ($drawnConfirmCategory, $this);
			}
		}
		
		return $drawnConfirmCategory;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function editCategory() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('editCategoryClass');
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$category = $modelObj->findCategory ($uid, $type, $pidList);
		
		// Hook: preEditCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preEditCategoryRendering')) {
				$hookObj->preEditCategoryRendering ($this, $category, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnEditCategory = $viewObj->drawEditCategory ($category, $pidList);
		
		// Hook: postEditCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postEditCategoryRendering')) {
				$hookObj->postEditCategoryRendering ($drawnEditCategory, $this);
			}
		}
		
		return $drawnEditCategory;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function deleteCategory() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('deleteCategoryClass');
		
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$category = $modelObj->findCategory ($uid, $type, $pidList);
		
		// Hook: preDeleteCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preDeleteCategoryRendering')) {
				$hookObj->preDeleteCategoryRendering ($this, $category, $pidList);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnDeleteCategory = $viewObj->drawDeleteCategory ($category, $pidList);
		
		// Hook: postDeleteCategoryRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postDeleteCategoryRendering')) {
				$hookObj->postDeleteCategoryRendering ($drawnDeleteCategory, $this);
			}
		}
		
		return $drawnDeleteCategory;
	}
	
	/**
	 * 
	 * @return string|unknown
	 */
	public function searchAll() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSearchAllClass');
		
		if (intval ($this->piVars ['start_day']) == 0) {
			$starttime = $this->getListViewTime ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['start_day']);
		} else {
			$starttime = new \TYPO3\CMS\Cal\Model\CalDate (intval ($this->piVars ['start_day']) . '000000');
		}
		if (intval ($this->piVars ['end_day']) == 0) {
			$endtime = $this->getListViewTime ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['end_day']);
		} else {
			$endtime = new \TYPO3\CMS\Cal\Model\CalDate (intval ($this->piVars ['end_day']) . '000000');
		}
		$searchword = strip_tags ($this->piVars ['query']);
		if ($searchword == '') {
			$searchword = $this->cObj->stdWrap ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['query'], $this->conf ['view.'] ['search.'] ['event.'] ['defaultValues.'] ['query.']);
		}
		$endtime->addSeconds (86399);
		
		/* Get the boundaries for allowed search dates */
		$minStarttime = new \TYPO3\CMS\Cal\Model\CalDate (intval ($this->conf ['view.'] ['search.'] ['startRange']) . '000000');
		$maxEndtime = new \TYPO3\CMS\Cal\Model\CalDate (intval ($this->conf ['view.'] ['search.'] ['endRange']) . '000000');
		
		/* Check starttime against boundaries */
		if ($starttime->before ($minStarttime)) {
			$starttime->copy ($minStarttime);
		}
		if ($starttime->after ($maxEndtime)) {
			$starttime->copy ($maxEndtime);
		}
		
		/* Check endtime against boundaries */
		if ($endtime->before ($minStarttime)) {
			$endtime->copy ($minStarttime);
		}
		if ($endtime->after ($maxEndtime)) {
			$endtime->copy ($maxEndtime);
		}
		
		/* Check endtime against starttime */
		if ($endtime->before ($starttime)) {
			$endtime->copy ($starttime);
		}
		
		$locationIds = strip_tags (Controller::convertLinkVarArrayToList ($this->piVars ['location_ids']));
		$organizerIds = strip_tags (Controller::convertLinkVarArrayToList ($this->piVars ['organizer_ids']));
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$list = array ();
		if ($this->piVars ['query'] && ($this->piVars ['submit'] || ! $this->conf ['view.'] ['search.'] ['startSearchAfterSubmit'])) {
			$list ['phpicalendar_event'] = $modelObj->searchEvents ($type, $pidList, $starttime, $endtime, $searchword, $locationIds, $organizerIds);
			$list ['location'] = $modelObj->searchLocation ($type, $pidList, $searchword);
			$list ['organizer'] = $modelObj->searchOrganizer ($type, $pidList, $searchword);
		}
		
		// Hook: preSearchAllRendering
		if (is_array ($hookObjectsArr)) {
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists ($hookObj, 'preSearchAllRendering')) {
					$hookObj->preSearchAllRendering ($list, $this);
				}
			}
		}
		
		if ($this->conf ['view.'] ['enableAjax']) {
			$ajaxStringArray = Array ();
			foreach ($list as $location) {
				$ajaxStringArray [] = '{' . $this->getEventAjaxString ($location) . '}';
			}
			return '[' . implode (',', $ajaxStringArray) . ']';
		}
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnList = $viewObj->drawSearchAllResult ($list, $starttime, $endtime, $searchword, $locationIds, $organizerIds);
		
		// Hook: postSearchAllRendering
		if (is_array ($hookObjectsArr)) {
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists ($hookObj, 'postSearchAllRendering')) {
					$hookObj->postSearchAllRendering ($drawnList, $list, $this);
				}
			}
		}
		return $drawnList;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function searchLocation() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSearchLocationClass');
		
		$searchword = strip_tags ($this->piVars ['query']);
		if ($searchword == '') {
			$searchword = $this->cObj->stdWrap ($this->conf ['view.'] ['search.'] ['location.'] ['defaultValues.'] ['query'], $this->conf ['view.'] ['search.'] ['location.'] ['defaultValues.'] ['query.']);
			if ($searchword == '') {
				//
			}
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$list = $modelObj->searchLocation ($type, $pidList, $searchword);
		
		// Hook: preSearchLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSearchLocationRendering')) {
				$hookObj->preSearchLocationRendering ($list, $this);
			}
		}
		
		if ($this->conf ['view.'] ['enableAjax']) {
			$ajaxStringArray = Array ();
			foreach ($list as $location) {
				$ajaxStringArray [] = '{' . $this->getEventAjaxString ($location) . '}';
			}
			return '[' . implode (',', $ajaxStringArray) . ']';
		}
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnList = $viewObj->drawSearchLocationResult ($list, $searchword);
		
		// Hook: postSearchLocationRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchLocationRendering')) {
				$hookObj->postSearchLocationRendering ($drawnList, $list, $this);
			}
		}
		
		return $drawnList;
	}
	
	/**
	 * 
	 * @return string|unknown
	 */
	public function searchOrganizer() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSearchOrganizerClass');
		
		$searchword = strip_tags ($this->piVars ['query']);
		if ($searchword == '') {
			$searchword = $this->cObj->stdWrap ($this->conf ['view.'] ['search.'] ['organizer.'] ['defaultValues.'] ['query'], $this->conf ['view.'] ['search.'] ['organizer.'] ['defaultValues.'] ['query.']);
			if ($searchword == '') {
				//
			}
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$list = $modelObj->searchOrganizer ($type, $pidList, $searchword);
		
		// Hook: preSearchOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSearchOrganizerRendering')) {
				$hookObj->preSearchOrganizerRendering ($list, $this);
			}
		}
		
		if ($this->conf ['view.'] ['enableAjax']) {
			$ajaxStringArray = Array ();
			foreach ($list as $organizer) {
				$ajaxStringArray [] = '{' . $this->getEventAjaxString ($organizer) . '}';
			}
			return '[' . implode (',', $ajaxStringArray) . ']';
		}
		
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnList = $viewObj->drawSearchOrganizerResult ($list, $searchword);
		
		// Hook: postSearchOrganizerRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchOrganizerRendering')) {
				$hookObj->postSearchOrganizerRendering ($drawnList, $list, $this);
			}
		}
		
		return $drawnList;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function searchUserAndGroup() {
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSearchUserAndGroupClass');
		
		$searchword = strip_tags ($this->piVars ['query']);
		$allowedUsers = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['allowedUsers'], 1);
		
		$additionalWhere = '';
		if (count ($allowedUsers) > 0) {
			$additionalWhere = ' AND uid in (' . implode (',', $allowedUsers) . ')';
		}
		
		if ($searchword != '') {
			$additionalWhere .= $this->cObj->searchWhere ($searchword, $this->conf ['view.'] ['search.'] ['searchUserFieldList'], 'fe_users');
		}
		
		// Hook: preSearchUser
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSearchUser')) {
				$hookObj->preSearchUser ($additionalWhere, $this);
			}
		}
		
		$userList = Array ();
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', 'pid in (' . $this->conf ['pidList'] . ')' . $additionalWhere);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				unset ($row ['username']);
				unset ($row ['password']);
				$userList [] = $row;
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		
		// Hook: postSearchUser
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchUser')) {
				$hookObj->postSearchUser ($userList, $this);
			}
		}
		
		$additionalWhere = '';
		
		$allowedGroups = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['allowedGroups'], 1);
		if (count ($allowedUsers) > 0) {
			$additionalWhere = ' AND uid in (' . implode (',', $allowedGroups) . ')';
		}
		
		if ($searchword != '') {
			$additionalWhere .= $this->cObj->searchWhere ($searchword, $this->conf ['view.'] ['search.'] ['searchGroupFieldList'], 'fe_groups');
		}
		
		// Hook: preSearchGroup
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSearchGroup')) {
				$hookObj->preSearchGroup ($additionalWhere, $this);
			}
		}
		
		$groupList = Array ();
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_groups', 'pid in (' . $this->conf ['pidList'] . ')' . $additionalWhere);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$groupList [] = $row;
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		
		// Hook: postSearchGroup
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchGroup')) {
				$hookObj->postSearchGroup ($groupList, $this);
			}
		}
		
		$ajaxUserStringArray = Array ();
		foreach ($userList as $user) {
			$ajaxUserStringArray [] = '{' . $this->getEventAjaxString ($user) . '}';
		}
		$ajaxGroupStringArray = Array ();
		foreach ($groupList as $group) {
			$ajaxGroupStringArray [] = '{' . $this->getEventAjaxString ($group) . '}';
		}
		return '{"fe_users":[' . implode (',', $ajaxUserStringArray) . '],"fe_groups":[' . implode (',', $ajaxGroupStringArray) . ']}';
	}
	
	/**
	 * 
	 * @return unknown
	 */
	public function subscription() {
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawSubscriptionClass');
		
		// Hook: preSubscriptionRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preSubscriptionRendering')) {
				$hookObj->preSubscriptionRendering ($this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnSubscriptionManager = $viewObj->drawSubscriptionManager ();
		
		// Hook: postSubscriptionRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSubscriptionRendering')) {
				$hookObj->postSubscriptionRendering ($drawnSubscriptionManager, $this);
			}
		}
		
		return $drawnSubscriptionManager;
	}
	
	/**
	 * 
	 * @return unknown
	 */
	public function meeting() {
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawMeetingClass');
		
		// Hook: preMeetingRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preMeetingRendering')) {
				$hookObj->preMeetingRendering ($this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnMeetingManager = $viewObj->drawMeetingManager ();
		
		// Hook: postMeetingRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postMeetingRendering')) {
				$hookObj->postMeetingRendering ($drawnMeetingManager, $this);
			}
		}
		
		return $drawnMeetingManager;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function translation() {
		$type = $this->conf ['type'];
		$overlay = intval ($this->piVars ['overlay']);
		$uid = $this->conf ['uid'];
		$servicename = $this->piVars ['servicename'];
		$subtype = $this->piVars ['subtype'];
		if ($overlay > 0 && $uid > 0) {
			$hookObjectsArr = $this->getHookObjectsArray ('createTranslationClass');
			
			// Hook: preCreateTranslation
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists ($hookObj, 'preCreateTranslation')) {
					$hookObj->preCreateTranslation ($this);
				}
			}
			$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
			$modelObj->createTranslation ($uid, $overlay, $servicename, $type, $subtype);
			
			// Hook: postCreateTranslation
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists ($hookObj, 'postCreateTranslation')) {
					$hookObj->postCreateTranslation ($this);
				}
			}
		}
		unset ($this->piVars ['overlay']);
		unset ($this->piVars ['servicename']);
		unset ($this->piVars ['subtype']);
		$viewParams = $this->shortenLastViewAndGetTargetViewParameters (false);
		$this->conf ['view'] = $viewParams ['view'];
		$this->conf ['lastview'] = $viewParams ['lastview'];
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$this->conf ['view'] = $rightsObj->checkView ($this->conf ['view']);
		$this->conf ['uid'] = $viewParams ['uid'];
		$this->conf ['type'] = $viewParams ['type'];
		return '';
	}
	
	/**
	 * 
	 * @return unknown
	 */
	public function todo() {
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$getdate = $this->conf ['getdate'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawTodoClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$todoSubtype = $confArr ['todoSubtype'];
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', $todoSubtype);
		
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$todo = $modelObj->findTodo ($uid, $type, $pidList);
		
		// Hook: preTodoRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preTodoRendering')) {
				$hookObj->preTodoRendering ($todo, $this);
			}
		}
		$viewObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'viewcontroller');
		$drawnTodo = $viewObj->drawEvent ($todo, $getdate);
		
		// Hook: postTodoRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postTodoRendering')) {
				$hookObj->postTodoRendering ($drawnTodo, $todo, $this);
			}
		}
		
		return $drawnTodo;
	}
	
	/**
	 * 
	 * @return unknown
	 */
	public function loadEvents() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadEventsClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		
		if (! $this->piVars ['start']) {
			$this->piVars ['start'] = $confArr ['recurrenceStart'];
		}
		$startObj = new \TYPO3\CMS\Cal\Model\CalDate ($this->piVars ['start'] . '000000');
		$startObj->setTZbyId ('UTC');
		
		if (! $this->piVars ['end']) {
			$this->piVars ['end'] = $confArr ['recurrenceEnd'];
		}
		
		$endObj = new \TYPO3\CMS\Cal\Model\CalDate ($this->piVars ['end'] . '000000');
		$endObj->setTZbyId ('UTC');
		$eventTypes = '0,1,2,3';
		
		if ($confArr ['todoSubtype'] == 'event') {
			$eventTypes = '0,1,2,3,4';
		}
		
		$master_array = $modelObj->findEventsForList ($startObj, $endObj, $type, $pidList, $eventTypes);
		
		// Hook: preLoadEventsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadEventsRendering')) {
				$hookObj->preLoadEventsRendering ($master_array, $this);
			}
		}
		
		$this->conf ['view'] = $this->piVars ['targetView'];
		if (! empty ($master_array)) {
			// use array keys for the loop in order to be able to use referenced events instead of copies and save some memory
			$masterArrayKeys = array_keys ($master_array);
			$ajaxStringArray = Array ();
			
			foreach ($masterArrayKeys as $dateKey) {
				$dateArray = &$master_array [$dateKey];
				$dateArrayKeys = array_keys ($dateArray);
				foreach ($dateArrayKeys as $timeKey) {
					$arrayOfEvents = &$dateArray [$timeKey];
					$eventKeys = array_keys ($arrayOfEvents);
					foreach ($eventKeys as $eventKey) {
						$event = &$arrayOfEvents [$eventKey];
						$ajaxStringArray [] = '{' . $this->getEventAjaxString ($event) . '}';
					}
				}
			}
			$ajaxString = implode (',', $ajaxStringArray);
			// $ajaxString .= $ajaxEdit;
		}
		$this->conf ['view'] = 'load_events';
		
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		$sims ['###IMG_PATH###'] = \TYPO3\CMS\Cal\Utility\Functions::expandPath ($this->conf ['view.'] ['imagePath']);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ('[' . $ajaxString . ']', $sims, $rems, $wrapped);
		
		// Hook: postLoadEventsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadEventsRendering')) {
				$hookObj->postLoadEventsRendering ($page, $this);
			}
		}
		
		return $page;
	}
	
	/**
	 * 
	 * @param unknown $uid
	 * @param string $eventType
	 * @return string
	 */
	public function loadEvent($uid, $eventType = '') {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$eventType = intval ($this->piVars ['event_type']);
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadEventClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		if ($eventType == \TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_TODO) {
			$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
			$todoSubtype = $confArr ['todoSubtype'];
			$availableTypes = $modelObj->getServiceTypes ('cal_event_model', $todoSubtype);
			if (! in_array ($type, $availableTypes)) {
				$type = '';
			}
			$event = $modelObj->findTodo ($uid, $type, $pidList);
		} else {
			$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
			if (! in_array ($type, $availableTypes)) {
				$type = '';
			}
			$event = $modelObj->findEvent ($uid, $type, $pidList);
		}
		
		// Hook: preLoadEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadEventRendering')) {
				$hookObj->preLoadEventRendering ($event, $this);
			}
		}
		
		$ajaxString = '';
		
		if (is_object ($event)) {
			$ajaxString = $this->getEventAjaxString ($event);
			$ajaxString .= 'events.push(tmp' . $event->getUid () . ');' . "\n";
			$ajaxString .= 'addEvents();';
		} else {
			$ajaxString = 'error, can not find the event';
		}
		
		// Hook: posteLoadEventRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadEventRendering')) {
				$hookObj->postLoadEventRendering ($ajaxString, $this);
			}
		}
		
		return $ajaxString;
	}
	
	/**
	 * 
	 * @param unknown $event
	 * @return string
	 */
	public function getEventAjaxString(&$event) {
		if (is_object ($event)) {
			$eventValues = $event->getValuesAsArray ();
		} else if (is_array ($event)) {
			$eventValues = $event;
		}
		if ($eventValues ['isFreeAndBusyEvent'] == 1) {
			$eventValues ['titel'] = $this->conf ['view.'] ['freeAndBusy.'] ['eventTitle'];
			$eventValues ['description'] = $event->getCalendarObject ()->getTitle ();
		}
		$ajaxStringArray = Array ();
		
		foreach ($eventValues as $key => $value) {
			if (is_array ($value)) {
				if (count ($value) > 0) {
					$ajaxStringArray [] = '"' . $key . '":' . '{' . $this->getEventAjaxString ($eventValues [$key]) . '}';
				} else {
					$ajaxStringArray [] = '"' . $key . '":' . '[]';
				}
			} else if (is_object ($value)) {
				$ajaxStringArray [] = '"' . $key . '":' . '{' . $this->getEventAjaxString ($eventValues [$key]) . '}';
			} else {
				if ($key !== 'l18n_diffsource') {
					$ajaxStringArray [] = '"' . $key . '":' . json_encode ($value);
				}
			}
		}
		
		return implode (',', $ajaxStringArray);
	}
	
	/**
	 * 
	 * @return string
	 */
	public function loadTodos() {
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadTodosClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$availableTypes = $modelObj->getServiceTypes ('cal_event_model', 'event');
		if (! in_array ($type, $availableTypes)) {
			$type = '';
		}
		
		$result_array = $modelObj->findCurrentTodos ($type, $pidList);
		
		// Hook: preLoadTodosRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadTodosRendering')) {
				$hookObj->preLoadTodosRendering ($result_array, $this);
			}
		}
		
		$ajaxStringArray = Array ();
		
		$this->conf ['view'] = $this->piVars ['targetView'];
		if (! empty ($result_array)) {
			// use array keys for the loop in order to be able to use referenced events instead of copies and save some memory
			$resultArrayKeys = array_keys ($result_array);
			foreach ($resultArrayKeys as $resultArrayKey) {
				$masterArrayKeys = array_keys ($result_array [$resultArrayKey]);
				foreach ($masterArrayKeys as $dateKey) {
					$dateArray = &$result_array [$resultArrayKey] [$dateKey];
					$dateArrayKeys = array_keys ($dateArray);
					foreach ($dateArrayKeys as $timeKey) {
						$arrayOfEvents = &$dateArray [$timeKey];
						$eventKeys = array_keys ($arrayOfEvents);
						foreach ($eventKeys as $eventKey) {
							$event = &$arrayOfEvents [$eventKey];
							$ajaxStringArray [] = '{' . $this->getEventAjaxString ($event) . '}';
						}
					}
				}
			}
		}
		$this->conf ['view'] = 'load_todos';
		
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		$sims ['###IMG_PATH###'] = \TYPO3\CMS\Cal\Utility\Functions::expandPath ($this->conf ['view.'] ['imagePath']);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ('[' . implode (',', $ajaxStringArray) . ']', $sims, $rems, $wrapped);
		
		// Hook: postLoadTodosRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadTodosRendering')) {
				$hookObj->postLoadTodosRendering ($page, $this);
			}
		}
		
		return $page;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function loadCalendars() {
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadCalendarsClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ajaxStringArray = Array ();
		$deselectedCalendarIds = GeneralUtility::trimExplode (',', $this->conf ['view.'] ['calendar.'] ['subscription'], 1);
		$calendarIds = Array ();
		foreach ($deselectedCalendarIds as $calendarUid) {
			$calendarIds [] = $calendarUid;
			$calendar = $modelObj->findCalendar ($calendarUid, 'tx_cal_calendar', $this->conf ['pidList']);
			$ajaxStringArray [] = '{' . $this->getEventAjaxString ($calendar) . '}';
		}
		$calendarArray = $modelObj->findAllCalendar ('tx_cal_calendar', $this->conf ['pidList']);
		
		// Hook: preLoadCalendarsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadCalendarsRendering')) {
				$hookObj->preLoadCalendarsRendering ($calendarArray, $this);
			}
		}
		
		foreach ($calendarArray ['tx_cal_calendar'] as $calendar) {
			if (! in_array ($calendar->getUid (), $calendarIds)) {
				$ajaxStringArray [] = '{' . $this->getEventAjaxString ($calendar) . '}';
			}
		}
		
		// Hook: postLoadCalendarsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadCalendarsRendering')) {
				$hookObj->postLoadCalendarsRendering ($ajaxStringArray, $this);
			}
		}
		return '[' . implode (',', $ajaxStringArray) . ']';
	}
	
	/**
	 * 
	 * @return string
	 */
	public function loadCategories() {
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadCategoriesClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ajaxStringArray = Array ();
		$categoryArray = $modelObj->findAllCategories ('cal_category_model', $this->confArr ['categoryService'], $this->conf ['pidList']);
		
		// Hook: preLoadCategoriesRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadCategoriesRendering')) {
				$hookObj->preLoadCategoriesRendering ($categoryArray, $this);
			}
		}
		
		foreach ($categoryArray ['tx_cal_category'] as $category) {
			$ajaxStringArray [] = '{' . $this->getEventAjaxString ($category) . '}';
		}
		
		// Hook: postLoadCategoriesRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadCategoriesRendering')) {
				$hookObj->postLoadCategoriesRendering ($ajaxStringArray, $this);
			}
		}
		return '[' . implode (',', $ajaxStringArray) . ']';
	}
	
	/**
	 * 
	 * @return string
	 */
	public function loadLocations() {
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadLocationsClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ajaxStringArray = Array ();
		
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$locationArray = $modelObj->findAllLocations ($type, $pidList);
		
		// Hook: preLoadLocationsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadLocationsRendering')) {
				$hookObj->preLoadLocationsRendering ($locationArray, $this);
			}
		}
		
		foreach ($locationArray as $location) {
			$ajaxStringArray [] = '{' . $this->getEventAjaxString ($location) . '}';
		}
		
		// Hook: postLoadLocationsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadLocationsRendering')) {
				$hookObj->postLoadLocationsRendering ($ajaxStringArray, $this);
			}
		}
		return '[' . implode (',', $ajaxStringArray) . ']';
	}
	
	/**
	 * 
	 * @return string
	 */
	public function loadOrganizers() {
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadOrganizersClass');
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$ajaxStringArray = Array ();
		
		$type = $this->conf ['type'];
		$pidList = $this->conf ['pidList'];
		$organizerArray = $modelObj->findAllOrganizer ($type, $pidList);
		
		// Hook: preLoadOrganizersRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preLoadOrganizersRendering')) {
				$hookObj->preLoadOrganizersRendering ($organizerArray, $this);
			}
		}
		
		foreach ($organizerArray as $organizer) {
			$ajaxStringArray [] = '{' . $this->getEventAjaxString ($organizer) . '}';
		}
		
		// Hook: postLoadOrganizersRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadOrganizersRendering')) {
				$hookObj->postLoadOrganizersRendering ($ajaxStringArray, $this);
			}
		}
		return '[' . implode (',', $ajaxStringArray) . ']';
	}
	
	/**
	 * 
	 * @return string
	 */
	public function loadRights() {
		$hookObjectsArr = $this->getHookObjectsArray ('drawLoadRightsClass');
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$options = Array (
				'create',
				'edit',
				'delete' 
		);
		$rights = Array ();
		foreach ($options as $option) {
			$isAllowedToOptionCalendar = $rightsObj->isAllowedTo ($option, 'calendar') ? 'true' : 'false';
			$isAllowedToOptionCategory = $rightsObj->isAllowedTo ($option, 'category') ? 'true' : 'false';
			$isAllowedToOptionEvent = $rightsObj->isAllowedTo ($option, 'event') ? 'true' : 'false';
			$isAllowedToOptionLocation = $rightsObj->isAllowedTo ($option, 'location') ? 'true' : 'false';
			$isAllowedToOptionOrganizer = $rightsObj->isAllowedTo ($option, 'organizer') ? 'true' : 'false';
			$rights [] = '"'.($option == 'delete' ? 'del' : $option) . '":{"calendar":' . $isAllowedToOptionCalendar . ',"category":' . $isAllowedToOptionCategory . ',"event":' . $isAllowedToOptionEvent . ',"location":' . $isAllowedToOptionLocation . ',"organizer":' . $isAllowedToOptionOrganizer . '}';
		}
		$rights [] = '"admin":' . ($rightsObj->isCalAdmin () ? 'true' : 'false');
		$rights [] = '"userId":' . $rightsObj->getUserId ();
		$rights [] = '"userGroups":[' . implode (',', $rightsObj->getUserGroups ()) . ']';
		
		// Hook: postLoadRightsRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postLoadRightsRendering')) {
				$hookObj->postLoadRightsRendering ($rights, $this);
			}
		}
		return '{' . implode (',', $rights) . '}';
	}
	
	/**
	 * 
	 * @param unknown $piFlexForm
	 */
	public function updateConfWithFlexform(&$piFlexForm) {
		// Controller::updateIfNotEmpty($this->conf['pages'], $this->pi_getFFvalue($piFlexForm, 'pages'));
		// Controller::updateIfNotEmpty($this->conf['recursive'], $this->pi_getFFvalue($piFlexForm, 'recursive'));
		if ($this->conf ['dontListenToFlexForm'] == 1) {
			return;
		}
		if ($this->conf ['dontListenToFlexForm.'] ['general.'] ['calendarName'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['calendarName'], $this->pi_getFFvalue ($piFlexForm, 'calendarName'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['general.'] ['allowSubscribe'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['allowSubscribe'], $this->pi_getFFvalue ($piFlexForm, 'subscription') == 1 ? 1 : - 1);
		}
		if ($this->conf ['dontListenToFlexForm.'] ['general.'] ['subscribeFeUser'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['subscribeFeUser'], $this->pi_getFFvalue ($piFlexForm, 'subscription') == 2 ? 1 : - 1);
		}
		if ($this->conf ['dontListenToFlexForm.'] ['general.'] ['subscribeWithCaptcha'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['subscribeWithCaptcha'], $this->pi_getFFvalue ($piFlexForm, 'subscribeWithCaptcha'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['general.'] ['allowedViews'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['allowedViews'], $this->pi_getFFvalue ($piFlexForm, 'allowedViews'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['general.'] ['calendarDistance'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['calendar.'] ['nearbyDistance'], intval ($this->pi_getFFvalue ($piFlexForm, 'calendarDistance')));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['day.'] ['dayViewPid'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['day.'] ['dayViewPid'], $this->pi_getFFvalue ($piFlexForm, 'dayViewPid', 's_Day_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['day.'] ['dayStart'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['day.'] ['dayStart'], $this->pi_getFFvalue ($piFlexForm, 'dayStart', 's_Day_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['day.'] ['dayEnd'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['day.'] ['dayEnd'], $this->pi_getFFvalue ($piFlexForm, 'dayEnd', 's_Day_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['day.'] ['gridLength'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['day.'] ['gridLength'], $this->pi_getFFvalue ($piFlexForm, 'gridLength', 's_Day_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['day.'] ['weekViewPid'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['week.'] ['weekViewPid'], $this->pi_getFFvalue ($piFlexForm, 'weekViewPid', 's_Week_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['month.'] ['monthViewPid'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['month.'] ['monthViewPid'], $this->pi_getFFvalue ($piFlexForm, 'monthViewPid', 's_Month_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['month.'] ['monthMakeMiniCal'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['month.'] ['monthMakeMiniCal'], $this->pi_getFFvalue ($piFlexForm, 'monthMakeMiniCal', 's_Month_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['month.'] ['monthShowListView'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['month.'] ['showListInMonthView'], $this->pi_getFFvalue ($piFlexForm, 'monthShowListView', 's_Month_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['year.'] ['yearViewPid'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['year.'] ['yearViewPid'], $this->pi_getFFvalue ($piFlexForm, 'yearViewPid', 's_Year_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['event.'] ['eventViewPid'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['event.'] ['eventViewPid'], $this->pi_getFFvalue ($piFlexForm, 'eventViewPid', 's_Event_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['event.'] ['isPreview'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['event.'] ['isPreview'], $this->pi_getFFvalue ($piFlexForm, 'isPreview', 's_Event_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['listViewPid'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['listViewPid'], $this->pi_getFFvalue ($piFlexForm, 'listViewPid', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['starttime'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['starttime'], $this->pi_getFFvalue ($piFlexForm, 'starttime', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['endtime'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['endtime'], $this->pi_getFFvalue ($piFlexForm, 'endtime', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['maxEvents'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['maxEvents'], $this->pi_getFFvalue ($piFlexForm, 'maxEvents', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['maxRecurringEvents'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['maxRecurringEvents'], $this->pi_getFFvalue ($piFlexForm, 'maxRecurringEvents', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['usePageBrowser'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['pageBrowser.'] ['usePageBrowser'], $this->pi_getFFvalue ($piFlexForm, 'usePageBrowser', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['recordsPerPage'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['pageBrowser.'] ['recordsPerPage'], $this->pi_getFFvalue ($piFlexForm, 'recordsPerPage', 's_List_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['list.'] ['pagesCount'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['list.'] ['pageBrowser.'] ['pagesCount'], $this->pi_getFFvalue ($piFlexForm, 'pagesCount', 's_List_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['ics.'] ['showIcsLinks'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['ics.'] ['showIcsLinks'], $this->pi_getFFvalue ($piFlexForm, 'showIcsLinks', 's_Ics_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showLogin'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showLogin'], $this->pi_getFFvalue ($piFlexForm, 'showLogin', 's_Other_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showSearch'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showSearch'], $this->pi_getFFvalue ($piFlexForm, 'showSearch', 's_Other_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showJumps'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showJumps'], $this->pi_getFFvalue ($piFlexForm, 'showJumps', 's_Other_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showGoto'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showGoto'], $this->pi_getFFvalue ($piFlexForm, 'showGoto', 's_Other_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showCalendarSelection'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showCalendarSelection'], $this->pi_getFFvalue ($piFlexForm, 'showCalendarSelection', 's_Other_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showCategorySelection'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showCategorySelection'], $this->pi_getFFvalue ($piFlexForm, 'showCategorySelection', 's_Other_View'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['other.'] ['showTomorrowEvents'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['other.'] ['showTomorrowEvents'], $this->pi_getFFvalue ($piFlexForm, 'showTomorrowEvents', 's_Other_View'));
		}
		
		if ($this->conf ['dontListenToFlexForm.'] ['filters.'] ['categorySelection'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['category'], $this->pi_getFFvalue ($piFlexForm, 'categorySelection', 's_Cat'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['filters.'] ['categoryMode'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['categoryMode'], $this->pi_getFFvalue ($piFlexForm, 'categoryMode', 's_Cat'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['filters.'] ['calendarSelection'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['calendar'], $this->pi_getFFvalue ($piFlexForm, 'calendarSelection', 's_Cat'));
		}
		if ($this->conf ['dontListenToFlexForm.'] ['filters.'] ['calendarMode'] != 1) {
			Controller::updateIfNotEmpty ($this->conf ['view.'] ['calendarMode'], $this->pi_getFFvalue ($piFlexForm, 'calendarMode', 's_Cat'));
		}
		
		$flexformTyposcript = $this->pi_getFFvalue ($piFlexForm, 'myTS', 's_TS_View');
		if ($flexformTyposcript) {
			$tsparser = new \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser();
			// Copy conf into existing setup 
			$tsparser->setup = $this->conf;
			// Parse the new Typoscript 
			$tsparser->parse ($flexformTyposcript);
			// Copy the resulting setup back into conf 
			$this->conf = $tsparser->setup;
		}
	}
	
	/**
	 * 
	 * @param string $confVar
	 * @param string $newConfVar
	 */
	public static function updateIfNotEmpty(&$confVar, $newConfVar) {
		if ($newConfVar != '') {
			$confVar = $newConfVar;
		}
	}
	
	/**
	 * 
	 * @param array $linkVar
	 * @return string
	 */
	public static function convertLinkVarArrayToList($linkVar) {
		if (is_array ($linkVar)) {
			$first = true;
			$new = '';
			foreach ($linkVar as $key => $value) {
				if ($first) {
					if ($value == 'on') {
						$value = intval ($key);
					}
					$new .= intval ($value);
					$first = false;
				} else {
					if ($value == 'on') {
						$value = intval ($key);
					}
					$new .= ',' . intval ($value);
				}
			}
			return $new;
		} else {
			return implode (',', GeneralUtility::intExplode (',', $linkVar));
		}
	}
	
	/**
	 * 
	 * @param array $tags
	 * @param string $page
	 * @return string
	 */
	public static function replace_tags($tags = Array(), $page) {
		if (sizeof ($tags) > 0) {
			$sims = Array ();
			foreach ($tags as $tag => $data) {
				// This replaces any tags
				$sims ['###' . strtoupper ($tag) . '###'] = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($data, '###' . strtoupper ($tag) . '###', Array (), Array ());
			}
			
			$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		} else {
			//die('No tags designated for replacement.');
		}
		return $page;
	}
	
	/**
	 * 
	 * @param string $takeFirstInsteadOfLast
	 * @return Ambigous <multitype:NULL string Ambigous <NULL, string> , \TYPO3\CMS\Cal\Controller\multitype:>
	 */
	public function shortenLastViewAndGetTargetViewParameters($takeFirstInsteadOfLast = false) {
		$returnParams = array ();
		if (count ($this->conf ['view.'] ['allowedViews']) == 1 && count ($this->conf ['view.'] ['allowedViewsToLinkTo']) == 1) {
			$returnParams ['lastview'] = null;
			$returnParams ['view'] = $this->conf ['view.'] ['allowedViews'] [0];
		} else {
			$views = explode ('||', $this->conf ['lastview']);
			if ($takeFirstInsteadOfLast) {
				$target = array_shift ($views);
				$views = array ();
			} else {
				$target = array_pop ($views);
			}
			$lastview = implode ('||', $views);
			
			$viewParams = Controller::convertLastViewParamsToArray ($target);
			$returnParams = $viewParams [0];
			
			switch (trim ($returnParams ['view'])) {
				case 'event' :
				case 'organizer' :
				case 'location' :
				case 'edit_calendar' :
				case 'edit_category' :
				case 'edit_location' :
				case 'edit_organizer' :
				case 'edit_event' :
					break;
				case 'rss' :
					$returnParams ['uid'] = null;
					$returnParams ['type'] = null;
					$returnParams ['gettime'] = null;
					$returnParams ['getdate'] = $this->conf ['getdate'];
					$returnParams ['page_id'] = $returnParams ['page_id'] . ',151';
					break;
				default :
					$returnParams ['uid'] = null;
					$returnParams ['type'] = null;
					$returnParams ['gettime'] = null;
					$returnParams ['getdate'] = empty ($returnParams ['getdate']) ? $this->conf ['getdate'] : $returnParams ['getdate'];
					break;
			}
			
			switch ($this->conf ['view']) {
				case 'search_event' :
					$returnParams ['start_day'] = null;
					$returnParams ['end_day'] = null;
					$returnParams ['category'] = null;
					$returnParams ['query'] = null;
					break;
				case 'event' :
					$returnParams ['ts_table'] = null;
					break;
			}
			$returnParams ['lastview'] = $lastview;
		}
		return $returnParams;
	}
	
	/**
	 * 
	 * @param string $overrideParams
	 * @return NULL|string
	 */
	public function extendLastView($overrideParams = false) {
		if (count ($this->conf ['view.'] ['allowedViews']) == 1 && count ($this->conf ['view.'] ['allowedViewsToLinkTo']) == 1) {
			return NULL;
		}
		
		$params = array (
				'view' => $this->conf ['view'],
				'page_id' => $GLOBALS ['TSFE']->id 
		);
		if ($overrideParams && is_array ($overrideParams)) {
			$params = array_merge ($params, $overrideParams);
		}
		switch ($this->conf ['view']) {
			case 'event' :
			case 'organizer' :
			case 'location' :
			case 'edit_calendar' :
			case 'edit_category' :
			case 'edit_location' :
			case 'edit_organizer' :
			case 'edit_event' :
				$params ['uid'] = $this->conf ['uid'];
				$params ['type'] = $this->conf ['type'];
				break;
			default :
				break;
		}
		
		$paramsForUrl = array ();
		foreach ($params as $key => $val) {
			$paramsForUrl [] = $key . '-' . $val;
		}
		
		return ($this->conf ['lastview'] != null ? $this->conf ['lastview'] . '||' : '') . implode ('|', $paramsForUrl);
	}
	
	/**
	 * 
	 * @param unknown $config
	 * @return multitype:
	 */
	public static function convertLastViewParamsToArray($config) {
		$views = explode ('||', $config);
		$result = Array ();
		foreach ($views as $viewNr => $viewConf) {
			$paramArray = explode ('|', $viewConf);
			foreach ($paramArray as $paramString) {
				$param = explode ('-', $paramString);
				$result [$viewNr] [$param [0]] = $param [1];
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * @param \TYPO3\CMS\Cal\Controller\Controller $controller
	 */
	public static function initRegistry(&$controller) {
		$myCobj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$myCobj = $controller->cObj;
		$controller->cObj = &$myCobj;
		$myConf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$myConf = $controller->conf;
		$controller->conf = &$myConf;
		$myController = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$myController = $controller;
		$controller = &$myController;
		// besides of the regular cObj we provide a localCobj, whos data can be overridden with custom data for a more flexible rendering of TSObjects
		$local_cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'local_cobj');
		$local_cObj = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
		$local_cObj->start (array ());
		$cache = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cache');
		$cache = Array ();
		$controller->local_cObj = &$local_cObj;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function __toString() {
		return get_class ($this);
	}
	
	/**
	 * 
	 * @param unknown $str
	 * @param unknown $additionalClasses
	 * @return string
	 */
	public function pi_wrapInBaseClass($str, $additionalClasses = array()) {
		$content = '<div class="' . str_replace ('_', '-', $this->prefixId) . ' ' . implode (' ', $additionalClasses) . '">
		' . $str . '</div>';
		
		if (! $GLOBALS ['TSFE']->config ['config'] ['disablePrefixComment']) {
			$content = '
			<!--

			BEGIN: Content of extension "' . $this->extKey . '", plugin "' . $this->prefixId . '"

			-->
			' . $content . '
		
			<!-- END: Content of extension "' . $this->extKey . '", plugin "' . $this->prefixId . '" -->

			';
		}
		
		return $content;
	}
	
	/**
	 * 
	 * @param array $params
	 */
	public function moveParamsIntoSession(&$params) {
		if (empty ($params)) {
			$params = $this->piVars;
		}
		$sessionPiVars = GeneralUtility::trimExplode (',', $this->conf ['sessionPiVars'], 1);
		
		foreach ((array) $params [$this->prefixId] as $key => $value) {
			if (in_array ($key, $sessionPiVars)) {
				$_SESSION [$this->prefixId] [$key] = $value;
				unset ($params [$this->prefixId] [$key]);
			}
		}
	}
	
	/**
	 * 
	 */
	public function getParamsFromSession() {
		if (! $this->piVars ['view']) {
			if ($this->piVars ['week']) {
				$this->piVars ['view'] = 'week';
			} else if ($this->piVars ['day']) {
				$this->piVars ['view'] = 'day';
			} else if ($this->piVars ['month']) {
				$this->piVars ['view'] = 'month';
			} else if ($this->piVars ['year']) {
				$this->piVars ['view'] = 'year';
			}
		}
		
		if ($this->conf ['dontListenToPiVars']) {
			$this->piVars = array ();
		} else {
			foreach ((array) $_SESSION [$this->prefixId] as $key => $value) {
				if (! array_key_exists ($key, $this->piVars)) {
					$this->piVars [$key] = $value;
				}
			}
		}
		if (! $this->piVars ['getdate'] && ! $this->piVars ['week'] && ! $this->piVars ['year'] && $this->conf ['_DEFAULT_PI_VARS.'] ['getdate']) {
			$this->piVars ['getdate'] = $this->conf ['_DEFAULT_PI_VARS.'] ['getdate'];
		}
		if (! $this->piVars ['getdate']) {
			if ($this->piVars ['week']) {
				$this->piVars ['getdate'] = \TYPO3\CMS\Cal\Utility\Functions::getDayByWeek ($this->piVars ['year'], $this->piVars ['week'], $this->piVars ['weekday']);
				
				unset ($this->piVars ['year']);
				unset ($this->piVars ['week']);
				unset ($this->piVars ['weekday']);
			} else {
				$date = new \TYPO3\CMS\Cal\Model\CalDate ();
				$date->setTZbyID ('UTC');
				if (! $this->piVars ['year']) {
					$this->piVars ['year'] = $date->format ('%Y');
				}
				if (! $this->piVars ['month']) {
					$this->piVars ['month'] = $date->format ('%m');
				}
				if (! $this->piVars ['day']) {
					$this->piVars ['day'] = $date->format ('%d');
				}
				if ($this->piVars ['month'] == 2) {
					if ((($this->piVars ['year'] % 400) == 0 || (($this->piVars ['year'] % 4) == 0 && ($this->piVars ['year'] % 100) != 0)) && $this->piVars ['day'] >= 29) {
						$this->piVars ['day'] = 29;
					} else if ($this->piVars ['day'] > 28) {
						$this->piVars ['day'] = 28;
					}
				} else if (in_array ($this->piVars ['month'], array (
						4,
						6,
						9,
						11 
				)) && $this->piVars ['day'] > 30) {
					$this->piVars ['day'] = 30;
				}
				$this->piVars ['getdate'] = str_pad ((int) $this->piVars ['year'], 4, "0", STR_PAD_LEFT) . str_pad ((int) $this->piVars ['month'], 2, "0", STR_PAD_LEFT) . str_pad ((int) $this->piVars ['day'], 2, "0", STR_PAD_LEFT);
				unset ($this->piVars ['year']);
				unset ($this->piVars ['month']);
				unset ($this->piVars ['day']);
			}
		}
		unset ($_SESSION [$this->prefixId]);
	}
	
	/**
	 * 
	 */
	public function clearPiVarParams() {
		if ($this->conf ['dontListenToPiVars'] || $this->conf ['clearPiVars'] == 'all') {
			$this->piVars = array ();
		} else {
			$clearPiVars = GeneralUtility::trimExplode (',', $this->conf ['clearPiVars'], 1);
			foreach ((array) $this->piVars as $key => $value) {
				if (in_array ($key, $clearPiVars)) {
					unset ($this->piVars [$key]);
				}
			}
		}
	}
	
	/**
	 * Returns a array with fields/parameters that can be used for link rendering in typoscript.
	 * It's based on the link functions from \TYPO3\CMS\Frontend\Plugin\AbstractPlugin.
	 *
	 * @param
	 *        	array			Referenced array in which the parameters get merged into
	 * @param
	 *        	array			Array with parameter=>value pairs of piVars that should override present piVars
	 * @param
	 *        	boolean		Flag that indicates if the linktarget is allowed to be cached (takes care of cacheHash and no_cache parameter)
	 * @param
	 *        	boolean		Flag that's clearing all present piVars, thus only piVars defined in $overrulePIvars are kept
	 * @param
	 *        	integer		Alternative ID of a page that should be used as link target. If empty or 0, current page is used
	 * @return nothing
	 *
	 */
	public function getParametersForTyposcriptLink(&$parameterArray, $overrulePIvars = array(), $cache = false, $clearAnyway = false, $altPageId = 0) {
		
		// copied from function 'pi_linkTP_keepPIvars'
		if (is_array ($this->piVars) && is_array ($overrulePIvars) && ! $clearAnyway) {
			$piVars = $this->piVars;
			unset ($piVars ['DATA']);
			if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) < '6002000') {
				$piVars = GeneralUtility::array_merge_recursive_overrule($piVars, $overrulePIvars);
			} else {
				\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($piVars, $overrulePIvars);
			}
			$overrulePIvars = $piVars;
			if ($this->pi_autoCacheEn) {
				$cache = $this->pi_autoCache ($overrulePIvars);
			}
		}
		
		$piVars = array (
				$this->prefixId => $overrulePIvars 
		);
		
		/* TEST */
		if ($piVars [$this->prefixId] ['getdate']) {
			$date = new \TYPO3\CMS\Cal\Model\CalDate ($piVars [$this->prefixId] ['getdate']);
			
			$sessionVars = Array ();
			switch ($piVars [$this->prefixId] ['view']) {
				case 'week' :
					$piVars [$this->prefixId] ['year'] = $date->getYear ();
					$piVars [$this->prefixId] ['week'] = $date->getWeekOfYear ();
					$piVars [$this->prefixId] ['weekday'] = $date->getDayOfWeek ();
					$sessionVars ['month'] = substr ($piVars [$this->prefixId] ['getdate'], 4, 2);
					$sessionVars ['day'] = substr ($piVars [$this->prefixId] ['getdate'], 6, 2);
					if ($date->getMonth () == 12 && $piVars [$this->prefixId] ['week'] == 1) {
						$piVars [$this->prefixId] ['year'] ++;
					}
					
					unset ($piVars [$this->prefixId] ['view']);
					unset ($piVars [$this->prefixId] ['getdate']);
					break;
				case 'event' :
				case 'todo' :
					$piVars [$this->prefixId] ['year'] = substr ($piVars [$this->prefixId] ['getdate'], 0, 4);
					$piVars [$this->prefixId] ['month'] = substr ($piVars [$this->prefixId] ['getdate'], 4, 2);
					$piVars [$this->prefixId] ['day'] = substr ($piVars [$this->prefixId] ['getdate'], 6, 2);
					unset ($piVars [$this->prefixId] ['getdate']);
					break;
				case 'day' :
					$piVars [$this->prefixId] ['year'] = substr ($piVars [$this->prefixId] ['getdate'], 0, 4);
					$piVars [$this->prefixId] ['month'] = substr ($piVars [$this->prefixId] ['getdate'], 4, 2);
					$piVars [$this->prefixId] ['day'] = substr ($piVars [$this->prefixId] ['getdate'], 6, 2);
				case 'month' :
					$piVars [$this->prefixId] ['year'] = substr ($piVars [$this->prefixId] ['getdate'], 0, 4);
					$piVars [$this->prefixId] ['month'] = substr ($piVars [$this->prefixId] ['getdate'], 4, 2);
					$sessionVars ['day'] = substr ($piVars [$this->prefixId] ['getdate'], 6, 2);
				case 'year' :
					$piVars [$this->prefixId] ['year'] = substr ($piVars [$this->prefixId] ['getdate'], 0, 4);
					$sessionVars ['month'] = substr ($piVars [$this->prefixId] ['getdate'], 4, 2);
					$sessionVars ['day'] = substr ($piVars [$this->prefixId] ['getdate'], 6, 2);
					unset ($piVars [$this->prefixId] ['view']);
					unset ($piVars [$this->prefixId] ['getdate']);
			}
			
			foreach ($sessionVars as $key => $value) {
				//$_SESSION[$this->prefixId][$key] = $value;
			}
		}
		/* TEST */
		
		// use internal method for cleaning up piVars
		$this->cleanupUrlParameter ($piVars);
		
		// copied and modified logic of function 'pi_linkTP'
		# once useCacheHash property in typolinks has stdWrap, we can use this flag - until then it's unfortunately useless :(
		#$parameterArray['link_useCacheHash'] = $this->pi_USER_INT_obj ? 0 : $cache;
		$parameterArray ['link_no_cache'] = $this->pi_USER_INT_obj ? 0 : ! $cache;
		$parameterArray ['link_parameter'] = $altPageId ? $altPageId : ($this->pi_tmpPageId ? $this->pi_tmpPageId : $GLOBALS ['TSFE']->id);
		$parameterArray ['link_additionalParams'] = $this->conf ['parent.'] ['addParams'] . GeneralUtility::implodeArrayForUrl ('', $piVars, '', true) . $this->pi_moreParams;
		$parameterArray ['link_ATagParams'] = 'class="url"';
		
		# add time/date related parameters to all link objects, so that they can use them e.g. to display the monthname etc.
		$parameterArray ['getdate'] = $this->conf ['getdate'];
		if ($overrulePIvars ['getdate'] && is_object ($date)) {
			$parameterArray ['link_timestamp'] = $date->getTime ();
			$parameterArray ['link_getdate'] = $overrulePIvars ['getdate'];
		}
	}
	
	/**
	 * Modified function pi_linkTP.
	 * It calls a function for cleaning up the piVars right before calling the original function.
	 * Returns the $str wrapped in <a>-tags with a link to the CURRENT page, but with $urlParameters set as extra parameters for the page.
	 *
	 * @param
	 *        	string		The content string to wrap in <a> tags
	 * @param
	 *        	array			Array with URL parameters as key/value pairs. They will be "imploded" and added to the list of parameters defined in the plugins TypoScript property "parent.addParams" plus $this->pi_moreParams.
	 * @param
	 *        	boolean		If $cache is set (0/1), the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
	 * @param
	 *        	integer		Alternative page ID for the link. (By default this function links to the SAME page!)
	 * @return string input string wrapped in <a> tags
	 * @see pi_linkTP_keepPIvars(), \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::typoLink()
	 */
	public function pi_linkTP($str, $urlParameters = array(), $cache = 0, $altPageId = 0) {
		$this->cleanupUrlParameter ($urlParameters);
		$link = parent::pi_linkTP ($str, $urlParameters, $cache, $altPageId);
		$this->pi_USER_INT_obj = 0;
		return $link;
	}
	
	/**
	 * 
	 * @param array $urlParameters
	 */
	public function cleanupUrlParameter(&$urlParameters) {
		/*
		 * [Franz] this little construct should be a first step into a centralized url-parameter handler for intelligent and nice looking urls. /* But it's merely experimental. A better, more flexible solution/concept need's to be found. /* To save some parsing time, I've removed the calls to $controller->extendLastView() on calls to the pi-link functions, /* because all these use internally this function and the lastView parameter is added by this function now.
		 */
		$params = &$urlParameters [$this->prefixId];
		$removeParams = array ();
		$lastViewParams = array ();
		$useLastView = true;
		// temporary fix for BACK_LINK urls
		$dontExtendLastView = $params ['dontExtendLastView'];
		unset ($params ['dontExtendLastView']);
		
		switch (trim ($params ['view'])) {
			case 'search_all' :
			case 'search_event' :
			case 'search_location' :
			case 'search_organizer' :
				$this->pi_USER_INT_obj = 1;
				$useLastView = false;
				break;
			default :
				if ($params ['type'] || GeneralUtility::inList ('week,day,year', trim ($params ['view']))) {
					$removeParams = array (
							$this->getPointerName (),
							'submit',
							'query' 
					);
				}
				if ($params [$this->getPointerName] || ($params ['category'] && $params ['view'] != 'event')) {
					$useLastView = false;
				}
				break;
		}
		if (count ($removeParams)) {
			foreach ($removeParams as $name) {
				if (isset ($params [$name])) {
					$lastViewParams [$name] = $params [$name];
					unset ($params [$name]);
				}
			}
			if ($useLastView && ! $dontExtendLastView) {
				$params ['lastview'] = $this->extendLastView ($lastViewParams);
			} else if (! $useLastView) {
				$params ['lastview'] = NULL;
			}
		}
		
		$this->moveParamsIntoSession ($urlParameters);
	}
	
	/**
	 * 
	 * @param string $action
	 * @param object $object
	 */
	private function checkRedirect($action, $object) {
		if ($this->conf ['view.'] ['enableAjax']) {
			die ();
		}
		if ($this->conf ['view.'] [$action . '_' . $object . '.'] ['redirectAfter' . ucwords ($action) . 'ToPid'] || $this->conf ['view.'] [$action . '_' . $object . '.'] ['redirectAfter' . ucwords ($action) . 'ToView']) {
			$linkParams = Array ();
			if ($object == 'event') {
				$linkParams [$this->prefixId . '[getdate]'] = $this->conf ['getdate'];
			}
			if ($this->conf ['view.'] [$action . '_' . $object . '.'] ['redirectAfter' . ucwords ($action) . 'ToView']) {
				$linkParams [$this->prefixId . '[view]'] = $this->conf ['view.'] [$action . '_' . $object . '.'] ['redirectAfter' . ucwords ($action) . 'ToView'];
			}
			$hookObjectsArr = $this->getHookObjectsArray ('beforeRedirect');
			// Hook: beforeRedirect
			foreach ($hookObjectsArr as $hookObj) {
			    if (method_exists ($hookObj, 'beforeRedirect')) {
			        $hookObj->beforeRedirect ($this, $action, $object, $linkParams);
			    }
			}
			$this->pi_linkTP ('|', $linkParams, $this->conf ['cache'], $this->conf ['view.'] [$action . '_' . $object . '.'] ['redirectAfter' . ucwords ($action) . 'ToPid']);
			$rURL = $this->cObj->lastTypoLinkUrl;
			Header ('Location: ' . GeneralUtility::locationHeaderUrl ($rURL));
			exit;
		}
	}
	
	/**
	 * Method for post processing the rendered event
	 * 
	 * @return string $content
	 */
	public function finish(&$content) {
		$hookObjectsArr = $this->getHookObjectsArray ('finishViewRendering');
		// Hook: preFinishViewRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preFinishViewRendering')) {
				$hookObj->preFinishViewRendering ($this, $content);
			}
		}
		
		// translate output
		$this->translateLanguageMarker ($content);
		
		// Hook: postFinishViewRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postFinishViewRendering')) {
				$hookObj->postFinishViewRendering ($this, $content);
			}
		}
		return $content;
	}
	
	/**
	 * 
	 */
	public function translateLanguageMarker(&$content) {
		// translate leftover markers
		
		$match = array();
		preg_match_all ('!(###|%%%)([A-Z0-9_-|]*)\1!is', $content, $match);
		$allLanguageMarkers = array_unique ($match [2]);
		
		if (count ($allLanguageMarkers)) {
			$sims = array ();
			foreach ($allLanguageMarkers as $key => $marker) {
				$wrapper = $match [1] [$key];
				if (preg_match ('/.*_LABEL$/', $marker)) {
					$value = $this->pi_getLL ('l_' . strtolower (substr ($marker, 0, strlen ($marker) - 6)));
				} else if (preg_match ('/^L_.*/', $marker)) {
					$value = $this->pi_getLL (strtolower ($marker));
				} else if ($wrapper == '%%%') {
					$value = $this->pi_getLL ('l_' . strtolower ($marker));
				} else {
					$value = '';
				}
				$sims [$wrapper . $marker . $wrapper] = $value;
			}
			if (count ($sims)) {
				$content = $this->cObj->substituteMarkerArray ($content, $sims);
			}
		}
		return $content;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getPointerName() {
		return $this->pointerName;
	}
	
	/**
	 * 
	 * @param unknown $objectType
	 * @param unknown $additionalWhere
	 * @return array
	 */
	public function findRelatedEvents($objectType, $additionalWhere) {
		$relatedEvents = Array ();
		$modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$objectType . '.'] ['includeEventsInResult'] == 1) {
			$starttime = $this->getListViewTime ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->conf ['view'] . '.'] ['includeEventsInResult.'] ['starttime']);
			$endtime = $this->getListViewTime ($this->conf ['view.'] [$this->conf ['view'] . '.'] [$this->conf ['view'] . '.'] ['includeEventsInResult.'] ['endtime']);
			$relatedEvents = $modelObj->findEventsForList ($starttime, $endtime, '', $this->conf ['pidList'], '0,1,2,3', $additionalWhere);
		}
		return $relatedEvents;
	}
	
	/**
	 * Sets the PHP constant for the week start day.
	 * This must be called as
	 * early as possible to avoid PEAR Date defining its default instead.
	 *
	 * @return void
	 */
	public function setWeekStartDay() {
		if ($this->cObj->data ['pi_flexform']) {
			$this->pi_initPIflexForm (); // Init and get the flexform data of the plugin
			$piFlexForm = $this->cObj->data ['pi_flexform'];
			
			if ($this->conf ['dontListenToFlexForm.'] ['day.'] ['weekStartDay'] != 1) {
				Controller::updateIfNotEmpty ($this->conf ['view.'] ['weekStartDay'], $this->pi_getFFvalue ($piFlexForm, 'weekStartDay'));
			}
		}
		
		define ('DATE_CALC_BEGIN_WEEKDAY', $this->conf ['view.'] ['weekStartDay'] == 'Sunday' ? 0 : 1);
	}
}

?>