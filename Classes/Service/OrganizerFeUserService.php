<?php
namespace TYPO3\CMS\Cal\Service;
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
 * Base model for the calendar organizer.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class OrganizerFeUserService extends \TYPO3\CMS\Cal\Service\BaseService {
	
	var $keyId = 'tx_feuser';
	var $tableId = 'fe_users';
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * @param integer $uid
	 * @param string $pidList
	 * @return void|\TYPO3\CMS\Cal\Model\OrganizerFeUser
	 */
	public function find($uid, $pidList) {
		if (! $this->isAllowedService ())
			return;
		if ($pidList == '') {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', 'uid=' . $uid . ' ' . $this->cObj->enableFields ('fe_users'));
		} else {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', ' pid IN (' . $pidList . ') AND uid=' . $uid . ' ' . $this->cObj->enableFields ('fe_users'));
		}
		if ($result) {
			$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result);
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			return new \TYPO3\CMS\Cal\Model\OrganizerFeUser($row, $pidList);
		}
	}
	
	/**
	 * Looks for an organizer with a given uid on a certain pid-list
	 * 
	 * @param string $pidList
	 *        	to search in
	 * @return array \TYPO3\CMS\Cal\Model\OrganizerFeUser
	 */
	public function findAll($pidList) {
		if (! $this->isAllowedService ())
			return;
		$organizer = array ();
		$orderBy = \TYPO3\CMS\Cal\Utility\Functions::getOrderBy ('fe_users');
		if ($pidList == '') {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', ' 1 = 1 ' . $this->cObj->enableFields ('fe_users'), '', $orderBy);
		} else {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('*', 'fe_users', ' pid IN (' . $pidList . ') ' . $this->cObj->enableFields ('fe_users'), '', $orderBy);
		}
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$organizer [] = new \TYPO3\CMS\Cal\Model\OrganizerFeUser( $row, $pidList);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		return $organizer;
	}
	
	/**
	 * Search for organizer
	 * 
	 * @param string $pidList
	 *        	to search in
	 * @return void|array \TYPO3\CMS\Cal\Model\OrganizerFeUser
	 */
	public function search($pidList = '', $searchword) {
		if (! $this->isAllowedService ())
			return;
		return $this->getOrganizerFromTable ($pidList, $this->searchWhere ($searchword));
	}
	
	/**
	 * Generates the sql query and builds organizer objects out of the result rows
	 * 
	 * @param string $pidList
	 *        	to search in
	 * @param string $additionalWhere
	 *        	where clause
	 * @return array \TYPO3\CMS\Cal\Model\OrganizerFeUser
	 */
	private function getOrganizerFromTable($pidList = '', $additionalWhere = '') {
		$organizers = Array ();
		$orderBy = \TYPO3\CMS\Cal\Utility\Functions::getOrderBy ($this->tableId);
		if ($pidList != '') {
			$additionalWhere .= ' AND ' . $this->tableId . '.pid IN (' . $pidList . ')';
		}
		$select = $this->tableId . '.*';
		$table = $this->tableId;
		$where = '1=1 ' . $additionalWhere . $this->cObj->enableFields ($this->tableId);
		$groupBy = '';
		$orderBy = \TYPO3\CMS\Cal\Utility\Functions::getOrderBy ($this->tableId);
		$limit = '';
		
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_organizer_feuser_service', 'organizerServiceClass', 'service');
		
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preGetOrganizerFromTableExec')) {
				$hookObj->preGetOrganizerFromTableExec ($this, $select, $table, $where, $groupBy, $orderBy, $limit);
			}
		}
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupBy, $orderBy, $limit);
		
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$organizers [] = new \TYPO3\CMS\Cal\Model\OrganizerFeUser($row, $pidList);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		return $organizers;
	}
	
	/**
	 * Generates a search where clause.
	 *
	 * @param string $sw:        	
	 * @return string
	 */
	public function searchWhere($sw) {
		if (! $this->isAllowedService ())
			return;
		$where = $this->cObj->searchWhere ($sw, $this->conf ['view.'] ['search.'] ['searchOrganizerFieldList'], 'fe_users');
		return $where;
	}
	
	/**
	 * Updates the organizer with the given $uid with the post data  
	 * @param integer $uid
	 * @return void|\TYPO3\CMS\Cal\Model\OrganizerFeUser
	 */
	function updateOrganizer($uid) {
		$insertFields = Array (
				'tstamp' => time () 
		);
		// TODO: Check if all values are correct
		
		$this->retrievePostData ($insertFields);
		$uid = $this->checkUidForLanguageOverlay ($uid, 'fe_users');
		// Creating DB records
		$table = 'fe_users';
		$where = 'uid = ' . $uid;
		$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ($table, $where, $insertFields);
		return $this->find ($uid, $this->conf ['pidList']);
	}
	
	/**
	 * Removes the organizer with the $uid
	 * @param integer $uid
	 */
	public function removeOrganizer($uid) {
		if (! $this->isAllowedService ())
			return;
		if ($this->rightsObj->isAllowedToDeleteOrganizer ()) {
			$updateFields = Array (
					'tstamp' => time (),
					'deleted' => 1 
			);
			$table = 'fe_users';
			$where = 'uid = ' . $uid;
			$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ($table, $where, $updateFields);
		}
	}
	
	/**
	 * Adds the attribute and provided post data to the array
	 * @param array $insertFields
	 */
	private function retrievePostData(&$insertFields) {
		if (! $this->isAllowedService ())
			return;
		$hidden = 0;
		if ($this->controller->piVars ['hidden'] == 'true' && ($this->rightsObj->isAllowedToEditOrganizerHidden () || $this->rightsObj->isAllowedToCreateOrganizerHidden ()))
			$hidden = 1;
		$insertFields ['hidden'] = $hidden;
		
		if ($this->rightsObj->isAllowedToEditOrganizerName () || $this->rightsObj->isAllowedToCreateOrganizerName ()) {
			$insertFields ['name'] = strip_tags ($this->controller->piVars ['name']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerDescription () || $this->rightsObj->isAllowedToCreateOrganizerDescription ()) {
			$insertFields ['title'] = $this->cObj->removeBadHTML ($this->controller->piVars ['title'], $this->conf);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerStreet () || $this->rightsObj->isAllowedToCreateOrganizerStreet ()) {
			$insertFields ['address'] = strip_tags ($this->controller->piVars ['address']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerZip () || $this->rightsObj->isAllowedToCreateOrganizerZip ()) {
			$insertFields ['zip'] = strip_tags ($this->controller->piVars ['zip']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerCity () || $this->rightsObj->isAllowedToCreateOrganizerCity ()) {
			$insertFields ['city'] = strip_tags ($this->controller->piVars ['city']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerPhone () || $this->rightsObj->isAllowedToCreateOrganizerPhone ()) {
			$insertFields ['phone'] = strip_tags ($this->controller->piVars ['phone']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerEmail () || $this->rightsObj->isAllowedToCreateOrganizerEmail ()) {
			$insertFields ['email'] = strip_tags ($this->controller->piVars ['email']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerImage () || $this->rightsObj->isAllowedToCreateOrganizerImage ()) {
			$insertFields ['image'] = strip_tags ($this->controller->piVars ['image']);
		}
		
		if ($this->rightsObj->isAllowedToEditOrganizerLink () || $this->rightsObj->isAllowedToCreateOrganizerLink ()) {
			$insertFields ['www'] = strip_tags ($this->controller->piVars ['www']);
		}
	}
	
	/**
	 * Saves an organizer at the page with the id $pid
	 * @param integer $pid
	 * @return void|\TYPO3\CMS\Cal\Model\OrganizerFeUser
	 */
	public function saveOrganizer($pid) {
		if (! $this->isAllowedService ())
			return;
		$crdate = time ();
		$insertFields = array (
				'pid' => $pid,
				'tstamp' => $crdate,
				'crdate' => $crdate 
		);
		// TODO: Check if all values are correct
		
		$hidden = 0;
		if ($this->controller->piVars ['hidden'] == 'true')
			$hidden = 1;
		$insertFields ['hidden'] = $hidden;
		if ($this->controller->piVars ['name'] != '') {
			$insertFields ['name'] = strip_tags ($this->controller->piVars ['name']);
		}
		if ($this->controller->piVars ['description'] != '') {
			$insertFields ['title'] = $this->cObj->removeBadHTML ($this->controller->piVars ['title']);
		}
		if ($this->controller->piVars ['street'] != '') {
			$insertFields ['address'] = strip_tags ($this->controller->piVars ['address']);
		}
		if ($this->controller->piVars ['zip'] != '') {
			$insertFields ['zip'] = strip_tags ($this->controller->piVars ['zip']);
		}
		if ($this->controller->piVars ['city'] != '') {
			$insertFields ['city'] = strip_tags ($this->controller->piVars ['city']);
		}
		if ($this->controller->piVars ['phone'] != '') {
			$insertFields ['phone'] = strip_tags ($this->controller->piVars ['phone']);
		}
		if ($this->controller->piVars ['email'] != '') {
			$insertFields ['email'] = strip_tags ($this->controller->piVars ['email']);
		}
		if ($this->controller->piVars ['image'] != '') {
			$insertFields ['image'] = strip_tags ($this->controller->piVars ['image']);
		}
		if ($this->controller->piVars ['link'] != '') {
			$insertFields ['www'] = strip_tags ($this->controller->piVars ['www']);
		}
		
		// Creating DB records
		$insertFields ['cruser_id'] = $this->rightsObj->getUserId ();
		$uid = $this->_saveOrganizer ($insertFields);
		return $this->find ($uid, $this->conf ['pidList']);
	}
	
	/**
	 * Does the database save
	 * @param array $insertFields
	 * @throws \RuntimeException
	 * @return integer the uid of the saved organizer
	 */
	private function _saveOrganizer(&$insertFields) {
		$table = 'fe_users';
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $insertFields);
		if (FALSE === $result){
			throw new \RuntimeException('Could not write '.$table.' record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458155);
		}
		$uid = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
		return $uid;
	}
	
	/**
	 * Checks if this service is allowed to be processed
	 * @return boolean
	 */
	public function isAllowedService() {
		$this->confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$useOrganizerStructure = ($this->confArr ['useOrganizerStructure'] ? $this->confArr ['useOrganizerStructure'] : 'tx_cal_location');
		if ($useOrganizerStructure == $this->keyId) {
			return true;
		}
		return false;
	}
	
	/**
	 * Creates a translation overlay record for a given organizer with the uid
	 * @param integer $uid
	 * @param integer $overlay
	 */
	public function createTranslation($uid, $overlay) {
		$table = 'fe_users';
		$select = $table . '.*';
		$where = $table . '.uid = ' . $uid;
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($result) {
			$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result);
			if (is_array ($row)) {
				unset ($row ['uid']);
				$crdate = time ();
				$row ['tstamp'] = $crdate;
				$row ['crdate'] = $crdate;
				$row ['l18n_parent'] = $uid;
				$row ['sys_language_uid'] = $overlay;
				$this->_saveOrganizer ($row);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		return;
	}
}

?>