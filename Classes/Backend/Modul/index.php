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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;

/**
 * Module 'Indexer' for the 'cal' extension.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */


$GLOBALS ['LANG']->includeLLFile ('EXT:cal/Resources/Private/Language/locallang_indexer.xml');
$BE_USER->modAccess ($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
                               // DEFAULT initialization of a module [END]

// Make instance:
$SOBE = new \TYPO3\CMS\Cal\Backend\Modul\CalIndexer ();
$SOBE->init ();

$SOBE->main ();
$SOBE->printContent ();
?>