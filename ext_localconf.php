<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'controller/class.tx_cal_controller.php','_controller','list_type',1);
t3lib_extMgm::addUserTSConfig('options.saveDocNew.tx_cal_events=1');
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
		'title' => 'Cal PHPiCalendar Model', 'description' => '', 'subtype' => '',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'model/class.tx_cal_phpicalendar_model.php',
		'className' => 'tx_cal_phpicalendar_model',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_organizer_model' /* sv type */,  'tx_default_organizer' /* sv key */,
	array(
		'title' => 'Cal Organizer Model', 'description' => '', 'subtype' => '',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'model/class.tx_cal_organizer.php',
		'className' => 'tx_cal_organizer',
	)
);

/* Cal Example Concrete Model */
t3lib_extMgm::addService($_EXTKEY,  'cal_location_model' /* sv type */,  'tx_default_location' /* sv key */,
	array(
		'title' => 'Cal Location Model', 'description' => '', 'subtype' => '',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'model/class.tx_cal_location.php',
		'className' => 'tx_cal_location',
	)
);

/* Default day View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_event' /* sv key */,
	array(
		'title' => 'Default Event View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default day View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_day' /* sv key */,
	array(
		'title' => 'Default Day View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default week View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_week' /* sv key */,
	array(
		'title' => 'Default Week View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default month View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_month' /* sv key */,
	array(
		'title' => 'Default Month View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default year View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_year' /* sv key */,
	array(
		'title' => 'Default Year View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default list View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_list' /* sv key */,
	array(
		'title' => 'Default List View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default ics View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_ics' /* sv key */,
	array(
		'title' => 'Default Ics View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default location View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_location' /* sv key */,
	array(
		'title' => 'Default Location View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

/* Default organizer View */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_organizer' /* sv key */,
	array(
		'title' => 'Default Organizer View', 'description' => '', 'subtype' => 'single',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
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
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
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
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_location_view.php',
		'className' => 'tx_cal_delete_location_view',
	)
);

/* Default remove location service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_location' /* sv key */,
	array(
		'title' => 'Default Remove Location View', 'description' => '', 'subtype' => 'remove_location',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
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
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_delete_organizer_view.php',
		'className' => 'tx_cal_delete_organizer_view',
	)
);

/* Default remove organizer service */
t3lib_extMgm::addService($_EXTKEY,  'cal_view' /* sv type */,  'tx_default_remove_organizer' /* sv key */,
	array(
		'title' => 'Default Remove Organizer View', 'description' => '', 'subtype' => 'remove_organizer',
		'available' => TRUE, 'priority' => 50, 'quality' => 50,
		'os' => '', 'exec' => '',
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'view/class.tx_cal_phpicalendarview.php',
		'className' => 'tx_cal_phpicalendarview',
	)
);

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:cal/hooks/class.tx_cal_tcemain_processdatamap.php:tx_cal_tcemain_processdatamap';
// $GLOBALS ['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_controller.php']['draweventClass'][] = 'EXT:cal/hooks/class.tx_cal_controller_renderevent:tx_cal_controller_renderevent';
?>