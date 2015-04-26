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
 * Update wizzard after move of typoscript templates from EXT:cal/static/ to EXT:cal/Configuration/TypoScript/
 *
 * @author Mario Matzulla <mario@matzullas.de>
 */
class TypoScriptUpdateWizard extends \TYPO3\CMS\Install\Updates\AbstractUpdate {

	/**
	 * @var string
	 */
	protected $title = 'Migrate static_include_file relations of the cal extension';
	
	/**
	 * Returns the migration description
	 * @return string The description
	 */
	protected function getMigrationDescription() {
		return 'Found old references to EXT:cal/static/. This wizard will replace EXT:cal/static/ references to the new EXT:cal/Configuration/TypoScript/ folder.';
	}
	
	/**
	 * Checks if an update is needed
	 *
	 * @param string &$description The description for the update
	 * @return boolean TRUE if an update is needed, FALSE otherwise
	 */
	public function checkForUpdate(&$description) {
		$updateNeeded = FALSE;
		// Fetch records where the field media does not contain a plain integer value
		// * check whether media field is not empty
		// * then check whether media field does not contain a reference count (= not integer)
		$sql = $GLOBALS['TYPO3_DB']->SELECTquery(
			'COUNT(*)',
			'sys_template',
			'include_static_file like \'%XT:cal/static/%\''
		);
		$resultSet = $GLOBALS['TYPO3_DB']->sql_query($sql);
		$notMigratedRowsCount = 0;
		if ($resultSet !== FALSE) {
			list($notMigratedRowsCount) = $GLOBALS['TYPO3_DB']->sql_fetch_row($resultSet);
			$notMigratedRowsCount = (int)$notMigratedRowsCount;
			$GLOBALS['TYPO3_DB']->sql_free_result($resultSet);
		}
		if ($notMigratedRowsCount > 0) {
			$description = $this->getMigrationDescription();
			$updateNeeded = TRUE;
		}
		return $updateNeeded;
	}
	
	/**
	 * Performs the database update.
	 *
	 * @param array &$dbQueries Queries done in this update
	 * @param mixed &$customMessages Custom messages
	 * @return boolean TRUE on success, FALSE on error
	 */
	public function performUpdate(array &$dbQueries, &$customMessages) {
		$sql = 'UPDATE sys_template	SET include_static_file = replace(include_static_file,\'XT:cal/static/\',\'XT:cal/Configuration/TypoScript/\') WHERE include_static_file like \'%XT:cal/static/%\'';
		$resultSet = $GLOBALS['TYPO3_DB']->sql_query($sql);
		return TRUE;
	}

}
