<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2004 
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * This hook extends the tcemain class.
 * It catches changes on tx_cal_event
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_tcemain_processcmdmap {
	function processCmdmap_postProcess(&$command, &$table, &$id, &$value, &$this) {
		switch($table) {
			case 'tx_cal_event' :
				$select = '*';
				$table = 'tx_cal_event';
				$where = 'uid = '.$id;
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
					$pageTSConf = t3lib_befunc::getPagesTSconfig($row['pid']);
					$notificationService =& getNotificationService();
					if($command=='delete'){
						$notificationService->notifyOfChanges($row, array('deleted' => $value), $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['notify.']);
						
						/* Clean up any pending reminders for this event */
						$reminderService = &getReminderService();
						$reminderService->deleteReminder($row['uid']);

					}else{
						$notificationService->notifyOfChanges($row, array($command => $value), $pageTSConf['options.']['tx_cal_controller.']['view.']['event.']['notify.']);
					}
				}
			break;
			case 'tx_cal_calendar' :
				require_once(t3lib_extMgm::extPath('cal').'service/class.tx_cal_icalendar_service.php');
				$service = t3lib_div::makeInstance('tx_cal_icalendar_service');
				
				$service->deleteScheduledUpdates($id);
			break;
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processcmdmap.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/hooks/class.tx_cal_tcemain_processcmdmap.php']);
}
?>