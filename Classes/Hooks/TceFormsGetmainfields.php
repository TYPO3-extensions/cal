<?php
namespace TYPO3\CMS\Cal\Hooks;
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
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class TceFormsGetmainfields {
	function getMainFields_preProcess($table, &$row, $tceform) {
		if ($table == 'tx_cal_event') {
			
			/* If the event is temporary, make it read only. */
			if ($row ['isTemp']) {
				$GLOBALS ['TCA'] ['tx_cal_event'] ['ctrl'] ['readOnly'] = 1;
			}
			/* If we have posted data and a new record, preset values to what they were on the previous record */
			if (is_array ($GLOBALS ['HTTP_POST_VARS'] ['data'] ['tx_cal_event']) && strstr ($row ['uid'], 'NEW')) {
				$eventPostData = array_pop ($GLOBALS ['HTTP_POST_VARS'] ['data'] ['tx_cal_event']);
				
				/* Set the calendar if there's not already a value set (from TSConfig) */
				if (! $row ['calendar_id']) {
					$row ['calendar_id'] = $eventPostData ['calendar_id'];
				}
				
				/* Set the category if there's not already a value set (from TSConfig) */
				/*
				if(!$row['category_id']) {
					$categoriesArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $eventPostData['category_id'], 1);
					$categoryItemArray = array();					
					foreach($categoriesArray as $category) {
						$categoryRow = BackendUtility::getRecord('tx_cal_category', $category);
						$categoryItemArray[] = $categoryRow['uid'].'|'.BackendUtility::getRecordTitle('tx_cal_category', $categoryRow, 1);
					}

					$row['category_id'] = implode(',', $categoryItemArray);
				}
				*/

			} else if (! strstr ($row ['uid'], 'NEW')) {
				if ($GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['USdateFormat'] == '1') {
					$format = '%m-%d-%Y';
				} else {
					$format = '%d-%m-%Y';
				}
				
				$row ['start_date'] = $this->formatDate ($row ['start_date'], $format);
				$row ['end_date'] = $this->formatDate ($row ['end_date'], $format);
				$row ['until'] = $this->formatDate ($row ['until'], $format);
			}
			
			/* If we have a calendar, set the category query to take this calendar into account */
			if ($row ['calendar_id']) {
				$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
				if($confArr ['categoryService'] == 'tx_cal_category') {
					$GLOBALS ['TCA'] ['tx_cal_event'] ['columns'] ['category_id'] ['config'] ['foreign_table_where'] = 'AND tx_cal_category.calendar_id IN (' . $row ['calendar_id'] . ',0) ORDER BY tx_cal_category.title';
				}
			}
		}
		
		if ($table == 'tx_cal_exception_event') {
			
			if (! strstr ($row ['uid'], 'NEW')) {
				if ($GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['USdateFormat'] == '1') {
					$format = '%m-%d-%Y';
				} else {
					$format = '%d-%m-%Y';
				}
				
				$row ['start_date'] = $this->formatDate ($row ['start_date'], $format);
				$row ['end_date'] = $this->formatDate ($row ['end_date'], $format);
				$row ['until'] = $this->formatDate ($row ['until'], $format);
			}
		}
		
		if ($table == 'tx_cal_fe_user_event_monitor_mm') {
			$rec = BackendUtility::getRecord ($table, $row ['uid']);
			
			$label = '';
			switch ($row ['tablenames']) {
				case 'fe_users' :
					$feUserRec = BackendUtility::getRecord ('fe_users', $rec ['uid_foreign']);
					$row ['uid_foreign'] = $row ['tablenames'] . '_' . $feUserRec ['uid'] . '|' . $feUserRec ['username'];
					break;
				case 'fe_groups' :
					$feUserRec = BackendUtility::getRecord ('fe_groups', $rec ['uid_foreign']);
					$row ['uid_foreign'] = $row ['tablenames'] . '_' . $feUserRec ['uid'] . '|' . $feUserRec ['title'];
					break;
				case 'tx_cal_unknown_users' :
					$feUserRec = BackendUtility::getRecord ('tx_cal_unknown_users', $rec ['uid_foreign']);
					$row ['uid_foreign'] = $row ['tablenames'] . '_' . $feUserRec ['uid'] . '|' . $feUserRec ['email'];
					break;
			}
		}
		
		if ($table == 'tx_cal_attendee') {
			$row ['fe_group_id'] = '';
		}
	}
	function formatDate($ymdDate, $format) {
		if ($ymdDate) {
			$dateObj = new \TYPO3\CMS\Cal\Model\CalDate (intval ($ymdDate) . '000000');
			$dateObj->setTZbyId ('UTC');
			return $dateObj->getTime ();
		} else {
			$dateString = '';
		}
		
		return $dateString;
	}
}

?>