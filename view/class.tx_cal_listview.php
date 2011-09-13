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
	var $pointerName = 'offset';
	var $eventCounter = array();
	
	function tx_cal_listview(){
		$this->tx_cal_base_view();
		$this->pointerName = $this->conf['view.']['list.']['pageBrowser.']['pointer'] ? $this->conf['view.']['list.']['pageBrowser.']['pointer'] : $this->pointerName;
	}
	
	function drawList(&$master_array, $page='',$starttime,$endtime) {
		# only use day stamps
	 
		if($page==''){
			$this->_init($master_array);
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($confArr['useTeaser']) {
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listWithTeaserTemplate']);
			}else{
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listTemplate']);
			}
			if ($page == '') {
				return '<h3>calendar: no list template file found:</h3>'.$this->conf['view.']['list.']['listTemplate'];
			}
		}

		$listTemplate = $this->cObj->getSubpart($page, '###LIST_TEMPLATE###');
		if($listTemplate==''){
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			if($confArr['useTeaser']) {
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listWithTeaserTemplate']);
			}else{
				$page = $this->cObj->fileResource($this->conf['view.']['list.']['listTemplate']);
			}
			if ($page == '') {
				return '<h3>calendar: no list template file found:</h3>'.$this->conf['view.']['list.']['listTemplate'];
			}
			$listTemplate = $this->cObj->getSubpart($page, '###LIST_TEMPLATE###');
		}
		// dead code?
		#$dayWrapper = $this->cObj->getSubpart($page, '###LIST_DAY_WRAPPER###');
		#$weekWrapper = $this->cObj->getSubpart($page, '###LIST_WEEK_WRAPPER###');
		#$monthWrapper = $this->cObj->getSubpart($page, '###LIST_MONTH_WRAPPER###');
		#$yearWrapper = $this->cObj->getSubpart($page, '###LIST_YEAR_WRAPPER###');
		
		// ordering of the events
		switch (strtolower($this->conf['view.']['list.']['order'])) {
			default:
				$reverse = false;
				break;
			case 'desc':
				$reverse = true;
				break;
		};
		
		/* Subtract strtotimeOffset because we're going from GMT back to local time */
		if ($reverse) {
			$GLOBALS['TSFE']->register['cal_list_starttime'] = $endtime->getTime();
			$GLOBALS['TSFE']->register['cal_list_endtime'] = $starttime->getTime();
		} else {
			$GLOBALS['TSFE']->register['cal_list_starttime'] = $starttime->getTime();
			$GLOBALS['TSFE']->register['cal_list_endtime'] = $endtime->getTime();
		}
		
		$rems = array();
		$sims = array();

		// only proceed if the master_array is not empty
		if(count($master_array)) {

			$count = 0;
			$this->eventCounter = array();
			$listStartOffsetCounter = 0;
			$listStartOffset = intval($this->conf['view.']['list.']['listStartOffset']);
	
			if($this->conf['view.']['list.']['pageBrowser.']['usePageBrowser']) {
				$offset=intval($this->controller->piVars[$this->pointerName]);
				$recordsPerPage=intval($this->conf['view.']['list.']['pageBrowser.']['recordsPerPage']);
			}
			$finished=false;
			
	
			// parse the master_array for "valid" events of the current listView and reference them in a separate array that is used for rendering
			// use array keys for the loops, so that references can be used and less memory is needed :)
			$master_array_keys = array_keys($master_array);
			if($reverse) $master_array_keys = array_reverse($master_array_keys);
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
								if ($listStartOffset && $listStartOffsetCounter < $listStartOffset) {
									$listStartOffsetCounter++;
									continue;
								}
								$event = &$a[$uid];

								if (!is_object($event)){
									continue;
								}
								$eventStart = $event->getStart();
								$eventEnd = $event->getEnd();

								if ($eventEnd->before($starttime) || $eventStart->after($endtime)){
									continue;
								}

								/* If we haven't saved an event date already, save this one */
								if (!$firstEventDate) {
									$firstEventDate = new tx_cal_date();
									if ($reverse) {
										$firstEventDate->copy($eventEnd);
									} else {
										$firstEventDate->copy($eventStart);
									}
								}
								/* Always save the current event date as the last one and let it fall through */
								if ($reverse) {
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
								if ($this->conf['view.']['list.']['pageBrowser.']['usePageBrowser']) {
									if ($count<$recordsPerPage*$offset) {
										$this->eventCounter['byDate'][$year][$month][$day]['previousPages']++;
										$this->eventCounter['byWeek'][$week]['previousPages']++;
										$this->eventCounter['byYear'][$year]['previousPages']++;
										$this->eventCounter['byMonth'][$month]['previousPages']++;
										$this->eventCounter['byYearMonth'][$year][$month]['previousPages']++;
										$this->eventCounter['byDay'][$day]['previousPages']++;
										$this->eventCounter['byYearDay'][$year][$day]['previousPages']++;
									} else if ($count>$recordsPerPage*$offset+$recordsPerPage-1) {
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
									
									if ($count<$recordsPerPage*$offset || $count>$recordsPerPage*$offset+$recordsPerPage-1) {
										$count++;
										if ($count == intval($this->conf['view.']['list.']['maxEvents'])) {
											$finished = true;
										}
										continue;
									}
								}
	
								// reference the event in the rendering array
								$eventsInList[$cal_time][] = &$event;
	
								$count ++;
								if ($count == intval($this->conf['view.']['list.']['maxEvents'])) {
									$finished = true;
								}
							}
						}
					}
				}
			}
			
			if($firstEventDate){
				$GLOBALS['TSFE']->register['cal_list_firstevent'] = $firstEventDate->getTime();
			}
			if($lastEventDate){
				$GLOBALS['TSFE']->register['cal_list_lastevent'] = $lastEventDate->getTime();
			}
			if($count) {
				$GLOBALS['TSFE']->register['cal_list_events_total'] = $count;
				// reference the array with all event counts in the TYPO3 register for usage from within hooks or whatever
				$GLOBALS['TSFE']->register['cal_list_eventcounter'] = &$this->eventCounter;
			}
			if($days = count($eventsInList)) {
				$GLOBALS['TSFE']->register['cal_list_days_total'] = $days;
			}

			// start rendering the events
			if(count($eventsInList) && $count > 0) {
				$times = array_keys($eventsInList);
				// preset vars
				$firstTime = true;
				$listItemCount = 0;
				$alternationCount = 0;
				$pageItemCount = $recordsPerPage*$offset;
				
				$lastEventTime = $lastEventWeek = $lastEventMonth = $lastEventYear = new tx_cal_date('000000001000000');
				
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
				$alternatingLayoutConfig = $this->conf['view.']['list.']['alternatingLayoutMarkers.'];
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
				
				foreach($times as $cal_time) {
					$e_keys = array_keys($eventsInList[$cal_time]);
					$calTimeObject = new tx_cal_date($cal_time.'000000');
					
					$cal_day = $calTimeObject->getDay();
					$cal_month = $calTimeObject->getMonth();
					$cal_year = $calTimeObject->getYear();
					$cal_week = $calTimeObject->format('%U');
					
					if($firstTime) {
						$yearItemCounter = (int)$this->eventCounter['byYear'][$cal_year]['previousPages'];
						$monthItemCounter = (int)$this->eventCounter['byYearMonth'][$cal_year][$cal_month]['previousPages'];
						$weekItemCounter = (int)$this->eventCounter['byWeek'][$cal_week]['previousPages'];
						$dayItemCounter = (int)$this->eventCounter['byDate'][$cal_year][$cal_month][$cal_day]['previousPages'];
						#t3lib_div::debug(array($yearItemCounter,$monthItemCounter,$weekItemCounter,$dayItemCounter));
					}
					
					foreach($e_keys as $e_key) {
						$event = &$eventsInList[$cal_time][$e_key];
						
						if($firstTime) {
								$eventStart = $event->getStart();
								$lastEventTime->copy($eventStart);
								$lastEventMonth->copy($eventStart);
								$lastEventWeek->copy($eventStart);
								$lastEventYear->copy($eventStart);
						}
	
	
						//yearwrapper
						if($this->conf['view.']['list.']['enableYearWrapper'] && (intval($lastEventYear->format('%Y')) < intval($calTimeObject->format('%Y')) || $firstTime)){
							$this->initLocalCObject();
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['yearWrapperFormat']));
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['yearWrapper'],$this->conf['view.']['list.']['yearWrapper.']);
							$lastEventYear->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterYearWrapper']) $alternationCount = 0;
							if(!$firstTime) $yearItemCounter = 0;
						}
						//monthwrapper
						if($this->conf['view.']['list.']['enableMonthWrapper'] && (intval($lastEventMonth->format('%Y%m')) < intval($calTimeObject->format('%Y%m')) || $firstTime)){
							$this->initLocalCObject();
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['monthWrapperFormat']));
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapper'],$this->conf['view.']['list.']['monthWrapper.']);
							$lastEventMonth->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterMonthWrapper']) $alternationCount = 0;
							if(!$firstTime) $monthItemCounter = 0;
						}
						//weekwrapper
						if($this->conf['view.']['list.']['enableWeekWrapper'] && ($lastEventWeek->format('%U') < $cal_week || $firstTime || ($cal_week==1 && $cal_year > $lastEventWeek->getYear() ))){
							$this->initLocalCObject();
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['weekWrapperFormat']));
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['weekWrapper'],$this->conf['view.']['list.']['weekWrapper.']);
							$lastEventWeek->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterWeekWrapper']) $alternationCount = 0;
							if(!$firstTime) $weekItemCounter = 0;
						}
						//daywrapper
						if($this->conf['view.']['list.']['enableDayWrapper'] && ($lastEventTime->before($calTimeObject) || $firstTime)){
							$this->initLocalCObject();
							$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['dayWrapperFormat']));
							$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['dayWrapper'],$this->conf['view.']['list.']['dayWrapper.']);
							$lastEventTime->copy($calTimeObject);
							if($this->conf['view.']['list.']['restartAlternationAfterDayWrapper']) $alternationCount = 0;
							if(!$firstTime) $dayItemCounter = 0;
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
						$eventText = $event->renderEventForList($layoutSuffix);
						
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
						}else{
							$middle .= $eventText;
						}
						
						$alternationCount++;
						$firstTime = false;
					}
				}
				
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


		$listRems = array();
		$listRems['###PRE_LIST_TEMPLATE###'] = '';
		$listRems['###POST_LIST_TEMPLATE###'] = '';
		$sims['###FOUND###'] = '';
		$rems['###PAGEBROWSER###'] = '';
		
		if(!$middle){
			$this->initLocalCObject();
			$middle = $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['event.']['noEventFound'],$this->conf['view.']['list.']['event.']['noEventFound.']);
		} else {
			// prepare pre- and post-list subparts
			$listRems['###PRE_LIST_TEMPLATE###'] = $this->cObj->getSubpart($page, '###PRE_LIST_TEMPLATE###'); 
			$listRems['###POST_LIST_TEMPLATE###'] = $this->cObj->getSubpart($page, '###POST_LIST_TEMPLATE###'); 

			$sims['###FOUND###'] = $this->cObj->stdWrap($count,$this->conf['view.']['list.']['found_stdWrap.']);
			// render pagebrowser
			if(($count > $recordsPerPage && $this->conf['view.']['list.']['pageBrowser.']['onlyShowIfNeeded']) || !$this->conf['view.']['list.']['pageBrowser.']['onlyShowIfNeeded']) {
				$pageBrowser = $this->cObj->getSubpart($page, '###PAGEBROWSER###');
				$rems['###PAGEBROWSER###'] = $this->getPageBrowser($count,$recordsPerPage,$pageBrowser);
			}
		}
		$rems['###LIST###'] = $middle;
		$listTemplate = substituteMarkerArrayNotCached($listTemplate, array (), $listRems, array ());
		$return = substituteMarkerArrayNotCached($listTemplate, $sims, $rems, array ());
		$rems = array();
		return $this->finish($return, $rems);
	}
	
	
	function getPageBrowser($recordsTotal,$recordsPerPage,$template) {
		$pb = '';
		
		//render PageBrowser
		if($this->conf['view.']['list.']['pageBrowser.']['usePageBrowser']) {
			$this->controller->pointerName = $this->pointerName;
				
			// use the piPageBrowser
			if($this->conf['view.']['list.']['pageBrowser.']['useType'] == 'piPageBrowser') {
				$browserConfig = &$this->conf['view.']['list.']['pageBrowser.']['piPageBrowser.'];
				$this->controller->internal['res_count'] = $recordsTotal;
				$this->controller->internal['results_at_a_time'] = $recordsPerPage;
				if($maxPages = intval($this->conf['view.']['list.']['pageBrowser.']['pagesCount'])) {
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
					if ($this->controller->piVars[$v] > $highestVal) {
						$highestVal = $this->controller->piVars[$v];
					}
				}
				$this->controller->pi_lowerThan = $highestVal+1;
	
				$pb = $this->controller->pi_list_browseresults($browserConfig['showResultCount'], $browserConfig['tableParams'],$wrapArr, $this->pointerName, $browserConfig['hscText']);
				
			} else {
				// use default page browser of cal
				$browserConfig = $this->conf['view.']['list.']['pageBrowser.']['default.'];
				$offset=intval($this->controller->piVars[$this->pointerName]);
				
				$pagesTotal=intval($recordsPerPage)==0?1:ceil($recordsTotal/$recordsPerPage);
				$nextPage=$offset+1;
				$previousPage=$offset-1;
				$pagesCount=$this->conf['view.']['list.']['pageBrowser.']['pagesCount']-1;
				
				$min=1;
				$max=$pagesTotal;
				if($pagesTotal > $pagesCount+1 && $pagesCount>0) {
					$pstart=$offset-ceil(($pagesCount-2)/2);
					if($pstart<1) $pstart=1;
					$pend=$pstart+$pagesCount;
					if($pend>$pagesTotal-1) $pend=$pagesTotal-1;
					$spacer = $this->local_cObj->cObjGetSingle($browserConfig['spacer'],$browserConfig['spacer.']);
				} else {
					$pstart=$min;
					$pend=$pagesTotal;
				}
	
				$pbMarker['###PAGEOF###']=sprintf($this->controller->pi_getLL('l_page_of'),$offset+1,$pagesTotal);
				//Extra Single Marker
				$pbMarker['###PAGE###']=$offset+1;
				$pbMarker['###PAGETOTAL###']=$pagesTotal;
	
				//next+previous
				$this->initLocalCObject();
				$pbMarker['###NEXT###'] = '';
				if($nextPage+1<=$pagesTotal) {
					$this->local_cObj->data['link'] = $this->controller->pi_linkTP_keepPIvars_url(array($this->pointerName=>$nextPage),1);
					$pbMarker['###NEXT###'] = $this->local_cObj->cObjGetSingle($browserConfig['nextLink'],$browserConfig['nextLink.']);
				}
				
				$pbMarker['###PREVIOUS###'] = '';
				if($previousPage>=0) {
					$this->local_cObj->data['link'] = $this->controller->pi_linkTP_keepPIvars_url(array($this->pointerName=>$previousPage),1);
					$pbMarker['###PREVIOUS###'] = $this->local_cObj->cObjGetSingle($browserConfig['prevLink'],$browserConfig['prevLink.']);
				}
				
				for($i=$min;$i<=$max;$i++) {
					if($offset+1==$i) {
						$pbMarker['###PAGES###'].=$this->cObj->stdWrap($i,$browserConfig['actPage_stdWrap.']);
					} else {
						if($i==1 || $i==$max || ($i>1 && $i>=$pstart && $i<=$pend && $i<$max)) {
							$this->local_cObj->setCurrentVal($i);
							$this->local_cObj->data['link'] = $this->controller->pi_linkTP_keepPIvars_url(array($this->pointerName=>($i-1)),1); 
							$pbMarker['###PAGES###'].= $this->local_cObj->cObjGetSingle($browserConfig['pageLink'],$browserConfig['pageLink.']);;
						} elseif (($i==2 && $i<$pstart) || ($i==$pend+1 && $i<$max)){
							unset($this->local_cObj->data['link']);
							$pbMarker['###PAGES###'].= $spacer;
						}
					}
				}
				$pb=substituteMarkerArrayNotCached($template,$pbMarker, array (),	array ());
			}
		}
		return $pb;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']);
}
?>