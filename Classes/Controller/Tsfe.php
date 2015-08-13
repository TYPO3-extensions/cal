<?php
namespace TYPO3\CMS\Cal\Controller;
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
class Tsfe extends \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController {
	function Tsfe($TYPO3_CONF_VARS, $id, $type, $no_cache = '', $cHash = '', $jumpurl = '', $MP = '', $RDCT = '') {
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) < 4006000) {
			return $this->tslib_fe ($TYPO3_CONF_VARS, $id, $type, $no_cache, $cHash, $jumpurl, $MP, $RDCT);
		}
		return $this->__construct ($TYPO3_CONF_VARS, $id, $type, $no_cache, $cHash, $jumpurl, $MP, $RDCT);
	}
	function pageNotFoundHandler($code, $header = '', $reason = '') {
		// do nothing
	}
	function pageNotFoundAndExit($reason = '', $header = '') {
		// do nothing
	}
}

?>