<?php
namespace TYPO3\CMS\Cal\Utility;
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
class Registry {
	
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
	public static function &Registry($namespace, $var) {
		static $instances = Array ();
		// remove to get case-insensitive namespace
		$namespace = strtolower ($namespace);
		$var = strtolower ($var);
		return $instances [$namespace] [$var];
	}
}

?>