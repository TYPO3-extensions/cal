<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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
 * 
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_notification_view extends t3lib_svbase {
	
	function tx_cal_notification_view(){
	}
	
	function notifyOfChanges($oldEventDataArray, $newEventDataArray, $conf){
	
		$marker = array();
	   	$marker['###TITLE###'] = $oldEventDataArray['title'];
	   	if($oldEventDataArray['start_date']>0){
			$marker['###START_DATE###'] = strftime($conf['dateFormat'],$oldEventDataArray['start_date']);
		}else{
	   		$marker['###START_DATE###'] = '';
	   	}
	   	if($oldEventDataArray['start_time']>0){
			$marker['###START_TIME###'] = strftime($conf['timeFormat'],$oldEventDataArray['start_time']);
		}else{
	   		$marker['###START_TIME###'] = '';
	   	}
	   	if($oldEventDataArray['end_date']>0){
			$marker['###END_DATE###'] = strftime($conf['dateFormat'],$oldEventDataArray['end_date']);
		}else{
	   		$marker['###END_DATE###'] = '';
	   	}
	   	if($oldEventDataArray['end_time']>0){
			$marker['###END_TIME###'] = strftime($conf['timeFormat'],$oldEventDataArray['end_time']);
		}else{
	   		$marker['###END_TIME###'] = '';
	   	}
	   	$marker['###DESCRIPTION###'] = $oldEventDataArray['description'];
	   	$marker['###LOCATION###'] = $oldEventDataArray['location'];
	   	$marker['###ORGANIZER###'] = $oldEventDataArray['organizer'];
	   	$marker['###HIDDEN###'] = '';
	   	$marker['###DELETE###'] = '';
	   	
	   	$llFile = t3lib_extMgm::extPath('cal').'controller/locallang.xml';
		$LOCAL_LANG = array_pop(t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang?$GLOBALS['LANG']->lang:'de'));
		$somethingHasChanged = false;
		foreach($newEventDataArray as $key => $item){
			switch($key){
				case('hidden'):
					$marker['###HIDDEN###'] = $newEventDataArray['hidden']==1?$LOCAL_LANG['l_event_has_been_hidden']:$LOCAL_LANG['l_event_has_been_unhidden'];
					$somethingHasChanged = true;
				break;
				case('deleted'):
					$marker['###DELETE###'] = $newEventDataArray['deleted']==1?$LOCAL_LANG['l_event_has_been_delete']:$LOCAL_LANG['l_event_has_been_restored'];
					$somethingHasChanged = true;
				break;
				case('calendar_id'):
	
				break;
				case('organizer'):
					$marker['###ORGANIZER###'] .= $conf['oldNewSeparator'].' '.$newEventDataArray['organizer'];
					$somethingHasChanged = true;
				break;
				case('location'):
					$marker['###LOCATION###'] .= $conf['oldNewSeparator'].' '.$newEventDataArray['location'];
					$somethingHasChanged = true;
				break;
				case('description'):
					$marker['###DESCRIPTION###'] .= $conf['oldNewSeparator'].' '.$newEventDataArray['description'];
					$somethingHasChanged = true;
				break;
				case('title'):
					$marker['###TITLE###'] .= $conf['oldNewSeparator'].' '.$newEventDataArray['title'];
					$somethingHasChanged = true;
				break;
				case('start_date'):
					if($newEventDataArray['start_date']>0){
						$marker['###START_DATE###'] .= $conf['oldNewSeparator'].' '.strftime($conf['dateFormat'],$newEventDataArray['start_date']);
						$somethingHasChanged = true;
					}
				break;
				case('start_time'):
					if($newEventDataArray['start_time']>0){
						$marker['###START_TIME###'] .= $conf['oldNewSeparator'].' '.strftime($conf['timeFormat'],$newEventDataArray['start_time']);
						$somethingHasChanged = true;
					}
				break;
				case('end_date'):
					if($newEventDataArray['end_date']>0){
						$marker['###END_DATE###'] .= $conf['oldNewSeparator'].' '.strftime($conf['dateFormat'],$newEventDataArray['end_date']);
						$somethingHasChanged = true;
					}
				break;
				case('end_time'):
					if($newEventDataArray['end_time']>0){
						$marker['###END_TIME###'] .= $conf['oldNewSeparator'].' '.strftime($conf['timeFormat'],$newEventDataArray['end_time']);
						$somethingHasChanged = true;
					}
				break;
			}
		}
		if($somethingHasChanged){
			require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
			$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
			$mailer->start();
			$mailer->from_email = $conf['emailAddress'];
			$mailer->from_name = $conf['fromName'];
			$mailer->replyto_email = $conf['emailReplyAddress'];
			$mailer->replyto_name = $conf['replyToName'];
			$mailer->organisation = $conf['organisation'];
			
			$select = 'fe_users.*';
			$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
			$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.uid = '.$oldEventDataArray['uid'];
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
			while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
					$template = $conf[$row1['uid'].'.']['onChangeTemplate'];
					if(!$template){
						$template = $conf['all.']['onChangeTemplate'];
					}
					$titleText = $conf[$row1['uid'].'.']['onChangeEmailTitle'];
					if(!$titleText){
						$titleText = $conf['all.']['onChangeEmailTitle'];
					}
					if($newEventDataArray['deleted']){
						$template = $conf['all.']['onDeleteTemplate'];
						$titleText = $conf['all.']['onDeleteEmailTitle'];
					}
					$absFile = t3lib_div::getFileAbsFileName($template);
		   			$text = t3lib_div::getURL($absFile);
					$text = replace_tags($marker, $text);
					$titleText = replace_tags($marker, $titleText);
					$mailer->subject = $titleText;
					$mailer->setPlain($text);
					$mailer->send($row1['email']);
				}
			}
		}
	}
	
	function notify(&$newEventDataArray, $conf){
				
	   	$marker = array();
	   	$marker['###TITLE###'] = '';
	   	$marker['###START_DATE###'] = '';
	   	$marker['###START_TIME###'] = '';
	   	$marker['###END_DATE###'] = '';
	   	$marker['###END_TIME###'] = '';
	   	$marker['###DESCRIPTION###'] = '';
	   	$marker['###LOCATION###'] = '';
	   	$marker['###ORGANIZER###'] = '';
	
		foreach($newEventDataArray as $key => $item){
			switch($key){
				case('calendar_id'):
	
				break;
				case('organizer'):
					$marker['###ORGANIZER###'] = $newEventDataArray['organizer'];
				break;
				case('location'):
					$marker['###LOCATION###'] = $newEventDataArray['location'];
				break;
				case('description'):
					$marker['###DESCRIPTION###'] = $newEventDataArray['description'];
				break;
				case('title'):
					$marker['###TITLE###'] = $newEventDataArray['title'];
				break;
				case('start_date'):
					if($newEventDataArray['start_date']>0){
						$marker['###START_DATE###'] = strftime($conf['dateFormat'],$newEventDataArray['start_date']);
					}
				break;
				case('start_time'):
					if($newEventDataArray['start_time']>0){
						$marker['###START_TIME###'] = strftime($conf['timeFormat'],$newEventDataArray['start_time']);
					}
				break;
				case('end_date'):
					if($newEventDataArray['end_date']>0){
						$marker['###END_DATE###'] = strftime($conf['dateFormat'],$newEventDataArray['end_date']);
					}
				break;
				case('end_time'):
					if($newEventDataArray['end_time']>0){
						$marker['###END_TIME###'] = strftime($conf['timeFormat'],$newEventDataArray['end_time']);
					}
				break;
			}
		}
		
		require_once (PATH_t3lib.'class.t3lib_htmlmail.php');
		$mailer =t3lib_div::makeInstance('t3lib_htmlmail');
		$mailer->start();
		
		$mailer->from_email = $conf['emailAddress'];
		$mailer->from_name = $conf['fromName'];
		$mailer->replyto_email = $conf['emailReplyAddress'];
		$mailer->replyto_name = $conf['replyToName'];
		$mailer->organisation = $conf['organisation'];
		
		$select = 'fe_users.*';
		$table = 'fe_users, tx_cal_fe_user_event_monitor_mm, tx_cal_event';
		$where = 'fe_users.uid = tx_cal_fe_user_event_monitor_mm.uid_foreign AND  tx_cal_fe_user_event_monitor_mm.uid_local = tx_cal_event.uid AND tx_cal_event.deleted = 0 AND tx_cal_event.uid = '.$newEventDataArray['uid'];
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$table,$where);
		while ($row1 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			if($row1['email']!='' && t3lib_div::validEmail($row1['email'])){
				$template = $conf[$row1['uid'].'.']['onCreateTemplate'];
				if(!$template){
					$template = $conf['all.']['onCreateTemplate'];
				}
				$titleText = $conf[$row1['uid'].'.']['onCreateEmailTitle'];
				if(!$titleText){
					$titleText = $conf['all.']['onCreateEmailTitle'];
				}
				$absFile = t3lib_div::getFileAbsFileName($template);
	   			$text = t3lib_div::getURL($absFile);
				$text = replace_tags($marker, $text);
				$titleText = replace_tags($marker, $titleText);
				$mailer->subject = $titleText;
				$mailer->setPlain($text);
				$mailer->send($row1['email']);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_notification_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_notification_view.php']);
}
?>
