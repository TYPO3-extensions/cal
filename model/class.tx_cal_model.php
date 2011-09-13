<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Steffen Kamper
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
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_calendar.php');
require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');

/**
 * Base model for the calendar.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_model extends tx_cal_base_model {
	
	var $row;
	var $isClone = false;
	var $tstamp;
	var $sequence = 1;
	var $title;
	var $organizer;
	var $location;
	var $content;
	var $start;
	var $end;
	var $allday = 0;
	var $timezone;
	var $calnumber = 1;
	var $calname;
	var $calendarUid;
	var $url;
	var $alarmdescription;
	var $summary;
	var $description;
	var $overlap = 1;
	var $status;
	var $_class;
	var $until;
	var $freq = '';
	var $reccuring_end;
	var $cnt;
	var $bysecond = array();
	var $byminute = array();
	var $byhour = array();
	var $byday = array();
	var $byweekno = array();
	var $bymonth = array();
	var $byyearday = array();
	var $bymonthday = array();
	var $byweekday = array();
	var $bysetpos = array();
	var $wkst = '';
	var $displayend;
	var $spansday;
	var $categories = array();
	var $categoriesAsString;
	var $categoryUidsAsArray;
	var $location_id = 0;
	var $organizer_id = 0;
	var $locationLink;
	var $organizerLink;
	var $locationPage;
	var $organizerPage;
	var $organizerObject;
	var $locationObject;
	var $exception_single_ids = array();
	var $notifyUserIds = array();
	var $exceptionGroupIds = array();
	var $notifyGroupIds = array();
	var $creatorUserIds = array();
	var $creatorGroupIds = array();
	var $exceptionEvents = array();
	var $editable = false;
	var $headerstyle = 'default_categoryheader';//'#557CA3';//'#0000ff';
	var $bodystyle = 'default_categorybody';//''#6699CC';//'#ccffcc';
	var $crdate = 0;

	/* new */
	var $event_type;
	var $page;
	var $ext_url;
	/* new */
	
	var $externalPlugin = 0;
	
	var $sharedUsers = Array();
	var $sharedGroups = Array();
	var $eventOwner;
	
	var $attendee = '';
	
	/**
	 *  Constructor.
	 *  @param	$serviceKey String	The serviceKey for this model
	 */
	function tx_cal_model($serviceKey){
		$this->setObjectType('event');
		$this->tx_cal_base_model($serviceKey);
	}
		
	/**
	 *  Returns the timestamp value.
	 *  @return		Integer		The timestamp.
	 */
	function getTstamp() {
		return $this->tstamp;
	}

	/**
	 *  Sets the timestamp value.
	 *	@param	$timestamp	Integer	The timestamp.
	 */
	function setTstamp($timestamp) {
		$this->tstamp = $timestamp;
	}
	
	/**
	 *  Returns the sequence value.
	 *  @return		Array		The sequence.
	 */
	function getSequence() {
		return $this->sequence;
	}

	/**
	 *  Sets the sequence value.
	 *	@param	$sequence	Array	The sequence.
	 */
	function setSequence($sequence) {
		$this->sequence = $sequence;
	}
	

	/**
	 *  Sets the event organizer.
	 *  @param	$organizer	String		The organizer of the event.
	 */
	function setOrganizer($organizer) { 
		$this->organizer = $organizer; 
	}
	
	/**
	 *  Returns the event organizer.
	 *  @return		String		The organizer of the event.
	 */
	function getOrganizer() { 
		return $this->organizer; 
	}

	/**
	 *  Sets the event title.
	 *  @param	$title	String		The title of the event.
	 */
	function setTitle($title) { 
		$this->title = $title; 
	}
	
	/**
	 *  Returns the event title.
	 *  @return		String		The title of the event.
	 */
	function getTitle() { 
		return $this->title; 
	}
	
	/**
	 *  Sets the event creation time.
	 *  @param	$timestamp	Integer		Timestamp of the event creation.
	 */
	function setCreationDate($timestamp) { 
		$this->crdate = $timestamp; 
	}
	
	/**
	 *  Returns timestamp of the event creation.
	 *  @return		Integer		Timestamp of the event creation.
	 */
	function getCreationDate() { 
		return $this->crdate; 
	}
	
	
	/**
	 *  Returns the rendered event.
	 *  @return		String		rendered event.
	 */
	function renderEvent(){
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$d = nl2br($cObj->parseFunc($this->getDescription(),$this->conf['parseFunc.']));
		$eventStart = $this->getStart();
		$eventEnd = $this->getEnd();
		return '<h3>'.$this->getTitle().'</h3><font color="#000000"><ul>' .
				'<li>Start: '.$eventStart->format('%H:%M').'</li>' .
				'<li>End: '.$eventEnd->format('%H:%M').'</li>' .
				'<li> Organizer: '.$this->getOrganizer().'</li>' .
				'<li>Location: '.$this->getLocation().'</li>' .
				'<li>Description: '.$d.'</li></ul></font>';
	}
	
	/**
	 *  Returns the rendered event for allday.
	 *  @return		String		rendered event for allday -> the title.
	 */
	function renderEventForAllDay(){
		return $this->getTitle();
	}

	/**
	 *  Returns the rendered event for day.
	 *  @return		String		rendered event for day -> the title.
	 */
	function renderEventForDay(){
		return $this->title;
	}

	/**
	 *  Returns the rendered event for week.
	 *  @return		String		rendered event for week -> the title.
	 */
	function renderEventForWeek(){
		return $this->title;
	}

	/**
	 *  Returns the rendered event month day.
	 *  @return		String		rendered event for month -> the title.
	 */
	function renderEventForMonth(){
		return $this->title;
	}

	/**
	 *  Returns the rendered event month day for a mini month view.
	 *  @return		String		rendered event for a mini month -> the title.
	 */
	function renderEventForMiniMonth(){
		return $this->title;
	}

	/**
	 *  Returns the rendered event for year.
	 *  @return		String		rendered event for year -> the title.
	 */
	function renderEventForYear(){
		return $this->title;
	}

	/**
	 *  Returns the location value.
	 *  @return		String		The location.
	 */
	function getLocation() { 
		return $this->location; 
	}

	/**
	 *  Sets the event location value.
	 *  @param	$location	String		The location.
	 */
	function setLocation($location) { 
		$this->location = $location; 
	}

	/**
	 *  Returns the location link value.
	 *  @return		String		The location link.
	 */
	function getLocationLinkUrl() { 
		return $this->locationLink; 
	}

	/**
	 *  Sets the event location link value.
	 *  @param	$locationLink	String		The location link.
	 */
	function setLocationLinkUrl($locationLink) { 
		$this->locationLink = $locationLink; 
	}

	/**
	 *  Sets the event location page value.
	 *  @param	$page	Integer		The location page.
	 */
	function setLocationPage($page) { 
		$this->locationPage = $page; 
	}
	
	/**
	 *	Returns the locationPage.
	 *	@return		Integer		The pid to link the location to
	 */
	function getLocationPage() { 
		return $this->locationPage; 
	}
	
	/**
	 *	Returns the startdate object.
	 *	@return		Integer		The startdate timeObject
	 */
	function getStart(){	
		return $this->start;
	}

	/**
	 *	Returns the enddate object.
	 *	@return		Integer		The enddate timeObject
	 */
	function getEnd(){
		return $this->end;
	}
	
	/**
	 *  Sets the event start.
	 *  @param	$start	Object		Pear Date object
	 */
	function setStart($start){
		$this->start = new tx_cal_date();
		$this->start->copy($start);
		$this->row['start_date'] = $start->format('%Y%m%d');
		$this->row['start_time'] = $start->getHour() * 3600 + $start->getMinute() * 60;
	}

	/**
	 *  Sets the event end.
	 *  @param	$end	Object		Pear Date object
	 */
	function setEnd($end){
		$this->end = new tx_cal_date();
		$this->end->copy($end);
		$this->row['end_date'] = $end->format('%Y%m%d');
		$this->row['end_time'] = $end->getHour() * 3600 + $end->getMinute() * 60;
	}

	/**
	 *	Returns the startdate as unix timestamp.
	 *	@return		Integer		The startdate as unix timestamp
	 */
	function getStartAsTimestamp() {
		$start = &$this->getStart();
		return $start->getDate(DATE_FORMAT_UNIXTIME);
	}
	
	/**
	 *	Returns the enddate as unix timestamp.
	 *	@return		Integer		The enddate as unix timestamp
	 */
	function getEndAsTimestamp() {
		$end = &$this->getEnd();
		return $end->getDate(DATE_FORMAT_UNIXTIME);
	}

	/**
	 *	Returns the status value.
	 *	@return		String		The status
	 */
	function getStatus() {	
		if($this->status=='' && $this->freq!='' && $this->freq!='none'){
			$this->status ='recurring';
		}
		return $this->status; 
	}

	/**
	 *  Sets the event status.
	 *  @param	$status	String		The status.
	 */
	function setStatus($status){
		$this->status = $status;
	}

	/**
	 *	Returns the ? value.
	 *	@return		?		The ?
	 *  @TODO field is missing
	 */
	function getConfirmed() { 
		return; 
	}

	/**
	 *	Returns the cal recu value.
	 *	@return		Array		The ? - empty array
	 *  @TODO What is that for?
	 */
	function getCalRecu() { 
		return array(); 
	}

	/**
	 *	Returns the cal number value.
	 *	@return		String		The calnumber
	 */
	function getCalNumber() { 
		return $this->calnumber; 
	}

	/**
	 *  Sets the calnumber.
	 *  @param	$calnumber	String		The calnumber.
	 */
	function setCalNumber($calnumber) { 
		$this->calnumber = $calnumber; 
	}

	/**
	 *	Returns the calendar uid.
	 *	@return		Integer		The calendar uid
	 */
	function getCalendarUid() { 
		return $this->calendarUid; 
	}

	/**
	 *  Sets the calendar uid.
	 *  @param	$uid	Integer		The calendar uid.
	 */
	function setCalendarUid($uid) { 
		$this->calendarUid = $uid; 
	}
	
	/**
	 * Returns the calendar object
	 *
	 * @return		tx_cal_calendar_model	The calendar object
	 */
	function getCalendarObject() {
		if(!$this->calendarObject) {
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$this->calendarObject = $modelObj->findCalendar($this->getCalendarUid());
		}
		
		return $this->calendarObject;
	}

	/**
	 *	Returns the calendar name.
	 *	@return		String		The calendar name
	 */
	function getCalName() { 
		return $this->calname; 
	}

	/**
	 *  Sets the calendar name.
	 *  @param	$name	String		The calendar name.
	 */
	function setCalName($calname) { 
		$this->calname = $calname; 
	}

	function getOverlap() { 
		return $this->overlap; 
	}
	
	function setOverlap($overlap){
		$this->overlap = $overlap;
	}
	
	function getTimezone() {
		return $this->timezone;	
	}
	
	function setTimezone($timezone) {
		$this->timezone = $timezone;
	}
	
	function getDuration() {
		return endtime - starttime;	
	}
	
	function isAllday() { 
		return $this->allday; 
	}
	
	function setAllday($boolean){
		$this->allday = $boolean;
	}
	
	function getRecurringRule() {
		if($this->freq!='none' && $this->freq!=''){
			$return = array();
			$return['FREQ'] = $this->freq;
			$return['INTERVAL'] = $this->interval; 
			return $return;
		}
		return;
	}
	
	function setRecur($recur = array()){
//TODO?
	}
	
	function getUrl() {
		return $this->url;
	}
	
	function setUrl($url) {
		$this->url = $url;
	}
	
	function getVAlarmDescription() {
		return $this->url;
	}
	
	function setVAlarmDescription($alarmdescription) {
		$this->alarmdescription = $alarmdescription;
	}
	
	function isClone() {
		return $this->isClone;
	}
	
	function setIsClone($boolean) {
		$this->isClone = $boolean;
	}
	
	function getRecurrance() {
		$a = array();
		$a['tzid'] = $this->timezone;
		$a['date'] = $this->startdate;
		$a['time'] = $this->starthour;
		return $a;	
	}
	
	 function getByMonth() {
	 	return $this->bymonth;
	 }
	 
	 function setByMonth($bymonth) {
	 	if($bymonth!=''){
	 		$this->bymonth = split (',', $bymonth);
	 	}
	 	if(strtoupper($bymonth)=='ALL' || in_array('all',$this->bymonth)){
	 		$this->bymonth = array(1,2,3,4,5,6,7,8,9,10,11,12);
	 	}
	 }
	 
	 function getByDay() {
	 	return $this->byday;
	 }
	 
	 function setByDay($byday) {
	 	$byday = strtoupper($byday);
	 	if($byday!=''){
	 		$this->byday = split (',', $byday);
	 	}

	 	if(strtoupper($byday)=='ALL' || in_array('all',$this->byday)){
	 		$this->byday = array('MO','TU','WE','TH','FR','SA','SU');
	 	}
	 }
	
	 
	 function getByMonthDay() {
	 	return $this->bymonthday;
	 }
	 
	 function setByMonthday($bymonthday) {
	 	if($bymonthday!=''){
	 		$this->bymonthday = t3lib_div::trimExplode (',', $bymonthday,1);
	 	}
	 	if(strtoupper($bymonthday)=='ALL' || in_array('all',$this->bymonthday)){
	 		$this->bymonthday = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
	 	}
	 }
	 
	 function getByWeekDay() {
	 	return $this->byweekday;
	 }
	 
	 function setByWeekDay($byweekday) {
	 	$this->byweekday = split (',', $byweekday);
	 }
	 
	 function getByWeekNo() {
	 	return $this->byweekno;
	 }
	 
	 function setByWeekNo($byweekno) {
	 	$this->byweekno = split (',', $byweekno);
	 }
	 
	 function getByMinute() {
	 	return $this->byminute;
	 }
	 
	 function setByMinute($byminute) {
	 	$this->byminute = split (',', $byminute);
	 }
	 
	 function getByHour() {
	 	return $this->byhour;
	 }
	 
	 function setByHour($byhour) {
	 	$this->byhour = split (',', $byhour);
	 }
	 
	 function getBySecond() {
	 	return $this->bysecond;
	 }
	 
	 function setBySecond($bysecond) {
	 	$this->bysecond = split (',', $bysecond);
	 }
	 
	 function getByYearDay() {
	 	return $this->byyearday;
	 }
	 
	 function setByYearDay($byyearday) {
	 	$this->byyearday = split (',', $byyearday);
	 }
	 
	 function getBySetPos() {
	 	return $this->bysetpos;
	 }
	 
	 function setBySetPos($bysetpos){
	 	$this->bysetpos = $bysetpos;
	 }
	 
	 function getWkst() {
	 	return $this->wkst;
	 }
	 
	 function setWkst($wkst){
	 	$this->wkst = $wkst;
	 }
	 
	 function getInterval() {
	 	return $this->interval;
	 }
	 
	 function setInterval($interval) {
	 	$this->interval = $interval;
	 }
	 
	 function getSummary() {
	 	return $this->summary;
	 }
	 
	 function setSummary($summary){
	 	$this->summary = $summary;
	 }
	 
	 function getClass() {
	 	return $this->_class;
	 }
	 
	 function setClass($class){
	 	$this->_class = $class;
	 }
	 
	 function getDisplayEnd() {
	 	return $this->displayend;
	 }
	 
	 function setDisplayEnd($displayend){
	 	$this->displayend = $displayend;
	 }
	 
	 function getContent() {
		return $this->content;
	}

	function setContent($t) {
		$this->content = $t;
	}
	 
	 /**
	  * Returns
	  */
	 function getDescription() {
	 	return $this->description;
	 }
	 
	 /**
	  * Sets the discription attribute
	  * @param	$description	string	Description of the event
	  */
	 function setDescription($description){
	 	$this->description = $description;
	 }
	 
	 
	 /**
	  * Returns the until attribute (yyymmdd)
	  */
	 function getUntil() {
	 	return $this->until;
	 }
	 
	 /**
	  * Sets the until object.
	  * @param	$until	object		pear date object
	  */
	 function setUntil($until){
	 	$this->until = $until;
	 }
	 
	 function getFreq() {
	 	return $this->freq;
	 }
	 
	 /**
	  * Sets the recurring frequency
	  */
	 function setFreq($freq){
	 	$this->freq = $freq;
	 }
	 
	 /**
	  * Returns how often a recurring event is supposed to recurr as max
	  */
	 function getCount() {
	 	return $this->cnt;
	 }
	 
	 /**
	  * Sets how often a recurring event is supposed to recurr as max
	  * @param	$count	int		How often a recurring event is supposed to recurr as max
	  */
	 function setCount($count){
	 	$this->cnt = $count;
	 }
	 
	 /**
	  * Returns TRUE if the events lasts the whole day
	  */
	 function getSpansDay() {
	 	return $this->spansday;
	 }
	 
	 /**
	  * Sets the spansday attribute
	  * @param	$spansday	boolean	TRUE, if the event lasts the whole day
	  */
	 function setSpansDay($spansday){
	 	$this->spansday = $spansday;
	 }
	 
	 /**
	  * Returns the categories (array)
	  */
	 function getCategories() {
	 	return $this->categories;
	 }
	 
	 /**
	  * Sets the categories
	  * @param	$categories	Array	An array representation of the categories
	  */
	 function setCategories($categories){
	 	if(is_array($categories)){
	 		$this->categories = $categories;
	 	}
	 }
	 
	 /**
	  * Adds an event to the exceptionEvents array
	  * @param	$ex_events	object	Instance of this class (tx_cal_model)
	  */
	 function addExceptionEvent($ex_event){
	 	array_push($this->exceptionEvents, $ex_event);
	 }
	 
	 /**
	  * Sets the exceptionEvents
	  * @param	$ex_events	array	Array of exception events
	  */
	 function setExceptionEvents($ex_events){
	 	$this->exceptionEvents = $ex_events;
	 }
	 
	 /**
	  * Returns the exceptionEvents array
	  */
	 function getExceptionEvents(){
	 	return $this->exceptionEvents;	
	 }
	 
	 /**
	  * Sets the editable value
	  * @param	$editable	boolean		TRUE, if the event should be editable
	  */
	 function setEditable($editable){
	 	$this->editable = $editable;
	 }
	 
	 /**
	  * Returns TRUE if this event is editable
	  */
	 function getEditable(){
	 	return $this->editable;	
	 }
	 
	 
	 /**
	  * Sets the organizer_id
	  * @param	$id		int		The new id
	  */
	 function setOrganizerId($id){
	 	$this->organizer_id = $id;
	 }
	 
	 /**
	  * Returns the organizer_id
	  */
	 function getOrganizerId(){
	 	return $this->organizer_id;	
	 }
	
	/**
	 * Returns the organizer object.
	 */
	function getOrganizerObject() {
		if(!$this->organizerObject) {
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($confArr['useOrganizerStructure'] ? $confArr['useOrganizerStructure'] : 'tx_cal_organizer');
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$this->organizerObject = $modelObj->findOrganizer($this->getOrganizerId(), $useOrganizerStructure);
		}
		
		return $this->organizerObject;
	}
	 
	 /**
	  * Sets the organizerLink
	  * @param	$id		string		A html link to an organizer
	  */
	 function setOrganizerLinkUrl($id){
	 	$this->organizerLink = $id;
	 }
	 
	 /**
	  * Return the organizerLink. A html link to an organizer
	  */
	 function getOrganizerLinkUrl(){
	 	return $this->organizerLink;	
	 }
	 
	 /**
	  * Return the organizerpage. The pid to link the organizer to
	  */
	 function getOrganizerPage(){
	 	return $this->organizerPage;	
	 }
	 
	 /**
	  * Sets the organizerPage
	  * @param	$pid		int		The pid to link the organizer to
	  */
	 function setOrganizerPage($pid){
	 	$this->organizerPage = $pid;
	 }
	 
	 /**
	  * Sets the location_id
	  * @param	$id		int		The new id
	  */
	 function setLocationId($id){
	 	$this->location_id = $id;
	 }
	 
	 /**
	  * Returns the location_id
	  */
	 function getLocationId(){
	 	return $this->location_id;	
	 }
	
	/**
	 * Returns the location object.
	 */
	function getLocationObject() {
		if(!$this->locationObject) {
			$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($confArr['useLocationStructure'] ? $confArr['useLocationStructure'] : 'tx_cal_location');
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$this->locationObject = $modelObj->findLocation($this->getLocationId(),$useLocationStructure);
		}

		return $this->locationObject;
	}
	
	
	
	 
	 /**
	  * Adds an id to the exception_single_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addExceptionSingleId($id){
	 	$this->exception_single_ids[] =$id;
	 }
	 
	 /**
	  * Returns the exception_single_ids array
	  */
	 function getExceptionSingleIds(){
	 	return $this->exception_single_ids;	
	 }
	 
	 /**
	  * Sets the exception_single_ids array
	  * @param $idArray	Array	Array of exception single ids
	  */
	 function setExceptionSingleIds($idArray){
	 	$this->exception_single_ids = $idArray;	
	 }
	 
	 /**
	  * Adds an id to the notifyUserIds array
	  * @param	$id		int		The id to be added
	  */
	 function addNotifyUser($id){
	 	$this->notifyUserIds[] = $id;
	 }
	 
	 /**
	  * Adds an category object to the category array
	  * @param	$category		object		The category to be added
	  */
	 function addCategory($category){
	 	$this->categories[] = $category;
	 }
	 
	 /**
	  * Returns the notifyUserIds array
	  */
	 function getNotifyUserIds(){
	 	return $this->notifyUserIds;	
	 }
	 
	 /**
	  * Adds am id to the exceptionGroupIds array
	  * @param	$id		int		The id to be added
	  */
	 function addExceptionGroupId($id){
		if($id>0){
	 		array_push($this->exceptionGroupIds,$id);
		}
	 }
	 
	 /**
	  * Returns the exceptionGroupIds array
	  */
	 function getExceptionGroupIds(){
	 	return $this->exceptionGroupIds;	
	 }
	 
	 /**
	  * Sets the exceptionGroupIds array
	  * @param $idArray	Array	Array of exception group ids
	  */
	 function setExceptionGroupIds($idArray){
	 	$this->exceptionGroupIds = $idArray;	
	 }
	 
	 
	 /**
	  * Adds an id to the notifyGroupIds array
	  * @param	$id		int		The id to be added
	  */
	 function addNotifyGroup($id){
	 	if($id>0){
	 		$this->notifyGroup[] = $id;
	 	}
	 }
	 
	 /**
	  * Returns the notifyGroupIds array
	  */
	 function getNotifyGroupIds(){
	 	return $this->notifyGroupIds;	
	 }
	 
	 /**
	  * Adds an id to the creatorUserIds array
	  * @param	$id		int		The id to be added
	  */
	 function addCreatorUserId($id){
	 	array_push($this->creatorUserIds,$id);
	 }
	 
	 /**
	  * Returns the creatorUserIds array
	  */
	 function getCreatorUserIds(){
	 	return $this->creatorUserIds;	
	 }
	 
	 /**
	  * Adds an id to the creatorGroupIds array
	  * @param	$id		int		The id to be added
	  */
	 function addCreatorGroupId($id){
	 	$this->creatorGroupIds[] =$id;
	 }
	 
	 /**
	  * Returns the creatorGroupIds array
	  */
	 function getCreatorGroupIds(){
	 	return $this->creatorGroupIds;	
	 }
	 
	 /**
	  * Sets the headerstyle
	  * @param	$style	String	A style name
	  */
	 function setHeaderStyle($style){
	 	if($style!=''){
	 		$this->headerstyle = $style;
	 	}
	 }
	 
	 /**
	  * Returns the headerstyle name
	  */
	 function getHeaderStyle(){
	 	return $this->headerstyle;	
	 }
	 
	 /**
	  * Sets the bodystyle
	  * @param	$style	String	A style name
	  */
	 function setBodyStyle($style){
	 	if($style!=''){
	 		$this->bodystyle = $style;
	 	}
	 }
	 
	 /**
	  * Returns the bodystyle name
	  */
	 function getBodyStyle(){
	 	return $this->bodystyle;	
	 }
	 
	 	
	/* new */
	 function setPage($t){
	 	$this->page = $t;
	 }

	 function setExtUrl($t){
	 	$this->ext_url = $t;
	 }
	 
	function getEventType(){
		return $this->event_type;
	}

	 function setEventType($t){
	 	$this->event_type = $t;
	 }
	 /* new */
	 
	 function search($pidList=''){
	 }
	 
	function addSharedUser($id){
		$this->sharedUsers[] = $id;
	}
	
	function addSharedGroup($id){
		$this->sharedGroups[] = $id;
	}
	
	function getSharedUsers(){
		return ($this->sharedUsers);
	}
	
	function getSharedGroups(){
		return($this->sharedGroups);
	}
	
	function setSharedUsers($userIds){
		$this->sharedUsers = $userIds;
	}
	
	function setSharedGroups($groupIds){
		$this->sharedGroups = $groupIds;
	}
	 
	function isEventOwner($userId, $groupIdArray){
		if(is_array($this->eventOwner['fe_users']) && in_array($userId, $this->eventOwner['fe_users'])){
			return true;
		}
		foreach($groupIdArray as $id){
			if(is_array($this->eventOwner['fe_groups']) && in_array($id, $this->eventOwner['fe_groups'])){
				return true;
			}
		}
		return false;
	}
	
	function isSharedUser($userId, $groupIdArray){
		if(is_array($this->getSharedUsers()) && in_array($userId, $this->getSharedUsers())){
			return true;
		}
		foreach($groupIdArray as $id){
			if(is_array($this->getSharedGroups()) && in_array($id, $this->getSharedGroups())){
		 		return true;
			}
		}
		
		return false;
	}
	
	 function getAdditionalValuesAsArray() {
		 	$values = array();
/*		
			$values = $this->row;
			$values['uid']=$this->uid;
			$values['pid']=$this->pid;
			$values['crdate']=$this->crdate;
			$values['title']=$this->title;
			$values['hidden']=$this->hidden;
			$values['description']=$this->description;
			$values['calendar_id']=$this->calendarUid;
			$values['organizer']=$this->organizer;
			$values['organizer_id']=$this->organizer_id;
			$values['organizer_pid'] = $this->organizerPage;
			$values['categories']=$this->categories;
			$values['category_string'] = $this->getCategoriesAsString(false);
			$values['location']=$this->location;
			$values['location_id']=$this->location_id;
			$values['location_pid'] = $this->locationPage;
			$values['start_date']=$this->start->format('%Y%m%d');
			$values['start_time']=$this->start->getHour()*3600 + $this->start->getMinute();
			$values['end_date']=$this->end->format('%Y%m%d');
			$values['end_time']=$this->end->getHour()*3600 + $this->end->getMinute();
			$values['allday']=$this->allday;
			$values['category_headerstyle']=$this->headerstyle;
			$values['category_bodystyle']=$this->bodystyle;
			$values['exception_single_ids']=implode(',',(Array)$this->exception_single_ids);
			$values['exceptionGroupIds']=implode(',',(Array)$this->exceptionGroupIds);
			$values['image'] = implode(',',(Array)$this->image);
			$values['imagecaption'] = implode(chr(12),(Array)$this->imageCaption);
			$values['imagealttext'] = implode(chr(12),(Array)$this->imageAltText);
			$values['imagetitletext'] = implode(chr(12),(Array)$this->imageTitleText);
			$values['freq']=$this->freq;
			if(is_object($this->until)){
				$values['until']=$this->until->format('%Y%m%d');
			} else {
				$values['until'] = '00000101';
			}
			$values['cnt']=$this->cnt;
			$values['byday']=implode(',',$this->byday);
			$values['bymonthday']=implode(',',$this->bymonthday);
			$values['bymonth']=implode(',',$this->bymonth);
			$values['intrval']=$this->interval;
			$values['attachment']=implode(',',(Array)$this->attachment);
			$values['attachmentcaption'] = implode(chr(12),(Array)$this->attachmentCaption);
			$values['page']=$this->page;
			$values['type'] = $this->event_type;
			$values['ext_url'] = $this->ext_url;
*/
			$values['page']=$this->page;
			$values['type'] = $this->event_type;
			$values['intrval']=$this->interval;
			$values['cnt']=$this->cnt;
			if(is_object($this->until)){
				$values['until']=$this->until->format('%Y%m%d');
			} else {
				$values['until'] = '00000101';
			}
			$values['category_headerstyle']=$this->headerstyle;
			$values['category_bodystyle']=$this->bodystyle;
			$start = &$this->getStart();
			$values['start_date']=$start->format('%Y%m%d');
			$values['start_time']=$start->getHour()*3600 + $start->getMinute();
			$values['start'] = $this->getStartAsTimestamp();
			$end = &$this->getEnd();
			$values['end_date']=$end->format('%Y%m%d');
			$values['end_time']=$end->getHour()*3600 + $end->getMinute();
			$values['end'] = $this->getEndAsTimestamp();
			$values['allday']=$this->allday;
			$values['calendar_id']=$this->calendarUid;
			$values['category_string'] = $this->getCategoriesAsString(false);

			return $values;
//t3lib_div::debug($values);
	}
	
	function getCategoriesAsString($asLink = true){
		/*if($this->categoriesAsString){
			return $this->categoriesAsString;
		}*/
		$this->categoriesAsString = array();
		$rememberCats = array();

		if(!empty($this->categories)){
			foreach($this->categories as $categoryObject){
				if(in_array($categoryObject->getUid(), $rememberCats)){
					continue;
				}
				
				$rememberCats[] = $categoryObject->getUid();
				// init object and hand over the data of the category as fake DB values
				$this->initLocalCObject($categoryObject->getValuesAsArray());
				$categoryTitle=$this->local_cObj->stdWrap($categoryObject->getTitle(),$this->conf['view.'][$this->conf['view'].'.']['categoryLink_stdWrap.']);

				if($asLink){
					$headerstyle=$categoryObject->getHeaderStyle();
					$this->local_cObj->data['ATagParams'] = $headerstyle!='' ? ' class="'.$headerstyle.'"':'';
					$parameter['category']=$categoryObject->getUid();
					$parameter['offset'] = null;
					$controller = &tx_cal_registry::Registry('basic','controller');
					$this->local_cObj->data['link'] = $controller->pi_linkTP_keepPIvars_url($parameter, $this->conf['cache'], $this->conf['clear_anyway']);
					$this->local_cObj->setCurrentVal($categoryTitle);
					$this->categoriesAsString[]= $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['categoryLink'],$this->conf['view.'][$this->conf['view'].'.']['categoryLink.']);
				}else{
					$this->categoriesAsString[]= $categoryTitle;
				}
			}
		}
		// reset the object
		$this->initLocalCObject();
		return implode($this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['categoryLink_splitChar'],$this->conf['view.'][$this->conf['view'].'.']['categoryLink_splitChar.']),$this->categoriesAsString);
	}
	
	function getCategoryUidsAsArray(){

		if($this->categoryUidsAsArray){
			return $this->categoryUidsAsArray;
		}
		$first = true;
		$this->categoryUidsAsArray = array();
		if($this->getCategories()){
			foreach($this->getCategories() as $categoryArray){
				if($first){
					$this->categoryUidsAsArray[] = $categoryArray->getUid();
					$first = false;
				}else{
					$this->categoryUidsAsArray[] = $categoryArray->getUid();
				}
			}
		}
		return $this->categoryUidsAsArray;
	}
	
	function cloneEvent(){
		$instanceOfClass = t3lib_div :: makeInstanceClassName(get_class($this));
		$event = new $instanceOfClass ($this->getType());
		$event->setIsClone(true);
		return $event;
	}
	
	
	
	/**
	 * Calls user function defined in TypoScript
	 *
	 * @param	integer		$mConfKey : if this value is empty the var $mConfKey is not processed
	 * @param	mixed		$passVar : this var is processed in the user function
	 * @return	mixed		the processed $passVar
	 */
	function userProcess($mConfKey, $passVar) {
		if ($this->conf[$mConfKey]) {
			$funcConf = $this->conf[$mConfKey . '.'];
			$funcConf['parentObj'] = & $this;
			$passVar = $GLOBALS['TSFE']->cObj->callUserFunction($this->conf[$mConfKey], $funcConf, $passVar);
		}
		return $passVar;
	}
	
	function isExternalPluginEvent(){
		return $this->externalPlugin;
	}
	
	function getExternalPluginEventLink(){}
	
	function addAdditionalSingleViewUrlParams(&$currentParams){}
	
	function getLengthInSeconds(){
		$eventStart = $this->getStart();
		$eventEnd = $this->getEnd();
		$days = Date_Calc::dateDiff($eventStart->getDay(), $eventStart->getMonth(), $eventStart->getYear(), $eventEnd->getDay(), $eventEnd->getMonth(), $eventEnd->getYear());
		$hours = $eventEnd->getHour() - $eventStart->getHour();
		$minutes = $eventEnd->getMinute() - $eventStart->getMinute();
		return $days*86400 + $hours*3600 + $minutes*60;
	}
	 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_model.php']);
}
?>
