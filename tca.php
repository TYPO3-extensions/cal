<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

	// get extension confArr
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
	// page where records will be stored in that have been created with a wizard
$sPid = ($fTableWhere?'###STORAGE_PID###':'###CURRENT_PID###');
	// hide new localizations
$hideOrganizerTextfield = ($confArr['hideOrganizerTextfield']?'mergeIfNotBlank':'');
$hideLocationTextfield = ($confArr['hideLocationTextfield']?'mergeIfNotBlank':'');
$useLocationStructure = ($confArr['useLocationStructure']?$confArr['useLocationStructure']:'tx_cal_location');
$useOrganizerStructure = ($confArr['useOrganizerStructure']?$confArr['useOrganizerStructure']:'tx_cal_organizer');
//require_once(PATH_t3lib."class.t3lib_iconWorks.php");
switch ($useLocationStructure){
	case 'tx_tt_address':
		$useLocationStructure = 'tt_address';
		$locationOrderBy = "name";
		$addressLocationWhere = " AND tx_cal_controller_islocation=1 ";
	break;
	case 'tx_partner_main':
		$locationOrderBy = "label";
	break;
	default:
		$locationOrderBy = "name";
	break;
}
switch ($useOrganizerStructure){
	case 'tx_tt_address':
		$useOrganizerStructure = "tt_address";
		$organizerOrderBy = "name";
		$addressOrganizerWhere = " AND tx_cal_controller_isorganizer=1 ";
	break;
	case 'tx_partner_main':
		$organizerOrderBy = "label";
	break;
	default:
		$organizerOrderBy = "name";
	break;
}

$TCA["tx_cal_event"] = Array (
	"ctrl" => $TCA["tx_cal_event"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,category_id,title,start_date,start_time,end_date,end_time,organizer,location,description,image,imagecaption,freq,byday,bymonthday,bymonth,until,count,end,intrval,ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"
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
		'calendar_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.calendar',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_cal_calendar',
				'foreign_table_where' => 'ORDER BY tx_cal_calendar.title',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.createNewCategory',
						'icon' => 'EXT:cal/icon_tx_cal_calendar.gif',
						'params' => Array(
							'table'=>'tx_cal_calendar',
							'pid' => $sPid,
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.editCategory',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
					),
				),
			)
		),
		'category_id' => Array (
			'exclude' => 1,
			'displayCond' => 'FIELD:calendar_id:REQ:true',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.category',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_cal_category',
				'size' => 4,
				'minitems' => 1,
				'autoSizeMax' => 25,
				'maxitems' => 500,
				'MM' => 'tx_cal_event_category_mm',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.createNewCategory',
						'icon' => 'EXT:cal/icon_tx_cal_category.gif',
						'params' => Array(
							'table'=>'tx_cal_category',
							'pid' => $sPid,
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.editCategory',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
					),
				),
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
				'default' => mktime(0,0,0,date("m"),date("d"),date("Y")),
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
				'foreign_table' => $useOrganizerStructure,
				'foreign_table_where' => $addressOrganizerWhere.'ORDER BY '.$useOrganizerStructure.".".$organizerOrderBy,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.createNewCategory',
						'icon' => 'new_el.gif',//t3lib_iconWorks::getIcon($useOrganizerStructure),
						'params' => Array(
							'table'=>$useOrganizerStructure,
							'pid' => $sPid,
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.editCategory',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
					),
				),
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
				'foreign_table' => $useLocationStructure,
				'foreign_table_where' => $addressLocationWhere.'ORDER BY '.$useLocationStructure.".".$locationOrderBy,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.createNewCategory',
						'icon' => 'new_el.gif',//t3lib_iconWorks::getIcon($useLocationStructure),
						'params' => Array(
							'table'=> $useLocationStructure,
							'pid' => $sPid,
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:tt_news/locallang_tca.php:tt_news.editCategory',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
					),
				),
			)
		),
		"description" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.description",		
			"config" => Array (
				'type' => 'text',
	            'cols' => '40',    
	            'rows' => '6',
	            'wizards' => Array(
					'_PADDING' => 4,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_ttc.php:bodytext.W.RTE',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				)
			)
		),
		"freq" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.freq",		
			"config" => Array (
				"type" => "select",	
				"size" => "1",
				'items' => Array (
					Array("LLL:EXT:cal/locallang_db.php:frequency.none",'none'),
					Array("LLL:EXT:cal/locallang_db.php:frequency.day",'day'),
					Array("LLL:EXT:cal/locallang_db.php:frequency.week",'week'),
					Array("LLL:EXT:cal/locallang_db.php:frequency.month",'month'),
					Array("LLL:EXT:cal/locallang_db.php:frequency.year",'year'),
				),
			)
		),
		"byday" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_event.byday",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
//				"type" => "select",	
//				"size" => "5",
//				"maxitems" => "7",
//				"renderMode" => "singlebox",
//				"items" => Array (
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_all", "all"),
//					Array("---","--div--"),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_sunday",'su'),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_monday",'mo'),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_tuesday",'tu'),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_wednesday",'we'),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_thursday",'th'),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_friday",'fr'),
//					Array("LLL:EXT:cal/controller/locallang.php:l_daysofweek_lang_saturday",'sa'),
//				),
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
//				"type" => "select",	
//				"size" => "5",
//				"maxitems" => "12",
//				"renderMode" => "singlebox",
//				"items" => Array (
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_all", "all"),
//					Array("---","--div--"),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_January", 1),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_February", 2),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_March", 3),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_April", 4),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_May", 5),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_June", 6),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_July", 7),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_August", 8),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_September", 9),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_October", 10),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_November", 11),
//					Array("LLL:EXT:cal/controller/locallang.php:l_monthsofyear_lang_December", 12),
//				),
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
		'image' => Array (
			'exclude' => 1,
			'l10n_mode' => $l10n_mode_image,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => '1000',
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),
		'imagecaption' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.caption',
			'l10n_mode' => $l10n_mode,
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '3'
			)
		),
		'imagealttext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.imagealttext',
			'l10n_mode' => $l10n_mode,
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'imagetitletext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.imagetitletext',
			'l10n_mode' => $l10n_mode,
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "type, title;;1;;,calendar_id,category_id,".($hideLocationTextfield?'location_id':'location;;2;;').",".($hideOrganizerTextfield?'organizer_id':'organizer;;3;;').",start_date, start_time,end_date,end_time, description;;5;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image]:rte_transform[flag=rte_enabled|mode=ts],image,imagecaption;;4;;,--div--;Recurrence, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"),
		"1" => Array("showitem" => "type, title;;1;;,calendar_id,category_id,".($hideLocationTextfield?'location_id':'location;;2;;').",".($hideOrganizerTextfield?'organizer_id':'organizer;;3;;').",start_date, start_time,end_date,end_time, page,--div--;Recurrence, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"),
		"2" => Array("showitem" => "type, title;;1;;,calendar_id,category_id,".($hideLocationTextfield?'location_id':'location;;2;;').",".($hideOrganizerTextfield?'organizer_id':'organizer;;3;;').",start_date, start_time,end_date,end_time, ext_url,--div--;Recurrence, freq, byday, bymonthday, bymonth, until, cnt, intrval, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt"),
	),
	"palettes" => Array (
		'1' => Array('showitem' => 'hidden,starttime,endtime,fe_group'),
		'2' => Array('showitem' => 'location_id'),
		'3' => Array('showitem' => 'organizer_id'),
		'4' => Array('showitem' => 'imagealttext,imagetitletext'),
	)
);
$TCA["tx_cal_event"]['ctrl']['requestUpdate']='calendar_id';

$calIdString = "";
if(is_array($GLOBALS['HTTP_POST_VARS']['data']['tx_cal_event'])){
	$TCA["tx_cal_event"]['columns']['category_id']['config']['foreign_table_where'] = 'AND tx_cal_category.calendar_id = ###REC_FIELD_calendar_id###';
}else{
	$TCA["tx_cal_event"]['columns']['calendar_id']['config']['items'] = Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.none',0),
				);
}

//************************************************************************************************
//	CALENDAR
//************************************************************************************************

$TCA['tx_cal_calendar'] = Array (
	'ctrl' => $TCA['tx_cal_calendar']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle'
	),
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0',
			)
		),
		'title' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.title',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'unique, required',
			)
		),
		"owner" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_calendar.owner",		
			"config" => Array (
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'neg_foreign_table' => 'fe_groups',
				'MM' => 'tx_cal_calendar_user_group_mm',
				'size' => 4,
				'minitems' => 0,
				'autoSizeMax' => 25,
				'maxitems' => 500,
			)
		),
		'type' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.type',
			'config' => Array (
				'type' => 'select',
				'size' => 1,
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_calendar.type.I.0', 0),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_calendar.type.I.1', 1),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_calendar.type.I.2', 2)
				),
				'default' => 0
			)
		),

		'ext_url' => Array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.ext_url',
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
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.ics_file',
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
		"0" => Array("showitem" => "type,title;;1;;,owner"),
		"1" => Array("showitem" => "type,title;;1;;,owner,ext_url"),
		"2" => Array("showitem" => "type,title;;1;;,owner,ics_file"),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden',)
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

//************************************************************************************************
//	CATEGORY
//************************************************************************************************

$TCA['tx_cal_category'] = Array (
	'ctrl' => $TCA['tx_cal_category']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle'
	),
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0',
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
		'headerstyle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.headerstyle',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '16',
			)
		),
		'bodystyle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.bodystyle',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '16',
			)
		),
		"calendar_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:cal/locallang_db.php:tx_cal_category.calendar",		
			"config" => Array (
				'type' => 'select',
				'foreign_table' => 'tx_cal_calendar',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		
	),
	'types' => Array (
		"0" => Array("showitem" => "type,title;;1;;,calendar_id"),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,headerstyle,bodystyle',)
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

$TCA['tx_cal_organizer'] = Array (
	'ctrl' => $TCA['tx_cal_organizer']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,name,description, street,zip,city,phone,email,image,link'
	),
	'feInterface' => $TCA['tx_cal_organizer']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'name' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'street' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.street',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'zip' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'city' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'phone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'lower',
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/pics/tx_cal',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
        'link' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.link',
            'config' => Array (
                'type' => 'input',
                'size' => '25',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
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
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,name;;;;2-2-2,description;;;richtext[*]:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/pics/tx_cal/], street, zip,city,phone,email,image,link')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);

//************************************************************************************************
//
//************************************************************************************************
$TCA['tx_cal_location'] = Array (
	'ctrl' => $TCA['tx_cal_location']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden, name,description,street,zip,city,phone,email,image,link'
	),
	'feInterface' => $TCA['tx_cal_location']['feInterface'],
	'columns' => Array (
	
		'hidden' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'name' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.name',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'description' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.description',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
				'wizards' => Array(
					'_PADDING' => 2,
					'RTE' => Array(
						'notNewRecords' => 1,
						'RTEonly' => 1,
						'type' => 'script',
						'title' => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon' => 'wizard_rte2.gif',
						'script' => 'wizard_rte.php',
					),
				),
			)
		),
		'street' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.street',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'zip' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'city' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'phone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'lower',
			)
		),
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.image',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',
				'max_size' => 500,
				'uploadfolder' => 'uploads/pics/tx_cal',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
        'link' => Array (
            'exclude' => 0,
            'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.link',
            'config' => Array (
                'type' => 'input',
                'size' => '25',
                'max' => '255',
                'checkbox' => '',
                'eval' => 'trim',
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
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,name,description;;;richtext[*]:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/pics/tx_cal/], street, zip,city,phone,email,image,link')
	),
	'palettes' => Array (
		'1' => Array('showitem' => '')
	)
);
?>