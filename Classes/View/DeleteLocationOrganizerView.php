<?php
namespace TYPO3\CMS\Cal\View;
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
 * A service which renders a form to create / edit a location or organizer.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class DeleteLocationOrganizerView extends \TYPO3\CMS\Cal\View\FeEditingBaseView {
	
	var $isLocation = true;
	var $objectString = 'location';
	
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Draws a delete form for a location or an organizer.
	 * 
	 * @param
	 *        	boolean True if a location should be deleted
	 * @param
	 *        	object		The object to be deleted
	 * @param
	 *        	object		The cObject of the mother-class.
	 * @param
	 *        	object		The rights object.
	 * @return string HTML output.
	 */
	public function drawDeleteLocationOrOrganizer($isLocation = true, &$object) {
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['delete_location.'] ['template']);
		if ($page == '') {
			return '<h3>category: no delete location template file found:</h3>' . $this->conf ['view.'] ['delete_location.'] ['template'];
		}
		
		$this->isLocation = $isLocation;
		$this->object = $object;
		if ($isLocation) {
			$this->objectString = 'location';
		} else {
			$this->objectString = 'organizer';
		}
		
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		
		$sims ['###UID###'] = $this->conf ['uid'];
		$sims ['###TYPE###'] = $this->conf ['type'];
		$sims ['###VIEW###'] = 'remove_' . $this->objectString;
		$sims ['###LASTVIEW###'] = $this->controller->extendLastView ();
		$sims ['###L_DELETE_LOCATION###'] = $this->controller->pi_getLL ('l_delete_' . $this->objectString);
		$sims ['###L_DELETE###'] = $this->controller->pi_getLL ('l_delete');
		$sims ['###L_CANCEL###'] = $this->controller->pi_getLL ('l_cancel');
		$sims ['###ACTION_URL###'] = htmlspecialchars ($this->controller->pi_linkTP_keepPIvars_url (array (
				'view' => 'remove_' . $this->objectString 
		)));
		$this->getTemplateSubpartMarker ($page, $sims, $rems, $wrapped);
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, Array (), $rems, Array ());
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, Array (), Array ());
		$sims = Array ();
		$rems = Array ();
		$wrapped = Array ();
		$this->object->getMarker ($page, $sims, $rems, $wrapped);
		
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, $rems, $wrapped);
	}
}

?>