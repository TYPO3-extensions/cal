<?php
/***************************************************************
*  Copyright notice
*
*  (c)   2004-2005 Rupert Germann <rupi@gmx.li>
*  All   rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Class for updating tt_news content elements and category relations.
 *
 * @author  Rupert Germann <rupi@gmx.li>
 * @package TYPO3
 * @subpackage tt_news
 */
class ext_update {

	/**
	 * Main function, returning the HTML content of the module
	 *
	 * @return	string		HTML
	 */
	function main() {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		$testres = $GLOBALS['TYPO3_DB']->exec_SELECTquery ('*', 'tx_cal_event', '');
		// check only for events without calendar_id
		// TODO check if a category has multiple fe_users or fe_groups assigend to itself
		if ($testres && $GLOBALS['TYPO3_DB']->sql_num_rows($testres)) {
			$count_calendar_ids = $GLOBALS['TYPO3_DB']->sql_num_rows($testres);
		}
		
		$anonymousUserUid = t3lib_div::_POST('anonymoususeruid');
		$publicCalendar = t3lib_div::_POST('publiccalendar');

		if (!t3lib_div::_POST('do_update') || !is_numeric($anonymousUserUid)) {

			if ($count_calendar_ids) {
				$returnthis = '<b>There are found '.$count_calendar_ids.' event(s) to update.</b><br><br>';
			}

			$returnthis .= '<form action="" method="get">';
			$returnthis .= 'Insert the anonymousUserUid: <input name="anonymoususeruid" type="text" value="" size="5" maxlength="5" /><br />';
			$returnthis .= 'Insert title for public calendar: <input name="publiccalendar" type="text" value="" size="20" maxlength="50" />';
			$returnthis .= '<br><b>Do you want to perform the action now?</b><br>';
			$returnthis .= '<input type="hidden" name="id" value="0" />';
			$returnthis .= '<input type="hidden" name="CMD[showExt]" value="cal" />';
			$returnthis .= '<input type="hidden" name="SET[singleDetails]" value="updateModule" />';
			$returnthis .= '<input type="hidden" name="do_update" value="1" />';
			$returnthis .= 'Click to start the update<br /><button name="submit">Start update</button></form>';
			return $returnthis;
		} else {
			$query = array(
				'SELECT' => '*',
				'FROM' => 'tx_cal_event',
				'WHERE' => '',
				'GROUPBY' => '');
			$event_result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
			while($event = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($event_result)){
				$query = array(
					'SELECT' => '*',
					'FROM' => 'tx_cal_category',
					'WHERE' => 'tx_cal_category.uid = '.$event['category_id'],
					'GROUPBY' => '');
				$category_result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
				while($category = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($category_result)){
					$query = array(
						'SELECT' => 'fe_users.uid, fe_users.username',
						'FROM' => 'fe_users,tx_cal_fe_user_category_mm',
						'WHERE' => 'tx_cal_fe_user_category_mm.uid_local = '.$category['uid'].' AND tx_cal_fe_user_category_mm.uid_foreign = fe_users.uid AND tx_cal_fe_user_category_mm.tablenames="fe_users"',
						'GROUPBY' => '');
					$user_result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
					if($GLOBALS['TYPO3_DB']->sql_num_rows($user_result)==1){
						while($user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($user_result)){
//debug($user);
// TODO What are we doing here?						
//							if($user['uid']== $anonymousUserUid){
//								$user_id = 0;
//								$calendarTitle = $publicCalendar;
//							}else{
//								$user_id = $user['uid'];
//								$calendarTitle = $user['username']."`s Calendar";
//							}
//							$table = "tx_cal_category";
//							$where = "uid = ".$category['uid'];	
//							$updateFields = array("calendar_id" =>$user_id);
//debug($table);
//debug($where);
//debug($updateFields);
//							$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
						}
					}else{
						$query = array(
						'SELECT' => 'fe_groups.uid, fe_groups.title',
						'FROM' => 'fe_groups,tx_cal_fe_user_category_mm',
						'WHERE' => 'tx_cal_fe_user_category_mm.uid_local = '.$category['uid'].' AND tx_cal_fe_user_category_mm.uid_foreign = fe_groups.uid AND tx_cal_fe_user_category_mm.tablenames="fe_groups"',
						'GROUPBY' => '');
						$group_result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
						if($GLOBALS['TYPO3_DB']->sql_num_rows($group_result)==1){
							while($group = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($group_result)){
//debug($group);
						
								$table = "tx_cal_category";
								$where = "uid = ".$category['uid'];	
								$updateFields = array("fe_user_id" =>$group);
//debug($table);
//debug($where);
//debug($updateFields);
								$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
								
								$calendarTitle = $group['title']."` Calendar";
								$user_id = $group['uid'];
							}
						}
						
						$crdate = time();
						$insertFields['pid'] = $category['pid'];
						$insertFields['tstamp'] = $crdate;
						$insertFields['crdate'] = $crdate;
						$insertFields['cruser_id'] = $BE_USER->id;
						$insertFields['title'] = $calendarTitle;
						$insertFields['owner'] = $user_id;
						$insertFields['type'] = $category['type'];
						$insertFields['ext_url'] = $category['ext_url'];
						$insertFields['ics_files'] = $category['ics_files'];
						$table = "tx_cal_calendar";
//debug($table);
//debug($insertFields);
						$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($table,$insertFields);
						$calendar_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
						
						$table = "tx_cal_event";
						$where = "uid = ".$event['uid'];	
						$updateFields = array("category_id" =>$category['uid'], "calendar_id" => $calendar_uid);
//debug($table);
//debug($where);
//debug($updateFields);
						$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
						
						$table = "tx_cal_category";
						$where = "uid = ".$category['uid'];	
						$updateFields = array("calendar_id" =>$calendar_uid);
//debug($table);
//debug($where);
//debug($updateFields);
						$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
					}
				}
			}
		}
		return "Done. Please check you records";
	}

	/**
	 * Checks how many rows are found and returns true if there are any
	 * (this function is called from the extension manager)
	 *
	 * @param	string		$what: what should be updated
	 * @return	boolean
	 */
	function access($what = 'all') {
		if ($what = 'all') {
			if(is_object($GLOBALS['TYPO3_DB'])) {
				if (in_array('tx_cal_calendar', $GLOBALS['TYPO3_DB']->admin_get_tables ())) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_cal_calendar', '1=1');
					if ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
						return 0;
					}else{
						return 1;
					}
				}
			}
		}
	}


	/**
	 * Creates query finding all tt_news elements which has a category relation in tt_news table not replicated in tt_news_cat_mm
	 *
	 * @param	string		$updatewhat: determines which query should be returned
	 * @return	string		Full query
	 */
	function query($updatewhat) {
		if ($updatewhat == 'categoryrelations') {
			$query = array(
			'SELECT' => 'tt_news.uid,tt_news.category,tt_news_cat_mm.uid_foreign, max(tt_news.category = tt_news_cat_mm.uid_foreign) as testit',
				'FROM' => 'tt_news LEFT JOIN tt_news_cat_mm ON tt_news.uid = tt_news_cat_mm.uid_local',
				'WHERE' => '1=1 AND tt_news.category',
				'GROUPBY' => 'uid HAVING (testit !=1 OR ISNULL(testit))');
			return $query;
		}
//		} elseif($updatewhat == 'flexforms') {
//			$query = array(
//			'SELECT' => '*',
//				'FROM' => 'tt_content',
//				'WHERE' => 'CType="list" AND list_type="9" AND pi_flexform=""');
//
//			return $query;
//		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/class.ext_update.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/class.ext_update.php']);
}
?>
