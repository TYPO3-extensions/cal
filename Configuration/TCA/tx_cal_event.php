<?php
defined('TYPO3_MODE') or die();

$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('cal');
$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

$sPid = '###CURRENT_PID###'; // storage pid????

$useLocationStructure = $configuration['useLocationStructure'] ?: 'tx_cal_location';
$useOrganizerStructure = $configuration['useOrganizerStructure'] ?: 'tx_cal_organizer';

switch ($useLocationStructure){
	case 'tx_tt_address':
		$useLocationStructure = 'tt_address';
		break;
}
switch ($useOrganizerStructure){
	case 'tx_tt_address':
		$useOrganizerStructure = 'tt_address';
		break;
	case 'tx_feuser':
		$useOrganizerStructure = 'fe_users';
		break;
}

$tx_cal_event = array(
	'ctrl' => array(
			'requestUpdate' => 'calendar_id,freq,rdate_type,allday',
			'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event',
			'label' => 'title',
			'tstamp' => 'tstamp',
			'crdate' => 'crdate',
			'cruser_id' => 'cruser_id',
			'default_sortby' => 'ORDER BY start_date DESC, start_time DESC',
			'delete' => 'deleted',
			'versioningWS' => TRUE,
			'origUid' => 't3_origuid',
			'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
			'transOrigPointerField' => 'l18n_parent',
			'transOrigDiffSourceField' => 'l18n_diffsource',
			'languageField' => 'sys_language_uid',
			'type' => 'type',
			'typeicon_column' => 'type',
			'typeicons' => array(
					'1' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_events_intlnk.gif',
					'2' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_events_exturl.gif',
					'3' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_events_meeting.gif',
					'4' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_events_todo.gif'
			),
			'dividers2tabs' => $configuration['noTabDividers'] ? FALSE : TRUE,
			'enablecolumns' => array(
					'disabled' => 'hidden',
					'starttime' => 'starttime',
					'endtime' => 'endtime'
			),
			'iconfile' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_events.gif',
			'searchFields' => 'title,organizer,organizer_link,location,location_link,teaser,description,ext_url,image,imagecaption,imagealttext,imagetitletext,attachment,attachmentcaption',
			'label_userFunc' => 'TYPO3\\CMS\\Cal\\Backend\\TCA\\Labels->getEventRecordLabel'
	),
	'feInterface' => array(
			'fe_admin_fieldList' => 'hidden, title, starttime, endtime, start_date, start_time, end_date, end_time, relation_cnt, organizer, organizer_id, organizer_pid, location, location_id, location_pid, description, freq, byday, bymonthday, bymonth, until, count, interval, rdate_type, rdate, notify_cnt'
	),
	'interface' => array(
			'showRecordFieldList' => 'hidden,category_id,title,start_date,start_time,allday,end_date,end_time,organizer,location,description,image,attachment,freq,byday,bymonthday,bymonth,until,count,rdate_type,rdate,end,intrval,exception_cnt, shared_user_cnt,attendee,status,priority,completed'
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
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.title',
					'config' => array(
							'type' => 'input',
							'size' => '30',
							'max' => '128',
							'eval' => 'required'
					)
			),
			'starttime' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'datetime',
							'default' => '0',
							'checkbox' => '0'
					)
			),
			'endtime' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'datetime',
							'default' => '0',
							'checkbox' => '0'
					)
			),
			'calendar_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.calendar',
					'config' => array(
							'type' => 'select',
							'size' => 1,
							'minitems' => 1,
							'maxitems' => 1,
							'itemsProcFunc' => 'TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc->getRecords',
							'itemsProcFunc_config' => array(
									'table' => 'tx_cal_calendar',
									'orderBy' => 'tx_cal_calendar.title'
							),
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
									),
									'edit' => array(
											'type' => 'popup',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_calendar.edit',
											'module' => array(
													'name' => 'wizard_edit'
											),
											'popup_onlyOpenIfSelected' => 1,
											'icon' => 'edit2.gif',
											'JSopenParams' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
											'params' => array(
													'table' => 'tx_cal_calendar'
											)
									)
							)
					)
			),
			'category_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.category',
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
								'dataProvider' => 'TYPO3\\CMS\\Cal\\TreeProvider\\DatabaseTreeDataProvider',
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
							'size' => 12,
							'autoSizeMax' => 20,
							'itemListStyle' => 'height:300px;',
							'minitems' => 0,
							'maxitems' => 20,
							'foreign_table' => 'tx_cal_category',
							'MM' => 'tx_cal_event_category_mm',
							
							'wizards' => array(
									'_PADDING' => 2,
									'_VERTICAL' => 1,
									'add' => array(
											'type' => 'script',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.createNew',
											'icon' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_category.gif',
											'params' => array(
													'table' => 'tx_cal_category',
													'pid' => $sPid,
													'setValue' => 'append'
											),
											'module' => array(
													'name' => 'wizard_add'
											)
									),
									'edit' => array(
											'type' => 'popup',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.edit',
											'module' => array(
													'name' => 'wizard_edit'
											),
											'popup_onlyOpenIfSelected' => 1,
											'icon' => 'edit2.gif',
											'JSopenParams' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
											'params' => array(
													'table' => 'tx_cal_category'
											)
									)
							)
					)
			),
			'start_date' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start_date',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'required,date',
							'tx_cal_event' => 'start_date'
					)
			),
			'allday' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.allday',
					'config' => array(
							'type' => 'check',
							'default' => 0
					)
			),
			'start_time' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start_time',
					'displayCond' => 'FIELD:allday:!=:1',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'time',
							'default' => '0'
					)
			),
			'end_date' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end_date',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'required,date',
							'tx_cal_event' => 'end_date'
					)
			),
			'end_time' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end_time',
					'displayCond' => 'FIELD:allday:!=:1',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'time',
							'default' => '0'
					)
			),
			'organizer' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer',
					'config' => array(
							'type' => 'input',
							'size' => '30',
							'max' => '128'
					)
			),
			'organizer_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_id',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'size' => 1,
							'minitems' => 0,
							'maxitems' => 1,
							'allowed' => $useOrganizerStructure,
							'wizards' => array(
									'_PADDING' => 2,
									'_VERTICAL' => 1,
									'add' => array(
											'type' => 'script',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.createNew',
											'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif', // \TYPO3\CMS\Backend\Utility\IconUtility::getIcon($useOrganizerStructure),
											'params' => array(
													'table' => $useOrganizerStructure,
													'pid' => $sPid,
													'setValue' => 'set'
											),
											'module' => array(
													'name' => 'wizard_add'
											)
									),
									'edit' => array(
											'type' => 'popup',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.edit',
											'module' => array(
													'name' => 'wizard_edit'
											),
											'popup_onlyOpenIfSelected' => 1,
											'icon' => 'edit2.gif',
											'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
											'params' => array(
													'table' => $useOrganizerStructure 
											)
									),
									'suggest' => array(
											'type' => 'suggest'
									)
							)
					)
			),
			'organizer_pid' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_pid',
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
			'organizer_link' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_link',
					'config' => array(
							'type' => 'input',
							'size' => '25',
							'max' => '128',
							'checkbox' => '',
							'eval' => 'trim',
							'wizards' => array(
									'_PADDING' => 2,
									'link' => array(
											'type' => 'popup',
											'title' => 'Link',
											'icon' => 'link_popup.gif',
											'module' => array(
													'name' => 'wizard_element_browser',
													'urlParameters' => array(
															'mode' => 'wizard'
													)
											),
											'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
									)
							)
					)
			),
			'location' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location',
					'config' => array(
							'type' => 'input',
							'size' => '30',
							'max' => '128'
					)
			),
			'location_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_id',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'size' => 1,
							'minitems' => 0,
							'maxitems' => 1,
							'allowed' => $useLocationStructure,
							'wizards' => array(
									'_PADDING' => 2,
									'_VERTICAL' => 1,
									'add' => array(
											'type' => 'script',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.createNew',
											'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif', // \TYPO3\CMS\Backend\Utility\IconUtility::getIcon($useLocationStructure),
											'params' => array(
													'table' => $useLocationStructure,
													'pid' => $sPid,
													'setValue' => 'set'
											),
											'module' => array(
													'name' => 'wizard_add'
											)
									),
									'edit' => array(
											'type' => 'popup',
											'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.edit',
											'module' => array(
													'name' => 'wizard_edit'
											),
											'popup_onlyOpenIfSelected' => 1,
											'icon' => 'edit2.gif',
											'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
											'params' => array(
													'table' => $useLocationStructure 
											)
									),
									'suggest' => array(
											'type' => 'suggest'
									)
							)
					)
			),
			'location_pid' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_pid',
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
			'location_link' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_link',
					'config' => array(
							'type' => 'input',
							'size' => '25',
							'max' => '128',
							'checkbox' => '',
							'eval' => 'trim',
							'wizards' => array(
									'_PADDING' => 2,
									'link' => array(
											'type' => 'popup',
											'title' => 'Link',
											'icon' => 'link_popup.gif',
											'module' => array(
													'name' => 'wizard_element_browser',
													'urlParameters' => array(
															'mode' => 'wizard'
													)
											),
											'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
									)
							)
					)
			),
			'teaser' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.teaser',
					'config' => array(
							'type' => 'text',
							'cols' => '40',
							'rows' => '6',
							'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
							'wizards' => array(
									'_PADDING' => 4,
									'RTE' => array(
											'notNewRecords' => 1,
											'RTEonly' => 1,
											'type' => 'script',
											'title' => 'LLL:EXT:cms/locallang_ttc.php:bodytext.W.RTE',
											'icon' => 'wizard_rte2.gif',
											'module' => array(
													'name' => 'wizard_rte'
											)
									)
							)
					)
			),
			'description' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.description',
					'config' => array(
						'type' => 'text',
						'cols' => '40',
						'rows' => '6',
						'softref' => 'rtehtmlarea_images,typolink_tag,images,email[subst],url',
						'wizards' => array(
							'_PADDING' => 4,
							'RTE' => array(
								'notNewRecords' => 1,
								'RTEonly' => 1,
								'type' => 'script',
								'title' => 'Title',
								'icon' => 'wizard_rte2.gif',
								'module' => array(
									'name' => 'wizard_rte',
								)
							)
						)
					)
			),
			'freq' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.freq',
					'config' => array(
							'type' => 'select',
							'size' => '1',
							'items' => array(
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:frequency.none',
											'none'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:frequency.day',
											'day'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:frequency.week',
											'week'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:frequency.month',
											'month'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:frequency.year',
											'year'
									)
							)
					)
			),
			'byday' => array(
					'exclude' => 1,
					'displayCond' => 'FIELD:freq:IN:week,month,year',
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.byday_short',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->byDay'
					)
			),
			'bymonthday' => array(
					'exclude' => 1,
					'displayCond' => 'FIELD:freq:IN:month,year',
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.bymonthday_short',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->byMonthDay'
					)
			),
			'bymonth' => array(
					'exclude' => 1,
					'displayCond' => 'FIELD:freq:IN:year',
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.bymonth_short',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->byMonth'
					)
			),
			'until' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.until',
					'displayCond' => 'FIELD:freq:IN:day,week,month,year',
					'config' => array(
							'type' => 'input',
							'size' => '12',
							'max' => '20',
							'eval' => 'date'
					)
			),
			'cnt' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.count',
					'displayCond' => 'FIELD:freq:IN:day,week,month,year',
					'config' => array(
							'type' => 'input',
							'size' => '4',
							'eval' => 'num',
							'checkbox' => '0'
					)
			),
			'intrval' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.interval',
					'displayCond' => 'FIELD:freq:IN:day,week,month,year',
					'config' => array(
							'type' => 'input',
							'size' => '4',
							'eval' => 'num',
							'default' => '1'
					)
			),
			'rdate_type' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.rdate_type',
					'config' => array(
							'type' => 'select',
							'size' => 1,
							'items' => array(
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:rdate_type.none',
											'none'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:rdate_type.date',
											'date'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:rdate_type.date_time',
											'date_time'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:rdate_type.period',
											'period'
									)
							),
							'default' => 0
					)
			),
			'rdate' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.rdate',
					'displayCond' => 'FIELD:rdate_type:IN:date_time,date,period',
					'config' => array(
							'type' => 'user',
							'userFunc' => 'TYPO3\CMS\Cal\Backend\TCA\CustomTca->rdate'
					)
			),
			'deviation' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.deviation',
					'config' => array(
							'type' => 'inline',
							'foreign_table' => 'tx_cal_event_deviation',
							'foreign_field' => 'parentid',
							'foreign_label' => 'title',
							'maxitems' => 10,
							'appearance' => array(
									'collapseAll' => 1,
									'expandSingle' => 1,
									'useSortable' => 1
							)
					)
			),
			'monitor_cnt' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_fe_user_event.monitor',
					'config' => array(
							'type' => 'inline',
							'foreign_table' => 'tx_cal_fe_user_event_monitor_mm',
							'foreign_field' => 'uid_local',
							'appearance' => array(
									'collapseAll' => 1,
									'expandSingle' => 1,
									'useSortable' => 1
							)
					)
			),
			'exception_cnt' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.exception',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'allowed' => 'tx_cal_exception_event,tx_cal_exception_event_group',
							'size' => 6,
							'minitems' => 0,
							'maxitems' => 100,
							'MM' => 'tx_cal_exception_event_mm'
					)
			),
			'fe_cruser_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.fe_cruser_id',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'allowed' => 'fe_users',
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
			'fe_crgroup_id' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.fe_crgroup_id',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'allowed' => 'fe_groups',
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
			
			'shared_user_cnt' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.shared_user',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'allowed' => 'fe_users,fe_groups',
							'size' => 6,
							'minitems' => 0,
							'maxitems' => 100,
							'MM' => 'tx_cal_event_shared_user_mm',
							'wizards' => array(
									'suggest' => array(
											'type' => 'suggest'
									)
							)
					)
			),

			/* new */
			'type' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.type',
					'config' => array(
							'type' => 'select',
							'size' => 1,
							'items' => array(
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.type.I.0',
											0
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.type.I.1',
											1
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.type.I.2',
											2 
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.type.I.3',
											3 
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.type.I.4',
											4 
									)
							),
							'default' => 0
					)
			),
			
			'ext_url' => array(
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.external',
					'config' => array(
							'type' => 'input',
							'size' => '40',
							'max' => '256',
							'eval' => 'required',
							'wizards' => array(
									'_PADDING' => 2,
									'link' => array(
											'type' => 'popup',
											'title' => 'Link',
											'icon' => 'link_popup.gif',
											'module' => array(
													'name' => 'wizard_element_browser',
													'urlParameters' => array(
															'mode' => 'wizard'
													)
											),
											'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
									)
							)
					)
			),
			
			'page' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.shortcut_page',
					'config' => array(
							'type' => 'group',
							'internal_type' => 'db',
							'allowed' => 'pages',
							'size' => '1',
							'maxitems' => '1',
							'minitems' => '0',
							'show_thumbs' => '1',
							'eval' => 'required',
							'wizards' => array(
									'suggest' => array(
											'type' => 'suggest'
									)
							)
					)
			),
			/* new */
			'image' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
					'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig ( 'image', array(
							'maxitems' => 5,
							// Use the imageoverlayPalette instead of the basicoverlayPalette
							'foreign_types' => array(
									'0' => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									),
									\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									)
							)
					))
			),
			'attachment' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cms/locallang_ttc.php:media',
					'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig ( 'attachment', array(
							'maxitems' => 5,
							// Use the imageoverlayPalette instead of the basicoverlayPalette
							'foreign_types' => array(
									'0' => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									),
									\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									),
									\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									),
									\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									),
									\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									),
									\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
											'showitem' => '
											--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
											--palette--;;filePalette'
									)
							)
					))
			),
			
			'attendee' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.attendee',
					'config' => array(
							'type' => 'inline',
							'foreign_table' => 'tx_cal_attendee',
							'foreign_field' => 'event_id',
							'appearance' => array(
									'collapseAll' => 1,
									'expandSingle' => 1,
									'useSortable' => 1
							)
					)
			),
			'send_invitation' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.send_invitation',
					'config' => array(
							'type' => 'check',
							'default' => '0'
					)
			),
			'status' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status',
					'config' => array(
							'type' => 'select',
							'items' => array(
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.0',
											'0'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_attendee.status.NEEDS-ACTION',
											'NEEDS-ACTION'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.status.COMPLETED',
											'COMPLETED'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.status.IN-PROGRESS',
											'IN-PROGRESS'
									),
									array(
											'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.status.CANCELLED',
											'CANCELLED'
									)
							),
							'size' => '1',
							'minitems' => 1,
							'maxitems' => 1
					)
			),
			'priority' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.priority',
					'config' => array(
							'type' => 'select',
							'items' => array(
									array(
											0,
											0
									),
									array(
											1,
											1
									),
									array(
											2,
											2 
									),
									array(
											3,
											3 
									),
									array(
											4,
											4 
									),
									array(
											5,
											5 
									),
									array(
											6,
											6 
									),
									array(
											7,
											7 
									),
									array(
											8,
											8 
									),
									array(
											9,
											9 
									)
							),
							'size' => '1',
							'minitems' => 1,
							'maxitems' => 1
					)
			),
			'completed' => array(
					'exclude' => 1,
					'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.completed',
					'config' => array(
							'type' => 'input',
							'size' => '3',
							'eval' => 'num',
							'checkbox' => '0'
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
							'foreign_table' => 'tx_cal_event',
							'foreign_table_where' => 'AND tx_cal_event.sys_language_uid IN (-1,0)'
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
					'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,type, title;;1;;,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end;6,calendar_id,category_id,' . ($configuration['useTeaser'] ? 'teaser;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],' : ''). 'description;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, deviation, exception_cnt,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.files_sheet,image,attachment,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.other_sheet, monitor_cnt, shared_user_cnt'
			),
			'1' => array(
					'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,type, page,title;;1;;,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end;6,calendar_id,category_id,' . ($configuration['useTeaser'] ? 'teaser;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],' : ''). '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, deviation, exception_cnt, --div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.other_sheet, monitor_cnt, shared_user_cnt'
			),
			'2' => array(
					'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,type, ext_url,title;;1;;,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end;6,calendar_id,category_id,' . ($configuration['useTeaser'] ? 'teaser;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],' : ''). '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, deviation, exception_cnt, --div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.other_sheet, monitor_cnt, shared_user_cnt'
			),
			'3' => array(
					'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,type, title;;1;;,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end;6,calendar_id,category_id,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.attendance_sheet,attendee,send_invitation,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, deviation, exception_cnt,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link')
			),
			'4' => array(
					'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,type, title;;1;;,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.due;6,calendar_id,category_id,description;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.todo_sheet, status, priority, completed,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, deviation, exception_cnt,--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.files_sheet,attachment'
			)
	),
	'palettes' => array(
		'1' => array(
				'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label',
				'canNotCollapse' => 1
		),
		'2' => array(
				'showitem' => 'until, cnt, intrval',
				'canNotCollapse' => 1
		),
		'5' => array(
				'showitem' => 'start_date,start_time,allday',
				'canNotCollapse' => 1
		),
		'6' => array(
				'showitem' => 'end_date,end_time',
				'canNotCollapse' => 1
		),
		'7' => array(
				'showitem' => 'rdate',
				'canNotCollapse' => 1
		)
	)
);

if ($configuration['categoryService'] == 'sys_category'){
	unset($tx_cal_event['columns']['category_id']['config']);
	$tx_cal_event['columns']['category_id']['config'] = array(
		'type' => 'select',
		'renderType' => 'selectTree',
		'treeConfig' => array(
			'dataProvider' => 'TYPO3\\CMS\\Cal\\TreeProvider\\DatabaseTreeDataProvider',
			'parentField' => 'parent',
			'appearance' => array(
				'showHeader' => TRUE,
				'allowRecursiveMode' => TRUE,
				'expandAll' => TRUE,
				'maxLevels' => 99
			)
		),
		'MM' => 'sys_category_record_mm',
		'MM_match_fields' => array(
			'fieldname' => 'category_id',
			'tablenames' => 'tx_cal_event'
		),
		'MM_opposite_field' => 'items',
		'foreign_table' => 'sys_category',
		'foreign_table_where' => ' AND (sys_category.sys_language_uid = 0 OR sys_category.l10n_parent = 0)ORDER BY sys_category.sorting',
		'size' => 10,
		'autoSizeMax' => 20,
		'minitems' => 0,
		'maxitems' => 20
	);
}

return $tx_cal_event;