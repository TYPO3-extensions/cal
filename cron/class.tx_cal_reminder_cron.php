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
		
		$event = t3lib_BEfunc::getRecord ('tx_cal_event', $this->uid);
		if(!is_array($event)) return;
		// ******************
		// Constants defined
		// ******************
		
		define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));
		
		define('PATH_site', dirname(PATH_thisScript).'/');
		
		if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
			define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
		} elseif (@is_dir(PATH_site.'tslib/')) {
			define('PATH_tslib', PATH_site.'tslib/');
		} else {
		
			// define path to tslib/ here:
			$configured_tslib_path = '';
		
			// example:
			// $configured_tslib_path = '/var/www/mysite/typo3/sysext/cms/tslib/';
		
			define('PATH_tslib', $configured_tslib_path);
		}
		
		if (PATH_tslib=='') {
			die('Cannot find tslib/. Please set path by defining $configured_tslib_path in '.basename(PATH_thisScript).'.');
		}
		
		require_once (t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');
		$tx_cal_api = t3lib_div :: makeInstanceClassName('tx_cal_api');
		$tx_cal_api = new $tx_cal_api();
		$tx_cal_api = &$tx_cal_api->tx_cal_api_without($event['pid']);
		
		$eventObject = $tx_cal_api->findEvent($event['uid'], 'tx_cal_phpicalendar', $event['pid']);
		$eventObject->conf['view'] = 'event';
		
		$reminderService = &getReminderService();
		$reminderService->remind($eventObject);
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