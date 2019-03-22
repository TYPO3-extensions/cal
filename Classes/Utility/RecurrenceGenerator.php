<?php

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
namespace TYPO3\CMS\Cal\Utility;

use TYPO3\CMS\Cal\Backend\Modul\CalIndexer;
use TYPO3\CMS\Cal\Controller\Api;
use TYPO3\CMS\Cal\Controller\DateParser;
use TYPO3\CMS\Cal\Model\CalDate;
use TYPO3\CMS\Cal\Service\EventService;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 *
 * @author : Mario Matzulla
 */
class RecurrenceGenerator {
	
	/**
	 * The table name of the index table
	 */
	const INDEX_TABLE = 'tx_cal_index';
	
	/**
	 *
	 * @var string
	 */
	public $info = '';
	
	/**
	 *
	 * @var null
	 */
	public $pageIDForPlugin;
	
	/**
	 *
	 * @var null|string
	 */
	public $starttime;
	
	/**
	 *
	 * @var null|string
	 */
	public $endtime;
	
	/**
	 *
	 * @var array
	 */
	public $extConf;
	
	/**
	 *
	 * @param null $pageIDForPlugin        	
	 * @param null $starttime        	
	 * @param null $endtime        	
	 */
	public function __construct($pageIDForPlugin = null, $starttime = null, $endtime = null) {
		$this->extConf = unserialize ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal'] );
		$this->pageIDForPlugin = $pageIDForPlugin;
		if ($starttime == null) {
			$starttime = $this->getTimeParsed ( $this->extConf ['recurrenceStart'] )->format ( '%Y%m%d' );
		}
		$this->starttime = $starttime;
		if ($endtime == null) {
			$endtime = $this->getTimeParsed ( $this->extConf ['recurrenceEnd'] )->format ( '%Y%m%d' );
		}
		$this->endtime = $endtime;
	}
	
	/**
	 *
	 * @return string
	 */
	function getInfo() {
		return $this->info;
	}
	
	/**
	 *
	 * @param int $pageId        	
	 */
	public function cleanIndexTable($pageId) {
		$this->getDatabaseConnection ()->exec_DELETEquery ( self::INDEX_TABLE, 'event_uid in (select uid from tx_cal_event where pid = ' . intval ( $pageId ) . ')' );
	}
	
	/**
	 *
	 * @param int $uid        	
	 * @param string $table        	
	 */
	public function cleanIndexTableOfUid($uid, $table) {
		$this->getDatabaseConnection ()->exec_DELETEquery ( self::INDEX_TABLE, 'event_uid = ' . $uid . ' AND tablename = "' . $table . '"' );
	}
	
	/**
	 *
	 * @param int $uid        	
	 */
	function cleanIndexTableOfCalendarUid($uid) {
		$databaseConnection = $this->getDatabaseConnection ();
		$select = 'uid';
		$table = 'tx_cal_event';
		$where = 'deleted = 0 AND calendar_id = ' . $uid;
		$uids = array_keys ( $databaseConnection->exec_SELECTgetRows ( $select, $table, $where, '', '', '', 'uid' ) );
		$uids [] = 0;
		$databaseConnection->exec_DELETEquery ( self::INDEX_TABLE, 'event_uid IN (' . implode ( $uids ) . ')' . ' AND tablename="' . $table . '"' );
	}
	
	/**
	 *
	 * @param int $uid        	
	 */
	function cleanIndexTableOfExceptionGroupUid($uid) {
		$databaseConnection = $this->getDatabaseConnection ();
		$cObj = &Registry::Registry ( 'basic', 'cobj' );
		$uids = Array (
				0 
		);
		$where = 'AND tx_cal_exception_event_group.uid = ' . $uid . $cObj->enableFields ( 'tx_cal_exception_event' ) . $cObj->enableFields ( 'tx_cal_exception_event_group' );
		$results = $databaseConnection->exec_SELECT_mm_query ( 'tx_cal_exception_event_group.*', 'tx_cal_exception_event', 'tx_cal_exception_event_mm', 'tx_cal_exception_event_group', $where );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				$uids [] = $row ['uid'];
			}
			$databaseConnection->sql_free_result ( $results );
		}
		$databaseConnection->exec_DELETEquery ( self::INDEX_TABLE, 'event_uid IN (' . implode ( $uids ) . ')' . ' AND tablename = "tx_cal_exception_event"' );
	}
	
	/**
	 *
	 * @param int $eventPage        	
	 *
	 * @return int
	 */
	function countRecurringEvents($eventPage = 0) {
		$databaseConnection = $this->getDatabaseConnection ();
		$count = 0;
		$select = 'count(*)';
		$table = 'tx_cal_event';
		$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
		if ($eventPage > 0) {
			$where = 'pid = ' . $eventPage . ' AND ' . $where;
		}
		$results = $databaseConnection->exec_SELECTquery ( $select, $table, $where );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				$count = $row ['count(*)'];
			}
			$databaseConnection->sql_free_result ( $results );
		}
		
		$table = 'tx_cal_exception_event';
		$results = $databaseConnection->exec_SELECTquery ( $select, $table, $where );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				$count += $row ['count(*)'];
			}
			$databaseConnection->sql_free_result ( $results );
		}
		return $count;
	}
	
	/**
	 *
	 * @return array
	 */
	public function getRecurringEventPages() {
		$pages = array ();
		$table = 'tx_cal_event';
		$this->getPageTitleAndUidFromPagesContaining ( $table, $pages );
		
		$table = 'tx_cal_exception_event';
		$this->getPageTitleAndUidFromPagesContaining ( $table, $pages );
		
		return $pages;
	}
	
	/**
	 *
	 * @param string $table        	
	 * @param array $pages        	
	 */
	protected function getPageTitleAndUidFromPagesContaining($table, array &$pages) {
		$databaseConnection = $this->getDatabaseConnection ();
		$select = 'pid';
		$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
		$pids = Array ();
		$groupBy = 'pid';
		$results = $databaseConnection->exec_SELECTquery ( $select, $table, $where, $groupBy );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				$pids [] = $row ['pid'];
			}
			$databaseConnection->sql_free_result ( $results );
		}
		if (! empty ( $pids )) {
			$select = 'title,uid';
			$where = 'deleted = 0 and uid in (' . implode ( ',', $pids ) . ')';
			$results = $databaseConnection->exec_SELECTquery ( $select, 'pages', $where );
			if ($results) {
				while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
					$pages [$row ['uid']] = $row ['title'];
				}
				$databaseConnection->sql_free_result ( $results );
			}
		}
	}
	
	/**
	 * Generate index
	 *
	 * @param int $eventPage        	
	 */
	function generateIndex($eventPage = 0) {
		$eventService = $this->getEventService ();
		if (! is_object ( $eventService )) {
			return;
		}
		$eventService->starttime = new CalDate ( $this->starttime );
		$eventService->endtime = new CalDate ( $this->endtime );
		$databaseConnection = $this->getDatabaseConnection ();
		
		$select = '*';
		$table = 'tx_cal_event';
		$this->info .= '<h3>tx_cal_event</h3><br/><ul>';
		$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
		if ($eventPage > 0) {
			$where = 'pid = ' . $eventPage . ' AND ' . $where;
		}
		$results = $databaseConnection->exec_SELECTquery ( $select, $table, $where );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				// make sure that rdate is empty in case that something went wrong during event creation (e.g. by copying)
				if ($row ["rdate_type"] == "none" || $row ["rdate_type"] == "" || $row ["rdate_type"] == "0") {
					$row ["rdate"] = "";
				}
				$this->info .= '<li>' . $row ['title'] . '</li>';
				$event = $eventService->createEvent ( $row, false );
				$eventService->recurringEvent ( $event );
			}
			$databaseConnection->sql_free_result ( $results );
		}
		$this->info .= '</ul>';
		$this->info .= '<h3>tx_cal_exception_event</h3><br/><ul>';
		$table = 'tx_cal_exception_event';
		$results = $databaseConnection->exec_SELECTquery ( $select, $table, $where );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				$this->info .= '<li>' . $row ['title'] . '</li>';
				$event = $eventService->createEvent ( $row, true );
				$eventService->recurringEvent ( $event );
			}
			$databaseConnection->sql_free_result ( $results );
		}
		$this->info .= '</ul>';
		$this->info .= 'Done.';
		$this->info .= '<br/><br/><a href="javascript:history.back();">' . LocalizationUtility::translate ( 'l_back', 'cal' ) . '</a><br/><br/>';
	}
	
	/**
	 * Genrate index for UID
	 *
	 * @param int $uid        	
	 * @param string $table        	
	 */
	function generateIndexForUid($uid, $table) {
		$eventService = $this->getEventService ();
		if (! is_object ( $eventService )) {
			return;
		}
		$eventService->starttime = new CalDate ( $this->starttime );
		$eventService->endtime = new CalDate ( $this->endtime );
		
		$this->cleanIndexTableOfUid ( $uid, $table );
		$databaseConnection = $this->getDatabaseConnection ();
		
		$select = '*';
		$where = 'uid = ' . ( int ) $uid;
		$rows = $databaseConnection->exec_SELECTgetRows ( $select, $table, $where );
		foreach ( $rows as $row ) {
			$event = $eventService->createEvent ( $row, $table == 'tx_cal_exception_event' );
			$eventService->recurringEvent ( $event );
		}
		$this->info = 'Done.';
	}
	
	/**
	 * Generate index for the given calendar
	 *
	 * @param int $uid        	
	 */
	function generateIndexForCalendarUid($uid) {
		$eventService = $this->getEventService ();
		if (! is_object ( $eventService )) {
			return;
		}
		$eventService->starttime = new CalDate ( $this->starttime );
		$eventService->endtime = new CalDate ( $this->endtime );
		
		$this->cleanIndexTableOfCalendarUid ( $uid );
		$databaseConnection = $this->getDatabaseConnection ();
		
		$select = '*';
		$table = 'tx_cal_event';
		$where = 'calendar_id = ' . $uid;
		$rows = $databaseConnection->exec_SELECTgetRows ( $select, $table, $where );
		foreach ( $rows as $row ) {
			$event = $eventService->createEvent ( $row, false );
			$eventService->recurringEvent ( $event );
		}
		$this->info = 'Done.';
	}
	
	/**
	 * Generate the index for the given exception group id
	 *
	 * @param int $uid        	
	 */
	function generateIndexForExceptionGroupUid($uid) {
		$eventService = $this->getEventService ();
		if (! is_object ( $eventService )) {
			return;
		}
		$eventService->starttime = new CalDate ( $this->starttime );
		$eventService->endtime = new CalDate ( $this->endtime );
		
		$this->cleanIndexTableOfExceptionGroupUid ( $uid );
		$databaseConnection = $this->getDatabaseConnection ();
		
		$cObj = &Registry::Registry ( 'basic', 'cobj' );
		$where = 'tx_cal_exception_event_group.id = ' . $uid . $cObj->enableFields ( 'tx_cal_exception_event' ) . $cObj->enableFields ( 'tx_cal_exception_event_group' );
		$results = $databaseConnection->exec_SELECT_mm_query ( 'tx_cal_exception_event_group.*', 'tx_cal_exception_event', 'tx_cal_exception_event_mm', 'tx_cal_exception_event_group', $where );
		if ($results) {
			while ( $row = $databaseConnection->sql_fetch_assoc ( $results ) ) {
				$event = $eventService->createEvent ( $row, false );
				$eventService->recurringEvent ( $event );
			}
			$databaseConnection->sql_free_result ( $results );
		}
		$this->info = 'Done.';
	}
	
	/**
	 * Get the event service
	 *
	 * @return EventService
	 */
	function getEventService() {
		static $eventService = null;
		if (is_object ( $eventService )) {
			return $eventService;
		}
		try {
			$modelObj = &Registry::Registry ( 'basic', 'modelcontroller' );
			if (! $modelObj) {
				/** @var Api $calAPI */
				$calAPI = GeneralUtility::makeInstance ( 'TYPO3\\CMS\\Cal\\Controller\\Api' );
				$calAPI = &$calAPI->tx_cal_api_without ( $this->pageIDForPlugin );
				$modelObj = $calAPI->modelObj;
			}
			$eventService = $modelObj->getServiceObjByKey ( 'cal_event_model', 'event', 'tx_cal_phpicalendar' );
		} catch ( \Exception $e ) {
			$this->info = CalIndexer::getMessage ( $e, FlashMessage::ERROR );
		}
		if (! is_object ( $eventService )) {
			$this->info = CalIndexer::getMessage ( 'Could not fetch the event service! Please make sure the page id is correct!', FlashMessage::ERROR );
		}
		return $eventService;
	}
	
	/**
	 * Get the time parsed
	 *
	 * @param string $timeString        	
	 *
	 * @return CalDate
	 */
	protected function getTimeParsed($timeString) {
		/** @var DateParser $dp */
		$dp = GeneralUtility::makeInstance ( 'TYPO3\\CMS\\Cal\\Controller\\DateParser' );
		$dp->parse ( $timeString, 0, '' );
		return $dp->getDateObjectFromStack ();
	}
	
	/**
	 * Get the database connection
	 *
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS ['TYPO3_DB'];
	}
}