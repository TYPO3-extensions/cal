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


class tx_cal_realurl {

	/**
	 * Main hook function.  Generates an entire RealURL configuration.
	 *
	 * @param		array		Main parameters.  Typically, 'config' is the
	 *							existing RealURL configuration thas has been
	 *							generated to this point and 'extKey' is unique
	 *							that this hook used when it was registered.
	 */
	function addRealURLConfig(&$params, $parentObj) {
		$config = &$params['config'];
		$extKey = &$params['extKey'];

		if(!is_array($config['postVarSets']['_DEFAULT'])) {
			$config['postVarSets']['_DEFAULT'] = array();
		}
		$config['postVarSets']['_DEFAULT'] = array_merge($config['postVarSets']['_DEFAULT'], $this->addPostVarSets());
		
		if(!is_array($config['fileName']['index'])) {
			$config['fileName']['index'] = array();
		}
		$config['fileName']['index'] = array_merge($config['fileName']['index'], $this->addFilenameSet());

		return $config;
	}
	
	function addFilenameSet(){
		$calendarRSS = array();
		$calendarRSS['calendarRSS.xml'] = array('keyValues' => array('type' => 151));
		return $calendarRSS;
	}
	
	/**
	 * Adds the postVarSets (not specific to a page) to the RealURL config.
	 *
	 * @return		array		RealURL configuration element.
	 */
	function addPostVarSets() {
		$postVarSets = array();
		
		$postVarSets['calendar'] = array(
			$this->addValueMap('tx_cal_controller[year]', array(
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
			)),
			$this->addValueMap('tx_cal_controller[month]', array(
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
			)),
			$this->addValueMap('tx_cal_controller[day]', array(
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
			)),
			$this->addValueMap('tx_cal_controller[view]', array(
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
				'confirm_event' => 'confirm_event',
				'save_event' => 'save_event',
				'delete_event' => 'delete_event',
				'confirm_event' => 'confirm_event',
				'remove_event' => 'remove_event',
				'save_exception_event' => 'save_exception_event',
				'create_calendar' => 'create_calendar',
				'confirm_calendar' => 'confirm_calendar',
				'save_calendar' => 'save_calendar',
				'edit_calendar' => 'edit_calendar',
				'confirm_calendar' => 'confirm_calendar',
				'save_calendar' => 'save_calendar',
				'delete_calendar' => 'delete_calendar',
				'confirm_calendar' => 'confirm_calendar',
				'remove_calendar' => 'remove_calendar',
				'create_category' => 'create_category',
				'confirm_category' => 'confirm_category',
				'save_category' => 'save_category',
				'edit_category' => 'edit_category',
				'confirm_category' => 'confirm_category',
				'save_category' => 'save_category',
				'delete_category' => 'delete_category',
				'confirm_category' => 'confirm_category',
				'remove_category' => 'remove_category',
				'create_location' => 'create_location',
				'confirm_location' => 'confirm_location',
				'save_location' => 'save_location',
				'edit_location' => 'edit_location',
				'confirm_location' => 'confirm_location',
				'save_location' => 'save_location',
				'delete_location' => 'delete_location',
				'confirm_location' => 'confirm_location',
				'remove_location' => 'remove_location',
				'create_organizer' => 'create_organizer',
				'confirm_organizer' => 'confirm_organizer',
				'save_organizer' => 'save_organizer',
				'edit_organizer' => 'edit_organizer',
				'confirm_organizer' => 'confirm_organizer',
				'save_organizer' => 'save_organizer',
				'delete_organizer' => 'delete_organizer',
				'confirm_organizer' => 'confirm_organizer',
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
			)),
			$this->addValueMap('tx_cal_controller[type]', array(
				'tx_cal_phpicalendar' => 'tx_cal_phpicalendar',
				'tx_cal_organizer' => 'tx_cal_organizer',
				'tx_cal_location' => 'tx_cal_location',
				'tx_cal_calendar' => 'tx_cal_calendar',
				'tx_cal_category' => 'tx_cal_category',
				'tx_cal_attendee' => 'tx_cal_attendee',
				'tx_tt_address' => 'tx_tt_address',
				'tx_feuser' => 'tx_feuser',
				'tx_partner_main' => 'tx_feuser'
			)),
			$this->addTable('tx_cal_controller[uid]', 'tx_cal_event', 'title', 'tx_cal_phpicalendar'),
			$this->addTable('tx_cal_controller[uid]', 'tx_cal_organizer', 'name', 'tx_cal_organizer'),
			$this->addTable('tx_cal_controller[uid]', 'tx_cal_location', 'name', 'tx_cal_location')

		);

		return $postVarSets;
	}
	
	/*************************************************************************
	 *
	 * Helper functions for generating common RealURL config elements.
	 *
	 ************************************************************************/
	
	/**
	 * Adds a RealURL config element for simple GET variables.
	 *
	 *	array( 'GETvar' => 'tx_calendar_pi1[f1]' ),
	 *
	 * @param		string		The GET variable.
	 * @return		array		RealURL config element.
	 */	
	function addSimple($key) {
		return array( 'GETvar' => $key );
	}
	
	
	/**
	 * Adds RealURL config element for table lookups.
	 *
	 *	array(
	 *		'GETvar'      => 'tx_ttnews[tt_news]',
	 *		'lookUpTable' => array(
	 *			'table'               => 'tt_news',
	 *			'id_field'            => 'uid',
	 *			'alias_field'         => 'title',
	 *			'addWhereClause'      => ' AND NOT deleted',
	 *			'useUniqueCache'      => 1,
	 *			'useUniqueCache_conf' => array(
	 *				'strtolower'     => 1,
	 *				'spaceCharacter' => '_',
	 *			)						
	 *		)
	 *	)
	 *
	 * @param		string		The GET variable.
	 * @param		string		The name of the table.
	 * @param		string		The field in the table to be used in the URL.
	 * @param		string		Previous GET variable that must be present for
	 *							this rule to be evaluated.
	 * @return		array		RealURL config element.
	 */
	function addTable($key, $table, $aliasField='title', $condForPrevious=false, $where=' AND NOT deleted') {
		$configArray = array();
		
		if($condForPrevious) {
			$configArray['cond'] = array ('prevValueInList' => $condForPrevious);
		}
		
		$configArray['GETvar'] = $key;
		$configArray['lookUpTable'] = array(
			'table' => $table,
			'id_field' => 'uid',
			'alias_field' => $aliasField,
			'addWhereClause' => $where,
			'useUniqueCache' => 1,
			'useUniqueCache_conf' => array(
				'strtolower' => 1,
				'spaceCharacter' => '_',
			),
		);
		
		return $configArray;
	}
	
	/**
	 * Adds RealURL config element for value map.
	 *	array(
	 *		'GETvar' => 'sub',
	 *		'valueMap' => array(
	 *			'subscribe' => '1',
	 *			'unsubscribe' => '2',
	 *		),
	 *		'noMatch' => 'bypass',
	 *	)
	 *
	 * @param		string		The GET variable.
	 * @param		array		Associative array with label and value.
	 * @param		string		noMatch behavior.
	 * @return		array		RealURL config element.
	 */
	function addValueMap($key, $valueMapArray, $noMatch='bypass') {
		$configArray = array();
		$configArray['GETvar'] = $key;
		
		if(is_array($valueMapArray)) {
			foreach($valueMapArray as $key => $value) {
				$configArray['valueMap'][$key] = $value;
			}
		}
		
		$configArray['noMatch'] = $noMatch;
		return $configArray;
	}
	
	/**
	 * Adds RealURL config element for single type.
	 *
	 *	array(
	 *		'type' => 'single',
	 *		'keyValues' => array(
	 *			'tx_newloginbox_pi1[forgot]' => 1,
	 *		)
	 *	)
	 *
	 * @param		array		Associative array of GET variables and values. 
	 *							All values must be matched.
	 * @return		array		RealURL config element.
	 */
	function addSingle($keyValueArray) {
		$configArray = array();
		$configArray['type'] = 'single';
		
		if(is_array($keyValueArray)) {
			foreach($keyValueArray as $key => $value) {
				$configArray['keyValues'][$key] = $value;
			}
		}
		
		return $configArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_realurl.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_realurl.php']);
}
?>