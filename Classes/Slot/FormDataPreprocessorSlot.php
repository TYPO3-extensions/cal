<?php
namespace TYPO3\CMS\Cal\Slot;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Form\DataPreprocessor;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Slot class for the FormEngine DataPreprocessor
 */
class FormDataPreprocessorSlot implements FormDataProviderInterface {
	
	public static function register() {
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7005000) {
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\TYPO3\CMS\Cal\Slot\FormDataPreprocessorSlot::class] = array(
					'before' => array(
							\TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDateTimeFields::class,
					),
			);
		} else {
			\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class)->connect(
					\TYPO3\CMS\Backend\Form\DataPreprocessor::class,
					'fetchRecordPostProcessing',
					\TYPO3\CMS\Cal\Slot\FormDataPreprocessorSlot::class,
					'fetchCalRecordPostProcessing'
			);
		}
	}

	/**
	 * Fetch the tx_cal_* records and manipulate them
	 * 
	 * @param DataPreprocessor $recordData
	 * @return void
	 */
	public function fetchCalRecordPostProcessing(DataPreprocessor $recordData) {

		if (preg_match('/^tx_cal_(.*)$/', key($recordData->regTableItems)) == FALSE) {
			return;
		}

		foreach ($recordData->regTableItems_data as $key => $value) {
			$table = substr($key, 0, -(strlen($key) - strripos($key, '_')));

			$mainFields = new \TYPO3\CMS\Cal\Hooks\TceFormsGetmainfields();
			$mainFields->getMainFields_preProcess($table, $value, NULL);

			$recordData->regTableItems_data[$key] = $value;
		}
	}
	
	/**
	 * Migrate date and datetime db field values to timestamp
	 *
	 * @param array $result
	 * @return array
	 */
	public function addData(array $result) {
		$dateTimeFormats = $this->getDatabase()->getDateTimeFormats($result['tableName']);
		foreach ($result['vanillaTableTca']['columns'] as $column => $columnConfig) {
			if (isset($columnConfig['config']['tx_cal_event'])) {
				
				
				$mainFields = new \TYPO3\CMS\Cal\Hooks\TceFormsGetmainfields();
				$mainFields->getMainFields_preProcess($result['tableName'], $result['databaseRow'], NULL);
				
				return $result;
				
			}
		}
		return $result;
	}
	
	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}
}