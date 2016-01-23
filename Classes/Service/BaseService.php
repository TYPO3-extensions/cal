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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Cal\Controller\Registry;

/**
 * A base service.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
abstract class BaseService extends \TYPO3\CMS\Core\Service\AbstractService {
	var $cObj; // The backReference to the mother cObj object set at call time
	/**
	 * The rights service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\RightsService
	 */
	var $rightsObj;
	/**
	 * The model controller object
	 * 
	 * @var \TYPO3\CMS\Cal\Model\ModelController
	 */
	var $modelObj;
	
	/**
	 * The main controller object
	 * 
	 * @var \TYPO3\CMS\Cal\Controller\Controller
	 */
	var $controller;
	var $conf;
	var $prefixId = 'tx_cal_controller';
	
	/**
	 * The calendar service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\CalendarService
	 */
	var $calendarService;
	
	/**
	 * The category service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\CategoryService
	 */
	var $categoryService;
	
	/**
	 * The event service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\EventService
	 */
	var $eventService;
	
	/**
	 * The location service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\LocationService
	 */
	var $locationService;
	
	/**
	 * The locationAddress service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\LocationAddressService
	 */
	var $locationAddressService;
	
	/**
	 * The locationPartner service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\LocationPartnerService
	 */
	var $locationPartnerService;
	
	/**
	 * The organizer service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\OrganizerService
	 */
	var $organizerService;
	
	/**
	 * The organizerAddress service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\OrganizerAddressService
	 */
	var $organizerAddressService;
	
	/**
	 * The organizerPartner service object
	 * 
	 * @var \TYPO3\CMS\Cal\Service\OrganizerPartnerService
	 */
	var $organizerPartnerService;
	var $fileFunc;
	var $extConf;
	
	public function __construct() {
		$this->controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$this->conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$this->rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
		$this->cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$this->modelObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'modelcontroller');
		$this->extConf = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		if(! isset ($this->extConf ['categoryService'] )){
			$this->extConf ['categoryService'] = 'tx_cal_category';
		}
	}
	
	protected static function insertIdsIntoTableWithMMRelation($mm_table, $idArray, $uid, $tablename, $additionalParams = array(), $switchUidLocalForeign = false) {
		$uid_local = 'uid_local';
		$uid_foreign = 'uid_foreign';
		if($switchUidLocalForeign){
			$uid_local = 'uid_foreign';
			$uid_foreign = 'uid_local';
		}
		foreach ($idArray as $key => $foreignid) {
			if (is_numeric ($foreignid)) {
				$insertFields = array_merge (array (
						$uid_local => $uid,
						$uid_foreign => $foreignid,
						'tablenames' => $tablename,
						'sorting' => $key + 1 
				), $additionalParams);
				$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($mm_table, $insertFields);
				if (FALSE === $result){
					throw new \RuntimeException('Could not write '.$mm_table.' record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458138);
				}
			}
		}
	}
	
	protected static function splitUserAndGroupIds($allIds, &$userArray, &$groupArray) {
		foreach ($allIds as $value) {
			preg_match ('/(^[ug])_(.*)/', $value, $idname);
			if ($idname [1] == 'u') {
				$userArray [] = $idname [2];
			} else if ($idname [1] == 'g') {
				$groupArray [] = $idname [2];
			}
		}
	}
	
	protected static function _notifyOfChanges(&$event, &$insertFields) {
		$valueArray = $event->getValuesAsArray ();
		$notificationService = &\TYPO3\CMS\Cal\Utility\Functions::getNotificationService ();
		$notificationService->notifyOfChanges ($valueArray, $insertFields);
		self::_scheduleReminder ($event->getUid ());
	}
	
	protected static function _notify(&$insertFields) {
		$notificationService = &\TYPO3\CMS\Cal\Utility\Functions::getNotificationService ();
		$notificationService->notify ($insertFields);
	}
	
	protected function _invite(&$event) {
		$notificationService = &\TYPO3\CMS\Cal\Utility\Functions::getNotificationService ();
		$oldView = $this->conf ['view'];
		$this->conf ['view'] = 'ics';
		$eventValues = Array ();
		$eventValues ['uid'] = $event->getUid ();
		$notificationService->invite ($eventValues, $eventValues);
		$this->conf ['view'] = $oldView;
	}
	
	protected static function _scheduleReminder($eventUid) {
		$reminderService = &\TYPO3\CMS\Cal\Utility\Functions::getReminderService ();
		$reminderService->scheduleReminder ($eventUid);
	}
	
	protected static function stopReminder($uid) {
		$reminderService = &\TYPO3\CMS\Cal\Utility\Functions::getReminderService ();
		$reminderService->deleteReminderForEvent ($uid);
	}
	
	protected function searchForAdditionalFieldsToAddFromPostData(&$insertFields, $object, $isSave = true) {
		$fields = GeneralUtility::trimExplode (',', $this->conf ['rights.'] [$isSave ? 'create.' : 'edit.'] [$object . '.'] ['additionalFields'], 1);
		foreach ($fields as $field) {
			if (($isSave && $this->rightsObj->isAllowedTo ('create', $object, $field)) || (! $isSave && $this->rightsObj->isAllowedTo ('edit', $object, $field))) {
				if ($this->conf ['view.'] [$this->conf ['view'] . '.'] ['additional_fields.'] [$field . '_stdWrap.']) {
					$insertFields [$field] = $this->cObj->stdWrap ($this->controller->piVars [$field], $this->conf ['view.'] [$this->conf ['view'] . '.'] ['additional_fields.'] [$field . '_stdWrap.']);
				} else {
					$insertFields [$field] = $this->controller->piVars [$field];
				}
			}
		}
	}
	
	protected function checkOnNewOrDeletableFiles($objectType, $type, &$insertFields, $uid) {
		if ($this->conf ['view.'] ['enableAjax'] || $this->conf ['view.'] ['dontShowConfirmView'] == 1) {
			$insertFields [$type] = Array ();
			if (is_array ($_FILES [$this->prefixId] ['name'] [$type])) {
				$files = Array ();
				if ($this->controller->piVars [$type]) {
					$files = $this->controller->piVars [$type];
				}
				
				if (! $this->fileFunc) {
					$this->fileFunc = new \TYPO3\CMS\Core\Utility\File\BasicFileUtility();
					$all_files = Array ();
					$all_files ['webspace'] ['allow'] = '*';
					$all_files ['webspace'] ['deny'] = '';
					$this->fileFunc->init ('', $all_files);
				}
				$allowedExt = Array ();
				$denyExt = Array ();
				if ($type == 'file') {
					$allowedExt = explode (',', $GLOBALS ['TYPO3_CONF_VARS'] ['GFX'] ['imagefile_ext']);
				} else if ($type == 'attachment') {
					$allowedExt = explode (',', $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['allow']);
					$denyExt = explode (',', $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['deny']);
				}
				$removeFiles = $this->controller->piVars ['remove_' . $type] ? $this->controller->piVars ['remove_' . $type] : Array ();
				
				foreach ($_FILES [$this->prefixId] ['name'] [$type] as $id => $filename) {
					
					if ($_FILES [$this->prefixId] ['error'] [$type] [$id]) {
						continue;
					} else {
						$theFile = GeneralUtility::upload_to_tempfile ($_FILES [$this->prefixId] ['tmp_name'] [$type] [$id]);
						$fI = GeneralUtility::split_fileref ($filename);
						if (in_array ($fI ['fileext'], $denyExt)) {
							continue;
						} else if ($type == 'image' && ! empty ($allowedExt) && ! in_array ($fI ['fileext'], $allowedExt)) {
							continue;
						}
						$theDestFile = $this->fileFunc->getUniqueName ($this->fileFunc->cleanFileName ($fI ['file']), $uploadPath);
						GeneralUtility::upload_copy_move ($theFile, $theDestFile);
						$insertFields [$type] [] = basename ($theDestFile);
					}
				}
				
				foreach ($files as $file) {
					if (in_array ($file, $removeFiles)) {
						unlink ('typo3temp/' . $file);
					}
				}
			}
			$insertFields [$type] = implode (',', $insertFields [$type]);
		} else {
			$insertFields [$type] = $this->controller->piVars [$type];
			$this->checkOnTempFile ($type, $insertFields, $objectType, $uid);
		}
		
		$removeFiles = $this->controller->piVars ['remove_' . $type] ? $this->controller->piVars ['remove_' . $type] : Array ();
		if(!empty($removeFiles)){
			$where = 'uid_foreign = ' . $uid . ' AND  tablenames=\''.$objectType.'\' AND fieldname=\''.$type.'\' AND uid in ('.implode(',', array_values($removeFiles)).')';
			$result = $GLOBALS ['TYPO3_DB']->exec_DELETEquery ('sys_file_reference', $where);
			if (FALSE === $result){
				throw new \RuntimeException('Could not write sys_file_reference record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458138);
			}
		}
	}
	
	protected function checkOnTempFile($type, &$insertFields, $objectType, $uid) {
		$fileadminDirectory = rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/';
		/** @var $storageRepository \TYPO3\CMS\Core\Resource\StorageRepository */
		$storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\StorageRepository');
		$storages = $storageRepository->findAll();
		foreach ($storages as $tmpStorage) {
			$storageRecord = $tmpStorage->getStorageRecord();
			$configuration = $tmpStorage->getConfiguration();
			$isLocalDriver = $storageRecord['driver'] === 'Local';
			$isOnFileadmin = !empty($configuration['basePath']) && \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($configuration['basePath'], $fileadminDirectory);
			if ($isLocalDriver && $isOnFileadmin) {
				$storage = $tmpStorage;
				break;
			}
		}
		if (!isset($storage)) {
			throw new \RuntimeException('Local default storage could not be initialized - might be due to missing sys_file* tables.');
		}
		$fileFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
		$fileIndexRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\Index\\FileIndexRepository');
		$targetDirectory = PATH_site . $fileadminDirectory . 'user_upload/';
		if (is_array ($insertFields [$type])) {
			foreach ($insertFields [$type] as $file) {
				$this->_checkOnTempFile($storage, $fileIndexRepository, $targetDirectory, $type, $insertFields, $objectType, $file, $uid);
			}
		} else {
			$this->_checkOnTempFile($storage, $fileIndexRepository, $targetDirectory, $type, $insertFields, $objectType, $insertFields [$type], $uid);
		}
		$count = $GLOBALS ['TYPO3_DB']->exec_SELECTcountRows ('uid', 'sys_file_reference', 'uid_foreign = ' . $uid . ' AND tablenames = \''.$objectType.'\' AND fieldname = \''.$type.'\'');
		$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ($objectType, 'uid = '.$uid, Array($type => $count));
		if (FALSE === $result){
			throw new \RuntimeException('Could not write sys_file_reference record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458138);
		}
		unset($insertFields [$type]);
	}
	
	private function _checkOnTempFile(&$storage, &$fileIndexRepository, $targetDirectory, $type, &$insertFields, $objectType, $fileOrig, $uid) {
		if(strlen($fileOrig)==0){
			return;
		}
		$fileObject = null;
		if (substr ($fileOrig, 0, 7) == '__NEW__') {
			$file = substr ($fileOrig, 7);
			if (file_exists(PATH_site .'typo3temp/' .$file)) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::upload_copy_move(PATH_site .'typo3temp/'. $file, $targetDirectory . $file);
				$fileObject = $storage->getFile('user_upload/' . $file);
		
				$fileIndexRepository->add($fileObject);
				$dataArray = array(
						'uid_local' => $fileObject->getUid(),
						'tablenames' => $objectType,
						'uid_foreign' => $uid,
						// the sys_file_reference record should always placed on the same page
						// as the record to link to, see issue #46497
						'pid' => $insertFields['pid'],
						'fieldname' => $type,
						'sorting_foreign' => 0
				);
				foreach($this->controller->piVars[$type] as $id => $image){
					if($image == $fileOrig){
						if (isset($this->controller->piVars[$type.'_caption'][$id])) {
							$dataArray['description'] = $this->controller->piVars[$type.'_caption'][$id];
						}
						if (isset($this->controller->piVars[$type.'_title'][$id])) {
							$dataArray['title'] = $this->controller->piVars[$type.'_title'][$id];
						}
						break;
					}
				}
				
				$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_reference', $dataArray);
				if (FALSE === $result){
					throw new \RuntimeException('Could not write sys_file_reference record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458138);
				}
				unlink(PATH_site .'typo3temp/'. $file);
			}
		} else {
			$dataArray = Array();
			foreach($this->controller->piVars[$type] as $id => $image){
				if($image == $fileOrig){
					if (isset($this->controller->piVars[$type.'_caption'][$id])) {
						$dataArray['description'] = $this->controller->piVars[$type.'_caption'][$id];
					}
					if (isset($this->controller->piVars[$type.'_title'][$id])) {
						$dataArray['title'] = $this->controller->piVars[$type.'_title'][$id];
					}
					break;
				}
			}
			if(!empty($dataArray)){
				$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_file_reference','uid='.$fileOrig, $dataArray);
				if (FALSE === $result){
					throw new \RuntimeException('Could not write sys_file_reference record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458138);
				}
			}
		}
	}
	
	protected function getAdditionalWhereForLocalizationAndVersioning($table) {
		$localizationPrefix = 'l18n';
		$selectConf = Array();
		if('sys_category' == $table) {
			$localizationPrefix = 'l10n';
		}
		if ($GLOBALS ['TSFE']->sys_language_mode == 'strict' && $GLOBALS ['TSFE']->sys_language_content) {
			// sys_language_mode == 'strict': If a certain language is requested, select only news-records from the default language which have a translation. The translated articles will be overlayed later in the list or single function.
			
			$querryArray = $this->cObj->getQuery ($table, array (
					'selectFields' => $table . '.'.$localizationPrefix.'_parent',
					'where' => $table . '.sys_language_uid = ' . $GLOBALS ['TSFE']->sys_language_content,
					'pidInList' => $this->conf ['pidList'] 
			), true);
			
			$tmpres = $GLOBALS ['TYPO3_DB']->exec_SELECT_queryArray ($querryArray);
			
			$strictUids = Array ();
			
			while ($tmprow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($tmpres)) {
				$strictUids [] = $tmprow [$localizationPrefix.'_parent'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($tmpres);
			
			$strStrictUids = implode (',', $strictUids);
			$selectConf ['where'] .= '(' . $table . '.uid IN (' . ($strStrictUids ? $strStrictUids : 0) . ') OR ' . $table . '.sys_language_uid=-1)'; // sys_language_uid=-1 = [all languages]
		} else {
			// sys_language_mode != 'strict': If a certain language is requested, select only news-records in the default language. The translated articles (if they exist) will be overlayed later in the list or single function.
			$selectConf ['where'] .= $table . '.sys_language_uid IN (0,-1)';
		}
		
		if ($this->conf ['showRecordsWithoutDefaultTranslation']) {
			$selectConf ['where'] = ' (' . $selectConf ['where'] . ' OR (' . $table . '.sys_language_uid=' . $GLOBALS ['TSFE']->sys_language_content . ' AND NOT ' . $table . '.'.$localizationPrefix.'_parent))';
		}
		
		// filter Workspaces preview.
		// Since "enablefields" is ignored in workspace previews it's required to filter out news manually which are not visible in the live version AND the selected workspace.
		if ($GLOBALS ['TSFE']->sys_page->versioningPreview) {
			// execute the complete query
			$wsSelectconf = $selectConf;
			$wsSelectconf ['selectFields'] = 'uid,pid,tstamp,crdate,deleted,hidden,sys_language_uid,'.$localizationPrefix.'_parent,'.$localizationPrefix.'_diffsource,t3ver_oid,t3ver_id,t3ver_label,t3ver_wsid,t3ver_state,t3ver_stage,t3ver_count,t3ver_tstamp,t3_origuid';
			$wsRes = $this->cObj->exec_getQuery ($table, $wsSelectconf);
			$removeUids = Array ();
			while ($wsRow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($wsRes)) {
				$orgUid = $wsRow ['uid'];
				$GLOBALS ['TSFE']->sys_page->versionOL ($table, $wsRow);
				if (! $wsRow ['uid']) { // if versionOL returns nothing the record is not visible in the selected Workspace
					$removeUids [] = $orgUid;
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($wsRes);
			
			$removeUidList = implode (',', array_unique ($removeUids));
			
			// add list of not visible uids to the whereclause
			if ($removeUidList) {
				$selectConf ['where'] .= ' AND ' . $table . '.uid NOT IN (' . $removeUidList . ')';
			}
		}
		return ' AND ' . $selectConf ['where'];
	}
	
	protected static function checkUidForLanguageOverlay($uid, $table) {
		$select = $table . '.*';
		$where = $table . '.uid = ' . $uid;
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($result) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
				
				if ($GLOBALS ['TSFE']->sys_language_content) {
					$row = $GLOBALS ['TSFE']->sys_page->getRecordOverlay ($table, $row, $GLOBALS ['TSFE']->sys_language_content, $GLOBALS ['TSFE']->sys_language_contentOL, '');
				}
				if ($GLOBALS['TSFE']->sys_page->versioningPreview == TRUE) {
					// get workspaces Overlay
					$GLOBALS ['TSFE']->sys_page->versionOL ($table, $row);
				}
				if ($row ['_LOCALIZED_UID']) {
					$uid = $row ['_LOCALIZED_UID'];
				}
				return $uid;
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		}
		return $uid;
	}
	
	public function __toString() {
		return get_class ($this);
	}
}

?>