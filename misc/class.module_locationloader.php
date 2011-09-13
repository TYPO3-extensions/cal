<?php
/*
 * Created on Nov 28, 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class module_locationloader extends tx_cal_base_view {
	
	/**
	 * The function adds location markers into the event template
	 * @param Object $moduleCaller Instance of the event model (phpicalendar_model)
	 */
	function start(&$moduleCaller){
		if ($moduleCaller->getLocationId() > 0) {
			$moduleCaller->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
			$useLocationStructure = ($moduleCaller->confArr['useLocationStructure'] ? $moduleCaller->confArr['useLocationStructure'] : 'tx_cal_location');
			$location = $moduleCaller->controller->modelObj->findLocation($moduleCaller->getLocationId(),$useLocationStructure);
			
			$page = $moduleCaller->cObj->fileResource($moduleCaller->conf['module.']['locationloader.']['template']);
			if ($page == '') {
				return '<h3>module locationloader: no template file found:</h3>' . $moduleCaller->conf['module.']['locationloader.']['template'];
			}
			$sims = array();
			$rems = array();
			$location->getLocationMarker($page, $sims, $rems);
			return $moduleCaller->cObj->substituteMarkerArrayCached($page, $sims, $rems, array());
		}
		return 'test';
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/misc/class.module_locationloader.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/misc/class.module_locationloader.php']);
}
?>
