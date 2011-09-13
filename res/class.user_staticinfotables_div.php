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

require_once(t3lib_extMgm::extPath('static_info_tables').'class.tx_staticinfotables_div.php');

class user_staticinfotables_div extends tx_staticinfotables_div {

	/**
	 * Function to use in own TCA definitions
	 * Adds additional select items
	 *
	 * @param	array		itemsProcFunc data array
	 * @return	void
	 */
	function selectItemsTCA($params) {
		global $TCA;
/*
		$params['items'] = &$items;
		$params['config'] = $config;
		$params['TSconfig'] = $iArray;
		$params['table'] = $table;
		$params['row'] = $row;
		$params['field'] = $field;
*/
		$table = $params['config']['itemsProcFunc_config']['table'];
		$tcaWhere = $params['config']['itemsProcFunc_config']['where'];
		$where = user_staticinfotables_div::replaceMarkersInSQL($tcaWhere, $params['table'], $params['row']);
		
		if ($table) {
			$indexField = $params['config']['itemsProcFunc_config']['indexField'];
			$indexField = $indexField ? $indexField : 'uid';

			$lang = strtolower(tx_staticinfotables_div::getCurrentLanguage());
			$titleFields = tx_staticinfotables_div::getTCAlabelField($table, TRUE, $lang);
			$prefixedTitleFields = array();
			foreach ($titleFields as $titleField) {
				$prefixedTitleFields[] = $table.'.'.$titleField;
			}
			$fields = $table.'.'.$indexField.','.implode(',', $prefixedTitleFields);

			if ($params['config']['itemsProcFunc_config']['prependHotlist']) {

				$limit = $params['config']['itemsProcFunc_config']['hotlistLimit'];
				$limit = $limit ? $limit : '8';

				$app = $params['config']['itemsProcFunc_config']['hotlistApp'];
				$app = $app ? $app : TYPO3_MODE;

				$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
						$fields,
						$table,
						'tx_staticinfotables_hotlist',
						'',	// $foreign_table
						'AND tx_staticinfotables_hotlist.application='.$GLOBALS['TYPO3_DB']->fullQuoteStr($app,'tx_staticinfotables_hotlist'),
						'',
						'tx_staticinfotables_hotlist.sorting DESC',	// $orderBy
						$limit
					);

				$cnt = 0;
				$rows = array();
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
					#$params['items'][] = array($row[$titleField], $row[$indexField], '');
					foreach ($titleFields as $titleField) {
						if ($row[$titleField]) {
							$rows[$row[$indexField]] = $row[$titleField];
							break;
						}
					}
					$cnt++;
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($res);

				if (!isset($params['config']['itemsProcFunc_config']['hotlistSort']) OR $params['config']['itemsProcFunc_config']['hotlistSort']) {
					asort ($rows);
				}

				foreach ($rows as $index => $title)	{
					$params['items'][] = array($title, $index, '');
					$cnt++;
				}
				if($cnt && !$params['config']['itemsProcFunc_config']['hotlistOnly']) {
					$params['items'][] = array('--------------', '', '');
				}
			}

				// Set ORDER BY:
			$orderBy = $titleFields[0];

			if(!$params['config']['itemsProcFunc_config']['hotlistOnly']) {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, '1'.t3lib_BEfunc::deleteClause($table).$where, '', $orderBy);
				if($res) {
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						foreach ($titleFields as $titleField) {
							if ($row[$titleField]) {
								$params['items'][] = array($row[$titleField], $row[$indexField], '');
								break;
							}
						}
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($res);
				}
			}
		}
	}
	
	/*
	 * Replaces any dynamic markers in a SQL statement.
	 *
	 * @param	string		The SQL statement with dynamic markers.
	 * @param	string		Name of the table.
	 * @param	array		Database row.
	 * @return	string		SQL query with dynamic markers subsituted.
	 */
	function replaceMarkersInSQL($sql, $table, $row)	{
		$TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig($table, $row);
		
		/* Replace references to specific fields with value of that field */
		if (strstr($sql,'###REC_FIELD_'))	{
			$sql_parts = explode('###REC_FIELD_',$sql);
			while(list($kk,$vv)=each($sql_parts))	{
				if ($kk)	{
					$sql_subpart = explode('###',$vv,2);
					$sql_parts[$kk]=$TSconfig['_THIS_ROW'][$sql_subpart[0]].$sql_subpart[1];
				}
			}
			$sql = implode('',$sql_parts);
		}
+
		/* Replace markers with TSConfig values */
		$sql = str_replace('###CURRENT_PID###',intval($TSconfig['_CURRENT_PID']),$sql);
		$sql = str_replace('###THIS_UID###',intval($TSconfig['_THIS_UID']),$sql);
		$sql = str_replace('###THIS_CID###',intval($TSconfig['_THIS_CID']),$sql);
		$sql = str_replace('###STORAGE_PID###',intval($TSconfig['_STORAGE_PID']),$sql);
		$sql = str_replace('###SITEROOT###',intval($TSconfig['_SITEROOT']),$sql);
		$sql = str_replace('###PAGE_TSCONFIG_ID###',intval($TSconfig[$field]['PAGE_TSCONFIG_ID']),$sql);
		$sql = str_replace('###PAGE_TSCONFIG_IDLIST###',$GLOBALS['TYPO3_DB']->cleanIntList($TSconfig[$field]['PAGE_TSCONFIG_IDLIST']),$sql);
		$sql = str_replace('###PAGE_TSCONFIG_STR###',$GLOBALS['TYPO3_DB']->quoteStr($TSconfig[$field]['PAGE_TSCONFIG_STR'], $foreign_table),$sql);
		
		return $sql;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.user_staticinfotables_div.php'])    {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.user_staticinfotables_div.php']);
}
	
?>