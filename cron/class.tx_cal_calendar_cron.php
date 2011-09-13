<?php

include_once(t3lib_extMgm::extPath('gabriel','class.tx_gabriel_event.php'));

class tx_cal_calendar_cron extends tx_gabriel_event {
	var $uid;
	
	/**
	 * PHP4 wrapper for constructor, 
	 * have to be here evne though the constructor is not defined in the derived class, 
	 * else the constructor of the parent class will not be called in PHP4
	 *
	 */
	function tx_cal_calendar_cron() {
		$this->__construct();
	}

	function execute() {
		require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
		$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
		
		$service->update($this->uid);
	}
	
	
	function getUID() {
		return $this->uid;
	}
	
	function setUID($uid) {
		$this->uid = $uid;
	}
		
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/cron/class.tx_cal_calendar_cron.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/cron/class.tx_cal_calendar_cron.php']);
}


?>