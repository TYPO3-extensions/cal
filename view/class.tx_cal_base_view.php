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

require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * TODO
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_base_view extends t3lib_svbase {

	var $cObj;
	var $conf;
 	var $rightsObj;
	var $tempATagParam;
	var $master_array;
	var $controller;
	var $modelObj;
	var $prefixId = 'tx_cal_controller';
	var $viewarray;

 	function setController(&$controller){
		$this->cObj = &$controller->cObj;
		$this->controller = &$controller;
		$this->conf = &$controller->conf;
		$this->rightsObj = &$controller->rightsObj;
		$this->modelObj = &$controller->modelObj;
	}

	function _init(&$master_array){
		$this->tempATagParam = $GLOBALS['TSFE']->ATagParams;
		$this->master_array = $master_array;
	}

	function finish(&$page, &$rems){
		$page = $this->checkForMonthMarker($page);
		$sims = array();
		if ($this->rightsObj->isCalAdmin()) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_administration_view').'"';
			$parameter = array ("view" => "admin", "lastview" => $this->conf['view']);
			$sims['adminlink'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_administration_view'),$parameter, $this->conf['cache'], $this->conf['clear_anyway']);
			$rems["###SHOW_ADMINLINK###"] =  $this->cObj->getSubpart($page, "###SHOW_ADMINLINK###");
		} else {
			$rems["###SHOW_ADMINLINK###"] = '';
		}
		if ($this->conf["view."]["other."]["showTomorrowEvents"] == 1) {
			$page = $this->tomorrows_events($page);
			$rems["###TOMORROWS_EVENTS###"] =  $this->cObj->getSubpart($page, "###TOMORROWS_EVENTS###");
		} else {
			$rems["###TOMORROWS_EVENTS###"] = '';
		}
		if ($this->conf["view."]["other."]["showTodos"] == 1) {
			$page = $this->get_vtodo($page);
		} else {
			$rems["###VTODO###"] = '';
		}

		if ($this->conf["view."]["other."]["showGoto"] != 1) {
			$rems["###SHOW_GOTO###"] = '';
		}
		if ($this->conf["view."]["other."]["showLogin"] != 1) {
			$rems["###SHOW_USER_LOGIN###"] = '';
		}else{
			$parameter = array ("view" => $this->conf['view']);
			$sims['LOGIN_ACTION'] = $this->controller->pi_linkTP_keepPIvars_url($parameter, $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['other.']['loginPageId']);

			if($this->rightsObj->isLoggedIn()){
				$sims['LOGIN_TYPE'] = "logout";
				$sims['l_login'] = $this->controller->pi_getLL('l_logout');
				$sims['l_login_button'] = $this->controller->pi_getLL('l_logout');
				$sims['username'] = $this->rightsObj->getUserName();
				$rems["###LOGIN###"] = "";
				$rems["###LOGOUT###"] = $this->cObj->getSubpart($page, "###LOGOUT###");
			}else{
				$sims['LOGIN_TYPE'] = "login";
				$sims['l_login'] = $this->controller->pi_getLL('l_login');
				$sims['l_login_button'] = $this->controller->pi_getLL('l_login');
				$rems["###LOGIN###"] = $this->cObj->getSubpart($page, "###LOGIN###");
				$rems["###LOGOUT###"] = "";
			}
			$sims['USER_FOLDER'] = $this->conf['view.']['other.']['userFolderId'];
			$sims['REDIRECT_URL'] = $this->controller->pi_linkTP_keepPIvars_url();
//			$rems["###SHOW_USER_LOGIN###"] =  $this->cObj->getSubpart($page, "###SHOW_USER_LOGIN###");
		}

		if ($this->conf["view."]["ics."]['showIcsLinks'] == 1) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_ics_view').'"';
			$icslink = $this->cObj->getSubpart($page, "###SHOW_ICSLINK###");
			$icslink = str_replace('###ICSLINK###',$this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_calendar_icslink'), array ("type" => "tx_cal_category", "view" => "icslist", "lastview" => $this->conf['view']), $this->conf['cache'], $this->conf['clear_anyway']),$icslink);
			$rems["###SHOW_ICSLINK###"] = $icslink;
		}else{
			$rems["###SHOW_ICSLINK###"] = "";
		}
		if ($this->conf["view."]["other."]["showSearch"] != 1) {
			$rems["###SHOW_SEARCH###"] = '';
		}else{
			$page = $this->replace_files($page, array ('search_box' => $this->conf["view."]["other."]["searchBoxTemplate"]));
			$sims['l_search'] = $this->controller->pi_getLL('l_search');
			$sims['getdate'] = $this->conf['getdate'];
			$rems["###SHOW_SEARCH###"] =  $this->cObj->getSubpart($page, "###SHOW_SEARCH###");
		}


		$selectedCategories = "";
		$this->list_legend($sims['legend'], $selectedCategories);

		if ($this->conf["view."]["other."]["showCategorySelection"] == 1) {
			$link = $this->controller->pi_linkTP_keepPIvars_url();
			$params = t3lib_div::explodeUrl2Array($link,TRUE);
			if(!empty($params[$this->prefixId])){
				foreach($params[$this->prefixId] as $key => $param){
					if($key!="category"){
						$tmp = $this->conf['view.']['other.']['categorySwitch'];
						$tmp = str_replace('###NAME###',$this->prefixId.'['.$key.']',$tmp);
						$hiddenParams .= str_replace('###VALUE###',$param,$tmp);
					}
				}
			}
			$sims['categoryurl'] = $link;
			$sims['hiddenvalues'] = $hiddenParams;
			$sims['LIST_CATEGORIES_PICK'] = $selectedCategories;

			$tmp = $this->conf['view.']['other.']['categorySelectorSubmit'];
			$sims['categorysubmit'] = str_replace('###REFRESH###',$this->controller->pi_getLL('l_refresh'),$tmp);
			$rems["###SHOW_CATEGORY_SELECTOR###"] = $this->cObj->getSubpart($page, "###SHOW_CATEGORY_SELECTOR###");;
			$sims['L_CATEGORY_SELECTOR'] = $this->controller->pi_getLL('l_category_selector');
		}else{
			$rems["###SHOW_CATEGORY_SELECTOR###"] = "";
		}

		if ($this->conf["view."]["other."]["showJumps"] != 1) {
			$rems["###SHOW_JUMPS###"] = "";
			$rems["###DONT_SHOW_JUMPS###"] = $this->cObj->getSubpart($page, "###DONT_SHOW_JUMPS###");
		}else{
			$sims['list_jumps'] = $this->list_jumps();
			$rems["###SHOW_JUMPS###"] =  $this->cObj->getSubpart($page, "###SHOW_JUMPS###");
			$rems["###DONT_SHOW_JUMPS###"] = "";
		}

		if ($this->conf["view."]["other."]["showCalendarSwitch"]) {
			$rems["###SHOW_CALENDAR_SWITCH###"] = $this->cObj->getSubpart($page, "###SHOW_CALENDAR_SWITCH###");
			$this->list_calendars($sims);
		}else{
			$rems["###SHOW_CALENDAR_SWITCH###"] = $this->cObj->getSubpart($page, "###SHOW_CALENDAR_SWITCH###");
			$rems["###SHOW_CALENDAR_SWITCH###"] = "";
		}

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['lastview'].'_view').'"';
		if(count($this->conf["view."]['defaultView'])==1){
			$rems["###BACKLINK###"] = "";
		}else{
			if (!empty ($this->conf['page_id'])) {
				$rems["###BACKLINK###"] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_back'), array ("view" => $this->conf['lastview'], "type" => "", "uid" => ""), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['page_id']);
			} else {
				$rems["###BACKLINK###"] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_back'), array ("view" => $this->conf['lastview'], "type" => "", "uid" => ""), $this->conf['cache'], $this->conf['clear_anyway']);
			}
		}

		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		$page = $this->replaceLinksToOtherViews($page);

		// select for calendars
		$day_array2 = array();
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$sims['list_icals'] = ''; //display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED));
		$sims['list_years'] = $this->list_years($this_year, $this->conf['view.']['other.']['dateFormatYearJump'], 1);
		$sims['list_months'] = $this->list_months($this_year, $this->conf['view.']['other.']['dateFormatMonthJump']);
		$sims['list_weeks'] = $this->list_weeks($this_year, $this->conf['view.']['other.']['dateFormatWeekJump'], $this->conf["view."]["weekStartDay"]);

		$sims["L_SEARCH"] = $this->controller->pi_getLL('l_search');
		$sims["QUERY"] = strip_tags($this->controller->piVars['query']);
		$sims["LASTVIEW"] = strip_tags($this->controller->piVars['view']);
		$sims["THIS_VIEW"] = $this->conf['view'];
		$sims["type"] = $this->conf['type'];
		$sims["option"] = $this->conf['option'];
		$sims["calendar"] = $this->conf['calendar'];
		$sims["page_id"] = $this->conf['page_id'];
		$sims["LASTVIEW"] = $this->conf['view'];
		$sims["CURRENT_VIEW"] = $this->conf['view'];
		$sims["SEARCH_ACTION_URL"] = $this->controller->pi_linkTP_keepPIvars_url(array("view"=>"search_event"));

		$page = $this->controller->replace_tags($sims, $page);
		$sims = array();
		$sims['img_path'] = expandPath($this->conf['view.']['imagePath']);
		$page = $this->controller->replace_tags($sims, $page);
		$GLOBALS['TSFE']->ATagParams = $this->tempATagParam;
		return $page;
	}

	function checkForMonthMarker($page) {

		$getdate = $this->conf['getdate'];
		if($this->conf['view.']['month.']['navigation']==1){
			$page = str_replace('###CALENDAR_NAV###', '', $page);
		}else{
			$template = $this->cObj->fileResource($this->conf["view."]["month."]["calendarNavTemplate"]);
			if ($template == "") {
				return "<h3>calendar: no calendar_nav template file found:</h3>".$this->conf["view."]["month."]["calendarNavTemplate"];
			}
			$page = str_replace('###CALENDAR_NAV###', $template, $page);
		}
		$match = array();
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
				$data = $this->_draw_month($template_file, $offset, $type);
				$page = str_replace($match[0][$i], $data, $page);
				$i ++;
			}
		}

		$unix_time = strtotime($getdate);
		$display_date = strftime($this->conf['view.']['month.']['dateFormatMonth'], $unix_time);

		$day_array2 = array();
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

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$rems["###NEXT_MONTHLINK###"] = $this->controller->pi_linkTP_keepPIvars($nextmonthlinktext, array ("getdate" => $next_month, "view" => "month"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["month."]["monthViewPid"]);
			$rems["###PREV_MONTHLINK###"] = $this->controller->pi_linkTP_keepPIvars($prevmonthlinktext, array ("getdate" => $prev_month, "view" => "month"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$rems["###NEXT_MONTHLINK###"] = $this->controller->pi_linkTP_keepPIvars($nextmonthlinktext, array ("getdate" => $next_month, "view" => "month"), $this->conf['cache'], $this->conf['clear_anyway']);
			$rems["###PREV_MONTHLINK###"] = $this->controller->pi_linkTP_keepPIvars($prevmonthlinktext, array ("getdate" => $prev_month, "view" => "month"), $this->conf['cache'], $this->conf['clear_anyway']);
		}

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		$prevyearlinktext = $this->cObj->getSubpart($page, "###PREV_YEARLINKTEXT###");
		$nextyearlinktext = $this->cObj->getSubpart($page, "###NEXT_YEARLINKTEXT###");
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$rems["###PREV_YEARLINK###"] = $this->controller->pi_linkTP_keepPIvars($prevyearlinktext, array ("getdate" => $prev_year, "view" => "year"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["year_view_id"]);
			$rems["###NEXT_YEARLINK###"] = $this->controller->pi_linkTP_keepPIvars($nextyearlinktext, array ("getdate" => $next_year, "view" => "year"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["year_view_id"]);
		} else {
			$rems["###PREV_YEARLINK###"] = $this->controller->pi_linkTP_keepPIvars($prevyearlinktext, array ("getdate" => $prev_year, "view" => "year"), $this->conf['cache'], $this->conf['clear_anyway']);
			$rems["###NEXT_YEARLINK###"] = $this->controller->pi_linkTP_keepPIvars($nextyearlinktext, array ("getdate" => $next_year, "view" => "year"), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		$parse_month = date("Ym", $unix_time);
		$first_of_month = $this_year.$this_month."01";
		$weekStartDay = $this->conf["view."]["weekStartDay"];
		$start_month_day = tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay);

		$num_of_events2 = 0;

		$languageArray = array (
			'getdate' => $getdate,
			'display_date' => $display_date,
			'next_month' => $next_month,
			'prev_month' => $prev_month,
			'l_calendar' => $this->controller->pi_getLL('l_calendar'),
			'calendar_name' => $this->conf["calendarName"],
			'l_legend' => $this->controller->pi_getLL('l_legend'),
			'l_tomorrows' => $this->controller->pi_getLL('l_tomorrows'),
			'l_jump' => $this->controller->pi_getLL('l_jump'),
			'l_todo' => $this->controller->pi_getLL('l_todo'),
			'l_day' => $this->controller->pi_getLL('l_day'),
			'l_week' => $this->controller->pi_getLL('l_week'),
			'l_month' => $this->controller->pi_getLL('l_month'),
			'l_year' => $this->controller->pi_getLL('l_year'),
			'l_prev' => $this->controller->pi_getLL('l_prev'),
			'l_next' => $this->controller->pi_getLL('l_next'),
			'l_subscribe' => $this->controller->pi_getLL('l_subscribe'),
			'l_download' => $this->controller->pi_getLL('l_download'),
			'l_this_months' => $this->controller->pi_getLL('l_this_months'),
			'this_year' => $this_year,
			'next_year' => $next_year,
			'prev_year' => $prev_year,
			'l_search' => $this->controller->pi_getLL('l_search'),
			'l_powered_by' => $this->controller->pi_getLL('l_powered_by'),
			'l_this_site_is' => $this->controller->pi_getLL('l_this_site_is'),
			'l_invalid_login' => $this->controller->pi_getLL('l_invalid_login'),
			'l_username' => $this->controller->pi_getLL('l_username'),
			'l_password' => $this->controller->pi_getLL('l_password'),
		);

		$page = $this->controller->replace_tags($languageArray, $page);
		if ($this->conf["view."]["month."]["thisMonthsEvents"]) {
			$tx_cal_listview = t3lib_div::makeInstanceService("cal_view", "list", "list");
			$tx_cal_listview->setController($this->controller);

			$rems["###SHOWBOTTOMEVENTS###"] = $tx_cal_listview->drawList($this->master_array);
			$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		} else {
			$rems["###SHOWBOTTOMEVENTS###"] = '';
			$page = $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		}
		return $page;
	}

	function replace_files($page, $tags = array ()) {
		if (sizeof($tags) > 0)
			foreach ($tags as $tag => $data) {

				// This opens up another template and parses it as well.
				$data = $GLOBALS['TSFE']->tmpl->getFileName($data);
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

	function replaceLinksToOtherViews($template) {
		$dayviewlinktext = $this->cObj->getSubpart($template, "###DAYVIEWLINKTEXT###");
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
		if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
			$rems["###DAYVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($dayviewlinktext, array ("view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$rems["###DAYVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($dayviewlinktext, array ("view" => "day"), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_week_view').'"';
		$weekviewlinktext = $this->cObj->getSubpart($template, "###WEEKVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
			$rems["###WEEKVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($weekviewlinktext, array ("view" => "week"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["week."]["weekViewPid"]);
		} else {
			$rems["###WEEKVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($weekviewlinktext, array ("view" => "week"), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
		$monthviewlinktext = $this->cObj->getSubpart($template, "###MONTHVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$rems["###MONTHVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($monthviewlinktext, array ("view" => "month"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$rems["###MONTHVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($monthviewlinktext, array ("view" => "month"), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		$yearviewlinktext = $this->cObj->getSubpart($template, "###YEARVIEWLINKTEXT###");
		if (!empty ($this->conf["view."]["year."]["yearViewPid"])) {
			$rems["###YEARVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($yearviewlinktext, array ("view" => "year"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["yearViewPid"]);
		} else {
			$rems["###YEARVIEWLINK###"] = $this->controller->pi_linkTP_keepPIvars($yearviewlinktext, array ("view" => "year"), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$return = $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		return $return;
	}

	function list_jumps() {
		$today = date('Ymd', time());
		$tmp = $this->conf["view."]["other."]['optionString'];
		$tmp = str_replace('###VALUE###','#',$tmp);
		$return = str_replace('###NAME###',$this->controller->pi_getLL('l_jump'),$tmp);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
		if (!empty ($this->conf["view."]["day."]["dayViewPid"])) {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "day",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "day",), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$tmp = $this->conf["view."]["other."]['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_goday'),$tmp);

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_week_view').'""'.$this->controller->pi_getLL('l_week_view').'"';
		if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "week",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["week."]["weekViewPid"]);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "week",), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$tmp = $this->conf["view."]["other."]['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_goweek'),$tmp);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "month",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "month",), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$tmp = $this->conf["view."]["other."]['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_gomonth'),$tmp);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["year_view_id"]);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $today, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$tmp = $this->conf["view."]["other."]['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_goyear'),$tmp);
		return $return;
	}

	function list_legend(&$return, &$selectedCategories) {
		$i = 1;

		//$return = '<form action="'.$link.'" method="post">';
		$catArray = split(",",$this->conf['category']);
//debug($this->master_array['legend']);
		$usedCategories = array();
		if (is_array($this->master_array['legend'])) {
			foreach ($this->master_array['legend'] as $calendarVal) {
				if(is_array($calendarVal)){
					foreach ($calendarVal as $key => $val){
						$return .= $this->cObj->stdWrap($key, $this->conf['view.']['other.']['legendCalendar_stdWrap.']);
//						$return .= '<div class="legend_calendar">'.$key.'</div>';
						if (is_array($val)) {
							foreach ($val as $colorArray) {

								foreach ($colorArray as $key2 => $val2) {
									if(array_key_exists($val2['uid'], $usedCategories)){
										continue;
									}
									
									$usedCategories[$val2['uid']] = "";
									$selected = "";
									if(in_array($val2['uid'],$catArray) || !$this->conf['category']){
										$selected = 'selected="selected"';
									}
									$tmp = $this->cObj->stdWrap($val2['title'], $this->conf['view.']['other.']['legendCategory_stdWrap.']);
									$return .= str_replace('###HEADERSTYLE###',$key2,$tmp);
									
									$selectedCategories .= '<option '.$selected.' value="'.$val2['uid'].'">'.$val2['title'].'</option>';
//									$return .= '<div class="V9"><span class="'.$key2.'_bullet '.$key2.'_legend_bullet" >&bull;</span><span class="'.$key2.'_text '.$key2.'_legend_text">'.$val2['title'].'</span></div>';
								}
							}
						}
					}
				}
			}
		}
		$return = $this->cObj->stdWrap($return, $this->conf['view.']['other.']['legendCalendar_stdWrap.']);
	}

	function list_months($this_year, $dateFormat_month) {
		$month_time = strtotime("$this_year-01-01");
		$getdate_month = date("m", strtotime($this->conf['getdate']));
		for ($i = 0; $i < 12; $i ++) {
			$monthdate = date("Ymd", $month_time);
			$month_month = date("m", $month_time);
			$select_month = strftime($dateFormat_month, $month_time);
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
			if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $monthdate, "view" => "month",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["month."]["monthViewPid"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $monthdate, "view" => "month",), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			if ($month_month == $getdate_month) {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listMonthSelected_stdWrap.']);
				$return .= str_replace('###MONTH###',$select_month,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listMonth_stdWrap.']);
				$return .= str_replace('###MONTH###',$select_month,$tmp);
			}
			$month_time = strtotime("+1 month", $month_time);
		}
		return $return;
	}

	function list_years($this_year, $dateFormat, $num_years) {

		$year_time = strtotime($this->conf['getdate']);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		for ($i = 0; $i < $num_years; $i ++) {
			$offset = $num_years - $i;
			$prev_time = strtotime("-$offset year", $year_time);
			$prev_date = date("Ymd", $prev_time);
			$prev_year = strftime($dateFormat, $prev_time);
			if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $prev_date, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["year_view_id"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $prev_date, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYear_stdWrap.']);
			$return .= str_replace('###YEAR###',$prev_year,$tmp);
		}

		$getdate_date = date("Ymd", $year_time);
		$getdate_year = strftime($dateFormat, $year_time);
		if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $getdate_date, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["year_view_id"]);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $getdate_date, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYearSelected_stdWrap.']);
		$return .= str_replace('###YEAR###',$getdate_year,$tmp);
		for ($i = 0; $i < $num_years; $i ++) {
			$offset = $i +1;
			$next_time = strtotime("+$offset year", $year_time);
			$next_date = date("Ymd", $next_time);
			$next_year = strftime($dateFormat, $next_time);
			if (!empty ($this->conf["view."]["year."]["year_view_id"])) {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $next_date, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["year."]["year_view_id"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $next_date, "view" => "year",), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYear_stdWrap.']);
			$return .= str_replace('###YEAR###',$next_year,$tmp);
		}

		return $return;
	}

	function list_weeks($this_year, $dateFormat_week_jump, $weekStartDay) {
		$day_array2 = array();
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$check_week = strtotime($this->conf['getdate']);
		$start_week_time = strtotime(tx_cal_calendar :: dateOfWeek(strtotime("$this_year-01-01"), $weekStartDay, $weekStartDay));
		$end_week_time = $start_week_time + (6 * 25 * 60 * 60);

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_week_view').'"';
		do {
			$weekdate = date("Ymd", $start_week_time);
			$select_week1 = strftime($dateFormat_week_jump, $start_week_time);
			$select_week2 = strftime($dateFormat_week_jump, $end_week_time);


			if (!empty ($this->conf["view."]["week."]["weekViewPid"])) {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $weekdate, "view" => "week",), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["week."]["weekViewPid"]);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars("", array ("getdate" => $weekdate, "view" => "week",), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			if (($check_week >= $start_week_time) && ($check_week <= $end_week_time)) {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listWeeksSelected_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listWeeks_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			}
			$start_week_time = strtotime("+1 week", $start_week_time);
			$end_week_time = $start_week_time + (6 * 25 * 60 * 60);
		} while (date("Y", $start_week_time) <= $this_year);

		return $return;
	}

	function list_calendars(&$sims){
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$modelObj = new $tx_cal_modelcontroller ($this->controller);
		$calendarService = $modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
		$calendarIds = $calendarService->getIdsFromTable("",$this->conf['pidList'], true,true,true);
		if(is_array($calendarIds)){
			$calendar .= '<option value="">'.$this->controller->pi_getLL('l_all_cal_comb_lang').'</option>';
			foreach($calendarIds as $calendarRow){
				if($this->conf['calendar']==$calendarRow['uid']){
					$calendar .= '<option value="'.$calendarRow['uid'].'" selected="selected">'.$calendarRow['title'].'</option>';
				}else{
					$calendar .= '<option value="'.$calendarRow['uid'].'">'.$calendarRow['title'].'</option>';
				}
			}
		}

		$sims['l_calendar'] = $this->controller->pi_getLL('l_calendar');
		$sims['calendar_ids'] = $calendar;
		$sims['change_calendar_action_url'] = $this->controller->pi_linkTP_keepPIvars_url( array("view"=>$this->conf['view']));
		return $calendar;
	}



	function tomorrows_events($template) {

		// , $next_day, $timeFormat, $tomorrows_events_lines
		$unix_time = strtotime($this->conf['getdate']);

		$next_day = date('Ymd', strtotime("+1 day", $unix_time));

		$match1 = $this->cObj->getSubpart($template, "###T_ALLDAY_SWITCH###");
		$match2 = $this->cObj->getSubpart($template, "###T_EVENT_SWITCH###");
		$loop_t_ad = trim($match1);
		$loop_t_e = trim($match2);
		$return_adtmp = '';
		$return_etmp = '';

		if (is_array($this->master_array[$next_day]) && sizeof($this->master_array[$next_day]) > 0) {
			foreach ($this->master_array[$next_day] as $cal_time => $event_times) {
				foreach ($event_times as $uid => $val) {
					$tmp = $this->cObj->stdWrap($this->getLinkToEvent($val,$val->getTitle(),$this->conf['view'],($this->conf['getdate']+1)), $this->conf['view.']['other.']['tomorrowsEvents_stdWrap.']);
					$return = str_replace('###HEADERSTYLE###',$val->getHeaderStyle(),$tmp);
					if ($val->getStartHour() == '') {
						$replace_ad .= str_replace('###T_ALLDAY###', $return, $loop_t_ad);
					} else {
						$replace_e .= str_replace('###T_EVENT###', $return, $loop_t_e);
					}
				}
			}

			$rems["###T_ALLDAY_SWITCH###"] = $replace_ad;
			$rems["###T_EVENT_SWITCH###"] = $replace_e;
			return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());

		} else {
			$rems["###T_ALLDAY_SWITCH###"] = '';
			$rems["###T_EVENT_SWITCH###"] = '';
			return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		}
	}

	function get_vtodo($template, $next_day, $timeFormat, $tomorrows_events_lines, $show_completed, $show_todos) {
		$match1 = array();
		$match2 = array();
		$match3 = array();
		preg_match("!<\!-- switch show_completed on -->(.*)<\!-- switch show_completed off -->!is", $this->page, $match1);
		preg_match("!<\!-- switch show_important on -->(.*)<\!-- switch show_important off -->!is", $this->page, $match2);
		preg_match("!<\!-- switch show_normal on -->(.*)<\!-- switch show_normal off -->!is", $this->page, $match3);
		$completed = trim($match1[1]);
		$important = trim($match2[1]);
		$normal = trim($match3[1]);
		$nugget2 = '';

		if (is_array($this->master_array['-2'])) {
			foreach ($this->master_array['-2'] as $vtodo_times) {
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
		$GLOBALS['TSFE']->ATagParams = 'title="'.($this->conf['view.']['event.']['useTitleForLinkTitle']?$event->getTitle():$this->conf['view.']['event.']['ownLinkTitleText']).'"';
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
		if($event->isExternalPluginEvent()){
			return $event->getExternalPluginEventLink();
		}
		if($this->conf["view."]["event."]["isPreview"]){
			if (!empty ($this->conf["view."]["event."]["eventViewPid"])) {
				return $this->controller->pi_linkTP_keepPIvars($linktext, array ("page_id" => $GLOBALS['TSFE']->id, "getdate" => $date, "lastview" => $this->conf['view'], "view" => "event", "type" => $event->getType(), "uid" => $event->getUid(), "preview" => 1), $this->conf['cache'], $this->conf['clear_anyway'],  $this->conf["view."]["event."]["eventViewPid"]);
			}
			return $this->controller->pi_linkTP_keepPIvars($linktext, array ("getdate" => $date, "lastview" => $currentView, "view" => "event", "type" => $event->getType(), "uid" => $event->getUid(), "preview" => 1), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		if (!empty ($this->conf["view."]["event."]["eventViewPid"])) {
			return $this->controller->pi_linkTP_keepPIvars($linktext, array ("page_id" => $GLOBALS['TSFE']->id, "getdate" => $date, "lastview" => $this->conf['view'], "view" => "event", "type" => $event->getType(), "uid" => $event->getUid()), $this->conf['cache'], $this->conf['clear_anyway'],  $this->conf["view."]["event."]["eventViewPid"]);
		}
		return $this->controller->pi_linkTP_keepPIvars($linktext, array ("getdate" => $date, "lastview" => $currentView, "view" => "event", "type" => $event->getType(), "uid" => $event->getUid()), $this->conf['cache'], $this->conf['clear_anyway']);
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
	 * Draws the month view
	 *  @param		$page	string		The page template
	 *  @param		$offset	integer		The month offset. Default = +0
	 *  @param		$type	integer		The date of the event
	 *	@return		string		The HTML output.
	 */
	function _draw_month($page, $offset = '+0', $type) {

		$unix_time = strtotime($this->conf['getdate']);
		$day_array2 = array();
		ereg("([0-9]{4})([0-9]{2})([0-9]{2})", $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];

		$weekStartDay = $this->conf["view."]["weekStartDay"];
		$loop_wd = $this->cObj->getSubpart($page, "###LOOPWEEKDAY###");
		$t_month = $this->cObj->getSubpart($page, "###SWITCHMONTHDAY###");
		$startweek = $this->cObj->getSubpart($page, "###LOOPMONTHWEEKS_DAYS###");
		$endweek = $this->cObj->getSubpart($page, "###LOOPMONTHDAYS_WEEKS###");
		$weeknum = $this->cObj->getSubpart($page, "###LOOPWEEK_NUMS###");

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
		$start_day = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($this->conf['getdate']), $weekStartDay, $weekStartDay));
		$month_title = strftime($this->conf['view.']['month.']['dateFormatMonth'], $fake_getdate_time);
		$month_date = date('Ymd', $fake_getdate_time);

		$view_array = array ();
		if(!$this->viewarray){
			if (count ($this->master_array>1)) {
				foreach ($this->master_array as $ovlKey => $ovlValue) {
					if($ovlKey=='legend'){
						continue;
					}
					foreach ($ovlValue as $ovl_time_key => $ovl_time_Value) {
						foreach ($ovl_time_Value as $ovl2Value) {
							$starttime = $ovl2Value->getStarttime();

							if(date("Ymd",$starttime)>=$ovlKey){
								$endtime = $ovl2Value->getEndtime();
								if($ovl_time_key=="-1"){
									$endtime += 1;
								}
								for ($j = $starttime; $j < $endtime; $j = $j + 60 * 60 * 24) {
									$view_array[date("Ymd",$j)]["0000"][count($view_array[date("Ymd",$j)]["0000"])]=$ovl2Value;
								}
							}
						}
					}
				}
			}
			$this->viewarray = $view_array;
		}
		$languageArray = array (
			'month_title' => $month_title,
		);
		
		$page = $this->controller->replace_tags($languageArray, $page);

		if ($type == 'small') {
			$langtype = "%a";
			$typeSize = 2;
		}
		elseif ($type == 'medium') {
			$langtype = "%a";
		}
		elseif ($type == 'large') {
			$langtype = "%A";
		}
		$weekday_loop .= str_replace('###WEEKNUM###', "&nbsp;", $weeknum);
		for ($i = 0; $i < 7; $i ++) {
			$weekday = strftime($langtype, $start_day);
			if($typeSize){
				$weekday = substr($weekday,0,$typeSize);
			}
			$start_day = strtotime("+1 day", $start_day);
			$loop_tmp = str_replace('###WEEKDAY###', $weekday, $loop_wd);
			$weekday_loop .= $loop_tmp;
		}
		$weekday_loop .= $endweek;
		$start_day = strtotime(tx_cal_calendar :: dateOfWeek(strtotime($first_of_month), $weekStartDay, $weekStartDay));

		$i = 0;
		$whole_month = TRUE;
		$isAllowedToCreateEvents = $this->rightsObj->isAllowedToCreateEvents();
		
		$createLink = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['month.']['addIcon'], array ("getdate" => 'GETDATE', "lastview" => "month", "view" => "create_event"), $this->conf['cache'], $this->conf['clear_anyway'],$this->conf["view."]["event."]["createEventViewPid"]);
		$dayLink = $this->controller->pi_linkTP_keepPIvars('GETDAY', array ("getdate" => 'GETDATE', "view" => "day"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["day."]["dayViewPid"]);
	
		do {

			if ($i == 0){
				$middle .= $startweek;
				$num = date("W", $start_day);
				$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_week_view').'"';
				$num = $this->controller->pi_linkTP_keepPIvars($num, array ("getdate" => date("Ymd",$start_day), "view" => "week"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["week."]["weekViewPid"]);
				$middle .= str_replace('###WEEKNUM###', $num, $weeknum);
			}
			$i ++;
			$switch = array ('ALLDAY' => '');
			$check_month = date("m", $start_day);
			$daylink = date("Ymd", $start_day);
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
			if ($daylink>=date("Ymd",time()) && $isAllowedToCreateEvents) {
				$tmp = str_replace('GETDATE',date("Ymd", $start_day),$createLink);
				$switch['LINK'] = $tmp;
			} else {
				$switch['LINK'] = "";
			}
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
			
			$tmp = str_replace('GETDAY',date("j", $start_day),$dayLink);
			$tmp = str_replace('GETDATE',date("Ymd", $start_day),$tmp);
			$switch['LINK'] .= $tmp;


			if ($type == 'small') {
				$style = $this->conf["view."]["month."]["monthSmallStyle"]." ";
			}
			elseif ($type == 'medium') {
				$style = $this->conf["view."]["month."]["monthMediumStyle"]." ";
			}
			elseif ($type == 'large') {
				$style = $this->conf["view."]["month."]["monthLargeStyle"]." ";
			}


			if ($check_month != $minical_month) {
				$style .= $this->conf["view."]["month."]["monthOffStyle"]." ";
			}
			if ($daylink == $this->conf['getdate']) {
				$style .= $this->conf["view."]["month."]["monthSelectedStyle"]." ";
			}
			if ($daylink == date("Ymd")) {
				$style .= $this->conf["view."]["month."]["monthOnStyle"]."";
			}
			$temp = str_replace("###STYLE###", $style, $t_month);
			if ($this->viewarray[$daylink]) {
				foreach ($this->viewarray[$daylink] as $cal_time => $event_times) {
					foreach ($event_times as $uid => $val) {
						if ($val->getStartHour() == '') {
							if ($type == 'large') {
								if ($this->rightsObj->isAllowedToEditEvents()) {
									$switch['ALLDAY'] .= $this->conf['view.']['month.']['editIcon'];
								}
								$tmp = $this->cObj->stdWrap($val->renderEventForAllDay(), $this->conf['view.']['month.']['allDayLarge_stdWrap.']);
								$switch['ALLDAY'] .= str_replace('###HEADERSTYLE###',$val->getHeaderStyle(),$tmp);
							}else if ($type == 'small'){// && !$isAllowedToCreateEvents){
								$switch['LINK'] = $this->cObj->stdWrap($switch['LINK'], $this->conf['view.']['month.']['smallLink_stdWrap.']);
							} else {
								$switch['ALLDAY'] .= $this->cObj->stdWrap($val->getHeaderStyle(), $this->conf['view.']['month.']['mediumLink_stdWrap.']);
							}
						} else {
							$start2 = strftime($this->conf['view.']['month.']['timeFormatSmall'], $val->getStarttime());
							if ($type == 'large') {
								$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event_view').'"';
								if($this->conf['view.']['freeAndBusy.']['enable'] && !$val->isEventOwner($this->rightsObj->getUserId(),$this->rightsObj->getUserGroups()) && $this->conf['option']=='freeandbusy' && $this->conf['calendar']){
									$switch['EVENT'] .= $this->cObj->stdWrap($switch['LINK'], $this->conf['view.']['month.']['freeAndBusyEvent_stdWrap.']);
								}else{
									$tmp = $this->cObj->stdWrap($this->getLinkToEvent($val, $val->renderEventForMonth(),$this->conf['view'], $this->conf['getdate']), $this->conf['view.']['month.']['eventLarge_stdWrap.']);
									$switch['EVENT'] .= str_replace('###HEADERSTYLE###', $val->getHeaderStyle(), $tmp);
								}
							}else if ($type == 'small'){// && !$isAllowedToCreateEvents){
								$switch['LINK'] = $this->cObj->stdWrap($switch['LINK'], $this->conf['view.']['month.']['smallLink_stdWrap.']);
							} else {
								$tmp = $this->cObj->stdWrap("", $this->conf['view.']['month.']['eventMedium_stdWrap.']);
								$tmp = str_replace('###TITLE###', $val->getTitle(), $tmp);
								$tmp = str_replace('###HEADERSTYLE###', $val->getHeaderStyle(), $tmp);
								$switch['EVENT'] .= $this->getLinkToEvent($val, $tmp,$this->conf['view'], $this->conf['getdate']);
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
		$rems['###LOOPMONTHWEEKS_DAYS###'] = "";
		$rems['###LOOPWEEK_NUMS###'] = "";

		$return = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';#
		if (!empty ($this->conf["view."]["month."]["monthViewPid"])) {
			$month_link = $this->controller->pi_linkTP_keepPIvars($month_title, array ("getdate" => $month_date, "view" => "month"), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf["view."]["month."]["monthViewPid"]);
		} else {
			$month_link = $this->controller->pi_linkTP_keepPIvars($month_title, array ("getdate" => $month_date, "view" => "month"), $this->conf['cache'], $this->conf['clear_anyway']);
		}

		$return = str_replace('###MONTH_LINK###', $month_link, $return);
		return $return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']);
}
?>
