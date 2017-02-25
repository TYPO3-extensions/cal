<?php
namespace TYPO3\CMS\Cal\Backend\TCA;
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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 *
 * @author Steffen Kamper <info(at)sk-typo3.de>
 */
class Labels {
	function getEventRecordLabel(&$params, &$pObj) {
		if ($params ['table'] != 'tx_cal_event' && $params ['table'] != 'tx_cal_exception_event')
			return '';
			
			// Get complete record
		$rec = BackendUtility::getRecordWSOL ($params ['table'], $params ['row'] ['uid']);
		$dateObj = new \TYPO3\CMS\Cal\Model\CalDate ($rec ['start_date'] . '000000');
		$dateObj->setTZbyId ('UTC');
		
		$format = str_replace (array (
				'd',
				'm',
				'y',
				'Y' 
		), array (
				'%d',
				'%m',
				'%y',
				'%Y' 
		), $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['ddmmyy']);
		if ($rec ['allday'] || $params ['table'] == 'tx_cal_exception_event') {
			/* If we have an all day event, only show the date */
			$datetime = $dateObj->format ($format);
			$params ['start_date'] = $datetime;
		} else {
			/* For normal events, show both the date and time */
			// gmdate is ok, as long as $rec['start_time'] just holds information about 24h.
			$datetime = $dateObj->format ($format);
			
			$params ['start_date'] = $datetime;
			
			$extConf = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
			if ($extConf ['showTimes'] == 1) {
				$datetime .= ' ' . gmdate ($GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['hhmm'], $rec ['start_time']);
			}
		}
		// Assemble the label
		$label = $datetime . ': ' . $rec ['title'];
		
		// Write to the label
		$params ['title'] = $label;
	}
	function getAttendeeRecordLabel(&$params, &$pObj) {
		if (! $params ['table'] == 'tx_cal_attendee')
			return '';
			
			// Get complete record
		$rec = BackendUtility::getRecord ($params ['table'], $params ['row'] ['uid']);
		
		$label = $rec ['email'];
		if ($rec ['fe_user_id']) {
			$feUserRec = BackendUtility::getRecord ('fe_users', $rec ['fe_user_id']);
			$label = $feUserRec ['name'] != '' ? $feUserRec ['name'] : $feUserRec ['username'];
		}
		$label .= ' (' . $GLOBALS ['LANG']->sl ('LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.attendance.' . $rec ['attendance']) . ' -> ' . $rec ['status'] . ')';
		
		// Write to the label
		$params ['title'] = $label;
	}
	function getMonitoringRecordLabel(&$params, &$pObj) {
		if (! $params ['table'] == 'tx_cal_fe_user_event_monitor_mm')
			return '';
			
			// Get complete record
		$rec = BackendUtility::getRecord ($params ['table'], $params ['row'] ['uid']);
		
		$label = '';
		switch ($rec ['tablenames']) {
			case 'fe_users' :
				$feUserRec = BackendUtility::getRecord ('fe_users', $rec ['uid_foreign']);
				$label = $feUserRec ['name'] != '' ? $feUserRec ['name'] : $feUserRec ['username'];
				break;
			case 'fe_groups' :
				$feUserRec = BackendUtility::getRecord ('fe_groups', $rec ['uid_foreign']);
				$label = $feUserRec ['title'];
				break;
			case 'tx_cal_unknown_users' :
				$feUserRec = BackendUtility::getRecord ('tx_cal_unknown_users', $rec ['uid_foreign']);
				$label = $feUserRec ['email'];
				break;
		}
		
		// Write to the label
		$params ['title'] = $label . ' (' . $GLOBALS ['LANG']->sl ('LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_fe_user_event.offset') . ': ' . $rec ['offset'] . ')';
	}
	function getDeviationRecordLabel(&$params, &$pObj) {
		if (! $params ['table'] == 'tx_cal_event_deviation')
			return '';
			
			// Get complete record
		$rec = BackendUtility::getRecord ($params ['table'], $params ['row'] ['uid']);
		
		$label = $GLOBALS ['LANG']->sl ('LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.deviation') . ': ';
		
		if ($rec ['orig_start_date']) {
			
			$dateObj = new \TYPO3\CMS\Cal\Model\CalDate ($rec ['orig_start_date'] . '000000');
			$dateObj->setTZbyId ('UTC');
			
			$format = str_replace (array (
					'd',
					'm',
					'y',
					'Y'
			), array (
					'%d',
					'%m',
					'%y',
					'%Y'
			), $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['ddmmyy']);
			
			$datetime = $dateObj->format ($format);
			$label .= $datetime;
		}
		
		if ($rec ['orig_start_time']) {
			$label .= ' (' . gmdate ($GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['hhmm'], $rec ['orig_start_time']) . ')';
		}
		
		// Write to the label
		$params ['title'] = $label;
	}
}

?>