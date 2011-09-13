<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Steffen Kamper
 * (c) 2005-2007 Foundation for Evangelism
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
 *
 * @author Steffen Kamper <info(at)sk-typo3.de>
 */

require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
require_once(t3lib_extMgm::extPath('cal').'res/pearLoader.php');

class tx_cal_labels {
		
	function getEventRecordLabel(&$params, &$pObj)	{
		
        if ($params['table'] != 'tx_cal_event' && $params['table'] != 'tx_cal_exception_event') return '';
		
		// Get complete record 
		$rec = t3lib_BEfunc::getRecord($params['table'], $params['row']['uid']);
		$dateObj = new tx_cal_date($rec['start_date'].'000000');
		$dateObj->setTZbyId('UTC');

		$time = $rec['start_time'];
		$format = str_replace(array('d','m','y','Y'),array('%d','%m','%y','%Y'),$GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy']);
		if($rec['allday'] || $params['table'] == 'tx_cal_exception_event') {
			/* If we have an all day event, only show the date */
			$datetime = $dateObj->format($format);
		} else {
			/* For normal events, show both the date and time */
			// gmdate is ok, as long as $rec['start_time'] just holds information about 24h.
			$datetime = $dateObj->format($format).' '.gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $rec['start_time']);
		}
		// Assemble the label
		$label = $datetime.': '.$rec['title'];

        //Write to the label
        $params['title'] =  $label;
	}
	
	function getAttendeeRecordLabel(&$params, &$pObj)	{
		
        if (!$params['table'] == 'tx_cal_attendee') return '';
		
		// Get complete record 
		$rec = t3lib_BEfunc::getRecord($params['table'], $params['row']['uid']);

		$label = $rec['email'];
		if($rec['fe_user_id']){
			$feUserRec = t3lib_BEfunc::getRecord('fe_users', $rec['fe_user_id']);
			$label = $feUserRec['name']!=''?$feUserRec['name']:$feUserRec['username'];
		}
		$label .= ' ('.$GLOBALS['LANG']->sl('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.attendance.'.$rec['attendance']).')';

        //Write to the label
        $params['title'] =  $label;
	}
		
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_labels.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_labels.php']);
}  
  
?>
