<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Mario Matzulla (mario(at)matzullas.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * This is a collection of many useful functions
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */

// function returns starttime and endtime and event length for drawing into a grid

function drawEventTimes ($start, $end, $gridLength) {
	$time = array();
	preg_match ('/([0-9]{2})([0-9]{2})/', $start, $time);
	$sta_h = $time[1];
	$sta_min = $time[2];
	$sta_min = sprintf('%02d', floor($sta_min / $gridLength) * $gridLength);
	if ($sta_min == 60) {
		$sta_h = sprintf('%02d', ($sta_h + 1));
		$sta_min = '00';
	}

	preg_match ('/([0-9]{2})([0-9]{2})/', $end, $time);
	$end_h = $time[1];
	$end_min = $time[2];
	$end_min = sprintf('%02d', floor($end_min / $gridLength) * $gridLength);
	if ($end_min == 60) {
		$end_h = sprintf('%02d', ($end_h + 1));
		$end_min = '00';
	}

	if (($sta_h . $sta_min) == ($end_h . $end_min))  {
		$end_min += $gridLength;
		if ($end_min == 60) {
			$end_h = sprintf('%02d', ($end_h + 1));
			$end_min = '00';
		}
	}

	$draw_len = ($end_h * 60 + $end_min) - ($sta_h * 60 + $sta_min);
	return array ('draw_start' => ($sta_h . $sta_min), 'draw_end' => ($end_h . $end_min), 'draw_length' => $draw_len);
}

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


function getHourFromTime($time) {
	$time = str_replace(':', '', $time);
	
    if ($time) {
		$retVal = substr($time, 0, strlen($time)-2);
	}
	return $retVal;
}
function getMinutesFromTime($time) {
	$time = str_replace(':', '', $time);
	if ($time) {
		$retVal = substr($time,-2);
	}
	return $retVal;
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

function getDayFromTimestamp($timestamp=0){
	if($timestamp>86399){
		return gmmktime(0, 0, 0, gmdate('m',$timestamp),gmdate('d',$timestamp),gmdate('Y',$timestamp));
	}
	return 0;
}

function getTimeFromTimestamp($timestamp=0){
	if($timestamp>0){
		return gmmktime(gmdate('H',$timestamp),gmdate('i',$timestamp),0,0,0,1) - gmmktime(0,0,0,0,0,1);
	}
	return 0;
}

function strtotimeOffset($unixtime)
{
   $zone = intval(date('O',$unixtime))/100;
   return $zone*60*60;
}

/**
 * Calculates strtotime() using GMT rather than local time.
 * @param		string		The time to calculate.
 * @param		integer		Timestamp that timeString is relative to.
 * @return		integer		Calculates timestamp.
 */
function gmstrtotime($timeString, $timestamp) {
	$offset = strtotimeOffset($timestamp);
	
	$localTime = strtotime($timeString, $timestamp - $offset);
	$gmtTime = $localTime + $offset;
	
	return $gmtTime;
}

/**
 * Returns a formatted date from a string based on a given format
 *
 * Supported formats
 *
 * %Y - year as a decimal number including the century
 * %m - month as a decimal number (range 1 to 12)
 * %d - day of the month as a decimal number (range 1 to 31)
 *
 * %H - hour as decimal number using a 24-hour clock (range 0 to 23)
 * %M - minute as decimal number
 * %s - second as decimal number
 * %u - microsec as decimal number
 * @param string date  string to convert to date
 * @param string format expected format of the original date
 * @return string rfc3339 w/o timezone YYYYMMDD YYYYMMDDThh:mm:ss YYYYMMDDThh:mm:ss.s
 */
function parseDate( $date, $format ) {
	// Builds up date pattern from the given $format, keeping delimiters in place.
	if( !preg_match_all( "/%([YmdHMsu])([^%])*/", $format, $formatTokens, PREG_SET_ORDER ) ) {
		return false;
	}
	foreach( $formatTokens as $formatToken ) {
		$delimiter = preg_quote( $formatToken[2], "/" );
		if($formatToken[1] == 'Y') {
			$datePattern .= '(.{1,4})'.$delimiter;
		} elseif($formatToken[1] == 'u') {
			$datePattern .= '(.{1,5})'.$delimiter;
		} else {
			$datePattern .= '(.{1,2})'.$delimiter;
		} 
	}
	
	// Splits up the given $date
	if( !preg_match( "/".$datePattern."/", $date, $dateTokens) ) {
		return false;
	}
	$dateSegments = array();
	for($i = 0; $i < count($formatTokens); $i++) {
		$dateSegments[$formatTokens[$i][1]] = $dateTokens[$i+1];
	}
	 
	// Reformats the given $date into rfc3339
	 
	if( $dateSegments["Y"] && $dateSegments["m"] && $dateSegments["d"] ) {
		if( ! checkdate ( $dateSegments["m"], $dateSegments["d"], $dateSegments["Y"] )) 
		{ 
			return false;
		}
		$dateReformated =
			str_pad($dateSegments["Y"], 4, '0', STR_PAD_LEFT)
			.str_pad($dateSegments["m"], 2, '0', STR_PAD_LEFT)
			.str_pad($dateSegments["d"], 2, '0', STR_PAD_LEFT);
	} else {
		return false;
	}
	if( $dateSegments["H"] && $dateSegments["M"] ) {
		$dateReformated .=
			"T".str_pad($dateSegments["H"], 2, '0', STR_PAD_LEFT)
			.':'.str_pad($dateSegments["M"], 2, '0', STR_PAD_LEFT);
	     
		if( $dateSegments["s"] ) {
			$dateReformated .=
				":".str_pad($dateSegments["s"], 2, '0', STR_PAD_LEFT);
			if( $dateSegments["u"] ) {
				$dateReformated .=
					'.'.str_pad($dateSegments["u"], 5, '0', STR_PAD_RIGHT);
			}
		}
	}
	$ts = strtotime($dateReformated);
	$ts += strtotimeOffset($ts);
	return $ts;
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

?>
