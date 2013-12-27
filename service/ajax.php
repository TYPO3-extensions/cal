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

// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

if($_COOKIE['fe_typo_user']){
	session_id($_COOKIE['fe_typo_user']);
	session_start();
}
	// Initialize FE user object:
$feUserObj = tslib_eidtools::initFeUser();
// Connect to database:
tslib_eidtools::connectDB();
$controllerPiVarsGET = t3lib_div::_GET('tx_cal_controller');
$controllerPiVarsPOST = t3lib_div::_POST('tx_cal_controller');
$controllerPiVars = array();
if(is_array($controllerPiVarsPOST) && is_array($controllerPiVarsGET)){
	$controllerPiVars = array_merge($controllerPiVarsPOST, $controllerPiVarsGET);
}else if (is_array($controllerPiVarsPOST)){
	$controllerPiVars = $controllerPiVarsPOST;
}else if (is_array($controllerPiVarsGET)){
	$controllerPiVars = $controllerPiVarsGET;
}

$pid = intval($controllerPiVars['pid']);
$uid = intval($controllerPiVars['uid']);
$calendar = intval($controllerPiVars['calendar']);
$pidList = $controllerPiVars['pidList'];
$view = $controllerPiVars['view'];
$type = $controllerPiVars['type'];
require_once (t3lib_extMgm::extPath('cal').'/controller/class.tx_cal_api.php');

if(is_array($_SESSION['cal_api_'.$pid.'_conf'])){
	$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
	$cObj = t3lib_div :: makeInstance('tslib_cObj');
	$GLOBALS['TSFE'] = &$_SESSION['cal_api_'.$pid.'_tsfe'];
	$GLOBALS['TCA'] = &$_SESSION['cal_api_'.$pid.'_tca'];
	$tx_cal_api = &$tx_cal_api->tx_cal_api_with($cObj, $_SESSION['cal_api_'.$pid.'_conf']);
}else{
	$tx_cal_api = t3lib_div :: makeInstance('tx_cal_api');
	$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pid, $feUserObj);
	$_SESSION['cal_api_'.$pid.'_conf'] = $tx_cal_api->conf;
	$_SESSION['cal_api_'.$pid.'_tsfe'] = $GLOBALS['TSFE'];
	$_SESSION['cal_api_'.$pid.'_tca'] = $GLOBALS['TCA'];
}

if($controllerPiVars['translations']) {
	if ($tx_cal_api->controller->conf['language']) {
		$tx_cal_api->controller->LLkey = $tx_cal_api->controller->conf['language'];
	}
	$tx_cal_api->controller->pi_loadLL();
	switch ($controllerPiVars['translations']){
		case 'day':
		case 'month':
			$returnValue = array(
				"timeSeparator" => ' '.$tx_cal_api->controller->pi_getLL('l_to').' ',
				"newEventText" 	=> $tx_cal_api->controller->pi_getLL('l_new_event'),
				"shortMonths" 	=> tx_cal_functions::getMonthNames('%b'),
				"longMonths"	=> tx_cal_functions::getMonthNames('%B'),
				"shortDays"     => tx_cal_functions::getWeekdayNames('%a'),
				"longDays"	=> tx_cal_functions::getWeekdayNames('%A'),
				"buttonText" => Array(
						'today'		=>	$tx_cal_api->controller->pi_getLL('l_today'),
						'lastWeek'	=>	$tx_cal_api->controller->pi_getLL('l_prev'),
						'nextWeek'	=>	$tx_cal_api->controller->pi_getLL('l_next'),
						'create'	=>	$tx_cal_api->controller->pi_getLL('l_create'),
						'edit'		=>	$tx_cal_api->controller->pi_getLL('l_edit'),
						'deleteText'	=>	$tx_cal_api->controller->pi_getLL('l_delete'),
						'save'		=>	$tx_cal_api->controller->pi_getLL('l_save'),
						'cancel'	=>	$tx_cal_api->controller->pi_getLL('l_cancel')
				)
			);
			$ajax_return_data = json_encode( $returnValue );
			$htmlheader_contenttype = 'Content-Type: application/json';
			break;
	}
} else if(is_array($controllerPiVars['translate'])){
	$tx_cal_api->controller->pi_loadLL();
	foreach($controllerPiVars['translate'] as $value){
		$translationArray[$value] = $tx_cal_api->controller->pi_getLL('l_'.strtolower($value));
	}
	$ajax_return_data = json_encode($translationArray);
	$htmlheader_contenttype = 'Content-Type: application/json';
} else {
	$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
	$checkedView = $rightsObj->checkView($view);
	$res = '';
	$error = true;
	
	if($checkedView == $view){
		$error = false;
		$return = $tx_cal_api->controller->getContent(false);
		echo $return;
		exit;
	}else{
		$res = 'You do not have the proper rights!'.$checkedView.'='.$view;
	}
	
	$ajax_return_data = t3lib_div::array2xml(array('error'=>$error,'response'=>$res));
	$htmlheader_contenttype = 'Content-Type: text/xml';
}
header('Expires: '.gmdate( 'D, d M Y H:i:s' ).' GMT');
// gmdate is ok.
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');
header('Content-Length: '.strlen($ajax_return_data));
header($htmlheader_contenttype);

echo $ajax_return_data;
exit;
?>