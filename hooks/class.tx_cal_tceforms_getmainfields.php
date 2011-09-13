<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2004 
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_tceforms_getmainfields {
	
	function getMainFields_preProcess($table,&$row,$this) {
		
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

			}
			
			/* If we have a calendar, set the category query to take this calendar into account */
			if($row['calendar_id']) {
				$TCA['tx_cal_event']['columns']['category_id']['config']['foreign_table_where'] = 'AND tx_cal_category.calendar_id IN ('.$row['calendar_id'].',0) ORDER BY tx_cal_category.title';
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tceforms_getmainfields.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tceforms_getmainfields.php']);
}
?>