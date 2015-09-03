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

$tx_cal_event_deviation = array(
		'ctrl' => array(
				'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.deviation',
				'label' => 'title',
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'cruser_id' => 'cruser_id',
				'default_sortby' => 'start_date',
				'delete' => 'deleted',
				'iconfile' => $extRelPath . 'Resources/Public/icons/icon_tx_cal_event_deviation.gif',
				'enablecolumns' => array(
						'disabled' => 'hidden'
				),
				'versioningWS' => TRUE,
				'hideTable' => $configuration['hideDeviationRecords'],
				'searchFields' => 'title,organizer,organizer_link,location,location_link,teaser,description,image,imagecaption,imagealttext,imagetitletext,attachment,attachmentcaption',
				'label_userFunc' => 'TYPO3\\CMS\\Cal\\Backend\\TCA\\Labels->getDeviationRecordLabel'
		),
		'interface' => array(
				'showRecordFieldList' => 'hidden,title,start_date,start_time,allday,end_date,end_time,organizer,location,description,image,attachment'
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
				'parentid' => array(
						'config' => array(
								'type' => 'passthrough'
						)
				),
				'title' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.title',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '128'
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
				'orig_start_date' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.orig_start_date',
						'config' => array(
								'type' => 'input',
								'size' => '12',
								'max' => '20',
								'eval' => 'required,date'
						)
				),
				'orig_start_time' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.orig_start_time',
						'config' => array(
								'type' => 'input',
								'size' => '12',
								'max' => '20',
								'eval' => 'time'
						)
				),
				'start_date' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start_date',
						'config' => array(
								'type' => 'input',
								'size' => '12',
								'max' => '20',
								'eval' => 'date'
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
								'eval' => 'date'
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
						'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.orig_start;3, title;;1;;,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end;6,' . ($configuration['useTeaser'] ? 'teaser;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],' : ''). 'description;;5;richtext:rte_transform[flag=rte_enabled|mode=ts_css],--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.files_sheet,image;;4;;;,imagecaption,attachment,attachmentcaption'
				)
		),
		'palettes' => array(
				'1' => array(
						'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'
				),
				'3' => array(
						'showitem' => 'orig_start_date,orig_start_time',
						'canNotCollapse' => 1
				),
				'5' => array(
						'showitem' => 'start_date,start_time,allday',
						'canNotCollapse' => 1
				),
				'6' => array(
						'showitem' => 'end_date,end_time',
						'canNotCollapse' => 1
				)
		)
);

return $tx_cal_event_deviation;