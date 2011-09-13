<?php


/***************************************************************
*  Copyright notice
*
*  (c) 2004 
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
        $starttime=getDayFromTimestamp($starttime);
        $endtime=getDayFromTimestamp($endtime);
        
        if($page==''){
			$this->_init($master_array);
	
			$page = $this->cObj->fileResource($this->conf['view.']['list.']['listTemplate']);
			if ($page == '') {
				return '<h3>calendar: no list template file found:</h3>'.$this->conf['view.']['list.']['listTemplate'];
			}
		}
        $listTemplate = $this->cObj->getSubpart($page, '###LIST_TEMPLATE###');
		$pageBrowser = $this->cObj->getSubpart($page, '###PAGEBROWSER###');
		$loop[0] = $this->cObj->getSubpart($page, '###SHOWBOTTOMEVENTS_ODD###');
		$loop[1] = $this->cObj->getSubpart($page, '###SHOWBOTTOMEVENTS_EVEN###');
		
        $dayWrapper = $this->cObj->getSubpart($page, '###LIST_DAY_WRAPPER###');
        $weekWrapper = $this->cObj->getSubpart($page, '###LIST_WEEK_WRAPPER###');
		$monthWrapper = $this->cObj->getSubpart($page, '###LIST_MONTH_WRAPPER###');
		$i = 0;
		
		/* Subtract strtotimeOffset because we're going from GMT back to local time */		
		$GLOBALS['TSFE']->register['cal_list_starttime'] = $starttime - strtotimeOffset($starttime);
		$GLOBALS['TSFE']->register['cal_list_endtime'] = $endtime - strtotimeOffset($endtime);
		
		$rems = array();
		$sims = array();
		$sims['###HEADING###']=$this->cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['heading'],$this->conf['view.'][$this->conf['view'].'.']['heading.'],$TSkey='__');
		$postTemplate = $this->cObj->getSubpart($page, '###POST_LIST_TEMPLATE###');	
		$postTemplate = $this->cObj->substituteMarkerArrayCached($postTemplate, $sims, $rems, array());
		$rems['###POST_LIST_TEMPLATE###'] = $postTemplate;
		$listTemplate = $this->cObj->substituteMarkerArrayCached($listTemplate, array (), $rems, array ());

		$count = $countFinished = 0;
        $finished='';
		$lastEventTime = $lastEventWeek = $lastEventMonth = 0;
		$categoryGroupArray = array();
		$categoryIds = array();
		if($this->conf['view.']['list.']['enableCategoryWrapper']){
			$this->categoryService = $this->modelObj->getServiceObjByKey('cal_category_model', 'category', 'tx_cal_category');
			$categoryIds = $this->categoryService->getCategoryArray($this->conf['pidList'], true);
		}
		$calendarGroupArray = array();
		$calendarIds = array();
		if($this->conf['view.']['list.']['enableCalendarWrapper']){
			$this->calendarService = $this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
			$calendarIds = $this->calendarService->getIdsFromTable('',$this->conf['pidList'],true,true);
		}

		foreach ($master_array as $cal_time => $event_times) {
			if (is_array($event_times)) {
				foreach ($event_times as $a_key => $a) {
					if (is_array($a)) {
						foreach ($a as $uid => $val) {
							$subTemplate = $loop[$i];
							if(!is_object($val)){
								continue;
							}	
                            
							if((intval($val->getEnddate()) < $starttime || intval($val->getStartdate()) > $endtime)){
								continue;
							}
                            
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
                            
                            $cal_month=intval(substr($cal_time,0,6));
							$cal_week = date('W', strtotime($cal_time));

							//monthwrapper
							if($this->conf['view.']['list.']['enableMonthWrapper'] && $lastEventMonth<$cal_month){
                                $this->conf['view.']['list.']['monthWrapper.']['10.']['value']=strtotime($cal_time);
                                $middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['monthWrapper'],$this->conf['view.']['list.']['monthWrapper.'],$TSkey='__');
								$lastEventMonth=$cal_month;
							}
							//weekwrapper
							if($this->conf['view.']['list.']['enableWeekWrapper'] && $lastEventWeek<$cal_week){
                                $this->conf['view.']['list.']['weekWrapper.']['10.']['value']=strtotime($cal_time);
								$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['weekWrapper'],$this->conf['view.']['list.']['weekWrapper.'],$TSkey='__');
								$lastEventWeek=$cal_week;
							}
							//daywrapper
							if($this->conf['view.']['list.']['enableDayWrapper'] && $lastEventTime<$cal_time){
                                $this->conf['view.']['list.']['dayWrapper.']['10.']['value']=strtotime($cal_time);
								$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['dayWrapper'],$this->conf['view.']['list.']['dayWrapper.'],$TSkey='__');
								$lastEventTime=$cal_time;
							}

                            
                            
							$switch = array();
							$rems = array();
							$wrapped = array();
							$val->getEventMarker($subTemplate,$rems,$switch, $wrapped, 'list');

							
							
							if($this->conf['view.']['list.']['enableCategoryWrapper']){
								$ids = $val->getCategoryUidsAsArray();
								foreach($master_array['legend'] as $calendarCategoryArray){
									
									$rememberUid = array();
					
									foreach($categoryIds as $temp){
										foreach($temp as $categoryRow){
											$isInAllowedUidList = !empty($allowedUids)&&$allowedUids[0]>0?in_array($categoryRow['uid'], $allowedUids):true;				
											if(!in_array($categoryRow['uid'], $rememberUid) && $isInAllowedUidList){// && (!empty($allowedUids))?in_array($categoryRow['uid'], $allowedUids):true){
												if(in_array($categoryRow['uid'],$ids)){
													$categoryGroupArray[$categoryRow['title']] .= $this->cObj->substituteMarkerArrayCached($subTemplate, $rems, $switch, $wrapped);
												}
												$rememberUid[] = $categoryRow['uid'];
											}
										}
									}
								}
							}else if($this->conf['view.']['list.']['enableCalendarWrapper']){
								$id = $val->getCalendarUid();
								foreach($calendarIds as $calendarRow){
									if($calendarRow['uid']==$id){
										$calendarGroupArray[$calendarRow['title']] .= $this->cObj->substituteMarkerArrayCached($subTemplate, $switch, $rems, $wrapped);
									}
								}
							}else{
								$middle .= $this->cObj->substituteMarkerArrayCached($subTemplate, $switch, $rems, $wrapped);
								$i = ($i == 1) ? 0 : 1;
							}

							$count ++;
							if ($count == intval($this->conf['view.']['list.']['maxEvents'])) {
                                $rems['###SHOWBOTTOMEVENTS###'] = $middle;
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
            $pbMarker['###NEXT###']=$nextPage+1<=$pagesTotal?$this->cObj->stdWrap($this->controller->pi_linkTP_keepPIvars('&gt;&gt;',array('offset'=>$nextPage),1),$this->conf['view.']['list.']['pageBrowser.']['next_stdWrap.']):'';
            $GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_prev').'"';
            $pbMarker['###PREVIOUS###']=$previousPage<0?'':$this->cObj->stdWrap($this->controller->pi_linkTP_keepPIvars('&lt;&lt;',array('offset'=>$previousPage),1),$this->conf['view.']['list.']['pageBrowser.']['previous_stdWrap.']);
            
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
            $pb=$this->cObj->substituteMarkerArrayCached($pageBrowser,$pbMarker, array (),  array ());
        }
        
        //Wrapper
        if($this->conf['view.']['list.']['enableCalendarWrapper']){
        	foreach($calendarGroupArray as $calTitel => $calendarEntries){
        		$this->conf['view.']['list.']['calendarWrapper.']['10.']['value']=$calTitel;
				$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['calendarWrapper'],$this->conf['view.']['list.']['calendarWrapper.'],$TSkey='__');
				$middle .= $calendarEntries;
        	}
        }
        if($this->conf['view.']['list.']['enableCategoryWrapper']){
        	foreach($categoryGroupArray as $catTitel => $categoryEntries){
        		$this->conf['view.']['list.']['categoryWrapper.']['10.']['value']=$catTitel;
				$middle .= $this->cObj->cObjGetSingle($this->conf['view.']['list.']['categoryWrapper'],$this->conf['view.']['list.']['categoryWrapper.'],$TSkey='__');
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

		if ($this->rightsObj->isAllowedToCreateEvents()) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
			$sims['###CREATE_EVENT_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['list.']['addIcon'], array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
		} else {
			$sims['###CREATE_EVENT_LINK###'] = '';
		}

		$rems['###SHOWBOTTOMEVENTS###'] = $middle;
		$rems['###PAGEBROWSER###']=$pb;

		$this->showBackLink($listTemplate, $rems, $sims);
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
