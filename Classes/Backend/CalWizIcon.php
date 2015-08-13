<?php
namespace TYPO3\CMS\Cal\Backend;
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