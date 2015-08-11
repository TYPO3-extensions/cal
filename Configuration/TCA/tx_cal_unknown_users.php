<?php
defined('TYPO3_MODE') or die();

$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('cal');

$tx_cal_unknown_users = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_unknown_users',
		'label' => 'email',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY email',
		'delete' => 'deleted',
		'enablecolumns' => array(),
		'versioningWS' => TRUE,
		'iconfile' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_unknown_users.gif',
		'searchFields' => 'email'
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'hidden, email'
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden,email'
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'email' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_unknown_users.email',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '64',
				'eval' => 'required'
			)
		)
	),
	'types' => array(
		'0' => array(
			'showitem' => 'hidden,email'
		)
	),
	'palettes' => array(
		'1' => array(
			''
		)
	)
);

return $tx_cal_unknown_users;