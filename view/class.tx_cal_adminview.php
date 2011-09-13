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
class tx_cal_adminview extends tx_cal_base_view {

	function drawAdminPage() {
		$a = array();
		$this->_init($a);
		
		$page = $this->cObj->fileResource($this->cObj->conf["view."]["admin."]["adminTemplate"]);
		if ($page == "") {
			return "<h3>calendar: no adminTemplate file found:</h3>".$this->cObj->conf["view."]["admin."]["adminTemplate"];
		}
		$return = $page;

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_calendar').'"';
		$createCalendarLink .= $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_create_calendar'), array ("lastview" => "admin", "view" => "create_calendar", "type" => "tx_cal_calendar"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["calendar."]["create_calendarViewPid"]);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_category').'"';
		$createCategoryLink .= $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_create_category'), array ("lastview" => "admin", "view" => "create_category", "type" => "tx_cal_category"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["category."]["create_categoryViewPid"]);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_organizer').'"';
		$createOrganizerLink .= $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_create_organizer'), array ("lastview" => "admin", "view" => "create_organizer"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["organizer."]["create_organizerViewPid"]);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_location').'"';
		$createLocationLink .= $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_create_location'), array ("lastview" => "admin", "view" => "create_location"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["location."]["create_locationViewPid"]);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_create_event').'"';
		$createEventLink .= $this->controller->pi_linkTP_keepPIvars($this->shared->lang('l_create_event'), array ("lastview" => "admin", "view" => "create_event"), $this->cObj->conf['cache'], $this->cObj->conf['clear_anyway'], $this->cObj->conf["view."]["event."]["create_eventViewPid"]);
		
		//CALENDAR
		$calendarArray = $this->controller->modelObj->findAllCalendar('tx_cal_calendar');
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_edit_calendar').'"';
		$editCalendarOptions = '<option value="">'.$this->shared->lang('l_select').'</option>';
		foreach($calendarArray as $calendar){
			$editCalendarOptions .= '<option value="'.$calendar['uid'].'" >'.$calendar['title'].'</option>';
		}
		$params = array ("lastview" => "admin", "view" => "edit_calendar", "type" => "tx_cal_calendar");
		foreach($params as $key => $value){
			$editCalendarParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ("lastview" => "admin", "view" => "delete_calendar", "type" => "tx_cal_calendar");
		foreach($params as $key => $value){
			$deleteCalendarParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}

		//CATEGORY
		$categoryArray = $this->controller->modelObj->findAllCategories('cal_category_model','tx_cal_category',$this->cObj->conf['pidList']);

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->shared->lang('l_edit_category').'"';
		$editCategoryOptions = '<option value="">'.$this->shared->lang('l_select').'</option>';
		foreach($categoryArray as $category){
			$editCategoryOptions .= '<option value="'.$category['uid'].'" >'.$category['title'].'</option>';
		}
		$params = array ("lastview" => "admin", "view" => "edit_category", "type" => "tx_cal_category");
		foreach($params as $key => $value){
			$editCategoryParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ("lastview" => "admin", "view" => "delete_category", "type" => "tx_cal_category");
		foreach($params as $key => $value){
			$deleteCategoryParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		//EVENT
		$editEventOptions = '<option value="">'.$this->shared->lang('l_select').'</option>';
		$eventArray = $this->controller->modelObj->findAll('cal_event_model');
		unset($eventArray['legend']);
		foreach($eventArray as $timeArray){
			foreach($timeArray as $eventArray){
				foreach($eventArray as $event){
					$editEventOptions .= '<option value="'.$event->getUid().'" >'.$event->getTitle().'</option>';
				}
			}
		}
		$params = array ("lastview" => "admin", "view" => "edit_event", "type" => "tx_cal_phpicalendar");
		foreach($params as $key => $value){
			$editEventParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ("lastview" => "admin", "view" => "delete_event", "type" => "tx_cal_phpicalendar");
		foreach($params as $key => $value){
			$deleteEventParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$sims = array(
			'l_administration_view' => $this->shared->lang('l_administration_view'),
			'l_create' => $this->shared->lang('l_create'),
			'l_edit' => $this->shared->lang('l_edit'),
			'l_delete' => $this->shared->lang('l_delete'),
			'L_EVENT_LABEL' => $this->shared->lang('l_event'),
			'CREATE_CALENDAR_LINK' => $createCalendarLink,
			'CREATE_CATEGORY_LINK' => $createCategoryLink,
			'CREATE_ORGANIZER_LINK' => $createOrganizerLink,
			'CREATE_LOCATION_LINK' => $createLocationLink,
			'CREATE_EVENT_LINK' => $createEventLink,
			'EDIT_EVENT_URL' => $editEventLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_EVENT_PARAMETER' => $editEventParams,
			'EDIT_EVENT_OPTIONS' => $editEventOptions,
			'DELETE_EVENT_URL' => $deleteEventLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_EVENT_PARAMETER' => $deleteEventParams,
			'DELETE_EVENT_OPTIONS' => $editEventOptions,
			'L_CALENDAR_LABEL' => $this->shared->lang('l_calendar'),
			'EDIT_CALENDAR_URL' => $editCalendarLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_CALENDAR_PARAMETER' => $editCalendarParams,
			'EDIT_CALENDAR_OPTIONS' => $editCalendarOptions,
			'DELETE_CALENDAR_URL' => $deleteCalendarLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_CALENDAR_PARAMETER' => $deleteCalendarParams,
			'DELETE_CALENDAR_OPTIONS' => $editCalendarOptions,
			'L_CATEGORY_LABEL' => $this->shared->lang('l_category'),
			'EDIT_CATEGORY_URL' => $editCategoryLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_CATEGORY_PARAMETER' => $editCategoryParams,
			'EDIT_CATEGORY_OPTIONS' => $editCategoryOptions,
			'DELETE_CATEGORY_URL' => $deleteCategoryLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_CATEGORY_PARAMETER' => $deleteCategoryParams,
			'DELETE_CATEGORY_OPTIONS' => $editCategoryOptions,
		);

		$page = $this->shared->replace_tags($sims, $page);
		$a = array();
		return $this->finish($page, $a);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_adminview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_adminview.php']);
}
?>