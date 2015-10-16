<?php
namespace TYPO3\CMS\Cal\Ajax;
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

// Exit, if script is called directly (must be included via eID in index_ts.php)
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (! defined ('PATH_typo3conf'))
	die ('Could not access this script directly!');

if ($_COOKIE ['fe_typo_user']) {
	session_id ($_COOKIE ['fe_typo_user']);
	session_start ();
}
// Initialize FE user object:
$feUserObj = \TYPO3\CMS\Frontend\Utility\EidUtility::initFeUser ();
// Connect to database:
\TYPO3\CMS\Frontend\Utility\EidUtility::connectDB ();
$controllerPiVarsGET = GeneralUtility::_GET ('tx_cal_controller');
$controllerPiVarsPOST = GeneralUtility::_POST ('tx_cal_controller');
$controllerPiVars = Array ();
if (is_array ($controllerPiVarsPOST) && is_array ($controllerPiVarsGET)) {
	$controllerPiVars = array_merge ($controllerPiVarsPOST, $controllerPiVarsGET);
} else if (is_array ($controllerPiVarsPOST)) {
	$controllerPiVars = $controllerPiVarsPOST;
} else if (is_array ($controllerPiVarsGET)) {
	$controllerPiVars = $controllerPiVarsGET;
}

$pid = intval ($controllerPiVars ['pid']);
$view = $controllerPiVars ['view'];

/** @var \TYPO3\CMS\Cal\Controller\Api $calAPI */
$calAPI = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\Api');
if (is_array ($_SESSION ['cal_api_' . $pid . '_conf'])) {
	$cObj = new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
	$GLOBALS ['TSFE'] = &$_SESSION ['cal_api_' . $pid . '_tsfe'];
	$GLOBALS ['TCA'] = &$_SESSION ['cal_api_' . $pid . '_tca'];
	$calAPI = $calAPI->tx_cal_api_with ($cObj, $_SESSION ['cal_api_' . $pid . '_conf']);
} else {
	$calAPI = $calAPI->tx_cal_api_without ($pid, $feUserObj);
	$_SESSION ['cal_api_' . $pid . '_conf'] = $calAPI->conf;
	$_SESSION ['cal_api_' . $pid . '_tsfe'] = $GLOBALS ['TSFE'];
	$_SESSION ['cal_api_' . $pid . '_tca'] = $GLOBALS ['TCA'];
}

if ($controllerPiVars ['translations']) {
	if ($calAPI->controller->conf ['language']) {
		$calAPI->controller->LLkey = $calAPI->controller->conf ['language'];
	}
	$tempScriptRelPath = $calAPI->controller->scriptRelPath;
	$calAPI->controller->scriptRelPath = $calAPI->controller->locallangPath;
	$calAPI->controller->pi_loadLL ();
	$calAPI->controller->scriptRelPath = $tempScriptRelPath;

	switch ($controllerPiVars ['translations']) {
		case 'day' :
		case 'month' :
			$returnValue = Array (
					"timeSeparator" => ' ' . $calAPI->controller->pi_getLL ('l_to') . ' ',
					"newEventText" => $calAPI->controller->pi_getLL ('l_new_event'),
					"shortMonths" => \TYPO3\CMS\Cal\Utility\Functions::getMonthNames ('%b'),
					"longMonths" => \TYPO3\CMS\Cal\Utility\Functions::getMonthNames ('%B'),
					"shortDays" => \TYPO3\CMS\Cal\Utility\Functions::getWeekdayNames ('%a'),
					"longDays" => \TYPO3\CMS\Cal\Utility\Functions::getWeekdayNames ('%A'),
					"buttonText" => Array (
							'today' => $calAPI->controller->pi_getLL ('l_today'),
							'lastWeek' => $calAPI->controller->pi_getLL ('l_prev'),
							'nextWeek' => $calAPI->controller->pi_getLL ('l_next'),
							'create' => $calAPI->controller->pi_getLL ('l_create'),
							'edit' => $calAPI->controller->pi_getLL ('l_edit'),
							'deleteText' => $calAPI->controller->pi_getLL ('l_delete'),
							'save' => $calAPI->controller->pi_getLL ('l_save'),
							'cancel' => $calAPI->controller->pi_getLL ('l_cancel') 
					) 
			);
			$ajax_return_data = json_encode ($returnValue);
			$htmlheader_contenttype = 'Content-Type: application/json';
			break;
	}
} else if (is_array ($controllerPiVars ['translate'])) {
	$tempScriptRelPath = $calAPI->controller->scriptRelPath;
	$calAPI->controller->scriptRelPath = $calAPI->controller->locallangPath;
	$calAPI->controller->pi_loadLL ();
	$calAPI->controller->scriptRelPath = $tempScriptRelPath;
	$translationArray = array();
	foreach ($controllerPiVars ['translate'] as $value) {
		$translationArray [$value] = $calAPI->controller->pi_getLL ('l_' . strtolower ($value));
	}
	$ajax_return_data = json_encode ($translationArray);
	$htmlheader_contenttype = 'Content-Type: application/json';
} else {
	$rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
	$checkedView = $rightsObj->checkView ($view);
	$res = '';
	$error = true;
	
	if ($checkedView == $view) {
		$error = false;
		$return = $calAPI->controller->getContent (false);
		echo $return;
		exit ();
	} else {
		$res = 'You do not have the proper rights!' . $checkedView . '=' . $view;
	}
	
	$ajax_return_data = GeneralUtility::array2xml (array (
			'error' => $error,
			'response' => $res 
	));
	$htmlheader_contenttype = 'Content-Type: text/xml';
}
header ('Expires: ' . gmdate ('D, d M Y H:i:s') . ' GMT');
// gmdate is ok.
header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . 'GMT');
header ('Cache-Control: no-cache, must-revalidate');
header ('Pragma: no-cache');
header ('Content-Length: ' . strlen ($ajax_return_data));
header ($htmlheader_contenttype);

echo $ajax_return_data;
exit ();
?>