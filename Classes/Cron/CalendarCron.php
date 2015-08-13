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
include_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('gabriel', 'class.tx_gabriel_event.php'));
class CalendarCron extends \tx_gabriel_event {
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
		$service = new \TYPO3\CMS\Cal\Service\ICalendarService();
		
		$service->update ($this->uid);
	}
	
	public function getUID() {
		return $this->uid;
	}
	
	public function setUID($uid) {
		$this->uid = $uid;
	}
}

?>