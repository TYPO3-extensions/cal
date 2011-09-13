<?php

include_once(t3lib_extMgm::extPath('gabriel','class.tx_gabriel_event.php'));

class tx_cal_reminder_cron extends tx_gabriel_event {
	var $uid;
	
	/**
	 * PHP4 wrapper for constructor, 
	 * have to be here evne though the constructor is not defined in the derived class, 
	 * else the constructor of the parent class will not be called in PHP4
	 *
	 */
	function tx_cal_reminder_cron() {
		$this->__construct();
	}

	function execute() {
		require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
		
		$event = t3lib_BEfunc::getRecord ("tx_cal_event", $this->uid);
		$pageTSConf = t3lib_befunc::getPagesTSconfig($event['pid']);
		
		$reminderService = &getReminderService();
		$reminderService->remind($event, $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['remind.']);
	}
	
	
	function getUID() {
		return $this->uid;
	}
	
	function setUID($uid) {
		$this->uid = $uid;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/cron/class.tx_cal_reminder_cron.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/cron/class.tx_cal_reminder_cron.php']);
}

?>
