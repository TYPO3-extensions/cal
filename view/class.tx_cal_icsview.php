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


require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_icsview extends tx_cal_base_view {
	
	var $limitAttendeeToThisEmail = '';
	
	function tx_cal_icsview(){
		$this->tx_cal_base_view();
	}
	
	function drawIcsList(&$master_array, $getdate) {
		$this->_init($master_array);		
		$page = $this->cObj->fileResource($this->conf['view.']['ics.']['icsListTemplate']);
		if ($page == '') {
			return '<h3>calendar: no icsListTemplate file found:</h3>'.$this->conf['view.']['ics.']['icsListTemplate'];
		}
		
		$calendarLinkLoop = $this->cObj->getSubpart($page, '###CALENDARLINK_LOOP###');
		$return = '';
		$page = str_replace('###L_ICSLISTTITLE###',$this->controller->pi_getLL('l_icslist_title'),$page);
		$rememberUid = array();
	
    	//by calendar
    	$this->calendarService = $this->modelObj->getServiceObjByKey('cal_calendar_model', 'calendar', 'tx_cal_calendar');
    	$calendarIds = $this->calendarService->getIdsFromTable('',$this->conf['pidList'],true,true);
		
    	$calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar');

		foreach($calendarArray['tx_cal_calendar'] as $calendar){
			if(is_object($calendar)) {
				if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
					$this->initLocalCObject($calendar->getValuesAsArray());
					$this->local_cObj->setCurrentVal($calendar->getTitle());
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array('calendar' => $calendar->getUid(), 'view' => 'ics'),$this->conf['cache'],1);
					$icslink = $this->local_cObj->cObjGetSingle($this->conf['view.']['ics.']['icsViewCalendarLink'],$this->conf['view.']['ics.']['icsViewCalendarLink.']);
				}
				$calendarReturn .= str_replace('###LINK###',$icslink,$calendarLinkLoop);
			}
		}
		
		$categoryLinkLoop = $this->cObj->getSubpart($page, '###CATEGORYLINK_LOOP###');
		
		//by category
		$categories = $master_array['tx_cal_category'][0][0];  
		foreach((array)$categories as $category){
			if(is_object($category)) {
				if(in_array($category->getUid(),$rememberUid)){
					continue;
				}
				$icslink = '';
				if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
					$this->initLocalCObject($category->getValuesAsArray());
					$this->local_cObj->setCurrentVal($category->getTitle());
					$this->controller->getParametersForTyposcriptLink($this->local_cObj->data, array('category' => $category->getUid(), 'type' => 'tx_cal_phpicalendar', 'view' => 'ics'),$this->conf['cache'],1);
					$icslink = $this->local_cObj->cObjGetSingle($this->conf['view.']['ics.']['icsViewCategoryLink'],$this->conf['view.']['ics.']['icsViewCategoryLink.']);
				}
				$categoryReturn .= str_replace('###LINK###',$icslink,$categoryLinkLoop);
				$rememberUid[] = $category->getUid();
			}
		}

		$sims = array();
		$sims['###CALENDAR_LABEL###'] = $this->controller->pi_getLL('l_calendar');
		$sims['###CATEGORY_LABEL###'] = $this->controller->pi_getLL('l_category');
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, array(), array ());
		$a = array('###CATEGORYLINK_LOOP###' => $categoryReturn, '###CALENDARLINK_LOOP###' => $calendarReturn);
		return $this->finish($page, $a);
	}


	function drawIcs(&$master_array, $getdate, $sendHeaders=true, $limitAttendeeToThisEmail='') {
		$this->_init($master_array);
		$this->limitAttendeeToThisEmail = $limitAttendeeToThisEmail;
		$absFile = t3lib_div::getFileAbsFileName($this->conf['view.']['ics.']['icsTemplate']);
		$page = t3lib_div::getURL($absFile);
		
		if ($page == '') {
			return '<h3>calendar: no ics template file found:</h3>'.$this->conf['view.']['ics.']['icsTemplate'];
		}
		$ics_events = '';
		
		$select = 'tx_cal_event_deviation.*,tx_cal_index.start_datetime,tx_cal_index.end_datetime';
		$table = 'tx_cal_event_deviation right outer join tx_cal_index on tx_cal_event_deviation.uid = tx_cal_index.event_deviation_uid';
		
		$oldView = $this->conf['view'];
		$this->conf['view'] = 'single_ics';
		
		foreach($this->master_array as $eventDate => $eventTimeArray){
			if(is_a($eventTimeArray,'tx_cal_model')){
				$ics_events .= $eventTimeArray->renderEventFor('ics');
			}else{
				foreach ($eventTimeArray as $key => $eventArray) {
					foreach($eventArray as $eventUid => $event){
						if (is_object($event)) {
							$ics_events .= $event->renderEventFor('ics');
							
							$where = 'tx_cal_event_deviation.parentid = '.$event->getUid();
							$deviationResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
							if($deviationResult) {
								while ($deviationRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($deviationResult)) {
									$start = new tx_cal_date(substr($deviationRow['start_datetime'],0,8));
									$start->setHour(substr($deviationRow['start_datetime'],8,2));
									$start->setMinute(substr($deviationRow['start_datetime'],10,2));
									$end = new tx_cal_date(substr($deviationRow['end_datetime'],0,8));
									$end->setHour(substr($deviationRow['end_datetime'],8,2));
									$end->setMinute(substr($deviationRow['end_datetime'],10,2));
									unset($deviationRow['start_datetime']);
									unset($deviationRow['end_datetime']);
									$new_event = tx_cal_functions::makeInstance('tx_cal_phpicalendar_rec_deviation_model',$event,$deviationRow,$start,$end);
									$ics_events .= $new_event->renderEventFor('ics');
								}
								$GLOBALS['TYPO3_DB']->sql_free_result($deviationResult);
							}
						}
					}
				}
			}
		}
		$this->conf['view'] = $oldView;
		
		$rems = array ();
		$rems['###EVENT###'] = strip_tags($ics_events);
		$title = $getdate;
		if(!empty($this->master_array)){
			if(!is_a($this->master_array[0],'tx_cal_model')){
				if($this->controller->piVars['calendar']){
					$calendar = $this->modelObj->findCalendar($this->controller->piVars['calendar'],'tx_cal_calendar',$this->conf['pidList']);
					if(is_object($calendar)) {
						$title = $calendar->getTitle();
					}
				}else if($this->controller->piVars['category']){
					$category = $this->modelObj->findCategory($this->controller->piVars['category'],'tx_cal_category',$this->conf['pidList']);
					if(is_object($category)) {
						$title = $category->getTitle();
					}
				}
			}else{
				$title = $this->master_array[0]->getTitle();
			}
		}else if($this->controller->piVars['category']){
			$category = $this->modelObj->findCategory($this->conf['category'],'tx_cal_category',$this->conf['pidList']);
			if(is_object($category)) {
				$title = $category->getTitle();
			}
		}else if($this->controller->piVars['calendar']){
			$calendar = $this->modelObj->findCalendar($this->conf['calendar'],'tx_cal_calendar',$this->conf['pidList']);
			if(is_object($calendar)) {
				$title = $calendar->getTitle();
			}
		}
		$title .= '.ics';
		$title = strtr($title,array(' '=>'',','=>'_',));
		
		if($sendHeaders) {
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Content-Disposition: attachment; filename='.$title);
			header('Content-Type: text/ics');
			header('Pragma: ');
			header('Cache-Control:');
		}
		include(t3lib_extMgm::extPath('cal').'ext_emconf.php');
		$myem_conf = array_pop($EM_CONF);
		$method = 'PUBLISH';
		if($this->limitAttendeeToThisEmail){
			$method = 'REQUEST';
		}
		
		$return = tx_cal_functions::substituteMarkerArrayNotCached($page, array ('###CAL_VERSION###'=>$myem_conf['version'], '###METHOD###' => $method, '###TIMEZONE###' => $this->cObj->cObjGetSingle($this->conf['view.']['ics.']['timezone'],$this->conf['view.']['ics.']['timezone.'])), $rems, array ()); 
		return tx_cal_functions::removeEmptyLines($return); 
	} 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_icsview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_icsview.php']);
}
?>