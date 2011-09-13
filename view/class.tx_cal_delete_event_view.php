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


require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_fe_editing_base_view.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_delete_event_view extends tx_cal_fe_editing_base_view {
	
	var $event;
	
	function tx_cal_delete_event_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a delete event form.
	 *  @param		object		The event to be deleted
	 *  @param      object      The cObject of the mother-class
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteEvent(&$event, $pidList ) {
		$modelObj = $this->modelObj;
		unset($this->controller->piVars['category']);
		$page = $this->cObj->fileResource($this->conf['view.']['delete_event.']['template']);
		if ($page == '') {
			return '<h3>calendar: no confirm event template file found:</h3>'.$this->conf['view.']['delete_event.']['template'];
		}
		
		$this->object = $event;
		
		if(!$this->object->isUserAllowedToDelete()){
			return 'You are not allowed to delete this event!';
		}
	
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				
		$rems = array();
		$sims = array();
		$wrapped = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'remove_event';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###OPTION###'] = $this->conf['option'];
		$sims['###L_DELETE_EVENT###'] = $this->controller->pi_getLL('l_delete_event');
		$sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'remove_event','category'=>null));
		$this->getTemplateSubpartMarker($page, $sims, $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$wrapped = array();
		$this->getTemplateSingleMarker($page, $sims, $rems, $wrapped);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, $wrapped);;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function getCalendarIdMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###CALENDAR_ID###'] = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_calendar','uid = '.intval($this->object->getCalendarUid()).'');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$sims['###CALENDAR_ID###'] = $this->cObj->stdWrap($row['title'], $this->conf['view.'][$this->conf['view'].'.']['calendar_id_stdWrap.']);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
	}
	
	function getCategoryMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###CATEGORY###'] = '';

		$categoryArray = $this->object->getCategories();
		if (!empty($categoryArray)) {
			$ids = array();
			$names = array();

			foreach ($categoryArray as $id=> $value) {
				$ids[] = $id;
				$names[] = $value->getTitle();
			}
			$sims['###CATEGORY###'] = $this->cObj->stdWrap(implode(', ',$names), $this->conf['view.'][$this->conf['view'].'.']['category_stdWrap.']);
		}
	}
	
	function getAlldayMarker(& $template, & $sims, & $rems, & $wrapped){
		$label = $this->controller->pi_getLL('l_false');
		if ($this->object->isAllDay() == '1') {
			$label = $this->controller->pi_getLL('l_true');
		}
		$sims['###ALLDAY###'] = $this->cObj->stdWrap($label, $this->conf['view.'][$this->conf['view'].'.']['allday_stdWrap.']);
	}
	
	function getStartdateMarker(& $template, & $sims, & $rems, & $wrapped){
		$startDate = $this->object->getStart();
		$split = $this->conf['dateConfig.']['splitSymbol'];
		$startDateFormatted = $startDate->format('%Y'.$split.'%m'.$split.'%d');
		$sims['###STARTDATE###'] = $this->cObj->stdWrap($startDateFormatted, $this->conf['view.'][$this->conf['view'].'.']['startdate_stdWrap.']);
	}
	
	function getEnddateMarker(& $template, & $sims, & $rems, & $wrapped){
		$endDate = $this->object->getEnd();
		$split = $this->conf['dateConfig.']['splitSymbol'];
		$endDateFormatted = $endDate->format('%Y'.$split.'%m'.$split.'%d');			
		$sims['###ENDDATE###'] = $this->cObj->stdWrap($endDateFormatted, $this->conf['view.'][$this->conf['view'].'.']['enddate_stdWrap.']);
	}
	
	function getStarttimeMarker(& $template, & $sims, & $rems, & $wrapped){
		$startDate = $this->object->getStart();
		$sims['###STARTTIME###'] = $this->cObj->stdWrap($startDate->format($this->conf['view.']['event.']['event.']['timeFormat']), $this->conf['view.'][$this->conf['view'].'.']['starttime_stdWrap.']);
	}
	
	function getEndtimeMarker(& $template, & $sims, & $rems, & $wrapped){
		$endDate = $this->object->getEnd();
		$sims['###ENDTIME###'] = $this->cObj->stdWrap($endDate->format($this->conf['view.']['event.']['event.']['timeFormat']), $this->conf['view.'][$this->conf['view'].'.']['endtime_stdWrap.']);
	}
	
	function getTitleMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###TITLE###'] = $this->cObj->stdWrap($this->object->getTitle(), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);
	}
	
	function getOrganizerMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###ORGANIZER###'] = '';
		if(!$this->confArr['hideOrganizerTextfield'] && $organizer = $this->object->getOrganizer()){
			$sims['###ORGANIZER###'] = $this->cObj->stdWrap($organizer, $this->conf['view.'][$this->conf['view'].'.']['organizer_stdWrap.']);
		}
	}
	
	function getCalOrganizerMarker(& $template, & $sims, & $rems, & $wrapped){	
		$sims['###CAL_ORGANIZER###'] = '';
		if($organizer = $this->object->getOrganizerObject()){
			$sims['###CAL_ORGANIZER###'] = $this->cObj->stdWrap($organizer->getName(), $this->conf['view.'][$this->conf['view'].'.']['cal_organizer_stdWrap.']);
		}
	}
	
	function getLocationMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###LOCATION###'] = '';
		if(!$this->confArr['hideLocationTextfield'] && $location = $this->object->getLocation()){
			$sims['###LOCATION###'] = $this->cObj->stdWrap($location, $this->conf['view.'][$this->conf['view'].'.']['location_stdWrap.']);
		}
	}
	
	function getCalLocationMarker(& $template, & $sims, & $rems, & $wrapped){	
		$sims['###CAL_LOCATION###'] = '';
		if($location = $this->object->getLocationObject()){
			$sims['###CAL_LOCATION###'] = $this->cObj->stdWrap($location->getName(), $this->conf['view.'][$this->conf['view'].'.']['cal_location_stdWrap.']);
		}
	}
	
	function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped){
		$this->object->getDescriptionMarker($template, $sims, $rems, $wrapped, $this->conf['view']);
	}
	
	function getTeaserMarker(& $template, & $sims, & $rems, & $wrapped){
		$this->object->getTeaserMarker($template, $sims, $rems, $wrapped, $this->conf['view']);
	}
	
	function getFrequencyMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###FREQUENCY###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_'.$this->object->getFreq()), $this->conf['view.'][$this->conf['view'].'.']['frequency_stdWrap.']);
	}
	
	function getByDayMarker(& $template, & $sims, & $rems, & $wrapped){
		$by_day = array('MO','TU','WE','TH','FR','SA','SU');
		$dayName = strtotime('next monday');
		$temp_sims = array();
		foreach ($this->object->getByDay() as $day) {

			if (in_array($day,$by_day)){
				$temp_sims[] = strftime('%a',$dayName);
			}
			$dayName+=86400;
		}

		$sims['###BY_DAY###'] = $this->cObj->stdWrap(implode(',',$temp_sims), $this->conf['view.'][$this->conf['view'].'.']['byDay_stdWrap.']);
	}
	
	function getByMonthDayMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###BY_MONTHDAY###'] = $this->cObj->stdWrap(implode(',',$this->object->getByMonthDay()), $this->conf['view.'][$this->conf['view'].'.']['byMonthday_stdWrap.']);
	}
	
	function getByMonthMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###BY_MONTH###'] = $this->cObj->stdWrap(implode(',',$this->object->getByMonth()), $this->conf['view.'][$this->conf['view'].'.']['byMonth_stdWrap.']);
	}
	
	function getUntilMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###UNTIL###'] = '';
		$untilDate = $this->object->getUntil();
		if(is_object($untilDate)) {
			$split = $this->conf['dateConfig.']['splitSymbol'];
			$untilDateFormatted = $untilDate->format('%Y'.$split.'%m'.$split.'%d');
			$sims['###UNTIL###'] = $this->cObj->stdWrap($untilDateFormatted, $this->conf['view.'][$this->conf['view'].'.']['until_stdWrap.']);
		}
	}
	
	function getCountMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###COUNT###'] = $this->cObj->stdWrap($this->object->getCount(), $this->conf['view.'][$this->conf['view'].'.']['count_stdWrap.']);
	}
	
	function getIntervalMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###INTERVAL###'] = $this->cObj->stdWrap($this->object->getInterval(), $this->conf['view.'][$this->conf['view'].'.']['interval_stdWrap.']);
	}
	
	function getNotifyMarker(& $template, & $sims, & $rems, & $wrapped){
		$sims['###NOTIFY###'] = '';
		$cal_notify_user = Array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('fe_users.username','fe_users, tx_cal_fe_user_event_monitor_mm','pid in ('.$this->conf['pidList'].') AND fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND tx_cal_fe_user_event_monitor_mm.uid_local = '.$this->object->getUid());
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$cal_notify_user[] = $row['username'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		if(!empty($cal_notify_user)){
			$sims['###NOTIFY###'] = $this->cObj->stdWrap(implode(',',(array) $cal_notify_user), $this->conf['view.'][$this->conf['view'].'.']['notify_stdWrap.']);
		}
	}
	
	function getExceptionMarker(& $template, & $sims, & $rems, & $wrapped){
		$exception = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_exception_event.title','tx_cal_exception_event,tx_cal_exception_event_mm','pid IN ('.$this->conf['pidList'].') AND tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event" AND tx_cal_exception_event_mm.uid_local ='.$this->object->getUid().$this->cObj->enableFields('tx_cal_exception_event'));
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$exception[] = $row['title'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
					
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_exception_event_group.title','tx_cal_exception_event_group,tx_cal_exception_event_mm','pid in ('.$this->conf['pidList'].') AND tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event_group" AND tx_cal_exception_event_mm.uid_local ='.$this->object->getUid().$this->cObj->enableFields('tx_cal_exception_event_group'));
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$exception[] = $row['title'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		$sims['###EXCEPTION###'] = $this->cObj->stdWrap(implode(',',$exception), $this->conf['view.'][$this->conf['view'].'.']['exception_stdWrap.']);
	}
	
	function getCreateExceptionMarker(& $template, & $sims, & $rems, & $wrapped){
		if($this->object->isClone() && $this->rightsObj->isAllowedToCreateExceptionEvent()){
			$local_sims['###ACTION_EXCEPTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_exception_event','type'=>null,'uid'=>null));
			$local_sims['###L_CREATE_EXCEPTION###'] = $this->controller->pi_getLL('l_create_exception');
			$local_sims['###L_TITLE###'] = $this->controller->pi_getLL('l_event_title');
			$eventStart = $this->object->getStart();
			$eventEnd = $this->object->getEnd();
			$local_sims['###EVENT_START_DAY###'] = $eventStart->format('%Y%m%d');
			$local_sims['###EVENT_END_DAY###'] = $eventEnd->format('%Y%m%d');
			$local_sims['###EVENT_START_TIME###'] = $eventStart->format('%H%M');
			$local_sims['###EVENT_END_TIME###'] =$eventEnd->format('%H%M');
			$local_sims['###EVENT_UID###'] = $this->object->getUid();
			$rems['###CREATE_EXCEPTION###'] = $this->cObj->getSubpart($template, '###CREATE_EXCEPTION###');
			$rems['###CREATE_EXCEPTION###'] = $this->cObj->substituteMarkerArrayCached($rems['###CREATE_EXCEPTION###'], $local_sims, array(), array ());
		}else{
			$rems['###CREATE_EXCEPTION###'] = '';
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_event_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_event_view.php']);
}
?>
