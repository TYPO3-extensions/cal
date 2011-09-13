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
		$page = $this->cObj->fileResource($this->conf['view.']['event.']['deleteEventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no confirm event template file found:</h3>'.$this->conf['view.']['event.']['deleteEventTemplate'];
		}
		
		$this->event = $event;
		
		if(!$this->event->isUserAllowedToDelete()){
			return 'You are not allowed to delete this event!';
		}
	
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
				
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'remove_event';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###OPTION###'] = $this->conf['option'];
		$sims['###CALENDAR_ID###'] = intval($this->controller->piVars['calendar_id']);
		$sims['###L_CONFIRM_EVENT###'] = $this->controller->pi_getLL('l_confirm_event');
		$sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'save_event','category'=>null));
		$this->getTemplateSubpartMarker($page, $rems, $sims);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$this->getTemplateSingleMarker($page, $rems, $sims);
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
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
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
		if ($this->event->getHidden()) {
			$hidden = 'true';
		} else {
			$hidden = 'false';
		}
		$sims['###HIDDEN###'] = $this->cObj->stdWrap($hidden, $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
	}
	
	function getCalendarMarker(& $template, & $rems, & $sims){
		$sims['###CALENDAR###'] = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_calendar','uid = '.intval($this->event->getCalendarUid()).'');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($row['title'], $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);;
		}
	}
	
	function getCategoryMarker(& $template, & $rems, & $sims){
		$sims['###CATEGORY###'] = '';

		$categoryArray = $this->event->getCategories();
		if (!empty($categoryArray)) {
			$ids = array();
			$names = array();

			foreach ($categoryArray as $id=> $value) {
				$ids[] = $id;
				$names[] = $value->getTitle();
			}
			$sims['###CATEGORY###'] = $this->cObj->stdWrap(implode(', ',$names), $this->conf['view.'][$this->conf['view'].'.']['category_stdWrap.']);;
		}
	}
	
	function getAlldayMarker(& $template, & $rems, & $sims){
		$sims['###ALLDAY###'] = '';
		$allday = 'false';
		if ($this->event->isAllday()) {
			$allday = 'true';
		}
		$sims['###ALLDAY###'] = $this->cObj->stdWrap($allday, $this->conf['view.'][$this->conf['view'].'.']['allday_stdWrap.']);
	}
	
	function getStartdateMarker(& $template, & $rems, & $sims){
		$sims['###STARTDATE###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$this->conf['view'].'.']['eventDateFormat'], $this->event->getStartDate()), $this->conf['view.'][$this->conf['view'].'.']['startdate_stdWrap.']);
	}
	
	function getEnddateMarker(& $template, & $rems, & $sims){
		$sims['###ENDDATE###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$this->conf['view'].'.']['eventDateFormat'], $this->event->getEndDate()), $this->conf['view.'][$this->conf['view'].'.']['enddate_stdWrap.']);
	}
	
	function getStarttimeMarker(& $template, & $rems, & $sims){
		$sims['###STARTTIME###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$this->conf['view'].'.']['eventTimeFormat'], $this->event->getStartHour()), $this->conf['view.'][$this->conf['view'].'.']['starttime_stdWrap.']);
	}
	
	function getEndtimeMarker(& $template, & $rems, & $sims){
		$sims['###ENDTIME###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$this->conf['view'].'.']['eventTimeFormat'], $this->event->getEndHour()), $this->conf['view.'][$this->conf['view'].'.']['endtime_stdWrap.']);
	}
	
	function getTitleMarker(& $template, & $rems, & $sims){
		$sims['###TITLE###'] = $this->cObj->stdWrap($this->event->getTitle(), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);
	}
	
	function getOrganizerMarker(& $template, & $rems, & $sims){
		$sims['###ORGANIZER###'] = $this->cObj->stdWrap($this->event->row['organizer'], $this->conf['view.'][$this->conf['view'].'.']['organizer_stdWrap.']);
	}
	
	function getCalOrganizerMarker(& $template, & $rems, & $sims){	
		$sims['###CAL_ORGANIZER###'] = '';
		if ($this->event->getOrganizerId()) {
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer');
			$organizer = $this->modelObj->findOrganizer($this->event->getOrganizerId(),$useOrganizerStructure);
			$sims['###CAL_ORGANIZER_VALUE###'] = $organizer->getUid();
			$sims['###CAL_ORGANIZER###'] = $this->cObj->stdWrap($organizer->getName(), $this->conf['view.'][$this->conf['view'].'.']['cal_organizer_stdWrap.']);;
		}
	}
	
	function getLocationMarker(& $template, & $rems, & $sims){
		$sims['###LOCATION###'] = $this->cObj->stdWrap($this->event->row['location'], $this->conf['view.'][$this->conf['view'].'.']['location_stdWrap.']);
	}
	
	function getCalLocationMarker(& $template, & $rems, & $sims){	
		$sims['###CAL_LOCATION###'] = '';
		if ($this->event->getLocationId()) {
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');
			$location = $this->modelObj->findLocation($this->event->getLocationId(),$useLocationStructure);
			$sims['###CAL_LOCATION_VALUE###'] = $location->getUid();
			$sims['###CAL_LOCATION###'] = $this->cObj->stdWrap($location->getName(), $this->conf['view.'][$this->conf['view'].'.']['cal_location_stdWrap.']);
		}
	}
	
	function getDescriptionMarker(& $template, & $rems, & $sims){
		$this->event->getDescriptionMarker($template, $rems, $sims, false, $this->conf['view']);
	}
	
	function getTeaserMarker(& $template, & $rems, & $sims){
		$this->event->getTeaserMarker($template, $rems, $sims, $this->conf['view']);
	}
	
	function getFrequencyMarker(& $template, & $rems, & $sims){
		$sims['###FREQUENCY###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_'.$this->event->getFreq()), $this->conf['view.'][$this->conf['view'].'.']['frequency_stdWrap.']);
	}
	
	function getByDayMarker(& $template, & $rems, & $sims){
		$by_day = array('MO','TU','WE','TH','FR','SA','SU');
		$dayName = strtotime('next monday');
		$temp_sims = array();
		foreach ($this->event->getByDay() as $day) {

			if (in_array($day,$by_day)){
				$temp_sims[] = strftime('%a',$dayName);
			}
			$dayName+=86400;
		}

		$sims['###BY_DAY###'] = $this->cObj->stdWrap(implode(',',$temp_sims), $this->conf['view.'][$this->conf['view'].'.']['byDay_stdWrap.']);
	}
	
	function getByMonthDayMarker(& $template, & $rems, & $sims){
		$sims['###BY_MONTHDAY###'] = $this->cObj->stdWrap(implode(',',$this->event->getByMonthDay()), $this->conf['view.'][$this->conf['view'].'.']['byMonthday_stdWrap.']);
	}
	
	function getByMonthMarker(& $template, & $rems, & $sims){
		$sims['###BY_MONTH###'] = $this->cObj->stdWrap(implode(',',$this->event->getByMonth()), $this->conf['view.'][$this->conf['view'].'.']['byMonth_stdWrap.']);
	}
	
	function getUntilMarker(& $template, & $rems, & $sims){
		$sims['###UNTIL###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$this->conf['view'].'.']['eventDateFormat'], $this->event->getUntil()), $this->conf['view.'][$this->conf['view'].'.']['until_stdWrap.']);
	}
	
	function getCountMarker(& $template, & $rems, & $sims){
		$sims['###COUNT###'] = $this->cObj->stdWrap($this->event->getCount(), $this->conf['view.'][$this->conf['view'].'.']['count_stdWrap.']);
	}
	
	function getIntervalMarker(& $template, & $rems, & $sims){
		$sims['###INTERVAL###'] = $this->cObj->stdWrap($this->event->getInterval(), $this->conf['view.'][$this->conf['view'].'.']['interval_stdWrap.']);
	}
	
	function getNotifyMarker(& $template, & $rems, & $sims){
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('username','fe_users','pid in ('.$this->conf['pidList'].')');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$cal_notify_user[] = $row['username'];
		}
		$sims['###NOTIFY###'] = $this->cObj->stdWrap(implode(',',(array) $cal_notify_user), $this->conf['view.'][$this->conf['view'].'.']['notify_stdWrap.']);
	}
	
	function getExceptionMarker(& $template, & $rems, & $sims){
		$exception = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_exception_event.title','tx_cal_exception_event,tx_cal_exception_event_mm','pid IN ('.$this->conf['pidList'].') AND tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event" AND tx_cal_exception_event_mm.uid_local ='.$this->event->getUid().$this->cObj->enableFields('tx_cal_exception_event'));
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$exception[] = $row['title'];
		}			
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_cal_exception_event_group.title','tx_cal_exception_event_group,tx_cal_exception_event_mm','pid in ('.$this->conf['pidList'].') AND tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event_group" AND tx_cal_exception_event_mm.uid_local ='.$this->event->getUid().$this->cObj->enableFields('tx_cal_exception_event_group'));
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$exception[] = $row['title'];
		}
		$sims['###EXCEPTION###'] = $this->cObj->stdWrap(implode(',',$exception), $this->conf['view.'][$this->conf['view'].'.']['exception_stdWrap.']);
	}
	
	function getCreateExceptionMarker(& $template, & $rems, & $sims){
		if($this->event->isClone() && $this->rightsObj->isAllowedToCreateExceptionEvents()){
			$local_sims['###ACTION_EXCEPTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_exception_event','type'=>null,'uid'=>null));
			$local_sims['###L_CREATE_EXCEPTION###'] = $this->controller->pi_getLL('l_create_exception');
			$local_sims['###L_TITLE###'] = $this->controller->pi_getLL('l_event_title');
			$local_sims['###EVENT_START_DAY###'] = gmdate('Ymd',$this->event->getStartDate());
			$local_sims['###EVENT_END_DAY###'] = gmdate('Ymd',$this->event->getEndDate());
			$local_sims['###EVENT_START_TIME###'] = gmdate('Hi',$this->event->getStartHour());
			$local_sims['###EVENT_END_TIME###'] = gmdate('Hi',$this->event->getEndHour());
			$local_sims['###EVENT_UID###'] = $this->event->getUid();
			$rems['###CREATE_EXCEPTION###'] = $this->cObj->getSubpart($template, '###CREATE_EXCEPTION###');
			$rems['###CREATE_EXCEPTION###'] = $this->cObj->substituteMarkerArrayCached($rems['###CREATE_EXCEPTION###'], $local_sims, array(), array ());
		}else{
			$rems['###CREATE_EXCEPTION###'] = '';
		}
	}
	
	function getFormEndMarker(& $template, & $rems, & $sims){	
		$temp = $this->cObj->getSubpart($template, '###FORM_END###');
		$temp_sims = array();
		$temp_sims['###BACK_LINK###'] = $this->controller->pi_linkTP_keepPIvars_url( $this->controller->shortenLastViewAndGetTargetViewParameters());
		$temp_sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$temp_sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$rems['###FORM_END###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_event_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_event_view.php']);
}
?>
