<?php
namespace TYPO3\CMS\Cal\Cron;
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
class IndexerScheduler extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
	
	public $eventFolder = '';
	
	public $typoscriptPage = '';
	
	public $starttime = '';
	
	public $endtime = '';
	
	public function execute() {
		$success = true;
		$logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
		
		$starttime = $this->getTimeParsed($this->starttime)->format('%Y%m%d');
		$endtime = $this->getTimeParsed($this->endtime)->format('%Y%m%d');

		$logger->info('Starting to index cal events from '.$starttime.' until '.$endtime.'. Using Typoscript page '.$this->typoscriptPage.' as configuration reference.');
		$rgc = new \TYPO3\CMS\Cal\Utility\RecurrenceGenerator($this->typoscriptPage, $starttime, $endtime);
		foreach(explode(',',$this->eventFolder) as $folderId){
			$eventFolder = intval($folderId);
			if($eventFolder > 0) {
				$logger->info('Working with folder '.$eventFolder);
				$rgc->cleanIndexTable ($eventFolder);
				$logger->info('Starting to index... ');
				$rgc->generateIndex ($eventFolder);
				$logger->info('done.');
			}
		}
		$logger->info('IndexerScheduler done.');
		return $success;
	}
	
	private function getTimeParsed($timeString) {
		$dp = new \TYPO3\CMS\Cal\Controller\DateParser ();
		$dp->parse ($timeString, 0, '');
		return $dp->getDateObjectFromStack ();
	}	
}

?>