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

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_icsview extends tx_cal_base_view {
	
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

		foreach($calendarIds as $calendarRow){
				if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
					$GLOBALS['TSFE']->ATagParams = 'title="'.$calendarRow['title'].'_'.$this->controller->pi_getLL('l_ics_view').'"';
					$icslink = $this->controller->pi_linkToPage($calendarRow['title'], $GLOBALS['TSFE']->id.',150','', array ($this->prefixId.'[calendar]' => $calendarRow['uid'], $this->prefixId.'[type]' => 'tx_cal_phpicalendar', $this->prefixId.'[view]' => 'ics'));
				}
				$calendarReturn .= str_replace('###LINK###',$icslink,$calendarLinkLoop);
				
		}
		
		$categoryLinkLoop = $this->cObj->getSubpart($page, '###CATEGORYLINK_LOOP###');
		
		//by category
		$categories = $master_array[0];
		foreach($categories as $category){
			if(in_array($category->getUid(),$rememberUid)){
				continue;
			}
			$icslink = '';
			if ($this->conf['view.']['ics.']['showIcsLinks'] == 1) {
				$GLOBALS['TSFE']->ATagParams = 'title="'.$category->getTitle().'_'.$this->controller->pi_getLL('l_ics_view').'"';
				$icslink = $this->controller->pi_linkToPage($category->getTitle(), $GLOBALS['TSFE']->id.',150','', array ($this->prefixId.'[category]' => $category->getUid(), $this->prefixId.'[type]' => 'tx_cal_phpicalendar', $this->prefixId.'[view]' => 'ics'));
			}
			$categoryReturn .= str_replace('###LINK###',$icslink,$categoryLinkLoop);
			$rememberUid[] = $category->getUid();
		}

		$sims = array();
		$sims['###CALENDAR_LABEL###'] = $this->controller->pi_getLL('l_calendar');
		$sims['###CATEGORY_LABEL###'] = $this->controller->pi_getLL('l_category');
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$a = array('###CATEGORYLINK_LOOP###' => $categoryReturn, '###CALENDARLINK_LOOP###' => $calendarReturn);
		return $this->finish($page, $a);
	}


	function drawIcs(&$master_array, $getdate, $sendHeaders=true) {
		$this->_init($master_array);
		$page = $this->cObj->fileResource($this->conf['view.']['ics.']['icsTemplate']);
		
		if ($page == '') {
			return '<h3>calendar: no ics template file found:</h3>'.$this->conf['view.']['ics.']['icsTemplate'];
		}
		$ics_events = '';
		$calUid = $this->conf['view.']['ics.']['calUid'];
		foreach($this->master_array as $eventDate => $eventTimeArray){
			if(is_object($eventTimeArray)){
				$ics_events .= $this->getIcsFromEvent($eventTimeArray, $page);
			}else{
				foreach ($eventTimeArray as $key => $eventArray) {
					foreach($eventArray as $eventUid => $event){
						if (is_object($event)) {
							$ics_events .= $this->getIcsFromEvent($event, $page);
						}
					}
				}
			}
		}
		$rems = array ();
		$rems['###EVENT###'] = $ics_events;
		$title = $getdate;
		if(count($this->master_array)>0){
			if(!is_object($this->master_array[0])){
				if($this->controller->piVars['calendar']){
					$calendar = $this->controller->modelObj->findCalendar($this->controller->piVars['calendar'],'tx_cal_calendar',$this->conf['pidList']);
					$title = $calendar['title'];
				}else if($this->controller->piVars['category']){
					$category = $this->controller->modelObj->findCategory($this->controller->piVars['category'],'tx_cal_category',$this->conf['pidList']);
					$title = $category->getTitle();
				}
			}else{
				$title = $this->master_array[0]->getTitle();
			}
		}else if($this->controller->piVars['category']){
			$category = $this->controller->modelObj->findCategory($this->conf['category'],'tx_cal_category',$this->conf['pidList']);
			$title = $category->getTitle();
		}else if($this->controller->piVars['calendar']){
			$calendar = $this->controller->modelObj->findCalendar($this->conf['calendar'],'tx_cal_category',$this->conf['pidList']);
			$title = $calendar['title'];
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
        return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
	}
	
	function getIcsFromEvent(&$event, &$page){
		$eventTemplate = $this->cObj->getSubpart($page, '###EVENT###');
		$sims = array ();
		//TODO: Define a UID
        $lang=$GLOBALS['TSFE']->config['config']['language']?'LANGUAGE='.$GLOBALS['TSFE']->config['config']['language']:'LANGUAGE=en';
		$sims['###UID###'] = $this->conf['view.']['ics.']['eventUidPrefix'].'_'.$event->getCalendarUid().'_'.$event->getUid();
		$sims['###DTSTAMP###'] = 'DTSTAMP:'.gmdate('Ymd', $event->getCreationDate()).'T'.gmdate('His', $event->getStarttime());
		$sims['###SUMMARY###'] = $this->cObj->stdWrap($lang.':'.$event->getTitle(),$this->conf['summary_stdWrap.']);
		$sims['###DESCRIPTION###'] = $this->cObj->stdWrap($lang.':'.ereg_replace(chr(10), '\n', $event->getDescription()),$this->conf['description_stdWrap.']);
		$sims['###LOCATION###'] = $this->cObj->stdWrap($lang.':'.$event->getLocation(),$this->conf['location_stdWrap.']);
		$sims['###CATEGORY###'] = $this->cObj->stdWrap($lang.':'.$event->getCategoriesAsString(false),$this->conf['category_stdWrap.']);
		if ($event->getStarttime() == $event->getEndtime() || $event->getEndtime() == 0) {
			$sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART;VALUE=DATE:'.gmdate('Ymd', $event->getStarttime());
			$sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = '';
		} else {
			$sims['###DTSTART_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTSTART:'.gmdate('Ymd', $event->getStarttime()).'T'.gmdate('His', $event->getStarttime());
			$sims['###DTEND_YEAR_MONTH_DAY_HOUR_MINUTE###'] = 'DTEND:'.gmdate('Ymd', $event->getEndtime()).'T'.gmdate('His', $event->getEndtime());
		}
//		$sims['###DTEND_YEAR_MONTH_DAY###'] = gmdate('Ymd', $event->getEndtime());
//		$sims['###DTEND_HOUR_MINUTE_SECOND###'] = gmdate('Hi', $event->getEndtime());
		$rrule = $this->getRrule($event);
		$sims['###RRULE###'] = $rrule!='' ? 'RRULE:'.$rrule : '';
		$exdates = '';
		$exrule = '';
		foreach ($event->getExceptionEvents() as $ex_event) {
			if ($ex_event->getFreq() == 'none') {
				$exdates .= 'EXDATE:'.gmdate('Ymd', $ex_event->getStarttime()).',';
			} else {
				$exrule .= 'EXRULE:'.$this->getRrule($ex_event);
			}
		}
		$exdates = substr($exdates, $exdates.length, -1);
		$sims['###EXDATE###'] = $exdates;
		$sims['###EXRULE###'] = $exrule;
		$temp = abs($event->getEndtime() - $event->getStarttime());

		$sims['###DURATION_DAYS###'] = gmdate('z', $temp);
		$sims['###DURATION_HOURS###'] = gmdate('H', $temp);
		$sims['###DURATION_MINUTES###'] = gmdate('i', $temp);		
		$sims['###ATTACHMENT###'] = '';

		if(count($event->getAttachmentURLs())>0){
			foreach($event->getAttachmentURLs() as $val){
				$sims['###ATTACHMENT###'] .= t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->conf['view.']['ics.']['attachment.']['path'].$val ;
			}
		}
        if($sims['###ATTACHMENT###'] != '') $sims['###ATTACHMENT###'] = 'ATTACH:'.$sims['###ATTACHMENT###'];
        
		return $this->cObj->substituteMarkerArrayCached($eventTemplate, $sims, array (), array ());
	}

	function getRrule($event) {

		$rrule = '';
		if ($event->getFreq() != 'none') {
			$rrule = 'FREQ='.$this->getFreq($event->getFreq()).';INTERVAL='.$event->getInterval().';';
			if ($event->getCount() != 0) {
				$rrule .= 'COUNT='.$event->getCount().';';
			}
			if (count($event->getByDay()) > 0) {
				$rrule .= 'BYDAY=';
				foreach ($event->getByDay() as $day) {
					$rrule .= $day.',';
				}
				$rrule = substr($rrule, $rrule.lenght, -1);
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
		}
		return strtoupper($rrule);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_icsview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_icsview.php']);
}
?>
