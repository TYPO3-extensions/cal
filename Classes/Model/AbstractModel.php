<?php
namespace TYPO3\CMS\Cal\Model;
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

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class AbstractModel {
	var $noAutoFetchMethods = Array (); // array with method names as array values, where the method has the naming scheme 'getCustomMethodName', where a setter with the same naming and where the get-method itself expects parameters and thus can not be fetched dynamically
	function getNoAutoFetchMethods() {
		return $this->noAutoFetchMethods;
	}
}

?>