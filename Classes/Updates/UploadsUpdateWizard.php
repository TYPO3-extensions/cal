<?php
namespace TYPO3\CMS\Cal\Updates;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Upgrade wizard which goes through all files referenced in the tx_cal_event.attachment filed
 * and creates sys_file records as well as sys_file_reference records for the individual usages.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 */
class UploadsUpdateWizard extends \TYPO3\CMS\Cal\Updates\AbstractUpdateWizard {

	/**
	 * @var string
	 */
	protected $title = 'Migrate file relations of tx_cal_event "attachments"';

	/**
	 * Returns the migration description
	 * @return string The description
	 */
	protected function getMigrationDescription() {
		return 'There are Content Elements of type "upload" which are referencing files that are not using ' . ' the File Abstraction Layer. This wizard will move the files to fileadmin/' . self::FOLDER_ContentUploads . ' and index them.';
	}
	
	protected function getRecordTableName() {
		return 'tx_cal_event';
	}
	
	/**
	 * Returns the table column names
	 * @return array:string Array containing the table column names
	 */
	protected function getColumnNameArray() {
		return array('uid', 'pid', 'attachment', 'attachmentcaption');
	}
	
	protected function getColumnName() {
		return 'attachment';
	}

	/**
	 * Processes the actual transformation from CSV to sys_file_references
	 *
	 * @param array $record
	 * @return void
	 */
	protected function migrateRecord(array $record) {
		$collections = array();

		$files = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $record['attachment'], TRUE);
		$descriptions = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('
', $record['attachmentcaption']);
		$i = 0;
		foreach ($files as $file) {
			if (file_exists(PATH_site . 'uploads/tx_cal/media/' . $file)) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::upload_copy_move(PATH_site . 'uploads/tx_cal/media/' . $file, $this->targetDirectory . $file);
				$fileObject = $this->storage->getFile(self::FOLDER_ContentUploads . '/' . $file);
				//TYPO3 >= 6.2.0
				if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 6002000) {
					$this->fileIndexRepository->add($fileObject);
				} else {
					//TYPO3 6.1.0
					$this->fileRepository->addToIndex($fileObject);
				}
				$dataArray = array(
					'uid_local' => $fileObject->getUid(),
					'tablenames' => 'tx_cal_event',
					'uid_foreign' => $record['uid'],
					// the sys_file_reference record should always placed on the same page
					// as the record to link to, see issue #46497
					'pid' => $record['pid'],
					'fieldname' => 'attachment',
					'sorting_foreign' => $i
				);
				if (isset($descriptions[$i])) {
					$dataArray['description'] = $descriptions[$i];
				}
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_reference', $dataArray);
				unlink(PATH_site . 'uploads/tx_cal/media/' . $file);
			}
			$i++;
		}
		$this->cleanRecord($record, $i, $collections);
	}

	/**
	 * Removes the old fields from the database-record
	 *
	 * @param array $record
	 * @param integer $fileCount
	 * @param array $collectionUids
	 * @return void
	 */
	protected function cleanRecord(array $record, $fileCount, array $collectionUids) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_cal_event', 'uid = ' . $record['uid'], array(
			'attachment' => $fileCount,
			'attachmentcaption' => ''
		));
	}

	/**
	 * Returns the table and column mapping.
	 *
	 * @return array
	 */
	protected function getTableColumnMapping() {
		$mapping = array(
			'mapTableName' => 'tx_cal_event',
			'mapFieldNames' => array(
				'uid'          => 'uid',
				'pid'          => 'pid',
				'attachment'        => 'attachment',
				'attachmentcaption' => 'attachmentcaption',
			)
		);

		if ($GLOBALS['TYPO3_DB'] instanceof \TYPO3\CMS\Dbal\Database\DatabaseConnection) {
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dbal']['mapping']['tx_cal_event'])) {
				$mapping = array_merge_recursive($mapping, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dbal']['mapping']['tx_cal_event']);
			}
		}

		return $mapping;
	}
}
