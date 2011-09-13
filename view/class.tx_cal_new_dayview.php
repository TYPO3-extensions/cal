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

require_once (t3lib_extMgm::extPath('cal').'view/class.tx_cal_new_timeview.php');

/**
 * Base model for the day.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_new_dayview extends tx_cal_new_timeview {
	
	var $day;
	var $month;
	var $year;
	var $weekdayNumber;
	var $hasAlldayEvents = false;
	var $Ymd;
	var $time;

	var $events = Array();
	
	/**
	 *  Constructor.
	 */
	public function tx_cal_new_dayview($day, $month, $year, $parentMonth = -1){
		$this->tx_cal_new_timeview();
		$this->mySubpart = 'DAY_SUBPART';
		$this->day = intval($day);
		$this->month = intval($month);
		$this->year = intval($year);
		$date = new tx_cal_date();
		$date->setDay($this->day);
		$date->setMonth($this->month);
		$date->setYear($this->year);
		$this->weekdayNumber = $date->format('%w');
		$this->Ymd = $date->format('%Y%m%d');
		$this->time = $date->getTime();
		if ($parentMonth >= 0) {
			$this->setParentMonth(intval($parentMonth));
		} else {
			$this->setParentMonth($this->month);
		} 
	}
	
	public function addEvent(&$event){
//		if (($event->isAllday())&&($event->getStart()->format('%Y%m%d')!=$this->Ymd) ) {
//		} else {
			$this->events[$event->getStart()->format('%H%M')][] = &$event;
//		}
	}
	
	public function getEventsMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$content = '';
		$timeKeys = array_keys($this->events);
		foreach($timeKeys as $timeKey){
			$eventKeys = array_keys($this->events[$timeKey]);
			foreach($eventKeys as $eventKey){
				if(!$this->events[$timeKey][$eventKey]->isAllday()){
					$content .= $this->events[$timeKey][$eventKey]->renderEventFor($view);
				}
			}
		}
		
		$sims['###EVENTS###'] = $content;
	}
	
	public function getEventsColumnMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$content = '';
		
		$conf = &tx_cal_registry::Registry('basic','conf');
		$dayStart = $conf['view.']['day.']['dayStart']; //'0700';   // Start time for day grid
		$dayEnd = $conf['view.']['day.']['dayEnd']; //'2300';   // End time for day grid
		$gridLength = $conf['view.']['day.']['gridLength']; //'15';    // Grid distance in minutes for day view, multiples of 15 preferred
		
		while(strlen($dayStart) < 6){
			$dayStart .= '0';
		}
		while(strlen($dayEnd) < 6){
			$dayEnd .= '0';
		}
		if($gridLength == 0){
			$gridLength = 15;
		}

		$d_start = new tx_cal_date($this->Ymd.$dayStart);
		$d_start->setTZbyId('UTC');
		$d_end = new tx_cal_date($this->Ymd.$dayEnd);
		$d_end->setTZbyId('UTC');
		
		// splitting the events into H:M, to find out if events run in parallel 
		$i = new tx_cal_date();
		$eventArray = Array();
		$viewArray = Array();
		$positionArray = Array();
		$timeKeys = array_keys($this->events);
		
		// Sort by starttime, otherwise $pos_array keys may be assigned multiple times and events may therefore overwrite each other
		asort($timeKeys);

		foreach($timeKeys as $timeKey){
			$eventKeys = array_keys($this->events[$timeKey]);
			foreach($eventKeys as $eventKey){
				if(!$this->events[$timeKey][$eventKey]->isAllday() && ($this->events[$timeKey][$eventKey]->getStart()->format('%Y%m%d')==$this->events[$timeKey][$eventKey]->getEnd()->format('%Y%m%d'))){
					$eventMappingKey = $this->events[$timeKey][$eventKey]->getType().'_'.$this->events[$timeKey][$eventKey]->getUid().'_'.$this->events[$timeKey][$eventKey]->getStart()->format('%Y%m%d%H%M%S');
					$eventArray[$eventMappingKey] = &$this->events[$timeKey][$eventKey];
					
					$i->copy($this->events[$timeKey][$eventKey]->getStart());
					$time = $i->getTime();
					$time = $time - ($time % ($gridLength * 60));
					$i = new tx_cal_date($time);
					if($i->before($d_start)){
						$i->copy($d_start);
					}
					
					$entries = 0;
					for($i;$i->before($this->events[$timeKey][$eventKey]->getEnd());$i->addSeconds($gridLength * 60)){
						$ymd = $i->format('%Y%m%d');
						$hm = $i->format('%H%M');
						$viewArray[$ymd][$hm][] = $eventMappingKey;
						$entries++;
						
						$count = count($viewArray[$ymd][$hm]);
						
						foreach($viewArray[$ymd][$hm] as $mappingKey){
							if(!$positionArray[$mappingKey] || $positionArray[$mappingKey] < $count){
								$positionArray[$mappingKey] = $count;
							}
						}
						
					}
					$rowspan_array[$this->Ymd][$eventMappingKey] = $entries;
				}
			}
		}
		
		if(!empty($viewArray[$this->Ymd])){
			$max=array();
			foreach ($viewArray[$this->Ymd] as $array_time => $time_val) {
				$c = count($viewArray[$this->Ymd][$array_time]);
				array_push($max, $c);
			}
			$nbrGridCols = max($max);
		}else{
			$nbrGridCols = 1;
		}
				
		// splitting the events into H:M, to find out if events run in parallel 
		$pos_array = Array();
		$i->copy($d_start);
		while($i->before($d_end)){
			$i_formatted = $i->format('%H%M');

			if (is_array($viewArray[$this->Ymd][$i_formatted]) && count($viewArray[$this->Ymd][$i_formatted]) > 0) {
				foreach ($viewArray[$this->Ymd][$i_formatted] as $eventKey) {
					$event = &$eventArray[$eventKey];
					$eventStart = $event->getStart();
					$eventMappingKey = $event->getType().'_'.$event->getUid().'_'.$eventStart->format('%Y%m%d%H%M%S');
					if (array_key_exists($eventMappingKey, $pos_array)) {
						$eventEnd = $event->getEnd();
						$eventEnd->subtractSeconds((($eventEnd->getMinute() % $gridLength) * 60));
						if ($i_formatted >= $eventEnd->format('%H%M')) {
							$t_array[$i_formatted][$pos_array[$eventMappingKey]] = array ('ended' => $eventMappingKey);
						} else {
							$t_array[$i_formatted][$pos_array[$eventMappingKey]] = array ('started' => $eventMappingKey);
						}
					} else {
						for ($j = 0; $j < $nbrGridCols; $j ++) {
							if (count($t_array[$i_formatted][$j]) == 0 || !isset ($t_array[$i_formatted][$j])) {
								$pos_array[$eventMappingKey] = $j;
								$t_array[$i_formatted][$j] = array ('begin' => $eventMappingKey);
								break;
							}
						}
					}
				}
			} else {
				$t_array[$i_formatted] = '';
			}
			
			$i->addSeconds($gridLength *60);
		}
	
		$sims['###EVENTS_COLUMN###'] = $this->renderEventsColumn($eventArray, $d_start, $d_end, $viewArray, $t_array, $positionArray);
	}
	
	private function renderEventsColumn(&$eventArray, &$d_start, &$d_end, &$view_array, &$t_array, &$positionArray){

		$conf = &tx_cal_registry::Registry('basic','conf');
		$gridLength = $conf['day.']['gridLength'];

		$cal_time_obj = new tx_cal_date($this->Ymd.'000000');
		$cal_time_obj->setTZbyId('UTC');
		$eventCounter = 0;
		foreach ($t_array as $cal_time => $val) {
			preg_match('/([0-9]{2})([0-9]{2})/', $cal_time, $dTimeStart);
			$cal_time_obj->setHour($dTimeStart[1]);
			$cal_time_obj->setMinute($dTimeStart[2]);
			$key = $cal_time_obj->format($conf['view.'][$conf['view'].'.']['timeFormatDay']);

			if ($val != '' && count($val) > 0) {
				for ($i = 0; $i < count($val); $i ++) {
					if(!empty($val[$i])){
						$keys = array_keys($val[$i]);
						switch ($keys[0]) {
							case 'begin' :
								$event = &$eventArray[$val[$i][$keys[0]]];
								$eventContent = $event->renderEventFor($conf['view']);
								$colSpan = $positionArray[$val[$i][$keys[0]]];
								//left
								//1 = 0
								//2 = 50
								//3 = 33.333
								//4 = 25
								
								$left = 0;
								if($colSpan > 1){
									$left = 100/$colSpan * $i;
								}
								
								//width
								//1 = 100
								//2 = 85,50
								//3 = 56.666, 56.666, 33.333
								//4 = 42.5, 42.5, 42.5, 25
								//5 = 34,34,34,34,20
								
								$width = 100;
								if($colSpan > 1){
									$width = 135/$colSpan;
								}

								//TODO: move this into a hook
								$eventContent = str_replace(Array('***LEFT***','***WIDTH***'),Array($left,$width),$eventContent);
								
								$daydisplay .= $eventContent;
								// End event drawing
								break;
						}
					}
				}
			}
		}
		return $daydisplay;
	}
	
	public function getDayClassesMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$classes = 'day weekday'.$this->weekdayNumber;
		if($this->current){
			$classes .= ' currentDay';
		}
		if($this->selected){
			$classes .= ' selectedDay';
		}
		if(!empty($this->events) || $this->hasAlldayEvents){
			$classes .= ' withEventDay';
		}
		if(intval($this->getParentMonth()) != intval($this->month)){
			$classes .= ' monthOff';
		}
		
		$sims['###DAY_CLASSES###'] = $classes;
	}
	
	function getDayTitleMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAY_TITLE###'] = $this->getWeekdayString($this->time, $view);
	}
	
	public function getDayLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###DAY_LINK###'] = $this->getDayLink($view, $this->time);
	}
	
	public function getDayLink($view, $value, $hasEvent = false){
		$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$conf = &tx_cal_registry::Registry('basic','conf');
		$dayLinkViewTarget = $conf['view.']['dayLinkTarget'];
		$isAllowedToCreateEvent = $rightsObj->isAllowedToCreateEvent();
			
		$local_cObj = &$this->getLocalCObject();
		$local_cObj->setCurrentVal($value);
		$local_cObj->data['view'] = $dayLinkViewTarget;
		$controller = &tx_cal_registry::Registry('basic','controller');
		
		if(($rightsObj->isViewEnabled($dayLinkViewTarget) || $conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid']) && (!empty($this->events) || $hasEvent || $this->hasAlldayEvents || $isAllowedToCreateEvent)){
			$controller->getParametersForTyposcriptLink($local_cObj->data, array ('getdate' => $this->Ymd, 'view' => $dayLinkViewTarget,  $controller->getPointerName() => NULL), $conf['cache'], $conf['clear_anyway'], $conf['view.'][$dayLinkViewTarget.'.'][$dayLinkViewTarget.'ViewPid']);
		}
		return $local_cObj->cObjGetSingle($conf['view.'][$view.'.'][$dayLinkViewTarget.'ViewLink'],$conf['view.'][$view.'.'][$dayLinkViewTarget.'ViewLink.']);
	}
	
	public function getAlldayMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$content = '';
		$timeKeys = array_keys($this->events);
		foreach($timeKeys as $timeKey){
			$eventKeys = array_keys($this->events[$timeKey]);
			foreach($eventKeys as $eventKey){
				if($this->events[$timeKey][$eventKey]->isAllday() || ($this->events[$timeKey][$eventKey]->getStart()->format('%Y%m%d')!=$this->events[$timeKey][$eventKey]->getEnd()->format('%Y%m%d'))){
					$content .= $this->events[$timeKey][$eventKey]->renderEventFor($view);
				}
			}
		}
		if($content=='' && ($view == 'week' || $view =='day')){
			$content = '<td class="st-c st-s">&nbsp;</td>';
		}
		$sims['###ALLDAY###'] = $content;
	}
	
	public function setCurrent(&$dateObject){
		if($this->day==$dateObject->day && $this->month==$dateObject->month && $this->year==$dateObject->year){
			$this->current = true;
		}
	}
	
	public function setSelected(&$dateObject){
		if($this->day==$dateObject->day && $this->month==$dateObject->month && $this->year==$dateObject->year){
			$this->selected = true;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_new_dayview.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_new_dayview.php']);
}
?>