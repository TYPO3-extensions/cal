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

/**
 * Base model for the calendar.  Provides basic model functionality that other
 * models can use or override by extending the class.  
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_model extends t3lib_svbase {
	
	var $type;
	var $uid;
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
	var $timezone;
	var $calnumber = 1;
	var $callegenddescription = array();
	var $calname;
	var $url;
	var $alarmdescription;
	var $summary;
	var $description;
	var $overlap = 1;
	var $status;
	var $_class;
	var $until;
	var $freq = "";
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
	var $category;
	var $category_id;
	var $location_id;
	var $organizer_id;
	var $locationLink;
	var $organizerLink;
	var $exception_single_ids = array();
	var $notify_single_ids = array();
	var $exception_group_ids = array();
	var $notify_group_ids = array();
	var $creator_single_ids = array();
	var $creator_group_ids = array();
	var $exceptionEvents = array();
	var $editable = false;
	var $headercolor = "#557CA3";//"#0000ff";
	var $bodycolor = "#6699CC";//"#ccffcc";
	var $headertextcolor = "#FFFFFF";
	var $bodytextcolor = "#FFFFFF";
	/* new */
	var $event_type;
	var $page;
	var $ext_url;
	/* new */

	/**
	 *  Finds all events within a given range.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAllWithin(&$cObj, $starttime, $endtime, $pidList) {}
	
	/**
	 *  Finds all events.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAll(&$cObj, $pidList) {}
	
	/**
	 *  Finds a single event.
	 *
	 *  @return		object			The event represented by the model.
	 */
	function find($uid, $pidList) {}
	
	
	
	function getTstamp() {
		return $this->tstamp;
	}

	function setTstamp($t) {
		$this->tstamp = $t;
	}
	
	function getSequence() {
		return $this->sequence;
	}

	function setSequence($t) {
		$this->sequence = $t;
	}
	
	function getStarttime() {
		return $this->starttime;
	}

	function setStarttime($t) {
		if($t>0){
			$date = getdate($t);
			$this->starthour = gmmktime($date['hours'],$date['minutes'],0,0,0,1) - gmmktime(0,0,0,0,0,1);
			$this->startdate = gmmktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
			$this->starttime = $t;
		}
	}

	function getEndtime() {
		return $this->endtime;
	}

	/**
	 * Sets the endtime attribute
	 * @param	$time	int
	 */
	function setEndtime($time) {
		if($time>0){
			$date = getdate($time);
			$this->endhour = gmmktime($date['hours'],$date['minutes'],0,0,0,1) - gmmktime(0,0,0,0,0,1);
			$this->enddate = gmmktime(0,0,0,$date['mon'],$date['mday'],$date['year']);
			$this->endtime = $time;
		}
	}

	/**
	 * Returns the type attribute
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute. This should be the service type
	 * @param	$type	string	The service type
	 */
	function setType($type) {
		$this->type = $type;
	}

	/**
	 *  Sets the event organizer.
	 *
	 *  @param		string		The organizer of the event.
	 *	 @return		void
	 */
	function setOrganizer($organizer) { 
		$this->organizer = $organizer; 
	}
	
	/**
	 *  Gets the event organizer.
	 *
	 *  @return		string		The organizer of the event.
	 */
	function getOrganizer() { 
		return $this->organizer; 
	}

	/**
	 *  Sets the event title.
	 *
	 *  @param		string		The title of the event.
	 *	 @return		void
	 */
	function setTitle($title) { 
		$this->title = $title; 
	}
	
	/**
	 *  Gets the event title.
	 *
	 *  @return		string		The title of the event.
	 */
	function getTitle() { 
		return $this->title; 
	}
	
	/**
	 *  Sets the event uid.
	 *
	 *  @param		integer		The UID of the event.
	 *	 @return		void
	 */
	function setUid($uid) { 
		$this->uid = $uid; 
	}
	
	/**
	 *  Gets the event uid.
	 *
	 *  @return		integer		The uid of the event.
	 */
	function getUid() { 
		return $this->uid; 
	}
	
	/**
	 *  Gets the event uid.
	 *
	 *  @return		integer		The uid of the event.
	 */
	function getCalLegendDescription() { 
		return $this->callegenddescription; 
	}
	
	/**
	 *  Gets the event uid.
	 *
	 *  @return		integer		The uid of the event.
	 */
	function addCalLegendDescription($color, $description) { 
		$this->callegenddescription[$color] = $description; 
	}
	
	function renderEvent($cObj){
		
		$d = nl2br($cObj->parseFunc($this->getDescription(),$cObj->conf["parseFunc."]));
		return "<h3>".$this->getTitle()."</h3><font color='#000000'><ul>" .
				"<li>Start: ".date ('H:i', $this->getStarttime())."</li>" .
				"<li>End: ".date ('H:i', $this->getEndtime())."</li>" .
				"<li> Organizer: ".$this->getOrganizer()."</li>" .
				"<li>Location: ".$this->getLocation()."</li>" .
				"<li>Description: ".$d."</li></ul></font>";

	}
	
	function renderEventForAllDay(){
		return $this->getTitle();
	}

	function renderEventForDay(){
		return $this->title;
	}

	function renderEventForWeek(){
		return $this->title;
	}

	function renderEventForMonth(){
		return $this->title;
	}

	function renderEventForYear(){
		return $this->title;
	}

	function getStartHour(){
		return gmdate("Hi",$this->starthour);
	}
	
	function setStartHour($h){
		$this->starthour = $h;
	}

	function getEndHour(){
		if ($this->endhour) {
			return gmdate("Hi",$this->endhour);
		}
	}
	
	function setEndHour($h){
		$this->endhour = $h;
		$this->endtime = $this->enddate + $this->endhour;
	}
	
	function getLocation() { 
		return $this->location; 
	}
	
	function setLocation($loc='') { 
		return $this->location = $loc; 
	}
	
	function getLocationLink() { 
		return $this->locationLink; 
	}
	
	function setLocationLink($loc='') { 
		return $this->locationLink = $loc; 
	}
	
	function getStartDate(){	
		return $this->startdate;
	}

	function getEndDate(){
		return $this->enddate;
	}
	
	function setStartDate($d){
		$this->startdate = $d;
		$this->starttime = ($this->startdate + $this->starthour);
	}
	
	function setEndDate($d){
		$this->enddate = $d;
		$this->endtime = 	$this->enddate + $this->endhour;
	}


	function getStatus() {	
		if($this->status=='' && $this->freq!='' && $this->freq!="none"){
			$this->status ="recurring";
		}
		return $this->status; 
	}
	
	function setStatus($status){
		$this->status = $status;
	}

	function getConfirmed() { 
		return; 
	}

	function getCalRecu() { 
		return array(); 
	}
	
	function getCalNumber() { 
		return $this->calnumber; 
	}
	
	function setCalNumber($calnumber) { 
		$this->calnumber = $calnumber; 
	}
	
	function getCalName() { 
		return $this->calname; 
	}
	
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
	 	if($bymonth=='all' || in_array('all',$this->bymonth)){
	 		$this->bymonth = array(1,2,3,4,5,6,7,8,9,10,11,12);
	 	}
	 }
	 
	 function getByDay() {
	 	return $this->byday;
	 }
	 
	 function setByday($byday) {
	 	if($byday!=''){
	 		$this->byday = split (',', $byday);
	 	}
	 	if($byday=='all' || in_array('all',$this->byday)){
	 		$this->byday = array('mo','tu','we','th','fr','sa','su');
	 	}
	 }
	 
	 function getByMonthDay() {
	 	return $this->bymonthday;
	 }
	 
	 function setByMonthday($bymonthday) {
	 	if($bymonthday!=''){
	 		$this->bymonthday = split (',', $bymonthday);
	 	}
	 	if($bymonthday=='all' || in_array('all',$this->bymonthday)){
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
	 
	 function setBySerPos($bysetpos){
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
	  * Returns the category
	  */
	 function getCategory() {
	 	return $this->category;
	 }
	 
	 /**
	  * Sets the category
	  * @param	$cat	string	A string representation of the category
	  */
	 function setCategory($cat){
	 	$this->category = $cat;
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
	  * Sets the headercolor
	  * @param	$color	string	A html color description
	  */
	 function setHeaderColor($color){
	 	$this->headercolor = $color;
	 }
	 
	 /**
	  * Returns the headercolor. A html color description
	  */
	 function getHeaderColor(){
	 	return $this->headercolor;	
	 }
	 
	 /**
	  * Sets the bodycolor
	  * @param	$color	string	A html color description
	  */
	 function setBodyColor($color){
	 	$this->bodycolor = $color;
	 }
	 
	 /**
	  * Returns the bodycolor. A html color description
	  */
	 function getBodyColor(){
	 	return $this->bodycolor;	
	 }
	 
	 /**
	  * Sets the headertextcolor
	  * @param	$color	string	A html color description
	  */
	 function setHeaderTextColor($color){
	 	$this->headertextcolor = $color;
	 }
	 
	 /**
	  * Returns the headertextcolor. A html color description
	  */
	 function getHeaderTextColor(){
	 	return $this->headertextcolor;	
	 }
	 
	 /**
	  * Sets the bodytextcolor
	  * @param	$color	string	A html color description
	  */
	 function setBodyTextColor($color){
	 	$this->bodytextcolor = $color;
	 }
	 
	 /**
	  * Returns the bodytextcolor. A html color description
	  */
	 function getBodyTextColor(){
	 	return $this->bodytextcolor;	
	 }
	 
	 /**
	  * Sets the organizer_id
	  * @param	$id		int		The new id
	  */
	 function setOrganizer_id($id){
	 	$this->organizer_id = $id;
	 }
	 
	 /**
	  * Returns the organizer_id
	  */
	 function getOrganizer_id(){
	 	return $this->organizer_id;	
	 }
	 
	 /**
	  * Sets the organizerlink
	  * @param	$id		string		A html link to an organizer
	  */
	 function setOrganizerLink($id){
	 	$this->organizerlink = $id;
	 }
	 
	 /**
	  * Return the organizerlink. A html link to an organizer
	  */
	 function getOrganizerLink(){
	 	return $this->organizerlink;	
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
	  * Sets the category_id
	  * @param	$id		int		The new id
	  */
	 function setCategoryId($id){
	 	$this->category_id = $id;
	 }
	 
	 /**
	  * Returns the category_id
	  */
	 function getCategoryId(){
	 	return $this->category_id;	
	 }
	 
	 /**
	  * Adds an id to the exception_single_ids array
	  * @param	$id		int		The id to be added
	  */
	 function addExceptionSingleId($id){
	 	array_push($this->exception_single_ids,$id);
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
	 	array_push($this->exception_group_ids,$id);
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
	 	array_push($this->notify_group_ids,$id);
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
	 	array_push($this->creator_group_ids,$id);
	 }
	 
	 /**
	  * Returns the creator_group_ids array
	  */
	 function getCreatorGroupIds(){
	 	return $this->creator_group_ids;	
	 }
	 
	 /**
	  * This function looks, if the event is a recurring event
	  * and creates the recurrings events for a given time.
	  * The starting and ending dates are calculated from the conf 
	  * array ('gedate' and 'view').
	  * 
	  * @param		$conf	array		The configuration array
	  * @param		$event	object		Instance of this class (tx_cal_model)
	  */
	 function recurringEvent(&$conf, $event){		
		$master_array = array();
		$uid_counter = 0;
		$except_dates 	= array();
		$except_times 	= array();
		$first_duration = TRUE;
		if($event->getCount()!=0){
			$count 			=  $event->getCount();
		}else{
			$count 			= 1000;
		}
		$valarm_set 	= FALSE;
		$attendee		= array();
		$organizer		= array();

		$start_time			= $event->getStartHour();
		$end_time			= $event->getEndHour();
		$start_date			= $event->getStartDate();
		$end_date			= $event->getEndDate();
		

		$summary 			= $event->getSummary();
		$start_unixtime 	= $event->getStarttime();
		$the_duration		= $event->getDuration();
		$rrule_array		= $event->getRecurringRule();
		$description		= $event->getDescription();
		$url				= $event->getUrl();
		$valarm_description = $event->getVAlarmDescription();
		$end_unixtime		= $event->getEndtime();
		if($end_unixtime==0){
			$end_unixtime = $start_unixtime;
		}
		$recurrence_id		= $event->getRecurrance();
		$uid				= $event->getUid();
		$class				= $event->getClass();
		$location			= $event->getLocation();
		$until				= $event->getUntil();
		$bymonth			= $event->getByMonth();
		$byday				= $event->getByDay();
		$bymonthday			= $event->getByMonthDay();
		$byweek				= $event->getByWeekDay();
		$byweekno 			= $event->getByWeekNo();
		$byminute			= $event->getByMinute();
		$byhour				= $event->getByHour();
		$bysecond			= $event->getBySecond();
		$byyearday			= $event->getByYearDay();
		$bysetpos			= $event->getBySetPos();
		$wkst				= $event->getWkst();
		$number				= $event->getInterval();
		
		$current_view = $conf['view'];
		$getdate = $conf['getdate'];
		
		
		// what date we want to get data for (for day calendar)
		if (!isset($getdate) || $getdate == '') $getdate = date('Ymd');
		preg_match ("/([0-9]{4})([0-9]{2})([0-9]{2})/", $getdate, $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
					
		if (!isset($url)) $url = '';
		if (!isset($type)) $type = '';

		// Handle DURATION
		if (!isset($end_unixtime) && isset($the_duration)) {
			$end_unixtime 	= $start_unixtime + $the_duration;
			$end_time 	= date ('Hi', $end_unixtime);
		}
			
		// CLASS support
		if (isset($class)) {
			if ($class == 'PRIVATE') {
				$summary ='**PRIVATE**';
				$description ='**PRIVATE**';
			} elseif ($class == 'CONFIDENTIAL') {
				$summary ='**CONFIDENTIAL**';
				$description ='**CONFIDENTIAL**';
			}
		}	 
		
		// make sure we have some value for $uid
		if (!isset($uid)) {
			$uid = $uid_counter;
			$uid_counter++;
			$uid_valid = false;
		} else {
			$uid_valid = true;
		}

		if ($uid_valid && isset($processed[$uid]) && isset($recurrence_id['date'])) {

			$old_start_date = $processed[$uid][0];
			$old_start_time = $processed[$uid][1];

			if ($recurrence_id['value'] == 'DATE') $old_start_time = '-1';
				$start_date_tmp = $recurrence_id['date'];

			//removeOverlap($start_date_tmp, $old_start_time, $uid);
			if (isset($master_array[date("Ymd",$start_date_tmp)][$old_start_time][$uid])) {
				unset($master_array[date("Ymd",$start_date_tmp)][$old_start_time][$uid]);  // SJBO added $uid twice here
				if (sizeof($master_array[date("Ymd",$start_date_tmp)][$old_start_time]) == 0) {
					unset($master_array[date("Ymd",$start_date_tmp)][$old_start_time]);
				}
			}
			
			$write_processed = false;
		} else {
			$write_processed = true;
		}	
		$mArray_begin = gmmktime (0,0,0,12,21,($this_year - 1));
		$mArray_end = gmmktime (0,0,0,1,12,($this_year + 1));	

		if (isset($start_time) && isset($end_time)) {
			// Mozilla style all-day events or just really long events
			if (($end_time - $start_time) > 2345 || $end_time==$start_time) {
				$alldayStart = $start_date;
				$alldayEnd = ($start_date + 60*60*24);
			}
		}
		if (isset($start_unixtime,$end_unixtime) && (date('Ymd',$start_unixtime) == date('Ymd',$end_unixtime))==true && $start_time == $end_time) {
			$event->setSpansDay(1);
			$bleed_check = (($start_unixtime - $end_unixtime) < (60*60*24)) ? '-1' : '0';
		} else {
			$event->setSpansDay(0);
			$bleed_check = 0;
		}
		if (isset($start_time) && $start_time != '') {
			preg_match ('/([0-9]{2})([0-9]{2})/', $start_time, $time);
			preg_match ('/([0-9]{2})([0-9]{2})/', $end_time, $time2);
			if (isset($start_unixtime) && isset($end_unixtime)) {
				$length = $end_unixtime - $start_unixtime;
			} else {
				$length = ($time2[1]*60+$time2[2]) - ($time[1]*60+$time[2]);
			}	
			$drawKey = drawEventTimes($start_time, $end_time, $conf['view.']['day.']['gridLength']); 
			preg_match ('/([0-9]{2})([0-9]{2})/', $drawKey['draw_start'], $time3);
			$hour = $time3[1];
			$minute = $time3[2];
		}

		// RECURRENCE-ID Support
		if (isset($recurrence_d)) {		
			$recurrence_delete["$recurrence_d"]["$recurrence_t"] = $uid;
		}			
		// handle single changes in recurring events
		// Maybe this is no longer need since done at bottom of parser? - CL 11/20/02
		if ($uid_valid && $write_processed) {
			if (!isset($hour)) $hour = 00;
			if (!isset($minute)) $minute = 00;
			$processed[$uid] = array($start_date,($hour.$minute), $type);
		}
		// Handling of the all day events
		if ((isset($alldayStart) && $alldayStart != '')) {
			$start = $alldayStart;
			if ($event->getSpansDay()) {
				//$alldayEnd = $end_unixtime;
			}
			if (isset($alldayEnd)) {
				$end = $alldayEnd;
			} else {
				$end = strtotime('+1 day', $start);
			}
			// Changed for 1.0, basically write out the entire event if it starts while the array is written.
			if (($start < $mArray_end) && ($start < $end)) {

				while (($start != $end) && ($start < $mArray_end)) {
					$start_date2 = date('Ymd', $start);
					$master_array[$start_date2][('-1')][$uid]= $event;
					$start = strtotime('+1 day', $start);

				}
				if (!$write_processed) $master_array[date("Ymd",$start_date)]['-1'][$uid]['exception'] = true;
			}
		}	
		// Handling regular events
		if ((isset($start_time) && $start_time != '') && (!isset($alldayStart) || $alldayStart == '')) {
			if (($bleed_check == '-1')) {//($end_time >= $bleed_time) && 
				$start_tmp = strtotime(date('Ymd',$start_unixtime));
				$end_date_tmp = date('Ymd',$end_unixtime);
				while ($start_tmp <= $end_unixtime) {
					$start_date_tmp = date('Ymd',$start_tmp);
					if ($start_tmp == $start_date) {
//						$hour = "-1";
//						$minute = "";
						$time_tmp = $hour.$minute;
						$start_time_tmp = $start_time;
					} else {
						$time_tmp = '0000';
						$start_time_tmp = '0000';
					}
					if ($start_date_tmp == $end_date_tmp) {
						$end_time_tmp = $end_time;
					} else {
						$end_time_tmp = '2400';
						$display_end_tmp = $end_time;
					}
					$master_array[$start_date_tmp][$time_tmp][$uid] = $event;
					$start_tmp = strtotime('+1 day',$start_tmp);
				}
			} else {
				if ($bleed_check == '-1') {
					$display_end_tmp = $end_time;
					$end_time_tmp1 = '2400';	
				}
				if (!isset($end_time_tmp1)) $end_time_tmp1 = $end_time;
				// This if statement should prevent writing of an excluded date if its the first recurrance - CL
				if (!in_array($start_date, $except_dates)) {
					$master_array[date("Ymd",$start_date)][($hour.$minute)][$uid] = $event;
					if (!$write_processed) $master_array[date("Ymd",$start_date)][($hour.$minute)][$uid]['exception'] = true;
				}
			}
		}
		// Handling of the recurring events, RRULE
		if (isset($rrule_array) && is_array($rrule_array)) {

			if (isset($alldayStart) && $alldayStart != '') {
				$hour = '-';
				$minute = '1';
				$rrule_array['START_DAY'] = $alldayStart;
				$rrule_array['END_DAY'] = $alldayEnd;
				$rrule_array['END'] = 'end';
				$recur_start = $alldayStart;
				$start_date = $alldayStart;
				if (isset($alldayEnd)) {
					$diff_allday_days = tx_cal_calendar::dayCompare($alldayEnd, $alldayStart);
				 } else {
					$diff_allday_days = 1;
				}
			} else {
				$rrule_array['START_DATE'] = $start_date;
				$rrule_array['START_TIME'] = $start_time;
				$rrule_array['END_TIME'] = $end_time;
				$rrule_array['END'] = 'end';
			}
		
			$start_date_time = $start_date;
			$this_month_start_time = strtotime($this_year.$this_month.'01');
			if ($current_view == 'year' || ($save_parsed_cals == 'yes' && !$is_webcal)) {
				$start_range_time = strtotime($this_year.'-01-01 -2 weeks');
				$end_range_time = strtotime($this_year.'-12-31 +2 weeks');
			} else if ($current_view == 'list') {
				$start_range_time = strtotime($conf["view."]["list."]['starttime']);
				$end_range_time = strtotime($conf["view."]["list."]['endtime']);
			} else {
				$start_range_time = strtotime('-1 month -2 day', $this_month_start_time);
				$end_range_time = strtotime('+2 month +2 day', $this_month_start_time);
			}

			$recur = $master_array[date("Ymd",$start_date)][($hour.$minute)][$uid]->getRecurringRule();
			// Modify the COUNT based on BYDAY
			if ((is_array($byday)) && !empty($byday) && (isset($count))) {
				$blah = sizeof($byday);
				$count = ($count / $blah);
				unset ($blah);
			}
			if (!isset($number)) $number = 2;
			// if $until isn't set yet, we set it to the end of our range we're looking at		
			if (!isset($until) || $until=='') $until = $end_range_time;
			if (!isset($abs_until)) $abs_until = date('YmdHis', $end_range_time);
			$end_date_time = $until;
			$start_range_time_tmp = $start_range_time;
			$end_range_time_tmp = $end_range_time;
	
			// If the $end_range_time is less than the $start_date_time, or $start_range_time is greater
			// than $end_date_time, we may as well forget the whole thing
			// It doesn't do us any good to spend time adding data we aren't even looking at
			// this will prevent the year view from taking way longer than it needs to
			if ($end_range_time_tmp >= $start_date_time && $start_range_time_tmp <= $end_date_time) {		
				// if the beginning of our range is less than the start of the item, we may as well set it equal to it
				if ($start_range_time_tmp < $start_date_time) $start_range_time_tmp = $start_date_time;
				if ($end_range_time_tmp > $end_date_time) $end_range_time_tmp = $end_date_time;
	
				// initialize the time we will increment
				$next_range_time = $start_range_time_tmp;
			
				// FIXME: This is a hack to fix repetitions with $interval > 1 
				if ($count > 1 && $number > 1) $count = 1 + ($count - 1) * $number; 
				$count_to = 0;
				$freq_type = $rrule_array['FREQ'];
				// start at the $start_range and go until we hit the end of our range.
				while (($next_range_time >= $start_range_time_tmp) && ($next_range_time <= $end_range_time_tmp) && ($count_to != $count)) {

					$func = $freq_type.'Compare';
					$diff = tx_cal_calendar::$func($next_range_time, $start_date, $conf["view."]["week."]['weekStartDay']);
					if ($diff < $count) {
						if ($diff % $number == 0) {
							$interval = $number;
							switch ($rrule_array['FREQ']) {
								case 'day':
									$next_date_time = $next_range_time;
									$recur_data[] = $next_date_time;
									break;
								case 'week':
									// Populate $byday with the default day if it's not set.
									if (empty($byday)) {
										//$test = tx_cal_controller::getDaysOfWeekReallyShort();
										$daysofweek = $this->getDaysOfWeekShort();
										$byday[] = strtoupper(substr($daysofweek[date('w', $start_date_time)], 0, 2));//
									}
									if (is_array($byday)) {
										foreach($byday as $day) {
											$day = tx_cal_calendar::two2threeCharDays(strtoupper($day));									
											$next_date_time = strtotime($day,$next_range_time);// + (12 * 60 * 60);
											// Since this renders events from $next_range_time to $next_range_time + 1 week, I need to handle intervals
											// as well. This checks to see if $next_date_time is after $dayStart (i.e., "next week"), and thus
											// if we need to add $interval weeks to $next_date_time.
											if ($next_date_time > strtotime($conf["view."]["week."]['weekStartDay'], $next_range_time) && $interval > 1) {
												$next_date_time = strtotime('+'.($interval - 1).' '.$freq_type, $next_date_time);
											}
											$recur_data[] = $next_date_time;
										}
									}
									break;
								case 'month':
									if (empty($bymonth)){
										$bymonth = array(1,2,3,4,5,6,7,8,9,10,11,12);
									}
									$next_range_time = strtotime(date('Y-m-01', $next_range_time));
									$next_date_time = $next_range_time;
									if(empty($bymonthday) && empty($byday)){
										$bymonthday = array(date('d', $event->getStarttime()));
									}

									if (isset($bymonthday) && !empty($bymonthday) && ((empty($byday)))) {
										foreach($bymonthday as $day) {
											if ($day < 0) $day = ((date('t', $next_range_time)) + ($day)) + 1;
											$year = date('Y', $next_range_time);
											$month = date('m', $next_range_time);
											if (checkdate($month,$day,$year)) {
												$next_date_time = gmmktime(0,0,0,$month,$day,$year);
												$recur_data[] = $next_date_time;
											}
										}
									} elseif (is_array($byday)) {
										foreach($byday as $day) {
											$day = strtoupper($day);
											ereg ('([-\+]{0,1})?([0-9]{1})?([A-Z]{2})', $day, $byday_arr);
											//Added for 2.0 when no modifier is set
											if ($byday_arr[2] != '') {
												$nth = $byday_arr[2]-1;
											} else {
												$nth = 0;
											}
											$on_day = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]));
											$on_day_num = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]),false);
											if ((isset($byday_arr[1])) && ($byday_arr[1] == '-')) {
												$last_day_tmp = date('t',$next_range_time);
												$next_range_time = strtotime(date('Y-m-'.$last_day_tmp, $next_range_time));
												$last_tmp = (date('w',$next_range_time) == $on_day_num) ? '' : 'last ';
												$next_date_time = strtotime($last_tmp.$on_day.' -'.$nth.' week', $next_range_time);
												$month = date('m', $next_date_time);
												if (in_array($month, $bymonth)) {
													$recur_data[] = $next_date_time;
												}
											} elseif (isset($bymonthday) && (!empty($bymonthday))) {
												// This supports MONTHLY where BYDAY and BYMONTH are both set
												foreach($bymonthday as $day) {
													$year 	= date('Y', $next_range_time);
													$month 	= date('m', $next_range_time);
													if (checkdate($month,$day,$year)) {
														$next_date_time = gmmktime(0,0,0,$month,$day,$year);
														$daday = strtolower(strftime("%a", $next_date_time));
														if ($daday == $on_day && in_array($month, $bymonth)) {
															$recur_data[] = $next_date_time;
														}
													}
												}
											} elseif ((isset($byday_arr[1])) && ($byday_arr[1] != '-')) {
												$next_date_time = strtotime($on_day.' +'.$nth.' week', $next_range_time);
												$month = date('m', $next_date_time);
												if (in_array($month, $bymonth)) {
													$recur_data[] = $next_date_time;
												}
											}
											$next_date = date('Ymd', $next_date_time);
										}
									}
									break;
								case 'year':
									if (empty($bymonth)) {
										$m = date('m', $start_date_time);
										$bymonth = array($m);
									}	
									foreach($bymonth as $month) {
										// Make sure the month & year used is within the start/end_range.
										if ($month < date('m', $next_range_time)) {
											$year = date('Y', strtotime('+1 years', $next_range_time));
										} else {
											$year = date('Y', $next_range_time);
										}

										if (!empty($byday)) {
											$checkdate_time = gmmktime(0,0,0,$month,1,$year);
											foreach($byday as $day) {
												ereg ('([-\+]{0,1})?([0-9]{1})?([A-Z]{2})', $day, $byday_arr);
												if ($byday_arr[2] != '') {
													$nth = $byday_arr[2]-1;
												} else {
													$nth = 0;
												}
												$on_day = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3]));
												$on_day_num = tx_cal_calendar::two2threeCharDays(strtoupper($byday_arr[3],false));
												if ($byday_arr[1] == '-') {
													$last_day_tmp = date('t',$checkdate_time);
													$checkdate_time = strtotime(date('Y-m-'.$last_day_tmp, $checkdate_time));
													$last_tmp = (date('w',$checkdate_time) == $on_day_num) ? '' : 'last ';
													$next_date_time = strtotime($last_tmp.$on_day.' -'.$nth.' week', $checkdate_time);
												} else {															
													$next_date_time = strtotime($on_day.' +'.$nth.' week', $checkdate_time);
												}
											}
										} else {
											$day 	= date('d', $start_date_time);
											$next_date_time = gmmktime(0,0,0,$month,$day,$year);
										}
										$recur_data[] = $next_date_time;
									}
									if (!empty($byyearday)) {
										foreach ($byyearday as $yearday) {
											ereg ('([-\+]{0,1})?([0-9]{1,3})', $yearday, $byyearday_arr);
											if ($byyearday_arr[1] == '-') {
												$ydtime = gmmktime(0,0,0,12,31,$this_year);
												$yearnum = $byyearday_arr[2] - 1;
												$next_date_time = strtotime('-'.$yearnum.' days', $ydtime);
											} else {
												$ydtime = gmmktime(0,0,0,1,1,$this_year);
												$yearnum = $byyearday_arr[2] - 1;
												$next_date_time = strtotime('+'.$yearnum.' days', $ydtime);
											}
											$recur_data[] = $next_date_time;
										}
									} 

									break;
								default:
									// anything else we need to end the loop
									$next_range_time = $end_range_time_tmp + 100;
									$count_to = $count;
							}
						} else {
							$interval = 1;
						}
						$next_range_time = strtotime('+'.$interval.' '.$freq_type, $next_range_time);
					} else {
						// end the loop because we aren't going to write this event anyway
						$count_to = $count;
					}
					// use the same code to write the data instead of always changing it 5 times						
					if (isset($recur_data) && is_array($recur_data)) {
						$recur_data_hour = @substr($start_time,0,2);
						$recur_data_minute = @substr($start_time,2,2);
						foreach($recur_data as $recur_data_time) {
							$recur_data_year = date('Y', $recur_data_time);
							$recur_data_month = date('m', $recur_data_time);
							$recur_data_day = date('d', $recur_data_time);
							$recur_data_date = $recur_data_year.$recur_data_month.$recur_data_day;

							if (($recur_data_time > $start_date_time) && ($recur_data_time <= $end_date_time) && ($count_to != $count) && !in_array($recur_data_date, $except_dates)) {
								if (isset($alldayStart) && $alldayStart != '') {
									$start_time2 = $recur_data_time;
									$end_time2 = strtotime('+'.$diff_allday_days.' days', $recur_data_time);
									while ($start_time2 < $end_time2) {
										$start_date2 = date('Ymd', $start_time2);
										$master_array[$start_date2][('-1')][$uid] = $event;//array ('event_text' => $summary, 'description' => $description, 'location' => $location, 'organizer' => serialize($organizer), 'attendee' => serialize($attendee), 'calnumber' => $calnumber, 'calname' => $actual_calname, 'url' => $url, 'status' => $status, 'class' => $class, 'recur' => $recur );
										$start_time2 = strtotime('+1 day', $start_time2);
									}
								} else {
									$start_unixtime_tmp = mktime($recur_data_hour,$recur_data_minute,0,$recur_data_month,$recur_data_day,$recur_data_year);
									$end_unixtime_tmp = $start_unixtime_tmp + $length;
									
									if (($end_time >= $bleed_time) && ($bleed_check == '-1')) {
										$start_tmp = strtotime(date('Ymd',$start_unixtime_tmp));
										$end_date_tmp = date('Ymd',$end_unixtime_tmp);
										while ($start_tmp < $end_unixtime_tmp) {
											$start_date_tmp = date('Ymd',$start_tmp);
											if ($start_date_tmp == $recur_data_year.$recur_data_month.$recur_data_day) {
												$time_tmp = $hour.$minute;
												$start_time_tmp = $start_time;
											} else {
												$time_tmp = '0000';
												$start_time_tmp = '0000';
											}
											if ($start_date_tmp == $end_date_tmp) {
												$end_time_tmp = $end_time;
											} else {
												$end_time_tmp = '2400';
												$display_end_tmp = $end_time;
											}
											
											// Let's double check the until to not write past it
											$until_check = $start_date_tmp.$time_tmp.'00';
											if ($abs_until > $until_check) {
												$new_event = t3lib_div::makeInstance(get_class($event));
												$new_event->setStarttime($start_unixtime_tmp);
												if($event->getEndtime()!="0"){
													$new_event->setEndtime($end_unixtime_tmp);
												}else{
													$new_event->setEndtime($new_event->getStarttime());
												}
												$new_event->setDisplayEnd($display_end_tmp);
												$new_event->setSummary($summary);
												$new_event->setUID($uid);
												$new_event->setOverlap(0);
												$new_event->setTitle($event->getTitle());
												$new_event->setType($event->getType());
												$new_event->setDescription($description);
												$new_event->setStatus($event->getStatus());
												$new_event->setClass($class);
												$new_event->setSpansDay(true);
												$new_event->setLocation($location);
												$new_event->setOrganizer($event->getOrganizer());
												$new_event->setAttendee(serialize($attendee));
												$new_event->setCalName($event->getCalName());
												$new_event->setUrl($url);
												$new_event->setRecur($recur);
												$new_event->setHeaderColor($event->getHeaderColor());
												$new_event->setBodyColor($event->getBodyColor());
												$new_event->setCategory($event->getCategory());
												$master_array[$start_date_tmp][$time_tmp][$uid] = $new_event; 
											}
											$start_tmp = strtotime('+1 day',$start_tmp);
										}
									} else {
										if ($bleed_check == '-1') {
											$display_end_tmp = $end_time;
											$end_time_tmp1 = '2400';
												
										}
										if (!isset($end_time_tmp1)) $end_time_tmp1 = $end_time;
									
										// Let's double check the until to not write past it
										$until_check = $recur_data_date.$hour.$minute.'00';
										if ($abs_until > $until_check) {
											$new_event = t3lib_div::makeInstance(get_class($event));
											$new_event->setStarttime($start_unixtime_tmp);
											if($event->getEndtime()>0){
												$new_event->setEndtime($end_unixtime_tmp);
											}else{
												$new_event->setEndtime($new_event->getStarttime());
											}
											$new_event->setDisplayEnd($display_end_tmp);
											$new_event->setSummary($summary);
											$new_event->setUID($uid);
											$new_event->setOverlap(0);
											$new_event->setTitle($event->getTitle());
											$new_event->setType($event->getType());
											$new_event->setDescription($description);
											$new_event->setStatus($event->getStatus());
											$new_event->setClass($class);
											$new_event->setSpansDay(true);
											$new_event->setLocation($location);
											$new_event->setOrganizer($event->getOrganizer());
											$new_event->setAttendee(serialize($attendee));
											$new_event->setCalName($event->getCalName());
											$new_event->setUrl($url);
											$new_event->setRecur($recur);
											$new_event->setBodyColor($event->getBodyColor());
											$new_event->setHeaderColor($event->getHeaderColor());
											$new_event->setCategory($event->getCategory());
											$master_array[$recur_data_date][($hour.$minute)][$uid] = $new_event;
										}
										
									}
								}
							}
						}
						unset($recur_data);
					}
				}
			}
		}

		// This should remove any exdates that were missed.
		// Added for version 0.9.5
		if (is_array($except_dates)) {
			foreach ($except_dates as $key => $value) {
				$time = $except_times[$key];
				unset($master_array[date("Ymd",$value)][$time][$uid]);
				if (count($master_array[date("Ymd",$value)][$time]) < 1) {
					unset($master_array[date("Ymd",$value)][$time]);
					if (count($master_array[date("Ymd",$value)]) < 1) {
						unset($master_array[date("Ymd",$value)]);	
					}
				}
			}
		}
		return $master_array;
	}
	
	/**
	 * This function merges an array of events with another array of events.
	 * The structure is: [date][time][event]
	 * @param	$events		array where the events should be added into
	 * @param	$events_tmp	array which is supposed to be merged
	 */
	function mergeEvents(&$events, &$events_tmp){
		foreach ($events_tmp as $event_tmp_key => $event_tmp) {
			if(array_key_exists($event_tmp_key,$events)==1){
				foreach($event_tmp as $event_tmp_timekey => $event_tmp_time) {
					if(array_key_exists($event_tmp_timekey,$events[$event_tmp_key])){
						$events[$event_tmp_key][$event_tmp_timekey] = $events[$event_tmp_key][$event_tmp_timekey] + $event_tmp_time;
					} else {
						$events[$event_tmp_key][$event_tmp_timekey] = $event_tmp_time;
					}
				}
			} else {
				$events[$event_tmp_key] = $event_tmp;
			}
		}
	}
	
	/**
	 * This function removes an array of events from another array of events.
	 * The structure is: [date][time][event]
	 * @param	$events		array where the events should be deleted from
	 * @param	$events_tmp	array which is supposed to be deleted
	 */
	function removeEvents(&$events_tmp, &$ex_events){
		foreach ($events_tmp as $event_tmp_key => $event_tmp) {
			if(array_key_exists($event_tmp_key,$ex_events)==1){
				array_splice($events_tmp[$event_tmp_key], 0);
			}
		}
	}
	
	/**
	 * This function returns an array of weekdays (english)
	 */
	function getDaysOfWeekShort() {
		return array ('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
	}
	
	function getValuesAsArray(){
		return array(
			"uid"=>$this->uid,
			"title"=>$this->title,
			"description"=>$this->description,
			"organizer"=>$this->organizer,
			"organizer_id"=>$this->organizer_id,
			"category"=>$this->category,
			"location"=>$this->location,
			"location_id"=>$this->location_id,
			"startdate"=>$this->startdate,
			"starthour"=>$this->starthour,
			"enddate"=>$this->enddate,
			"endhour"=>$this->endhour,
			"starttime"=>$this->starttime,
			"endtime"=>$this->endtimes,);	
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
	 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_model.php']);
}
?>