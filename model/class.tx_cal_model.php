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
	var $categories = array();
	var $categoriesAsString;
	var $categoryUidsAsArray;
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
	var $headerstyle = "default_categoryheader";//"#557CA3";//"#0000ff";
	var $bodystyle = "default_categorybody";//""#6699CC";//"#ccffcc";

	/* new */
	var $event_type;
	var $page;
	var $ext_url;
	/* new */
	
	var $image;
	var $imageCation;
	var $imageTitleText;
	var $imageAltText;
	
	var $externalPlugin = 0;
	
	var $sharedUsers;
	var $eventOwner;
	
	function tx_cal_model(&$controller, $serviceKey){
		$this->tx_cal_base_model($controller, $serviceKey);
	}
	
	/**
	 *  Finds all events within a given range.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAllWithin($starttime, $endtime, $pidList) {}
	
	/**
	 *  Finds all events.
	 *
	 *  @return		array			The array of events represented by the model.
	 */
	function findAll($pidList) {}
	
	/**
	 *  Finds a single event.
	 *
	 *  @return		object			The event represented by the model.
	 */
	function find($uid, $pidList) {}
	
	function getHidden() {
		return $this->hidden;
	}

	function setHidden($h) {
		$this->hidden = $h;
	}
	
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
	
	function renderEvent(){
		
		$d = nl2br($this->cObj->parseFunc($this->getDescription(),$this->conf["parseFunc."]));
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
	
	function renderEventForList(){
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
	
	function getCalendarUid() { 
		return $this->calendarUid; 
	}
	
	function setCalendarUid($uid) { 
		$this->calendarUid = $uid; 
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
	 	if($style!=""){
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
	 	if($style!=""){
	 		$this->bodystyle = $style;
	 	}
	 }
	 
	 /**
	  * Returns the bodystyle name
	  */
	 function getBodyStyle(){
	 	return $this->bodystyle;	
	 }
	 
	 /**
	  * Sets the images
	  * @param	$images	blob	One or more images
	  */
	 function setImage($image){
	 	if($image!=""){
	 		$this->image = $image;
	 	}
	 }
	 
	 /**
	  * Returns the image blob
	  */
	 function getImage(){
	 	return $this->image;	
	 }
	 
	 /**
	  * Sets the image alt text
	  * @param	$text	String	the image alt text(s)
	  */
	 function setImageAltText($text){
	 	if($text!=""){
	 		$this->imageAltText = $text;
	 	}
	 }
	 
	 /**
	  * Returns the image alt text(s)
	  */
	 function getImageAltText(){
	 	return $this->imageAltText;	
	 }
	 
	 /**
	  * Sets the image title text
	  * @param	$text	String	the image title text(s)
	  */
	 function setImageTitleText($text){
	 	if($text!=""){
	 		$this->imageTitleText = $text;
	 	}
	 }
	 
	 /**
	  * Returns the image title text(s)
	  */
	 function getImageTitleText(){
	 	return $this->imageTitleText;	
	 }
	 
	 /**
	  * Sets the image caption
	  * @param	$text	String	the image caption
	  */
	 function setImageCaption($text){
	 	if($text!=""){
	 		$this->imageCaption = $text;
	 	}
	 }
	 
	 /**
	  * Returns the image caption
	  */
	 function getImageCaption(){
	 	return $this->imageCaption;	
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
			"uid"=>$this->uid,
			"title"=>$this->title,
			"hidden"=>$this->hidden,
			"description"=>$this->description,
			"calendar_id"=>$this->calendarUid,
			"organizer"=>$this->organizer,
			"organizer_id"=>$this->organizer_id,
			"categories"=>$this->categories,
			"location"=>$this->location,
			"location_id"=>$this->location_id,
			"startdate"=>$this->startdate,
			"starthour"=>$this->starthour,
			"enddate"=>$this->enddate,
			"endhour"=>$this->endhour,
			"starttime"=>$this->starttime,
			"endtime"=>$this->endtimes,
			"category_headerstyle"=>$this->headerstyle,
			"category_bodystyle"=>$this->bodystyle,
			"exception_single_ids"=>$this->exception_single_ids,
			"exception_group_ids"=>$this->exception_group_ids,
			"image" => $this->image,
			"imagecaption" => $this->imagecaption,
			"imagealttext" => $this->imagealttext,
			"imagetitletext" => $this->imagetitletext,
		);
	}
	
	function getCategoriesAsString($asLink = true){
		if($this->categoriesAsString){
			return $this->categoriesAsString;
		}
		$first = true;
		$this->categoriesAsString = "";
		$this->tempATagParam = $GLOBALS['TSFE']->ATagParams;
		if(!empty($this->categories)){
			foreach($this->getCategories() as $categoryArray){
				$GLOBALS['TSFE']->ATagParams = 'title="'.$categoryArray['title'].'"';
				$parameter["category"]=$categoryArray['uid'];
				if($first){
					if($asLink){
						$this->categoriesAsString .= $this->controller->pi_linkTP_keepPIvars($categoryArray['title'], $parameter, $this->conf['cache'], $this->conf['clear_anyway']);
					}else{
						$this->categoriesAsString .= $categoryArray['title'];
					}
					$first = false;
				}else{
					if($asLink){
						$this->categoriesAsString .= ", ".$this->controller->pi_linkTP_keepPIvars($categoryArray['title'], $parameter, $this->conf['cache'], $this->conf['clear_anyway']);
					}else{
						$this->categoriesAsString .= ", ".$categoryArray['title'];
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
		foreach($this->getCategories() as $categoryArray){
			if($first){
				$this->categoryUidsAsArray[] = $categoryArray['uid'];
				$first = false;
			}else{
				$this->categoryUidsAsArray[] = $categoryArray['uid'];
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
	
	function getImageMarkers(&$markerArray, $lConf, $isSingleView=false) {
		// overwrite image sizes from TS with the values from the content-element if they exist.
		if ($this->conf['FFimgH'] || $this->conf['FFimgW']) {
			$lConf['image.']['file.']['maxW'] = $this->conf['FFimgW'];
			$lConf['image.']['file.']['maxH'] = $this->conf['FFimgH'];
		}

		if ($this->conf['imageMarkerFunc']) {
			$markerArray = $this->userProcess('imageMarkerFunc', array($markerArray, $lConf));
		} else {

			$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
			$imageNum = t3lib_div::intInRange($imageNum, 0, 100);
			$theImgCode = '';
			$imgs = t3lib_div::trimExplode(',', $this->getImage(), 1);
			$imgsCaptions = explode(chr(10), $this->getImageCaption());//$row['imagecaption']
			$imgsAltTexts = explode(chr(10), $this->getImageAltText());//$row['imagealttext']
			$imgsTitleTexts = explode(chr(10), $this->getImageTitleText);//$row['imagetitletext']
			reset($imgs);

			$cc = 0;
			// remove first img from the image array in single view if the TSvar firstImageIsPreview is set
			if (count($imgs) > 1 && $this->conf['firstImageIsPreview'] && $isSingleView) {
				array_shift($imgs);
				array_shift($imgsCaptions);
				array_shift($imgsAltTexts);
				array_shift($imgsTitleTexts);
			}
			// get img array parts for single view pages
			if ($this->piVars[$this->conf['singleViewPointerName']]) {
				$spage = $this->piVars[$this->conf['singleViewPointerName']];
				$astart = $imageNum*$spage;
				$imgs = array_slice($imgs,$astart,$imageNum);
				$imgsCaptions = array_slice($imgsCaptions,$astart,$imageNum);
				$imgsAltTexts = array_slice($imgsAltTexts,$astart,$imageNum);
				$imgsTitleTexts = array_slice($imgsTitleTexts,$astart,$imageNum);
			}
			while (list(, $val) = each($imgs)) {
				if ($cc == $imageNum) break;
				if ($val) {

					$lConf['image.']['altText'] = $imgsAltTexts[$cc];
					$lConf['image.']['titleText'] = $imgsTitleTexts[$cc];
					$lConf['image.']['file'] = 'uploads/pics/' . $val;
				}
				$theImgCode .= $this->cObj->IMAGE($lConf['image.']) . $this->cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				$cc++;
			}
			$markerArray['###EVENT_IMAGE###'] = '';
			if ($cc) {
				$markerArray['###EVENT_IMAGE###'] = $this->cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
			}
		}
		return $markerArray;
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