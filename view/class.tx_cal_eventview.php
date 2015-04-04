<?php
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
class tx_cal_eventview extends tx_cal_base_view {
	public function tx_cal_eventview() {
		$this->tx_cal_base_view ();
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

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_eventview.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/view/class.tx_cal_eventview.php']);
}
?>