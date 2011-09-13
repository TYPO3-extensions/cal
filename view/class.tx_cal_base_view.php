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
require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_base_service.php');
require_once(PATH_t3lib.'class.t3lib_cs.php');

/**
 * TODO
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_base_view extends tx_cal_base_service {

	var $tempATagParam;
	var $master_array;
	var $viewarray;
	var $legend = '';

	function tx_cal_base_view(){
		$this->tx_cal_base_service();
	}

	function _init(&$master_array){
        #store cs_convert-object
        $this->cs_convert=t3lib_div::makeInstance('t3lib_cs');
		$this->tempATagParam = $GLOBALS['TSFE']->ATagParams;
		$this->master_array = $master_array;
	}

	function showAdminLink(&$page, &$rems, &$sims){
		if ($this->rightsObj->isCalAdmin()) {
				
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_administration_view').'"';
			$parameter = array ('view' => $this->conf['view.']['admin.']['viewName'], 'lastview' => $this->controller->extendLastView());
			$sims['###ADMINLINK###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_administration_view'),$parameter, $this->conf['cache'], $this->conf['clear_anyway']);
			$rems['###SHOW_ADMINLINK###'] =  $this->cObj->getSubpart($page, '###SHOW_ADMINLINK###');
		} else {
			$rems['###SHOW_ADMINLINK###'] = '';
		}
	}
	
	function showTomorrowsEvents(&$page, &$rems, &$sims){
		if ($this->conf['view.']['other.']['showTomorrowEvents'] == 1) {
			$rems['###TOMORROWS_EVENTS###'] = $this->tomorrows_events($this->cObj->getSubpart($page, '###TOMORROWS_EVENTS###'));
//			$rems['###TOMORROWS_EVENTS###'] =  $this->cObj->getSubpart($page, '###TOMORROWS_EVENTS###');
		} else {
			$rems['###TOMORROWS_EVENTS###'] = '';
		}
	}
	
	function showTodos(&$page, &$rems, &$sims){
		if ($this->conf['view.']['other.']['showTodos'] == 1) {
			$page = $this->get_vtodo($page);
		} else {
			$rems['###VTODO###'] = '';
		}
	}
	
	function showUserLogin(&$page, &$rems, &$sims){
		
		if ($this->conf['view.']['other.']['showLogin'] != 1) {
			$rems['###SHOW_USER_LOGIN###'] = '';
		}
		else
		{
			$parameter = array ('view' => $this->conf['view']);
			$sims['###LOGIN_ACTION###'] = $this->controller->pi_linkTP_keepPIvars_url($parameter, $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['other.']['loginPageId']);

			if($this->rightsObj->isLoggedIn()){
				$sims['###LOGIN_TYPE###'] = 'logout';
				$sims['###L_LOGIN###'] = $this->controller->pi_getLL('l_logout');
				$sims['###L_LOGIN_BUTTON###'] = $this->controller->pi_getLL('l_logout');
				$sims['###USERNAME###'] = $this->rightsObj->getUserName();
				$rems['###LOGIN###'] = '';
				$rems['###LOGOUT###'] = $this->cObj->getSubpart($page, '###LOGOUT###');
			}else{
				$sims['###LOGIN_TYPE###'] = 'login';
				$sims['###L_LOGIN###'] = $this->controller->pi_getLL('l_login');
				$sims['###L_LOGIN_BUTTON###'] = $this->controller->pi_getLL('l_login');
				$rems['###LOGIN###'] = $this->cObj->getSubpart($page, '###LOGIN###');
				$rems['###LOGOUT###'] = '';
			}
			$sims['###USER_FOLDER###'] = $this->conf['view.']['other.']['userFolderId'];
			$sims['###REDIRECT_URL###'] = $this->controller->pi_linkTP_keepPIvars_url();
		}
	}
	
	function showIcsLink(&$page, &$rems, &$sims){
		if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_ics_view').'"';
			$icslink = $this->cObj->getSubpart($page, '###SHOW_ICSLINK###');
			$rems['###SHOW_ICSLINK###'] = str_replace('###ICSLINK###',$this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_calendar_icslink'), array ('type' => 'tx_cal_category', 'view' => 'icslist', 'lastview' => $this->conf['view']), $this->conf['cache'], $this->conf['clear_anyway']),$icslink);
		}else{
			$rems['###SHOW_ICSLINK###'] = '';
		}
	}
	
	function showSearch(&$page, &$rems, &$sims){
		if ($this->conf['view.']['other.']['showSearch'] != 1) {
			$rems['###SHOW_SEARCH###'] = '';
		}else{
			$page = $this->replace_files($page, array ('search_box' => $this->conf['view.']['other.']['searchBoxTemplate']));
			$sims['###L_SEARCH###'] = $this->controller->pi_getLL('l_search');
			$sims['###L_DOSEARCH###'] = $this->controller->pi_getLL('l_dosearch');
			$sims['###GETDATE###'] = $this->conf['getdate'];
			$rems['###SHOW_SEARCH###'] =  $this->cObj->getSubpart($page, '###SHOW_SEARCH###');
		}
	}
	
	function showJumps(&$page, &$rems, &$sims){
		if ($this->conf['view.']['other.']['showJumps'] != 1) {
			$rems['###SHOW_JUMPS###'] = '';
			$rems['###DONT_SHOW_JUMPS###'] = $this->cObj->getSubpart($page, '###DONT_SHOW_JUMPS###');
		}else{
			$sims['###LIST_JUMPS###'] = $this->list_jumps();
			$rems['###SHOW_JUMPS###'] =  $this->cObj->getSubpart($page, '###SHOW_JUMPS###');
			$rems['###DONT_SHOW_JUMPS###'] = '';
		}
	}
	
	function showCalendarSelector(&$page, &$rems, &$sims){
		if ($this->conf['view.']['other.']['showCalendarSwitch']) {
			$rems['###SHOW_CALENDAR_SWITCH###'] = $this->cObj->getSubpart($page, '###SHOW_CALENDAR_SWITCH###');
			$this->list_calendars($sims);
		}else{
			$rems['###SHOW_CALENDAR_SWITCH###'] = $this->cObj->getSubpart($page, '###SHOW_CALENDAR_SWITCH###');
			$rems['###SHOW_CALENDAR_SWITCH###'] = '';
		}
	}
	
	function showBackLink(&$page, &$rems, &$sims){
		$viewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$viewParams['view'].'_view').'"';
		if(count($this->conf['view.']['defaultView'])==1){
			$rems['###BACKLINK###'] = '';
		}else{
			$this->controller->addBacklink($sims);
		}
	}
	
	function showLegend(&$page, &$rems, &$sims){
		$this->list_legend($sims['###LEGEND###']);
	}
	
	function showList(&$page, &$rems, &$sims){
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0, 0, 0, $this_month, $this_day, $this_year);

		if ($this->conf['view.']['month.']['thisMonthsEvents']) {
			$listSubpart =  $this->cObj->getSubpart($page, '###SHOWBOTTOMEVENTS###');
			
			$tx_cal_listview = t3lib_div::makeInstanceService('cal_view', 'list', 'list');
			$starttime = gmmktime(0,0,0,$this_month,1,$this_year);
			$endtime = gmmktime(0,0,0,$this_month+1,1,$this_year);
			$sims['###LIST###'] = $tx_cal_listview->drawList($this->master_array,'',$starttime,$endtime);
			$rems['###SHOWBOTTOMEVENTS###'] = $this->cObj->substituteMarkerArrayCached($listSubpart, $sims, $rems, array ());
		}else if($this->conf['view']=='week'){
			$weekStartDay = $this->conf['view.']['weekStartDay'];
			$dateOfWeek = tx_cal_calendar :: dateOfWeek($unix_time, $weekStartDay, $weekStartDay);
			$starttime = strtotime($dateOfWeek);
			$starttime += strtotimeOffset($starttime);
			$endtime = $starttime + 604799;
		}else if($this->conf['view']=='year'){
			$starttime = gmmktime(0,0,0,1,1,$this_year);
			$endtime = gmmktime(0,0,0,1,1,$this_year+1);
		} else {
			$rems['###SHOWBOTTOMEVENTS###'] = '';
		}
	}

	function finish(&$page, &$rems){
		$page = $this->checkForMonthMarker($page);
		$sims = array();
		
		/* Extract the markers from the template and loop over them */
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $page, $match);
		$allMarkers = array_unique($match[1]);
		foreach($allMarkers as $marker){
			switch($marker){
				case 'LOGIN':
					$this->showUserLogin($page, $rems, $sims);
					unset($allMarkers['LOGOUT']);
					break;
				case 'SHOW_CALENDAR_SWITCH':
					$this->showCalendarSelector($page, $rems, $sims);
					break;
				case 'SHOW_ADMINLINK':
					$this->showAdminLink($page, $rems, $sims);
					break;
				case 'TOMORROWS_EVENTS':
					$this->showTomorrowsEvents($page, $rems, $sims);
					break;
				case 'VTODO':
					$this->showTodos($page, $rems, $sims);
					break;
				case 'SHOW_ICSLINK':
					$this->showIcsLink($page, $rems, $sims);
					break;
				case 'SHOW_SEARCH':
					$this->showSearch($page, $rems, $sims);
					break;
				case 'SHOW_JUMPS':
					$this->showJumps($page, $rems, $sims);
					break;
				case 'SHOWBOTTOMEVENTS':
					$this->showList($page, $rems, $sims);
					break;
				default:
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div::makeInstanceService(substr($marker,8), 'module');
						if(is_object($module)){
							$rems['###'.$marker.'###'] = $module->start($this);
						}
					}
					break;
			}
		}
		
		$this->showBackLink($page, $rems, $sims);
		$this->showLegend($page, $rems, $sims);
		
		if ($this->rightsObj->isAllowedToCreateEvents()) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
			$sims['###CREATE_EVENT_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['list.']['addIcon'], array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
		} else {
			$sims['###CREATE_EVENT_LINK###'] = '';
		}
		
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		$page = $this->replaceLinksToOtherViews($page);
		
		// select for calendars
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		
		/* Extract the markers from the template and loop over them */
		preg_match_all('/###([A-Z0-9_-|]*)\###/', $page, $match);
		$allMarkers = array_unique($match[1]);		
		foreach($allMarkers as $marker) {
			switch($marker) {
				case 'LIST_ICALS':
					$sims['###LIST_ICALS###'] = ''; //display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED));
					break;
				case 'LIST_YEARS':
					$sims['###LIST_YEARS###'] = $this->list_years($this_year, $this->conf['view.']['other.']['dateFormatYearJump'], 1);
					break;
				case 'LIST_MONTHS':
					$sims['###LIST_MONTHS###'] = $this->list_months($this_year, $this->conf['view.']['other.']['dateFormatMonthJump']);
					break;
				case 'LIST_WEEKS':
					$sims['###LIST_WEEKS###'] = $this->list_weeks($this_year, $this->conf['view.']['other.']['dateFormatWeekJump'], $this->conf['view.']['weekStartDay']);
					break;
				case 'QUERY':
					$sims['###QUERY###'] = strip_tags($this->controller->piVars['query']);
					break;
				case 'LASTVIEW':
					$sims['###LASTVIEW###'] = strip_tags($this->controller->extendLastView());
					break;
				case 'THIS_VIEW':
					$sims['###THIS_VIEW###'] = $this->conf['view'];
					break;
				case 'TYPE':
					$sims['###TYPE###'] = $this->conf['type'];
					break;
				case 'OPTION':
					$sims['###OPTION###'] = $this->conf['option'];
					break;
				case 'CALENDAR':
					$sims['###CALENDAR###'] = $this->conf['calendar'];
					break;
				case 'PAGE_ID':
					$sims['###PAGE_ID###'] = $this->conf['page_id'];
					break;
				case 'LASTVIEW':
					$sims['###LASTVIEW###'] = $this->controller->extendLastView();
					break;
				case 'CURRENT_VIEW':
					$sims['###CURRENT_VIEW###'] = $this->conf['view'];
					break;
				case 'SEARCH_ACTION_URL':
					$sims['###SEARCH_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'search_event'));
					break;
				case 'CREATE_CALENDAR':
					$sims['###CREATE_CALENDAR###'] = $this->getCreateCalendarLink();
					break;
				case 'CALENDAR_LIST':
					$this->getCalendarList($page, $rems, $sims);
					break;
			}
			
		}

		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array (), array ());
		
		/* Extract the markers from the template and loop over them */
		preg_match_all('/###([A-Z0-9_-|]*)\###/', $page, $match);
		$allMarkers = array_unique($match[1]);
		foreach($allMarkers as $marker) {
			switch($marker) {
				case 'REFRESH':
					$sims['###REFRESH###'] = $this->controller->pi_getLL('l_refresh');
					break;
				case 'IMG_PATH':
					$sims['###IMG_PATH###'] = expandPath($this->conf['view.']['imagePath']);
					break;
				case 'CATEGORYURL':
					$sims['###CATEGORYURL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>$this->conf['view']));
					break;
				case 'MONTH_MENU':
					$sims['###MONTH_MENU###'] = $this->getMonthMenu($this->conf['view.']['other.']['monthMenu.']);
					break;
			}
		}
		$this->content = $this->cObj->substituteMarkerArrayCached($page, $sims, array (), array ());
		$GLOBALS['TSFE']->ATagParams = $this->tempATagParam;

		return $this->content;
	}


	function checkForMonthMarker($page) {

		$getdate = $this->conf['getdate'];
		if($this->conf['view.']['month.']['navigation']==1){
			$page = str_replace('###CALENDAR_NAV###', '', $page);
		}else{
			$template = $this->cObj->fileResource($this->conf['view.']['month.']['calendarNavTemplate']);
			if ($template == '') {
				return '<h3>calendar: no calendar_nav template file found:</h3>'.$this->conf['view.']['month.']['calendarNavTemplate'];
			}
			$page = str_replace('###CALENDAR_NAV###', $template, $page);
		}
		$match = array();
		preg_match_all('!\###MONTH_([A-Z]*)\|?([+|-])([0-9]{1,2})\###!is', $page, $match);
		if (sizeof($match) > 0) {
			$i = 0;
			foreach ($match[1] as $key => $val) {
				if ($match[1][$i] == 'SMALL') {
					$template_file = $this->cObj->fileResource($this->conf['view.']['month.']['monthSmallTemplate']);
					$type = 'small';
					$offset = $match[2][$i].$match[3][$i];
				}
				elseif ($match[1][$i] == 'MEDIUM') {
					$template_file = $this->cObj->fileResource($this->conf['view.']['month.']['monthMediumTemplate']);
					$type = 'medium';
					$offset = $match[3][$i];
				} else {
					$template_file = $this->cObj->fileResource($this->conf['view.']['month.']['monthLargeTemplate']);
					$type = 'large';
					$offset = $match[2][$i].$match[3][$i];
				}
				$data = $this->_draw_month($template_file, $offset, $type);

				$page = str_replace($match[0][$i], $data, $page);
				$i ++;
			}
		}

		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);
		
		$display_date = gmstrftime($this->conf['view.']['month.']['dateFormatMonth'], $unix_time);

		$next_year = ($this_year+1).$this_month.$this_day;
		$prev_year = ($this_year-1).$this_month.$this_day;

		$next_month = gmdate('Ymd',gmmktime(0,0,0,$this_month+1,$this_day,$this_year));
		$prev_month = gmdate('Ymd',gmmktime(0,0,0,$this_month-1,$this_day,$this_year));
		$nextmonthlinktext = $this->cObj->getSubpart($page, '###NEXT_MONTHLINKTEXT###');
		$prevmonthlinktext = $this->cObj->getSubpart($page, '###PREV_MONTHLINKTEXT###');

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
		if (!empty ($this->conf['view.']['month.']['monthViewPid'])) {
			$rems['###NEXT_MONTHLINK###'] = $this->controller->pi_linkTP_keepPIvars($nextmonthlinktext, array ('getdate' => $next_month, 'view' => 'month'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
			$rems['###PREV_MONTHLINK###'] = $this->controller->pi_linkTP_keepPIvars($prevmonthlinktext, array ('getdate' => $prev_month, 'view' => 'month'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
		} else {
			$rems['###NEXT_MONTHLINK###'] = $this->controller->pi_linkTP_keepPIvars($nextmonthlinktext, array ('getdate' => $next_month, 'view' => 'month'), $this->conf['cache'], $this->conf['clear_anyway']);
			$rems['###PREV_MONTHLINK###'] = $this->controller->pi_linkTP_keepPIvars($prevmonthlinktext, array ('getdate' => $prev_month, 'view' => 'month'), $this->conf['cache'], $this->conf['clear_anyway']);
		}

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		$prevyearlinktext = $this->cObj->getSubpart($page, '###PREV_YEARLINKTEXT###');
		$nextyearlinktext = $this->cObj->getSubpart($page, '###NEXT_YEARLINKTEXT###');
		if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
			$rems['###PREV_YEARLINK###'] = $this->controller->pi_linkTP_keepPIvars($prevyearlinktext, array ('getdate' => $prev_year, 'view' => 'year'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
			$rems['###NEXT_YEARLINK###'] = $this->controller->pi_linkTP_keepPIvars($nextyearlinktext, array ('getdate' => $next_year, 'view' => 'year'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
		} else {
			$rems['###PREV_YEARLINK###'] = $this->controller->pi_linkTP_keepPIvars($prevyearlinktext, array ('getdate' => $prev_year, 'view' => 'year'), $this->conf['cache'], $this->conf['clear_anyway']);
			$rems['###NEXT_YEARLINK###'] = $this->controller->pi_linkTP_keepPIvars($nextyearlinktext, array ('getdate' => $next_year, 'view' => 'year'), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		$weekStartDay = $this->conf['view.']['weekStartDay'];
		$start_month_day = tx_cal_calendar :: dateOfWeek(gmmktime(0,0,0,$this_month,1,$this_year), $weekStartDay, $weekStartDay);

		$num_of_events2 = 0;

		$languageArray = array (
			'getdate' => $getdate,
			'display_date' => $display_date,
			'next_month' => $next_month,
			'prev_month' => $prev_month,
			'l_calendar' => $this->controller->pi_getLL('l_calendar'),
			'calendar_name' => $this->conf['calendarName'],
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
		if($this->rightsObj->isViewEnabled($this->conf['view.']['dayLinkTarget']) || $this->conf['view.'][$this->conf['view.']['dayLinkTarget'].'.'][$this->conf['view.']['dayLinkTarget'].'ViewPid']){
			$dayviewlinktext = $this->cObj->getSubpart($template, '###DAYVIEWLINKTEXT###');
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
			$rems['###DAYVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($dayviewlinktext, array ('view' => $this->conf['view.']['dayLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
		}else{
			$rems['###DAYVIEWLINK###'] = '';
		}
		if($this->rightsObj->isViewEnabled($this->conf['view.']['weekLinkTarget']) || $this->conf['view.'][$this->conf['view.']['weekLinkTarget'].'.'][$this->conf['view.']['weekLinkTarget'].'ViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
			$weekviewlinktext = $this->cObj->getSubpart($template, '###WEEKVIEWLINKTEXT###');
			$rems['###WEEKVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($weekviewlinktext, array ('view' => $this->conf['view.']['weekLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
		}else{
			$rems['###WEEKVIEWLINK###'] = '';
		}
		if($this->rightsObj->isViewEnabled('month') || $this->conf['view.']['month.']['monthViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
			$monthviewlinktext = $this->cObj->getSubpart($template, '###MONTHVIEWLINKTEXT###');
			$rems['###MONTHVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($monthviewlinktext, array ('view' => 'month', 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
		}else{
			$rems['###MONTHVIEWLINK###'] = '';
		}
		if($this->rightsObj->isViewEnabled('year') || $this->conf['view.']['year.']['yearViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
			$yearviewlinktext = $this->cObj->getSubpart($template, '###YEARVIEWLINKTEXT###');
			$rems['###YEARVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($yearviewlinktext, array ('view' => 'year', 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['yearViewPid']);
		}else{
			$rems['###YEARVIEWLINK###'] = '';
		}
		$return = $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		return $return;
	}

	function list_jumps() {
		$today = gmdate('Ymd');
		$tmp = $this->conf['view.']['other.']['optionString'];
		$tmp = str_replace('###VALUE###','#',$tmp);
		$return = str_replace('###NAME###',$this->controller->pi_getLL('l_jump'),$tmp);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
		if (!empty ($this->conf['view.']['day.']['dayViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['dayLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['dayLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway']);
		}

		$link = preg_split('/\"/', $link);
		$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
		$tmp = $this->conf['view.']['other.']['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_goday'),$tmp);

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
		if (!empty ($this->conf['view.']['week.']['weekViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['weekLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['weekLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
		$tmp = $this->conf['view.']['other.']['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_goweek'),$tmp);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
		if (!empty ($this->conf['view.']['month.']['monthViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
		$tmp = $this->conf['view.']['other.']['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_gomonth'),$tmp);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
		$tmp = $this->conf['view.']['other.']['optionString'];
		$tmp = str_replace('###VALUE###',$link[1],$tmp);
		$return .= str_replace('###NAME###',$this->controller->pi_getLL('l_goyear'),$tmp);
		
		return $return;
	}

	function list_legend(&$return) {

		$this->conf['view.']['category.']['tree.']['category'] = $this->conf['category'];
		$this->conf['view.']['category.']['tree.']['calendar'] = '0,'.$this->conf['calendar'];
		$categoryArray = $this->modelObj->findAllCategories('','',$this->conf['pidList']);

		$return = $this->getCategorySelectionTree($this->conf['view.']['category.']['tree.'], $categoryArray, $this->conf['view.']['other.']['showCategorySelection']);
		$return = $this->cObj->stdWrap($return, $this->conf['view.']['other.']['legend_stdWrap.']);
	}
	
	function getMonthMenu($conf) {
		$month = gmdate('m');
		$year = gmdate('Y');
		if($conf['monthStart.']['thisMonth']){
			$month_time = tx_cal_calendar::calculateStartMonthTime(time());
		}else{
			$month_time = gmmktime(0,0,0,$conf['monthStart'],1,$conf['yearStart']);
			$month = $conf['monthStart'];
			$year = $conf['yearStart'];
		}

		for ($i = 0; $i < $conf['count']; $i ++) {
			$monthdate = gmdate('Ymd', $month_time);
			$month_month = gmdate('m', $month_time);
			$select_month = gmstrftime($conf['format'], $month_time);
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
			if($this->rightsObj->isViewEnabled('month') || $this->conf['view.']['month.']['monthViewPid']){
				$link = $this->controller->pi_linkTP_keepPIvars($select_month, array ('getdate' => $monthdate, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
			}else{
				$link = $select_month;
			}

			$return .= $this->cObj->stdWrap($link, $conf['month_stdWrap.']);

			$month++;
			$month_time = gmmktime(0,0,0,$month,1,$year);
		}
		return $return;
	}
	
	function getCategorySelectionTree($treeConf, $categoryArray, $renderAsForm = false){
		$treeHtml = '';
		
		foreach($categoryArray as $modelCategoryArray){
			$categoryArrayByUid = $modelCategoryArray[0];
			$categoryArrayByCalendarUid = $modelCategoryArray[2];
		
			$parentCategoryArray = array();
			foreach($categoryArrayByUid as $category){
				$parentCategoryArray[$category->getParentUid()][] = $category;
			}
	
			foreach($categoryArrayByCalendarUid as $calendarTitle => $calendarCategoryArray){
				$calendarParams = explode('###',$calendarTitle);
				$calendarTitle = $calendarParams[1];
				$calendarUid = $calendarParams[0];
				if($calendarParams[2]){
					$calendarType = $calendarParams[2];
					$calendarService = $this->getCalendarService();
					$calendar = $calendarService->find($calendarUid,$this->conf['pidList']);
					$calendarTitle = $calendar->getTitle().$calendar->getEditLink();
				}
				
				if(intval($treeConf['calendar'])==$treeConf['calendar']){
					$ids = explode(',',$treeConf['calendar']);
					if(!in_array($calendarUid,$ids)) {
						continue;
					}
				}else{
					continue;
				}
				$treeHtml .= $this->cObj->stdWrap($calendarTitle,$treeConf['calendarTitle_stdWrap.']);
				if(empty($calendarCategoryArray)){
					$treeHtml .= $this->cObj->stdWrap($treeConf['emptyElement'],$treeConf['emptyElement.']);
				}else{
					foreach($calendarCategoryArray as $rootCategoryId){
						$rootCategory = $categoryArrayByUid[$rootCategoryId];
						if($rootCategory->getParentUid() == 0 || !$categoryArrayByUid[$rootCategory->getParentUid()]){
							$treeHtml .= $this->cObj->stdWrap($this->addSubCategory($treeConf, $parentCategoryArray, $rootCategory,0,$renderAsForm), $treeConf['rootElement.']);
						}
					}
				}
			}
		}

		if($renderAsForm){
			$treeHtml .= $treeConf['categorySelectorSubmit'];
		}
		return $treeHtml;
	}
	
	function addSubCategory(&$treeConf, &$parentCategoryArray, &$parentCategory, $level, $renderAsForm){
		$level++;
		$treeHtml = '';
		if($renderAsForm){
			$selectedCategories = array();
			if($treeConf['category']){
				$selectedCategories = explode(',',$treeConf['category']);
			}
			$treeHtml .= $this->cObj->stdWrap(((in_array($parentCategory->getUid(),$selectedCategories) || empty($selectedCategories))?' checked="checked"':''), $treeConf['selector.']);
		}
		$treeHtml .= $treeConf['element'];
		$sims = array();
		$rems = array();
		$wrapper = array();
		$parentCategory->getCategoryMarker($treeHtml,$rems,$sims,$wrapper);
		$sims['###LEVEL###'] = $level;
		$treeHtml = $this->cObj->substituteMarkerArrayCached($treeHtml, $sims, $rems, $wrapper);
		
		$categoryArray = $parentCategoryArray[$parentCategory->getUid()];
		if(is_array($categoryArray)){
			
			$tempHtml = $treeConf['subElement'];
			$sims = array();
			$rems = array();
			$wrapper = array();
			$parentCategory->getCategoryMarker($tempHtml,$rems,$sims,$wrapper);
			$sims['###LEVEL###'] = $level;
			$treeHtml .= $this->cObj->substituteMarkerArrayCached($tempHtml, $sims, $rems, $wrapper);
			
			foreach($categoryArray as $category){
				
				$treeHtml .= $this->cObj->stdWrap($this->addSubCategory($treeConf,$parentCategoryArray, $category, $level, $renderAsForm), $treeConf['subElement_wrap.']);
				
			}
			
			
			$treeHtml .= $treeConf['subElement_pre'];
		}	
		return $treeHtml;
	}
	
	function getCreateCalendarLink(){
		if($this->rightsObj->isAllowedToCreateCalendar()){
			$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_create_calendar') . '" alt="' . $this->controller->pi_getLL('l_create_calendar') . '"';
			return  $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['calendar.']['addIcon'], array (
				'view' => 'create_calendar',
			'type' => 'tx_cal_calendar', 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['createCalendarViewPid']);
		}
		return '';
	}
	
	function getCalendarList(&$page, &$rems, &$sims){
		$temp_sims = array();
		$this->list_calendars($temp_sims);
		$tempArray = explode('<option',$temp_sims['###CALENDAR_IDS###']);
		unset($tempArray[0]);
		unset($tempArray[1]);
		foreach($tempArray as $value){
			$sims['###CALENDAR_LIST###'] .= '<option'.$value;
		}
	}

	function list_months($this_year, $dateFormat_month) {
		$month = 1;
		$month_time = gmmktime(0,0,0,$month,1,$this_year);//strtotime($this_year.'-01-01');
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_month = $day_array2[2];
		
		for ($i = 0; $i < 12; $i ++) {
			$monthdate = gmdate('Ymd', $month_time);
			$month_month = gmdate('m', $month_time);
			$select_month = gmstrftime($dateFormat_month, $month_time);
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
			if (!empty ($this->conf['view.']['month.']['monthViewPid'])) {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $monthdate, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $monthdate, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
			if ($month_month == $this_month) {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listMonthSelected_stdWrap.']);
				$return .= str_replace('###MONTH###',$select_month,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listMonth_stdWrap.']);
				$return .= str_replace('###MONTH###',$select_month,$tmp);
			}
			$month++;
			$month_time = gmmktime(0,0,0,$month,1,$this_year);
		}
		return $return;
	}

	function list_years($this_year, $dateFormat, $num_years) {
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);
		
		
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		for ($i = 0; $i < $num_years; $i ++) {
			$offset = $num_years - $i;
			$prev_date = ($this_year-$offset).$this_month.$this_day;

			$prev_year = gmstrftime($dateFormat, gmmktime(0,0,0,$this_month,$this_day,$this_year-$offset));
			if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $prev_date, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $prev_date, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
			$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYear_stdWrap.']);
			$return .= str_replace('###YEAR###',$prev_year,$tmp);
		}

		$getdate_year = strftime($dateFormat, $unix_time);
		if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $this->conf['getdate'], 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $this->conf['getdate'], 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
		$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYearSelected_stdWrap.']);
		$return .= str_replace('###YEAR###',$getdate_year,$tmp);
		for ($i = 0; $i < $num_years; $i ++) {
			$offset = $i +1;
			$next_date = ($this_year+$offset).$this_month.$this_day;
			$next_year = gmstrftime($dateFormat, gmmktime(0,0,0,$this_month,$this_day,$this_year+$offset));
			if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $next_date, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $next_date, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
			$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYear_stdWrap.']);
			$return .= str_replace('###YEAR###',$next_year,$tmp);
		}

		return $return;
	}

	function list_weeks($this_year, $dateFormat_week_jump, $weekStartDay) {
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);
		$start_week_time = strtotime(tx_cal_calendar :: dateOfWeek(gmmktime(0,0,0,1,1,$this_year), $weekStartDay, $weekStartDay));
		$start_week_time += strtotimeOffset($start_week_time);

		$end_week_time = $start_week_time + (6 * 24 * 60 * 60);

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
		do {
			$weekdate = gmdate('Ymd', $start_week_time);
			$select_week1 = gmstrftime($dateFormat_week_jump, $start_week_time);
			$select_week2 = gmstrftime($dateFormat_week_jump, $end_week_time);


			if (!empty ($this->conf['view.']['week.']['weekViewPid'])) {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $weekdate, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $weekdate, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
			if (($unix_time >= $start_week_time) && ($unix_time <= $end_week_time)) {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listWeeksSelected_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listWeeks_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			}
			$start_week_time = strtotime('+1 week', $start_week_time);
			$end_week_time = $start_week_time + (6 * 24 * 60 * 60);
		} while (gmdate('Y', $start_week_time) <= $this_year);

		return $return;
	}

	function list_calendars(&$sims){
		$tx_cal_modelcontroller = t3lib_div :: makeInstanceClassName('tx_cal_modelcontroller');
		$modelObj = new $tx_cal_modelcontroller ($this->controller);
		$calendarService = $modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
		$calendarIds = $calendarService->getIdsFromTable('',$this->conf['pidList'], true,true,true);
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

		$sims['###L_CALENDAR###'] = $this->controller->pi_getLL('l_calendar');
		$sims['###CALENDAR_IDS###'] = $calendar;
		$sims['###CHANGE_CALENDAR_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>$this->conf['view']));
		return $calendar;
	}



	function tomorrows_events($template) {

		// , $next_day, $timeFormat, $tomorrows_events_lines
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);

		$next_day = gmdate('Ymd', gmmktime(0,0,0,$this_month,$this_day+1,$this_year));

		$match1 = $this->cObj->getSubpart($template, '###T_ALLDAY_SWITCH###');
		$match2 = $this->cObj->getSubpart($template, '###T_EVENT_SWITCH###');
		$loop_t_ad = trim($match1);
		$loop_t_e = trim($match2);
		$return_adtmp = '';
		$return_etmp = '';

		if (is_array($this->master_array[$next_day]) && sizeof($this->master_array[$next_day]) > 0) {
			$replace_ad = '';
			$replace_e = '';
			foreach ($this->master_array[$next_day] as $cal_time => $event_times) {
				foreach ($event_times as $uid => $val) {
					$wrapped['###EVENT_LINK###'] = explode('|',$this->controller->getLinkToEvent($val, '|',$this->conf['view'],($this->conf['getdate']+1), $this->conf['view.']['other.']['tomorrowsEvents_stdWrap.']));
					$return = $wrapped['###EVENT_LINK###'][0].$val->renderTomorrowsEvent().$wrapped['###EVENT_LINK###'][1];
					if ($val->getStartHour() == 0) {
						$replace_ad .= $return;
					} else {
						$replace_e .= $return;
					}
				}
			}

			$rems['###T_ALLDAY_SWITCH###'] = str_replace('###T_ALLDAY###', $replace_ad, $loop_t_ad);
			$rems['###T_EVENT_SWITCH###'] = str_replace('###T_EVENT###', $replace_e, $loop_t_e);
			return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
		}
		$rems['###T_ALLDAY_SWITCH###'] = '';
		$rems['###T_EVENT_SWITCH###'] = '';
		return $this->cObj->substituteMarkerArrayCached($template, array (), $rems, array ());
	}

	function get_vtodo($template, $next_day, $timeFormat, $tomorrows_events_lines, $show_completed, $show_todos) {
		$match1 = array();
		$match2 = array();
		$match3 = array();
		preg_match('!<\!-- switch show_completed on -->(.*)<\!-- switch show_completed off -->!is', $this->page, $match1);
		preg_match('!<\!-- switch show_important on -->(.*)<\!-- switch show_important off -->!is', $this->page, $match2);
		preg_match('!<\!-- switch show_normal on -->(.*)<\!-- switch show_normal off -->!is', $this->page, $match3);
		$completed = trim($match1[1]);
		$important = trim($match2[1]);
		$normal = trim($match3[1]);
		$nugget2 = '';

		if (is_array($this->master_array['-2'])) {
			foreach ($this->master_array['-2'] as $vtodo_times) {
				foreach ($vtodo_times as $val) {
					$vtodo_text = stripslashes(urldecode($val['vtodo_text']));
					if ($vtodo_text != '') {
						if (isset ($val['description'])) {
							$description = stripslashes(urldecode($val['description']));
						} else {
							$description = '';
						}
						$completed_date = $val['completed_date'];
						$event_calna = $val['calname'];
						$status = $val['status'];
						$priority = $val['priority'];
						$start_date = $val['start_date'];
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
	
	function getFreq($eventFreq){
		$freq_type = '';
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

		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);

		$weekStartDay = $this->conf['view.']['weekStartDay'];
		$loop_wd = $this->cObj->getSubpart($page, '###LOOPWEEKDAY###');
		$t_month = $this->cObj->getSubpart($page, '###SWITCHMONTHDAY###');
		$startweek = $this->cObj->getSubpart($page, '###LOOPMONTHWEEKS_DAYS###');
		$endweek = $this->cObj->getSubpart($page, '###LOOPMONTHDAYS_WEEKS###');
		$weeknum = $this->cObj->getSubpart($page, '###LOOPWEEK_NUMS###');

		if ($type != 'medium') {
			$fake_getdate_time = gmmktime(0,0,0,$this_month + intval($offset),15,$this_year);

		} else {
			$fake_getdate_time = gmmktime(0,0,0,intval($offset),15,$this_year);
		}
		
		$minical_month = gmdate('m', $fake_getdate_time);
		$minical_year = gmdate('Y', $fake_getdate_time);
//		$first_of_month = $minical_year.$minical_month.'01';
//		$first_of_year = $minical_year.'0101';

		$month_title = gmstrftime($this->conf['view.']['month.']['dateFormatMonth'], $fake_getdate_time);
		$month_date = gmdate('Ymd', $fake_getdate_time);

		$view_array = array ();
		if(!$this->viewarray){
			if (count ($this->master_array>0)) {
				foreach ($this->master_array as $ovlKey => $ovlValue) {
					if($ovlKey=='legend'){
						continue;
					}
					foreach ($ovlValue as $ovl_time_key => $ovl_time_Value) {
						foreach ($ovl_time_Value as $ovl2Value) {
							$starttime = $ovl2Value->getStarttime();
							if(gmdate('Ymd',$starttime)>=$ovlKey){
								$endtime = $ovl2Value->getEndtime();
								if($ovl_time_key=='-1'){
									$endtime += 1;
								}

								for ($j = $starttime; $j < $endtime; $j = $j + 60 * 60 * 24) {
									$view_array[gmdate('Ymd',$j)]['0000'][count($view_array[gmdate('Ymd',$j)]['0000'])]=$ovl2Value;
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
			$langtype = '%a';
			$typeSize = 2;
		}
		elseif ($type == 'medium') {
			$langtype = '%a';
		}
		elseif ($type == 'large') {
			$langtype = '%A';
		}
		
		$start_day = tx_cal_calendar :: dateOfWeek($unix_time, $weekStartDay, $weekStartDay);

		$weekday_loop .= str_replace('###WEEKNUM###', '&nbsp;', $weeknum);
		for ($i = 0; $i < 7; $i ++) {
			ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $start_day, $day_array2);
			$start_day_time = gmmktime(0,0,0,$day_array2[2],$day_array2[3],$day_array2[1]);
			$weekday = gmstrftime($langtype, $start_day_time);
			if($typeSize){
				$weekday = $this->cs_convert->substr(getCharset(),$weekday,0,$typeSize);
			}
			$start_day++;
			$loop_tmp = str_replace('###WEEKDAY###', $weekday, $loop_wd);
			$weekday_loop .= $loop_tmp;
		}
		$weekday_loop .= $endweek;
		$weekStart = tx_cal_calendar :: dateOfWeek(gmmktime(0,0,0,$minical_month,1,$minical_year), $weekStartDay, $weekStartDay);
		$start_day = strtotime($weekStart);
		$start_day += strtotimeOffset($start_day);

		$i = 0;
		$whole_month = TRUE;
		$isAllowedToCreateEvents = $this->rightsObj->isAllowedToCreateEvents();
		$createOffset = $this->conf['rights.']['create.']['event.']['timeOffset'] * 60 + strtotimeOffset($unix_time);
		do {
			$daylink = gmdate('Ymd', $start_day);
			if ($i == 0){
				$middle .= $startweek;
				$num = gmdate('W', $start_day+86400);
				$hasEvent = false;
				for($j = 0; $j < 7; $j++){
					if(is_array($this->viewarray[gmdate('Ymd', $start_day+(86400*$j))]) || $isAllowedToCreateEvents){
						$hasEvent = true;
						break;
					}
				}
				if(($this->rightsObj->isViewEnabled($this->conf['view.']['weekLinkTarget']) || $this->conf['view.'][$this->conf['view.']['weekLinkTarget'].'.'][$this->conf['view.']['weekLinkTarget'].'ViewPid']) && $hasEvent){
					$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
					$num = $this->controller->pi_linkTP_keepPIvars($num, array ('getdate' => gmdate('Ymd',$start_day), 'view' => $this->conf['view.']['weekLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
				}
				$middle .= str_replace('###WEEKNUM###', $num, $weeknum);
			}
			$i ++;
			$switch = array ('###ALLDAY###' => '');
			$check_month = gmdate('m', $start_day);
			
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
			if ($daylink>=gmdate('Ymd') && $isAllowedToCreateEvents && $start_day+86400>(time()+$createOffset)) {
				$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
				$switch['###LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['week.']['addIcon'], array ('getdate' => $daylink, 'lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), 0, $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
			} else {
				$switch['###LINK###'] = '';
			}
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
			
			if(($this->rightsObj->isViewEnabled($this->conf['view.']['dayLinkTarget']) || $this->conf['view.'][$this->conf['view.']['dayLinkTarget'].'.'][$this->conf['view.']['dayLinkTarget'].'ViewPid']) && ($this->viewarray[$daylink] || $isAllowedToCreateEvents)){
				$switch['###LINK###'] .= $this->controller->pi_linkTP_keepPIvars(gmdate('j', $start_day), array ('getdate' => $daylink, 'view' => $this->conf['view.']['dayLinkTarget'], 'lastview' => $this->controller->extendLastView()), 0, $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
			}else{
				$switch['###LINK###'] .= gmdate('j', $start_day);
			}
			
			$style = '';
			
			
			if ($check_month != $minical_month) {
				$style .= $this->conf['view.']['month.']['monthOffStyle'].' ';
			}
			if ($daylink == $this->conf['getdate']) {
				$style .= $this->conf['view.']['month.']['monthSelectedStyle'].' ';
			}
			if ($daylink == gmdate('Ymd')) {
				$style .= $this->conf['view.']['month.']['monthTodayStyle'].' ';
			}
			if (gmdate('w',$start_day)==0 || gmdate('w',$start_day)==6) {
				$style .= $this->conf['view.']['month.']['monthWeekendStyle'].' ';
			}

			if ($type == 'small') {
				$style .= $this->conf['view.']['month.']['monthSmallStyle'].' ';
			}
			elseif ($type == 'medium') {
				$style .= $this->conf['view.']['month.']['monthMediumStyle'].' ';
			}
			elseif ($type == 'large') {
				$style .= $this->conf['view.']['month.']['monthLargeStyle'].' ';
			}

			$temp = str_replace('###STYLE###', $style, $t_month);
			$wraped = array();
			if ($this->viewarray[$daylink]) {
				foreach ($this->viewarray[$daylink] as $cal_time => $event_times) {
					foreach ($event_times as $uid => $val) {
						if($type == 'large'){
							$switch['###EVENT###'] .= $val->renderEventForMonth();
						}else if($type == 'medium'){
							$switch['###EVENT###'] .= $val->renderEventForYear();
						}else if ($type == 'small'){
							$switch['###LINK###'] = $this->cObj->stdWrap($switch['###LINK###'], $this->conf['view.']['month.']['smallLink_stdWrap.']);
							$style .= $this->conf['view.']['month.']['eventDayStyle'].' ';
							break;
						}
						
					}
				}
			}
			$switch['###EVENT###'] = (isset ($switch['###EVENT###'])) ? $switch['###EVENT###'] : '';
			$switch['###ALLDAY###'] = (isset ($switch['###ALLDAY###'])) ? $switch['###ALLDAY###'] : '';
			
            // Adds hook for processing of extra month day markers
		    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cal_controller']['extraMonthDayMarkerHook'])) {
			    foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cal_controller']['extraMonthDayMarkerHook'] as $_classRef) {
                    $_procObj = & t3lib_div::getUserObj($_classRef);
				    $switch = $_procObj->extraMonthDayMarkerProcessor($this,$daylink,$switch);
			    }
		    }
        
        
			$middle .= $this->cObj->substituteMarkerArrayCached($temp, $switch, array(), $wraped);

			$start_day += 86400; // 60 * 60 *24 -> strtotime('+1 day', $start_day);
			if ($i == 7) {
				$i = 0;
				$middle .= $endweek;
				$checkagain = gmdate('m', $start_day);
				if ($checkagain != $minical_month)
					$whole_month = FALSE;
			}
		} while ($whole_month == TRUE);


		$rems['###LOOPWEEKDAY###'] = $weekday_loop;
		$rems['###LOOPMONTHWEEKS###'] = $middle;
		$rems['###LOOPMONTHWEEKS_DAYS###'] = '';
		$rems['###LOOPWEEK_NUMS###'] = '';

        
        
		$return = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());

		if($this->rightsObj->isViewEnabled('month') || $this->conf['view.']['month.']['monthViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';#
			$month_link = $this->controller->pi_linkTP_keepPIvars($month_title, array ('getdate' => $month_date, 'view' => 'month'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
		}else{
			$month_link = $month_title;
		}

		$return = str_replace('###MONTH_LINK###', $month_link, $return);

		return $return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']);
}
?>
