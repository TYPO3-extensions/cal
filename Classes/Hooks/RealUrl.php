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
class RealUrl {
	
	/**
	 * Main hook function.
	 * Generates an entire RealURL configuration.
	 *
	 * @param
	 *        	array		Main parameters. Typically, 'config' is the
	 *        	existing RealURL configuration thas has been
	 *        	generated to this point and 'extKey' is unique
	 *        	that this hook used when it was registered.
	 */
	function addRealURLConfig(&$params, $parentObj) {
		$config = &$params ['config'];
		
		if (! is_array ( $config ['postVarSets'] ['_DEFAULT'] )) {
			$config ['postVarSets'] ['_DEFAULT'] = Array ();
		}
		$config ['postVarSets'] ['_DEFAULT'] = array_merge ( $config ['postVarSets'] ['_DEFAULT'], $this->addPostVarSets () );
		
		if (! is_array ( $config ['fileName'] ['index'] )) {
			$config ['fileName'] ['index'] = Array ();
		}
		$config ['fileName'] ['index'] = array_merge ( $config ['fileName'] ['index'], $this->addFilenameSet () );
		
		return $config;
	}
	
	function addFilenameSet() {
		$calendarRSS = Array ();
		$calendarRSS ['calendarRSS.xml'] = Array (
				'keyValues' => Array (
						'type' => 151 
				) 
		);
		$calendarRSS ['calendar.ics'] = Array (
				'keyValues' => Array (
						'type' => 150
				)
		);
		return $calendarRSS;
	}
	
	/**
	 * Adds the postVarSets (not specific to a page) to the RealURL config.
	 *
	 * @return array configuration element.
	 */
	function addPostVarSets() {
		$postVarSets = Array ();
		
		$postVarSets ['calendar'] = Array (
				$this->addValueMap ( 'tx_cal_controller[year]', Array (
						'2000' => '2000',
						'2001' => '2001',
						'2002' => '2002',
						'2003' => '2003',
						'2004' => '2004',
						'2005' => '2005',
						'2006' => '2006',
						'2007' => '2007',
						'2008' => '2008',
						'2009' => '2009',
						'2010' => '2010',
						'2011' => '2011',
						'2012' => '2012',
						'2013' => '2013',
						'2014' => '2014',
						'2015' => '2015',
						'2016' => '2016',
						'2017' => '2017',
						'2018' => '2018',
						'2019' => '2019',
						'2020' => '2020' 
				) ),
				$this->addValueMap ( 'tx_cal_controller[month]', Array (
						'01' => '01',
						'02' => '02',
						'03' => '03',
						'04' => '04',
						'05' => '05',
						'06' => '06',
						'07' => '07',
						'08' => '08',
						'09' => '09',
						'10' => '10',
						'11' => '11',
						'12' => '12' 
				) ),
				$this->addValueMap ( 'tx_cal_controller[day]', Array (
						'01' => '01',
						'02' => '02',
						'03' => '03',
						'04' => '04',
						'05' => '05',
						'06' => '06',
						'07' => '07',
						'08' => '08',
						'09' => '09',
						'10' => '10',
						'11' => '11',
						'12' => '12',
						'13' => '13',
						'14' => '14',
						'15' => '15',
						'16' => '16',
						'17' => '17',
						'18' => '18',
						'19' => '19',
						'20' => '20',
						'21' => '21',
						'22' => '22',
						'23' => '23',
						'24' => '24',
						'25' => '25',
						'26' => '26',
						'27' => '27',
						'28' => '28',
						'29' => '29',
						'30' => '30',
						'31' => '31' 
				) ),
				$this->addValueMap ( 'tx_cal_controller[view]', Array (
						'month' => 'month',
						'year' => 'year',
						'week' => 'week',
						'day' => 'day',
						'event' => 'event',
						'list' => 'list',
						'admin' => 'admin',
						'search_event' => 'search_event',
						'search_location' => 'search_location',
						'search_organizer' => 'search_organizer',
						'search_all' => 'search_all',
						'create_event' => 'create_event',
						'confirm_event' => 'confirm_event',
						'save_event' => 'save_event',
						'edit_event' => 'edit_event',
						'delete_event' => 'delete_event',
						'remove_event' => 'remove_event',
						'save_exception_event' => 'save_exception_event',
						'create_calendar' => 'create_calendar',
						'confirm_calendar' => 'confirm_calendar',
						'save_calendar' => 'save_calendar',
						'edit_calendar' => 'edit_calendar',
						'delete_calendar' => 'delete_calendar',
						'remove_calendar' => 'remove_calendar',
						'create_category' => 'create_category',
						'confirm_category' => 'confirm_category',
						'save_category' => 'save_category',
						'edit_category' => 'edit_category',
						'delete_category' => 'delete_category',
						'remove_category' => 'remove_category',
						'create_location' => 'create_location',
						'confirm_location' => 'confirm_location',
						'save_location' => 'save_location',
						'edit_location' => 'edit_location',
						'delete_location' => 'delete_location',
						'remove_location' => 'remove_location',
						'create_organizer' => 'create_organizer',
						'confirm_organizer' => 'confirm_organizer',
						'save_organizer' => 'save_organizer',
						'edit_organizer' => 'edit_organizer',
						'delete_organizer' => 'delete_organizer',
						'remove_organizer' => 'remove_organizer',
						'organizer' => 'organizer',
						'location' => 'location',
						'ics' => 'ics',
						'icslist' => 'icslist',
						'single_ics' => 'single_ics',
						'subscription' => 'subscription',
						'meeting' => 'meeting',
						'translation' => 'translation',
						'todo' => 'todo',
						'ajax' => 'ajax' 
				) ),
				$this->addValueMap ( 'tx_cal_controller[type]', Array (
						'tx_cal_phpicalendar' => 'tx_cal_phpicalendar',
						'tx_cal_organizer' => 'tx_cal_organizer',
						'tx_cal_location' => 'tx_cal_location',
						'tx_cal_calendar' => 'tx_cal_calendar',
						'tx_cal_category' => 'tx_cal_category',
						'sys_category' => 'sys_category',
						'tx_cal_attendee' => 'tx_cal_attendee',
						'tx_tt_address' => 'tx_tt_address',
						'tx_feuser' => 'tx_feuser',
						'tx_partner_main' => 'tx_feuser',
						'tx_cal_ts_service' => 'tx_cal_ts_service'
				) ),
				$this->addTable ( 'tx_cal_controller[uid]', 'tx_cal_event', 'title', 'tx_cal_phpicalendar' ),
				$this->addTable ( 'tx_cal_controller[uid]', 'tx_cal_organizer', 'name', 'tx_cal_organizer' ),
				$this->addTable ( 'tx_cal_controller[uid]', 'tx_cal_location', 'name', 'tx_cal_location' ) 
		);
		
		$postVarSets ['export'] = Array (
				0 => Array (
						'GETvar' => 'tx_cal_controller[calendar]',
						'lookUpTable' => Array (
								'table' => 'tx_cal_calendar',
								'id_field' => 'uid',
								'alias_field' => 'title',
								'addWhereClause' => ' AND NOT deleted',
								'useUniqueCache' => 1,
								'useUniqueCache_conf' => Array (
										'strtolower' => 1,
										'spaceCharacter' => '_' 
								) 
						) 
				) 
		);
		
		return $postVarSets;
	}
	
	/**
	 * ***********************************************************************
	 *
	 * Helper functions for generating common RealURL config elements.
	 *
	 * **********************************************************************
	 */
	
	/**
	 * Adds a RealURL config element for simple GET variables.
	 *
	 * array( 'GETvar' => 'tx_calendar_pi1[f1]' ),
	 *
	 * @param
	 *        	string		The GET variable.
	 * @return array config element.
	 */
	function addSimple($key) {
		return Array (
				'GETvar' => $key 
		);
	}
	
	/**
	 * Adds RealURL config element for table lookups.
	 *
	 * array(
	 * 'GETvar' => 'tx_ttnews[tt_news]',
	 * 'lookUpTable' => array(
	 * 'table' => 'tt_news',
	 * 'id_field' => 'uid',
	 * 'alias_field' => 'title',
	 * 'addWhereClause' => ' AND NOT deleted',
	 * 'useUniqueCache' => 1,
	 * 'useUniqueCache_conf' => array(
	 * 'strtolower' => 1,
	 * 'spaceCharacter' => '_',
	 * )
	 * )
	 * )
	 *
	 * @param
	 *        	string		The GET variable.
	 * @param
	 *        	string		The name of the table.
	 * @param
	 *        	string		The field in the table to be used in the URL.
	 * @param
	 *        	string		Previous GET variable that must be present for
	 *        	this rule to be evaluated.
	 * @return array config element.
	 */
	function addTable($key, $table, $aliasField = 'title', $condForPrevious = false, $where = ' AND NOT deleted') {
		$configArray = Array ();
		
		if ($condForPrevious) {
			$configArray ['cond'] = Array (
					'prevValueInList' => $condForPrevious 
			);
		}
		
		$configArray ['GETvar'] = $key;
		$configArray ['lookUpTable'] = Array (
				'table' => $table,
				'id_field' => 'uid',
				'alias_field' => $aliasField,
				'addWhereClause' => $where,
				'useUniqueCache' => 1,
				'useUniqueCache_conf' => Array (
						'strtolower' => 1,
						'spaceCharacter' => '_' 
				) 
		);
		
		return $configArray;
	}
	
	/**
	 * Adds RealURL config element for value map.
	 * array(
	 * 'GETvar' => 'sub',
	 * 'valueMap' => array(
	 * 'subscribe' => '1',
	 * 'unsubscribe' => '2',
	 * ),
	 * 'noMatch' => 'bypass',
	 * )
	 *
	 * @param
	 *        	string		The GET variable.
	 * @param
	 *        	array		Associative array with label and value.
	 * @param
	 *        	string		noMatch behavior.
	 * @return array config element.
	 */
	function addValueMap($key, $valueMapArray, $noMatch = 'bypass') {
		$configArray = Array ();
		$configArray ['GETvar'] = $key;
		
		if (is_array ( $valueMapArray )) {
			foreach ( $valueMapArray as $key => $value ) {
				$configArray ['valueMap'] [$key] = $value;
			}
		}
		
		$configArray ['noMatch'] = $noMatch;
		return $configArray;
	}
	
	/**
	 * Adds RealURL config element for single type.
	 *
	 * array(
	 * 'type' => 'single',
	 * 'keyValues' => array(
	 * 'tx_newloginbox_pi1[forgot]' => 1,
	 * )
	 * )
	 *
	 * @param
	 *        	array		Associative array of GET variables and values.
	 *        	All values must be matched.
	 * @return array config element.
	 */
	function addSingle($keyValueArray) {
		$configArray = Array ();
		$configArray ['type'] = 'single';
		
		if (is_array ( $keyValueArray )) {
			foreach ( $keyValueArray as $key => $value ) {
				$configArray ['keyValues'] [$key] = $value;
			}
		}
		
		return $configArray;
	}
}

?>