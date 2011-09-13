<?php
if (!defined ('TYPO3_MODE'))	die ('Access denied.');

require_once(t3lib_extMgm::extPath('cal').'res/class.tx_cal_itemsProcFunc.php');
require_once(t3lib_extMgm::extPath('cal').'res/class.tx_cal_customtca.php');


if(t3lib_extMgm::isLoaded('static_info_tables')) {
	require_once(t3lib_extMgm::extPath('cal').'res/class.user_staticinfotables_div.php');
}

// get extension confArr
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
// page where records will be stored in that have been created with a wizard
$sPid = '###CURRENT_PID###'; // storage pid????

// hide new localizations
$hideOrganizerTextfield = ($confArr['hideOrganizerTextfield']?'mergeIfNotBlank':'');
$hideLocationTextfield = ($confArr['hideLocationTextfield']?'mergeIfNotBlank':'');
$useLocationStructure = ($confArr['useLocationStructure']?$confArr['useLocationStructure']:'tx_cal_location');
$useOrganizerStructure = ($confArr['useOrganizerStructure']?$confArr['useOrganizerStructure']:'tx_cal_organizer');
$useTeaser = $confArr['useTeaser'];

switch ($useLocationStructure){
	case 'tx_tt_address':
		$useLocationStructure = 'tt_address';
		$locationOrderBy = 'name';
		$addressLocationWhere = ' AND tx_cal_controller_islocation=1 ';
	break;
	case 'tx_partner_main':
		$locationOrderBy = 'label';
	break;
	default:
		$locationOrderBy = 'name';
	break;
}
switch ($useOrganizerStructure){
	case 'tx_tt_address':
		$useOrganizerStructure = 'tt_address';
		$organizerOrderBy = 'name';
		$addressOrganizerWhere = ' AND tx_cal_controller_isorganizer=1 ';
	break;
	case 'tx_partner_main':
		$organizerOrderBy = 'label';
	break;
	case 'tx_feuser':
		$useOrganizerStructure = 'fe_users';
		$organizerOrderBy = 'username';
	default:
		$organizerOrderBy = 'name';
	break;
}

/* If date2cal is loaded, include it as a wizard */
if(t3lib_extMgm::isLoaded('date2cal')) {
	$date2CalTCA = Array (
		'type' => 'userFunc',
		'userFunc' => 'EXT:date2cal/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard',
		'evalValue' => 'date',
	);
	if(@is_dir(t3lib_extMgm::extPath('date2cal').'/src')){
		$date2CalTCA['userFunc'] = 'EXT:date2cal/src/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard';
	}
} else {
	$date2CalTCA = Array ();
}



$TCA['tx_cal_event'] = Array (
	'ctrl' => $TCA['tx_cal_event']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,category_id,title,start_date,start_time,allday,end_date,end_time,organizer,location,description,image,imagecaption,attachment,attachmentcaption,freq,byday,bymonthday,bymonth,until,count,rdate_type,rdate,end,intrval,ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt, shared_user_cnt,attendee'
	),
	'feInterface' => $TCA['tx_cal_event']['feInterface'],
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
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
				'eval' => 'required',
			)
		),
		'starttime' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'calendar_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.calendar',
			'config' => Array (
				'type' => 'select',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
				'itemsProcFunc' => 'tx_cal_itemsProcFunc->getRecords',
				'itemsProcFunc_config' => array(
					'table' => 'tx_cal_calendar',
					'orderBy' => 'tx_cal_calendar.title'
				),
				#'foreign_table' => 'tx_cal_calendar',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.createNew',
						'icon' => 'EXT:cal/res/icons/icon_tx_cal_calendar.gif',
						'params' => Array(
							'table'=>'tx_cal_calendar',
							'pid' => $sPid,
							'setValue' => 'set'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.edit',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
						'params' => Array(
							'table' => 'tx_cal_calendar',
						),
					),
				),
			)
		),
		'category_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.category',
			'config' => Array (
				'type' => 'select',	  
				'form_type' => 'user',
				'userFunc' => 'tx_cal_treeview->displayCategoryTree',
				'treeView' => 1,
				'size' => 12,
				'autoSizeMax' => 20,
				'itemListStyle' => 'height:300px;',
				'minitems' => 0,
				'maxitems' => 20,
				//'itemsProcFunc' => 'tx_cal_itemsProcFunc->getRecords',
				//'itemsProcFunc_config' => array(
				//	'table' => 'tx_cal_category',
				//),
				'foreign_table' => 'tx_cal_category',
				//'foreign_table_where' => $limitViewOnlyToPidsWhere?' AND tx_cal_category'.$limitViewOnlyToPidsWhere:' AND tx_cal_category.sys_language_uid IN (-1,0)',	
				'MM' => 'tx_cal_event_category_mm',
					
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.createNew',
						'icon' => 'EXT:cal/res/icons/icon_tx_cal_category.gif',
						'params' => Array(
							'table'=>'tx_cal_category',
							'pid' => $sPid,
							'setValue' => 'append'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.edit',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=500,width=660,status=0,menubar=0,scrollbars=1',
						'params' => Array(
							'table' => 'tx_cal_category',
						),
					),
				),
			),
		),
		'start_date' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.start_date',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'required,tx_cal_dateeval',
				'wizards' => Array (
					'calendar' => $date2CalTCA
				),
			)
		),
		'allday' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.allday',
			'config' => Array (
				'type' => 'check',
				'default' => 0,
			)
		),
		'start_time' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.start_time',
			'displayCond' => 'FIELD:allday:!=:1',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'time',
				'default' => '0',
			)
		),
		'end_date' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.end_date',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'required,tx_cal_dateeval',
				'wizards' => Array (
					'calendar' => $date2CalTCA
				),
			)
		),
		'end_time' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.end_time',
			'displayCond' => 'FIELD:allday:!=:1',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'time',
				'default' => '0',
			)
		),
		'organizer' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.organizer',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128'
			)
		),
		'organizer_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.organizer_id',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.none',0),
				),
				'itemsProcFunc' => 'tx_cal_itemsProcFunc->getRecords',
				'itemsProcFunc_config' => array(
					'table' => $useOrganizerStructure,
					'where' => $addressOrganizerWhere,
					'orderBy' => $useOrganizerStructure.'.'.$organizerOrderBy
				),
				#'foreign_table' => $useOrganizerStructure,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.createNew',
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
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.edit',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
						'params' => Array(
							'table' => $useOrganizerStructure,
						),
					),
				),
			)
		),
		'organizer_pid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.organizer_pid',
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
		'organizer_link' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.organizer_link',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '128',
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
		'location' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.location',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			)
		),
		'location_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.location_id',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.none',0),
				),
				'itemsProcFunc' => 'tx_cal_itemsProcFunc->getRecords',
				'itemsProcFunc_config' => array(
					'table' => $useLocationStructure,
					'where' => $addressLocationWhere,
					'orderBy' => $useLocationStructure.'.'.$locationOrderBy
				),
				#'foreign_table' => $useLocationStructure,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.createNew',
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
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.edit',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
						'params' => Array(
							'table' => $useLocationStructure,
						),
					),
				),
			)
		),
		'location_pid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.location_pid',
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
		'location_link' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.location_link',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '128',
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
		'teaser' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.teaser',		
			'config' => Array (
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
		'description' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.description',		
			'config' => Array (
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
		'freq' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.freq',		
			'config' => Array (
				'type' => 'select', 
				'size' => '1',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:frequency.none','none'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.day','day'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.week','week'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.month','month'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.year','year'),
				),
			)
		),
		'byday' => Array (
			'exclude' => 1,
			'displayCond' => 'FIELD:freq:IN:week,month,year',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.byday',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			),
		),
		'bymonthday' => Array (		
			'exclude' => 1,
			'displayCond' => 'FIELD:freq:IN:month,year',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonthday',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			)
		),
		'bymonth' => Array (		
			'exclude' => 1,
			'displayCond' => 'FIELD:freq:IN:month,year',	
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonth',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			),
		),
		'until' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.until',
			'displayCond' => 'FIELD:freq:IN:day,week,month,year',	
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'tx_cal_dateeval',
				'wizards' => Array (
					'calendar' => $date2CalTCA
				),
			)
		),
		'cnt' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.count',
			'displayCond' => 'FIELD:freq:IN:day,week,month,year',		
			'config' => Array (
				'type' => 'input',	
				'size' => '4',
				'eval' => 'num',
				'checkbox' => '0'
			)
		),
		'intrval' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.interval',
			'displayCond' => 'FIELD:freq:IN:day,week,month,year',	
			'config' => Array (
				'type' => 'input',	
				'size' => '4',
				'eval' => 'num',
				'default' => '1',
			)
		),
		'rdate_type' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.rdate_type',
			'config' => Array (
				'type' => 'select',
				'size' => 1,
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:rdate_type.none', 'none'),
					Array('LLL:EXT:cal/locallang_db.php:rdate_type.date', 'date'),
					Array('LLL:EXT:cal/locallang_db.php:rdate_type.date_time', 'date_time'),
					Array('LLL:EXT:cal/locallang_db.php:rdate_type.period', 'period'),
				),
				'default' => 0
			)
		),
		'rdate' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.rdate',
			'displayCond' => 'FIELD:rdate_type:IN:date_time,date,period',
			'config' => Array (
				'type' => 'text',
				'cols' => '40',	   
				'rows' => '2',
			)
		),
		'monitor_cnt' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_fe_user_event.monitor',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_users,fe_groups,tx_cal_unknown_users',	
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_fe_user_event_monitor_mm',
			)
		),
		'exception_cnt' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.exception',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_cal_exception_event,tx_cal_exception_event_group', 
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_exception_event_mm',
			)
		),
		'fe_cruser_id' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.fe_cruser_id',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_users',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'fe_crgroup_id' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.fe_crgroup_id',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'fe_groups',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		
		'shared_user_cnt' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.shared_user',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_users,fe_groups',	
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_event_shared_user_mm',
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
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.type.I.2', 2),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.type.I.3', 3)
				),
				'default' => 0
			)
		),

		'ext_url' => Array (
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.external',
			'config' => Array (
				'type' => 'input',
				'size' => '40',
				'max' => '256',
				'eval' => 'required',
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
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.shortcut_page',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1',
				'eval' => 'required',
			)
		),
		/* new */
		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.images',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => '1000',
				'uploadfolder' => 'uploads/tx_cal/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),
		'imagecaption' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.caption',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '3'
			)
		),
		'imagealttext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.imagealttext',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'imagetitletext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.imagetitletext',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'attachment' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_ttc.php:media',
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',	// Must be empty for disallowed to work.
				'disallowed' => 'php,php3',
				'max_size' => '10000',
				'uploadfolder' => 'uploads/tx_cal/media',
				'show_thumbs' => '1',
				'size' => '4',
				'maxitems' => '10',
				'minitems' => '0'
			)
		),
		'attachmentcaption' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.caption',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '3'
			)
		),
		'attendee' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.attendee',
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_cal_attendee', 
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_event_attendee_mm',
				'wizards' => Array(
					'_PADDING' => 2,
					'_VERTICAL' => 1,
					'add' => Array(
						'type' => 'script',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_attendee.createNew',
						'icon' => 'new_el.gif',//t3lib_iconWorks::getIcon($useLocationStructure),
						'params' => Array(
							'table'=> 'tx_cal_attendee',
							'pid' => $sPid,
							'setValue' => 'append'
						),
						'script' => 'wizard_add.php',
					),
					'edit' => Array(
						'type' => 'popup',
						'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_attendee.edit',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=600,width=525,status=0,menubar=0,scrollbars=1',
					),
				),
			)
		),
		'send_invitation' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.send_invitation',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_cal_event',
				'foreign_table_where' => 'AND tx_cal_event.uid=###REC_FIELD_l18n_parent### AND tx_cal_event.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => '--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.general_sheet,type, title;;1;;,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.start;5,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.end;6,calendar_id,category_id,'.($useTeaser ? 'teaser;;;richtext:rte_transform[flag=rte_enabled|mode=ts_css],' : '').'description;;5;richtext:rte_transform[flag=rte_enabled|mode=ts_css],--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.location_sheet,'.($hideLocationTextfield?'location_id,location_pid,location_link':'location,location_id,location_pid,location_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.organizer_sheet,'.($hideOrganizerTextfield?'organizer_id,organizer_pid,organizer_link':'organizer,organizer_id,organizer_pid,organizer_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.files_sheet,image;;4;;;,imagecaption,attachment,attachmentcaption,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.other_sheet, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt, shared_user_cnt'),
		'1' => Array('showitem' => '--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.general_sheet,type, page,title;;1;;,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.start;5,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.end;6,calendar_id,category_id,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, --div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.location_sheet,'.($hideLocationTextfield?'location_id,location_pid,location_link':'location,location_id,location_pid,location_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.organizer_sheet,'.($hideOrganizerTextfield?'organizer_id,organizer_pid,organizer_link':'organizer,organizer_id,organizer_pid,organizer_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.other_sheet, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt, shared_user_cnt'),
		'2' => Array('showitem' => '--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.general_sheet,type, ext_url,title;;1;;,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.start;5,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.end;6,calendar_id,category_id,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;, --div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.location_sheet,'.($hideLocationTextfield?'location_id,location_pid,location_link':'location,location_id,location_pid,location_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.organizer_sheet,'.($hideOrganizerTextfield?'organizer_id,organizer_pid,organizer_link':'organizer,organizer_id,organizer_pid,organizer_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.other_sheet, monitor_cnt, ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until,exception_cnt, shared_user_cnt'),
		'3' => Array('showitem' => '--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.general_sheet,type, title;;1;;,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.start;5,--palette--;LLL:EXT:cal/locallang_db.php:tx_cal_event.end;6,calendar_id,category_id,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.attendance_sheet,attendee,send_invitation,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.recurrence_sheet, freq;;2;;, byday, bymonthday, bymonth, rdate_type;;7;;,--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.location_sheet,'.($hideLocationTextfield?'location_id,location_pid,location_link':'location,location_id,location_pid,location_link').',--div--;LLL:EXT:cal/locallang_db.xml:tx_cal_event.organizer_sheet,'.($hideOrganizerTextfield?'organizer_id,organizer_pid,organizer_link':'organizer,organizer_id,organizer_pid,organizer_link')),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'),
		'2' => Array('showitem' => 'until, cnt, intrval', 'canNotCollapse' => 1),
		'4' => Array('showitem' => 'imagealttext,imagetitletext'),
		'5' => Array('showitem' => 'start_date,start_time,allday','canNotCollapse' => 1),
		'6' => Array('showitem' => 'end_date,end_time','canNotCollapse' => 1),
		'7' => Array('showitem' => 'rdate','canNotCollapse' => 1),
	)
);

//************************************************************************************************
//	CALENDAR
//************************************************************************************************

$TCA['tx_cal_calendar'] = Array (
	'ctrl' => $TCA['tx_cal_calendar']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle,activate_fnb,fnb_user_cnt'
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
				'max' => '128',
				'eval' => 'unique, required',
			)
		),
		'owner' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.owner',		
			'config' => Array (
				'type' => 'select',
				'MM' => 'tx_cal_calendar_user_group_mm',
				'foreign_table' => 'fe_users',
				'neg_foreign_table' => 'fe_groups',
				'size' => 4,
				'minitems' => 0,
				'autoSizeMax' => 25,
				'maxitems' => 500,
			)
		),
		'activate_fnb' => Array (
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.activate_fnb',
			'config' => Array (
				'type' => 'check'
			)
		),
		'fnb_user_cnt' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.fb_users_groups',		
			'config' => Array (
				'type' => 'select',
				'MM' => 'tx_cal_calendar_fnb_user_group_mm',
				'foreign_table' => 'fe_users',
				'neg_foreign_table' => 'fe_groups',
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
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
				'allowed' => 'ics', // Must be empty for disallowed to work.
				'max_size' => '10000',
				'uploadfolder' => 'uploads/tx_cal/ics',
				'show_thumbs' => '0',
				'size' => '1',
				'autoSizeMax' => '1',
				'maxitems' => '1',
				'minitems' => '0'
			)
		),
		
		'refresh' => Array (
			'exclude' => 1,
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar.refresh',
			'displayCond' => 'EXT:gabriel:LOADED:true',
			'config' => Array (
				'type' => 'input',
				'size' => '6',
				'max' => '4',
				'eval' => 'num',
				'default' => '60',
			),
		),
		
		'md5' => Array (
			'config' => Array (
				'type' => 'passthrough',
			),
		),
		'headerstyle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.headerstyle',
			'config' => Array (
				'type' => 'user',
				'userFunc' => 'tx_cal_customtca->getHeaderStyles',
			)
		),
		'bodystyle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.bodystyle',
			'config' => Array (
				'type' => 'user',
				'userFunc' => 'tx_cal_customtca->getBodyStyles',
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_cal_calendar',
				'foreign_table_where' => 'AND tx_cal_calendar.uid=###REC_FIELD_l18n_parent### AND tx_cal_calendar.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'type,title;;1;;,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt'),
		'1' => Array('showitem' => 'type,title;;1;;,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,ext_url,refresh'),
		'2' => Array('showitem' => 'type,title;;1;;,owner,headerstyle,bodystyle,activate_fnb,fnb_user_cnt,ics_file,refresh'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label',)
	)
);

if($confArr['useRecordSelector']) {
	unset($TCA['tx_cal_calendar']['columns']['owner']['config']['foreign_table']);
	unset($TCA['tx_cal_calendar']['columns']['owner']['config']['neg_foreign_table']);

	$TCA['tx_cal_calendar']['columns']['owner']['config']['type'] = 'group';
	$TCA['tx_cal_calendar']['columns']['owner']['config']['internal_type'] = 'db';
	$TCA['tx_cal_calendar']['columns']['owner']['config']['allowed'] = 'fe_users,fe_groups';
	
	unset($TCA['tx_cal_calendar']['columns']['fnb_user_cnt']['config']['foreign_table']);
	unset($TCA['tx_cal_calendar']['columns']['fnb_user_cnt']['config']['neg_foreign_table']);

	$TCA['tx_cal_calendar']['columns']['fnb_user_cnt']['config']['type'] = 'group';
	$TCA['tx_cal_calendar']['columns']['fnb_user_cnt']['config']['internal_type'] = 'db';
	$TCA['tx_cal_calendar']['columns']['fnb_user_cnt']['config']['allowed'] = 'fe_users,fe_groups';
}

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
				'max' => '128',
				'eval' => 'required',
			)
		),
		'exception_event_cnt' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event_group.exception_event_cnt',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'tx_cal_exception_event',	
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_exception_event_group_mm',
			)
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;1;;,color,exception_event_cnt')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,t3ver_label',)
	)
);

//************************************************************************************************
//	CATEGORY
//************************************************************************************************

$TCA['tx_cal_category'] = Array (
	'ctrl' => $TCA['tx_cal_category']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,headerstyle,bodystyle,calendar_id,single_pid,shared_user_allowed,notification_emails'
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
				'max' => '128',
				'eval' => 'required',
			)
		),
		'headerstyle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.headerstyle',
			'config' => Array (
				'type' => 'user',
				'userFunc' => 'tx_cal_customtca->getHeaderStyles',
			)
		),
		'bodystyle' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.bodystyle',
			'config' => Array (
				'type' => 'user',
				'userFunc' => 'tx_cal_customtca->getBodyStyles',
			)
		),
		'calendar_id' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.calendar',		
			'config' => Array (
				'type' => 'select',
				'itemsProcFunc' => 'tx_cal_itemsProcFunc->getRecords',
				'itemsProcFunc_config' => array(
					'table' => 'tx_cal_calendar',
					'orderBy' => 'tx_cal_calendar.title'
				),
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_event.none',0),
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'parent_category' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.parent_category',
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_cal_treeview->displayCategoryTree',
				'treeView' => 1,
				'size' => 1,
				'autoSizeMax' => 20,
				'itemListStyle' => 'height:300px;',
				'minitems' => 0,
				'maxitems' => 2,
				'foreign_table' => 'tx_cal_category',
			),
		),
		'shared_user_allowed' => Array (
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.shared_user_allowed',
			'config' => Array (
				'type' => 'check'
			)
		),
		'single_pid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.single_pid',
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
		'notification_emails' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category.notification_emails',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_cal_category',
				'foreign_table_where' => 'AND tx_cal_category.uid=###REC_FIELD_l18n_parent### AND tx_cal_category.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'type,title;;1;;,calendar_id,parent_category,shared_user_allowed,single_pid,notification_emails'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label,headerstyle,bodystyle')
	)
);


$TCA['tx_cal_exception_event'] = Array (
	'ctrl' => $TCA['tx_cal_exception_event']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,title,start_date,end_date,freq,byday,bymonthday,bymonth,until,count,end,intrval,ex_freq, ex_byday, ex_bymonthday, ex_bymonth, ex_until'
	),
	'feInterface' => $TCA['tx_cal_exception_event']['feInterface'],
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
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			)
		),
		'starttime' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'

			)
		),
		'endtime' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'datetime',
				'default' => '0',
				'checkbox' => '0'

			)
		),
		'start_date' => Array (		
			'exclude' => 1, 
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.start_date',
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'required,tx_cal_dateeval',
				'wizards' => Array (
					'calendar' => $date2CalTCA
				),
			)
		),
		'end_date' => Array (		
			'config' => Array (
				'type' => 'passthrough',
			)
		),

		'freq' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.freq',		
			'config' => Array (
				'type' => 'select', 
				'size' => '1',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:frequency.none','none'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.day','day'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.week','week'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.month','month'),
					Array('LLL:EXT:cal/locallang_db.php:frequency.year','year'),
				),
			)
		),

		'byday' => Array (
			'exclude' => 1,
			'displayCond' => 'FIELD:freq:IN:week,month,year',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.byday',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			),
		),


		'bymonthday' => Array (		
			'exclude' => 1,
			'displayCond' => 'FIELD:freq:IN:month,year',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.bymonthday',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			)
		),

		'bymonth' => Array (		
			'exclude' => 1,
			'displayCond' => 'FIELD:freq:IN:month,year',	
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.bymonth',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '128',
			),
		),
		
		'until' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.until',
			'displayCond' => 'FIELD:freq:IN:day,week,month,year',	
			'config' => Array (
				'type' => 'input',
				'size' => '12',
				'max' => '20',
				'eval' => 'tx_cal_dateeval',
				'wizards' => Array (
					'calendar' => $date2CalTCA
				),
			)
		),
		
		'cnt' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.count',
			'displayCond' => 'FIELD:freq:IN:day,week,month,year',		
			'config' => Array (
				'type' => 'input',	
				'size' => '4',
				'eval' => 'num',
				'checkbox' => '0'
			)
		),
		
		'intrval' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event.interval',
			'displayCond' => 'FIELD:freq:IN:day,week,month,year',	
			'config' => Array (
				'type' => 'input',	
				'size' => '4',
				'eval' => 'num',
				'default' => '1',
			)
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'title;;1;;,start_date,end_date, freq;;2;;, byday, bymonthday, bymonth, monitor_cnt'),
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,t3ver_label'),
		'2' => Array('showitem' => 'until, cnt, intrval', 'canNotCollapse' => 1),
	)
);

$TCA['tx_cal_organizer'] = Array (
	'ctrl' => $TCA['tx_cal_organizer']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,name,description, street,zip,city,country_zone,country,phone,fax,email,image,link,shared_user_cnt'
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
				'max' => '128',
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
				'max' => '128',
			)
		),
		'zip' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '15',
			)
		),
		'city' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '128',
			)
		),
		'country_zone' => Array (
			'exclude' => 1,
			'displayCond' => 'EXT:static_info_tables:LOADED:true',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.countryzone',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'itemsProcFunc' => 'user_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array (
					'table' => 'static_country_zones',
					'where' => '1 AND zn_country_iso_3="###REC_FIELD_country###"',
					'indexField' => 'zn_code',
					'prependHotlist' => 1,
					'hotlistLimit' => 5,
					'hotlistApp' => 'tx_cal',
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'country' => Array (
			'exclude' => 1,
			'displayCond' => 'EXT:static_info_tables:LOADED:true',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.country',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'itemsProcFunc' => 'user_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array (
					'table' => 'static_countries',
					'indexField' => 'cn_iso_3',
					'prependHotlist' => 1,
					'hotlistLimit' => 5,
					'hotlistApp' => 'tx_cal',
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'phone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '24',
			)
		),
		'fax' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.fax',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '24',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '64',
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
				'uploadfolder' => 'uploads/tx_cal/pics',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'imagecaption' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.caption',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '3'
			)
		),
		'imagealttext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.imagealttext',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'imagetitletext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.imagetitletext',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'link' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.link',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '128',
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
		'shared_user_cnt' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.shared_user',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_users,fe_groups',	
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_organizer_shared_user_mm',
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_cal_organizer',
				'foreign_table_where' => 'AND tx_cal_organizer.uid=###REC_FIELD_l18n_parent### AND tx_cal_organizer.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'name;;1;;2-2-2,description;;;richtext:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/tx_cal/pics/], street, city, country, country_zone, zip, phone,fax,email,image;;2;;;,imagecaption,link,shared_user_cnt')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'),
		'2' => Array('showitem' => 'imagealttext,imagetitletext'),
	)
);

//************************************************************************************************
//
//************************************************************************************************
$TCA['tx_cal_location'] = Array (
	'ctrl' => $TCA['tx_cal_location']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden, name,description,street,zip,city,country,phone,fax,email,image,link,shared_user_cnt'
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
				'max' => '128',
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
				'max' => '128',
			)
		),
		'zip' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.zip',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '15',
			)
		),
		'city' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.city',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '128',
			)
		),
		'country_zone' => Array (
			'exclude' => 1,
			'displayCond' => 'EXT:static_info_tables:LOADED:true',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.countryzone',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'itemsProcFunc' => 'user_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array (
					'table' => 'static_country_zones',
					'where' => '1 AND zn_country_iso_3="###REC_FIELD_country###"',
					'indexField' => 'zn_code',
					'prependHotlist' => 1,
					'hotlistLimit' => 5,
					'hotlistApp' => 'tx_cal',
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			),
		),
		'country' => Array (
			'exclude' => 1,
			'displayCond' => 'EXT:static_info_tables:LOADED:true',
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.country',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('',0),
				),
				'itemsProcFunc' => 'user_staticinfotables_div->selectItemsTCA',
				'itemsProcFunc_config' => array (
					'table' => 'static_countries',
					'indexField' => 'cn_iso_3',
					'prependHotlist' => 1,
					'hotlistLimit' => 5,
					'hotlistApp' => 'tx_cal',
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'phone' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.phone',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '24',
			)
		),
		'fax' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.fax',
			'config' => Array (
				'type' => 'input',
				'size' => '15',
				'max' => '24',
			)
		),
		'email' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.email',
			'config' => Array (
				'type' => 'input',
				'size' => '30',
				'max' => '64',
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
				'uploadfolder' => 'uploads/tx_cal/pics',
				'show_thumbs' => 1,
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'imagecaption' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.caption',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '3'
			)
		),
		'imagealttext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.imagealttext',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'imagetitletext' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.imagetitletext',
			'l10n_mode' => 'mergeIfNotBlank',
			'config' => Array (
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'link' => Array (
			'exclude' => 0,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.link',
			'config' => Array (
				'type' => 'input',
				'size' => '25',
				'max' => '128',
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
		'shared_user_cnt' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event.shared_user',		
			'config' => Array (
				'type' => 'group',	
				'internal_type' => 'db',	
				'allowed' => 'fe_users,fe_groups',	
				'size' => 6,	
				'minitems' => 0,
				'maxitems' => 100,	
				'MM' => 'tx_cal_location_shared_user_mm',
			)
		),
		'sys_language_uid' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => Array(
					Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('', 0),
				),
				'foreign_table' => 'tx_cal_location',
				'foreign_table_where' => 'AND tx_cal_location.uid=###REC_FIELD_l18n_parent### AND tx_cal_location.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => Array(
			'config'=>array(
				'type'=>'passthrough')
		),
		't3ver_label' => Array (
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => Array (
				'type'=>'none',
				'cols' => 27
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'name;;1;;2-2-2,description;;;richtext:rte_transform[mode=ts_images-ts_reglinks|imgpath=uploads/tx_cal/pics/], street, city, country, country_zone, zip, phone,fax, email,image;;2;;;,imagecaption,link,shared_user_cnt')
	),
	'palettes' => Array (
		'1' => Array('showitem' => 'hidden,l18n_parent,sys_language_uid,t3ver_label'),
		'2' => Array('showitem' => 'imagealttext,imagetitletext'),
	)
);

$TCA['tx_cal_unknown_users'] = Array (
	'ctrl' => $TCA['tx_cal_unknown_users']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,email'
	),
	'feInterface' => $TCA['tx_cal_unknown_users']['feInterface'],
	'columns' => Array (
		'hidden' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'email' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_unknown_users.email',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '64',
				'eval' => 'required',
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,email')
	),
	'palettes' => Array (
		'1' => Array(''),
	)
);

$TCA['tx_cal_attendee'] = Array (
	'ctrl' => $TCA['tx_cal_attendee']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,fe_user_id,email,attendance,status'
	),
	'feInterface' => $TCA['tx_cal_attendee']['feInterface'],
	'columns' => Array (
		'hidden' => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'fe_user_id' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_attendee.fe_user_id',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'fe_users',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.fe_users.0',''),
				),
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
		'email' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_attendee.email',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',
				'max' => '64',
				'eval' => 'lower',
			)
		),
		'attendance' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_attendee.attendance',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.attendance.NON','NON'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.attendance.OPT-PARTICIPANT','OPT-PARTICIPANT'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.attendance.REQ-PARTICIPANT','REQ-PARTICIPANT'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.attendance.CHAIR','CHAIR'),
				),	
				'size' => '1',
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
		'status' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status',		
			'config' => Array (
				'type' => 'select',
				'items' => Array (
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status.0','0'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status.NEEDS-ACTION','NEEDS-ACTION'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status.ACCEPTED','ACCEPTED'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status.DECLINE','DECLINE'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status.TENTATIVE','TENTATIVE'),
					Array('LLL:EXT:cal/locallang_db.php:tx_cal_attendee.status.DELEGATED','DELEGATED'),
				),	
				'size' => '1',
				'minitems' => 1,
				'maxitems' => 1,
			)
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden,fe_user_id,email,attendance,status')
	),
	'palettes' => Array (
		'1' => Array(''),
	)
);

/* If wec_map is present, define the address fields */
if(t3lib_extMgm::isLoaded('wec_map')) {
	t3lib_div::loadTCA('tx_cal_location');
	
	$TCA['tx_cal_location']['ctrl']['EXT']['wec_map'] = array (
		'isMappable' => 1,
		'addressFields' => array (
			'street' => 'street',
			'city' => 'city',
			'state' => 'country_zone',
			'zip' => 'zip',
			'country' => 'country',
		),
	);
	
	$geocodeTCA = array (
		'tx_wecmap_geocode' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:wec_map/locallang_db.xml:berecord_geocodelabel',
			'config' => array(
				'type' => 'passthrough',
				'form_type' => 'user',
				'userFunc' => 'tx_wecmap_backend->checkGeocodeStatus',
			),
		),
	);
	
	t3lib_extMgm::addTCAcolumns('tx_cal_location', $geocodeTCA, 1);
	$TCA['tx_cal_location']['interface']['showRecordFieldList'] .= ',tx_wecmap_geocode';
	t3lib_extMgm::addToAllTCAtypes('tx_cal_location', 'tx_wecmap_geocode');
	
	$mapTCA = array (
		'tx_wecmap_map' => array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:wec_map/locallang_db.xml:berecord_maplabel',		
			'config' => array (
				'type' => 'passthrough',
				'form_type' => 'user',
				'userFunc' => 'tx_wecmap_backend->drawMap',
			),
		),
	);
		
	t3lib_extMgm::addTCAcolumns('tx_cal_location', $mapTCA, 1);
	$TCA['tx_cal_location']['interface']['showRecordFieldList'] .= ',tx_wecmap_map';
	t3lib_extMgm::addToAllTCAtypes('tx_cal_location', 'tx_wecmap_map');
}


if($confArr['newRecurUI'] && (t3lib_div::int_from_ver(TYPO3_version) >= 4001000)) {
	require_once(t3lib_extMgm::extPath('cal').'res/class.tx_cal_customtca.php');
	
	$TCA['tx_cal_event']['columns']['byday']['label'] = 'LLL:EXT:cal/locallang_db.php:tx_cal_event.byday_short';
	$TCA['tx_cal_event']['columns']['byday']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->byDay',
	);
	
	$TCA['tx_cal_event']['columns']['bymonthday']['label'] = 'LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonthday_short';
	$TCA['tx_cal_event']['columns']['bymonthday']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->byMonthDay',
	);
	
	$TCA['tx_cal_event']['columns']['bymonth']['label'] = 'LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonth_short';
	$TCA['tx_cal_event']['columns']['bymonth']['displayCond'] = 'FIELD:freq:IN:year';
	$TCA['tx_cal_event']['columns']['bymonth']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->byMonth',
	);	
	
	$TCA['tx_cal_exception_event']['columns']['byday']['label'] = 'LLL:EXT:cal/locallang_db.php:tx_cal_event.byday_short';
	$TCA['tx_cal_exception_event']['columns']['byday']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->byDay',
	);
	
	$TCA['tx_cal_event']['columns']['rdate']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->rdate',
	);
	
	$TCA['tx_cal_exception_event']['columns']['bymonthday']['label'] = 'LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonthday_short';
	$TCA['tx_cal_exception_event']['columns']['bymonthday']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->byMonthDay',
	);
	
	$TCA['tx_cal_exception_event']['columns']['bymonth']['label'] = 'LLL:EXT:cal/locallang_db.php:tx_cal_event.bymonth_short';
	$TCA['tx_cal_exception_event']['columns']['bymonth']['displayCond'] = 'FIELD:freq:IN:year';
	$TCA['tx_cal_exception_event']['columns']['bymonth']['config'] = Array (
		'type' => 'user',
		'userFunc' => 'tx_cal_customtca->byMonth',
	);	
	
}

?>