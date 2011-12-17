<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// Include the class for getting custom labels.
if (TYPO3_MODE == 'BE') {
	include_once(t3lib_extMgm::extPath('cal').'res/class.tx_cal_labels.php');
	include_once(t3lib_extMgm::extPath('cal').'res/class.tx_cal_itemsProcFunc.php');
}

// Allow all calendar records on standard pages, in addition to SysFolders.
t3lib_extMgm::allowTableOnStandardPages('tx_cal_event');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_category');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_calendar');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_exception_event');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_exception_event_group');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_location');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_organizer');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_unknown_users');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_attendee');
t3lib_extMgm::allowTableOnStandardPages('tx_cal_fe_user_event_monitor_mm');

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

// TCA Definitions.
$TCA['tx_cal_event'] = Array (
	'ctrl' => Array (
		'requestUpdate' => 'calendar_id,freq,rdate_type,allday',
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_event',
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
		'typeicons' => Array (
			'1' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_events_intlnk.gif',
			'2' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_events_exturl.gif',
			'3' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_events_meeting.gif',
			'4' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_events_todo.gif',
		),
		'dividers2tabs' => $confArr['noTabDividers']?FALSE:TRUE,		
		'enablecolumns' => Array (		
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_events.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime, start_date, start_time, end_date, end_time, relation_cnt, organizer, organizer_id, organizer_pid, location, location_id, location_pid, description, freq, byday, bymonthday, bymonth, until, count, interval, rdate_type, rdate, notify_cnt'
	)
);


$TCA['tx_cal_category'] = Array (
	'ctrl' => Array (
		'requestUpdate' => 'calendar_id',
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
        'versioningWS' => TRUE,
        'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_category.gif',
//		'treeParentField' => 'calendar_id',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime',
	)
);


$TCA['tx_cal_calendar'] = Array (
	'ctrl' => Array (
		'requestUpdate' => 'activate_fnb',
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
			'1' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_calendar_exturl.gif',
			'2' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_calendar_ics.gif',
		),
        'versioningWS' => TRUE,
        'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_calendar.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime',
	)
);


$TCA['tx_cal_exception_event'] = Array (
	'ctrl' => Array (
		'requestUpdate' => 'calendar_id,freq,rdate_type,allday',
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event',		
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY start_date DESC',	
		'delete' => 'deleted',
		'enablecolumns' => Array (		
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
        'versioningWS' => TRUE,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_exception_event.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, title, starttime, endtime, start_date, end_date, relation_cnt, freq, byday, bymonthday, bymonth, until, count, interval'
	)
);


$TCA['tx_cal_exception_event_group'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_exception_event_group',
		'label' => 'title',		
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY title',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_exception_event_group.gif',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
        'versioningWS' => TRUE,
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'title',
	)
);


$TCA['tx_cal_location'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_location',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_location.gif',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
        'versioningWS' => TRUE,
        'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'name',
	)
);


$TCA['tx_cal_organizer'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_organizer.gif',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
        'versioningWS' => TRUE,
        'origUid' => 't3_origuid',
		'shadowColumnsForNewPlaceholders' => 'sys_language_uid,l18n_parent',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'name',
	)
);

$TCA['tx_cal_unknown_users'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_unknown_users',		
		'label' => 'email',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY email',	
		'delete' => 'deleted',
		'enablecolumns' => Array (
		),
        'versioningWS' => TRUE,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_unknown_users.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, email',
	)
);


$TCA['tx_cal_attendee'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.xml:tx_cal_attendee',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'uid',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_attendee.gif',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
        'versioningWS' => TRUE,
	),
);

$TCA['tx_cal_fe_user_event_monitor_mm'] = Array (
	'ctrl' => Array (
		'requestUpdate' => '',
		'title' => 'LLL:EXT:cal/locallang_db.php:tx_cal_fe_user_event.monitor',		
		'label' => 'tablenames',
		'label_alt' => 'tablenames,offset',
		'label_alt_force' => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_fe_user_event_monitor_mm.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => ''
	)
);

$TCA['tx_cal_event_deviation'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:cal/locallang_db.xml:tx_cal_event.deviation',		
		'label' => 'title',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'start_date',	
		'delete' => 'deleted',	
	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'res/icons/icon_tx_cal_event_deviation.gif',
		'enablecolumns' => Array (
			'disabled' => 'hidden',
		),
        'versioningWS' => TRUE,
	),
);


// enable label_userFunc only for TYPO3 v 4.1 and higher
if (t3lib_div::int_from_ver(TYPO3_version) >= 4001000) {
	$TCA['tx_cal_attendee']['ctrl']['label_userFunc']="tx_cal_labels->getAttendeeRecordLabel";
	$TCA['tx_cal_fe_user_event_monitor_mm']['ctrl']['label_userFunc']="tx_cal_labels->getMonitoringRecordLabel";
}

// Get the location and organizer structures.
$useLocationStructure = ($confArr['useLocationStructure']?$confArr['useLocationStructure']:'tx_cal_location');
$useOrganizerStructure = ($confArr['useOrganizerStructure']?$confArr['useOrganizerStructure']:'tx_cal_organizer');

if($useLocationStructure=='tx_tt_address'){
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
	t3lib_extMgm::addToAllTCAtypes('tt_address', 'tx_cal_controller_islocation,');
}

if($useOrganizerStructure=='tx_tt_address'){
	$tempColumns = Array (
	    'tx_cal_controller_isorganizer' => Array (
	        'exclude' => 1,
	        'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_organizer.isorganizer',
	        'config' => Array (
	            'type' => 'check',
	            'default' => 0,
	        )
	    ),
	);
	
	t3lib_div::loadTCA('tt_address');
	t3lib_extMgm::addTCAcolumns('tt_address',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('tt_address', 'tx_cal_controller_isorganizer,');
}



// Define the TCA for a checkbox to enable access control.
$tempColumns = Array (
	'tx_cal_enable_accesscontroll' => Array (		
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_enable_accesscontroll',
		'config' => Array (
            'type' => 'check',
            'default' => 0,
        )
	)
);

// Add the checkbox for backend users.
t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_users','tx_cal_enable_accesscontroll;;;;1-1-1', '0');
$TCA['be_users']['ctrl']['requestUpdate']=$TCA['be_users']['ctrl']['requestUpdate'].',tx_cal_enable_accesscontroll';

// Add the checkbox for backend groups.
t3lib_div::loadTCA('be_groups');
t3lib_extMgm::addTCAcolumns('be_groups',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_groups','tx_cal_enable_accesscontroll;;;;1-1-1');
$TCA['be_groups']['ctrl']['requestUpdate']=$TCA['be_groups']['ctrl']['requestUpdate'].',tx_cal_enable_accesscontroll';



// Define the TCA for the access control calendar selector.
$tempColumns = Array (
	'tx_cal_calendar' => Array (		
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar_accesscontroll',
		'displayCond' => 'FIELD:tx_cal_enable_accesscontroll:REQ:true',
		'config' => Array (
			'type' => 'select',
			'size' => 10,
			'minitems' => 0,
			'maxitems' => 100,
			'autoSizeMax' => 20,
            'itemListStyle' => 'height:130px;',
			'foreign_table' => 'tx_cal_calendar',
		)
	)
);

// Add the calendar selector for backend users.
t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_users','tx_cal_calendar;;;;1-1-1', '0');

// Add the calendar selector for backend groups.
t3lib_div::loadTCA('be_groups');
t3lib_extMgm::addTCAcolumns('be_groups',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_groups','tx_cal_calendar;;;;1-1-1');

require_once(t3lib_extMgm::extPath('cal').'res/class.tx_cal_treeview.php');

// Define the TCA for the access control category selector.
$tempColumns = Array (
	'tx_cal_category' => Array (		
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_category_accesscontroll',
		'displayCond' => 'FIELD:tx_cal_enable_accesscontroll:REQ:true',
		'config' => Array (
			'type' => 'select',
			'form_type' => 'user',
			'userFunc' => 'tx_cal_treeview->displayCategoryTree',
			'treeView' => 1,
			'size' => 20,
			'minitems' => 0,
			'maxitems' => 100,
			'autoSizeMax' => 20,
            'itemListStyle' => 'height:270px;',
			'foreign_table' => 'tx_cal_category',
		)
	)
);

// Add the category selecor for backend users.
t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_users','tx_cal_category;;;;1-1-1', '0');

// Add the category selector for backeng groups.
t3lib_div::loadTCA('be_groups');
t3lib_extMgm::addTCAcolumns('be_groups',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_groups','tx_cal_category;;;;1-1-1');

// Define the TCA for the access control calendar selector.
$tempColumns = Array (
	'tx_cal_calendar' => Array (		
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar_private',
		'config' => Array (
			'type' => 'group',	
			'internal_type' => 'db',	
			'allowed' => 'tx_cal_calendar',
			'minitems' => 0,
			'maxitems' => 1,
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
			),
		)
	),
	'tx_cal_calendar_subscription' => Array (		
		'exclude' => 1,
		'label' => 'LLL:EXT:cal/locallang_db.php:tx_cal_calendar_subscription',
		'config' => Array (
			'type' => 'group',	
			'internal_type' => 'db',	
			'allowed' => 'tx_cal_calendar',
			'minitems' => 0,
			'maxitems' => 99,
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
			),
		)
	)
);

// Add the calendar selector for backend users.
t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('fe_users','tx_cal_calendar,tx_cal_calendar_subscription;;;;1-1-1');

include_once(t3lib_extMgm::extPath($_EXTKEY).'res/class.tx_cal_treeview.php');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_controller', 'FILE:EXT:cal/res/flexform1_ds.xml');

// Set up the tt_content table to hide layout and select key, but show pi_flexform.
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_controller']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_controller']='pi_flexform';

// Add the plugin.
t3lib_extMgm::addPlugin(Array('LLL:EXT:cal/locallang_db.php:tt_content.list_type', $_EXTKEY.'_controller'),'list_type');

// Add Calendar Events to the "Insert Records" content element
t3lib_extMgm::addToInsertRecords('tx_cal_event');



// initalize 'context sensitive help' (csh)
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_event','EXT:cal/res/help/locallang_csh_txcalevent.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_calendar','EXT:cal/res/help/locallang_csh_txcalcal.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_category','EXT:cal/res/help/locallang_csh_txcalcat.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_exception_event','EXT:cal/res/help/locallang_csh_txcalexceptionevent.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_exception_event_group','EXT:cal/res/help/locallang_csh_txcalexceptioneventgroup.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_location','EXT:cal/res/help/locallang_csh_txcallocation.php');
t3lib_extMgm::addLLrefForTCAdescr('tx_cal_organizer','EXT:cal/res/help/locallang_csh_txcalorganizer.php');

// Add the static templates.
t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts/','Classic CSS-based template');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/ts_standard/','Standard CSS-based template');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/ajax/','AJAX-based template (Experimental!)');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/css/','Classic CSS styles');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/css_standard/','Standard CSS styles');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/rss_feed/','News-feed (RSS,RDF,ATOM)');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/ics/','ICS Export');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/fe-editing/','Fe-Editing');
if (TYPO3_MODE=="BE")	{
	$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
	if($extConf['useNewRecurringModel']){
		t3lib_extMgm::addModule("tools","calrecurrencegenerator","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
	}
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_cal_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'controller/class.tx_cal_wizicon.php';
}
?>