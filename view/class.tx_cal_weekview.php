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
			 
			$weekStartDay = $this->conf['view.']['weekStartDay']; //'Monday';   // Day of the week your week starts on
			$dayStart = $this->conf['view.']['day.']['dayStart']; //'0700';   // Start time for day grid
			$dayEnd = $this->conf['view.']['day.']['dayEnd']; //'2300';   // End time for day grid
			$gridLength = $this->conf['view.']['day.']['gridLength']; //'15';    // Grid distance in minutes for day view, multiples of 15 preferred
			 
			$day_array2 = array();
			ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $getdate, $day_array2);
			$this_day = $day_array2[3];
			$this_month = $day_array2[2];
			$this_year = $day_array2[1];
			$unix_time = gmmktime(0, 0, 0, $this_month, $this_day, $this_year);
			 
			$next_month = gmdate('Ymd', gmmktime(0, 0, 0, $this_month+1, $this_day, $this_year));
			$prev_month = gmdate('Ymd', gmmktime(0, 0, 0, $this_month-1, $this_day, $this_year));
			 
			$start_month_day = tx_cal_calendar :: dateOfWeek(gmmktime(0, 0, 0, $this_month, 1, $this_year), $weekStartDay, $weekStartDay);
			 
			$thisday2 = gmstrftime($this->conf['view.']['week.']['dateFormatWeekList'], $unix_time);
			 
			$num_of_events2 = 0;
			 
			$next_week = gmdate('Ymd', $unix_time + 604800); // 7 * 24 * 60 * 60
			$prev_week = gmdate('Ymd', $unix_time - 604800);
			$next_day = gmdate('Ymd', $unix_time + 86400);
			$prev_day = gmdate('Ymd', $unix_time - 86400);
			
			$dateOfWeek = tx_cal_calendar :: dateOfWeek($unix_time, $weekStartDay, $weekStartDay);

			// Nasty fix to work with TS strftime
			$start_week_time_without_gm = strtotime($dateOfWeek);
			$end_week_time_without_gm = $start_week_time_without_gm + 604799;
			
			$start_week_time = $start_week_time_without_gm+ strtotimeOffset($start_week_time_without_gm);
			$end_week_time = $start_week_time + 604799; //(7 * 24 * 60 * 60);

   
			$start_week = gmstrftime($this->conf['view.']['week.']['dateFormatWeek'], $start_week_time);
			$end_week = gmstrftime($this->conf['view.']['week.']['dateFormatWeek'], $end_week_time);
			$GLOBALS['TSFE']->register['cal_week_endtime'] = $end_week_time_without_gm;
			$GLOBALS['TSFE']->register['cal_week_starttime'] = $start_week_time_without_gm;

			$display_date = $this->cObj->cObjGetSingle($this->conf['view.']['week.']['titleWrap'],$this->conf['view.']['week.']['titleWrap.'],$TSkey='__');

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

			$view_array = array ();
			$rowspan_array = array();
			 
			if (count($this->master_array) > 0) {
				foreach (array_keys($this->master_array) as $ovlKey) {
					if ($ovlKey == 'legend') {
						continue;
					}
					 
					$dTimeStart = array();
					$dTimeEnd = array();
					$dDate = array();
					preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayStart'], $dTimeStart);
					preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayEnd'], $dTimeEnd);
					preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
					//      preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $thisday, $dDate);
					$d_start = gmmktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
					$d_end = gmmktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
                    //minus 1 second to allow endtime 24:00
                    $d_end--;

					foreach (array_keys($this->master_array[$ovlKey]) as $ovl_time_key) {					 
						foreach (array_keys($this->master_array[$ovlKey][$ovl_time_key]) as $ovl2Key) {
							 $ovl2Value = &$this->master_array[$ovlKey][$ovl_time_key][$ovl2Key];
							if ($ovl_time_key == '-1') {
								$starttime = $ovl2Value->getStartdate();
								$endtime = $ovl2Value->getEnddate()+1;
								for($j = $starttime; $j < $endtime; $j = $j + 86400){ //60 * 60 * 24) {
									$view_array[gmdate('Ymd', $j)]['-1'][] = $ovl2Value;
								}
								 
							} else if($ovl2Value->getStarttime()<$end_week_time){
								$starttime = $ovl2Value->getStarttime() - $ovl2Value->getStarttime() % ($gridLength * 60);
								$endtime = $ovl2Value->getEndtime();
								 
								$rest = ($endtime % ($gridLength * 60));
								$endtime = $endtime - $rest;
								if ($rest > 0) {
									$endtime = $endtime + ($gridLength * 60);
								}
								$entries = 1;
								$old_day = $ovlKey;
								$endOfDay = $d_end;
								$d_start = $d_start - $gridLength * 60;
								 
								// get x-array possition
								for($k = 0; $k < count($view_array[($ovlKey)]); $k++) {
									if (empty($view_array[gmdate('Ymd', $starttime)][$starttime][$k])) {
										break;
									}
								}
								for ($j = $starttime; $j < $endtime && $j < $end_week_time; $j += $gridLength * 60) {
									$view_array[gmdate('Ymd', $j)][$j][$k] = $ovl2Value;
									if ($j >= $endOfDay) {
										$rowspan_array[$old_day][$ovl2Value->getType().'_'.$ovl2Value->getUid()] = $entries-1;
										 
										$endOfDay += 86400;//60 * 60 * 24;
										$old_day = gmdate('Ymd', $endOfDay);
										$d_start += 86400;//60 * 60 * 24;
										$j = $d_start;
										for($k = 0; $k < count($view_array[gmdate('Ymd', $d_start)]); $k++) {
											if (empty($view_array[gmdate('Ymd', $d_start)][$d_start][$k])) {
												break;
											}
										}
										$entries = 0;
									}
									 
									$entries++;
								}

								$rowspan_array[$old_day][$ovl2Value->getType().'_'.$ovl2Value->getUid()] = $entries-1;
							}
						}
					}
				}
			}

			$startdate = gmdate('Ymd', $start_week_time);
			$enddate = gmdate('Ymd', $end_week_time);
			for($i = $startdate; $i <= $enddate; $i++) {
				if (!empty($view_array[$i])) {
					$max = array();
					foreach (array_keys($view_array[$i]) as $array_time) {
						$c = count($view_array[$i][$array_time]);
						array_push($max, $c);
					}
					$nbrGridCols[$i] = max($max);
				} else {
					$nbrGridCols[$i] = 1;
				}
			}
			$t_array = array ();
			$pos_array = array ();
			preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayStart'], $dTimeStart);
			preg_match('/([0-9]{2})([0-9]{2})/', $this->conf['view.']['day.']['dayEnd'], $dTimeEnd);
			//debug($view_array);
			foreach(array_keys($view_array) as $week_key) {
				$week_day = &$view_array[$week_key];
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);
				$d_start = gmmktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
				$d_end = gmmktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
				$d_start = $d_start - $d_start % ($gridLength * 60);
				$d_end = $d_end - $d_end % ($gridLength * 60);
				 
				for ($i = $d_start; $i < $d_end; $i = $i + ($gridLength * 60)) {
					if (is_array($view_array[$week_key][$i]) && count($view_array[$week_key][$i]) > 0) {
						foreach (array_keys($view_array[$week_key][$i]) as $eventKey) {
							$event = &$view_array[$week_key][$i][$eventKey];
							if (is_array($pos_array[$week_key]) && array_key_exists($event->getType().$event->getUid(), ($pos_array[$week_key]))) {
								$nd = $event->getEndtime() - ($event->getEndtime() % ($gridLength * 60));
								if ($i >= $nd) {
									$t_array[$week_key][$i][$pos_array[$week_key][$event->getType().$event->getUid()]] = array ('ended' => $event);
								} else {
									$t_array[$week_key][$i][$pos_array[$week_key][$event->getType().$event->getUid()]] = array ('started' => $event);
								}
							} else {
								for ($j = 0; $j < $nbrGridCols[$week_key]?$nbrGridCols[$week_key]:1; $j ++) {
									if (count($t_array[$week_key][$i][$j]) == 0 || !isset ($t_array[$week_key][$i][$j])) {
										$pos_array[$week_key][$event->getType().$event->getUid()] = $j;
										$t_array[$week_key][$i][$j] = array ('begin' => $event);
										break;
									}
								}
							}
						}
						//debug($t_array);
						 
					} else {
						$t_array[$week_key][$i] = '';
					}
				}
			}
			 
			$temp_time = $start_week_time;
			for ($i = 0; $i < 7; $i ++) {
				$thisdate = gmdate('Ymd', $temp_time);
				$weekarray[$i] = $thisdate;
				$temp_time = $temp_time + 86400;
			}
			 
			$page = $this->cObj->fileResource($this->conf['view.']['week.']['weekTemplate']);
			if ($page == '') {
				return "<h3>week: no template file found:</h3>".$this->conf['view.']['week.']['weekTemplate']."<br />Please check your template record and add both cal items at 'include static (from extension)'";
			}
			$page = $this->replace_files($page, array (
			'sidebar' => $this->conf['view.']['other.']['sidebarTemplate'])
			);
			 
			$languageArray = array (
			'getdate' => $getdate,
				'display_date' => $display_date,
				'legend_prev_day' => $legend_prev_day_link,
				'legend_next_day' => $legend_next_day_link,
				'next_day_link' => $next_day_link,
				'next_week_link' => $next_week_link,
				'prev_day_link' => $prev_day_link,
				'prev_week_link' => $prev_week_link,
				'sidebar_date' => '',
				'l_next' => $this->controller->pi_getLL('l_next'),
				'l_prev' => $this->controller->pi_getLL('l_prev'),
				'l_goprint' => $this->controller->pi_getLL('l_goprint'),
				'l_preferences' => $this->controller->pi_getLL('l_preferences'),
				'l_calendar' => $this->controller->pi_getLL('l_calendar'),
				'l_legend' => $this->controller->pi_getLL('l_legend'),
				'l_tomorrows' => $this->controller->pi_getLL('l_tomorrows'),
				'l_jump' => $this->controller->pi_getLL('l_jump'),
				'l_todo' => $this->controller->pi_getLL('l_todo'),
				'l_day' => $this->controller->pi_getLL('l_day'),
				'l_week' => $this->controller->pi_getLL('l_week'),
				'l_month' => $this->controller->pi_getLL('l_month'),
				'l_year' => $this->controller->pi_getLL('l_year'),
				'l_subscribe' => $this->controller->pi_getLL('l_subscribe'),
				'l_download' => $this->controller->pi_getLL('l_download'),
				'l_search' => $this->controller->pi_getLL('l_search'),
				'l_powered_by' => $this->controller->pi_getLL('l_powered_by'),
				'l_this_site_is' => $this->controller->pi_getLL('l_this_site_is'),
				);
				
			$page = $this->controller->replace_tags($languageArray, $page);
			// Replaces the allday events
			$loop_ad = $this->cObj->getSubpart($page, '###LOOPALLDAY###');
			$loop_begin = $this->cObj->getSubpart($page, '###ALLDAYSOFWEEK_BEGIN###');
			$loop_end = $this->cObj->getSubpart($page, '###ALLDAYSOFWEEK_END###');
			 
			foreach ($weekarray as $get_date) {
				$replace = $loop_begin;
				$colspan = 'colspan="'.($nbrGridCols[$get_date]?$nbrGridCols[$get_date]:1).'"';
				 
				$sims['###COLSPAN###'] = $this->cObj->substituteMarkerArrayCached($colspan, $replace, array (), array ());
				$replace = $this->cObj->substituteMarkerArrayCached($replace, $sims, array (), array ());
				if (is_array($view_array[$get_date]['-1'])) {
					foreach ($view_array[$get_date]['-1'] as $id => $allday) {
						$replace .= $allday->renderEventForAllDay();					 
					}
				}
				$replace .= $loop_end;
				$weekreplace .= $replace;
			}
			$rems = array ();
			$rems['###ALLDAYSOFWEEK###'] = $weekreplace;
			$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
			$rems = array ();
			// Replaces the daysofweek
			$loop_dof = $this->cObj->getSubpart($page, '###DAYSOFWEEK###');
			$dateOfWeek = tx_cal_calendar :: dateOfWeek($unix_time, $weekStartDay, $weekStartDay);

			$start_day = strtotime($dateOfWeek);
			$start_day += strtotimeOffset($start_day);
			$start_week_time = $start_day;
			
			$isAllowedToCreateEvents = $this->rightsObj->isAllowedToCreateEvents();
	
			for ($i = 0; $i < 7; $i ++) {
				$day_num = gmdate('w', $start_day);
	
				$daylink = gmdate('Ymd', $start_day);
	
				$weekday = gmstrftime($this->conf['view.']['week.']['dateFormatWeekList'], $start_day);
	
				if ($daylink == $getdate) {
					$row1 = 'rowToday';
					$row2 = 'rowOn';
					$row3 = 'rowToday';
				} else {
					$row1 = 'rowOff';
					$row2 = 'rowOn';
					$row3 = 'rowOff';
				}
				if(($this->rightsObj->isViewEnabled($this->conf['view.']['dayLinkTarget']) || $this->conf['view.'][$this->conf['view.']['dayLinkTarget'].'.'][$this->conf['view.']['dayLinkTarget'].'ViewPid']) && ($view_array[$daylink] || $isAllowedToCreateEvents)){
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
					if (!empty ($this->conf['view.']['day.']['dayViewPid'])) {
						$link = $this->controller->pi_linkTP_keepPIvars($this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']), array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
					} else {
						$link = $this->controller->pi_linkTP_keepPIvars($this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']), array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
					}
				}else{
					$link = $this->cObj->stdWrap($weekday, $this->conf['view.']['week.']['weekday_stdWrap.']);
				}
				$start_day += 86400; //strtotime('+1 day', $start_day);
				$start_wt += 86400; //strtotime('+1 day', $start_wt);
				$colspan = 'colspan="'.($nbrGridCols[$daylink]?$nbrGridCols[$daylink]:1).'"';
				$search = array ('###LINK###', '###DAYLINK###', '###ROW1###', '###ROW2###', '###ROW3###', '###COLSPAN###', '###HANDEL###');
				$replace = array ($link, $daylink, $row1, $row2, $row3, $colspan, 'onclick="alert(\'test\')"');
				$loop_tmp = str_replace($search, $replace, $loop_dof);
				$weekday_loop .= $loop_tmp;
			}
			 
			$rems['###DAYSOFWEEK###'] = $weekday_loop;
			$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
			 
			// Build the body
			$loop_hours = $this->cObj->getSubpart($page, '###LOOPROW###');
			$loop_event = $this->cObj->getSubpart($page, '###LOOPEVENT###');
			 
			$border = 0;
			$thisdate = $start_week_time;
			 
			$d_start = gmmktime($dTimeStart[1], $dTimeStart[2], 0, 0, 0, 1);
			$d_end = gmmktime($dTimeEnd[1], $dTimeEnd[2], 0, 0, 0, 1);
			
			$d_start = $d_start - $d_start % ($gridLength * 60);
			$d_end = $d_end - $d_end % ($gridLength * 60);
			
			$loops = ($d_end - $d_start)/($gridLength * 60);
			
			$d_start_time = $d_start - gmmktime(0, 0, 0, 0, 0, 1);
			$weekdisplay = '';
			$sims = array('###GRIDLENGTH###' => $gridLength, '###60TH_GRIDLENGTH###' => (60 / $gridLength));
			 
			
			$createOffset = $this->conf['rights.']['create.']['event.']['timeOffset'] * 60 + strtotimeOffset($unix_time);
			if(!$this->conf['view.']['event.']['createEventViewPid']){
				$this->conf['view.']['event.']['createEventViewPid'] = $GLOBALS['TSFE']->id;
			}
			for($i = 0; $i < $loops; $i++) {
				for($j = 0; $j < 7; $j++) {
					$daytime = $start_week_time+$j * 86400 +$d_start_time;
					$day = gmdate('Ymd', $daytime);
					$time = $daytime+$i * $gridLength * 60;
					if ($j == 0) {
						$key = gmstrftime($this->conf['view.']['week.']['timeFormatWeek'], $time);
						if (ereg('([0-9]{1,2}):00', $key)) {
							$sims['###TIME###'] = $key;
							$weekdisplay .= $this->cObj->substituteMarkerArrayCached($this->conf['view.']['week.']['weekDisplayFullHour'], $sims, array(), array ());
						} else {
							$weekdisplay .= $this->cObj->substituteMarkerArrayCached($this->conf['view.']['week.']['weekDisplayInbetween'], $sims, array(), array ());
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
									$event = $something[$k][$keys[0]];
									
									$rest = $event->getEndtime() % ($gridLength * 60);
									$plus = 0;
									if ($rest > 0) {
										$plus = 1;
									}

									$sims['###ROWSPAN###'] = $rowspan_array[$day][$event->getType().'_'.$event->getUid()];								 
									$weekdisplay .= $this->cObj->substituteMarkerArrayCached($this->conf['view.']['week.']['weekEventPre'].$event->renderEventForWeek(), $sims, array (), array ());
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
									$link = '';
									if ($time > (time()+$createOffset) && $isAllowedToCreateEvents) {
										$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
										$link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['addIcon'], array ('getdate' => $day, 'gettime' => gmdate('Hi', $time), 'lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), 0, $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
									}
									$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['week.']['normalCell_stdWrap.']);
									$tmp = str_replace('###COLSPAN###', $remember, $tmp);
									$tmp = str_replace('###CLASS###', $class, $tmp);
									if($isAllowedToCreateEvents){
										$tmp = str_replace('###HANDEL###', 'onclick="createCalEvent(this, '.$this->conf['view.']['event.']['createEventViewPid'].','.$day.',\''.gmdate('Hi', $time).'\',\''.gmdate('Hi', $time+3600).'\')"', $tmp);
									}
									$weekdisplay .= $tmp;
									$remember = 0;
								}
							}
							if ($remember > 0) {
								$link = '';
								if ($time > (time()+$createOffset) && $isAllowedToCreateEvents) {
									$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
									$link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['addIcon'], array ('getdate' => $day, 'gettime' => gmdate('Hi', $time), 'lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), 0, $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
								}
								 
								$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['week.']['normalCell_stdWrap.']);
								$tmp = str_replace('###COLSPAN###', $remember, $tmp);
								$tmp = str_replace('###CLASS###', $class, $tmp);
								if($isAllowedToCreateEvents){
									$tmp = str_replace('###HANDEL###', 'onclick="createCalEvent(this, '.$this->conf['view.']['event.']['createEventViewPid'].','.$day.',\''.gmdate('Hi', $time).'\',\''.gmdate('Hi', $time+3600).'\')"', $tmp);
								}
								$weekdisplay .= $tmp;
								$remember = 0;
							}
						}
						 
					} else {
						$link = '';
						if ($time > (time()+$createOffset) && $isAllowedToCreateEvents) {
							$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
							$link = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['addIcon'], array ('getdate' => $day, 'gettime' => gmdate('Hi', $time), 'lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), 0, $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
						}
						 
						$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['week.']['normalCell_stdWrap.']);
						$tmp = str_replace('###COLSPAN###', $nbrGridCols[$day]?$nbrGridCols[$day]:1, $tmp);
						$tmp = str_replace('###CLASS###', $class, $tmp);
						if($isAllowedToCreateEvents){
							$tmp = str_replace('###HANDEL###', 'onclick="createCalEvent(this, '.$this->conf['view.']['event.']['createEventViewPid'].','.$day.',\''.gmdate('Hi', $time).'\',\''.gmdate('Hi', $time+3600).'\')"', $tmp);
						}
						$weekdisplay .= $tmp;
					}
					if ($j == 6) {
						$weekdisplay .= $this->conf['view.']['week.']['weekFinishRow'];
					}
				}
			}
			$rems = array ();
			$rems['###LOOPEVENTS###'] = $weekdisplay;
			return $this->finish($page, $rems);
		}
	}
	 
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']) {
		include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']);
	}
?>
