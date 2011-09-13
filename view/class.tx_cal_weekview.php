<?php
/***************************************************************
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
 ***************************************************************/

	 
require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');
 
/**
* A concrete view for the calendar.
* It is based on the phpicalendar project
*
* @author Mario Matzulla <mario(at)matzullas.de>
*/
class tx_cal_weekview extends tx_cal_base_view {
	
	function tx_cal_weekview(){
		$this->tx_cal_base_view();
	}
	
	public function newDrawWeek(&$master_array, $getdate){
		
		if (!isset ($getdate) || $getdate == '') {
			$getdate = new tx_cal_date();
		} else {
			$getdate = new tx_cal_date($getdate);
		}
		
		require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_weekview.php');
		
		
		$weekModel = new tx_cal_new_weekview($getdate->getWeekOfYear(), $getdate->year);
		$today = new tx_cal_date();
		$weekModel->setCurrent($today);
		$weekModel->setSelected($getdate);
		
		$weekdayLength = intval($this->conf['view.']['month.']['weekdayLength'.ucwords($type).'Month']);
		if($weekdayLength > 0){
			$weekModel->weekDayLength = $weekdayLength;
		}
		
		$masterArrayKeys = array_keys($master_array);
		foreach ($masterArrayKeys as $dateKey) {
			$dateArray = &$master_array[$dateKey];
			$dateArrayKeys = array_keys($dateArray);
			foreach ($dateArrayKeys as $timeKey) {
				$arrayOfEvents = &$dateArray[$timeKey];
				$eventKeys = array_keys($arrayOfEvents);
				foreach ($eventKeys as $eventKey) {
					$weekModel->addEvent($arrayOfEvents[$eventKey]);
				}
			}
		}
		
		$subpart = $this->cObj->fileResource($this->conf['view.']['week.']['newWeekTemplate']);
		$page = $this->cObj->fileResource($this->conf['view.']['week.']['weekTemplate']);
		$page = str_replace('###WEEK###', $weekModel->render($subpart), $page);
		$rems = Array();
									
		return $this->finish($page, $rems);
	}

	/**
	*  Draws the week view.
	*  @param  $master_array array  The events to be drawn.
	*  @param  $getdate  integer  The date of the event
	* @return  string  The HTML output.
	*/
	public function drawWeek(&$master_array, $getdate) {

		if($this->conf['useNewTemplatesAndRendering']){
			return $this->newDrawWeek($master_array, $getdate);
		}
		$this->_init($master_array);
	
		$page = $this->cObj->fileResource($this->conf['view.']['week.']['weekTemplate']);
		if ($page == '') {
			return "<h3>week: no template file found:</h3>".$this->conf['view.']['week.']['weekTemplate']."<br />Please check your template record and add both cal items at 'include static (from extension)'";
		}

		$weekTemplate = $this->cObj->getSubpart($page,'###WEEK_TEMPLATE###');
		if ($weekTemplate == '') {
			$rems = array ();
			return $this->finish($page, $rems);
		}
 
		$dayStart = $this->conf['view.']['day.']['dayStart']; //'0700';   // Start time for day grid
		$dayEnd = $this->conf['view.']['day.']['dayEnd']; //'2300';   // End time for day grid
		$gridLength = $this->conf['view.']['day.']['gridLength']; //'15';    // Grid distance in minutes for day view, multiples of 15 preferred
		
		if (!isset ($getdate) || $getdate == '') {
			$getdate_obj = new tx_cal_date();
			$getdate = $getdate_obj->format('%Y%m%d');
		}
		
		$day_array2 = array();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = new tx_cal_date($getdate.'000000');
		$today = new tx_cal_date();
		$todayFormatted = $today->format('%Y%m%d');
		
		$now = new tx_cal_date($getdate.'000000');
		$endOfNextMonth = new tx_cal_date(Date_Calc::endOfNextMonth($this_day, $this_month, $this_year));
		$now->addSeconds(60*60*24*31);
		
		$next_month = $now->format('%Y%m%d');
		if($now->after($endOfNextMonth)){
			$next_month = $endOfNextMonth->format('%Y%m%d');
		}
		
		$now = new tx_cal_date($getdate.'000000');
		$startOfPrevMonth = new tx_cal_date(Date_Calc::endOfPrevMonth($this_day, $this_month, $this_year));
		$startOfPrevMonth->setDay(1);
		$now->subtractSeconds(60*60*24*31);
		
		$prev_month = $now->format('%Y%m%d');
		if($now->before($startOfPrevMonth)){
			$prev_month = $startOfPrevMonth->format('%Y%m%d');
		}
		
		$dateOfMonth = Date_Calc::beginOfWeek(1,$this_month,$this_year);
		$start_month_day = new tx_cal_date($dateOfMonth.'000000');

		$thisday2 = $unix_time->format($this->conf['view.']['week.']['dateFormatWeekList']);
		 
		$num_of_events2 = 0;
		
		$next_week_obj = new tx_cal_date();
		$next_week_obj->copy($unix_time);
		$next_week_obj->addSeconds(60*60*24*7);
		$next_week = $next_week_obj->format('%Y%m%d');
		$next_week_obj->subtractSeconds(60*60*24*7*2);
		$prev_week = $next_week_obj->format('%Y%m%d');
		
		$next_day_obj = $unix_time->getNextDay();
		$next_day = $next_day_obj->format('%Y%m%d');
		$prev_day_obj = $unix_time->getPrevDay();
		$prev_day = $prev_day_obj->format('%Y%m%d');

		$dateOfWeek = Date_Calc::beginOfWeek($unix_time->getDay(),$unix_time->getMonth(),$unix_time->getYear());
		
		$week_start_day = new tx_cal_date($dateOfWeek.'000000');

		// Nasty fix to work with TS strftime
		$start_week_time = new tx_cal_date($dateOfWeek.'000000');
		$start_week_time->setTZbyID('UTC');
		$end_week_time = new tx_cal_date();
		$end_week_time->copy($start_week_time);
		$end_week_time->addSeconds(604799);
		
		$GLOBALS['TSFE']->register['cal_week_endtime'] = $end_week_time->getTime();
		$GLOBALS['TSFE']->register['cal_week_starttime'] = $start_week_time->getTime();
		$display_date = $this->cObj->cObjGetSingle($this->conf['view.']['week.']['titleWrap'],$this->conf['view.']['week.']['titleWrap.']);

		$this->initLocalCObject();
		$dayLinkViewTarget = &$this->conf['view.']['dayLinkTarget'];
		$this->local_cObj->data['view'] = $dayLinkViewTarget;
		
		$this->local_cObj->setCurrentVal($this->conf['view.']['week.']['legendNextDayLink']);
		$legend_next_day_link = $this->local_cObj->cObjGetSingle($this->conf['view.']['week.']['legendNextDayLink'],$this->conf['view.']['week.']['legendNextDayLink.']);

		$this->local_cObj->setCurrentVal($this->conf['view.']['week.']['legendPrevDayLink']);
		$legend_prev_day_link = $this->local_cObj->cObjGetSingle($this->conf['view.']['week.']['legendPrevDayLink'],$this->conf['view.']['week.']['legendPrevDayLink.']);
		

		// Figure out colspans
		$dayborder = 0;
		$thisdate = $start_week_time;
		
		$eventArray = array();
		
		$view_array = array ();
		$rowspan_array = array();
		
		$endOfDay = new tx_cal_date();
		$startOfDay = new tx_cal_date();
		
		//creating the dateObjects only once:
		$starttime = new tx_cal_date();
		$endtime = new tx_cal_date();
		$j = new tx_cal_date();
	
		if (count($this->master_array) > 0) {
			$masterKeys = array_keys($this->master_array);
			foreach ($masterKeys as $ovlKey) {
				$dTimeStart = array();
				$dTimeEnd = array();
				$dDate = array();
				preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayStart'], $dTimeStart);
				preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayEnd'], $dTimeEnd);
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
				
				$d_start = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeStart[1].':'.sprintf("%02d", $dTimeStart[2]).':00');
				$d_start->setTZbyID('UTC');
				$d_end = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeEnd[1].':'.sprintf("%02d", $dTimeEnd[2]).':00');
				$d_end->setTZbyID('UTC');
				
                    //minus 1 second to allow endtime 24:00
                    $d_end->subtractSeconds(1);
				$ovlTimeKeys = array_keys($this->master_array[$ovlKey]);
				foreach ($ovlTimeKeys as $ovl_time_key) {	
					$ovlDayKeys = array_keys($this->master_array[$ovlKey][$ovl_time_key]);			 
					foreach ($ovlDayKeys as $ovl2Key) {
						$event = &$this->master_array[$ovlKey][$ovl_time_key][$ovl2Key];
						$eventStart = $event->getStart();
						$eventMappingKey = $event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M');
						$eventArray[$ovlKey.'_'.$eventMappingKey] = &$event;
						
						$starttime->copy($event->getStart());
						$endtime->copy($event->getEnd());
						
						if ($ovl_time_key == '-1') {
							
							$j->copy($starttime);
							$view_array[$j->format('%Y%m%d')]['-1'][] = $ovlKey.'_'.$eventMappingKey;
							$j->addSeconds(86400);
							for($j; $j->before($endtime) && $j->before($end_week_time); $j->addSeconds(86400)){
								$view_array[$j->format('%Y%m%d')]['-1'][] = $ovlKey.'_'.$eventMappingKey;
							}
							 
						} else if($starttime->before($end_week_time)){
							$starttime->subtractSeconds(($starttime->getMinute()%$gridLength)*60);
							$endtime->addSeconds((($endtime->getMinute())%$gridLength)*60);

							$entries = 1;
							$old_day = new tx_cal_date($ovlKey.'000000');
							
							$endOfDay->copy($d_end);
							$startOfDay->copy($d_start);
							
							// get x-array possition
							for($k = 0; $k < count($view_array[($ovlKey)]); $k++) {
								if (empty($view_array[$starttime->format('%Y%m%d')][$starttime->format('%H%M')][$k])) {
									break;
								}
							}
							
							$j->copy($starttime);
							
							if($j->before($startOfDay)){
								$j->copy($startOfDay);
							}

							$counter = 0;

							while($j->before($endtime) && $j->before($end_week_time)){
								$counter++;
								$view_array[$j->format('%Y%m%d')][$j->format('%H%M')][] = $ovlKey.'_'.$eventMappingKey;
								if($j->after($endOfDay)){
									$rowspan_array[$old_day->format('%Y%m%d')][$eventMappingKey] = $entries-1;
									$endOfDay->addSeconds(86400);
									$old_day->copy($endOfDay);
									$startOfDay->addSeconds(86400);
									$j->addSeconds(86400);
									$j->setHour($startOfDay->getHour());
									$j->setMinute($startOfDay->getMinute());
									$j->subtractSeconds($gridLength*60);
									for($k = 0; $k < count($view_array[$startOfDay->format('%Y%m%d')]); $k++) {
										if (empty($view_array[$startOfDay->format('%Y%m%d')][$startOfDay->format('%H%M')][$k])) {
											break;
										}
									}
									$entries = 0;
									$eventArray[$startOfDay->format('%Y%m%d').'_'.$eventMappingKey] = &$event;
								}
								$j->addSeconds($gridLength * 60);
								$entries++;
							}
							$rowspan_array[$old_day->format('%Y%m%d')][$eventMappingKey] = $entries-1;
						}
					}
				}
			}
		}

		if($this->conf['view.']['week.']['dynamic']==1){
			$dayStart = '2359';
			$dayEnd = '0000';
			$firstStart = true;
			$firstEnd = true;
			$dynamicEnd = intval($end_week_time->format('%Y%m%d'));
			for($dynamicStart= intval($start_week_time->format('%Y%m%d')); $dynamicStart < $dynamicEnd; $dynamicStart++){
				if(is_array($view_array[$dynamicStart])){
					$timeKeys = array_keys($view_array[$dynamicStart]);
					$formatedLast = array_pop($timeKeys);
					while(intval($formatedLast)< 0 && !empty($timeKeys)) {
						$formatedLast = array_pop($timeKeys);
					}
					
					$formatedFirst = null;
					if(count($timeKeys)>0){
						do{
							$formatedFirst = array_shift($timeKeys);
						}
						while(intval($formatedFirst)< 0 && !empty($timeKeys));
					} else {
						$formatedFirst = $formatedLast;
					}
					if(intval($formatedFirst) > 0 && (intval($formatedFirst) < intval($dayStart) || $firstStart)){
						$dayStart = sprintf("%04d", $formatedFirst);
						$firstStart = false;
					}
					if(intval($formatedLast) > intval($dayEnd) || $firstEnd){
						$dayEnd = sprintf("%04d", $formatedLast + $gridLength);
						$firstEnd = false;
					}
				}
			}
			$dayStart = substr($dayStart,0,2).'00';
		}
		$startdate = new tx_cal_date($start_week_time->format('%Y%m%d').'000000');
		$enddate = new tx_cal_date();
		$enddate->copy($end_week_time);
		for($i = $startdate; $enddate->after($i); $i->addSeconds(86400)) {
			if (!empty($view_array[$i->format('%Y%m%d')])) {
				$max = array();
				foreach (array_keys($view_array[$i->format('%Y%m%d')]) as $array_time) {
					$c = count($view_array[$i->format('%Y%m%d')][$array_time]);
					array_push($max, $c);
				}
				$nbrGridCols[$i->format('%Y%m%d')] = max($max);
			} else {
				$nbrGridCols[$i->format('%Y%m%d')] = 1;
			}
		}
		$t_array = array ();
		$pos_array = array ();
		preg_match('/([0-9]{2})([0-9]{2})/', $dayStart, $dTimeStart);
		preg_match('/([0-9]{2})([0-9]{2})/', $dayEnd, $dTimeEnd);
		
		$nd = new tx_cal_date();

		foreach(array_keys($view_array) as $week_key) {
			$week_day = &$view_array[$week_key];
			preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);
			$d_start = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeStart[1].':'.sprintf("%02d", $dTimeStart[2]).':00');
			$d_start->setTZbyId('UTC');
			$d_end = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeEnd[1].':'.sprintf("%02d", $dTimeEnd[2]).':00');
			$d_end->setTZbyId('UTC');
			
			$d_start->subtractSeconds(($d_start->getMinute()%$gridLength)*60);
			$d_end->addSeconds(($gridLength-(($d_end->getMinute())%$gridLength))*60);

			for ($i->copy($d_start); !$i->after($d_end); $i->addSeconds($gridLength * 60)) {
				$timeKey = $i->format('%H%M');
				if (is_array($view_array[$week_key][$timeKey]) && count($view_array[$week_key][$timeKey]) > 0) {
					foreach (array_keys($view_array[$week_key][$timeKey]) as $eventKey) {
						$event = &$eventArray[$view_array[$week_key][$timeKey][$eventKey]];
						$eventStart = $event->getStart();
						$startFormatted = $eventStart->format('%Y%m%d%H%M');
						$eventType = $event->getType();
						$eventUid = $event->getUid();
						if (is_array($pos_array[$week_key]) && array_key_exists($eventType.$eventUid.'_'.$startFormatted, ($pos_array[$week_key]))) {
							
							$nd->copy($event->getEnd());
							$nd->addSeconds(($gridLength-(($nd->getMinute())%$gridLength))*60);
							if ($nd->before($i)) {
								$t_array[$week_key][$timeKey][$pos_array[$week_key][$eventType.$eventUid.'_'.$startFormatted]] = array ('ended' => $week_key.'_'.$eventType.'_'.$eventUid.'_'.$startFormatted);
							} else {
								$t_array[$week_key][$timeKey][$pos_array[$week_key][$eventType.$eventUid.'_'.$startFormatted]] = array ('started' => $week_key.'_'.$eventType.'_'.$eventUid.'_'.$startFormatted);
							}
						} else {
							for ($j = 0; $j < $nbrGridCols[$week_key]?$nbrGridCols[$week_key]:1; $j ++) {
								if (!isset ($t_array[$week_key][$timeKey][$j]) || count($t_array[$week_key][$timeKey][$j]) == 0) {
									$pos_array[$week_key][$event->getType().$event->getUid().'_'.$startFormatted] = $j;
									$t_array[$week_key][$timeKey][$j] = array ('begin' => $week_key.'_'.$eventType.'_'.$eventUid.'_'.$startFormatted);
									break;
								}
							}
						}
					}
					//debug($t_array);
					 
				} else {
					$t_array[$week_key][$timeKey] = '';
				}
			}
		}

		$thisdate = new tx_cal_date(); 
		$thisdate->copy($week_start_day);

		for ($i = 0; $i < 7; $i ++) {
			$weekarray[$i] = $thisdate->format('%Y%m%d');
			$thisdate->addSeconds(86400);
		}
		
		$sims = array (
			'###GETDATE###' => $getdate,
			'###DISPLAY_DATE###' => $display_date, 
			'###LEGEND_PREV_DAY###' => $legend_prev_day_link, 
			'###LEGEND_NEXT_DAY###' => $legend_next_day_link, 
			'###SIDEBAR_DATE###' => '',
			);
			
		// Replaces the allday events
		$alldays = $this->cObj->getSubpart($weekTemplate, '###ALLDAYSOFWEEK##');

		foreach ($weekarray as $get_date) {
			$replace = '';
			if (is_array($view_array[$get_date]['-1'])) {
				foreach ($view_array[$get_date]['-1'] as $id => $allday) {
					$replace .= $eventArray[$allday]->renderEventForAllDay();					 
				}
			}
			$weekreplace .= tx_cal_functions::substituteMarkerArrayNotCached($alldays,Array('###COLSPAN###'=>'colspan="'.($nbrGridCols[$get_date]?$nbrGridCols[$get_date]:1).'"','###ALLDAY###'=>$replace));;
		}

		$rems = array ();
		$rems['###ALLDAYSOFWEEK###'] = $weekreplace;

		// Replaces the daysofweek
		$loop_dof = $this->cObj->getSubpart($weekTemplate, '###DAYSOFWEEK###');

		$start_day = new tx_cal_date();
		$start_day->copy($week_start_day);
		
		$isAllowedToCreateEvent = $this->rightsObj->isAllowedToCreateEvent();

		for ($i = 0; $i < 7; $i ++) {
			$day_num = $start_day->format('%w');

			$daylink = $start_day->format('%Y%m%d');

			$weekday = $start_day->format($this->conf['view.']['week.']['dateFormatWeekList']);

			if ($daylink == $getdate) {
				$row1 = 'rowToday';
				$row2 = 'rowOn';
				$row3 = 'rowToday';
			} else {
				$row1 = 'rowOff';
				$row2 = 'rowOn';
				$row3 = 'rowOff';
			}

			
			$dayLinkViewTarget = &$this->conf['view.']['dayLinkTarget'];
			if(($this->rightsObj->isViewEnabled($dayLinkViewTarget) || $this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid']) && ($view_array[$daylink] || $isAllowedToCreateEvent)){
				$this->initLocalCObject();
				$this->local_cObj->setCurrentVal($weekday);
				if (!empty ($this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid'])) {
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid']);
				} else {
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway']);
				}
				$this->local_cObj->data['view'] = $dayLinkViewTarget;
				$link = $this->local_cObj->cObjGetSingle($this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewLink'],$this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewLink.']);;
				$link = $this->cObj->stdWrap($link, $this->conf['view.']['week.']['weekday_stdWrap.']);
			}else{
				$link = $this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']);
			}
			$start_day->addSeconds(86400);
			$colspan = 'colspan="'.($nbrGridCols[$daylink]?$nbrGridCols[$daylink]:1).'"';
			$search = array ('###LINK###', '###DAYLINK###', '###ROW1###', '###ROW2###', '###ROW3###', '###COLSPAN###', '###TIME###');
			$replace = array ($link, $daylink, $row1, $row2, $row3, $colspan, $start_day->format('%Y %m %d %H %M %s'));
			$loop_tmp = str_replace($search, $replace, $loop_dof);
			$weekday_loop .= $loop_tmp;
		}

		$rems['###DAYSOFWEEK###'] = $weekday_loop;
		 
		// Build the body
		$border = 0;
		$thisdate = $start_week_time;
		 
		$dTimeStart[2] -= $dTimeStart[2] % $gridLength;
		$dTimeEnd[2] -= $dTimeEnd[2] % $gridLength;

		preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);

		$loops = (($dTimeEnd[1]*60+$dTimeEnd[2])-($dTimeStart[1]*60+$dTimeStart[2]))/($gridLength);
		
		$weekdisplay = '';
		
		$createOffset = intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60;
		
		$cal_time_obj = new tx_cal_date();
		$cal_time_obj->copy($week_start_day);
		$cal_time_obj->setHour(intval($dTimeStart[1]));
		$cal_time_obj->setMinute(intval($dTimeStart[2]));

		$start = 0;
		
		for($i = $start; $i < $loops; $i++) {
			$time = $cal_time_obj->format('%H%M');
			for($j = 0; $j < 7; $j++) {
				$day = $cal_time_obj->format('%Y%m%d');
				if ($j == 0) {
					$key = $cal_time_obj->format('%I:%M');
					if (preg_match ('/([0-9]{1,2}):00/', $key)) {
						$weekdisplay .= sprintf($this->conf['view.']['week.']['weekDisplayFullHour'], (60 / $gridLength), $cal_time_obj->format($this->conf['view.']['week.']['timeFormatWeek']), $gridLength);
					} else {
						$weekdisplay .= sprintf($this->conf['view.']['week.']['weekDisplayInbetween'], $gridLength);
					}
				}
				$something = $t_array[$day][$time];

				$class = $this->conf['view.']['week.']['classWeekborder'];
				if($day == $todayFormatted){
					$class .= ' '.$this->conf['view.']['week.']['classTodayWeekborder'];
				}
				if (is_array($something) && $something != "" && count($something) > 0) {
					for ($k = 0; $k < count($something); $k ++) {
						if (!empty($something[$k])) {
							$keys = array_keys($something[$k]);
							switch ($keys[0]) {
								case 'begin' :
								$event = &$eventArray[$something[$k][$keys[0]]];
								
								$rest = $event->getEnd()->getMinute() % ($gridLength * 60);
								$plus = 0;
								if ($rest > 0) {
									$plus = 1;
								}

								$weekdisplay .= sprintf($this->conf['view.']['week.']['weekEventPre'],$rowspan_array[$day][$event->getType().'_'.$event->getUid().'_'.$event->getStart()->format('%Y%m%d%H%M')]);
								$weekdisplay .= $event->renderEventForWeek();
								$weekdisplay .= $this->conf['view.']['week.']['weekEventPost'];
								// End event drawing
								break;
							}
						}
					}
					if (count($something) < ($nbrGridCols[$day]?$nbrGridCols[$day]:1)) {
						$remember = 0;
						for($l = 0; $l < ($nbrGridCols[$day]?$nbrGridCols[$day]:1); $l++) {
							if (!$something[$l]) {
								$remember++;
							}
							else if($remember > 0) {
								$weekdisplay .= $this->getCreateEventLink('week', $this->conf['view.']['week.']['normalCell'], $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $remember, $class, $time);
								$remember = 0;
							}
						}
						if ($remember > 0) {
							$weekdisplay .= $this->getCreateEventLink('week', $this->conf['view.']['week.']['normalCell'], $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $remember, $class, $time);
							$remember = 0;
						}
					}
					 
				} else {
					$weekdisplay .= $this->getCreateEventLink('week', $this->conf['view.']['week.']['normalCell'], $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $nbrGridCols[$day]?$nbrGridCols[$day]:1, $class, $time);
				}
				
				if ($j == 6) {
					$weekdisplay .= $this->conf['view.']['week.']['weekFinishRow'];
				}
				$cal_time_obj->addSeconds(86400);
			}
			$cal_time_obj->setYear($week_start_day->getYear());
			$cal_time_obj->setMonth($week_start_day->getMonth());
			$cal_time_obj->setDay($week_start_day->getDay());
			$cal_time_obj->addSeconds($gridLength * 60);
		}
		$weekTemplate = tx_cal_functions::substituteMarkerArrayNotCached($weekTemplate, $sims, array (), array ());
		$rems['###LOOPEVENTS###'] = $weekdisplay;
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, array(), array ('###WEEK_TEMPLATE###'=>$weekTemplate), array ());
		return $this->finish($page, $rems);
	}
}
 
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']);
}
?>