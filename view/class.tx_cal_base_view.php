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
	var $eventArray;
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

	function getAdminLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###ADMIN_LINK###'] = '';
		if ($this->rightsObj->isAllowedToConfigure()) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_administration_view').'"';
			$parameter = array ('view' => 'admin', 'lastview' => $this->controller->extendLastView());
			$sims['###ADMIN_LINK###'] = sprintf($this->conf['view.']['admin.']['link_wrap'],$this->controller->pi_linkTP_keepPIvars($this->conf['view.']['admin.']['linkText'],$parameter, $this->conf['cache'], $this->conf['clear_anyway']));
		}
	}
	
	function getTomorrowsEventsMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###TOMORROWS_EVENTS###'] = '';
		if ($this->conf['view.']['other.']['showTomorrowEvents'] == 1) {
			$rems['###TOMORROWS_EVENTS###'] = $this->tomorrows_events($this->cObj->getSubpart($page, '###TOMORROWS_EVENTS###'));
		}
	}
	
	function getTodoMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###TODO###'] = '';
		if ($this->conf['view.']['other.']['showTodos'] == 1) {
			// TODO still to be implemented
		}
	}
	
	function getUserLoginMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###USER_LOGIN###'] = '';
		if ($this->conf['view.']['other.']['showLogin'] == 1) {
			$local_sims = array();
			$local_rems = array();
			$parameter = array ('view' => $this->conf['view']);
			$local_sims['###LOGIN_ACTION###'] = $this->controller->pi_linkTP_keepPIvars_url($parameter, $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['other.']['loginPageId']);

			if($this->rightsObj->isLoggedIn()){
				$local_sims['###LOGIN_TYPE###'] = 'logout';
				$local_sims['###L_LOGIN###'] = $this->controller->pi_getLL('l_logout');
				$local_sims['###L_LOGIN_BUTTON###'] = $this->controller->pi_getLL('l_logout');
				$local_sims['###USERNAME###'] = $this->rightsObj->getUserName();
				$local_rems['###LOGIN###'] = '';
			}else{
				$local_sims['###LOGIN_TYPE###'] = 'login';
				$local_sims['###L_LOGIN###'] = $this->controller->pi_getLL('l_login');
				$local_sims['###L_LOGIN_BUTTON###'] = $this->controller->pi_getLL('l_login');
				$local_rems['###LOGOUT###'] = '';
			}
			$local_sims['###USER_FOLDER###'] = $this->conf['view.']['other.']['userFolderId'];
			$local_sims['###REDIRECT_URL###'] = $this->controller->pi_linkTP_keepPIvars_url();
			$rems['###USER_LOGIN###'] = $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($page,'###USER_LOGIN###'), $local_sims, $local_rems, array());
		}
	}
	
	function getIcsLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###ICS_LINK###'] = '';
		if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_ics_view').'"';
			$sims['###ICS_LINK###'] = sprintf($this->conf['view.']['ics.']['link_wrap'],$this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_calendar_icslink'), array ('type' => 'tx_cal_category', 'view' => 'icslist', 'lastview' => $this->conf['view']), $this->conf['cache'], $this->conf['clear_anyway']));
		}
	}
	
	function getSearchMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###SHOW_SEARCH###'] = '';
		if ($this->conf['view.']['other.']['showSearch'] == 1) {
			$local_sims = array();
			$page = $this->replace_files($page, array ('search_box' => $this->conf['view.']['other.']['searchBoxTemplate']));
			$local_sims['###L_SEARCH###'] = $this->controller->pi_getLL('l_search');
			$local_sims['###L_DOSEARCH###'] = $this->controller->pi_getLL('l_dosearch');
			$local_sims['###GETDATE###'] = $this->conf['getdate'];
			$rems['###SHOW_SEARCH###'] = $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($page,'###SHOW_SEARCH###'), $local_sims, array(), array());
		}
	}
	
	function getJumpsMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###JUMPS###'] = '';
		if ($this->conf['view.']['other.']['showJumps'] == 1) {
			ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
			$this_day = $day_array2[3];
			$this_month = $day_array2[2];
			$this_year = $day_array2[1];
			$temp_sims = array();
			$temp_sims['###LIST_JUMPS###'] = $this->list_jumps();
			$temp_sims['###LIST_ICALS###'] = ''; //display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED));
			$temp_sims['###LIST_YEARS###'] = $this->list_years($this_year, $this->conf['view.']['other.']['dateFormatYearJump']);
			$temp_sims['###LIST_MONTHS###'] = $this->list_months($this_year, $this->conf['view.']['other.']['dateFormatMonthJump']);
			$temp_sims['###LIST_WEEKS###'] = $this->list_weeks($this_year, $this->conf['view.']['other.']['dateFormatWeekJump'], $this->conf['view.']['weekStartDay']);
			
			$rems['###JUMPS###'] = $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($page, '###JUMPS###'), $temp_sims, array(), array());
		}
	}
	
	function getCalendarSelectorMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###CALENDAR_SELECTOR###'] = '';
		if ($this->conf['view.']['other.']['showCalendarSelector']) {
			$temp_sims = array();
			$calendarService = $this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
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
	
			$temp_sims['###L_CALENDAR###'] = $this->controller->pi_getLL('l_calendar');
			$temp_sims['###CALENDAR_IDS###'] = $calendar;
			$temp_sims['###CHANGE_CALENDAR_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>$this->conf['view']));
			$rems['###CALENDAR_SELECTOR###'] = $this->cObj->substituteMarkerArrayCached($this->cObj->getSubpart($page, '###CALENDAR_SELECTOR###'), $local_sims, array(), array());
		}
	}
	
	function getBackLinkMarker(&$page, &$sims, &$rems, &$wrapped){
		$sims['###BACK_LINK###'] = '';
		if(count($this->conf['view.']['allowedViews'])>1){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$viewParams['view'].'_view').'"';
			$viewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
			$pid = array_shift($viewParams);
			$sims['###BACK_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_back'), $viewParams, $this->conf['cache'], $this->conf['clear_anyway'],$pid);
		}
	}
	
	function getLegendMarker(&$page, &$sims, &$rems, $view){
		$this->list_legend($sims['###LEGEND###']);
	}
	
	function getListMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###LIST###'] = '';
		$starttime = new tx_cal_date($this->conf['getdate']);
		$starttime->setTZbyId('UTC');
		$tx_cal_listview = t3lib_div::makeInstanceService('cal_view', 'list', 'list');
		$listSubpart =  $this->cObj->getSubpart($page, '###LIST###');
		if ($this->conf['view']=='month' && $this->conf['view.']['month.']['showListInMonthView']) {
			$starttime = tx_cal_calendar::calculateStartMonthTime($starttime);
			$endtime = tx_cal_calendar::calculateEndMonthTime($starttime);
			$rems['###LIST###'] = $tx_cal_listview->drawList($this->master_array,$listSubpart,$starttime,$endtime);
		}else if($this->conf['view']=='day'){
			$starttime = tx_cal_calendar::calculateStartDayTime($starttime);
			$endtime = tx_cal_calendar::calculateEndDayTime($starttime);
			$rems['###LIST###'] = $tx_cal_listview->drawList($this->master_array,$listSubpart,$starttime,$endtime);
		}else if($this->conf['view']=='week'){
			$starttime = tx_cal_calendar::calculateStartWeekTime($starttime);
			$endtime = tx_cal_calendar::calculateEndWeekTime($starttime);
			$rems['###LIST###'] = $tx_cal_listview->drawList($this->master_array,$listSubpart,$starttime,$endtime);
		}else if($this->conf['view']=='year'){
			$starttime = tx_cal_calendar::calculateStartYearTime($starttime);
			$endtime = tx_cal_calendar::calculateEndYearTime($starttime);
			$rems['###LIST###'] = $tx_cal_listview->drawList($this->master_array,$listSubpart,$starttime,$endtime);
		}
	}
	
	function getCreateEventLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###CREATE_EVENT_LINK###'] = '';
		if ($this->rightsObj->isAllowedToCreateEvent()) {
			$calcOffset = new tx_cal_date();
			//TODO find a better solution for CET
			$calcOffset->setTZbyID('CET');
			$createOffset = (intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60) + $calcOffset->tz->getOffset($calcOffset)/1000;
			$sims['###CREATE_EVENT_LINK###'] = $this->getCreateEventLink($view, '', new tx_cal_date(), $createOffset, true, '', '', $this->conf['view.']['day.']['dayStart']);
		}
	}

	function getCreateEventLink($view, $wrap, $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $remember, $class, $time){
		$tmp = '';
		if(!$this->rightsObj->isViewEnabled('create_event')){
			return $tmp;
		}
		$now = new tx_cal_date();
		$now->setTZbyId('UTC');
		$now->addSeconds($createOffset);
//debug($cal_time_obj->format('%Y%m%d %H%M').' after'.$now->format('%Y%m%d %H%M'),'hier:'.$createOffset);
		if ($cal_time_obj->after($now) && $isAllowedToCreateEvent) {
			$GLOBALS['TSFE']->ATagParams = '';
			if($this->conf['view.']['enableAjax']){
				$GLOBALS['TSFE']->ATagParams .= sprintf(' onclick="'.$this->conf['view.'][$view.'.']['event.']['addLinkOnClick'].'"',$time,$cal_time_obj->format('%Y%m%d'));
				$tmp .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.'][$view.'.']['event.']['addIcon'], array ('gettime' => $time, 'getdate'=>$cal_time_obj->format('%Y%m%d'), 'lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), 0, $this->conf['clear_anyway'],$this->conf['view.']['event.']['createEventViewPid']);
				if($wrap){
					$tmp = sprintf($wrap,'id="cell_'.$cal_time_obj->format('%Y%m%d').$time.'" ondblclick="javascript:eventUid=0;eventTime=\''.$time.'\';eventDate='.$cal_time_obj->format('%Y%m%d').';EventDialog.showDialog(this);" ',$remember,$class,$tmp,$cal_time_obj->format('%Y %m %d %H %M %s'));
				}
			}else{
				$linkConf = Array();
				$linkConf['no_cache'] = 0;
				$linkConf['useCacheHash'] = 0;
				$linkConf['additionalParams'] = '&tx_cal_controller[gettime]='.$time.'&tx_cal_controller[getdate]='.$cal_time_obj->format('%Y%m%d').'&tx_cal_controller[lastview]='.$this->controller->extendLastView().'&tx_cal_controller[view]=create_event';
				$linkConf['title'] = $this->controller->pi_getLL('l_create_event');
				$linkConf['section'] = 'default';
				$linkConf['parameter'] = $this->conf['view.']['event.']['createEventViewPid']?$this->conf['view.']['event.']['createEventViewPid']:$GLOBALS['TSFE']->id;

				$tmp .= $this->cObj->typolink($this->conf['view.'][$view.'.']['event.']['addIcon'],$linkConf);
				if($wrap){
					$tmp = sprintf($wrap,$remember,$class,$tmp,$cal_time_obj->format('%Y %m %d %H %M %s'));
				}
			}
		}else{
			if($this->conf['view.']['enableAjax']){
				$tmp = sprintf($wrap,'',$remember,$class,'');
			}else{
				$tmp = sprintf($wrap,$remember,$class,'');
			}
		}
		return $tmp;
	}

	function getQueryMarker(&$page, &$sims, &$rems, $view){
		$sims['###QUERY###'] = strip_tags($this->controller->piVars['query']);
	}

	function getLastviewMarker(&$page, &$sims, &$rems, $view){
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
	}

	function getThisViewMarker(&$page, &$sims, &$rems, $view){
		$sims['###THIS_VIEW###'] = $this->conf['view'];
	}

	function getTypeMarker(&$page, &$sims, &$rems, $view){
		$sims['###TYPE###'] = $this->conf['type'];
	}

	function getOptionMarker(&$page, &$sims, &$rems, $view){
		$sims['###OPTION###'] = $this->conf['option'];
	}

	function getCalendarMarker(&$page, &$sims, &$rems, $view){
		$sims['###CALENDAR###'] = $this->conf['calendar'];
	}

	function getPageIdMarker(&$page, &$sims, &$rems, $view){
		$sims['###PAGE_ID###'] = $this->conf['page_id'];
	}

	function getAjaxUrlMarker(&$page, &$sims, &$rems, $view){
		$sims['###AJAX_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array(),0,1);
	}

	function getAvailableCalendarMarker(&$page, &$sims, &$rems, $view){
		$calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar');
		$sims['###AVAILABLE_CALENDAR###'] = '<select id="calendarSelector">';
		foreach($calendarArray as $calendar){
			$sims['###AVAILABLE_CALENDAR###'] .= '<option value="'.$calendar->getUID().'" >'.$calendar->getTitle().'</option>';
		}
		$sims['###AVAILABLE_CALENDAR###'] .= '</select>';
	}

	function getPidMarker(&$page, &$sims, &$rems, $view){
		$sims['###PID###'] = $GLOBALS['TSFE']->id;
	}

	function getImgPathMarker(&$page, &$sims, &$rems, $view){
		$sims['###IMG_PATH###'] = expandPath($this->conf['view.']['imagePath']);
	}

	function getJsPathMarker(&$page, &$sims, &$rems, $view){
		$sims['###JS_PATH###'] = expandPath($this->conf['view.']['javascriptPath']);
	}

	function getCategoryurlMarker(&$page, &$sims, &$rems, $view){
		$sims['###CATEGORYURL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>$this->conf['view']));
	}

	function getMonthMenuMarker(&$page, &$sims, &$rems, $view){
		$sims['###MONTH_MENU###'] = $this->getMonthMenu($this->conf['view.']['other.']['monthMenu.']);
	}

	function getMarker(& $template, & $sims, & $rems, & $wrapped, $view='') {
		if($view==''){
			$view = $this->conf['view'];
		}
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		/*if($this->objectType=='event'){
		 debug($allMarkers);
		 }*/
		foreach ($allMarkers as $marker) {
			switch ($marker) {
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if(method_exists($this,$funcFromMarker)) {
						$this->$funcFromMarker($template, $sims, $rems, $wrapped, $view);
					}
					break;
			}
		}
	
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);
		/*if($this->objectType=='event'){
		 debug($allSingleMarkers);
		 }*/
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'IMG_PATH':
						//do nothing. we replace it at the end
					break;
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
						$this->$funcFromMarker($template, $sims, $rems, $view);
					}else if (preg_match('/MODULE__([A-Z0-9_-|])*/', $marker)) {
						$tmp=explode('___',substr($marker, 8));
						$modules[$tmp[0]][]=$tmp[1];
					} else if ($this->conf['view.'][$view.'.'][strtolower($marker)]) {
						$this->conf['view.'][$view.'.'][$this->objectType.'.']['value'] = $this->row[strtolower($marker)];
						$sims['###' . $marker . '###'] = $this->cObj->cObjGetSingle($this->conf['view.'][$view.'.'][strtolower($marker)],$this->conf['view.'][$view.'.'][strtolower($marker).'.']);
					} else {
						$sims['###' . $marker . '###'] = '';
					}
					break;
			}
		}
		
		#use alternativ way of MODULE__MARKER
		#syntax: ###MODULE__MODULENAME___MODULEMARKER###
		#collect them, call each Modul, retrieve Array of Markers and replace them
		#this allows to spread the Module-Markers over complete template instead of one time
		#also work with old way of MODULE__-Marker

		if(is_array($modules)) {  #MODULE-MARKER FOUND
			foreach($modules as $themodule=>$markerArray) {
				$module = t3lib_div :: makeInstanceService($themodule, 'module');
				if (is_object($module)) {
					if($markerArray[0]=='') {
						$sims['###MODULE__'.$themodule.'###'] = $module->start($this); #old way
					} else {
						$moduleMarker= $module->start($this); # get Markerarray from Module
						foreach($moduleMarker as $key=>$val) {
							$sims['###MODULE__'.$themodule.'___'.$key.'###'] = $val;
						}
					}
				}
			}
		}

		$hookObjectsArr = $this->controller->getHookObjectsArray('searchForViewMarker');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchForViewMarker')) {
				$hookObj->postSearchForViewMarker($this, $template, $sims, $rems, $wrapped, $view);
			}
		}
	}

	function finish(&$page, &$rems){
		$page = $this->checkForMonthMarker($page);
		$sims = array();
		$wrapped = array();
		
		if($this->conf['view.']['enableAjax'] && 
				($this->rightsObj->isLoggedIn() && 
						($this->rightsObj->isAllowedTo('create','event') || $this->rightsObj->isAllowedTo('edit','event') || $this->rightsObj->isAllowedTo('delete','event'))
				) || $this->conf['rights.']['create.']['event.']['public']){
			$page = $this->cObj->fileResource($this->conf['view.']['event.']['ajaxTemplate']).$page;
			
			if(t3lib_extMgm::isLoaded('extjs')) {
				// include the file
				require_once(t3lib_extMgm::extPath("extjs")."class.tx_extjs.php");
				// define, which adapter to use
				tx_extjs::setAdapter('yui');
				// define, which css-template to use
				tx_extjs::setResource('default');
				tx_extjs::setCompressed(TRUE);
				tx_extjs::includeLib();
			}else{
				return "You have to install the 'extjs' extension to use the ajax features";
			}
		}
		
		$this->getMarker($page, $sims, $rems, $wrapped, $this->conf['view']);
		
		$GLOBALS['TSFE']->ATagParams = $this->tempATagParam;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, $wrapped);
		$sims = array();
		$rems = array();
		$this->getImgPathMarker($page, $sims, $rems, $this->conf['view']);
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, $wrapped);
	}


	function checkForMonthMarker($page) {

		if($this->conf['view.']['month.']['navigation']==1){
			$page = str_replace('###CALENDAR_NAV###', '', $page);
		}else{
			$template = $this->cObj->fileResource($this->conf['view.']['month.']['horizontalSidebarTemplate']);
			if ($template == '') {
				return '<h3>calendar: no calendar_nav template file found:</h3>'.$this->conf['view.']['month.']['horizontalSidebarTemplate'];
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

		$display_date = $this->controller->getDateTimeObject->format($this->conf['view.']['month.']['dateFormatMonth']);

		$next_year = ($this->conf['year']+1).sprintf("%02d", $this->conf['month']).sprintf("%02d", $this->conf['day']);
		$prev_year = ($this->conf['year']-1).sprintf("%02d", $this->conf['month']).sprintf("%02d", $this->conf['day']);
		
		$endOfNextMonth = new tx_cal_date(Date_Calc::endOfNextMonth($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$endOfNextMonth->setDay($this->conf['day']);
		
		$startOfPrevMonth = new tx_cal_date(Date_Calc::endOfPrevMonth($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$startOfPrevMonth->setDay($this->conf['day']);
		
		$next_month = $endOfNextMonth->format('%Y%m%d');
		$prev_month = $startOfPrevMonth->format('%Y%m%d');		
		
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
		$start_month_day = tx_cal_calendar::calculateStartMonthTime($this->controller->getDateTimeObject);
		$start_month_day = tx_cal_calendar::calculateStartWeekTime($start_month_day);

		$num_of_events2 = 0;

		$languageArray = array (
			'getdate' => $this->conf['getdate'],
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
			'this_year' => $this->conf['year'],
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

	function getDayviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$rems['###DAYVIEWLINK###'] = '';
		if($this->rightsObj->isViewEnabled($this->conf['view.']['dayLinkTarget']) || $this->conf['view.'][$this->conf['view.']['dayLinkTarget'].'.'][$this->conf['view.']['dayLinkTarget'].'ViewPid']){
			$dayviewlinktext = $this->cObj->getSubpart($template, '###DAYVIEWLINKTEXT###');
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
			$rems['###DAYVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($dayviewlinktext, array ('getdate'=>$this->conf['getdate'],'view' => $this->conf['view.']['dayLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
		}
	}
	
	function getWeekviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$rems['###WEEKVIEWLINK###'] = '';
		if($this->rightsObj->isViewEnabled($this->conf['view.']['weekLinkTarget']) || $this->conf['view.'][$this->conf['view.']['weekLinkTarget'].'.'][$this->conf['view.']['weekLinkTarget'].'ViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
			$weekviewlinktext = $this->cObj->getSubpart($template, '###WEEKVIEWLINKTEXT###');
			$rems['###WEEKVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($weekviewlinktext, array ('getdate'=>$this->conf['getdate'],'view' => $this->conf['view.']['weekLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
		}
	}
	
	function getMonthviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$rems['###MONTHVIEWLINK###'] = '';
		if($this->rightsObj->isViewEnabled('month') || $this->conf['view.']['month.']['monthViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
			$monthviewlinktext = $this->cObj->getSubpart($template, '###MONTHVIEWLINKTEXT###');
			$rems['###MONTHVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($monthviewlinktext, array ('getdate'=>$this->conf['getdate'],'view' => 'month', 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
		}
	}
	
	function getYearviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$rems['###YEARVIEWLINK###'] = '';
		if($this->rightsObj->isViewEnabled('year') || $this->conf['view.']['year.']['yearViewPid']){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
			$yearviewlinktext = $this->cObj->getSubpart($template, '###YEARVIEWLINKTEXT###');
			$rems['###YEARVIEWLINK###'] = $this->controller->pi_linkTP_keepPIvars($yearviewlinktext, array ('getdate'=>$this->conf['getdate'],'view' => 'year', 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['yearViewPid']);
		}
	}

	function list_jumps() {
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		
		// gmdate is ok.
		$return = sprintf($this->conf['view.']['other.']['optionString'],gmdate('Ymd'),$this->controller->pi_getLL('l_jump'));
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_day_view').'"';
		if (!empty ($this->conf['view.']['day.']['dayViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['dayLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['dayLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway']);
		}

		$link = preg_split('/\"/', $link);
		$return .= sprintf($this->conf['view.']['other.']['optionString'],t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1],$this->controller->pi_getLL('l_goday'));

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
		if (!empty ($this->conf['view.']['week.']['weekViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['weekLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => $this->conf['view.']['weekLinkTarget'],), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$return .= sprintf($this->conf['view.']['other.']['optionString'],t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1],$this->controller->pi_getLL('l_goweek'));
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
		if (!empty ($this->conf['view.']['month.']['monthViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$return .= sprintf($this->conf['view.']['other.']['optionString'],t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1],$this->controller->pi_getLL('l_gomonth'));
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $today, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		$link = preg_split('/\"/', $link);
		$return .= sprintf($this->conf['view.']['other.']['optionString'],t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1],$this->controller->pi_getLL('l_goyear'));
		
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
		// gmdate is ok.
		$month = gmdate('m');
		$year = gmdate('Y');
		if($conf['monthStart.']['thisMonth']){
			$month_time = tx_cal_calendar::calculateStartMonthTime();
		}else{
			$month_time = tx_cal_calendar::calculateStartDayTime();
			$month_time->setDay(1);
			$month_time->setMonth($conf['monthStart']);
			$month_time->setYear($conf['yearStart']);
			$month = $conf['monthStart'];
			$year = $conf['yearStart'];
		}

		for ($i = 0; $i < $conf['count']; $i ++) {
			$monthdate = $month_time->format('%Y%m%d');
			$month_month = $month_time->getMonth();
			$select_month = $month_time_>format($conf['format']);
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_month_view').'"';
			if($this->rightsObj->isViewEnabled('month') || $this->conf['view.']['month.']['monthViewPid']){
				$link = $this->controller->pi_linkTP_keepPIvars($select_month, array ('getdate' => $monthdate, 'view' => 'month',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
			}else{
				$link = $select_month;
			}

			$return .= $this->cObj->stdWrap($link, $conf['month_stdWrap.']);

			$month_time->addSeconds(86400 * 32);
			$month_time = tx_cal_calendar::calculateStartMonthTime($month_time);
		}
		return $return;
	}
	
	function getCategorySelectionTree($treeConf, $categoryArray, $renderAsForm = false){
		$treeHtml = '';
		foreach($categoryArray as $categoryServiceKey => $categoryServiceResult){	
			foreach($categoryServiceResult as $modelCategoryArray){
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
						$calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
						$calendar = $calendarService->find($calendarUid,$this->conf['pidList']);
						$calendarTitle = $calendar->getTitle();//.$calendar->getEditLink();
					}
					
					if(intval($treeConf['calendar'])==$treeConf['calendar']){
						$ids = explode(',',$treeConf['calendar']);
						if(!in_array($calendarUid,$ids)) {
							continue;
						}
					}else{
						continue;
					}
					$treeConf['calendarTitle.']['value'] = $calendarTitle;
					$treeHtml .= $this->cObj->cObjGetSingle($treeConf['calendarTitle'],$treeConf['calendarTitle.']);
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
			if($treeConf['category']!=''){
				$selectedCategories = explode(',',$treeConf['category']);
			}
			$treeHtml .= $this->cObj->stdWrap(((in_array($parentCategory->getUid(),$selectedCategories) || empty($selectedCategories))?' checked="checked"':''), $treeConf['selector.']);
		}
		$treeHtml .= $treeConf['element'];
		$sims = array();
		$rems = array();
		$wrapper = array();
		$parentCategory->getMarker($treeHtml,$sims,$rems,$wrapper);
		$sims['###LEVEL###'] = $level;
		$treeHtml = $this->cObj->substituteMarkerArrayCached($treeHtml, $sims, $rems, $wrapper);
		
		$categoryArray = $parentCategoryArray[$parentCategory->getUid()];
		if(is_array($categoryArray)){
			
			$tempHtml = $treeConf['subElement'];
			$sims = array();
			$rems = array();
			$wrapper = array();
			$parentCategory->getMarker($tempHtml,$sims,$rems,$wrapper);
			$sims['###LEVEL###'] = $level;
			$treeHtml .= $this->cObj->substituteMarkerArrayCached($tempHtml, $sims, $rems, $wrapper);
			
			foreach($categoryArray as $category){
				
				$treeHtml .= $this->cObj->stdWrap($this->addSubCategory($treeConf,$parentCategoryArray, $category, $level, $renderAsForm), $treeConf['subElement_wrap.']);
				
			}
			$treeHtml .= $treeConf['subElement_pre'];
		}	
		return $treeHtml;
	}
	
	function getCreateCalendarLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###CREATE_CALENDAR_LINK###'] = '';
		if($this->rightsObj->isAllowedToCreateCalendar()){
			$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_create_calendar') . '" alt="' . $this->controller->pi_getLL('l_create_calendar') . '"';
			$sims['###CREATE_CALENDAR_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['calendar.']['calendar.']['addIcon'], array (
				'view' => 'create_calendar',
			'type' => 'tx_cal_calendar', 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['createCalendarViewPid']);
		}
	}

	function list_months($this_year, $dateFormat_month) {
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_month = $day_array2[2];
		
		if($this->conf['view.']['other.']['listMonth_onlyShowCurrentYear']) {
			$month = 1;
			$monthSize = 12;
			$monthOffset = $monthSize - $this_month;
		} else {
			$monthSize = intval($this->conf['view.']['other.']['listMonth_totalMonthCount']);
			$monthSize = $monthSize ? $monthSize : 12; // ensure valid data
			
			$monthOffset = intval($this->conf['view.']['other.']['listMonth_previousMonthCount']);
			$monthOffset = ($monthOffset < $monthSize) ? $monthOffset : intval($monthSize/2);
			
			$month = $this_month - $monthOffset; // calc start month
			if($month < 1) { // the year needs to be switched
				$this_year = $this_year - intval(abs($month) / 12)-1; // calc the year
				$month = 12 + ($month % 12);
			}
		}
		
		$month_time = tx_cal_calendar::calculateStartDayTime();
		$month_time->setDay(1);
		$month_time->setMonth($month);
		$month_time->setYear($this_year);
		
		for ($i = 0; $i < $monthSize; $i ++) {
			$monthdate = $month_time->format('%Y%m%d');
			$month_month = $month_time->getMonth();
			$select_month = $month_time->format($dateFormat_month);
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
			$month_time->addSeconds(86400 * 32);
			$month_time = tx_cal_calendar::calculateStartMonthTime($month_time);
		}
		return $return;
	}

	function list_years($this_year, $dateFormat) {
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);
		
		$yearSize = intval($this->conf['view.']['other.']['listYear_totalYearCount']);
		$yearSize = $yearSize ? $yearSize : 3; // ensure valid data
		
		$yearOffset = intval($this->conf['view.']['other.']['listYear_previousYearCount']);
		$yearOffset = ($yearOffset < $yearSize) ? $yearOffset : intval($yearSize/2);
		
		$currentYear = $this_year - $yearOffset;
		
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_year_view').'"';
		$getdate_year = strftime($dateFormat, $unix_time);

		for ($i = 0; $i < $yearSize; $i ++) {
			$date = $currentYear.$this_month.$this_day;
			$year = gmstrftime($dateFormat, gmmktime(0,0,0,$this_month,$this_day,$currentYear));
			
			if (!empty ($this->conf['view.']['year.']['year_view_id'])) {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $date, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['year_view_id']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $date, 'view' => 'year',), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
			if($currentYear == $this_year) {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYearSelected_stdWrap.']);
			} else {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listYear_stdWrap.']);
			}
			$return .= str_replace('###YEAR###',$year,$tmp);
			
			$currentYear++; 
		}

		return $return;
	}

	function list_weeks($this_year, $dateFormat_week_jump, $weekStartDay) {
		
		if($this->conf['view.']['other.']['listWeek_onlyShowCurrentYear']) {
			$weekSize = 52;
			
			$start_week_time = new tx_cal_date($this->controller->getDateTimeObject->getYear().'0101','Ymd');
			$start_week_time->setTZbyId('UTC');
		} else {
			$weekSize = intval($this->conf['view.']['other.']['listWeek_totalWeekCount']);
			$weekSize = $weekSize ? $weekSize : 10; // ensure valid data
			
			$weekOffset = intval($this->conf['view.']['other.']['listWeek_previousWeekCount']);
			$weekOffset = ($weekOffset < $weekSize) ? $weekOffset : intval($weekSize/2);
			
			$start_week_time = new tx_cal_date();
			$start_week_time->copy($this->controller->getDateTimeObject);
			$start_week_time->subtractSeconds(604800 * $weekOffset);
		}
		
		$start_week_time = tx_cal_calendar::calculateStartWeekTime($start_week_time,$this->conf['view.']['weekStartDay']);
		$end_week_time = tx_cal_calendar::calculateEndWeekTime($start_week_time,$this->conf['view.']['weekStartDay']);
		$formattedGetdate = $this->controller->getDateTimeObject->format('%Y%m%d');

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';

		for ($i=0; $i < $weekSize; $i++) {
			$weekdate = $start_week_time->format('%Y%m%d');
			$select_week1 = $start_week_time->format($dateFormat_week_jump);
			$select_week2 = $end_week_time->format($dateFormat_week_jump);


			if (!empty ($this->conf['view.']['week.']['weekViewPid'])) {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $weekdate, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars('', array ('getdate' => $weekdate, 'view' => $this->conf['view.']['weekLinkTarget']), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = preg_split('/\"/', $link);
			$link[1] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link[1];
			$formattedStart = $start_week_time->format('%Y%m%d');
			$formattedEnd = $end_week_time->format('%Y%m%d');
			if (($formattedGetdate >= $formattedStart) && ($formattedGetdate <= $formattedEnd)) {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listWeeksSelected_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link[1], $this->conf['view.']['other.']['listWeeks_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			}
			$start_week_time->addSeconds(604800);
			$end_week_time->addSeconds(604800);
		}

		return $return;
	}

	function tomorrows_events($template) {

		$starttime = new tx_cal_date($this->conf['getdate'],'Ymd');
		$starttime->setTZbyId('UTC');

		$starttime->addSeconds(86400);
		$next_day = $starttime->format('%Y%m%d');

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
				foreach ($event_times as $uid => $event) {
					$wrapped['###EVENT_LINK###'] = explode('|',$event->getLinkToEvent('|',$this->conf['view'],$next_day, $this->conf['view.']['other.']['tomorrowsEvents_stdWrap.']));
					$return = $wrapped['###EVENT_LINK###'][0].$event->renderTomorrowsEvent().$wrapped['###EVENT_LINK###'][1];
					$eventStart = $event->getStart();
					if ($eventStart->getHour() == 0 && $eventStart->getMinute() == 0) {
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

		$monthTemplate = $this->cObj->getSubpart($page, '###MONTH_TEMPLATE###');
		if($monthTemplate!=''){
			$weekStartDay = $this->conf['view.']['weekStartDay'];
			$loop_wd = $this->cObj->getSubpart($monthTemplate, '###LOOPWEEKDAY###');
			$t_month = $this->cObj->getSubpart($monthTemplate, '###SWITCHMONTHDAY###');
			$startweek = $this->cObj->getSubpart($monthTemplate, '###LOOPMONTHWEEKS_DAYS###');
			$endweek = $this->cObj->getSubpart($monthTemplate, '###LOOPMONTHDAYS_WEEKS###');
			$weeknum = $this->cObj->getSubpart($monthTemplate, '###LOOPWEEK_NUMS###');
	
			if ($type != 'medium') {
				$fake_getdate_time = new tx_cal_date();
				$fake_getdate_time->copy($this->controller->getDateTimeObject);
				$fake_getdate_time->setDay(15);
				$fake_getdate_time->addSeconds(intval($offset)*2592000);
			} else {
				$fake_getdate_time = new tx_cal_date();
				$fake_getdate_time->copy($this->controller->getDateTimeObject);
				$fake_getdate_time->setDay(15);
				$fake_getdate_time->setMonth($offset);
			}
			
			$minical_month = $fake_getdate_time->getMonth();
			$minical_year = $fake_getdate_time->getYear();
	
			$month_title = $fake_getdate_time->format($this->conf['view.']['month.']['dateFormatMonth']);
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['monthLinkTarget'].'_view').'"';
			$month_title = $this->controller->pi_linkTP_keepPIvars($month_title, array ('getdate' => $fake_getdate_time->format('%Y%m%d'), 'view' => $this->conf['view.']['monthLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
			$month_date = $fake_getdate_time->format('%Y%m%d');
	
			$view_array = array ();

			if(!$this->viewarray){
				$this->eventArray = array();
				if (!empty($this->master_array)) {
					foreach ($this->master_array as $dateKey => $dateArray) {
						foreach ($dateArray as $timeKey => $arrayOfEvents) {
							foreach ($arrayOfEvents as $event) {
								$this->eventArray[$dateKey.'_'.$event->getType().'_'.$event->getUid()] = $event;
								$starttime = new tx_cal_date();
								$starttime->copy($event->getStart());
								$endtime = new tx_cal_date();
								$endtime->copy($event->getEnd());
								if($timeKey=='-1'){
									$endtime->addSeconds(1); // needed to let allday events show up
								}
								$j = new tx_cal_date();
								for ($j->copy($starttime); $j->before($endtime); $j->addSeconds(60 * 60 * 24)) {
									$view_array[$j->format('%Y%m%d')]['0000'][count($view_array[$j->format('%Y%m%d')]['0000'])] = $dateKey.'_'.$event->getType().'_'.$event->getUid();
								}
							}
						}
					}
				}
				$this->viewarray = $view_array;
			}
			
			$monthTemplate = str_replace('###MONTH_TITLE###',$month_title,$monthTemplate);
	
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
			$dateOfWeek = Date_Calc::beginOfWeek(15,$fake_getdate_time->getMonth(),$fake_getdate_time->getYear());
			$start_day = new tx_cal_date($dateOfWeek,'Ymd');
			if($weekStartDay=='Sunday'){
				$start_day = $start_day->getPrevDay();
			}
			
			$weekday_loop .= sprintf($weeknum, $this->conf['view.']['month.']['monthCornerStyle'], '');
			for ($i = 0; $i < 7; $i ++) {
				$weekday = $start_day->format($langtype);
				if($typeSize){
					$weekday = $this->cs_convert->substr(getCharset(),$weekday,0,$typeSize);
				}
				$start_day->addSeconds(86400);
				$loop_tmp = str_replace('###WEEKDAY###', $weekday, $loop_wd);
				$weekday_loop .= $loop_tmp;
			}
			$weekday_loop .= $endweek;
			
			$dateOfWeek = Date_Calc::beginOfWeek(1,$fake_getdate_time->getMonth(),$fake_getdate_time->getYear());
			$start_day = new tx_cal_date($dateOfWeek,'Ymd');
			$start_day->setTZbyID('UTC');
			if($weekStartDay=='Sunday'){
				$start_day = $start_day->getPrevDay();
			}
	
			$i = 0;
			$whole_month = TRUE;
			$isAllowedToCreateEvent = $this->rightsObj->isAllowedToCreateEvent();

			$calcOffset = new tx_cal_date();
			//TODO find a better solution for CET
			$calcOffset->setTZbyID('CET');
			$createOffset = (intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60) + $calcOffset->tz->getOffset($calcOffset)/1000;
			
			do {
				$daylink = new tx_cal_date();
				$daylink->copy($start_day);

				$formatedGetdate = $daylink->format('%Y%m%d');
				
				$startWeekTime = tx_cal_calendar::calculateStartWeekTime($this->controller->getDateTimeObject,$this->conf['view.']['weekStartDay']);
				$endWeekTime = tx_cal_calendar::calculateEndWeekTime($this->controller->getDateTimeObject,$this->conf['view.']['weekStartDay']);
				$isCurrentWeek = false;
				if ($formatedGetdate>=$startWeekTime->format('%Y%m%d') && $formatedGetdate<=$endWeekTime->format('%Y%m%d')) {
					$isCurrentWeek = true;
				}

				if ($i == 0){
					$middle .= $startweek;
					$start_day->addSeconds(86400);
					$num = $start_day->format('%U');
					$hasEvent = false;
					$start_day->subtractSeconds(86400);
					for($j = 0; $j < 7; $j++){
						if(is_array($this->viewarray[$start_day->format('%Y%m%d')]) || $isAllowedToCreateEvent){
							$hasEvent = true;
							break;
						}
						$start_day->addSeconds(86400);
					}
					$start_day->copy($daylink);
					if(($this->rightsObj->isViewEnabled($this->conf['view.']['weekLinkTarget']) || $this->conf['view.'][$this->conf['view.']['weekLinkTarget'].'.'][$this->conf['view.']['weekLinkTarget'].'ViewPid']) && $hasEvent){
						$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['weekLinkTarget'].'_view').'"';
						$num = $this->controller->pi_linkTP_keepPIvars($num, array ('getdate' => $formatedGetdate, 'view' => $this->conf['view.']['weekLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['week.']['weekViewPid']);
					}

					if ($isCurrentWeek) {
						$middle .= sprintf($weeknum, ' '.$this->conf['view.']['month.']['monthCurrentWeekStyle'], $num);
					}else{
						$middle .= sprintf($weeknum, '', $num);
					}
				}
				$i ++;
				$switch = array ('###ALLDAY###' => '');
				$check_month = $start_day->getMonth();
				
				$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
				
				$switch['###LINK###'] = $this->getCreateEventLink('month','',$start_day,$createOffset,$isAllowedToCreateEvent,'','',$this->conf['view.']['day.']['dayStart']);
				
				$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_'.$this->conf['view.']['dayLinkTarget'].'_view').'"';
				
				$style = '';
				
				if(($this->rightsObj->isViewEnabled($this->conf['view.']['dayLinkTarget']) || $this->conf['view.'][$this->conf['view.']['dayLinkTarget'].'.'][$this->conf['view.']['dayLinkTarget'].'ViewPid']) && ($this->viewarray[$formatedGetdate] || $isAllowedToCreateEvent)){
					$switch['###LINK###'] .= $this->controller->pi_linkTP_keepPIvars($start_day->getDay(), array ('getdate' => $formatedGetdate, 'view' => $this->conf['view.']['dayLinkTarget'], 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['day.']['dayViewPid']);
					$switch['###LINK###'] = $this->cObj->stdWrap($switch['###LINK###'], $this->conf['view.']['month.'][$type.'Link_stdWrap.']);
					$style .= $this->conf['view.']['month.']['eventDayStyle'].' ';
				}else{
					$switch['###LINK###'] .= $start_day->getDay();
				}
				
				if ($check_month != $minical_month) {
					$style .= $this->conf['view.']['month.']['monthOffStyle'].' ';
				}
				if ($formatedGetdate == $this->conf['getdate']) {
					$style .= $this->conf['view.']['month.']['monthSelectedStyle'].' ';
				}
				$tempDate = new tx_cal_date();
				if ($formatedGetdate == $tempDate->format('%Y%m%d')) {
					$style .= $this->conf['view.']['month.']['monthTodayStyle'].' ';
				}
				if ($start_day->format('%w')==0 || $start_day->format('%w')==6) {
					$style .= $this->conf['view.']['month.']['monthWeekendStyle'].' ';
				}

				if ($isCurrentWeek) {
					$style .= $this->conf['view.']['month.']['monthCurrentWeekStyle'].' ';
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
				
				if ($this->viewarray[$formatedGetdate] && $type!='small') {
					foreach ($this->viewarray[$formatedGetdate] as $cal_time => $event_times) {
						foreach ($event_times as $uid => $eventId) {
							if($type == 'large'){
								$switch['###EVENT###'] .= $this->eventArray[$eventId]->renderEventForMonth();
							}else if($type == 'medium'){
								$switch['###EVENT###'] .= $this->eventArray[$eventId]->renderEventForYear();
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
	
				$start_day->addSeconds(86400); // 60 * 60 *24 -> strtotime('+1 day', $start_day);
				if ($i == 7) {
					$i = 0;
					$middle .= $endweek;
					$checkagain = $start_day->getMonth();
					if ($checkagain != $minical_month){
						$whole_month = FALSE;
					}
				}
			} while ($whole_month == TRUE);
	
			$rems['###LOOPWEEKDAY###'] = $weekday_loop;
			$rems['###LOOPMONTHWEEKS###'] = $middle;
			$rems['###LOOPMONTHWEEKS_DAYS###'] = '';
			$rems['###LOOPWEEK_NUMS###'] = '';
			$monthTemplate = $this->cObj->substituteMarkerArrayCached($monthTemplate, array (), $rems, array ());
			$page = $this->cObj->substituteMarkerArrayCached($page, array(), array ('###MONTH_TEMPLATE###'=>$monthTemplate), array ());
		}
		
		$listTemplate = $this->cObj->getSubpart($page, '###LIST###');
		if($listTemplate!=''){
			$tx_cal_listview = &t3lib_div::makeInstanceService('cal_view', 'list', 'list');
			$starttime = gmmktime(0,0,0,$this_month,1,$this_year);
			$endtime = gmmktime(0,0,0,$this_month+1,1,$this_year);
			$rems['###LIST###'] = $tx_cal_listview->drawList($this->master_array,$listTemplate,$starttime,$endtime);
		}

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
	
	function getMeetingInformationMarker(&$page, &$sims, &$rems, $view){
		$sims['###MEETING_INFORMATION###'] = '';
		$foundEvents = Array();
		$eventService = &getEventService();
		$eventDateArray = $eventService->findMeetingEventsWithEmptyStatus($this->conf['pidList']);
		if(!empty($eventDateArray)){
			$foundEvents[] = 'These meetings require your action:';
		}
		if(is_array($eventDateArray)){
			foreach($eventDateArray as $eventTimeArray){
				foreach($eventTimeArray as $eventArray){
					foreach($eventArray as $event){
						$foundEvents[] = $event->getLinkToEvent($event->getTitle(), $this->conf['view'], $this->conf['getdate']);
					}
				}
			}
		}
		$sims['###MEETING_INFORMATION###'] = implode('<br/>',$foundEvents);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']);
}
?>
