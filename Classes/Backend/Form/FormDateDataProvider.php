<?php
namespace TYPO3\CMS\Cal\Backend\Form;
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

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

/**
 * FormDateDataProvider class for the FormEngine
 */
class FormDateDataProvider implements FormDataProviderInterface {
	
	public static function register() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\TYPO3\CMS\Cal\Backend\Form\FormDateDataProvider::class] = array(
				'before' => array(
						\TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDateTimeFields::class,
				),
		);
	}

	/**
	 * Migrate date and datetime db field values to timestamp
	 *
	 * @param array $result
	 * @return array
	 */
	public function addData(array $result) {
		foreach ($result['vanillaTableTca']['columns'] as $column => $columnConfig) {
			if (isset($columnConfig['config']['tx_cal_event'])) {
				
				
				$mainFields = new \TYPO3\CMS\Cal\Hooks\TceFormsGetmainfields();
				$mainFields->getMainFields_preProcess($result['tableName'], $result['databaseRow'], NULL);
				
				return $result;
				
			}
		}
		return $result;
	}
	
}