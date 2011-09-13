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


// dummy function parseDate
function parseDate($stftime, $strftime_format) {
	return $stftime;
}

// function returns starttime and endtime and event length for drawing into a grid

function drawEventTimes ($start, $end, $gridLength) {
	preg_match ('/([0-9]{2})([0-9]{2})/', $start, $time);
	$sta_h = $time[1];
	$sta_min = $time[2];
	$sta_min = sprintf("%02d", floor($sta_min / $gridLength) * $gridLength);
	if ($sta_min == 60) {
		$sta_h = sprintf("%02d", ($sta_h + 1));
		$sta_min = "00";
	}

	preg_match ('/([0-9]{2})([0-9]{2})/', $end, $time);
	$end_h = $time[1];
	$end_min = $time[2];
	$end_min = sprintf("%02d", floor($end_min / $gridLength) * $gridLength);
	if ($end_min == 60) {
		$end_h = sprintf("%02d", ($end_h + 1));
		$end_min = "00";
	}

	if (($sta_h . $sta_min) == ($end_h . $end_min))  {
		$end_min += $gridLength;
		if ($end_min == 60) {
			$end_h = sprintf("%02d", ($end_h + 1));
			$end_min = "00";
		}
	}

	$draw_len = ($end_h * 60 + $end_min) - ($sta_h * 60 + $sta_min);
	return array ("draw_start" => ($sta_h . $sta_min), "draw_end" => ($end_h . $end_min), "draw_length" => $draw_len);
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
	require_once (PATH_t3lib.'class.t3lib_tcemain.php');
	$tce = t3lib_div::makeInstance('t3lib_TCEmain');
	$tce->admin = 1;
	$tce->clear_cacheCmd('pages');
}


function getHourFromTime($time) {
	$time = str_replace(':', '', $time);
	if ($time) {
		$retVal = substr($time, 0, 2);
	}
	return $retVal;
}
function getMinutesFromTime($time) {
	$time = str_replace(':', '', $time);
	if ($time) {
		$retVal = substr($time, 2, 2);
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

?>