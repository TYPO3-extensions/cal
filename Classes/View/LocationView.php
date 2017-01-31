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
class LocationView extends \TYPO3\CMS\Cal\View\BaseView {
	
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Draws a location.
	 * 
	 * @param
	 *        	object		The location to be drawn.
	 * @return string HTML output.
	 */
	function drawLocation($location, $relatedEvents = Array()) {
		$this->_init ($relatedEvents);
		$lastview = $this->controller->extendLastView ();
		$uid = $this->conf ['uid'];
		$type = $this->conf ['type'];
		$page = file_get_contents ($this->conf ['view.'] ['location.'] ['locationTemplate']);
		if ($page == '') {
			return $this->createErrorMessage ('No location template file found at: >' . $this->conf ['view.'] ['location.'] ['locationTemplate'] . '<.', 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.');
		}
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		if (is_object ($location)) {
			$rems ['###LOCATION###'] = $location->renderLocation ();
			if ($this->conf ['view.'] ['location.'] ['substitutePageTitle'] == 1) {
				$GLOBALS ['TSFE']->page ['title'] = $location->getName ();
				$GLOBALS ['TSFE']->indexedDocTitle = $location->getName ();
			}
		} else {
			$rems ['###LOCATION###'] = $this->cObj->cObjGetSingle ($this->conf ['view.'] ['location.'] ['noLocationFound'], $this->conf ['view.'] ['location.'] ['noLocationFound.']);
		}
		return $this->finish ($page, $rems);
	}
}

?>