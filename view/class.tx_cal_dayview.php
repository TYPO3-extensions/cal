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
class tx_cal_dayview extends tx_cal_base_view {

	/**
	 *  Draws the day view
	 *  @param		$master_array	array		The events to be drawn.
	 *  @param		$getdate		integer		The date of the event
	 *	@return		string		The HTML output.
	 */
	function drawDay(&$master_array, $getdate) {
			
		$this->_init($master_array);

		$weekStartDay = $this->conf["view."]["weekStartDay"]; //'Monday';			// Day of the week your week starts on
		$dayStart = $this->conf["view."]["day."]["dayStart"]; //'0700';			// Start time for day grid
		$dayEnd = $this->conf["view."]["day."]["dayEnd"]; //'2300';			// End time for day grid
		$gridLength = $this->conf["view."]["day."]["gridLength"]; //'15';				// Grid distance in minutes for day view, multiples of 15 preferred

		if (!isset ($getdate) || $getdate == '') {
			$getdate = date('Ymd');
		}

		$unix_time = strtotime($getdate);
		$day_array2 = array();
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		$today_today = date('Ymd', time());
		$next_day = date('Ymd', strtotime("+1 day", $unix_time));
		$prev_day = date('Ymd', strtotime("-1 day", $unix_time));
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
		if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
			$next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["nextDaySymbol"], array ("getdate" => $next_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
			$prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["previousDaySymbol"], array ("getdate" => $prev_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
			$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["legendPrevDayLink"], array ("getdate" => $prev_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
			$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["legendNextDayLink"], array ("getdate" => $next_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["nextDaySymbol"], array ("getdate" => $next_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway']);
			$prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["previousDaySymbol"], array ("getdate" => $prev_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway']);
			$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["legendPrevDayLink"], array ("getdate" => $prev_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway']);
			$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars($this->conf["view."]["day."]["legendNextDayLink"], array ("getdate" => $next_day, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway']);
		}

		// find out next month
		$next_month_month = ($this_month +1 == '13') ? '1' : ($this_month +1);
		$next_month_day = $this_day;
		$next_month_year = ($next_month_month == '1') ? ($this_year +1) : $this_year;
		while (!checkdate($next_month_month, $next_month_day, $next_month_year))
			$next_month_day --;
		$next_month_time = mktime(0, 0, 0, $next_month_month, $next_month_day, $next_month_year);


		// find out last month
		$prev_month_month = ($this_month -1 == '0') ? '12' : ($this_month -1);
		$prev_month_day = $this_day;
		$prev_month_year = ($prev_month_month == '12') ? ($this_year -1) : $this_year;
		while (!checkdate($prev_month_month, $prev_month_day, $prev_month_year))
			$prev_month_day --;

		$prev_month_time = mktime(0, 0, 0, $prev_month_month, $prev_month_day, $prev_month_year);

		$next_month = date("Ymd", $next_month_time);
		$prev_month = date("Ymd", $prev_month_time);
		$display_date = strftime($this->conf["view."]["day."]['dateFormatWeekList'], $unix_time);

		$parse_month = date("Ym", $unix_time);
		$first_of_month = $this_year.$this_month."01";

		$start_month_day = tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay);

		$num_of_events2 = 0;

		$page = $this->cObj->fileResource($this->conf["view."]["day."]["dayTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->conf["view."]["day."]["dayTemplate"]."<br />Please check your template record and add both cal items at 'include static (from extension)'";
		}

		$page = $this->replace_files($page, array (
			'sidebar' => $this->conf["view."]["other."]["sidebarTemplate"]) 
		);

		$languageArray = array (
			'getdate' => $getdate, 
			'display_date' => $display_date, 
			'legend_prev_day' => $legend_prev_day_link, 
			'legend_next_day' => $legend_next_day_link, 
			'next_day_link' => $next_day_link, 
			'prev_day_link' => $prev_day_link,
			'sidebar_date' => '',
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
			'l_powered_by' => $this->controller->pi_getLL('l_powered_by'), 
			'l_subscribe' => $this->controller->pi_getLL('l_subscribe'), 
			'l_download' => $this->controller->pi_getLL('l_download'), 
			'l_this_site_is' => $this->controller->pi_getLL('l_this_site_is'), 
		);
		
		$page = $this->controller->replace_tags($languageArray, $page);
		// Replaces the daysofweek
		$loop_dof = $this->cObj->getSubpart($page, "###DAYSOFWEEK###");
		$start_day = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));

		for ($i = 0; $i < 7; $i ++) {
			$day_num = date("w", $start_day);

			$daylink = date('Ymd', $start_day);

			$weekday = strftime($this->conf["view."]["day."]['dateFormatDay'], strtotime($daylink));

			if ($daylink == $getdate) {
				$row1 = 'rowToday';
				$row2 = 'rowOn';
				$row3 = 'rowToday';
			} else {
				$row1 = 'rowOff';
				$row2 = 'rowOn';
				$row3 = 'rowOff';
			}
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
			if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
				$link = $this->controller->pi_linkTP_keepPIvars($weekday, array ("getdate" => $daylink, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars($weekday, array ("getdate" => $daylink, "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$start_day = strtotime("+1 day", $start_day);
			$start_wt = strtotime("+1 day", $start_wt);
			$search = array ('###LINK###', '###DAYLINK###', '###ROW1###', '###ROW2###', '###ROW3###');
			$replace = array ($link, $daylink, $row1, $row2, $row3);
			$loop_tmp = str_replace($search, $replace, $loop_dof);
			$weekday_loop .= $loop_tmp;
		}
		$rems["###DAYSOFWEEK###"] = $weekday_loop;
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		// Build the body
		$dayborder = 0;

		$out = '';
		$fillTime = $dayStart;
		$day_array = array ();

		while ($fillTime < $dayEnd) {
			array_push($day_array, $fillTime);
			$dTime = array();
			preg_match('/([0-9]{2})([0-9]{2})/', $fillTime, $dTime);
			$fill_h = $dTime[1];
			$fill_min = $dTime[2];
			$fill_min = sprintf('%02d', $fill_min + $gridLength);
			if ($fill_min == 60) {
				$fill_h = sprintf('%02d', ($fill_h +1));
				$fill_min = '00';
			}
			$fillTime = $fill_h.$fill_min;
		}
		$nbrGridCols = array();
		$gridLength = $this->conf["view."]["day."]["gridLength"];
		
		$dayborder = 0;
		$thisdate = $start_week_time = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));;
		//$swt = $start_week_time;
//debug($this->master_array);		
		$view_array = array ();
		$rowspan_array = array();

		if (count($this->master_array)>1) {
			foreach ($this->master_array as $ovlKey => $ovlValue) {
				if($ovlKey=='legend'){
					continue;
				}
				$dTimeStart = array();
				$dTimeEnd = array();
				$dDate = array();
				preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayStart"], $dTimeStart);
				preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayEnd"], $dTimeEnd);
//						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $thisday, $dDate);
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
				$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
				$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
					
				foreach ($ovlValue as $ovl_time_key => $ovl_time_Value) {
					foreach ($ovl_time_Value as $ovl2Value) {
						if ($ovl_time_key == "-1") {
							$starttime = $ovl2Value->getStartdate();
							$endtime = $ovl2Value->getEnddate()+1;
							for($j = $starttime; $j < $endtime; $j = $j + 60 * 60 * 24){
								$view_array[date("Ymd",$j)]["-1"][] = $ovl2Value;
							}

						}else{
							$starttime = $ovl2Value->getStarttime() - $ovl2Value->getStarttime() % ($gridLength * 60);
							$endtime = $ovl2Value->getEndtime();
							$rest = ($endtime % ($gridLength * 60));
							$endtime = $endtime - $rest;
							if($rest > 0){
								$endtime = $endtime + ($gridLength * 60);
							}
							$entries = 1;
							$old_day = $ovlKey;
							$endOfDay = $d_end;
							$d_start = $d_start - $gridLength * 60;
							for($k = 0; $k < count($view_array[($ovlKey)]); $k++){
								if(empty($view_array[date("Ymd",$starttime)][$starttime][$k])){
									break;
								}
							}
							for ($j = $starttime; $j < $endtime; $j = $j + $gridLength * 60) {
								$view_array[date("Ymd",$j)][$j][$k] = $ovl2Value;			
								if($j >= $endOfDay){
									$rowspan_array[$old_day][$ovl2Value->getType()."_".$ovl2Value->getUid()] = $entries-1;
									
									$endOfDay = $endOfDay + 60 * 60 * 24;
									$old_day = date("Ymd",$endOfDay);
									$d_start = $d_start + 60 * 60 * 24;
									$j = $d_start;
									$entries = 0;
									for($k = 0; $k < count($view_array[date("Ymd",$d_start)]); $k++){
										if(empty($view_array[date("Ymd",$d_start)][$d_start][$k])){
											break;
										}
									}
								}
								$entries++;
			
								
							}
							$rowspan_array[$old_day][$ovl2Value->getType()."_".$ovl2Value->getUid()] = $entries-1;
						}
					}
				}
			}
		}
//debug($view_array);

		if(!empty($view_array[$getdate])){
			$max=array();
			foreach ($view_array[$getdate] as $array_time => $time_val) {
				$c = count($view_array[$getdate][$array_time]);
				array_push($max, $c);
			}
			$nbrGridCols[$getdate] = max($max);
		}else{
			$nbrGridCols[$getdate] = 1;
		}

		// Replaces the allday events
		$replace = '';
		if (is_array($view_array[$getdate]['-1'])) {
			$loop_ad = $this->cObj->getSubpart($page, "###LOOPALLDAY###");
			foreach ($view_array[$getdate]['-1'] as $uid => $allday) {
				$sims["###ALLDAY###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($allday, $allday->renderEventForAllDay(),"day", $this->conf['getdate']), $loop_ad, array (), array ());
				$sims["###STYLE###"] = $this->cObj->substituteMarkerArrayCached($allday->getHeaderStyle(), $loop_ad, array (), array ());
				$replace .= $this->cObj->substituteMarkerArrayCached($loop_ad, $sims, array (), array ());
			}
		}
		$rems["###ALLDAY###"] = $replace;
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		
		$view_array = $view_array[$getdate];
		$nbrGridCols = $nbrGridCols[$getdate]?$nbrGridCols[$getdate]:1;
		$t_array = array ();
		$pos_array = array ();
		preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $dDate);
		preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayStart"], $dTimeStart);
		preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayEnd"], $dTimeEnd);
		$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
		$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
		$d_start = $d_start - $d_start % $gridLength;
		$d_end = $d_end - $d_end % $gridLength;

		for ($i = $d_start; $i < $d_end; $i = $i + $gridLength *60) {
			if (is_array($view_array[$i]) && count($view_array[$i]) > 0) {
				foreach ($view_array[$i] as $event) {
					if (array_key_exists($event->getType().$event->getUid(), $pos_array)) {
						$nd = $event->getEndtime() - ($event->getEndtime() % ($gridLength * 60));
						if ($i >= $nd) {
							$t_array[$i][$pos_array[$event->getType().$event->getUid()]] = array ("ended" => $event);
						} else {
							$t_array[$i][$pos_array[$event->getType().$event->getUid()]] = array ("started" => $event);
						}
					} else {
						for ($j = 0; $j < $nbrGridCols; $j ++) {
							if (count($t_array[$i][$j]) == 0 || !isset ($t_array[$i][$j])) {
								$pos_array[$event->getType().$event->getUid()] = $j;
								$t_array[$i][$j] = array ("begin" => $event);
								break;
							}
						}
					}
				}
			} else {
				$t_array[$i] = "";
			}
		}
		$loop_hours = $this->cObj->getSubpart($page, "###LOOPROW###");
		$loop_event = $this->cObj->getSubpart($page, "###LOOPEVENT###");
		
		$event_length = array ();
		$border = 0;
		preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		
		$isAllowedToCreateEvents = $this->rightsObj->isAllowedToCreateEvents();
		$createOffset = $this->conf['rights.']['create.']['event.']['timeOffset'] * 60;

		foreach ($t_array as $key => $val) {
			$time = $key;
			$cal_time = date("Hi", $key);
			$key = strftime($this->conf['view.']['day.']['timeFormatDay'], $key);

			if (preg_match('/([0-9]{1,2}):00/', $key)) {
				$dayTimeCell = $this->cObj->stdWrap($key, $this->conf['view.']['day.']['dayTimeCell_stdWrap.']);
				$dayTimeCell = str_replace('###ROWSPAN###',(60 / $gridLength),$dayTimeCell);
				$dayTimeCell = str_replace('###GRIDLENGTH###',$gridLength,$dayTimeCell);
				$daydisplay .= $dayTimeCell;
			}
			elseif ($cal_time == $dayStart) {
				$size_tmp = 60 - (int) substr($cal_time, 2, 2);
				$dayTimeCell = $this->cObj->stdWrap($key, $this->conf['view.']['day.']['dayTimeCell_stdWrap.']);
				$dayTimeCell = str_replace('###ROWSPAN###',($size_tmp / $gridLength),$dayTimeCell);
				$dayTimeCell = str_replace('###GRIDLENGTH###',$gridLength,$dayTimeCell);
				$daydisplay .= $dayTimeCell;
			} else {
				$daydisplay .= $this->cObj->stdWrap($gridLength, $this->conf['view.']['day.']['dayTimeCell2_stdWrap.']);
			}
			if ($dayborder == 0) {
				$class = ' '.$this->conf['view.']['day.']['classDayborder'];
				$dayborder ++;
			} else {
				$class = ' '.$this->conf['view.']['day.']['classDayborder2'];
				$dayborder = 0;
			}

			if ($val != "" && count($val) > 0) {
				for ($i = 0; $i < count($val); $i ++) {
					if(!empty($val[$i])){
						$keys = array_keys($val[$i]);
						switch ($keys[0]) {
							case 'begin' :
								$event = $val[$i][$keys[0]];
								$event_start = strftime($this->conf['view.']['day.']['timeFormatDay'], strtotime($event->getStartHour()));
								$event_end = strftime($this->conf['view.']['day.']['timeFormatDay'], strtotime($event->getEndHour() ? $event->getEndHour():$event->getStartHour()));
								$event_calno = $event->getCalNumber();
								$event_recur = $event->getCalRecu();
								$event_status = strtolower($event->getStatus());
								$confirmed = "";
								if ($event_status != '') {
									$confirmed = $this->cObj->stdWrap($event_status, $this->conf['view.']['day.']['statusIcon_stdWrap.']);
								}else if (is_array($event_recur) && count($event_recur)>0) {
									$confirmed = $this->conf['view.']['day.']['recurringIcon'];
								}

								$sims["###EDITLINK###"] = "";
								if ($event->isUserAllowedToEdit()) {
									$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_event').'"';
									$sims["###EDITLINK###"] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['day.']['editIcon'], array ("lastview" => "day", "view" => "edit_event", "type" => $event->getType(), "uid" => $event->getUid()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["event."]["editEventViewPid"]);
								}
								if($event->isUserAllowedToDelete()){
									$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_delete_event').'" alt="'.$this->controller->pi_getLL('l_delete_event').'"';
									$sims["###EDITLINK###"] .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['day.']['deleteIcon'], array("lastview" => "day", "view" => 'delete_event', "type" => $event->getType(), "uid" => $event->getUid()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["event."]["deleteEventViewPid"]);
								}
								$rest = $event->getEndtime() % ($gridLength * 60);
								$plus = 0;
								if($rest>0){
									$plus = 1;
								}
								$dayEndTime = $event->getEndtime();
								if($dayEndTime > $d_end){
									$dayEndTime = $d_end;
								}
								$dayStartTime = $event->getStarttime();
								if($dayStartTime < $d_start){
									$dayStartTime = $d_start;
								}
								$sims["###ROWSPAN###"] = ($dayEndTime -  $dayStartTime) / ($gridLength * 60) + $plus;

								$event_temp = $this->conf['view.']['day.']['dayEventPre'];
							
								// Start drawing the event
								$event_temp .= $loop_event;
								$switch = array();
								$rems = array();
								$event->getEventMarker($event_temp,$rems,$switch);
								$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event_view').'"';
								$sims["###EVENT###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($event, $event->renderEventForDay(),"day", $this->conf['getdate']), $event_temp, array (), array ());
								$e_start = strftime($this->conf['view.']['day.']['timeFormatDay'], $event->getStarttime());
								$sims["###EVENT_START###"] = $confirmed.$this->cObj->substituteMarkerArrayCached($e_start, $event_temp, array (), array ());
								$e_end = strftime($this->conf['view.']['day.']['timeFormatDay'], $event->getEndtime());
								$sims["###EVENT_END###"] = $this->cObj->substituteMarkerArrayCached($e_end, $event_temp, array (), array ());
								$sims["###CONFIRMED###"] = $this->cObj->substituteMarkerArrayCached($event->getConfirmed(), $event_temp, array (), array ());
									
								if($this->conf['view.']['freeAndBusy.']['enable'] && !$event->isEventOwner($this->rightsObj->getUserId(),$this->rightsObj->getUserGroups()) && $this->conf['option']=='freeandbusy' && $this->conf['calendar']){
									$sims["###EVENT###"] = $this->conf['view.']['freeAndBusy.']['eventTitle'];
									$sims["###STYLE###"] = $this->conf['view.']['freeAndBusy.']['headerStyle'];
									$sims["###BODYSTYLE###"] = $this->conf['view.']['freeAndBusy.']['bodyStyle'];
								}else{
									$sims["###EVENT###"] = $this->getLinkToEvent($event, $event->renderEventForDay(),"day", $getdate);
									$sims["###STYLE###"] = $event->getHeaderStyle();
									$sims["###BODYSTYLE###"] = $event->getBodyStyle();
								}
								$daydisplay .= $this->cObj->substituteMarkerArrayCached($event_temp, $sims, array (), array ());
								$daydisplay .= $this->conf['view.']['day.']['dayEventPost'];
								// End event drawing
								break;
						}
					}
				}
				if (count($val) < $nbrGridCols) {
					$remember = 0;
					for($l = 0; $l < $nbrGridCols; $l++){
						if(!$val[$l]){
							$remember++;
						}else if($remember>0){
							
							$link = "";
							if ($isAllowedToCreateEvents && $time>(time()+$createOffset)) {
								$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
								$link .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['day.']['addIcon'], array ("gettime" => $cal_time, "lastview" => "day", "view" => "create_event"), $this->conf['cache'], $this->conf['clear_anyway'],$this->conf["view."]["event."]["create_eventViewPid"]);
							}
							$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['day.']['normalCell_stdWrap.']);
							$tmp = str_replace('###COLSPAN###',$remember,$tmp);
							$tmp = str_replace('###CLASS###',$class,$tmp);
							$daydisplay .= $tmp;
							$remember = 0;
						}
					}
					if($remember>0){
						$link = "";
						if ($isAllowedToCreateEvents && $time>(time()+$createOffset)) {
							$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
							$link .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['day.']['addIcon'], array ("gettime" => $cal_time, "lastview" => "day", "view" => "create_event"), $this->conf['cache'], $this->conf['clear_anyway'],$this->conf["view."]["event."]["create_eventViewPid"]);
						}
						$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['day.']['normalCell_stdWrap.']);
						$tmp = str_replace('###COLSPAN###',$remember,$tmp);
						$tmp = str_replace('###CLASS###',$class,$tmp);
						$daydisplay .= $tmp;
						$remember = 0;
					}
				}

			} else {
				$link = "";
				if ($isAllowedToCreateEvents && $time>(time()+$createOffset)) {
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
					$link .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['day.']['addIcon'], array ("gettime" => $cal_time, "lastview" => "day", "view" => "create_event"), $this->conf['cache'], $this->conf['clear_anyway'],$this->conf["view."]["event."]["create_eventViewPid"]);
				}
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['day.']['normalCell_stdWrap.']);
				$tmp = str_replace('###COLSPAN###',$nbrGridCols,$tmp);
				$tmp = str_replace('###CLASS###',$class,$tmp);
				$daydisplay .= $tmp;
			}
			$daydisplay .= $this->conf['view.']['day.']['dayFinishRow'];
		}
		
		$rems = array ();
		$rems["###DAYEVENTS###"] = $daydisplay;
		$page = $this->checkForMonthMarker($page);
		return $this->finish($page, $rems);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_dayview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_dayview.php']);
}
?>