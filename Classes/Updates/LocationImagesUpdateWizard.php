<?php
namespace TYPO3\CMS\Cal\Updates;
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
 * Upgrade wizard which goes through all files referenced in the tx_cal_location.image filed
 * and creates sys_file records as well as sys_file_reference records for the individual usages.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 */
class LocationImagesUpdateWizard extends \TYPO3\CMS\Cal\Updates\AbstractImagesUpdateWizard {

	/**
	 * @var string
	 */
	protected $title = 'Migrate file relations of tx_cal_location "image"';

	/**
	 * Returns the migration description
	 * @return string The description
	 */
	protected function getMigrationDescription() {
		return 'There are calendar location with an "image" which are referencing files that are not using ' . ' the File Abstraction Layer. This wizard will move the files to fileadmin/' . self::FOLDER_ContentUploads . ' and index them.';
	}
	
	protected function getRecordTableName() {
		return 'tx_cal_location';
	}
	
}
