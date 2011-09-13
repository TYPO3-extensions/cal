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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_itemsProcFunc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_itemsProcFunc.php']);
}
?>