<?php
defined('TYPO3_MODE') or die();

$tx_cal_organizer = array(
		'ctrl' => array(
				'title' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer',
				'label' => 'name',
				'tstamp' => 'tstamp',
				'crdate' => 'crdate',
				'cruser_id' => 'cruser_id',
				'default_sortby' => 'ORDER BY name',
				'delete' => 'deleted',
				'iconfile' => 'EXT:cal/Resources/Public/icons/icon_tx_cal_organizer.gif',
				'enablecolumns' => array(
						'disabled' => 'hidden'
				),
				'versioningWS' => TRUE,
				'origUid' => 't3_origuid',
				'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
				'transOrigPointerField' => 'l18n_parent',
				'transOrigDiffSourceField' => 'l18n_diffsource',
				'languageField' => 'sys_language_uid',
				'searchFields' => 'name,description,street,zip,city,country_zone,country,phone,fax,email,image,imagecaption,imagealttext,imagetitletext,link'
		),
		'feInterface' => array(
				'fe_admin_fieldList' => 'name'
		),
		'interface' => array(
				'showRecordFieldList' => 'hidden,name,description, street,zip,city,country_zone,country,phone,fax,email,image,link,shared_user_cnt'
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
				'name' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.name',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '128',
								'eval' => 'required'
						)
				),
				'description' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.description',
						'config' => array(
								'type' => 'text',
								'cols' => '30',
								'rows' => '5',
								'wizards' => array(
										'_PADDING' => 2,
										'RTE' => array(
												'notNewRecords' => 1,
												'RTEonly' => 1,
												'type' => 'script',
												'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
												'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
												'module' => array(
														'name' => 'wizard_rte'
												)
										)
								)
						)
				),
				'street' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.street',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '128'
						)
				),
				'zip' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.zip',
						'config' => array(
								'type' => 'input',
								'size' => '15',
								'max' => '15'
						)
				),
				'city' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.city',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '128'
						)
				),
				'country_zone' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.countryzone'
				// Configuration is done depending on the version @see end of this file
								),
				'country' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.country'
				// Configuration is done depending on the version @see end of this file
								),
				'phone' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.phone',
						'config' => array(
								'type' => 'input',
								'size' => '15',
								'max' => '24'
						)
				),
				'fax' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.fax',
						'config' => array(
								'type' => 'input',
								'size' => '15',
								'max' => '24'
						)
				),
				'email' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.email',
						'config' => array(
								'type' => 'input',
								'size' => '30',
								'max' => '64',
								'eval' => 'lower'
						)
				),
				'image' => array(
						'exclude' => 1,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.image',
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
				'link' => array(
						'exclude' => 0,
						'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.link',
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
												'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_link.gif',
												'module' => array(
													'name' => 'wizard_element_browser'
												),
												'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
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
								'MM' => 'tx_cal_organizer_shared_user_mm',
								'wizards' => array(
										'suggest' => array(
												'type' => 'suggest'
										)
								)
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
								'foreign_table' => 'tx_cal_organizer',
								'foreign_table_where' => 'AND tx_cal_organizer.sys_language_uid IN (-1,0)'
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
                    'columnsOverrides' => array(
                        'description' => array(
                            'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                        )
                    ),
                    'showitem' => 'name, --palette--;;1, description, street, city, country, country_zone, zip, phone,fax,email,image,link,shared_user_cnt'
				)
		),
		'palettes' => array(
				'1' => array(
						'showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'
				)
		)
);

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')){
	$tx_cal_organizer['columns']['country_zone']['config'] = array(
			'type' => 'select',
			'items' => array(
					array(
							'',
							0
					)
			),
			'foreign_table' => 'static_country_zones',
			'foreign_table_where' => 'ORDER BY static_country_zones.zn_name_en',
			'itemsProcFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\ElementRenderingHelper->translateCountryZonesSelector',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
			'wizards' => array(
					'suggest' => array(
							'type' => 'suggest',
							'default' => array(
									'receiverClass' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\SuggestReceiver'
							)
					)
			)
	);
	$tx_cal_organizer['columns']['country']['config'] = array(
			'type' => 'select',
			'items' => array(
					array(
							'',
							0
					)
			),
			'foreign_table' => 'static_countries',
			'foreign_table_where' => 'ORDER BY static_countries.cn_short_en',
			'itemsProcFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\ElementRenderingHelper->translateCountriesSelector',
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
			'wizards' => array(
					'suggest' => array(
							'type' => 'suggest',
							'default' => array(
									'receiverClass' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\SuggestReceiver'
							)
					)
			)
	);
}

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) < 7000000) {
	$tx_cal_organizer['types']['0']['showitem'] = 'name, --palette--;;1, description;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css], street, city, country, country_zone, zip, phone,fax,email,image,link,shared_user_cnt';
}

return $tx_cal_organizer;