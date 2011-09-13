<?php
/*
 * Register necessary class names with autoloader
 *
 */
// TODO: document necessity of providing autoloader information
return array(
	'tx_cal_calendar_scheduler' => t3lib_extMgm::extPath('cal', 'cron/class.tx_cal_calendar_scheduler.php'),
	'tx_cal_reminder_scheduler' => t3lib_extMgm::extPath('cal', 'cron/class.tx_cal_reminder_scheduler.php')
);
?>