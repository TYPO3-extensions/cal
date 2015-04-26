<?php
namespace TYPO3\CMS\Cal\Backend;
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
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class CalWizIcon {
	
	public function proc($wizardItems) {
		
		$LL = $this->includeLocalLang ();
		
		$wizardItems ['plugins_tx_cal'] = array (
				'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath ('cal') . 'Resources/Public/icons/ce_wiz.gif',
				'title' => $GLOBALS['LANG']->getLLL ('pi1_title', $LL),
				'description' => $GLOBALS['LANG']->getLLL ('pi1_plus_wiz_description', $LL),
				'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=cal_controller' 
		);
		
		return $wizardItems;
	}
	
	public function includeLocalLang() {
		$llFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('cal') . 'Resources/Private/Language/locallang_plugin.xml';
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
			$localizationParser = new \TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser();
			$LOCAL_LANG = $localizationParser->getParsedData ($llFile, $GLOBALS ['LANG']->lang);
		} else {
			$LOCAL_LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile ($llFile, $GLOBALS ['LANG']->lang);
		}
		
		return $LOCAL_LANG;
	}
	
	// get used charset
	public static function getCharset() {
		if ($GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['forceCharset']) { // First priority: forceCharset! If set, this will be authoritative!
			$charset = $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['forceCharset'];
		} elseif (is_object ($GLOBALS ['LANG'])) {
			$charset = $GLOBALS ['LANG']->charSet; // If "LANG" is around, that will hold the current charset
		} else {
			$charset = 'utf-8'; // THIS is just a hopeful guess!
		}
		
		return $charset;
	}
}

?>