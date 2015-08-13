<?php
namespace TYPO3\CMS\Cal\Cron;
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

use TYPO3\CMS\Backend\Utility\BackendUtility;

include_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('gabriel', 'class.tx_gabriel_event.php'));
class ReminderCron extends \tx_gabriel_event {
	
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
		$eventMonitor = BackendUtility::getRecord ('tx_cal_fe_user_event_monitor_mm', $this->uid);
		
		$event = BackendUtility::getRecord ('tx_cal_event', $eventMonitor ['uid_local']);
		
		if (! is_array ($event))
			return;
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
		
		/* Check Page TSConfig for a preview page that we should use */
		$pageTSConf = BackendUtility::getPagesTSconfig ($event ['pid']);
		if ($pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin']) {
			$pageIDForPlugin = $pageTSConf ['options.'] ['tx_cal_controller.'] ['pageIDForPlugin'];
		} else {
			$pageIDForPlugin = $event ['pid'];
		}
		
		$page = BackendUtility::getRecord ('pages', intval ($pageIDForPlugin), "doktype");
		
		if ($page ['doktype'] != 254) {
			$calAPI = new \TYPO3\CMS\Cal\Controller\Api();
			$calAPI = &$calAPI->tx_cal_api_without ($pageIDForPlugin);
			
			$eventObject = $calAPI->modelObj->findEvent ($event ['uid'], 'tx_cal_phpicalendar', $calAPI->conf ['pidList'], false, false, false, true);
			$tx_cal_api->conf ['view'] = 'event';
			
			$reminderService = &\TYPO3\CMS\Cal\Utility\Functions::getReminderService ();
			$reminderService->remind ($eventObject, $eventMonitor);
		}
	}
	
	public function getUID() {
		return $this->uid;
	}
	
	public function setUID($uid) {
		$this->uid = $uid;
	}
}

?>