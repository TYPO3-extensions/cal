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
 * This class is a temporary store view helper for the Fluid templating engine.
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class Tx_Cal_ViewHelpers_TempStoreViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	
	/**
	 * @var array
	 */
	static private $store = Array();
	
    /**
     * Renders some classic dummy content: Lorem Ipsum...
     *
     * @param string $key The key
     * @param object $set The object to set
     * @param object $get The object to set
     * @return object If $get is defined an object will be returned (if found in the store)
     * @author Mario Matzulla <mario@matzullas.de>
     */
    public function render($key = NULL, $set = NULL, $get = NULL) {
        if($key != NULL && $set != NULL){
        	self::$store[$key] = $set;
        } else if($get != NULL){
        	return self::$store[$get];
        }
    }
}

?>