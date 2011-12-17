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

/**
 * This hook extends the befunc class.
 * It changes the date values in the list view for tx_cal_event and tx_cal_exception_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */

class tx_cal_befunc {
	
	function preprocessvalue(&$conf) {
		if($conf['tx_cal_event']){
			unset($conf['eval']);
		}
	}
	
	function postprocessvalue(&$conf) {
		if($conf['colConf']['tx_cal_event']){
			$value = new tx_cal_date($conf['value'].'000000');
			if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] == '1'){
				$conf['value'] = $value->format('%d.%m.%Y');
			} else {
				$conf['value'] = $value->format('%d-%m-%Y');
			}
		}
		return $conf['value'];
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_befunc.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_befunc.php']);
}
?>