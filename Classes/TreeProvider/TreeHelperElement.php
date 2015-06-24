<?php
namespace TYPO3\CMS\Cal\TreeProvider;
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Christian Technology Ministries International Inc.
 * All rights reserved
 *
 * Adapted from original tt_news code by Ruper Germann
 * (c) 2005 Rupert Germann <rupi@gmx.li>
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