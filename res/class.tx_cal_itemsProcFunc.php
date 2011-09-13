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

class tx_cal_itemsProcFunc {
	
	/**
	 * Gets the items array of all available translations.
	 * @param	array		The current config array.
	 * @return	array
	 *
	 * @todo 	Localize translation names.  Probably not too critical since
	 *			they're mostly English anyway but its easy to do.
	 *
	 */
	function getDayTimes($config) {
		$interval = 60 * 30;
		$dayLength = 60 * 60 * 24;
		for($time=0; $time < $dayLength; $time+=$interval ) {
			// gmdate is ok, as long as $time just holds information about 24h.
			$label = gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $time);
			$value = gmdate('Hi', $time);
			$config['items'][] = Array($label, $value);
 		}
		
		return $config;
	}
	
	/**
	 * Gets the listing of users and groups.
	 * @param		array		The current config array.
	 * @return		array
	 */
	function getUsersAndGroups($config) {
		/* Add frontend groups */
		$table = 'fe_groups';
		$where = '1=1 '.t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);
		$res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where, '', 'title');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$label = t3lib_BEfunc::getRecordTitle($table, $row);
			$value = -1 * intval($row['uid']);
			$config['items'][] = Array($label, $value);
		}
		
		/* Add a divider */
		$config['items'][] = Array('------', '--div--');

		/* Add frontend users */
		$table = 'fe_users';
		$where = '1=1 '.t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table);		
		$res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where, '', 'name');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$label = t3lib_BEfunc::getRecordTitle($table, $row);
			$value = $row['uid'];
			$config['items'][] = Array($label, $value);
		}
		
		return $config;
	}
	
	/**
	 * General purpose function for fetching records from a given table using a combination of backend access control
	 * settings and User TSConfig options. Records are added to then added to the items array.
	 *
	 * @param		array		Associate array with keys 'items', 'config', 'TSconfig', 'table', 'row', and 'field'.
	 * @return		none
	 */
	function getRecords(&$params) {
		$table = $params['config']['itemsProcFunc_config']['table'];
		$where = $params['config']['itemsProcFunc_config']['where'];
		$groupBy = $params['config']['itemsProcFunc_config']['groupBy'];
		$orderBy = $params['config']['itemsProcFunc_config']['orderBy'];
		$limit = $params['config']['itemsProcFunc_config']['limit'];
		
		/* Get the records, with access restrictions and all that good stuff applied. */
		$res = tx_cal_itemsProcFunc::getSQLResource($table, $where, $groupBy, $orderBy, $limit);
		
		/* Loop over all records, adding them to the items array */
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$label = t3lib_BEfunc::getRecordTitle($table, $row);
			$value = $row['uid'];
			$params['items'][] = array($label, $value);
		}
	}
	
	
	/**
	 * General purpose function for fetching records from a given table using a combination of backend access control
	 * settings and User TSConfig options. A SQL resource is returned.
	 * exec_SELECTquery($select_fields,$from_table,$where_clause,$groupBy='',$orderBy='',$limit='')
	 *
	 * @param		string		Name of the table.
	 * @param		string		Custom WHERE clause.
	 * @param		string		GROUP BY options.
	 * @param		string 		ORDER BY options.
	 * @param		string		LIMIT options.
	 * @return		object		SQL resource.
	 */
	function getSQLResource($table, $where='', $groupBy='', $orderBy='', $limit='') {
		/* Initialize the variables and config options */
		$be_userCategories = array(0);
		$be_userCalendars = array(0);
		$enableAccessControl = false;
		$accessControlWhere = '';
		$languageWhere = '';
		$limitViewOnlyToPidsWhere = '';
		
		/* If we're grabbing calendar or category records, check access control settings */
		if($table == 'tx_cal_calendar' or $table == 'tx_cal_category') {
			
			/* If we have a non-admin backend user, check access control settings */
			if(is_object($GLOBALS['BE_USER']) && !$GLOBALS['BE_USER']->user['admin']) {
				
				/* Get access control settings for the user */
				if($GLOBALS['BE_USER']->user['tx_cal_enable_accesscontroll']) {
					$enableAccessControl = true;
					$be_userCategories = t3lib_div::trimExplode(',',$GLOBALS['BE_USER']->user['tx_cal_category'],1);
					$be_userCalendars = t3lib_div::trimExplode(',',$GLOBALS['BE_USER']->user['tx_cal_calendar'],1);
				}
				
				/* Get access control settings for all groups */
				if(is_array($GLOBALS['BE_USER']->userGroups)) {
					foreach ($GLOBALS['BE_USER']->userGroups as $gid => $group) {
						if($group['tx_cal_enable_accesscontroll']){
							$enableAccessControl = true;
							if ($group['tx_cal_category']) {
								$groupCategories = t3lib_div::trimExplode(',',$group['tx_cal_category'],1);
								$be_userCategories = array_merge($be_userCategories,$groupCategories);
							}
							if ($group['tx_cal_calendar']) {
								$groupCalendars = t3lib_div::trimExplode(',',$group['tx_cal_calendar'],1);
								$be_userCalendars = array_merge($be_userCalendars,$groupCalendars);
							}
						}
					}
				}
				
				/* If access control was enabled for the user or groups, add a WHERE clause */
				if($enableAccessControl){
					$accessControlWhere = ' AND tx_cal_calendar.uid IN ('.implode(',',$be_userCalendars).')';
				}
			}
		}
		
		
		/* Check TSConfig settings */
		$GLOBALS['BE_USER']->fetchGroupData();
		$pids = $GLOBALS['BE_USER']->userTS['options.']['tx_cal_controller.']['limitViewOnlyToPids'];
		if($pids != ''){
			$limitViewOnlyToPidsWhere = ' AND '.$table.'.pid IN ('.$pids.')';
		}
		
		/* If a languageField is available for the table, use it */
		if(array_key_exists('languageField', (array)$GLOBALS['TCA'][$table]['ctrl'])) {
			$languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
			$languageWhere = ' AND '.$table.'.'.$languageField.' IN (-1,0)';
		}
		
		
		/* Construct the query */
		$where = '1=1 '.t3lib_BEfunc::BEenableFields($table).t3lib_BEfunc::deleteClause($table).$limitViewOnlyToPidsWhere.$accessControlWhere.$languageWhere.$where;
		$res = $GLOBALS['TYPO3_DB']->exec_selectQuery('*', $table, $where, $groupBy, $orderBy, $limit);
		
		return $res;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_itemsProcFunc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_itemsProcFunc.php']);
}
?>