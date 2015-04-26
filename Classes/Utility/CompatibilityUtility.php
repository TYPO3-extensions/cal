<?php
namespace TYPO3\CMS\Cal\Utility;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use \TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Move deprecated function calls to this class if they can't be removed
 */
class CompatibilityUtility {

	/**
	 * Deprecation Log:
	 * TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule()
	 * [!!!] Since 6.2 use ArrayUtility::mergeRecursiveWithOverrule
	 * WARNING: The new method changed its signature and does not return the first parameter anymore, but it is more performant.
	 *
	 * Can be removed if TYPO3 6.2 or higher is minimum requirement for cal
	 * and replace \TYPO3\CMS\Cal\Utility\CompatibilityUtility::mergeRecursiveWithOverrule
	 * with \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule
	 *
	 * @param array $original Original array. It will be *modified* by this method and contains the result afterwards!
	 * @param array $overrule Overrule array, overruling the original array
	 * @return void
	 */
	function mergeRecursiveWithOverrule(array &$original, array $overrule) {
		if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= '6002000') {
			\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($original, $overrule);
		} else {
			$original = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge_recursive_overrule($original, $overrule);
		}
	}
}