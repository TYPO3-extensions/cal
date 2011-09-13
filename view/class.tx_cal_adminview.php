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
	
	function tx_cal_adminview(){
		$this->tx_cal_base_view();
	}
	function drawAdminPage() {
		$a = array();
		$this->_init($a);
		
		$page = $this->cObj->fileResource($this->conf['view.']['admin.']['adminTemplate']);
		if ($page == '') {
			return '<h3>calendar: no adminTemplate file found:</h3>'.$this->conf['view.']['admin.']['adminTemplate'];
		}
		$return = $page;

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_calendar').'"';
		$createCalendarLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_calendar'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_calendar', 'type' => 'tx_cal_calendar'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['create_calendarViewPid']);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_category').'"';
		$createCategoryLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_category'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_category', 'type' => 'tx_cal_category'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['category.']['create_categoryViewPid']);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_organizer').'"';
		$createOrganizerLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_organizer'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_organizer'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['organizer.']['create_organizerViewPid']);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_location').'"';
		$createLocationLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_location'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_location'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['location.']['create_locationViewPid']);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
		$createEventLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_event'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['create_eventViewPid']);
		
		//CALENDAR
		$calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar');
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_calendar').'"';
		$editCalendarOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		foreach($calendarArray as $calendar){
			$editCalendarOptions .= '<option value="'.$calendar->getUID().'" >'.$calendar->getTitle().'</option>';
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_calendar', 'type' => 'tx_cal_calendar');
		foreach($params as $key => $value){
			$editCalendarParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'delete_calendar', 'type' => 'tx_cal_calendar');
		foreach($params as $key => $value){
			$deleteCalendarParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}

		//CATEGORY
		$categoryArrays = $this->modelObj->findAllCategories('cal_category_model','tx_cal_category',$this->conf['pidList']);

		$categoryArray = $categoryArrays[0];
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_category').'"';
		$editCategoryOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		foreach($categoryArray as $category){
			$editCategoryOptions .= '<option value="'.$category->getUid().'" >'.$category->getTitle().'</option>';
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_category', 'type' => 'tx_cal_category');
		foreach($params as $key => $value){
			$editCategoryParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'delete_category', 'type' => 'tx_cal_category');
		foreach($params as $key => $value){
			$deleteCategoryParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		//EVENT
		$editEventOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		$eventArray = $this->modelObj->findAll('cal_event_model','tx_cal_phpicalendar','event', $this->conf['pidList']);
		unset($eventArray['legend']);
		foreach($eventArray as $timeArray){
			foreach($timeArray as $eventArray){
				foreach($eventArray as $event){
					$editEventOptions .= '<option value="'.$event->getUid().'" >'.$event->getTitle().'</option>';
				}
			}
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_event', 'type' => 'tx_cal_phpicalendar');
		foreach($params as $key => $value){
			$editEventParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'delete_event', 'type' => 'tx_cal_phpicalendar');
		foreach($params as $key => $value){
			$deleteEventParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$locationModel = ($confArr['useLocationStructure']?$confArr['useLocationStructure']:'tx_cal_location');
		$organizerModel = ($confArr['useOrganizerStructure']?$confArr['useOrganizerStructure']:'tx_cal_organizer');
		
		//LOCATION
		$locationArray = $this->modelObj->findAllLocations($locationModel);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_location').'"';
		$editLocationOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		foreach($locationArray as $location){
			$editLocationOptions .= '<option value="'.$location->getUID().'" >'.$location->getName().'</option>';
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_location', 'type' => $locationModel);
		foreach($params as $key => $value){
			$editLocationParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'delete_location', 'type' => $locationModel);
		foreach($params as $key => $value){
			$deleteLocationParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		//ORGANIZER
		$organizerArray = $this->modelObj->findAllOrganizer($organizerModel);
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_organizer').'"';
		$editOrganizerOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		foreach($organizerArray as $organizer){
			$editOrganizerOptions .= '<option value="'.$organizer->getUID().'" >'.$organizer->getName().'</option>';
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_organizer', 'type' => $organizerModel);
		foreach($params as $key => $value){
			$editOrganizerParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'delete_organizer', 'type' => $organizerModel);
		foreach($params as $key => $value){
			$deleteOrganizerParams .= '<input type="hidden" value="'.$value.'" name="'.$this->prefixId.'['.$key.']"/>';
		}

		
		
		$sims = array(
			'l_administration_view' => $this->controller->pi_getLL('l_administration_view'),
			'l_create' => $this->controller->pi_getLL('l_create'),
			'l_edit' => $this->controller->pi_getLL('l_edit'),
			'l_delete' => $this->controller->pi_getLL('l_delete'),
			'L_EVENT_LABEL' => $this->controller->pi_getLL('l_event'),
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
			'L_CALENDAR_LABEL' => $this->controller->pi_getLL('l_calendar'),
			'EDIT_CALENDAR_URL' => $editCalendarLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_CALENDAR_PARAMETER' => $editCalendarParams,
			'EDIT_CALENDAR_OPTIONS' => $editCalendarOptions,
			'DELETE_CALENDAR_URL' => $deleteCalendarLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_CALENDAR_PARAMETER' => $deleteCalendarParams,
			'DELETE_CALENDAR_OPTIONS' => $editCalendarOptions,
			'L_CATEGORY_LABEL' => $this->controller->pi_getLL('l_category'),
			'EDIT_CATEGORY_URL' => $editCategoryLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_CATEGORY_PARAMETER' => $editCategoryParams,
			'EDIT_CATEGORY_OPTIONS' => $editCategoryOptions,
			'DELETE_CATEGORY_URL' => $deleteCategoryLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_CATEGORY_PARAMETER' => $deleteCategoryParams,
			'DELETE_CATEGORY_OPTIONS' => $editCategoryOptions,
			'L_LOCATION_LABEL' => $this->controller->pi_getLL('l_location'),			
			'EDIT_LOCATION_URL' => $editLocationLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_LOCATION_PARAMETER' => $editLocationParams,
			'EDIT_LOCATION_OPTIONS' => $editLocationOptions,
			'DELETE_LOCATION_URL' => $deleteLocationLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_LOCATION_PARAMETER' => $deleteLocationParams,
			'DELETE_LOCATION_OPTIONS' => $editLocationOptions,
			'L_ORGANIZER_LABEL' => $this->controller->pi_getLL('l_organizer'),						
			'EDIT_ORGANIZER_URL' => $editOrganizerLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'EDIT_ORGANIZER_PARAMETER' => $editOrganizerParams,
			'EDIT_ORGANIZER_OPTIONS' => $editOrganizerOptions,
			'DELETE_ORGANIZER_URL' => $deleteOrganizerLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'DELETE_ORGANIZER_PARAMETER' => $deleteOrganizerParams,
			'DELETE_ORGANIZER_OPTIONS' => $editOrganizerOptions,
		);

		$page = $this->controller->replace_tags($sims, $page);
		$a = array();
		return $this->finish($page, $a);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_adminview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_adminview.php']);
}
?>