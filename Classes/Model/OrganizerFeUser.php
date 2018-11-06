<?php
namespace TYPO3\CMS\Cal\Model;
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
 * Base model for the calendar organizer.
 * Provides basic model functionality that other
 * models can use or override by extending the class.
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class OrganizerFeUser extends \TYPO3\CMS\Cal\Model\Organizer {
	
	/**
	 * Constructor
	 * 
	 * @param array $row
	 *        	array
	 * @param string $pidList
	 *        	to search in
	 */
	public function __construct($row, $pidList) {
		parent::__construct($row, $pidList);
		$this->setType ('tx_feuser');
		$this->setObjectType ('organizer');
		$this->createOrganizer ($row);
		$this->templatePath = $this->conf ['view.'] ['organizer.'] ['organizerModelTemplate4FEUser'];
	}
	function createOrganizer($row) {
		$this->setUid ($row ['uid']);
		$this->setName ($row ['name']);
		$this->setStreet ($row ['address']);
		$this->setCity ($row ['city']);
		$this->setZip ($row ['zip']);
		$this->setCountry ($row ['country']);
		$this->setPhone ($row ['telephone']);
		$this->setEmail ($row ['email']);
		$this->setImage (\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode (',', $row ['image'], 1));
		$this->setLink ($row ['www']);
		$this->row = $row;
	}
}

?>