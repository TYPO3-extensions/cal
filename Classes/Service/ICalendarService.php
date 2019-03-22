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
use TYPO3\CMS\Backend\Utility\BackendUtility;

define ( 'ICALENDAR_PATH', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ( 'cal' ) . 'Classes/Model/ICalendar.php' );

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class ICalendarService extends \TYPO3\CMS\Cal\Service\BaseService {
	public function __construct() {
		parent::__construct ();
	}
	
	/**
	 * Looks for an external calendar with a given uid on a certain pid-list
	 *
	 * @param integer $uid
	 *        	to search for
	 * @param string $pidList
	 *        	to search in
	 * @return array array ($row)
	 */
	public function find($uid, $pidList = '') {
		$enableFields = '';
		if (TYPO3_MODE == 'BE') {
			$enableFields = BackendUtility::BEenableFields ( 'tx_cal_calendar' ) . ' AND tx_cal_calendar.deleted = 0';
		} else {
			$enableFields = $this->cObj->enableFields ( 'tx_cal_calendar' );
		}
		if ($pidList == '') {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'tx_cal_calendar', ' type IN (1,2) AND uid=' . $uid . ' ' . $enableFields );
		} else {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'tx_cal_calendar', ' type IN (1,2) AND pid IN (' . $pidList . ') AND uid=' . $uid . ' ' . $enableFields );
		}
		if ($result) {
			$row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result );
			$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
			return $row;
		}
		return array ();
	}
	
	/**
	 * Looks for all external calendars on a certain pid-list
	 *
	 * @param string $pidList
	 *        	to search in
	 * @return array array of array (array of $rows)
	 */
	public function findAll($pidList) {
		$enableFields = '';
		$orderBy = \TYPO3\CMS\Cal\Utility\Functions::getOrderBy ( 'tx_cal_calendar' );
		if (TYPO3_MODE == 'BE') {
			$enableFields = BackendUtility::BEenableFields ( 'tx_cal_calendar' ) . ' AND tx_cal_calendar.deleted = 0';
		} else {
			$enableFields = $this->cObj->enableFields ( 'tx_cal_calendar' );
		}
		$return = array ();
		if ($pidList == '') {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'tx_cal_calendar', ' type IN (1,2) ' . $enableFields, '', $orderBy );
		} else {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'tx_cal_calendar', ' type IN (1,2) AND pid IN (' . $pidList . ') ' . $enableFields, '', $orderBy );
		}
		if ($result) {
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				$return [] = $row;
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
		}
		
		return $return;
	}
	
	/**
	 *
	 * @param int $uid
	 *        	The calendar record uid
	 * @throws \RuntimeException
	 */
	public function update($uid) {
		$calendar = $this->find ( $uid );
		
		if ($calendar ['type'] == 2) {
			$url = GeneralUtility::getFileAbsFileName ( 'uploads/tx_cal/ics/' . $calendar ['ics_file'] );
		} else {
			$url = $calendar ['ext_url'];
		}
		
		$newMD5 = $this->updateEvents ( $uid, $calendar ['pid'], $url, $calendar ['md5'], $calendar ['cruser_id'] );
		
		/* If the events changed, update the calendar in the DB */
		if ($newMD5) {
			/* Update the calendar */
			$insertFields = array (
					'tstamp' => time (),
					'md5' => $newMD5 
			);
			$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ( 'tx_cal_calendar', 'uid=' . $uid, $insertFields );
			if (FALSE === $result) {
				throw new \RuntimeException ( 'Could not write new md5 hash to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1456171285 );
			}
		}
		
		$this->scheduleUpdates ( $calendar ['refresh'], $uid );
	}
	
	/**
	 * Updates an existing calendar
	 *
	 * @param int $uid
	 *        	The calendar record uid
	 * @param int $pid
	 *        	The page id
	 * @param string $urlString
	 *        	The url to get the ics content from
	 * @param string $md5
	 *        	The md5 hash of the current content
	 * @param int $cruser_id
	 *        	The create user id
	 * @return string|boolean False or the new md5 hash
	 */
	public function updateEvents($uid, $pid, $urlString, $md5, $cruser_id) {
		$urls = GeneralUtility::trimExplode ( "\n", $urlString, 1 );
		$mD5Array = Array ();
		$contentArray = Array ();
		
		foreach ( $urls as $key => $url ) {
			/* If the calendar has a URL, get a checksum on the contents */
			if ($url != '') {
				$contents = GeneralUtility::getURL ( $url );
				
				$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ( 'tx_cal_icalendar_service', 'importIcsContent', 'service' );
				
				// Hook: configuration
				foreach ( $hookObjectsArr as $hookObj ) {
					if (method_exists ( $hookObj, 'importIcsContent' )) {
						$hookObj->importIcsContent ( $contents );
					}
				}
				
				$mD5Array [$key] = md5 ( $contents );
				$contentArray [$key] = $contents;
			}
		}
		
		$newMD5 = md5 ( implode ( '', $mD5Array ) );
		
		/* If the calendar has changed */
		if ($newMD5 != $md5) {
			
			$notInUids = Array ();
			
			foreach ( $contentArray as $contents ) {
				/* Parse the contents into ICS data structure */
				$iCalendar = $this->getiCalendarFromICSFile ( $contents );
				
				/* Create new events belonging to the specified calendar */
				$notInUids = array_merge ( $notInUids, $this->insertCalEventsIntoDB ( $iCalendar->_components, $uid, $pid, $cruser_id ) );
			}
			
			$notInUids = array_unique ( $notInUids );
			
			/* Delete old events, that have not been updated */
			$this->deleteTemporaryEvents ( $uid, $notInUids );
			
			\TYPO3\CMS\Cal\Utility\Functions::clearCache ();
			
			return $newMD5;
		} else {
			return false;
		}
	}
	
	/**
	 * Schedules future updates using the scheduling engine.
	 *
	 * @param integer $refreshInterval
	 *        	Frequency (in minutes) between calendar updates.
	 * @param integer $uid
	 *        	UID of the calendar to be updated.
	 */
	public function scheduleUpdates($refreshInterval, $uid) {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ( 'scheduler' )) {
			$recurring = $refreshInterval * 60;
			/* If calendar has a refresh time, schedule recurring gabriel event for refresh */
			if ($recurring) {
				$calendarRow = BackendUtility::getRecordRaw ( 'tx_cal_calendar', 'uid=' . $uid );
				$taskId = $calendarRow ['schedulerId'];
				
				$scheduler = new \TYPO3\CMS\Scheduler\Scheduler ();
				
				if ($taskId > 0) {
					try {
						$task = $scheduler->fetchTask ( $taskId );
						$execution = new \TYPO3\CMS\Scheduler\Execution ();
						$execution->setStart ( time () + $recurring );
						$execution->setIsNewSingleExecution ( true );
						$execution->setMultiple ( true );
						$task->setExecution ( $execution );
						$scheduler->saveTask ( $task );
					} catch ( OutOfBoundsException $e ) {
						$this->createSchedulerTask ( $scheduler, $recurring, $uid );
					}
				} else {
					$this->createSchedulerTask ( $scheduler, $recurring, $uid );
				}
			}
		}
	}
	
	/**
	 *
	 * @param unknown $scheduler        	
	 * @param unknown $offset        	
	 * @param unknown $calendarUid        	
	 * @throws \RuntimeException
	 */
	public function createSchedulerTask(&$scheduler, $offset, $calendarUid) {
		/* Set up the scheduler event */
		$task = new \TYPO3\CMS\Cal\Cron\CalendarScheduler ();
		$task->setUID ( $calendarUid );
		$taskGroup = BackendUtility::getRecordRaw ( 'tx_scheduler_task_group', 'groupName="cal"' );
		if ($taskGroup ['uid']) {
			$task->setTaskGroup ( $taskGroup ['uid'] );
		} else {
			$crdate = time ();
			$insertFields = Array ();
			$insertFields ['pid'] = 0;
			$insertFields ['tstamp'] = $crdate;
			$insertFields ['crdate'] = $crdate;
			$insertFields ['cruser_id'] = 0;
			$insertFields ['groupName'] = 'cal';
			$insertFields ['description'] = 'Calendar Base';
			$table = 'tx_scheduler_task_group';
			$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( $table, $insertFields );
			if (FALSE === $result) {
				throw new \RuntimeException ( 'Could not write ' . $table . ' record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458142 );
			}
			$uid = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
			$task->setTaskGroup ( $uid );
		}
		$task->setDescription ( 'Import of external calendar (calendar_id=' . $calendarUid . ')' );
		/* Schedule the event */
		$execution = new \TYPO3\CMS\Scheduler\Execution ();
		$execution->setStart ( time () + ($offset) );
		$execution->setIsNewSingleExecution ( true );
		$execution->setMultiple ( true );
		$task->setExecution ( $execution );
		$scheduler->addTask ( $task );
		$GLOBALS ['TYPO3_DB']->exec_UPDATEquery ( 'tx_cal_calendar', 'uid=' . $calendarUid, Array (
				'schedulerId' => $task->getTaskUid () 
		) );
	}
	
	/**
	 *
	 * @param int $calendarUid
	 *        	The calendar uid to get the task id from (database)
	 */
	public function deleteSchedulerTask($calendarUid) {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ( 'scheduler' )) {
			$calendarRow = BackendUtility::getRecordRaw ( 'tx_cal_calendar', 'uid=' . $calendarUid );
			$taskId = $calendarRow ['schedulerId'];
			if ($taskId > 0) {
				$scheduler = new \TYPO3\CMS\Scheduler\Scheduler ();
				
				$task = $scheduler->fetchTask ( $taskId );
				
				$task->setDisabled ( true );
				$task->remove ();
				$task->save ();
			}
		}
	}
	
	/**
	 *
	 * @param int $uid
	 *        	The crid of the gabriel record
	 */
	public function deleteScheduledUpdates($uid) {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ( 'gabriel' )) {
			$eventUID = 'tx_cal_calendar:' . $uid;
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_gabriel', ' crid="' . $eventUID . '"' );
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_gabriel', ' nextexecution=0' );
		}
	}
	
	/**
	 * Deletes temporary events on a given calendar.
	 *
	 * @param int $uid
	 *        	The uid of the calendar
	 * @param array $eventUidsNotIn
	 *        	Event uids not to be deleted
	 */
	public function deleteTemporaryEvents($uid, $eventUidsNotIn = Array()) {
		if (intval ( $uid ) > 0) {
			$additionalWhere = '';
			if (! empty ( $eventUidsNotIn )) {
				$additionalWhere = ' AND uid NOT IN (' . implode ( ',', $eventUidsNotIn ) . ')';
			}
			/* Delete the calendar events */
			$where = ' calendar_id=' . $uid . ' AND isTemp=1' . $additionalWhere;
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'uid', 'tx_cal_event', $where );
			$uids = Array ();
			if ($result) {
				while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
					$uids [] = $row ['uid'];
					$this->clearAllImagesAndAttachments( $row ['uid'] );
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
			}
			$this->deleteExceptions ( $uids );
			$this->deleteDeviations ( $uids );
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_event', $where );
			
			/* Delete any scheduled events (tasks) in gabriel */
			$this->deleteScheduledUpdates ( $uid );
		}
	}
	
	/**
	 * Deletes all deviation relations to the given event uids
	 *
	 * @param array $eventUidArray
	 *        	The given event uids
	 */
	private function deleteDeviations($eventUidArray = Array()) {
		if (! empty ( $eventUidArray )) {
			$where = 'tx_cal_event_deviation.parentid in (' . implode ( ',', $eventUidArray ) . ')';
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_event_deviation', $where );
		}
	}
	
	/**
	 * Deletes all exception relations to the given event uids
	 *
	 * @param array $eventUidArray
	 *        	The given event uids
	 */
	private function deleteExceptions($eventUidArray = Array()) {
		if (! empty ( $eventUidArray )) {
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'tx_cal_exception_event.uid', 'tx_cal_exception_event_mm inner join tx_cal_exception_event on tx_cal_exception_event_mm.uid_foreign = tx_cal_exception_event.uid', 'tx_cal_exception_event_mm.uid_local in (' . implode ( ',', $eventUidArray ) . ') and tx_cal_exception_event_mm.tablenames = "tx_cal_exception_event"' );
			$exceptionEventUids = Array ();
			if ($result) {
				while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
					$exceptionEventUids [] = $row ['uid'];
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
			}
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'tx_cal_exception_event_group.uid', 'tx_cal_exception_event_group_mm inner join tx_cal_exception_event_group on tx_cal_exception_event_group_mm.uid_foreign = tx_cal_exception_event_group.uid', 'tx_cal_exception_event_group_mm.uid_local in (' . implode ( ',', $eventUidArray ) . ') and tx_cal_exception_event_group_mm.tablenames = "tx_cal_exception_group"' );
			$exceptionGroupUids = Array ();
			if ($result) {
				while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
					$exceptionGroupUids [] = $row ['uid'];
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
			}
			if (! empty ( $exceptionEventUids )) {
				$where = 'tx_cal_exception_event.uid in (' . implode ( ',', $exceptionEventUids ) . ')';
				$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event', $where );
				$where = 'tx_cal_exception_event_mm.uid_foreign in (' . implode ( ',', $exceptionEventUids ) . ') and tablenames="tx_cal_exception_event"';
				$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event_mm', $where );
			}
			if (! empty ( $exceptionGroupUids )) {
				$where = 'tx_cal_exception_group.uid in (' . implode ( ',', $exceptionGroupUids ) . ')';
				$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_group', $where );
				$where = 'tx_cal_exception_event_mm.uid_foreign in (' . implode ( ',', $exceptionGroupUids ) . ') and tablenames="tx_cal_exception_group"';
				$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event_mm', $where );
			}
		}
	}
	
	/**
	 * Deletes temporary categories on a given calendar
	 *
	 * @param int $uid
	 *        	The uid of the calendar
	 */
	public function deleteTemporaryCategories($uid) {
		/* Delete the calendar categories */
		$where = ' calendar_id=' . $uid;
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( $this->extConf ['categoryService'], $where );
	}
	
	/**
	 *
	 * @param int $uid
	 *        	The calendar uid
	 */
	public function deleteScheduledUpdatesFromCalendar($uid) {
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'uid', 'tx_cal_event', 'calendar_id=' . $uid );
		$resultUids = Array ();
		if ($result) {
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				$resultUids [] = $row ['uid'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
		}
		if (! empty ( $resultUids )) {
			$crids = '"tx_cal_event:' . implode ( '","tx_cal_event:', $resultUids ) . '"';
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_gabriel', 'crid in (' . $crids . ')' );
		}
	}
	
	/**
	 * Returns a parsed ICalendar object of some ics content
	 *
	 * @param string $text
	 *        	The ics content
	 * @return \TYPO3\CMS\Cal\Model\ICalendar
	 * @throws \RuntimeException
	 */
	public function getiCalendarFromIcsFile($text) {
		require_once (ICALENDAR_PATH);
		$iCalendar = new \TYPO3\CMS\Cal\Model\ICalendar ();
		if (! $iCalendar->parsevCalendar ( $text )) {
			throw new \RuntimeException ( 'Could not parse vCalendar data ' . $text, 1451245373 );
		}
		return $iCalendar;
	}
	private function getDtstart($component) {
		return $this->getDateValue ( $component, 'DTSTART' );
	}
	private function getDtend($component) {
		return $this->getDateValue ( $component, 'DTEND' );
	}
	private function getTstamp($component) {
		return $this->getDateValue ( $component, 'TSTAMP' );
	}
	private function getDateValue($component, $attribute) {
		if ($component->getAttribute ( $attribute )) {
			$value = $component->getAttribute ( $attribute );
			if (is_array ( $value )) {
				$dateTime = new \TYPO3\CMS\Cal\Model\CalDate ( $value ['year'] . $value ['month'] . $value ['mday'] . '000000' );
			} else {
				$dateTime = new \TYPO3\CMS\Cal\Model\CalDate ( $value );
			}
			$params = $component->getAttributeParameters ( $attribute );
			$timezone = $params ['TZID'];
			if ($timezone) {
				$dateTime->convertTZbyID ( $timezone );
			}
			return $dateTime;
		}
		return null;
	}
	private function setCategories($component, $insertFields, $pid, $calId) {
		$categories = array ();
		$categoryString = $component->getAttribute ( 'CATEGORY' );
		if ($categoryString == "") {
			if (is_array ( $component->getAttribute ( 'CATEGORIES' ) )) {
				foreach ( $component->getAttribute ( 'CATEGORIES' ) as $cat ) {
					$categories [] = $cat;
				}
			} else {
				$categoryString = $component->getAttribute ( 'CATEGORIES' );
				$categories = GeneralUtility::trimExplode ( ',', $categoryString, 1 );
			}
		} else {
			$categories = GeneralUtility::trimExplode ( ',', $categoryString, 1 );
		}
		
		$categoryUids = array ();
		foreach ( $categories as $category ) {
			$category = trim ( $category );
			$categorySelect = '*';
			$categoryTable = 'tx_cal_category';
			$categoryWhere = 'calendar_id = ' . intval ( $calId ) . ' AND title =' . $GLOBALS ['TYPO3_DB']->fullQuoteStr ( $category, $categoryTable );
			$foundCategory = false;
			$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( $categorySelect, $categoryTable, $categoryWhere );
			if ($result) {
				while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
					$foundCategory = true;
					$categoryUids [] = $row ['uid'];
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
			}
			
			if (! $foundCategory) {
				$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( $categoryTable, array (
						'tstamp' => $insertFields ['crdate'],
						'crdate' => $insertFields ['crdate'],
						'pid' => $pid,
						'title' => $category,
						'calendar_id' => $calId 
				) );
				if (FALSE === $result) {
					throw new \RuntimeException ( 'Could not write ' . $categoryTable . ' record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458143 );
				}
				$categoryUids [] = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
			}
		}
		return $categoryUids;
	}
	private function setRecurrence($component, &$insertFields) {
		if ($component->getAttribute ( 'RRULE' )) {
			$rrule = $component->getAttribute ( 'RRULE' );
			
			$this->insertRuleValues ( $rrule, $insertFields );
		}
		
		if ($component->getAttribute ( 'RDATE' )) {
			$rdate = $component->getAttribute ( 'RDATE' );
			if (is_array ( $rdate )) {
				$insertFields ['rdate'] = implode ( ',', $rdate );
			} else {
				$insertFields ['rdate'] = $rdate;
			}
			if ($component->getAttributeParameters ( 'RDATE' )) {
				$parameterArray = $component->getAttributeParameters ( 'RDATE' );
				$keys = array_keys ( $parameterArray );
				$insertFields ['rdate_type'] = strtolower ( $keys [0] );
			} else {
				$insertFields ['rdate_type'] = 'date_time';
			}
		}
	}
	private function setRecurrenceId($component, $eventUid, &$insertFields) {
		$recurrenceIdStart = new \TYPO3\CMS\Cal\Model\CalDate ( $component->getAttribute ( 'RECURRENCE-ID' ) );
		$params = $component->getAttributeParameters ( 'RECURRENCE-ID' );
		$timezone = $params ['TZID'];
		if ($timezone) {
			$recurrenceIdStart->convertTZbyID ( $timezone );
		}
		
		$indexEntry = BackendUtility::getRecordRaw ( 'tx_cal_index', 'event_uid="' . $eventUid . '" AND start_datetime="' . $recurrenceIdStart->format ( '%Y%m%d%H%M%S' ) . '"' );
		
		if ($indexEntry) {
			$table = 'tx_cal_event_deviation';
			$insertFields ['parentid'] = $eventUid;
			$insertFields ['orig_start_time'] = $recurrenceIdStart->getHour () * 3600 + $recurrenceIdStart->getMinute () * 60;
			$recurrenceIdStart->setHour ( 0 );
			$recurrenceIdStart->setMinute ( 0 );
			$recurrenceIdStart->setSecond ( 0 );
			$insertFields ['orig_start_date'] = $recurrenceIdStart->getTime ();
			unset ( $insertFields ['calendar_id'] );
			
			if ($indexEntry ['event_deviation_uid'] > 0) {
				$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ( $table, 'uid=' . $indexEntry ['event_deviation_uid'], $insertFields );
				$eventDeviationUid = $indexEntry ['event_deviation_uid'];
			} else {
				$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( $table, $insertFields );
				if (FALSE === $result) {
					throw new \RuntimeException ( 'Could not write ' . $table . ' record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458145 );
				}
				$eventDeviationUid = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
			}
			$GLOBALS ['TYPO3_DB']->exec_UPDATEquery ( 'tx_cal_index', 'uid=' . $indexEntry ['uid'], Array (
					'event_deviation_uid' => $eventDeviationUid 
			) );
		}
	}
	private function setExceptions($component, $eventUid, $pid, $cruserId) {
		/* Delete the old exception relations */
		$exceptionEventUidsToBeDeleted = Array ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'tx_cal_exception_event.uid', 'tx_cal_exception_event,tx_cal_exception_event_mm', 'tx_cal_exception_event.uid = tx_cal_exception_event_mm.uid_foreign AND tx_cal_exception_event_mm.uid_local=' . $eventUid );
		if ($result) {
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				$exceptionEventUidsToBeDeleted [] = $row ['uid'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
		}
		if (! empty ( $exceptionEventUidsToBeDeleted )) {
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event', 'uid in (' . implode ( ',', $exceptionEventUidsToBeDeleted ) . ')' );
		}
		
		$exceptionEventGroupUidsToBeDeleted = Array ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( 'tx_cal_exception_event_group.uid', 'tx_cal_exception_event_group,tx_cal_exception_event_group_mm', 'tx_cal_exception_event_group.uid = tx_cal_exception_event_group_mm.uid_foreign AND tx_cal_exception_event_group_mm.uid_local=' . $eventUid );
		if ($result) {
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				$exceptionEventGroupUidsToBeDeleted [] = $row ['uid'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ( $result );
		}
		if (! empty ( $exceptionEventGroupUidsToBeDeleted )) {
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event_group', 'uid in (' . implode ( ',', $exceptionEventGroupUidsToBeDeleted ) . ')' );
		}
		
		$where = ' uid_local=' . $eventUid;
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event_mm', $where );
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_exception_event_group_mm', $where );
		
		// Exceptions:
		if ($component->getAttribute ( 'EXDATE' )) {
			if (is_array ( $component->getAttribute ( 'EXDATE' ) )) {
				foreach ( $component->getAttribute ( 'EXDATE' ) as $exceptionDescription ) {
					$this->createException ( $pid, $cruserId, $eventUid, $exceptionDescription );
				}
			} else {
				$this->createException ( $pid, $cruserId, $eventUid, $component->getAttribute ( 'EXDATE' ) );
			}
		}
		if ($component->getAttribute ( 'EXRULE' )) {
			if (is_array ( $component->getAttribute ( 'EXRULE' ) )) {
				foreach ( $component->getAttribute ( 'EXRULE' ) as $exceptionDescription ) {
					$this->createExceptionRule ( $pid, $cruserId, $eventUid, $exceptionDescription );
				}
			} else {
				$this->createExceptionRule ( $pid, $cruserId, $eventUid, $component->getAttribute ( 'EXRULE' ) );
			}
		}
	}
	private function generateIndexEntries($eventUid, $pid) {
		$pageTSConf = BackendUtility::getPagesTSconfig ( $pid );
		if ($pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin']) {
			$pageIDForPlugin = $pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin'];
		} else {
			$pageIDForPlugin = $pid;
		}
		/** @var \TYPO3\CMS\Cal\Utility\RecurrenceGenerator $rgc */
		$rgc = GeneralUtility::makeInstance ( 'TYPO3\\CMS\\Cal\\Utility\\RecurrenceGenerator', $pageIDForPlugin );
		$rgc->generateIndexForUid ( $eventUid, 'tx_cal_event' );
	}
	private function sendReminders($eventUid, $pid, $insertFields) {
		if ($this->conf ['view.'] ['event.'] ['remind']) {
			/* Schedule reminders for new and changed events */
			$reminderService = &\TYPO3\CMS\Cal\Utility\Functions::getReminderService ();
			$reminderService->scheduleReminder ( $eventUid );
		}
	}
	private function connectCategories($categoryUids, $eventUid) {
		/* Delete the old category relations */
		$where = ' uid_local=' . $eventUid;
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'tx_cal_event_category_mm', $where );
		$i = 0;
		foreach ( $categoryUids as $uid ) {
			$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_cal_event_category_mm', array (
					'uid_local' => $eventUid,
					'uid_foreign' => $uid,
					'sorting' => $i++
			) );
			if (FALSE === $result) {
				throw new \RuntimeException ( 'Could not write tx_cal_event_category_mm record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458146 );
			}
		}
	}
	private function cleanupCategories($deleteNotUsedCategories, $calId, $insertedOrUpdatedCategoryUids) {
		if ($deleteNotUsedCategories) {
			/* Delete the categories */
			$where = ' calendar_id=' . $calId;
			if (! empty ( $insertedOrUpdatedCategoryUids )) {
				array_unique ( $insertedOrUpdatedCategoryUids );
				$where .= ' AND uid NOT IN (' . implode ( ',', $insertedOrUpdatedCategoryUids ) . ')';
			}
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ( $this->extConf ['categoryService'], $where );
		}
	}
	private function saveOrUpdate($eventRow, $insertFields) {
		$table = 'tx_cal_event';
		if ($eventRow ['uid']) {
			$result = $GLOBALS ['TYPO3_DB']->exec_UPDATEquery ( $table, 'uid=' . $eventRow ['uid'], $insertFields );
			return $eventRow ['uid'];
		}
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( $table, $insertFields );
		if (FALSE === $result) {
			throw new \RuntimeException ( 'Could not write ' . $table . ' record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458144 );
		}
		return $GLOBALS ['TYPO3_DB']->sql_insert_id ();
	}
	private function setAttachments($component, &$insertFields, $pid, $eventUid) {
		$this->clearAllImagesAndAttachments ( $eventUid );
		$attachmentUrls = $component->getAttribute ( 'ATTACH' );
		if (is_array ( $attachmentUrls )) {
			foreach ( $attachmentUrls as $attachmentUrl ) {
				$this->storeAttachment ( $attachmentUrl, $insertFields, $eventUid, $pid );
			}
		} else if (is_string ( $attachmentUrls ) && strlen ( trim ( $attachmentUrls ) ) > 0) {
			$this->storeAttachment ( $attachmentUrls, $insertFields, $eventUid, $pid );
		}
	}
	public function clearAllImagesAndAttachments($uid) {
		$fileIndexRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance ( 'TYPO3\\CMS\\Core\\Resource\\Index\\FileIndexRepository' );
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ( '*', 'sys_file_reference', 'tablenames="tx_cal_event" and uid_foreign =' . $uid );
		if ($result) {
			while ( $row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ( $result ) ) {
				if($GLOBALS ['TYPO3_DB']->exec_SELECTcountRows ( '*', 'sys_file_reference', 'uid_local=' . $row ['uid_local'] ) == 1) {
					$fileIndexRepository->remove ( $row ['uid_local'] );
				}
			}
		}
		$result = $GLOBALS ['TYPO3_DB']->exec_DELETEquery ( 'sys_file_reference', 'tablenames="tx_cal_event" and uid_foreign =' . $uid );
	}
	private function storeAttachment($externalUrl, $insertFields, $eventUid, $pid) {
		if (! $this->fileFunc) {
			$this->fileFunc = new \TYPO3\CMS\Core\Utility\File\BasicFileUtility ();
			$all_files = Array ();
			$all_files ['webspace'] ['allow'] = '*';
			$all_files ['webspace'] ['deny'] = '';
			$this->fileFunc->init ( '', $all_files );
		}
		
		$qParts = parse_url ( $externalUrl );
		$fI = pathinfo ( $qParts ['path'] );
		$ext = strtolower ( $fI ['extension'] );
		
		$report = array ();
		GeneralUtility::getURL ( $externalUrl, 1, FALSE, $report );
		$content = GeneralUtility::getURL ( $externalUrl );
		
		$imageExt = explode ( ',', $GLOBALS ['TYPO3_CONF_VARS'] ['GFX'] ['imagefile_ext'] );
		$type = 'attachment';
		if (stristr ( $report ['content_type'], 'image' ) || in_array ( $ext, $imageExt )) {
			$type = 'image';
		}
		
		$allowedExt = array ();
		$denyExt = array ();
		if ($type == 'image') {
			$allowedExt = explode ( ',', $GLOBALS ['TYPO3_CONF_VARS'] ['GFX'] ['imagefile_ext'] );
		} else if ($type == 'attachment') {
			if (isset ( $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['allow'] ) && strlen ( $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['allow'] ) > 0) {
				$allowedExt = explode ( ',', $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['allow'] );
			}
			if (isset ( $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['deny'] ) && strlen ( $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['deny'] ) > 0) {
				$denyExt = explode ( ',', $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['fileExtensions'] ['webspace'] ['deny'] );
			}
		}
		
		if (( string ) $content === '' || (! empty ( $denyExt ) && in_array ( $ext, $denyExt )) || (! empty ( $allowedExt ) && ! in_array ( $ext, $allowedExt ))) {
			return;
		}
		
		$theDestFile = $this->fileFunc->getUniqueName ( $this->fileFunc->cleanFileName ( $fI ['basename'] ), PATH_site . 'typo3temp/' );
		GeneralUtility::writeFile ( $theDestFile, $content );
		$insertFields [$type] = '__NEW__' . basename ( $theDestFile );
		$insertFields ['pid'] = $pid;
		if (! isset ( $this->controller->piVars )) {
			if (! isset ( $this->controller )) {
				$this->controller = GeneralUtility::makeInstance ( 'TYPO3\\CMS\\Cal\\Controller\\Controller' );
			}
			$this->controller->piVars = array ();
		}
		
		$tempType = $this->controller->piVars [$type];
		$this->controller->piVars [$type] = array ();
		$this->checkOnTempFile ( $type, $insertFields, 'tx_cal_event', $eventUid );
		$this->controller->piVars [$type] = $tempType;
	}
	
	/**
	 *
	 * @param array $iCalendarComponentArray
	 *        	component array
	 * @param int $calId
	 *        	The calendar uid to add the events/todos to
	 * @param string $pid
	 *        	The save page id
	 * @param string $cruserId
	 *        	The create user id
	 * @param number $isTemp
	 *        	are the records only temporary (1 == true, 0 == false)
	 * @param string $deleteNotUsedCategories
	 *        	Should not assigned categories be deleted
	 * @return array The inserted or updated event uids
	 * @throws \RuntimeException
	 */
	public function insertCalEventsIntoDB($iCalendarComponentArray = array(), $calId, $pid = '', $cruserId = '', $isTemp = 1, $deleteNotUsedCategories = true) {
		$insertedOrUpdatedEventUids = Array ();
		$insertedOrUpdatedCategoryUids = Array ();
		if (empty ( $iCalendarComponentArray )) {
			return $insertedOrUpdatedEventUids;
		}
		
		foreach ( $iCalendarComponentArray as $component ) {
			$insertFields = array ();
			$insertFields ['isTemp'] = $isTemp;
			$insertFields ['tstamp'] = time ();
			$insertFields ['crdate'] = time ();
			$insertFields ['pid'] = $pid;
			if ($component->getType () == 'vEvent' || $component->getType () == 'vTodo') {
				$insertFields ['cruser_id'] = $cruserId;
				$insertFields ['calendar_id'] = $calId;
				
				$dtstart = $this->getDtstart ( $component );
				if ($dtstart != null) {
					$insertFields ['start_date'] = $dtstart->format ( '%Y%m%d' );
					$insertFields ['start_time'] = $dtstart->hour * 3600 + $dtstart->minute * 60;
				} else if ($component->getType () == 'vEvent') {
					// a Todo does not need a start, but an event
					continue;
				}
				
				$insertFields ['icsUid'] = $component->getAttribute ( 'UID' );
				
				$eventRow = BackendUtility::getRecordRaw ( 'tx_cal_event', 'icsUid="' . $insertFields ['icsUid'] . '"' );
				
				$tstamp = $this->getTstamp ( $component );
				
				// Update only events that have changed!
				if ($tstamp != null && $tstamp->getTime () == $eventRow ['tstamp']) {
					$insertedOrUpdatedEventUids [] = $eventRow ['uid'];
					continue;
				}
				
				if (isset ( $eventRow ['tstamp'] ) && $tstamp != null) {
					$insertFields ['tstamp'] = $tstamp->getTime ();
				}
				
				$dtend = $this->getDtend ( $component );
				if ($dtend != null) {
					$insertFields ['end_date'] = $dtend->format ( '%Y%m%d' );
					$insertFields ['end_time'] = $dtend->hour * 3600 + $dtend->minute * 60;
				}
				
				if ($component->getAttribute ( 'DURATION' )) {
					$enddate = $insertFields ['start_time'] + $component->getAttribute ( 'DURATION' );
					$dateTime = new \TYPO3\CMS\Cal\Model\CalDate ( $insertFields ['start_date'] );
					$dateTime->addSeconds ( $enddate );
					$params = $component->getAttributeParameters ( 'DURATION' );
					$timezone = $params ['TZID'];
					if ($timezone) {
						$dateTime->convertTZbyID ( $timezone );
					}
					$insertFields ['end_date'] = $dateTime->format ( '%Y%m%d' );
					$insertFields ['end_time'] = $dateTime->hour * 3600 + $dateTime->minute * 60;
				}
				
				// Fix for allday events
				if ($insertFields ['start_time'] == 0 && $insertFields ['end_time'] == 0 && $insertFields ['start_date'] != 0) {
					$date = new \TYPO3\CMS\Cal\Model\CalDate ( $insertFields ['end_date'] . '000000' );
					$date->setTZbyId ( 'UTC' );
					$date->subtractSeconds ( 86400 );
					$insertFields ['end_date'] = $date->format ( '%Y%m%d' );
				}
				
				$insertFields ['title'] = $component->getAttribute ( 'SUMMARY' );
				
				if ($component->getAttribute ( 'URL' )) {
					$insertFields ['ext_url'] = $component->getAttribute ( 'URL' );
				}
				
				if ($component->getType () == 'vEvent' && $component->organizerName ()) {
					$insertFields ['organizer'] = str_replace ( '"', '', $component->organizerName () );
				}
				
				$insertFields ['location'] = $component->getAttribute ( 'LOCATION' );
				if ($insertFields ['location'] == null) {
					$insertFields ['location'] = '';
				}
				
				$insertFields ['description'] = $component->getAttribute ( 'DESCRIPTION' );
				
				$categoryUids = $this->setCategories ( $component, $insertFields, $pid, $calId );
				
				$this->setRecurrence ( $component, $insertFields );
				
				$eventUid = $this->saveOrUpdate ( $eventRow, $insertFields );
				
				if ($component->getAttribute ( 'RECURRENCE-ID' )) {
					$this->setRecurrenceId ( $component, $eventUid, $insertFields );
				} else {
					$this->setExceptions ( $component, $eventUid, $pid, $cruserId );
				}
				
				$this->generateIndexEntries ( $eventUid, $pid );
				
				$this->sendReminders ( $eventUid, $pid, $insertFields );
				
				$this->connectCategories ( $categoryUids, $eventUid );
				
				$this->setAttachments ( $component, $insertFields, $pid, $eventUid );
				
				$insertedOrUpdatedEventUids [] = $eventUid;
				$insertedOrUpdatedCategoryUids = array_merge ( $insertedOrUpdatedCategoryUids, $categoryUids );
				
				// Hook: insertCalEventsIntoDB
				$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ( 'tx_cal_icalendar_service', 'iCalendarServiceClass', 'service' );
				
				foreach ( $hookObjectsArr as $hookObj ) {
					if (method_exists ( $hookObj, 'insertCalEventsIntoDB' )) {
						$hookObj->insertCalEventsIntoDB ( $this, $eventUid, $component );
					}
				}
			}
		}
		
		$this->cleanupCategories ( $deleteNotUsedCategories, $calId, $insertedOrUpdatedCategoryUids );
		return $insertedOrUpdatedEventUids;
	}
	
	/**
	 *
	 * @param unknown $rule        	
	 * @param array $insertFields        	
	 */
	private function insertRuleValues($rule, &$insertFields) {
		$data = str_replace ( 'RRULE:', '', $rule );
		$rule = explode ( ';', $data );
		foreach ( $rule as $recur ) {
			preg_match ( '/(.*)=(.*)/', $recur, $regs );
			$rrule_array [$regs [1]] = $regs [2];
		}
		foreach ( $rrule_array as $key => $val ) {
			switch ($key) {
				case 'FREQ' :
					switch ($val) {
						case 'YEARLY' :
							$freq_type = 'year';
							break;
						case 'MONTHLY' :
							$freq_type = 'month';
							break;
						case 'WEEKLY' :
							$freq_type = 'week';
							break;
						case 'DAILY' :
							$freq_type = 'day';
							break;
						case 'HOURLY' :
							$freq_type = 'hour';
							break;
						case 'MINUTELY' :
							$freq_type = 'minute';
							break;
						case 'SECONDLY' :
							$freq_type = 'second';
							break;
					}
					$insertFields ['freq'] = strtolower ( $freq_type );
					break;
				case 'COUNT' :
					$insertFields ['cnt'] = $val;
					break;
				case 'UNTIL' :
					$until = str_replace ( 'T', '', $val );
					$until = str_replace ( 'Z', '', $until );
					if (strlen ( $until ) == 8)
						$until = $until . '235959';
					$abs_until = $until;
					preg_match ( '/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/', $until, $regs );
					$insertFields ['until'] = $regs [1] . $regs [2] . $regs [3];
					break;
				case 'INTERVAL' :
					$insertFields ['intrval'] = $val;
					break;
				case 'BYSECOND' :
					// $bysecond = $val;
					// $bysecond = explode(',', $bysecond);
					break;
				case 'BYMINUTE' :
					// $byminute = $val;
					// $byminute = explode(',', $byminute);
					break;
				case 'BYHOUR' :
					// $byhour = $val;
					// $byhour = explode(',', $byhour);
					break;
				case 'BYDAY' :
					$insertFields ['byday'] = strtolower ( $val );
					break;
				case 'BYMONTHDAY' :
					$insertFields ['bymonthday'] = strtolower ( $val );
					break;
				case 'BYYEARDAY' :
					// $byyearday = $val;
					// $byyearday = explode(',', $byyearday);
					break;
				case 'BYWEEKNO' :
					// $byweekno = $val;
					// $byweekno = explode(',', $byweekno);
					break;
				case 'BYMONTH' :
					$insertFields ['bymonth'] = strtolower ( $val );
					break;
				case 'BYSETPOS' :
					// $bysetpos = $val;
					break;
				case 'WKST' :
					// $wkst = $val;
					break;
				case 'END' :
					// ??
					break;
			}
		}
	}
	
	/**
	 *
	 * @param unknown $pid        	
	 * @param unknown $cruserId        	
	 * @param unknown $eventUid        	
	 * @param unknown $exceptionDescription        	
	 * @throws \RuntimeException
	 */
	private function createException($pid, $cruserId, $eventUid, $exceptionDescription) {
		$exceptionDate = new \TYPO3\CMS\Cal\Model\CalDate ( $exceptionDescription );
		
		$insertFields = Array ();
		$insertFields ['tstamp'] = time ();
		$insertFields ['crdate'] = time ();
		$insertFields ['pid'] = $pid;
		$insertFields ['cruser_id'] = $cruserId;
		$insertFields ['title'] = 'Exception for event ' . $eventUid. ' on '.$exceptionDate->format ( '%Y%m%d' );
		$insertFields ['start_date'] = $exceptionDate->format ( '%Y%m%d' );
		
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_cal_exception_event', $insertFields );
		if (FALSE === $result) {
			throw new \RuntimeException ( 'Could not write tx_cal_exception_event record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458147 );
		}
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_cal_exception_event_mm', array (
				'tablenames' => 'tx_cal_exception_event',
				'uid_local' => $eventUid,
				'uid_foreign' => $GLOBALS ['TYPO3_DB']->sql_insert_id () 
		) );
		if (FALSE === $result) {
			throw new \RuntimeException ( 'Could not write tx_cal_exception_event_mm record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458148 );
		}
	}
	
	/**
	 *
	 * @param unknown $pid        	
	 * @param unknown $cruserId        	
	 * @param unknown $eventUid        	
	 * @param unknown $exceptionRuleDescription        	
	 * @throws \RuntimeException
	 */
	private function createExceptionRule($pid, $cruserId, $eventUid, $exceptionRuleDescription) {
		$event = BackendUtility::getRecordRaw ( 'tx_cal_event', 'uid=' . $eventUid );
		
		$insertFields = Array ();
		$insertFields ['tstamp'] = time ();
		$insertFields ['crdate'] = time ();
		$insertFields ['pid'] = $pid;
		$insertFields ['cruser_id'] = $cruserId;
		$insertFields ['title'] = 'Exception rule for event ' . $eventUid;
		$insertFields ['start_date'] = $event ['start_date'];
		$this->insertRuleValues ( $exceptionRuleDescription, $insertFields );
		
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_cal_exception_event', $insertFields );
		if (FALSE === $result) {
			throw new \RuntimeException ( 'Could not write tx_cal_exception_event_mm record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458149 );
		}
		$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ( 'tx_cal_exception_event_mm', array (
				'tablenames' => 'tx_cal_exception_event',
				'uid_local' => $eventUid,
				'uid_foreign' => $GLOBALS ['TYPO3_DB']->sql_insert_id () 
		) );
		if (FALSE === $result) {
			throw new \RuntimeException ( 'Could not write tx_cal_exception_event_mm record to database: ' . $GLOBALS ['TYPO3_DB']->sql_error (), 1431458150 );
		}
	}
}

?>