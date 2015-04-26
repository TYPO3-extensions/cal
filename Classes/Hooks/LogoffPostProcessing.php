<?php
namespace TYPO3\CMS\Cal\Hooks;
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2005-2009 Mario Matzulla
 * (c) 2005-2009 Christian Technology Ministries International Inc.
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