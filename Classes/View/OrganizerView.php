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