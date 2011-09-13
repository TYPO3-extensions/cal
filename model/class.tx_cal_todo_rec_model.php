<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
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

require_once (t3lib_extMgm :: extPath('cal') . 'controller/class.tx_cal_calendar.php');
require_once (t3lib_extMgm :: extPath('cal') . 'model/class.tx_cal_phpicalendar_rec_model.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_todo_rec_model extends tx_cal_phpicalendar_rec_model {
	
	function tx_cal_todo_rec_model($todo, $start, $end) {
		$this->tx_cal_phpicalendar_rec_model($todo, $start, $end);
		$this->setEventType($this->EVENT_TYPE_TODO);
	}
	
	function renderEvent() {
		return $this->fillTemplate('###TEMPLATE_TODO###');
	}
	
	function renderEventFor($viewType){
		if($this->parentEvent->conf['view.']['freeAndBusy.']['enable']==1){
			$viewType .= '_FNB';
		}
		return $this->fillTemplate('###TEMPLATE_TODO_'.strtoupper($viewType).'###');
	}

	function renderEventPreview() {
		$this->parentEvent->isPreview = true;
		return $this->fillTemplate('###TEMPLATE_TODO_PREVIEW###');
	}
	
	function renderTomorrowsEvent() {
		$this->parentEvent->isTomorrow = true;
		return $this->fillTemplate('###TEMPLATE_TODO_TOMORROW###');
	}
	
	function fillTemplate($subpartMarker){
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$page = $cObj->fileResource($this->parentEvent->conf['view.']['todo.']['todoModelTemplate']);
		if ($page == '') {
			return '<h3>calendar: no todo model template file found:</h3>' . $this->parentEvent->conf['view.']['todo.']['todoModelTemplate'];
		}
		$page = $cObj->getSubpart($page,$subpartMarker);
		if(!$page){
			return 'could not find the >'.$subpartMarker.'< subpart-marker in '.$this->parentEvent->conf['view.']['todo.']['todoModelTemplate'];
		}
		$rems = array ();
		$sims = array ();
		$wrapped = array();
		$this->getMarker($page, $sims, $rems, $wrapped, $this->parentEvent->conf['view']);
		return $this->parentEvent->finish(tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped));
	}
	
	function getStatusMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->parentEvent->getStatusMarker($template, $sims, $rems, $wrapped, $view);
	}
	
	function getPriorityMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->parentEvent->getPriorityMarker($template, $sims, $rems, $wrapped, $view);
	}
	
	function getCompletedMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$this->parentEvent->getCompletedMarker($template, $sims, $rems, $wrapped, $view);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_todo_rec_model.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_todo_rec_model.php']);
}
?>