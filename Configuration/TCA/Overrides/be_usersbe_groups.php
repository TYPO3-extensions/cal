<?php
defined('TYPO3_MODE') or die();

// Define the TCA for a checkbox and calendar-/category selector to enable access control.
$tempColumns = array(
	'tx_cal_enable_accesscontroll' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_enable_accesscontroll',
		'config' => array(
			'type' => 'check',
			'default' => 0
		)
	),
	'tx_cal_calendar' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar_accesscontroll',
		'displayCond' => 'FIELD:tx_cal_enable_accesscontroll:REQ:true',
		'config' => array(
			'type' => 'select',
			'size' => 10,
			'minitems' => 0,
			'maxitems' => 100,
			'autoSizeMax' => 20,
			'itemListStyle' => 'height:130px;',
			'foreign_table' => 'tx_cal_calendar'
		)
	),
	'tx_cal_category' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category_accesscontroll',
		'displayCond' => 'FIELD:tx_cal_enable_accesscontroll:REQ:true',
		'config' => array(
			'type' => 'select',
			'form_type' => 'user',
			'userFunc' => 'TYPO3\CMS\Cal\TreeProvider\TreeView->displayCategoryTree',
			'treeView' => 1,
			'size' => 20,
			'minitems' => 0,
			'maxitems' => 100,
			'autoSizeMax' => 20,
			'itemListStyle' => 'height:270px;',
			'foreign_table' => 'tx_cal_category'
		)
	)
);

// Add the checkbox and the calendar-/category selector for backend users.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_cal_enable_accesscontroll;;;;1-1-1', '0');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_cal_calendar;;;;1-1-1', '0');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_cal_category;;;;1-1-1', '0');
$GLOBALS['TCA']['be_users']['ctrl']['requestUpdate'] .= ',tx_cal_enable_accesscontroll';

// Add the checkbox and the calendar-/category selector for backend groups.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_groups', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'tx_cal_enable_accesscontroll;;;;1-1-1');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'tx_cal_calendar;;;;1-1-1');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_groups', 'tx_cal_category;;;;1-1-1');
$GLOBALS['TCA']['be_groups']['ctrl']['requestUpdate'] .= ',tx_cal_enable_accesscontroll';