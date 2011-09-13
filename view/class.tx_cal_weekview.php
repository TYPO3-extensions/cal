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
	
	/**
	 *  Draws the week view.
	 *  @param		$master_array	array		The events to be drawn.
	 *  @param		$getdate		integer		The date of the event
	 *	@return		string		The HTML output.
	 */
	function drawWeek(&$master_array, $getdate) {

		$this->_init($master_array);

		$weekStartDay = $this->cObj->conf["view."]["weekStartDay"]; //'Monday';			// Day of the week your week starts on
		$dayStart = $this->cObj->conf["view."]["day."]["dayStart"]; //'0700';			// Start time for day grid
		$dayEnd = $this->cObj->conf["view."]["day."]["dayEnd"]; //'2300';			// End time for day grid
		$gridLength = $this->cObj->conf["view."]["day."]["gridLength"]; //'15';				// Grid distance in minutes for day view, multiples of 15 preferred

		$unix_time = strtotime($getdate);
		$day_array2 = array();
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

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

		$parse_month = date("Ym", $unix_time);
		$first_of_month = $this_year.$this_month."01";

		$start_month_day = tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay);

		$thisday2 = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week_list'), $unix_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());

		$num_of_events2 = 0;

		$today_today = date('Ymd', (time()));
		$next_week = date("Ymd", strtotime("+1 week", $unix_time));
		$prev_week = date("Ymd", strtotime("-1 week", $unix_time));
		$next_day = date('Ymd', strtotime("+1 day", $unix_time));
		$prev_day = date('Ymd', strtotime("-1 day", $unix_time));
		$start_week_time = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));
		$end_week_time = $start_week_time + (6 * 25 * 60 * 60);
		$start_week = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week'), $start_week_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
		$end_week = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week'), $end_week_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
		$display_date = "$start_week - $end_week";
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_week_view').'"';
		if (!empty ($this->cObj->conf["view."]["week."]["weekViewPid"])) {
			$next_week_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['nextWeekSymbol'], array ("getdate" => $next_week, "view" => "week"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["week."]["weekViewPid"]);
			$prev_week_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['previousWeekSymbol'], array ("getdate" => $prev_week, "view" => "week"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["week."]["weekViewPid"]);
		} else {
			$next_week_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['nextWeekSymbol'], array ("getdate" => $next_week, "view" => "week"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$prev_week_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['previousWeekSymbol'], array ("getdate" => $prev_week, "view" => "week"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
		}
		
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_day_view').'"';
		if (!empty ($this->cObj->conf["view."]["day."]["dayViewPid"])) {
			$next_day_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['nextDaySymbol'], array ("getdate" => $next_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			$prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['previousDaySymbol'], array ("getdate" => $prev_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/left_arrows.gif" alt="'.$this->shared->lang('l_prev').'" class="nextweek_arrow" />', array ("getdate" => $next_week, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
			$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/right_arrows.gif" alt="'.$this->shared->lang('l_next').'" class="previousweek_arrow" />', array ("getdate" => $prev_week, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["day."]["dayViewPid"]);
		} else {
			$next_day_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['nextDaySymbol'], array ("getdate" => $next_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$prev_day_link = $this->controller->pi_linkTP_keepPIvars($this->cObj->conf['view.']['week.']['previousDaySymbol'], array ("getdate" => $prev_day, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$legend_prev_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/left_arrows.gif" alt="'.$this->shared->lang('l_prev').'" class="nextweek_arrow" />', array ("getdate" => $next_week, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			$legend_next_day_link = $this->controller->pi_linkTP_keepPIvars('<img src="###IMG_PATH###/right_arrows.gif" alt="'.$this->shared->lang('l_next').'" class="previousweek_arrow" />', array ("getdate" => $prev_week, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
		}
		// For the side months
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		// Figure out colspans
		$dayborder = 0;
		$thisdate = $start_week_time;
		$swt = $start_week_time;
		$view_array = array ();
		$rowspan_array = array();
//		for ($i = 0; $i < 7; $i ++) {
//			$thisday = date("Ymd", $thisdate);
//			$nbrGridCols[$thisday] = 1;
			if (count($this->master_array)>1) {
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
						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
//						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $thisday, $dDate);
						$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
						$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);		
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
								// get x-array possition
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
										for($k = 0; $k < count($this->master_array[date("Ymd",$d_start)]); $k++){
											if(empty($view_array[date("Ymd",$d_start)][$d_start][$k])){
												break;
											}
										}
										$entries = 0;
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

		$t_array = array ();
		$pos_array = array ();		
		preg_match('/([0-9]{2})([0-9]{2})/', $this->cObj->conf["view."]["day."]["dayStart"], $dTimeStart);
		preg_match('/([0-9]{2})([0-9]{2})/', $this->cObj->conf["view."]["day."]["dayEnd"], $dTimeEnd);
//debug($view_array);
		foreach($view_array as $week_key => $week_day){
			preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $week_key, $dDate);
			$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
			$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
			$d_start = $d_start - $d_start % ($gridLength * 60);
			$d_end = $d_end - $d_end % ($gridLength * 60);

			for ($i = $d_start; $i < $d_end; $i = $i + ($gridLength * 60)) {		
				if (is_array($view_array[$week_key][$i]) &&count($view_array[$week_key][$i]) > 0) {
					foreach ($view_array[$week_key][$i] as $event) {
						if (is_array($pos_array[$week_key]) && array_key_exists($event->getType().$event->getUid(), ($pos_array[$week_key]))) {
							$nd = $event->getEndtime() - ($event->getEndtime() % ($gridLength * 60));
							if ($i >= $nd) {
								$t_array[$week_key][$i][$pos_array[$week_key][$event->getType().$event->getUid()]] = array ("ended" => $event);
							} else {
								$t_array[$week_key][$i][$pos_array[$week_key][$event->getType().$event->getUid()]] = array ("started" => $event);
							}
						} else {
							for ($j = 0; $j < $nbrGridCols[$week_key]?$nbrGridCols[$week_key]:1; $j ++) {
								if (count($t_array[$week_key][$i][$j]) == 0 || !isset ($t_array[$week_key][$i][$j])) {
									$pos_array[$week_key][$event->getType().$event->getUid()] = $j;
									$t_array[$week_key][$i][$j] = array ("begin" => $event);
									break;
								}
							}
						}
					}
//debug($t_array);

				} else {
					$t_array[$week_key][$i] = "";
				}
			}
		}

		$temp_time = $start_week_time;
		for ($i = 0; $i < 7; $i ++) {
			$thisdate = date('Ymd', $temp_time);
			$weekarray[$i] = $thisdate;
			$temp_time = $temp_time + 24*60*60;
		}

		$page = $this->cObj->fileResource($this->cObj->conf["view."]["week."]["weekTemplate"]);
		if ($page == "") {
			return "<h3>week: no template file found:</h3>".$this->cObj->conf["view."]["week."]["weekTemplate"]."<br />Please check your template record and add both cal items at 'include static (from extension)'";
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
			'next_week_link' => $next_week_link, 
			'prev_day_link' => $prev_day_link, 
			'prev_week_link' => $prev_week_link, 
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
			'l_subscribe' => $this->shared->lang('l_subscribe'), 
			'l_download' => $this->shared->lang('l_download'), 
			'l_search' => $this->shared->lang('l_search'), 
			'l_powered_by' => $this->shared->lang('l_powered_by'), 
			'l_this_site_is' => $this->shared->lang('l_this_site_is'), 
		);

		$page = $this->shared->replace_tags($languageArray, $page);
		// Replaces the allday events
		$loop_ad = $this->cObj->getSubpart($page, "###LOOPALLDAY###");
		$loop_begin = $this->cObj->getSubpart($page, "###ALLDAYSOFWEEK_BEGIN###");
		$loop_end = $this->cObj->getSubpart($page, "###ALLDAYSOFWEEK_END###");

		foreach ($weekarray as $get_date) {
			$replace = $loop_begin;
			$colspan = 'colspan="'.($nbrGridCols[$get_date]?$nbrGridCols[$get_date]:1).'"';

			$sims["###COLSPAN###"] = $this->cObj->substituteMarkerArrayCached($colspan, $replace, array (), array ());
			$replace = $this->cObj->substituteMarkerArrayCached($replace, $sims, array (), array ());
			if (is_array($this->master_array[$get_date]['-1'])) {

				foreach ($this->master_array[$get_date]['-1'] as $uid => $allday) {
					$sims["###ALLDAY###"] = '<div class="'.$allday->getHeaderStyle().'_allday">'.$this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($allday, $allday->renderEventForAllDay(),"day", date("Ymd",$allday->getStarttime())), $loop_ad, array (), array ())."</div>";
					$sims["###STYLE###"] = $this->cObj->substituteMarkerArrayCached($allday->getHeaderStyle(), $loop_ad, array (), array ());

					$replace .= $this->cObj->substituteMarkerArrayCached($loop_ad, $sims, array (), array ());

				}
			}
			$replace .= $loop_end;
			$weekreplace .= $replace;
		}
		$rems = array ();
		$rems["###ALLDAYSOFWEEK###"] = $weekreplace;
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		$rems = array ();
		// Replaces the daysofweek
		$loop_dof = $this->cObj->getSubpart($page, "###DAYSOFWEEK###");
		$start_wt = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));
		$start_day = strtotime($weekStartDay);

		for ($i = 0; $i < 7; $i ++) {
			$day_num = date("w", $start_day);
			$daylink = date('Ymd', $start_wt);
//			if ($current_view == 'day') {
//				$weekday = $daysofweek_lang[$day_num];
//			} else {

				$weekday = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week_list'), strtotime($daylink), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
//			}

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
				$link = $this->controller->pi_linkTP_keepPIvars('<span class="V9BOLD">'.$weekday.'</span>', array ("getdate" => $daylink, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'],$this->cObj->conf["view."]["day."]["dayViewPid"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('<span class="V9BOLD">'.$weekday.'</span>', array ("getdate" => $daylink, "view" => "day"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway']);
			}
			$start_day = strtotime("+1 day", $start_day);
			$start_wt = strtotime("+1 day", $start_wt);
			$colspan = 'colspan="'.($nbrGridCols[$daylink]?$nbrGridCols[$daylink]:1).'"';
			$search = array ('###LINK###', '###DAYLINK###', '###ROW1###', '###ROW2###', '###ROW3###', '###COLSPAN###');
			$replace = array ($link, $daylink, $row1, $row2, $row3, $colspan);
			$loop_tmp = str_replace($search, $replace, $loop_dof);
			$weekday_loop .= $loop_tmp;
		}

		
		$rems["###DAYSOFWEEK###"] = $weekday_loop;
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		// Build the body
		$loop_hours = $this->cObj->getSubpart($page, "###LOOPROW###");
		$loop_event = $this->cObj->getSubpart($page, "###LOOPEVENT###");

		$border = 0;
		$thisdate = $swt;

		$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, 0,0,1);
		$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, 0,0,1);
		$d_start = $d_start - $d_start % ($gridLength*60);
		$d_end = $d_end - $d_end % ($gridLength*60);
		$loops = ($d_end - $d_start)/($gridLength*60);
		$d_start_time = $d_start - mktime(0, 0, 0, 0,0,1);
		$weekdisplay = "";
		$sims = array('###GRIDLENGTH###' => $gridLength, '###60TH_GRIDLENGTH###' => (60 / $gridLength));
		for($i=0;$i<$loops;$i++){
			for($j=0;$j<7;$j++){
				$daytime = $start_week_time+$j*60*60*24+$d_start_time;
				$day = date("Ymd",$daytime);
				$time = $daytime+$i*$gridLength*60;
				if($j==0){
					$key = date($this->shared->lang('l_timeFormat'),$time);
					if (ereg("([0-9]{1,2}):00", $key)) {
						$sims['###TIME###'] = $key;
						$weekdisplay .= $this->cObj->substituteMarkerArrayCached($this->cObj->conf['view.']['week.']['weekDisplayFullHour'], $sims, array(), array ());
					} else {
						$weekdisplay .= $this->cObj->substituteMarkerArrayCached($this->cObj->conf['view.']['week.']['weekDisplayInbetween'], $sims, array(), array ());
					}
				}
				
				$something = $t_array[$day][$time];
				$class = ' class="weekborder"';
				if (is_array($something) && $something != "" && count($something) > 0) {
					for ($k = 0; $k < count($something); $k ++) {
						if(!empty($something[$k])){
							$keys = array_keys($something[$k]);
							switch ($keys[0]) {
								case 'begin' :
									$event = $something[$k][$keys[0]];
									$event_start = date($this->shared->lang('l_timeFormat'), strtotime($event->getStartHour()));
									$event_end = date($this->shared->lang('l_timeFormat'), strtotime($event->getEndHour()));
									$event_calno = $event->getCalNumber();
									$event_recur = $event->getCalRecu();
									$event_status = strtolower($event->getStatus());
									if ($event_status != '') {
										$confirmed = '<img src="'.t3lib_extMgm :: siterelpath('cal').'template/img/'.$event_status.'.gif" width="9" height="9" alt="" border="0" hspace="0" vspace="0" />&nbsp;';
									}
									elseif (is_array($event_recur)) {
										$confirmed = '<img src="'.t3lib_extMgm :: siterelpath('cal').'template/img/recurring.gif" width="9" height="9" alt="" border="0" hspace="0" vspace="0" />&nbsp;';
									}
									if ($this->rightsObj->isAllowedToEditEvents()) {
										$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_edit_event').'"';
										$editlink = $this->controller->pi_linkTP_keepPIvars('<img src="typo3/gfx/edit2.gif" alt="'.$this->shared->lang('l_edit_event').'" border="0"/>', array ("lastview" => "week", "view" => "edit_event", "type" => $event->getType(), "uid" => $event->getUid()), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["event."]["edit_eventViewPid"]);
									}
									$rest = $event->getEndtime() % ($gridLength * 60);
									$plus = 0;
									if($rest>0){
										$plus = 1;
									}
									//$rowspan = (($event->getEndtime() - ($rest)) - ($event->getStarttime() - ($event->getStarttime() % ($gridLength * 60)))) / ($gridLength * 60) + $plus;
									$rowspan = $rowspan_array[$day][$event->getType()."_".$event->getUid()];
									$weekdisplay .= '<td rowspan="'.$rowspan.'" colspan="1" align="left" valign="top" class="eventbg2 '.$event->getBodyStyle().'">'."\n";
		
									// Start drawing the event
									$event_temp = $loop_event;
									$switch = array();
									$rems = array();
									$event->getEventMarker($event_temp,$rems,$switch);
									$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_event_view').'"';
									$sims["###EVENT###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($event, $event->renderEventForWeek(),"week", $day), $event_temp, array (), array ());
									$e_start = date($this->shared->lang('l_timeFormat'), $event->getStarttime());
									$sims["###EVENT_START###"] = $editlink.$this->cObj->substituteMarkerArrayCached($e_start, $event_temp, array (), array ());
									$e_end = date($this->shared->lang('l_timeFormat'), $event->getEndtime());
									$sims["###EVENT_END###"] = $this->cObj->substituteMarkerArrayCached($e_end, $event_temp, array (), array ());
									$sims["###CONFIRMED###"] = $this->cObj->substituteMarkerArrayCached($event->getConfirmed(), $event_temp, array (), array ());
									$sims["###STYLE###"] = $this->cObj->substituteMarkerArrayCached($event->getHeaderStyle(), $event_temp, array (), array ());
									
									$weekdisplay .= $this->cObj->substituteMarkerArrayCached($event_temp, $sims, array (), array ());
									$weekdisplay .= '</td>'."\n";
									// End event drawing
									break;
							}
						}
					}
					if (count($something) < ($nbrGridCols[$day]?$nbrGridCols[$day]:1)) {
						$remember = 0;
						for($l = 0; $l < ($nbrGridCols[$day]?$nbrGridCols[$day]:1); $l++){
							if(!$something[$l]){
								$remember++;
							}else if($remember>0){
								$weekdisplay .= '<td colspan="'.$remember.'" '.$class.'>';
								if ($this->rightsObj->isAllowedToCreateEvents()) {
									$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
									$weekdisplay .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" alt="'.$this->shared->lang('l_create_event').'" border="0"/>', array ("getdate" => $day, "gettime" => date("Hi",$time), "lastview" => "week", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["event."]["create_eventViewPid"]);
								}
								$weekdisplay .= '&nbsp;</td>'."\n";
								$remember = 0;
							}
						}
						if($remember>0){
							$weekdisplay .= '<td colspan="'.$remember.'" '.$class.'>';
							if ($this->rightsObj->isAllowedToCreateEvents()) {
								$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
								$weekdisplay .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" alt="'.$this->shared->lang('l_create_event').'" border="0"/>', array ("getdate" => $day, "gettime" => date("Hi",$time), "lastview" => "week", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["event."]["create_eventViewPid"]);
							}
							$weekdisplay .= '&nbsp;</td>'."\n";
							$remember = 0;
						}
					}
	
				} else {
					$weekdisplay .= '<td colspan="'.($nbrGridCols[$day]?$nbrGridCols[$day]:1).'" '.$class.'>';
					if ($this->rightsObj->isAllowedToCreateEvents()) {
						$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
						$weekdisplay .= $this->controller->pi_linkTP_keepPIvars('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" alt="'.$this->shared->lang('l_create_event').'" border="0"/>', array ("getdate" => $day, "gettime" => date("Hi",$time), "lastview" => "week", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["event."]["create_eventViewPid"]);
					}
					$weekdisplay .= '&nbsp;</td>'."\n";
				}
				if($j==6){
					$weekdisplay .= '</tr>'."\n";
				}
			}
		}
		$rems = array ();
		$rems["###LOOPEVENTS###"] = $weekdisplay;
		return $this->finish($page, $rems);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_weekview.php']);
}
?>