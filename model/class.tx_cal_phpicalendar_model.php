<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
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

require_once (t3lib_extMgm :: extPath('cal') . 'model/class.tx_cal_model.php');
require_once (t3lib_extMgm :: extPath('cal') . 'controller/class.tx_cal_calendar.php');


/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_phpicalendar_model extends tx_cal_model {

	var $location;
	var $isException;
	var $createUserId;
	var $isPreview = false;
	var $isTomorrow = false;
	var $teaser;
	var $limitAttendeeToThisEmail = '';
	var $timezone = 'UTC';
	var $cachedValueArray = Array();
	
	function tx_cal_phpicalendar_model($row, $isException, $serviceKey) {
		$this->tx_cal_model($serviceKey);
		
		if(is_array($row)) {
			$this->createEvent($row, $isException);
		}
		
		$this->isException = $isException;
		$this->type = 'tx_cal_phpicalendar';
		$this->objectType = 'event';
	}
	
	function updateWithPIVars(&$piVars) {
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$modelObj = &tx_cal_registry::Registry('basic','modelController');
		$controller = &tx_cal_registry::Registry('basic','controller');
		$dateIsSet = false;
		
		$customFieldArray = Array();
		if($this->conf['view']=='create_event' || $this->conf['view']=='edit_event'){
			$customFieldArray = t3lib_div::trimExplode(',',$this->conf['rights.'][$this->conf['view']=='create_event'?'create.':'edit.']['event.']['additionalFields'],1);
		}else if($this->conf['view']=='confirm_event'){
			if($this->row['uid']>0){
				$customFieldArray = t3lib_div::trimExplode(',',$this->conf['rights.']['edit.']['event.']['additionalFields'],1);
			} else {
				$customFieldArray = t3lib_div::trimExplode(',',$this->conf['rights.']['create.']['event.']['additionalFields'],1);
			}
		}

		include_once (t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

		foreach($piVars as $key => $value) {
			switch($key) {
				case 'calendar_id':
					$this->setCalendarUid(intval($piVars['calendar_id']));
					unset($piVars['calendar_id']);
					break;
				case 'category':
				case 'category_ids':
					$categories = array();
					$categoryService = &$modelObj->getServiceObjByKey('cal_category_model', 'category', 'tx_cal_category');
					$categoryService->getCategoryArray($this->conf['pidList'], $categories);
					$piVarsCaregoryArray = explode(',',$controller->convertLinkVarArrayToList($piVars[$key]));
					if(!empty($piVarsCaregoryArray)){
						foreach($piVarsCaregoryArray as $categoryId){
							$this->addCategory($categories[0][0][$categoryId]);
						}
					}
					unset($piVars['category']);
					unset($piVars['category_ids']);
					break;
				case 'allday':
					if(intval($piVars['allday'])==1){
						$this->setAllday(true);
					}else{
						$this->setAllday(false);
					}
					break;
				case 'startdate':
				case 'starttime':
				case 'startminutes':
					$start = new tx_cal_date(getYmdFromDateString($this->conf, strip_tags($piVars['startdate'])).'000000');
					if(strlen($piVars['starttime'])==4){
						$tempArray = Array();
						preg_match ('/([0-9]{2})([0-9]{2})/',$piVars['starttime'],$tempArray);
						$start->setHour(intval($tempArray[1]));
						$start->setMinute(intval($tempArray[2]));
					}else{
						$start->setHour(intval($piVars['starttime']));
						$start->setMinute(intval($piVars['startminutes']));
					}
					$start->setSecond(0);
					$start->setTZbyId('UTC');
					$this->setStart($start);
					unset($piVars['startdate']);
					unset($piVars['starttime']);
					unset($piVars['startminutes']);
					$dateIsSet = true;
					break;
				case 'enddate':
				case 'endtime':
				case 'endminutes':
					$end = new tx_cal_date(getYmdFromDateString($this->conf, strip_tags($piVars['enddate'])).'000000');
					if(strlen($piVars['endtime'])==4){
						$tempArray = Array();
						preg_match ('/([0-9]{2})([0-9]{2})/',$piVars['endtime'],$tempArray);
						$end->setHour(intval($tempArray[1]));
						$end->setMinute(intval($tempArray[2]));
					}else{
						$end->setHour(intval($piVars['endtime']));
						$end->setMinute(intval($piVars['endminutes']));
					}
					$end->setSecond(0);
					$end->setTzById('UTC');
					$this->setEnd($end);
					unset($piVars['enddate']);
					unset($piVars['endtime']);
					unset($piVars['endminutes']);
					$dateIsSet = true;
					break;
				case 'organizer':
					$this->setOrganizer(strip_tags($piVars['organizer']));
					unset($piVars['organizer']);
					break;
				case 'location':
					$this->setLocation(strip_tags($piVars['location']));
					unset($piVars['location']);
					break;
				case 'cal_organizer':
					$this->setOrganizerId(intval($piVars['cal_organizer']));
					unset($piVars['cal_organizer']);
					break;
				case 'cal_location':
					$this->setLocationId(intval($piVars['cal_location']));
					unset($piVars['cal_location']);
					break;
				case 'title':
					$this->setTitle(strip_tags($piVars['title']));
					unset($piVars['title']);
					break;
				case 'description':
					$this->setDescription($cObj->removeBadHTML($piVars['description'], array()));
					unset($piVars['description']);
					unset($piVars['_TRANSFORM_description']);
					break;
				case 'teaser':
					$this->setTeaser($cObj->removeBadHTML($piVars['teaser'], array()));
					unset($piVars['teaser']);
					break;
				case 'image':
					if(is_array($piVars['image'])) {
						foreach($piVars['image'] as $image){
							$this->addImage(strip_tags($image));
						}
					}
					break;
				case 'image_caption':
					$this->setImageCaption(explode(chr(10),$cObj->removeBadHTML($piVars['image_caption'],$this->conf)));
					unset($piVars['image_caption']);
					break;
				case 'image_alt':
					$this->setImageAltText(explode(chr(10),$cObj->removeBadHTML($piVars['image_alt'],$this->conf)));
					unset($piVars['image_alt']);
					break;
				case 'image_title':
					$this->setImageTitleText(explode(chr(10),$cObj->removeBadHTML($piVars['image_title'],$this->conf)));
					unset($piVars['image_title']);
					break;
				case 'attachment':
					$this->setAttachment(array());
					if(is_array($piVars['attachment'])) {
						foreach($piVars['attachment'] as $attachment){
							$this->addAttachment(strip_tags($attachment));
						}
					}
					break;
				case 'attachment_caption':
					$this->setAttachmentCaption(explode(chr(10),$cObj->removeBadHTML($piVars['attachment_caption'],$this->conf)));
					unset($piVars['attachment_caption']);
					break;
				case 'frequency_id':
					$valueArray = array('none','day','week','month','year');
					$this->setFreq(in_array($piVars['frequency_id'],$valueArray)?$piVars['frequency_id']:'none');
					unset($piVars['frequency_id']);
					break;
				case  'by_day':
					if(is_array($piVars['by_day'])) {
						$this->setByDay(strtolower(strip_tags(implode(',',$piVars['by_day']))));
					} else {
						$this->setByDay(strtolower(strip_tags($piVars['by_day'])));
					}
					unset($piVars['by_day']);
					break;
				case 'by_monthday':
					$this->setByMonthDay(strtolower(strip_tags($piVars['by_monthday'])));
					unset($piVars['by_monthday']);
					break;
				case 'by_month':
					$this->setByMonth(strtolower(strip_tags($piVars['by_month'])));
					unset($piVars['by_month']);
					break;
				case 'until':
					if($piVars['until'] != 0) {
						$until = new tx_cal_date(getYmdFromDateString($this->conf, strip_tags($piVars['until'])).'000000');
						$this->setUntil($until);
					}
					unset($piVars['until']);
					break;
				case 'count':
					$this->setCount(intval($piVars['count']));
					unset($piVars['count']);
					break;
				case 'interval':
					$this->setInterval(intval($piVars['interval']));
					unset($piVars['interval']);
					break;
				case 'exception_ids':
					$this->setExceptionSingleIds(array());
					$this->setExceptionGroupIds(array());
					foreach (t3lib_div::trimExplode(',',$piVars['exception_ids'],1) as $value) {
						preg_match('/(^[a-z])_([0-9]+)/', $value, $idname);
						if ($idname[1] == 'u') {
							$this->addExceptionSingleId($idname[2]);
						} else if ($idname[1] == 'g'){
							$this->addExceptionGroupId($idname[2]);
						}
					}
					break;
				case 'shared':
				case 'shared_ids':
					$this->setSharedGroups(array());
					$this->setSharedUsers(array());
					$values = $piVars[$key]; 
					if(!is_array($piVars[$key])){
						$values = t3lib_div::trimExplode(',',$piVars[$key],1);
					}
					foreach ($values as $entry) {
						preg_match('/(^[a-z])_([0-9]+)/', $entry, $idname);
						if ($idname[1] == 'u') {
							$this->addSharedUser($idname[2]);
						} else if ($idname[1] == 'g'){
							$this->addSharedGroup($idname[2]);
						}
					}
					break;
				default:
					if(in_array($key,$customFieldArray)){
						$this->row[$key] = $value;
					}
			}
		}
		if(!$dateIsSet && $piVars['mygetdate']){
			$startDay = strip_tags($piVars['mygetdate']);
			$startHour = '00';
			$startMinutes = '00';
			if($piVars['gettime']){
				$startHour = substr(strip_tags($piVars['gettime']),0,2);
				$startMinutes = substr(strip_tags($piVars['gettime']),2,2);
			}
			
			$start = new tx_cal_date($startDay.' '.$startHour.':'.$startMinutes.':00');
			$start->setTzById('UTC');
			$this->setStart($start);
			$end = new tx_cal_date();
			$end->copy($start);
			$end->addSeconds($this->conf['view.']['event.']['event.']['defaultEventLength']);
			$this->setEnd($end);
		}
	}

	function createEvent($row, $isException) {
		require_once (t3lib_extMgm :: extPath('cal') . 'controller/class.tx_cal_functions.php');

		$this->row = $row;
		$this->setType($this->serviceKey);
		$this->setUid($row['uid']);
		$this->setPid($row['pid']);
		$this->setCreationDate($row['crdate']);
		$this->setCreateUserId($row['cruser_id']);
		$this->setHidden($row['hidden']);
		$this->setTstamp($row['tstamp']);
		
		$this->setCalendarUid($row['calendar_id']);
		
		$this->setTimezone($row['timezone']);
		
		if($row['allday']){
			$row['start_time']=0;
			$row['end_time']=0;
		}else if($row['start_time']==0 && $row['end_time']==0){
			$row['allday'] = 1;
		}
		$tempDate = new tx_cal_date($row['start_date'].'000000');
		$tempDate->setTZbyId('UTC');
		$tempDate->addSeconds($row['start_time']);
		$this->setStart($tempDate);
		$tempDate = new tx_cal_date($row['end_date'].'000000');
		$tempDate->setTZbyId('UTC');
		$tempDate->addSeconds($row['end_time']);
		$this->setEnd($tempDate);
		
		$this->setAllday($row['allday']);
		$eventStart = $this->getStart();
		$eventEnd = $this->getEnd();
		if(!$this->isAllday() && $eventStart->equals($this->getEnd()) || $eventStart->after($this->getEnd())){
			$tempDate = new tx_cal_date($row['start_date']);
			$tempDate->setTZbyId('UTC');
			$tempDate->addSeconds($row['start_time']+$this->conf['view.']['event.']['event.']['defaultEventLength']);
			$this->setEnd($tempDate);
		}
		
		if($this->isAllday()){
			$eventEnd->addSeconds(86399);
			$this->setEnd($eventEnd);
		}
		
		$this->setTitle($row['title']);
		$this->setCategories($row['categories']);

		$this->setFreq($row['freq']);
		$this->setByDay($row['byday']);
		$this->setByMonthDay($row['bymonthday']);
		$this->setByMonth($row['bymonth']);
		
		$tempDate = new tx_cal_date($row['until'].'000000');
		$tempDate->setTZbyId('UTC');
		$this->setUntil($tempDate);

		$this->setCount($row['cnt']);
		$this->setInterval($row['intrval']);

		/* new */
		$this->setEventType($row['type']);
		$this->setPage($row['page']);
		$this->setExtUrl($row['ext_url']);
		/* new */

		$this->setImage(t3lib_div::trimExplode(',',$row['image'],1));
		$this->setImageTitleText(t3lib_div::trimExplode(chr(10),$row['imagetitletext']));
		$this->setImageAltText(t3lib_div::trimExplode(chr(10),$row['imagealttext']));
		$this->setImageCaption(t3lib_div::trimExplode(chr(10),$row['imagecaption']));

		if ($row['attachment']) {
			$fileArr = explode(',', $row['attachment']);
			while (list (, $val) = each($fileArr)) {
				// fills the marker ###FILE_LINK### with the links to the attached files
				$this->addAttachment($val);
			}
		}
		if ($row['attachmentcaption']) {
			$captionArray = t3lib_div::trimExplode(chr(10), $row['attachmentcaption']);
			$this->setAttachmentCaption($captionArray);
		}
		
		if($row['exception_single_ids']){
			$ids = explode(',',$row['exception_single_ids']);
			foreach($ids as $id){
				$this->addExceptionSingleId($id); 
			}
		}
		if($row['exceptionGroupIds']){
			$ids = explode(',',$row['exceptionGroupIds']);
			foreach($ids as $id){
				$this->addExceptionGroupId($id); 
			}
		}

		$this->eventOwner = $row['event_owner'];
		
		$controller = &tx_cal_registry::Registry('basic','controller');

		$this->setTeaser($controller->pi_RTEcssText($row['teaser']));

		if (!$isException) {

			$this->setDescription($controller->pi_RTEcssText($row['description']));

			if ($row['location_id'] != 0) {
				$this->setLocationId($row['location_id']);
			} else {
				$this->setLocation($row['location']);
			}
			$this->setLocationPage($row['location_pid']);
			$this->setLocationLinkUrl($row['location_link']);

			if ($row['organizer_id'] != 0) {
				$this->setOrganizerId($row['organizer_id']);
			} else {
				$this->setOrganizer($row['organizer']);
			}
			$this->setOrganizerPage($row['organizer_pid']);
			$this->setOrganizerLinkUrl($row['organizer_link']);
		}

		$this->sharedUsers = Array ();
		$this->sharedGroups = Array ();
		$table = 'tx_cal_event_shared_user_mm';
		$select = 'uid_foreign,tablenames';
		$where = 'uid_local = ' . $this->getUid();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($row1['tablenames']=='fe_users'){
				$this->addSharedUser($row1['uid_foreign']);
			}else if($row1['tablenames']=='fe_groups'){
				$this->addSharedGroup($row1['uid_foreign']);
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		$this->notifyUserIds = Array ();
		$this->notifyGroupIds = Array ();
		$table = 'tx_cal_fe_user_event_monitor_mm';
		$select = 'uid_foreign,tablenames';
		$where = 'uid_local = ' . $this->getUid();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($row1['tablenames']=='fe_users'){
				$this->addNotifyUser($row1['uid_foreign']);
			}else if($row1['tablenames']=='fe_groups'){
				$this->addNotifyGroup($row1['uid_foreign']);
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
	}

	function cloneEvent() {
		$instanceOfClass = t3lib_div :: makeInstanceClassName(get_class($this));
		$event = new $instanceOfClass ($this->getValuesAsArray(), $this->isException, $this->getType());
		$event->setIsClone(true);
		return $event;
	}

	/**
	 *	Gets the location of the event.	 Location does not exist in the default
	 *	model, only in calexampl3.
	 *	
	 *	@return		string		The location.
	 */
	function getLocation() {
		return $this->location;
	}

	/**
	 *	Sets the location of the event.	 Location does not exist in the default
	 *	model, only in calexampl3.
	 *
	 *	@param		string		The location.
	 *	@return		void
	 */
	function setLocation($location) {
		$this->location = $location;
	}
	

	/**
	 *	Gets the teaser of the event. 
	 *	
	 *	@return		string		The teaser.
	 */
	function getTeaser() {
		return $this->teaser;
	}

	/**
	 *	Sets the teaser of the event.
	 *
	 *	@param		string		The location.
	 *	@return		void
	 */
	function setTeaser($teaser) {
		$this->teaser = $teaser;
	}

	function getLocationLink($view) {
		$locationLink = '';
		if($this->getLocationLinkUrl()!=''){
			$tempArray = $this->getValuesAsArray();
			$tempArray['link'] = $this->getLocationLinkUrl();
			$this->initLocalCObject($tempArray);
			unset($tempArray);
			$this->local_cObj->setCurrentVal($this->getLocation());
			$locationLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['location'],$this->conf['view.'][$view.'.']['event.']['location.']);
		}else if ($this->getLocationId() > 0) {
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure'] ? $this->confArr['useLocationStructure'] : 'tx_cal_location');
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$location = $modelObj->findLocation($this->getLocationId(),$useLocationStructure);
			
			if(is_object($location)) {
				$tempData = $location->getValuesAsArray();
				$this->initLocalCObject($tempData);
				unset($tempData);
				$this->local_cObj->setCurrentVal($location->getName());
				/* If a specific location page is defined, link to it */
				if($this->getLocationPage() > 0){
					$this->local_cObj->data['link'] = $this->getLocationPage();
					$locationLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['location'],$this->conf['view.'][$view.'.']['event.']['location.']);
				} else {
					/* If location view is allowed, link to it */
					$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
					if($rightsObj->isViewEnabled($this->conf['view.']['locationLinkTarget']) || $this->conf['view.']['location.']['locationViewPid']){ 
						$controller = &tx_cal_registry::Registry('basic','controller');
						$location->getLinkToLocation('|');
						$this->local_cObj->data['link'] = $controller->cObj->lastTypoLinkUrl;
						$locationLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['location'],$this->conf['view.'][$view.'.']['event.']['location.']);
					} else {
						/* Just show the name of the location */
						$this->local_cObj->data['link'] = '';
						$locationLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['location'],$this->conf['view.'][$view.'.']['event.']['location.']);
					}
				}
				
			} else {
				t3lib_div::devLog('getLocationLink: no location object found', 'cal', 1);
				$locationLink = '';
			}
		}
		return $locationLink;
	}

	function getOrganizerLink($view) {
		$organizerLink = '';
		if($this->getOrganizerLinkUrl()!=''){
			$tempArray = $this->getValuesAsArray();
			$tempArray['link'] = $this->getOrganizerLinkUrl();
			$this->initLocalCObject($tempArray);
			unset($tempArray);
			$this->local_cObj->setCurrentVal($this->getOrganizer());
			$organizerLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['organizer'],$this->conf['view.'][$view.'.']['event.']['organizer.']);
		}else if ($this->getOrganizerId() > 0) {
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($this->confArr['useOrganizerStructure'] ? $this->confArr['useOrganizerStructure'] : 'tx_cal_organizer');
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$organizer = $modelObj->findOrganizer($this->getOrganizerId(),$useOrganizerStructure);
			
			if(is_object($organizer)) {
				$tempData = $organizer->getValuesAsArray();
				$this->initLocalCObject($tempData);
				unset($tempData);
				$this->local_cObj->setCurrentVal($organizer->getName());
				
				/* If a specific organizer page is defined, link to it */
				if($this->getOrganizerPage() > 0){
					$this->local_cObj->data['link'] = $this->getOrganizerPage();
					$organizerLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['organizer'],$this->conf['view.'][$view.'.']['event.']['organizer.']);
				} else {
					/* If organizer view is allowed, link to it */
					$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
					if($rightsObj->isViewEnabled($this->conf['view.']['organizerLinkTarget']) || $this->conf['view.']['organizer.']['organizerViewPid']){
						$controller = &tx_cal_registry::Registry('basic','controller');
						$organizer->getLinkToOrganizer('|');
						$this->local_cObj->data['link'] = $controller->cObj->lastTypoLinkUrl;
						$organizerLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['organizer'],$this->conf['view.'][$view.'.']['event.']['organizer.']);
					} else {
						/* Just show the name of the organizer */
						$this->local_cObj->data['link'] = '';
						$organizerLink = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['organizer'],$this->conf['view.'][$view.'.']['event.']['organizer.']);
					}
				}
			} else {
				t3lib_div::devLog('getOrganizerLink: no organizer object found', 'cal', 1);
				$organizerLink = '';
			}
		}
		return $organizerLink;
	}

	/**
	 * Returns the headerstyle name
	 */
	function getHeaderStyle() {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if($this->conf['view.']['freeAndBusy.']['enable']==1){
			return $this->conf['view.']['freeAndBusy.']['headerStyle'];
		}
		if ($this->conf['view.'][$this->conf['view'].'.']['event.']['differentStyleIfOwnEvent'] && $rightsObj->getUserId() == $this->getCreateUserId()) {
			return $this->conf['view.']['event.']['event.']['headerStyleOfOwnEvent'];
		} else if (!empty ($this->categories) && $this->categories[0]->getHeaderStyle() != '') {
			return $this->categories[0]->getHeaderStyle();
		}
		return $this->headerstyle;
	}
	
	/**
	 * Returns the bodystyle name
	 */
	function getBodyStyle() {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if($this->conf['view.']['freeAndBusy.']['enable']==1){
			return $this->conf['view.']['freeAndBusy.']['bodyStyle'];
		}
		if ($this->conf['view.'][$this->conf['view'].'.']['event.']['differentStyleIfOwnEvent'] && $rightsObj->getUserId() == $this->getCreateUserId()) {
			return $this->conf['view.']['event.']['event.']['bodyStyleOfOwnEvent'];
		} else if (!empty ($this->categories) && $this->categories[0]->getBodyStyle() != '') {
			return $this->categories[0]->getBodyStyle();
		}

		return $this->bodystyle;
	}

	/**
	*  Gets the createUserId of the event.
	*  
	*  @return		string		The create user id.
	*/
	function getCreateUserId() {
		return $this->createUserId;
	}
	
	function getTimezone(){
		return $this->timezone;
	}
	
	function setTimezone($timezone){
		$this->timezone = $timezone;
	}

	/**
	 *	Sets the createUserId of the event.
	 *
	 *	@param		string		The create user id.
	 *	@return		void
	 */
	function setCreateUserId($createUserId) {
		$this->createUserId = $createUserId;
	}
	
	function renderEventForOrganizer() {
		return $this->renderEventFor('ORGANIZER');
	}
	
	function renderEventForLocation() {
		return $this->renderEventFor('LOCATION');
	}

	function renderEventForDay() {
		return $this->renderEventFor('DAY');
	}

	function renderEventForWeek() {
		return $this->renderEventFor('WEEK');
	}

	function renderEventForAllDay() {
		return $this->renderEventFor('ALLDAY');
	}

	function renderEventForMonth() {
		if($this->isAllday()){
			return $this->renderEventFor('MONTH_ALLDAY');
		}
		return $this->renderEventFor('MONTH');
	}

	function renderEventForYear() {
		return $this->renderEventFor('year');
	}

	function renderEvent() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT###');
	}
	
	function renderEventForList($rowCount = 0) {
		if($rowCount%2==0){
			return $this->renderEventFor('LIST_EVEN');
		}
		return $this->renderEventFor('LIST_ODD');
	}
	
	function renderEventFor($viewType){
		if($this->conf['view.']['freeAndBusy.']['enable']==1){
			$viewType .= '_FNB';
		}
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_'.strtoupper($viewType).'###');
	}
	
	function fillTemplate($subpartMarker){
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$page = $cObj->fileResource($this->conf['view.']['event.']['phpicalendarEventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no event template file found:</h3>' . $this->conf['view.']['event.']['phpicalendarEventTemplate'];
		}
		$page = $cObj->getSubpart($page,$subpartMarker);
		if(!$page){
			return 'could not find the >'.$subpartMarker.'< subpart-marker in '.$this->conf['view.']['event.']['phpicalendarEventTemplate'];
		}
		$rems = array ();
		$sims = array ();
		$wrapped = array();
		$this->getMarker($page, $sims, $rems, $wrapped);
		return $cObj->substituteMarkerArrayCached($page, $sims, $rems, $wrapped);
	}

	function renderEventPreview() {
		$this->isPreview = true;
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_PREVIEW###');
	}
	
	function renderTomorrowsEvent() {
		$this->isTomorrow = true;
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_TOMORROW###');
	}

	function getSubscriptionMarker(& $template, & $sims, & $rems, &$wrapped, $view) {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$monitoring = $this->conf['monitor'];
		$getdate = $this->conf['getdate'];
		$captchaStr = 0;
		$rems['###SUBSCRIPTION###'] = '';
		$sims['###NOTLOGGEDIN_NOMONITORING_HEADING###'] = '';
		$sims['###NOTLOGGEDIN_NOMONITORING_SUBMIT###'] = '';
		$sims['###NOTLOGGEDIN_MONITORING_HEADING###'] = '';
		$sims['###NOTLOGGEDIN_MONITORING_SUBMIT###'] = '';
		$sims_temp['L_CAPTCHA_START_SUCCESS'] = '';
		$sims_temp['L_CAPTCHA_STOP_SUCCESS'] = '';
		
		$controller = &tx_cal_registry::Registry('basic','controller');
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		if ($this->conf['allowSubscribe'] == 1 && $uid) {
			if ($monitoring != null && $monitoring != '') {

				$user_uid = $rightsObj->getUserId();
				
				switch ($monitoring) {
					case 'start' :
						{
							if ($user_uid>0) {
								$table = 'tx_cal_fe_user_event_monitor_mm';
								$fields_values = array (
									'uid_local' => $uid,
									'uid_foreign' => $user_uid,
									'tablenames' => 'fe_users',
									'sorting' => 1
								);
								$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $fields_values);
							} else {
								if ($this->conf['subscribeWithCaptcha'] == 1 && t3lib_extMgm :: isLoaded('captcha')) {
									session_start();
									$captchaStr = $_SESSION['tx_captcha_string'];
									$_SESSION['tx_captcha_string'] = '';
								} else {
									$captchaStr = -1;
								}

								if (($captchaStr && $controller->piVars['captcha'] === $captchaStr) ||
									($this->conf['subscribeWithCaptcha'] == 0)) {
									//send confirm email!!
									$email = $controller->piVars['email'];

									require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
									$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
									$mailer->start();
									$mailer->from_email = $this->conf['view.']['event.']['notify.']['emailAddress'];
									$mailer->from_name = $this->conf['view.']['event.']['notify.']['fromName'];
									$mailer->replyto_email = $this->conf['view.']['event.']['notify.']['emailReplyAddress'];
									$mailer->replyto_name = $this->conf['view.']['event.']['notify.']['replyToName'];
									$mailer->organisation = $this->conf['view.']['event.']['notify.']['organisation'];

									$local_template = $cObj->fileResource($this->conf['view.']['event.']['notify.']['confirmTemplate']);

									$htmlTemplate = $cObj->getSubpart($local_template,'###HTML###');
									$plainTemplate = $cObj->getSubpart($local_template,'###PLAIN###');
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getMarker($htmlTemplate,$local_switch,$local_rems, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'start', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$this->getCreationDate())));
									$htmlTemplate = $cObj->substituteMarkerArrayCached($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getMarker($plainTemplate,$local_switch,$local_rems, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'start', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$this->getCreationDate())));

									$plainTemplate = $cObj->substituteMarkerArrayCached($plainTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$mailer->subject = $this->conf['view.']['event.']['notify.']['confirmTitle'];

									
									$rems['###SUBSCRIPTION###'] = $controller->pi_getLL('l_monitor_start_thanks');
									$mailer->theParts['html']['content'] = $htmlTemplate;
									$mailer->theParts['html']['path'] = '';
									$mailer->extractMediaLinks();
									$mailer->extractHyperLinks();
									$mailer->fetchHTMLMedia();
									$mailer->substMediaNamesInHTML(0); // 0 = relative
									$mailer->substHREFsInHTML();
									$mailer->setHTML($mailer->encodeMsg($mailer->theParts['html']['content']));
							
									$mailer->substHREFsInHTML();
					
									$mailer->setPlain(strip_tags($plainTemplate));
									$mailer->setHeaders();
									$mailer->setContent();
							
									$mailer->setRecipient($email);
									$mailer->sendtheMail();
									return;
								}else{
									$sims_temp['L_CAPTCHA_START_SUCCESS'] = $controller->pi_getLL('l_monitor_wrong_captcha');
								}
							}
							break;
						}
					case 'stop' :
						{
							if ($user_uid>0) {
								$table = 'tx_cal_fe_user_event_monitor_mm';
								$where = 'uid_foreign = ' . $user_uid . ' AND uid_local = ' . $uid. ' AND tablenames = "fe_users"';
								$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
							} else {
								if ($this->conf['subscribeWithCaptcha'] == 1 && t3lib_extMgm :: isLoaded('captcha')) {
									session_start();
									$captchaStr = $_SESSION['tx_captcha_string'];
									$_SESSION['tx_captcha_string'] = '';
								} else {
									$captchaStr = -1;
								}

								if (($captchaStr && $controller->piVars['captcha'] === $captchaStr) ||
									($this->conf['subscribeWithCaptcha'] == 0)) {
									$email = $controller->piVars['email'];
									$table = 'tx_cal_unknown_users';
									$select = 'crdate';
									$where = 'email = "' . $email . '"';
									$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
									$crdate = 0;
									while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
										$crdate = $row['crdate'];
										break;
									}
									$GLOBALS['TYPO3_DB']->sql_free_result($result);
									
									require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
									$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
									$mailer->start();
									$mailer->from_email = $this->conf['view.']['event.']['notify.']['emailAddress'];
									$mailer->from_name = $this->conf['view.']['event.']['notify.']['fromName'];
									$mailer->replyto_email = $this->conf['view.']['event.']['notify.']['emailReplyAddress'];
									$mailer->replyto_name = $this->conf['view.']['event.']['notify.']['replyToName'];
									$mailer->organisation = $this->conf['view.']['event.']['notify.']['organisation'];

									$local_template = $cObj->fileResource($this->conf['view.']['event.']['notify.']['unsubscribeConfirmTemplate']);

									$htmlTemplate = $cObj->getSubpart($local_template,'###HTML###');
									$plainTemplate = $cObj->getSubpart($local_template,'###PLAIN###');
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getMarker($htmlTemplate,$local_switch,$local_rems, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$crdate)));
									$htmlTemplate = $cObj->substituteMarkerArrayCached($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getMarker($plainTemplate,$local_switch,$local_rems, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$crdate)));
									$plainTemplate = $cObj->substituteMarkerArrayCached($plainTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$mailer->subject = $this->conf['view.']['event.']['notify.']['unsubscribeConfirmTitle'];
									
									$rems['###SUBSCRIPTION###'] = $controller->pi_getLL('l_monitor_stop_thanks');
									$mailer->theParts['html']['content'] = $htmlTemplate;
									$mailer->theParts['html']['path'] = '';
									$mailer->extractMediaLinks();
									$mailer->extractHyperLinks();
									$mailer->fetchHTMLMedia();
									$mailer->substMediaNamesInHTML(0); // 0 = relative
									$mailer->substHREFsInHTML();
									$mailer->setHTML($mailer->encodeMsg($mailer->theParts['html']['content']));

									$mailer->substHREFsInHTML();
		
									$mailer->setPlain(strip_tags($plainTemplate));
									$mailer->setHeaders();
									$mailer->setContent();

									$mailer->setRecipient($email);
									$mailer->sendtheMail();
									return;
									
								}else{
									$sims_temp['L_CAPTCHA_STOP_SUCCESS'] = $controller->pi_getLL('l_monitor_wrong_captcha');
								}
							}
							break;
						}
						
				}
				
			}
			
			/* If we have a logged in user */
			if ($rightsObj->isLoggedIn() && $this->conf['subscribeFeUser'] == 1) {
				$select = '*';
				$from_table = 'tx_cal_fe_user_event_monitor_mm';
				$whereClause = 'uid_foreign = ' . $rightsObj->getUserId() .
				' AND uid_local = ' . $uid;

				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from_table, $whereClause, $groupBy = '', $orderBy = '', $limit = '');
				$found_one = false;
				// create a local cObj with a customized data array, that is allowed to be changed 
				$this->initLocalCObject($this->getValuesAsArray());
				$this->local_cObj->setCurrentVal($controller->pi_getLL('l_monitor_event_logged_in_monitoring'));
				while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$this->local_cObj->data['link'] = $controller->pi_linkTP_keepPIvars_url(array (
						'view' => 'event',
						'monitor' => 'stop',
						'type' => $type,
						'uid' => $uid,
					), $this->conf['cache'], $this->conf['clear_anyway']);
					$rems['###SUBSCRIPTION###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['isMonitoringEventLink'],$this->conf['view.'][$view.'.']['event.']['isMonitoringEventLink.']);
					$found_one = true;
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
				
				if (!$found_one) {
					$this->local_cObj->setCurrentVal($controller->pi_getLL('l_monitor_event_logged_in_nomonitoring'));
					$this->local_cObj->data['link'] = $controller->pi_linkTP_keepPIvars_url(array (
						'view' => 'event',
						'monitor' => 'start',
						'type' => $type,
						'uid' => $uid,
					), $this->conf['cache'], $this->conf['clear_anyway']);
					$rems['###SUBSCRIPTION###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['isNotMonitoringEventLink'],$this->conf['view.'][$view.'.']['event.']['isNotMonitoringEventLink.']);
				}
			} else { /* Not a logged in user */
			
				/* If a CAPTCHA is required to subscribe, add a couple extra markers */
				if ($this->conf['subscribeWithCaptcha'] == 1 && t3lib_extMgm :: isLoaded('captcha')) {
					$sims_temp['CAPTCHA_SRC'] = '<img src="'.t3lib_extMgm :: siteRelPath('captcha') . 'captcha/captcha.php'.'" alt="" />';
					$sims_temp['L_CAPTCHA_TEXT'] = $controller->pi_getLL('l_captcha_text');
					$sims_temp['CAPTCHA_TEXT'] = '<input type="text" size=10 name="tx_cal_controller[captcha]" value="">';
				} else {
					$sims_temp['CAPTCHA_SRC'] = '';
					$sims_temp['L_CAPTCHA_TEXT'] = '';
					$sims_temp['CAPTCHA_TEXT'] = '';
				}
				
				$notLoggedinNoMonitoring = $cObj->getSubpart($template, '###NOTLOGGEDIN_NOMONITORING###');
				$parameter = array (
					'no_cache' => 1,
					'view' => 'event',
					'monitor' => 'start',
					'type' => $type,
					'uid' => $uid
				);
				$actionUrl = $controller->pi_linkTP_keepPIvars_url($parameter);

				$parameter2 = array (
					'no_cache' => 1,
					'getdate' => $getdate,
					'lastview' => $controller->extendLastView(), 'view' => 'event', 'monitor' => 'stop');
					
				$actionUrl2 = $controller->pi_linkTP_keepPIvars_url($parameter2);
				$sims_temp['NOTLOGGEDIN_NOMONITORING_HEADING'] = $controller->pi_getLL('l_monitor_event_logged_in_nomonitoring');
				$sims_temp['NOTLOGGEDIN_NOMONITORING_SUBMIT'] = $controller->pi_getLL('l_submit');
				$sims_temp['L_ENTER_EMAIL'] = $controller->pi_getLL('l_enter_email');
				$sims_temp['ACTIONURL'] = $actionUrl;
				$monitor = $controller->replace_tags($sims_temp, $notLoggedinNoMonitoring);

				$sims_temp['ACTIONURL'] = $actionUrl2;
				$notLoggedinMonitoring = $cObj->getSubpart($template, '###NOTLOGGEDIN_MONITORING###');
				$sims_temp['NOTLOGGEDIN_MONITORING_HEADING'] = $controller->pi_getLL('l_monitor_event_logged_in_monitoring');
				$sims_temp['NOTLOGGEDIN_MONITORING_SUBMIT'] = $controller->pi_getLL('l_submit');
				$sims_temp['L_ENTER_EMAIL'] = $controller->pi_getLL('l_enter_email');

				$monitor .= $controller->replace_tags($sims_temp, $notLoggedinMonitoring);
				$rems['###SUBSCRIPTION###'] = $monitor;
			} 
		} else {
			$rems['###SUBSCRIPTION###'] = '';
		}
	}

	function getStartAndEndMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$controller = &tx_cal_registry::Registry('basic','controller');
		$this->initLocalCObject();
		$eventStart = $this->getStart();
		$eventEnd = $this->getEnd();
		if ($eventStart->equals($this->getEnd())) {
			$sims['###STARTTIME_LABEL###'] = '';
			$sims['###ENDTIME_LABEL###'] = '';
			$sims['###STARTTIME###'] = '';
			$sims['###ENDTIME###'] = '';
			$this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view.'.']['event.']['dateFormat']));
			$sims['###STARTDATE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['startdate'],$this->conf['view.'][$view.'.']['event.']['startdate.']);

			$sims['###STARTDATE_LABEL###'] = $controller->pi_getLL('l_event_allday');
			if($this->conf['view.'][$view.'.']['event.']['dontShowEndDateIfEqualsStartDateAllday']==1){
				$sims['###ENDDATE###'] = '';
				$sims['###ENDDATE_LABEL###'] = '';
			} else {
				$this->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view.'.']['event.']['dateFormat']));
				$sims['###ENDDATE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['enddate'],$this->conf['view.'][$view.'.']['event.']['enddate.']);
				$sims['###ENDDATE_LABEL###'] = $controller->pi_getLL('l_event_enddate');
			}
		} else {
			if ($this->isAllday()) {
				$sims['###STARTTIME_LABEL###'] = '';
				$sims['###STARTTIME###'] = '';
			} else {
				$sims['###STARTTIME_LABEL###'] = $controller->pi_getLL('l_event_starttime');
				$this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view.'.']['event.']['timeFormat']));
				$sims['###STARTTIME###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['starttime'],$this->conf['view.'][$view.'.']['event.']['starttime.']);

			}
			if ($this->isAllday()) {
				$sims['###ENDTIME_LABEL###'] = '';
				$sims['###ENDTIME###'] = '';
			} else {
				$sims['###ENDTIME_LABEL###'] = $controller->pi_getLL('l_event_endtime');
				$this->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view.'.']['event.']['timeFormat']));
				$sims['###ENDTIME###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['endtime'],$this->conf['view.'][$view.'.']['event.']['endtime.']);
				
			}
			
			$this->local_cObj->setCurrentVal($eventStart->format($this->conf['view.'][$view.'.']['event.']['dateFormat']));
			$sims['###STARTDATE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['startdate'],$this->conf['view.'][$view.'.']['event.']['startdate.']);			
			if ($this->conf['view.'][$view.'.']['event.']['dontShowEndDateIfEqualsStartDate'] && $eventEnd->format('%Y%m%d') == $eventStart->format('%Y%m%d')) {
				$sims['###STARTDATE_LABEL###'] = $controller->pi_getLL('l_date');
				$sims['###ENDDATE_LABEL###'] = '';
				$sims['###ENDDATE###'] = '';
			} else {
				$sims['###STARTDATE_LABEL###'] = $controller->pi_getLL('l_event_startdate');
				$sims['###ENDDATE_LABEL###'] = $controller->pi_getLL('l_event_enddate');
				$this->local_cObj->setCurrentVal($eventEnd->format($this->conf['view.'][$view.'.']['event.']['dateFormat']));
				$sims['###ENDDATE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['enddate'],$this->conf['view.'][$view.'.']['event.']['enddate.']);
			}
		}
	}

	function getTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject();
		$this->local_cObj->setCurrentVal($this->getTitle());
		if($this->isTomorrow && !in_array($view,array('create_event','edit_event')) && $this->conf['view.']['other.']['tomorrowsEvents']){
			$sims['###TITLE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['other.']['tomorrowsEvents'],$this->conf['view.']['other.']['tomorrowsEvents.']);
		}else if($this->isAllday() && $this->conf['view.'][$view.'.']['event.']['alldayTitle']){
			$sims['###TITLE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['alldayTitle'],$this->conf['view.'][$view.'.']['event.']['alldayTitle.']);
		}else if($this->conf['view.'][$view.'.']['event.']['title']){
			$sims['###TITLE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['title'],$this->conf['view.'][$view.'.']['event.']['title.']);
		}else{
			$sims['###TITLE###'] = $this->getTitle();
		}
	}
	
	function getTitleFnbMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims['###TITLE_FNB###'] = $this->conf['view.']['freeAndBusy.']['eventTitle'];
	}
	

	function getOrganizerMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->getOrganizerLinkUrl()!='' || $this->getOrganizerId() > 0) {
			$sims['###ORGANIZER###'] = $this->getOrganizerLink($view);
		} else {
			$this->initLocalCObject($this->getValuesAsArray());
			if($this->getOrganizerPage() >0){
				$this->local_cObj->data['link'] = $this->getOrganizerPage();
			}else{
				$this->local_cObj->data['link'] = '';
			}
			$this->local_cObj->setCurrentVal($this->getOrganizer());
			$sims['###ORGANIZER###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['organizer'], $this->conf['view.'][$view.'.']['event.']['organizer.']);
		}
	}

	function getLocationMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->getLocationLinkUrl()!='' || $this->getLocationId() > 0) {
			$sims['###LOCATION###'] = $this->getLocationLink($view);
		} else {
			$this->initLocalCObject($this->getValuesAsArray());
			if($this->getLocationPage() >0){
				$this->local_cObj->data['link'] = $this->getLocationPage();
			}else{
				$this->local_cObj->data['link'] = '';
			}
			$this->local_cObj->setCurrentVal($this->getLocation());
			$sims['###LOCATION###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['location'], $this->conf['view.'][$view.'.']['event.']['location.']);
		}
	}

	function getTeaserMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if($confArr['useTeaser']) {
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($this->getTeaser());
			$sims['###TEASER###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['teaser'],$this->conf['view.'][$view.'.']['event.']['teaser.']);
		} else {
			$sims['###TEASER###'] = '';
		}
	}
	
	function getIcsLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
			$tempArray = $this->getValuesAsArray();
			$tempArray['link'] = $GLOBALS['TSFE']->id;
			$tempArray['additionalParams'] = '&'.$this->prefixId.'[type]='.$this->getType().'&'.$this->prefixId.'[view]=single_ics'.'&'.$this->prefixId.'[uid]='.$this->getUid();
			$this->initLocalCObject($tempArray);
			$wrapped['###ICS_LINK###'] = explode('|',$this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['ics'], $this->conf['view.'][$view.'.']['event.']['ics.']));
		}else{
			$rems['###ICS_LINK###'] = '';
		}
	}

	function getCategoryMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject();
		$this->local_cObj->setCurrentVal($this->getCategoriesAsString(false));
		$sims['###CATEGORY###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['category'], $this->conf['view.'][$view.'.']['event.']['category.']);
	}
	
	function getCategoryLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject();
		$this->local_cObj->setCurrentVal($this->getCategoriesAsString());
		$sims['###CATEGORY_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['categoryLink'], $this->conf['view.'][$view.'.']['event.']['categoryLink.']);
	}

	function getHeaderstyleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
	}
	
	function getBodystyleMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims['###BODYSTYLE###'] = $this->getBodyStyle();
	}
	
	/**
	 * Returns the calendar style name
	 */
	function getCalendarStyle(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->initLocalCObject();
		$this->local_cObj->setCurrentVal($this->getCalendarUid());
		$sims['###CALENDARSTYLE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['calendarStyle'], $this->conf['view.'][$view.'.']['event.']['calendarStyle.']);
	}
	
	
	function getMapMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims['###MAP###'] = '';
		if ($this->conf['view.'][$view.'.']['event.']['showMap'] && $this->getLocationId()) {
			/* Pull values from Flexform object into individual variables */
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure'] ? $this->confArr['useLocationStructure'] : 'tx_cal_location');
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			$location = $modelObj->findLocation($this->getLocationId(), $useLocationStructure);
			$local_sims = array();
			$local_rems = array();
			$local_wrapped = array();
			$location->getMarker('###MAP###',$local_sims,$local_rems,$local_wrapped);
			$sims['###MAP###'] = $local_sims['###MAP###'];
		}
	}

	function getAttachmentMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims['###ATTACHMENT###'] = '';
		
		//due tue missing TS configuration  support of the uploads rendering of css_styled_content, we have to manually fake some db values for it
		$tempData = $this->getValuesAsArray();
		$tempData['filelink_size'] = $this->conf['view.'][$view.'.']['event.']['attachment.']['showFileSize'];
		$tempData['media'] = implode(',',$this->getAttachment());
		$tempData['layout'] = $this->conf['view.'][$view.'.']['event.']['attachment.']['layout'];
		$tempData['imagecaption'] = implode(chr(10),$this->getAttachmentCaption());
		$filePath = $this->conf['view.'][$view.'.']['event.']['attachment.']['filePath'] ? $this->conf['view.'][$view.'.']['event.']['attachment.']['filePath'] : '';
		$filePath = ($filePath == '1' || $filePath == 'true') ? $GLOBALS['TCA']['tx_cal_event']['columns']['attachment']['config']['uploadfolder'].'/' : $filePath;
		$tempData['select_key'] = $filePath;
		$this->initLocalCObject($tempData);
		$this->local_cObj->setCurrentVal($tempData['media']);
		$sims['###ATTACHMENT###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['attachment'],$this->conf['view.'][$view.'.']['event.']['attachment.']);
	}
	
	function getAttachmentUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###ATTACHMENT_URL###'] = '';
		if($this->getAttachment()){
			$this->initLocalCObject();
			$tempArray = Array();
			foreach($this->getAttachment() as $attachment){
				$tempArray[] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'uploads/tx_cal/media/'.$attachment;
			}
			$this->local_cObj->setCurrentVal(implode(',',$tempArray));
			$sims['###ATTACHMENT_URL###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['attachment_url'], $this->conf['view.'][$view.'.']['event.']['attachment_url.']);
		}
	}

	function getEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart();
		$wrapped['###EVENT_LINK###'] = explode('|',$this->getLinkToEvent('|',$view, $eventStart->format('%Y%m%d')));
	}
	
	function getEventUrlMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart();
		$this->getLinkToEvent('|',$view, $eventStart->format('%Y%m%d'));
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$wrapped['###EVENT_URL###'] = htmlspecialchars($cObj->lastTypoLinkUrl);
	}
	
	function getAbsoluteEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$eventStart = $this->getStart();
		$wrapped['###ABSOLUTE_EVENT_LINK###'] = explode('|',$this->getLinkToEvent('|',$view, $eventStart->format('%Y%m%d')));
	}

	function getStartdate(){
		$start = $this->getStart();
		return $start->format(getFormatStringFromConf($this->conf));
	}
	
	function getEnddate(){
		$end = $this->getEnd();
		return $end->format(getFormatStringFromConf($this->conf));
	}
	
	function getEditLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$eventStart = $this->getStart();
		$sims['###EDIT_LINK###'] = '';
		
		if ($this->isUserAllowedToEdit()) {
			$controller = &tx_cal_registry::Registry('basic','controller');
			$linkConf = $this->getValuesAsArray();
			if($this->conf['view.']['enableAjax']){
				$temp = sprintf($this->conf['view.'][$view.'.']['event.']['editLinkOnClick'],$this->getUid(),$this->getType());
				$linkConf['ATagParams'] = ' onclick="'.$temp.'"';
			}
			$linkConf['no_cache'] = 0;
			$linkConf['useCacheHash'] = 0;
			$linkConf['additionalParams'] = '&tx_cal_controller[view]=edit_event&tx_cal_controller[type]='.$this->getType().'&tx_cal_controller[uid]='.$this->getUid().'&tx_cal_controller[getdate]='.$eventStart->format('%Y%m%d').'&tx_cal_controller[lastview]='.$controller->extendLastView();
			$linkConf['section'] = 'default';
			$linkConf['link'] = $this->conf['view.']['event.']['editEventViewPid']?$this->conf['view.']['event.']['editEventViewPid']:$GLOBALS['TSFE']->id;
			
			$this->initLocalCObject($linkConf);
			$this->local_cObj->setCurrentVal($this->conf['view.'][$view.'.']['event.']['editIcon']);
			$sims['###EDIT_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['editLink'],$this->conf['view.'][$view.'.']['event.']['editLink.']);
		}
		if ($this->isUserAllowedToDelete()) {
			$controller = &tx_cal_registry::Registry('basic','controller');
			$linkConf = $this->getValuesAsArray();
			if($this->conf['view.']['enableAjax']){
				$temp = sprintf($this->conf['view.'][$view.'.']['event.']['deleteLinkOnClick'],$this->getUid(),$this->getType());
				$linkConf['ATagParams'] = ' onclick="'.$temp.'"';
			}
			$linkConf['no_cache'] = 0;
			$linkConf['useCacheHash'] = 0;
			$linkConf['additionalParams'] = '&tx_cal_controller[view]=delete_event&tx_cal_controller[type]='.$this->getType().'&tx_cal_controller[uid]='.$this->getUid().'&tx_cal_controller[getdate]='.$eventStart->format('%Y%m%d').'&tx_cal_controller[lastview]='.$controller->extendLastView();
			$linkConf['section'] = 'default';
			$linkConf['link'] = $this->conf['view.']['event.']['deleteEventViewPid']?$this->conf['view.']['event.']['deleteEventViewPid']:$GLOBALS['TSFE']->id;

			$this->initLocalCObject($linkConf);
			$this->local_cObj->setCurrentVal($this->conf['view.'][$view.'.']['event.']['deleteIcon']);
			$sims['###EDIT_LINK###'] .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['deleteLink'],$this->conf['view.'][$view.'.']['event.']['deleteLink.']);
		}
	}
	

	
	function getMoreLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###MORE_LINK###'] = '';
		if ($this->conf['view.'][$view.'.']['event.']['isPreview'] && $this->conf['preview']) {
			$controller = &tx_cal_registry::Registry('basic','controller');

			$linkUrl = $controller->pi_linkTP_keepPIvars_url(
			array (
				'page_id' => $GLOBALS['TSFE']->id,
				'preview' => null,
				'view' => event,
				'uid' => $this->getUid(), 
				'type' => $this->getType(), 
				'lastview' => $controller->extendLastView()
			), 
			$this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['eventViewPid']);
			$tempArray = $this->getValuesAsArray();
			$tempArray['link'] = $linkUrl;
			$this->initLocalCObject($tempArray);
			$this->local_cObj->setCurrentVal($controller->pi_getLL('l_more'));
			$sims['###MORE_LINK###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['moreLink'],$this->conf['view.'][$view.'.']['event.']['moreLink.']);
		}
	}

	
	function getStartdateMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->getStartAndEndMarker($template, $sims, $rems, $wrapped, $view);
	}
	
	function getEnddateMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
	}
	
	function getStarttimeMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
	}
	
	function getEndtimeMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->getStartdateMarker($template, $sims, $rems, $wrapped, $view);
	}
	
	function getDescriptionStriptagsMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->striptags = true;
		$this->getDescriptionMarker($template, $sims, $rems, $wrapped, $view);
		$this->striptags = false;
	}
	
	function getStatusMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$event_status = strtolower($this->getStatus());
		$confirmed = '';
		if ($event_status != '') {
			$confirmed = sprintf($this->conf['view.'][$view.'.']['event.']['statusIcon'],$event_status);
		}
		else if (is_array($this->getCalRecu()) && count($this->getCalRecu())>0) {
			$confirmed = $this->conf['view.'][$view.'.']['event.']['recurringIcon'];
		}
		$sims['###STATUS###'] = $confirmed;
	}

	function getAdditionalValuesAsArray() {
			$values = parent::getAdditionalValuesAsArray();
			$values['event_owner'] = $this->eventOwner;
			$values['cruser_id'] = $this->getCreateUserId();
			return $values;
	}

	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if(!$rightsObj->isViewEnabled('edit_event')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		$editOffset = $this->conf['rights.']['edit.']['event.']['timeOffset'] * 60;

		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups();
		}
		$isEventOwner = $this->isEventOwner($feUserUid, $feGroupsArray);
		$isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
		if ($rightsObj->isAllowedToEditStartedEvent()) {
			$eventHasntStartedYet = true;
		} else {
			$temp = new tx_cal_date();
			$temp->setTZbyId('UTC');
			$temp->addSeconds($editOffset);
			$eventStart = $this->getStart();
			$eventHasntStartedYet = $eventStart->after($temp);
		}
		$isAllowedToEditEvent = $rightsObj->isAllowedToEditEvent();
		$isAllowedToEditOwnEventsOnly = $rightsObj->isAllowedToEditOnlyOwnEvent();

		if ($isAllowedToEditOwnEventsOnly) {
			return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
		}
		return $isAllowedToEditEvent && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		if(!$rightsObj->isViewEnabled('delete_event')){
			return false;
		}
		if ($rightsObj->isCalAdmin()) {
			return true;
		}
		$deleteOffset = $this->conf['rights.']['delete.']['event.']['timeOffset'] * 60;
		if ($feUserUid == '') {
			$feUserUid = $rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $rightsObj->getUserGroups();
		}
		$isEventOwner = $this->isEventOwner($feUserUid, $feGroupsArray);
		$isSharedUser = $this->isSharedUser($feUserUid, $feGroupsArray);
		if ($rightsObj->isAllowedToDeleteStartedEvents()) {
			$eventHasntStartedYet = true;
		} else {
			$temp = new tx_cal_date();
			$temp->setTZbyId('UTC');
			$temp->addSeconds($editOffset);
			$eventStart = $this->getStart();
			$eventHasntStartedYet = $eventStart->after($temp);
		}
		$isAllowedToDeleteEvents = $rightsObj->isAllowedToDeleteEvents();
		$isAllowedToDeleteOwnEventsOnly = $rightsObj->isAllowedToDeleteOnlyOwnEvents();

		if ($isAllowedToDeleteOwnEventsOnly) {
			return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
		}
		return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
	}
	
	function __toString(){
		return 'Phpicalendar '.(is_object($this)?'object':'something').': '.implode(',',$this->row);
	}
	
	function getAttendees() {
		$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		return $globalAttendeeArray = $modelObj->findEventAttendees($this->getUid());
	}
	
	function getAttendeeMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###ATTENDEE###'] = '';
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		$globalAttendeeArray = $modelObj->findEventAttendees($this->getUid());

		$isChairMan = false;
		$chairmanEmail = 'none';
		foreach($globalAttendeeArray as $serviceKey => $attendeeArray){
			foreach ($attendeeArray as $attendee) {
				if($attendee->getAttendance()=='CHAIR'){
					$chairmanEmail = $attendee->getEmail();
				}
				if($attendee->getAttendance() == 'CHAIR' && $rightsObj->getUserId() == $attendee->getFeUserId()){
					$isChairMan = true;
					break;
				}
			}
		}
		if(in_array($view, Array('ics','ics_single'))){
			if($globalAttendeeArray){
				foreach($globalAttendeeArray as $serviceType => $attendeeArray){
					foreach($attendeeArray as $attendee){
						if($attendee->getAttendance()=='CHAIR'){
							$sims['###ORGANIZER###'] = 'ORGANIZER;ROLE='.$attendee->getAttendance().':MAILTO:'.$attendee->getEmail();
						}
						if($this->limitAttendeeToThisEmail!='' && $attendee->getEmail()!=$this->limitAttendeeToThisEmail){
							continue;
						}
						if($attendee->getStatus()==0){
							$attendee->setStatus('NEEDS-ACTION');
						}
						$sims['###ATTENDEE###'] .= 'ATTENDEE;ROLE='.$attendee->getAttendance().';PARTSTAT='.$attendee->getStatus().';RSVP=TRUE:MAILTO:'.$attendee->getEmail();
					}
				}
			}
		}else{
			if($rightsObj->isLoggedIn() && !empty($attendeeArray)){
				$formattedArray = Array();
				$partOf = false;
				$controller = &tx_cal_registry::Registry('basic','controller');
				foreach($globalAttendeeArray as $serviceKey => $attendeeArray){
					foreach ($attendeeArray as $attendee) {
						 $finalString = $attendee->getName().' ';
						 $finalString .= $attendee->getEmail().' ';
						 if($attendee->getAttendance()=='CHAIR'){
							$finalString .= sprintf($this->conf['view.'][$view.'.']['event.']['attendeeIcon'],$attendee->getAttendance(),$attendee->getAttendance(),$attendee->getAttendance());
						 }else{
							$finalString .= sprintf($this->conf['view.'][$view.'.']['event.']['attendeeIcon'],$attendee->getStatus(),$attendee->getStatus(),$attendee->getStatus());
						 }
						 
						 if($rightsObj->getUserId() == $attendee->getFeUserId() || $isChairMan){
							$partOf = true;
							$this->initLocalCObject($this->getValuesAsArray());
							if($attendee->getAttendance() != 'CHAIR' && ($attendee->getStatus()=='ACCEPTED' || $attendee->getStatus()=='0' || $attendee->getStatus()=='NEEDS-ACTION')){
								$this->local_cObj->setCurrentVal($controller->pi_getLL('l_meeting_decline'));
								$this->local_cObj->data['link'] = $controller->pi_linkTP_keepPIvars(array ('view' => 'meeting', 'lastview'=>$controller->extendLastView(), 'attendee' => $attendee->getUid(), 'uid' => $this->getUid(), 'status' => 'decline', 'sid' => md5($this->getUid().$attendee->getEmail().$attendee->row['crdate'])), $this->conf['cache'], $this->conf['clear_anyway'],	$this->conf['view.']['event.']['meeting.']['statusViewPid']).' ';
								$finalString .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['declineMeetingLink'], $this->conf['view.'][$view.'.']['event.']['declineMeetingLink.']);
							}
							if($attendee->getAttendance() != 'CHAIR' && ($attendee->getStatus()=='DECLINE' || $attendee->getStatus()=='0' || $attendee->getStatus()=='NEEDS-ACTION')){
								$this->local_cObj->setCurrentVal($controller->pi_getLL('l_meeting_accept'));
								$this->local_cObj->data['link'] = $controller->pi_linkTP_keepPIvars(array ('view' => 'meeting', 'lastview'=>$controller->extendLastView(), 'attendee' => $attendee->getUid(), 'uid' => $this->getUid(), 'status' => 'accept', 'sid' => md5($this->getUid().$attendee->getEmail().$attendee->row['crdate'])), $this->conf['cache'], $this->conf['clear_anyway'],  $this->conf['view.']['event.']['meeting.']['statusViewPid']);
								$finalString .= $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['acceptMeetingLink'], $this->conf['view.'][$view.'.']['event.']['acceptMeetingLink.']);
							}
						 }
						 $formattedArray[] = $finalString;
					}
				}
				if($partOf){
					$this->initLocalCObject();
					$this->local_cObj->setCurrentVal(implode('<br/>',$formattedArray));
					$sims['###ATTENDEE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['attendee'], $this->conf['view.'][$view.'.']['event.']['attendee.']);
				}
			}
		}
	}
	
	function getLinkToEvent($linktext, $view, $date, $urlOnly = false) {
		/* new */
		$controller = &tx_cal_registry::Registry('basic','controller');
		if($linktext==''){
			$linktext = $controller->pi_getLL('l_no_title');
		}
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');

		if($rightsObj->isViewEnabled('event') || $this->conf['view.']['event.']['eventViewPid']){
			$tempRecordArray = $this->getValuesAsArray();
			$this->initLocalCObject($tempRecordArray);
			$this->local_cObj->setCurrentVal($linktext);

			if(!$this->conf['view.'][$view.'.']['event.']['eventLink']) {
				$view = 'event';
			}

			// create the link if the event points to a page or external URL
			if($this->event_type != 0 && $this->event_type != 3){ // normal or meeting
				// determine the link type
				switch ($this->event_type) {
					// shortcut to page - create the link
					case 1:
						$param = $this->page;
						break;
					// external url
					case 2:
						$param = $this->ext_url;
						break;
				}
	
				// create & return the link
				$this->local_cObj->data['link'] = $param;
				return $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['eventLink'],$this->conf['view.'][$view.'.']['event.']['eventLink.']);
			}
			
			$linkParams = array ('page_id' => $GLOBALS['TSFE']->id, 'getdate' => $date, 'lastview' => $controller->extendLastView(), 'view' => 'event', 'type' => $this->getType(), 'uid' => $this->getUid());
			$this->addAdditionalSingleViewUrlParams($linkParams);
			
			/* new */
			if($this->isExternalPluginEvent()){
				return $this->getExternalPluginEventLink();
			}
			if($this->conf['view.']['event.']['isPreview']){
				$linkParams['preview'] = 1;
				$linkUrl = $controller->pi_linkTP_keepPIvars_url($linkParams, $this->conf['cache'], $this->conf['clear_anyway'],	 $this->conf['view.']['event.']['eventViewPid']);
				$this->local_cObj->data['link'] = $linkUrl;
				return $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['eventLink'],$this->conf['view.'][$view.'.']['event.']['eventLink.']);
			}

			$pid = $this->conf['view.']['event.']['eventViewPid'];
			if(is_array($this->getCategories())){
				foreach($this->getCategories() as $category){
					if($category->getSinglePid()){
						$pid = $category->getSinglePid();
						break;
					}
				}
			}
			$linkUrl = $controller->pi_linkTP_keepPIvars_url($linkParams, $this->conf['cache'], $this->conf['clear_anyway'],  $pid);
			if($urlOnly){
				return $linkUrl;
			}
			$this->local_cObj->data['link'] = $linkUrl;
			return $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.']['event.']['eventLink'],$this->conf['view.'][$view.'.']['event.']['eventLink.']);
			
		}else{
			return $linktext;
		}
	}
	
	function getEventIdMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$start = $this->getStart();
		$sims['###EVENT_ID###'] = $this->getType().$this->getUid().$start->format('%Y%m%d%H%M');
	}
	
	function getGuidMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###GUID###'] = $this->conf['view.']['ics.']['eventUidPrefix'].'_'.$this->getCalendarUid().'_'.$this->getUid();
	}
	
	function getDtstampMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DTSTAMP###'] = 'DTSTAMP:'.gmdate('Ymd', $this->getCreationDate()).'T'.gmdate('His', $this->getCreationDate());
	}
	
	function getDtstartYearMonthDayHourMinuteMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$eventStart = $this->getStart();
		if ($this->isAllday()) {
			$sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART;VALUE=DATE:'.$eventStart->format('%Y%m%d');
		}else{
			$offset = strtotimeOffset($eventStart->getTime());
			$eventStart->subtractSeconds($offset);
			$sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART:'.$eventStart->format('%Y%m%dT%H%M%SZ');
			$eventStart->addSeconds($offset);
		}
	}
	
	function getDtendYearMonthDayHourMinuteMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$eventStart = $this->getStart();
		$eventEnd = $this->getEnd();
		if ($this->isAllday()){
			$eventEnd->addSeconds(84600);
			$sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND;VALUE=DATE:'.$eventEnd->format('%Y%m%d');
		}else{
			$offset = strtotimeOffset($eventEnd->getTime());
			$eventEnd->subtractSeconds($offset);
			$sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND:'.$eventEnd->format('%Y%m%dT%H%M%SZ');
			$eventEnd->addSeconds($offset);
		}
	}
	
	function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view ) {
		$sims['###RRULE###'] = '';
		$rrule = $this->getRRule($this);
		if($rrule) {
			$sims['###RRULE###'] = 'RRULE:'.$rrule;
		}
	}
	
	function getRRule(&$event) {
		$rrule = '';
		$allowedValues = array('second','minute','hour','day','week','month','year'); 
		if(in_array($event->getFreq(),$allowedValues)){
			$rrule = 'FREQ='.$event->getIcsFreqLabel($event->getFreq()).';INTERVAL='.$event->getInterval().';';
			if ($event->getCount() != 0) {
				$rrule .= 'COUNT='.$event->getCount().';';
			}
			if (count($event->getByDay()) > 0) {
				$rrule .= 'BYDAY=';
				foreach ($event->getByDay() as $day) {
					$rrule .= $day.',';
				}
				$rrule = substr($rrule, $rrule.length, -1);
			}
			if ($event->getByWeekNo().length > 0) {
				$rrule .= 'BYWEEKNO=';
				foreach ($event->getByWeekNo() as $week) {
					$rrule .= $week.',';
				}
				$rrule .= ';';
			}
			if ($event->getByMonth().length > 0) {
				$rrule .= 'BYMONTH=';
				foreach ($event->getByMonth() as $month) {
					$rrule .= $month.',';
				}
				$rrule .= ';';
			}
			if ($event->getByYearDay().length > 0) {
				$rrule .= 'BYYEARDAY=';
				foreach ($event->getByYearDay() as $yearday) {
					$rrule .= $yearday.',';
				}
				$rrule .= ';';
			}
			if ($event->getByMonthDay().length > 0) {
				$rrule .= 'BYMONTHDAY=';
				foreach ($event->getByMonthDay() as $monthday) {
					$rrule .= $monthday.',';
				}
				$rrule .= ';';
			}
			if ($event->getByWeekDay().length > 0) {
				$rrule .= 'BYWEEKDAY=';
				foreach ($event->getByWeekDay() as $weekday) {
					$rrule .= $weekday.',';
				}
				$rrule .= ';';
			}
			$until = $event->getUntil(); 
			if (is_object($until) && $until->format('%Y%m%d')>19700101){ 
				$rrule .= ';UNTIL='.$until->format('%Y%m%dT000000Z'); 
			}
		}
		return strtoupper($rrule);
	}
	
	function getExdateMarker(& $template, & $sims, & $rems, $view) {
		$sims['###EXDATE###'] = '';
		$exceptionDates = array();
		foreach ($this->getExceptionEvents() as $exceptionEvent) {
			if ($exceptionEvent->getFreq() == 'none') {
				$exceptionEventStart = $exceptionEvent->getStart();
				$exceptionDates[] = $exEventStart->format('%Y%m%d');
			}
		}
		
		if(count($exceptionDates)) {
			$sims['###EXDATE###'] = 'EXDATE:'.implode(',', $exceptionDates);
		}
		

	}
	
	function getExruleMarker(& $template, & $sims, & $rems, $view) {
		$sims['###EXRULE###'] = '';
		$exceptionRules = array();
		foreach ($this->getExceptionEvents() as $exceptionEvent) {
			if ($exceptionEvent->getFreq() != 'none') {
				$exceptionRules .= $this->getRRule($exceptionEvent);
			}
		}
		
		if(count($exceptionRules)) {
			$sims['###EXRULE###'] = 'EXRULE:'.implode(',', $exceptionRules);
		}
	}

	function getIcsFreqLabel($eventFreq){
		$freq_type = '';
		switch ($eventFreq){
			case 'year':		$freq_type = 'YEARLY';		break;
			case 'month':		$freq_type = 'MONTHLY';		break;
			case 'week':		$freq_type = 'WEEKLY';		break;
			case 'day':			$freq_type = 'DAILY';		break;
			case 'hour':		$freq_type = 'HOURLY';		break;
			case 'minute':		$freq_type = 'MINUTELY';	break;
			case 'second':		$freq_type = 'SECONDLY';	break;
		}
		return $freq_type;
	}
	
	
	function getCruserNameMarker(& $template, & $sims, & $rems, $view){
		$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		$sims['###CRUSER_NAME###'] = '';
		$feUser = $modelObj->findFeUser($this->getCreateUserId());
		if(is_array($feUser)){
			$this->initLocalCObject();
			$this->local_cObj->setCurrentVal($feUser[$this->conf['view.'][$this->conf['view'].'.']['event.']['cruser_name.']['db_field']]);
			$sims['###CRUSER_NAME###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.']['event.']['cruser_name'], $this->conf['view.'][$this->conf['view'].'.']['event.']['cruser_name.']);
		}
	}
	
	function getNow(){
		$now = new tx_cal_date();
		$now->setTZbyId('UTC');
		return $now;
	}
	
	function getToday(){
		$today = new tx_cal_date();
		$today->setTZbyId('UTC');
		$today->setHour(0);
		$today->setMinute(0);
		$today->setSecond(0);
		return $today;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']);
}
?>
