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

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_delete_event_view extends tx_cal_base_view {

	/**
	 *  Draws a delete event form.
	 *  @param		object		The event to be deleted
	 *  @param      object      The cObject of the mother-class
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteEvent(&$event, $pidList ) {

		$page = $this->cObj->fileResource($this->conf['view.']['event.']['deleteEventTemplate']);
		if ($page=='') {
			return '<h3>calendar: no delete event template file found:</h3>'.$this->conf['view.']['event.']['deleteEventTemplate'];
		}
		
		// creating dropdown options for frequency
		$frequency = '';
		switch ($event->getFreq()){
			case 'none':
				$frequency = $this->controller->pi_getLL('l_none');
				break;
			case 'year':
				$frequency = $this->controller->pi_getLL('l_format_recur_lang_yearly_single');
				break;
			case 'month':
				$frequency = $this->controller->pi_getLL('l_format_recur_lang_monthly_single');
				break;
			case 'week':
				$frequency = $this->controller->pi_getLL('l_format_recur_lang_weekly_single');
				break;
			case 'day':
				$frequency = $this->controller->pi_getLL('l_format_recur_lang_daily_single');
				break;
		}
		
		$calendar = $event->getCalendarUid();
		
		// creating options for category
		$category = $event->getCategoriesAsString(false);
		
	
		// creating options for location
		$cal_location = '';
		if($event->getLocationId()){
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location');		
			$location = $this->modelObj->findLocation($event->getLocationId(),$useLocationStructure);
			$cal_location = $location->getName();
		}
		$location = $event->getLocation();
		
		// creating options for organizer
		$cal_organizer = '';
		if($event->getOrganizerId()){
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer');		
			$organizer = $this->modelObj->findOrganizer($event->getOrganizerId(),$useOrganizerStructure);
			$cal_organizer = $organizer->getName();
		}
		$organizer = $event->getOrganizer();
		
		// creating options for exception events & -groups
		$exception = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_exception_event','pid in ('.$pidList.')');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if(is_array($event->getExceptionSingleIds()) && array_search($row['uid'], $event->getExceptionSingleIds())!==false){
				$exception .= $row['title'].', ';
			}
		}			
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_cal_exception_event_group','pid in ('.$pidList.')');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if(is_array($event->getExceptionGroupIds()) && array_search($row['uid'], $event->getExceptionGroupIds())!==false){
				$exception .= $row['title'].', ';
			}
		}
		if(strlen($exception)>3){
			$exception = substr($exception,0,strlen($exception)-2);
		}
		// selecting uids of available creator & -groups
		$cal_user = '';
		$where = ' AND tx_cal_event.uid='.$event->getUid().' AND tx_cal_fe_user_category_mm.tablenames="fe_users" AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0';
		$orderBy = '';
		$groupBy = '';
		$limit = '';
//		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('fe_users.*','tx_cal_event','tx_cal_fe_user_category_mm','fe_users',$where,$groupBy ,$orderBy,$limit);
//		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {	
//			if($row['name']==''){
//				$cal_user .= $row['username'].', ';
//			}else{
//				$cal_user .= $row['name'].', ';
//			}
//		}
		
//		$where = ' AND tx_cal_event.uid='.$event->getUid().' AND tx_cal_fe_user_category_mm.tablenames='fe_groups' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0';
//		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('fe_groups.*','tx_cal_event','tx_cal_fe_user_category_mm','fe_groups',$where,$groupBy ,$orderBy,$limit);
//		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
//			$cal_user = $row['title'].', ';
//		}
//		if(strlen($cal_user)>3){
//			$cal_user = substr($cal_user,0,strlen($cal_user)-2);
//		}
		
		// selectionf uids of available notify/monitor users & -groups
		$cal_notify_user = '';
		$where = ' AND tx_cal_event.uid='.$event->getUid().' AND tx_cal_fe_user_event_monitor_mm.tablenames="fe_users" AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0';
		$orderBy = '';
		$groupBy = '';
		$limit = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('fe_users.*','tx_cal_event','tx_cal_fe_user_event_monitor_mm','fe_users',$where,$groupBy ,$orderBy,$limit);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$cal_notify_user =$row['name'].', ';
		}

		$where = ' AND tx_cal_event.uid='.$event->getUid().' AND tx_cal_fe_user_event_monitor_mm.tablenames="fe_groups" AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('fe_groups.*','tx_cal_event','tx_cal_fe_user_event_monitor_mm','fe_groups',$where,$groupBy ,$orderBy,$limit);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$cal_notify_user =$row['title'].', ';
		}

		$event_start_day = date('Ymd',$event->getStartdate());
		$event_end_day = date('Ymd',$event->getEnddate());
		$event_start_time = date('Hi',$event->getStarttime());
		$event_end_time = date('Hi',$event->getEndtime());
		$title = $event->getTitle();
		$organizer = $event->getOrganizer();
		$location = $event->getLocation();
		$description = $event->getDescription();
		$by_day = $event->getByDay();
		$by_monthday = $event->getByMonthDay();
		$by_month = $event->getByMonth();
		$until = $event->getUntil();
		if($until>0){
			$until = date('Ymd', $until);
		}else{
			$until = '';
		}
		$count = $event->getCount();
		$interval = $event->getInterval();
		$uid = $event->getUid();
		
		$hidden = '';		
		$languageArray = array(
			'uid'					=> $uid,
			'view'					=> 'remove_event',
			'lastview'				=> $this->controller->extendLastView(),
			'type'					=> 'tx_cal_phpicalendar',
			'l_delete_event'		=> $this->controller->pi_getLL('l_delete_event'),
			'l_hidden'				=> $this->controller->pi_getLL('l_hidden'),
			'hidden'				=> $hidden,
			'l_category'			=> $this->controller->pi_getLL('l_event_category'),
			'category_ids'			=> $category,
			'l_event_start_day'		=> $this->controller->pi_getLL('l_event_edit_startdate'),
			'event_start_day'		=> $event_start_day,
			'l_event_start_time'	=> $this->controller->pi_getLL('l_event_edit_starttime'),
			'event_start_time'		=> $event_start_time,
			'l_event_end_day'		=> $this->controller->pi_getLL('l_event_edit_enddate'),
			'event_end_day'			=> $event_end_day,
			'l_event_end_time'		=> $this->controller->pi_getLL('l_event_edit_endtime'),
			'event_end_time'		=> $event_end_time,
			'l_title'				=> $this->controller->pi_getLL('l_event_title'),
			'title'					=> $title,
			'l_user'				=> $this->controller->pi_getLL('l_event_user'),
			'user_ids' 				=> $cal_user,
			'l_organizer'			=> $this->controller->pi_getLL('l_event_organizer'),
			'organizer'				=> $organizer,
			'l_cal_organizer'		=> $this->controller->pi_getLL('l_event_cal_organizer'),
			'organizer_ids'			=> $cal_organizer,
			'l_location'			=> $this->controller->pi_getLL('l_event_location'),
			'location_ids'			=> $cal_location,
			'l_cal_location'		=> $this->controller->pi_getLL('l_event_cal_location'),
			'location'				=> $location,
			'l_description'			=> $this->controller->pi_getLL('l_event_description'),
			'description'			=> $description,
			'l_frequency'			=> $this->controller->pi_getLL('l_event_frequency'),
			'frequency_ids' 		=> $frequency,
			'l_by_day'				=> $this->controller->pi_getLL('l_event_edit_byday'),
			'by_day'				=> $by_day,
			'l_by_monthday'			=> $this->controller->pi_getLL('l_event_edit_bymonthday'),
			'by_monthday'			=> $by_monthday,
			'l_by_month'			=> $this->controller->pi_getLL('l_event_edit_bymonth'),
			'by_month'				=> $by_month,
			'l_until'				=> $this->controller->pi_getLL('l_event_edit_until'),
			'until'					=> $until,
			'l_count'				=> $this->controller->pi_getLL('l_event_count'),
			'count'					=> $count,
			'l_interval'			=> $this->controller->pi_getLL('l_event_interval'),
			'interval'				=> $interval,
			'l_notify_on_change'	=> $this->controller->pi_getLL('l_event_monitor'),
			'notify_ids'			=> $cal_notify_user,
			'l_exception'			=> $this->controller->pi_getLL('l_event_exception'),
			'exception_ids'			=> $exception,
			'l_delete'				=> $this->controller->pi_getLL('l_delete'),
			'l_cancel'				=> $this->controller->pi_getLL('l_cancel'),
			'action_url'			=> $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'remove_event')),
		);
		
		if($event->isClone()){
			$languageArray['action_exception_url'] = $this->controller->pi_linkTP_keepPIvars_url(array('view'=>'save_exception_event'));
			$languageArray['l_create_exception'] = $this->controller->pi_getLL('l_create_exception');
			$rems['###CREATE_EXCEPTION###'] = $this->cObj->getSubpart($page, '###CREATE_EXCEPTION###');
		}else{
			$rems['###CREATE_EXCEPTION###'] = '';
		}
		
		$page = $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
		
		// replacing all values at the template
		$page = $this->controller->replace_tags($languageArray,$page);

		return $page;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_event_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_event_view.php']);
}
?>