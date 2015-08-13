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

if (! class_exists ('Date', FALSE)) {
	if (! defined ('PATH_SEPARATOR')) {
		define ('PATH_SEPARATOR', OS_WINDOWS ? ';' : ':');
	}
	
	$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('cal') . 'res/PEAR/';
	
	// set_include_path(get_include_path(). PATH_SEPARATOR . $path);
	require_once ($path . 'Date.php');
}

?>