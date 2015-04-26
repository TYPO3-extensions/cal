<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2005 Mario Matzulla (mario(at)matzullas.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;

/**
 * Module 'Indexer' for the 'cal' extension.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */


$GLOBALS ['LANG']->includeLLFile ('EXT:cal/Classes/Backend/Modul/locallang.xml');
$BE_USER->modAccess ($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
                               // DEFAULT initialization of a module [END]

// Make instance:
$SOBE = new \TYPO3\CMS\Cal\Backend\Modul\CalIndexer ();
$SOBE->init ();

$SOBE->main ();
$SOBE->printContent ();
?>