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

		$weekStartDay = $this->cObj->conf["view."]["weekStartDay"]; //'Monday';			// Day of the week your week starts on
		$dayStart = $this->cObj->conf["view."]["day."]["dayStart"]; //'0700';			// Start time for day grid
		$dayEnd = $this->cObj->conf["view."]["day."]["dayEnd"]; //'2300';			// End time for day grid
		$gridLength = $this->cObj->conf["view."]["day."]["gridLength"]; //'15';				// Grid distance in minutes for day view, multiples of 15 preferred

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
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_day_view').'"';
		if (!empty ($this->cObj->conf["view."]["day."]["dayViewPid"])) {
			$next_day_link = $this->controller->pi_linkTP_keepPIvars('&raquo;', array ("getdate" => $next_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			$prev_day_link = $this->controller->pi_linkTP_keepPIvars('&laquo;', array ("getdate" => $prev_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/left_arrows.gif" width="16" height="20" border="0" align="left" />', array ("getdate" => $prev_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/right_arrows.gif" width="16" height="20" border="0" align="right" />', array ("getdate" => $next_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
		} else {
			$next_day_link = $this->controller->pi_linkTP_keepPIvars('&raquo;', array ("getdate" => $next_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$prev_day_link = $this->controller->pi_linkTP_keepPIvars('&laquo;', array ("getdate" => $prev_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/left_arrows.gif" width="16" height="20" border="0" align="left" />', array ("getdate" => $prev_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/right_arrows.gif" width="16" height="20" border="0" align="right" />', array ("getdate" => $next_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
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
		$display_date = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_day'), $unix_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());

		$parse_month = date("Ym", $unix_time);
		$first_of_month = $this_year.$this_month."01";

		$start_month_day = tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay);

		$thisday2 = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week_list'), $unix_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
		$num_of_events2 = 0;

		$page = $this->cObj->fileResource($this->cObj->conf["view."]["day."]["dayTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->cObj->conf["view."]["day."]["dayTemplate"]."<br />Please check your template record and add both cal items at 'include static (from extension)'";
		}

		$page = $this->replace_files($page, array (
			'sidebar' => $this->cObj->conf["view."]["other."]["sidebarTemplate"]) 
		);

		$languageArray = array (
			'getdate' => $getdate, 
			'display_date' => $display_date, 
			'legend_prev_day' => $legend_prev_day_link, 
			'legend_next_day' => $legend_next_day_link, 
			'next_day_link' => $next_day_link, 
			'prev_day_link' => $prev_day_link,
			'sidebar_date' => '',
			'l_goprint' => $this->shared->lang('l_goprint'), 
			'l_preferences' => $this->shared->lang('l_preferences'), 
			'l_calendar' => $this->shared->lang('l_calendar'), 
			'l_legend' => $this->shared->lang('l_legend'), 
			'l_tomorrows' => $this->shared->lang('l_tomorrows'), 
			'l_jump' => $this->shared->lang('l_jump'), 
			'l_todo' => $this->shared->lang('l_todo'), 
			'l_day' => $this->shared->lang('l_day'), 
			'l_week' => $this->shared->lang('l_week'), 
			'l_month' => $this->shared->lang('l_month'), 
			'l_year' => $this->shared->lang('l_year'), 
			'l_powered_by' => $this->shared->lang('l_powered_by'), 
			'l_subscribe' => $this->shared->lang('l_subscribe'), 
			'l_download' => $this->shared->lang('l_download'), 
			'l_this_site_is' => $this->shared->lang('l_this_site_is'), 
		);
		
		$page = $this->shared->replace_tags($languageArray, $page);

		// Replaces the allday events
		$replace = '';
		if (is_array($this->master_array[$getdate]['-1'])) {
			$loop_ad = $this->cObj->getSubpart($page, "###LOOPALLDAY###");
			foreach ($this->master_array[$getdate]['-1'] as $uid => $allday) {
				$sims["###ALLDAY###"] = '<div class="'.$allday->getHeaderStyle().'_allday">'.$this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($allday, $allday->renderEventForAllDay(),"day", date("Ymd",$allday->getStarttime())), $loop_ad, array (), array ())."</div>";
				$sims["###STYLE###"] = $this->cObj->substituteMarkerArrayCached($allday->getHeaderStyle(), $loop_ad, array (), array ());
				$replace .= $this->cObj->substituteMarkerArrayCached($loop_ad, $sims, array (), array ());
			}
		}
		$rems["###ALLDAY###"] = $replace;
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		// Replaces the daysofweek
		$loop_dof = $this->cObj->getSubpart($page, "###DAYSOFWEEK###");

		$start_day = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));

		for ($i = 0; $i < 7; $i ++) {
			$day_num = date("w", $start_day);

			$daylink = date('Ymd', $start_day);

			if ($this->cObj->conf["view"] == 'day') {
				$a = array ();
				$a = ($this->shared->getDaysOfWeek());
				$weekday = $a[$day_num];
			} else {
				$weekday = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week_list'), strtotime($daylink), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			}
			if ($daylink == $getdate) {
				$row1 = 'rowToday';
				$row2 = 'rowOn';
				$row3 = 'rowToday';
			} else {
				$row1 = 'rowOff';
				$row2 = 'rowOn';
				$row3 = 'rowOff';
			}
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_day_view').'"';
			if (!empty ($this->cObj->conf["view."]["day."]["dayViewPid"])) {
				$link = $this->controller->pi_linkTP_keepPIvars($weekday, array ("getdate" => $daylink, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars($weekday, array ("getdate" => $daylink, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
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
		$gridLength = $this->cObj->conf["view."]["day."]["gridLength"];
		
		$dayborder = 0;
		$thisdate = $start_week_time = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));;
		//$swt = $start_week_time;
//debug($this->master_array);		
		$view_array = array ();
		$rowspan_array = array();
//		for ($i = 0; $i < 7; $i ++) {
//			$thisday = date("Ymd", $thisdate);
//			$nbrGridCols[$thisday] = 1;
			if (count($this->master_array)>1) {
//				foreach ($this->master_array[($thisday)] as $ovlKey => $ovlValue) {
				foreach ($this->master_array as $ovlKey => $ovlValue) {
					if($ovlKey=='legend'){
						continue;
					}
					if ($ovlKey != "-1") {
						$dTimeStart = array();
						$dTimeEnd = array();
						$dDate = array();
						preg_match('/([0-9]{2})([0-9]{2})/', $this->cObj->conf["view."]["day."]["dayStart"], $dTimeStart);
						preg_match('/([0-9]{2})([0-9]{2})/', $this->cObj->conf["view."]["day."]["dayEnd"], $dTimeEnd);
//						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $thisday, $dDate);
						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
						$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
						$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
//debug($ovlValue);						
						foreach ($ovlValue as $ovl_time_Value) {
							foreach ($ovl_time_Value as $ovl2Value) {
							
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
								for($k = 0; $k < count($this->master_array[($ovlKey)]); $k++){
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
										for($k = 0; $k < count($this->master_array[date("Ymd",$d_start)]); $k++){
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
				$max = array ();
				if(!empty($view_array[$ovlKey])){
					foreach ($view_array[$ovlKey] as $array_time => $time_val) {
						$c = count($view_array[$ovlKey][$array_time]);
						array_push($max, $c);
					}
					$nbrGridCols[($ovlKey)] = max($max);
				}else{
					$nbrGridCols[($ovlKey)] = 1;
				}
			}else{
				$thisday = date("Ymd", $thisdate);
				if(!empty($view_array[$thisday])){
					$max = array ();
					foreach ($view_array[$thisday] as $array_time => $time_val) {
						$c = count($view_array[$thisday][$array_time]);
						array_push($max, $c);
					}
					$nbrGridCols[($thisday)] = max($max);
				}else{
					$nbrGridCols[($thisday)] = 1;
					$view_array[$thisday] = "";
				}
			}
//			$thisdate = ($thisdate + (25 * 60 * 60));
//		}
//debug($view_array);
		$view_array = $view_array[$getdate];
		$nbrGridCols = $nbrGridCols[$getdate]?$nbrGridCols[$getdate]:1;
		$t_array = array ();
		$pos_array = array ();
		preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $dDate);
		preg_match('/([0-9]{2})([0-9]{2})/', $this->cObj->conf["view."]["day."]["dayStart"], $dTimeStart);
		preg_match('/([0-9]{2})([0-9]{2})/', $this->cObj->conf["view."]["day."]["dayEnd"], $dTimeEnd);
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

		foreach ($t_array as $key => $val) {

			$cal_time = date("Hi", $key);
			$key = date($this->shared->lang('l_timeFormat'), $key);

			if (preg_match('/([0-9]{1,2}):00/', $key)) {
				$daydisplay .= '<tr>'."\n";
				$daydisplay .= '<td rowspan="'. (60 / $gridLength).'" align="center" valign="top" width="60" class="timeborder">'.$key.'</td>'."\n";
				$daydisplay .= '<td bgcolor="#a1a5a9" width="1" height="'.$gridLength.'"></td>'."\n";
			}
			elseif ($cal_time == $dayStart) {
				$size_tmp = 60 - (int) substr($cal_time, 2, 2);
				$daydisplay .= '<tr>'."\n";
				$daydisplay .= "<td rowspan=\"". ($size_tmp / $gridLength)."\" align=\"center\" valign=\"top\" width=\"60\" class=\"timeborder\">$key</td>\n";
				$daydisplay .= '<td bgcolor="#a1a5a9" width="1" height="'.$gridLength.'"></td>'."\n";
			} else {
				$daydisplay .= '<tr>'."\n";
				$daydisplay .= '<td bgcolor="#a1a5a9" width="1" height="'.$gridLength.'"></td>'."\n";
			}
			if ($dayborder == 0) {
				$class = ' class="dayborder"';
				$dayborder ++;
			} else {
				$class = ' class="dayborder2"';
				$dayborder = 0;
			}

			if ($val != "" && count($val) > 0) {
				for ($i = 0; $i < count($val); $i ++) {
					if(!empty($val[$i])){
						$keys = array_keys($val[$i]);
						switch ($keys[0]) {
							case 'begin' :
								$event = $val[$i][$keys[0]];
								$event_start = date($this->shared->lang('l_timeFormat'), strtotime($event->getStartHour()));
								$event_end = date($this->shared->lang('l_timeFormat'), strtotime($event->getEndHour() ? $event->getEndHour():$event->getStartHour()));
								$event_calno = $event->getCalNumber();
								$event_recur = $event->getCalRecu();
								$event_status = strtolower($event->getStatus());
								$confirmed = "";
								if ($event_status != '') {
									$confirmed = '<img src="'.t3lib_extMgm :: siterelpath('cal').'template/img/'.$event_status.'.gif" width="9" height="9" alt="" border="0" hspace="0" vspace="0" />&nbsp;';
								}
								elseif (is_array($event_recur) && count($event_recur)>0) {
									$confirmed = '<img src="'.t3lib_extMgm :: siterelpath('cal').'template/img/recurring.gif" width="9" height="9" alt="" border="0" hspace="0" vspace="0" />&nbsp;';
								}
								if ($this->rightsObj->isAllowedToEditEvents()) {
									$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_edit_event').'"';
									$editlink = $this->controller->pi_linkTP_keepPIvars('<img src="typo3/gfx/edit2.gif" border="0"/>', array ("lastview" => "day", "view" => "edit_event", "type" => $event->getType(), "uid" => $event->getUid()), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["event."]["edit_eventViewPid"]);
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
								$rowspan = ($dayEndTime -  $dayStartTime) / ($gridLength * 60) + $plus;
								$daydisplay .= '<td rowspan="'.$rowspan.'"   align="left" valign="top" class="eventbg2 '.$event->getBodyStyle().'">'."\n";
	
								// Start drawing the event
								$event_temp = $loop_event;
								$switch = array();
								$rems = array();
								$event->getEventMarker($event_temp,$rems,$switch);
								$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_event_view').'"';
								$sims["###EVENT###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($event, $event->renderEventForDay(),"day", $getdate), $event_temp, array (), array ());
								$e_start = date($this->shared->lang('l_timeFormat'), $event->getStarttime());
								$sims["###EVENT_START###"] = $editlink.$confirmed.$this->cObj->substituteMarkerArrayCached($e_start, $event_temp, array (), array ());
								$e_end = date($this->shared->lang('l_timeFormat'), $event->getEndtime());
								$sims["###EVENT_END###"] = $this->cObj->substituteMarkerArrayCached($e_end, $event_temp, array (), array ());
								$sims["###CONFIRMED###"] = $this->cObj->substituteMarkerArrayCached($event->getConfirmed(), $event_temp, array (), array ());
								$sims["###STYLE###"] = $this->cObj->substituteMarkerArrayCached($event->getHeaderStyle(), $event_temp, array (), array ());
	
								$daydisplay .= $this->cObj->substituteMarkerArrayCached($event_temp, $sims, array (), array ());
								$daydisplay .= '</td>';
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
							$daydisplay .= '<td style="" colspan="'.$remember.'" '.$class.'>';
							if ($this->rightsObj->isAllowedToCreateEvents()) {
								$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
								$daydisplay .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ("gettime" => $cal_time, "lastview" => "day", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'],$this->cObj->conf["view."]["event."]["create_eventViewPid"]);
							}
							$daydisplay .= '&nbsp;</td>'."\n";
							$remember = 0;
						}
					}
					if($remember>0){
						$daydisplay .= '<td style="" colspan="'.$remember.'" '.$class.'>';
						if ($this->rightsObj->isAllowedToCreateEvents()) {
							$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
							$daydisplay .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ("gettime" => $cal_time, "lastview" => "day", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'],$this->cObj->conf["view."]["event."]["create_eventViewPid"]);
						}
						$daydisplay .= '&nbsp;</td>'."\n";
						$remember = 0;
					}
				}

			} else {
				$daydisplay .= '<td colspan="'.$nbrGridCols.'" '.$class.'>';
				if ($this->rightsObj->isAllowedToCreateEvents()) {
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
					$daydisplay .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ("gettime" => $cal_time, "lastview" => "day", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'],$this->cObj->conf["view."]["event."]["create_eventViewPid"]);
				}
				$daydisplay .= '&nbsp;</td>'."\n";
			}
			$daydisplay .= '</tr>'."\n";
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