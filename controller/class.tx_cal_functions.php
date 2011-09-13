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

// function to determine maximum necessary columns per day
// actually an algorithm to get the smallest multiple for two numbers
function kgv($a, $b) {
	$x = $a;
	$y = $b;
	while ($x != $y) {
		if ($x < $y) $x += $a;
		else $y += $b;
	}
	return $x;
}

// merge a given range into $ol_ranges. Returns the merged $ol_ranges.
// if count = -2, treat as a "delete" call (for removeOverlap)
// Why -2? That way, there's less fudging of the math in the code.
function merge_range($ol_ranges, $start, $end, $count = 0) {

	foreach ($ol_ranges as $loop_range_key => $loop_range) {
		
		if ($start < $end) {
			// handle ranges between $start and $loop_range['start']
			if ($start < $loop_range['start']) {
				$new_ol_ranges[] = array('count' => $count, 'start' => $start, 'end' => min($loop_range['start'], $end));
				$start = $loop_range['start'];
			}

			// $start is always >= $loop_range['start'] at this point.
			// handles ranges between $loop_range['start'] and $loop_range['end']
			if ($loop_range['start'] < $end && $start < $loop_range['end']) {
				// handles ranges between $loop_range['start'] and $start
				if ($loop_range['start'] < $start) {
					$new_ol_ranges[] = array('count' => $loop_range['count'], 'start' => $loop_range['start'], 'end' => $start);
				}
				// handles ranges between $start and $end (where they're between $loop_range['start'] and $loop_range['end'])
				$new_count = $loop_range['count'] + $count + 1;
				if ($new_count >= 0) {
					$new_ol_ranges[] = array('count' => $new_count, 'start' => $start, 'end' => min($loop_range['end'], $end));
				}
				// handles ranges between $end and $loop_range['end']
				if ($loop_range['end'] > $end) {
					$new_ol_ranges[] = array('count' => $loop_range['count'], 'start' => $end, 'end' => $loop_range['end']);
				}
				$start = $loop_range['end'];
			} else {
				$new_ol_ranges[] = $loop_range;
			}
		} else {
			$new_ol_ranges[] = $loop_range;
		}
	}

	// Catches anything left over.
	if ($start < $end) {
		$new_ol_ranges[] = array('count' => $count, 'start' => $start, 'end' => $end);
	}

	return $new_ol_ranges;
}

// Finds the highest value of 'count' in $ol_ranges
function find_max_overlap($ol_ranges) {

	$count = 0;
	foreach ($ol_ranges as $loop_range) {
		if ($count < $loop_range['count'])
			$count = $loop_range['count'];
	}

	return $count;
}

// Merges overlapping blocks
function flatten_ol_blocks($event_date, $ol_blocks, $new_block_key) {

	global $master_array;

	// Loop block = each other block in the array, the ones we're merging into new block.
	// New block = the changed block that caused the flatten_ol_blocks call. Everything gets merged into this.
	$new_block = $ol_blocks[$new_block_key];
	reset($ol_blocks);
	while ($loop_block_array = each($ol_blocks)) {
		$loop_block_key = $loop_block_array['key'];
		$loop_block = $loop_block_array['value'];
		// only compare with other blocks
		if ($loop_block_key != $new_block_key) {
			// check if blocks overlap
			if (($loop_block['blockStart'] < $new_block['blockEnd']) && ($loop_block['blockEnd'] > $new_block['blockStart'])) {
				// define start and end of merged overlap block
				if ($new_block['blockStart'] > $loop_block['blockStart']) $ol_blocks[$new_block_key]['blockStart'] = $loop_block['blockStart'];
				if ($new_block['blockEnd'] < $loop_block['blockEnd']) $ol_blocks[$new_block_key]['blockEnd'] = $loop_block['blockEnd'];
				$ol_blocks[$new_block_key]['events'] = array_merge($new_block['events'], $loop_block['events']);
				foreach ($loop_block['overlapRanges'] as $ol_range) {
					$new_block['overlapRanges'] = merge_range($new_block['overlapRanges'], $ol_range['start'], $ol_range['end'], $ol_range['count']);
				}
				$ol_blocks[$new_block_key]['overlapRanges'] = $new_block['overlapRanges'];
				$ol_blocks[$new_block_key]['maxOverlaps'] = find_max_overlap($new_block['overlapRanges']);
				foreach ($ol_blocks[$new_block_key]['events'] as $event) {
					$master_array[$event_date][$event['time']][$event['key']]['event_overlap'] = $ol_blocks[$new_block_key]['maxOverlaps'];
				}
				unset($ol_blocks[$loop_block_key]);
				reset($ol_blocks);
			}
		} 
	}

	return $ol_blocks;
}

// Builds $overlap_array structure, and updates event_overlap in $master_array for the given events.
function checkOverlap($event_date, $event_time, $uid, $master_array, &$overlap_array, $gridLength) {
	return;
	$event = $master_array[$event_date][$event_time][$uid];
	// Copy out the array - we replace this at the end.
	$ol_day_array = $overlap_array[$event_date];

	$drawTimes = drawEventTimes($event->event_start, $event->event_end, $gridLength);

	// For a given date,
	// 	- check to see if the event's already in a block, and if so, add it.
	//		- make sure the new block doesn't overlap another block, and if so, merge the blocks.
	// - check that there aren't any events we already passed that we should handle.
	//		- "flatten" the structure again, merging the blocks.

	// $overlap_array structure:
	//	array of ($event_dates)
	//		array of unique overlap blocks (no index) -

	// $overlap_block structure
	// 'blockStart'    - $start_time of block - earliest $start_time of the events in the block. 
	//					 Shouldn't be any overlap w/ a different overlap block in that day (as if they overlap, they get merged).
	// 'blockEnd'      - $end_time of block - latest $end_time of the events in the block.
	// 'maxOverlaps'   - max number of overlaps for the whole block (highest 'count' in overlapRanges)
	// 'events'        - array of event "pointers" (no index) - each event in the block.
	//		'time' - $start_time of event in the block
	//		'key'  - $uid of event
	// 'overlapRanges' - array of time ranges + overlap counts (no index) - the specific overlap info.
	//					 Shouldn't be any overlap w/ the overlap ranges in a given overlap_block - if there is overlap, the block should be split.
	//		'count' - number of overlaps that time range (can be zero if that range has no overlaps).
	//		'start' - start_time for the overlap block.
	//		'end'	- end_time for the overlap block.

	$ol_day_array = $overlap_array[$event_date];
	// Track if $event has been merged in, so we don't re-add the details to 'event' or 'overlapRanges' multiple times.
	$already_merged_once = false;
	// First, check the existing overlap blocks, see if the event overlaps with any.

	if (isset($ol_day_array)) {
		foreach ($ol_day_array as $loop_block_key => $loop_ol_block) {
			// Should $event be in this $ol_block? If so, add it.
			if ($loop_ol_block['blockStart'] < $drawTimes['draw_end'] && $loop_ol_block['blockEnd'] > $drawTimes['draw_start']) {
				// ... unless it's already in the $ol_block
				if (!in_array(array('time' => $drawTimes['draw_start'], 'key' => $uid), $loop_ol_block['events'])) {
					$loop_ol_block['events'][] = array('time' => $drawTimes['draw_start'], 'key' => $uid);
					if ($loop_ol_block['blockStart'] > $drawTimes['draw_start']) $loop_ol_block['blockStart'] = $drawTimes['draw_start'];
					if ($loop_ol_block['blockEnd'] < $drawTimes['draw_end']) $loop_ol_block['blockEnd'] = $drawTimes['draw_end'];

					// Merge in the new overlap range
					$loop_ol_block['overlapRanges'] = merge_range($loop_ol_block['overlapRanges'], $drawTimes['draw_start'], $drawTimes['draw_end']);
					$loop_ol_block['maxOverlaps'] = find_max_overlap($loop_ol_block['overlapRanges']);
					foreach ($loop_ol_block['events'] as $max_overlap_event) {
						$master_array[$event_date][$max_overlap_event['time']][$max_overlap_event['key']]['event_overlap'] = $loop_ol_block['maxOverlaps'];
					}
					$ol_day_array[$loop_block_key] = $loop_ol_block;
					$ol_day_array = flatten_ol_blocks($event_date, $ol_day_array, $loop_block_key);
					$already_merged_once = true;
					break;
				// Handle repeat calls to checkOverlap - semi-bogus since the event shouldn't be created more than once, but this makes sure we don't get an invalid event_overlap.
				} else {
					$master_array[$event_date][$event_time][$uid]['event_overlap'] = $loop_ol_block['maxOverlaps'];
				}
			}
		}
	}

	// Then, check all the events, make sure there isn't a new overlap that we need to create.
	foreach ($master_array[$event_date] as $time_key => $time) {
		// Skip all-day events for overlap purposes.
		if ($time_key != '-1') {
			foreach ($time as $loop_event_key => $loop_event) {
				// Make sure we haven't already dealt with the event, and we're not checking against ourself.
				if ($loop_event->event_overlap == 0 && $loop_event_key != $uid) {
					$loopDrawTimes = drawEventTimes($loop_event->event_start, $loop_event->event_end);
 					if ($loopDrawTimes['draw_start'] < $drawTimes['draw_end'] && $loopDrawTimes['draw_end'] > $drawTimes['draw_start']) {
 						if ($loopDrawTimes['draw_start'] < $drawTimes['draw_start']) {
 							$block_start = $loopDrawTimes['draw_start'];
						} else {
							$block_start = $drawTimes['draw_start'];
						}
						if ($loopDrawTimes['draw_end'] > $drawTimes['draw_end']) {
							$block_end = $loopDrawTimes['draw_end'];
						} else {
							$block_end = $drawTimes['draw_end'];
						}
						$events = array(array('time' => $loopDrawTimes['draw_start'], 'key' => $loop_event_key));
						$overlap_ranges = array(array('count' => 0, 'start' => $loopDrawTimes['draw_start'], 'end' => $loopDrawTimes['draw_end']));
						// Only add $event if we haven't already put it in a block
						if (!$already_merged_once) {
							$events[] = array('time' => $drawTimes['draw_start'], 'key' => $uid); 
							$overlap_ranges = merge_range($overlap_ranges, $drawTimes['draw_start'], $drawTimes['draw_end']);
							$already_merged_once = true;
						}
						$ol_day_array[] = array('blockStart' => $block_start, 'blockEnd' => $block_end, 'maxOverlaps' => 1, 'events' => $events, 'overlapRanges' => $overlap_ranges);

						foreach ($events as $max_overlap_event) {
							$master_array[$event_date][$max_overlap_event['time']][$max_overlap_event['key']]['event_overlap'] = 1;
						}
						// Make sure we pass in the key of the newly added item above.
						end($ol_day_array);
						$last_day_key = key($ol_day_array);
						$ol_day_array = flatten_ol_blocks($event_date, $ol_day_array, $last_day_key);
					}
				}
			}
		}
	}

	$overlap_array[$event_date] = $ol_day_array;

}

// Remove an event from the overlap data.
// This could be completely bogus, since overlap array is empty when this gets called in my tests, but I'm leaving it in anyways.
function removeOverlap($ol_start_date, $ol_start_time, $ol_key) {
	global $master_array, $overlap_array;
	if (isset($overlap_array[$ol_start_date])) {
		if (sizeof($overlap_array[$ol_start_date]) > 0) {
			$ol_end_time = $master_array[$ol_start_date][$ol_start_time][$ol_key]['event_end'];
			foreach ($overlap_array[$ol_start_date] as $block_key => $block) {
				if (in_array(array('time' => $ol_start_time, 'key' => $ol_key), $block['events'])) {
					// Check if this is a 2-event block (i.e., there's no block left when we remove $ol_key
					// and if so, just unset it and move on.
					if (count($block['events']) == 2) {
						foreach ($block['events'] as $event) {
							$master_array[$ol_start_date][$event['time']][$event['key']]['event_overlap'] = 0;
						}
						unset($overlap_array[$ol_start_date][$block_key]);
					} else {
						// remove $ol_key from 'events'
						$event_key = array_search(array('time' => $ol_start_time, 'key' => $ol_key), $block['events']);
						unset($overlap_array[$ol_start_date][$block_key]['events'][$event_key]);

						// These may be bogus, since we're not using drawEventTimes.
						// "clean up" 'overlapRanges' and calc the new maxOverlaps.
						// use the special "-2" count to tell merge_range we're deleting.
						$overlap_array[$ol_start_date][$block_key]['overlapRanges'] = merge_range($block['overlapRanges'], $ol_start_time, $ol_end_time, -2);
						$overlap_array[$ol_start_date][$block_key]['maxOverlaps'] = find_max_overlap($block['overlapRanges']);

						// recreate blockStart and blockEnd from the other events, and fix maxOverlap while we're at it.
						$blockStart = $ol_end_time;
						$blockEnd = $ol_start_time;
						foreach ($overlap_array[$ol_start_date][$block_key]['events'] as $event) {
							$blockStart = min($blockStart, $event['time']);
							$blockEnd = max($blockEnd, $master_array[$ol_start_date][$event['time']][$event['key']]['event_end']);
							$master_array[$ol_start_date][$event['time']][$event['key']]['event_overlap'] = $overlap_array[$ol_start_date][$block_key]['maxOverlaps'];
						}
						$overlap_array[$ol_start_date][$block_key]['blockStart'] = $blockStart;
						$overlap_array[$ol_start_date][$block_key]['blockEnd'] = $blockEnd;
					}
				}
			}
		}
	}
}

function notifyOfChanges($oldEventDataArray, $newEventDataArray, $conf=array()){
	
	$title = $oldEventDataArray['title'];
	$uid = $oldEventDataArray['uid'];
	$title_text = 'The event "'.$title.'" has changed:\n';
	$text = '';
	foreach($newEventDataArray as $key => $item){
		switch($key){
			case('organizer'):
				$text .= "Organizer: ".$oldEventDataArray['organizer']." => ".$item."\n";
			break;
			case('location'):
				$text .= "Location: ".$oldEventDataArray['name']." => ".$item."\n";
			break;
			case('description'):
				$text .= "Description: ".$oldEventDataArray['description']." => ".$item."\n";
			break;
			case('title'):
				$text .= "Title: ".$oldEventDataArray['title']." => ".$item."\n";
			break;
			case('start_date'):
				$text .= "Start date: ".$oldEventDataArray['start_date']." => ".$item."\n";
			break;
			case('start_hour'):
				$text .= "Start hour: ".$oldEventDataArray['start_hour']." => ".$item."\n";
			break;
			case('end_date'):
				$text .= "End date: ".$oldEventDataArray['end_date']." => ".$item."\n";
			break;
			case('end_hour'):
				$text .= "End hour: ".$oldEventDataArray['end_hour']." => ".$item."\n";
			break;
		}
	}
	if($text!=''){
		$select = "fe_users.*";
		$local_table = "tx_cal_event";
		$mm_table = "tx_cal_fe_user_event_monitor_mm";
		$foreign_table = "fe_users";
		$where = " AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0 AND tx_cal_event.uid = ".$uid;
		if(!empty($conf)){
			$sender = $conf['emailaddress'];
			$reply = $conf['emailreplyaddress'];
		}else{
			$pageTSConf = t3lib_befunc::getPagesTSconfig($GLOBALS['HTTP_POST_VARS']['popViewId']);
			$sender = $pageTSConf['options.']['tx_cal_controller.']['emailaddress'];
			$reply = $pageTSConf['options.']['tx_cal_controller.']['emailreplyaddress'];
		}
		$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$local_table,$mm_table,$foreign_table,$where);		
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($row1['email']!=""){
				mail($row1['email'], $title_text, $text, 'From: '.$sender . "\r\n" .
				   				'Reply-To: '.$reply . "\r\n" .'X-Mailer: PHP/');				
			}
		}
	}
}

function notify(&$newEventDataArray, $conf=array()){
	$title = $newEventDataArray['title'];
	$uid = $newEventDataArray['uid'];
	$title_text = 'The event "'.$title.'" has changed:\n';
	$text = '';
	foreach($newEventDataArray as $key => $item){
		switch($key){
			case('organizer'):
				$text .= "Organizer: ".$newEventDataArray['organizer']."\n";
			break;
			case('location'):
				$text .= "Location: ".$newEventDataArray['name']."\n";
			break;
			case('description'):
				$text .= "Description: ".$newEventDataArray['description']."\n";
			break;
			case('title'):
				$text .= "Title: ".$newEventDataArray['title']."\n";
			break;
			case('start_date'):
				$text .= "Start date: ".$newEventDataArray['start_date']."\n";
			break;
			case('start_hour'):
				$text .= "Start hour: ".$newEventDataArray['start_hour']."\n";
			break;
			case('end_date'):
				$text .= "End date: ".$newEventDataArray['end_date']."\n";
			break;
			case('end_hour'):
				$text .= "End hour: ".$newEventDataArray['end_hour']."\n";
			break;
		}
	}

	if(!empty($conf)){
		$sender = $conf['emailaddress'];
		$reply = $conf['emailreplyaddress'];
	}else{
		$pageTSConf = t3lib_befunc::getPagesTSconfig($GLOBALS['HTTP_POST_VARS']['popViewId']);
		$sender = $pageTSConf['options.']['tx_cal_controller.']['emailaddress'];
		$reply = $pageTSConf['options.']['tx_cal_controller.']['emailreplyaddress'];
	}
	$select = "fe_users.*";
	$local_table = "tx_cal_event";
	$mm_table = "tx_cal_fe_user_event_monitor_mm";
	$foreign_table = "fe_users";
	$where = " AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0 AND tx_cal_event.uid = ".$uid;
	if(!empty($conf)){
		$sender = $conf['emailaddress'];
		$reply = $conf['emailreplyaddress'];
	}else{
		$pageTSConf = t3lib_befunc::getPagesTSconfig($GLOBALS['HTTP_POST_VARS']['popViewId']);
		$sender = $pageTSConf['options.']['tx_cal_controller.']['emailaddress'];
		$reply = $pageTSConf['options.']['tx_cal_controller.']['emailreplyaddress'];
	}
	$result = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$local_table,$mm_table,$foreign_table,$where);		
	while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
		if($row1['email']!=""){
			mail($row1['email'], $title_text, $text, 'From: '.$sender . "\r\n" .
			   				'Reply-To: '.$reply . "\r\n" .'X-Mailer: PHP/');				
		}
	}
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

?>