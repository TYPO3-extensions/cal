<?php
namespace TYPO3\CMS\Cal\View;
/**
 * *************************************************************
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
 * *************************************************************
 */

/**
 * Base model for the day.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
abstract class NewTimeView {
	var $mySubpart;
	var $template;
	var $cs_convert;
	var $weekDayLength = 100;
	var $monthNameLength = 100;
	var $weekDayFormat = '%A';
	var $current = false;
	var $selected = false;
	var $parentMonth;
	
	/**
	 * Constructor.
	 * 
	 * @param $serviceKey String
	 *        	serviceKey for this model
	 */
	public function __construct() {
	}
	
	abstract function addEvent(&$event);
	
	public function render(&$template) {
		$this->template = $template;
		$rems = array ();
		$sims = array ();
		$wrapped = array ();
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		
		$subpart = $cObj->getSubpart ($template, $this->mySubpart);
		$this->getMarker ($subpart, $sims, $rems, $wrapped);
		return $this->finish (\TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($subpart, $sims, $rems, $wrapped));
	}
	
	protected function getMarker(& $template, & $sims, & $rems, & $wrapped, $view = '', $base = 'view') {
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		if ($view == '' && $base == 'view') {
			$view = ! empty ($conf ['alternateRenderingView']) && is_array ($conf [$base . '.'] [$conf ['alternateRenderingView'] . '.']) ? $conf ['alternateRenderingView'] : $conf ['view'];
		}
		preg_match_all ('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique ($match [1]);
		
		foreach ($allMarkers as $marker) {
			switch ($marker) {
				default :
					if (preg_match ('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService (substr ($marker, 8), 'module');
						if (is_object ($module)) {
							$rems ['###' . $marker . '###'] = $module->start ($this);
						}
					}
					$funcFromMarker = 'get' . str_replace (' ', '', ucwords (str_replace ('_', ' ', strtolower ($marker)))) . 'Marker';
					
					if (method_exists ($this, $funcFromMarker)) {
						$this->$funcFromMarker ($template, $sims, $rems, $wrapped, $view);
					}
					break;
			}
		}
		
		preg_match_all ('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique ($match [1]);
		$allSingleMarkers = array_diff ($allSingleMarkers, $allMarkers);
		
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'ACTIONURL' :
				case 'L_ENTER_EMAIL' :
				case 'L_CAPTCHA_TEXT' :
				case 'CAPTCHA_SRC' :
				case 'IMG_PATH' :
					// do nothing
					break;
				default :
					if (preg_match ('/.*_LABEL$/', $marker) || preg_match ('/^L_.*/', $marker)) {
						continue;
					}
					$funcFromMarker = 'get' . str_replace (' ', '', ucwords (str_replace ('_', ' ', strtolower ($marker)))) . 'Marker';
					if (method_exists ($this, $funcFromMarker)) {
						$this->$funcFromMarker ($template, $sims, $rems, $wrapped, $view);
					} else if (preg_match ('/MODULE__([A-Z0-9_-|])*/', $marker)) {
						$tmp = explode ('___', substr ($marker, 8));
						$modules [$tmp [0]] [] = $tmp [1];
					} else if ($conf [$base . '.'] [$view . '.'] [strtolower ($marker)]) {
						$current = '';
						
						// first, try to fill $current with a method of the model matching the markers name
						$functionName = 'get' . str_replace (' ', '', ucwords (str_replace ('_', ' ', strtolower ($marker))));
						if (method_exists ($this, $functionName)) {
							$tmp = $this->$functionName ();
							if (! is_object ($tmp) && ! is_array ($tmp)) {
								$current = $tmp;
							}
							unset ($tmp);
						}
						$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'local_cobj');
						$cObj->setCurrentVal ($current);
						$sims ['###' . $marker . '###'] = $cObj->cObjGetSingle ($conf [$base . '.'] [$view . '.'] [strtolower ($marker)], $conf [$base . '.'] [$view . '.'] [strtolower ($marker) . '.']);
					}
					break;
			}
		}
		
		// se alternativ way of MODULE__MARKER
		// yntax: ###MODULE__MODULENAME___MODULEMARKER###
		// ollect them, call each Modul, retrieve Array of Markers and replace them
		// his allows to spread the Module-Markers over complete template instead of one time
		// lso work with old way of MODULE__-Marker
		
		if (is_array ($modules)) { // ODULE-MARKER FOUND
			foreach ($modules as $themodule => $markerArray) {
				$module = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService ($themodule, 'module');
				if (is_object ($module)) {
					if ($markerArray [0] == '') {
						$sims ['###MODULE__' . $themodule . '###'] = $module->start ($this); // ld way
					} else {
						$moduleMarker = $module->start ($this); // get Markerarray from Module
						foreach ($moduleMarker as $key => $val) {
							$sims ['###MODULE__' . $themodule . '___' . $key . '###'] = $val;
						}
					}
				}
			}
		}
		
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_base_model', 'searchForObjectMarker', 'model');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchForObjectMarker')) {
				$hookObj->postSearchForObjectMarker ($this, $template, $sims, $rems, $wrapped, $view);
			}
		}
	}
	
	/**
	 * Method for post processing the rendered event
	 * 
	 * @return processed content/output
	 */
	protected function finish(&$content) {
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_base_model', 'finishModelRendering', 'model');
		// Hook: preFinishModelRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preFinishModelRendering')) {
				$hookObj->preFinishModelRendering ($this, $content);
			}
		}
		
		// translate output
		$this->translateLanguageMarker ($content);
		
		// Hook: postFinishModelRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postFinishModelRendering')) {
				$hookObj->postFinishModelRendering ($this, $content);
			}
		}
		return $content;
	}
	protected function translateLanguageMarker(&$content) {
		// translate leftover markers
		preg_match_all ('!(###|%%%)([A-Z0-9_-|]*)\_LABEL\1!is', $content, $match);
		$allLanguageMarkers = array_unique ($match [2]);
		
		if (count ($allLanguageMarkers)) {
			$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
			$sims = array ();
			foreach ($allLanguageMarkers as $key => $marker) {
				$wrapper = $match [1] [$key];
				$label = $controller->pi_getLL ('l_' . strtolower ($marker));
				if ($label == '') {
					$label = $controller->pi_getLL ('l_event_' . strtolower ($marker));
				}
				$sims [$wrapper . $marker . '_LABEL' . $wrapper] = $label;
			}
			if (count ($sims)) {
				$content = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($content, $sims, array (), array ());
			}
		}
		return $content;
	}
	
	/**
	 * Method to initialise a local content object, that can be used for customized TS rendering with own db values
	 * 
	 * @param $customData array
	 *        	key => value pairs that should be used as fake db-values for TS rendering instead of the values of the current object
	 */
	function getLocalCObject($customData = false) {
		$local_cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'local_cObj');
		if ($customData && is_array ($customData)) {
			$local_cObj->data = $customData;
		} else {
			$local_cObj->data = $this->cachedValueArray;
		}
		return $local_cObj;
	}
	function getMondayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###MONDAY###'] = $this->getWeekdayString (strtotime ("2001-01-01"));
	}
	function getTuesdayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###TUESDAY###'] = $this->getWeekdayString (strtotime ("2001-01-02"));
	}
	function getWednesdayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###WEDNESDAY###'] = $this->getWeekdayString (strtotime ("2001-01-03"));
	}
	function getThursdayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###THURSDAY###'] = $this->getWeekdayString (strtotime ("2001-01-04"));
	}
	function getFridayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###FRIDAY###'] = $this->getWeekdayString (strtotime ("2001-01-05"));
	}
	function getSaturdayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###SATURDAY###'] = $this->getWeekdayString (strtotime ("2001-01-06"));
	}
	function getSundayMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###SUNDAY###'] = $this->getWeekdayString (strtotime ("2001-01-07"));
	}
	public function getWeekdayString($timestamp) {
		if (! is_object ($this->cs_convert)) {
			$this->cs_convert = new \TYPO3\CMS\Core\Charset\CharsetConverter();
		}
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		return $this->cs_convert->substr (\TYPO3\CMS\Cal\Utility\Functions::getCharset (), strftime ($this->weekDayFormat, $timestamp), 0, $this->weekDayLength);
	}
	public function getCreateEventLink($view, $wrap, $date, $createOffset, $isAllowedToCreateEvent, $remember, $class, $time) {
		$tmp = '';
		
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		
		if (! $rightsObj->isViewEnabled ('create_event')) {
			if ($conf ['view.'] ['enableAjax']) {
				return sprintf ($wrap, $remember, $class, '');
			} else {
				return sprintf ($wrap, $remember, $class, '');
			}
		}
		$now = new \TYPO3\CMS\Cal\Model\CalDate ();
		$now->setTZbyId ('UTC');
		$now->addSeconds ($createOffset);
		
		$date = new \TYPO3\CMS\Cal\Model\CalDate ();
		$date->setDay ($this->day);
		$date->setMonth ($this->month);
		$date->setYear ($this->year);
		
		if (($date->after ($now) || $rightsObj->isAllowedToCreateEventInPast ()) && $isAllowedToCreateEvent) {
			$local_cObj = $this->getLocalCObject ();
			$conf ['clear_anyway'] = 1;
			if ($conf ['view.'] ['enableAjax']) {
				$local_cObj->setCurrentVal ($conf ['view.'] [$view . '.'] ['event.'] ['addIcon']);
				$local_cObj->data ['link_ATagParams'] = sprintf (' onclick="' . $conf ['view.'] [$view . '.'] ['event.'] ['addLinkOnClick'] . '"', $time, $date->format ('%Y%m%d'));
				$controller->getParametersForTyposcriptLink ($local_cObj->data, array (
						'gettime' => $time,
						'getdate' => $date->format ('%Y%m%d'),
						'view' => 'create_event' 
				), 0, $conf ['clear_anyway'], $conf ['view.'] ['event.'] ['createEventViewPid']);
				$tmp .= $local_cObj->cObjGetSingle ($conf ['view.'] [$view . '.'] ['event.'] ['addLink'], $conf ['view.'] [$view . '.'] ['event.'] ['addLink.']);
				if ($wrap) {
					$tmp = sprintf ($wrap, 'id="cell_' . $date->format ('%Y%m%d') . $time . '" ondblclick="javascript:eventUid=0;eventTime=\'' . $time . '\';eventDate=' . $date->format ('%Y%m%d') . ';EventDialog.showDialog(this);" ', $remember, $class, $tmp, $date->format ('%Y %m %d %H %M %s'));
				}
			} else {
				$local_cObj->setCurrentVal ($conf ['view.'] [$view . '.'] ['event.'] ['addIcon']);
				// linkConf = Array();
				$local_cObj->data ['link_useCacheHash'] = 0;
				$local_cObj->data ['link_no_cache'] = 1;
				$local_cObj->data ['link_additionalParams'] = '&tx_cal_controller[gettime]=' . $time . '&tx_cal_controller[getdate]=' . $date->format ('%Y%m%d') . '&tx_cal_controller[lastview]=' . $controller->extendLastView () . '&tx_cal_controller[view]=create_event';
				$local_cObj->data ['link_section'] = 'default';
				$local_cObj->data ['link_parameter'] = $conf ['view.'] ['event.'] ['createEventViewPid'] ? $conf ['view.'] ['event.'] ['createEventViewPid'] : $GLOBALS ['TSFE']->id;
				
				$tmp .= $local_cObj->cObjGetSingle ($conf ['view.'] [$view . '.'] ['event.'] ['addLink'], $conf ['view.'] [$view . '.'] ['event.'] ['addLink.']);
				if ($wrap) {
					$tmp = sprintf ($wrap, $remember, $class, $tmp, $date->format ('%Y %m %d %H %M %s'));
				}
			}
		} else {
			if ($conf ['view.'] ['enableAjax']) {
				$tmp = sprintf ($wrap, $remember, $class, '');
			} else {
				$tmp = sprintf ($wrap, $remember, $class, '');
			}
		}
		return $tmp;
	}
	public function getTimetableHeightMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$gridLength = $conf ['view.'] ['day.'] ['gridLength'];
		$dayStart = $conf ['view.'] ['day.'] ['dayStart']; // '0700'; // Start time for day grid
		$dayEnd = $conf ['view.'] ['day.'] ['dayEnd']; // '2300'; // End time for day grid
		
		while (strlen ($dayStart) < 6) {
			$dayStart .= '0';
		}
		while (strlen ($dayEnd) < 6) {
			$dayEnd .= '0';
		}
		
		$d_start = new \TYPO3\CMS\Cal\Model\CalDate ('01012000' . $dayStart);
		$d_end = new \TYPO3\CMS\Cal\Model\CalDate ('01012000' . $dayEnd);
		
		$sims ['###TIMETABLE_HEIGHT###'] = (($d_end->getHour () * 3600 + $d_end->getMinute () * 60) - ($d_start->getHour () * 3600 + $d_start->getMinute () * 60)) / $gridLength * 0.35;
	}
	public function getTimeCellsMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$timesSubpart = $cObj->getSubpart ($template, 'TIME_CELLS');
		
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		
		$gridLength = $conf ['view.'] ['day.'] ['gridLength'];
		if ($gridLength == 0) {
			$gridLength = 15;
		}
		
		$gridTime = new \TYPO3\CMS\Cal\Model\CalDate ();
		$gridTime->setTZbyId ('UTC');
		$gridTime->setMinute (0);
		
		$times = '';
		
		$dayStart = $conf ['view.'] ['day.'] ['dayStart']; // '0700'; // Start time for day grid
		$dayEnd = $conf ['view.'] ['day.'] ['dayEnd']; // '2300'; // End time for day grid
		
		while (strlen ($dayStart) < 6) {
			$dayStart .= '0';
		}
		while (strlen ($dayEnd) < 6) {
			$dayEnd .= '0';
		}
		
		if ($conf ['view'] == 'day') {
			$d_start = new \TYPO3\CMS\Cal\Model\CalDate ($this->Ymd . $dayStart);
			$d_end = new \TYPO3\CMS\Cal\Model\CalDate ($this->Ymd . $dayEnd);
		}
		if ($conf ['view'] == 'week') {
			$d_start = new \TYPO3\CMS\Cal\Model\CalDate ($this->weekStart . $dayStart);
			$d_end = new \TYPO3\CMS\Cal\Model\CalDate ($this->weekStart . $dayEnd);
		}
		$d_start->setTZbyId ('UTC');
		$d_end->setTZbyId ('UTC');
		
		$count = 86000;
		$value = 0;
		$createOffset = 0;
		$isAllowedToCreateEvent = $rightsObj->isAllowedToCreateEvent ();
		if ($isAllowedToCreateEvent) {
			$createOffset = intval ($conf ['rights.'] ['create.'] ['event.'] ['timeOffset']) * 60;
		}
		
		while ($d_end->after ($d_start)) {
			if ($isAllowedToCreateEvent) {
				$createLink = $this->getCreateEventLink ($view, '', $d_start, $createOffset, true, '', '', $d_start->format ('%H%M'));
			} else {
				$createLink = '';
			}
			$times .= str_replace (array (
					'###TIME###',
					'###VALUE###',
					'###CREATE_EVENT_LINK###' 
			), array (
					$d_start->format ($conf ['view.'] [$conf ['view'] . '.'] ['timeFormatDay']),
					$d_start->getHour (),
					$createLink 
			), $timesSubpart);
			$d_start->addSeconds (3600);
		}
		$rems ['###TIME_CELLS###'] = $times;
	}
	public function getHourCellsMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$hoursSubpart = $cObj->getSubpart ($template, 'HOUR_CELLS');
		
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$gridLength = $conf ['view.'] ['day.'] ['gridLength'];
		
		if ($gridLength == 0) {
			$gridLength = 15;
		}
		
		$dayStart = $conf ['view.'] ['day.'] ['dayStart']; // '0700'; // Start time for day grid
		$dayEnd = $conf ['view.'] ['day.'] ['dayEnd']; // '2300'; // End time for day grid
		
		while (strlen ($dayStart) < 6) {
			$dayStart .= '0';
		}
		while (strlen ($dayEnd) < 6) {
			$dayEnd .= '0';
		}
		
		$count = intval ($dayEnd - $dayStart) / 10000;
		$hours = '';
		for ($i = 0; $i < $count; $i ++) {
			$hours .= $hoursSubpart;
		}
		$rems ['###HOUR_CELLS###'] = $hours;
	}
	abstract function setCurrent(&$dateObject);
	abstract function setSelected(&$dateObject);
	public function getCreateEventLinkMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###CREATE_EVENT_LINK###'] = '';
		$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		if (! $rightsObj->isViewEnabled ('create_event')) {
			return;
		}
		$than = new \TYPO3\CMS\Cal\Model\CalDate ();
		$than->setTZbyId ('UTC');
		$than->addSeconds ($createOffset);
		
		$date = new \TYPO3\CMS\Cal\Model\CalDate ();
		$date->setDay ($this->day);
		$date->setMonth ($this->month);
		$date->setYear ($this->year);
		$date->setHour ($this->hour);
		$date->setMinute ($this->minute);
		if ($rightsObj->isAllowedToCreateEventForTodayAndFuture ()) {
			$date->setHour (23);
			$date->setMinute (59);
		}
		
		if (($date->after ($than) || $rightsObj->isAllowedToCreateEventInPast ()) && $rightsObj->isAllowedToCreateEvent ()) {
			$local_cObj = $this->getLocalCObject ();
			$timeParams = '';
			if ($view == 'day' || $view == 'week') {
				$timeParams = '&tx_cal_controller[gettime]=' . $date->format ('%H%M');
			} else if ($conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['starttime.'] ['default'] == 'now') {
				$now = new \TYPO3\CMS\Cal\Model\CalDate ();
				$than->setTZbyId ('UTC');
				$timeParams = '&tx_cal_controller[gettime]=' . $now->format ('%H%M');
			}
			
			$local_cObj->setCurrentVal ($conf ['view.'] [$view . '.'] ['event.'] ['addIcon']);
			// linkConf = Array();
			$local_cObj->data ['link_useCacheHash'] = 0;
			$local_cObj->data ['link_no_cache'] = 1;
			$local_cObj->data ['link_additionalParams'] = $timeParams . '&tx_cal_controller[startdate]=' . $date->format ('%Y%m%d') . '&tx_cal_controller[lastview]=' . $controller->extendLastView () . '&tx_cal_controller[view]=create_event';
			$local_cObj->data ['link_section'] = 'default';
			$local_cObj->data ['link_parameter'] = $conf ['view.'] ['event.'] ['createEventViewPid'] ? $conf ['view.'] ['event.'] ['createEventViewPid'] : $GLOBALS ['TSFE']->id;
			
			$sims ['###CREATE_EVENT_LINK###'] .= $local_cObj->cObjGetSingle ($conf ['view.'] [$view . '.'] ['event.'] ['addLink'], $conf ['view.'] [$view . '.'] ['event.'] ['addLink.']);
		}
	}
	public function getParentMonth() {
		return $this->parentMonth;
	}
	public function setParentMonth($parentMonth) {
		$this->parentMonth = $parentMonth;
	}
}

?>