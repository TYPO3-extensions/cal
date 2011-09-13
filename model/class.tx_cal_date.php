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

require_once(t3lib_extMgm::extPath('cal').'res/pearLoader.php');
/**
 * Extends the PEAR date class and adds a compareTo method.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_date extends Date {
 	
	/**
	 * Compare function.
	 * @return int	-1,0,1 => less, equals, greater
	 */
	function compareTo($object){
		if(is_subclass_of($object, 'Date')){
			return $this->compare($this,$object);
		}
		return -1;
	}
	
	# @override
	function equals($compareDate){
		$a = doubleval($compareDate->format('%Y%m%d%H%M%S'));
		$b = doubleval($this->format('%Y%m%d%H%M%S'));
		if($a==$b){
			return true;
		}
		return false;
	}
	
	# @override
	function before($compareDate){
		$a = doubleval($compareDate->format('%Y%m%d%H%M%S'));
		$b = doubleval($this->format('%Y%m%d%H%M%S'));
		if($a>$b){
			return true;
		}
		return false;
	}
	
	# @override
	function after($compareDate){
		$a = doubleval($compareDate->format('%Y%m%d%H%M%S'));
		$b = doubleval($this->format('%Y%m%d%H%M%S'));
		if($a<$b){
			return true;
		}
		return false;
	}
	
	# @override
	function compare($compareDateA, $compareDateB){
		$a = doubleval($compareDateA->format('%Y%m%d%H%M%S'));
		$b = doubleval($compareDateB->format('%Y%m%d%H%M%S'));
		if($a==$b){
			return 0;
		}
		if($a<$b){
			return -1;
		}
		return 1;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_date.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_date.php']);
}
?>