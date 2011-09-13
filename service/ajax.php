<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
 * (http://evangelize.org). The WEC is developing TYPO3-based
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
$tx_cal_api = t3lib_div :: makeInstanceClassName('tx_cal_api');
$tx_cal_api = new $tx_cal_api();
$tx_cal_api = &$tx_cal_api->tx_cal_api_without($pid, $feUserObj);

$rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');

$checkedView = $rightsObj->checkView($view);

$res = '';
$error = true;
if($checkedView == $view){
	$error = false;
	echo $tx_cal_api->controller->getContent(false);
	exit;
}else{
	$res = 'You do not have the proper rights!'.$checkedView.'='.$view;
}

$ajax_return_data = t3lib_div::array2xml(array('error'=>$error,'response'=>$res));
$htmlheader_contenttype = 'Content-Type: text/xml';
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
// gmdate is ok.
header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');
header('Content-Length: '.strlen($ajax_return_data));
header($htmlheader_contenttype);

echo $ajax_return_data;
exit;
?>