<?php
if (! defined ( 'TYPO3_MODE' ))
	die ( 'Access denied.' );

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ( $_EXTKEY );

// Allow all calendar records on standard pages, in addition to SysFolders.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_event' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_category' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_calendar' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_exception_event' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_exception_event_group' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_location' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_organizer' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_unknown_users' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_attendee' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_fe_user_event_monitor_mm' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages ( 'tx_cal_event_deviation' );

// Add Calendar Events to the "Insert Records" content element
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords ( 'tx_cal_event' );

// initalize 'context sensitive help' (csh)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_event', 'EXT:cal/Resources/Private/Help/locallang_csh_txcalevent.php' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_calendar', 'EXT:cal/Resources/Private/Help/locallang_csh_txcalcal.php' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_category', 'EXT:cal/Resources/Private/Help/locallang_csh_txcalcat.php' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_exception_event', 'EXT:cal/Resources/Private/Help/locallang_csh_txcalexceptionevent.php' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_exception_event_group', 'EXT:cal/Resources/Private/Help/locallang_csh_txcalexceptioneventgroup.php' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_location', 'EXT:cal/Resources/Private/Help/locallang_csh_txcallocation.php' );
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr ( 'tx_cal_organizer', 'EXT:cal/Resources/Private/Help/locallang_csh_txcalorganizer.php' );

if (TYPO3_MODE == "BE") {
	if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger ( TYPO3_version ) < '8000000') {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule ( "tools", "calrecurrencegenerator", "", $extPath . "Classes/Backend/Modul/" );
		$GLOBALS ['TBE_MODULES_EXT'] ['xMOD_db_new_content_el'] ['addElClasses'] ['TYPO3\CMS\Cal\Backend\CalWizIcon'] = $extPath . 'Classes/Backend/CalWizIcon.php';
	} else {
		// Add module
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
			'tools',
			'txcalM1',
			'',
			'',
			[
					'routeTarget' => \TYPO3\CMS\Cal\Backend\Modul\CalIndexer::class . '::mainAction',
					'access' => 'admin',
					'name' => 'tools_txcalM1',
					'icon' => 'EXT:cal/Classes/Backend/Modul/icon_tx_cal_indexer2.svg',
					'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_indexer_mod.xml'
			]
		);
	}
}
?>