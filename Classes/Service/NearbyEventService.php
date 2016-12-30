<?php
namespace TYPO3\CMS\Cal\Service;
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
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class NearbyEventService extends \TYPO3\CMS\Cal\Service\EventService {
	
	public function __construct() {
		parent::__construct();
		
		// Lets see if the user is logged in
		if ($this->rightsObj->isLoggedIn () && ! $this->rightsObj->isCalAdmin () && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ('wec_map') && $this->conf ['view.'] ['calendar.'] ['nearbyDistance'] > 0) {
			$user = $GLOBALS ['TSFE']->fe_user->user;
			
			/* Geocode the address */
			$latlong = \JBartels\WecMap\Utility\Cache::lookup ($user ['street'], $user ['city'], $user ['state'], $user ['zip'], $user ['country']);
			if (isset ($latlong ['long']) && isset ($latlong ['lat'])) {
				$this->internalAdditionTable = ',' . $this->conf ['view.'] ['calendar.'] ['nearbyAdditionalTable'];
				$this->internalAdditionWhere = ' ' . str_replace (Array (
						'###LONGITUDE###',
						'###LATITUDE###',
						'###DISTANCE###' 
				), Array (
						$latlong ['long'],
						$latlong ['lat'],
						$this->conf ['view.'] ['calendar.'] ['nearbyDistance'] 
				), $this->conf ['view.'] ['calendar.'] ['nearbyAdditionalWhere']);
			} else {
				$this->internalAdditionWhere = ' AND 1=2';
			}
		} else {
			// not logged in -> we can't localize
			$this->internalAdditionWhere = ' AND 1=2';
		}
	}
}

?>