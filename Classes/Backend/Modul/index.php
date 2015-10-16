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

/**
 * Module 'Indexer' for the 'cal' extension.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */

$MCONF ["name"] = "tools_calrecurrencegenerator";

$MCONF ["access"] = "admin";
// MCONF["script"]="index.php";
$MCONF ["script"] = "_DISPATCH";

$MLANG ["default"] ["tabs_images"] ["tab"] = "icon_tx_cal_indexer.gif";
$MLANG ["default"] ["ll_ref"] = "LLL:EXT:cal/Resources/Private/Language/locallang_indexer_mod.xml";


$GLOBALS ['LANG']->includeLLFile ('EXT:cal/Resources/Private/Language/locallang_indexer.xml');

$GLOBALS ['BE_USER']->modAccess ($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
                               // DEFAULT initialization of a module [END]

// Make instance:
$SOBE = new \TYPO3\CMS\Cal\Backend\Modul\CalIndexer ();
$SOBE->init ();

$SOBE->main ();
$SOBE->printContent ();
?>