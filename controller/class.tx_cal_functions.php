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

/**
 * This is a collection of many useful functions
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */

// function returns starttime and endtime and event length for drawing into a grid

// word wrap function that returns specified number of lines
// when lines is 0, it returns the entire string as wordwrap() does it
function word_wrap($str, $length, $lines=0) {
	if ($lines > 0) {
		$len = $length * $lines;
		if ($len < strlen($str)) {
			$str = substr($str,0,$len).'...';
		}
	}
	return $str;
}

function replace_tags($tags = array(), $page) {
	if (sizeof($tags) > 0)
	{
		$sims = array();
		foreach ($tags as $tag => $data)
		{
			// This replaces any tags
			$page = str_replace($tag,$data,$page);
		}

	}
	return $page;

}

/*
 * Expands a path if it includes EXT: shorthand.
 * @param		string		The path to be expanded.
 * @return					The expanded path.
 */
function expandPath($path) {
	if (!strcmp(substr($path,0,4),'EXT:'))	{
		list($extKey,$script)=explode('/',substr($path,4),2);
		if ($extKey && t3lib_extMgm::isLoaded($extKey))	{
			$extPath=t3lib_extMgm::extPath($extKey);
			$path=substr($extPath,strlen(PATH_site)).$script;
		}
	}

	return $path;

}

function clearCache() {
	$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', 'reg1=77');
}


function &getNotificationService(){
	$key = 'tx_default_notification';
	$serviceChain = '';
	/* Loop over all services providign the specified service type and subtype */
	while (is_object($notificationService = t3lib_div::makeInstanceService('cal_view', 'notify', $serviceChain))) {
		$serviceChain.=','.$notificationService->getServiceKey();
		/* If the key of the current service matches what we're looking for, return the object */
		if($key == $notificationService->getServiceKey()) {
			return $notificationService;
		}
	}
}

function &getReminderService() {
	$key = 'tx_default_reminder';
	$serviceChain = '';
	
	/* Loop over all services providign the specified service type and subtype */
	while (is_object($reminderService = t3lib_div::makeInstanceService('cal_view', 'remind', $serviceChain))) {
		$serviceChain.=','.$reminderService->getServiceKey();
		/* If the key of the current service matches what we're looking for, return the object */
		if($key == $reminderService->getServiceKey()) {
			return $reminderService;
		}
	}
}

function &getEventService(){
	$key = 'tx_cal_phpicalendar';
	$serviceChain = '';
	/* Loop over all services providign the specified service type and subtype */
	while (is_object($eventService = t3lib_div::makeInstanceService('cal_event_model', 'event', $serviceChain))) {
		$serviceChain.=','.$eventService->getServiceKey();
		/* If the key of the current service matches what we're looking for, return the object */
		if($key == $eventService->getServiceKey()) {
			return $eventService;
		}
	}
}


//get used charset
function getCharset() {
    if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'])	{	// First priority: forceCharset! If set, this will be authoritative!
		$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
	} elseif (is_object($GLOBALS['LANG']))	{
		$charset = $GLOBALS['LANG']->charSet;	// If "LANG" is around, that will hold the current charset
	} else {
		$charset = 'iso-8859-1';	// THIS is just a hopeful guess!
	}
    
    return $charset;
}

function getOrderBy($table) {
	global $TCA;
	t3lib_div::loadTCA($table);
	
	if(isset($TCA[$table]['ctrl']['default_sortby'])) {
		$orderBy = str_replace("ORDER BY ", "", $TCA[$table]['ctrl']['default_sortby']);
	} elseif(isset($TCA[$table]['ctrl']['sortby'])) {
		$orderBy = $TCA[$table]['ctrl']['sortby'];
	}
	
	return $orderBy;
}

function getmicrotime(){
	list($asec, $sec) = explode(" ",microtime());
	return date('H:m:s',intval($sec)).' '.$asec;
}

function strtotimeOffset($unixtime)
{
   $zone = intval(date('O',$unixtime))/100;
   return $zone*60*60;
}

function getFormatStringFromConf($conf) {
	$dateFormatArray = array();
	$dateFormatArray[$conf['dateConfig.']['dayPosition']] = '%d';
	$dateFormatArray[$conf['dateConfig.']['monthPosition']] = '%m';
	$dateFormatArray[$conf['dateConfig.']['yearPosition']] = '%Y';
	$format = $dateFormatArray[0].$conf['dateConfig.']['splitSymbol'].$dateFormatArray[1].$conf['dateConfig.']['splitSymbol'].$dateFormatArray[2];
	return $format;
}

function getYmdFromDateString($conf, $string) {
	// yyyy.mm.dd or dd.mm.yyyy or mm.dd.yyyy
	$stringArray = explode($conf['dateConfig.']['splitSymbol'],$string);
	$ymdString = $stringArray[$conf['dateConfig.']['yearPosition']].$stringArray[$conf['dateConfig.']['monthPosition']].$stringArray[$conf['dateConfig.']['dayPosition']];
	return $ymdString;
}

// returns true if $str begins with $sub
function beginsWith( $str, $sub ) {
   return ( substr( $str, 0, strlen( $sub ) ) == $sub );
}

// return tru if $str ends with $sub
function endsWith( $str, $sub ) {
   return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

?>
