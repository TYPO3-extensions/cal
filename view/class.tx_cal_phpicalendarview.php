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

require_once (PATH_t3lib.'class.t3lib_svbase.php');
require_once (PATH_tslib."class.tslib_pibase.php");
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_shared.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_phpicalendarview extends t3lib_svbase {

	var $local_pibase;
	var $cObj;
	var $conf;
	var $shared;
	var $prefixId = 'tx_cal_controller';

	/**
	 *  Draws a single event.
	 *  @param		array			The events to be drawn.
	 *	 @return		string		The HTML output.
	 */
	function drawEvent(& $cObj, & $event, $getdate, & $rightsObj) {

		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);

		$lastview = $this->conf['lastview'];
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$page = $this->cObj->fileResource($this->conf["view."]["event."]["eventTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->conf["view."]["event."]["eventTemplate"];
		}
		$rems["###EVENT###"] = $event->renderEvent($cObj, $rightsObj);
		if (!empty ($this->conf['page_id'])) {
			$rems["###BACKLINK###"] = $this->shared->pi_linkToPage($this->shared->lang('l_back'), array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => $lastview), 0, $this->conf['page_id']);
		} else {
			$rems["###BACKLINK###"] = $this->shared->pi_linkToPage($this->shared->lang('l_back'), array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => $lastview));
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		return $this->replaceLinksToOtherViews($page, $getdate);
	}

	/**
	 *  Draws a single event.
	 *  @param		array			The events to be drawn.
	 *	 @return		string		The HTML output.
	 */
	function drawDay($cObj, $master_array, $getdate, $rightsObj) {

		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);

		$weekStartDay = $this->conf["view."]["week."]["weekStartDay"]; //'Monday';			// Day of the week your week starts on
		$dayStart = $this->conf["view."]["day."]["dayStart"]; //'0700';			// Start time for day grid
		$dayEnd = $this->conf["view."]["day."]["dayEnd"]; //'2300';			// End time for day grid
		$gridLength = $this->conf["view."]["day."]["gridLength"]; //'15';				// Grid distance in minutes for day view, multiples of 15 preferred

		if (!isset ($getdate) || $getdate == '') {
			$getdate = date('Ymd');
		}

		$unix_time = strtotime($getdate);
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		$today_today = date('Ymd', time());
		$next_day = date('Ymd', strtotime("+1 day", $unix_time));
		$prev_day = date('Ymd', strtotime("-1 day", $unix_time));
		if (!empty ($this->conf["dayViewPid"])) {
			$next_day_link = $this->shared->pi_linkToPage('&raquo;', array ($this->prefixId."[getdate]" => $next_day, $this->prefixId."[view]" => "day"), 0, $this->conf["dayViewPid"]);
			$prev_day_link = $this->shared->pi_linkToPage('&laquo;', array ($this->prefixId."[getdate]" => $prev_day, $this->prefixId."[view]" => "day"), 0, $this->conf["dayViewPid"]);
			$legend_prev_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/left_arrows.gif" alt="'.$this->shared->lang('l_prev').'" width="16" height="20" border="0" align="left" />', array ($this->prefixId."[getdate]" => $prev_day, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
			$legend_next_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/right_arrows.gif" alt="'.$this->shared->lang('l_next').'" width="16" height="20" border="0" align="right" />', array ($this->prefixId."[getdate]" => $next_day, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$next_day_link = $this->shared->pi_linkToPage('&raquo;', array ($this->prefixId."[getdate]" => $next_day, $this->prefixId."[view]" => "day"));
			$prev_day_link = $this->shared->pi_linkToPage('&laquo;', array ($this->prefixId."[getdate]" => $prev_day, $this->prefixId."[view]" => "day"));
			$legend_prev_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/left_arrows.gif" alt="'.$this->shared->lang('l_prev').'" width="16" height="20" border="0" align="left" />', array ($this->prefixId."[getdate]" => $prev_day, $this->prefixId."[view]" => "day"));
			$legend_next_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/right_arrows.gif" alt="'.$this->shared->lang('l_next').'" width="16" height="20" border="0" align="right" />', array ($this->prefixId."[getdate]" => $next_day, $this->prefixId."[view]" => "day"));
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

		// select for calendars
		$list_icals = '';
		$list_years = $this->list_years($getdate, $this_year, $master_array, $this->shared->lang('l_dateFormat_week_jump'), $weekStartDay);
		$list_months = $this->list_months($getdate, $this_year, $master_array, $this->shared->lang('l_dateFormat_month'), $weekStartDay);
		$list_weeks = $this->list_weeks($getdate, $this_year, $master_array, $this->shared->lang('l_dateFormat_week_jump'), $weekStartDay);
		$list_icals_pick = '';
		$list_jumps = $this->list_jumps();
		$list_calcolors = $this->list_calcolors($master_array);
		$page = $this->cObj->fileResource($this->conf["view."]["day."]["dayTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->conf["view."]["day."]["dayTemplate"];
		}
		$page = $this->replace_files($page, array (
			'sidebar' => $this->conf["view."]["other."]["sidebarTemplate"], 
			'search_box' => $this->conf["view."]["other."]["searchBoxTemplate"])
		);

		if ($this->conf["view."]["other."]["showLogin"] != 'yes') {
			$rems["###SHOW_USER_LOGIN###"] = '';
		}
		if ($this->conf["view."]["other."]["showSearch"] != 'yes') {
			$rems["###SHOW_SEARCH###"] = '';
		}
		if ($this->conf["view."]["other."]["showGoto"] != 'yes') {
			$rems["###SHOW_GOTO###"] = '';
		}
		if ($this->conf["view."]["other."]["showMultiple"] != 'yes') {
			$rems["###SHOW_MULTIPLE###"] = '';
		}

		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		$languageArray = array (
			'getdate' => $getdate, 
			'display_date' => $display_date, 
			'legend_prev_day' => $legend_prev_day_link, 
			'legend_next_day' => $legend_next_day_link, 
			'next_day_link' => $next_day_link, 
			'prev_day_link' => $prev_day_link,
			'show_goto' => '',
			'calendar_name' => $this->conf["calendarName"],
			'list_icals' => $list_icals, 
			'legend' => $list_calcolors,
			'list_jumps' => $list_jumps,
			'list_years' => $list_years, 
			'list_months' => $list_months, 
			'list_weeks' => $list_weeks,
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
			'l_search' => $this->shared->lang('l_search'), 
			'l_this_site_is' => $this->shared->lang('l_this_site_is'), 
			'l_login' => $this->shared->lang('l_login'), 
			'l_logout' => $this->shared->lang('l_logout')
		,);
		$page = $this->shared->replace_tags($languageArray, $page);

		// Replaces the allday events
		$replace = '';
		if (is_array($master_array[$getdate]['0000'])) {
			$loop_ad = $this->cObj->getSubpart($page, "###LOOPALLDAY###");
			foreach ($master_array[$getdate]['0000'] as $uid => $allday) {
				$sims["###ALLDAY###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($allday, $allday->renderEventForAllDay(),"day", date("Ymd",$allday->getStarttime())), $loop_ad, array (), array ());
				$sims["###CALNO###"] = $this->cObj->substituteMarkerArrayCached($allday->getHeaderColor(), $loop_ad, array (), array ());
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
			if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
				$link = $this->shared->pi_linkToPage($weekday, array ($this->prefixId."[getdate]" => $daylink, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
			} else {
				$link = $this->shared->pi_linkToPage($weekday, array ($this->prefixId."[getdate]" => $daylink, $this->prefixId."[view]" => "day"));
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
		
		$view_array = array ();
		$rowspan_array = array();
		for ($i = 0; $i < 7; $i ++) {
			$thisday = date("Ymd", $thisdate);
			$nbrGridCols[$thisday] = 1;
			if (isset ($master_array[$thisday])) {
				foreach ($master_array[($thisday)] as $ovlKey => $ovlValue) {
					if ($ovlKey != "-1") {
						preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayStart"], $dTimeStart);
						preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayEnd"], $dTimeEnd);
						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $thisday, $dDate);
						$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
						$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);
						
						foreach ($ovlValue as $ovl2Value) {
							
							$starttime = $ovl2Value->getStarttime() - $ovl2Value->getStarttime() % ($gridLength * 60);
							$endtime = $ovl2Value->getEndtime();
							$rest = ($endtime % ($gridLength * 60));
							$endtime = $endtime - $rest;
							if($rest > 0){
								$endtime = $endtime + ($gridLength * 60);
							}
							$entries = 1;
							$old_day = $thisday;
							$endOfDay = $d_end;
							$d_start = $d_start - $gridLength * 60;
							
							for ($j = $starttime; $j < $endtime; $j = $j + $gridLength * 60) {
								$view_array[date("Ymd",$j)][$j][count($view_array[date("Ymd",$j)][$j])] = $ovl2Value;			
								if($j >= $endOfDay){
									$rowspan_array[$old_day][$ovl2Value->getType()."_".$ovl2Value->getUid()] = $entries-1;
									
									$endOfDay = $endOfDay + 60 * 60 * 24;
									$old_day = date("Ymd",$endOfDay);
									$d_start = $d_start + 60 * 60 * 24;
									$j = $d_start;
									$entries = 0;
								}
								$entries++;
			
								
							}
							$rowspan_array[$old_day][$ovl2Value->getType()."_".$ovl2Value->getUid()] = $entries-1;
						}
					}
				}
				$max = array ();
				if(!empty($view_array[$thisday])){
					foreach ($view_array[$thisday] as $array_time => $time_val) {
						$c = count($view_array[$thisday][$array_time]);
						array_push($max, $c);
					}
					$nbrGridCols[($thisday)] = max($max);
				}else{
					$nbrGridCols[($thisday)] = 1;
				}
			}else{
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
			$thisdate = ($thisdate + (25 * 60 * 60));
		}
	
//		
//		if (isset ($master_array[($getdate)])) {
//			foreach ($master_array[($getdate)] as $ovlKey => $ovlValue) {
//				if ($ovlKey != '-1') {
//					foreach ($ovlValue as $ovl2Value) {					
//						$starttime = $ovl2Value->getStarttime() - $ovl2Value->getStarttime() % ($gridLength * 60);
//						$endtime = $ovl2Value->getEndtime();
//						for ($j = $starttime; $j <= $endtime; $j = $j + $gridLength * 60) {
//							$view_array[$j][count($view_array[$j])] = $ovl2Value;
//						}
//					}
//
//				}
//			}
//			if(!empty($view_array)){
//				$max = array ();
//				foreach ($view_array as $array_time => $time_val) {
//					array_push($max, count($view_array[$array_time]));
//				}
//				$nbrGridCols = max($max);
//			}
//		}

		$view_array = $view_array[$getdate];
		$nbrGridCols = $nbrGridCols[$getdate];
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
								$event_end = date($this->shared->lang('l_timeFormat'), strtotime($event->getEndHour()));
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
								if ($rightsObj->isAllowedToEditEvents()) {
									$editlink = $this->shared->pi_linkToPage('<img src="typo3/gfx/edit2.gif" border="0"/>', array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => "day", $this->prefixId."[view]" => "edit_event", $this->prefixId."[type]" => $event->getType(), $this->prefixId."[uid]" => $event->getUid()), 0, $this->conf["view."]["event"]["edit_eventViewPid"]);
								}
								$rest = $event->getEndtime() % ($gridLength * 60);
								$plus = 0;
								if($rest>0){
									$plus = 1;
								}
								$rowspan = (($event->getEndtime() - ($event->getEndtime() % ($gridLength * 60))) - ($event->getStarttime() - ($event->getStarttime() % ($gridLength * 60)))) / ($gridLength * 60) + $plus;
								$daydisplay .= '<td rowspan="'.$rowspan.'"   align="left" valign="top" class="eventbg2" style="background-color:'.$event->getBodyColor().';color:'.$event->getBodyTextColor().';border:1px solid '.$event->getHeaderColor().'">'."\n";
	
								// Start drawing the event
								$event_temp = $loop_event;
								$sims["###EVENT###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($event, $event->renderEventForDay(),"day", $getdate), $event_temp, array (), array ());
								$e_start = date($this->shared->lang('l_timeFormat'), $event->getStarttime());
								$sims["###EVENT_START###"] = $editlink.$confirmed.$this->cObj->substituteMarkerArrayCached($e_start, $event_temp, array (), array ());
								$e_end = date($this->shared->lang('l_timeFormat'), $event->getEndtime());
								$sims["###EVENT_END###"] = $this->cObj->substituteMarkerArrayCached($e_end, $event_temp, array (), array ());
								$sims["###CONFIRMED###"] = $this->cObj->substituteMarkerArrayCached($event->getConfirmed(), $event_temp, array (), array ());
								$sims["###EVENT_CALNO###"] = $this->cObj->substituteMarkerArrayCached($event->getHeaderColor(), $event_temp, array (), array ());
								$sims["###EVENT_TEXT_CALNO###"] = $this->cObj->substituteMarkerArrayCached($event->getHeaderTextColor(), $event_temp, array (), array ());
	
								$daydisplay .= $this->cObj->substituteMarkerArrayCached($event_temp, $sims, array (), array ());
								$daydisplay .= '</td>';
								// End event drawing
								break;
							case 'ended' :
//								$daydisplay .= '<td colspan="'.count($val).'" '.$class.'>';
	//							if ($rightsObj->isAllowedToCreateEvents()) {
	//								$daydisplay .= $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[gettime]" => $cal_time, $this->prefixId."[lastview]" => "day", $this->prefixId."[view]" => "create_event"), 0, $this->conf["view."]["event"]["create_eventViewPid"]);
	//							}
	//							$daydisplay .= '&nbsp;</td>'."\n";
								break;
						}
					}
				}
				if (count($val) < $nbrGridCols) {
					$daydisplay .= '<td colspan="'. ($nbrGridCols -count($val)).'" '.$class.'>';
					if ($rightsObj->isAllowedToCreateEvents()) {
						$daydisplay .= $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[gettime]" => $cal_time, $this->prefixId."[lastview]" => "day", $this->prefixId."[view]" => "create_event"), 0, $this->conf["view."]["event."]["create_eventViewPid"]);
					}
					$daydisplay .= '&nbsp;</td>'."\n";
				}

			} else {
				$daydisplay .= '<td colspan="'.$nbrGridCols.'" '.$class.'>';
				if ($rightsObj->isAllowedToCreateEvents()) {
					$daydisplay .= $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[gettime]" => $cal_time, $this->prefixId."[lastview]" => "day", $this->prefixId."[view]" => "create_event"), 0, $this->conf["view."]["event."]["create_eventViewPid"]);
				}
				$daydisplay .= '&nbsp;</td>'."\n";
			}
			$daydisplay .= '</tr>'."\n";
		}

		$rems = array ();
		$rems["###DAYEVENTS###"] = $daydisplay;
		$page = $this->checkForMonthMarker($master_array, $page, $getdate, $rightsObj);
		if ($this->conf["view."]["other."]["showTomorrowEvents"] == 1) {
			$page = $this->tomorrows_events($page, $getdate, $master_array);
		} else {
			$rems["###TOMORROWS_EVENTS###"] = '';
		}
		if ($this->conf["view."]["other."]["show_todos"] == 1) {
			$page = $this->get_vtodo($page, $getdate, $master_array);
		} else {
			$rems["###VTODO###"] = '';
		}
		if ($this->conf["view."]["other."]["showGoto"] != 1) {
			$rems["###SHOW_GOTO###"] = '';
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		return $this->replaceLinksToOtherViews($page, $getdate);

	}

	/**
	 *  Draws a single event.
	 *  @param		array			The events to be drawn.
	 *	 @return		string		The HTML output.
	 */
	function drawWeek($cObj, $master_array, $getdate, $rightsObj) {

		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);

		$weekStartDay = $this->conf["view."]["week."]["weekStartDay"]; //'Monday';			// Day of the week your week starts on
		$dayStart = $this->conf["view."]["day."]["dayStart"]; //'0700';			// Start time for day grid
		$dayEnd = $this->conf["view."]["day."]["dayEnd"]; //'2300';			// End time for day grid
		$gridLength = $this->conf["view."]["day."]["gridLength"]; //'15';				// Grid distance in minutes for day view, multiples of 15 preferred

		$unix_time = strtotime($getdate);
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

//		if ($minicalView == "current")
//			$minicalView = "week";

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

		if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
			$next_week_link = $this->shared->pi_linkToPage('&raquo;', array ($this->prefixId."[getdate]" => $next_week, $this->prefixId."[view]" => "week"), 0, $this->conf["view."]["week."]["weekViewPid"]);
			$prev_week_link = $this->shared->pi_linkToPage('&laquo;', array ($this->prefixId."[getdate]" => $prev_week, $this->prefixId."[view]" => "week"), 0, $this->conf["view."]["week."]["weekViewPid"]);
		} else {
			$next_week_link = $this->shared->pi_linkToPage('&raquo;', array ($this->prefixId."[getdate]" => $next_week, $this->prefixId."[view]" => "week"));
			$prev_week_link = $this->shared->pi_linkToPage('&laquo;', array ($this->prefixId."[getdate]" => $prev_week, $this->prefixId."[view]" => "week"));
		}

		if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
			$next_day_link = $this->shared->pi_linkToPage('&rsaquo;', array ($this->prefixId."[getdate]" => $next_day, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
			$prev_day_link = $this->shared->pi_linkToPage('&lsaquo;', array ($this->prefixId."[getdate]" => $prev_day, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
			$legend_prev_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/left_arrows.gif" alt="'.$this->shared->lang('l_prev').'" width="16" height="20" border="0" align="left" />', array ($this->prefixId."[getdate]" => $next_week, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
			$legend_next_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/right_arrows.gif" alt="'.$this->shared->lang('l_next').'" width="16" height="20" border="0" align="right" />', array ($this->prefixId."[getdate]" => $prev_week, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$next_day_link = $this->shared->pi_linkToPage('&rsaquo;', array ($this->prefixId."[getdate]" => $next_day, $this->prefixId."[view]" => "day"));
			$prev_day_link = $this->shared->pi_linkToPage('&lsaquo;', array ($this->prefixId."[getdate]" => $prev_day, $this->prefixId."[view]" => "day"));
			$legend_prev_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/left_arrows.gif" alt="'.$this->shared->lang('l_prev').'" width="16" height="20" border="0" align="left" />', array ($this->prefixId."[getdate]" => $next_week, $this->prefixId."[view]" => "day"));
			$legend_next_day_link = $this->shared->pi_linkToPage('<img src="###IMG_PATH###/right_arrows.gif" alt="'.$this->shared->lang('l_next').'" width="16" height="20" border="0" align="right" />', array ($this->prefixId."[getdate]" => $prev_week, $this->prefixId."[view]" => "day"));
		}
		// For the side months
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		// select for calendars
		$list_icals = ''; //display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED));
		$list_years = $this->list_years($getdate, $this_year, $master_array, $this->shared->lang('l_dateFormat_week_jump'), $weekStartDay);
		$list_months = $this->list_months($getdate, $this_year, $master_array, $this->shared->lang('l_dateFormat_month'), $weekStartDay);
		$list_weeks = $this->list_weeks($getdate, $this_year, $master_array, $this->shared->lang('l_dateFormat_week_jump'), $weekStartDay);
		$list_jumps = $this->list_jumps();
		$list_calcolors = $this->list_calcolors($master_array);

		// Figure out colspans
		$dayborder = 0;
		$thisdate = $start_week_time;
		$swt = $start_week_time;
		$view_array = array ();
		$rowspan_array = array();
		for ($i = 0; $i < 7; $i ++) {
			$thisday = date("Ymd", $thisdate);
			$nbrGridCols[$thisday] = 1;
			if (isset ($master_array[$thisday])) {
				foreach ($master_array[($thisday)] as $ovlKey => $ovlValue) {
					if ($ovlKey != "-1") {
						preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayStart"], $dTimeStart);
						preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayEnd"], $dTimeEnd);
						preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $thisday, $dDate);
						$d_start = mktime($dTimeStart[1], $dTimeStart[2], 0, $dDate[2], $dDate[3], $dDate[1]);
						$d_end = mktime($dTimeEnd[1], $dTimeEnd[2], 0, $dDate[2], $dDate[3], $dDate[1]);		
						foreach ($ovlValue as $ovl2Value) {
							
							$starttime = $ovl2Value->getStarttime() - $ovl2Value->getStarttime() % ($gridLength * 60);
							$endtime = $ovl2Value->getEndtime();

							$rest = ($endtime % ($gridLength * 60));
							$endtime = $endtime - $rest;
							if($rest > 0){
								$endtime = $endtime + ($gridLength * 60);
							}
							$entries = 1;
							$old_day = $thisday;
							$endOfDay = $d_end;
							$d_start = $d_start - $gridLength * 60;
							
							for ($j = $starttime; $j < $endtime; $j = $j + $gridLength * 60) {
								$view_array[date("Ymd",$j)][$j][count($view_array[date("Ymd",$j)][$j])] = $ovl2Value;		
								if($j >= $endOfDay){
									$rowspan_array[$old_day][$ovl2Value->getType()."_".$ovl2Value->getUid()] = $entries-1;
									
									$endOfDay = $endOfDay + 60 * 60 * 24;
									$old_day = date("Ymd",$endOfDay);
									$d_start = $d_start + 60 * 60 * 24;
									$j = $d_start;
									$entries = 0;
								}
								$entries++;
			
								
							}
							$rowspan_array[$old_day][$ovl2Value->getType()."_".$ovl2Value->getUid()] = $entries-1;
						}
					}
				}
				$max = array ();
				if(!empty($view_array[$thisday])){
					foreach ($view_array[$thisday] as $array_time => $time_val) {
						$c = count($view_array[$thisday][$array_time]);
						array_push($max, $c);
					}
					$nbrGridCols[($thisday)] = max($max);
				}else{
					$nbrGridCols[($thisday)] = 1;
				}
			}else{
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
			$thisdate = ($thisdate + (25 * 60 * 60));
		}
//debug($view_array);
		$t_array = array ();
		$pos_array = array ();		
		preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayStart"], $dTimeStart);
		preg_match('/([0-9]{2})([0-9]{2})/', $this->conf["view."]["day."]["dayEnd"], $dTimeEnd);
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
							for ($j = 0; $j < $nbrGridCols[$week_key]; $j ++) {
								if (count($t_array[$week_key][$i][$j]) == 0 || !isset ($t_array[$week_key][$i][$j])) {
									$pos_array[$week_key][$event->getType().$event->getUid()] = $j;
									$t_array[$week_key][$i][$j] = array ("begin" => $event);
									break;
								}
							}
						}
					}

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

		$page = $this->cObj->fileResource($this->conf["view."]["week."]["weekTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->conf["view."]["week."]["weekTemplate"];
		}
		$page = $this->replace_files($page, array (
			'sidebar' => $this->conf["view."]["other."]["sidebarTemplate"],
			'search_box' => $this->conf["view."]["other."]["searchBoxTemplate"])
		);

		if ($this->conf["view."]["other."]["allow_login"] != 'yes') {
			$rems["###SHOW_USER_LOGIN###"] = '';
		}
		if ($this->conf["view."]["other."]["showSearch"] != 'yes') {
			$rems["###SHOW_SEARCH###"] = '';
		}
		if ($this->conf["view."]["other."]["showGoto"] != 'yes') {
			$rems["###SHOW_GOTO###"] = '';
		}
		if ($this->conf["view."]["other."]["showMultiple"] != 'yes') {
			$rems["###SHOW_MULTIPLE###"] = '';
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		$languageArray = array ( 
			'getdate' => $getdate, 
			'display_date' => $display_date, 
			'legend_prev_day' => $legend_prev_day_link, 
			'legend_next_day' => $legend_next_day_link, 
			'next_day_link' => $next_day_link, 
			'next_week_link' => $next_week_link, 
			'prev_day_link' => $prev_day_link, 
			'prev_week_link' => $prev_week_link, 
			'show_goto' => '',
			'calendar_name' => $this->conf["calendarName"],
			'list_icals' => $list_icals, 
			'legend' => $list_calcolors,
			'list_jumps' => $list_jumps,
			'list_years' => $list_years, 
			'list_months' => $list_months, 
			'list_weeks' => $list_weeks,
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
			'l_login' => $this->shared->lang('l_login'), 
			'l_logout' => $this->shared->lang('l_logout'),
		);

		$page = $this->shared->replace_tags($languageArray, $page);
		// Replaces the allday events
		$loop_ad = $this->cObj->getSubpart($page, "###LOOPALLDAY###");
		$loop_begin = $this->cObj->getSubpart($page, "###ALLDAYSOFWEEK_BEGIN###");
		$loop_end = $this->cObj->getSubpart($page, "###ALLDAYSOFWEEK_END###");

		foreach ($weekarray as $get_date) {
			$replace = $loop_begin;
			$colspan = 'colspan="'.($nbrGridCols[$get_date]).'"';

			$sims["###COLSPAN###"] = $this->cObj->substituteMarkerArrayCached($colspan, $replace, array (), array ());
			$replace = $this->cObj->substituteMarkerArrayCached($replace, $sims, array (), array ());
			if (is_array($master_array[$get_date]['0000'])) {

				foreach ($master_array[$get_date]['0000'] as $uid => $allday) {
					$sims["###ALLDAY###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($allday, $allday->renderEventForAllDay(),"week", date("Ymd",$allday->getStarttime())), $loop_ad, array (), array ());
					$sims["###CALNO###"] = $this->cObj->substituteMarkerArrayCached($allday->getHeaderColor(), $loop_ad, array (), array ());

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
//debug($nbrGridCols);
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
			if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
				$link = $this->shared->pi_linkToPage('<span class="V9BOLD">'.$weekday.'</span>', array ($this->prefixId."[getdate]" => $daylink, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
			} else {
				$link = $this->shared->pi_linkToPage('<span class="V9BOLD">'.$weekday.'</span>', array ($this->prefixId."[getdate]" => $daylink, $this->prefixId."[view]" => "day"));
			}
			$start_day = strtotime("+1 day", $start_day);
			$start_wt = strtotime("+1 day", $start_wt);
			$colspan = 'colspan="'.$nbrGridCols[$daylink].'"';
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
//debug($t_array);		
		for($i=0;$i<$loops;$i++){
			for($j=0;$j<7;$j++){
				$daytime = $start_week_time+$j*60*60*24+$d_start_time;
				$day = date("Ymd",$daytime);
				$time = $daytime+$i*$gridLength*60;
				if($j==0){
					$key = date($this->shared->lang('l_timeFormat'),$time);
					if (ereg("([0-9]{1,2}):00", $key)) {
						$weekdisplay .= '<tr>';
						$weekdisplay .= '<td colspan="4" rowspan="'. (60 / $gridLength).'" align="center" valign="top" width="60" class="timeborder">';
						$weekdisplay .= $key.'</td>';
						$weekdisplay .= '<td bgcolor="#a1a5a9" width="1" height="'.$gridLength.'"></td>'."\n";
//					}
//					elseif ($cal_time == $dayStart) {
//						$size_tmp = 60 - (int) substr($cal_time, 2, 2);
//						$weekdisplay .= '<tr>';
//						$weekdisplay .= '<td colspan="4" rowspan="'. ($size_tmp / $gridLength).'" align="center" valign="top" width="60" class="timeborder">'.$key.'</td>';
//						$weekdisplay .= '<td bgcolor="#a1a5a9" width="1" height="'.$gridLength.'"></td>';
					} else {
						$weekdisplay .= '<tr>';
						
						$weekdisplay .= '<td bgcolor="#a1a5a9" width="1" height="'.$gridLength.'"></td>'."\n";
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
									if ($rightsObj->isAllowedToEditEvents()) {
										$editlink = $this->shared->pi_linkToPage('<img src="typo3/gfx/edit2.gif" border="0"/>', array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[lastview]" => "week", $this->prefixId."[view]" => "edit_event", $this->prefixId."[type]" => $event->getType(), $this->prefixId."[uid]" => $event->getUid()), 0, $this->conf["view."]["event."]["edit_eventViewPid"]);
									}
									$rest = $event->getEndtime() % ($gridLength * 60);
									$plus = 0;
									if($rest>0){
										$plus = 1;
									}
									//$rowspan = (($event->getEndtime() - ($rest)) - ($event->getStarttime() - ($event->getStarttime() % ($gridLength * 60)))) / ($gridLength * 60) + $plus;
									$rowspan = $rowspan_array[$day][$event->getType()."_".$event->getUid()];
									$weekdisplay .= '<td rowspan="'.$rowspan.'" colspan="1" align="left" valign="top" class="eventbg2" style="background-color:'.$event->getBodyColor().';color:'.$event->getBodyTextColor().';border:1px solid '.$event->getHeaderColor().'">'."\n";
		
									// Start drawing the event
									$event_temp = $loop_event;
									$sims["###EVENT###"] = $this->cObj->substituteMarkerArrayCached($this->getLinkToEvent($event, $event->renderEventForWeek(),"week", $day), $event_temp, array (), array ());
									$e_start = date($this->shared->lang('l_timeFormat'), $event->getStarttime());
									$sims["###EVENT_START###"] = $editlink.$this->cObj->substituteMarkerArrayCached($e_start, $event_temp, array (), array ());
									$e_end = date($this->shared->lang('l_timeFormat'), $event->getEndtime());
									$sims["###EVENT_END###"] = $this->cObj->substituteMarkerArrayCached($e_end, $event_temp, array (), array ());
									$sims["###CONFIRMED###"] = $this->cObj->substituteMarkerArrayCached($event->getConfirmed(), $event_temp, array (), array ());
									$sims["###EVENT_CALNO###"] = $this->cObj->substituteMarkerArrayCached($event->getHeaderColor(), $event_temp, array (), array ());
									$sims["###EVENT_TEXT_CALNO###"] = $this->cObj->substituteMarkerArrayCached($event->getHeaderTextColor(), $event_temp, array (), array ());
									
									$weekdisplay .= $this->cObj->substituteMarkerArrayCached($event_temp, $sims, array (), array ());
									$weekdisplay .= '</td>'."\n";
									// End event drawing
									break;
								case 'ended' :
									//$weekdisplay .= '<td colspan="'.count($something).'" '.$class.'>&nbsp;</td>'."\n";
									break;
							}
						}
					
						if (count($something) < $nbrGridCols[$day]) {
							$weekdisplay .= '<td style="" colspan="'. (($nbrGridCols[$day] - count($something))).'" '.$class.'>';
							if ($rightsObj->isAllowedToCreateEvents()) {
								$weekdisplay .= $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ($this->prefixId."[getdate]" => $day, $this->prefixId."[gettime]" => date("Hi",$time), $this->prefixId."[lastview]" => "week", $this->prefixId."[view]" => "create_event"), 0, $this->conf["view."]["event."]["create_eventViewPid"]);
							}
							$weekdisplay .= '&nbsp;</td>'."\n";
						}
					}
	
				} else {
					$weekdisplay .= '<td test colspan="'.$nbrGridCols[$day].'" '.$class.'>';
					if ($rightsObj->isAllowedToCreateEvents()) {
						$weekdisplay .= $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ($this->prefixId."[getdate]" => $day, $this->prefixId."[gettime]" => date("Hi",$time), $this->prefixId."[lastview]" => "week", $this->prefixId."[view]" => "create_event"), 0, $this->conf["view."]["event."]["create_eventViewPid"]);
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
		$page = $this->checkForMonthMarker($master_array, $page, $getdate, $rightsObj);
		if ($this->conf["view."]["other."]["showTomorrowEvents"] == 1) {
			$page = $this->tomorrows_events($page, $getdate, $master_array);
		} else {
			$rems["###TOMORROWS_EVENTS###"] = '';
			$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		}
		if ($this->conf["view."]["other."]["show_todos"] == 1) {
			$page = $this->get_vtodo($page, $getdate, $master_array);
		} else {
			$rems["###VTODO###"] = '';
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		return $this->replaceLinksToOtherViews($page, $getdate);
	}

	/**
	 *  Draws a single event.
	 *  @param		array			The events to be drawn.
	 *	 @return		string		The HTML output.
	 */
	function drawMonth(& $cObj, $master_array, $getdate, $rightsObj) {

		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);
		$page = $this->cObj->fileResource($this->conf["view."]["month."]["monthTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->conf["view."]["month."]["monthTemplate"];
		}
		$page = $this->checkForMonthMarker($master_array, $page, $getdate, $rightsObj);
		return $this->replaceLinksToOtherViews($page, $getdate);
	}

	function draw_month($page, $offset = '+0', $type, $master_array, $getdate, $rightsObj) {
	
		$unix_time = strtotime($getdate);

		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		$weekStartDay = $this->conf["view."]["week."]["weekStartDay"];
		$loop_wd = $this->cObj->getSubpart($page, "###LOOPWEEKDAY###");
		$loop_md = $this->cObj->getSubpart($page, "###LOOPMONTHDAYS###");
		$t_month[0] = $this->cObj->getSubpart($page, "###SWITCHNOTTHISMONTH###");
		$t_month[1] = $this->cObj->getSubpart($page, "###SWITCHISTODAY###");
		$t_month[2] = $this->cObj->getSubpart($page, "###SWITCHISMONTH###");
		$startweek = $this->cObj->getSubpart($page, "###LOOPMONTHWEEKS_DAYS###");
		$endweek = $this->cObj->getSubpart($page, "###LOOPMONTHDAYS_WEEKS###");

		if ($type != 'medium') {
			$fake_getdate_time = strtotime($this_year.'-'.$this_month.'-15');

			$fake_getdate_time = strtotime("$offset month", $fake_getdate_time);
		} else {
			$fake_getdate_time = strtotime($this_year.'-'.$offset.'-15');
			//$fake_getdate_time 	= strtotime($this_year.'-'.$this_month.'-15');
			//$fake_getdate_time 	= strtotime("$offset month", $fake_getdate_time);
		}
		$minical_month = date("m", $fake_getdate_time);
		$minical_year = date("Y", $fake_getdate_time);
		$first_of_month = $minical_year.$minical_month."01";
		$first_of_year = $minical_year."0101";
		$start_day = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($getdate), $weekStartDay, $weekStartDay));
		$month_title = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_month'), $fake_getdate_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
		$month_date = date('Ymd', $fake_getdate_time);
		
		for ($i = 0; $i < 31; $i++) {
			$thisday = $first_of_month + $i;
			if (isset ($master_array[$thisday])) {
				foreach ($master_array[($thisday)] as $ovlKey => $ovlValue) {
					if ($ovlKey != "-1") {
						foreach ($ovlValue as $ovl2Value) {							
							$starttime = $ovl2Value->getStarttime();
							if(date("Ymd",$starttime)>=$thisday){
								$endtime = $ovl2Value->getEndtime();
	
								for ($j = $starttime + 60 * 60 * 24; $j < $endtime; $j = $j + 60 * 60 * 24) {
									$master_array[date("Ymd",$j)]["0000"][count($master_array[date("Ymd",$j)]["0000"])]=$ovl2Value;
								}
							}
						}
					}
				}
			}
		}
		
		$icslink = "";
		if ($this->conf["view."]["ics."]['showIcsLinks'] == 1) {
			$icslink = $this->shared->pi_linkToPage($this->shared->lang('l_calendar_icslink'), array ($this->prefixId."[type]" => "phpicalendar", $this->prefixId."[view]" => "ics", "type" => "150"));
		}

		$languageArray = array (
			'month_title' => $month_title, 
			'icslink' => $icslink,
		);

		$page = $this->shared->replace_tags($languageArray, $page);

		if ($type == 'small') {
			$langtype = $this->shared->getDaysOfWeekReallyShort;
		}
		elseif ($type == 'medium') {
			$langtype = $this->shared->getDaysOfWeekShort();
		}
		elseif ($type == 'large') {
			$langtype = $this->shared->getDaysOfWeek();
		}

		for ($i = 0; $i < 7; $i ++) {
			$day_num = date("w", $start_day);
			$weekday = $langtype[$day_num];
			$start_day = strtotime("+1 day", $start_day);
			$loop_tmp = str_replace('###WEEKDAY###', $weekday, $loop_wd);
			$weekday_loop .= $loop_tmp;
		}
		$start_day = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay));

		$i = 0;
		$whole_month = TRUE;
		$isAllowedToCreateEvents = $rightsObj->isAllowedToCreateEvents();
		do {

			if ($i == 0)
				$middle .= $startweek;
			$i ++;

			$switch = array ('ALLDAY' => '');
			$check_month = date("m", $start_day);
			$daylink = date("Ymd", $start_day);
			if ($isAllowedToCreateEvents) {
				$switch['LINK'] = $this->shared->pi_linkToPage('<img src="'.t3lib_extMgm :: siteRelPath('cal').'template/img/add.gif" border="0"/>', array ($this->prefixId."[getdate]" => date("Ymd", $start_day), $this->prefixId."[lastview]" => "month", $this->prefixId."[view]" => "create_event"), 0, $this->conf["create_eventViewPid"]);
			} else {
				$switch['LINK'] = "";
			}
			if (!empty ($this->conf["dayViewPid"])) {
				$switch['LINK'] .= $this->shared->pi_linkToPage(date("j", $start_day), array ($this->prefixId."[getdate]" => date("Ymd", $start_day), $this->prefixId."[view]" => "day"), 0, $this->conf["dayViewPid"]);
			} else {
				$switch['LINK'] .= $this->shared->pi_linkToPage(date("j", $start_day), array ($this->prefixId."[getdate]" => date("Ymd", $start_day), $this->prefixId."[view]" => "day"));
			}

			if ($check_month != $minical_month) {
				$temp = $t_month[0];
			}
			elseif ($daylink == $getdate) {
				$temp = $t_month[1];
			} else {
				$temp = $t_month[2];
			}

			if ($master_array[$daylink]) {
				foreach ($master_array[$daylink] as $cal_time => $event_times) {
					foreach ($event_times as $uid => $val) {
						if ($val->getStartHour() == '') {
							if ($type == 'large') {
								$switch['ALLDAY'] .= '<div class="V10"><ul style="color:'.$val->getHeaderColor().'"><li><span style="color:#000000">';
								if ($rightsObj->isAllowedToEditEvents()) {
									$switch['ALLDAY'] .= '<img src="typo3/gfx/edit2.gif" />';
								}
								$switch['ALLDAY'] .= $val->renderEventForMonth();
								$switch['ALLDAY'] .= '</span></li></ul></div>';
							}else if ($type == 'small' && !$isAllowedToCreateEvents){
								$switch['LINK'] = '<span class="bold_link">'.$switch['LINK'].'</span>';
							} else {
								$switch['ALLDAY'] .= '<ul style="color:'.$val->getHeaderColor().'"><li></li></ul>';
							}
						} else {
							$start2 = date($this->shared->lang('l_timeFormat_small'), $val->getStarttime());
							if ($type == 'large') {
								$switch['EVENT'] .= '<div class="V9"><ul style="color:'.$val->getHeaderColor().'"><li><span style="color:#000000">';
								$switch['EVENT'] .= $this->getLinkToEvent($val, $val->renderEventForMonth(),"month", $daylink).'<br />';
								$switch['EVENT'] .= '</span></li></ul></div>';
							}else if ($type == 'small' && !$isAllowedToCreateEvents){
								$switch['LINK'] = '<span class="bold_link">'.$switch['LINK'].'</span>';
							} else {
								$switch['EVENT'] = '<img src="'.t3lib_extMgm :: siterelpath('cal').'template/img/event_dot.gif" alt=" " width="11" height="10" border="0" />';
							}
						}
					}
				}
			}

			$switch['EVENT'] = (isset ($switch['EVENT'])) ? $switch['EVENT'] : '';
			$switch['ALLDAY'] = (isset ($switch['ALLDAY'])) ? $switch['ALLDAY'] : '';


			foreach ($switch as $tag => $data) {
				$temp = str_replace('###'.$tag.'###', $data, $temp);
			}
			$middle .= $temp;

			$start_day = strtotime("+1 day", $start_day);
			if ($i == 7) {
				$i = 0;
				$middle .= $endweek;
				$checkagain = date("m", $start_day);
				if ($checkagain != $minical_month)
					$whole_month = FALSE;
			}
		} while ($whole_month == TRUE);

		$rems["###LOOPWEEKDAY###"] = $weekday_loop;
		$rems["###LOOPMONTHWEEKS###"] = $middle;
		$return = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$month_link = $this->shared->pi_linkToPage($month_title, array ($this->prefixId."[getdate]" => $month_date, $this->prefixId."[view]" => "month"), 0, $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$month_link = $this->shared->pi_linkToPage($month_title, array ($this->prefixId."[getdate]" => $month_date, $this->prefixId."[view]" => "month"));
		}
		$return = str_replace('###MONTH_LINK###', $month_link, $return);
		return $return;
	}

	function nomonthbottom($template) {
		$rems["###SHOWBOTTOMEVENTS###"] = '';
		return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
	}

	function monthbottom($template, $master_array, $getdate) {
		$unix_time = strtotime($getdate);

		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		$loop[0] = $this->cObj->getSubpart($template, "###SHOWBOTTOMEVENTS_ODD###");
		$loop[1] = $this->cObj->getSubpart($template, "###SHOWBOTTOMEVENTS_EVEN###");

		$m_start = $this_year.$this_month.'01';
		$u_start = strtotime($m_start);
		$i = 0;
		do {
			if (isset ($master_array[$m_start])) {

				foreach ($master_array[$m_start] as $cal_time => $event_times) {

					foreach ($event_times as $uid => $val) {
						$switch['CALNAME'] = $val->getCategory();
						if ($val->getEndtime() == '0' || $val->getStarttime() == $val->getEndtime()) {
							$switch['START_TIME'] = $this->shared->lang('l_all_day');
							$switch['EVENT_TEXT'] = $this->getLinkToEvent($val, $val->renderEventForMonth(),"month", date("Ymd",$val->getStarttime()));
							$switch['DESCRIPTION'] = $val->getContent();
						} else {
							$event_start = date("H:i", $val->getStarttime());
							$event_end = date("H:i", $val->getEndtime());
							$switch['START_TIME'] = $event_start.' - '.$event_end;
							$switch['EVENT_TEXT'] = $this->getLinkToEvent($val, $val->renderEventForMonth(),"month", date("Ymd",$val->getStarttime()));
							$switch['DESCRIPTION'] = $val->getContent();
						}

						if ($switch['EVENT_TEXT'] != '') {
							$dateString = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week_list'), $u_start, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
							if ($this->conf["view"] == "list") {
								$switch['DAYLINK'] = $dateString;
							} else {
								if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
									$switch['DAYLINK'] = $this->shared->pi_linkToPage($dateString, array ($this->prefixId."[getdate]" => $m_start, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
								} else {
									$switch['DAYLINK'] = $this->shared->pi_linkToPage($dateString, array ($this->prefixId."[getdate]" => $m_start, $this->prefixId."[view]" => "day"));
								}
							}

							$temp = $loop[$i];
							foreach ($switch as $tag => $data) {
								$temp = str_replace('###'.$tag.'###', $data, $temp);
							}
							$middle .= $temp;
							$i = ($i == 1) ? 0 : 1;
						}
					}
				}
			}
			$u_start = strtotime("+1 day", $u_start);
			$m_start = date('Ymd', $u_start);
			$check_month = date('m', $u_start);
			unset ($switch);
		} while ($this_month == $check_month);
		$rems["###SHOWBOTTOMEVENTS###"] = $middle;
		return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());

	}

	/**
	 *  Draws a single event.
	 *  @param		array			The events to be drawn.
	 *	 @return		string		The HTML output.
	 */
	function drawYear($cObj, $master_array, $getdate, $rightsObj) {
		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);

		$page = $this->cObj->fileResource($this->conf["view."]["year."]["yearTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no template file found:</h3>".$this->conf["view."]["year."]["yearTemplate"];
		}
		$page = $this->checkForMonthMarker($master_array, $page, $getdate, $rightsObj);
		return $this->replaceLinksToOtherViews($page, $getdate);

	}

	function list_jumps() {
		$today = date('Ymd', time());
		$return = '<option value="#">'.$this->shared->lang('l_jump').'</option>';
		if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "day",), 0, $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "day",));
		}
		$link = preg_split('/\"/', $link);
		$goday = $this->shared->lang('l_goday');
		$return .= "<option value=\"$link[1]\">$goday</option>\n";
		if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "week",), 0, $this->conf["view."]["week."]["weekViewPid"]);
		} else {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "week",));
		}
		$link = preg_split('/\"/', $link);
		$goweek = $this->shared->lang('l_goweek');
		$return .= "<option value=\"$link[1]\">$goweek</option>\n";
		if (!empty ($this->conf["view."]["nonth."]["monthViewPid"])) {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "month",), 0, $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "month",));
		}
		$link = preg_split('/\"/', $link);
		$gomonth = $this->shared->lang('l_gomonth');
		$return .= "<option value=\"$link[1]\">$gomonth</option>\n";
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "year",), 0, $this->conf["view."]["year."]["year_view_id"]);
		} else {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $today, $this->prefixId."[view]" => "year",));
		}
		$link = preg_split('/\"/', $link);
		$goyear = $this->shared->lang('l_goyear');
		$return .= "<option value=\"$link[1]\">$goyear</option>\n";
		return $return;
	}

	function list_calcolors($master_array) {
		$i = 1;
		if (is_array($master_array['legend'])) {
			foreach ($master_array['legend'] as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$return .= '<div class="V9"><ul style="color:'.$key2.'"><li><span style="color:#000000">'.$val2.'</span></li></ul></div>';
					}
				} else {
					if ($i > 7) {
						$i = 1;
					} else {
						$i = $key;
					}
					$val = str_replace("\,", ",", $val);
					$return .= '<img src="'.t3lib_extMgm :: siterelpath('cal').'template/img/monthdot_'.$i.'.gif" alt="" /> '.$val.'<br />';
				}
				$i ++;
			}
		}
		return $return;
	}

	function list_months($getdate, $this_year, $cal, $dateFormat_month) {
		$month_time = strtotime("$this_year-01-01");
		$getdate_month = date("m", strtotime($getdate));
		for ($i = 0; $i < 12; $i ++) {
			$monthdate = date("Ymd", $month_time);
			$month_month = date("m", $month_time);
			$select_month = tx_cal_calendar :: localizeDate($dateFormat_month, $month_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $monthdate, $this->prefixId."[view]" => "month",), 0, $this->conf["view."]["month."]["monthViewPid"]);
			} else {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $monthdate, $this->prefixId."[view]" => "month",));
			}
			$link = preg_split('/\"/', $link);
			if ($month_month == $getdate_month) {
				$return .= "<option value=\"$link[1]\" selected=\"selected\">$select_month</option>\n";
			} else {
				$return .= "<option value=\"$link[1]\">$select_month</option>\n";
			}
			$month_time = strtotime("+1 month", $month_time);
		}
		return $return;
	}

	function list_years($getdate, $this_year, $cal, $num_years) {
		$year_time = strtotime($getdate);
		for ($i = 0; $i < $num_years; $i ++) {
			$offset = $num_years - $i;
			$prev_time = strtotime("-$offset year", $year_time);
			$prev_date = date("Ymd", $prev_time);
			$prev_year = date("Y", $prev_time);
			if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $prev_date, $this->prefixId."[view]" => "year",), 0, $this->conf["view."]["year."]["year_view_id"]);
			} else {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $prev_date, $this->prefixId."[view]" => "year",));
			}
			$link = preg_split('/\"/', $link);
			$return .= "<option value=\"$link[1]\">$prev_year</option>\n";
		}

		$getdate_date = date("Ymd", $year_time);
		$getdate_year = date("Y", $year_time);
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $getdate_date, $this->prefixId."[view]" => "year",), 0, $this->conf["view."]["year."]["year_view_id"]);
		} else {
			$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $getdate_date, $this->prefixId."[view]" => "year",));
		}
		$link = preg_split('/\"/', $link);
		$return .= "<option value=\"$link[1]\" selected=\"selected\">$getdate_year</option>\n";

		for ($i = 0; $i < $num_years; $i ++) {
			$offset = $i +1;
			$next_time = strtotime("+$offset year", $year_time);
			$next_date = date("Ymd", $next_time);
			$next_year = date("Y", $next_time);
			if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $next_date, $this->prefixId."[view]" => "year",), 0, $this->conf["view."]["year."]["year_view_id"]);
			} else {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $next_date, $this->prefixId."[view]" => "year",));
			}
			$link = preg_split('/\"/', $link);
			$return .= "<option value=\"$link[1]\">$next_year</option>\n";
		}

		return $return;
	}

	function list_weeks($getdate, $this_year, $cal, $dateFormat_week_jump, $weekStartDay) {
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$check_week = strtotime($getdate);
		$start_week_time = strtotime(tx_cal_calendar :: dateOfWeek(strtotime("$this_year-01-01"), $weekStartDay, $weekStartDay));
		$end_week_time = $start_week_time + (6 * 25 * 60 * 60);

		do {
			$weekdate = date("Ymd", $start_week_time);
			$select_week1 = tx_cal_calendar :: localizeDate($dateFormat_week_jump, $start_week_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
			$select_week2 = tx_cal_calendar :: localizeDate($dateFormat_week_jump, $end_week_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());

			if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $weekdate, $this->prefixId."[view]" => "week",), 0, $this->conf["view."]["week."]["weekViewPid"]);
			} else {
				$link = $this->shared->pi_linkToPage("", array ($this->prefixId."[getdate]" => $weekdate, $this->prefixId."[view]" => "week",));
			}
			$link = preg_split('/\"/', $link);
			if (($check_week >= $start_week_time) && ($check_week <= $end_week_time)) {

				$return .= "<option value=\"$link[1]\" selected=\"selected\">$select_week1 - $select_week2</option>\n";
			} else {
				$return .= "<option value=\"$link[1]\">$select_week1 - $select_week2</option>\n";
			}
			$start_week_time = strtotime("+1 week", $start_week_time);
			$end_week_time = $start_week_time + (6 * 25 * 60 * 60);
		} while (date("Y", $start_week_time) <= $this_year);

		return $return;
	}

	function list_languages($getdate, $cal, $current_view) {
		$dir_handle = @ opendir(BASE.'languages/');
		$tmp_pref_language = urlencode(ucfirst($language));
		while ($file = readdir($dir_handle)) {
			if (substr($file, -8) == ".inc.php") {
				$language_tmp = urlencode(ucfirst(substr($file, 0, -8)));
				if ($language_tmp == $tmp_pref_language) {
					$return .= "<option value=\"$current_view.php?chlang=$language_tmp\" selected=\"selected\">in $language_tmp</option>\n";
				} else {
					$return .= "<option value=\"$current_view.php?chlang=$language_tmp\">in $language_tmp</option>\n";
				}
			}
		}
		closedir($dir_handle);

		return $return;
	}

	function replaceLinksToOtherViews($template, $getdate) {
		$dayviewlinktext = $this->cObj->getSubpart($template, "###DAYVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
			$rems["###DAYVIEWLINK###"] = $this->shared->pi_linkToPage($dayviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "day"), 0, $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$rems["###DAYVIEWLINK###"] = $this->shared->pi_linkToPage($dayviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "day"));
		}
		$weekviewlinktext = $this->cObj->getSubpart($template, "###WEEKVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
			$rems["###WEEKVIEWLINK###"] = $this->shared->pi_linkToPage($weekviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "week"), 0, $this->conf["view."]["week."]["weekViewPid"]);
		} else {
			$rems["###WEEKVIEWLINK###"] = $this->shared->pi_linkToPage($weekviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "week"));
		}
		$monthviewlinktext = $this->cObj->getSubpart($template, "###MONTHVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$rems["###MONTHVIEWLINK###"] = $this->shared->pi_linkToPage($monthviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "month"), 0, $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$rems["###MONTHVIEWLINK###"] = $this->shared->pi_linkToPage($monthviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "month"));
		}
		$yearviewlinktext = $this->cObj->getSubpart($template, "###YEARVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$rems["###YEARVIEWLINK###"] = $this->shared->pi_linkToPage($yearviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "year"), 0, $this->conf["view."]["year."]["year_view_id"]);
		} else {
			$rems["###YEARVIEWLINK###"] = $this->shared->pi_linkToPage($yearviewlinktext, array ($this->prefixId."[getdate]" => $getdate, $this->prefixId."[view]" => "year"));
		}
		$return = $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		$return = str_replace("###IMG_PATH###", t3lib_extMgm :: siterelpath('cal')."template/img", $return);
		return $return;
	}

	function nocalendar_nav($template) {
		return str_replace('###CALENDAR_NAV###', '', $template);
	}

	function tomorrows_events($template, $getdate, $master_array) {

		// , $next_day, $timeFormat, $tomorrows_events_lines
		$unix_time = strtotime($getdate);

		$next_day = date('Ymd', strtotime("+1 day", $unix_time));

		$match1 = $this->cObj->getSubpart($template, "###T_ALLDAY_SWITCH###");
		$match2 = $this->cObj->getSubpart($template, "###T_EVENT_SWITCH###");
		$loop_t_ad = trim($match1);
		$loop_t_e = trim($match2);
		$return_adtmp = '';
		$return_etmp = '';
		if (is_array($master_array[$next_day]) && sizeof($master_array[$next_day]) > 0) {
			foreach ($master_array[$next_day] as $cal_time => $event_times) {
				foreach ($event_times as $uid => $val) {
					$event_text = stripslashes(urldecode($val->getTitle()));
					$event_text = strip_tags($event_text, '<b><i><u>');
					if ($event_text != "") {
						if ($val->getStartHour() == '') {
							$return_adtmp = $val->getTitle();
							$replace_ad .= str_replace('###T_ALLDAY###', $return_adtmp, $loop_t_ad);
						} else {
							$return_etmp = $val->getTitle();
							$replace_e .= str_replace('###T_EVENT###', $return_etmp, $loop_t_e);
						}
					}
				}
			}

			$rems["###T_ALLDAY_SWITCH###"] = $replace_ad;
			$rems["###T_EVENT_SWITCH###"] = $replace_e;
			return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());

		} else {

			$rems["###T_EVENT_SWITCH###"] = '';
			return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		}
	}

	function get_vtodo($template, $getdate, $master_array, $next_day, $timeFormat, $tomorrows_events_lines, $show_completed, $show_todos) {

		preg_match("!<\!-- switch show_completed on -->(.*)<\!-- switch show_completed off -->!is", $this->page, $match1);
		preg_match("!<\!-- switch show_important on -->(.*)<\!-- switch show_important off -->!is", $this->page, $match2);
		preg_match("!<\!-- switch show_normal on -->(.*)<\!-- switch show_normal off -->!is", $this->page, $match3);
		$completed = trim($match1[1]);
		$important = trim($match2[1]);
		$normal = trim($match3[1]);
		$nugget2 = '';

		if (is_array($master_array['-2'])) {
			foreach ($master_array['-2'] as $vtodo_times) {
				foreach ($vtodo_times as $val) {
					$vtodo_text = stripslashes(urldecode($val["vtodo_text"]));
					if ($vtodo_text != "") {
						if (isset ($val["description"])) {
							$description = stripslashes(urldecode($val["description"]));
						} else {
							$description = "";
						}
						$completed_date = $val['completed_date'];
						$event_calna = $val['calname'];
						$status = $val["status"];
						$priority = $val['priority'];
						$start_date = $val["start_date"];
						$due_date = $val['due_date'];
						$vtodo_array = array ('cal' => $event_calna, 'completed_date' => $completed_date, 'description' => $description, 'due_date' => $due_date, 'priority' => $priority, 'start_date' => $start_date, 'status' => $status, 'vtodo_text' => $vtodo_text);

						$vtodo_array = base64_encode(serialize($vtodo_array));
						$vtodo_text = word_wrap(strip_tags(str_replace('<br />', ' ', $vtodo_text), '<b><i><u>'), 21, $tomorrows_events_lines);
						$data = array ('{VTODO_TEXT}', '{VTODO_ARRAY}');
						$rep = array ($vtodo_text, $vtodo_array);

						// Reset this TODO's category.
						$temp = '';
						if ($status == 'COMPLETED' || (isset ($val['completed_date']) && isset ($val['completed_time']))) {
							if ($show_completed == 'yes') {
								$temp = $completed;
							}
						}
						elseif (isset ($val['priority']) && ($val['priority'] != 0) && ($val['priority'] <= 5)) {
							$temp = $important;
						} else {
							$temp = $normal;
						}

						// Do not include TODOs which do not have the
						// category set.
						if ($temp != '') {
							$nugget1 = str_replace($data, $rep, $temp);
							$nugget2 .= $nugget1;
						}
					}
				}
			}
		}

		// If there are no TODO items, completely hide the TODO list.
		if (($nugget2 == '') || ($show_todos != 'yes')) {
			$this->page = preg_replace('!<\!-- switch vtodo on -->(.*)<\!-- switch vtodo off -->!is', '', $this->page);
		}

		// Otherwise display the list of TODOs.
		else {
			$this->page = preg_replace('!<\!-- switch show_completed on -->(.*)<\!-- switch show_normal off -->!is', $nugget2, $this->page);
		}
	}

	function getLinkToEvent($event, $linktext, $currentView, $date) {
		/* new */
		// create the link if the event points to a page or external URL
		if($event->event_type != 0){

			// determine the link type
			switch ($event->event_type) {
				// shortcut to page - create the link
				case 1:
					$param = $event->page;
					break;
				// external url
				case 2:
					$param = $event->ext_url;
					break;
			}
			
			// create & return the link
			$linkTSConfig['parameter'] = $param;
			return $this->cObj->typoLink($linktext,$linkTSConfig);			
		}		
		/* new */
		if (!empty ($this->conf["view."]["event."]["eventViewPid"])) {
			return $this->shared->pi_linkToPage($linktext, array ($this->prefixId."[page_id]" => t3lib_div :: _GP("id"), $this->prefixId."[getdate]" => $date, $this->prefixId."[lastview]" => $this->cObj->conf['view'], $this->prefixId."[view]" => "event", $this->prefixId."[type]" => $event->getType(), $this->prefixId."[uid]" => $event->getUid()), 0, $this->conf["view."]["event."]["eventViewPid"]);
		}
		return $this->shared->pi_linkToPage($linktext, array ($this->prefixId."[getdate]" => $date, $this->prefixId."[lastview]" => $currentView, $this->prefixId."[view]" => "event", $this->prefixId."[type]" => $event->getType(), $this->prefixId."[uid]" => $event->getUid()));
	}

	function replace_files($page, $tags = array ()) {
		if (sizeof($tags) > 0)
			foreach ($tags as $tag => $data) {

				// This opens up another template and parses it as well.
				$data = (file_exists($data)) ? $this->cObj->fileResource($data) : $data;
				// This removes any unfilled tags
				if (!$data) {
					$page = preg_replace('!<\!-- ###'.$tag.'### start -->(.*)<\!-- ###'.$tag.'### end -->!is', '', $data);
				}

				// This replaces any tags
				$page = str_replace('###'.strtoupper($tag).'###', $data, $page);
			} else {
			//die('No tags designated for replacement.');
		}
		return $page;
	}

	function checkForMonthMarker($master_array, $page, $getdate, $rightsObj) {

		$page = $this->nocalendar_nav($page);
		preg_match_all('!\###MONTH_([A-Z]*)\|?([+|-])([0-9]{1,2})\###!is', $page, $match);
		if (sizeof($match) > 0) {
			$i = 0;
			foreach ($match[1] as $key => $val) {
				if ($match[1][$i] == 'SMALL') {
					$template_file = $this->cObj->fileResource($this->conf["view."]["month."]["monthSmallTemplate"]);
					$type = 'small';
					$offset = $match[2][$i].$match[3][$i];
				}
				elseif ($match[1][$i] == 'MEDIUM') {
					$template_file = $this->cObj->fileResource($this->conf["view."]["month."]["monthMediumTemplate"]);
					$type = 'medium';
					$offset = $match[3][$i];
				} else {
					$template_file = $this->cObj->fileResource($this->conf["view."]["month."]["monthLargeTemplate"]);
					$type = 'large';
					$offset = $match[2][$i].$match[3][$i];
				}
				$data = $this->draw_month($template_file, $offset, $type, $master_array, $getdate, $rightsObj);
				$page = str_replace($match[0][$i], $data, $page);
				$i ++;
			}
		}

		$unix_time = strtotime($getdate);
		$display_date = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_month'), $unix_time, $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());

		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		$next_year = strtotime("+1 year", strtotime($getdate));
		$next_year = date("Ymd", $next_year);
		$prev_year = strtotime("-1 year", strtotime($getdate));
		$prev_year = date("Ymd", $prev_year);

		$today_today = date('Ymd', time());

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
		$nextmonthlinktext = $this->cObj->getSubpart($page, "###NEXT_MONTHLINKTEXT###");
		$prevmonthlinktext = $this->cObj->getSubpart($page, "###PREV_MONTHLINKTEXT###");

		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$rems["###NEXT_MONTHLINK###"] = $this->shared->pi_linkToPage($nextmonthlinktext, array ($this->prefixId."[getdate]" => $next_month, $this->prefixId."[view]" => "month"), 0, $this->conf["monthViewPid"]);
			$rems["###PREV_MONTHLINK###"] = $this->shared->pi_linkToPage($prevmonthlinktext, array ($this->prefixId."[getdate]" => $prev_month, $this->prefixId."[view]" => "month"), 0, $this->conf["monthViewPid"]);
		} else {
			$rems["###NEXT_MONTHLINK###"] = $this->shared->pi_linkToPage($nextmonthlinktext, array ($this->prefixId."[getdate]" => $next_month, $this->prefixId."[view]" => "month"));
			$rems["###PREV_MONTHLINK###"] = $this->shared->pi_linkToPage($prevmonthlinktext, array ($this->prefixId."[getdate]" => $prev_month, $this->prefixId."[view]" => "month"));
		}

		$prevyearlinktext = $this->cObj->getSubpart($page, "###PREV_YEARLINKTEXT###");
		$nextyearlinktext = $this->cObj->getSubpart($page, "###NEXT_YEARLINKTEXT###");
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$rems["###PREV_YEARLINK###"] = $this->shared->pi_linkToPage($prevyearlinktext, array ($this->prefixId."[getdate]" => $prev_year, $this->prefixId."[view]" => "year"), 0, $this->conf["year_view_id"]);
			$rems["###NEXT_YEARLINK###"] = $this->shared->pi_linkToPage($nextyearlinktext, array ($this->prefixId."[getdate]" => $next_year, $this->prefixId."[view]" => "year"), 0, $this->conf["year_view_id"]);
		} else {
			$rems["###PREV_YEARLINK###"] = $this->shared->pi_linkToPage($prevyearlinktext, array ($this->prefixId."[getdate]" => $prev_year, $this->prefixId."[view]" => "year"));
			$rems["###NEXT_YEARLINK###"] = $this->shared->pi_linkToPage($nextyearlinktext, array ($this->prefixId."[getdate]" => $next_year, $this->prefixId."[view]" => "year"));
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		$parse_month = date("Ym", $unix_time);
		$first_of_month = $this_year.$this_month."01";
		$weekStartDay = $this->conf["view."]["week."]["weekStartDay"];
		$start_month_day = tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay);

		$num_of_events2 = 0;

		$icslink = "";
		if ($this->conf["view."]["ics."]["showIcsLinks"] == 1) {
			$icslink = $this->shared->pi_linkToPage($this->shared->lang('l_calendar_icslink'), array ($this->prefixId."[type]" => "tx_cal_phpicalendar", $this->prefixId."[view]" => "ics", "type" => "150"));
		}

		$languageArray = array (
			'getdate' => $getdate,
			'display_date' => $display_date, 
			'next_month' => $next_month, 
			'prev_month' => $prev_month,  
			'l_calendar' => $this->shared->lang('l_calendar'),
			'calendar_name' => $this->conf["calendarName"],
			'l_legend' => $this->shared->lang('l_legend'), 
			'l_tomorrows' => $this->shared->lang('l_tomorrows'), 
			'l_jump' => $this->shared->lang('l_jump'), 
			'l_todo' => $this->shared->lang('l_todo'), 
			'l_day' => $this->shared->lang('l_day'), 
			'l_week' => $this->shared->lang('l_week'), 
			'l_month' => $this->shared->lang('l_month'), 
			'l_year' => $this->shared->lang('l_year'), 
			'l_prev' => $this->shared->lang('l_prev'), 
			'l_next' => $this->shared->lang('l_next'), 
			'l_subscribe' => $this->shared->lang('l_subscribe'), 
			'l_download' => $this->shared->lang('l_download'), 
			'l_this_months' => $this->shared->lang('l_this_months'), 
			'this_year' => $this_year, 
			'next_year' => $next_year, 
			'prev_year' => $prev_year, 
			'l_search' => $this->shared->lang('l_search'), 
			'l_powered_by' => $this->shared->lang('l_powered_by'), 
			'l_this_site_is' => $this->shared->lang('l_this_site_is'), 
			'l_invalid_login' => $this->shared->lang('l_invalid_login'), 
			'l_username' => $this->shared->lang('l_username'), 
			'l_password' => $this->shared->lang('l_password'), 
			'icslink' => $icslink,
		);

		$page = $this->shared->replace_tags($languageArray, $page);
		$thisMonthsEvents = $this->conf["view."]["month."]["thisMonthsEvents"];
		if ($thisMonthsEvents == 1) {
			$page = $this->monthbottom($page, $master_array, $getdate);
		} else {
			$page = $this->nomonthbottom($page);
		}
		return $page;
	}

	function drawList($cObj, $master_array, $getdate) {
		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);

		$page = $this->cObj->fileResource($this->conf["view."]["list."]["listTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no list template file found:</h3>".$this->conf["view."]["list."]["listTemplate"];
		}
		$loop[0] = $this->cObj->getSubpart($page, "###SHOWBOTTOMEVENTS_ODD###");
		$loop[1] = $this->cObj->getSubpart($page, "###SHOWBOTTOMEVENTS_EVEN###");
		$i = 0;
		$starttime = date("Ymd", strtotime($this->conf["view."]["list."]['starttime']));
		$endtime = date("Ymd", strtotime($this->conf["view."]["list."]['endtime']));
		$count = 0;
		foreach ($master_array as $cal_time => $event_times) {

			if (is_array($event_times) && $cal_time >= $starttime && $cal_time <= $endtime) {
				foreach ($event_times as $a_key => $a) {
					if (is_array($a)) {
						foreach ($a as $uid => $val) {
							$switch['CALNAME'] = $val->getCategory();
							if ($val->getEndtime() == '0' || $val->getStarttime() == $val->getEndtime()) {
								$switch['START_TIME'] = $this->shared->lang('l_all_day');
								$switch['EVENT_TEXT'] = $this->getLinkToEvent($val, $val->renderEventForMonth(),"month", date("Ymd",$val->getStarttime()));
								$switch['DESCRIPTION'] = $val->getContent();
							} else {
								$event_start = date("H:i", $val->getStarttime());
								$event_end = date("H:i", $val->getEndtime());
								$switch['START_TIME'] = $event_start.' - '.$event_end;
								$switch['EVENT_TEXT'] = $this->getLinkToEvent($val, $val->renderEventForMonth(),"month", date("Ymd",$val->getStarttime()));
								$switch['DESCRIPTION'] = $val->getContent();
							}

							if ($switch['EVENT_TEXT'] != '') {
								$dateString = tx_cal_calendar :: localizeDate($this->shared->lang('l_dateFormat_week_list'), $val->getStarttime(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());

								$switch['DAYLINK'] = $dateString;

								$temp = $loop[$i];
								foreach ($switch as $tag => $data) {
									$temp = str_replace('###'.$tag.'###', $data, $temp);
								}
								$middle .= $temp;
								$i = ($i == 1) ? 0 : 1;
							}
							$count ++;
							if ($count == $this->conf['maxEvents']) {
								$rems["###SHOWBOTTOMEVENTS###"] = $middle;
								return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
							}
						}
					}
				}
			}
		}
		$rems["###SHOWBOTTOMEVENTS###"] = $middle;
		return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
	}

	function drawIcs($cObj, $master_array, $getdate) {
		$this->local_pibase = t3lib_div :: makeInstance('tslib_pibase'); // Local pibase.
		$this->cObj = $cObj; // cObj
		$this->conf = $this->cObj->conf;
		$tx_cal_shared = t3lib_div :: makeInstanceClassName('tx_cal_shared');
		$this->shared = new $tx_cal_shared ($this->cObj);

		$page = $this->cObj->fileResource($this->conf["view."]["ics."]["icsTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no ics template file found:</h3>".$this->conf["view."]["ics."]["icsTemplate"];
		}
		$ics_events = "";
		$calUid = $this->conf["view."]["ics."]['calUid'];
		foreach($master_array as $eventDate => $eventTimeArray){
			foreach ($eventTimeArray as $key => $event) {
				if (is_object($event)) {
					$eventTemplate = $this->cObj->getSubpart($page, "###EVENT###");
					$sims = array ();
					$sims['###UID###'] = $this->cObj->substituteMarkerArrayCached($calUid, '###UID###', array (), array ());
					$sims['###SUMMARY###'] = $this->cObj->substituteMarkerArrayCached($event->getTitle(), '###SUMMARY###', array (), array ());
					$sims['###DESCRIPTION###'] = $this->cObj->substituteMarkerArrayCached(ereg_replace(chr(10), '\n', $event->getDescription()), '###DESCRIPTION###', array (), array ());
					$sims['###LOCATION###'] = $this->cObj->substituteMarkerArrayCached($event->getLocation(), '###LOCATION###', array (), array ());
					if ($event->getStarttime() == $event->getEndtime() || $event->getEndtime() == 0) {
						$sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = $this->cObj->substituteMarkerArrayCached("DTSTART;VALUE=DATE:".date("Ymd", $event->getStarttime()), '###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###', array (), array ());
					} else {
						$sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = $this->cObj->substituteMarkerArrayCached("DTSTART:".date("Ymd", $event->getStarttime())."T".date("Hi", $event->getStarttime())."00", '###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###', array (), array ());
					}
					$sims['###DTEND_YEAR_MONTH_DAY###'] = $this->cObj->substituteMarkerArrayCached(date("Ymd", $event->getEndtime()), '###DTEND_YEAR_MONTH_DAY###', array (), array ());
					$sims['###DTEND_HOUR_MINUTE_SECOND###'] = $this->cObj->substituteMarkerArrayCached(date("Hi", $event->getEndtime()), '###DTEND_HOUR_MINUTE_SECOND###', array (), array ());
					$rrule = "RRULE:".$this->getRrule($event);
					$sims['###RRULE###'] = $this->cObj->substituteMarkerArrayCached($rrule, '###RRULE###', array (), array ());
					$exdates = "";
					$exrule = "";
					foreach ($event->getExceptionEvents() as $ex_event) {
						if ($ex_event->getFreq() == "none") {
							$exdates .= "EXDATE:".date("Ymd", $ex_event->getStarttime()).",";
						} else {
							$exrule .= "EXRULE:".$this->getRrule($ex_event);
						}
					}
					$exdates = substr($exdates, $exdates.length, -1);
					$sims['###EXDATE###'] = $this->cObj->substituteMarkerArrayCached($exdates, '###EXDATE###', array (), array ());
					$sims['###EXRULE###'] = $this->cObj->substituteMarkerArrayCached($exrule, '###EXRULE###', array (), array ());
					$temp = getdate(abs($event->getEndtime() - $event->getStarttime()));
					$sims['###DURATION_HOURS###'] = $this->cObj->substituteMarkerArrayCached(date("H", abs($event->getEndtime() - $event->getStarttime())), '###DURATION_HOURS###', array (), array ());
					$sims['###DURATION_MINUTES###'] = $this->cObj->substituteMarkerArrayCached(date("i", abs($event->getEndtime() - $event->getStarttime())), '###DURATION_MINUTES###', array (), array ());
					$ics_events .= $this->cObj->substituteMarkerArrayCached($eventTemplate, $sims, array (), array ());
				}
	
			}
		}
		$rems = array ();
		$rems["###EVENT###"] = $ics_events;

		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=".$getdate.".ics");
		header("Content-Type: text/ics");
		header("Pragma: ");
		header("Cache-Control:");
		return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
	}

	function getRrule($event) {

		$rrule = "";
		if ($event->getFreq() != 'none') {
			$rrule = "FREQ=".$this->getFreq($event->getFreq()).";INTERVAL=".$event->getInterval().";";
			if ($event->getCount() != 0) {
				$rrule .= "COUNT=".$event->getCount().";";
			}
			if (count($event->getByDay()) > 0) {
				$rrule .= "BYDAY=";
				foreach ($event->getByDay() as $day) {
					$rrule .= $day.",";
				}
				$rrule = substr($rrule, $rrule.lenght, -1);
			}
			if ($event->getByWeekNo().length > 0) {
				$rrule .= "BYWEEKNO=";
				foreach ($event->getByWeekNo() as $week) {
					$rrule .= $week.",";
				}
				$rrule .= ";";
			}
			if ($event->getByMonth().length > 0) {
				$rrule .= "BYMONTH=";
				foreach ($event->getByMonth() as $month) {
					$rrule .= $month.",";
				}
				$rrule .= ";";
			}
			if ($event->getByYearDay().length > 0) {
				$rrule .= "BYYEARDAY=";
				foreach ($event->getByYearDay() as $yearday) {
					$rrule .= $yearday.",";
				}
				$rrule .= ";";
			}
			if ($event->getByMonthDay().length > 0) {
				$rrule .= "BYMONTHDAY=";
				foreach ($event->getByMonthDay() as $monthday) {
					$rrule .= $monthday.",";
				}
				$rrule .= ";";
			}
			if ($event->getByWeekDay().length > 0) {
				$rrule .= "BYWEEKDAY=";
				foreach ($event->getByWeekDay() as $weekday) {
					$rrule .= $weekday.",";
				}
				$rrule .= ";";
			}
		}
		return strtoupper($rrule);
	}
	
	function getFreq($eventFreq){
		$freq_type = "";
		switch ($eventFreq){
			case 'year':		$freq_type = 'YEARLY';	break;
			case 'month':		$freq_type = 'MONTHLY';	break;
			case 'week':		$freq_type = 'WEEKLY';	break;
			case 'day':			$freq_type = 'DAILY';		break;
			case 'hour':		$freq_type = 'HOURLY';	break;
			case 'minute':		$freq_type = 'MINUTELY';	break;
			case 'second':		$freq_type = 'SECONDLY';	break;
		}
		return $freq_type;
	}

	/**
	 *  Draws a organizer.
	 *  @param		object		The organizer to be drawn.
	 *	@return		string		The HTML output.
	 */
	function drawOrganizer($cObj, $organizer, $rightsObj) {
		return $organizer->renderOrganizer($cObj);
	}

	/**
	 *  Draws a location.
	 *  @param		object		The location to be drawn.
	 *	@return		string		The HTML output.
	 */
	function drawLocation($cObj, $location, $rightsObj) {
		return $location->renderLocation($cObj);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_phpicalendarview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_phpicalendarview.php']);
}
?>