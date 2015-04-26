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
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class OrganizerView extends \TYPO3\CMS\Cal\View\BaseView {
	
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Draws a organizer.
	 * 
	 * @param
	 *        	object		The organizer to be drawn.
	 * @return string HTML output.
	 */
	function drawOrganizer($organizer, $relatedEvents = Array()) {
		$this->_init ($relatedEvents);
		
		$lastview = $this->controller->extendLastView ();
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['organizer.'] ['organizerTemplate']);
		if ($page == '') {
			return '<h3>calendar: no organizer template file found:</h3>' . $this->conf ['view.'] ['organizer.'] ['organizerTemplate'];
		}
		if (is_object ($organizer)) {
			$rems ['###ORGANIZER###'] = $organizer->renderOrganizer ();
			if ($this->conf ['view.'] ['event.'] ['substitutePageTitle'] == 1) {
				$GLOBALS ['TSFE']->page ['title'] = $organizer->getName ();
				$GLOBALS ['TSFE']->indexedDocTitle = $organizer->getName ();
			}
		} else {
			$rems ['###ORGANIZER###'] = $this->cObj->stdWrap ($this->controller->pi_getLL ('l_no_organizer_results'), $this->conf ['view.'] ['organizer.'] ['noOrganizerFound_stdWrap.']);
		}
		
		return $this->finish ($page, $rems);
	}
}

?>