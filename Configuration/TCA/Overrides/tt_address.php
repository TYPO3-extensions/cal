<?php
defined('TYPO3_MODE') or die();

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {

	$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);

	// Append backend search configuration for tt_address:
	$delimeter = isset($GLOBALS['TCA']['tt_address']['ctrl']['searchFields']) ? ',' : '';
	$GLOBALS['TCA']['tt_address']['ctrl']['searchFields'] .= $delimeter . 'tx_cal_controller_latitude,tx_cal_controller_longitude';

	// Get the location and organizer structures.
	$useLocationStructure = $configuration['useLocationStructure'] ?: 'tx_cal_location';
	$useOrganizerStructure = $configuration['useOrganizerStructure'] ?: 'tx_cal_organizer';

	if ($useLocationStructure == 'tx_tt_address') {
		$tempColumns = array(
			'tx_cal_controller_islocation' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_location.islocation',
				'config' => array(
						'type' => 'check',
						'default' => 1
				)
			)
		);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'tx_cal_controller_islocation,');
	}

	if ($useOrganizerStructure == 'tx_tt_address') {
		$tempColumns = array(
			'tx_cal_controller_isorganizer' => array(
				'exclude' => 1,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_organizer.isorganizer',
				'config' => array(
					'type' => 'check',
					'default' => 0
				)
			)
		);
		
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns);
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'tx_cal_controller_isorganizer,');
	}
}