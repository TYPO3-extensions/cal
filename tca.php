<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_cal_event"] = Array (
	"ctrl" => $TCA["tx_cal_event"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,category_id,title,start_date,start_time,end_date,end_time,organizer,location,description,freq,byday,bymonthday,bymonth,until,count,end,intrval,ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"
	),
	"feInterface" => $TCA["tx_cal_event"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"starttime" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["starttime"],
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"default" => "0",
				"checkbox" => "0"

			)
		),
		"endtime" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["endtime"],
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"default" => "0",
				"checkbox" => "0"

			)
		),
		'category_id' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.category',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'foreign_table' => 'tx_cal_category',
				'foreign_table_where' => 'ORDER BY tx_cal_category.title',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		"start_date" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.start_date",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"start_time" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.start_time",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "time",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"end_date" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.end_date",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"end_time" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.end_time",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "time",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"organizer" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.organizer",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		'organizer_id' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.organizer_id',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.none',0),
				),
				'foreign_table' => 'tt_address',
				'foreign_table_where' => 'ORDER BY tt_address.name',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		"location" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.location",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		'location_id' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.location_id',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.none',0),
				),
				'foreign_table' => 'tt_address',
				'foreign_table_where' => 'ORDER BY tt_address.name',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.description",		
			"config" => Array (
				'type' => 'text',
	            'cols' => '40',    
	            'rows' => '6'
			)
		),
		"freq" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.freq",		
			"config" => Array (
				"type" => "select",	
				"size" => "1",
				'items' => Array (
					Array('none','none'),
					Array('secondly','second'),
					Array('minutely','minute'),
					Array('hourly','hour'),
					Array('daily','day'),
					Array('weekly','week'),
					Array('monthly','month'),
					Array('yearly','year'),
				),
			)
		),
		"byday" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.byday",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"bymonthday" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonthday",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"bymonth" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonth",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"until" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.until",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"cnt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.count",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
				"eval" => "num",
			)
		),
		"intrval" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.interval",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
				"eval" => "num",
				"default" => "1",
			)
		),
		"monitor_cnt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_fe_user_event.monitor",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 6,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_cal_fe_user_event_monitor_mm",
			)
		),
		"exception_cnt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.exception",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cal_exception_event,tx_cal_exception_event_group",	
				"size" => 6,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_cal_exception_event_mm",
			)
		),
		"fe_cruser_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.fe_cruser_id",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"fe_crgroup_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.fe_crgroup_id",
			"config" => Array (
				"type" => "group",
				"internal_type" => "db",
				"allowed" => "fe_groups",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),

		/* new */
		'type' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.type',
			'config' => Array (
				'type' => 'select',
				'size' => 1,
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.type.I.0', 0),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.type.I.1', 1),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.type.I.2', 2)
				),
				'default' => 0
			)
		),

		'ext_url' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.external',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),

		'page' => Array (
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.shortcut_page',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1'
			)
		),
		/* new */
	),
	"types" => Array (
		"0" => Array("showitem" => "title;;1;;,category_id,location,organizer;;2;;,start_date, start_time,end_date,end_time,type, description,--div--;Relations, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"),
		"1" => Array("showitem" => "title;;1;;,category_id,location,organizer;;2;;,start_date, start_time,end_date,end_time,type, page,--div--;Relations, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"),
		"2" => Array("showitem" => "title;;1;;,category_id,location,organizer;;2;;,start_date, start_time,end_date,end_time,type, ext_url,--div--;Relations, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"),
	),
	"palettes" => Array (
		'1' => Array('showitem' => 'hidden,starttime,endtime,fe_group'),
		'2' => Array('showitem' => 'location_id,organizer_id'),
	)
);

//************************************************************************************************
//
//************************************************************************************************

$TCA['tx_cal_category'] = Array (
	'ctrl' => $TCA['tx_cal_category']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,headercolor,bodycolor,headertextcolor,bodytextcolor'
	),
	'feInterface' => $TCA['tx_cal_category']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'unique, required',
			)
		),
		'headercolor' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.headercolor',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'wizards' => Array(
					'_PADDING' => 2,
					'color' => Array(
						'title' => 'Color:',
						'type' => 'colorbox',
						'dim' => '12x12',
						'tableStyle' => 'border:solid 1px black;',
						'script' => 'wizard_colorpicker.php',
						'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
					),
				),
				'eval' => 'trim,nospace',
			)
		),
		'bodycolor' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.bodycolor',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'wizards' => Array(
					'_PADDING' => 2,
					'color' => Array(
						'title' => 'Color:',
						'type' => 'colorbox',
						'dim' => '12x12',
						'tableStyle' => 'border:solid 1px black;',
						'script' => 'wizard_colorpicker.php',
						'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
					),
				),
				'eval' => 'trim,nospace',
			)
		),
		'headertextcolor' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.headertextcolor',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'wizards' => Array(
					'_PADDING' => 2,
					'color' => Array(
						'title' => 'Color:',
						'type' => 'colorbox',
						'dim' => '12x12',
						'tableStyle' => 'border:solid 1px black;',
						'script' => 'wizard_colorpicker.php',
						'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
					),
				),
				'eval' => 'trim,nospace',
			)
		),
		'bodytextcolor' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.bodytextcolor',
			'config' => Array (
				'type' => 'input',
				'size' => '7',
				'max' => '7',
				'wizards' => Array(
					'_PADDING' => 2,
					'color' => Array(
						'title' => 'Color:',
						'type' => 'colorbox',
						'dim' => '12x12',
						'tableStyle' => 'border:solid 1px black;',
						'script' => 'wizard_colorpicker.php',
						'JSopenParams' => 'height=300,width=250,status=0,menubar=0,scrollbars=1',
					),
				),
				'eval' => 'trim,nospace',
			)
		),
		"relation_cnt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_fe_user_category.fe_user",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "fe_users,fe_groups",	
				"size" => 6,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_cal_fe_user_category_mm",
			)
		),
		'type' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.type',
			'config' => Array (
				'type' => 'select',
				'size' => 1,
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_category.type.I.0', 0),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_category.type.I.1', 1),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_category.type.I.2', 2)
				),
				'default' => 0
			)
		),

		'ext_url' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.ext_url',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
				'wizards' => Array(
					'_PADDING' => 2,
					'link' => Array(
						'type' => 'popup',
						'title' => 'Link',
						'icon' => 'link_popup.gif',
						'script' => 'browse_links.php?mode=wizard',
						'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
					)
				)
			)
		),

		'ics_file' => Array (
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.ics_file',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'ics',	// Must be empty for disallowed to work.
				'max_size' => '10000',
				'uploadfolder' => 'uploads/tx_cal/ics',
				'show_thumbs' => '0',
				'size' => '1',
				'autoSizeMax' => '1',
				'maxitems' => '1',
				'minitems' => '0'
			)
		),
	),
	'types' => Array (
		"0" => Array("showitem" => "type,title;;1;;,relation_cnt"),
		"1" => Array("showitem" => "type,title;;1;;,relation_cnt,ext_url"),
		"2" => Array("showitem" => "type,title;;1;;,relation_cnt,ics_file"),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,headercolor,bodycolor;;;;,headertextcolor,bodytextcolor',)
	)
);

$TCA['tx_cal_exception_event_group'] = Array (
	'ctrl' => $TCA['tx_cal_exception_event_group']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,tx_cal_exception_event_cnt'
	),
	'feInterface' => $TCA['tx_cal_exception_event_group']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event_group.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		"exception_event_cnt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event_group.exception_event_cnt",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_cal_exception_event",	
				"size" => 6,	
				"minitems" => 0,
				"maxitems" => 100,	
				"MM" => "tx_cal_exception_event_group_mm",
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;;;2-2-2,color,hidden;;1;;3-3-3,exception_event_cnt')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '',)
	)
);

$TCA["tx_cal_exception_event"] = Array (
	"ctrl" => $TCA["tx_cal_exception_event"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,start_date,start_time,end_date,end_time,freq,byday,bymonthday,bymonth,until,count,end,intrval,ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until"
	),
	"feInterface" => $TCA["tx_cal_event"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (		
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"starttime" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["starttime"],
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"default" => "0",
				"checkbox" => "0"

			)
		),
		"endtime" => Array (		
			"exclude" => 1,	
			"label" => $LANG_GENERAL_LABELS["endtime"],
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "datetime",
				"default" => "0",
				"checkbox" => "0"

			)
		),
		"start_date" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.start_date",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"start_time" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.start_time",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "time",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"end_date" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.end_date",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"end_time" => Array (		
			"exclude" => 1,	
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.end_time",
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "time",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"freq" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.freq",		
			"config" => Array (
				"type" => "select",	
				"size" => "1",
				'items' => Array (
					Array('none','none'),
					Array('secondly','second'),
					Array('minutely','minute'),
					Array('hourly','hour'),
					Array('dayly','day'),
					Array('weekly','week'),
					Array('monthly','month'),
					Array('yearly','year'),
				),
			)
		),
		"byday" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.byday",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"bymonthday" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.bymonthday",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"bymonth" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.bymonth",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"until" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.until",		
			"config" => Array (
				"type" => "input",
				"size" => "12",
				"max" => "20",
				"eval" => "date",
				"default" => "0",
				"checkbox" => "0"
			)
		),
		"cnt" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.count",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
				"eval" => "num",
			)
		),
		"intrval" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.interval",		
			"config" => Array (
				"type" => "input",	
				"size" => "4",
				"eval" => "num",
				"default" => "1"
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden, starttime, endtime, start_date,start_time,end_date,end_time,title, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>