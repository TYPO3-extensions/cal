<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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

/**
 * Front controller for the calendar base.  Takes requests from the main
 * controller and starts rendering in the appropriate calendar view by
 * utilizing TYPO3 services.
 *
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_viewcontroller {
	
	var $cObj;
	
	function tx_cal_viewcontroller(&$cObj){
		$this->cObj = $cObj;
	}
	
	/**
	 *  Draws the day view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawDay($master_array=array(), $getdate, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_day');
		
		$content = $viewObj->drawDay($this->cObj, $master_array, $getdate, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the week view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawWeek($master_array=array(), $getdate, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_week');
		
		$content = $viewObj->drawWeek($this->cObj, $master_array, $getdate, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the month view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawMonth($master_array=array(), $getdate, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_month');			
		$content = $viewObj->drawMonth($this->cObj, $master_array, $getdate, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the year view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawYear($master_array=array(), $getdate, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_year');
		$content = $viewObj->drawYear($this->cObj, $master_array, $getdate, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the list view.
	 *
	 *  @param		object		The events to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawList($master_array=array(), $getdate) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_list');
		$content = $viewObj->drawList($this->cObj, $master_array, $getdate);
		
		return $content;
	}
	
	/**
	 *  Draws the month view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawEvent($event, $getdate, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_event');
		$content = $viewObj->drawEvent($this->cObj, $event, $getdate, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the ics view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawIcs($master_array=array(), $getdate) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_ics');
		$content = $viewObj->drawIcs($this->cObj, $master_array, $getdate);
		
		return $content;
	}
	
	/**
	 *  Draws the location view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawLocation($organizer, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_location');
		$content = $viewObj->drawLocation($this->cObj, $organizer, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the organizer view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawOrganizer($organizer, $rightsObj) {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'single', '_organizer');
		$content = $viewObj->drawOrganizer($this->cObj, $organizer, $rightsObj);
		
		return $content;
	}
	
	/**
	 *  Draws the create event view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawCreateEvent($getdate, $rightsObj, $pidList='') {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'create_event', '_create_event');
		$content = $viewObj->drawCreateEvent($getdate, $this->cObj, $rightsObj, $pidList);
		
		return $content;
	}
	
	/**
	 *  Draws the confirm event view.
	 *
	 *  @param		object		The event to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawConfirmEvent($rightsObj, $pidList='') {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'confirm_event', '_confirm_event');
		$content = $viewObj->drawConfirmEvent($this->cObj, $rightsObj, $pidList);
		
		return $content;
	}
	
	/**
	 *  Draws the edit event view.
	 *
	 *  @param		object		The event to be edited.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawEditEvent($event, $rightsObj, $pidList='') {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'create_event', '_create_event');
		$content = $viewObj->drawCreateEvent("", $this->cObj, $rightsObj, $pidList, $event);
		
		return $content;
	}
	
	/**
	 *  Draws the delete event view.
	 *
	 *  @param		object		The event to be deleted.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawDeleteEvent($event, $rightsObj, $pidList='') {	
		/* Call the view and pass it the event to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'delete_event', '_delete_event');
		$content = $viewObj->drawDeleteEvent($event, $this->cObj, $rightsObj, $pidList);
		
		return $content;
	}
	
	/**
	 *  Draws the create location view.
	 *
	 *  @param		object		The location to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawCreateLocation($getdate, $rightsObj, $pidList='') {	
		/* Call the view and pass it the location to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'create_location', '_create_location');
		$content = $viewObj->drawCreateLocationOrOrganizer(true, $this->cObj, $rightsObj, $pidList);
		return $content;
	}
	
	/**
	 *  Draws the confirm location view.
	 *
	 *  @param		object		The location to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawConfirmLocation($rightsObj, $pidList='') {	
		/* Call the view and pass it the location to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'confirm_location', '_confirm_location');
		$content = $viewObj->drawConfirmLocationOrOrganizer(true, $this->cObj, $rightsObj, $pidList);
		
		return $content;
	}
	
	/**
	 *  Draws the edit location view.
	 *
	 *  @param		object		The location to be edited.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawEditLocation($location, $rightsObj, $pidList='') {	
		/* Call the view and pass it the location to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'create_location', '_create_location');
		$content = $viewObj->drawCreateLocationOrOrganizer(true, $this->cObj, $rightsObj, $pidList, $location);
		
		return $content;
	}
	
	/**
	 *  Draws the delete location view.
	 *
	 *  @param		object		The location to be deleted.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawDeleteLocation($location, $rightsObj, $pidList='') {	
		/* Call the view and pass it the location to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'delete_location', '_delete_location');
		$content = $viewObj->drawDeleteLocation($location, $this->cObj, $rightsObj, $pidList, $location);
		
		return $content;
	}
	
	/**
	 *  Draws the create organizer view.
	 *
	 *  @param		object		The organizer to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawCreateOrganizer($getdate, $rightsObj, $pidList='') {	
		/* Call the view and pass it the organizer to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'create_organizer', '_create_organizer');
		$content = $viewObj->drawCreateLocationOrOrganizer(false, $this->cObj, $rightsObj, $pidList);
		
		return $content;
	}
	
	/**
	 *  Draws the confirm organizer view.
	 *
	 *  @param		object		The organizer to be drawn.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawConfirmOrganizer($rightsObj, $pidList='') {	
		/* Call the view and pass it the organizer to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'confirm_organizer', '_confirm_organizer');
		$content = $viewObj->drawConfirmLocationOrOrganizer(false, $this->cObj, $rightsObj, $pidList);
		
		return $content;
	}
	
	/**
	 *  Draws the edit event view.
	 *
	 *  @param		object		The event to be edited.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawEditOrganizer($organizer, $rightsObj, $pidList='') {	
		/* Call the view and pass it the organizer to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'create_organizer', '_create_organizer');
		$content = $viewObj->drawCreateLocationOrOrganizer(false, $this->cObj, $rightsObj, $pidList, $organizer);
		
		return $content;
	}
	
	/**
	 *  Draws the delete organizer view.
	 *
	 *  @param		object		The organizer to be deleted.
	 *  @return		string		The HTML output of the specified view.
	 */
	function drawDeleteOrganizer($organizer, $rightsObj, $pidList='') {	
		/* Call the view and pass it the organizer to draw */
		$viewObj = $this->getServiceObjByKey('cal_view', 'delete_organizer', '_delete_organizer');
		$content = $viewObj->drawDeleteOrganizer($organizer, $this->cObj, $rightsObj, $pidList, $organizer);
		
		return $content;
	}
	
	/**
	 * Helper function to return a service object with the given type, subtype, and serviceKey
	 *
	 * @param	string	The type of the service.
	 * @param	string	The subtype of the service.
	 * @param	string	The serviceKey.
	 * @return	object	The service object.
	 */
	function getServiceObjByKey($type, $subtype='', $key) {		
		$serviceChain = '';		
		/* Loop over all services providign the specified service type and subtype */
		while (is_object($obj = t3lib_div::makeInstanceService($type, $subtype, $serviceChain))) {
			$serviceChain.=','.$obj->getServiceKey();
			return $obj;
		}
		
		return;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_viewcontroller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_viewcontroller.php']);
}

?>