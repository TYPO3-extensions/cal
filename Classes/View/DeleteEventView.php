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

use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A service which renders a form to create / edit an EventModel.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class DeleteEventView extends \TYPO3\CMS\Cal\View\FeEditingBaseView {
	
	var $event;
	
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Draws a delete event form.
	 * 
	 * @param
	 *        	object		The event to be deleted
	 * @param
	 *        	object The cObject of the mother-class
	 * @param
	 *        	object		The rights object.
	 * @return string HTML output.
	 */
	function drawDeleteEvent(&$event, $pidList) {
		$modelObj = $this->modelObj;
		unset ($this->controller->piVars ['category']);
		$page = Functions::getContent ($this->conf ['view.'] ['delete_event.'] ['template']);
		if ($page == '') {
			return '<h3>calendar: no confirm event template file found:</h3>' . $this->conf ['view.'] ['delete_event.'] ['template'];
		}
		
		$this->object = $event;
		
		if (! $this->object->isUserAllowedToDelete ()) {
			return 'You are not allowed to delete this event!';
		}
		
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		
		$sims ['###UID###'] = $this->conf ['uid'];
		$sims ['###TYPE###'] = $this->conf ['type'];
		$sims ['###VIEW###'] = 'remove_event';
		$sims ['###LASTVIEW###'] = $this->controller->extendLastView ();
		$sims ['###OPTION###'] = $this->conf ['option'];
		$sims ['###L_DELETE_EVENT###'] = $this->controller->pi_getLL ('l_delete_event');
		$sims ['###L_SAVE###'] = $this->controller->pi_getLL ('l_save');
		$sims ['###L_CANCEL###'] = $this->controller->pi_getLL ('l_cancel');
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url (array (
				'view' => 'remove_event',
				'category' => null 
		)));
		$this->getTemplateSubpartMarker ($page, $sims, $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		$this->getTemplateSingleMarker ($page, $sims, $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		;
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
	}
	function getCalendarIdMarker(& $template, & $sims, & $rems) {
		$sims ['###CALENDAR_ID###'] = '';
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'tx_cal_calendar', 'uid = ' . intval ($this->object->getCalendarUid ()) . '');
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$sims ['###CALENDAR_ID###'] = $this->cObj->stdWrap ($row ['title'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['calendar_id_stdWrap.']);
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
	}
	function getCategoryMarker(& $template, & $sims, & $rems) {
		$sims ['###CATEGORY###'] = '';
		
		$categoryArray = $this->object->getCategories ();
		if (! empty ($categoryArray)) {
			$ids = Array ();
			$names = Array ();
			
			foreach ($categoryArray as $id => $value) {
				$ids [] = $id;
				$names [] = $value->getTitle ();
			}
			$sims ['###CATEGORY###'] = $this->cObj->stdWrap (implode (', ', $names), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['category_stdWrap.']);
		}
	}
	function getAlldayMarker(& $template, & $sims, & $rems) {
		$label = $this->controller->pi_getLL ('l_false');
		if ($this->object->isAllDay () == '1') {
			$label = $this->controller->pi_getLL ('l_true');
		}
		$sims ['###ALLDAY###'] = $this->cObj->stdWrap ($label, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['allday_stdWrap.']);
	}
	function getStartdateMarker(& $template, & $sims, & $rems) {
		$startDate = $this->object->getStart ();
		$split = $this->conf ['dateConfig.'] ['splitSymbol'];
		$startDateFormatted = $startDate->format ('%Y' . $split . '%m' . $split . '%d');
		$sims ['###STARTDATE###'] = $this->cObj->stdWrap ($startDateFormatted, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['startdate_stdWrap.']);
	}
	function getEnddateMarker(& $template, & $sims, & $rems) {
		$endDate = $this->object->getEnd ();
		$split = $this->conf ['dateConfig.'] ['splitSymbol'];
		$endDateFormatted = $endDate->format ('%Y' . $split . '%m' . $split . '%d');
		$sims ['###ENDDATE###'] = $this->cObj->stdWrap ($endDateFormatted, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['enddate_stdWrap.']);
	}
	function getStarttimeMarker(& $template, & $sims, & $rems) {
		$startDate = $this->object->getStart ();
		$sims ['###STARTTIME###'] = $this->cObj->stdWrap ($startDate->format ($this->conf ['view.'] ['event.'] ['event.'] ['timeFormat']), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['starttime_stdWrap.']);
	}
	function getEndtimeMarker(& $template, & $sims, & $rems) {
		$endDate = $this->object->getEnd ();
		$sims ['###ENDTIME###'] = $this->cObj->stdWrap ($endDate->format ($this->conf ['view.'] ['event.'] ['event.'] ['timeFormat']), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['endtime_stdWrap.']);
	}
	function getTitleMarker(& $template, & $sims, & $rems) {
		$sims ['###TITLE###'] = $this->cObj->stdWrap ($this->object->getTitle (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['title_stdWrap.']);
	}
	function getOrganizerMarker(& $template, & $sims, & $rems) {
		$sims ['###ORGANIZER###'] = '';
		if (! $this->extConf ['hideOrganizerTextfield'] && $organizer = $this->object->getOrganizer ()) {
			$sims ['###ORGANIZER###'] = $this->cObj->stdWrap ($organizer, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['organizer_stdWrap.']);
		}
	}
	function getCalOrganizerMarker(& $template, & $sims, & $rems) {
		$sims ['###CAL_ORGANIZER###'] = '';
		if ($organizer = $this->object->getOrganizerObject ()) {
			$sims ['###CAL_ORGANIZER###'] = $this->cObj->stdWrap ($organizer->getName (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['cal_organizer_stdWrap.']);
		}
	}
	function getLocationMarker(& $template, & $sims, & $rems) {
		$sims ['###LOCATION###'] = '';
		if (! $this->extConf ['hideLocationTextfield'] && $location = $this->object->getLocation ()) {
			$sims ['###LOCATION###'] = $this->cObj->stdWrap ($location, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['location_stdWrap.']);
		}
	}
	function getCalLocationMarker(& $template, & $sims, & $rems) {
		$sims ['###CAL_LOCATION###'] = '';
		if ($location = $this->object->getLocationObject ()) {
			$sims ['###CAL_LOCATION###'] = $this->cObj->stdWrap ($location->getName (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['cal_location_stdWrap.']);
		}
	}
	function getDescriptionMarker(& $template, & $sims, & $rems) {
		$this->object->getDescriptionMarker ($template, $sims, $rems, $wrapped, $this->conf ['view']);
	}
	function getTeaserMarker(& $template, & $sims, & $rems) {
		$this->object->getTeaserMarker ($template, $sims, $rems, $wrapped, $this->conf ['view']);
	}
	function getFrequencyMarker(& $template, & $sims, & $rems) {
		$sims ['###FREQUENCY###'] = $this->cObj->stdWrap ($this->controller->pi_getLL ('l_' . $this->object->getFreq ()), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['frequency_stdWrap.']);
	}
	function getByDayMarker(& $template, & $sims, & $rems) {
		$by_day = Array (
				'MO',
				'TU',
				'WE',
				'TH',
				'FR',
				'SA',
				'SU' 
		);
		$dayName = strtotime ('next monday');
		$temp_sims = Array ();
		foreach ($this->object->getByDay () as $day) {
			
			if (in_array ($day, $by_day)) {
				$temp_sims [] = strftime ('%a', $dayName);
			}
			$dayName += 86400;
		}
		
		$sims ['###BY_DAY###'] = $this->cObj->stdWrap (implode (',', $temp_sims), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['byDay_stdWrap.']);
	}
	function getByMonthDayMarker(& $template, & $sims, & $rems) {
		$sims ['###BY_MONTHDAY###'] = $this->cObj->stdWrap (implode (',', $this->object->getByMonthDay ()), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['byMonthday_stdWrap.']);
	}
	function getByMonthMarker(& $template, & $sims, & $rems) {
		$sims ['###BY_MONTH###'] = $this->cObj->stdWrap (implode (',', $this->object->getByMonth ()), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['byMonth_stdWrap.']);
	}
	function getUntilMarker(& $template, & $sims, & $rems) {
		$sims ['###UNTIL###'] = '';
		$untilDate = $this->object->getUntil ();
		if (is_object ($untilDate)) {
			$split = $this->conf ['dateConfig.'] ['splitSymbol'];
			$untilDateFormatted = $untilDate->format ('%Y' . $split . '%m' . $split . '%d');
			$sims ['###UNTIL###'] = $this->cObj->stdWrap ($untilDateFormatted, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['until_stdWrap.']);
		}
	}
	function getCountMarker(& $template, & $sims, & $rems) {
		$sims ['###COUNT###'] = $this->cObj->stdWrap ($this->object->getCount (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['count_stdWrap.']);
	}
	function getIntervalMarker(& $template, & $sims, & $rems) {
		$sims ['###INTERVAL###'] = $this->cObj->stdWrap ($this->object->getInterval (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['interval_stdWrap.']);
	}
	function getRdateTypeMarker(& $template, & $sims, & $rems) {
		$sims ['###RDATE_TYPE###'] = $this->cObj->stdWrap ($this->controller->pi_getLL ('l_' . $this->object->getRdateType ()), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['rdateType_stdWrap.']);
	}
	function getRdateMarker(& $template, & $sims, & $rems) {
		$sims ['###RDATE###'] = $this->cObj->stdWrap ($this->object->getRdate (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['rdate_stdWrap.']);
	}
	function getNotifyMarker(& $template, & $sims, & $rems) {
		$sims ['###NOTIFY###'] = '';
		$cal_notify_user = Array ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('fe_users.username', 'fe_users, tx_cal_fe_user_event_monitor_mm', 'tx_cal_fe_user_event_monitor_mm.pid in (' . $this->conf ['pidList'] . ') AND fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND tx_cal_fe_user_event_monitor_mm.uid_local = ' . $this->object->getUid ());
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$cal_notify_user [] = $row ['username'];
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		if (! empty ($cal_notify_user)) {
			$sims ['###NOTIFY###'] = $this->cObj->stdWrap (implode (',', (array) $cal_notify_user), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['notify_stdWrap.']);
		}
	}
	function getExceptionMarker(& $template, & $sims, & $rems) {
		$exception = Array ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_exception_event.title', 'tx_cal_exception_event,tx_cal_exception_event_mm', 'pid IN (' . $this->conf ['pidList'] . ') AND tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event" AND tx_cal_exception_event_mm.uid_local =' . $this->object->getUid () . $this->cObj->enableFields ('tx_cal_exception_event'));
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$exception [] = $row ['title'];
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_exception_event_group.title', 'tx_cal_exception_event_group,tx_cal_exception_event_mm', 'pid in (' . $this->conf ['pidList'] . ') AND tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event_group" AND tx_cal_exception_event_mm.uid_local =' . $this->object->getUid () . $this->cObj->enableFields ('tx_cal_exception_event_group'));
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$exception [] = $row ['title'];
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		
		$sims ['###EXCEPTION###'] = $this->cObj->stdWrap (implode (',', $exception), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['exception_stdWrap.']);
	}
	function getCreateExceptionMarker(& $template, & $sims, & $rems) {
		if ($this->object->isClone () && $this->rightsObj->isAllowedToCreateExceptionEvent ()) {
			$local_sims ['###ACTION_EXCEPTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url (array (
					'view' => 'save_exception_event',
					'type' => null,
					'uid' => null 
			));
			$local_sims ['###L_CREATE_EXCEPTION###'] = $this->controller->pi_getLL ('l_create_exception');
			$local_sims ['###L_TITLE###'] = $this->controller->pi_getLL ('l_event_title');
			$eventStart = $this->object->getStart ();
			$eventEnd = $this->object->getEnd ();
			$local_sims ['###EVENT_START_DAY###'] = $eventStart->format ('%Y%m%d');
			$local_sims ['###EVENT_END_DAY###'] = $eventEnd->format ('%Y%m%d');
			$local_sims ['###EVENT_START_TIME###'] = $eventStart->format ('%H%M');
			$local_sims ['###EVENT_END_TIME###'] = $eventEnd->format ('%H%M');
			$local_sims ['###EVENT_UID###'] = $this->object->getUid ();
			$rems ['###CREATE_EXCEPTION###'] = $this->cObj->getSubpart ($template, '###CREATE_EXCEPTION###');
			$rems ['###CREATE_EXCEPTION###'] = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($rems ['###CREATE_EXCEPTION###'], $local_sims, Array (), Array ());
		} else {
			$rems ['###CREATE_EXCEPTION###'] = '';
		}
	}
}

?>