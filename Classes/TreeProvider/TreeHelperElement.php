<?php
namespace TYPO3\CMS\Cal\TreeProvider;
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
 * @author Mario Matzulla <mario(at)matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */

/**
 * this class displays a tree selector with nested tt_news categories.
 */
class TreeHelperElement extends \TYPO3\CMS\Backend\Form\Element\AbstractFormElement {
	
	public function getDbFileIcon($fName, $mode, $allowed, $itemArray, $selector = '', $params = array(), $onFocus = '', $table = '', $field = '', $uid = '', $config = array()) {
		return $this->dbFileIcons($fName, $mode, $allowed, $itemArray, $selector, $params, $onFocus, $table, $field, $uid, $config);
	}
	
	public function getRenderWizards($itemKinds, $wizConf, $table, $row, $field, $PA, $itemName, $specConf, $RTE = FALSE) {
		return $this->renderWizards($itemKinds, $wizConf, $table, $row, $field, $PA, $itemName, $specConf, $RTE);
	}
	
	/**
	 * Dummy handler
	 *
	 * @param string $table The table name of the record
	 * @param string $field The field name which this element is supposed to edit
	 * @param array $row The record data array where the value(s) for the field can be found
	 * @param array $additionalInformation An array with additional configuration options.
	 * @return string The HTML code for the TCEform field
	 */
	public function render($table, $field, $row, &$additionalInformation) {
		// deliberately empty as this class is not used the same way
		return '';
	}
}