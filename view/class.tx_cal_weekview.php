<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
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
	class tx_cal_weekview extends tx_cal_base_view {
		
		function tx_cal_weekview(){
			$this->tx_cal_base_view();
		}
	
		/**
		*  Draws the week view.
		*  @param  $master_array array  The events to be drawn.
		*  @param  $getdate  integer  The date of the event
		* @return  string  The HTML output.
		*/
		function drawWeek(&$master_array, $getdate) {
			 
			$this->_init($master_array);
		
			$page = $this->cObj->fileResource($this->conf['view.']['week.']['weekTemplate']);
			if ($page == '') {
				return "<h3>week: no template file found:</h3>".$this->conf['view.']['week.']['weekTemplate']."<br />Please check your template record and add both cal items at 'include static (from extension)'";
			}
			$page = $this->replace_files($page, array (
			'sidebar' => $this->conf['view.']['other.']['sidebarTemplate'])
			);
			
			$weekTemplate = $this->cObj->getSubpart($page,'###WEEK_TEMPLATE###');
			if ($weekTemplate == '') {
				$rems = array ();
				return $this->finish($page, $rems);
			}
	 
			$weekStartDay = $this->conf['view.']['weekStartDay']; //'Monday';   // Day of the week your week starts on
			$dayStart = $this->conf['view.']['day.']['dayStart']; //'0700';   // Start time for day grid
			$dayEnd = $this->conf['view.']['day.']['dayEnd']; //'2300';   // End time for day grid
			$gridLength = $this->conf['view.']['day.']['gridLength']; //'15';    // Grid distance in minutes for day view, multiples of 15 preferred
			
			if (!isset ($getdate) || $getdate == '') {
				$getdate_obj = new tx_cal_date();
				$getdate = $getdate_obj->format('%Y%m%d');
			}
			
			$day_array2 = array();
			ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $getdate, $day_array2);
			$this_day = $day_array2[3];
			$this_month = $day_array2[2];
			$this_year = $day_array2[1];
			$unix_time = new tx_cal_date($getdate,'Ymd');
			
			$now = new tx_cal_date($getdate,'Ymd');
			$endOfNextMonth = new tx_cal_date(Date_Calc::endOfNextMonth($this_day, $this_month, $this_year));
			$now->addSeconds(60*60*24*31);
			
			$next_month = $now->format('%Y%m%d');
			if($now->after($endOfNextMonth)){
				$next_month = $endOfNextMonth->format('%Y%m%d');
			}
			
			$now = new tx_cal_date($getdate,'Ymd');
			$startOfPrevMonth = new tx_cal_date(Date_Calc::endOfPrevMonth($this_day, $this_month, $this_year));
			$startOfPrevMonth->setDay(1);
			$now->subtractSeconds(60*60*24*31);
			
			$prev_month = $now->format('%Y%m%d');
			if($now->before($startOfPrevMonth)){
				$prev_month = $startOfPrevMonth->format('%Y%m%d');
			}
			
			$dateOfMonth = Date_Calc::beginOfWeek(1,$this_month,$this_year);
			$start_month_day = new tx_cal_date($dateOfMonth,'Ymd');
			if($weekStartDay=='Sunday'){
				$start_month_day = $start_month_day->getPrevDay();
			}
			 
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
			$prev_day = $next_week_obj->format('%Y%m%d');
			
			$dateOfWeek = Date_Calc::beginOfWeek($this_day,$this_month,$this_year);
			$week_start_day = new tx_cal_date($dateOfWeek,'Ymd');
			if($weekStartDay=='Sunday'){
				$week_start_day = $week_start_day->getPrevDay();
			}
			else if($weekStartDay=='Wednesday'){
				$week_start_day->addSeconds(259200);
			}

			// Nasty fix to work with TS strftime
			$start_week_time = new tx_cal_date($dateOfWeek,'Ymd');
			$start_week_time->setTZbyID('UTC');
			$end_week_time = new tx_cal_date();
			$end_week_time->copy($start_week_time);
			$end_week_time->addSeconds(604799);

			$GLOBALS['TSFE']->register['cal_week_endtime'] = $end_week_time->format($this->conf['view.']['week.']['strftimeTitleStartFormat']);
			$GLOBALS['TSFE']->register['cal_week_starttime'] = $start_week_time->format($this->conf['view.']['week.']['strftimeTitleEndFormat']);
			$display_date = $this->cObj->cObjGetSingle($this->conf['view.']['week.']['titleWrap'],$this->conf['view.']['week.']['titleWrap.']);

			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
			if (!empty ($this->conf['view.']['week.']['weekViewPid'])) {
				$next_week_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['nextWeekSymbol'], array ('getdate' => $next_week, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
				$prev_week_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['previousWeekSymbol'], array ('getdate' => $prev_week, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
			} else {
				$next_week_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['nextWeekSymbol'], array ('getdate' => $next_week, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
				$prev_week_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['previousWeekSymbol'], array ('getdate' => $prev_week, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			 
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
			if (!empty ($this->conf['view.']['day.']['dayViewPid'])) {
				$next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['nextDaySymbol'], array ('getdate' => $next_day, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
				$prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['previousDaySymbol'], array ('getdate' => $prev_day, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
				$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['legendPrevDayLink'], array ('getdate' => $next_week, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
				$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['legendNextDayLink'], array ('getdate' => $prev_week, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
			} else {
				$next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['nextDaySymbol'], array ('getdate' => $next_day, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
				$prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['previousDaySymbol'], array ('getdate' => $prev_day, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
				$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['legendPrevDayLink'], array ('getdate' => $next_week, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
				$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['legendNextDayLink'], array ('getdate' => $prev_week, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			 
			// Figure out colspans
			$dayborder = 0;
			$thisdate = $start_week_time;
			
			$eventArray = array();
			
			$view_array = array ();
			$rowspan_array = array();
			
			if (count($this->master_array) > 0) {
				foreach (array_keys($this->master_array) as $ovlKey) {
					$eventDate = new tx_cal_date($ovlKey,'Ymd');
					$eventDate->setTZbyID('UTC');
					if ($eventDate->before($start_week_time) || $eventDate->after($end_week_time)) {
						continue;
					}
					
					$dTimeStart = array();
					$dTimeEnd = array();
					$dDate = array();
					preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayStart'], $dTimeStart);
					preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayEnd'], $dTimeEnd);
					preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
					
					$d_start = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeStart[1].':'.sprintf("%02d", $dTimeStart[2]).':00','Ymd H:i:s (Z)');
					$d_start->setTZbyID('UTC');
					$d_end = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeEnd[1].':'.sprintf("%02d", $dTimeEnd[2]).':00','Ymd H:i:s (Z)');
					$d_end->setTZbyID('UTC');
					
                    //minus 1 second to allow endtime 24:00
                    $d_end->subtractSeconds(1);
					foreach (array_keys($this->master_array[$ovlKey]) as $ovl_time_key) {					 
						foreach (array_keys($this->master_array[$ovlKey][$ovl_time_key]) as $ovl2Key) {
							$event = &$this->master_array[$ovlKey][$ovl_time_key][$ovl2Key];
							$eventStart = $event->getStart();
							$eventArray[$ovlKey.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M')] = &$event;
							$starttime = new tx_cal_date();
							$starttime->copy($event->getStart());
							$endtime = new tx_cal_date();
							$endtime->copy($event->getEnd());
							if ($ovl_time_key == '-1') {
								$j = new tx_cal_date();
								$j->copy($starttime);
								$view_array[$j->format('%Y%m%d')]['-1'][] = $ovlKey.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M');
								$j->addSeconds(86400);
								for($j; $j->before($endtime); $j->addSeconds(86400)){
									$view_array[$j->format('%Y%m%d')]['-1'][] = $ovlKey.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M');
								}
								 
							} else if($starttime->before($end_week_time)){
								$starttime->subtractSeconds(($starttime->getMinute()%$gridLength)*60);
								$endtime->addSeconds((($endtime->getMinute())%$gridLength)*60);

								$entries = 1;
								$old_day = new tx_cal_date($ovlKey,'Ymd');
								$endOfDay = $d_end;
								$startOfDay = $d_start;
								
								// get x-array possition
								for($k = 0; $k < count($view_array[($ovlKey)]); $k++) {
									if (empty($view_array[$starttime->format('%Y%m%d')][$starttime->format('%H%M')][$k])) {
										break;
									}
								}
								$j = new tx_cal_date();
								$j->copy($starttime);

								$counter = 0;

								while($j->before($endtime) && $j->before($end_week_time)){
									$counter++;
									$view_array[$j->format('%Y%m%d')][$j->format('%H%M')][$k] = $ovlKey.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M');
									if($j->after($endOfDay)){
										$rowspan_array[$old_day->format('%Y%m%d')][$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M')] = $entries-1;
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
										$eventArray[$startOfDay->format('%Y%m%d').'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M')] = &$event;
									}
									$j->addSeconds($gridLength * 60);
									$entries++;
								}
								$rowspan_array[$old_day->format('%Y%m%d')][$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M')] = $entries-1;
							}
						}
					}
				}
			}

			if($this->conf['view.']['week.']['dynamic']){
				$keys = array_keys($view_array);
				$firstStart = true;
				$firstEnd = true;
				foreach($keys as $key){
					$timeKeys = array_keys($view_array[$key]);
					$formatedLast = array_pop($timeKeys);
					$formatedFirst = array_shift($timeKeys);
					if(intval($formatedFirst) < intval($dayStart) || $firstStart){
						$dayStart = $formatedFirst;
						$firstStart = false;
					}
					if(intval($formatedLast) > intval($dayEnd) || $firstEnd){
						$dayEnd = $formatedLast;
						$firstEnd = false;
					}
				}
			}

			$startdate = new tx_cal_date($start_week_time->format('%Y%m%d'),'Ymd');
			$enddate = new tx_cal_date($end_week_time->format('%Y%m%d'),'Ymd');
			
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

			foreach(array_keys($view_array) as $week_key) {
				$week_day = &$view_array[$week_key];
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);
				$d_start = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeStart[1].':'.sprintf("%02d", $dTimeStart[2]).':00','Ymd H:i:s');
				$d_start->toUTC();
				$d_end = new tx_cal_date($dDate[1].$dDate[2].$dDate[3].' '.$dTimeEnd[1].':'.sprintf("%02d", $dTimeEnd[2]).':00','Ymd H:i:s');
				$d_end->toUTC();
				
				$d_start->subtractSeconds(($d_start->getMinute()%$gridLength)*60);
				$d_end->addSeconds(($gridLength-(($d_end->getMinute())%$gridLength))*60);

				for ($i->copy($d_start); !$i->after($d_end); $i->addSeconds($gridLength * 60)) {
					$timeKey = $i->format('%H%M');
					if (is_array($view_array[$week_key][$timeKey]) && count($view_array[$week_key][$timeKey]) > 0) {
						foreach (array_keys($view_array[$week_key][$timeKey]) as $eventKey) {
							$event = &$eventArray[$view_array[$week_key][$timeKey][$eventKey]];
							$eventStart = $event->getStart();
							if (is_array($pos_array[$week_key]) && array_key_exists($event->getType().$event->getUid(), ($pos_array[$week_key]))) {
								$nd = new tx_cal_date();
								$nd->copy($event->getEnd());
								$nd->addSeconds(($gridLength-(($nd->getMinute())%$gridLength))*60);
								if ($nd->before($i)) {
									$t_array[$week_key][$timeKey][$pos_array[$week_key][$event->getType().$event->getUid()]] = array ('ended' => $week_key.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M'));
								} else {
									$t_array[$week_key][$timeKey][$pos_array[$week_key][$event->getType().$event->getUid()]] = array ('started' => $week_key.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M'));
								}
							} else {
								for ($j = 0; $j < $nbrGridCols[$week_key]?$nbrGridCols[$week_key]:1; $j ++) {
									if (count($t_array[$week_key][$timeKey][$j]) == 0 || !isset ($t_array[$week_key][$timeKey][$j])) {
										$pos_array[$week_key][$event->getType().$event->getUid()] = $j;
										$t_array[$week_key][$timeKey][$j] = array ('begin' => $week_key.'_'.$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M'));
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

			$thisdate = new tx_cal_date($start_week_time->format('%Y%m%d'),'Ymd');

			for ($i = 0; $i < 7; $i ++) {
				$weekarray[$i] = $thisdate->format('%Y%m%d');
				$thisdate->addSeconds(86400);
			}
			
			$sims = array (
				'###GETDATE###' => $getdate,
				'###DISPLAY_DATE###' => $display_date, 
				'###LEGEND_PREV_DAY###' => $legend_prev_day_link, 
				'###LEGEND_NEXT_DAY###' => $legend_next_day_link, 
				'###NEXT_DAY_LINK###' => $next_day_link, 
				'###PREV_DAY_LINK###' => $prev_day_link,
				'###NEXT_WEEK_LINK###' => $next_week_link, 
				'###PREV_WEEK_LINK###' => $prev_week_link,
				'###SIDEBAR_DATE###' => '',
				'###L_NEXT###' => $this->controller->pi_getLL('l_next'),
				'###L_PREV###' => $this->controller->pi_getLL('l_prev'),
				'###L_GOPRINT###' => $this->controller->pi_getLL('l_goprint'), 
				'###L_PREFERENCES###' => $this->controller->pi_getLL('l_preferences'), 
				'###L_CALENDAR###' => $this->controller->pi_getLL('l_calendar'), 
				'###L_LEGEND###' => $this->controller->pi_getLL('l_legend'), 
				'###L_TOMORROWS###' => $this->controller->pi_getLL('l_tomorrows'), 
				'###L_JUMP###' => $this->controller->pi_getLL('l_jump'), 
				'###L_TODO###' => $this->controller->pi_getLL('l_todo'), 
				'###L_DAY###' => $this->controller->pi_getLL('l_day'), 
				'###L_WEEK###' => $this->controller->pi_getLL('l_week'), 
				'###L_MONTH###' => $this->controller->pi_getLL('l_month'), 
				'###L_YEAR###' => $this->controller->pi_getLL('l_year'), 
				'###L_POWERED_BY###' => $this->controller->pi_getLL('l_powered_by'), 
				'###L_SUBSCRIBE###' => $this->controller->pi_getLL('l_subscribe'), 
				'###L_DOWNLOAD###' => $this->controller->pi_getLL('l_download'), 
				'###L_THIS_SITE_IS###' => $this->controller->pi_getLL('l_this_site_is'),
				);
				
			// Replaces the allday events
			$loop_begin = $this->cObj->getSubpart($weekTemplate, '###ALLDAYSOFWEEK_BEGIN###');
			$loop_end = $this->cObj->getSubpart($weekTemplate, '###ALLDAYSOFWEEK_END###');
		 
			foreach ($weekarray as $get_date) {
				$replace = sprintf($loop_begin,'colspan="'.($nbrGridCols[$get_date]?$nbrGridCols[$get_date]:1).'"');
				
				if (is_array($view_array[$get_date]['-1'])) {
					foreach ($view_array[$get_date]['-1'] as $id => $allday) {
						$replace .= $eventArray[$allday]->renderEventForAllDay();					 
					}
				}
				$replace .= $loop_end;
				$weekreplace .= $replace;
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
				if(($this->rightsObj->isViewEnabled($this->conf['view.']['dayLinkTarget']) || $this->conf['view.'][$this->conf['view.']['dayLinkTarget'].'.'][$this->conf['view.']['dayLinkTarget'].'ViewPid']) && ($view_array[$daylink] || $isAllowedToCreateEvent)){
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
					if (!empty ($this->conf['view.']['day.']['dayViewPid'])) {
						$link = $this->controller->pi_linkTP_keepPIvars($this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']), array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
					} else {
						$link = $this->controller->pi_linkTP_keepPIvars($this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']), array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
					}
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

			$d_start = new tx_cal_date();
			$d_start->copy($week_start_day);
			$d_start->setHour($dTimeStart[1]);
			$d_start->setMinute($dTimeStart[2]);
			$d_end = new tx_cal_date();
			$d_end->copy($week_start_day);
			$d_end->addSeconds(608400);
			$d_end->setHour($dTimeEnd[1]);
			$d_end->setMinute($dTimeEnd[2]);
			
			$loops = (($dTimeEnd[1]*60+$dTimeEnd[2])-($dTimeStart[1]*60+$dTimeStart[2]))/($gridLength);
			$d_start_time = $d_start->subtractSeconds(1);
			$weekdisplay = '';
			
			$calcOffset = new tx_cal_date();
			//TODO find a better solution for CET
			$calcOffset->setTZbyID('CET');
			$createOffset = (intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60) + $calcOffset->tz->getOffset($calcOffset)/1000;
			
			$cal_time_obj = new tx_cal_date();
			$cal_time_obj->copy($d_start);
			$cal_time_obj->toUTC();
			$cal_time_obj->setHour(intval($dTimeStart[1]));
			$cal_time_obj->setMinute(intval($dTimeStart[2]));

			$start = 0;
			for($i = $start; $i < $loops; $i++) {
				$time = $cal_time_obj->format('%H%M');
				for($j = 0; $j < 7; $j++) {
					$day = $cal_time_obj->format('%Y%m%d');
					if ($j == 0) {
						$key = $cal_time_obj->format($this->conf['view.']['week.']['timeFormatWeek']);
						if (ereg('([0-9]{1,2}):00', $key)) {
							$weekdisplay .= sprintf($this->conf['view.']['week.']['weekDisplayFullHour'], (60 / $gridLength), $key, $gridLength);
						} else {
							$weekdisplay .= sprintf($this->conf['view.']['week.']['weekDisplayInbetween'], $gridLength);
						}
					}
					$something = $t_array[$day][$time];

					$class = ' '.$this->conf['view.']['week.']['classWeekborder'];
					if (is_array($something) && $something != "" && count($something) > 0) {
						for ($k = 0; $k < count($something); $k ++) {
							if (!empty($something[$k])) {
								$keys = array_keys($something[$k]);
								switch ($keys[0]) {
									case 'begin' :
									$event = &$eventArray[$something[$k][$keys[0]]];
									$eventStart = $event->getStart();
									$dayEndTime = new tx_cal_date();
									$dayEndTime->copy($event->getEnd());
									$dayStartTime = new tx_cal_date();
									$dayStartTime->copy($event->getStart());

									$rest = $dayEndTime->getMinute() % ($gridLength * 60);
									$plus = 0;
									if ($rest > 0) {
										$plus = 1;
									}

									$weekdisplay .= sprintf($this->conf['view.']['week.']['weekEventPre'],$rowspan_array[$day][$event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M')]);
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
				$cal_time_obj->setYear($d_start->getYear());
				$cal_time_obj->setMonth($d_start->getMonth());
				$cal_time_obj->setDay($d_start->getDay());
				$cal_time_obj->addSeconds($gridLength * 60);
			}
			$weekTemplate = $this->cObj->substituteMarkerArrayCached($weekTemplate, $sims, array (), array ());
			$rems['###LOOPEVENTS###'] = $weekdisplay;
			$page = $this->cObj->substituteMarkerArrayCached($page, array(), array ('###WEEK_TEMPLATE###'=>$weekTemplate), array ());
			return $this->finish($page, $rems);
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']) {
		include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']);
	}
?>
