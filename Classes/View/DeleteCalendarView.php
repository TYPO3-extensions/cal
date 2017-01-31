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

/**
 * A service which renders a form to create / edit a phpicalendar event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class DeleteCalendarView extends \TYPO3\CMS\Cal\View\FeEditingBaseView {
	
	var $calendar;
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Draws a delete form for a calendar.
	 * 
	 * @param
	 *        	boolean True if a location should be deleted
	 * @param
	 *        	object		The object to be deleted
	 * @param
	 *        	object		The cObject of the mother-class.
	 * @param
	 *        	object		The rights object.
	 * @return string HTML output.
	 */
	public function drawDeleteCalendar(&$calendar) {
		$page = file_get_contents ($this->conf ['view.'] ['delete_calendar.'] ['template']);
		if ($page == '') {
			return '<h3>calendar: no confirm calendar template file found:</h3>' . $this->conf ['view.'] ['delete_calendar.'] ['template'];
		}
		
		$this->object = $calendar;
		
		if (! $this->object->isUserAllowedToDelete ()) {
			return 'You are not allowed to delete this calendar!';
		}
		
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		
		$sims ['###UID###'] = $this->conf ['uid'];
		$sims ['###TYPE###'] = $this->conf ['type'];
		$sims ['###VIEW###'] = 'save_event';
		$sims ['###LASTVIEW###'] = $this->controller->extendLastView ();
		$sims ['###L_DELETE_CALENDAR###'] = $this->controller->pi_getLL ('l_delete_calendar');
		$sims ['###L_DELETE###'] = $this->controller->pi_getLL ('l_delete');
		$sims ['###L_CANCEL###'] = $this->controller->pi_getLL ('l_cancel');
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url (array (
				'view' => 'remove_calendar' 
		)));
		$this->getTemplateSubpartMarker ($page, $sims, $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		$this->getTemplateSingleMarker ($page, $sims, $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
	}
	
	public function getCalendarTypeMarker(& $template, & $sims, & $rems) {
		$calendarTypeArray = Array (
				$this->controller->pi_getLL ('l_calendar_type0'),
				$this->controller->pi_getLL ('l_calendar_exturl'),
				$this->controller->pi_getLL ('l_calendar_icsfile') 
		);
		$sims ['###CALENDARTYPE###'] = $this->applyStdWrap ($calendarTypeArray [$this->object->getCalendarType ()], 'calendarType_stdWrap');
	}
	
	public function getExtUrlMarker(& $template, & $sims, & $rems) {
		$sims ['###EXTURL###'] = '';
		if ($this->object->getCalendarType () == 1) {
			$sims ['###EXTURL###'] = $this->applyStdWrap ($this->object->getExtUrl (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['exturl_stdWrap.']);
		}
	}
	
	public function getRefreshMarker(& $template, & $sims, & $rems) {
		$sims ['###REFRESH_LABEL###'] = '';
		if ($this->object->getCalendarType () > 0) {
			$sims ['###REFRESH_LABEL###'] = $this->applyStdWrap ($this->object->getRefresh (), 'refresh_stdWrap');
		}
	}
	
	public function getIcsFileMarker(& $template, & $sims, & $rems) {
		$sims ['###ICSFILE###'] = '';
		if ($this->object->getCalendarType () == 2) {
			$sims ['###ICSFILE###'] = $this->applyStdWrap ($this->object->getIcsFile (), 'icsfile_stdWrap');
		}
	}
	
	public function getTitleMarker(& $template, & $sims, & $rems) {
		$sims ['###TITLE###'] = $this->applyStdWrap ($this->object->getTitle (), 'title_stdWrap');
	}
	
	public function getActivateFreeAndBusyMarker(& $template, & $sims, & $rems) {
		$sims ['###ACTIVATE_FREEANDBUSY###'] = $this->applyStdWrap ($this->object->isActivateFreeAndBusy () ? $this->controller->pi_getLL ('l_true') : $this->controller->pi_getLL ('l_false'), 'activateFreeAndBusy_stdWrap');
	}
	
	public function getFreeAndBusyUserMarker(& $template, & $sims, & $rems) {
		$displaylist = Array ();
		$user = $this->object->getFreeAndBusyUser ('fe_users');
		if (! empty ($user)) {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('fe_users.name', 'fe_users', 'uid IN (' . implode (',', $user) . ')' . $this->cObj->enableFields ('fe_users'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$displaylist [] = $row ['name'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		$groups = $this->object->getFreeAndBusyUser ('fe_groups');
		if (! empty ($groups)) {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('fe_groups.title', 'fe_groups', 'uid IN (' . implode (',', $groups) . ')' . $this->cObj->enableFields ('fe_groups'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$displaylist [] = $row ['title'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		$sims ['###FREEANDBUSYUSER###'] = $this->applyStdWrap (implode (',', $displaylist), 'freeAndBusyUser_stdWrap');
	}
	
	public function getOwnerMarker(& $template, & $sims, & $rems) {
		$displaylist = Array ();
		$user = $this->object->getOwner ('fe_users');
		if (! empty ($user)) {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('fe_users.name', 'fe_users', 'uid IN (' . implode (',', $user) . ')' . $this->cObj->enableFields ('fe_users'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$displaylist [] = $row ['name'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		$group = $this->object->getOwner ('fe_groups');
		if (! empty ($group)) {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('fe_groups.title', 'fe_groups', 'uid IN (' . implode (',', $group) . ')' . $this->cObj->enableFields ('fe_groups'));
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$displaylist [] = $row ['title'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		$sims ['###OWNER###'] = $this->applyStdWrap (implode (',', $displaylist), 'owner_stdWrap');
	}
}

?>