<?php
defined('TYPO3_MODE') or die();

$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

$tx_cal_calendar = array(
	'ctrl' => array(
			'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar',
			'label' => 'title',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'default_sortby' => 'ORDER BY title',
			'delete' => 'deleted',
			'enablecolumns' => array(
					'disabled' => 'hidden',
					'starttime' => 'starttime',
					'endtime' => 'endtime'
			),
			'type' => 'type',
			'typeicon_column' => 'type',
			'typeicons' => array(
					'1' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar_exturl.gif',
					'2' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar_ics.gif'
			),
			'versioningWS' => TRUE,
			'origUid' => 't3_origuid',
			'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
			'transOrigPointerField' => 'l18n_parent',
			'transOrigDiffSourceField' => 'l18n_diffsource',
			'languageField' => 'sys_language_uid',
			'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_calendar.gif',
			'searchFields' => 'title,ext_url,ext_url_notes,ics_file'
	),
	'feInterface' => array(
			'fe_admin_fieldList' => 'hidden, title, starttime, endtime'
	),
	'interface' => array(
			'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby'
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
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.title',
					'config' => array(
							'type' => 'input',
							'size' => '30',
							'max' => '128',
							'eval' => 'unique, required'
					)
			),
			'owner' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.owner',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'MM' => 'tx_cal_calendar_user_group_mm',
							'size' => 4,
							'minitems' => 0,
							'autoSizeMax' => 25,
							'maxitems' => 500,
							'allowed' => 'fe_users,fe_groups',
					)
			),
			'activate_fnb' => array(
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.activate_fnb',
			        'onChange' => 'reload',
					'config' => array(
							'type' => 'check'
					)
			),
			'fnb_user_cnt' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.fb_users_groups',
					'displayCond' => 'FIELD:activate_fnb:=:1',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'MM' => 'tx_cal_calendar_fnb_user_group_mm',
							'size' => 6,
							'minitems' => 0,
							'maxitems' => 100,
							'allowed' => 'fe_users,fe_groups',
					)
			),
			'nearby' => array(
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.nearby',
					'config' => array(
							'type' => 'check'
					)
			),
			'type' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type',
					'config' => array(
                            'renderType' => 'selectSingle',
							'type' => 'select',
							'size' => 1,
							'items' => array(
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type.I.0',
											0
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type.I.1',
											1
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.type.I.2',
											2 
									)
							),
							'default' => 0
					)
			),
			
			'ext_url' => array(
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.ext_url',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->extUrl'
					)
			),
			
			'ext_url_notes' => array(
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.ext_url_notes',
					'config' => array(
							'type' => 'text'
					)
			),
			
			'ics_file' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.ics_file',
					'config' => array(
					        'type' => 'group',
							'internal_type' => 'file',
							'allowed' => 'ics', // Must be empty for disallowed to work.
							'max_size' => '10000',
							'uploadfolder' => 'uploads/tx_cal/ics',
							'size' => '1',
					        'fieldWizard' => array(
					            'fileThumbnails' => array(
					                'disabled' => true,
					            )
					        ),
							'autoSizeMax' => '1',
							'maxitems' => '1',
							'minitems' => '0'
					)
			),
			
			'refresh' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.refresh',
					'config' => array(
							'type' => 'input',
							'size' => '6',
							'max' => '4',
							'eval' => 'num',
							'default' => '60'
					)
			),
			'schedulerId' => array(
					'exclude' => 0,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.schedulerId',
					'config' => array(
							'type' => 'input',
							'size' => '5',
							'readOnly' => 1
					)
			),
			
			'md5' => array(
					'config' => array(
							'type' => 'passthrough'
					)
			),
			'headerstyle' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.headerstyle',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->getHeaderStyles'
					)
			),
			'bodystyle' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.bodystyle',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->getBodyStyles'
					)
			),
			'sys_language_uid' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
					'config' => array(
                            'renderType' => 'selectSingle',
							'type' => 'select',
							'foreign_table' => 'sys_language',
							'foreign_table_where' => 'ORDER BY sys_language.title',
							'items' => array(
									array(
											'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
											- 1
									),
									array(
											'LLL:EXT:lang/locallang_general.xlf:LGL.default_value',
											0
									)
							)
					)
			),
			'l18n_parent' => array(
					'displayCond' => 'FIELD:sys_language_uid:>:0',
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
					'config' => array(
                            'renderType' => 'selectSingle',
							'type' => 'select',
							'items' => array(
									array(
											'',
											0
									)
							),
							'foreign_table' => 'tx_cal_calendar',
							'foreign_table_where' => 'AND tx_cal_calendar.sys_language_uid IN (-1,0)'
					)
			),
			'l18n_diffsource' => array(
					'config' => array(
							'type' => 'passthrough'
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
                'showitem' => 'type,title, --palette--;;1,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby'
			),
			'1' => array(
                'showitem' => 'type,title, --palette--;;1,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby,ext_url,refresh,schedulerId'
			),
			'2' => array(
                'showitem' => 'type,title, --palette--;;1,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,nearby,ics_file,refresh,schedulerId'
			)
	),
	'palettes' => array(
		'1' => array(
			'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'
		)
	)
);

return $tx_cal_calendar;