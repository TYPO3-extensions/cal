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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_fe_editing_base_view.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');
    

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_create_event_view extends tx_cal_fe_editing_base_view {
	
	/* RTE vars */
	var $RTEObj;
    var $strEntryField;
    var $docLarge = 0;
    var $RTEcounter = 0;
    var $formName;
    var $additionalJS_initial = '';		// Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
	var $additionalJS_pre = array();	// Additional JavaScript to be printed before the form
	var $additionalJS_post = array();	// Additional JavaScript to be printed after the form
	var $additionalJS_submit = array();	// Additional JavaScript to be executed on submit
    var $PA = array(
            'itemFormElName' =>  '',
            'itemFormElValue' => '',
            );
    var $specConf = array();
    var $thisConfig = array();
    var $RTEtypeVal = 'text';
    var $thePidValue;
    
    var $validation = '';
    var $useDateSelector = false;
    var $dateSelectorConf = '';
    
    var $dateFormatArray = array();
    
    var $cal_notify_user_ids = array();
    var $cal_notify_group_ids = array();
	
	var $eventType = 'tx_cal_phpicalendar';
	
	var $confArr = array();

	function tx_cal_create_event_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a create event form.
	 *  @param      int         A date to create the event for. Format: yyyymmdd
	 *  @param      object      The cObject of the mother-class
	 *  @param      object      The rights object
	 *  @param		string		Comma separated list of pids.
	 *  @param      object      A phpicalendar object to be updated
	 *	@return		string		The HTML output.
	 */
	function drawCreateEvent($getdate, $pidList, $event=''){

		$this->initTemplate();
		$this->objectString = 'event';
		
		$page = $this->cObj->fileResource($this->conf['view.']['event.']['createEventTemplate']);
		if ($page=='') {
			return '<h3>calendar: no create event template file found:</h3>'.$this->conf['view.']['event.']['createEventTemplate'];
		}
		
		if(is_object($object) && !$object->isUserAllowedToEdit()){
			return $this->controller->pi_getLL('l_not_allowed_edit').$this->objectString;
		}else if(!is_object($object) && !$this->rightsObj->isAllowedTo('create',$this->objectString,'')){
			return $this->controller->pi_getLL('l_not_allowed_create').$this->objectString;
		}
		
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
	
		$this->validation = '';
		$sims = array();
		$rems = array();
		
		$this->dateFormatArray = array();
		$this->dateFormatArray[$this->conf['view.']['event.']['dateDayPosition']] = 'dd';
		$this->dateFormatArray[$this->conf['view.']['event.']['dateMonthPosition']] = 'mm';
		$this->dateFormatArray[$this->conf['view.']['event.']['dateYearPosition']] = 'yyyy';
		
		
		$sims['###UID###'] = '';
		$sims['###TYPE###'] = $this->eventType;
		$sims['###L_EDIT_EVENT###'] = $this->controller->pi_getLL('l_create_event');
		

		// If an event has been passed on the form is a edit form
		if(is_object($event) && $event->isUserAllowedToEdit($this->rightsObj->getUserId())){
			$this->isEditMode = true;
			$this->object = $event;
			$this->prepareUserArray();
			$sims['###UID###'] = $this->object->getUid();
			$sims['###TYPE###'] = $this->object->getType();
			$sims['###L_EDIT_EVENT###'] = $this->controller->pi_getLL('l_edit_event');
		}
		
		$this->getTemplateSubpartMarker($page, $rems, $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
                
		$sims = array();
		$rems = array();
		
		$this->getTemplateSingleMarker($page, $rems, $sims);
		$this->addAdditionalMarker($page, $rems, $sims);		
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		
		$sims = array();
		$sims['###START_DAY_SELECTOR###'] = '';
		$sims['###END_DAY_SELECTOR###'] = ''; 
		$sims['###UNTIL_SELECTOR###'] = '';
		if($this->useDateSelector){
			$sims['###START_DAY_SELECTOR###'] = $this->useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_start_day',$this->dateSelectorConf) : '';
			$sims['###END_DAY_SELECTOR###'] = $this->useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_end_day',$this->dateSelectorConf) : '';
			$sims['###UNTIL_SELECTOR###'] = $this->useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_until',$this->dateSelectorConf) : '';
		}
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function initTemplate(){
		$this->categoryService = &$this->modelObj->getServiceObjByKey('cal_category_model', 'category', 'tx_cal_category');
		$this->calendarService = &$this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');

		if (t3lib_extMgm::isLoaded('rlmp_dateselectlib')){
				require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');
				tx_rlmpdateselectlib::includeLib();
				
				/* Only read date selector option if rlmp_dateselectlib is installed */
				$this->useDateSelector = $this->conf['view.']['event.']['useDateSelector'];
		}
		
		$dateFormatArray = array();
		$dateFormatArray[$this->conf['view.']['event.']['dateDayPosition']] = '%d';
		$dateFormatArray[$this->conf['view.']['event.']['dateMonthPosition']] = '%m';
		$dateFormatArray[$this->conf['view.']['event.']['dateYearPosition']] = '%Y';
		$dateFormatString = $dateFormatArray[0].$this->conf['view.']['event.']['dateSplitSymbol'].$dateFormatArray[1].$this->conf['view.']['event.']['dateSplitSymbol'].$dateFormatArray[2];

		$this->dateSelectorConf = array('calConf.' => array (
                           'dateTimeFormat' => $dateFormatString,
                           'inputFieldDateTimeFormat' => $dateFormatString,
                           'toolTipDateTimeFormat' => $dateFormatString,
                           //'showMethod' => 'absolute',
                           //'showPositionAbsolute' => '100,150',
                           //'stylesheet' => 'fileadmin/mystyle.css'
              )
    	);
	}
	
	function getCalendarMarker(& $template, & $rems, & $sims){

		$sims['###CALENDAR###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventCalendar()){
			$id = $this->object->getCalendarUid();
			if($this->conf['switch_calendar']){
				$id = $this->conf['switch_calendar'];
			}
			$calendarIds = $this->calendarService->getIdsFromTable('',$this->conf['pidList'],true,true);
			if (empty($calendarIds)) {
				return '<h3>You have to create a calendar before you can create events</h3>';
			}
			foreach($calendarIds as $calendarRow){
				if($calendarRow['uid']==$id){
					$calendar .= '<option value="'.$calendarRow['uid'].'" selected="selected">'.$calendarRow['title'].'</option>';
				}else{
					$calendar .= '<option value="'.$calendarRow['uid'].'">'.$calendarRow['title'].'</option>';
				}
			}
			
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendar, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);
			
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventCalendar()){
			// JH: check vs conf list and conf default value(s) of Categories
			$calendarIds = $this->calendarService->getIdsFromTable('',$this->conf['pidList'], true,true);
			if (empty($calendarIds)) {
				return '<h3>You have to create a calendar before you can create events</h3>';
			}
			
			if(!$this->conf['switch_calendar']){
				$sims['###CALENDAR_ID###'] = $calendarIds[0]['uid'];
				if(count($calendarIds)>1){
					$calendar .= '<option>'.$this->controller->pi_getLL('l_select').'</option>';
				}
			}

			if(count($calendarIds)==1){
				$this->conf['switch_calendar']=$calendarIds[0]['uid'];
			}

			foreach($calendarIds as $calendarRow){
				if($this->conf['switch_calendar']==$calendarRow['uid']){
					$calendar .= '<option value="'.$calendarRow['uid'].'" selected="selected">'.$calendarRow['title'].'</option>';
				}else{
					$calendar .= '<option value="'.$calendarRow['uid'].'">'.$calendarRow['title'].'</option>';
				}
			}
			
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendar, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);		
		}
	}
	
	
	function getCategoryMarker(& $template, & $rems, & $sims){
		$sims['###CATEGORY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventCategory()){
			
			$tempCalendarConf = $this->conf['calendar'];
			$tempCategoryConf = $this->conf['category'];
			$this->conf['calendar'] = $this->conf['switch_calendar']?$this->conf['switch_calendar']:$this->object->getCalendarUid();
			$this->conf['calendar'] .= ',0';
			$ids = array();
			if(!$this->conf['switch_calendar'] && count($this->object->getCategories())==0){
				$this->conf['category'] = 'a';
			}else{
				foreach($this->object->getCategories() as $cat){
					$ids[] = $cat->getUid();
				}
				$this->conf['category'] = implode(',',$ids);
			}
			
			$this->conf['view.']['edit_event.']['tree.']['calendar'] = $this->conf['calendar'];
			$this->conf['view.']['edit_event.']['tree.']['category'] = $this->conf['category'];
			
			$categoryArray = $this->modelObj->findAllCategories('','tx_cal_category',$this->conf['pidList']);

			$sims['###CATEGORY###'] = $this->cObj->stdWrap($this->getCategorySelectionTree($this->conf['view.']['edit_event.']['tree.'], array($categoryArray), true), $this->conf['view.'][$this->conf['view'].'.']['category_stdWrap.']);
			
			$this->conf['calendar'] = $tempCalendarConf;
			if(!$this->conf['switch_calendar'] && count($this->object->getCategories())==0){
				$this->conf['category'] = $tempCategoryConf;
			}
			
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventCategory()){
			
			$tempCalendarConf = $this->conf['calendar'];
			$tempCategoryConf = $this->conf['category'];
			$this->conf['calendar'] = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
			if($this->rightsObj->isAllowedToCreateEventCalendar()){
				$this->conf['calendar'] = $this->conf['switch_calendar'];
			}
			$this->conf['calendar'] .= ',0';
			$this->conf['category'] = 'a';

			if($this->conf['calendar']){
				$this->conf['view.']['create_event.']['tree.']['calendar'] = $this->conf['calendar'];
				$this->conf['view.']['create_event.']['tree.']['category'] = $this->conf['category'];

				$categoryArray = $this->modelObj->findAllCategories('','tx_cal_category',$this->conf['pidList']);

				$sims['###CATEGORY###'] = $this->cObj->stdWrap($this->getCategorySelectionTree($this->conf['view.']['create_event.']['tree.'], array($categoryArray), true), $this->conf['view.'][$this->conf['view'].'.']['category_stdWrap.']);
			}
			$this->conf['calendar'] = $tempCalendarConf;
			if(!$this->conf['category']=='a'){
				$this->conf['category'] = $tempCategoryConf;
			}
			
		}
	}
	
	function getAlldayMarker(& $template, & $rems, & $sims){
		$sims['###ALLDAY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventDateTime()){
			$sims['###ALLDAY###'] = $this->cObj->stdWrap($this->object->isAllday()?'checked="checked"':'', $this->conf['view.'][$this->conf['view'].'.']['allday_stdWrap.']);
		}else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventDateTime()){
			$sims['###ALLDAY###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['allday_stdWrap.']);
			
		}
	}
	
	function getStartdateMarker(& $template, & $rems, & $sims){
		$sims['###STARTDATE###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventDateTime()){

			$event_start_day = gmdate('Ymd',$this->object->getStartdate());
			$eventStartDay = array();
			
			ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $event_start_day, $eventStartDay);
			$startDayArray = array();
			$startDayArray[$this->conf['view.']['event.']['dateDayPosition']] = $eventStartDay[3];
			$startDayArray[$this->conf['view.']['event.']['dateMonthPosition']] = $eventStartDay[2];
			$startDayArray[$this->conf['view.']['event.']['dateYearPosition']] = $eventStartDay[1];
			
			$sims['###STARTDATE###'] = $this->cObj->stdWrap($startDayArray[0].$this->conf['view.']['event.']['dateSplitSymbol'].$startDayArray[1].$this->conf['view.']['event.']['dateSplitSymbol'].$startDayArray[2], $this->conf['view.'][$this->conf['view'].'.']['startdate_stdWrap.']);
		}else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventDateTime()){
			ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $eventStartDay);
			$startDayArray = array();
			$startDayArray[$this->conf['view.']['event.']['dateDayPosition']] = $eventStartDay[3];
			$startDayArray[$this->conf['view.']['event.']['dateMonthPosition']] = $eventStartDay[2];
			$startDayArray[$this->conf['view.']['event.']['dateYearPosition']] = $eventStartDay[1];
			
			$sims['###STARTDATE###'] = $this->cObj->stdWrap($startDayArray[0].$this->conf['view.']['event.']['dateSplitSymbol'].$startDayArray[1].$this->conf['view.']['event.']['dateSplitSymbol'].$startDayArray[2], $this->conf['view.'][$this->conf['view'].'.']['startdate_stdWrap.']);
			
		}
	}
	
	function getEnddateMarker(& $template, & $rems, & $sims){
		$sims['###ENDDATE###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventDateTime()){
			if($this->object->getEnddate()==0){
				$event_end_day = gmdate('Ymd',$this->object->getStartdate());
			}else{
				$event_end_day = gmdate('Ymd',$this->object->getEnddate());
			}
			$eventEndDay = array();
			
			ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $event_end_day, $eventEndDay);
			$endDayArray = array();
			$endDayArray[$this->conf['view.']['event.']['dateDayPosition']] = $eventEndDay[3];
			$endDayArray[$this->conf['view.']['event.']['dateMonthPosition']] = $eventEndDay[2];
			$endDayArray[$this->conf['view.']['event.']['dateYearPosition']] = $eventEndDay[1];
			
			$sims['###ENDDATE###'] = $this->cObj->stdWrap($endDayArray[0].$this->conf['view.']['event.']['dateSplitSymbol'].$endDayArray[1].$this->conf['view.']['event.']['dateSplitSymbol'].$endDayArray[2], $this->conf['view.'][$this->conf['view'].'.']['enddate_stdWrap.']);
		}else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventDateTime()){
			ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $eventEndDay);
			$endDayArray = array();
			$endDayArray[$this->conf['view.']['event.']['dateDayPosition']] = $eventEndDay[3];
			$endDayArray[$this->conf['view.']['event.']['dateMonthPosition']] = $eventEndDay[2];
			$endDayArray[$this->conf['view.']['event.']['dateYearPosition']] = $eventEndDay[1];
			
			$sims['###ENDDATE###'] = $this->cObj->stdWrap($endDayArray[0].$this->conf['view.']['event.']['dateSplitSymbol'].$endDayArray[1].$this->conf['view.']['event.']['dateSplitSymbol'].$endDayArray[2], $this->conf['view.'][$this->conf['view'].'.']['enddate_stdWrap.']);
			
		}
	}
	
	function getStarttimeMarker(& $template, & $rems, & $sims){
		$sims['###STARTTIME###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventDateTime()){
			$event_start_time = gmdate('Hi',$this->object->getStarttime());
			include_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');
			$start_hour = getHourFromTime(intval($this->controller->piVars['gettime']));
			$start_minute = getMinutesFromTime(intval($this->controller->piVars['gettime']));
			
			$start_time_minute = getMinutesFromTime($event_start_time);
			$start_time_hour = getHourFromTime($event_start_time);
			$start_hours = '';
			for ($i=0;$i<24;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$start_hours .= '<option value="'.$value.'"'.($start_time_hour==$value? ' selected="selected"' : '' ).'>'.$value.'</option>';
			}
			$start_minutes = '';
			for ($i=0;$i<60;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$start_minutes .= '<option value="'.$value.'"'.($start_time_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
			}
			$starthour = $this->cObj->stdWrap($start_hours, $this->conf['view.'][$this->conf['view'].'.']['starthour_stdWrap.']);
			$startminute = $this->cObj->stdWrap($start_minutes, $this->conf['view.'][$this->conf['view'].'.']['startminute_stdWrap.']);
			
			$sims['###STARTTIME###'] = $starthour.$startminute;
		}else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventDateTime()){
			include_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');
			
			$start_hour = getHourFromTime(intval($this->controller->piVars['gettime']));
			$start_minute = getMinutesFromTime(intval($this->controller->piVars['gettime']));
			
			$start_hours = '';
			for ($i=0;$i<24;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$start_hours .= '<option value="'.$value.'"'.($start_hour==$value? ' selected="selected"' : '' ).'>'.$value.'</option>';
			}
			$start_minutes = '';
			for ($i=0;$i<60;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$start_minutes .= '<option value="'.$value.'"'.($start_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
			}
			$starthour = $this->cObj->stdWrap($start_hours, $this->conf['view.'][$this->conf['view'].'.']['starthour_stdWrap.']);
			$startminute = $this->cObj->stdWrap($start_minutes, $this->conf['view.'][$this->conf['view'].'.']['startminute_stdWrap.']);
			
			$sims['###STARTTIME###'] = $starthour.$startminute;
		}
	}
	
	function getEndtimeMarker(& $template, & $rems, & $sims){
		$sims['###ENDTIME###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventDateTime()){
			$event_end_time = gmdate('Hi',$this->object->getEndtime());
			if($event_end_time==0){
				$event_end_time = gmdate('Hi',$this->object->getStarttime());
			}
			include_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');

			$end_time_minute = getMinutesFromTime($event_end_time);
			$end_time_hour = getHourFromTime($event_end_time);
			$end_hours = '';
			for ($i=0;$i<24;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$end_hours .= '<option value="'.$value.'"'.($end_time_hour==$value? ' selected="selected"' : '' ).'>'.$value.'</option>';
			}
			$end_minutes = '';
			for ($i=0;$i<60;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$end_minutes .= '<option value="'.$value.'"'.($end_time_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
			}
			$endhour = $this->cObj->stdWrap($end_hours, $this->conf['view.'][$this->conf['view'].'.']['endhour_stdWrap.']);
			$endminute = $this->cObj->stdWrap($end_minutes, $this->conf['view.'][$this->conf['view'].'.']['endminute_stdWrap.']);
			
			$sims['###ENDTIME###'] = $endhour.$endminute;
		}else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventDateTime()){
			include_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_functions.php');
			
			$start_hour = getHourFromTime(intval($this->controller->piVars['gettime']));
			$start_minute = getMinutesFromTime(intval($this->controller->piVars['gettime']));
			if($start_hour == '23') {
				$end_hour = '00';
			}
			$end_hour = $start_hour + 1;
			$end_minute = $start_minute;
			$end_hours = '';
			for ($i=0;$i<24;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$end_hours .= '<option value="'.$value.'"'.($end_hour==$value? ' selected="selected"' : '' ).'>'.$value.'</option>';
			}
			$end_minutes = '';
			for ($i=0;$i<60;$i++) {
				$value = str_pad($i, 2, '0', STR_PAD_LEFT);
				$end_minutes .= '<option value="'.$value.'"'.($end_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
			}
			$endhour = $this->cObj->stdWrap($end_hours, $this->conf['view.'][$this->conf['view'].'.']['endhour_stdWrap.']);
			$endminute = $this->cObj->stdWrap($end_minutes, $this->conf['view.'][$this->conf['view'].'.']['endminute_stdWrap.']);
			
			$sims['###ENDTIME###'] = $endhour.$endminute;
		}
	}
	
	function prepareUserArray(){
		if($this->isEditMode){
			// selection uids of available notify/monitor users & -groups
			$cal_notify_user = '';
			$this->cal_notify_user_ids = array();
			$where = ' AND tx_cal_event.uid='.$this->object->getUid().' AND fe_users.deleted = 0 AND fe_users.disable = 0'.$this->cObj->enableFields('tx_cal_event');
			//TODO add this when groups are allowed: AND tx_cal_fe_user_event_monitor_mm.tablenames="fe_users" 
			$orderBy = '';
			$groupBy = '';
			$limit = '';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('fe_users.*','tx_cal_event','tx_cal_fe_user_event_monitor_mm','fe_users',$where,$groupBy ,$orderBy,$limit);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$this->cal_notify_user_ids[] = $row['uid'];
			}

			$this->cal_notify_group_ids = array();
			$where = ' AND tx_cal_event.uid='.$this->object->getUid().' AND tx_cal_fe_user_event_monitor_mm.tablenames="fe_groups" '.$this->cObj->enableFields('tx_cal_event');
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('fe_groups.*','tx_cal_event','tx_cal_fe_user_event_monitor_mm','fe_groups',$where,$groupBy ,$orderBy,$limit);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				array_push($this->cal_notify_group_ids,$row['uid']);
			}
		}
	}
	
	function getOrganizerMarker(& $template, & $rems, & $sims){
		$sims['###ORGANIZER###'] = '';
		if(!$this->confArr['hideOrganizerTextfield'] && $this->isEditMode && $this->rightsObj->isAllowedToEditEventOrganizer()){
			$sims['###ORGANIZER###'] = $this->cObj->stdWrap($this->object->getOrganizer(), $this->conf['view.'][$this->conf['view'].'.']['organizer_stdWrap.']);

		} else if(!$this->confArr['hideOrganizerTextfield'] && !$this->isEditMode && $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$sims['###ORGANIZER###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['organizer_stdWrap.']);
		}
	}
	
	function getCalOrganizerMarker(& $template, & $rems, & $sims){
		$sims['###CAL_ORGANIZER###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventOrganizer()){
			$cal_organizer = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
			$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer');		
			$organizers = $this->modelObj->findAllOrganizer($useOrganizerStructure,$this->conf['pidList']);
			foreach($organizers as $organizer){
				if($this->object->getOrganizerId()==$organizer->getUid()){
					$cal_organizer .= '<option value="'.$organizer->getUid().'" selected="selected">'.$organizer->getName().'</option>';
				}else{
					$cal_organizer .= '<option value="'.$organizer->getUid().'">'.$organizer->getName().'</option>';
				}
			}
			$sims['###CAL_ORGANIZER_VALUE###'] = $this->object->getOrganizerId();
			$sims['###CAL_ORGANIZER###'] = $this->cObj->stdWrap($cal_organizer, $this->conf['view.'][$this->conf['view'].'.']['cal_organizer_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventOrganizer()){
			$uidList = array(explode(',',$this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateOrganizer.']['uidList']));
			$uidDefault = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateOrganizer.']['uidDefault'];
			// creating options for organizer
			$cal_organizer = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer');		
			$organizers = $this->modelObj->findAllOrganizer($useOrganizerStructure,$this->conf['pidList']);
			if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateOrganizer.']['uidList']) {
				if(!$this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateOrganizer.']['uidDefault']) {
					$cal_organizer = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
				}
				foreach($organizers as $organizer){
					if(in_array($organizer->getUid(),$uidList)){
						$cal_organizer .= '<option value="'.$organizer->getUid().'"';
						if($organizer->getUid() == $uidDefault) {
							$cal_organizer .= ' selected="selected"';
						}
						$cal_organizer .= '>'.$organizer->getName().'</option>';
					}
				}
			}
			// if no default values found
			else {
				// creating options for location by standard fe plugin entry point
				foreach($organizers as $organizer){
					$cal_organizer .= '<option value="'.$organizer->getUid().'"';
					if($organizer->getUid() == $uidDefault) {
						$cal_organizer .= ' selected="selected"';
					}
					$cal_organizer .= '>'.$organizer->getName().'</option>';
				}
			}
			$sims['###CAL_ORGANIZER###'] = $this->cObj->stdWrap($cal_organizer, $this->conf['view.'][$this->conf['view'].'.']['cal_organizer_stdWrap.']);
		}
	}
	
	function getLocationMarker(& $template, & $rems, & $sims){
		$sims['###LOCATION###'] = '';
		if(!$this->confArr['hideLocationTextfield'] && $this->isEditMode && $this->rightsObj->isAllowedToEditEventLocation()){
			$sims['###LOCATION###'] = $this->cObj->stdWrap($this->object->getLocation(), $this->conf['view.'][$this->conf['view'].'.']['location_stdWrap.']);

		} else if(!$this->confArr['hideLocationTextfield'] && !$this->isEditMode && $this->rightsObj->isAllowedToCreateEventLocation()){
			$sims['###LOCATION###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['location_stdWrap.']);
		}
	}
	
	function getCalLocationMarker(& $template, & $rems, & $sims){
		$sims['###CAL_LOCATION###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventLocation()){		
			$cal_location = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
			$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');		
			$locations = $this->modelObj->findAllLocations($useLocationStructure,$this->conf['pidList']);
			foreach($locations as $location){
				if($this->object->getLocationId()==$location->getUid()){
					$cal_location .= '<option value="'.$location->getUid().'" selected="selected">'.$location->getName().'</option>';
				}else{
					$cal_location .= '<option value="'.$location->getUid().'">'.$location->getName().'</option>';
				}
			}
			$sims['###CAL_LOCATION_VALUE###'] = $this->object->getLocationId();
			$sims['###CAL_LOCATION###'] = $this->cObj->stdWrap($cal_location, $this->conf['view.'][$this->conf['view'].'.']['cal_location_stdWrap.']);
		}else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventLocation()){

			$uidList = array(explode(',',$this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateLocation.']['uidList']));
			$uidDefault = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateLocation.']['uidDefault'];
			// creating options for location
			$cal_location = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');		
			$locations = $this->modelObj->findAllLocations($useLocationStructure,$this->conf['pidList']);
			if($this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateLocation.']['uidList']) {
				if(!$this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateLocation.']['uidDefault']) {
					$cal_location = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
				}
				foreach($locations as $location){
					if(in_array($location->getUid(),$uidList)){
						$cal_location .= '<option value="'.$location->getUid().'"';
						if($location->getUid() == $uidDefault) {
							$cal_location .= ' selected="selected"';
						}
						$cal_location .= '>'.$location->getName().'</option>';
					}
				}
			}
			// if no default values found
			else {
				// creating options for location by standard fe plugin entry point
				foreach($locations as $location){
					$cal_location .= '<option value="'.$location->getUid().'"';
					if($location->getUid() == $uidDefault) {
						$cal_location .= ' selected="selected"';
					}
					$cal_location .= '>'.$location->getName().'</option>';
				}
			}
			$sims['###CAL_LOCATION###'] = $this->cObj->stdWrap($cal_location, $this->conf['view.'][$this->conf['view'].'.']['cal_location_stdWrap.']);
		}
	}
	
	function getDescriptionMarker(& $template, & $rems, & $sims){
		$sims['###ADDITIONALJS_PRE###'] = '';
		$sims['###ADDITIONALJS_POST###'] = '';
		$sims['###ADDITIONALJS_SUBMIT###'] = '';
		$sims['###DESCRIPTION###'] = '';

		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventDescription()){
			$sims['###DESCRIPTION###'] = $this->cObj->stdWrap('<textarea name="tx_cal_controller[description]">'.$this->object->getDescription().'</textarea>', $this->conf['view.'][$this->conf['view'].'.']['description_stdWrap.']);
			
			/* Start setting the RTE markers */
			if (t3lib_extMgm::isLoaded('rtehtmlarea'))   require_once(t3lib_extMgm::extPath('rtehtmlarea').'pi2/class.tx_rtehtmlarea_pi2.php'); //RTE 
			if(!$this->RTEObj && t3lib_extMgm::isLoaded('rtehtmlarea'))  $this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
			if(is_object($this->RTEObj) && $this->RTEObj->isAvailable() && $this->conf['rights.']['edit.']['event.']['enableRTE']) {
				$this->RTEcounter++;
				$this->formName = 'tx_cal_controller';
				$this->strEntryField = 'description';
				$this->PA['itemFormElName'] = 'tx_cal_controller[description]';
				$this->PA['itemFormElValue'] = $this->object->getDescription();
				$this->thePidValue = $GLOBALS['TSFE']->id;
                if($this->conf['view.']['create_event.']['rte.']['width']>0 && $this->conf['view.']['create_event.']['rte.']['height']>0)
                    $this->RTEObj->RTEdivStyle = 'height:'.$this->conf['view.']['create_event.']['rte.']['height'].'px; width:'.$this->conf['view.']['create_event.']['rte.']['width'].'px;';     
                    
				$RTEItem = $this->RTEObj->drawRTE($this,'tx_cal_event',$this->strEntryField,$row=array(), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);
				$sims['###ADDITIONALJS_PRE###'] = $this->additionalJS_initial.'
					<script type="text/javascript">'. implode(chr(10), $this->additionalJS_pre).'
					</script>';
				$sims['###ADDITIONALJS_POST###'] = '
					<script type="text/javascript">'. implode(chr(10), $this->additionalJS_post).'
					</script>';
				$sims['###ADDITIONALJS_SUBMIT###'] = implode(';', $this->additionalJS_submit);
				$sims['###DESCRIPTION###'] = $this->cObj->stdWrap($RTEItem, $this->conf['view.'][$this->conf['view'].'.']['description_stdWrap.']);

			}
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventDescription()){
			$sims['###DESCRIPTION###'] = $this->cObj->stdWrap('<textarea name="tx_cal_controller[description]"></textarea>', $this->conf['view.'][$this->conf['view'].'.']['description_stdWrap.']);
            
            /* Start setting the RTE markers */
            if($this->conf['rights.']['create.']['event.']['enableRTE']) {
                if (t3lib_extMgm::isLoaded('rtehtmlarea'))   require_once(t3lib_extMgm::extPath('rtehtmlarea').'pi2/class.tx_rtehtmlarea_pi2.php'); //RTE    
                if(!$this->RTEObj && t3lib_extMgm::isLoaded('rtehtmlarea')) $this->RTEObj = t3lib_div::makeInstance('tx_rtehtmlarea_pi2');
                
            }
                
			
			if($this->conf['rights.']['create.']['event.']['enableRTE'] && t3lib_extMgm::isLoaded('rtehtmlarea') && $this->RTEObj->isAvailable() ) {
                $this->RTEcounter++;
				$this->formName = 'tx_cal_controller';
				$this->strEntryField = 'description';
				$this->PA['itemFormElName'] = 'tx_cal_controller[description]';
				$this->PA['itemFormElValue'] = '';
				$this->thePidValue = $GLOBALS['TSFE']->id;
				if($this->conf['view.']['create_event.']['rte.']['width']>0 && $this->conf['view.']['create_event.']['rte.']['height']>0)
                    $this->RTEObj->RTEdivStyle = 'height:'.$this->conf['view.']['create_event.']['rte.']['height'].'px; width:'.$this->conf['view.']['create_event.']['rte.']['width'].'px;';     
                
                $RTEItem = $this->RTEObj->drawRTE($this,'tx_cal_event',$this->strEntryField,$row=array(), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);
				$sims['###ADDITIONALJS_PRE###'] = $this->additionalJS_initial.'
					<script type="text/javascript">'. implode(chr(10), $this->additionalJS_pre).'
					</script>';
				$sims['###ADDITIONALJS_POST###'] = '
					<script type="text/javascript">'. implode(chr(10), $this->additionalJS_post).'
					</script>';
				$sims['###ADDITIONALJS_SUBMIT###'] = implode(';', $this->additionalJS_submit);
				$sims['###DESCRIPTION###'] = $this->cObj->stdWrap($RTEItem, $this->conf['view.'][$this->conf['view'].'.']['description_stdWrap.']);
			}
			/* End setting the RTE markers */
            	
		}
	}
	
	function getTeaserMarker(& $template, & $rems, & $sims){
		$sims['###TEASER###'] = '';
		
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if($confArr['useTeaser']) {
			if($this->isEditMode && $this->rightsObj->isAllowedToEditEventTeaser()){
				$sims['###TEASER###'] = $this->cObj->stdWrap('<textarea name="tx_cal_controller[teaser]">'.$this->object->getTeaser().'</textarea>', $this->conf['view.'][$this->conf['view'].'.']['teaser_stdWrap.']);
			} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventTeaser()){
				$sims['###TEASER###'] = $this->cObj->stdWrap('<textarea name="tx_cal_controller[teaser]"></textarea>', $this->conf['view.'][$this->conf['view'].'.']['teaser_stdWrap.']);
			}
		}
	}
	
	function getFrequencyMarker(& $template, & $rems, & $sims){
		$sims['###FREQUENCY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventRecurring()){
			$frequency_values = array('none', 'year', 'month', 'week', 'day');
			$frequency = '';
			foreach ($frequency_values as $freq) {
				$frequency .= '<option value="'.$freq.'"'.($freq==$this->object->getFreq()?' selected="selected"':'').'>'.$this->controller->pi_getLL('l_'.$freq).'</option>';
			}
			$sims['###FREQUENCY###'] = $this->cObj->stdWrap($frequency, $this->conf['view.'][$this->conf['view'].'.']['frequency_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$frequency_values = array('none', 'year', 'month', 'week', 'day');
			$frequency = '';
			foreach ($frequency_values as $freq) {
				$frequency .= '<option value="'.$freq.'">'.$this->controller->pi_getLL('l_'.$freq).'</option>';
			}
			$sims['###FREQUENCY###'] = $this->cObj->stdWrap($frequency, $this->conf['view.'][$this->conf['view'].'.']['frequency_stdWrap.']);
		}
	}
	
	function getByDayMarker(& $template, & $rems, & $sims){
		$sims['###BY_DAY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventRecurring()){
			$by_day = array('MO','TU','WE','TH','FR','SA','SU');
			$dayName = strtotime('next monday');
			$temp_sims = array();
			foreach ($by_day as $day) {
				if (in_array($day,$this->object->getByDay())){
					$temp_sims['###BY_DAY_CHECKED_'.$day.'###'] = 'checked />'.strftime('%a',$dayName);
				}
				else {
					$temp_sims['###BY_DAY_CHECKED_'.$day.'###'] = '/>'.strftime('%a',$dayName);
				}
				$dayName+=86400;
			}
			$sims['###BY_DAY###'] = $this->cObj->stdWrap(implode('###SPLITTER###',$temp_sims), $this->conf['view.'][$this->conf['view'].'.']['byDay_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$by_day = array('MON','TUE','WED','THU','FRI','SAT','SUN');
			$dayName = strtotime('next monday');
			$temp_sims = array();
			foreach ($by_day as $day) {
				$temp_sims['###BY_DAY_CHECKED_'.$day.'###'] = '/>'.strftime('%a',$dayName);
				$dayName+=86400;
			}
			$sims['###BY_DAY###'] = $this->cObj->stdWrap(implode('###SPLITTER###',$temp_sims), $this->conf['view.'][$this->conf['view'].'.']['byDay_stdWrap.']);
		}
	}
	
	function getByMonthDayMarker(& $template, & $rems, & $sims){
		$sims['###BY_MONTHDAY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventRecurring()){
			$sims['###BY_MONTHDAY###'] = $this->cObj->stdWrap(implode(',',$this->object->getByMonthDay()), $this->conf['view.'][$this->conf['view'].'.']['byMonthday_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$sims['###BY_MONTHDAY###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['byMonthday_stdWrap.']);
		}
	}
	
	function getByMonthMarker(& $template, & $rems, & $sims){
		$sims['###BY_MONTH###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventRecurring()){
			$sims['###BY_MONTH###'] = $this->cObj->stdWrap(implode(',',$this->object->getByMonth()), $this->conf['view.'][$this->conf['view'].'.']['byMonth_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$sims['###BY_MONTH###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['byMonth_stdWrap.']);
		}
	}
	
	function getUntilMarker(& $template, & $rems, & $sims){
		$sims['###UNTIL###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventRecurring()){
			$until = $this->object->getUntil();

			if($until>0){
				$untilArray = array();
				ereg ('([0-9]{4})([0-9]{2})([0-9]{2})', gmdate('Ymd',$until), $untilArray);
				$startDayArray = array();
				$startDayArray[$this->conf['view.']['event.']['dateDayPosition']] = $untilArray[3];
				$startDayArray[$this->conf['view.']['event.']['dateMonthPosition']] = $untilArray[2];
				$startDayArray[$this->conf['view.']['event.']['dateYearPosition']] = $untilArray[1];
				$until = $startDayArray[0].$this->conf['view.']['event.']['dateSplitSymbol'].$startDayArray[1].$this->conf['view.']['event.']['dateSplitSymbol'].$startDayArray[2];
			}else{
				$until = '';
			}
			$sims['###UNTIL###'] = $this->cObj->stdWrap($until, $this->conf['view.'][$this->conf['view'].'.']['until_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$sims['###UNTIL###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['until_stdWrap.']);
		}
	}
	
	function getCountMarker(& $template, & $rems, & $sims){
		$sims['###COUNT###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventRecurring()){
			$sims['###COUNT###'] = $this->cObj->stdWrap($this->object->getCount(), $this->conf['view.'][$this->conf['view'].'.']['count_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$sims['###COUNT###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['count_stdWrap.']);
		}
	}
	
	function getIntervalMarker(& $template, & $rems, & $sims){
		$sims['###INTERVAL###'] = '';
		if(($this->editMode && $this->rightsObj->isAllowedToEditEventRecurring())){
			$sims['###INTERVAL###'] = $this->cObj->stdWrap($this->object->getInterval(), $this->conf['view.'][$this->conf['view'].'.']['interval_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventRecurring()){
			$sims['###INTERVAL###'] = $this->cObj->stdWrap('', $this->conf['view.'][$this->conf['view'].'.']['interval_stdWrap.']);
		}
	}
	
	function getNotifyMarker(& $template, & $rems, & $sims){
		$sims['###NOTIFY###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventNotify()){
				
			if($this->conf['rights.']['allowedUsers']!=''){
				// creating options for exceptions and monitoring - users
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')');
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					if(array_search($row['uid'],$this->cal_notify_user_ids)!==false){
						$cal_notify_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'" checked="checked" name="tx_cal_controller[notify][]" />'.$row['username'].'<br />';
					}else{
						$cal_notify_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[notify][]"/>'.$row['username'].'<br />';
					}
				}
			}
			$sims['###NOTIFY###'] = $this->cObj->stdWrap($cal_notify_user, $this->conf['view.'][$this->conf['view'].'.']['notify_stdWrap.']);
		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventNotify() && ($this->conf['rights.']['allowedUsers']!='' || $this->conf['rights.']['allowedGroups']!='')){
			if($this->conf['rights.']['allowedUsers']!=''){
				// creating options for exceptions and monitoring - users
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','fe_users','pid in ('.$this->conf['pidList'].')');
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$cal_notify_user .= '<input type="checkbox" value="u_'.$row['uid'].'_'.$row['username'].'"  name="tx_cal_controller[notify][]"/>'.$row['username'].'<br />';
				}
			}
			$sims['###NOTIFY###'] = $this->cObj->stdWrap($cal_notify_user, $this->conf['view.'][$this->conf['view'].'.']['notify_stdWrap.']);
		}
	}
	
	function getExceptionMarker(& $template, & $rems, & $sims){
		$sims['###EXCEPTION###'] = '';
		if($this->isEditMode && $this->rightsObj->isAllowedToEditEventException()){
			// creating options for exception events & -groups
			$exception = '';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_exception_event','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('tx_cal_exception_event'));
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(is_array($this->object->getExceptionSingleIds()) && array_search($row['uid'], $this->object->getExceptionSingleIds())!==false){
					$exception .= '<input type="checkbox" value="s_'.$row['uid'].'_'.$row['title'].'" checked="checked" name="tx_cal_controller[exception_ids][]"/>'.$row['title'].'<br />';
				}else{
					$exception .= '<input type="checkbox" value="s_'.$row['uid'].'_'.$row['title'].'" name="tx_cal_controller[exception_ids][]" />'.$row['title'].'<br />';
				}
			}			
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_exception_event_group','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('tx_cal_exception_event_group'));
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if(is_array($this->object->getExceptionGroupIds()) && array_search($row['uid'], $this->object->getExceptionGroupIds())!==false){
					$exception .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" checked="checked" name="tx_cal_controller[exception_ids][]" />'.$row['title'].'<br />';
				}else{
					$exception .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" name="tx_cal_controller[exception_ids][]" />'.$row['title'].'<br />';
				}
			}
			$sims['###EXCEPTION###'] = $this->cObj->stdWrap($exception, $this->conf['view.'][$this->conf['view'].'.']['exception_stdWrap.']);

		} else if(!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventException()){
			// creating options for exception events & -groups
			$exception = '';
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_exception_event','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('tx_cal_exception_event'));
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$exception .= '<input type="checkbox" value="s_'.$row['uid'].'_'.$row['title'].'" name="tx_cal_controller[exception_ids][]" />'.$row['title'].'<br />';
			}			
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_exception_event_group','pid in ('.$this->conf['pidList'].')'.$this->cObj->enableFields('tx_cal_exception_event_group'));
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$exception .= '<input type="checkbox" value="g_'.$row['uid'].'_'.$row['title'].'" name="tx_cal_controller[exception_ids][]" />'.$row['title'].'<br />';
			}
			$sims['###EXCEPTION###'] = $this->cObj->stdWrap($exception, $this->conf['view.'][$this->conf['view'].'.']['exception_stdWrap.']);
		}
	}
	
	function getFormStartMarker(& $template, & $rems, & $sims){
		$temp = $this->cObj->getSubpart($template, '###FORM_START###');
		$temp_sims = array();

		$temp_sims['###L_WRONG_SPLIT_SYMBOL_MSG###'] = str_replace('###DATE_SPLIT_SYMBOL###',$this->conf['view.']['event.']['dateSplitSymbol'],$this->controller->pi_getLL('l_wrong_split_symbol_msg'));
		$temp_sims['###L_WRONG_DATE_MSG###'] = $this->controller->pi_getLL('l_wrong_date');
		$temp_sims['###L_WRONG_TIME_MSG###'] = $this->controller->pi_getLL('l_wrong_time');
		$temp_sims['###L_IS_IN_PAST_MSG###'] = $this->controller->pi_getLL('l_is_in_past');
		$rems['###FORM_START###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
		
	}
	
	function addAdditionalMarker(& $template, & $rems, & $sims){
		$sims['###DATE_SPLIT_SYMBOL###'] = $this->conf['view.']['event.']['dateSplitSymbol'];
		$sims['###DATE_DAY_POSITION###'] = $this->conf['view.']['event.']['dateDayPosition'];
		$sims['###DATE_MONTH_POSITION###'] = $this->conf['view.']['event.']['dateMonthPosition'];
		$sims['###DATE_YEAR_POSITION###'] = $this->conf['view.']['event.']['dateYearPosition'];
		$sims['###VALIDATION###'] = $this->validation;
		
		$sims['###GETDATE###'] = $this->conf['getdate'];
		$sims['###GETTIME###'] = $this->conf['gettime'];
		$sims['###THIS_VIEW###'] = 'create_event';
		$sims['###NEXT_VIEW###'] = 'confirm_event';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();

		$sims['###OPTION###'] = $this->conf['option'];
		if($this->isEditMode){
			if($this->conf['switch_calendar']){
				$sims['###CALENDAR_ID###'] = $this->conf['switch_calendar'];
			}else{
				$sims['###CALENDAR_ID###'] = $this->object->getCalendarUid();
			}
		}else{
			if(($this->isEditMode && !$this->rightsObj->isAllowedToEditEventCalendar()) || (!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventCalendar())){
				$sims['###CALENDAR_ID###'] = $this->conf['switch_calendar'];
			}else{
				$sims['###CALENDAR_ID###'] = $this->conf['rights.']['create.']['event.']['fields.']['allowedToCreateCalendar.']['uidDefault'];
			}
		}
		
		$linkParams = array();
		$linkParams['view'] = 'confirm_event';
		if(($this->isEditMode && !$this->rightsObj->isAllowedToEditEventCalendar()) || (!$this->isEditMode && $this->rightsObj->isAllowedToCreateEventCalendar())){
			$linkParams['lastview'] = $this->controller->extendLastView();
		}
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( $linkParams);
		
		$sims['###CHANGE_CALENDAR_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'edit_event'));
	}
}
	

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_event_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_create_event_view.php']);
}
?>
