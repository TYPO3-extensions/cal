<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::allowTableOnStandardPages('tx_cal_event');
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

$TCA["tx_cal_event"] = Array (
	"ctrl" => Array (
		'requestUpdate' => 'calendar_id',
		"title" => "LLL:EXT:cal/locallang_db.php:tx_cal_event",		
		"label" => "start_date,start_time,title",
		"label_alt" => "start_date,start_time,title",
		"label_alt_force" => 1,	
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"starttime" => "starttime",
		"endtime" => "endtime",
		"description" => "description",
		"start_time" => "start_time",
		"end_time" => "end_time",
		"start_date" => "start_date",
		"end_date" => "end_date",
		"organizer" => "organizer",
		"freq" => "freq",
		"byday" => "byday",
		"bymonthday" => "bymonthday",
		"bymonth" => "bymonth",
		"until" => "until",
		"count" => "cnt",
		"interval" => "intrval",
		"sortby" => "start_date",
		"default_sortby" => "ORDER BY start_date DESC, start_time DESC",
		"delete" => "deleted",
		"notify" => "notify",
		/* new */
		'type' => 'type',
		'typeicon_column' => 'type',
		'typeicons' => Array (
			'1' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_events_intlnk.gif",
			'2' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_events_exturl.gif",
		),
		'dividers2tabs' => $confArr['noTabDividers']?FALSE:TRUE,		
		/* new */
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_events.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, starttime, endtime, start_date, start_time, end_date, end_time, relation_cnt, organizer, organizer_id, location, location_id, description, freq, byday, bymonthday, bymonth, until, count, interval, notify_cnt"
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_cal_exception_event');

$TCA["tx_cal_exception_event"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:cal/locallang_db.php:tx_cal_exception_event",		
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"starttime" => "starttime",
		"endtime" => "endtime",
		"start_time" => "start_time",
		"end_time" => "end_time",
		"start_date" => "start_date",
		"end_date" => "end_date",
		"freq" => "freq",
		"byday" => "byday",
		"bymonthday" => "bymonthday",
		"bymonth" => "bymonth",
		"until" => "until",
		"count" => "cnt",
		"interval" => "intrval",
		"sortby" => "start_date",
		"default_sortby" => "ORDER BY start_date DESC, start_time DESC",	
		"delete" => "deleted",
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_exception_event.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, starttime, endtime, start_date, start_time, end_date, end_time, relation_cnt, freq, byday, bymonthday, bymonth, until, count, interval"
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_cal_calendar');

$TCA['tx_cal_calendar'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'type' => 'type',
		'typeicon_column' => 'type',
		'typeicons' => Array (
			'1' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_calendar_exturl.gif",
			'2' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_calendar_ics.gif",
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_calendar.gif",
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_cal_category');

$TCA['tx_cal_category'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_category.gif",
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_cal_exception_event_group');

$TCA['tx_cal_exception_event_group'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event_group',
		'label' => "title",		
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_exception_event_group.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_cal_location');

$TCA['tx_cal_location'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_location.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'name',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_cal_organizer');

$TCA['tx_cal_organizer'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_cal_organizer.gif",
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'name',
	)
);

t3lib_div::loadTCA('tt_content');


// Jan 18032006 start
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_controller']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_controller']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_controller', 'FILE:EXT:cal/flexform_ds.xml');

// Jan 18032006 end

// initalize "context sensitive help" (csh)
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_event','EXT:cal/locallang_csh_txcalevent.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_calendar','EXT:cal/locallang_csh_txcalcal.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_category','EXT:cal/locallang_csh_txcalcat.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_exception_event','EXT:cal/locallang_csh_txcalexceptionevent.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_exception_event_group','EXT:cal/locallang_csh_txcalexceptioneventgroup.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_location','EXT:cal/locallang_csh_txcallocation.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_organizer','EXT:cal/locallang_csh_txcalorganizer.php');

t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts/','CSS-based tmpl');
t3lib_extMgm::addStaticFile($_EXTKEY,'view/static/css/','default CSS-styles');

$useLocationStructure = ($confArr['useLocationStructure']?$confArr['useLocationStructure']:'tx_cal_location');
$useOrganizerStructure = ($confArr['useOrganizerStructure']?$confArr['useOrganizerStructure']:'tx_cal_organizer');
if($useLocationStructure=="tx_tt_address"){
	$tempColumns = Array (
	    'tx_cal_controller_islocation' => Array (
	        'exclude' => 1,
	        'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location.islocation',
	        'config' => Array (
	            'type' => 'check',
	            'default' => 1,
	        )
	    ),
	);
	
	t3lib_div::loadTCA('tt_address');
	t3lib_extMgm::addTCAcolumns('tt_address',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('tt_address',
	    'tx_cal_controller_islocation,');
}
if($useOrganizerStructure=="tx_tt_address"){
	$tempColumns = Array (
	    'tx_cal_controller_isorganizer' => Array (
	        'exclude' => 1,
	        'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.isorganizer',
	        'config' => Array (
	            'type' => 'check',
	            'default' => 1,
	        )
	    ),
	);
	
	t3lib_div::loadTCA('tt_address');
	t3lib_extMgm::addTCAcolumns('tt_address',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('tt_address',
	    'tx_cal_controller_isorganizer,');
}


t3lib_extMgm::addPlugin(Array('LLL:EXT:cal/locallang_db.php:tt_content.list_type', $_EXTKEY.'_controller'),'list_type');
if (TYPO3_MODE=='BE')	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_cal_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'controller/class.tx_cal_wizicon.php';
?>