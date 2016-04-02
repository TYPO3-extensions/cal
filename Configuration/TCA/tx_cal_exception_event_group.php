<?php
defined('TYPO3_MODE') or die();

$tx_cal_exception_event_group = array(
		'ctrl' => array(
				'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_exception_event_group',
				'label' => 'title',
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'cruser_id' => 'cruser_id',
				'default_sortby' => 'ORDER BY title',
				'delete' => 'deleted',
				'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_exception_event_group.gif',
				'enablecolumns' => array(
						'disabled' => 'hidden'
				),
				'versioningWS' => TRUE,
				'searchFields' => 'title'
		),
		'feInterface' => array(
				'fe_admin_fieldList' => 'title'
		),
		'interface' => array(
				'showRecordFieldList' => 'hidden,title,tx_cal_exception_event_cnt'
		),
		'columns' => array(
				'hidden' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
						'config' => array(
								'type' => 'check',
								'default' => '0'
						)
				),
				'title' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_exception_event_group.title',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '128',
								'eval' => 'required'
						)
				),
				'exception_event_cnt' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_exception_event_group.exception_event_cnt',
						'config' => array(
								'type' => 'group',
								'internal_type' => 'db',
								'allowed' => 'tx_cal_exception_event',
								'size' => 6,
								'minitems' => 0,
								'maxitems' => 100,
								'MM' => 'tx_cal_exception_event_group_mm',
								'wizards' => array(
										'suggest' => array(
												'type' => 'suggest'
										)
								)
						)
				),
				't3ver_label' => array(
						'displayCond' => 'FIELD:t3ver_label:REQ:true',
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
						'config' => array(
								'type' => 'none',
								'cols' => 27 
						)
				)
		),
		'types' => array(
				'0' => array(
                    'showitem' => 'title, --palette--;;1,color,exception_event_cnt'
				)
		),
		'palettes' => array(
				'1' => array(
						'showitem' => 'hidden,t3ver_label'
				)
		)
);

return $tx_cal_exception_event_group;