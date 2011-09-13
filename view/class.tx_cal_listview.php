<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Steffen Kamper
 * (c) 2005-2007 Foundation for Evangelism
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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_listview extends tx_cal_base_view {
	var $pointerName = 'offset';
	
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
		$pageBrowser = $this->cObj->getSubpart($page, '###PAGEBROWSER###');
		$loop[0] = $this->cObj->getSubpart($page, '###LIST_ODD###');
		$loop[1] = $this->cObj->getSubpart($page, '###LIST_EVEN###');
		
		$dayWrapper = $this->cObj->getSubpart($page, '###LIST_DAY_WRAPPER###');
		$weekWrapper = $this->cObj->getSubpart($page, '###LIST_WEEK_WRAPPER###');
		$monthWrapper = $this->cObj->getSubpart($page, '###LIST_MONTH_WRAPPER###');
		$i = 0;
		
		/* Subtract strtotimeOffset because we're going from GMT back to local time */		
		$GLOBALS['TSFE']->register['cal_list_starttime'] = $starttime->getTime();
		$GLOBALS['TSFE']->register['cal_list_endtime'] = $endtime->getTime();
		
		$rems = array();
		$sims = array();
		$sims['###HEADING###']=$this->cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['heading'],$this->conf['view.'][$this->conf['view'].'.']['heading.']);
		$postTemplate = $this->cObj->getSubpart($page, '###POST_LIST_TEMPLATE###'); 
		$postTemplate = $this->cObj->substituteMarkerArrayCached($postTemplate, $sims, $rems, array());
		$rems['###POST_LIST_TEMPLATE###'] = $postTemplate;
		$listTemplate = $this->cObj->substituteMarkerArrayCached($listTemplate, array (), $rems, array ());

		$count = 0;
		$finished=false;
		$lastEventTime = new tx_cal_date('000000001000000');
		$lastEventWeek = new tx_cal_date('000000001000000');
		$lastEventMonth = new tx_cal_date('000000001000000');
		$categoryGroupArray = array();
		$categoryArray = array();
		if($this->conf['view.']['list.']['enableCategoryWrapper']){
			$allCategoryArray = $this->modelObj->findAllCategories('','',$this->conf['pidList']);
			$categoryArray = (Array)$allCategoryArray['tx_cal_category'][0][0];
		}
		$calendarGroupArray = array();
		$calendarArray = array();
		if($this->conf['view.']['list.']['enableCalendarWrapper']){
			$allCalendarArray = $this->modelObj->findAllCalendar('',$this->conf['pidList']);
			$calendarArray = (Array)$allCalendarArray['tx_cal_calendar'];
		}
		
		$firstEventTime = null;
		$lastEventTime = null;

		// use array keys for the loops, so that references can be used and less memory is needed :)
		$master_array_keys = array_keys($master_array);
		foreach ($master_array_keys as $cal_time) {
			if($finished) break;
			// create a reference
			$event_times = &$master_array[$cal_time];
			if (is_array($event_times)) {
				$day_array2 = array();
				$calTimeObject = new tx_cal_date($cal_time.'000000');
				$event_times_keys = array_keys($event_times);
				foreach ($event_times_keys as $a_key) {
					if($finished) break;
					$a = &$event_times[$a_key];
					
					if (is_array($a)) {
						$a_keys = array_keys($a);
						foreach ($a_keys as $uid) {
							if($finished) break;
							$event = &$a[$uid];
							
							$subTemplate = $loop[$i];
							if(!is_object($event)){
								continue;
							}	
							$eventStart = $event->getStart();
							$eventEnd = $event->getEnd();

							if($eventEnd->before($starttime) || $eventStart->after($endtime)){
								continue;
							}
							
							$firstTime = false;
							/* If we haven't saved an event date already, save this one */
							if(!$firstEventDate) {
								$firstEventDate = new tx_cal_date();
								$lastEventTime = new tx_cal_date();
								$firstEventDate->copy($eventStart);
								$lastEventTime->copy($eventStart);
								$firstTime = true;
							}
							/* Always save the current event date as the last one and let it fall through */
							$lastEventDate = $eventEnd;

							//Pagebrowser
							if($this->conf['view.']['list.']['pageBrowser.']['usePageBrowser']) {
								$recordsPerPage=intval($this->conf['view.']['list.']['pageBrowser.']['recordsPerPage']);
								$offset=intval($this->controller->piVars[$this->pointerName]);
								if($count<$recordsPerPage*$offset || $count>$recordsPerPage*$offset+$recordsPerPage-1) {
									$count++;
									if ($count == intval($this->conf['view.']['list.']['maxEvents'])) {
										$finished = true;
									}
									continue;
								}
							}

							$cal_month = $calTimeObject->getMonth();
							$cal_year = $calTimeObject->getYear();
							$cal_week = $calTimeObject->format('%U');

							//monthwrapper
							if($this->conf['view.']['list.']['enableMonthWrapper'] && (intval($lastEventMonth->format('%Y%m')) < intval($calTimeObject->format('%Y%m')) || $firstTime)){
								$this->initLocalCObject();
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['monthWrapperFormat']));
								$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapper'],$this->conf['view.']['list.']['monthWrapper.']);
								$lastEventMonth->copy($calTimeObject);
							}
							//weekwrapper
							if($this->conf['view.']['list.']['enableWeekWrapper'] && ($lastEventWeek->format('%U') < $cal_week || $firstTime || ($cal_week==1 && $cal_year > $lastEventWeek->getYear() ))){
								$this->initLocalCObject();
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['weekWrapperFormat']));
								$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['weekWrapper'],$this->conf['view.']['list.']['weekWrapper.']);
								$lastEventWeek->copy($calTimeObject);
							}
							//daywrapper
							if($this->conf['view.']['list.']['enableDayWrapper'] && ($lastEventTime->before($calTimeObject) || $firstTime)){
								$this->initLocalCObject();
								$this->local_cObj->setCurrentVal($calTimeObject->format($this->conf['view.']['list.']['dayWrapperFormat']));
								$middle .= $this->local_cObj->cObjGetSingle($this->conf['view.']['list.']['dayWrapper'],$this->conf['view.']['list.']['dayWrapper.']);
								$lastEventTime->copy($calTimeObject);
							}
							
							$eventText = $event->renderEventForList($i,'list');
							
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
								$i = ($i == 1) ? 0 : 1;
							}

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
		
		//render PageBrowser
		if($this->conf['view.']['list.']['pageBrowser.']['usePageBrowser']) {
			$this->controller->pointerName = $this->pointerName;
				
			// use the piPageBrowser
			if($this->conf['view.']['list.']['pageBrowser.']['useType'] == 'piPageBrowser') {
				$browserConfig = &$this->conf['view.']['list.']['pageBrowser.']['piPageBrowser.'];
				$this->controller->internal['res_count'] = $count;
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
				foreach($wrapArrFields as $key) {
					if ($browserConfig[$key]) {
						$wrapArr[$key] = $browserConfig[$key];
					}
				}
	
				if ($wrapArr['showResultsNumbersWrap'] && strpos($this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_displays'],'%s')) {
				// if the advanced pagebrowser is enabled and the "pi_list_browseresults_displays" label contains %s it will be replaced with the content of the label "pi_list_browseresults_displays_advanced"
					$this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_displays'] = $this->controller->LOCAL_LANG[$this->controller->LLkey]['pi_list_browseresults_displays_advanced'];
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
				
				$pagesTotal=intval($recordsPerPage)==0?1:ceil($count/$recordsPerPage);
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
				$pb=$this->cObj->substituteMarkerArrayCached($pageBrowser,$pbMarker, array (),	array ());
			}
		}


		//Wrapper
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
					$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['noCategoryWrapper'],$this->conf['view.']['list.']['noCategoryWrapper.']);
				} else {
					$this->conf['view.']['list.']['categoryWrapper.']['10.']['value']=$categoryArray[$categoryId]->getTitle();
					$text = $this->cObj->cObjGetSingle($this->conf['view.']['list.']['categoryWrapper.']['10'],$this->conf['view.']['list.']['categoryWrapper.']['10.']);
					$middle .= str_replace('###CATEGORY_STYLE###',$categoryArray[$categoryId]->getHeaderStyle(),$text);
				}
				$middle .= $categoryGroupArray[$categoryId];
			}
		}

		
		$sims = array();
		if(!$middle){
			$middle = $this->cObj->stdWrap($this->controller->pi_getLL('l_no_events'),$this->conf['view.']['list.']['event.']['noEventFound.']);
			$sims['###FOUND###'] = '';
			$pb = ''; // No need for the page browser if we have no results.
		} else {
			$sims['###FOUND###'] = $this->cObj->stdWrap($count,$this->conf['view.']['list.']['found_stdWrap.']);
		}

		$rems['###LIST###'] = $middle;
		$rems['###PAGEBROWSER###']=$pb;
		


		$return = $this->cObj->substituteMarkerArrayCached($listTemplate, $sims, $rems, array ());
		$rems = array();
		return $this->finish($return, $rems);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']);
}
?>
