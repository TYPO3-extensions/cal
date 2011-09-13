<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
 * (http://evangelize.org). The WEC is developing TYPO3-based
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
class tx_cal_adminview extends tx_cal_base_view {
	
	function tx_cal_adminview(){
		$this->tx_cal_base_view();
	}
	function drawAdminPage() {
		$a = array();
		$rems = array();
		$this->_init($a);
		
		$page = $this->cObj->fileResource($this->conf['view.']['admin.']['adminTemplate']);
		if ($page == '') {
			return '<h3>calendar: no adminTemplate file found:</h3>'.$this->conf['view.']['admin.']['adminTemplate'];
		}
		$return = $page;
		$showCalendarForm = false;
		$showCategoryForm = false;
		$showEventForm = false;
		$showLocationForm = false;
		$showOrganizerForm = false;
		
		if($this->rightsObj->isAllowedTo("create","calendar") && $this->rightsObj->isViewEnabled('create_calendar')){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_calendar').'"';
			$createCalendarLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_calendar'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_calendar', 'type' => 'tx_cal_calendar'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['calendar.']['createCalendarViewPid']);
			$showCalendarForm = true;
		}else{
			$rems['###CREATE_CALENDAR###'] = '';	
		}
		if(!$this->rightsObj->isAllowedTo("edit","calendar") || !$this->rightsObj->isViewEnabled('edit_calendar')){
			$rems['###EDIT_CALENDAR###'] = '';
		}else{
			$showCalendarForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","calendar") || !$this->rightsObj->isViewEnabled('delete_calendar')){
			$rems['###DELETE_CALENDAR###'] = '';
		}else{
			$showCalendarForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","calendar") && !$this->rightsObj->isAllowedTo("edit","calendar")){
			$rems['###CHOOSE_CALENDAR###'] = '';
		}
		if(!$showCalendarForm){
			$rems['###CALENDAR_FORM###'] = '';
		}
		if($this->rightsObj->isAllowedTo("create","category") && $this->rightsObj->isViewEnabled('create_category')){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_category').'"';
			$createCategoryLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_category'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_category', 'type' => 'tx_cal_category'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['category.']['createCategoryViewPid']);
			$showCategoryForm = true;
		}else{
			$rems['###CREATE_CATEGORY###'] = '';	
		}
		if(!$this->rightsObj->isAllowedTo("edit","category") || !$this->rightsObj->isViewEnabled('edit_category')){
			$rems['###EDIT_CATEGORY###'] = '';
		}else{
			$showCategoryForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","category") || !$this->rightsObj->isViewEnabled('delete_category')){
			$rems['###DELETE_CATEGORY###'] = '';
		}else{
			$showCategoryForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","category") && !$this->rightsObj->isAllowedTo("edit","category")){
			$rems['###CHOOSE_CATEGORY###'] = '';
		}
		if(!$showCategoryForm){
			$rems['###CATEGORY_FORM###'] = '';
		}
		if($this->rightsObj->isAllowedTo("create","organizer") && $this->rightsObj->isViewEnabled('create_organizer')){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_organizer').'"';
			$createOrganizerLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_organizer'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_organizer'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['organizer.']['createOrganizerViewPid']);
			$showOrganizerForm = true;
		}else{
			$rems['###CREATE_ORGANIZER###'] = '';	
		}
		if(!$this->rightsObj->isAllowedTo("edit","organizer") || !$this->rightsObj->isViewEnabled('edit_organizer')){
			$rems['###EDIT_ORGANIZER###'] = '';
		}else{
			$showOrganizerForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","organizer") || !$this->rightsObj->isViewEnabled('delete_organizer')){
			$rems['###DELETE_ORGANIZER###'] = '';
		}else{
			$showOrganizerForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","organizer") && !$this->rightsObj->isAllowedTo("edit","organizer")){
			$rems['###CHOOSE_ORGANIZER###'] = '';
		}
		if(!$showOrganizerForm){
			$rems['###ORGANIZER_FORM###'] = '';
		}
		if($this->rightsObj->isAllowedTo("create","location") && $this->rightsObj->isViewEnabled('create_location')){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_location').'"';
			$createLocationLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_location'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_location'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['location.']['createLocationViewPid']);
			$showLocationForm = true;
		}else{
			$rems['###CREATE_LOCATION###'] = '';	
		}
		if(!$this->rightsObj->isAllowedTo("edit","location") || !$this->rightsObj->isViewEnabled('edit_location')){
			$rems['###EDIT_LOCATION###'] = '';
		}else{
			$showLocationForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","location") || !$this->rightsObj->isViewEnabled('delete_location')){
			$rems['###DELETE_LOCATION###'] = '';
		}else{
			$showLocationForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","location") && !$this->rightsObj->isAllowedTo("edit","location")){
			$rems['###CHOOSE_LOCATION###'] = '';
		}
		if(!$showLocationForm){
			$rems['###LOCATION_FORM###'] = '';
		}
		if($this->rightsObj->isAllowedTo("create","event") && $this->rightsObj->isViewEnabled('create_event')){
			$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_create_event').'"';
			$createEventLink .= $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_create_event'), array ('lastview' => $this->controller->extendLastView(), 'view' => 'create_event'), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['view.']['event.']['createEventViewPid']);
			$showEventForm = true;
		}else{
			$rems['###CREATE_EVENT###'] = '';	
		}
		if(!$this->rightsObj->isAllowedTo("edit","event") || !$this->rightsObj->isViewEnabled('edit_event')){
			$rems['###EDIT_EVENT###'] = '';
		}else{
			$showEventForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","event") || !$this->rightsObj->isViewEnabled('delete_event')){
			$rems['###DELETE_EVENT###'] = '';
		}else{
			$showEventForm = true;
		}
		if(!$this->rightsObj->isAllowedTo("delete","event") && !$this->rightsObj->isAllowedTo("edit","event")){
			$rems['###CHOOSE_EVENT###'] = '';
		}
		if(!$showEventForm){
			$rems['###EVENT_FORM###'] = '';
		}
		
		//CALENDAR
		$calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar');
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_calendar').'"';
		$editCalendarOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		foreach($calendarArray as $calendar){
			if($calendar->isUserAllowedToEdit() || $calendar->isUserAllowedToDelete()){
				$editCalendarOptions .= '<option value="'.$calendar->getUID().'" >'.$calendar->getTitle().'</option>';
			}
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_calendar', 'type' => 'tx_cal_calendar');
		foreach($params as $key => $value){
			$editCalendarParams .= '<input type="hidden" value="'.$value.'" id="calendar_'.$key.'" name="'.$this->prefixId.'['.$key.']"/>';
		}

		//CATEGORY
		$categoryArrays = $this->modelObj->findAllCategories('cal_category_model','tx_cal_category',$this->conf['pidList']);

		$categoryArray = $categoryArrays['tx_cal_category'][0][0];
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_edit_category').'"';
		$editCategoryOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
		foreach($categoryArray as $category){
			if($category->isUserAllowedToEdit() || $category->isUserAllowedToDelete()){
				$editCategoryOptions .= '<option value="'.$category->getUid().'" >'.$category->getTitle().'</option>';
			}
		}
		$params = array ('lastview' => $this->controller->extendLastView(), 'view' => 'edit_category', 'type' => 'tx_cal_category');
		foreach($params as $key => $value){
			$editCategoryParams .= '<input type="hidden" value="'.$value.'" id="category_'.$key.'" name="'.$this->prefixId.'['.$key.']"/>';
		}
		
		//EVENT
		/*$editEventOptions = '<option value="">'.$this->controller->pi_getLL('l_select').'</option>';
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
		}*/
		
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
			$editLocationParams .= '<input type="hidden" value="'.$value.'" id="location_'.$key.'" name="'.$this->prefixId.'['.$key.']"/>';
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
			$editOrganizerParams .= '<input type="hidden" value="'.$value.'" id="organizer_'.$key.'" name="'.$this->prefixId.'['.$key.']"/>';
		}

		
		$sims = array(
			'###L_ADMINISTRATION_VIEW###' => $this->controller->pi_getLL('l_administration_view'),
			'###L_CREATE###' => $this->controller->pi_getLL('l_create'),
			'###L_EDIT###' => $this->controller->pi_getLL('l_edit'),
			'###L_DELETE###' => $this->controller->pi_getLL('l_delete'),
			'###L_EVENT_LABEL###' => $this->controller->pi_getLL('l_event'),
			'###CREATE_CALENDAR_LINK###' => $createCalendarLink,
			'###CREATE_CATEGORY_LINK###' => $createCategoryLink,
			'###CREATE_ORGANIZER_LINK###' => $createOrganizerLink,
			'###CREATE_LOCATION_LINK###' => $createLocationLink,
			'###CREATE_EVENT_LINK###' => $createEventLink,
			'###EDIT_EVENT_URL###' => $editEventLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###EDIT_EVENT_PARAMETER###' => $editEventParams,
			'###EDIT_EVENT_OPTIONS###' => $editEventOptions,
			'###DELETE_EVENT_URL###' => $deleteEventLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###DELETE_EVENT_PARAMETER###' => $deleteEventParams,
			'###DELETE_EVENT_OPTIONS###' => $editEventOptions,
			'###L_CALENDAR_LABEL###' => $this->controller->pi_getLL('l_calendar'),
			'###EDIT_CALENDAR_URL###' => $editCalendarLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###EDIT_CALENDAR_PARAMETER###' => $editCalendarParams,
			'###EDIT_CALENDAR_OPTIONS###' => $editCalendarOptions,
			'###DELETE_CALENDAR_URL###' => $deleteCalendarLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###DELETE_CALENDAR_PARAMETER###' => $deleteCalendarParams,
			'###DELETE_CALENDAR_OPTIONS###' => $editCalendarOptions,
			'###L_CATEGORY_LABEL###' => $this->controller->pi_getLL('l_category'),
			'###EDIT_CATEGORY_URL###' => $editCategoryLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###EDIT_CATEGORY_PARAMETER###' => $editCategoryParams,
			'###EDIT_CATEGORY_OPTIONS###' => $editCategoryOptions,
			'###DELETE_CATEGORY_URL###' => $deleteCategoryLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###DELETE_CATEGORY_PARAMETER###' => $deleteCategoryParams,
			'###DELETE_CATEGORY_OPTIONS###' => $editCategoryOptions,
			'###L_LOCATION_LABEL###' => $this->controller->pi_getLL('l_location'),			
			'###EDIT_LOCATION_URL###' => $editLocationLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###EDIT_LOCATION_PARAMETER###' => $editLocationParams,
			'###EDIT_LOCATION_OPTIONS###' => $editLocationOptions,
			'###DELETE_LOCATION_URL###' => $deleteLocationLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###DELETE_LOCATION_PARAMETER###' => $deleteLocationParams,
			'###DELETE_LOCATION_OPTIONS###' => $editLocationOptions,
			'###L_ORGANIZER_LABEL###' => $this->controller->pi_getLL('l_organizer'),						
			'###EDIT_ORGANIZER_URL###' => $editOrganizerLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###EDIT_ORGANIZER_PARAMETER###' => $editOrganizerParams,
			'###EDIT_ORGANIZER_OPTIONS###' => $editOrganizerOptions,
			'###DELETE_ORGANIZER_URL###' => $deleteOrganizerLink .= $this->controller->pi_linkTP_keepPIvars_url(),
			'###DELETE_ORGANIZER_PARAMETER###' => $deleteOrganizerParams,
			'###DELETE_ORGANIZER_OPTIONS###' => $editOrganizerOptions,
		);

		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());

		$a = array();
		return $this->finish($page, $a);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_adminview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_adminview.php']);
}
?>