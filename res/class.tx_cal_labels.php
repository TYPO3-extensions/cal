<?php
/*
 * Created on Nov 28, 2006
 *
 * class for labels in tce forms
 */
 
/**
 *
 * @author Steffen Kamper <info(at)sk-typo3.de>
 */

require_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');

class tx_cal_labels {
		
	function getEventRecordLabel(&$params, &$pObj)	{
		
        if (!$params['table'] == 'tx_cal_event') return '';
		
		// Get complete record 
		$rec = t3lib_BEfunc::getRecord($params['table'], $params['row']['uid']);
		$day = $rec['start_date'] + strtotimeOffset($rec['start_date']);
		$time = $rec['start_time'];
		
		if($rec['allday']) {
			/* If we have an all day event, only show the date */
			$datetime = gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $day);
		} else {
			/* For normal events, show both the date and time */
			$datetime = gmdate($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'].' '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $day + $rec['start_time']);
		}

		// Assemble the label
		$label = $datetime.': '.$rec['title'];

        //Write to the label
        $params['title'] =  $label;
	}
		
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_labels.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_labels.php']);
}  
  
?>
