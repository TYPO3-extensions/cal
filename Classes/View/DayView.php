<?php
namespace TYPO3\CMS\Cal\View;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

use TYPO3\CMS\Cal\Model\Pear\Date\Calc;
use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class DayView extends \TYPO3\CMS\Cal\View\BaseView {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function newDrawDay(&$master_array, $getdate) {
		if (! isset ($getdate) || $getdate == '') {
			$getdate = new  \TYPO3\CMS\Cal\Model\CalDate ();
		} else {
			$getdate = new  \TYPO3\CMS\Cal\Model\CalDate ($getdate);
		}
		
		$dayModel = new \TYPO3\CMS\Cal\View\NewDayView($getdate->day, $getdate->month, $getdate->year);
		$today = new  \TYPO3\CMS\Cal\Model\CalDate ();
		$dayModel->setCurrent ($today);
		$dayModel->setSelected ($getdate);
		
		$dayModel->weekDayFormat = $this->conf ['view.'] ['day.'] ['dateFormatDay'];
		$weekdayLength = intval ($this->conf ['view.'] ['day.'] ['weekdayLength']);
		if ($weekdayLength > 0) {
			$dayModel->weekDayLength = $weekdayLength;
		}
		
		$masterArrayKeys = array_keys ($master_array);
		foreach ($masterArrayKeys as $dateKey) {
			$dateArray = &$master_array [$dateKey];
			$dateArrayKeys = array_keys ($dateArray);
			foreach ($dateArrayKeys as $timeKey) {
				$arrayOfEvents = &$dateArray [$timeKey];
				$eventKeys = array_keys ($arrayOfEvents);
				foreach ($eventKeys as $eventKey) {
					$dayModel->addEvent ($arrayOfEvents [$eventKey]);
				}
			}
		}
		$subpart = Functions::getContent ($this->conf ['view.'] ['day.'] ['newDayTemplate']);
		$page = Functions::getContent ($this->conf ['view.'] ['day.'] ['dayTemplate']);
		$page = str_replace ('###DAY###', $dayModel->render ($subpart), $page);
		$rems = Array ();
		
		return $this->finish ($page, $rems);
	}
	
	/**
	 * Draws the day view
	 * 
	 * @param $master_array array
	 *        	to be drawn.
	 * @param $getdate integer
	 *        	of the event
	 * @return string HTML output.
	 */
	public function drawDay(&$master_array, $getdate) {
		$this->_init ($master_array);
		if ($this->conf ['useNewTemplatesAndRendering']) {
			return $this->newDrawDay ($master_array, $getdate);
		}
		
		$page = Functions::getContent ($this->conf ['view.'] ['day.'] ['dayTemplate']);
		if ($page == '') {
			return "<h3>day: no template file found:</h3>" . $this->conf ['view.'] ['day.'] ['dayTemplate'] . "<br />Please check your template record and add both cal items at 'include static (from extension)'";
		}
		
		$dayTemplate = $this->cObj->getSubpart ($page, '###DAY_TEMPLATE###');
		if ($dayTemplate == '') {
			$rems = Array ();
			return $this->finish ($page, $rems);
		}
		
		$dayStart = $this->conf ['view.'] ['day.'] ['dayStart']; // '0700'; // Start time for day grid
		$dayEnd = $this->conf ['view.'] ['day.'] ['dayEnd']; // '2300'; // End time for day grid
		$gridLength = $this->conf ['view.'] ['day.'] ['gridLength']; // '15'; // Grid distance in minutes for day view, multiples of 15 preferred
		
		if (! isset ($getdate) || $getdate == '') {
			$getdate_obj = new  \TYPO3\CMS\Cal\Model\CalDate ();
			$getdate = $getdate_obj->format ('%Y%m%d');
		}
		
		$day_array2 = Array ();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $day_array2);
		$this_day = $day_array2 [3];
		$this_month = $day_array2 [2];
		$this_year = $day_array2 [1];
		
		$unix_time = new  \TYPO3\CMS\Cal\Model\CalDate ($getdate . '000000');
		
		$this->initLocalCObject ();
		
		$this->local_cObj->setCurrentVal ($this->conf ['view.'] ['day.'] ['legendNextDayLink']);
		$legend_next_day_link = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['day.'] ['legendNextDayLink'], $this->conf ['view.'] ['day.'] ['legendNextDayLink.']);
		
		$this->local_cObj->setCurrentVal ($this->conf ['view.'] ['day.'] ['legendPrevDayLink']);
		$legend_prev_day_link = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] ['day.'] ['legendPrevDayLink'], $this->conf ['view.'] ['day.'] ['legendPrevDayLink.']);
		
		$next_month_obj = new  \TYPO3\CMS\Cal\Model\CalDate ();
		$next_month_obj->copy ($unix_time);
		$next_month_obj->addSeconds (604800);
		$next_month = $next_month_obj->format ('%Y%m%d');
		$prev_month_obj = new  \TYPO3\CMS\Cal\Model\CalDate ();
		$prev_month_obj->copy ($unix_time);
		$prev_month_obj->subtractSeconds (604801);
		$prev_month = $prev_month_obj->format ('%Y%m%d');
		
		$dateOfWeek = Calc::beginOfWeek ($this_day, $this_month, $this_year);
		$week_start_day = new  \TYPO3\CMS\Cal\Model\CalDate ($dateOfWeek . '000000');
		
		$start_day = $unix_time;
		$start_week_time = $start_day;
		
		$end_week_time = new  \TYPO3\CMS\Cal\Model\CalDate ();
		$end_week_time->copy ($start_week_time);
		$end_week_time->addSeconds (604799);
		
		// Nasty fix to work with TS strftime
		$start_day_time = new  \TYPO3\CMS\Cal\Model\CalDate ($getdate . '000000');
		$start_day_time->setTZbyId ('UTC');
		$end_day_time = \TYPO3\CMS\Cal\Controller\Calendar::calculateEndDayTime ($start_day_time);
		
		$GLOBALS ['TSFE']->register ['cal_day_starttime'] = $start_day_time->getTime ();
		$GLOBALS ['TSFE']->register ['cal_day_endtime'] = $end_day_time->getTime ();
		
		$display_date = $this->cObj->cObjGetSingle ($this->conf ['view.'] ['day.'] ['titleWrap'], $this->conf ['view.'] ['day.'] ['titleWrap.'], $TSkey = '__');
		
		$dayTemplate = Functions::getContent ($this->conf ['view.'] ['day.'] ['dayTemplate']);
		if ($dayTemplate == '') {
			return '<h3>calendar: no template file found:</h3>' . $this->conf ['view.'] ['day.'] ['dayTemplate'] . '<br />Please check your template record and add both cal items at "include static (from extension)"';
		}
		
		$dayTemplate = $this->replace_files ($dayTemplate, Array (
				'sidebar' => $this->conf ['view.'] ['other.'] ['sidebarTemplate'] 
		));
		
		$sims = Array (
				'###GETDATE###' => $getdate,
				'###DISPLAY_DATE###' => $display_date,
				'###LEGEND_PREV_DAY###' => $legend_prev_day_link,
				'###LEGEND_NEXT_DAY###' => $legend_next_day_link,
				'###SIDEBAR_DATE###' => '' 
		);
		
		// Replaces the daysofweek
		$loop_dof = $this->cObj->getSubpart ($dayTemplate, '###DAYSOFWEEK###');
		
		// Build the body
		$dayborder = 0;
		
		$out = '';
		$fillTime = sprintf ('%04d', $dayStart);
		$day_array = Array ();
		
		while ($fillTime < $dayEnd) {
			array_push ($day_array, $fillTime);
			$dTime = Array ();
			preg_match ('/([0-9]{2})([0-9]{2})/', $fillTime, $dTime);
			$fill_h = $dTime [1];
			$fill_min = $dTime [2];
			$fill_min = sprintf ('%02d', $fill_min + $gridLength);
			if ($fill_min == 60) {
				$fill_h = sprintf ('%02d', ($fill_h + 1));
				$fill_min = '00';
			}
			$fillTime = $fill_h . $fill_min;
		}
		$nbrGridCols = Array ();
		
		$dayborder = 0;
		
		$view_array = Array ();
		$rowspan_array = Array ();
		$eventArray = Array ();
		
		$endOfDay = new  \TYPO3\CMS\Cal\Model\CalDate ();
		$startOfDay = new  \TYPO3\CMS\Cal\Model\CalDate ();
		
		if (! empty ($this->master_array)) {
			foreach ($this->master_array as $ovlKey => $ovlValue) {
				$dTimeStart = Array ();
				$dTimeEnd = Array ();
				$dDate = Array ();
				preg_match ('/([0-9]{2})([0-9]{2})/', $dayStart, $dTimeStart);
				preg_match ('/([0-9]{2})([0-9]{2})/', $dayEnd, $dTimeEnd);
				preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $ovlKey, $dDate);
				
				$d_start = new  \TYPO3\CMS\Cal\Model\CalDate ($dDate [1] . $dDate [2] . $dDate [3] . ' ' . $dTimeStart [1] . ':' . $dTimeStart [2] . ':00');
				$d_start->setTZbyId ('UTC');
				$d_end = new  \TYPO3\CMS\Cal\Model\CalDate ($dDate [1] . $dDate [2] . $dDate [3] . ' ' . $dTimeEnd [1] . ':' . $dTimeEnd [2] . ':00');
				$d_end->setTZbyId ('UTC');
				
				foreach ($ovlValue as $ovl_time_key => $ovl_time_Value) {
					foreach ($ovl_time_Value as $event) {
						$eventStart = $event->getStart ();
						$eventArray [$event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M')] = $event;
						$starttime = new  \TYPO3\CMS\Cal\Model\CalDate ();
						$endtime = new  \TYPO3\CMS\Cal\Model\CalDate ();
						$j = new  \TYPO3\CMS\Cal\Model\CalDate ();
						if ($ovl_time_key == '-1') {
							$starttime->copy ($event->getStart ());
							$endtime->copy ($event->getEnd ());
							$endtime->addSeconds (1);
							
							for ($j->copy ($starttime); $j->before ($endtime) && $j->before ($end_week_time); $j->addSeconds (86400)) {
								$view_array [$j->format ('%Y%m%d')] ['-1'] [] = $event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M');
							}
						} else {
							$starttime->copy ($event->getStart ());
							$starttime->subtractSeconds (($starttime->getMinute () % $gridLength) * 60);
							
							$endtime->copy ($event->getEnd ());
							$endtime->subtractSeconds (($endtime->getMinute () % $gridLength) * 60);
							
							$entries = 1;
							$old_day = new  \TYPO3\CMS\Cal\Model\CalDate ($ovlKey . '000000');
							$old_day->setTZbyId ('UTC');
							$endOfDay->copy ($d_end);
							$startOfDay->copy ($d_start);
							
							// $d_start -= $gridLength * 60;
							for ($k = 0; $k < count ($view_array [($ovlKey)]); $k ++) {
								if (empty ($view_array [$starttime->format ('%Y%m%d')] [$starttime->format ('%H%M')] [$k])) {
									break;
								}
							}
							$j->copy ($starttime);
							if ($j->before ($startOfDay)) {
								$j->copy ($startOfDay);
							}
							while ($j->before ($endtime) && $j->before ($end_week_time)) {
								if ($j->after ($endOfDay)) {
									$rowspan_array [$old_day->format ('%Y%m%d')] [$event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M')] = $entries - 1;
									
									$endOfDay->addSeconds (60 * 60 * 24);
									$old_day->copy ($endOfDay);
									$startOfDay->addSeconds (60 * 60 * 24);
									$j->copy ($startOfDay);
									$entries = 0;
									for ($k = 0; $k < count ($view_array [$startOfDay->format ('%Y%m%d')]); $k ++) {
										if (empty ($view_array [$d_start->format ('%Y%m%d')] [$startOfDay->format ('%H%M')] [$k])) {
											break;
										}
									}
								} else {
									$view_array [$j->format ('%Y%m%d')] [$j->format ('%H%M')] [] = $event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M');
									$j->addSeconds ($gridLength * 60);
								}
								$entries ++;
							}
							$rowspan_array [$old_day->format ('%Y%m%d')] [$event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M')] = $entries - 1;
						}
					}
				}
			}
		}
		
		if ($this->conf ['view.'] ['day.'] ['dynamic'] == 1) {
			$dayStart = '2359';
			$dayEnd = '0000';
			if (is_array ($view_array [$getdate])) {
				$timeKeys = array_keys ($view_array [$getdate]);
				$formatedLast = array_pop ($timeKeys);
				$formatedFirst = $formatedLast;
				foreach ($timeKeys as $timeKey) {
					if ($timeKey > 0) {
						$formatedFirst = $timeKey;
						break;
					}
				}
				if (intval ($formatedFirst) > 0 && intval ($formatedFirst) < intval ($dayStart)) {
					$dayStart = sprintf ("%04d", $formatedFirst);
				}
				if (intval ($formatedLast) > intval ($dayEnd)) {
					$dayEnd = sprintf ("%04d", $formatedLast + $gridLength);
				}
			}
			$dayStart = substr ($dayStart, 0, 2) . '00';
		}
		if (! empty ($view_array [$getdate])) {
			$max = Array ();
			foreach ($view_array [$getdate] as $array_time => $time_val) {
				$c = count ($view_array [$getdate] [$array_time]);
				array_push ($max, $c);
			}
			$nbrGridCols [$getdate] = max ($max);
		} else {
			$nbrGridCols [$getdate] = 1;
		}
		
		$isAllowedToCreateEvent = $this->rightsObj->isAllowedToCreateEvent ();
		$start_day = $week_start_day;
		for ($i = 0; $i < 7; $i ++) {
			$day_num = $start_day->format ('%w');
			
			$daylink = $start_day->format ('%Y%m%d');
			
			$weekday = $start_day->format ($this->conf ['view.'] ['day.'] ['dateFormatDay']);
			
			if ($daylink == $getdate) {
				$row1 = 'rowToday';
				$row2 = 'rowOn';
				$row3 = 'rowToday';
			} else {
				$row1 = 'rowOff';
				$row2 = 'rowOn';
				$row3 = 'rowOff';
			}
			$dayLinkViewTarget = $this->conf ['view.'] ['dayLinkTarget'];
			if (($this->rightsObj->isViewEnabled ($dayLinkViewTarget) || $this->conf ['view.'] [$dayLinkViewTarget . '.'] [$dayLinkViewTarget . 'ViewPid']) && ($view_array [$daylink] || $isAllowedToCreateEvent)) {
				$this->initLocalCObject ();
				$this->local_cObj->setCurrentVal ($weekday);
				$this->local_cObj->data ['view'] = $dayLinkViewTarget;
				$this->controller->getParametersForTyposcriptLink ($this->local_cObj->data, Array (
						'getdate' => $daylink,
						'view' => $dayLinkViewTarget,
						$this->pointerName => NULL 
				), $this->conf ['cache'], $this->conf ['clear_anyway'], $this->conf ['view.'] [$dayLinkViewTarget . '.'] [$dayLinkViewTarget . 'ViewPid']);
				$link = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$dayLinkViewTarget . '.'] [$dayLinkViewTarget . 'ViewLink'], $this->conf ['view.'] [$dayLinkViewTarget . '.'] [$dayLinkViewTarget . 'ViewLink.']);
			} else {
				$link = $weekday;
			}
			$start_day->addSeconds (86400);
			
			$search = Array (
					'###LINK###',
					'###DAYLINK###',
					'###ROW1###',
					'###ROW2###',
					'###ROW3###' 
			);
			$replace = Array (
					$link,
					$daylink,
					$row1,
					$row2,
					$row3 
			);
			$loop_tmp = str_replace ($search, $replace, $loop_dof);
			$weekday_loop .= $loop_tmp;
		}
		$rems ['###DAYSOFWEEK###'] = $weekday_loop;
		
		// Replaces the allday events
		$replace = '';
		if (is_array ($view_array [$getdate] ['-1'])) {
			$loop_ad = $this->cObj->getSubpart ($dayTemplate, '###LOOPALLDAY###');
			foreach ($view_array [$getdate] ['-1'] as $uid => $allday) {
				$replace .= $eventArray [$allday]->renderEventForAllDay ();
			}
		}
		$sims ['###ALLDAY###'] = $replace;
		
		$view_array = $view_array [$getdate];
		$nbrGridCols = $nbrGridCols [$getdate] ? $nbrGridCols [$getdate] : 1;
		$t_array = Array ();
		$pos_array = Array ();
		preg_match ('/([0-9]{4})([0-9]{2})([0-9]{2})/', $getdate, $dDate);
		preg_match ('/([0-9]{2})([0-9]{2})/', $dayStart, $dTimeStart);
		preg_match ('/([0-9]{2})([0-9]{2})/', $dayEnd, $dTimeEnd);
		$dTimeStart [2] -= $dTimeStart [2] % $gridLength;
		$dTimeEnd [2] -= $dTimeEnd [2] % $gridLength;
		
		$d_start = new  \TYPO3\CMS\Cal\Model\CalDate ($dDate [1] . $dDate [2] . $dDate [3] . ' ' . $dTimeStart [1] . ':' . sprintf ("%02d", $dTimeStart [2]) . ':00');
		$d_start->setTZbyId ('UTC');
		$d_end = new  \TYPO3\CMS\Cal\Model\CalDate ($dDate [1] . $dDate [2] . $dDate [3] . ' ' . $dTimeEnd [1] . ':' . sprintf ("%02d", $dTimeEnd [2]) . ':00');
		$d_end->setTZbyId ('UTC');
		
		$i = new  \TYPO3\CMS\Cal\Model\CalDate ();
		$i->copy ($d_start);
		$i->setTZbyId ('UTC');
		while ($i->before ($d_end)) {
			$i_formatted = $i->format ('%H%M');
			if (is_array ($view_array [$i_formatted]) && count ($view_array [$i_formatted]) > 0) {
				foreach ($view_array [$i_formatted] as $eventKey) {
					$event = &$eventArray [$eventKey];
					$eventStart = $event->getStart ();
					if (array_key_exists ($event->getType () . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M'), $pos_array)) {
						$eventEnd = $event->getEnd ();
						$nd = $eventEnd->subtractSeconds ((($eventEnd->getMinute () % $gridLength) * 60));
						if ($i_formatted >= $nd) {
							$t_array [$i_formatted] [$pos_array [$event->getType () . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M')]] = Array (
									'ended' => $event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M') 
							);
						} else {
							$t_array [$i_formatted] [$pos_array [$event->getType () . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M')]] = Array (
									'started' => $event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M') 
							);
						}
					} else {
						for ($j = 0; $j < $nbrGridCols; $j ++) {
							if (count ($t_array [$i_formatted] [$j]) == 0 || ! isset ($t_array [$i_formatted] [$j])) {
								$pos_array [$event->getType () . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M')] = $j;
								$t_array [$i_formatted] [$j] = Array (
										'begin' => $event->getType () . '_' . $event->getUid () . '_' . $eventStart->format ('%Y%m%d%H%M') 
								);
								break;
							}
						}
					}
				}
			} else {
				$t_array [$i_formatted] = '';
			}
			
			$i->addSeconds ($gridLength * 60);
		}
		
		$event_length = Array ();
		$border = 0;
		
		$createOffset = intval ($this->conf ['rights.'] ['create.'] ['event.'] ['timeOffset']) * 60;
		
		$cal_time_obj = new  \TYPO3\CMS\Cal\Model\CalDate ($getdate . '000000');
		$cal_time_obj->setTZbyId ('UTC');
		foreach ($t_array as $cal_time => $val) {
			preg_match ('/([0-9]{2})([0-9]{2})/', $cal_time, $dTimeStart);
			$cal_time_obj->setHour ($dTimeStart [1]);
			$cal_time_obj->setMinute ($dTimeStart [2]);
			
			$key = $cal_time_obj->format ($this->conf ['view.'] ['day.'] ['timeFormatDay']);
			if (intval ($dTimeStart [2]) == 0) {
				$daydisplay .= sprintf ($this->conf ['view.'] ['day.'] ['dayTimeCell'], (60 / $gridLength), $key, $gridLength);
			} elseif ($cal_time_obj->equals ($d_start)) {
				$size_tmp = 60 - (int) substr ($cal_time, 2, 2);
				$daydisplay .= sprintf ($this->conf ['view.'] ['day.'] ['dayTimeCell'], ($size_tmp / $gridLength), $key, $gridLength);
			} else {
				$daydisplay .= sprintf ($this->conf ['view.'] ['day.'] ['dayTimeCell2'], $gridLength);
			}
			if ($dayborder == 0) {
				$class = ' ' . $this->conf ['view.'] ['day.'] ['classDayborder'];
				$dayborder ++;
			} else {
				$class = ' ' . $this->conf ['view.'] ['day.'] ['classDayborder2'];
				$dayborder = 0;
			}
			
			if ($val != '' && count ($val) > 0) {
				for ($i = 0; $i < count ($val); $i ++) {
					if (! empty ($val [$i])) {
						$keys = array_keys ($val [$i]);
						switch ($keys [0]) {
							case 'begin' :
								$event = &$eventArray [$val [$i] [$keys [0]]];
								$dayEndTime = new  \TYPO3\CMS\Cal\Model\CalDate ();
								$dayEndTime->copy ($event->getEnd ());
								$dayStartTime = new  \TYPO3\CMS\Cal\Model\CalDate ();
								$dayStartTime->copy ($event->getStart ());
								
								$rest = $dayStartTime->getMinute () % ($gridLength);
								$plus = 0;
								if ($rest > 0) {
									$plus = 1;
								}
								if ($dayEndTime->after ($d_end)) {
									$dayEndTime = $d_end;
								}
								if ($dayStartTime->before ($d_start)) {
									$dayStartTime = $d_start;
								}
								$colSpan = $rowspan_array [$getdate] [$val [$i] [$keys [0]]];
								
								$daydisplay .= sprintf ($this->conf ['view.'] ['day.'] ['dayEventPre'], $colSpan);
								$daydisplay .= $event->renderEventForDay ();
								$daydisplay .= $this->conf ['view.'] ['day.'] ['dayEventPost'];
								// End event drawing
								break;
						}
					}
				}
				if (count ($val) < $nbrGridCols) {
					
					$remember = 0;
					// Render cells with events
					for ($l = 0; $l < $nbrGridCols; $l ++) {
						if (! $val [$l]) {
							$remember ++;
						} else if ($remember > 0) {
							$daydisplay .= $this->getCreateEventLink ('day', $this->conf ['view.'] ['day.'] ['normalCell'], $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $remember, $class, $cal_time);
							$remember = 0;
						}
					}
					// Render cells next to events
					if ($remember > 0) {
						$daydisplay .= $this->getCreateEventLink ('day', $this->conf ['view.'] ['day.'] ['normalCell'], $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $remember, $class, $cal_time);
						$remember = 0;
					}
				}
			} else {
				// Render cells without events
				$daydisplay .= $this->getCreateEventLink ('day', $this->conf ['view.'] ['day.'] ['normalCell'], $cal_time_obj, $createOffset, $isAllowedToCreateEvent, $nbrGridCols, $class, $cal_time);
			}
			$daydisplay .= $this->conf ['view.'] ['day.'] ['dayFinishRow'];
		}
		
		$dayTemplate = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($dayTemplate, $sims, Array (), Array ());
		$rems ['###DAYEVENTS###'] = $daydisplay;
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), Array (
				'###DAY_TEMPLATE###' => $dayTemplate 
		), Array ());
		return $this->finish ($page, $rems);
	}
}

?>