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
	
	function tx_cal_listview(){
		$this->tx_cal_base_view();
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
		$GLOBALS['TSFE']->register['cal_list_starttime'] = $starttime->format($this->conf['view.'][$this->conf['view'].'.']['strftimeTitleStartFormat']);
		$GLOBALS['TSFE']->register['cal_list_endtime'] = $endtime->format($this->conf['view.'][$this->conf['view'].'.']['strftimeTitleEndFormat']);
		
		$rems = array();
		$sims = array();
		$sims['###HEADING###']=$this->cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['heading'],$this->conf['view.'][$this->conf['view'].'.']['heading.']);
		$postTemplate = $this->cObj->getSubpart($page, '###POST_LIST_TEMPLATE###'); 
		$postTemplate = $this->cObj->substituteMarkerArrayCached($postTemplate, $sims, $rems, array());
		$rems['###POST_LIST_TEMPLATE###'] = $postTemplate;
		$listTemplate = $this->cObj->substituteMarkerArrayCached($listTemplate, array (), $rems, array ());

		$count = $countFinished = 0;
		$finished='';
		$lastEventTime = $lastEventWeek = $lastEventMonth = new tx_cal_date('000000001','Ymd');
		$categoryGroupArray = array();
		$categoryIds = array();
		if($this->conf['view.']['list.']['enableCategoryWrapper']){
			$this->categoryService = $this->modelObj->getServiceObjByKey('cal_category_model', 'category', 'tx_cal_category');
			$categoryIds = array();
			$this->categoryService->getCategoryArray($this->conf['pidList'], $categoryIds, true);
		}
		$calendarGroupArray = array();
		$calendarIds = array();
		if($this->conf['view.']['list.']['enableCalendarWrapper']){
			$this->calendarService = $this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
			$calendarIds = $this->calendarService->getIdsFromTable('',$this->conf['pidList'],true,true);
		}
		
		$firstEventTime = null;
		$lastEventTime = null;
		
		foreach ($master_array as $cal_time => $event_times) {
			if (is_array($event_times)) {
				$day_array2 = array();
				$calTimeObject = new tx_cal_date($cal_time,'Ymd');
				foreach ($event_times as $a_key => $a) {
					if (is_array($a)) {
						foreach ($a as $uid => $event) {
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
							if($this->conf['view.']['list.']['pageBrowser']) {
								$recordsPerPage=intval($this->conf['view.']['list.']['pageBrowser.']['recordsPerPage']);
								$offset=intval($this->controller->piVars['offset']);
								if($count<$recordsPerPage*$offset || $count>$recordsPerPage*$offset+$recordsPerPage-1) {
									$count++;
									continue;
								}
							}
							
							if($finished!='') {
								continue;
							}

							$cal_month = $calTimeObject->getMonth();
							$cal_week = $calTimeObject->format('%U');

							//monthwrapper
							if($this->conf['view.']['list.']['enableMonthWrapper'] && ($lastEventMonth->getMonth() < $cal_month || $firstTime)){
								$this->conf['view.']['list.']['monthWrapper.']['10.']['value'] = $calTimeObject->format($this->conf['view.']['list.']['monthWrapperFormat']);
								$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapper'],$this->conf['view.']['list.']['monthWrapper.']);
								$lastEventMonth->copy($calTimeObject);
							}
							//weekwrapper
							if($this->conf['view.']['list.']['enableWeekWrapper'] && ($lastEventWeek->format('%U') < $cal_week || $firstTime)){
								$this->conf['view.']['list.']['weekWrapper.']['10.']['value'] = $calTimeObject->format($this->conf['view.']['list.']['weekWrapperFormat']);
								$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['weekWrapper'],$this->conf['view.']['list.']['weekWrapper.']);
								$lastEventWeek->copy($calTimeObject);
							}
							//daywrapper
							if($this->conf['view.']['list.']['enableDayWrapper'] && ($lastEventTime->before($calTimeObject) || $firstTime)){
								$this->conf['view.']['list.']['dayWrapper.']['10.']['value'] = $calTimeObject->format($this->conf['view.']['list.']['dayWrapperFormat']);
								$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['dayWrapper'],$this->conf['view.']['list.']['dayWrapper.']);
								$lastEventTime->copy($calTimeObject);
							}
							
							$eventText = $event->renderEventForList($i);
							
							if($this->conf['view.']['list.']['enableCategoryWrapper']){
								$ids = $event->getCategoryUidsAsArray();
								foreach($master_array['legend'] as $calendarCategoryArray){
									
									$rememberUid = array();
					
									foreach($categoryIds as $temp){
										foreach($temp as $categoryRow){
											$isInAllowedUidList = !empty($allowedUids)&&$allowedUids[0]>0?in_array($categoryRow['uid'], $allowedUids):true;				
											if(!in_array($categoryRow['uid'], $rememberUid) && $isInAllowedUidList){// && (!empty($allowedUids))?in_array($categoryRow['uid'], $allowedUids):true){
												if(in_array($categoryRow['uid'],$ids)){
													$categoryGroupArray[$categoryRow['title']] .= $eventText;
												}
												$rememberUid[] = $categoryRow['uid'];
											}
										}
									}
								}
							}else if($this->conf['view.']['list.']['enableCalendarWrapper']){
								$id = $event->getCalendarUid();
								foreach($calendarIds as $calendarRow){
									if($calendarRow['uid']==$id){
										$calendarGroupArray[$calendarRow['title']] .= $eventText;
									}
								}
							}else{
								$middle .= $eventText;
								$i = ($i == 1) ? 0 : 1;
							}

							$count ++;
							if ($count == intval($this->conf['view.']['list.']['maxEvents'])) {
								$rems['###LIST###'] = $middle;
								$GLOBALS['TSFE']->ATagParams = $this->tempATagParam;
								$finished=$this->cObj->substituteMarkerArrayCached($listTemplate, array (), $rems, array ());
								//store count for this case
								$countFinished = $count;
							}
						}
					}
				}
			}
		}
		if($firstEventDate){
			$GLOBALS['TSFE']->register['cal_list_firstevent'] = $firstEventDate->format($this->conf['view.'][$this->conf['view'].'.']['strftimeTitleStartFormat']);
		}
		if($lastEventDate){
			$GLOBALS['TSFE']->register['cal_list_lastevent'] = $lastEventDate->format($this->conf['view.'][$this->conf['view'].'.']['strftimeTitleEndFormat']);
		}
		
		//render PageBrowser
		if($this->conf['view.']['list.']['pageBrowser']) {	  
			if($countFinished>0) $count=$countFinished;
			
			#limit to maxEvents
			if( intval($this->conf['view.']['list.']['maxEvents'])>0 && $count>intval($this->conf['view.']['list.']['maxEvents']) ) $count=intval($this->conf['view.']['list.']['maxEvents']);
			
 
			$tmpA=$GLOBALS['TSFE']->ATagParams;
			$pagesTotal=intval($recordsPerPage)==0?1:ceil($count/$recordsPerPage);
			$nextPage=$offset+1;
			$previousPage=$offset-1;
			$pagesCount=$this->conf['view.']['list.']['pageBrowser.']['pagesCount']-1;
			
			$min=1;
			$max=$pagesTotal;
			if($pagesTotal >$pagesCount && $pagesCount>0) {
				$pstart=$offset-ceil(($pagesCount-2)/2);
				if($pstart<1) $pstart=1;
				$pend=$pstart+$pagesCount;
				if($pend>$pagesTotal-1) $pend=$pagesTotal-1;
			} else {
				$pstart=$min;
				$pend=$pagesTotal;
			}

			$pbMarker['###PAGEOF###']=sprintf($this->controller->pi_getLL('l_page_of'),$offset+1,$pagesTotal);
			//Extra Single Marker
			$pbMarker['###PAGE###']=$offset+1;
			$pbMarker['###PAGETOTAL###']=$pagesTotal;
			//next+previous
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_next').'"';
			$pbMarker['###NEXT###']=$nextPage+1<=$pagesTotal?$this->cObj->stdWrap($this->controller->pi_linkTP_keepPIvars($this->conf['view.']['list.']['pageBrowser.']['next'],array('offset'=>$nextPage),1),$this->conf['view.']['list.']['pageBrowser.']['next_stdWrap.']):'';
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_prev').'"';
			$pbMarker['###PREVIOUS###']=$previousPage<0?'':$this->cObj->stdWrap($this->controller->pi_linkTP_keepPIvars($this->conf['view.']['list.']['pageBrowser.']['prev'],array('offset'=>$previousPage),1),$this->conf['view.']['list.']['pageBrowser.']['previous_stdWrap.']);
			
			for($i=$min;$i<=$max;$i++) {
				if($offset+1==$i) {
					$pbMarker['###PAGES###'].=$this->cObj->stdWrap($i,$this->conf['view.']['list.']['pageBrowser.']['actPage_stdWrap.']);
				} else {
					if($i==1 || $i==$max || ($i>1 && $i>=$pstart && $i<=$pend && $i<$max)) {
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_page').' '.$i.'"'; 
					$link=$this->controller->pi_linkTP_keepPIvars($i,array('offset'=>($i-1)),1); 
					$pbMarker['###PAGES###'].=$this->cObj->stdWrap($link,$this->conf['view.']['list.']['pageBrowser.']['pages_stdWrap.']);
					} elseif (($i==2 && $i<$pstart) || ($i==$pend+1 && $i<$max)){
						$pbMarker['###PAGES###'].= $this->cObj->stdWrap('...',$this->conf['view.']['list.']['pageBrowser.']['actPage_stdWrap.']);
					}
				}
			}
			$GLOBALS['TSFE']->ATagParams = $tmpA;
			$pb=$this->cObj->substituteMarkerArrayCached($pageBrowser,$pbMarker, array (),	array ());
		}
		
		//Wrapper
		if($this->conf['view.']['list.']['enableCalendarWrapper']){
			foreach($calendarGroupArray as $calTitel => $calendarEntries){
				$this->conf['view.']['list.']['calendarWrapper.']['10.']['value']=$calTitel;
				$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['calendarWrapper'],$this->conf['view.']['list.']['calendarWrapper.']);
				$middle .= $calendarEntries;
			}
		}
		if($this->conf['view.']['list.']['enableCategoryWrapper']){
			foreach($categoryGroupArray as $catTitel => $categoryEntries){
				$this->conf['view.']['list.']['categoryWrapper.']['10.']['value']=$catTitel;
				$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['categoryWrapper'],$this->conf['view.']['list.']['categoryWrapper.']);
				$middle .= $categoryEntries;
			}
		}

		
		$sims = array();
		if(!$middle){
			$middle = $this->cObj->stdWrap($this->controller->pi_getLL('l_no_events'),$this->conf['view.']['list.']['noEventFound_stdWrap.']);
			$sims['###FOUND###'] = '';
			$pb = ''; // No need for the page browser if we have no results.
		} else {
			$sims['###FOUND###'] = $this->cObj->stdWrap($count,$this->conf['view.']['list.']['found_stdWrap.']);
		}

		$rems['###LIST###'] = $middle;
		$rems['###PAGEBROWSER###']=$pb;

		$GLOBALS['TSFE']->ATagParams = $this->tempATagParam;
		$return = $this->cObj->substituteMarkerArrayCached($listTemplate, $sims, $rems, array ());
		$rems = array();
		return $this->finish($return, $rems);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']);
}
?>