<?php
/***************************************************************
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
 ***************************************************************/

require_once (t3lib_extMgm :: extPath('cal') . 'model/class.tx_cal_phpicalendar_model.php');

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_phpicalendar_rec_deviation_model extends tx_cal_phpicalendar_model {

	function tx_cal_phpicalendar_rec_deviation_model($event, $row, $start, $end) {
		
		$this->tx_cal_model($event->serviceKey);
		unset($row['uid']);
		unset($row['pid']);
		unset($row['parentid']);
		unset($row['tstamp']);
		unset($row['crdate']);
		unset($row['cruser_id']);
		unset($row['deleted']);
		unset($row['hidden']);
		unset($row['starttime']);
		unset($row['endtime']);
		$row = array_merge($event->row,array_filter($row));	
		$this->createEvent($row, false);
		
		$this->setStart($start);
		$this->setEnd($end);
	}
	
	function getRRuleMarker(&$template, &$sims, &$rems, &$wrapped, $view ) {
		$sims['###RRULE###'] = 'RECURRENCE-ID:'.$this->getStart()->format('%Y%m%dT%H%M%SZ');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_rec_deviation_model.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_phpicalendar_rec_deviation_model.php']);
}
?>