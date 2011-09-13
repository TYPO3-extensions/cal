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
    
	function tx_cal_phpicalendar_model($controller, $row, $isException, $serviceKey) {
		$this->tx_cal_model($controller, $serviceKey);
		$this->createEvent($row, $isException);
		$this->isException = $isException;
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
		if(!$row['allday']){
			$this->setStartHour($row['start_time']);
			$this->setEndHour($row['end_time']);
		}
		if($row['start_time']==0 && $row['end_time']==0){
			$row['allday'] = 1;
		}
		$this->setStartDate(getDayFromTimestamp($row['start_date']));
		$this->setEndDate(getDayFromTimestamp($row['end_date']));
		$this->setAllday($row['allday']);

		

		$this->setTitle($row['title']);
		$this->setCategories($row['categories']);
		$this->setCalendarUid($row['calendar_id']);
		$this->setFreq($row['freq']);
		$this->setByDay($row['byday']);
		$this->setByMonthDay($row['bymonthday']);
		$this->setByMonth($row['bymonth']);
		$this->setUntil($row['until']);
		$this->setCount($row['cnt']);
		$this->setInterval($row['intrval']);

		/* new */
		$this->setEventType($row['type']);
		$this->setPage($row['page']);
		$this->setExtUrl($row['ext_url']);
		/* new */

		$this->setImage($row['image']);
		$this->setImageTitleText($row['imagetitletext']);
		$this->setImageAltText($row['imagealttext']);
		$this->setImageCaption($row['imagecaption']);

		if ($row['attachment']) {
			$fileArr = explode(',', $row['attachment']);
			while (list (, $val) = each($fileArr)) {
				// fills the marker ###FILE_LINK### with the links to the atached files
				$this->addAttachmentURL($val);
			}
		}
		if($row['exception_single_ids']){
			$ids = explode(',',$row['exception_single_ids']);
			foreach($ids as $id){
				$this->addExceptionSingleId($id); 
			}
		}
		if($row['exception_group_ids']){
			$ids = explode(',',$row['exception_group_ids']);
			foreach($ids as $id){
				$this->addExceptionGroupId($id); 
			}
		}

		$this->eventOwner = $row['event_owner'];
		
		$this->setTeaser($this->controller->pi_RTEcssText($row['teaser']));

		if (!$isException) {

			$this->setDescription($this->controller->pi_RTEcssText($row['description']));

			if ($row['location_id'] != 0) {
				$this->setLocationId($row['location_id']);
			} else {
				$this->setLocation($row['location']);
			}
			$this->setLocationPage($row['location_pid']);

			if ($row['organizer_id'] != 0) {
				$this->setOrganizerId($row['organizer_id']);
			} else {
				$this->setOrganizer($row['organizer']);
			}
			$this->setOrganizerPage($row['organizer_pid']);
		}

		$this->sharedUsers = array ();
		$table = 'tx_cal_event_shared_user_mm';
		$select = 'uid_foreign';
		$where = 'uid_local = ' . $this->getUid();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$this->sharedUsers[] = $row['uid_foreign'];
		}

		//startdate but no enddate fix
		if ($this->startdate && !$this->enddate) {
			$this->setEnddate($this->startdate);
		}
	}

	function cloneEvent() {
		$tx_cal_phpicalendar_model = t3lib_div :: makeInstanceClassName('tx_cal_phpicalendar_model');
		$event = new $tx_cal_phpicalendar_model ('', $this->getValuesAsArray(), $this->isException, $this->getType());
		$event->setIsClone(true);
		return $event;
	}

	/**
	 *  Gets the location of the event.  Location does not exist in the default
	 *  model, only in calexampl3.
	 *  
	 *  @return		string		The location.
	 */
	function getLocation() {
		return $this->location;
	}

	/**
	 *  Sets the location of the event.  Location does not exist in the default
	 *  model, only in calexampl3.
	 *
	 *  @param		string		The location.
	 *  @return		void
	 */
	function setLocation($location) {
		$this->location = $location;
	}
	

	/**
	 *  Gets the teaser of the event. 
	 *  
	 *  @return		string		The teaser.
	 */
	function getTeaser() {
		return $this->teaser;
	}

	/**
	 *  Sets the teaser of the event.
	 *
	 *  @param		string		The location.
	 *  @return		void
	 */
	function setTeaser($teaser) {
		$this->teaser = $teaser;
	}

	function getLocationLink($view) {
		if ($this->getLocationId() > 0) {
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure'] ? $this->confArr['useLocationStructure'] : 'tx_cal_location');
			$location = $this->modelObj->findLocation($this->getLocationId(),$useLocationStructure);

			if(is_object($location)) {
				/* If a specific location page is defined, link to it */
				if($this->getLocationPage() > 0){
					$locationLink =  $this->cObj->stdWrap(
						$this->controller->pi_linkTP($location->getName(), array (), $this->conf['cache'], $this->getLocationPage()
					),$this->conf['view.'][$view.'.']['location_stdWrap.']);
				} else {
					/* If location view is allowed, link to it */
					if($this->rightsObj->isViewEnabled($this->conf['view.']['locationLinkTarget']) || $this->conf['view.']['location.']['locationViewPid']){
						$GLOBALS['TSFE']->ATagParams = 'title="' .$this->getLocation().'"'; 
						$locationLink = $this->cObj->stdWrap(
							$this->controller->pi_linkTP_keepPIvars($location->getName(), array (
								'view' => 'location',
								'lastview' => $this->controller->extendLastView(), 
								'uid' => $this->getLocationId(), 
								'type' => $useLocationStructure
							),$this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['location.']['locationViewPid']
						),$this->conf['view.'][$view.'.']['location_stdWrap.']);
					} else {
						/* Just show the name of the location */ 
						$locationLink = $this->cObj->stdWrap($location->getName(), $this->conf['view.'][$view.'.']['location_stdWrap.']);
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
		if ($this->getOrganizerId() > 0) {
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($this->confArr['useOrganizerStructure'] ? $this->confArr['useOrganizerStructure'] : 'tx_cal_organizer');
			$organizer = $this->modelObj->findOrganizer($this->getOrganizerId(),$useOrganizerStructure);
			
			if(is_object($organizer)) {

				/* If a specific organizer page is defined, link to it */
				if($this->getOrganizerPage() > 0){
					$organizerLink = $this->cObj->stdWrap(
						$this->controller->pi_linkTP($organizer->getName(), array (), $this->conf['cache'], $this->getOrganizerPage()
					),$this->conf['view.'][$view.'.']['organizer_stdWrap.']);
				} else {
					/* If organizer view is allowed, link to it */
					if($this->rightsObj->isViewEnabled($this->conf['view.']['organizerLinkTarget']) || $this->conf['view.']['organizer.']['organizerViewPid']){
						$GLOBALS['TSFE']->ATagParams = 'title="' .$this->getOrganizer().'"'; 
						   $organizerLink = $this->cObj->stdWrap(
							   $this->controller->pi_linkTP_keepPIvars($organizer->getName(), array (
								'view' => 'organizer',
								'lastview' => $this->controller->extendLastView(), 
								   'uid' => $this->getOrganizerId(), 
								   'type' => $useOrganizerStructure,
							   ), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['organizer.']['organizerViewPid']
						   ),$this->conf['view.'][$view.'.']['organizer_stdWrap.']); 
					} else {
						/* Just show the name of the organizer */
						$organizerLink = $this->cObj->stdWrap($organizer->getName(), $this->conf['view.'][$view.'.']['organizer_stdWrap.']);
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
		if ($this->conf['view.']['event.']['differentStyleIfOwnEvent'] && $this->rightsObj->getUserId() == $this->getCreateUserId()) {
			return $this->conf['view.']['event.']['headerStyleOfOwnEvent'];
		} else if (!empty ($this->categories) && $this->categories[0]->getHeaderStyle() != '') {
			return $this->categories[0]->getHeaderStyle();
		}
		return $this->headerstyle;
	}
	
	/**
	 * Returns the bodystyle name
	 */
	function getBodyStyle() {
		if ($this->conf['view.']['event.']['differentStyleIfOwnEvent'] && $this->rightsObj->getUserId() == $this->getCreateUserId()) {
			return $this->conf['view.']['event.']['bodyStyleOfOwnEvent'];
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

	/**
	 *  Sets the createUserId of the event.
	 *
	 *  @param		string		The create user id.
	 *  @return		void
	 */
	function setCreateUserId($createUserId) {
		$this->createUserId = $createUserId;
	}

	function renderEventForDay() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_DAY###');
	}

	function renderEventForWeek() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_WEEK###');
	}

	function renderEventForAllDay() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_ALLDAY###');
	}

	function renderEventForMonth() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_MONTH###');
	}
	
	function renderEventForYear() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_YEAR###');
	}

	function renderEvent() {
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT###');
	}
	
	function fillTemplate($subpartMarker){
		$page = $this->cObj->fileResource($this->conf['view.']['event.']['phpicalendarEventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no event template file found:</h3>' . $this->conf['view.']['event.']['phpicalendarEventTemplate'];
		}
		$page = $this->cObj->getSubpart($page,$subpartMarker);
		if(!$page){
			return 'could not find the '.$subpartMarker.' subpart-marker in '.$this->conf['view.']['event.']['phpicalendarEventTemplate'];
		}
		$rems = array ();
		$sims = array ();
		$wrapped = array();
		$this->getEventMarker($page, $rems, $sims, $wrapped);
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, $wrapped);
	}

	function renderEventPreview() {
		$this->isPreview = true;
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_PREVIEW###');
	}
	
	function renderTomorrowsEvent() {
		$this->isTomorrow = true;
		return $this->fillTemplate('###TEMPLATE_PHPICALENDAR_EVENT_TOMORROW###');
	}

	function getSubscriptionMarker(& $template, & $rems, & $sims) {
		$uid = $this->conf['uid'];
		$type = $this->conf['type'];
		$monitoring = $this->conf['monitor'];
		$getdate = $this->conf['getdate'];
		$captchaStr = 0;
		$rems['###MONITOR_LOOP###'] = '';
		$sims['###NOTLOGGEDIN_NOMONITORING_HEADING###'] = '';
		$sims['###NOTLOGGEDIN_NOMONITORING_SUBMIT###'] = '';
		$sims['###NOTLOGGEDIN_MONITORING_HEADING###'] = '';
		$sims['###NOTLOGGEDIN_MONITORING_SUBMIT###'] = '';
		$sims_temp['L_CAPTCHA_START_SUCCESS'] = '';
		$sims_temp['L_CAPTCHA_STOP_SUCCESS'] = '';

		if ($this->conf['allowSubscribe'] == 1 && $uid) {
			if ($monitoring != null && $monitoring != '') {

				$user_uid = $this->rightsObj->getUserId();
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

								if (($captchaStr && $this->controller->piVars['captcha'] === $captchaStr) ||
									($this->conf['subscribeWithCaptcha'] == 0)) {
									//send confirm email!!
									$email = $this->controller->piVars['email'];

									require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
									$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
									$mailer->start();
									$mailer->from_email = $this->conf['view.']['event.']['notify.']['emailAddress'];
									$mailer->from_name = $this->conf['view.']['event.']['notify.']['fromName'];
									$mailer->replyto_email = $this->conf['view.']['event.']['notify.']['emailReplyAddress'];
									$mailer->replyto_name = $this->conf['view.']['event.']['notify.']['replyToName'];
									$mailer->organisation = $this->conf['view.']['event.']['notify.']['organisation'];

									$local_template = $this->cObj->fileResource($this->conf['view.']['event.']['notify.']['confirmTemplate']);

									$htmlTemplate = $this->cObj->getSubpart($local_template,'###HTML###');
									$plainTemplate = $this->cObj->getSubpart($local_template,'###PLAIN###');
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getEventMarker($htmlTemplate,$local_rems,$local_switch, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'start', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$this->getCreationDate())));
									$htmlTemplate = $this->cObj->substituteMarkerArrayCached($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getEventMarker($plainTemplate,$local_rems,$local_switch, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'start', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$this->getCreationDate())));

									$plainTemplate = $this->cObj->substituteMarkerArrayCached($plainTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$mailer->subject = $this->conf['view.']['event.']['notify.']['confirmTitle'];

									
									$rems['###MONITOR_LOOP###'] = $this->controller->pi_getLL('l_monitor_start_thanks');
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
									$sims_temp['L_CAPTCHA_START_SUCCESS'] = $this->controller->pi_getLL('l_monitor_wrong_captcha');
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

								if (($captchaStr && $this->controller->piVars['captcha'] === $captchaStr) ||
									($this->conf['subscribeWithCaptcha'] == 0)) {
									$email = $this->controller->piVars['email'];
									$table = 'tx_cal_unknown_users';
									$select = 'crdate';
									$where = 'email = "' . $email . '"';
									$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
									$crdate = 0;
									while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
										$crdate = $row['crdate'];
										break;
									}
									require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
									$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
									$mailer->start();
									$mailer->from_email = $this->conf['view.']['event.']['notify.']['emailAddress'];
									$mailer->from_name = $this->conf['view.']['event.']['notify.']['fromName'];
									$mailer->replyto_email = $this->conf['view.']['event.']['notify.']['emailReplyAddress'];
									$mailer->replyto_name = $this->conf['view.']['event.']['notify.']['replyToName'];
									$mailer->organisation = $this->conf['view.']['event.']['notify.']['organisation'];

									$local_template = $this->cObj->fileResource($this->conf['view.']['event.']['notify.']['unsubscribeConfirmTemplate']);

									$htmlTemplate = $this->cObj->getSubpart($local_template,'###HTML###');
									$plainTemplate = $this->cObj->getSubpart($local_template,'###PLAIN###');
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getEventMarker($htmlTemplate,$local_rems,$local_switch, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$crdate)));
									$htmlTemplate = $this->cObj->substituteMarkerArrayCached($htmlTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$local_switch = array();
									$local_rems = array();
									$local_wrapped = array();
									$this->getEventMarker($plainTemplate,$local_rems,$local_switch, $local_wrapped, 'event');
									$local_switch['###CONFIRM_LINK###'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->controller->pi_getPageLink($this->conf['view.']['event.']['notify.']['subscriptionViewPid'], '', array ('tx_cal_controller[view]' => 'subscription','tx_cal_controller[monitor]' => 'stop', 'tx_cal_controller[email]' => $email, 'tx_cal_controller[uid]' => $this->getUid(), 'tx_cal_controller[sid]' => md5($this->getUid().$email.$crdate)));
									$plainTemplate = $this->cObj->substituteMarkerArrayCached($plainTemplate, $local_switch, $local_rems, $local_wrapped);
									
									$mailer->subject = $this->conf['view.']['event.']['notify.']['unsubscribeConfirmTitle'];
									
									$rems['###MONITOR_LOOP###'] = $this->controller->pi_getLL('l_monitor_stop_thanks');
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
									$sims_temp['L_CAPTCHA_STOP_SUCCESS'] = $this->controller->pi_getLL('l_monitor_wrong_captcha');
								}
							}
							break;
						}
						
				}
				
			}
			
			/* If we have a logged in user */
			if ($this->rightsObj->isLoggedIn() && $this->conf['subscribeFeUser'] == 1) {
				$select = '*';
				$from_table = 'tx_cal_fe_user_event_monitor_mm';
				$whereClause = 'uid_foreign = ' . $this->rightsObj->getUserId() .
				' AND uid_local = ' . $uid;

				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from_table, $whereClause, $groupBy = '', $orderBy = '', $limit = '');
				$found_one = false;
				while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_monitor_event_logged_in_monitoring') . '" alt="' . $this->controller->pi_getLL('l_monitor_event_logged_in_monitoring') . '"';
					$rems['###MONITOR_LOOP###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_monitor_event_logged_in_monitoring'), array (
						'view' => 'event',
						'monitor' => 'stop',
						'type' => $type,
						'uid' => $uid,
					), $this->conf['cache'], $this->conf['clear_anyway']);
					$found_one = true;
				}
				if (!$found_one) {
					$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring') . '" alt="' . $this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring') . '"';
					$rems['###MONITOR_LOOP###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring'), array (
						'view' => 'event',
						'monitor' => 'start',
						'type' => $type,
						'uid' => $uid,
					), $this->conf['cache'], $this->conf['clear_anyway']);
				}
			} else { /* Not a logged in user */
			
				/* If a CAPTCHA is required to subscribe, add a couple extra markers */
				if ($this->conf['subscribeWithCaptcha'] == 1 && t3lib_extMgm :: isLoaded('captcha')) {
					$sims_temp['CAPTCHA_SRC'] = '<img src="'.t3lib_extMgm :: siteRelPath('captcha') . 'captcha/captcha.php'.'" alt="" />';
					$sims_temp['L_CAPTCHA_TEXT'] = $this->controller->pi_getLL('l_captcha_text');
					$sims_temp['CAPTCHA_TEXT'] = '<input type="text" size=10 name="tx_cal_controller[captcha]" value="">';
				} else {
					$sims_temp['CAPTCHA_SRC'] = '';
					$sims_temp['L_CAPTCHA_TEXT'] = '';
					$sims_temp['CAPTCHA_TEXT'] = '';
				}
				
				$notLoggedinNoMonitoring = $this->cObj->getSubpart($template, '###NOTLOGGEDIN_NOMONITORING###');
				$parameter = array (
					'no_cache' => 1,
					'view' => 'event',
					'monitor' => 'start',
					'type' => $type,
					'uid' => $uid
				);
				$actionUrl = $this->controller->pi_linkTP_keepPIvars_url($parameter);

				$parameter2 = array (
					'no_cache' => 1,
					'getdate' => $getdate,
					'lastview' => $this->controller->extendLastView(), 'view' => 'event', 'monitor' => 'stop');
					
				$actionUrl2 = $this->controller->pi_linkTP_keepPIvars_url($parameter2);
				$sims_temp['NOTLOGGEDIN_NOMONITORING_HEADING'] = $this->controller->pi_getLL('l_monitor_event_logged_in_nomonitoring');
				$sims_temp['NOTLOGGEDIN_NOMONITORING_SUBMIT'] = $this->controller->pi_getLL('l_submit');
				$sims_temp['L_ENTER_EMAIL'] = $this->controller->pi_getLL('l_enter_email');
				$sims_temp['ACTIONURL'] = $actionUrl;
				$monitor = $this->controller->replace_tags($sims_temp, $notLoggedinNoMonitoring);

				$sims_temp['ACTIONURL'] = $actionUrl2;
				$notLoggedinMonitoring = $this->cObj->getSubpart($template, '###NOTLOGGEDIN_MONITORING###');
				$sims_temp['NOTLOGGEDIN_MONITORING_HEADING'] = $this->controller->pi_getLL('l_monitor_event_logged_in_monitoring');
				$sims_temp['NOTLOGGEDIN_MONITORING_SUBMIT'] = $this->controller->pi_getLL('l_submit');
				$sims_temp['L_ENTER_EMAIL'] = $this->controller->pi_getLL('l_enter_email');

				$monitor .= $this->controller->replace_tags($sims_temp, $notLoggedinMonitoring);
				$rems['###MONITOR_LOOP###'] = $monitor;
			} 
		} else {
			$rems['###MONITOR_LOOP###'] = '';
		}
	}

	function getStartAndEndMarker(& $template, & $rems, & $sims, $view) {
		if (($this->getStarttime() == $this->getEndtime())) {
			$sims['###STARTTIME_LABEL###'] = '';
			$sims['###ENDTIME_LABEL###'] = '';
			$sims['###STARTTIME###'] = '';
			$sims['###ENDTIME###'] = '';
			$sims['###STARTDATE###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$view.'.']['eventDateFormat'], $this->getStartDate()),$this->conf['view.'][$view.'.']['startdate_stdWrap.']);

			$sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_allday');
			$sims['###ENDDATE###'] = '';
			$sims['###ENDDATE_LABEL###'] = '';
		} else {
			if ($this->getStartHour() == 0) {
				$sims['###STARTTIME_LABEL###'] = '';
				$sims['###STARTTIME###'] = '';
			} else {
				$sims['###STARTTIME_LABEL###'] = $this->controller->pi_getLL('l_event_starttime');
				$sims['###STARTTIME###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$view.'.']['eventTimeFormat'], $this->getStarttime()),$this->conf['view.'][$view.'.']['starttime_stdWrap.']);

			}
			if ($this->getEndHour() == 0) {
				$sims['###ENDTIME_LABEL###'] = '';
				$sims['###ENDTIME###'] = '';
			} else {
				$sims['###ENDTIME_LABEL###'] = $this->controller->pi_getLL('l_event_endtime');
				$sims['###ENDTIME###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$view.'.']['eventTimeFormat'], $this->getEndtime()),$this->conf['view.'][$view.'.']['endtime_stdWrap.']);
				
			}
			$sims['###STARTDATE_LABEL###'] = $this->controller->pi_getLL('l_event_startdate');
			$sims['###STARTDATE###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$view.'.']['eventDateFormat'], $this->getStartDate()),$this->conf['view.'][$view.'.']['startdate_stdWrap.']);

			if ($this->conf['view.'][$view.'.']['dontShowEndDateIfEqualsStartDate'] && $this->getEndDate() == $this->getStartDate()) {
				$sims['###ENDDATE_LABEL###'] = '';
				$sims['###ENDDATE###'] = '';
			} else {
				$sims['###ENDDATE_LABEL###'] = $this->controller->pi_getLL('l_event_enddate');
				$sims['###ENDDATE###'] = $this->cObj->stdWrap(gmstrftime($this->conf['view.'][$view.'.']['eventDateFormat'], $this->getEndDate()),$this->conf['view.'][$view.'.']['enddate_stdWrap.']);
			}
		}
	}

	function getTitleMarker(& $template, & $rems, & $sims, $view) {
		if($this->isTomorrow && !in_array($view,array('create_event','edit_event'))){
			$sims['###TITLE###'] = $this->cObj->stdWrap($this->getTitle(),$this->conf['view.']['other.']['tomorrowsEvents_stdWrap.']);
		} else if($this->conf['view.'][$view.'.']['alldayTitle_stdWrap.']){
			$sims['###TITLE###'] = $this->cObj->stdWrap($this->getTitle(),$this->conf['view.'][$view.'.']['alldayTitle_stdWrap.']);
		}else if($this->conf['view.'][$view.'.']['title_stdWrap.']){
			$sims['###TITLE###'] = $this->cObj->stdWrap($this->getTitle(),$this->conf['view.'][$view.'.']['title_stdWrap.']);
		} else {
			$sims['###TITLE###'] = $this->getTitle();
		}
	}

	function getOrganizerMarker(& $template, & $rems, & $sims, $view) {
		if ($this->getOrganizerId() > 0) {
			$sims['###ORGANIZER###'] = $this->getOrganizerLink($view);
		} else {
			if($this->getOrganizerPage() >0){
				$sims['###ORGANIZER###'] = $this->controller->pi_linkTP($this->getOrganizer(), array (), $this->conf['cache'], $this->getOrganizerPage());
			}else{
				if($this->conf['view.'][$view.'.']['organizer_stdWrap.']){
					$sims['###ORGANIZER###'] = $this->cObj->stdWrap($this->getOrganizer(), $this->conf['view.'][$view.'.']['organizer_stdWrap.']);
				}else{
					$sims['###ORGANIZER###'] = $this->getOrganizer();
				}
			}
		}
	}

	function getLocationMarker(& $template, & $rems, & $sims, $view) {
		if ($this->getLocationId() > 0) {
			$sims['###LOCATION###'] = $this->getLocationLink($view);
		} else {
			if($this->getLocationPage() >0){
				$sims['###LOCATION###'] = $this->controller->pi_linkTP($this->getLocation(), array (), $this->conf['cache'], $this->getLocationPage());
			}else{
				if($this->conf['view.'][$view.'.']['location_stdWrap.']){
					$sims['###LOCATION###'] = $this->cObj->stdWrap($this->getLocation(), $this->conf['view.'][$view.'.']['location_stdWrap.']);
				}else{
					$sims['###LOCATION###'] = $this->getLocation();
				}
			}
		}
	}

	function getDescriptionMarker(& $template, & $rems, & $sims, $striptags=false, $view) {
	    if($striptags){
	        $sims['###DESCRIPTION_STRIPTAGS###'] = strip_tags($this->cObj->stdWrap($this->getDescription(),$this->conf['view.'][$view.'.']['description_stdWrap.']));
	    }else{
	        if($this->isPreview){
	        	$sims['###DESCRIPTION###'] = $this->cObj->stdWrap($this->getDescription(), $this->conf['view.'][$view.'.']['preview_stdWrap.']);
	        } else {
	        	$sims['###DESCRIPTION###'] = $this->cObj->stdWrap($this->getDescription(),$this->conf['view.'][$view.'.']['description_stdWrap.']);
	        }
	    }
	} 
	
	function getTeaserMarker(& $template, & $rems, & $sims, $view) {
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		if($confArr['useTeaser']) {
	    	$sims['###TEASER###'] = $this->cObj->stdWrap($this->getTeaser(),$this->conf['view.'][$view.'.']['teaser_stdWrap.']);
		} else {
			$sims['###TEASER###'] = '';
		}
	}

	function getIcsLinkMarker(& $template, & $rems, & $sims, $view, $linktext='') {
		$sims['###ICSLINK###'] = '';
		if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
			$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_event_icslink') . '" alt="' . $this->controller->pi_getLL('l_event_icslink') . '"';
			$params = array ($this->prefixId . '[type]' => $this->getType(), $this->prefixId . '[view]' => 'single_ics', $this->prefixId . '[uid]' => $this->getUid());
			$sims['###ICSLINK###'] = $this->controller->pi_linkToPage($linktext==''?$this->controller->pi_getLL('l_event_icslink'):$linktext, $GLOBALS['TSFE']->id.',150','', $params);
		}
	}

	function getCategoryMarker(& $template, & $rems, & $sims, $view) {
		$sims['###CATEGORY###'] = $this->getCategoriesAsString(false);
	}
	
	function getCategoryLinkMarker(& $template, & $rems, & $sims, $view) {
		$sims['###CATEGORY_LINK###'] = $this->getCategoriesAsString();
	}
    function getCategoryHeaderStyle(& $template, & $rems, & $sims, $view) {
		$sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
	}
	
	function getCategoryBodyStyle(& $template, & $rems, & $sims, $view) {
		$sims['###BODYSTYLE###'] = $this->getBodyStyle();
	}
	
	/**
	 * Returns the calendar style name
	 */
	function getCalendarStyle(& $template, & $rems, & $sims, $view) {
		$sims['###CALENDARSTYLE###'] = $this->cObj->stdWrap($this->getCalendarUid(), $this->conf['view.'][$this->conf['view'].'.']['calendarStyle_stdWrap.']);
	}
	
    
	function getMapMarker(& $template, & $rems, & $sims, $view) {
		$sims['###MAP###'] = '';
		if ($this->conf['view.']['event.']['showMap'] && $this->getLocationId()) {
			/* Pull values from Flexform object into individual variables */

			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($this->confArr['useLocationStructure'] ? $this->confArr['useLocationStructure'] : 'tx_cal_location');
			$location = $this->modelObj->findLocation($this->getLocationId(), $useLocationStructure);
			$locationMarkerArray = $location->getLocationMarker();
			$sims['###MAP###'] = $locationMarkerArray['###MAP###'];
		}
	}

	function getAttachmentMarker(& $template, & $rems, & $sims, $view) {
		$sims['###ATTACHMENT###'] = '';
		
		if (count($this->getAttachmentURLs()) > 0) {
			$files_stdWrap = t3lib_div :: trimExplode('|', $this->conf['view.']['event.']['attachment_stdWrap.']['wrap']);
			$sims['###ATTACHMENT###'] = $files_stdWrap[0] . $this->cObj->stdWrap($this->controller->pi_getLL('textFiles'), $this->conf['view.']['event.']['attachmentHeader_stdWrap.']);
			foreach ($this->getAttachmentURLs() as $val) {
				$filelinks .= $this->cObj->filelink($val, $this->conf['view.']['event.']['attachment.']);
			}
			$sims['###ATTACHMENT###'] = $this->cObj->stdWrap($filelinks . $files_stdWrap[1], $this->conf['view.']['event.']['attachment_stdWrap.']);
		}
	}

	function getEventMarker(& $template, & $rems, & $sims, & $wrapped, $view='') {
	
		if($view==''){
			$view = $this->conf['view'];
		}
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		foreach ($allMarkers as $marker) {
			switch ($marker) {
				case 'MONITOR_LOOP' :
					$this->getSubscriptionMarker($template, $rems, $sims, $view);
					break;
				case 'EVENT_LINK':
					$wrapped['###EVENT_LINK###'] = explode('|',$this->controller->getLinkToEvent($this, '|',$this->conf['view'], gmdate('Ymd',$this->getStarttime())));
					break;
				case 'ICSLINK':
					if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
						$this->getIcsLinkMarker($template, $rems, $sims, $view,'|');
						$wrapped['###ICSLINK###'] = explode('|',$this->cObj->stdWrap($sims['###ICSLINK###'], $this->conf['view.']['event.']['ics_stdWrap.']));
						unset($sims['###ICSLINK###']);
					}else{
						$rems['###ICSLINK###'] = '';
					}
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
				        $this->$funcFromMarker($template, $rems, $sims, $view);
				    } 
					break;
			}
		}

		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'HEADING':
					$sims['###HEADING###'] = $this->controller->pi_getLL('l_event');
					break;
				case 'EDITLINK':
					$sims['###EDITLINK###'] = '';
					if ($this->isUserAllowedToEdit()) {
						$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_edit_event') . '" alt="' . $this->controller->pi_getLL('l_edit_event') . '"';
						$sims['###EDITLINK###'] = $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['event.']['editIcon'], array (
							'view' => 'edit_event',
						'type' => $this->getType(), 'uid' => $this->getUid(), 'getdate' => gmdate('Ymd',$this->getStartdate()),'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['editEventViewPid']);
					}
					if ($this->isUserAllowedToDelete()) {
						$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_delete_event') . '" alt="' . $this->controller->pi_getLL('l_delete_event') . '"';
						$sims['###EDITLINK###'] .= $this->controller->pi_linkTP_keepPIvars($this->conf['view.']['event.']['deleteIcon'], array (
							'view' => 'delete_event',
						'type' => $this->getType(), 'uid' => $this->getUid(), 'getdate' => gmdate('Ymd',$this->getStartdate()), 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['deleteEventViewPid']);
					}
					break;
				case 'MORE_LINK':
					$sims['###MORE_LINK###'] = '';
					if ($this->conf['view.']['event.']['isPreview'] && $this->conf['preview']) {
						$GLOBALS['TSFE']->ATagParams = 'title="' . $this->controller->pi_getLL('l_more') . '" alt="' . $this->controller->pi_getLL('l_more') . '"';
						$sims['###MORE_LINK###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_more'), array (
							'page_id' => $GLOBALS['TSFE']->id,
							'preview' => null,
							'view' => event,
						'uid' => $this->getUid(), 'type' => $this->getType(), 'lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['eventViewPid']);
					}
					break;
				
				case 'STARTDATE' :
				case 'ENDDATE' :
				case 'STARTTIME' :
				case 'ENDTIME' :
					$this->getStartAndEndMarker($template, $rems, $sims, $view);
					unset ($allSingleMarkers['STARTDATE']);
					unset ($allSingleMarkers['ENDDATE']);
					unset ($allSingleMarkers['STARTDATE_LABEL']);
					unset ($allSingleMarkers['ENDDATE_LABEL']);
					unset ($allSingleMarkers['STARTTIME']);
					unset ($allSingleMarkers['STARTTIME_LABEL']);
					unset ($allSingleMarkers['ENDTIME']);
					unset ($allSingleMarkers['ENDTIME_LABEL']);
					break;
				case 'TITLE' :
					$this->getTitleMarker($template, $rems, $sims, $view);
					break;
				case 'ORGANIZER' :
					$this->getOrganizerMarker($template, $rems, $sims, $view);
					break;
				case 'LOCATION' :
					$this->getLocationMarker($template, $rems, $sims, $view);
					break;
				case 'TEASER' :
					$this->getTeaserMarker($template, $rems, $sims, $view);
					break;
				case 'DESCRIPTION' :
					$this->getDescriptionMarker($template, $rems, $sims, false, $view);
					break;
				case 'DESCRIPTION_STRIPTAGS' :
					$this->getDescriptionMarker($template, $rems, $sims, true, $view);
					break;
				case 'CATEGORY' :
					$this->getCategoryMarker($template, $rems, $sims, $view);
					break;
				case 'CATEGORY_LINK':
					$this->getCategoryLinkMarker($template, $rems, $sims, $view);
					break;
				case 'HEADERSTYLE':
					$this->getCategoryHeaderStyle($template, $rems, $sims, $view);
					break;
				case 'BODYSTYLE':
					$this->getCategoryBodyStyle($template, $rems, $sims, $view);
					break;
				case 'CALENDARSTYLE':
					$this->getCalendarStyle($template, $rems, $sims, $view);
					break;
				case 'MAP' :
					$this->getMapMarker($template, $rems, $sims, $view);
					break;
				case 'ATTACHMENT' :
					$this->getAttachmentMarker($template, $rems, $sims, $view);
					break;
				case 'IMAGE' :
					$this->getImageMarkers($sims, $this->conf['view.'][$view.'.']['image_stdWrap.'], true);
					$sims['###IMAGE###'] = $this->cObj->stdWrap($sims['###IMAGE###'],$this->conf['view.'][$view.'.']['image_stdWrap.']);
					break;
				case 'ABS_IMAGE' :
					$this->getImageMarkers($sims, $this->conf['view.'][$view.'.'], true, true);
					break;
				case 'STATUS' :
					$event_status = strtolower($this->getStatus());
					$confirmed = '';
					if ($event_status != '') {
						$confirmed = $this->cObj->stdWrap($event_status, $this->conf['view.'][$this->conf['view'].'.']['statusIcon_stdWrap.']);
					}
					else if (is_array($this->getCalRecu()) && count($this->getCalRecu())>0) {
						$confirmed = $this->conf['view.'][$this->conf['view'].'.']['recurringIcon'];
					}
					$sims['###STATUS###'] = $confirmed;
					break;
				case 'ACTIONURL':
				case 'L_ENTER_EMAIL':
				case 'L_CAPTCHA_TEXT':
				case 'CAPTCHA_SRC':
				case 'IMG_PATH':
					//do nothing
					break;
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
					    $this->$funcFromMarker($template, $rems, $sims);
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.']['event.'][strtolower($marker).'_stdWrap.']);
					}
                    
                    
                    if (preg_match('/MODULE__([A-Z0-9_-|])*/', $marker)) {
                        $tmp=explode('___',substr($marker, 8));
                        $modules[$tmp[0]][]=$tmp[1];
                    } else {
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.']['event.'][strtolower($marker).'_stdWrap.']);
					}
                    
					break;
			}
		}
        
        #use alternativ way of MODULE__MARKER
        #syntax: ###MODULE__MODULENAME___MODULEMARKER###
        #collect them, call each Modul, retrieve Array of Markers and replace them
        #this allows to spread the Module-Markers over complete template instead of one time
        #also work with old way of MODULE__-Marker
        
        if(is_array($modules)) {  #MODULE-MARKER FOUND
            foreach($modules as $themodule=>$markerArray) {
                $module = t3lib_div :: makeInstanceService($themodule, 'module');
                if (is_object($module)) {
				    if($markerArray[0]=='') {
                        $sims['###MODULE__'.$themodule.'###'] = $module->start($this); #old way
                    } else {
                        $moduleMarker= $module->start($this); # get Markerarray from Module
                        foreach($moduleMarker as $key=>$val) {
                           $sims['###MODULE__'.$themodule.'___'.$key.'###'] = $val;
                        }
                    }
				}    
            }
        }
        
	}

	function retrievePostData(& $insertFields) {
		$hidden = 0;
		if ($this->controller->piVars['hidden'] == 'true' && ($this->rightsObj->isAllowedToEditEventHidden() || $this->rightsObj->isAllowedToCreateEventHidden()))
			$hidden = 1;
		$insertFields['hidden'] = $hidden;

		if ($this->rightsObj->isAllowedToEditEventCategory() || $this->rightsObj->isAllowedToCreateEventCategory()) {
			$insertFields['category_id'] = intval($this->controller->piVars['category_id']);
		}
		if ($this->rightsObj->isAllowedToEditEventDateTime() || $this->rightsObj->isAllowedToCreateEventDateTime()) {
			if ($this->controller->piVars['event_start_day'] != '') {
				$time = array ();
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_start_day'], $time);
				$insertFields['start_date'] = mktime(0, 0, 0, $time[2], $time[3], $time[1]);
			} else {
				return;
			}
			if ($this->controller->piVars['event_start_time'] != '') {
				preg_match('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_start_time'], $time);
				$insertFields['start_time'] = $time[1] * 3600 + $time[2] * 60;
			}
			if ($this->controller->piVars['event_end_day'] != '') {
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_end_day'], $time);
				$insertFields['end_date'] = mktime(0, 0, 0, $time[2], $time[3], $time[1]);
			}
			if ($this->controller->piVars['event_end_time'] != '') {
				preg_match('/([0-9]{2})([0-9]{2})/', $this->controller->piVars['event_end_time'], $time);
				$insertFields['end_time'] = $time[1] * 3600 + $time[2] * 60;
			}
		}
		if ($this->rightsObj->isAllowedToEditEventTitle() || $this->rightsObj->isAllowedToCreateEventTitle()) {
			$insertFields['title'] = strip_tags($this->controller->piVars['title']);
		}

		if ($this->rightsObj->isAllowedToEditEventOrganizer() || $this->rightsObj->isAllowedToCreateEventOrganizer()) {
			$insertFields['organizer'] = strip_tags($this->controller->piVars['organizer']);
			if ($this->controller->piVars['organizer_id'] != '') {
				$insertFields['organizer_id'] = intval($this->controller->piVars['organizer_id']);
			}
		}
		if ($this->rightsObj->isAllowedToEditEventLocation() || $this->rightsObj->isAllowedToCreateEventLocation()) {
			$insertFields['location'] = strip_tags($this->controller->piVars['location']);
			if ($this->controller->piVars['location_id'] != '') {
				$insertFields['location_id'] = intval($this->controller->piVars['location_id']);
			}
		}
		if ($this->controller->piVars['description'] != '' && ($this->rightsObj->isAllowedToEditEventDescription() || $this->rightsObj->isAllowedToCreateEventDescription())) {
			$insertFields['description'] = $this->cObj->removeBadHTML($this->controller->piVars['description'], $this->conf);
		}
		if ($this->rightsObj->isAllowedToEditEventRecurring() || $this->rightsObj->isAllowedToCreateEventRecurring()) {
			if ($this->controller->piVars['frequency_id'] != '') {
				$insertFields['freq'] = intval($this->controller->piVars['frequency_id']);
			}
			if ($this->controller->piVars['by_day'] != '') {
				$insertFields['byday'] = strip_tags($this->controller->piVars['by_day']);
			}
			if ($this->controller->piVars['by_monthday'] != '') {
				$insertFields['bymonthday'] = strip_tags($this->controller->piVars['by_monthday']);
			}
			if ($this->controller->piVars['by_month'] != '') {
				$insertFields['bymonth'] = strip_tags($this->controller->piVars['by_month']);
			}
			if ($this->controller->piVars['until'] != '') {
				preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $this->controller->piVars['until'], $time);
				$insertFields['until'] = mktime(0, 0, 0, $time[2], $time[3], $time[1]);
			}
			if ($this->controller->piVars['count'] != '') {
				$insertFields['cnt'] = intval($this->controller->piVars['count']);
			}
			if ($this->controller->piVars['interval'] != '') {
				$insertFields['intrval'] = intval($this->controller->piVars['interval']);
			}
		}
	}

	function insertIdsIntoTableWithMMRelation($mm_table, $idArray, $uid, $tablename) {
		foreach ($idArray as $foreignid) {
			if (is_numeric($foreignid)) {
				$insertFields = array (
					'uid_local' => $uid,
					'uid_foreign' => $foreignid,
					'tablenames' => $tablename
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($mm_table, $insertFields);
			}
		}
	}

	function search($pidList = '') {

		$categoryIds = implode(',',$this->getCategoryArray($pidList));
		$sw = strip_tags($this->controller->piVars['query']);
		$events = array ();
		if ($sw != '') {
			$additionalWhere = 'AND tx_cal_category.uid IN (' . $categoryIds . ') AND tx_cal_event.pid IN (' . $pidList . ') '.$this->cObj->enableFields('tx_cal_event') . $this->searchWhere($sw);
			$events = $this->getEventsFromTable(true, $additionalWhere);
		}
		return $events;
	}

	/**
	 * Generates a search where clause.
	 *
	 * @param	string		$sw: searchword(s)
	 * @return	string		querypart
	 */
	function searchWhere($sw) {
		$where = $this->cObj->searchWhere($sw, $this->conf['view.']['search.']['searchEventFieldList'], 'tx_cal_event');
		return $where;
	}

	function getValuesAsArray() {
		$values = parent :: getValuesAsArray();
		$values['event_owner'] = $this->eventOwner;
		$values['teaser'] = $this->getTeaser();
		return $values;
	}

	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		if ($this->rightsObj->isCalAdmin()) {
			return true;
		}
		$editOffset = $this->conf['rights.']['edit.']['event.']['timeOffset'] * 60;

		if ($feUserUid == '') {
			$feUserUid = $this->rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $this->rightsObj->getUserGroups();
		}
		$isEventOwner = $this->isEventOwner($this->rightsObj->getUserId(), $this->rightsObj->getUserGroups());
		$isSharedUser = $this->isSharedUser($this->rightsObj->getUserId());
		if ($this->rightsObj->isAllowedToEditStartedEvents()) {
			$eventHasntStartedYet = true;
		} else {
			$eventHasntStartedYet = $this->getStarttime() > time() + $editOffset;
		}
		$isAllowedToEditEvents = $this->rightsObj->isAllowedToEditEvents();
		$isAllowedToEditOwnEventsOnly = $this->rightsObj->isAllowedToEditOnlyOwnEvents();

		if ($isAllowedToEditOwnEventsOnly) {
			return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
		}
		return $isAllowedToEditEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		if ($this->rightsObj->isCalAdmin()) {
			return true;
		}
		$deleteOffset = $this->conf['rights.']['delete.']['event.']['timeOffset'] * 60;
		if ($feUserUid == '') {
			$feUserUid = $this->rightsObj->getUserId();
		}
		if (empty ($feGroupsArray)) {
			$feGroupsArray = $this->rightsObj->getUserGroups();
		}
		$isEventOwner = $this->isEventOwner($this->rightsObj->getUserId(), $this->rightsObj->getUserGroups());
		$isSharedUser = $this->isSharedUser($this->rightsObj->getUserId());
		if ($this->rightsObj->isAllowedToDeleteStartedEvents()) {
			$eventHasntStartedYet = true;
		} else {
			$eventHasntStartedYet = $this->getStarttime() > time() + $deleteOffset;
		}
		$isAllowedToDeleteEvents = $this->rightsObj->isAllowedToDeleteEvents();
		$isAllowedToDeleteOwnEventsOnly = $this->rightsObj->isAllowedToDeleteOnlyOwnEvents();

		if ($isAllowedToDeleteOwnEventsOnly) {
			return ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
		}
		return $isAllowedToDeleteEvents && ($isEventOwner || $isSharedUser) && $eventHasntStartedYet;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_model.php']);
}
?>
