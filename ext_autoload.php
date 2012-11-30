<?php
/*
 * Register necessary class names with autoloader
 *
 */
// TODO: document necessity of providing autoloader information

$extensionPath = t3lib_extMgm::extPath('cal');

return array(
	'tx_cal_calendar_scheduler' => $extensionPath . 'cron/class.tx_cal_calendar_scheduler.php',
	'tx_cal_reminder_scheduler' => $extensionPath . 'cron/class.tx_cal_reminder_scheduler.php',
	'ux_edit_wizard' => $extensionPath . 'xclass/class.ux_edit_wizard.php'
);
?>