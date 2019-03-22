<?php
namespace TYPO3\CMS\Cal\Hooks;
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
class LogoffPostProcessing {
	function clearSessionApiAfterLogin($params, &$pObj) {
		if ($_COOKIE ['fe_typo_user']) {
			session_id ($_COOKIE ['fe_typo_user']);
			session_start ();
			if (! is_array ($_SESSION)) {
				$_SESSION = Array ();
			}
			
			$sessionEntries = array_keys ($_SESSION);
			foreach ($sessionEntries as $key) {
				if (\TYPO3\CMS\Cal\Utility\Functions::beginsWith ($key, 'cal_api')) {
					unset ($_SESSION [$key]);
				}
			}
		}
	}
	function clearSessionApiAfterLogoff($_params, &$pObj) {
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP ('logintype') === 'logout' && $_COOKIE ['fe_typo_user']) {
			session_id ($_COOKIE ['fe_typo_user']);
			session_start ();
			
			if (! $_SESSION ['cal_api_logoff'] == 1) {
				
				if (is_array ($_SESSION)) {
					$sessionEntries = array_keys ($_SESSION);
					foreach ($sessionEntries as $key) {
						if (\TYPO3\CMS\Cal\Utility\Functions::beginsWith ($key, 'cal_api')) {
							unset ($_SESSION [$key]);
						}
					}
				}
				$_SESSION ['cal_api_logoff'] = 1;
			}
		}
	}
}

?>