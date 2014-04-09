<?php
/**
 * *************************************************************
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
 * *************************************************************
 */
class tx_cal_registry {
	
	/**
	 * Usage:
	 * $myfoo = & Registry('MySpace', 'Foo');
	 * $myfoo = 'something';
	 *
	 * $mybar = & Registry('MySpace', 'Bar');
	 * $mybar = new Something();
	 *
	 * @param string $namespace
	 *        	A namespace to prevent clashes
	 * @param string $var
	 *        	The variable to retrieve.
	 * @return mixed A reference to the variable. If not set it will be null.
	 */
	static function &Registry($namespace, $var) {
		static $instances = array ();
		// remove to get case-insensitive namespace
		$namespace = strtolower ($namespace);
		$var = strtolower ($var);
		return $instances [$namespace] [$var];
	}
	static function setInstance(&$object, $namespace, $var) {
		$myObject = &tx_publication_registry::Registry ($namespace, $var);
		$myObject = $object;
		$object = $myObject;
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/controller/class.tx_cal_registry.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/controller/class.tx_cal_registry.php']);
}
?>