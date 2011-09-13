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
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_confirm_event_view extends tx_cal_base_view {

	var $editMode = false;
	var $confArr = array();
	
	/**
	 *  Draws a confirm event form.
	 *  @param      object      The cObject of the mother-class
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawConfirmEvent() {		
//debug($this->controller->piVars);
//debug($_GET);
//debug($_POST);
		$modelObj = $this->controller->modelObj;		

		$page = $this->cObj->fileResource($this->conf['view.']['event.']['confirmEventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no confirm event template file found:</h3>'.$this->conf['view.']['event.']['confirmEventTemplate'];
		}
		
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		
		$lastViewParams = $this->controller->shortenLastViewAndGetTargetViewParameters();

		if($lastViewParams['view']=='edit_event'){
			$this->editMode = true;
		}
		
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'save_event';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###OPTION###'] = $this->conf['option'];
		$sims['###CALENDAR_ID###'] = intval($this->controller->piVars['calendar_id']);
		$sims['###L_CONFIRM_EVENT###'] = $this->controller->pi_getLL('l_confirm_event');
		$sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'save_event'));
		
		$this->getTemplateSubpartMarker(& $page, & $rems, & $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$this->getTemplateSingleMarker(& $page, & $rems, & $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function getTemplateSubpartMarker(& $template, & $rems, & $sims) {
		
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

		$allMarkers = array_unique($match[1]);

		foreach ($allMarkers as $marker) {
			switch ($marker) {
				case 'FORM_START' :
					$this->getFormStartMarker($template, $rems, $sims);
					break;
				case 'FORM_END' :
					$this->getFormEndMarker($template, $rems, $sims);
					break;
				case 'FORM_CALENDAR' :
					$this->getCalendarMarker($template, $rems, $sims);
					break;
				case 'PRE_FORM' :
					$this->getPreFormMarker($template, $rems, $sims);
					break;
				case 'FORM_HIDDEN' :
					$this->getHiddenMarker($template, $rems, $sims);
					break;
				case 'FORM_CATEGORY' :
					$this->getCategoryMarker($template, $rems, $sims);
					break;
				case 'FORM_DATETIME' :
					$this->getDateTimeMarker($template, $rems, $sims);
					break;
				case 'FORM_TITLE' :
					$this->getTitleMarker($template, $rems, $sims);
					break;
				case 'FORM_ORGANIZER' :
					$this->getOrganizerMarker($template, $rems, $sims);
					break;
				case 'FORM_LOCATION' :
					$this->getLocationMarker($template, $rems, $sims);
					break;
				case 'FORM_DESCRIPTION' :
					$this->getDescriptionMarker($template, $rems, $sims);
					break;
				case 'FORM_RECURRING' :
					$this->getRecurringMarker($template, $rems, $sims);
					break;
				case 'FORM_NOTIFY' :
					$this->getNotifyMarker($template, $rems, $sims);
					break;
				case 'FORM_EXCEPTION' :
					$this->getExceptionMarker($template, $rems, $sims);
					break;
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
				    if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $rems, $sims);
				    } 
					break;
			}
		}
	}
	
	function getTemplateSingleMarker(& $template, & $rems, & $sims) {
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_event_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
//debug('function: '.$funcFromMarker);
				        $this->$funcFromMarker($template, $rems, $sims);
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap']);
					}
					break;
			}
		}
	}
	
	function getFormStartMarker(& $template, & $rems, & $sims){
		
		$rems['###FORM_START###'] = $this->cObj->getSubpart($template, '###FORM_START###');
	}
	
	function getHiddenMarker(& $template, & $rems, & $sims){
		$sims['###HIDDEN###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventHidden()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventHidden())){
			if ($this->controller->piVars['hidden'] == 'on') {
				$hidden = 'true';
			} else {
				$hidden = 'false';
			}
			$sims['###HIDDEN###'] = $this->cObj->stdWrap($hidden, $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
			$sims['###HIDDEN_VALUE###'] = $hidden?1:0;
		}
	}
	
	function getCalendarMarker(& $template, & $rems, & $sims){
		$sims['###CALENDAR###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventCalendar()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventCalendar())){
			if ($this->controller->piVars['calendar_id']) {
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_calendar','uid = '.intval($this->controller->piVars['calendar_id']).'');
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$sims['###CALENDAR_VALUE###'] = $row['uid'];
					$sims['###CALENDAR###'] = $this->cObj->stdWrap($row['title'], $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);;
				}
			}
		}
	}
	
	function getCategoryMarker(& $template, & $rems, & $sims){
		$sims['###CATEGORY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventCategory()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventCategory())){
			if ($this->controller->piVars['category']) {
				$temp = $this->cObj->getSubpart($template, '###FORM_CATEGORY###');
				$ids = array();
				$names = array();
				$tempVar = $this->conf['calendar'];
				$this->conf['calendar'] = $this->conf['calendar_id'];
				$categoryArray = $this->modelObj->findAllCategories('','tx_cal_category',$this->conf['pidList']);
				foreach ($this->controller->piVars['category'] as $value) {
					$ids[] = $value;
					$names[] = $categoryArray[0][$value]->getTitle();
				}
				$this->conf['calendar'] = $tempVar;

				$sims['###CATEGORY_VALUE###'] = implode(',',$ids);
				$sims['###CATEGORY###'] = $this->cObj->stdWrap(implode(', ',$names), $this->conf['view.'][$this->conf['view'].'.']['category_stdWrap.']);;
			}
		}
	}
	
	function getAlldayMarker(& $template, & $rems, & $sims){
		$sims['###ALLDAY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventDateTime()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventDateTime())){
			$allday = 'false';
			if ($this->controller->piVars['allday'] == 'on') {
				$allday = 'true';
			}
			$sims['###ALLDAY###'] = $this->cObj->stdWrap($allday, $this->conf['view.'][$this->conf['view'].'.']['allday_stdWrap.']);
			$sims['###ALLDAY_VALUE###'] = $allday == 'true'?1:0;
		}
	}
	
	function getStartdateMarker(& $template, & $rems, & $sims){
		$sims['###STARTDATE###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventDateTime()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventDateTime())){
			
			$dateFormatArray = explode($this->conf['view.']['event.']['dateSplitSymbol'],strip_tags($this->controller->piVars['event_start_day']));
			$sims['###STARTDATE_VALUE###'] = $dateFormatArray[$this->conf['view.']['event.']['dateYearPosition']].$dateFormatArray[$this->conf['view.']['event.']['dateMonthPosition']].$dateFormatArray[$this->conf['view.']['event.']['dateDayPosition']];
			$sims['###STARTDATE###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['event_start_day']), $this->conf['view.'][$this->conf['view'].'.']['startdate_stdWrap.']);;
		}
	}
	
	function getEnddateMarker(& $template, & $rems, & $sims){
		$sims['###ENDDATE###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventDateTime()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventDateTime())){
			
			$dateFormatArray = explode($this->conf['view.']['event.']['dateSplitSymbol'],strip_tags($this->controller->piVars['event_end_day']));
			$sims['###ENDDATE_VALUE###'] = $dateFormatArray[$this->conf['view.']['event.']['dateYearPosition']].$dateFormatArray[$this->conf['view.']['event.']['dateMonthPosition']].$dateFormatArray[$this->conf['view.']['event.']['dateDayPosition']];
			$sims['###ENDDATE###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['event_end_day']), $this->conf['view.'][$this->conf['view'].'.']['enddate_stdWrap.']);;
		}
	}
	
	function getStarttimeMarker(& $template, & $rems, & $sims){
		$sims['###STARTTIME###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventDateTime()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventDateTime())){
			
			$sims['###STARTTIME_VALUE###'] = strip_tags($this->controller->piVars['event_start_hour']).strip_tags($this->controller->piVars['event_start_minutes']);
			$sims['###STARTTIME###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['event_start_hour']).':'.strip_tags($this->controller->piVars['event_start_minutes']), $this->conf['view.'][$this->conf['view'].'.']['starttime_stdWrap.']);;
		}
	}
	
	function getEndtimeMarker(& $template, & $rems, & $sims){
		$sims['###ENDTIME###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventDateTime()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventDateTime())){
			$sims['###ENDTIME_VALUE###'] = strip_tags($this->controller->piVars['event_end_hour']).strip_tags($this->controller->piVars['event_end_minutes']);
			$sims['###ENDTIME###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['event_end_hour']).':'.strip_tags($this->controller->piVars['event_end_minutes']), $this->conf['view.'][$this->conf['view'].'.']['endtime_stdWrap.']);;
		}
	}
	
	function getTitleMarker(& $template, & $rems, & $sims){
		$sims['###TITLE###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventTitle()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventTitle())){
			$sims['###TITLE###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['title']), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);;
			$sims['###TITLE_VALUE###'] = strip_tags($this->controller->piVars['title']);
		}
	}
	
	function getOrganizerMarker(& $template, & $rems, & $sims){
		$sims['###ORGANIZER###'] = '';
		if(!$this->confArr['hideOrganizerTextfield']){
			if(($this->editMode && $this->rightsObj->isAllowedToEditEventOrganizer()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventOrganizer())){
				$sims['###ORGANIZER###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['organizer']), $this->conf['view.'][$this->conf['view'].'.']['organizer_stdWrap.']);;
				$sims['###ORGANIZER_VALUE###'] = strip_tags($this->controller->piVars['organizer']);
			}
		}
	}
	
	function getCalOrganizerMarker(& $template, & $rems, & $sims){	
		$sims['###CAL_ORGANIZER###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventOrganizer()) || $this->rightsObj->isAllowedToCreateEventOrganizer()){
			if ($this->controller->piVars['cal_organizer']) {
				$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer');
				$organizer = $this->modelObj->findOrganizer(intval($this->controller->piVars['cal_organizer']),$useOrganizerStructure);
				$sims['###CAL_ORGANIZER_VALUE###'] = $organizer->getUid();
				$sims['###CAL_ORGANIZER###'] = $this->cObj->stdWrap($organizer->getName(), $this->conf['view.'][$this->conf['view'].'.']['cal_organizer_stdWrap.']);;
			}
		}
	}
	
	function getLocationMarker(& $template, & $rems, & $sims){
		$sims['###LOCATION###'] = '';
		if(!$this->confArr['hideLocationTextfield']){
			if(($this->editMode && $this->rightsObj->isAllowedToEditEventLocation()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventLocation())){
				$sims['###LOCATION###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['location']), $this->conf['view.'][$this->conf['view'].'.']['location_stdWrap.']);;
				$sims['###LOCATION_VALUE###'] = strip_tags($this->controller->piVars['location']);
			}
		}
	}
	
	function getCalLocationMarker(& $template, & $rems, & $sims){	
		$sims['###CAL_LOCATION###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventLocation()) || $this->rightsObj->isAllowedToCreateEventLocation()){
			if ($this->controller->piVars['cal_location']) {
				$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');
				$location = $this->modelObj->findLocation(intval($this->controller->piVars['cal_location']),$useLocationStructure);
				$sims['###CAL_LOCATION_VALUE###'] = $location->getUid();
				$sims['###CAL_LOCATION###'] = $this->cObj->stdWrap($location->getName(), $this->conf['view.'][$this->conf['view'].'.']['cal_location_stdWrap.']);
			}
		}
	}
	
	function getDescriptionMarker(& $template, & $rems, & $sims){
		$sims['###DESCRIPTION###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventDescription()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventDescription())){
			$sims['###DESCRIPTION###'] = $this->cObj->stdWrap($this->cObj->removeBadHTML($this->controller->piVars['description'], $this->conf), $this->conf['view.'][$this->conf['view'].'.']['description_stdWrap.']);
			$sims['###DESCRIPTION_VALUE###'] = htmlentities($this->cObj->removeBadHTML($this->controller->piVars['description'], $this->conf));
		}
	}
	
	function getTeaserMarker(& $template, & $rems, & $sims){
		$sims['###TEASER###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventTeaser()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventTeaser())){
			$sims['###TEASER###'] = $this->cObj->stdWrap($this->cObj->removeBadHTML($this->controller->piVars['teaser'], $this->conf), $this->conf['view.'][$this->conf['view'].'.']['teaser_stdWrap.']);
			$sims['###TEASER_VALUE###'] = htmlentities($this->cObj->removeBadHTML($this->controller->piVars['teaser'], $this->conf));
		}
	}
	
	function getFrequencyMarker(& $template, & $rems, & $sims){
		$sims['###FREQUENCY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###FREQUENCY###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['frequency_id']), $this->conf['view.'][$this->conf['view'].'.']['frequency_stdWrap.']);
			$sims['###FREQUENCY_VALUE###'] = strip_tags($this->controller->piVars['frequency_id']);
		}
	}
	
	function getByDayMarker(& $template, & $rems, & $sims){
		$sims['###BY_DAY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###BY_DAY###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['by_day']), $this->conf['view.'][$this->conf['view'].'.']['byDay_stdWrap.']);
			$sims['###BY_DAY_VALUE###'] = strip_tags($this->controller->piVars['by_day']);
		}
	}
	
	function getByMonthDayMarker(& $template, & $rems, & $sims){
		$sims['###BY_MONTHDAY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###BY_MONTHDAY###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['by_monthday']), $this->conf['view.'][$this->conf['view'].'.']['byMonthday_stdWrap.']);
			$sims['###BY_MONTHDAY_VALUE###'] = strip_tags($this->controller->piVars['by_monthday']);
		}
	}
	
	function getByMonthMarker(& $template, & $rems, & $sims){
		$sims['###BY_MONTH###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###BY_MONTH###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['by_month']), $this->conf['view.'][$this->conf['view'].'.']['byMonth_stdWrap.']);
			$sims['###BY_MONTH_VALUE###'] = strip_tags($this->controller->piVars['by_month']);
		}
	}
	
	function getUntilMarker(& $template, & $rems, & $sims){
		$sims['###UNTIL###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###UNTIL###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['until']), $this->conf['view.'][$this->conf['view'].'.']['until_stdWrap.']);
			$dateFormatArray = explode('-',strip_tags($this->controller->piVars['until']),$this->conf['view.']['event.']['dateSplitSymbol']);
			$sims['###UNTIL_VALUE###'] = $dateFormatArray[$this->conf['view.']['event.']['dateYearPosition']].$dateFormatArray[$this->conf['view.']['event.']['dateMonthPosition']].$dateFormatArray[$this->conf['view.']['event.']['dateDayPosition']];
		}
	}
	
	function getCountMarker(& $template, & $rems, & $sims){
		$sims['###COUNT###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###COUNT###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['count']), $this->conf['view.'][$this->conf['view'].'.']['count_stdWrap.']);
			$sims['###COUNT_VALUE###'] = strip_tags($this->controller->piVars['count']);
		}
	}
	
	function getIntervalMarker(& $template, & $rems, & $sims){
		$sims['###INTERVAL###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventRecurring())){
			$sims['###INTERVAL###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars['interval']), $this->conf['view.'][$this->conf['view'].'.']['interval_stdWrap.']);
			$sims['###INTERVAL_VALUE###'] = strip_tags($this->controller->piVars['interval']);
		}
	}
	
	function getNotifyMarker(& $template, & $rems, & $sims){
		$sims['###NOTIFY###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventNotify()) || (!$this->editMode && $this->rightsObj->isAllowedToCreateEventNotify())){
			if (is_array($this->controller->piVars['notify'])) {
				$notifydisplaylist = '';
				$notifyids = array();
				foreach ($this->controller->piVars['notify'] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					$notifyids[] = $idname[2];
					$notifydisplaylist .= ','.$idname[3];
				}
				$notifydisplaylist = substr($notifydisplaylist, 1, strlen($notifydisplaylist));
				$sims['###NOTIFY###'] = $this->cObj->stdWrap($notifydisplaylist, $this->conf['view.'][$this->conf['view'].'.']['notify_stdWrap.']);
				$sims['###NOTIFY_VALUE###'] = implode(',',$notifyids);
			}
		}
	}
	
	function getExceptionMarker(& $template, & $rems, & $sims){
		$sims['###EXCEPTION###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventException()) || $this->rightsObj->isAllowedToCreateEventException()){
			if (is_array($this->controller->piVars['exception_ids'])) {
				$exceptiondisplaylist = '';
				$single_exception_list = '';
				$group_exception_list = '';
				foreach ($this->controller->piVars['exception_ids'] as $value) {
					preg_match('/(^[a-z])_([0-9]+)_(.*)/', $value, $idname);
					if ($idname[1] == 's') {
						$single_exception_list .= ','.$idname[2];
					} else {
						$group_exception_list .= ','.$idname[2];
					}
					$exceptiondisplaylist .= $idname[3].',';
				}
				$exceptiondisplaylist = substr($exceptiondisplaylist, 0, strlen($exceptiondisplaylist) - 1);
				$sims['###EXCEPTION###'] = $this->cObj->stdWrap($exceptiondisplaylist, $this->conf['view.'][$this->conf['view'].'.']['exception_stdWrap.']);
				$sims['###EXCEPTION_SINGLE_VALUES###'] = $single_exception_list;
				$sims['###EXCEPTION_GROUP_VALUES###'] = $group_exception_list;
				
			}
		}
	}
	
	function getFormEndMarker(& $template, & $rems, & $sims){	
		$temp = $this->cObj->getSubpart($template, '###FORM_END###');
		$temp_sims = array();
		$temp_sims['###BACK_LINK###'] = $this->controller->pi_linkTP_keepPIvars_url( $this->controller->shortenLastViewAndGetTargetViewParameters());
		$temp_sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$temp_sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$rems['###FORM_END###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_event_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_confirm_event_view.php']);
}
?>
