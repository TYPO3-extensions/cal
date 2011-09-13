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
			$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$this->pageIDForPlugin = $pageIDForPlugin;
			if($starttime == null) {
				$starttime = $this->extConf['recurrenceStart'];
			}
			$this->starttime = $starttime;
			if($endtime == null) {
				$endtime = $this->extConf['recurrenceEnd'];
			}
			$this->endtime = $endtime;
		}
		
		function getInfo() {
			return $this->info;
		}
		
		function cleanIndexTable() {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_index','');
		}
		
		function cleanIndexTableOfUid($uid) {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_index','event_uid = '.$uid);
		}
		
		function cleanIndexTableOfCalendarUid($uid) {
			$uids = Array(0);
			$select = 'uid';
			$table = 'tx_cal_event';
			$where = 'deleted = 0 AND calendar_id = '.$uid;
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)){
				$uids[] = $row['uid'];
			}
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_index','event_uid IN ('.implode($uids).')');
		}
				
		function countRecurringEvents() {
			$select = 'count(*)';
			$table = 'tx_cal_event';
			$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)){
				return $row['count(*)'];
			}
			return '0';
		}
		
		function generateIndex() {
			$eventService = $this->getEventService();
			if(!is_object($eventService)){
				$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
				return;
			}
			$eventService->starttime = new tx_cal_date($this->starttime);
			$eventService->endtime = new tx_cal_date($this->endtime);
			$select = '*';
			$table = 'tx_cal_event';
			$where = 'deleted = 0 AND (freq IN ("day","week","month","year") OR (rdate AND rdate_type IN ("date_time","date","period")))';
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)){
				$event = $eventService->createEvent($row, false);
				$eventService->recurringEvent($event);
			}
			$this->info = 'Done.';
		}
		
		function generateIndexForUid($uid) {
			$eventService = $this->getEventService();
			if(!is_object($eventService)){
				$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
				return;
			}
			$eventService->starttime = new tx_cal_date($this->starttime);
			$eventService->endtime = new tx_cal_date($this->endtime);
			
			$this->cleanIndexTableOfUid($uid);
			
			$select = '*';
			$table = 'tx_cal_event';
			$where = 'uid = '.$uid;
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)){
				$event = $eventService->createEvent($row, false);
				$eventService->recurringEvent($event);
			}
			$this->info = 'Done.';
		}
		
		function generateIndexForCalendarUid($uid) {
			$eventService = $this->getEventService();
			if(!is_object($eventService)){
				$this->info = 'Could not fetch the event service! Please make sure the page id is correct!';
				return;
			}
			$eventService->starttime = new tx_cal_date($this->starttime);
			$eventService->endtime = new tx_cal_date($this->endtime);

			$this->cleanIndexTableOfCalendarUid($uid);
			
			$select = '*';
			$table = 'tx_cal_event';
			$where = 'calendar_id = '.$uid;
			$results = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($results)){
				$event = $eventService->createEvent($row, false);
				$eventService->recurringEvent($event);
			}
			$this->info = 'Done.';
		}
		
		function getEventService() {
			require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_registry.php');
			$modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
			if(!$modelObj) {
				require_once(t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
				$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
				$tx_cal_api = &$tx_cal_api->tx_cal_api_without($this->pageIDForPlugin);
				$modelObj = $tx_cal_api->modelObj;
			}
			return $modelObj->getServiceObjByKey('cal_event_model', 'event', 'tx_cal_phpicalendar');
		}
	}
	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/mod1/class.tx_cal_recurrence_generator.php']) {
	require_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/mod1/class.tx_cal_recurrence_generator.php']);
}

?>
