<?php
/*
 * @author: Mario Matzulla
 */
class tx_cal_recurrence_generator {
	var $info = '';
	var $pageIDForPlugin;
	var $starttime;
	var $endtime;
	var $extConf;
	function tx_cal_recurrence_generator($pageIDForPlugin, $starttime = null, $endtime = null) {
		$this->extConf = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$this->pageIDForPlugin = $pageIDForPlugin;
		if ($starttime == null) {
			$starttime = $this->extConf ['recurrenceStart'];
		}
		$this->starttime = $starttime;
		if ($endtime == null) {
			$endtime = $this->extConf ['recurrenceEnd'];
		}
		$this->endtime = $endtime;
	}
	function getInfo() {
		return $this->info;
	}
	function cleanIndexTable() {
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ('tx_cal_index', '');
	}
	function cleanIndexTableOfUid($uid, $table) {
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ('tx_cal_index', 'event_uid = ' . $uid . ' AND tablename = "' . $table . '"');
	}
	function cleanIndexTableOfCalendarUid($uid) {
		$uids = Array (
				0 
		);
		$select = 'uid';
		$table = 'tx_cal_event';
		$where = 'deleted = 0 AND calendar_id = ' . $uid;
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$uids [] = $row ['uid'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ('tx_cal_index', 'event_uid IN (' . implode ($uids) . ')' . ' AND tablename = "' . $table . '"');
	}
	function cleanIndexTableOfExceptionGroupUid($uid) {
		$cObj = &tx_cal_registry::Registry ('basic', 'cobj');
		$uids = Array (
				0 
		);
		$where = 'AND tx_cal_exception_event_group.uid = ' . $uid . $cObj->enableFields ('tx_cal_exception_event') . $cObj->enableFields ('tx_cal_exception_event_group');
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ('tx_cal_exception_event_group.*', 'tx_cal_exception_event', 'tx_cal_exception_event_mm', 'tx_cal_exception_event_group', $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$uids [] = $row ['uid'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		$GLOBALS ['TYPO3_DB']->exec_DELETEquery ('tx_cal_index', 'event_uid IN (' . implode ($uids) . ')' . ' AND tablename = "tx_cal_exception_event"');
	}
	function countRecurringEvents($eventPage = 0) {
		$count = 0;
		$select = 'count(*)';
		$table = 'tx_cal_event';
		$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
		if ($eventPage > 0) {
			$where = 'pid = ' . $eventPage . ' AND ' . $where;
		}
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$count = $row ['count(*)'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		
		$table = 'tx_cal_exception_event';
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$count += $row ['count(*)'];
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		return $count;
	}
	function generateIndex($eventPage = 0) {
		$eventService = $this->getEventService ();
		if (! is_object ($eventService)) {
			$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
			return;
		}
		$eventService->starttime = new tx_cal_date ($this->starttime);
		$eventService->endtime = new tx_cal_date ($this->endtime);
		$select = '*';
		$table = 'tx_cal_event';
		$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
		if ($eventPage > 0) {
			$where = 'pid = ' . $eventPage . ' AND ' . $where;
		}
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				// make sure that rdate is empty in case that something went wrong during event creation (e.g. by copying)
				if ($row ["rdate_type"] == "none" || $row ["rdate_type"] == "" || $row ["rdate_type"] == "0") {
					$row ["rdate"] = "";
				}
				
				$event = $eventService->createEvent ($row, false);
				$eventService->recurringEvent ($event);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		
		$table = 'tx_cal_exception_event';
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$event = $eventService->createEvent ($row, true);
				$eventService->recurringEvent ($event);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		$this->info = 'Done.';
	}
	function generateIndexForUid($uid, $table) {
		$eventService = $this->getEventService ();
		if (! is_object ($eventService)) {
			$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
			return;
		}
		$eventService->starttime = new tx_cal_date ($this->starttime);
		$eventService->endtime = new tx_cal_date ($this->endtime);
		
		$this->cleanIndexTableOfUid ($uid, $table);
		
		$select = '*';
		$where = 'uid = ' . $uid;
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$event = $eventService->createEvent ($row, $table == 'tx_cal_exception_event');
				$eventService->recurringEvent ($event);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		$this->info = 'Done.';
	}
	function generateIndexForCalendarUid($uid) {
		$eventService = $this->getEventService ();
		if (! is_object ($eventService)) {
			$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
			return;
		}
		$eventService->starttime = new tx_cal_date ($this->starttime);
		$eventService->endtime = new tx_cal_date ($this->endtime);
		
		$this->cleanIndexTableOfCalendarUid ($uid);
		
		$select = '*';
		$table = 'tx_cal_event';
		$where = 'calendar_id = ' . $uid;
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$event = $eventService->createEvent ($row, false);
				$eventService->recurringEvent ($event);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		$this->info = 'Done.';
	}
	function generateIndexForExceptionGroupUid($uid) {
		$eventService = $this->getEventService ();
		if (! is_object ($eventService)) {
			$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
			return;
		}
		$eventService->starttime = new tx_cal_date ($this->starttime);
		$eventService->endtime = new tx_cal_date ($this->endtime);
		
		$this->cleanIndexTableOfExceptionGroupUid ($uid);
		
		$select = '*';
		$where = 'tx_cal_exception_event_group.id = ' . $uid . $cObj->enableFields ('tx_cal_exception_event') . $cObj->enableFields ('tx_cal_exception_event_group');
		$results = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ('tx_cal_exception_event_group.*', 'tx_cal_exception_event', 'tx_cal_exception_event_mm', 'tx_cal_exception_event_group', $where);
		if ($results) {
			while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($results)) {
				$event = $eventService->createEvent ($row, false);
				$eventService->recurringEvent ($event);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($results);
		}
		$this->info = 'Done.';
	}
	function getEventService() {
		require_once (t3lib_extMgm::extPath ('cal') . '/controller/class.tx_cal_registry.php');
		$modelObj = &tx_cal_registry::Registry ('basic', 'modelcontroller');
		if (! $modelObj) {
			require_once (t3lib_extMgm::extPath ('cal') . '/controller/class.tx_cal_api.php');
			$tx_cal_api = t3lib_div::makeInstance ('tx_cal_api');
			$tx_cal_api = &$tx_cal_api->tx_cal_api_without ($this->pageIDForPlugin);
			$modelObj = $tx_cal_api->modelObj;
		}
		return $modelObj->getServiceObjByKey ('cal_event_model', 'event', 'tx_cal_phpicalendar');
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/mod1/class.tx_cal_recurrence_generator.php']) {
	require_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/mod1/class.tx_cal_recurrence_generator.php']);
}

?>