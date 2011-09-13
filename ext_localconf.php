<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'controller/class.tx_cal_controller.php','_controller','list_type',1);
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_cal_event=1');
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_cal_exception_event=1');

$TYPO3_CONF_VARS['FE']['eID_include']['cal_ajax'] = 'EXT:cal/service/ajax.php';

//$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
//$useLocationStructure = ($confArr['useLocationStructure']?$confArr['useLocationStructure']:'tx_cal_location');
//$useOrganizerStructure = ($confArr['useOrganizerStructure']?$confArr['useOrganizerStructure']:'tx_cal_organizer');
/**
 *  Both views and model are provided using TYPO3 services.  Models should be
 *  of the type 'cal_model' with a an extension key specific to that model.
 *  Views can be of two types.  The 'cal_view' type is used for views that 
 *  display multiple events.  Within this type, subtypes for 'single', 'day', 
 *  'week', 'month', 'year', and 'custom' are available.  The default views 
 *  each have the key 'default'.  Custom views tied to a specific model should 
 *  have service keys identical to the key of that model.
 */

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_event_model' /* sv type */,  'tx_cal_phpicalendar' /* sv key */,
	array(
		'title' => 'Cal PHPiCalendar Model', 'description' => '', 'subtype' => 'event',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_event_service.php',
		'className' => 'tx_cal_event_service',
	)
);



/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_organizer_model' /* sv type */,  'tx_partner_main' /* sv key */,
	array(
		'title' => 'Cal Organizer Model', 'description' => '', 'subtype' => 'organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_organizer_partner_service.php',
		'className' => 'tx_cal_organizer_partner_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_organizer_model' /* sv type */,  'tx_cal_organizer' /* sv key */,
	array(
		'title' => 'Cal Organizer Model', 'description' => '', 'subtype' => 'organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_organizer_service.php',
		'className' => 'tx_cal_organizer_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_organizer_model' /* sv type */,  'tx_tt_address' /* sv key */,
	array(
		'title' => 'Cal Organizer Model', 'description' => '', 'subtype' => 'organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_organizer_address_service.php',
		'className' => 'tx_cal_organizer_address_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_location_model' /* sv type */,  'tx_partner_main' /* sv key */,
	array(
		'title' => 'Cal Location Model', 'description' => '', 'subtype' => 'location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_location_partner_service.php',
		'className' => 'tx_cal_location_partner_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_location_model' /* sv type */,  'tx_tt_address' /* sv key */,
	array(
		'title' => 'Cal Location Model', 'description' => '', 'subtype' => 'location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_location_address_service.php',
		'className' => 'tx_cal_location_address_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_location_model' /* sv type */,  'tx_cal_location' /* sv key */,
	array(
		'title' => 'Cal Location Model', 'description' => '', 'subtype' => 'location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_location_service.php',
		'className' => 'tx_cal_location_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_calendar_model' /* sv type */,  'tx_cal_calendar' /* sv key */,
	array(
		'title' => 'Cal Calendar Model', 'description' => '', 'subtype' => 'calendar',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_calendar_service.php',
		'className' => 'tx_cal_calendar_service',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_category_model' /* sv type */,  'tx_cal_category' /* sv key */,
	array(
		'title' => 'Cal Category Model', 'description' => '', 'subtype' => 'category',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_category_service.php',
		'className' => 'tx_cal_category_service',
	)
);


/* Default day View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_event' /* sv key */,
	array(
		'title' => 'Default Event View', 'description' => '', 'subtype' => 'event',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_eventview.php',
		'className' => 'tx_cal_eventview',
	)
);

/* Default day View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_day' /* sv key */,
	array(
		'title' => 'Default Day View', 'description' => '', 'subtype' => 'day',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_dayview.php',
		'className' => 'tx_cal_dayview',
	)
);

/* Default week View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_week' /* sv key */,
	array(
		'title' => 'Default Week View', 'description' => '', 'subtype' => 'week',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_weekview.php',
		'className' => 'tx_cal_weekview',
	)
);

/* Default month View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_month' /* sv key */,
	array(
		'title' => 'Default Month View', 'description' => '', 'subtype' => 'month',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_monthview.php',
		'className' => 'tx_cal_monthview',
	)
);

/* Default year View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_year' /* sv key */,
	array(
		'title' => 'Default Year View', 'description' => '', 'subtype' => 'year',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_yearview.php',
		'className' => 'tx_cal_yearview',
	)
);

/* Default list View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_list' /* sv key */,
	array(
		'title' => 'Default List View', 'description' => '', 'subtype' => 'list',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_listview.php',
		'className' => 'tx_cal_listview',
	)
);

/* Default ics View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_ics' /* sv key */,
	array(
		'title' => 'Default Ics View', 'description' => '', 'subtype' => 'ics',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_icsview.php',
		'className' => 'tx_cal_icsview',
	)
);

/* Default icslist View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_icslist' /* sv key */,
	array(
		'title' => 'Default Ics List View', 'description' => '', 'subtype' => 'ics',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_icsview.php',
		'className' => 'tx_cal_icsview',
	)
);

/* Default rss View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_rss' /* sv key */,
	array(
		'title' => 'Default Rss View', 'description' => '', 'subtype' => 'rss',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_rssview.php',
		'className' => 'tx_cal_rssview',
	)
);

/* Default admin View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_admin' /* sv key */,
	array(
		'title' => 'Default Admin View', 'description' => '', 'subtype' => 'admin',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_adminview.php',
		'className' => 'tx_cal_adminview',
	)
);

/* Default location View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_location' /* sv key */,
	array(
		'title' => 'Default Location View', 'description' => '', 'subtype' => 'location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_locationview.php',
		'className' => 'tx_cal_locationview',
	)
);

/* Default organizer View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_organizer' /* sv key */,
	array(
		'title' => 'Default Organizer View', 'description' => '', 'subtype' => 'organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_organizerview.php',
		'className' => 'tx_cal_organizerview',
	)
);

/* Default create event View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_create_event' /* sv key */,
	array(
		'title' => 'Default Create Event View', 'description' => '', 'subtype' => 'create_event',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_create_event_view.php',
		'className' => 'tx_cal_create_event_view',
	)
);

/* Default confirm event View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_confirm_event' /* sv key */,
	array(
		'title' => 'Default Confirm Event View', 'description' => '', 'subtype' => 'confirm_event',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_confirm_event_view.php',
		'className' => 'tx_cal_confirm_event_view',
	)
);

/* Default delete event View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_delete_event' /* sv key */,
	array(
		'title' => 'Default Delete Event View', 'description' => '', 'subtype' => 'delete_event',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_event_view.php',
		'className' => 'tx_cal_delete_event_view',
	)
);

/* Default remove event service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_event' /* sv key */,
	array(
		'title' => 'Default Remove Event View', 'description' => '', 'subtype' => 'remove_event',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_eventview.php',
		'className' => 'tx_cal_eventview',
	)
);

/* Default create location View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_create_location' /* sv key */,
	array(
		'title' => 'Default Create Location View', 'description' => '', 'subtype' => 'create_location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_create_location_organizer_view.php',
		'className' => 'tx_cal_create_location_organizer_view',
	)
);

/* Default confirm location View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_confirm_location' /* sv key */,
	array(
		'title' => 'Default Confirm Location View', 'description' => '', 'subtype' => 'confirm_location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_confirm_location_organizer_view.php',
		'className' => 'tx_cal_confirm_location_organizer_view',
	)
);

/* Default delete location View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_delete_location' /* sv key */,
	array(
		'title' => 'Default Delete Location View', 'description' => '', 'subtype' => 'delete_location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_location_organizer_view.php',
		'className' => 'tx_cal_delete_location_organizer_view',
	)
);

/* Default remove location service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_location' /* sv key */,
	array(
		'title' => 'Default Remove Location View', 'description' => '', 'subtype' => 'remove_location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_locationview.php',
		'className' => 'tx_cal_locationview',
	)
);

/* Default create organizer View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_create_organizer' /* sv key */,
	array(
		'title' => 'Default Create Organizer View', 'description' => '', 'subtype' => 'create_organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_create_location_organizer_view.php',
		'className' => 'tx_cal_create_location_organizer_view',
	)
);

/* Default confirm organizer View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_confirm_organizer' /* sv key */,
	array(
		'title' => 'Default Confirm Organizer View', 'description' => '', 'subtype' => 'confirm_organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_confirm_location_organizer_view.php',
		'className' => 'tx_cal_confirm_location_organizer_view',
	)
);

/* Default delete organizer View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_delete_organizer' /* sv key */,
	array(
		'title' => 'Default Delete Organizer View', 'description' => '', 'subtype' => 'delete_organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_location_organizer_view.php',
		'className' => 'tx_cal_delete_location_organizer_view',
	)
);

/* Default remove organizer service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_organizer' /* sv key */,
	array(
		'title' => 'Default Remove Organizer View', 'description' => '', 'subtype' => 'remove_organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_organizerview.php',
		'className' => 'tx_cal_organizerview',
	)
);

/* Default create calendar View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_create_calendar' /* sv key */,
	array(
		'title' => 'Default Create Location View', 'description' => '', 'subtype' => 'create_calendar',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_create_calendar_view.php',
		'className' => 'tx_cal_create_calendar_view',
	)
);

/* Default confirm calendar View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_confirm_calendar' /* sv key */,
	array(
		'title' => 'Default Confirm Location View', 'description' => '', 'subtype' => 'confirm_calendar',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_confirm_calendar_view.php',
		'className' => 'tx_cal_confirm_calendar_view',
	)
);

/* Default delete calendar View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_delete_calendar' /* sv key */,
	array(
		'title' => 'Default Delete Location View', 'description' => '', 'subtype' => 'delete_calendar',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_calendar_view.php',
		'className' => 'tx_cal_delete_calendar_view',
	)
);

/* Default remove calendar service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_calendar' /* sv key */,
	array(
		'title' => 'Default Remove Location View', 'description' => '', 'subtype' => 'remove_calendar',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_calendar_view.php',
		'className' => 'tx_cal_monthview',
	)
);

/* Default create category View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_create_category' /* sv key */,
	array(
		'title' => 'Default Create Location View', 'description' => '', 'subtype' => 'create_category',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_create_category_view.php',
		'className' => 'tx_cal_create_category_view',
	)
);

/* Default confirm category View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_confirm_category' /* sv key */,
	array(
		'title' => 'Default Confirm Location View', 'description' => '', 'subtype' => 'confirm_category',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_confirm_category_view.php',
		'className' => 'tx_cal_confirm_category_view',
	)
);

/* Default delete category View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_delete_category' /* sv key */,
	array(
		'title' => 'Default Delete Location View', 'description' => '', 'subtype' => 'delete_category',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_category_view.php',
		'className' => 'tx_cal_delete_category_view',
	)
);

/* Default remove category service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_category' /* sv key */,
	array(
		'title' => 'Default Remove Location View', 'description' => '', 'subtype' => 'remove_category',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_monthview.php',
		'className' => 'tx_cal_monthview',
	)
);

/* Default search service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_searchall' /* sv key */,
	array(
		'title' => 'Default Search View', 'description' => '', 'subtype' => 'search',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_searchviews.php',
		'className' => 'tx_cal_searchviews',
	)
);

/* Default search service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_searchevent' /* sv key */,
	array(
		'title' => 'Default Search View', 'description' => '', 'subtype' => 'search',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_searchviews.php',
		'className' => 'tx_cal_searchviews',
	)
);

/* Default search service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_searchlocation' /* sv key */,
	array(
		'title' => 'Default Search View', 'description' => '', 'subtype' => 'search',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_searchviews.php',
		'className' => 'tx_cal_searchviews',
	)
);

/* Default search service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_searchorganizer' /* sv key */,
	array(
		'title' => 'Default Search View', 'description' => '', 'subtype' => 'search',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_searchviews.php',
		'className' => 'tx_cal_searchviews',
	)
);

/* Default notification service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_notification' /* sv key */,
	array(
		'title' => 'Default notification service', 'description' => '', 'subtype' => 'notify',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_notification_view.php',
		'className' => 'tx_cal_notification_view',
	)
);

/* Default reminder service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_reminder' /* sv key */,
	array(
		'title' => 'Default reminder service', 'description' => '', 'subtype' => 'remind',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_reminder_view.php',
		'className' => 'tx_cal_reminder_view',
	)
);

/* Default rights service */
t3lib_extMgm::addService($_EXTKEY,  'cal_rights_model' /* sv type */,  'tx_cal_rights' /* sv key */,
	array(
		'title' => 'Default rights service', 'description' => '', 'subtype' => 'rights',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'service/class.tx_cal_rights_service.php',
		'className' => 'tx_cal_rights_service',
	)
);
// Example for a module
t3lib_extMgm::addService($_EXTKEY,  'TEST' /* sv type */,  'tx_cal_module' /* sv key */,
	array(
		'title' => 'Test module', 'description' => '', 'subtype' => 'module',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'misc/class.module_example.php',
		'className' => 'module_example',
	)
);

// Example for a module
t3lib_extMgm::addService($_EXTKEY,  'LOCATIONLOADER' /* sv type */,  'tx_cal_module' /* sv key */,
	array(
		'title' => 'Location loader module', 'description' => '', 'subtype' => 'module',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'misc/class.module_locationloader.php',
		'className' => 'module_locationloader',
	)
);

// Example for a module
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_cal_subscription' /* sv key */,
	array(
		'title' => 'Subscription Manager', 'description' => '', 'subtype' => 'subscription',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_subscription_manager_view.php',
		'className' => 'tx_cal_subscription_manager_view',
	)
);

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:cal/hooks/class.tx_cal_tcemain_processdatamap.php:tx_cal_tcemain_processdatamap';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:cal/hooks/class.tx_cal_tcemain_processcmdmap.php:tx_cal_tcemain_processcmdmap';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:cal/hooks/class.tx_cal_tceforms_getmainfields.php:tx_cal_tceforms_getmainfields';
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['calendar'] = 'EXT:cal/controller/class.tx_cal_event_linkHandler.php:tx_cal_event_linkHandler';



if (t3lib_extMgm::isLoaded('gabriel')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['gabriel']['include'][$_EXTKEY] = array('cron/class.tx_cal_calendar_cron.php', 'cron/class.tx_cal_reminder_cron.php');
}

?>