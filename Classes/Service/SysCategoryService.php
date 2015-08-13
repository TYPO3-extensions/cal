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
 * Base model for the category.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class SysCategoryService extends \TYPO3\CMS\Cal\Service\BaseService {
	protected $categoryArrayByEventUid = array ();
	protected $categoryArrayByCalendarUid = null;
	protected $categoryArrayByUid = array ();
	protected $allCateogryIdsByParentId;
	protected $categoryArrayCached = Array ();
	public static $categoryToFilter;
	
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Looks for a category with a given uid on a certain pid-list
	 * 
	 * @param integer $uid
	 *        	to search for
	 * @param string $pidList
	 *        	to search in
	 * @return array array ($row)
	 */
	public function find($uid, $pidList) {
		$categoryIds = array ();
		$this->getCategoryArray ($pidList, $categoryIds, true);
		return $this->categoryArrayByUid [$uid];
	}
	
	/**
	 * Looks for all categorys on a certain pid-list
	 * 
	 * @param string $pidList
	 *        	to search in
	 * @return array array of array (array of $rows)
	 */
	public function findAll($pidList, &$categoryArrayToBeFilled) {
		$this->getCategoryArray ($pidList, $categoryArrayToBeFilled, true);
	}
	
	public function updateCategory($uid) {
		$insertFields = array (
				'tstamp' => time () 
		);
		// TODO: Check if all values are correct
		$this->searchForAdditionalFieldsToAddFromPostData ($insertFields, 'category', false);
		$this->retrievePostData ($insertFields);
		$uid = $this->checkUidForLanguageOverlay ($uid, 'sys_category');
		// Creating DB records
		$table = 'sys_category';
		$where = 'uid = ' . $uid;
		
		$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ($table, $where, $insertFields);
		
		$this->unsetPiVars ();
		return $this->find ($uid, $this->conf ['pidList']);
	}
	
	public function removeCategory($uid) {
		if ($this->rightsObj->isAllowedToDeleteCategory ()) {
			// 'delete' the category object
			$updateFields = array (
					'tstamp' => time (),
					'deleted' => 1 
			);
			$table = 'sys_category';
			$where = 'uid = ' . $uid;
			$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ($table, $where, $updateFields);
			
			// 'delete' all the events related to the category
			// $table = 'tx_cal_event';
			// $where = 'category_id = '.$uid;
			// $result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$updateFields);
			
			$this->unsetPiVars ();
		}
	}
	
	protected function retrievePostData(&$insertFields) {
		$hidden = 0;
		if ($this->controller->piVars ['hidden'] == '1' && ($this->rightsObj->isAllowedToEditCategoryHidden () || $this->rightsObj->isAllowedToCreateCategoryHidden ())) {
			$hidden = 1;
		}
		$insertFields ['hidden'] = $hidden;
		
		if ($this->rightsObj->isAllowedToEditCategoryTitle () || $this->rightsObj->isAllowedToCreateCategoryTitle ()) {
			$insertFields ['title'] = strip_tags ($this->controller->piVars ['title']);
		}
		
		if ($this->rightsObj->isAllowedToEditCategoryCalendar () || $this->rightsObj->isAllowedToCreateCategoryCalendar ()) {
			$insertFields ['calendar_id'] = intval ($this->controller->piVars ['calendar_id']);
		}
		
		if ($this->rightsObj->isAllowedToEditCategoryParent () || $this->rightsObj->isAllowedToCreateCategoryParent ()) {
			$insertFields ['parent_category'] = intval ($this->controller->piVars ['parent_category']);
		}
		
		if ($this->rightsObj->isAllowedToEditCategoryHeaderstyle () || $this->rightsObj->isAllowedToCreateCategoryHeaderstyle ()) {
			$insertFields ['headerstyle'] = strip_tags ($this->controller->piVars ['headerstyle']);
		}
		
		if ($this->rightsObj->isAllowedToEditCategoryBodystyle () || $this->rightsObj->isAllowedToCreateCategoryBodystyle ()) {
			$insertFields ['bodystyle'] = strip_tags ($this->controller->piVars ['bodystyle']);
		}
		
		if ($this->rightsObj->isAllowedToEditCategorySharedUser () || $this->rightsObj->isAllowedToCreateCategorySharedUser ()) {
			$insertFields ['shared_user_allowed'] = intval ($this->controller->piVars ['shared_user_allowed']);
		}
	}
	
	public function saveCategory($pid) {
		$crdate = time ();
		$insertFields = array (
				'pid' => $this->conf ['rights.'] ['create.'] ['calendar.'] ['saveCategoryToPid'] ? $this->conf ['rights.'] ['create.'] ['calendar.'] ['saveCategoryToPid'] : $pid,
				'tstamp' => $crdate,
				'crdate' => $crdate 
		);
		$this->searchForAdditionalFieldsToAddFromPostData ($insertFields, 'category');
		$this->retrievePostData ($insertFields);
		
		// Creating DB records
		$insertFields ['cruser_id'] = $this->rightsObj->getUserId ();
		$uid = $this->_saveCategory ($insertFields);
		$this->unsetPiVars ();
		return $this->find ($uid, $this->conf ['pidList']);
	}
	
	private function _saveCategory(&$insertFields) {
		$table = 'sys_category';
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $insertFields);
		if (FALSE === $result){
			throw new \RuntimeException('Could not write '.$table.' record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458158);
		}
		$uid = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
		return $uid;
	}
	
	public function getCategorySearchString($pidList, $includePublic) {
		if ($this->conf ['category'] != '') {
			$categorySearchString .= ' AND sys_category_record_mm.uid_local IN (' . $this->conf ['category'] . ')';
		}
		
		// Filter events by categories
		
		// Include categories
		if ($this->conf ['view.'] ['categoryMode'] == 1 && self::$categoryToFilter) {
			// Query to select all blacklisted events
			$sql = 'SELECT uid_foreign FROM sys_category_record_mm WHERE uid_local IN (' . self::$categoryToFilter . ')';
			// Add search substring with tx_cal_event.uid NOT IN
			$categorySearchString .= ' AND tx_cal_event.uid NOT IN (' . $sql . ')';
		}
		
		// Exclude categories
		if ($this->conf ['view.'] ['categoryMode'] == 2 && self::$categoryToFilter) {
			// Query to select all blacklisted events
			$sql = 'SELECT uid_foreign FROM sys_category_record_mm WHERE uid_local IN (' . self::$categoryToFilter . ')';
			// Add search substring with tx_cal_event.uid NOT IN
			$categorySearchString .= ' AND tx_cal_event.uid NOT IN (' . $sql . ')';
		}

		// Minimum match
		if ($this->conf ['view.'] ['categoryMode'] == 4 && self::$categoryToFilter) {
			$categorySearchString = '';
			$categories = explode(',',self::$categoryToFilter);
			for ($i = 0; $i < count($categories); $i++) {
				if ($i == 0) {
					$categorySearchString .= ' AND sys_category_record_mm.uid_local = "'.$categories[$i].'" ';
				} else {
					$categorySearchString .= ' AND (';

					$categorySearchString .= '	SELECT
													tx_cal_event'.$i.'.uid
												FROM
													sys_category_record_mm sys_category_record_mm'.$i.'
													JOIN tx_cal_event tx_cal_event'.$i.' ON sys_category_record_mm'.$i.'.uid_foreign = tx_cal_event'.$i.'.uid
												WHERE
													tx_cal_event'.$i.'.uid = tx_cal_event.uid
													AND sys_category_record_mm'.$i.'.uid_local = "'.$categories[$i].'"
												GROUP BY
													sys_category_record_mm'.$i.'.uid_local)';

				}
			}
		}

		return $categorySearchString;
	}
	
	/**
	 * Search for categories
	 */
	public function getCategoryArray($pidList, &$categoryArrayToBeFilled, $showPublicCategories = true) {
		if (! empty ($this->categoryArrayCached [md5 ($this->conf ['view.'] ['categoryMode'] . $this->conf ['view.'] ['allowedCategories'])])) {
			$categoryArrayToBeFilled [] = $this->categoryArrayCached [md5 ($this->conf ['view.'] ['categoryMode'] . $this->conf ['view.'] ['allowedCategories'])];
			return;
		}
		if ($this->rightsObj->isLoggedIn () && $showPublicCategories) {
			$feUserId = $this->rightsObj->getUserId ();
		} else if ($this->rightsObj->isLoggedIn ()) {
			$feUserId = $this->rightsObj->getUserId ();
		}
		
		$this->categoryArrayByUid = array ();
		$this->categoryArrayByEventUid = array ();
		$this->categoryArrayByCalendarUid = array ();
		
		$categoryIds = array ();
		$dbIds = array ();
		$fileIds = array ();
		$extUrlIds = array ();
		$additionalWhere = ' AND sys_category.pid IN (' . $pidList . ')';
		
		// compile category array
		$filterWhere = '';
		switch ($this->conf ['view.'] ['categoryMode']) {
			case 0 : // show all
				break;
			case 1 : // show selected
			case 3 :
				$allowedCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode (',', $this->cObj->stdWrap ($this->conf ['view.'] ['category'], $this->conf ['view.'] ['category.']), 1);
				if (! empty ($allowedCategories)) {
					$implodedAllowedCategories = implode (',', $allowedCategories);
					$filterWhere = ' AND sys_category.uid IN (' . $implodedAllowedCategories . ')';
					
					$select = 'sys_category.uid';
					$table = 'sys_category';
					$groupby = '';
					$orderby = '';
					$where = 'sys_category.uid NOT IN (' . $implodedAllowedCategories . ')';
					
					$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby, $orderby);
					$foundUids = array ();
					if ($result) {
						$excludedCategories = array ();
						while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
							$excludedCategories [] = $row ['uid'];
						}
						$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
						self::$categoryToFilter = implode (',', $excludedCategories);
					}
				}
				break;
			case 2 : // exclude selected
				$allowedCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode (',', $this->cObj->stdWrap ($this->conf ['view.'] ['category'], $this->conf ['view.'] ['category.']), 1);
				if (! empty ($allowedCategories)) {
					$implodedAllowedCategories = implode (',', $allowedCategories);
					$filterWhere = ' AND sys_category.uid NOT IN (' . $implodedAllowedCategories . ')';
					self::$categoryToFilter = $implodedAllowedCategories;
				}
				break;
			case 4 : // minimum match
				$allowedCategories = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode (',', $this->cObj->stdWrap ($this->conf ['view.'] ['category'], $this->conf ['view.'] ['category.']), 1);
				if (! empty ($allowedCategories)) {
					$implodedAllowedCategories = implode (',', $allowedCategories);
					self::$categoryToFilter = $implodedAllowedCategories;
				}
				break;
		}
		
		if (! $this->rightsObj->isCalAdmin () && $this->conf ['rights.'] [$this->conf ['view'] == 'create_event' ? 'create.' : 'edit.'] ['event.'] ['fields.'] ['category.'] ['allowedUids'] != '') {
			$filterWhere = ' AND sys_category.uid IN (' . $this->conf ['rights.'] [$this->conf ['view'] == 'create_event' ? 'create.' : 'edit.'] ['event.'] ['fields.'] ['category.'] ['allowedUids'] . ')';
		}
		
		$calendarService = &$this->modelObj->getServiceObjByKey ('cal_calendar_model', 'calendar', 'tx_cal_calendar');
		$calendarSearchString = $calendarService->getCalendarSearchString ($pidList, $showPublicCategories, $this->conf ['calendar'] ? $this->conf ['calendar'] : '');
		// Select all categories for the given pids
		$select = 'sys_category.*,tx_cal_calendar.title AS calendar_title,tx_cal_calendar.uid AS calendar_uid';
		$table = 'sys_category LEFT JOIN tx_cal_calendar ON sys_category.calendar_id=tx_cal_calendar.uid';
		$groupby = 'sys_category.uid';
		$orderby = 'calendar_id,sys_category.title ASC';
		$where = '1=1 ';
		$where .= $calendarSearchString;
		$where .= $this->cObj->enableFields ('tx_cal_calendar') . ' AND tx_cal_calendar.pid IN (' . $pidList . ') ' . $this->cObj->enableFields ('sys_category');
		$where .= $additionalWhere . $filterWhere;
		
		$where .= $this->getAdditionalWhereForLocalizationAndVersioning ('sys_category');
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby, $orderby);
		$foundUids = array ();
		$calendarUids = array ();
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if ($GLOBALS ['TSFE']->sys_language_content) {
					$row = $GLOBALS ['TSFE']->sys_page->getRecordOverlay ('sys_category', $row, $GLOBALS ['TSFE']->sys_language_content, $GLOBALS ['TSFE']->sys_language_contentOL, '');
				}
				if (! $row ['uid']) {
					continue;
				}
				if ($GLOBALS['TSFE']->sys_page->versioningPreview == TRUE) {
					// get workspaces Overlay
					$GLOBALS ['TSFE']->sys_page->versionOL ('sys_category', $row);
				}
				if (! $row ['uid']) {
					continue;
				}
				$category = $this->createCategory ($row);
				$foundUids [] = $row ['uid'];
				$calendarUids [] = $row ['calendar_uid'];
				
				$this->categoryArrayByUid [$row ['uid']] = $category;
				$this->categoryArrayByCalendarUid [$row ['calendar_uid'] . '###' . $row ['calendar_title'] . '###tx_cal_calendar'] [] = $category->getUid ();
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		
		$calendarsWithoutCategory = array_diff (\TYPO3\CMS\Core\Utility\GeneralUtility::intExplode (',', $this->conf ['view.'] ['calendar']), array_unique ($calendarUids));
		if (! empty ($calendarsWithoutCategory)) {
			$select = 'tx_cal_calendar.*';
			$table = 'tx_cal_calendar';
			$groupby = 'tx_cal_calendar.uid';
			$orderby = 'tx_cal_calendar.title ASC';
			$where = 'tx_cal_calendar.uid IN (' . implode (',', $calendarsWithoutCategory) . ')' . $calendarSearchString . $this->cObj->enableFields ('tx_cal_calendar');
			$where .= $this->getAdditionalWhereForLocalizationAndVersioning ('tx_cal_calendar');
			
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby, $orderby);
			if ($result) {
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
					if ($GLOBALS ['TSFE']->sys_language_content) {
						$row = $GLOBALS ['TSFE']->sys_page->getRecordOverlay ('tx_cal_calendar', $row, $GLOBALS ['TSFE']->sys_language_content, $GLOBALS ['TSFE']->sys_language_contentOL, '');
					}
					if (! $row ['uid']) {
						continue;
					}
					if ($GLOBALS['TSFE']->sys_page->versioningPreview == TRUE) {
						// get workspaces Overlay
						$GLOBALS ['TSFE']->sys_page->versionOL ('tx_cal_calendar', $row);
					}
					if (! $row ['uid']) {
						continue;
					}
					$this->categoryArrayByCalendarUid [$row ['uid'] . '###' . $row ['title'] . '###tx_cal_calendar'] = array ();
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			}
		}
		$additionalWhere = $filterWhere;
		// Select all global categories
		$select = 'sys_category.*';
		$table = 'sys_category';
		$groupby = 'sys_category.uid';
		$orderby = 'sys_category.title ASC';
		if (! empty ($foundUids)) {
			$additionalWhere .= ' AND sys_category.uid NOT IN (' . implode (',', $foundUids) . ')';
		}
		$where = 'sys_category.calendar_id = 0' . $this->cObj->enableFields ('sys_category') . $additionalWhere;
		$where .= $this->getAdditionalWhereForLocalizationAndVersioning ('sys_category');
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby, $orderby);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if ($GLOBALS ['TSFE']->sys_language_content) {
					$row = $GLOBALS ['TSFE']->sys_page->getRecordOverlay ('sys_category', $row, $GLOBALS ['TSFE']->sys_language_content, $GLOBALS ['TSFE']->sys_language_contentOL, '');
				}
				if (! $row ['uid']) {
					continue;
				}
				if ($GLOBALS['TSFE']->sys_page->versioningPreview == TRUE) {
					// get workspaces Overlay
					$GLOBALS ['TSFE']->sys_page->versionOL ('sys_category', $row);
				}
				if (! $row ['uid']) {
					continue;
				}
				
				$category = $this->createCategory ($row);
				$this->categoryArrayByUid [$row ['uid']] = $category;
				$this->categoryArrayByCalendarUid ['0###' . $this->controller->pi_getLL ('l_global_category')] [] = $category->getUid ();
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		
		// Map styles
		foreach ($this->categoryArrayByUid as $category) {
			$this->checkStyles ($category);
		}
		
		// Map categories to events
		$select = 'sys_category_record_mm.*';
		$table = 'sys_category_record_mm';
		$groupby = '';
		$orderby = 'uid_local ASC, sorting ASC';
		$where = 'tablenames = "tx_cal_event" and fieldname = "category_id"';
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby, $orderby);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if ($this->categoryArrayByUid [$row ['uid_local']]) {
					$this->categoryArrayByEventUid [$row ['uid_foreign']] [] = $this->categoryArrayByUid [$row ['uid_local']];
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		
		if ($this->conf ['view.'] ['freeAndBusy.'] ['enable']) {
			$select = 'sys_category.*, tx_cal_calendar.title AS calendar_title';
			$where = 'sys_category.shared_user_allowed = 1';
			$where .= $calendarService->getCalendarSearchString ($pidList, $showPublicCategories, $this->conf ['view.'] ['calendar'] ? $this->conf ['view.'] ['calendar'] : '');
			// ' AND tx_cal_event_shared_user_mm.uid_foreign = '.$this->rightsObj->getUserId();
			$where .= $this->cObj->enableFields ('tx_cal_calendar') . $this->cObj->enableFields ('sys_category') . $this->cObj->enableFields ('tx_cal_event');
			$where .= $this->getAdditionalWhereForLocalizationAndVersioning ('sys_category');
			$table = 'tx_cal_event LEFT JOIN tx_cal_event_shared_user_mm ON tx_cal_event.uid = tx_cal_event_shared_user_mm.uid_local ' . 'LEFT JOIN tx_cal_calendar ON tx_cal_event.calendar_id = tx_cal_calendar.uid ' . 'LEFT JOIN sys_category ON tx_cal_calendar.uid = sys_category.calendar_id';
			// $groupby = 'tx_cal_category.uid';
			
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby);
			if ($result) {
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
					if ($GLOBALS ['TSFE']->sys_language_content) {
						$row = $GLOBALS ['TSFE']->sys_page->getRecordOverlay ('sys_category', $row, $GLOBALS ['TSFE']->sys_language_content, $GLOBALS ['TSFE']->sys_language_contentOL, '');
					}
					if (! $row ['uid']) {
						continue;
					}
					if ($GLOBALS['TSFE']->sys_page->versioningPreview == TRUE) {
						// get workspaces Overlay
						$GLOBALS ['TSFE']->sys_page->versionOL ('sys_category', $row);
					}
					if (! $row ['uid']) {
						continue;
					}
					
					$category = $this->createCategory ($row);
					$this->categoryArrayByEventUid [$row ['uid_local']] [] = $category;
					$this->categoryArrayByUid [$row ['uid']] = $category;
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			}
		}
		
		$categoryStringByUid = implode (',', array_keys ($this->categoryArrayByUid));
		$categoryMultiArray = array (
				$this->categoryArrayByUid,
				$this->categoryArrayByEventUid,
				$this->categoryArrayByCalendarUid 
		);
		if ($categoryStringByUid) {
			$this->categoryArrayCached [md5 ($this->conf ['view.'] ['categoryMode'] . $categoryStringByUid)] = $categoryMultiArray;
		}
		$categoryArrayToBeFilled [] = $categoryMultiArray;
	}
	
	private function addChildCategories(&$categoryArray) {
		$calTreeView = new \TYPO3\CMS\Cal\TreeProvider\TreeView();
		
		$ids = Array ();
		$knownUids = Array ();
		foreach ($categoryArray as $category) {
			$ids = $calTreeView->checkChildIds ($category->row, $this->getAllCategoryIdsByParentId ());
			$knownUids [] = $category->getUid ();
		}
		$stillNeededChildCategoryIds = array_diff ($ids, $knownUids);
		if (! empty ($stillNeededChildCategoryIds)) {
			$select = 'sys_category.*';
			$table = 'sys_category';
			$where = 'sys_category.uid IN (' . implode (',', $stillNeededChildCategoryIds) . ')';
			$childCategories = $this->getCategoriesFromTable ($select, $table, $where, $groupby);
		}
	}
	
	private function getAllCategoryIdsByParentId() {
		if ($this->allCateogryIdsByParentId == null) {
			
			$categories = array ();
			$select = 'sys_category.uid, sys_category.parent_category';
			$table = 'sys_category';
			$where = '1=1' . $this->cObj->enableFields ('sys_category');
			$groupby = '';
			
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby);
			if ($result) {
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
					$categories [$row ['parent_category']] = $row ['uid'];
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
			}
			$this->allCateogryIdsByParentId = $categories;
		}
		return $this->allCateogryIdsByParentId;
	}
	
	public function getCategoriesForSharedUser() {
		$categories = array ();
		$select = '*';
		$table = 'tx_cal_event LEFT JOIN tx_cal_event_shared_user_mm ON tx_cal_event.uid = tx_cal_event_shared_user_mm.uid_local ' . 'LEFT JOIN tx_cal_calendar ON tx_cal_event.calendar_id = tx_cal_calendar.uid ' . 'LEFT JOIN sys_category ON tx_cal_calendar.uid = sys_category.calendar_id';
		$where = 'sys_category.shared_user_allowed = 1' . ' AND tx_cal_event_shared_user_mm.uid_foreign = ' . $this->rightsObj->getUserId () . $this->cObj->enableFields ('tx_cal_calendar') . $this->cObj->enableFields ('sys_category') . $this->cObj->enableFields ('tx_cal_event');
		
		$groupby = '';
		
		return $this->getCategoriesFromTable ($select, $table, $where, $groupby);
	}
	
	private function getCategoriesFromTable($select, $table, $where, $groupby = '') {
		$categories = array ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				if ($GLOBALS ['TSFE']->sys_language_content) {
					$row = $GLOBALS ['TSFE']->sys_page->getRecordOverlay ('sys_category', $row, $GLOBALS ['TSFE']->sys_language_content, $GLOBALS ['TSFE']->sys_language_contentOL, '');
				}
				if (! $row ['uid']) {
					continue;
				}
				
				$GLOBALS ['TSFE']->sys_page->versionOL ('sys_category', $row);
				$GLOBALS ['TSFE']->sys_page->fixVersioningPid ('sys_category', $row);
				
				if (! $row ['uid']) {
					continue;
				}
				$categories [$row ['uid']] = $row;
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		return $categories;
	}
	
	public function createCategory($row) {
		return new \TYPO3\CMS\Cal\Model\CategoryModel( $row, $this->getServiceKey ());
	}
	
	public function getCategoriesForEvent($eventUid) {
		if (count ($this->categoryArrayByEventUid) == 0) {
			$cats = array ();
			$this->findAll ($this->conf ['pidList'], $cats);
		}
		return $this->categoryArrayByEventUid [$eventUid];
	}
	
	public function checkStyles(&$category) {
		$headerStyle = $category->getHeaderStyle ();
		if ($headerStyle == '') {
			$parentUid = $category->getParentUid ();
			if ($parentUid == 0) {
				$category->setHeaderStyle ($this->conf ['view.'] ['category.'] ['category.'] ['defaultHeaderStyle']);
				$category->setBodyStyle ($this->conf ['view.'] ['category.'] ['category.'] ['defaultBodyStyle']);
			} else {
				if ($this->categoryArrayByUid [$parentUid]) {
					$this->checkStyles ($this->categoryArrayByUid [$parentUid]);
					$category->setHeaderStyle ($this->categoryArrayByUid [$parentUid]->getHeaderStyle ());
					$category->setBodyStyle ($this->categoryArrayByUid [$parentUid]->getBodyStyle ());
				} else {
					$category->setHeaderStyle ($this->conf ['view.'] ['category.'] ['category.'] ['defaultHeaderStyle']);
					$category->setBodyStyle ($this->conf ['view.'] ['category.'] ['category.'] ['defaultBodyStyle']);
				}
			}
		}
		$this->categoryArrayByUid [$category->getUid ()] = $category;
	}
	
	private function unsetPiVars() {
		unset ($this->controller->piVars ['hidden']);
		unset ($this->controller->piVars ['uid']);
		unset ($this->controller->piVars ['calendar']);
		unset ($this->controller->piVars ['type']);
		unset ($this->controller->piVars ['calendar_id']);
		unset ($this->controller->piVars ['category']);
		unset ($this->controller->piVars ['shared_user_allowed']);
		unset ($this->controller->piVars ['headerstyle']);
		unset ($this->controller->piVars ['bodystyle']);
		unset ($this->controller->piVars ['parent_category']);
		unset ($this->controller->piVars ['title']);
	}
	
	public function createTranslation($uid, $overlay) {
		$table = 'sys_category';
		$select = $table . '.*';
		$where = $table . '.uid = ' . $uid;
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				unset ($row ['uid']);
				$crdate = time ();
				$row ['tstamp'] = $crdate;
				$row ['crdate'] = $crdate;
				$row ['l18n_parent'] = $uid;
				$row ['sys_language_uid'] = $overlay;
				$this->_saveCategory ($row);
				return;
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
	}
	
	public function enhanceEventQuery(&$select, &$table, &$where, &$groupBy, &$orderBy) {
		$select .= ', sys_category_record_mm.uid_local AS category_uid ';
		$table .= ' LEFT JOIN sys_category_record_mm ON sys_category_record_mm.uid_foreign = tx_cal_event.uid';
		$where .= $this->getCategorySearchString ($this->conf ['pidList'], true);;
		$groupBy = 'tx_cal_event.uid';
		if ($this->conf ['view.'] ['joinCategoryByAnd']) {
			$categoryArray = GeneralUtility::trimExplode (',', $this->conf ['category'], 1);
			$groupBy .= ', sys_category_record_mm.uid_local HAVING count(*) =' . count ($categoryArray);
		}
		$orderBy .= ', tx_cal_event.uid,sys_category_record_mm.sorting';
			
		if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['additionalCategoryWhere']) {
			$where .= ' ' . $this->cObj->cObjGetSingle ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['additionalCategoryWhere'], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['event.'] ['additionalCategoryWhere.']);
		}
	}
	
	public function getUidsOfEventsWithCategories() {
		
		$uidCollector = Array();
		$select = 'sys_category_record_mm.*, tx_cal_event.pid, tx_cal_event.uid';
		$table = 'sys_category_record_mm LEFT JOIN tx_cal_event ON tx_cal_event.uid = sys_category_record_mm.uid_foreign';
		$groupby = 'sys_category_record_mm.uid_foreign';
		$orderby = '';
		$where = 'tx_cal_event.pid IN (' . $this->conf ['pidList'] . ')';
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where, $groupby, $orderby);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				$uidCollector [] = $row ['uid_foreign'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		return $uidCollector;
	}
}

?>
