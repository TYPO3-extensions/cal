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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class CreateEventView extends \TYPO3\CMS\Cal\View\FeEditingBaseView {
	
	/* RTE vars */
	var $RTEObj;
	var $strEntryField;
	var $docLarge = 0;
	var $RTEcounter = 0;
	var $formName;
	var $additionalJS_initial = ''; // Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
	var $additionalJS_pre = Array (); // Additional JavaScript to be printed before the form
	var $additionalJS_post = Array (); // Additional JavaScript to be printed after the form
	var $additionalJS_submit = Array (); // Additional JavaScript to be executed on submit
	var $PA = array (
			'itemFormElName' => '',
			'itemFormElValue' => '' 
	);
	var $specConf = Array ();
	var $thisConfig = Array ();
	var $RTEtypeVal = 'text';
	var $thePidValue;
	var $validation = '';
	var $cal_notifyUserIds = Array ();
	var $cal_notifyGroupIds = Array ();
	var $eventType = 'tx_cal_phpicalendar';
	var $confArr = Array ();
	
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Draws a create event form.
	 * 
	 * @param $getdate int
	 *        	date to create the event for. Format: yyyymmdd
	 * @param $pidList string
	 *        	list of pids.
	 * @param $object object
	 *        	phpicalendar object to be updated
	 * @return string HTML output.
	 */
	function drawCreateEvent($getdate, $pidList, $object = '') {
		$this->objectString = 'event';
		if (is_object ($object)) {
			$this->conf ['view'] = 'edit_' . $this->objectString;
		} else {
			$this->conf ['view'] = 'create_' . $this->objectString;
			unset ($this->controller->piVars ['uid']);
		}
		$lastPiVars = $this->controller->piVars;
		
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		
		// If an event has been passed on the form is a edit form
		if (is_object ($object) && $object->isUserAllowedToEdit ($this->rightsObj->getUserId ())) {
			$this->isEditMode = true;
			$this->object = $object;
			$this->prepareUserArray ();
			$sims ['###UID###'] = $this->object->getUid ();
			$sims ['###TYPE###'] = $this->object->getType ();
			$sims ['###L_EDIT_EVENT###'] = $this->controller->pi_getLL ('l_edit_event');
			$copy = $this->controller->piVars;
			$this->object->updateWithPIVars ($copy);
		} else {
			$sims ['###UID###'] = '';
			$sims ['###TYPE###'] = $this->eventType;
			$sims ['###L_EDIT_EVENT###'] = $this->controller->pi_getLL ('l_create_event');
			$this->object = $this->modelObj->createEvent ('tx_cal_phpicalendar');
			$this->controller->piVars ['mygetdate'] = $this->conf ['getdate'];
			$allValues = array_merge ($this->getDefaultValues (), $this->controller->piVars);
			$this->object->updateWithPIVars ($allValues);
		}
		
		$requiredFieldSims = Array ();
		$allRequiredFieldsAreFilled = $this->checkRequiredFields ($requiredFieldsSims);
		
		$constrainFieldSims = Array ();
		$noComplains = $this->checkContrains ($constrainFieldSims);
		
		if ($allRequiredFieldsAreFilled && $noComplains) {
			$GLOBALS ['TSFE']->fe_user->setKey ('ses', 'tx_cal_controller_creatingEvent', '1');
			$GLOBALS ['TSFE']->storeSessionData ();
			$this->conf ['lastview'] = $this->controller->extendLastView ();
			$this->conf ['view'] = 'confirm_' . $this->objectString;
			$this->controller->piVars = $lastPiVars;
			if ($this->conf ['view.'] ['dontShowConfirmView'] == 1) {
				return $this->controller->saveEvent ();
			}
			return $this->controller->confirmEvent ();
		}
		
		$this->initTemplate ();
		$sims ['###VIEW###'] = $this->conf ['view'];
		
		// Needed for translation options:
		$this->serviceName = 'cal_event_model';
		$this->table = 'tx_cal_event';
		
		$page = '';
		if ($this->conf ['view.'] ['enableAjax'] && $this->controller->piVars ['pid']) {
			$path = $this->conf ['view.'] ['create_event.'] ['ajaxTemplate'];
			$page = $this->cObj->fileResource ($path);
			$this->conf ['noWrapInBaseClass'] = 1;
			header ("Content-Type: application/xml");
			header ("Accept-Charset: UTF-8");
		} else {
			$path = $this->conf ['view.'] ['create_event.'] ['template'];
			$page = $this->cObj->fileResource ($path);
		}
		
		if ($page == '') {
			return \TYPO3\CMS\Cal\Utility\Functions::createErrorMessage ('No create event template file found at: >' . $path . '<.', 'Please make sure the path is correct and that you included the static template for fe-editing.');
		}
		
		if (is_object ($object) && ! $object->isUserAllowedToEdit ()) {
			return $this->controller->pi_getLL ('l_not_allowed_edit') . $this->controller->pi_getLL ('l_' . $this->objectString);
		} else if (! is_object ($object) && ! $this->rightsObj->isAllowedTo ('create', $this->objectString, '')) {
			return $this->controller->pi_getLL ('l_not_allowed_create') . $this->objectString;
		}
		
		$this->validation = '';
		
		$this->dateFormatArray = Array ();
		$this->dateFormatArray [$this->conf ['dateConfig.'] ['dayPosition']] = 'dd';
		$this->dateFormatArray [$this->conf ['dateConfig.'] ['monthPosition']] = 'mm';
		$this->dateFormatArray [$this->conf ['dateConfig.'] ['yearPosition']] = 'yyyy';
		
		$this->getTemplateSubpartMarker ($page, $sims, $rems, $wrapped, $this->conf ['view']);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		
		$sims = Array ();
		$rems = Array ();
		
		$this->getTemplateSingleMarker ($page, $sims, $rems, $this->conf ['view']);
		$this->addAdditionalMarker ($page, $sims, $rems);
		
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, Array ());
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		
		$sims = array_merge ($requiredFieldsSims, $constrainFieldSims);
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
	}
	function initTemplate() {
	}
	function getEventCalendarMarker(& $template, & $sims, & $rems) {
		$sims ['###EVENT_CALENDAR###'] = $this->object->getCalendarUid ();
	}
	function getEventCategoryMarker(& $template, & $sims, & $rems) {
		$sims ['###EVENT_CATEGORY###'] = 'new Array(';
		if ($this->isAllowed ('category')) {
			$cats = Array ();
			$categories = $this->object->getCategories ();
			if (is_array ($categories)) {
				foreach ($categories as $category) {
					$cats [] = '{"uid":' . $category->getUid () . '}';
				}
			}
			$sims ['###EVENT_CATEGORY###'] .= implode (',', $cats) . ')';
		} else {
			$sims ['###EVENT_CATEGORY###'] .= ')';
		}
	}
	function getCategoryArrayMarker(& $template, & $sims, & $rems) {
		$sims ['###CATEGORY_ARRAY###'] = 'new Array(';
		if ($this->isAllowed ('category')) {
			$tempCalendarConf = $this->conf ['calendar'];
			$tempCategoryConf = $this->conf ['category'];
			$this->conf ['calendar'] = $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['calendar.'] ['default'];
			if ($this->rightsObj->isAllowedToCreateEventCalendar ()) {
				$this->conf ['calendar'] = $this->conf ['switch_calendar'];
			}
			$this->conf ['calendar'] .= ',0';
			$this->conf ['category'] = '0';
			
			if ($this->conf ['calendar']) {
				$this->conf ['view.'] ['create_event.'] ['tree.'] ['calendar'] = $this->conf ['calendar'];
				$this->conf ['view.'] ['create_event.'] ['tree.'] ['category'] = $this->conf ['category'];
				
				$globalCategoryArrays = $this->modelObj->findAllCategories ('', 'tx_cal_category', $this->conf ['pidList']);
				$serviceKeyArray = Array ();
				foreach ($globalCategoryArrays as $serviceKey => $serviceCategoryArrays) {
					$elements = Array ();
					foreach ($serviceCategoryArrays [0] [0] as $category) {
						$elements [] = '{"uid":' . $category->getUid () . ',"parentuid":' . intval ($category->getParentUid ()) . ',"calendaruid":' . intval ($category->getCalendarUid ()) . ',"title":"' . $category->getTitle () . '","headerstyle":"' . $category->getHeaderStyle () . '","bodystyle":"' . $category->getBodyStyle () . '"}';
					}
					$serviceKeyArray [] = '{"' . $serviceKey . '": new Array(' . implode (',', $elements) . ')}';
				}
			}
			$this->conf ['calendar'] = $tempCalendarConf;
			if (! $this->conf ['category'] == '0') {
				$this->conf ['category'] = $tempCategoryConf;
			}
			$sims ['###CATEGORY_ARRAY###'] .= implode (',', $serviceKeyArray);
		}
		$sims ['###CATEGORY_ARRAY###'] .= ')';
	}
	function getCalendarArrayMarker(& $template, & $sims, & $rems) {
		$calendarArray = $this->modelObj->findAllCalendar ('tx_cal_calendar', $this->conf ['pidList']);
		$sims ['###CALENDAR_ARRAY###'] .= 'new Array(';
		$elements = Array ();
		foreach ($calendarArray ['tx_cal_calendar'] as $calendar) {
			$elements [] = '{"uid":' . $calendar->getUid () . ',"title":"' . $calendar->getTitle () . '"}';
		}
		$sims ['###CALENDAR_ARRAY###'] .= implode (',', $elements) . ')';
	}
	function getCategoryMarker(& $template, & $sims, & $rems) {
		$sims ['###CATEGORY###'] = '';
		if ($this->isAllowed ('category')) {
			
			$calendarUID = $this->object->getCalendarUid ();
			$categories = $this->object->getCategories ();
			$selectedCalendars = $this->object->getCalendarUid () . ',0';
			if (! $calendarUID && count ($categories) == 0) {
				$selectedCategories = '0';
			} else {
				$ids = array (
						0 
				);
				foreach ((array) $categories as $category) {
					if (is_object ($category)) {
						$ids [] = $category->getUid ();
					}
				}
				$selectedCategories = implode (',', $ids);
			}
			/* What does this do? */
			$this->conf ['view.'] [$this->conf ['view'] . '.'] ['tree.'] ['calendar'] = $selectedCalendars;
			$this->conf ['view.'] [$this->conf ['view'] . '.'] ['tree.'] ['category'] = $selectedCategories;
			
			$categoryArray = $this->modelObj->findAllCategories ('cal_category_model', $this->extConf ['categoryService'], $this->conf ['pidList']);
			
			$tree = $this->getCategorySelectionTree ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['tree.'], $categoryArray, true);
			$sims ['###CATEGORY###'] = $this->applyStdWrap ($tree, 'category_stdWrap');
		}
	}
	function getAlldayMarker(& $template, & $sims, & $rems) {
		$sims ['###ALLDAY###'] = '';
		if ($this->isAllowed ('allday')) {
			if ($this->object->isAllday ()) {
				$allDayValue = ' checked="checked"';
			} else {
				$allDayValue = ' ';
			}
			$sims ['###ALLDAY###'] = $this->applyStdWrap ($allDayValue, 'allday_stdWrap');
		}
	}
	function getStartdateMarker(& $template, & $sims, & $rems) {
		$sims ['###STARTDATE###'] = '';
		if ($this->isAllowed ('startdate')) {
			$eventStart = $this->object->getStart ();
			$startDateValue = $eventStart->format (\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf ($this->conf));
			
			$sims ['###STARTDATE###'] = $this->applyStdWrap ($startDateValue, 'startdate_stdWrap');
		}
	}
	function getEnddateMarker(& $template, & $sims, & $rems) {
		$sims ['###ENDDATE###'] = '';
		if ($this->isAllowed ('enddate')) {
			if ($this->object->getEnd () == 0) {
				$eventEnd = $this->object->getStart ();
			} else {
				$eventEnd = $this->object->getEnd ();
			}
			
			$endDateValue = $eventEnd->format (\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf ($this->conf));
			$sims ['###ENDDATE###'] = $this->applyStdWrap ($endDateValue, 'enddate_stdWrap');
		}
	}
	function getTimeSelector($start, $finish, $default, $stepping = 1) {
		$selector = '';
		for ($i = $start; $i < $finish; $i += $stepping) {
			$value = str_pad ($i, 2, '0', STR_PAD_LEFT);
			$selector .= '<option value="' . $value . '"' . ($default == $value ? ' selected="selected"' : '') . '>' . $value . '</option>';
		}
		
		return $selector;
	}
	function getStarttimeMarker(& $template, & $sims, & $rems) {
		$sims ['###STARTTIME###'] = '';
		if ($this->isAllowed ('starttime')) {
			$eventStart = $this->object->getStart ();
			$start_time_minute = ceil ($eventStart->getMinute () / $this->conf ['view.'] [$this->conf ['view'] . '.'] ['startminutes.'] ['stepping']) * $this->conf ['view.'] [$this->conf ['view'] . '.'] ['startminutes.'] ['stepping'];
			$start_time_hour = $eventStart->getHour ();
			
			$start_hours = $this->getTimeSelector (0, 24, $start_time_hour);
			$start_minutes = $this->getTimeSelector (0, 60, $start_time_minute, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['startminutes.'] ['stepping']);
			
			$sims ['###STARTTIME###'] = $this->applyStdWrap ($start_hours, 'starttime_stdWrap') . $this->applyStdWrap ($start_minutes, 'startminutes_stdWrap');
		}
	}
	function getEndtimeMarker(& $template, & $sims, & $rems) {
		$sims ['###ENDTIME###'] = '';
		if ($this->isAllowed ('endtime')) {
			$eventEnd = $this->object->getEnd ();
			$end_time_minute = ceil ($eventEnd->getMinute () / $this->conf ['view.'] [$this->conf ['view'] . '.'] ['startminutes.'] ['stepping']) * $this->conf ['view.'] [$this->conf ['view'] . '.'] ['startminutes.'] ['stepping'];
			$end_time_hour = $eventEnd->getHour ();
			$end_hours = $this->getTimeSelector (0, 24, $end_time_hour);
			$end_minutes = $this->getTimeSelector (0, 60, $end_time_minute, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['endminutes.'] ['stepping']);
			
			$sims ['###ENDTIME###'] = $this->applyStdWrap ($end_hours, 'endtime_stdWrap') . $this->applyStdWrap ($end_minutes, 'endminutes_stdWrap');
		}
	}
	function prepareUserArray() {
		if ($this->isEditMode) {
			// selection uids of available notify/monitor users & -groups
			$cal_notify_user = '';
			$this->cal_notifyUserIds = Array ();
			$where = ' AND tx_cal_event.uid=' . $this->object->getUid () . ' AND fe_users.deleted = 0 AND fe_users.disable = 0' . $this->cObj->enableFields ('tx_cal_event');
			// TODO add this when groups are allowed: AND tx_cal_fe_user_event_monitor_mm.tablenames="fe_users"
			$orderBy = '';
			$groupBy = '';
			$limit = '';
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ('fe_users.*', 'tx_cal_event', 'tx_cal_fe_user_event_monitor_mm', 'fe_users', $where, $groupBy, $orderBy, $limit);
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$this->cal_notifyUserIds [] = $row ['uid'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			
			$this->cal_notifyGroupIds = Array ();
			$where = ' AND tx_cal_event.uid=' . $this->object->getUid () . ' AND tx_cal_fe_user_event_monitor_mm.tablenames="fe_groups" ' . $this->cObj->enableFields ('tx_cal_event');
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ('fe_groups.*', 'tx_cal_event', 'tx_cal_fe_user_event_monitor_mm', 'fe_groups', $where, $groupBy, $orderBy, $limit);
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				array_push ($this->cal_notifyGroupIds, $row ['uid']);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
	}
	function getOrganizerMarker(& $template, & $sims, & $rems) {
		$sims ['###ORGANIZER###'] = '';
		if (! $this->extConf ['hideOrganizerTextfield'] && $this->isAllowed ('organizer')) {
			$sims ['###ORGANIZER###'] = $this->applyStdWrap ($this->object->getOrganizer (), 'organizer_stdWrap');
		}
	}
	function getCalOrganizerMarker(& $template, & $sims, & $rems) {
		$sims ['###CAL_ORGANIZER###'] = '';
		if ($this->isAllowed ('cal_organizer')) {
			$uidList = GeneralUtility::trimExplode (',', $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_organizer.'] ['allowedUids'], 1);
			$default = $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_organizer.'] ['default'];
			// creating options for organizer
			if ($this->object->getOrganizerId ()) {
				$default = $this->object->getOrganizerId ();
			}
			$cal_organizer = '<option value="">' . $this->controller->pi_getLL ('l_select') . '</option>';
			$useOrganizerStructure = ($this->extConf ['useOrganizerStructure'] ? $this->extConf ['useOrganizerStructure'] : 'tx_cal_organizer');
			$organizers = $this->modelObj->findAllOrganizer ($useOrganizerStructure, $this->conf ['pidList']);
			$feUserUid = $this->rightsObj->getUserId ();
			$feGroupsArray = $this->rightsObj->getUserGroups ();
			if ($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_organizer.'] ['allowedUids']) {
				if (! $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_organizer.'] ['default']) {
					$cal_organizer = '<option value="">' . $this->controller->pi_getLL ('l_select') . '</option>';
				}
				foreach ($organizers as $organizer) {
					if (in_array ($organizer->getUid (), $uidList)) {
						if (($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_organizer.'] ['onlyOwn']) && ! $organizer->isSharedUser ($feUserUid, $feGroupsArray)) {
							continue;
						}
						$cal_organizer .= '<option value="' . $organizer->getUid () . '"';
						if ($organizer->getUid () == $default) {
							$cal_organizer .= ' selected="selected"';
						}
						$this->initLocalCObject ($organizer->getValuesAsArray ());
						$this->local_cObj->setCurrentVal ($organizer->getName ());
						$optionValue = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['organizerDisplayField'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['organizerDisplayField.']);
						$cal_organizer .= '>' . $optionValue . '</option>';
					}
				}
			} 			// if no default values found
			else {
				// creating options for location by standard fe plugin entry point
				foreach ((array) $organizers as $organizer) {
					if (($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_organizer.'] ['onlyOwn']) && ! $organizer->isSharedUser ($feUserUid, $feGroupsArray)) {
						continue;
					}
					$cal_organizer .= '<option value="' . $organizer->getUid () . '"';
					if ($organizer->getUid () == $default) {
						$cal_organizer .= ' selected="selected"';
					}
					$this->initLocalCObject ($organizer->getValuesAsArray ());
					$this->local_cObj->setCurrentVal ($organizer->getName ());
					$optionValue = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['organizerDisplayField'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['organizerDisplayField.']);
					$cal_organizer .= '>' . $optionValue . '</option>';
				}
			}
			$sims ['###CAL_ORGANIZER###'] = $this->applyStdWrap ($cal_organizer, 'cal_organizer_stdWrap');
		}
	}
	function getLocationMarker(& $template, & $sims, & $rems) {
		$sims ['###LOCATION###'] = '';
		if (! $this->extConf ['hideLocationTextfield'] && $this->isAllowed ('location')) {
			$sims ['###LOCATION###'] = $this->applyStdWrap ($this->object->getLocation (), 'location_stdWrap');
		}
	}
	function getCalLocationMarker(& $template, & $sims, & $rems) {
		$sims ['###CAL_LOCATION###'] = '';
		if ($this->isAllowed ('cal_location')) {
			$uidList = GeneralUtility::trimExplode (',', $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_location.'] ['allowedUids'], 1);
			$default = $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_location.'] ['default'];
			if ($this->object->getLocationId ()) {
				$default = $this->object->getLocationId ();
			}
			// creating options for location
			$cal_location = '<option value="">' . $this->controller->pi_getLL ('l_select') . '</option>';
			$useLocationStructure = ($this->extConf ['useLocationStructure'] ? $this->extConf ['useLocationStructure'] : 'tx_cal_location');
			$locations = $this->modelObj->findAllLocations ($useLocationStructure, $this->conf ['pidList']);
			$feUserUid = $this->rightsObj->getUserId ();
			$feGroupsArray = $this->rightsObj->getUserGroups ();
			if ($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_location.'] ['allowedUids']) {
				if (! $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_location.'] ['default']) {
					$cal_location = '<option value="">' . $this->controller->pi_getLL ('l_select') . '</option>';
				}
				foreach ($locations as $location) {
					if (in_array ($location->getUid (), $uidList)) {
						if (($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_location.'] ['onlyOwn']) && ! $location->isSharedUser ($feUserUid, $feGroupsArray)) {
							continue;
						}
						$cal_location .= '<option value="' . $location->getUid () . '"';
						if ($location->getUid () == $default) {
							$cal_location .= ' selected="selected"';
						}
						$this->initLocalCObject ($location->getValuesAsArray ());
						$this->local_cObj->setCurrentVal ($location->getName ());
						$optionValue = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['locationDisplayField'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['locationDisplayField.']);
						$cal_location .= '>' . $optionValue . '</option>';
					}
				}
			} 			// if no default values found
			else {
				// creating options for location by standard fe plugin entry point
				foreach ($locations as $location) {
					if ($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['cal_location.'] ['onlyOwn'] && ! $location->isSharedUser ($feUserUid, $feGroupsArray)) {
						continue;
					}
					$cal_location .= '<option value="' . $location->getUid () . '"';
					if ($location->getUid () == $default) {
						$cal_location .= ' selected="selected"';
					}
					$this->initLocalCObject ($location->getValuesAsArray ());
					$this->local_cObj->setCurrentVal ($location->getName ());
					$optionValue = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['locationDisplayField'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['locationDisplayField.']);
					$cal_location .= '>' . $optionValue . '</option>';
				}
			}
			$sims ['###CAL_LOCATION###'] = $this->applyStdWrap ($cal_location, 'cal_location_stdWrap');
		}
	}
	function getDescriptionMarker(& $template, & $sims, & $rems) {
		$sims ['###ADDITIONALJS_PRE###'] = '';
		$sims ['###ADDITIONALJS_POST###'] = '';
		$sims ['###ADDITIONALJS_SUBMIT###'] = '';
		$sims ['###DESCRIPTION###'] = '';
		if ($this->isAllowed ('description')) {
			$sims ['###DESCRIPTION###'] = $this->cObj->stdWrap ('<textarea name="tx_cal_controller[description]" id="cal_event_description">' . $this->object->getDescription () . '</textarea>', $this->conf ['view.'] [$this->conf ['view'] . '.'] ['description_stdWrap.']);
			
			/* Start setting the RTE markers */
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('tinymce_rte')) {
				require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('tinymce_rte') . 'pi1/class.tx_tinymce_rte_pi1.php'); // alternative RTE
			}
			if (! $this->RTEObj && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('rtehtmlarea')) {
				$this->RTEObj = new \TYPO3\CMS\Rtehtmlarea\Controller\FrontendRteController();
			} else if (! $this->RTEObj && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('tinymce_rte')) {
				$this->RTEObj = \TYPO3\CMS\Cal\Utility\Functions::makeInstance('tx_tinymce_rte_pi1'); // load alternative RTE
			}
			if (is_object ($this->RTEObj) && $this->RTEObj->isAvailable () && $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['enableRTE']) {
				$this->RTEcounter ++;
				$this->formName = 'tx_cal_controller';
				$this->strEntryField = 'description';
				$this->PA ['itemFormElName'] = 'tx_cal_controller[description]';
				$this->PA ['itemFormElValue'] = $this->object->getDescription ();
				$this->thePidValue = $GLOBALS ['TSFE']->id;
				if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['rte.'] ['width'] > 0) {
					$this->RTEObj->RTEdivStyle = 'width:' . $this->conf ['view.'] [$this->conf ['view'] . '.'] ['rte.'] ['width'] . 'px;';
				}
				if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['rte.'] ['height'] > 0) {
					$this->RTEObj->RTEdivStyle .= 'height:' . $this->conf ['view.'] [$this->conf ['view'] . '.'] ['rte.'] ['height'] . 'px;';
				}
				
				$RTEItem = $this->RTEObj->drawRTE ($this, 'tx_cal_event', $this->strEntryField, $row = Array (), $this->PA, $this->specConf, $this->thisConfig, $this->RTEtypeVal, '', $this->thePidValue);
				$sims ['###ADDITIONALJS_PRE###'] = $this->additionalJS_initial . '
					<script type="text/javascript">' . implode (chr (10), $this->additionalJS_pre) . '
					</script>';
				$sims ['###ADDITIONALJS_POST###'] = '
					<script type="text/javascript">' . implode (chr (10), $this->additionalJS_post) . '
					</script>';
				$sims ['###ADDITIONALJS_SUBMIT###'] = implode (';', $this->additionalJS_submit);
				$sims ['###DESCRIPTION###'] = $this->applyStdWrap ($RTEItem, 'description_stdWrap');
			}
		}
	}
	function getAdditionaljsPostMarker(& $template, & $sims, & $rems) {
		// do nothing, to ensure that the preset marker doesn't get overwritten
	}
	function getAdditionaljsPreMarker(& $template, & $sims, & $rems) {
		// do nothing, to ensure that the preset marker doesn't get overwritten
	}
	function getTeaserMarker(& $template, & $sims, & $rems) {
		$sims ['###TEASER###'] = '';
		if ($this->isAllowed ('teaser')) {
			$sims ['###TEASER###'] = $this->applyStdWrap ($this->object->getTeaser (), 'teaser_stdWrap');
		}
	}
	function getFrequencyMarker(& $template, & $sims, & $rems) {
		$sims ['###FREQUENCY###'] = '';
		$frequency_values = array (
				'none',
				'day',
				'week',
				'month',
				'year' 
		);
		$frequency = '';
		
		if ($this->isAllowed ('recurring')) {
			foreach ($frequency_values as $freq) {
				$frequencyValue = $this->object->getFreq ();
				if ($freq == $frequencyValue) {
					$selectedFrequency = 'selected="selected"';
				} else {
					$selectedFrequency = '';
				}
				
				$frequency .= '<option value="' . $freq . '"' . $selectedFrequency . '>' . $this->controller->pi_getLL ('l_' . $freq) . '</option>';
			}
			$sims ['###FREQUENCY###'] = $this->applyStdWrap ($frequency, 'frequency_stdWrap');
		}
	}
	function getByDayMarker(& $template, & $sims, & $rems) {
		$sims ['###BY_DAY###'] = '';
		if ($this->isAllowed ('recurring')) {
			$by_day = array (
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
			foreach ($by_day as $day) {
				if (in_array ($day, $this->object->getByDay ())) {
					$temp_sims ['###BY_DAY_CHECKED_' . $day . '###'] = 'checked />' . strftime ('%a', $dayName);
				} else {
					$temp_sims ['###BY_DAY_CHECKED_' . $day . '###'] = '/>' . strftime ('%a', $dayName);
				}
				$dayName += 86400;
			}
			$sims ['###BY_DAY###'] = $this->applyStdWrap (implode ('###SPLITTER###', $temp_sims), 'byDay_stdWrap');
		}
	}
	function getByMonthDayMarker(& $template, & $sims, & $rems) {
		$sims ['###BY_MONTHDAY###'] = '';
		if ($this->isAllowed ('recurring')) {
			$sims ['###BY_MONTHDAY###'] = $this->applyStdWrap (implode (',', $this->object->getByMonthDay ()), 'byMonthday_stdWrap');
		}
	}
	function getByMonthMarker(& $template, & $sims, & $rems) {
		$sims ['###BY_MONTH###'] = '';
		if ($this->isAllowed ('recurring')) {
			$sims ['###BY_MONTH###'] = $this->applyStdWrap (implode (',', $this->object->getByMonth ()), 'byMonth_stdWrap');
		}
	}
	function getUntilMarker(& $template, & $sims, & $rems) {
		$sims ['###UNTIL###'] = '';
		if ($this->isAllowed ('recurring')) {
			$until = $this->object->getUntil ();
			if (is_object ($until) && $until->getYear () != 0) {
				$untilValue = $until->format (\TYPO3\CMS\Cal\Utility\Functions::getFormatStringFromConf ($this->conf));
				$sims ['###UNTIL###'] = $this->applyStdWrap ($untilValue, 'until_stdWrap');
			} else {
				$sims ['###UNTIL###'] = $this->applyStdWrap ('', 'until_stdWrap');
			}
		}
	}
	function getCountMarker(& $template, & $sims, & $rems) {
		$sims ['###COUNT###'] = '';
		if ($this->isAllowed ('recurring')) {
			$sims ['###COUNT###'] = $this->applyStdWrap ($this->object->getCount (), 'count_stdWrap');
		}
	}
	function getIntervalMarker(& $template, & $sims, & $rems) {
		$sims ['###INTERVAL###'] = '';
		if ($this->isAllowed ('recurring')) {
			$sims ['###INTERVAL###'] = $this->applyStdWrap ($this->object->getInterval (), 'interval_stdWrap');
		}
	}
	function getRdateTypeMarker(& $template, & $sims, & $rems) {
		$sims ['###RDATE_TYPE###'] = '';
		$rdateType_values = array (
				'none',
				'date_time',
				'date',
				'period' 
		);
		$rdateType = '';
		
		if ($this->isAllowed ('recurring')) {
			foreach ($rdateType_values as $rdate) {
				$rdateTypeValue = $this->object->getRdateType ();
				if ($rdate == $rdateTypeValue) {
					$selectedRdateType = 'selected="selected"';
				} else {
					$selectedRdateType = '';
				}
				
				$rdateType .= '<option value="' . $rdate . '"' . $selectedRdateType . '>' . $this->controller->pi_getLL ('l_' . $rdate) . '</option>';
			}
			$sims ['###RDATE_TYPE###'] = $this->applyStdWrap ($rdateType, 'rdateType_stdWrap');
		}
	}
	function getNotifyMarker(& $template, & $sims, & $rems) {
		$sims ['###NOTIFY###'] = '';
		if ($this->isAllowed ('notify')) {
			$cal_notify_user = '';
			$allowedUsers = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['allowedUsers'], 1);
			$selectedUsersPlusOffset = $this->object->getNotifyUserIds ();
			$selectedUsers = Array ();
			$userOffsetIndex = Array ();
			foreach ($selectedUsersPlusOffset as $userPlusOffset) {
				$userOffsetArray = GeneralUtility::trimExplode ('|', $userPlusOffset, 1);
				$selectedUsers [] = $userOffsetArray [0];
				$userOffsetIndex [$userOffsetArray [0]] = $userOffsetArray [1] == '' ? $this->conf ['view.'] ['event.'] ['remind.'] ['time'] : $userOffsetArray [1];
			}
			if (empty ($selectedUsers) && ! $this->isEditMode) {
				$selectedUsers = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['notify.'] ['defaultUser'], 1);
			}
			
			$pidWhere = ' pid in (' . $this->conf ['pidList'] . ') ';
			
			$allowedUsersList = implode ( ',', $allowedUsers );
			
			// Read only allowed users and only users with email address
			$allowedUsersWhere = ' AND email!="" ' . ($allowedUsers ? ' AND uid in (' . $allowedUsersList . ') ' : '');
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'fe_users', $pidWhere . $allowedUsersWhere . $this->cObj->enableFields ( 'fe_users' ) );
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				if (in_array ( $row ['uid'], $selectedUsers )) {
					$cal_notify_user .= '<input type="checkbox" value="u_' . $row ['uid'] . '_' . $row ['username'] . '" checked="checked" name="tx_cal_controller[notify][]" />' . $row ['username'] . '%%%L_REMIND_MINUTES_1%%%<input type="text" value="' . ($userOffsetIndex [$row ['uid']] ? $userOffsetIndex [$row ['uid']] : $this->conf ['view.'] ['event.'] ['remind.'] ['time']) . '"  name="tx_cal_controller[u_' . $row ['uid'] . '_notify_offset]" class="reminderOffset"/>%%%L_REMIND_MINUTES_2%%%<br />';
				} else {
					$cal_notify_user .= '<input type="checkbox" value="u_' . $row ['uid'] . '_' . $row ['username'] . '"  name="tx_cal_controller[notify][]"/>' . $row ['username'] . '%%%L_REMIND_MINUTES_1%%%<input type="text" value="' . $this->conf ['view.'] ['event.'] ['remind.'] ['time'] . '"  name="tx_cal_controller[u_' . $row ['uid'] . '_notify_offset]" class="reminderOffset"/>%%%L_REMIND_MINUTES_2%%%<br />';
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			$allowedGroups = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['allowedGroups'], 1);
			$selectedGroupsPlusOffset = $this->object->getNotifyGroupIds ();
			$selectedGroups = Array ();
			$groupOffsetIndex = Array ();
			foreach ($selectedGroupsPlusOffset as $groupPlusOffset) {
				$groupOffsetArray = GeneralUtility::trimExplode ('|', $groupPlusOffset, 1);
				$selectedGroups [] = $groupOffsetArray [0];
				$groupOffsetIndex [$groupOffsetArray [0]] = $groupOffsetArray [1] == '' ? $this->conf ['view.'] ['event.'] ['remind.'] ['time'] : $groupOffsetArray [1];
			}
			if (empty ($selectedGroups) && ! $this->isEditMode) {
				$selectedGroups = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['create.'] ['event.'] ['fields.'] ['notify.'] ['defaultGroup'], 1);
			}
			
			$allowedGroupsList = implode (',', $allowedGroups);
			// Read only allowed groups
			$allowedGroupsWhere = $allowedGroups ? ' AND uid in (' . $allowedGroupsList . ') ' : '';
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'fe_groups', $pidWhere . $allowedGroupsWhere . $this->cObj->enableFields ( 'fe_groups' ) );
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				if (in_array ( $row ['uid'], $selectedGroups ) !== false) {
					$cal_notify_user .= '<input type="checkbox" value="g_' . $row ['uid'] . '_' . $row ['title'] . '" checked="checked" name="tx_cal_controller[notify][]" />' . $row ['title'] . '%%%L_REMIND_MINUTES_1%%%<input type="text" value="' . ($groupOffsetIndex [$row ['uid']] ? $groupOffsetIndex [$row ['uid']] : $this->conf ['view.'] ['event.'] ['remind.'] ['time']) . '"  name="tx_cal_controller[g_' . $row ['uid'] . '_notify_offset]" class="reminderOffset"/>%%%L_REMIND_MINUTES_2%%%<br />';
				} else {
					$cal_notify_user .= '<input type="checkbox" value="g_' . $row ['uid'] . '_' . $row ['title'] . '"  name="tx_cal_controller[notify][]"/>' . $row ['title'] . '%%%L_REMIND_MINUTES_1%%%<input type="text" value="' . $this->conf ['view.'] ['event.'] ['remind.'] ['time'] . '"  name="tx_cal_controller[g_' . $row ['uid'] . '_notify_offset]" class="reminderOffset"/>%%%L_REMIND_MINUTES_2%%%<br />';
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			$sims ['###NOTIFY###'] = $this->applyStdWrap ($cal_notify_user, 'notify_stdWrap');
		}
	}
	function getExceptionMarker(& $template, & $sims, & $rems) {
		$sims ['###EXCEPTION###'] = '';
		if ($this->rightsObj->isAllowedToCreateEventException () || $this->rightsObj->isAllowedToEditEventException ()) {
			// creating options for exception events & -groups
			$exception = '';
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'tx_cal_exception_event', 'pid in (' . $this->conf ['pidList'] . ')' . $this->cObj->enableFields ('tx_cal_exception_event'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if (is_array ($this->object->getExceptionSingleIds ()) && array_search ($row ['uid'], $this->object->getExceptionSingleIds ()) !== false) {
					$exception .= '<input type="checkbox" value="u_' . $row ['uid'] . '_' . $row ['title'] . '" checked="checked" name="tx_cal_controller[exception_ids][]"/>' . $row ['title'] . '<br />';
				} else {
					$exception .= '<input type="checkbox" value="u_' . $row ['uid'] . '_' . $row ['title'] . '" name="tx_cal_controller[exception_ids][]" />' . $row ['title'] . '<br />';
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'tx_cal_exception_event_group', 'pid in (' . $this->conf ['pidList'] . ')' . $this->cObj->enableFields ('tx_cal_exception_event_group'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if (is_array ($this->object->getExceptionGroupIds ()) && array_search ($row ['uid'], $this->object->getExceptionGroupIds ()) !== false) {
					$exception .= '<input type="checkbox" value="g_' . $row ['uid'] . '_' . $row ['title'] . '" checked="checked" name="tx_cal_controller[exception_ids][]" />' . $row ['title'] . '<br />';
				} else {
					$exception .= '<input type="checkbox" value="g_' . $row ['uid'] . '_' . $row ['title'] . '" name="tx_cal_controller[exception_ids][]" />' . $row ['title'] . '<br />';
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			
			$sims ['###EXCEPTION###'] = $this->cObj->stdWrap ($exception, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['exception_stdWrap.']);
		}
	}
	function getFormStartMarker(& $template, & $sims, & $rems) {
		$temp = $this->cObj->getSubpart ($template, '###FORM_START###');
		$temp_sims = Array ();
		
		$temp_sims ['###L_WRONG_SPLIT_SYMBOL_MSG###'] = str_replace ('###DATE_SPLIT_SYMBOL###', $this->conf ['dateConfig.'] ['splitSymbol'], $this->controller->pi_getLL ('l_wrong_split_symbol_msg'));
		$temp_sims ['###L_WRONG_DATE_MSG###'] = $this->controller->pi_getLL ('l_wrong_date');
		$temp_sims ['###L_WRONG_TIME_MSG###'] = $this->controller->pi_getLL ('l_wrong_time');
		$temp_sims ['###L_IS_IN_PAST_MSG###'] = $this->controller->pi_getLL ('l_is_in_past');
		$rems ['###FORM_START###'] = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($temp, $temp_sims, Array (), Array ());
	}
	function addAdditionalMarker(& $template, & $sims, & $rems) {
		$sims ['###DATE_SPLIT_SYMBOL###'] = $this->conf ['dateConfig.'] ['splitSymbol'];
		$sims ['###DATE_DAY_POSITION###'] = $this->conf ['dateConfig.'] ['dayPosition'];
		$sims ['###DATE_MONTH_POSITION###'] = $this->conf ['dateConfig.'] ['monthPosition'];
		$sims ['###DATE_YEAR_POSITION###'] = $this->conf ['dateConfig.'] ['yearPosition'];
		$sims ['###VALIDATION###'] = $this->validation;
		
		$sims ['###GETDATE###'] = $this->conf ['getdate'];
		$sims ['###GETTIME###'] = $this->conf ['gettime'];
		$sims ['###THIS_VIEW###'] = 'create_event';
		$sims ['###NEXT_VIEW###'] = 'create_event';
		$sims ['###LASTVIEW###'] = $this->controller->extendLastView ();
		
		$sims ['###OPTION###'] = $this->conf ['option'];
		if (! $this->isEditMode) {
			if (($this->isEditMode && ! $this->rightsObj->isAllowedToEditEventCalendar ()) || (! $this->isEditMode && $this->rightsObj->isAllowedToCreateEventCalendar ())) {
				$calendarArray = $this->modelObj->findAllCalendar ('tx_cal_calendar', $this->conf ['pidList']);
				if (empty ($calendarArray ['tx_cal_calendar'])) {
					return '<h3>You have to create a calendar before you can create events</h3>';
				}
			}
		}
		$linkParams = Array ();
		$linkParams ['formCheck'] = '1';
		if (($this->isEditMode && ! $this->rightsObj->isAllowedToEditEventCalendar ()) || (! $this->isEditMode && $this->rightsObj->isAllowedToCreateEventCalendar ())) {
			// $linkParams['lastview'] = $this->controller->extendLastView();
		}
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url ($linkParams));
		
		$sims ['###CHANGE_CALENDAR_ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url ());
	}
	function getDateFormatMarker(& $template, & $sims, & $rems) {
		$dateFormatArray = Array ();
		$dateFormatArray [$this->conf ['dateConfig.'] ['dayPosition']] = 'd';
		$dateFormatArray [$this->conf ['dateConfig.'] ['monthPosition']] = 'm';
		$dateFormatArray [$this->conf ['dateConfig.'] ['yearPosition']] = 'Y';
		
		$sims ['###DATE_FORMAT###'] = $dateFormatArray [0] . $this->conf ['dateConfig.'] ['splitSymbol'] . $dateFormatArray [1] . $this->conf ['dateConfig.'] ['splitSymbol'] . $dateFormatArray [2];
	}
	function getEventTypeMarker(& $template, & $sims, & $rems) {
		$sims ['###EVENT_TYPE###'] = '';
		
		if ($this->isAllowed ('event_type')) {
			$idList = explode (',', $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['event_type.'] ['allowedUids']);
			$default = $this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] ['event.'] ['fields.'] ['event_type.'] ['default'];
			if ($this->object->getEventType () != 0) {
				$default = $this->object->getEventType ();
			}
			// creating options for event type
			$eventType = '<option value="">' . $this->controller->pi_getLL ('l_event_type_' . $default) . '</option>';
			if (count ($idList) > 0) {
				$eventType = '';
				foreach ($idList as $eventTypeId) {
					$eventType .= '<option value="' . $eventTypeId . '"';
					if ($eventTypeId == $default) {
						$eventType .= ' selected="selected"';
					}
					$optionValue = $this->controller->pi_getLL ('l_event_type_' . $eventTypeId);
					$eventType .= '>' . $optionValue . '</option>';
				}
			}
			$sims ['###EVENT_TYPE###'] = $this->applyStdWrap ($eventType, 'event_type_stdWrap');
		}
	}
	function getAttendeeMarker(& $template, & $sims, & $rems) {
		$sims ['###ATTENDEE###'] = '';
		if ($this->isAllowed ('attendee')) {
			$attendee = '';
			$allowedUsers = GeneralUtility::trimExplode (',', $this->conf ['rights.'] ['allowedUsers'], 1);
			$selectedUsers = Array (
					0 
			);
			$globalAttendeeArray = $this->object->getAttendees ();
			$attendeeAttendance = Array ();
			$externalAttendees = Array ();
			foreach ($globalAttendeeArray as $serviceKey => $attendeeArray) {
				foreach ($attendeeArray as $attendeeObject) {
					$selectedUsers [] = $attendeeObject->getFeUserId () ? $attendeeObject->getFeUserId () : $attendeeObject->getEmail ();
					$attendeeAttendance [$attendeeObject->getFeUserId () ? $attendeeObject->getFeUserId () : $attendeeObject->getEmail ()] = $attendeeObject->getAttendance ();
					if (! $attendeeObject->getFeUserId ()) {
						$externalAttendees [] = $attendeeObject->getEmail ();
					}
				}
			}
			
			$selectedUsersList = implode (',', $selectedUsers);
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', 'pid in (' . $this->conf ['pidList'] . ')' . $this->cObj->enableFields ('fe_users'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$name = $this->getFeUserDisplayName ($row);
				$attendee .= '<span>';
				if (! empty ($allowedUsers) && GeneralUtility::inList ($this->conf ['rights.'] ['allowedUsers'], $row ['uid'])) {
					
					if (GeneralUtility::inList ($selectedUsersList, $row ['uid'])) {
						$attendee .= '<input type="checkbox" value="u_' . $row ['uid'] . '" checked="checked" name="tx_cal_controller[attendee][]" />' . $name;
					} else {
						$attendee .= '<input type="checkbox" value="u_' . $row ['uid'] . '"  name="tx_cal_controller[attendee][]"/>' . $name;
					}
				} else if (empty ($allowedUsers)) {
					if (GeneralUtility::inList ($selectedUsersList, $row ['uid'])) {
						$attendee .= '<input type="checkbox" value="u_' . $row ['uid'] . '" checked="checked" name="tx_cal_controller[attendee][]" />' . $name . $this->getAttendeeOptions ('u_' . $row ['uid'], $attendeeAttendance [$row ['uid']]);
					} else {
						$attendee .= '<input type="checkbox" value="u_' . $row ['uid'] . '"  name="tx_cal_controller[attendee][]"/>' . $name . $this->getAttendeeOptions ('u_' . $row ['uid'], $attendeeAttendance [$row ['uid']]);
					}
				}
				if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['freeAndBusyViewPid'] && $row ['tx_cal_calendar'] && $this->rightsObj->isLoggedIn ()) {
					$groups = $this->rightsObj->getUserGroups ();
					$userId = $this->rightsObj->getUserId ();
					$where = 'uid_local = ' . $row ['tx_cal_calendar'] . ' AND ((tablenames = "fe_users" AND uid_foreign = ' . $userId . ') OR (tablenames = "fe_groups" AND uid_foreign in (' . implode (',', $groups) . ')))';
					$result1 = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'tx_cal_calendar_fnb_user_group_mm', $where);
					$calendarOwner = Array ();
					
					while ($row1 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result1)) {
						$ids [] = $row1 ['uid_local'];
						$calendarOwner [$row1 ['uid_local']] [$row1 ['tablenames']] [] = $row1 ['uid_foreign'];
					}
					$GLOBALS ['TYPO3_DB']->sql_free_result ($result1);
					
					$freeAndBusyIsEnabled = false;
					$result1 = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'tx_cal_calendar', 'pid in (' . $this->conf ['pidList'] . ') AND uid =' . $row ['tx_cal_calendar'] . $this->cObj->enableFields ('tx_cal_calendar'));
					while ($row1 = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result1)) {
						if ($row1 ['activate_fnb'] == 1) {
							$freeAndBusyIsEnabled = true;
						}
					}
					$GLOBALS ['TYPO3_DB']->sql_free_result ($result1);
					
					if ($freeAndBusyIsEnabled && ! empty ($calendarOwner)) {
						$start = $this->object->getStart ();
						$this->controller->pi_linkTP ('test', array (
								'tx_cal_controller[calendar]' => $row ['tx_cal_calendar'],
								'tx_cal_controller[getdate]' => $start->format ('%Y%m%d') 
						), $this->conf ['clear_anyway'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['freeAndBusyViewPid']);
						$tempArray ['link'] = $this->cObj->lastTypoLinkUrl;
						$this->initLocalCObject ($tempArray);
						$attendee .= $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['freeAndBusyViewLink'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['freeAndBusyViewLink.']);
					}
				}
				$attendee .= '</span><br/>';
			}
			
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			$sims ['###ATTENDEE###'] = $this->applyStdWrap ($attendee, 'attendee_stdWrap');
		}
	}
	function getAttendeeOptions($attendeeId, $selectedValue = '') {
		$options = Array (
				'OPT-PARTICIPANT' => $this->controller->pi_getLL ('l_event_attendee_OPT-PARTICIPANT'),
				'REQ-PARTICIPANT' => $this->controller->pi_getLL ('l_event_attendee_REQ-PARTICIPANT'),
				'CHAIR' => $this->controller->pi_getLL ('l_event_attendee_CHAIR') 
		);
		$html = '<select name="tx_cal_controller[attendance][' . $attendeeId . ']">';
		foreach ($options as $value => $option) {
			$html .= '<option value="' . $value . '"';
			
			if ($value == $selectedValue) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $option . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	function getAttendeeExternalMarker(& $template, & $sims, & $rems) {
		$sims ['###ATTENDEE_EXTERNAL###'] = '';
		if ($this->isAllowed ('attendee_external')) {
			$selectedUsers = Array (
					0 
			);
			$globalAttendeeArray = $this->object->getAttendees ();
			$attendeeAttendance = Array ();
			$externalAttendees = Array ();
			foreach ($globalAttendeeArray as $serviceKey => $attendeeArray) {
				foreach ($attendeeArray as $attendeeObject) {
					$selectedUsers [] = $attendeeObject->getFeUserId () ? $attendeeObject->getFeUserId () : $attendeeObject->getEmail ();
					$attendeeAttendance [$attendeeObject->getFeUserId () ? $attendeeObject->getFeUserId () : $attendeeObject->getEmail ()] = $attendeeObject->getAttendance ();
					if (! $attendeeObject->getFeUserId ()) {
						$externalAttendees [] = $attendeeObject->getEmail ();
					}
				}
			}
			$sims ['###ATTENDEE_EXTERNAL###'] = $this->applyStdWrap (implode (',', $externalAttendees), 'attendee_external_stdWrap');
		}
	}
	function getSendoutInvitationMarker(& $template, & $sims, & $rems, $view) {
		$sims ['###SENDOUT_INVITATION###'] = '';
		
		if ($this->isAllowed (sendout_invitation)) {
			$sendoutInvitation = '';
			if ($this->conf ['rights.'] [$this->isEditMode ? 'edit.' : 'create.'] [$this->objectString . '.'] ['fields.'] ['sendout_invitation.'] ['default'] || $this->controller->piVars ['sendout_invitation']) {
				$sendoutInvitation = ' checked="checked" ';
			}
			$sims ['###SENDOUT_INVITATION###'] = $this->applyStdWrap ($this->object->getSendoutInvitation (), 'sendout_invitation_stdWrap');
		}
	}
}