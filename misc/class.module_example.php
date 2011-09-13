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
class module_example extends tx_cal_base_view {
	
	function start(&$moduleCaller){
		return 'text from another module';
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/misc/class.module_example.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/misc/class.module_example.php']);
}
?>
