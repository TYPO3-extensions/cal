<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Steffen Kamper
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
class tx_cal_listview extends tx_cal_base_view {
	var $eventCounter = array();
	var $error = false;
	var $reverse = false;
	var $errorMessage = '';
	var $suggestMessage = '';
	
	var $starttime;
	var $endtime;
	
	var $objectsInList = Array();
	
	var $count;
	var $offset;
	var $recordsPerPage;
	var $listStartOffsetCounter;
	var $listStartOffset;
	
	function tx_cal_listview(){
		$this->tx_cal_base_view();
	}
	
	function initTemplate(&$page){
		if($page==''){
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($confArr['useTeaser']) {
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listWithTeaserTemplate']);
			}else{
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listTemplate']);
			}
			if ($page == '') {
				$this->error = true;
				if($confArr['useTeaser']) {
					$this->errorMessage = 'No list template file found for "view.list.listWithTeaserTemplate" at >'.$this->conf['view.']['list.']['listWithTeaserTemplate'].'<';
				} else {
					$this->errorMessage = 'No list template file found for "view.list.listTemplate" at >'.$this->conf['view.']['list.']['listTemplate'].'<';
				}
				$this->suggestMessage = 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.';
				return;
			}
		}
	}
	
	function getListSubpart($page){
		$listTemplate = $this->cObj->getSubpart($page, '###LIST_TEMPLATE###');
		if($listTemplate==''){
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($confArr['useTeaser']) {
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listWithTeaserTemplate']);
			}else{
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listTemplate']);
			}
			if ($page == '') {
				$this->error = true;
				if($confArr['useTeaser']) {
					$this->errorMessage = 'No list template file found for "view.list.listWithTeaserTemplate" at >'.$this->conf['view.']['list.']['listWithTeaserTemplate'].'<';
				} else {
					$this->errorMessage = 'No list template file found for "view.list.listTemplate" at >'.$this->conf['view.']['list.']['listTemplate'].'<';
				}
				$this->suggestMessage = 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.';
				return null;
			}
			$listTemplate = $this->cObj->getSubpart($page, '###LIST_TEMPLATE###');
			if($listTemplate==''){
				$this->error = true;
				if($confArr['useTeaser']) {
					$this->errorMessage = 'No list template file found for "view.list.listWithTeaserTemplate" at >'.$this->conf['view.']['list.']['listWithTeaserTemplate'].'<';
				} else {
					$this->errorMessage = 'No ###LIST_TEMPLATE### subpart found in "view.list.listTemplate" at >'.$this->conf['view.']['list.']['listTemplate'].'<';
				}
				$this->suggestMessage = 'Please include a ###LIST_TEMPLATE### subpart.';
				return null;
			}
		}
		return $listTemplate;
	}
	
	function processObjects(&$master_array, &$sims, &$rems){
		/* Subtract strtotimeOffset because we're going from GMT back to local time */
		if ($this->reverse) {
			$GLOBALS['TSFE']->register['cal_list_starttime'] = $this->endtime->getTime();
			$GLOBALS['TSFE']->register['cal_list_endtime'] = $this->starttime->getTime();
		} else {
			$GLOBALS['TSFE']->register['cal_list_starttime'] = $this->starttime->getTime();
			$GLOBALS['TSFE']->register['cal_list_endtime'] = $this->endtime->getTime();
		}
		
		// clear the register
		$GLOBALS['TSFE']->register['cal_list_firstevent'] = 0;
		$GLOBALS['TSFE']->register['cal_list_lastevent'] = 0;
		$GLOBALS['TSFE']->register['cal_list_events_total'] = 0;
		$GLOBALS['TSFE']->register['cal_list_eventcounter'] = 0;
		$GLOBALS['TSFE']->register['cal_list_days_total'] = 0;
		
		$sectionMenu = '';
		
		// only proceed if the master_array is not empty
		if(count($master_array)) {

			$this->count = 0;
			$this->eventCounter = array();
			$this->listStartOffsetCounter = 0;
			$this->listStartOffset = intval($this->conf['view.'][$this->conf['view'].'.']['listStartOffset']);
	
			if($this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['usePageBrowser']) {
				$this->offset=intval($this->controller->piVars[$this->pointerName]);
				$this->recordsPerPage=intval($this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['recordsPerPage']);
			}
			
			$this->walkThroughMasterArray($master_array, $this->reverse, $firstEventDate);
			
			if($firstEventDate){
				$GLOBALS['TSFE']->register['cal_list_firstevent'] = $firstEventDate->getTime();
			}
			if($lastEventDate){
				$GLOBALS['TSFE']->register['cal_list_lastevent'] = $lastEventDate->getTime();
			}
			if($this->count) {
				$GLOBALS['TSFE']->register['cal_list_events_total'] = $this->count;
				// reference the array with all event counts in the TYPO3 register for usage from within hooks or whatever
				$GLOBALS['TSFE']->register['cal_list_eventcounter'] = &$this->eventCounter;
			}
			if($days = count($this->objectsInList)) {
				$GLOBALS['TSFE']->register['cal_list_days_total'] = $days;
			}

			// start rendering the events
			if(count($this->objectsInList) && $this->count > 0) {
				$times = array_keys($this->objectsInList);

				// preset vars
				$firstTime = true;
				$listItemCount = 0;
				$alternationCount = 0;
				$pageItemCount = $this->recordsPerPage*$this->offset;
				
				// don't assign these dates in one line like "$date1 = $date2 = $date3 = new tx_cal_date()", as this will make all dates references to each other!!!
				$lastEventDay = new tx_cal_date('000000001000000');
				$lastEventWeek = new tx_cal_date('000000001000000');
				$lastEventMonth = new tx_cal_date('000000001000000');
				$lastEventYear = new tx_cal_date('000000001000000');

				$categoryGroupArray = array();
				$categoryArray = array();
				if ($this->conf['view.']['list.']['enableCategoryWrapper']){
					$allCategoryArray = $this->modelObj->findAllCategories('','',$this->conf['pidList']);
					$categoryArray = (Array)$allCategoryArray['tx_cal_category'][0][0];
				}
				$calendarGroupArray = array();
				$calendarArray = array();
				if ($this->conf['view.']['list.']['enableCalendarWrapper']){
					$allCalendarArray = $this->modelObj->findAllCalendar('',$this->conf['pidList']);
					$calendarArray = (Array)$allCalendarArray['tx_cal_calendar'];
				}


				// prepare alternating layouts
				$alternatingLayoutConfig = $this->conf['view.'][$this->conf['view'].'.']['alternatingLayoutMarkers.'];
				if (is_array($alternatingLayoutConfig) && count($alternatingLayoutConfig)) {
					$alternatingLayouts = array();
					$layout_keys = array_keys($alternatingLayoutConfig);
					foreach ($layout_keys as $key) {
						if (substr($key,strlen($key)-1) != '.') {
							$suffix = $this->cObj->stdWrap($alternatingLayoutConfig[$key],$alternatingLayoutConfig[$key.'.']);
							if ($suffix) {
								$alternatingLayouts[] = $suffix;
							}
						}
					}
				} else {
					$alternatingLayouts = array('LIST_ODD','LIST_EVEN');
				}


				// Hook: get hook objects for drawList
				$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_listview','drawList','view');

				if ($this->reverse) {
					arsort($times);
				} else {
					asort($times);
				}
				
				foreach($times as $cal_time) {
					$e_keys = array_keys($this->objectsInList[$cal_time]);
					
					// Hook: postSort
					if(count($hookObjectsArr)) {
						// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
						$hookObjectKeys = array_keys($hookObjectsArr);
						foreach ($hookObjectKeys as $hookObjKey) {
							$hookObj = &$hookObjectsArr[$hookObjKey];
							if (method_exists($hookObj, 'postSort')) {
								$hookObj->postSort($this, $e_keys, $cal_time);
							}
						}
					}
				
					unset($calTimeObject);

					$calTimeObject = new tx_cal_date($cal_time.'000000');
					$calTimeObject->setTZbyId('UTC');
	
					$cal_day = $calTimeObject->getDay();
					$cal_month = $calTimeObject->getMonth();
					$cal_year = $calTimeObject->getYear();
					$cal_week = $calTimeObject->format('%U');
					
					if(count($hookObjectsArr)) {
						$hookParams = array(
							'cal_day' => &$cal_day,
							'cal_month' => &$cal_month,
							'cal_year' => &$cal_year,
							'cal_week' => &$cal_week,
							'alternationCount' => &$alternationCount,
							'reverse' => $this->reverse,
						);
					}

					if($firstTime) {
						$yearItemCounter = (int)$this->eventCounter['byYear'][$cal_year]['previousPages'];
						$monthItemCounter = (int)$this->eventCounter['byYearMonth'][$cal_year][$cal_month]['previousPages'];
						$weekItemCounter = (int)$this->eventCounter['byWeek'][$cal_week]['previousPages'];
						$dayItemCounter = (int)$this->eventCounter['byDate'][$cal_year][$cal_month][$cal_day]['previousPages'];
					}
					
					foreach($e_keys as $e_key) {
						$event = &$this->objectsInList[$cal_time][$e_key];
						
						if($firstTime) {
								$eventStart = $event->getStart();
								$lastEventDay->copy($eventStart);
								$lastEventMonth->copy($eventStart);
								$lastEventWeek->copy($eventStart);
								$lastEventYear->copy($eventStart);
						}

						// Hook: preInnerEventWrapper
						if(count($hookObjectsArr)) {
							// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
							$hookObjectKeys = array_keys($hookObjectsArr);
							foreach ($hookObjectKeys as $hookObjKey) {
								$hookObj = &$hookObjectsArr[$hookObjKey];
								if (method_exists($hookObj, 'preInnerEventWrapper')) {
									$hookObj->preInnerEventWrapper($this, $middle, $event, $calTimeObject, $firstTime, $hookParams);
								}
							}
						}

						//yearwrapper
						if($this->conf['view.']['list.']['enableYearWrapper'] && ($this->hasPeriodChanged($lastEventYear->getYear(),$cal_year,$this->reverse) || $firstTime)){
							$this->initLocalCObject();
							if($this->conf['view.']['list.']['enableSectionMenu']){
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['yearSectionMenuFormat']));
								$this->local_cObj->data['link_parameter'] = '#'.$calTimeObject->format('%Y');
								$sectionMenu .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['sectionMenuItem'],$this->conf['view.']['list.']['sectionMenuItem.']);
							}
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['yearWrapperFormat']));
							if(!$firstTime){
								$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['yearWrapperEnd'],$this->conf['view.']['list.']['yearWrapperEnd.']);
							}
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['yearWrapper'],$this->conf['view.']['list.']['yearWrapper.']);
							$lastEventYear->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterYearWrapper']) $alternationCount = 0;
							if(!$firstTime) $yearItemCounter = 0;
						}
						//monthwrapper
						if($this->conf['view.']['list.']['enableMonthWrapper'] && ($this->hasPeriodChanged($lastEventMonth->format('%Y%m'),$calTimeObject->format('%Y%m'),$this->reverse) || $firstTime || $this->hasPeriodChanged($lastEventMonth->getYear(),$cal_year,$this->reverse))){
							$this->initLocalCObject();
							if($this->conf['view.']['list.']['enableSectionMenu']){
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['monthSectionMenuFormat']));
								$this->local_cObj->data['link_parameter'] = '#'.$calTimeObject->format('%Y%m');
								$sectionMenu .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['sectionMenuItem'],$this->conf['view.']['list.']['sectionMenuItem.']);
							}
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['monthWrapperFormat']));
							if(!$firstTime){
								$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapperEnd'],$this->conf['view.']['list.']['monthWrapperEnd.']);
							}
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapper'],$this->conf['view.']['list.']['monthWrapper.']);
							$lastEventMonth->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterMonthWrapper']) $alternationCount = 0;
							if(!$firstTime) $monthItemCounter = 0;
						}
						//weekwrapper
						if($this->conf['view.']['list.']['enableWeekWrapper'] && ($this->hasPeriodChanged($lastEventWeek->format('%U'),$cal_week,$this->reverse) || $firstTime || $this->hasPeriodChanged($lastEventWeek->getYear(),$cal_year,$this->reverse))){
							$this->initLocalCObject();
							if($this->conf['view.']['list.']['enableSectionMenu']){
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['weekSectionMenuFormat']));
								$this->local_cObj->data['link_parameter'] = '#'.$calTimeObject->format('%Y%U');
								$sectionMenu .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['sectionMenuItem'],$this->conf['view.']['list.']['sectionMenuItem.']);
							}
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['weekWrapperFormat']));
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['weekWrapper'],$this->conf['view.']['list.']['weekWrapper.']);
							$lastEventWeek->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterWeekWrapper']) $alternationCount = 0;
							if(!$firstTime) $weekItemCounter = 0;
						}
						//daywrapper
						if($this->conf['view.']['list.']['enableDayWrapper'] && (($this->reverse ? $lastEventDay->after($calTimeObject) : $lastEventDay->before($calTimeObject)) || $firstTime || $this->hasPeriodChanged($lastEventDay->getYear(),$cal_year,$this->reverse))){
							$this->initLocalCObject();
							if($this->conf['view.']['list.']['enableSectionMenu']){
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['daySectionMenuFormat']));
								$this->local_cObj->data['link_parameter'] = '#'.$calTimeObject->format('%Y%m%d');
								$sectionMenu .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['sectionMenuItem'],$this->conf['view.']['list.']['sectionMenuItem.']);
							}
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['dayWrapperFormat']));
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['dayWrapper'],$this->conf['view.']['list.']['dayWrapper.']);
							$lastEventDay->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterDayWrapper']) $alternationCount = 0;
							if(!$firstTime) $dayItemCounter = 0;
						}

						// Hook: postInnerEventWrapper
						if(count($hookObjectsArr)) {
							// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
							$hookObjectKeys = array_keys($hookObjectsArr);
							foreach ($hookObjectKeys as $hookObjKey) {
								$hookObj = &$hookObjectsArr[$hookObjKey];
								if (method_exists($hookObj, 'postInnerEventWrapper')) {
									$hookObj->postInnerEventWrapper($this, $middle, $event, $calTimeObject, $firstTime, $hookParams);
								}
							}
						}

						$listItemCount++;
						$monthItemCounter++;
						$weekItemCounter++;
						$dayItemCounter++;
						$yearItemCounter++;
						$totalListCount = $listItemCount + $pageItemCount;
						$GLOBALS['TSFE']->register['cal_event_list_num'] = $listItemCount;
						$GLOBALS['TSFE']->register['cal_event_list_num_total'] = $totalListCount;
						$GLOBALS['TSFE']->register['cal_event_list_num_in_day'] = $dayItemCounter;
						$GLOBALS['TSFE']->register['cal_event_list_num_in_week'] = $weekItemCounter;
						$GLOBALS['TSFE']->register['cal_event_list_num_in_month'] = $monthItemCounter;
						$GLOBALS['TSFE']->register['cal_event_list_num_in_year'] = $yearItemCounter;
						
						$layoutNum = $alternationCount % count($alternatingLayouts);
						$layoutSuffix = $alternatingLayouts[$layoutNum];
						$eventText = '';
						if($this->conf['view']=='location' || $this->conf['view']=='organizer' || $this->conf['view']=='event'){
							$eventText = $event->renderEventForList(strtoupper($this->conf['view']).'_'.$layoutSuffix);
						} else {
							$eventText = $event->renderEventForList($layoutSuffix);
						}

						$allowFurtherGrouping = true;
						// Hook: prepareOuterEventWrapper
						if(count($hookObjectsArr)) {
							// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
							$hookObjectKeys = array_keys($hookObjectsArr);
							foreach ($hookObjectKeys as $hookObjKey) {
								$hookObj = &$hookObjectsArr[$hookObjKey];
								if (method_exists($hookObj, 'prepareOuterEventWrapper')) {
									$hookObj->prepareOuterEventWrapper($this, $middle, $event, $calTimeObject, $firstTime, $hookParams, $allowFurtherGrouping);
								}
							}
						}
						
						if($allowFurtherGrouping) {
							if($this->conf['view.']['list.']['enableCategoryWrapper']){
								$ids = $event->getCategoryUidsAsArray();
								if(empty($ids)){
									$categoryGroupArray[$this->conf['view.']['list.']['noCategoryWrapper.']['uid']] .= $eventText;
								} else {
									$rememberUid = array();
						
									foreach($categoryArray as $categoryObject){
										if(!in_array($categoryObject->getUid(), $rememberUid)){
											if(in_array($categoryObject->getUid(),$ids)){
												$categoryGroupArray[$categoryObject->getUid()] .= $eventText;
											}
											$rememberUid[] = $categoryObject->getUid();
										}
									}
								}
							}else if($this->conf['view.']['list.']['enableCalendarWrapper']){
								$id = $event->getCalendarUid();
								foreach($calendarArray as $calendarObject){
									if($calendarObject->getUid()==$id){
										$calendarGroupArray[$calendarObject->getTitle()] .= $eventText;
									}
								}
							} else {
								$middle .= $eventText;
							}
						}

						$alternationCount++;
						$firstTime = false;
					}
				}

				$allowFurtherGrouping = true;
				
				// Hook: applyOuterEventWrapper
				if(count($hookObjectsArr)) {
					// use referenced hook objects, so that hook objects can store variables among different hook calls internally and don't have to mess with globals or registers
					$hookObjectKeys = array_keys($hookObjectsArr);
					foreach ($hookObjectKeys as $hookObjKey) {
						$hookObj = &$hookObjectsArr[$hookObjKey];
						if (method_exists($hookObj, 'applyOuterEventWrapper')) {
							$hookObj->applyOuterEventWrapper($this, $middle, $event, $allowFurtherGrouping);
						}
					}
				}

				if($allowFurtherGrouping) {
					//additional Wrapper
					if($this->conf['view.']['list.']['enableCalendarWrapper']){
						$this->initLocalCObject();
						foreach($calendarGroupArray as $calTitel => $calendarEntries){
							$this->local_cObj->setCurrentVal($calTitel);
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['calendarWrapper'],$this->conf['view.']['list.']['calendarWrapper.']);
							$middle .= $calendarEntries;
						}
					}
					if($this->conf['view.']['list.']['enableCategoryWrapper']){
						$keys = array_keys($categoryGroupArray);
						sort($keys);
						foreach($keys as $categoryId){
							if($categoryId==$this->conf['view.']['list.']['noCategoryWrapper.']['uid']){
								$this->initLocalCObject();
								$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['noCategoryWrapper'],$this->conf['view.']['list.']['noCategoryWrapper.']);
							} else {
								$currentCategory = &$categoryArray[$categoryId];
								$this->initLocalCObject($currentCategory->getValuesAsArray());
								$this->local_cObj->setCurrentVal($currentCategory->getTitle());
								$text = $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['categoryWrapper.']['10'],$this->conf['view.']['list.']['categoryWrapper.']['10.']);
								$middle .= str_replace('###CATEGORY_STYLE###',$currentCategory->getHeaderStyle(),$text);
							}
							$middle .= $categoryGroupArray[$categoryId];
						}
					}
				}
			}
		}
		return $middle;
	}
	
	function processObject(&$event, &$cal_time, &$firstEventDate){
		$eventStart = $event->getStart();
		$eventEnd = $event->getEnd();

		if ($eventEnd->before($this->starttime) || $eventStart->after($this->endtime)){
			return;
		}

		/* If we haven't saved an event date already, save this one */
		if (!$firstEventDate) {
			$firstEventDate = new tx_cal_date();
			if ($this->reverse) {
				$firstEventDate->copy($eventEnd);
			} else {
				$firstEventDate->copy($eventStart);
			}
		}
		/* Always save the current event date as the last one and let it fall through */
		if ($this->reverse) {
			$lastEventDate = $eventStart;
		} else {
			$lastEventDate = $eventEnd;
		}

		$year = $eventStart->getYear();
		$month = $eventStart->getMonth();
		$day = $eventStart->getDay();
		$week = $eventStart->format('%U');
		$this->eventCounter['byDate'][$year][$month][$day]['total']++;
		$this->eventCounter['byWeek'][$week]['total']++;
		$this->eventCounter['byYear'][$year]['total']++;
		$this->eventCounter['byMonth'][$month]['total']++;
		$this->eventCounter['byDay'][$day]['total']++;
		$this->eventCounter['byYearMonth'][$year][$month]['total']++;
		$this->eventCounter['byYearDay'][$year][$day]['total']++;

		//Pagebrowser
		if ($this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['usePageBrowser']) {
			if ($this->count<$this->recordsPerPage*$this->offset) {
				$this->eventCounter['byDate'][$year][$month][$day]['previousPages']++;
				$this->eventCounter['byWeek'][$week]['previousPages']++;
				$this->eventCounter['byYear'][$year]['previousPages']++;
				$this->eventCounter['byMonth'][$month]['previousPages']++;
				$this->eventCounter['byYearMonth'][$year][$month]['previousPages']++;
				$this->eventCounter['byDay'][$day]['previousPages']++;
				$this->eventCounter['byYearDay'][$year][$day]['previousPages']++;
			} else if ($this->count>$this->recordsPerPage*$this->offset+$this->recordsPerPage-1) {
				$this->eventCounter['byDate'][$year][$month][$day]['nextPages']++;
				$this->eventCounter['byWeek'][$week]['nextPages']++;
				$this->eventCounter['byYear'][$year]['nextPages']++;
				$this->eventCounter['byMonth'][$month]['nextPages']++;
				$this->eventCounter['byYearMonth'][$year][$month]['nextPages']++;
				$this->eventCounter['byDay'][$day]['nextPages']++;
				$this->eventCounter['byYearDay'][$year][$day]['nextPages']++;
			} else {
				$this->eventCounter['byDate'][$year][$month][$day]['currentPage']++;
				$this->eventCounter['byWeek'][$week]['currentPage']++;
				$this->eventCounter['byYear'][$year]['currentPage']++;
				$this->eventCounter['byMonth'][$month]['currentPage']++;
				$this->eventCounter['byYearMonth'][$year][$month]['currentPage']++;
				$this->eventCounter['byDay'][$day]['currentPage']++;
				$this->eventCounter['byYearDay'][$year][$day]['currentPage']++;
			}
			
			if ($this->count<$this->recordsPerPage*$this->offset || $this->count>$this->recordsPerPage*$this->offset+$this->recordsPerPage-1) {
				$this->count++;
				if ($this->count == intval($this->conf['view.']['list.']['maxEvents'])) {
					$finished = true;
				}
				return $finished;
			}
		}

		// reference the event in the rendering array
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_listview','sorting','view');
		if(count($hookObjectsArr)) {
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists($hookObj, 'sorting')) {
					$hookObj->sorting($this, $cal_time, $event);
				}
			}
		} else {
			$this->objectsInList[$cal_time][] = &$event;
		}
		
		if($this->conf['view.']['list.']['showLongEventsInEachWrapper']){
			if($this->conf['view.']['list.']['enableDayWrapper'] && $eventStart->format('%Y%m%d')!=$eventEnd->format('%Y%m%d')){
				$tempEventStart = new tx_cal_date();
				$tempEventStart->copy($eventStart);
				while($tempEventStart->format('%Y%m%d')!=$eventEnd->format('%Y%m%d')){
					$tempEventStart->addSeconds(60*60*24);
					$this->objectsInList[$tempEventStart->format('%Y%m%d')][] = &$event;
				}
			}
			if($this->conf['view.']['list.']['enableWeekWrapper'] && $eventStart->format('%Y%U')!=$eventEnd->format('%Y%U')){
				$tempEventStart = new tx_cal_date();
				$tempEventStart->copy($eventStart);
				while($tempEventStart->format('%Y%U')!=$eventEnd->format('%Y%U')){
					$tempEventStart->addSeconds(60*60*24*7);
					$this->objectsInList[$tempEventStart->format('%Y%m%d')][] = &$event;
				}
			}
			if($this->conf['view.']['list.']['enableMonthWrapper'] && $eventStart->format('%Y%m')!=$eventEnd->format('%Y%m')){
				$tempEventStart = new tx_cal_date();
				$tempEventStart->copy($eventStart);
				while($tempEventStart->format('%Y%m')!=$eventEnd->format('%Y%m')){
					$tempEventStart->setMonth($tempEventStart->getMonth()+1);
					$this->objectsInList[$tempEventStart->format('%Y%m01')][] = &$event;
				}
			}
			if($this->conf['view.']['list.']['enableYearWrapper'] && $eventStart->format('%Y')!=$eventEnd->format('%Y')){
				$tempEventStart = new tx_cal_date();
				$tempEventStart->copy($eventStart);
				while($tempEventStart->format('%Y')!=$eventEnd->format('%Y')){
					$tempEventStart->setYear($tempEventStart->getYear()+1);
					$this->objectsInList[$tempEventStart->format('%Y0101')][] = &$event;
				}
			}
		}

		$this->count ++;
		if ($this->count == intval($this->conf['view.']['list.']['maxEvents'])) {
			$finished = true;
		}
		return $finished;
	}
	
	function walkThroughMasterArray(&$master_array, $reverse, &$firstEventDate){
		$finished = false;
		
		// parse the master_array for "valid" events of the current listView and reference them in a separate array that is used for rendering
		// use array keys for the loops, so that references can be used and less memory is needed :)
		$master_array_keys = array_keys($master_array);
		
		if($reverse) {
			$master_array_keys = array_reverse($master_array_keys);
		}
		foreach ($master_array_keys as $cal_time) {
			if ($finished) break;
			// create a reference
			$event_times = &$master_array[$cal_time];
			if (is_array($event_times)) {
				$day_array2 = array();

				$event_times_keys = array_keys($event_times);
				if($reverse) $event_times_keys = array_reverse($event_times_keys);
				foreach ($event_times_keys as $a_key) {
					if ($finished) break;
					$a = &$event_times[$a_key];
					
					if (is_array($a)) {
						$a_keys = array_keys($a);
						if($reverse) $a_keys = array_reverse($a_keys);
						foreach ($a_keys as $uid) {
							if ($finished) break;
							if ($this->listStartOffset && $this->listStartOffsetCounter < $this->listStartOffset) {
								$this->listStartOffsetCounter++;
								continue;
							}
							$event = &$a[$uid];

							if (!is_object($event)){
								continue;
							}
							if($this->conf['view.']['list.']['hideStartedEvents'] == 1 && $event->getStart()->before($this->starttime)){
								continue;
							}

							$finished = $this->processObject($event, $cal_time, $firstEventDate);
						}
					}
				}
			}
		}
	}
	
	function drawList(&$master_array, $page='', $starttime, $endtime) {
		$this->starttime = $starttime;
		$this->endtime = $endtime;
		$this->objectsInList = Array();
		
		$this->initTemplate($page);
		
		$this->_init($master_array);
		
		if($this->error){
			return tx_cal_functions::createErrorMessage(
				$this->errorMessage,
				$this->suggestMessage
			);
		}

		$listTemplate = $this->getListSubpart($page);
		
		if($this->error){
			return $this->errorMessage;
		}
		
		// ordering of the events
		switch (strtolower($this->conf['view.'][$this->conf['view'].'.']['order'])) {
			default:
				$this->reverse = false;
				break;
			case 'desc':
				$this->reverse = true;
				break;
		};
		
		$rems = array();
		$sims = array();

		$middle = $this->processObjects($master_array, $sims, $rems);
	
		$listRems = array();
		$listRems['###PRE_LIST_TEMPLATE###'] = '';
		$listRems['###POST_LIST_TEMPLATE###'] = '';
		$sims['###FOUND###'] = '';
		if($this->conf['view.']['list.']['enableSectionMenu']){
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($sectionMenu);
			$sims['###SECTION_MENU###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['sectionMenu'],$this->conf['view.']['list.']['sectionMenu.']);
		}
		$rems['###PAGEBROWSER###'] = '';

		if(!$middle){
			$this->initLocalCObject();
			$middle = $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['event.']['noEventFound'],$this->conf['view.']['list.']['event.']['noEventFound.']);
		} else {
			if($this->conf['view.']['list.']['enableDayWrapper']){
				$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['dayWrapperEnd'],$this->conf['view.']['list.']['dayWrapperEnd.']);
			}
			if($this->conf['view.']['list.']['enableWeekWrapper']){
				$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['weekWrapperEnd'],$this->conf['view.']['list.']['weekWrapperEnd.']);
			}
			if($this->conf['view.']['list.']['enableMonthWrapper']){
				$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapperEnd'],$this->conf['view.']['list.']['monthWrapperEnd.']);
			}
			if($this->conf['view.']['list.']['enableYearWrapper']){
				$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['yearWrapperEnd'],$this->conf['view.']['list.']['yearWrapperEnd.']);
			}
			
			// prepare pre- and post-list subparts
			$listRems['###PRE_LIST_TEMPLATE###'] = $this->cObj->getSubpart($page, '###PRE_LIST_TEMPLATE###'); 
			$listRems['###POST_LIST_TEMPLATE###'] = $this->cObj->getSubpart($page, '###POST_LIST_TEMPLATE###'); 

			$sims['###FOUND###'] = $this->cObj->stdWrap($this->count,$this->conf['view.'][$this->conf['view'].'.']['found_stdWrap.']);
			// render pagebrowser
			if(($this->count > $this->recordsPerPage && $this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['onlyShowIfNeeded']) || !$this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['onlyShowIfNeeded']) {
				$pageBrowser = $this->cObj->getSubpart($page, '###PAGEBROWSER###');
				$rems['###PAGEBROWSER###'] = $this->getPageBrowser($pageBrowser);
			}
		}
		$rems['###LIST###'] = $middle;
		$middle = tx_cal_functions::substituteMarkerArrayNotCached($listTemplate, array (), $listRems, array ());
		$return = tx_cal_functions::substituteMarkerArrayNotCached($listTemplate, $sims, $rems, array ());
		$rems = array();
		return $this->finish($return, $rems);
	}
	
	
	function getPageBrowser($template) {
		$pb = '';

		//render PageBrowser
		if($this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['usePageBrowser']) {
			$this->controller->pointerName = $this->pointerName;
			// Hook: getPageBrowser
			$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_listview','getPageBrowser','view');
			if(count($hookObjectsArr)) {
				foreach ($hookObjectsArr as $hookObj) {
					if (method_exists($hookObj, 'renderPageBrowser')) {
						$hookObj->renderPageBrowser($this, $pb, $this->count, $this->recordsPerPage, $template);
					}
				}
				if($pb != '') {
					return $pb;
				}
			}
			
			// use the piPageBrowser
			if($this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['useType'] == 'piPageBrowser') {
				$browserConfig = &$this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['piPageBrowser.'];
				$this->controller->internal['res_count'] = $this->count;
				$this->controller->internal['results_at_a_time'] = $this->recordsPerPage;
				if($maxPages = intval($this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['pagesCount'])) {
					$this->controller->internal['maxPages'] = $maxPages;
				}
				$this->controller->internal['pagefloat'] = $browserConfig['pagefloat'];
				$this->controller->internal['showFirstLast'] = $browserConfig['showFirstLast'];
				$this->controller->internal['showRange'] = $browserConfig['showRange'];
				$this->controller->internal['dontLinkActivePage'] = $browserConfig['dontLinkActivePage'];
	
				$wrapArrFields = explode(',', 'disabledLinkWrap,inactiveLinkWrap,activeLinkWrap,browseLinksWrap,showResultsWrap,showResultsNumbersWrap,browseBoxWrap');
				$wrapArr = array();
				foreach ($wrapArrFields as $key) {
					if ($browserConfig[$key]) {
						$wrapArr[$key] = $browserConfig[$key];
					}
				}
	
				if ($wrapArr['showResultsNumbersWrap'] && strpos($this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_displays'],'%s')) {
				// if the advanced pagebrowser is enabled and the "pi_list_browseresults_displays" label contains %s it will be replaced with the content of the label "pi_list_browseresults_displays_advanced"
					$this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_displays'] = $this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_displays_advanced'];
				}

				if (!$browserConfig['showPBrowserText']) {
					$this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_page'] = '';
				}

				$this->controller->pi_alwaysPrev = $browserConfig['alwaysPrev'];
	
				// if there is a GETvar in the URL that is not in this list, caching will be disabled for the pagebrowser links
				$this->controller->pi_isOnlyFields = $this->pointerName.',view,model,category,type,getdate,uid';
	
				// pi_lowerThan limits the amount of cached pageversions for the list view. Caching will be disabled if one of the vars in $this->pi_isOnlyFields has a value greater than $this->pi_lowerThan
	
				// 	$this->pi_lowerThan = ceil($this->internal['res_count']/$this->internal['results_at_a_time']);
				$pi_isOnlyFieldsArr = explode(',',$this->controller->pi_isOnlyFields);
				$highestVal = 0;
				foreach ($pi_isOnlyFieldsArr as $k => $v) {
					$val = $this->controller->piVars[$v];
					if(is_array($this->controller->piVars[$v])){
						$val = $this->controller->piVars[$v][0];
					}
					if ($val > $highestVal) {
						$highestVal = $val;
					}
				}
				$this->controller->pi_lowerThan = $highestVal+1;
	
				$pb = $this->controller->pi_list_browseresults($browserConfig['showResultCount'], $browserConfig['tableParams'],$wrapArr, $this->pointerName, $browserConfig['hscText']);
				
			} else {
				// use default page browser of cal
				$browserConfig = $this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['default.'];
				$this->offset=intval($this->controller->piVars[$this->pointerName]);
				
				$pagesTotal=intval($this->recordsPerPage)==0?1:ceil($this->count/$this->recordsPerPage);
				$nextPage=$this->offset+1;
				$previousPage=$this->offset-1;
				$pagesCount=$this->conf['view.'][$this->conf['view'].'.']['pageBrowser.']['pagesCount']-1;
				
				$min=1;
				$max=$pagesTotal;
				if($pagesTotal > $pagesCount+1 && $pagesCount>0) {
					$pstart=$this->offset-ceil(($pagesCount-2)/2);
					if($pstart<1) $pstart=1;
					$pend=$pstart+$pagesCount;
					if($pend>$pagesTotal-1) $pend=$pagesTotal-1;
					$spacer = $this->local_cObj->cObjGetSingle($browserConfig['spacer'],$browserConfig['spacer.']);
				} else {
					$pstart=$min;
					$pend=$pagesTotal;
				}
	
				$pbMarker['###PAGEOF###']=sprintf($this->controller->pi_getLL('l_page_of'),$this->offset+1,$pagesTotal);
				//Extra Single Marker
				$pbMarker['###PAGE###']=$this->offset+1;
				$pbMarker['###PAGETOTAL###']=$pagesTotal;
	
				//next+previous
				$this->initLocalCObject();
				$pbMarker['###NEXT###'] = '';
				if($nextPage+1<=$pagesTotal) {
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array($this->pointerName=>$nextPage),$this->conf['cache']);
					$pbMarker['###NEXT###'] = $this->local_cObj->cObjGetSingle($browserConfig['nextLink'],$browserConfig['nextLink.']);
				}
				
				$pbMarker['###PREVIOUS###'] = '';
				if($previousPage>=0) {
					$previousPage = $previousPage == 0 ? NULL : $previousPage;
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array($this->pointerName=>$previousPage),$this->conf['cache']);
					$pbMarker['###PREVIOUS###'] = $this->local_cObj->cObjGetSingle($browserConfig['prevLink'],$browserConfig['prevLink.']);
				}
				
				for($i=$min;$i<=$max;$i++) {
					if($this->offset+1==$i) {
						$pbMarker['###PAGES###'].=$this->cObj->stdWrap($i,$browserConfig['actPage_stdWrap.']);
					} else {
						if($i==1 || $i==$max || ($i>1 && $i>=$pstart && $i<=$pend && $i<$max)) {
							$this->local_cObj->setCurrentVal($i);
							$pageNum = ($i-1);
							$pageNum = $pageNum == 0? NULL : $pageNum;
							$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array($this->pointerName=>$pageNum),$this->conf['cache']); 
							$pbMarker['###PAGES###'].= $this->local_cObj->cObjGetSingle($browserConfig['pageLink'],$browserConfig['pageLink.']);;
						} elseif (($i==2 && $i<$pstart) || ($i==$pend+1 && $i<$max)){
							unset($this->local_cObj->data['link_parameter']);
							$pbMarker['###PAGES###'].= $spacer;
						}
					}
				}
				$pb=tx_cal_functions::substituteMarkerArrayNotCached($template,$pbMarker, array (),	array ());
			}
		}
		return $pb;
	}

	function hasPeriodChanged($old,$new,$reverse = false,$debug = false) {
		if($debug) {
			t3lib_div::debug(array($old,$new,$reverse));
		}
		if($reverse) {
			return (intval($new) < intval($old));
		} else {
			return (intval($new) > intval($old));
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']);
}
?>