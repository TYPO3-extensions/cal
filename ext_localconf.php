<?php
if (! defined ('TYPO3_MODE'))
	die ('Access denied.');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43 ($_EXTKEY, 'Classes/Controller/Controller.php', '_controller', 'list_type', 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig ('options.saveDocNew.tx_cal_event=1');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig ('options.saveDocNew.tx_cal_exception_event=1');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript ($_EXTKEY, 'setup', '
	tt_content.shortcut.20.conf.tx_cal_event = < plugin.tx_cal_controller
	tt_content.shortcut.20.conf.tx_cal_event {
		displayCurrentRecord = 1
		// If you don\'t want that this record is reacting on certain piVars, add those to this list. To clear all piVars, use keyword "all"
		clearPiVars = uid,getdate,type,view
		// If you want that this record doesn\'t react on any piVar or session-stored var of cal - uncomment this option
		#dontListenToPiVars = 1
	}
', 43);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig ('options.tx_cal_controller.headerStyles = default_catheader=#557CA3,green_catheader=#53A062,orange_catheader=#E84F25,pink_catheader=#B257A2,red_catheader=#D42020,yellow_catheader=#B88F0B,grey_catheader=#73738C
	 options.tx_cal_controller.bodyStyles = default_catbody=#6699CC,green_catbody=#4FC464,orange_catbody=#FF6D3B,pink_catbody=#EA62D4,red_catbody=#FF5E56,yellow_catbody=#CCB21F,grey_catbody=#9292A1');

$GLOBALS['TYPO3_CONF_VARS'] ['FE'] ['eID_include'] ['cal_ajax'] = 'EXT:cal/Classes/Ajax/Ajax.php';

/**
 * Both views and model are provided using TYPO3 services.
 * Models should be
 * of the type 'cal_model' with a an extension key specific to that model.
 * Views can be of two types. The 'cal_view' type is used for views that
 * display multiple events. Within this type, subtypes for 'single', 'day',
 * 'week', 'month', 'year', and 'custom' are available. The default views
 * each have the key 'default'. Custom views tied to a specific model should
 * have service keys identical to the key of that model.
 */

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_event_model' /* sv type */,  'tx_cal_fnb' /* sv key */,
	array (
		'title' => 'Cal Free and Busy Model',
		'description' => '',
		'subtype' => 'event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\FnbEventService' 
));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_event_model' /* sv type */,  'tx_cal_phpicalendar' /* sv key */,
	array (
		'title' => 'Cal PHPiCalendar Model',
		'description' => '',
		'subtype' => 'event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\EventService' 
));

// get extension confArr
$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);

/* Cal Todo Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_event_model' /* sv type */,  'tx_cal_todo' /* sv key */,
	array (
		'title' => 'Cal Todo Model',
		'description' => '',
		'subtype' => $confArr ['todoSubtype'],
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\TodoService' 
));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_event_model' /* sv type */,  'tx_cal_nearby' /* sv key */,
	array (
		'title' => 'Cal Nearby Model',
		'description' => '',
		'subtype' => 'event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\NearbyEventService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_organizer_model' /* sv type */,  'tx_partner_main' /* sv key */,
	array (
		'title' => 'Cal Organizer Model',
		'description' => '',
		'subtype' => 'organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\OrganizerPartnerService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_organizer_model' /* sv type */,  'tx_cal_organizer' /* sv key */,
	array (
		'title' => 'Cal Organizer Model',
		'description' => '',
		'subtype' => 'organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\OrganizerService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_organizer_model' /* sv type */,  'tx_tt_address' /* sv key */,
	array (
		'title' => 'Cal Organizer Model',
		'description' => '',
		'subtype' => 'organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\OrganizerAddressService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_organizer_model' /* sv type */,  'tx_feuser' /* sv key */,
	array (
		'title' => 'Frontend User Organizer Model',
		'description' => '',
		'subtype' => 'organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\OrganizerFeUserService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_location_model' /* sv type */,  'tx_partner_main' /* sv key */,
	array (
		'title' => 'Cal Location Model',
		'description' => '',
		'subtype' => 'location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\LocationPartnerService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_location_model' /* sv type */,  'tx_tt_address' /* sv key */,
	array (
		'title' => 'Cal Location Model',
		'description' => '',
		'subtype' => 'location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\LocationAddressService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_location_model' /* sv type */,  'tx_cal_location' /* sv key */,
	array (
		'title' => 'Cal Location Model',
		'description' => '',
		'subtype' => 'location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\LocationService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_attendee_model' /* sv type */,  'tx_cal_attendee' /* sv key */,
	array (
		'title' => 'Cal Attendee Model',
		'description' => '',
		'subtype' => 'attendee',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\AttendeeService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_calendar_model' /* sv type */,  'tx_cal_calendar' /* sv key */,
	array (
		'title' => 'Cal Calendar Model',
		'description' => '',
		'subtype' => 'calendar',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\CalendarService' 
));

/* Cal Example Concrete Model */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_category_model' /* sv type */,  'tx_cal_category' /* sv key */,
	array (
		'title' => 'Cal Category Model',
		'description' => '',
		'subtype' => 'category',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\CategoryService' 
));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_category_model' /* sv type */,  'sys_category' /* sv key */,
	array (
		'title' => 'System Category Model',
		'description' => '',
		'subtype' => 'category',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\SysCategoryService'
));

/* Default day View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_event' /* sv key */,
	array (
		'title' => 'Default Event View',
		'description' => '',
		'subtype' => 'event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\EventView' 
));

/* Default day View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_day' /* sv key */,
	array (
		'title' => 'Default Day View',
		'description' => '',
		'subtype' => 'day',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DayView' 
));

/* Default week View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_week' /* sv key */,
	array (
		'title' => 'Default Week View',
		'description' => '',
		'subtype' => 'week',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\WeekView' 
));

/* Default month View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_month' /* sv key */,
	array (
		'title' => 'Default Month View',
		'description' => '',
		'subtype' => 'month',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\MonthView' 
));

/* Default year View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_year' /* sv key */,
	array (
		'title' => 'Default Year View',
		'description' => '',
		'subtype' => 'year',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\YearView' 
));

/* Default list View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_list' /* sv key */,
	array (
		'title' => 'Default List View',
		'description' => '',
		'subtype' => 'list',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ListView' 
));

/* Default ics View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_ics' /* sv key */,
	array (
		'title' => 'Default Ics View',
		'description' => '',
		'subtype' => 'ics',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\IcsView' 
));

/* Default icslist View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_icslist' /* sv key */,
	array (
		'title' => 'Default Ics List View',
		'description' => '',
		'subtype' => 'ics',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\IcsView' 
));

/* Default rss View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_rss' /* sv key */,
	array (
		'title' => 'Default Rss View',
		'description' => '',
		'subtype' => 'rss',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\RssView' 
));

/* Default admin View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_admin' /* sv key */,
	array (
		'title' => 'Default Admin View',
		'description' => '',
		'subtype' => 'admin',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\AdminView' 
));

/* Default location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_location' /* sv key */,
	array (
		'title' => 'Default Location View',
		'description' => '',
		'subtype' => 'location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\LocationView' 
));

/* Default organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_organizer' /* sv key */,
	array (
		'title' => 'Default Organizer View',
		'description' => '',
		'subtype' => 'organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\OrganizerView' 
));

/* Default create event View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_create_event' /* sv key */,
	array (
		'title' => 'Default Create Event View',
		'description' => '',
		'subtype' => 'create_event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\CreateEventView' 
));

/* Default confirm event View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_confirm_event' /* sv key */,
	array (
		'title' => 'Default Confirm Event View',
		'description' => '',
		'subtype' => 'confirm_event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ConfirmEventView' 
));

/* Default delete event View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_delete_event' /* sv key */,
	array (
		'title' => 'Default Delete Event View',
		'description' => '',
		'subtype' => 'delete_event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteEventView' 
));

/* Default remove event service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_remove_event' /* sv key */,
	array (
		'title' => 'Default Remove Event View',
		'description' => '',
		'subtype' => 'remove_event',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\EventView' 
));

/* Default create location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_create_location' /* sv key */,
	array (
		'title' => 'Default Create Location View',
		'description' => '',
		'subtype' => 'create_location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\CreateLocationOrganizerView' 
));

/* Default confirm location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_confirm_location' /* sv key */,
	array (
		'title' => 'Default Confirm Location View',
		'description' => '',
		'subtype' => 'confirm_location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ConfirmLocationOrganizerView' 
));

/* Default delete location View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_delete_location' /* sv key */,
	array (
		'title' => 'Default Delete Location View',
		'description' => '',
		'subtype' => 'delete_location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteLocationOrganizerView' 
));

/* Default remove location service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_remove_location' /* sv key */,
	array (
		'title' => 'Default Remove Location View',
		'description' => '',
		'subtype' => 'remove_location',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\LocationView' 
));

/* Default create organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_create_organizer' /* sv key */,
	array (
		'title' => 'Default Create Organizer View',
		'description' => '',
		'subtype' => 'create_organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\CreateLocationOrganizerView' 
));

/* Default confirm organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_confirm_organizer' /* sv key */,
	array (
		'title' => 'Default Confirm Organizer View',
		'description' => '',
		'subtype' => 'confirm_organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ConfirmLocationOrganizerView' 
));

/* Default delete organizer View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_delete_organizer' /* sv key */,
	array (
		'title' => 'Default Delete Organizer View',
		'description' => '',
		'subtype' => 'delete_organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteLocationOrganizerView' 
));

/* Default remove organizer service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_remove_organizer' /* sv key */,
	array (
		'title' => 'Default Remove Organizer View',
		'description' => '',
		'subtype' => 'remove_organizer',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\OrganizerView' 
));

/* Default create calendar View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_create_calendar' /* sv key */,
	array (
		'title' => 'Default Create Location View',
		'description' => '',
		'subtype' => 'create_calendar',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\CreateCalendarView' 
));

/* Default confirm calendar View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_confirm_calendar' /* sv key */,
	array (
		'title' => 'Default Confirm Location View',
		'description' => '',
		'subtype' => 'confirm_calendar',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ConfirmCalendarView' 
));

/* Default delete calendar View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_delete_calendar' /* sv key */,
	array (
		'title' => 'Default Delete Location View',
		'description' => '',
		'subtype' => 'delete_calendar',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteCalendarView' 
));

/* Default remove calendar service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_remove_calendar' /* sv key */,
	array (
		'title' => 'Default Remove Location View',
		'description' => '',
		'subtype' => 'remove_calendar',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteCalendarView' 
));

/* Default create category View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_create_category' /* sv key */,
	array (
		'title' => 'Default Create Location View',
		'description' => '',
		'subtype' => 'create_category',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\CreateCategoryView' 
));

/* Default confirm category View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_confirm_category' /* sv key */,
	array (
		'title' => 'Default Confirm Location View',
		'description' => '',
		'subtype' => 'confirm_category',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ConfirmCategoryView' 
));

/* Default delete category View */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_delete_category' /* sv key */,
	array (
		'title' => 'Default Delete Location View',
		'description' => '',
		'subtype' => 'delete_category',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteCategoryView' 
));

/* Default remove category service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_remove_category' /* sv key */,
	array (
		'title' => 'Default Remove Location View',
		'description' => '',
		'subtype' => 'remove_category',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\DeleteCategoryView' 
));

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_searchall' /* sv key */,
	array (
		'title' => 'Default Search View',
		'description' => '',
		'subtype' => 'search',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\SearchViews' 
));

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_searchevent' /* sv key */,
	array (
		'title' => 'Default Search View',
		'description' => '',
		'subtype' => 'search',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\SearchViews' 
));

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_searchlocation' /* sv key */,
	array (
		'title' => 'Default Search View',
		'description' => '',
		'subtype' => 'search',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\SearchViews' 
));

/* Default search service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_searchorganizer' /* sv key */,
	array (
		'title' => 'Default Search View',
		'description' => '',
		'subtype' => 'search',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\SearchViews' 
));

/* Default notification service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_notification' /* sv key */,
	array (
		'title' => 'Default notification service',
		'description' => '',
		'subtype' => 'notify',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\NotificationView' 
));

/* Default reminder service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_default_reminder' /* sv key */,
	array (
		'title' => 'Default reminder service',
		'description' => '',
		'subtype' => 'remind',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\ReminderView' 
));

/* Default rights service */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_rights_model' /* sv type */,  'tx_cal_rights' /* sv key */,
	array (
		'title' => 'Default rights service',
		'description' => '',
		'subtype' => 'rights',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\Service\\RightsService' 
));
// Example for a module
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'TEST' /* sv type */,  'tx_cal_module' /* sv key */,
	array (
		'title' => 'Test module',
		'description' => '',
		'subtype' => 'module',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\Module\\Example' 
));

// Example for a module
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'LOCATIONLOADER' /* sv type */,  'tx_cal_module' /* sv key */,
	array (
		'title' => 'Location loader module',
		'description' => '',
		'subtype' => 'module',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\Module\\LocationLoader' 
));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'ORGANIZERLOADER' /* sv type */,  'tx_cal_module' /* sv key */,
	array (
		'title' => 'Organizer loader module',
		'description' => '',
		'subtype' => 'module',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\Module\\OrganizerLoader' 
));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_cal_subscription' /* sv key */,
	array (
		'title' => 'Subscription Manager',
		'description' => '',
		'subtype' => 'subscription',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\SubscriptionManagerView' 
));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService ($_EXTKEY, 'cal_view' /* sv type */,  'tx_cal_meeting' /* sv key */,
	array (
		'title' => 'Meeting Manager',
		'description' => '',
		'subtype' => 'meeting',
		'available' => TRUE,
		'priority' => 50,
		'quality' => 50,
		'os' => '',
		'exec' => '',
		'className' => 'TYPO3\\CMS\\Cal\\View\\MeetingManagerView' 
));

$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['t3lib/class.t3lib_tcemain.php'] ['processDatamapClass'] ['tx_cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\TceMainProcessdatamap';
$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['t3lib/class.t3lib_tcemain.php'] ['processCmdmapClass'] ['tx_cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\TceMainProcesscmdmap';

if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7005000) {
	\TYPO3\CMS\Cal\Backend\Form\FormDateDataProvider::register();
} else if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7001000) {
	\TYPO3\CMS\Cal\Slot\FormDataPreprocessorSlot::register();
} else {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass']['tx_cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\TceFormsGetmainfields';
}

$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['tslib/class.tslib_content.php'] ['typolinkLinkHandler'] ['calendar'] = 'TYPO3\\CMS\\Cal\\Hooks\\EventLinkHandler';
$GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['tx_wecmap_pi3'] ['markerHook'] ['cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\WecMap:&WecMap->getMarkerContent';
$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['tce'] ['formevals'] ['tx_cal_dateeval'] = 'TYPO3\\CMS\\Cal\\Hooks\\DateEval';
// $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['felogin'] ['loginFormOnSubmitFuncs'] [] = 'TYPO3\\CMS\\Cal\\Hooks\\LogoffPostProcessing:LogoffPostProcessing->clearSessionApiAfterLogoff';
// $GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['felogin'] ['login_confirmed'] [] = 'TYPO3\\CMS\\Cal\\Hooks\\LogoffPostProcessing:LogoffPostProcessing->clearSessionApiAfterLogin';

if (! isset ($confArr ['enableRealURLAutoConfiguration']) || $confArr ['enableRealURLAutoConfiguration']) {
	$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['ext/realurl/class.tx_realurl_autoconfgen.php'] ['extensionConfiguration'] ['cal'] = 'TYPO3\\CMS\\Cal\\Hooks\\RealUrl->addRealURLConfig';
}

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('gabriel')) {
	$GLOBALS ['TYPO3_CONF_VARS'] ['EXTCONF'] ['gabriel'] ['include'] [$_EXTKEY] = Array (
			'TYPO3\\CMS\\Cal\\Cron\\CalendarCron',
			'TYPO3\\CMS\\Cal\\Cron\\ReminderCron' 
	);
}

$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['scheduler'] ['tasks'] ['TYPO3\\CMS\\Cal\\Cron\\CalendarScheduler'] = Array (
		'extension' => $_EXTKEY,
		'title' => 'Updating external calendars (created by saving the calendar record)',
		'description' => 'cal calendar scheduler integration',
		'additionalFields' => ''
);
$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['scheduler'] ['tasks'] ['TYPO3\\CMS\\Cal\\Cron\\ReminderScheduler'] = Array (
		'extension' => $_EXTKEY,
		'title' => 'Sending reminder for events (created by saving the event record)',
		'description' => 'cal reminder scheduler integration',
		'additionalFields' => ''
);
$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['scheduler'] ['tasks'] ['TYPO3\\CMS\\Cal\\Cron\\IndexerScheduler'] = Array (
		'extension' => $_EXTKEY,
		'title' => 'Indexer for recurring events',
		'description' => 'Indexing recurring events',
		'additionalFields' => 'TYPO3\\CMS\\Cal\\Cron\\IndexerSchedulerAdditionalFieldProvider'
);

/* defining stuff for scheduler */
if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList ($GLOBALS['TYPO3_CONF_VARS'] ['EXT'] ['extList'], 'scheduler')) {
	// find type of ext and determine paths
	// add these to the global TYPO3_LOADED_EXT
	$temp_extKey = 'scheduler';
	if (! isset ($GLOBALS ['TYPO3_LOADED_EXT'] [$temp_extKey])) {
		if (@is_dir (PATH_typo3conf . 'ext/' . $temp_extKey . '/')) {
			$GLOBALS ['TYPO3_LOADED_EXT'] [$temp_extKey] = Array (
					'type' => 'L',
					'siteRelPath' => 'typo3conf/ext/' . $temp_extKey . '/',
					'typo3RelPath' => '../typo3conf/ext/' . $temp_extKey . '/' 
			);
		} elseif (@is_dir (PATH_typo3 . 'ext/' . $temp_extKey . '/')) {
			$GLOBALS ['TYPO3_LOADED_EXT'] [$temp_extKey] = Array (
					'type' => 'G',
					'siteRelPath' => TYPO3_mainDir . 'ext/' . $temp_extKey . '/',
					'typo3RelPath' => 'ext/' . $temp_extKey . '/' 
			);
		} elseif (@is_dir (PATH_typo3 . 'sysext/' . $temp_extKey . '/')) {
			$GLOBALS ['TYPO3_LOADED_EXT'] [$temp_extKey] = Array (
					'type' => 'S',
					'siteRelPath' => TYPO3_mainDir . 'sysext/' . $temp_extKey . '/',
					'typo3RelPath' => 'sysext/' . $temp_extKey . '/' 
			);
		}
	}
	
	$GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extList_FE'] .= ',scheduler';
	$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['scheduler'] ['tasks'] ['TYPO3\\CMS\\Cal\\Cron\\CalendarScheduler'] = Array (
		'extension' => $_EXTKEY,
		'title' => 'Updating external calendars (created by saving the calendar record)',
		'description' => 'cal calendar scheduler integration',
		'additionalFields' => ''
	);
	$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['scheduler'] ['tasks'] ['TYPO3\\CMS\\Cal\\Cron\\ReminderScheduler'] = Array (
			'extension' => $_EXTKEY,
			'title' => 'Sending reminder for events (created by saving the event record)',
			'description' => 'cal reminder scheduler integration',
			'additionalFields' => ''
	);
	$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['scheduler'] ['tasks'] ['TYPO3\\CMS\\Cal\\Cron\\IndexerScheduler'] = Array (
			'extension' => $_EXTKEY,
			'title' => 'Indexer for recurring events',
			'description' => 'Indexing recurring events',
			'additionalFields' => 'TYPO3\\CMS\\Cal\\Cron\\IndexerSchedulerAdditionalFieldProvider'
	);
}

/* Include a custom userFunc for checking whether we're in frontend editing mode */
require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ($_EXTKEY) . 'Classes/Frontend/IsCalNotAllowedToBeCached.php');

// caching framework configuration
// Register cache 'tx_cal_cache'
if (! is_array ($GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'])) {
	$GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] = Array ();
}
// Define string frontend as default frontend, this must be set with TYPO3 4.5 and below
// and overrides the default variable frontend of 4.6
if (! isset ($GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['frontend'])) {
	$GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\StringFrontend';
}
if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) < '4006000') {
	// Define database backend as backend for 4.5 and below (default in 4.6)
	if (! isset ($GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['backend'])) {
		$GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend';
	}
	// Define data and tags table for 4.5 and below (obsolete in 4.6)
	if (! isset ($GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options'])) {
		$GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options'] = Array ();
	}
	if (! isset ($GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options'] ['cacheTable'])) {
		$GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options'] ['cacheTable'] = 'tx_cal_cache';
	}
	if (! isset ($GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options'] ['tagsTable'])) {
		$GLOBALS['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options'] ['tagsTable'] = 'tx_cal_cache_tags';
	}
}

// register cal cache table for "clear all caches"
if ($confArr ['cachingMode'] == 'normal') {
	$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['t3lib/class.t3lib_tcemain.php'] ['clearAllCache_additionalTables'] ['tx_cal_cache'] = 'tx_cal_cache';
}
$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['t3lib/class.t3lib_befunc.php'] ['postProcessValue'] [] = 'TYPO3\\CMS\\Cal\\Hooks\\Befunc->postprocessvalue';
$GLOBALS ['TYPO3_CONF_VARS'] ['SC_OPTIONS'] ['t3lib/class.t3lib_befunc.php'] ['preProcessValue'] [] = 'TYPO3\\CMS\\Cal\\Hooks\\Befunc->preprocessvalue';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_event_file_uploads'] = 'TYPO3\\CMS\\Cal\\Updates\\UploadsUpdateWizard';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_event_images'] = 'TYPO3\\CMS\\Cal\\Updates\\EventImagesUpdateWizard';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_location_images'] = 'TYPO3\\CMS\\Cal\\Updates\\LocationImagesUpdateWizard';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_organizer_images'] = 'TYPO3\\CMS\\Cal\\Updates\\OrganizerImagesUpdateWizard';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['cal_sys_template'] = 'TYPO3\\CMS\\Cal\\Updates\\TypoScriptUpdateWizard';
?>