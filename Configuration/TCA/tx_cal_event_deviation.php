<?php
defined('TYPO3_MODE') or die();

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
				'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_event_deviation.gif',
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
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
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
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
						'config' => array(
								'type' => 'input',
						        'renderType' => 'inputDateTime',
								'size' => '12',
								'eval' => 'datetime',
								'default' => '0',
								'checkbox' => '0'
						)
				),
				'endtime' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
						'config' => array(
								'type' => 'input',
						        'renderType' => 'inputDateTime',
								'size' => '12',
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
						        'renderType' => 'inputDateTime',
								'size' => '12',
								'eval' => 'required,date'
						)
				),
				'orig_start_time' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.orig_start_time',
						'config' => array(
								'type' => 'input',
						        'renderType' => 'inputDateTime',
								'size' => '12',
								'eval' => 'time'
						)
				),
				'start_date' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start_date',
						'config' => array(
								'type' => 'input',
						        'renderType' => 'inputDateTime',
								'size' => '12',
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
						        'renderType' => 'inputDateTime',
								'size' => '12',
								'eval' => 'time',
								'default' => '0'
						)
				),
				'end_date' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end_date',
						'config' => array(
								'type' => 'input',
						        'renderType' => 'inputDateTime',
								'size' => '12',
								'eval' => 'date',
						        'tx_cal_event' => 'start_date'
						)
				),
				'end_time' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end_time',
						'displayCond' => 'FIELD:allday:!=:1',
						'config' => array(
								'type' => 'input',
						        'renderType' => 'inputDateTime',
								'size' => '12',
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
    						    'fieldControl' => array(
    						        'addRecord' => array(
    						            'disabled' => '',
    						            'options' => array(
    						                'pid' => $sPid,
    						                'setValue' => 'set',
    						                'pid' => $sPid,
    						                'table' => $useOrganizerStructure,
    						                'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.createNew',
    						            )
    						        ),
    						        'editPopup' => array(
    						            'disabled' => '',
    						            'options' => array(
    						                'windowOpenParameters' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
    						                'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.edit',
    						            )
    						        )
    						    ),
								'wizards' => array(
										'_PADDING' => 2,
										'_VERTICAL' => 1,
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
						        'renderType' => 'inputLink',
								'wizards' => array(
										'_PADDING' => 2,
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
    						    'fieldControl' => array(
    						        'addRecord' => array(
    						            'disabled' => '',
    						            'options' => array(
    						                'pid' => $sPid,
    						                'setValue' => 'set',
    						                'pid' => $sPid,
    						                'table' => $useLocationStructure,
    						                'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.createNew',
    						            )
    						        ),
    						        'editPopup' => array(
    						            'disabled' => '',
    						            'options' => array(
    						                'windowOpenParameters' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
    						                'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.edit',
    						            )
    						        )
    						    ),
								'wizards' => array(
										'_PADDING' => 2,
										'_VERTICAL' => 1,
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
						        'renderType' => 'inputLink',
								'wizards' => array(
										'_PADDING' => 2,
										'link' => array(
												'type' => 'popup',
												'title' => 'Link',
												'icon' => 'actions-wizard-link',
												'module' => array(
													'name' => 'wizard_link'
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
						        'enableRichtext' => true,
    						    'fieldControl' => array(
    						        'fullScreenRichtext' => array(
    						            'disabled' => '',
    						            'options' => array(
    						                'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
    						            ),
    						        )
    						    ),
								'wizards' => array(
										'_PADDING' => 4,
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
						        'enableRichtext' => true,
    						    'fieldControl' => array(
    						        'fullScreenRichtext' => array(
    						            'disabled' => '',
    						            'options' => array(
    						                'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
    						            ),
    						        )
    						    ),
								'wizards' => array(
										'_PADDING' => 4,
								)
						)
				),
				'image' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.images',
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
						'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media',
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
						'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
						'config' => array(
								'type' => 'none',
								'cols' => 27 
						)
				)
		),
		'types' => array(
				'0' => array(
                    'showitem' => '--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.general_sheet,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.orig_start;3, title, --palette--;;1,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.start;5,--palette--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.end;6,' . ($configuration['useTeaser'] ? 'teaser,' : ''). 'description, --div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.location_sheet,' . ($configuration['hideLocationTextfield'] ? 'location_id,location_pid,location_link' : 'location,location_id,location_pid,location_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.organizer_sheet,' . ($configuration['hideOrganizerTextfield'] ? 'organizer_id,organizer_pid,organizer_link' : 'organizer,organizer_id,organizer_pid,organizer_link'). ',--div--;LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_event.files_sheet,image, --palette--;;4,imagecaption,attachment,attachmentcaption'
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
						'showitem' => 'allday,--linebreak--,start_date,start_time',
						'canNotCollapse' => 1
				),
				'6' => array(
						'showitem' => 'end_date,end_time',
						'canNotCollapse' => 1
				)
		)
);

if(\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) > 8000000){
	$tx_cal_event_deviation['columns']['attachment']['config'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('attachment', [
			'appearance' => [
					'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference'
			],
	]);
}

return $tx_cal_event_deviation;