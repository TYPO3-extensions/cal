<?php
namespace TYPO3\CMS\Cal\View;
/**
 * *************************************************************
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
 * *************************************************************
 */

/**
 * A service which renders a form to create / edit a phpicategory event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class DeleteCategoryView extends \TYPO3\CMS\Cal\View\FeEditingBaseView {
	
	var $category;
	
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
	public function drawDeleteCategory(&$category) {
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['delete_category.'] ['template']);
		if ($page == '') {
			return '<h3>category: no delete category template file found:</h3>' . $this->conf ['view.'] ['delete_category.'] ['template'];
		}
		
		$this->category = $category;
		
		$rems = Array ();
		$sims = Array ();
		
		$sims ['###UID###'] = $this->conf ['uid'];
		$sims ['###TYPE###'] = $this->conf ['type'];
		$sims ['###VIEW###'] = 'remove_category';
		$sims ['###LASTVIEW###'] = $this->controller->extendLastView ();
		$sims ['###L_DELETE_CATEGORY###'] = $this->controller->pi_getLL ('l_delete_category');
		$sims ['###L_DELETE###'] = $this->controller->pi_getLL ('l_delete');
		$sims ['###L_CANCEL###'] = $this->controller->pi_getLL ('l_cancel');
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url (array (
				'view' => 'remove_category' 
		)));
		$this->getTemplateSubpartMarker ($page, $sims, $rems);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, Array ());
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		$sims = Array ();
		$rems = Array ();
		$this->getTemplateSingleMarker ($page, $sims, $rems);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, Array ());
		;
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
	}
	
	public function getFormStartMarker(& $template, & $sims, & $rems) {
		$rems ['###FORM_START###'] = $this->cObj->getSubpart ($template, '###FORM_START###');
	}
	
	public function getHiddenMarker(& $template, & $sims, & $rems) {
		$sims ['###HIDDEN###'] = $this->cObj->stdWrap ($this->category->isHidden () ? $this->controller->pi_getLL ('l_true') : $this->controller->pi_getLL ('l_false'), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['hidden_stdWrap.']);
	}
	
	public function getTitleMarker(& $template, & $sims, & $rems) {
		$sims ['###TITLE###'] = $this->cObj->stdWrap ($this->category->getTitle (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['title_stdWrap.']);
	}
	
	public function getCalendarMarker(& $template, & $sims, & $rems) {
		$calendarUid = $this->category->getCalendarUid ();
		if ($calendarUid) {
			$calendar = $this->modelObj->findCalendar ($calendarUid, 'tx_cal_calendar', $this->conf ['pidList']);
			$calendarTitle = $calendar->getTitle ();
			$sims ['###CALENDAR###'] = $this->cObj->stdWrap ($calendarTitle, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['calendar_stdWrap.']);
		} else {
			$sims ['###CALENDAR###'] = '';
		}
	}
	
	public function getHeaderStyleMarker(& $template, & $sims, & $rems) {
		$sims ['###HEADERSTYLE###'] = $this->cObj->stdWrap ($this->category->getHeaderStyle (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['headerStyle_stdWrap.']);
	}
	
	public function getBodyStyleMarker(& $template, & $sims, & $rems) {
		$sims ['###BODYSTYLE###'] = $this->cObj->stdWrap ($this->category->getBodyStyle (), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['bodyStyle_stdWrap.']);
	}
	
	public function getParentCategoryMarker(& $template, &$sims, & $rems) {
		$parentUid = $this->category->getParentUid ();
		
		if ($parentUid) {
			/* Get parent category title */
			$category = $this->modelObj->findCategory ($parentUid, 'tx_cal_category', $this->conf ['pidList']);
			$parentCategory = $category->getTitle ();
			$sims ['###PARENT_CATEGORY###'] = $this->cObj->stdWrap ($parentCategory, $this->conf ['view.'] [$this->conf ['view'] . '.'] ['parentCategory_stdWrap.']);
		} else {
			$sims ['###PARENT_CATEGORY###'] = '';
		}
	}
	
	public function getSharedUserAllowedMarker(& $template, & $sims, & $rems) {
		$sims ['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap ($this->category->isSharedUserAllowed () ? $this->controller->pi_getLL ('l_true') : $this->controller->pi_getLL ('l_false'), $this->conf ['view.'] [$this->conf ['view'] . '.'] ['sharedUserAllowed_stdWrap.']);
	}
}

?>