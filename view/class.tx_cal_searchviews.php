<?php
/**
 * *************************************************************
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
 * *************************************************************
 */

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_searchviews extends tx_cal_listview {
	var $confArr = array ();
	var $page;
	var $objectType;
	function tx_cal_searchviews() {
		$this->tx_cal_listview ();
	}
	
	/**
	 * Draws a single event.
	 * 
	 * @param
	 *        	array			The events to be drawn.
	 * @return string HTML output.
	 */
	function drawSearch(&$master_array, $getdate) {
		$this->_init ($master_array);
		
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['other.'] ['searchBoxTemplate']);
		if ($page == '') {
			return '<h3>calendar: no template file found:</h3>' . $this->conf ['view.'] ['other.'] ['searchBoxTemplate'];
		}
		return $this->finish ($page, array ());
	}
	
	/**
	 * Draws a search result view.
	 * 
	 * @param
	 *        	object Array of the events found ()
	 * @return string HTML output.
	 */
	function drawSearchAllResult(&$master_array, $starttime, $endtime, $searchword, $locationIds = '', $organizerIds = '') {
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['search.'] ['searchResultAllTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>' . $this->conf ['view.'] ['search.'] ['searchResultAllTemplate'];
		}
		
		$this->_init ($master_array);
		
		if (array_key_exists ('phpicalendar_event', $master_array)) {
			$sims ['SEARCHEVENTRESULTS'] = $this->drawSearchEventResult ($master_array ['phpicalendar_event'], $starttime, $endtime, $searchword, $locationIds, $organizerIds);
		}
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($sims ['SEARCHEVENTRESULTS']);
		$sims ['SEARCHEVENTRESULTS'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['search_all.'] ['searchEvent'], $this->conf ['view.'] ['search_all.'] ['searchEvent.']);
		
		$this->objectsInList = Array ();
		if (array_key_exists ('location', $master_array)) {
			$sims ['SEARCHLOCATIONRESULTS'] = $this->drawSearchLocationResult ($master_array ['location'], $searchword);
		}
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($sims ['SEARCHLOCATIONRESULTS']);
		$sims ['SEARCHLOCATIONRESULTS'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['search_all.'] ['searchLocation'], $this->conf ['view.'] ['search_all.'] ['searchLocation.']);
		
		$this->objectsInList = Array ();
		if (array_key_exists ('organizer', $master_array)) {
			$sims ['SEARCHORGANIZERRESULTS'] = $this->drawSearchOrganizerResult ($master_array ['organizer'], $searchword);
		}
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($sims ['SEARCHORGANIZERRESULTS']);
		$sims ['SEARCHORGANIZERRESULTS'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['search_all.'] ['searchOrganizer'], $this->conf ['view.'] ['search_all.'] ['searchOrganizer.']);
		
		$page = $this->controller->replace_tags ($sims, $page);
		$rems = array ();
		return $this->finish ($page, $rems);
	}
	function getSearchActionUrlMarker(&$page, &$sims, &$rems, $view) {
		$this->initLocalCObject ();
		$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, Array (), $this->conf ['cache'], true);
		$sims ['###SEARCH_ACTION_URL###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['search.'] ['searchLinkUrl'], $this->conf ['view.'] ['search.'] ['searchLinkUrl.']);
	}
	function getCategoryIdsMarker(&$page, &$sims, &$rems, $view) {
		$sims ['###CATEGORY_IDS###'] = '<option value="">' . $this->controller->pi_getLL ('l_all') . '</option>';
		$catArrayArray = $this->modelObj->findAllCategories ('cal_category_model', 'tx_cal_category', $this->conf ['pidList']);
		
		$rememberUid = array ();
		$ids = array ();
		if ($this->controller->piVars ['submit'] && $this->controller->piVars ['category']) {
			$ids = $this->controller->piVars ['category'];
		}
		
		foreach ($catArrayArray as $categoryArrayFromService) {
			foreach ($categoryArrayFromService [0] [0] as $category) {
				$uid = $category->getUid ();
				if (! in_array ($uid, $rememberUid)) {
					if (in_array ($uid, $ids)) {
						$sims ['###CATEGORY_IDS###'] .= '<option value="' . $uid . '" selected="selected">' . $category->getTitle () . '</option>';
					} else {
						$sims ['###CATEGORY_IDS###'] .= '<option value="' . $uid . '" >' . $category->getTitle () . '</option>';
					}
					$rememberUid [] = $uid;
				}
			}
		}
	}
	function getLocationIdsMarker(&$page, &$sims, &$rems, $view) {
		$sims ['###LOCATION_IDS###'] = '<option  value="">' . $this->controller->pi_getLL ('l_all') . '</option>';
		$this->confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$locationArray = $this->modelObj->findAllLocations ($this->confArr ['useLocationStructure'] ? $this->confArr ['useLocationStructure'] : 'tx_cal_location', $this->conf ['pidList']);
		
		$locationIdArray = Array ();
		if ($this->controller->piVars ['submit'] && $this->controller->piVars ['location_ids']) {
			$locationIdArray = $this->controller->piVars ['location_ids'];
		}
		
		if (is_array ($locationArray)) {
			foreach ($locationArray as $location) {
				if (in_array ($location->getUid (), $locationIdArray)) {
					$sims ['###LOCATION_IDS###'] .= '<option value="' . $location->getUid () . '" selected="selected">' . $location->getName () . '</option>';
				} else {
					$sims ['###LOCATION_IDS###'] .= '<option value="' . $location->getUid () . '">' . $location->getName () . '</option>';
				}
			}
		}
	}
	function getOrganizerIdsMarker(&$page, &$sims, &$rems, $view) {
		$sims ['###ORGANIZER_IDS###'] = '<option  value="">' . $this->controller->pi_getLL ('l_all') . '</option>';
		$this->confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$organizerArray = $this->modelObj->findAllOrganizer ($this->confArr ['useOrganizerStructure'] ? $this->confArr ['useOrganizerStructure'] : 'tx_cal_organizer', $this->conf ['pidList']);
		
		$organizerIdArray = Array ();
		if ($organizerIds != '') {
			$organizerIdArray = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode (',', $organizerIds);
		}
		
		if (is_array ($organizerArray)) {
			foreach ($organizerArray as $organizer) {
				if (in_array ($organizer->getUid (), $organizerIdArray)) {
					$sims ['###ORGANIZER_IDS###'] .= '<option value="' . $organizer->getUid () . '" selected="selected">' . $organizer->getName () . '</option>';
				} else {
					$sims ['###ORGANIZER_IDS###'] .= '<option value="' . $organizer->getUid () . '">' . $organizer->getName () . '</option>';
				}
			}
		}
	}
	function getSelector(&$page, &$sims, &$rems, $view) {
		$useDateSelector = false;
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('rlmp_dateselectlib')) {
			require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('rlmp_dateselectlib') . 'class.tx_rlmpdateselectlib.php');
			tx_rlmpdateselectlib::includeLib ();
			
			/* Only read date selector option if rlmp_dateselectlib is installed */
			$useDateSelector = $this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['useDateSelector'];
		}
		$outputFormat = tx_cal_functions::getFormatStringFromConf ($this->conf);
		
		$dateSelectorConf = array (
				'calConf.' => $this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['rlmp_dateselectorlib_config.'] 
		);
		
		$dateSelectorConf ['calConf.'] ['dateTimeFormat'] = $outputFormat;
		$dateSelectorConf ['calConf.'] ['inputFieldDateTimeFormat'] = $outputFormat;
		
		$sims ['###SINGLE_DAY_SELECTOR###'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('single_date', $dateSelectorConf) : '';
		$sims ['###START_DAY_SELECTOR###'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_start_day', $dateSelectorConf) : '';
		$sims ['###END_DAY_SELECTOR###'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_end_day', $dateSelectorConf) : '';
	}
	function getStartDaySelectorMarker(&$page, &$sims, &$rems, $view) {
		if (! $sims ['###START_DATE_SELECTOR###']) {
			$this->getSelector ($page, $sims, $rems, $view);
		}
	}
	function getEndDaySelectorMarker(&$page, &$sims, &$rems, $view) {
		if (! $sims ['###END_DATE_SELECTOR###']) {
			$this->getSelector ($page, $sims, $rems, $view);
		}
	}
	function getSingleDaySelectorMarker(&$page, &$sims, &$rems, $view) {
		if (! $sims ['###SINGLE_DATE_SELECTOR###']) {
			$this->getSelector ($page, $sims, $rems, $view);
		}
	}
	function getStartAndEnd(&$page, &$sims, &$rems, $view) {
		$outputFormat = tx_cal_functions::getFormatStringFromConf ($this->conf);
		if (! $this->controller->piVars ['submit']) {
			$date = $this->controller->getListViewTime ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['start_day']);
			$sims ['###EVENT_START_DAY###'] = $date->format ($outputFormat);
			$date = $this->controller->getListViewTime ($this->conf ['view.'] ['search.'] ['defaultValues.'] ['end_day']);
			$sims ['###EVENT_END_DAY###'] = $date->format ($outputFormat);
		} else {
			if (intval ($this->controller->piVars ['start_day']) == 0) {
				$sims ['###EVENT_START_DAY###'] = $this->starttime->format ($outputFormat);
			} else {
				$sims ['###EVENT_START_DAY###'] = htmlspecialchars (strip_tags ($this->controller->piVars ['start_day']));
			}
			
			if (intval ($this->controller->piVars ['end_day']) == 0) {
				$sims ['###EVENT_END_DAY###'] = $this->endtime->format ($outputFormat);
			} else {
				$sims ['###EVENT_END_DAY###'] = htmlspecialchars (strip_tags ($this->controller->piVars ['end_day']));
			}
		}
	}
	function getEventStartDayMarker(&$page, &$sims, &$rems, $view) {
		if (! $sims ['###EVENT_START_DAY###']) {
			$this->getStartAndEnd ($page, $sims, $rems, $view);
		}
	}
	function getEventEndDayMarker(&$page, &$sims, &$rems, $view) {
		if (! $sims ['###EVENT_END_DAY###']) {
			$this->getStartAndEnd ($page, $sims, $rems, $view);
		}
	}
	
	/**
	 * Draws a search result view.
	 * 
	 * @param
	 *        	object The events found
	 * @return string HTML output.
	 */
	function drawSearchEventResult(&$master_array, $starttime, $endtime, $searchword, $locationIds = '', $organizerIds = '') {
		$this->objectType = 'event';
		$this->_init ($master_array);
		return $this->drawList ($master_array, '', $starttime, $endtime);
	}
	
	/**
	 * Draws a search result view.
	 * 
	 * @param
	 *        	object The location found
	 * @return string HTML output.
	 */
	function drawSearchLocationResult(&$master_array, $searchword) {
		return $this->drawSearchOrganizerResult ($master_array, $searchword, 'location');
	}
	
	/**
	 * Draws a search result view.
	 * 
	 * @param
	 *        	object The organizer found
	 * @return string HTML output.
	 */
	function drawSearchOrganizerResult(&$master_array, $searchword, $objectType = 'organizer') {
		$this->objectType = $objectType;
		$sims = array ();
		$this->_init ($master_array);
		
		if (count ($master_array, 1) == 2) 		// only one object element in the array
		{
			if ($this->objectType == 'organizer') {
				return $this->drawOrganizer (array_pop (array_pop ($master_array)), $this->conf ['getdate']);
			} else if ($this->objectType == 'location') {
				return $this->drawLocation (array_pop (array_pop ($master_array)), $this->conf ['getdate']);
			}
		}
		$starttime = new tx_cal_date ();
		$endtime = new tx_cal_date ();
		return $this->drawList ($master_array, '', $starttime, $endtime);
	}
	function initTemplate(&$page) {
		if ($page == '') {
			$page = $this->cObj->fileResource ($this->conf ['view.'] ['search.'] ['searchResult' . ucwords ($this->objectType) . 'Template']);
			if ($page == '') {
				$this->error = true;
				$this->errorMessage = 'No search ' . $this->objectType . ' result template file found for "view.search.searchResult' . ucwords ($this->objectType) . 'Template" at >' . $this->conf ['view.'] ['search.'] ['searchResult' . ucwords ($this->objectType) . 'Template'] . '<';
				$this->suggestMessage = 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.';
				return;
			}
		}
		if ($this->conf ['view'] == 'search_all') {
			$rems ['###SEARCHFORM###'] = '';
			$page = tx_cal_functions::substituteMarkerArrayNotCached ($page, array (), $rems, array ());
		}
		$this->page = $page;
	}
	function getListSubpart(&$page) {
		$listTemplate = $this->cObj->getSubpart ($page, '###LIST_TEMPLATE###');
		if ($listTemplate == '') {
			$this->error = true;
			$this->errorMessage = 'No ###LIST_TEMPLATE### subpart found in "view.search.searchResult' . ucwords ($this->objectType) . 'Template" at >' . $this->conf ['view.'] ['search.'] ['searchResult' . ucwords ($this->objectType) . 'Template'] . '<';
			$this->suggestMessage = 'Please include a ###LIST_TEMPLATE### subpart.';
			return null;
		}
		return $listTemplate;
	}
	function processObjects(&$master_array, &$sims, &$rems) {
		if ($this->objectType == 'event') {
			return parent::processObjects ($master_array, $sims, $rems);
		}
		// clear the register
		$GLOBALS ['TSFE']->register ['cal_list_firstevent'] = 0;
		$GLOBALS ['TSFE']->register ['cal_list_lastevent'] = 0;
		$GLOBALS ['TSFE']->register ['cal_list_events_total'] = 0;
		$GLOBALS ['TSFE']->register ['cal_list_eventcounter'] = 0;
		$GLOBALS ['TSFE']->register ['cal_list_days_total'] = 0;
		
		$sectionMenu = '';
		$middle = '';
		
		// only proceed if the master_array is not empty
		if (count ($master_array)) {
			
			$this->count = 0;
			$this->eventCounter = array ();
			$this->listStartOffsetCounter = 0;
			$this->listStartOffset = intval ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['listStartOffset']);
			
			if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['pageBrowser.'] ['usePageBrowser']) {
				$this->offset = intval ($this->controller->piVars [$this->pointerName]);
				$this->recordsPerPage = intval ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['pageBrowser.'] ['recordsPerPage']);
			}
			
			$this->walkThroughMasterArray ($master_array, $reverse, $firstEventDate);
			
			if ($this->count) {
				$GLOBALS ['TSFE']->register ['cal_list_events_total'] = $this->count;
				// reference the array with all event counts in the TYPO3 register for usage from within hooks or whatever
				$GLOBALS ['TSFE']->register ['cal_list_eventcounter'] = &$this->eventCounter;
			}
			if ($days = count ($this->objectsInList)) {
				$GLOBALS ['TSFE']->register ['cal_list_days_total'] = $days;
			}
			
			// start rendering the organizer
			if (count ($this->objectsInList) && $this->count > 0) {
				$times = array_keys ($this->objectsInList);
				if ($times)
					// preset vars
					$firstTime = true;
				$listItemCount = 0;
				$alternationCount = 0;
				$pageItemCount = $this->recordsPerPage * $this->offset;
				
				// don't assign these dates in one line like "$date1 = $date2 = $date3 = new tx_cal_date()", as this will make all dates references to each other!!!
				$lastEventDay = new tx_cal_date ('000000001000000');
				$lastEventWeek = new tx_cal_date ('000000001000000');
				$lastEventMonth = new tx_cal_date ('000000001000000');
				$lastEventYear = new tx_cal_date ('000000001000000');
				
				// prepare alternating layouts
				$alternatingLayoutConfig = $this->conf ['view.'] [$this->conf ['view'] . '.'] ['alternatingLayoutMarkers.'];
				if (is_array ($alternatingLayoutConfig) && count ($alternatingLayoutConfig)) {
					$alternatingLayouts = array ();
					$layout_keys = array_keys ($alternatingLayoutConfig);
					foreach ($layout_keys as $key) {
						if (substr ($key, strlen ($key) - 1) != '.') {
							$suffix = $this->cObj->stdWrap ($alternatingLayoutConfig [$key], $alternatingLayoutConfig [$key . '.']);
							if ($suffix) {
								$alternatingLayouts [] = $suffix;
							}
						}
					}
				} else {
					$alternatingLayouts = array (
							'ODD',
							'EVEN' 
					);
				}
				
				// Hook: get hook objects for drawList
				$hookObjectsArr = tx_cal_functions::getHookObjectsArray ('tx_cal_searchview', 'drawList', 'view');
				
				if ($reverse) {
					arsort ($times);
				} else {
					asort ($times);
				}
				
				foreach ($times as $cal_time) {
					$object = &$this->objectsInList [$cal_time];
					
					// Hook: innerObjectWrapper
					if (count ($hookObjectsArr)) {
						// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
						$hookObjectKeys = array_keys ($hookObjectsArr);
						foreach ($hookObjectKeys as $hookObjKey) {
							$hookObj = &$hookObjectsArr [$hookObjKey];
							if (method_exists ($hookObj, 'innerObjectWrapper')) {
								$hookObj->innerObjectWrapper ($this, $middle, $object);
							}
						}
					}
					
					$listItemCount ++;
					$totalListCount = $listItemCount + $pageItemCount;
					$GLOBALS ['TSFE']->register ['cal_event_list_num'] = $listItemCount;
					$GLOBALS ['TSFE']->register ['cal_event_list_num_total'] = $totalListCount;
					
					$layoutNum = $alternationCount % count ($alternatingLayouts);
					$layoutSuffix = $alternatingLayouts [$layoutNum];
					$objectText = '';
					$tempSims = array ();
					$tempRems = array ();
					$wrapped = array ();
					$functionName = 'render' . ucwords ($this->objectType) . 'For';
					$objectText = $object->$functionName (strtoupper ($this->conf ['view']), $layoutSuffix);
					
					$allowFurtherGrouping = true;
					// Hook: prepareOuterObjectWrapper
					if (count ($hookObjectsArr)) {
						// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
						$hookObjectKeys = array_keys ($hookObjectsArr);
						foreach ($hookObjectKeys as $hookObjKey) {
							$hookObj = &$hookObjectsArr [$hookObjKey];
							if (method_exists ($hookObj, 'prepareOuterObjectWrapper')) {
								$hookObj->prepareOuterObjectWrapper ($this, $middle, $object, $allowFurtherGrouping);
							}
						}
					}
					
					$alternationCount ++;
					$firstTime = false;
					
					$middle .= $objectText;
				}
				
				$allowFurtherGrouping = true;
				
				// Hook: applyOuterObjectWrapper
				if (count ($hookObjectsArr)) {
					// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
					$hookObjectKeys = array_keys ($hookObjectsArr);
					foreach ($hookObjectKeys as $hookObjKey) {
						$hookObj = &$hookObjectsArr [$hookObjKey];
						if (method_exists ($hookObj, 'applyOuterObjectWrapper')) {
							$hookObj->applyOuterObjectWrapper ($this, $middle, $object, $allowFurtherGrouping);
						}
					}
				}
			}
		}
		return $middle;
	}
	function walkThroughMasterArray(&$master_array, &$reverse, &$firstEventDate) {
		if ($this->objectType == 'event') {
			return parent::walkThroughMasterArray ($master_array, $reverse, $firstEventDate);
		}
		if (is_array ($master_array)) {
			foreach ($master_array as $a => $b) {
				if (is_array ($b)) {
					foreach ($b as $id => $object) {
						$this->processObject ($object, $id, $firstEventDate);
					}
				}
			}
		}
	}
	function processObject(&$object, $id, &$firstEventDate) {
		if ($this->objectType == 'event') {
			return parent::processObject ($object, $id, $firstEventDate);
		}
		// Pagebrowser
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['pageBrowser.'] ['usePageBrowser']) {
			$this->eventCounter ['count'] ['total'] ++;
			if ($this->count < $this->recordsPerPage * $this->offset) {
				$this->eventCounter ['count'] ['previousPages'] ++;
			} else if ($this->count > $this->recordsPerPage * $this->offset + $this->recordsPerPage - 1) {
				$this->eventCounter ['count'] ['nextPages'] ++;
			} else {
				$this->eventCounter ['count'] ['currentPage'] ++;
			}
			
			if ($this->count < $this->recordsPerPage * $this->offset || $this->count > $this->recordsPerPage * $this->offset + $this->recordsPerPage - 1) {
				$this->count ++;
				if ($this->count == intval ($this->conf ['view.'] ['list.'] ['maxEvents'])) {
					$finished = true;
				}
				return $finished;
			}
		}
		$this->objectsInList [] = $object;
		$this->count ++;
	}
	function getSearchAllLinkMarker(&$page, &$sims, &$rems, $view) {
		$sims ['###SEARCH_ALL_LINK###'] = '';
		if ($this->rightsObj->isViewEnabled ('search_all') && $this->conf ['view'] != 'search_all') {
			$this->initLocalCObject ();
			$this->local_cObj->setCurrentVal ($this->controller->pi_getLL ('l_search_everything'));
			$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, array (
					'view' => 'search_all' 
			), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['page_id']);
			$sims ['###SEARCH_ALL_LINK###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['search.'] ['searchAllLink'], $this->conf ['view.'] ['search.'] ['searchAllLink.']);
		}
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_searchviews.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_searchviews.php']);
}
?>