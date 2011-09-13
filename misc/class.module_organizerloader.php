<?php
/***************************************************************
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
 ***************************************************************/
 
require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class module_organizerloader extends tx_cal_base_view {
	
	/**
	 * The function adds organizer markers into the event template
	 * @param Object $moduleCaller Instance of the event model (phpicalendar_model)
	 */
	function start(&$moduleCaller){
		if ($moduleCaller->getOrganizerId() > 0) {
			$this->modelObj = &tx_cal_registry::Registry('basic','modelcontroller'); 
			$this->cObj = &tx_cal_registry::Registry('basic','cobj'); 

			$moduleCaller->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useOrganizerStructure = ($moduleCaller->confArr['useOrganizerStructure'] ? $moduleCaller->confArr['useOrganizerStructure'] : 'tx_cal_organizer');
			$organizer = $this->modelObj->findOrganizer($moduleCaller->getOrganizerId(),$useOrganizerStructure);
			
			if(is_object($organizer)) {
				$page = $this->cObj->fileResource($moduleCaller->conf['module.']['organizerloader.']['template']);
				if ($page == '') {
					return '<h3>module organizerloader: no template file found:</h3>' . $moduleCaller->conf['module.']['organizerloader.']['template'];
				}
				$sims = array();
				$rems = array();
				$wrapped = array();
				$organizer->getMarker($page, $sims, $rems, $wrapped);
				return tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, array());
			}
		}
		return '';
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/misc/class.module_organizerloader.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/misc/class.module_organizerloader.php']);
}
?>
