<?php
defined('TYPO3_MODE') or die();

$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('cal');

$tx_cal_category = array(
	'ctrl' => array(
		'requestUpdate' => 'calendar_id',
		'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category',
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
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'iconfile' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_category.gif',
		// 'treeParentField' => 'calendar_id',
		'searchFields' => 'title,notification_emails'
	),
	'feInterface' => array(
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime'
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle,calendar_id,single_pid,shared_user_allowed,notification_emails,icon'
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
		'title' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.title',
				'config' => array(
						'type' => 'input',
						'size' => '30',
						'max' => '128',
						'eval' => 'required'
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
		'calendar_id' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.calendar',
				'config' => array(
						'type' => 'select',
						'itemsProcFunc' => 'TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc->getRecords',
						'itemsProcFunc_config' => array(
								'table' => 'tx_cal_calendar',
								'orderBy' => 'tx_cal_calendar.title'
						),
						'items' => array(
								array(
										'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.none',
										0
								)
						),
						'size' => 1,
						'minitems' => 0,
						'maxitems' => 1,
						'allowed' => 'tx_cal_calendar',
						'wizards' => array(
								'suggest' => array(
										'type' => 'suggest'
								)
						)
				)
		),
		'parent_category' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.parent_category',
				'config' => array(
						'type' => 'select',
						'renderType' => 'selectTree',
						'parameterArray' => array(
								'fieldConf' => array(
										'config' => array(
												'renderMode' => 'tree',
										),
								),
						),
						'treeConfig' => array(
// 								'dataProvider' => 'TYPO3\\CMS\\Cal\\TreeProvider\\DatabaseTreeDataProvider',
								'parentField' => 'parent_category',
								'appearance' => array(
										'showHeader' => TRUE,
										'allowRecursiveMode' => TRUE,
										'expandAll' => TRUE,
										'maxLevels' => 99
								)
						),
						'form_type' => 'user',
						'userFunc' => 'TYPO3\CMS\Cal\TreeProvider\TreeView->displayCategoryTree',
						'treeView' => 1,
						'size' => 1,
						'autoSizeMax' => 20,
						'itemListStyle' => 'height:300px;',
						'minitems' => 0,
						'maxitems' => 2,
						'foreign_table' => 'tx_cal_category'
				)
		),
		'shared_user_allowed' => array(
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.shared_user_allowed',
				'config' => array(
						'type' => 'check'
				)
		),
		'single_pid' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.single_pid',
				'config' => array(
						'type' => 'group',
						'internal_type' => 'db',
						'allowed' => 'pages',
						'size' => '1',
						'maxitems' => '1',
						'minitems' => '0',
						'show_thumbs' => '1',
						'wizards' => array(
								'suggest' => array(
										'type' => 'suggest'
								)
						)
				)
		),
		'notification_emails' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.notification_emails',
				'config' => array(
						'type' => 'input',
						'size' => '30'
				)
		),
		'icon' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.icon',
				'config' => array(
						'type' => 'input',
						'size' => '30',
						'max' => '128'
				)
		),
		'sys_language_uid' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
				'config' => array(
						'type' => 'select',
						'foreign_table' => 'sys_language',
						'foreign_table_where' => 'ORDER BY sys_language.title',
						'items' => array(
								array(
										'LLL:EXT:lang/locallang_general.php:LGL.allLanguages',
										- 1
								),
								array(
										'LLL:EXT:lang/locallang_general.php:LGL.default_value',
										0
								)
						)
				)
		),
		'l18n_parent' => array(
				'displayCond' => 'FIELD:sys_language_uid:>:0',
				'exclude' => 1,
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
				'config' => array(
						'type' => 'select',
						'items' => array(
								array(
										'',
										0
								)
						),
						'foreign_table' => 'tx_cal_category',
						'foreign_table_where' => 'AND tx_cal_category.sys_language_uid IN (-1,0)'
				)
		),
		'l18n_diffsource' => array(
				'config' => array(
						'type' => 'passthrough'
				)
		),
		't3ver_label' => array(
				'displayCond' => 'FIELD:t3ver_label:REQ:true',
				'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
				'config' => array(
						'type' => 'none',
						'cols' => 27 
				)
		)
	),
	'types' => array(
			'0' => array(
					'showitem' => 'type,title;;1;;,calendar_id,parent_category,shared_user_allowed,single_pid,notification_emails,icon'
			)
	),
	'palettes' => array(
		'1' => array(
			'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label,headerstyle,bodystyle'
		)
	)
);


return $tx_cal_category;