<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Christian Technology Ministries International Inc.
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
 * International (http://CTMIinc.org). The WEC is developing TYPO3-based
 * (http://typo3.org) free software for churches around the world. Our desire
 * is to use the Internet to help offer new life through Jesus Christ. Please
 * see http://WebEmpoweredChurch.org/Jesus.
 *
 * You can redistribute this file and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This file is distributed in the hope that it will be useful for ministry,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the file!
 * *************************************************************
 */
class tx_cal_reminder_scheduler extends tx_scheduler_Task {
	var $uid;
	
	/**
	 * PHP4 wrapper for constructor,
	 * have to be here evne though the constructor is not defined in the derived class,
	 * else the constructor of the parent class will not be called in PHP4
	 */
	function tx_cal_reminder_scheduler() {
		$this->__construct ();
	}
	function execute() {
		require_once (t3lib_extMgm::extPath ('cal') . 'controller/class.tx_cal_functions.php');
		$event = t3lib_BEfunc::getRecord ('tx_cal_event', $this->uid);
		
		$select = '*';
		$table = 'tx_cal_fe_user_event_monitor_mm';
		$where = 'schedulerId = ' . $this->getTaskUid ();
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		$eventMonitor = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result);
		if (! is_array ($event)) {
			// the event could not be found, so we delete this reminder
			$this->remove ();
			return true;
		}
		
		// ******************
		// Constants defined
		// ******************
		define ('PATH_thisScript', str_replace ('//', '/', str_replace ('\\', '/', (php_sapi_name () == 'cgi' || php_sapi_name () == 'isapi' || php_sapi_name () == 'cgi-fcgi') && ($_SERVER ['ORIG_PATH_TRANSLATED'] ? $_SERVER ['ORIG_PATH_TRANSLATED'] : $_SERVER ['PATH_TRANSLATED']) ? ($_SERVER ['ORIG_PATH_TRANSLATED'] ? $_SERVER ['ORIG_PATH_TRANSLATED'] : $_SERVER ['PATH_TRANSLATED']) : ($_SERVER ['ORIG_SCRIPT_FILENAME'] ? $_SERVER ['ORIG_SCRIPT_FILENAME'] : $_SERVER ['SCRIPT_FILENAME']))));
		
		define ('PATH_site', dirname (PATH_thisScript) . '/');
		
		if (@is_dir (PATH_site . 'typo3/sysext/cms/tslib/')) {
			define ('PATH_tslib', PATH_site . 'typo3/sysext/cms/tslib/');
		} elseif (@is_dir (PATH_site . 'tslib/')) {
			define ('PATH_tslib', PATH_site . 'tslib/');
		} else {
			
			// define path to tslib/ here:
			$configured_tslib_path = '';
			
			// example:
			// $configured_tslib_path = '/var/www/mysite/typo3/sysext/cms/tslib/';
			
			define ('PATH_tslib', $configured_tslib_path);
		}
		
		if (PATH_tslib == '') {
			die ('Cannot find tslib/. Please set path by defining $configured_tslib_path in ' . basename (PATH_thisScript) . '.');
		}
		
		chdir (PATH_site);
		
		/* Check Page TSConfig for a preview page that we should use */
		$pageTSConf = t3lib_befunc::getPagesTSconfig ($event ['pid']);
		if ($pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin']) {
			$pageIDForPlugin = $pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin'];
		} else {
			$pageIDForPlugin = $event ['pid'];
		}
		
		$page = t3lib_BEfunc::getRecord ('pages', intval ($pageIDForPlugin), "doktype");
		
		if ($page ['doktype'] != 254) {
			$tx_cal_api = t3lib_div::makeInstance ('tx_cal_api');
			$tx_cal_api = &$tx_cal_api->tx_cal_api_without ($pageIDForPlugin);
			
			$eventObject = $tx_cal_api->modelObj->findEvent ($event ['uid'], 'tx_cal_phpicalendar', $tx_cal_api->conf ['pidList'], false, false, false, true);
			$tx_cal_api->conf ['view'] = 'event';
			
			$reminderService = &tx_cal_functions::getReminderService ();
			$reminderService->remind ($eventObject, $eventMonitor);
			return true;
		}
		
		$message = 'Cal was not able to send a reminder notice. You have to point to a page containing the cal Plugin. Configure in pageTSConf of page ' . $event ['pid'] . ': options.tx_cal_controller.pageIDForPlugin';
		throw new tx_scheduler_FailedExecutionException ($message, 1250596541);
	}
	function getUID() {
		return $this->uid;
	}
	function setUID($uid) {
		$this->uid = $uid;
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/cron/class.tx_cal_reminder_scheduler.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/cron/class.tx_cal_reminder_scheduler.php']);
}

?>