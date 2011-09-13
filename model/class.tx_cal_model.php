<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_svbase.php');
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
	var $type;
	var $uid;
	var $hidden;
	var $isClone = false;
	var $tstamp;
	var $sequence = 1;
	var $title;
	var $starttime;
	var $starthour = '';
	var $endtime;
	var $endhour = '';
	var $organizer;
	var $location;
	var $content;
	var $startdate;
	var $enddate;
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
	var $wkst = array();
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
	var $exception_single_ids = array();
	var $notify_single_ids = array();
	var $exception_group_ids = array();
	var $notify_group_ids = array();
	var $creator_single_ids = array();
	var $creator_group_ids = array();
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
	
	var $sharedUsers;
	var $eventOwner;
	
	/**
	 *  Constructor.
	 *  @param	$controller	Object	Reference to tx_cal_controller
	 *  @param	$serviceKey String	The serviceKey for this model
	 */
	function tx_cal_model(&$controller, $serviceKey){
		$this->tx_cal_base_model($controller, $serviceKey);
	}
	
	/**
	 *  Finds all events within a given range.
	 *  @return		array		The array of events represented by the model.
	 */
	function findAllWithin($starttime, $endtime, $pidList) {}
	
	/**
	 *  Finds all events.
	 *  @return		array		The array of events represented by the model.
	 */
	function findAll($pidList) {}
	
	/**
	 *  Finds a single event.
	 *  @return		object		The event represented by the model.
	 */
	function find($uid, $pidList) {}
	
	/**
	 *  Returns the hidden value.
	 *  @return		Integer		1 == true, 0 == false.
	 */
	function getHidden() {
		return $this->hidden;
	}
	
	/**
	 *  Sets the hidden value.
	 *	@param	$hidden Integer	1 == true, 0 == false.
	 */
	function setHidden($hidden) {
		$this->hidden = $hidden;
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
	 *  Returns the starttime value.
	 *  @return		Integer		The starttime.
	 */
	function getStarttime() {
		return $this->starttime;
	}
	
	/**
	 *  Sets the starthour, startdate and starttime values.
	 *	@param	$timestamp	Integer	The starttime.
	 */
	function setStarttime($timestamp) {
		if($timestamp>0){
			$this->starthour = getTimeFromTimestamp($timestamp);
			$this->startdate = getDayFromTimestamp($timestamp);
			$this->starttime = $timestamp;
		}
	}

	/**
	 *  Returns the endtime value.
	 *  @return		Integer		The endtime.
	 */
	function getEndtime() {
		return $this->endtime;
	}

	/**
	 * Sets the endhour, enddate and endtime value
	 * @param	$endtime	Integer	The endtime timestamp
	 */
	function setEndtime($endtime) {
		if($endtime>0){
			$this->endhour = getTimeFromTimestamp($endtime);
			$this->enddate = getDayFromTimestamp($endtime);
			$this->endtime = $endtime;
		}
	}

	/**
	 * 	Returns the type value
	 *  @return		Integer		The type.
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute. This should be the service type
	 * @param	$type	String	The service type
	 */
	function setType($type) {
		$this->type = $type;
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
	 *  Sets the event uid.
	 *  @param	$uid	Integer		The uid of the event.
	 */
	function setUid($uid) { 
		$this->uid = $uid; 
	}
	
	/**
	 *  Returns the event uid.
	 *  @return		Integer		The uid of the event.
	 */
	function getUid() { 
		return $this->uid; 
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
		$d = nl2br($this->cObj->parseFunc($this->getDescription(),$this->conf['parseFunc.']));
		return '<h3>'.$this->getTitle().'</h3><font color="#000000"><ul>' .
				'<li>Start: '.gmdate ('H:i', $this->getStartHour()).'</li>' .
				'<li>End: '.gmdate ('H:i', $this->getEndHour()).'</li>' .
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
	 *  Returns the rendered event for year.
	 *  @return		String		rendered event for year -> the title.
	 */
	function renderEventForYear(){
		return $this->title;
	}

	/**
	 *  Returns the starthour value.
	 *  @return		String		HHmm of the starthour timestamp value.
	 */
	function getStartHour(){
		return $this->starthour;
	}

	/**
	 *  Sets the event starthour value.
	 *  @param	$starthour	Integer		The starthour timestamp.
	 */
	function setStartHour($starthour){
		$this->starthour = $starthour;
		$this->starttime = $this->startdate + $this->starthour;
	}

	/**
	 *  Returns the endhour value.
	 *  @return		String		HHmm of the endhour timestamp value.
	 */
	function getEndHour(){
		return $this->endhour;
	}

	/**
	 *  Sets the event endhour value.
	 *  @param	$endhour	Integer		The endhour timestamp.
	 */
	function setEndHour($endhour){
		$this->endhour = $endhour;
		$this->endtime = $this->enddate + $this->endhour;
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
	function getLocationLink() { 
		return $this->locationLink; 
	}

	/**
	 *  Sets the event location link value.
	 *  @param	$locationLink	String		The location link.
	 */
	function setLocationLink($locationLink) { 
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
	 *	Returns the startdate value.
	 *	@return		Integer		The startdate timestamp
	 */
	function getStartDate(){	
		return $this->startdate;
	}

	/**
	 *	Returns the enddate value.
	 *	@return		Integer		The enddate timestamp
	 */
	function getEndDate(){
		return $this->enddate;
	}

	/**
	 *  Sets the event startdate and the starttime (startdate + starthour).
	 *  @param	$startdate	Integer		The startdate.
	 */
	function setStartDate($startdate){
		$this->startdate = $startdate;
		$this->starttime = ($this->startdate + $this->starthour);
	}

	/**
	 *  Sets the event startdate and the enddate (enddate + endhour).
	 *  @param	$enddate	Integer		The enddate.
	 */
	function setEndDate($enddate){
		$this->enddate = $enddate;
		$this->endtime = $this->enddate + $this->endhour;
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
	 
	 function setByday($byday) {
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
	 		$this->bymonthday = split (',', $bymonthday);
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
	  * Sets the until attribute.
	  * @param	$until	int		Int-representation of a date (yyyymmdd)
	  */
	 function setUntil($until){
	 	$this->until = $until;
	 	return;
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
	  * Returns the attendee
	  */
	 function getAttendee() {
	 	return $this->attendee;
	 }
	 
	 /**
	  * Sets the attendee
	  * @param	$attendee	TODO: What do we need here?	
	  */
	 function setAttendee($attendee){
	 	$this->attendee = $attendee;
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
	 	$this->categories = $categories;
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
	  * Sets the organizerLink
	  * @param	$id		string		A html link to an organizer
	  */
	 function setOrganizerLink($id){
	 	$this->organizerLink = $id;
	 }
	 
	 /**
	  * Return the organizerLink. A html link to an organizer
	  */
	 function getOrganizerLink(){
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
	  * Adds an id to the notify_single_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addNotifySingleId($id){
	 	array_push($this->notify_single_ids,$id);
	 }
	 
	 /**
	  * Adds an id to the category array
	  * @param	$categoryArray		array		The category attributes to be added
	  */
	 function addCategory($categoryArray){
	 	$this->categories[] = $categoryArray;
	 }
	 
	 /**
	  * Returns the notify_single_ids array
	  */
	 function getNotifySingleIds(){
	 	return $this->notify_single_ids;	
	 }
	 
	 /**
	  * Adds am id to the exception_group_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addExceptionGroupId($id){
		if($id>0){
	 		array_push($this->exception_group_ids,$id);
		}
	 }
	 
	 /**
	  * Returns the exception_group_ids array
	  */
	 function getExceptionGroupIds(){
	 	return $this->exception_group_ids;	
	 }
	 
	 /**
	  * Adds an id to the notify_group_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addNotifyGroupId($id){
	 	if($id>0){
	 		array_push($this->notify_group_ids,$id);
	 	}
	 }
	 
	 /**
	  * Returns the notify_group_ids array
	  */
	 function getNotifyGroupIds(){
	 	return $this->notify_group_ids;	
	 }
	 
	 /**
	  * Adds an id to the creator_single_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addCreatorSingleId($id){
	 	array_push($this->creator_single_ids,$id);
	 }
	 
	 /**
	  * Returns the creator_single_ids array
	  */
	 function getCreatorSingleIds(){
	 	return $this->creator_single_ids;	
	 }
	 
	 /**
	  * Adds an id to the creator_group_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addCreatorGroupId($id){
	 	$this->creator_group_ids[] =$id;
	 }
	 
	 /**
	  * Returns the creator_group_ids array
	  */
	 function getCreatorGroupIds(){
	 	return $this->creator_group_ids;	
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

	 function setEventType($t){
	 	$this->event_type = $t;
	 }
	 /* new */
	 
	 function search($pidList=''){
	 }
	 
	 function addSharedUser($id){
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
	
	function isSharedUser($userId){
		if(is_array($this->sharedUsers) && in_array($userId, $this->sharedUsers)){
			return true;
		}
		return false;
	}
	
	 function getValuesAsArray(){
		return array(
			'uid'=>$this->uid,
			'crdate'=>$this->crdate,
			'title'=>$this->title,
			'hidden'=>$this->hidden,
			'description'=>$this->description,
			'calendar_id'=>$this->calendarUid,
			'organizer'=>$this->organizer,
			'organizer_id'=>$this->organizer_id,
			'organizer_pid' => $this->organizerPage,
			'categories'=>$this->categories,
			'category_string' => $this->getCategoriesAsString(false),
			'location'=>$this->location,
			'location_id'=>$this->location_id,
			'location_pid' => $this->locationPage,
			'startdate'=>$this->startdate,
			'starthour'=>$this->starthour,
			'enddate'=>$this->enddate,
			'endhour'=>$this->endhour,
			'start_time'=>$this->starttime,
			'end_time'=>$this->endtimes,
			'allday'=>$this->allday,
			'category_headerstyle'=>$this->headerstyle,
			'category_bodystyle'=>$this->bodystyle,
			'exception_single_ids'=>$this->exception_single_ids,
			'exception_group_ids'=>$this->exception_group_ids,
			'image' => $this->image,
			'imagecaption' => $this->imagecaption,
			'imagealttext' => $this->imagealttext,
			'imagetitletext' => $this->imagetitletext,
			'freq'=>$this->freq,
			'until'=>$this->until,
			'cnt'=>$this->cnt,
			'byday'=>$this->byday,
			'bymonthday'=>$this->bymonthday,
			'bymonth'=>$this->bymonth,
			'intrval'=>$this->interval,
			'attachment'=>implode(',',$this->attachment),
			'page'=>$this->page,
			'type' => $this->event_type,
			'ext_url' => $this->ext_url,
		);
	}
	
	function getCategoriesAsString($asLink = true){
		if($this->categoriesAsString){
			return $this->categoriesAsString;
		}
		$first = true;
		$this->categoriesAsString = '';
		$this->tempATagParam = $GLOBALS['TSFE']->ATagParams;
		$rememberCats = array();
		if(!empty($this->categories)){
			foreach($this->categories as $categoryObject){

				if(in_array($categoryObject->getUid(), $rememberCats)){
					continue;
				}
				$rememberCats[] = $categoryObject->getUid();
				$GLOBALS['TSFE']->ATagParams = 'title="'.$categoryObject->getTitle().'"';
				$parameter['category']=$categoryObject->getUid();
				if($first){
					if($asLink){
						$this->categoriesAsString .= $this->controller->pi_linkTP_keepPIvars($categoryObject->getTitle(), $parameter, $this->conf['cache'], $this->conf['clear_anyway']);
					}else{
						$this->categoriesAsString .= $categoryObject->getTitle();
					}
					$first = false;
				}else{
					if($asLink){
						$this->categoriesAsString .= ', '.$this->controller->pi_linkTP_keepPIvars($categoryObject->getTitle(), $parameter, $this->conf['cache'], $this->conf['clear_anyway']);
					}else{
						$this->categoriesAsString .= ', '.$categoryObject->getTitle();
					}
				}
			}
		}
		$GLOBALS['TSFE']->ATagParams = $this->tempATagParam;
		return $this->categoriesAsString;
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
		$tx_cal_model = t3lib_div :: makeInstanceClassName('tx_cal_model');
		$event = new $tx_cal_model ($this->controller, $this->getType());
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
	 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_model.php']);
}
?>