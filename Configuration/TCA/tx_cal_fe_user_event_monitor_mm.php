<?php
defined('TYPO3_MODE') or die();

$tx_cal_fe_user_event_monitor_mm = array(
	'ctrl' => array(
		'requestUpdate' => '',
		'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_fe_user_event.monitor',
		'label' => 'tablenames',
		'label_alt' => 'tablenames,offset',
		'label_alt_force' => 1,
		'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_fe_user_event_monitor_mm.gif',
		'label_userFunc' => 'TYPO3\\CMS\\Cal\\Backend\\TCA\\Labels->getMonitoringRecordLabel'
	),
	'feInterface' => array(
		'fe_admin_fieldList' => ''
	),
	'interface' => array(
		'showRecordFieldList' => 'uid_foreign,uid_local,tablenames,offset,schedulerId'
	),
	'columns' => array(
		'uid_foreign' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_fe_user_event.monitor',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_users,fe_groups,tx_cal_unknown_users',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest'
					)
				)
			)
		),
		'uid_local' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tx_cal_event',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest'
					)
				)
			)
		),
		'tablenames' => array(
			'exclude' => 1,
			'label' => 'tablenames',
			'config' => array(
				'type' => 'input',
				'size' => '12'
			)
		),
		'offset' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_fe_user_event.offset',
			'config' => array(
				'type' => 'input',
				'size' => '6',
				'max' => '4',
				'eval' => 'num',
				'default' => '60'
			)
		),
		'schedulerId' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_fe_user_event.schedulerId',
			'config' => array(
				'type' => 'input',
				'size' => '5',
				'readOnly' => 1
			)
		)
	),
	'types' => array(
		'0' => array(
			'showitem' => 'uid_foreign,uid_local,offset,schedulerId'
		)
	)
);

return $tx_cal_fe_user_event_monitor_mm;