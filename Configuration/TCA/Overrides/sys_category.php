<?php
if (! defined ( 'TYPO3_MODE' )) {
	die ( 'Access denied.' );
}

$ll = 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:';

/**
 * Add extra fields to the sys_category record
 */
$newCalSysCategoryColumns = array (
		'images' => array(
				'exclude' => 1,
				'l10n_mode' => 'mergeIfNotBlank',
				'label' => $ll . 'tx_cal_category.image',
				'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
						'images',
						array(
								'appearance' => array(
										'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
										'showPossibleLocalizationRecords' => 1,
										'showRemovedLocalizationRecords' => 1,
										'showAllLocalizationLink' => 1,
										'showSynchronizationLink' => 1
								),
								'foreign_match_fields' => array(
										'fieldname' => 'images',
										'tablenames' => 'sys_category',
										'table_local' => 'sys_file',
								),
						),
						$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
				)
		),
		'single_pid' => array (
				'exclude' => 1,
				'l10n_mode' => 'mergeIfNotBlank',
				'label' => $ll . 'tx_cal_category.single_pid',
				'config' => array (
						'type' => 'group',
						'internal_type' => 'db',
						'allowed' => 'pages',
						'size' => 1,
						'maxitems' => 1,
						'minitems' => 0,
						'show_thumbs' => 1,
						'wizards' => array (
								'suggest' => array (
										'type' => 'suggest' 
								) 
						) 
				) 
		),
		'shortcut' => array (
				'exclude' => 1,
				'l10n_mode' => 'mergeIfNotBlank',
				'label' => $ll . 'tx_cal_category.shortcut',
				'config' => array (
						'type' => 'group',
						'internal_type' => 'db',
						'allowed' => 'pages',
						'size' => 1,
						'maxitems' => 1,
						'minitems' => 0,
						'show_thumbs' => 1,
						'wizards' => array (
								'suggest' => array (
										'type' => 'suggest' 
								) 
						) 
				) 
		),
		'headerstyle' => Array (
				'exclude' => 1,
				'label' => $ll . 'tx_cal_category.headerstyle',
				'config' => Array (
						'type' => 'user',
						'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->getHeaderStyles' 
				) 
		),
		'bodystyle' => Array (
				'exclude' => 1,
				'label' => $ll . 'tx_cal_category.bodystyle',
				'config' => Array (
						'type' => 'user',
						'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->getBodyStyles' 
				) 
		),
		'calendar_id' => Array (
				'exclude' => 1,
				'label' => $ll . 'tx_cal_category.calendar',
				'config' => Array (
                        'renderType' => 'selectSingle',
						'type' => 'select',
						'itemsProcFunc' => 'TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc->getRecords',
						'itemsProcFunc_config' => array (
								'table' => 'tx_cal_calendar',
								'orderBy' => 'tx_cal_calendar.title' 
						),
						'items' => Array (
								Array (
										'',
										0 
								) 
						),
						'size' => 1,
						'minitems' => 0,
						'maxitems' => 1,
						'allowed' => 'tx_cal_calendar',
						'wizards' => Array (
								'suggest' => array (
										'type' => 'suggest',
										'default' => $wizzardSuggestDefaults 
								) 
						) 
				) 
		),
		'shared_user_allowed' => Array (
				'label' => $ll . 'tx_cal_category.shared_user_allowed',
				'config' => Array (
						'type' => 'check'
				)
		),
		
		'notification_emails' => Array (
				'exclude' => 0,
				'label' => $ll . 'tx_cal_category.notification_emails',
				'config' => Array (
						'type' => 'input',
						'size' => '30'
				)
		),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns ( 'sys_category', $newCalSysCategoryColumns );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.options, images', '', 'before:description' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'single_pid', '', 'after:description' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'shortcut', '', 'after:shortcut' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'headerstyle', '', 'after:single_pid' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'bodystyle', '', 'after:headerstyle' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'calendar_id', '', 'after:bodystyle' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'shared_user_allowed', '', 'after:calendar_id' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes ( 'sys_category', 'notification_emails', '', 'after:shared_user_allowed' );
