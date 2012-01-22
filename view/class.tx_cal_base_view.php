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
	var $local_cObj; // reference to a locally created cObject whos data is allowed to be altered and is used to render TS objects

	function tx_cal_base_view(){
		$this->tx_cal_base_service();
		$this->pointerName = $this->controller->getPointerName();
	}

	function _init(&$master_array){
		#store cs_convert-object
		$this->cs_convert=t3lib_div::makeInstance('t3lib_cs');
		$this->master_array = &$master_array;
		$this->initLocalCObject();
		$this->pointerName = $this->controller->getPointerName();
	}

	function getAdminLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###ADMIN_LINK###'] = '';
		if ($this->rightsObj->isAllowedToConfigure()) {
			$this->initLocalCObject();
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, Array('view' => 'admin','lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway']);
			$sims['###ADMIN_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['admin.']['adminViewLink'],$this->conf['view.']['admin.']['adminViewLink.']);
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
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if ($this->rightsObj->isViewEnabled('todo') && $confArr['todoSubtype']=='todo') {
			$dateObject = new tx_cal_date($this->conf['getdate']);
			$pidList = $this->conf['pidList'];
			$todos = array();
			switch($this->conf['view']){
				case 'day':
					$todos = $this->modelObj->findTodosForDay($dateObject, '', $pidList);
					break;
				case 'week':
					$todos = $this->modelObj->findTodosForWeek($dateObject, '', $pidList);
					break;
				case 'month':
					$todos = $this->modelObj->findTodosForMonth($dateObject, '', $pidList);
					break;
				case 'year':
					$todos = $this->modelObj->findTodosForYear($dateObject, '', $pidList);
					break;
				case 'list':
					$endtime = $this->cObj->stdWrap($this->conf['view.']['list.']['endtime'], $this->conf['view.']['list.']['endtime.']);
					$todos = $this->modelObj->findTodosForList($dateObject, $this->controller->getListViewTime($endtime, $dateObject), '', $pidList);
					break;
			}
			$todoContent = '<tr><td></td></tr>';
			if(!empty($todos)) {
				foreach($todos as $todoDate => $todoTimeArray){
					if(is_object($todoTimeArray)){
						$todoContent .= $todoTimeArray->renderEventFor($this->conf['view']);
					}else{
						foreach ($todoTimeArray as $key => $todoArray) {
							foreach($todoArray as $todoUid => $todo){
								if (is_object($todo)) {
									$todoContent .= $todo->renderEventFor($this->conf['view']);
								}
							}
						}
					}
				}
			}
			
			$todoTemplate = $this->cObj->getSubpart($page, '###TODO###');
			$rems['###TODO###'] = tx_cal_functions::substituteMarkerArrayNotCached($todoTemplate, array(), array('###TODO_ENTRIES###'=>$todoContent));
		}
	}
	
	function getUserLoginMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###USER_LOGIN###'] = '';
		if ($this->conf['view.']['other.']['showLogin'] == 1) {
			$local_sims = array();
			$local_rems = array();
			$parameter = array ('view' => $this->conf['view'], $this->pointerName => NULL);
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
			$rems['###USER_LOGIN###'] = tx_cal_functions::substituteMarkerArrayNotCached($this->cObj->getSubpart($page,'###USER_LOGIN###'), $local_sims, $local_rems, array());
		}
	}
	
	function getIcsLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###ICS_LINK###'] = '';
		if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_calendar_icslink'));
			$this->local_cObj->data['link_wrap'] = str_replace('%s','|',$this->conf['view.']['ics.']['link_wrap']); // for backwards compatibility only, could be dropped actualy
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('view' => 'icslist',  $this->pointerName => NULL,'lastview' => $this->controller->extendLastView()), $this->conf['cache'], 1);
			$sims['###ICS_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['ics.']['icsViewLink'],$this->conf['view.']['ics.']['icsViewLink.']);
		}
	}
	
	function getSearchMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###SEARCH###'] = '';
		if ($this->conf['view.']['other.']['showSearch'] == 1) {
			$local_sims = array();
			$page = $this->replace_files($page, array ('search_box' => $this->conf['view.']['other.']['searchBoxTemplate']));
/* obsolete as of version 1.1.0
			$local_sims['###L_SEARCH###'] = $this->controller->pi_getLL('l_search');
			$local_sims['###L_DOSEARCH###'] = $this->controller->pi_getLL('l_dosearch');
*/
			$local_sims['###GETDATE###'] = $this->conf['getdate'];

			$rems['###SEARCH###'] = tx_cal_functions::substituteMarkerArrayNotCached($this->cObj->getSubpart($page,'###SEARCH###'), $local_sims, array(), array());
		}
	}
	
	function getSearchActionURLMarker(&$page, &$sims, &$rems, &$wrapped) {
		$parameter = array ('view' => 'search_all',  'getdate' => $this->conf['getdate']);
		$sims['###SEARCH_ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url($parameter,$this->conf['cache'],true));
	}
	
	function getJumpsMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###JUMPS###'] = '';
		if ($this->conf['view.']['other.']['showJumps'] == 1) {
			preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->conf['getdate'], $day_array2);
			$this_day = $day_array2[3];
			$this_month = $day_array2[2];
			$this_year = $day_array2[1];
			$temp_sims = array();
			$temp_sims['###LIST_JUMPS###'] = $this->list_jumps();
			$temp_sims['###LIST_ICALS###'] = ''; //display_ical_list(availableCalendars($username, $password, $ALL_CALENDARS_COMBINED));
			$temp_sims['###LIST_YEARS###'] = $this->list_years($this_year, $this->conf['view.']['other.']['dateFormatYearJump']);
			$temp_sims['###LIST_MONTHS###'] = $this->list_months($this_year, $this->conf['view.']['other.']['dateFormatMonthJump']);
			$temp_sims['###LIST_WEEKS###'] = $this->list_weeks($this_year, $this->conf['view.']['other.']['dateFormatWeekJump']);
			
			$rems['###JUMPS###'] = tx_cal_functions::substituteMarkerArrayNotCached($this->cObj->getSubpart($page, '###JUMPS###'), $temp_sims, array(), array());
		}
	}
	
	function getCalendarSelectorMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###CALENDAR_SELECTOR###'] = '';
		if ($this->conf['view.']['other.']['showCalendarSelection']) {
			$temp_sims = array();
			$calendarService = $this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
			$calendarArray = $calendarService->getCalendarFromTable($this->conf['pidList'], $calendarService->getCalendarSearchString($this->conf['pidList'], true,false));
			if(is_array($calendarArray)){
				$calendarOptions .= '<option value="">'.$this->controller->pi_getLL('l_all_cal_comb_lang').'</option>';
				foreach($calendarArray as $calendar){
					if($this->conf['calendar']==$calendar->row['uid']){
						$calendarOptions .= '<option value="'.$calendar->row['uid'].'" selected="selected">'.$calendar->getTitle().'</option>';
					}else{
						$calendarOptions .= '<option value="'.$calendar->row['uid'].'">'.$calendar->getTitle().'</option>';
					}
				}
			}
	
			$temp_sims['###L_CALENDAR###'] = $this->controller->pi_getLL('l_calendar');
			$temp_sims['###CALENDAR_IDS###'] = $calendarOptions;
			$temp_sims['###CHANGE_CALENDAR_ACTION_URL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url( array('view'=>$this->conf['view']),$this->conf['cache'],true));
			$rems['###CALENDAR_SELECTOR###'] = tx_cal_functions::substituteMarkerArrayNotCached($this->cObj->getSubpart($page, '###CALENDAR_SELECTOR###'), $temp_sims, array(), array());
		}
	}
	
	function getBackLinkMarker(&$page, &$sims, &$rems, &$wrapped){
		$sims['###BACK_LINK###'] = '';
		$viewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		# by Franz, 25.02.2009
		# checking for a allowed view with '$this->rightsObj->isViewEnabled($viewParams['view'])' for a chash-validated backlink piVar seems a bit odd.
		# So I removed this check in order to ease website admins life to not have to care about allowedViews only to get backlinks working :)
		# Hope this doesn't break anything or opens up XSS leaks. Feel free to put it back in if in doubt.
		if($this->conf['view'] != $viewParams['view']){
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_back'));
			$this->local_cObj->data['view'] = $viewParams['view'];
			$pid = intval($viewParams['page_id']);
			$viewParams['dontExtendLastView'] = true;
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, $viewParams, $this->conf['cache'], $this->conf['clear_anyway'],$pid);
			$sims['###BACK_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['backLink'],$this->conf['view.']['backLink.']);
		}
	}
	
	function getLegendMarker(&$page, &$sims, &$rems, $view){
		$this->list_legend($sims['###LEGEND###']);
	}
	
	function getListMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###LIST###'] = '';
		$starttime = new tx_cal_date($this->conf['getdate'].'000000');
		$starttime->setTZbyId('UTC');
		$tx_cal_listview = t3lib_div::makeInstanceService('cal_view', 'list', 'list');
		// set alternate rendering view, so that the rendering of the attached listView can be customized
		$tempAlternateRenderingView = $tx_cal_listview->conf['alternateRenderingView'];
		$renderingView = $this->conf['view.'][$this->conf['view'].'.']['useListEventRenderSettingsView'];
		$tx_cal_listview->conf['alternateRenderingView'] = $renderingView ? $renderingView : 'list';
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

		$tx_cal_listview->conf['alternateRenderingView'] = $tempAlternateRenderingView;
	}
	
	function getRelatedListMarker(&$page, &$sims, &$rems, &$wrapped){
		$rems['###RELATED_LIST###'] = '';
		$tx_cal_listview = t3lib_div::makeInstanceService('cal_view', 'list', 'list');
		$listSubpart =  $this->cObj->getSubpart($page, '###RELATED_LIST###');
		if($this->conf['view.'][$this->conf['view'].'.'][$this->conf['view'].'.']['includeEventsInResult']){
			$starttime = $this->controller->getListViewTime($this->conf['view.'][$this->conf['view'].'.'][$this->conf['view'].'.']['includeEventsInResult.']['starttime']);
			$endtime = $this->controller->getListViewTime($this->conf['view.'][$this->conf['view'].'.'][$this->conf['view'].'.']['includeEventsInResult.']['endtime']);
			if($this->master_array && !empty($this->master_array)){
				$rems['###RELATED_LIST###'] = $tx_cal_listview->drawList($this->master_array,$listSubpart,$starttime,$endtime);
			}
		}
	}
	
	function getCreateEventLinkMarker(&$page, &$sims, &$rems, $view){
		$sims['###CREATE_EVENT_LINK###'] = '';
		if ($this->rightsObj->isAllowedToCreateEvent()) {
			$createOffset = intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60;

			$now = new tx_cal_date();
			$now->setTZbyId('UTC');

			if($this->conf['getdate'] != $now->format('%Y%m%d')) {
				$cal_time_obj = new tx_cal_date($this->conf['getdate'].'000000');
				$cal_time_obj->setTZbyId('UTC');
			} else {
				$cal_time_obj = new tx_cal_date();
				$cal_time_obj->setTZbyId('UTC');
				$cal_time_obj->addSeconds($createOffset+10);
			}

			$sims['###CREATE_EVENT_LINK###'] = $this->getCreateEventLink($view, '', $cal_time_obj, $createOffset, true, '', '', $this->conf['view.']['day.']['dayStart']);
		}
	}

	function getCreateEventLink($view, $wrap, $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $remember, $class, $time){
		$tmp = '';
		if(!$this->rightsObj->isViewEnabled('create_event')){
			if($this->conf['view.']['enableAjax']) { 
			 	return sprintf($wrap, $remember, $class, '');
			} else { 
				return sprintf($wrap, $remember, $class, ''); 
			}
		}
		$now = new tx_cal_date();
		$now->setTZbyId('UTC');
		$now->addSeconds($createOffset);
		if($this->rightsObj->isAllowedToCreateEventForTodayAndFuture()){
			$now->setHour(23);
			$now->setMinute(59);
		}
//debug($cal_time_obj->format('%Y%m%d %H%M').' after'.$now->format('%Y%m%d %H%M'),'hier:'.$createOffset);
		if (($cal_time_obj->after($now) || $this->rightsObj->isAllowedToCreateEventInPast()) && $isAllowedToCreateEvent) {
			$this->initLocalCObject();
			if($this->conf['view.']['enableAjax']){
				$this->local_cObj->setCurrentVal($this->conf['view.'][$view.'.']['event.']['addIcon']);
				$this->local_cObj->data['link_ATagParams'] = sprintf(' onclick="'.$this->conf['view.'][$view.'.']['event.']['addLinkOnClick'].'"',$time,$cal_time_obj->format('%Y%m%d'));
				$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('gettime' => $time, 'getdate'=>$cal_time_obj->format('%Y%m%d'),  'view' => 'create_event'), 0, $this->conf['clear_anyway'],$this->conf['view.']['event.']['createEventViewPid']);
				$tmp .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['addLink'],$this->conf['view.'][$view.'.']['event.']['addLink.']);
				if($wrap){
					$tmp = sprintf($wrap,'id="cell_'.$cal_time_obj->format('%Y%m%d').$time.'" ondblclick="javascript:eventUid=0;eventTime=\''.$time.'\';eventDate='.$cal_time_obj->format('%Y%m%d').';EventDialog.showDialog(this);" ',$remember,$class,$tmp,$cal_time_obj->format('%Y %m %d %H %M %s'));
				}
			}else{
				$this->local_cObj->setCurrentVal($this->conf['view.'][$view.'.']['event.']['addIcon']);
				#$linkConf = Array();
				$this->local_cObj->data['link_useCacheHash'] = 0;
				$this->local_cObj->data['link_no_cache'] = 1;
				$this->local_cObj->data['link_additionalParams'] = '&tx_cal_controller[gettime]='.$time.'&tx_cal_controller[getdate]='.$cal_time_obj->format('%Y%m%d').'&tx_cal_controller[lastview]='.$this->controller->extendLastView().'&tx_cal_controller[view]=create_event';
				$this->local_cObj->data['link_section'] = 'default';
				$this->local_cObj->data['link_parameter'] = $this->conf['view.']['event.']['createEventViewPid']?$this->conf['view.']['event.']['createEventViewPid']:$GLOBALS['TSFE']->id;

				$tmp .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['addLink'],$this->conf['view.'][$view.'.']['event.']['addLink.']);
				if($wrap){
					$tmp = sprintf($wrap,$remember,$class,$tmp,$cal_time_obj->format('%Y %m %d %H %M %s'));
				}
			}
		}else{
			if($this->conf['view.']['enableAjax']){
				$tmp = sprintf($wrap,$remember,$class,'');
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
	
	function getAjax2UrlMarker(&$page, &$sims, &$rems, $view){
		$sims['###AJAX2_URL###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
	}

	function getAvailableCalendarMarker(&$page, &$sims, &$rems, $view){
		$ajaxString = '';
		$deselectedCalendarIds = t3lib_div::trimExplode(',',$this->conf['view.']['calendar.']['subscription'],1);
		$calendarIds = Array();
		foreach($deselectedCalendarIds as $calendarUid){
			$calendarIds[] = $calendarUid;
			$calendar = $this->modelObj->findCalendar($calendarUid, 'tx_cal_calendar', $this->conf['pidList']);
			$ajaxString .= 'var tmpCal'.$calendar->getUid().' = new Array();';
			$calendarValues = $calendar->getValuesAsArray();
			foreach($calendarValues as $key => $value){
				if($key!='l18n_diffsource'){
					$ajaxString .= 'tmpCal'.$calendar->getUid().'[\''.$key.'\']='.'\''.$value.'\';';
				}
			}
			$ajaxString .= 'tmpCal'.$calendar->getUid().'[\'enabled\']='.'false;';
			$ajaxString .= 'availableCalendar['.$calendar->getUid().'] = new calCalendar(tmpCal'.$calendar->getUid().');'."\n";
		}
		$calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar');
		
		foreach($calendarArray['tx_cal_calendar'] as $calendar){
			$ajaxString .= 'var tmpCal'.$calendar->getUid().' = new Array();';
			$calendarValues = $calendar->getValuesAsArray();
			if(!in_array($calendar->getUid(),$calendarIds)){
				foreach($calendarValues as $key => $value){
					if($key!='l18n_diffsource'){
						$ajaxString .= 'tmpCal'.$calendar->getUid().'[\''.$key.'\']='.'\''.$value.'\';';
					}
				}
				$ajaxString .= 'tmpCal'.$calendar->getUid().'[\'enabled\']='.'true;';
				$ajaxString .= 'availableCalendar['.$calendar->getUid().'] = new calCalendar(tmpCal'.$calendar->getUid().');'."\n";
			}
		}
		$sims['###AVAILABLE_CALENDAR###'] = $ajaxString;
	}

	function getPidMarker(&$page, &$sims, &$rems, $view){
		$sims['###PID###'] = $GLOBALS['TSFE']->id;
	}

	function getImgPathMarker(&$page, &$sims, &$rems, $view){
		$sims['###IMG_PATH###'] = tx_cal_functions::expandPath($this->conf['view.']['imagePath']);
	}

	function getJsPathMarker(&$page, &$sims, &$rems, $view){
		$sims['###JS_PATH###'] = tx_cal_functions::expandPath($this->conf['view.']['javascriptPath']);
	}

	function getCategoryurlMarker(&$page, &$sims, &$rems, $view){
		$categoryOverrule = array();
		foreach((array) $this->controller->piVars['category'] as $id => $categoryId){
			$categoryOverrule[$id] = '';
		}
		$sims['###CATEGORYURL###'] = htmlspecialchars($this->controller->pi_linkTP_keepPIvars_url(array('view'=>$this->conf['view'],'categorySelection'=>'1', $this->pointerName => NULL,'category'=> $categoryOverrule),$this->conf['cache'],$this->conf['clear_anyway']));
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
		/*if($this->getObjectType()=='event'){
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

		// todo: find a way to already exclude label markers in the regexp.
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);
		/*if($this->getObjectType()=='event'){
		 debug($allSingleMarkers);
		 }*/

		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'IMG_PATH':
						//do nothing. we replace it at the end
					break;
				default :
					/* not needed here anymore. Label markers do now get processed in the main controller
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;
					}
					if(preg_match('/L_.* /',$marker)){
						#$sims['###'.$marker.'###'] = $this->controller->pi_getLL(strtolower($marker));
						continue;
					}
					*/
					// if marker is a label - skip it
					if(preg_match('/.*_LABEL$/',$marker) || preg_match('/^L_.*/',$marker)) {
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
						$this->initLocalCObject();
						$current = '';
						if($this->row[strtolower($marker)]!=''){
							$current = $this->row[strtolower($marker)];
						}
						$this->local_cObj->setCurrentVal($current);
						$sims['###' . $marker . '###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][strtolower($marker)],$this->conf['view.'][$view.'.'][strtolower($marker).'.']);
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
		
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_base_view','searchForViewMarker','view');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchForViewMarker')) {
				$hookObj->postSearchForViewMarker($this, $template, $sims, $rems, $wrapped, $view);
			}
		}
	}

	function finish(&$page, &$rems){
		$sims = array();
		$wrapped = array();
		
		$this->getSidebarMarker($page, $sims, $rems, $this->conf['view']);
		$this->getCalendarNavMarker($page, $sims, $rems, $this->conf['view']);
		$page = $this->replaceViewMarker($page);
		$page = $this->checkForMonthMarker($page);
		
		$this->getMarker($page, $sims, $rems, $wrapped);
		$sims['###VIEW###'] = $this->conf['view'];
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);

		$sims = array();
		$rems = array();
		$this->getImgPathMarker($page, $sims, $rems, $this->conf['view']);

		return tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);
	}
	
	function replaceViewMarker($page){
		$next_day = new tx_cal_date();
		$next_day->copy($this->controller->getDateTimeObject);
		$next_day->addSeconds(86400);
		
		$prev_day = new tx_cal_date();
		$prev_day->copy($this->controller->getDateTimeObject);
		$prev_day->subtractSeconds(86400);
		
		$next_week = new tx_cal_date(Date_Calc::beginOfNextWeek($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$prev_week = new tx_cal_date(Date_Calc::beginOfPrevWeek($this->conf['day'], $this->conf['month'], $this->conf['year']));

		$next_year = ($this->conf['year']+1).sprintf("%02d", $this->conf['month']).sprintf("%02d", $this->conf['day']);
		$prev_year = ($this->conf['year']-1).sprintf("%02d", $this->conf['month']).sprintf("%02d", $this->conf['day']);
		
		$endOfNextMonth = new tx_cal_date(Date_Calc::endOfNextMonth($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$endOfNextMonth->setDay($this->conf['day']);
		
		$startOfPrevMonth = new tx_cal_date(Date_Calc::endOfPrevMonth($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$startOfPrevMonth->setDay($this->conf['day']);
		
		$next_month = $endOfNextMonth->format('%Y%m%d');
		$prev_month = $startOfPrevMonth->format('%Y%m%d');	

		$startOfThisWeek = new tx_cal_date(Date_Calc::beginOfWeek($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$endOfThisWeek = new tx_cal_date(Date_Calc::endOfWeek($this->conf['day'], $this->conf['month'], $this->conf['year']));
		$GLOBALS['TSFE']->register['cal_week_starttime'] = $startOfThisWeek->getTime();
		$GLOBALS['TSFE']->register['cal_week_endtime'] = $endOfThisWeek->getTime();

		$this->initLocalCObject();
		
		$dayViewPid = $this->conf['view.']['day.']['dayViewPid'] ? $this->conf['view.']['day.']['dayViewPid'] : false;
		$weekViewPid = $this->conf['view.']['week.']['weekViewPid'] ? $this->conf['view.']['week.']['weekViewPid'] : false;
		$monthViewPid = $this->conf['view.']['month.']['monthViewPid'] ? $this->conf['view.']['month.']['monthViewPid'] : false;
		$yearViewPid = $this->conf['view.']['year.']['yearViewPid'] ? $this->conf['view.']['year.']['yearViewPid'] : false;
		
		// next day
		$nextdaylinktext = $this->cObj->getSubpart($page, '###NEXT_DAYLINKTEXT###');
		$this->local_cObj->setCurrentVal($nextdaylinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $next_day->format('%Y%m%d'), 'view' => $this->conf['view.']['dayLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $dayViewPid);
		$rems['###NEXT_DAYLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['day.']['nextDayLink'],$this->conf['view.']['day.']['nextDayLink.']);
		
		// prev day
		$prevdaylinktext = $this->cObj->getSubpart($page, '###PREV_DAYLINKTEXT###');
		$this->local_cObj->setCurrentVal($prevdaylinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $prev_day->format('%Y%m%d'), 'view' => $this->conf['view.']['dayLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $dayViewPid);
		$rems['###PREV_DAYLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['day.']['prevDayLink'],$this->conf['view.']['day.']['prevDayLink.']);
		
		// next week
		$nextweeklinktext = $this->cObj->getSubpart($page, '###NEXT_WEEKLINKTEXT###');
		$this->local_cObj->setCurrentVal($nextweeklinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $next_week->format('%Y%m%d'), 'view' => $this->conf['view.']['weekLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $weekViewPid);
		$rems['###NEXT_WEEKLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['week.']['nextWeekLink'],$this->conf['view.']['week.']['nextWeekLink.']);
		
		// prev week
		$prevweeklinktext = $this->cObj->getSubpart($page, '###PREV_WEEKLINKTEXT###');
		$this->local_cObj->setCurrentVal($prevweeklinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $prev_week->format('%Y%m%d'), 'view' => $this->conf['view.']['weekLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $weekViewPid);
		$rems['###PREV_WEEKLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['week.']['prevWeekLink'],$this->conf['view.']['week.']['prevWeekLink.']);
		
		// next month
		$nextmonthlinktext = $this->cObj->getSubpart($page, '###NEXT_MONTHLINKTEXT###');
		$this->local_cObj->setCurrentVal($nextmonthlinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $next_month, 'view' => $this->conf['view.']['monthLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $monthViewPid);
		$rems['###NEXT_MONTHLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['month.']['nextMonthLink'],$this->conf['view.']['month.']['nextMonthLink.']);

		// prev month
		$prevmonthlinktext = $this->cObj->getSubpart($page, '###PREV_MONTHLINKTEXT###');
		$this->local_cObj->setCurrentVal($prevmonthlinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $prev_month, 'view' => $this->conf['view.']['monthLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $monthViewPid);
		$rems['###PREV_MONTHLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['month.']['prevMonthLink'],$this->conf['view.']['month.']['prevMonthLink.']);		


		// next year
		$nextyearlinktext = $this->cObj->getSubpart($page, '###NEXT_YEARLINKTEXT###');
		$this->local_cObj->setCurrentVal($nextyearlinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $next_year, 'view' => $this->conf['view.']['yearLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $yearViewPid);
		$rems['###NEXT_YEARLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['year.']['nextYearLink'],$this->conf['view.']['year.']['nextYearLink.']);

		// prev year
		$prevyearlinktext = $this->cObj->getSubpart($page, '###PREV_YEARLINKTEXT###');
		$this->local_cObj->setCurrentVal($prevyearlinktext);
		$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $prev_year, 'view' => $this->conf['view.']['yearLinkTarget'], $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $yearViewPid);
		$rems['###PREV_YEARLINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['year.']['prevYearLink'],$this->conf['view.']['year.']['prevYearLink.']);
		
		$this->local_cObj->setCurrentVal($this->controller->getDateTimeObject->getTime());
		
		$sims['###DISPLAY_DATE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['displayDate'],$this->conf['view.'][$this->conf['view'].'.']['displayDate.']);

		$wrapped = array();
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray('tx_cal_base_model','searchForObjectMarker','model');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchForObjectMarker')) {
				$hookObj->postSearchForObjectMarker($this, $page, $sims, $rems, $wrapped, $this->conf['view']);
			}
		}

		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, array ());

		$languageArray = array (
			'getdate' => $this->conf['getdate'],
			'next_month' => $next_month,
			'prev_month' => $prev_month,
			'calendar_name' => $this->conf['calendarName'],
			'this_year' => $this->conf['year'],
			'next_year' => $next_year,
			'prev_year' => $prev_year
		);
		
		return $this->controller->replace_tags($languageArray, $page);
	}


	function checkForMonthMarker($page) {

		$match = array();
		preg_match_all('!\###MONTH_([A-Z]*)\|?([+|-])?([0-9]{1,2})\###!is', $page, $match);
		if (sizeof($match) > 0) {
			$i = 0;
			foreach ($match[1] as $key => $val) {
				$offset = $match[2][$i].$match[3][$i];
				if ($match[1][$i] == 'SMALL') {
					$template_file = $this->cObj->fileResource($this->conf['view.']['month.']['monthSmallTemplate']);
					$type = 'small';
				}
				elseif ($match[1][$i] == 'MEDIUM') {
					$template_file = $this->cObj->fileResource($this->conf['view.']['month.']['monthMediumTemplate']);
					$type = 'medium';
				} else {
					$template_file = $this->cObj->fileResource($this->conf['view.']['month.']['monthLargeTemplate']);
					$type = 'large';
				}
				
				if($this->conf['useNewTemplatesAndRendering']){
					$data = $this->_draw_month_new($offset, $type);
				} else {
					$data = $this->_draw_month($template_file, $offset, $type);
				}

				$page = str_replace($match[0][$i], $data, $page);
				$i ++;
			}
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

	function getViewLinkMarker($view, &$template, &$sims, &$rems, &$wrapped) {
		$viewMarker = '###'.strtoupper($view).'VIEWLINK###';
		$viewTarget = $this->conf['view.'][strtolower($view).'LinkTarget'];
		$rems[$viewMarker] = '';
		if($this->rightsObj->isViewEnabled($viewTarget) || $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']){
			$this->initLocalCObject();
			if($viewTarget == $this->conf['view']){
				$this->local_cObj->data['link_ATagParams'] = 'class="current"';
			}
			$this->local_cObj->setCurrentVal($this->cObj->getSubpart($template, '###'.strtoupper($view).'VIEWLINKTEXT###'));
			$this->local_cObj->data['view'] = $viewTarget;
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate'=>$this->conf['getdate'],'view' => $viewTarget,  $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
			$rems[$viewMarker] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewLink'],$this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewLink.']);
		}
	}

	function getDayviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$this->getViewLinkMarker('day', $template, $sims, $rems, $wrapped);
	}
	
	function getWeekviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$this->getViewLinkMarker('week', $template, $sims, $rems, $wrapped);
	}
	
	function getMonthviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$this->getViewLinkMarker('month', $template, $sims, $rems, $wrapped);
	}
	
	function getYearviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$this->getViewLinkMarker('year', $template, $sims, $rems, $wrapped);
	}
	
	function getListviewlinkMarker(&$template, &$sims, &$rems, &$wrapped) {
		$this->getViewLinkMarker('list', $template, $sims, $rems, $wrapped);
	}

	function list_jumps() {
		$day_array2 = array();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		
		// gmdate is ok.
		$return = sprintf($this->conf['view.']['other.']['optionString'],gmdate('Ymd'),$this->controller->pi_getLL('l_jump'));
		$return .= $this->createJumpEntry('day');
		$return .= $this->createJumpEntry('week');
		$return .= $this->createJumpEntry('month');
		$return .= $this->createJumpEntry('year');
		return $return;
	}
	
	function createJumpEntry($view) {
		$viewTarget = $this->conf['view.'][strtolower($view).'LinkTarget'];
		if (!empty ($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid'])) {
			$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $today, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
		} else {
			$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $today, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway']);
		}
		return sprintf($this->conf['view.']['other.']['optionString'],t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link,$this->controller->pi_getLL('l_go'.$viewTarget));
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
			$this->initLocalCObject();
			$month_time->setMonth($this->local_cObj->cObjGetSingle($conf['monthStart'],$conf['monthStart.']));
			$this->initLocalCObject();
			$month_time->setYear($this->local_cObj->cObjGetSingle($conf['yearStart'],$conf['yearStart.']));
			$month = $conf['monthStart'];
			$year = $conf['yearStart'];
		}

		for ($i = 0; $i < $conf['count']; $i ++) {
			$monthdate = $month_time->format('%Y%m%d');
			$month_month = $month_time->getMonth();
			$select_month = $month_time->format($conf['format']);

			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($select_month);
			if($this->rightsObj->isViewEnabled('month') || $this->conf['view.']['month.']['monthViewPid']){
				$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $monthdate, 'view' => 'month', $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['month.']['monthViewPid']);
			}
			$link = $this->local_cObj->cObjGetSingle($this->conf['view.']['month.']['monthViewLink'],$this->conf['view.']['month.']['monthViewLink.']);

			$return .= $this->cObj->stdWrap($link, $conf['month_stdWrap.']);

			$month_time->addSeconds(86400 * 32);
			$month_time = tx_cal_calendar::calculateStartMonthTime($month_time);
		}
		return $return;
	}
	
	function getYearMenuMarker(&$page, &$sims, &$rems, $view) {
		// gmdate is ok.
		$conf = $this->conf['view.']['other.']['yearMenu.'];
		$year = gmdate('Y');
		if($conf['yearStart.']['thisYear']){
			$year_time = tx_cal_calendar::calculateStartYearTime();
		}else{
			$year_time = tx_cal_calendar::calculateStartYearTime();
			$this->initLocalCObject();
			$year_time->setYear($this->local_cObj->cObjGetSingle($conf['yearStart'],$conf['yearStart.']));
			$year = $conf['yearStart'];
		}

		for ($i = 0; $i < $conf['count']; $i ++) {
			$yeardate = $year_time->format('%Y%m%d');
			$select_year = $year_time->format($conf['format']);

			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($select_year);
			if($this->rightsObj->isViewEnabled('year') || $this->conf['view.']['year.']['yearViewPid']){
				$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $yeardate, 'view' => 'year', $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['year.']['yearViewPid']);
			}
			$link = $this->local_cObj->cObjGetSingle($this->conf['view.']['year.']['yearViewLink'],$this->conf['view.']['year.']['yearViewLink.']);

			$return .= $this->cObj->stdWrap($link, $conf['year_stdWrap.']);

			$year_time->setYear($year_time->getYear()+1);
		}
		$sims['###YEAR_MENU###'] = $return;
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
			if($treeConf['selector.']){
				$treeHtml .= $this->cObj->stdWrap(((in_array($parentCategory->getUid(),$selectedCategories) || empty($selectedCategories))?' checked="checked"':''), $treeConf['selector.']);
			} else {
				$catValues = $parentCategory->getValuesAsArray();
				$allowedCategoryArray = t3lib_div::trimExplode(',',$this->conf['view.']['category'],1);
				$notSelectedCategories = array_diff($allowedCategoryArray,$selectedCategories);
				if(in_array($parentCategory->getUid(),$selectedCategories) && !empty($notSelectedCategories)){
					$catValues['cur'] = 1;
				}
				$this->initLocalCObject($catValues);
				$this->local_cObj->setCurrentVal($parentCategory->getTitle());
				$treeHtml .= $this->local_cObj->cObjGetSingle($treeConf['alternativeSelect'], $treeConf['alternativeSelect.']);
			}
		}
		$treeHtml .= $treeConf['element'];
		$sims = array();
		$rems = array();
		$wrapper = array();
		$parentCategory->getMarker($treeHtml,$sims,$rems,$wrapper);
		$sims['###LEVEL###'] = $level;
		$treeHtml = tx_cal_functions::substituteMarkerArrayNotCached($treeHtml, $sims, $rems, $wrapper);
		
		$categoryArray = $parentCategoryArray[$parentCategory->getUid()];
		if(is_array($categoryArray)){
			
			$tempHtml = $treeConf['subElement'];
			$sims = array();
			$rems = array();
			$wrapper = array();
			$parentCategory->getMarker($tempHtml,$sims,$rems,$wrapper);
			$sims['###LEVEL###'] = $level;
			$treeHtml .= tx_cal_functions::substituteMarkerArrayNotCached($tempHtml, $sims, $rems, $wrapper);
			
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
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($this->conf['view.']['calendar.']['calendar.']['addIcon']);
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array (
				'view' => 'create_calendar',
				'type' => 'tx_cal_calendar'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['createCalendarViewPid']
			);
			$sims['###CREATE_CALENDAR_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['calendar.']['calendar.']['addLink'],$this->conf['view.']['calendar.']['calendar.']['addLink.']);
		}
	}

	function list_months($this_year, $dateFormat_month) {
		if($this->conf['view.']['other.']['listMonth_referenceToday']==1){
			$this_year = strftime('%Y');
		}
		
		$viewTarget = $this->conf['view.']['monthLinkTarget'];
		$day_array2 = array();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->conf['getdate'], $day_array2);
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

			if (!empty ($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid'])) {
				$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $monthdate, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $monthdate, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link;
			
			if ($month_month == $this_month) {
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['other.']['listMonthSelected_stdWrap.']);
				$return .= str_replace('###MONTH###',$select_month,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['other.']['listMonth_stdWrap.']);
				$return .= str_replace('###MONTH###',$select_month,$tmp);
			}
			$month_time->addSeconds(86400 * 32);
			$month_time = tx_cal_calendar::calculateStartMonthTime($month_time);
		}
		return $return;
	}

	function list_years($this_year, $dateFormat) {
		$viewTarget = $this->conf['view.']['yearLinkTarget'];
		$day_array2 = array();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);
		
		$yearSize = intval($this->conf['view.']['other.']['listYear_totalYearCount']);
		$yearSize = $yearSize ? $yearSize : 3; // ensure valid data
		
		$yearOffset = intval($this->conf['view.']['other.']['listYear_previousYearCount']);
		$yearOffset = ($yearOffset < $yearSize) ? $yearOffset : intval($yearSize/2);
		
		$currentYear = $this_year - $yearOffset;
		
		$getdate_year = strftime($dateFormat, $unix_time);

		for ($i = 0; $i < $yearSize; $i ++) {
			$date = $currentYear.$this_month.$this_day;
			$year = gmstrftime($dateFormat, gmmktime(0,0,0,$this_month,$this_day,$currentYear));
			
			if (!empty ($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid'])) {
				$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $date, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $date, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link;
			if($currentYear == $this_year) {
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['other.']['listYearSelected_stdWrap.']);
			} else {
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['other.']['listYear_stdWrap.']);
			}
			$return .= str_replace('###YEAR###',$year,$tmp);
			
			$currentYear++; 
		}

		return $return;
	}

	function list_weeks($this_year, $dateFormat_week_jump) {
		$viewTarget = $this->conf['view.']['weekLinkTarget'];
		
		if($this->conf['view.']['other.']['listWeek_onlyShowCurrentYear']) {
			$weekSize = 52;
			
			$start_week_time = new tx_cal_date($this->controller->getDateTimeObject->getYear().'0101000000');
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
		
		$start_week_time = tx_cal_calendar::calculateStartWeekTime($start_week_time);
		$end_week_time = tx_cal_calendar::calculateEndWeekTime($start_week_time);
		$formattedGetdate = $this->controller->getDateTimeObject->format('%Y%m%d');
		for ($i=0; $i < $weekSize; $i++) {
			$weekdate = $start_week_time->format('%Y%m%d');
			$select_week1 = $start_week_time->format($dateFormat_week_jump);
			$select_week2 = $end_week_time->format($dateFormat_week_jump);


			if (!empty ($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid'])) {
				$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $weekdate, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
			} else {
				$link = $this->controller->pi_linkTP_keepPIvars_url(array ('getdate' => $weekdate, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway']);
			}
			$link = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$link;
			$formattedStart = $start_week_time->format('%Y%m%d');
			$formattedEnd = $end_week_time->format('%Y%m%d');
			if (($formattedGetdate >= $formattedStart) && ($formattedGetdate <= $formattedEnd)) {
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['other.']['listWeeksSelected_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			} else {
				$tmp = $this->cObj->stdWrap($link, $this->conf['view.']['other.']['listWeeks_stdWrap.']);
				$tmp = str_replace('###WEEK1###',$select_week1,$tmp);
				$return .= str_replace('###WEEK2###',$select_week2,$tmp);
			}
			$start_week_time->addSeconds(604800);
			$end_week_time->addSeconds(604800);
		}

		return $return;
	}

	function tomorrows_events($template) {

		$starttime = new tx_cal_date($this->conf['getdate'].'000000');
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
			return tx_cal_functions::substituteMarkerArrayNotCached($template, array (), $rems, array ());
		}
		$rems['###T_ALLDAY_SWITCH###'] = '';
		$rems['###T_EVENT_SWITCH###'] = '';
		return tx_cal_functions::substituteMarkerArrayNotCached($template, array (), $rems, array ());
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
	
	function _draw_month_new($offset = '+0', $type){
		if(preg_match('![+|-][0-9]{1,2}!is',$offset)) { // new one
			$monthDate = new tx_cal_date();
			$monthDate->copy($this->controller->getDateTimeObject);
			$monthDate->setDay(15);
			if(intval($offset)<0){
				$monthDate->subtractSeconds(abs(intval($offset))*2592000);
			} else {
				$monthDate->addSeconds(intval($offset)*2592000);
			}
		} else {
			$monthDate = new tx_cal_date();
			$monthDate->copy($this->controller->getDateTimeObject);
			$monthDate->setDay(15);
			if(intval($offset)>12){
				$monthDate->setYear($monthDate->getYear() + ($offset - ($offset%12)) /12);
				$monthDate->setMonth($offset%12);
			} else {
				$monthDate->setMonth($offset);
			}
		}

		require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_monthview.php');
		$page = $this->cObj->fileResource($this->conf['view.']['month.']['new'.ucwords($type).'MonthTemplate']);
		
		$monthModel = tx_cal_new_monthview::getMonth($monthDate->month, $monthDate->year);
		$today = new tx_cal_date();
		$monthModel->setCurrent($today);
		$selected = new tx_cal_date($this->conf['getdate']);
		$monthModel->setSelected($selected);
		
		$monthModel->weekDayFormat = $this->conf['view.'][$this->conf['view'].'.']['weekdayFormat'.ucwords($type).'Month'];
		$weekdayLength = intval($this->conf['view.'][$this->conf['view'].'.']['weekdayLength'.ucwords($type).'Month']);
		if($weekdayLength > 0){
			$monthModel->weekDayLength = $weekdayLength;
		}
		
		

		$masterArrayKeys = array_keys($this->master_array);
		foreach ($masterArrayKeys as $dateKey) {
			$dateArray = &$this->master_array[$dateKey];
			$dateArrayKeys = array_keys($dateArray);
			foreach ($dateArrayKeys as $timeKey) {
				$arrayOfEvents = &$dateArray[$timeKey];
				$eventKeys = array_keys($arrayOfEvents);
				foreach ($eventKeys as $eventKey) {
					$monthModel->addEvent($arrayOfEvents[$eventKey]);
				}
			}
		}
							
		return $monthModel->render($page);
	}

		/**
	 * Draws the month view
	 *  @param		$page	string		The page template
	 *  @param		$offset	integer		The month offset. Default = +0
	 *  @param		$type	integer		The date of the event
	 *	@return		string		The HTML output.
	 */
	function _draw_month($page, $offset = '+0', $type) {
		$viewTarget = $this->conf['view.']['monthLinkTarget'];
		$monthTemplate = $this->cObj->getSubpart($page, '###MONTH_TEMPLATE###');
		if($monthTemplate!=''){
			$loop_wd = $this->cObj->getSubpart($monthTemplate, '###LOOPWEEKDAY###');
			$t_month = $this->cObj->getSubpart($monthTemplate, '###SWITCHMONTHDAY###');
			$startweek = $this->cObj->getSubpart($monthTemplate, '###LOOPMONTHWEEKS_DAYS###');
			$endweek = $this->cObj->getSubpart($monthTemplate, '###LOOPMONTHDAYS_WEEKS###');
			$weeknum = $this->cObj->getSubpart($monthTemplate, '###LOOPWEEK_NUMS###');
			$corner = $this->cObj->getSubpart($monthTemplate, '###CORNER###');

			/* 11.12.2008 Franz:
			* why is there a limitation that only MEDIUM calendar sheets can have absolute offsets and vice versa?
			* I'm commenting this out and make it more flexible.
			*/
			#if ($type != 'medium') {  // old one
			if(preg_match('![+|-][0-9]{1,2}!is',$offset)) { // new one
				$fake_getdate_time = new tx_cal_date();
				$fake_getdate_time->copy($this->controller->getDateTimeObject);
				$fake_getdate_time->setDay(15);
				if(intval($offset)<0){
					$fake_getdate_time->subtractSeconds(abs(intval($offset))*2592000);
				} else {
					$fake_getdate_time->addSeconds(intval($offset)*2592000);
				}
			} else {
				$fake_getdate_time = new tx_cal_date();
				$fake_getdate_time->copy($this->controller->getDateTimeObject);
				$fake_getdate_time->setDay(15);
				$fake_getdate_time->setMonth($offset);
			}
			
			$minical_month = $fake_getdate_time->getMonth();
			$minical_year = $fake_getdate_time->getYear();
			$today = new tx_cal_date();
	
			$month_title = $fake_getdate_time->format($this->conf['view.'][$viewTarget.'.']['dateFormatMonth']);
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($month_title);
			$this->local_cObj->data['view'] = $viewTarget;
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $fake_getdate_time->format('%Y%m%d'), 'view' => $viewTarget,  $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
			$month_title = $this->local_cObj->cObjGetSingle($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewLink'],$this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewLink.']);
			$month_date = $fake_getdate_time->format('%Y%m%d');
	
			$view_array = array ();
			
			if(!$this->viewarray){
				$this->eventArray = array();
				if (!empty($this->master_array)) {
					// use array keys for the loop in order to be able to use referenced events instead of copies and save some memory
					$masterArrayKeys = array_keys($this->master_array);
					foreach ($masterArrayKeys as $dateKey) {
						$dateArray = &$this->master_array[$dateKey];
						$dateArrayKeys = array_keys($dateArray);
						foreach ($dateArrayKeys as $timeKey) {
							$arrayOfEvents = &$dateArray[$timeKey];
							$eventKeys = array_keys($arrayOfEvents);
							foreach ($eventKeys as $eventKey) {
								$event = &$arrayOfEvents[$eventKey];
								$eventReferenceKey = $dateKey.'_'.$event->getType().'_'.$event->getUid().'_'.$event->getStart()->format('%Y%m%d%H%M%S');
								$this->eventArray[$eventReferenceKey] = &$event;
								$starttime = new tx_cal_date();
								$starttime->copy($event->getStart());
								$endtime = new tx_cal_date();
								$endtime->copy($event->getEnd());
								if($timeKey=='-1'){
									$endtime->addSeconds(1); // needed to let allday events show up
								}
								$j = new tx_cal_date();
								$j->copy($starttime);
								$j->setHour(0);
								$j->setMinute(0);
								$j->setSecond(0);
								for ($j;$j->before($endtime); $j->addSeconds(60 * 60 * 24)) {
									$view_array[$j->format('%Y%m%d')]['000000'][count($view_array[$j->format('%Y%m%d')]['000000'])] = $eventReferenceKey;
								}
							}
						}
					}
				}
				$this->viewarray = &$view_array;
			}

			$monthTemplate = str_replace('###MONTH_TITLE###',$month_title,$monthTemplate);
	
			$langtype = $this->conf['view.']['month.']['weekdayFormat'.ucwords($type).'Month'];
			$typeSize = intval($this->conf['view.']['month.']['weekdayLength'.ucwords($type).'Month']);

			$dateOfWeek = Date_Calc::beginOfWeek(15,$fake_getdate_time->getMonth(),$fake_getdate_time->getYear());
			$start_day = new tx_cal_date($dateOfWeek.'000000');

			// backwardscompatibility with old templates
			if(!empty($corner)) {
				$weekday_loop .= str_replace('###ADDITIONAL_CLASSES###',$this->conf['view.']['month.']['monthCornerStyle'],$corner);
			} else {
				$weekday_loop .= sprintf($weeknum, $this->conf['view.']['month.']['monthCornerStyle'], '');
			}

			for ($i = 0; $i < 7; $i ++) {
				$weekday = $start_day->format($langtype);
				$weekdayLong = $start_day->format('%A');
				if($typeSize){
					$weekday = $this->cs_convert->substr(tx_cal_functions::getCharset(),$weekday,0,$typeSize);
				}
				$start_day->addSeconds(86400);
				
				$additionalClasses = trim(sprintf($this->conf['view.']['month.']['monthDayOfWeekStyle'],$start_day->format('%w')));
				$markerArray = array(
					'###WEEKDAY###' => $weekday,
					'###WEEKDAY_LONG###' => $weekdayLong,
					'###ADDITIONAL_CLASSES###' => ' '.$additionalClasses,
					'###CLASSES###'=> (!empty($additionalClasses) ? ' class="'.$additionalClasses.'" ' : ''),
				);
				$weekday_loop .= strtr($loop_wd,$markerArray);
			}
			$weekday_loop .= $endweek;
			
			$dateOfWeek = Date_Calc::beginOfWeek(1,$fake_getdate_time->getMonth(),$fake_getdate_time->getYear());
			$endOfMonth = $this->controller->getListViewTime('monthend',$start_day);

			$start_day = new tx_cal_date($dateOfWeek.'000000');
			$start_day->setTZbyID('UTC');
	
			$i = 0;
			$whole_month = TRUE;
			$isAllowedToCreateEvent = $this->rightsObj->isAllowedToCreateEvent();

			$createOffset = intval($this->conf['rights.']['create.']['event.']['timeOffset']) * 60;
			
			$getdate = new tx_cal_date($this->conf['getdate']);
			$getdate->setTZbyID('UTC');
			$startWeekTime = tx_cal_calendar::calculateStartWeekTime($getdate);
			$endWeekTime = tx_cal_calendar::calculateEndWeekTime($getdate);
			
			$formattedWeekStartTime = $startWeekTime->format('%Y%m%d');
			$formattedWeekEndTime = $endWeekTime->format('%Y%m%d');
			do {
				$daylink = new tx_cal_date();
				$daylink->copy($start_day);

				$formatedGetdate = $daylink->format('%Y%m%d');
				$formatedDayDate = $daylink->format($this->conf['view.']['month.']['dateFormatDay']);
				
				$isCurrentWeek = false;
				$isSelectedWeek = false;
				if ($formatedGetdate>=$formattedWeekStartTime && $formatedGetdate<=$formattedWeekEndTime) {
					$isSelectedWeek = true;
				}

				if ($start_day->format('%Y%U') == $today->format('%Y%U')) {
					$isCurrentWeek = true;
				}

				if ($i == 0 && !empty($weeknum)){
					$start_day->addSeconds(86400);
					$num = $numPlain = $start_day->format('%U');
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
					$weekLinkViewTarget = $this->conf['view.']['weekLinkTarget'];
					if(($this->rightsObj->isViewEnabled($weekLinkViewTarget) || $this->conf['view.'][$weekLinkViewTarget.'.'][$weekLinkViewTarget.'ViewPid']) && $hasEvent){
						$this->initLocalCObject();
						$this->local_cObj->setCurrentVal($num);
						$this->local_cObj->data['view'] = $weekLinkViewTarget;
						$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $formatedGetdate, 'view' => $weekLinkViewTarget,  $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$weekLinkViewTarget.'.'][$weekLinkViewTarget.'ViewPid']);
						$num  = $this->local_cObj->cObjGetSingle($this->conf['view.'][$weekLinkViewTarget.'.'][$weekLinkViewTarget.'ViewLink'],$this->conf['view.'][$weekLinkViewTarget.'.'][$weekLinkViewTarget.'ViewLink.']);
					}

					$className = array();
					if ($isSelectedWeek && !empty($this->conf['view.']['month.']['monthSelectedWeekStyle'])) {
						$className[] = $this->conf['view.']['month.']['monthSelectedWeekStyle'];
					}
					if ($isCurrentWeek && !empty($this->conf['view.']['month.']['monthCurrentWeekStyle'])) {
						$className[] = $this->conf['view.']['month.']['monthCurrentWeekStyle'];
					}
					if ($hasEvent && !empty($this->conf['view.']['month.']['monthWeekWithEventStyle'])) {
						$className[] = $this->conf['view.']['month.']['monthWeekWithEventStyle'];
					}

					$weekClasses = trim(implode(' ',$className));
					$markerArray = array (
						'###ADDITIONAL_CLASSES###' => ($weekClasses ? ' '.$weekClasses : ''),
						'###CLASSES###' => ($weekClasses ? ' class="'.$weekClasses.'" ' : ''),
						'###WEEKNUM###' => $num,
						'###WEEKNUM_PLAIN###' => $numPlain,
					);
					$middle .= strtr($startweek,$markerArray);
					// we do this sprintf all only for backwards compatibility with old templates
					$middle .= strtr(sprintf($weeknum,$markerArray['###ADDITIONAL_CLASSES###'],$num),$markerArray);
				}
				$i ++;
				$switch = array ('###ALLDAY###' => '');
				$check_month = $start_day->getMonth();
				
				$switch['###LINK###'] = $this->getCreateEventLink('month','',$start_day,$createOffset,$isAllowedToCreateEvent,'','',$this->conf['view.']['day.']['dayStart']);
				
				$style = array();
				
				$dayLinkViewTarget = $this->conf['view.']['dayLinkTarget'];			
				if(($this->rightsObj->isViewEnabled($dayLinkViewTarget) || $this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid']) && ($this->viewarray[$formatedGetdate] || $isAllowedToCreateEvent)){
					$this->initLocalCObject();
					$this->local_cObj->setCurrentVal($formatedDayDate);
					$this->local_cObj->data['view'] = $dayLinkViewTarget;
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $formatedGetdate, 'view' => $dayLinkViewTarget,  $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid']);
					$switch['###LINK###'] .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewLink'],$this->conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewLink.']);
					if ($switch['###LINK###']==='') {
						$switch['###LINK###'] .= $formatedDayDate;
					}	
					$switch['###LINK###'] = $this->cObj->stdWrap($switch['###LINK###'], $this->conf['view.']['month.'][$type.'Link_stdWrap.']);
				}else{
					$switch['###LINK###'] .= $formatedDayDate;
				}
				// add a css class if the current day has a event - regardless if linked or not
				if($this->viewarray[$formatedGetdate]) {
					$style[] = $this->conf['view.']['month.']['eventDayStyle'];
				}
				$style[] = $this->conf['view.']['month.']['month'.ucfirst($type).'Style'];				
				
				if ($check_month != $minical_month) {
					$style[] = $this->conf['view.']['month.']['monthOffStyle'];
				}
				if ($start_day->format('%w')==0 || $start_day->format('%w')==6) {
					$style[] = $this->conf['view.']['month.']['monthDayWeekendStyle'];
				}
				if ($isSelectedWeek) {
					$style[] = $this->conf['view.']['month.']['monthDaySelectedWeekStyle'];
				}
				if ($formatedGetdate == $this->conf['getdate']) {
					$style[] = $this->conf['view.']['month.']['monthSelectedStyle'];
				}
				if ($isCurrentWeek) {
					$style[] = $this->conf['view.']['month.']['monthDayCurrentWeekStyle'];
				}
				if ($formatedGetdate == $today->format('%Y%m%d')) {
					$style[] = $this->conf['view.']['month.']['monthTodayStyle'];
				}
				if ($this->conf['view.']['month.']['monthDayOfWeekStyle']) {
					$style[] = sprintf($this->conf['view.']['month.']['monthDayOfWeekStyle'],$start_day->format('%w')); 
				}
				
				//clean up empty styles (code beautify)
				foreach($style as $key => $classname) {
					if($classname == '') {
						unset($style[$key]);
					}
				}
				
				// Adds hook for processing of extra month day style markers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cal_controller']['extraMonthDayStyleMarkerHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cal_controller']['extraMonthDayStyleMarkerHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						if(is_object($_procObj) && method_exists($_procObj,'extraMonthDayStyleMarkerProcessor')) {
							$_procObj->extraMonthDayStyleMarkerProcessor($this,$daylink,$switch,$type,$style);
						}
					}
				}
				
				$classesDay = implode(' ',$style);
				$markerArray = array(
					'###STYLE###' => $classesDay,
					'###ADDITIONAL_CLASSES###' => ($classesDay ? ' '.$classesDay : ''),
					'###CLASSES###' => ($classesDay ? ' class="'.$classesDay.'" ' : ''),
					'###DAY_ID###' => $formatedGetdate,
				);
				
				$temp = strtr($t_month,$markerArray);

				$wraped = array();
				
				if ($this->viewarray[$formatedGetdate] && preg_match('!\###EVENT\###!is',$t_month)) {
					foreach ($this->viewarray[$formatedGetdate] as $cal_time => $event_times) {
						foreach ($event_times as $uid => $eventId) {
							if ($type == 'large'){
								$switch['###EVENT###'] .= $this->eventArray[$eventId]->renderEventForMonth();
							} else if ($type == 'medium') {
								$switch['###EVENT###'] .= $this->eventArray[$eventId]->renderEventForYear();
							} else if ($type == 'small') {
								$switch['###EVENT###'] .= $this->eventArray[$eventId]->renderEventForMiniMonth();
							}
						}
					}
				}
				
				if ( !isset($switch['###EVENT###']) ) {
					$this->initLocalCObject();
					$switch['###EVENT###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$viewTarget.'.']['event.']['noEventFound'],$this->conf['view.'][$viewTarget.'.']['event.']['noEventFound.']);
				}
				if ( !isset($switch['###ALLDAY###']) ) {
					$this->initLocalCObject();
					$switch['###ALLDAY###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$viewTarget.'.']['event.']['noEventFound'],$this->conf['view.'][$viewTarget.'.']['event.']['noEventFound.']);
				}
				
	            // Adds hook for processing of extra month day markers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cal_controller']['extraMonthDayMarkerHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cal_controller']['extraMonthDayMarkerHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						if(is_object($_procObj) && method_exists($_procObj,'extraMonthDayMarkerProcessor')) {
							$switch = $_procObj->extraMonthDayMarkerProcessor($this,$daylink,$switch,$type);
						}
					}
				}
	        
	        
				$middle .= tx_cal_functions::substituteMarkerArrayNotCached($temp, $switch, array(), $wraped);
	
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
			$rems['###CORNER###'] = '';
			$monthTemplate = tx_cal_functions::substituteMarkerArrayNotCached($monthTemplate, array (), $rems, array ());
			$monthTemplate .= $ajaxEvents;
			$page = tx_cal_functions::substituteMarkerArrayNotCached($page, array(), array ('###MONTH_TEMPLATE###'=>$monthTemplate), array ());
		}
		
		$listTemplate = $this->cObj->getSubpart($page, '###LIST###');
		if($listTemplate!=''){
			$tx_cal_listview = &t3lib_div::makeInstanceService('cal_view', 'list', 'list');
			$starttime = gmmktime(0,0,0,$this_month,1,$this_year);
			$endtime = gmmktime(0,0,0,$this_month+1,1,$this_year);
			$rems['###LIST###'] = $tx_cal_listview->drawList($this->master_array,$listTemplate,$starttime,$endtime);
		}

		$return = tx_cal_functions::substituteMarkerArrayNotCached($page, array (), $rems, array ());

		if($this->rightsObj->isViewEnabled($viewTarget) || $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']){
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($month_title);
			$this->local_cObj->data['view'] = $viewTarget;
			$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array ('getdate' => $month_date, 'view' => $viewTarget, $this->pointerName => NULL), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewPid']);
			$month_link  = $this->local_cObj->cObjGetSingle($this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewLink'],$this->conf['view.'][$viewTarget.'.'][$viewTarget.'ViewLink.']);
		}else{
			$month_link = $month_title;
		}

		$return = str_replace('###MONTH_LINK###', $month_link, $return);

		return $return;
	}
	
	function getMeetingInformationMarker(&$page, &$sims, &$rems, $view){
		$sims['###MEETING_INFORMATION###'] = '';
		$foundEvents = Array();
		$eventService = &tx_cal_functions::getEventService();
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
	
	function getSidebarMarker(&$page, &$sims, &$rems, $view){
		$page = $this->replace_files($page, array (
		'sidebar' => $this->conf['view.']['other.']['sidebarTemplate'])
		);
	}
	
	function getCalendarNavMarker(&$page, &$sims, &$rems, $view){
		if($this->conf['view.']['month.']['navigation']==0){
			$page = str_replace('###CALENDAR_NAV###', '', $page);
		}else{
			$template = $this->cObj->fileResource($this->conf['view.']['month.']['horizontalSidebarTemplate']);
			if ($template == '') {
				$template = '<h3>calendar: no calendar_nav template file found:</h3>'.$this->conf['view.']['month.']['horizontalSidebarTemplate'];
			}
			$page = str_replace('###CALENDAR_NAV###', $template, $page);
		}
	}
	
	/**
	 * Method to initialise a local content object, that can be used for customized TS rendering with own db values
	 * @param	$customData	array	Array with key => value pairs that should be used as fake db-values for TS rendering instead of the values of the current object
	 */	
	function initLocalCObject($customData = false) {
		if (!is_object($this->local_cObj)) {
			$this->local_cObj = &tx_cal_registry::Registry('basic','local_cObj');
		}
		if ($customData && is_array($customData)) {
			$this->local_cObj->data = $customData;
		} else {
			$this->local_cObj->data = $this->cachedValueArray;
		}
	}
	
	/**
	 * Method to return all values from current view, that might be interresting for rendering TS objects
	 * @return	array	Array with key => value pairs that might be interresting
	 */	
	function getValuesAsArray() {
		if(!is_array($this->cachedValueArray) || (is_array($this->cachedValueArray) && !count($this->cachedValueArray))) {
			// for now, just return the data of the parent cObject
			$this->cachedValueArray = &$this->cObj->data;
		}
		return $this->cachedValueArray;
	}
	
	function getAllowedToCreateEventsMarker(&$page, &$sims, &$rems, $view){
		$sims['###ALLOWED_TO_CREATE_EVENTS###'] = $this->rightsObj->isAllowedToCreateEvent();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_base_view.php']);
}
?>