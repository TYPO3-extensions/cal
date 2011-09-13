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

		return $config;
	}
	
	/**
	 * Adds the postVarSets (not specific to a page) to the RealURL config.
	 *
	 * @return		array		RealURL configuration element.
	 */
	function addPostVarSets() {
		$postVarSets = array();
		
		$postVarSets['cal'] = array(
			$this->addSimple('tx_cal_controller[view]'),
			$this->addSimple('tx_cal_controller[getdate]'),
			$this->addSimple('tx_cal_controller[lastview]'),
			$this->addSimple('tx_cal_controller[type]'),
			$this->addTable('tx_cal_controller[category]', 'tx_cal_category'),
			$this->addTable('tx_cal_controller[uid]', 'tx_cal_event')
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
			'userUniqueCache_conf' => array(
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