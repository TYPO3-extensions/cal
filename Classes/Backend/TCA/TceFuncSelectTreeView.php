<?php
namespace TYPO3\CMS\Cal\Backend\TCA;
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
 * This function displays a selector with nested categories.
 * The original code is borrowed from the extension "Digital Asset Management" (tx_dam) author: Rene© Fritz <r.fritz@colorcube.de>
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */

/**
 * extend class \TYPO3\CMS\Backend\Tree\View\AbstractTreeView to change function wrapTitle().
 */
class TceFuncSelectTreeView extends \TYPO3\CMS\Backend\Tree\View\AbstractTreeView {
	var $TCEforms_itemFormElName = '';
	var $TCEforms_nonSelectableItemsArray = array ();
	
	/**
	 * wraps the record titles in the tree with links or not depending on if they are in the TCEforms_nonSelectableItemsArray.
	 *
	 * @param string $title:
	 *        	title
	 * @param array $v:
	 *        	array with uid and title of the current item.
	 * @return string wrapped title
	 */
	public function wrapTitle($title, $v, $bank = 0) {
		if ($v ['uid'] > 0) {
			if (in_array ($v ['uid'], $this->MOUNTS) || in_array ($v ['uid'], $this->TCEforms_nonSelectableItemsArray)) {
				return '<a href="#" title="' . $v ['title'] . '"><span style="color:#999;cursor:default;">' . $title . '</span></a>';
			} else {
				$hrefTitle = $v ['title'];
				$aOnClick = 'setFormValueFromBrowseWin(\'' . $this->TCEforms_itemFormElName . '\',' . $v ['uid'] . ',\'' . addslashes ($title) . '\'); return false;';
				return '<a href="#" onclick="' . htmlspecialchars ($aOnClick) . '" title="' . htmlentities ($v ['title']) . '">' . $title . '</a>';
			}
		} else {
			return $title;
		}
	}
	
	/**
	 * Get icon for the row.
	 * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
	 *
	 * @param
	 *        	array		Item row.
	 * @return string tag.
	 */
	public function getIcon($row) {
		if (in_array ($row ['uid'], $this->MOUNTS)) {
			$this->table = 'tx_cal_calendar';
		}
		$return = parent::getIcon ($row);
		$this->table = 'tx_cal_category';
		return $return;
	}
	
	/**
	 * Returns the root icon for a tree/mountpoint (defaults to the globe)
	 *
	 * @param
	 *        	array		Record for root.
	 * @return string image tag.
	 */
	public function getRootIcon($rec) {
		return $this->wrapIcon ('<img src="' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath ('cal') . 'res/icons/icon_tx_cal_calendar.gif" width="18" height="16" alt="" />', array ());
	}
}

?>