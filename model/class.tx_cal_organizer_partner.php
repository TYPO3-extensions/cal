<?php
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

// equire_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('cal').'model/class.tx_cal_organizer.php');

/**
 * Base model for the calendar organizer.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_organizer_partner extends tx_cal_organizer {
	var $partner;
	
	/**
	 * Constructor
	 * 
	 * @param integer $uid
	 *        	to search for
	 * @param string $pidList
	 *        	to search in
	 */
	function tx_cal_organizer_partner($uid, $pidList) {
		require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('partner') . 'api/class.tx_partner_main.php');
		require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('partner') . 'api/class.tx_partner_div.php');
		
		$this->partner = new tx_partner_main();
		$this->partner->getPartner ($uid);
		$this->partner->getContactInfo ($this->conf ['view.'] ['organizer.'] ['contactInfoType']);
		
		$this->tx_cal_organizer ($this->partner->data, $this->getType ());
		
		$this->setType ('tx_partner_main');
		$this->setObjectType ('organizer');
		$this->templatePath = $this->conf ['view.'] ['organizer.'] ['organizerModelTemplate4Partner'];
	}
	function getName() {
		$partnername = '';
		switch ($this->partner->data ['type']) {
			case 0 :
				$partnername = $this->partner->data ['first_name'] . ' ' . $this->partner->data ['last_name'];
				break;
			case 1 :
				$partnername = $this->partner->data ['org_name'];
				break;
			default :
				$partnername = $this->partner->data ['label'];
		}
		return $partnername;
	}
	function getFirstName() {
		return $this->partner->data ['first_name'];
	}
	function setFirstName($t) {
		$this->partner->data ['first_name'] = $t;
	}
	function getMiddleName() {
		return $this->partner->data ['middle_name'];
	}
	function setMiddleName($t) {
		$this->partner->data ['middle_name'] = $t;
	}
	function getLastName() {
		return $this->partner->data ['last_name'];
	}
	function setLastName($t) {
		$this->partner->data ['last_name'] = $t;
	}
	function getStreetNumber() {
		return $this->partner->data ['street_number'];
	}
	function setStreetNumber($t) {
		$this->partner->data ['street_number'] = $t;
	}
	function getZip() {
		return $this->partner->data ['postal_code'];
	}
	function setZip($t) {
		$this->partner->data ['postal_code'] = $t;
	}
	function fillTemplate($subpartMarker) {
		global $LANG;
		$LANG->includeLLFile ('EXT:partner/locallang.php');
		return parent::fillTemplate ($subpartMarker);
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_cal_organizer_partner.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_cal_organizer_partner.php']);
}
?>