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
class EventView extends \TYPO3\CMS\Cal\View\BaseView {
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Draws a single event.
	 * 
	 * @param $event object
	 *        	to be drawn.
	 * @param $getdate integer
	 *        	of the event
	 * @return string HTML output.
	 */
	public function drawEvent(&$event, $getdate, $relatedEvents = Array()) {
		$this->_init ($relatedEvents);
		
		if ($this->conf ['activateFluid'] == 1) {
			return $this->renderWithFluid ($event);
		}
		
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['event.'] ['eventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no template file found:</h3>' . $this->conf ['view.'] ['event.'] ['eventTemplate'];
		}
		if ($event == null) {
			$rems ['###EVENT###'] = $this->cObj->cObjGetSingle ($this->conf ['view.'] ['event.'] ['event.'] ['noEventFound'], $this->conf ['view.'] ['event.'] ['event.'] ['noEventFound.']);
		} else if ($this->conf ['preview']) {
			$rems ['###EVENT###'] = $event->renderEventPreview ();
		} else {
			$rems ['###EVENT###'] = $event->renderEvent ();
			if ($this->conf ['view.'] ['event.'] ['substitutePageTitle'] == 1) {
				$GLOBALS ['TSFE']->page ['title'] = $event->getTitle ();
				$GLOBALS ['TSFE']->indexedDocTitle = $event->getTitle ();
			}
		}
		
		return $this->finish ($page, $rems);
	}
}

?>