<?php
defined('TYPO3_MODE') or die();

$sPid = '###CURRENT_PID###'; // storage pid????

// Define the TCA for the access control calendar selector.
$tempColumns = array(
	'tx_cal_calendar' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar_private',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'tx_cal_calendar',
			'minitems' => 0,
			'maxitems' => 99,
			'wizards' => array(
				'_PADDING' => 2,
				'_VERTICAL' => 1,
				'add' => array(
					'type' => 'script',
					'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.createNew',
					'icon' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar.gif',
					'params' => array(
						'table' => 'tx_cal_calendar',
						'pid' => $sPid,
						'setValue' => 'set'
					),
					'module' => array(
						'name' => 'wizard_add'
					)
				)
			)
		)
	),
	'tx_cal_calendar_subscription' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar_subscription',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'tx_cal_calendar',
			'minitems' => 0,
			'maxitems' => 99,
			'wizards' => array(
				'_PADDING' => 2,
				'_VERTICAL' => 1,
				'add' => array(
					'type' => 'script',
					'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.createNew',
					'icon' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar.gif',
					'params' => array(
						'table' => 'tx_cal_calendar',
						'pid' => $sPid,
						'setValue' => 'set'
					),
					'module' => array(
						'name' => 'wizard_add'
					)
				)
			)
		)
	)
);

// Add the calendar selector for backend users.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_cal_calendar,tx_cal_calendar_subscription');
