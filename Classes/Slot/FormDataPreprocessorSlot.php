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

/**
 * Slot class for the FormEngine DataPreprocessor
 */
class FormDataPreprocessorSlot {

	/**
	 * Fetch the tx_cal_* records and manipulate them
	 * 
	 * @param DataPreprocessor $recordData
	 * @return void
	 */
	function fetchCalRecordPostProcessing(DataPreprocessor $recordData) {

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
}