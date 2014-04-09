<?php

/*
 * @author: Mario Matzulla
 */
class tx_cal_template_generator {
	
	// function tx_cal_template_generator($pageIDForPlugin, $starttime = null, $endtime = null) {
	function tx_cal_template_generator() {
	}
	function generateYear($yearNumber) {
		return new tx_cal_year_model ($yearNumber);
	}
	function generateMonth($monthNumber) {
	}
	function generateWeek($weekNumber) {
	}
	function generateDay($date) {
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/mod1/class.tx_cal_template_generator.php']) {
	require_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/mod1/class.tx_cal_template_generator.php']);
}

?>