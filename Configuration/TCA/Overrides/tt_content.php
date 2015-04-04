<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$_EXTKEY = $GLOBALS['_EXTKEY'] = 'cal';
$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName) . '_controller';

/***************
 * Plugin
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin (Array (
		'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tt_content.list_type',
		$_EXTKEY . '_controller' 
), 'list_type');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';

$extConf = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
if($extConf ['categoryService'] == 'tx_cal_category') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_cal.xml');
} else if ($extConf ['categoryService'] == 'sys_category') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_cal_sys_category.xml');
}

/***************
 * Default TypoScript
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/ts/', 'Classic CSS-based template');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/ts_standard/', 'Standard CSS-based template');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/ajax/', 'AJAX-based template (Experimental!)');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/css/', 'Classic CSS styles');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/css_standard/', 'Standard CSS styles');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/rss_feed/', 'News-feed (RSS,RDF,ATOM)');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/ics/', 'ICS Export');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ($_EXTKEY, 'static/fe-editing/', 'Fe-Editing');

