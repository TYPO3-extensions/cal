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

/**
 *
 * @author Franz Koch <franz.koch [at] elements . de>
 * @package TYPO3
 * @subpackage cal
 */
require_once (PATH_typo3 . 'wizard_edit.php');
class ux_SC_wizard_edit extends SC_wizard_edit {
	function main() {
		global $TCA;
		
		if ($this->doClose) {
			$this->closeWindow ();
		} else {
			
			// Initialize:
			$table = $this->P ['table'];
			$field = $this->P ['field'];
			t3lib_div::loadTCA ($table);
			$config = $TCA [$table] ['columns'] [$field] ['config'];
			$fTable = $this->P ['currentValue'] < 0 ? $config ['neg_foreign_table'] : $config ['foreign_table'];
			if (! empty ($this->P ['params'] ['table'])) {
				$fTable = $this->P ['currentValue'] < 0 ? $this->P ['params'] ['neg_table'] : $this->P ['params'] ['table'];
			}
			
			// Detecting the various allowed field type setups and acting accordingly.
			if (t3lib_utility_VersionNumber::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
				$is_P_int = t3lib_utility_Math::canBeInterpretedAsInteger ($this->P ['currentValue']) && $this->P ['currentValue'];
			} else {
				$is_P_int = t3lib_div::testInt ($this->P ['currentValue']);
			}
			
			if (is_array ($config) && $config ['type'] == 'select' && ! $config ['MM'] && $config ['maxitems'] <= 1 && $is_P_int && $this->P ['currentValue'] && $fTable) { // SINGLE value:
				header ('Location: ' . t3lib_div::locationHeaderUrl ('alt_doc.php?returnUrl=' . rawurlencode ('wizard_edit.php?doClose=1') . '&edit[' . $fTable . '][' . $this->P ['currentValue'] . ']=edit'));
			} elseif (is_array ($config) && $this->P ['currentSelectedValues'] && (($config ['type'] == 'select' && $config ['foreign_table']) || ($config ['type'] == 'group' && $config ['internal_type'] == 'db'))) { // MULTIPLE VALUES:
			                                                                                                                                                                                                 
				// Init settings:
				$allowedTables = $config ['type'] == 'group' ? $config ['allowed'] : $config ['foreign_table'] . ',' . $config ['neg_foreign_table'];
				$prependName = 1;
				$params = '';
				
				// Selecting selected values into an array:
				$dbAnalysis = new t3lib_loadDBGroup();
				$dbAnalysis->start ($this->P ['currentSelectedValues'], $allowedTables);
				$value = $dbAnalysis->getValueArray ($prependName);
				
				// Traverse that array and make parameters for alt_doc.php:
				foreach ($value as $rec) {
					$recTableUidParts = t3lib_div::revExplode ('_', $rec, 2);
					$params .= '&edit[' . $recTableUidParts [0] . '][' . $recTableUidParts [1] . ']=edit';
				}
				
				// Redirect to alt_doc.php:
				header ('Location: ' . t3lib_div::locationHeaderUrl ('alt_doc.php?returnUrl=' . rawurlencode ('wizard_edit.php?doClose=1') . $params));
			} else {
				$this->closeWindow ();
			}
		}
	}
}

?>