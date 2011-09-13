<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
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
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_tceforms_getmainfields {
	
	function getMainFields_preProcess($table,&$row, $tceform) {
		
		if($table == 'tx_cal_event') {
			global $TCA;
			t3lib_div::loadTCA('tx_cal_event');
			
			/* If the event is temporary, make it read only. */
			if($row['isTemp']) {
				$TCA['tx_cal_event']['ctrl']['readOnly'] = 1;
			}	
			/* If we have posted data and a new record, preset values to what they were on the previous record */
			if(is_array($GLOBALS['HTTP_POST_VARS']['data']['tx_cal_event']) && strstr($row['uid'], 'NEW')) {
				$eventPostData = array_pop($GLOBALS['HTTP_POST_VARS']['data']['tx_cal_event']);
				
				/* Set the calendar if there's not already a value set (from TSConfig) */
				if(!$row['calendar_id']) {
					$row['calendar_id'] = $eventPostData['calendar_id'];
				}
				
				/* Set the category if there's not already a value set (from TSConfig) */
				/*
				if(!$row['category_id']) {
					$categoriesArray = t3lib_div::trimExplode(',', $eventPostData['category_id'], 1);
					$categoryItemArray = array();					
					foreach($categoriesArray as $category) {
						$categoryRow = t3lib_befunc::getRecord('tx_cal_category', $category);
						$categoryItemArray[] = $categoryRow['uid'].'|'.t3lib_befunc::getRecordTitle('tx_cal_category', $categoryRow, 1);
					}

					$row['category_id'] = implode(',', $categoryItemArray);
				}
				*/

			}else if(!strstr($row['uid'], 'NEW')	){
				if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] == '1'){
					$format = '%m-%d-%Y';
				} else {
					$format = '%d-%m-%Y';
				}
				
				$row['start_date'] = $this->formatDate($row['start_date'], $format);
				$row['end_date'] = $this->formatDate($row['end_date'], $format);
				$row['until'] = $this->formatDate($row['until'], $format);
			}
			
			/* If we have a calendar, set the category query to take this calendar into account */
			if($row['calendar_id']) {
				$TCA['tx_cal_event']['columns']['category_id']['config']['foreign_table_where'] = 'AND tx_cal_category.calendar_id IN ('.$row['calendar_id'].',0) ORDER BY tx_cal_category.title';
			}
		}
		
		if($table == 'tx_cal_exception_event') {
			global $TCA;
			t3lib_div::loadTCA('tx_cal_exception_event');
			
			if(!strstr($row['uid'], 'NEW')	){
				if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] == '1'){
					$format = '%m-%d-%Y';
				} else {
					$format = '%d-%m-%Y';
				}
				
				$row['start_date'] = $this->formatDate($row['start_date'], $format);
				$row['end_date'] = $this->formatDate($row['end_date'], $format);
				$row['until'] = $this->formatDate($row['until'], $format);
			}
		}
	}
	
	function formatDate($ymdDate, $format) {
		if($ymdDate) {
			$dateObj = new tx_cal_date(intval($ymdDate).'000000');
			$dateObj->setTZbyId('UTC');
			$dateString = $dateObj->format($format);
		} else {
			$dateString = '';
		}
		
		return $dateString;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tceforms_getmainfields.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tceforms_getmainfields.php']);
}
?>