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

namespace TYPO3\CMS\Cal\Cron;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Cal\Controller\Api;
use TYPO3\CMS\Cal\Utility\Functions;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\FailedExecutionException;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * ReminderScheduler
 */
class ReminderScheduler extends AbstractTask {
	
	var $uid;
	
	/**
	 * PHP4 wrapper for constructor,
	 * have to be here evne though the constructor is not defined in the derived class,
	 * else the constructor of the parent class will not be called in PHP4
	 */
	public function __construct() {
		parent::__construct ();
	}
	
	public function execute() {
		$event = BackendUtility::getRecord ('tx_cal_event', $this->uid);
		
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
		$pageTSConf = BackendUtility::getPagesTSconfig ($event ['pid']);
		if ($pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin']) {
			$pageIDForPlugin = $pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin'];
		} else {
			$pageIDForPlugin = $event ['pid'];
		}
		
		$page = BackendUtility::getRecord ('pages', intval ($pageIDForPlugin), "doktype");
		
		if ($page ['doktype'] != 254) {
			/** @var \TYPO3\CMS\Cal\Controller\Api $calAPI */
			$calAPI = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\Api');
			$calAPI = &$calAPI->tx_cal_api_without ($pageIDForPlugin);
			
			$eventObject = $calAPI->modelObj->findEvent ($event ['uid'], 'tx_cal_phpicalendar', $calAPI->conf ['pidList'], false, false, false, false);
			$calAPI->conf ['view'] = 'event';
			
			$reminderService = &Functions::getReminderService ();
			$reminderService->remind ($eventObject, $eventMonitor);
			return true;
		}
		
		$message = 'Cal was not able to send a reminder notice. You have to point to a page containing the cal Plugin. Configure in pageTSConf of page ' . $event ['pid'] . ': options.tx_cal_controller.pageIDForPlugin';
		throw new FailedExecutionException ($message, 1250596541);
	}
	
	public function getUID() {
		return $this->uid;
	}
	
	public function setUID($uid) {
		$this->uid = $uid;
	}
}