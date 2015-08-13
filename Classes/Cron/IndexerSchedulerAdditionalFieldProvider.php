<?php
namespace TYPO3\CMS\Cal\Cron;
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

/**
 * Additional BE fields for cal recurring event indexer task.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 */
class IndexerSchedulerAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * Add additional fields
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return array Array containing all the information pertaining to the additional fields
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$additionalFields = array();
		$additionalFields['task_eventIndexer_eventFolder'] = $this->getEventFolderAdditionalField($taskInfo, $task, $parentObject);
		$additionalFields['task_eventIndexer_typoscriptPage'] = $this->getTyposcriptPageAdditionalField($taskInfo, $task, $parentObject);
		$additionalFields['task_eventIndexer_starttime'] = $this->getStarttimeAdditionalField($taskInfo, $task, $parentObject);
		$additionalFields['task_eventIndexer_endtime'] = $this->getEndtimeAdditionalField($taskInfo, $task, $parentObject);
		return $additionalFields;
	}

	/**
	 * Add an input field for event folders.
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return array Array containing all the information pertaining to the additional fields
	 */
	protected function getEventFolderAdditionalField(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$fieldName = 'tx_scheduler[cal_eventIndexer_eventFolder]';
		$fieldId = 'task_eventIndexer_eventFolder';
		$fieldHtml = '<input type="text" name="' . $fieldName . '" ' . 'id="' . $fieldId . '" value="' . $task->eventFolder . '"/>';
		$fieldConfiguration = array(
			'code' => $fieldHtml,
			'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer.xml:tableHeader1',
			'cshKey' => '',
			'cshLabel' => $fieldId
		);
		return $fieldConfiguration;
	}
	
	/**
	 * Add an input field for the typoscript page.
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return array Array containing all the information pertaining to the additional fields
	 */
	protected function getTyposcriptPageAdditionalField(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$fieldName = 'tx_scheduler[cal_eventIndexer_typoscriptPage]';
		$fieldId = 'task_eventIndexer_typoscriptPage';
		$fieldHtml = '<input type="text" name="' . $fieldName . '" ' . 'id="' . $fieldId . '" value="' . $task->typoscriptPage . '" />';
		$fieldConfiguration = array(
				'code' => $fieldHtml,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer.xml:tableHeader2',
				'cshKey' => '',
				'cshLabel' => $fieldId
		);
		return $fieldConfiguration;
	}
	
	/**
	 * Add an input field for the starttime.
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return array Array containing all the information pertaining to the additional fields
	 */
	protected function getStarttimeAdditionalField(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$fieldName = 'tx_scheduler[cal_eventIndexer_starttime]';
		$fieldId = 'task_eventIndexer_starttime';
		$fieldHtml = '<input type="text" name="' . $fieldName . '" ' . 'id="' . $fieldId . '" value="' . $task->starttime . '" />';
		$fieldConfiguration = array(
				'code' => $fieldHtml,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer.xml:indexStart',
				'cshKey' => '',
				'cshLabel' => $fieldId
		);
		return $fieldConfiguration;
	}
	
	/**
	 * Add an input field for the endtime.
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param AbstractTask|NULL $task When editing, reference to the current task. NULL when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return array Array containing all the information pertaining to the additional fields
	 */
	protected function getEndtimeAdditionalField(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$fieldName = 'tx_scheduler[cal_eventIndexer_endtime]';
		$fieldId = 'task_eventIndexer_endtime';
		$fieldHtml = '<input type="text" name="' . $fieldName . '" ' . 'id="' . $fieldId . '" value="' . $task->endtime . '" />';
		$fieldConfiguration = array(
				'code' => $fieldHtml,
				'label' => 'LLL:EXT:cal/Resources/Private/Language/locallang_indexer.xml:indexEnd',
				'cshKey' => '',
				'cshLabel' => $fieldId
		);
		return $fieldConfiguration;
	}


	/**
	 * Validate additional fields
	 *
	 * @param array $submittedData Reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return bool True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$validData = $this->validateEventFolderAdditionalField($submittedData, $parentObject);
		$validData &= $this->validateTyposcriptPageAdditionalField($submittedData, $parentObject);
		$validData &= $this->validateStarttimeAdditionalField($submittedData, $parentObject);
		$validData &= $this->validateEndtimeAdditionalField($submittedData, $parentObject);
		return $validData;
	}

	/**
	 * Checks if event folder field is correct
	 *
	 * @param array $submittedData Reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return bool True if data is valid
	 */
	public function validateEventFolderAdditionalField(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$validData = FALSE;
		if (!isset($submittedData['cal_eventIndexer_eventFolder'])) {
			$validData = TRUE;
		} elseif (preg_match('/^[0-9,]+$/',$submittedData['cal_eventIndexer_eventFolder'])) {
			$validData = TRUE;
		}
		return $validData;
	}

	/**
	 * Checks if typoscript page field is correct
	 *
	 * @param array $submittedData Reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return bool True if data is valid
	 */
	public function validateTyposcriptPageAdditionalField(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$validData = FALSE;
		if (!isset($submittedData['cal_eventIndexer_typoscriptPage'])) {
			$validData = TRUE;
		} elseif (preg_match('/^[0-9]+$/',$submittedData['cal_eventIndexer_typoscriptPage'])) {
			$validData = TRUE;
		}
		return $validData;
	}
	
	/**
	 * Checks if starttime field is correct
	 *
	 * @param array $submittedData Reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return bool True if data is valid
	 */
	public function validateStarttimeAdditionalField(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$validData = FALSE;
		if (isset($submittedData['cal_eventIndexer_starttime'])) {
			$validData = TRUE;
		}
		return $validData;
	}
	
	/**
	 * Checks if endtime field is correct
	 *
	 * @param array $submittedData Reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return bool True if data is valid
	 */
	public function validateEndtimeAdditionalField(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
		$validData = FALSE;
		if (isset($submittedData['cal_eventIndexer_endtime'])) {
			$validData = TRUE;
		}
		return $validData;
	}

	/**
	 * Save additional field in task
	 *
	 * @param array $submittedData Contains data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the current task object
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
		$task->eventFolder = $submittedData['cal_eventIndexer_eventFolder'];
		$task->typoscriptPage = $submittedData['cal_eventIndexer_typoscriptPage'];
		$task->starttime = $submittedData['cal_eventIndexer_starttime'];
		$task->endtime = $submittedData['cal_eventIndexer_endtime'];
	}

}
