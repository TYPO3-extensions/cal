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


require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_subscription_manager_view extends tx_cal_base_view {
	
	function tx_cal_subscription_manager_view(){
		$this->tx_cal_base_view();
	}
	
	/**
	 * Main function to draw the subscription manager view.
	 * @return		string		HTML output of the subscription manager.
	 */
	function drawSubscriptionManager(){
		
		$rems = array();
		$sims = array();
		$wrapped = array();
		
		$sims['###HEADING###'] = $this->controller->pi_getLL('l_manage_subscription');
		$sims['###STATUS###'] = '';
		$rems['###USER_LOGIN###']='';
		$rems['###SUBSCRIPTION_CONTAINER###']='';
		
		/* Get the subscription manager template */
		$page = $this->cObj->fileResource($this->conf['view.']['event.']['subscriptionManagerTemplate']);
		if ($page == '') {
			return '<h3>calendar: no event template file found:</h3>' . $this->conf['view.']['event.']['subscriptionManagerTemplate'];
		}
		
		$eventUID = strip_tags($this->controller->piVars['uid']);
		$email = strip_tags($this->controller->piVars['email']);
		$subscriptionHash = strip_tags($this->controller->piVars['sid']);
		/* If we have an event, email, and subscription id, try to subscribe or unsubscribe */
		if($eventUID > 0 && $email && $subscriptionHash){
			$event = $this->modelObj->findEvent($eventUID, 'tx_cal_phpicalendar', $this->conf['pidList']);
			
			unset($this->controller->piVars['monitor']);
			unset($this->controller->piVars['email']);
			unset($this->controller->piVars['sid']);
			$local_rems = array();
			$local_sims = array();
			$local_wrapped = array();
			
			$status = $this->cObj->getSubpart($page, '###STATUS###');
			switch($this->conf['monitor']){
				case 'stop': /* Unsubscribe a user */		
					if($this->unsubscribe($email, $event, $subscriptionHash)) {
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_monitor_event_unsubscribe_successful'), $event->getTitle());
					} else {
						/* No user to unsubscribe.  Output a message here? */
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_monitor_event_unsubscribe_error'), $event->getTitle());
					}
					
					break;
				case 'start': /* Subscribe a user */
					if($this->subscribe($email, $event, $subscriptionHash)) {
						$status = $this->cObj->getSubpart($page, '###STATUS_START###');
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_monitor_event_subscribe_successful'), $event->getTitle());
					} else {
						/* No user to subscribe.  Output a message here? */
						$sims['###STATUS###'] = sprintf($this->controller->pi_getLL('l_monitor_event_subscribe_error'), $event->getTitle());
					}
					break;
			}
			
			//$event->getMarker($status, $local_rems, $local_sims, $local_wrapped);
			//$rems['###STATUS###'] = tx_cal_functions::substituteMarkerArrayNotCached($status, $local_sims, $local_rems, $local_wrapped);
		} else {
			/* If there's a logged in user, show the subscription container */
			if($this->conf['subscribeFeUser'] && $this->rightsObj->isLoggedIn()){
				$subscriptionContainer = $this->cObj->getSubpart($page, '###SUBSCRIPTION_CONTAINER###');
				$return = '';

				$select = '*';
				$table = 'tx_cal_fe_user_event_monitor_mm, tx_cal_event';
				$where = 'tx_cal_event.uid = tx_cal_fe_user_event_monitor_mm.uid_local AND tx_cal_fe_user_event_monitor_mm.uid_foreign IN ('.$this->rightsObj->getUserId().')';
				$where .= ' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0';
				$where .= ' AND tx_cal_event.pid IN ('.$this->conf['pidList'].')';

				/* Save to temporary variables */
				$remUid = $this->conf['uid'];
				$remType = $this->conf['type'];
				
				$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table,$where);
				$eventList = array();
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
					$local_rems = array();
					$local_sims = array();
					$subscriptionContainer = $this->cObj->getSubpart($page, '###SUBSCRIPTION_CONTAINER###');
					$event = $this->modelObj->createEvent('tx_cal_phpicalendar');
					$event->setUid($row['uid']);
					$event->updateWithPiVars($row);
					$this->conf['uid'] = $event->getUid();
					$this->conf['type'] = $event->getType();
					$view = 'list';
					$event->getMarker($subscriptionContainer, $local_sims, $local_rems, $view);
					$eventList[] = '<li>'.tx_cal_functions::substituteMarkerArrayNotCached($subscriptionContainer, $local_sims, $local_rems, array()).'</li>';
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);
				
				/* Restore from temporary variables */
				$this->conf['uid'] = $remUid;
				$this->conf['type'] = $remType;
				
				if(empty($eventList)) {
					$return = 'No events found.';
				} else {
					$return = '<ul>'.implode(chr(10), $eventList).'</ul>';
				}
				
				$rems['###SUBSCRIPTION_CONTAINER###'] = $return;
			} else { 	/* Otherwise, request login or captcha validation */
				$sims['###STATUS###'] = 'You must be logged in to manage your event notifications.';
			}
		}
		$page = tx_cal_functions::substituteMarkerArrayNotCached($page, $sims, $rems, $wrapped);
		$rems = array();
		return $this->finish($page, $rems);
	}
	
	/**
	 * Attempts to unsubscribe an email address from a particular event if the
	 * subscription hash matches.  Check both the fe_users table and the
	 * tx_cal_unknown_users table.
	 * @param		string		Email address to unsubscribe.
	 * @param		object		Event that email should be unsubscribed from.
	 * @param		string		Unique hash of email and event.
	 * @return		boolean		True/false whether unsubscribe was successful.
	 * @todo 		Should we always try to unsubscribe both fe users and unknown
	 *				users or just try one and stop if successful?
	 */
	function unsubscribe($email, $event, $subscriptionHash) {
		$eventUID = $event->getUID();
		return $this->unsubscribeByTable('fe_users', $email, $eventUID, $subscriptionHash) || 
			   $this->unsubscribeByTable('tx_cal_unknown_users', $email, $eventUID, $subscriptionHash);
	}
	
	/**
	 * Attempts to unsubscribe an email address within a particular table from 
	 * a particular event if the subscription hash matches.
	 * @param		string		Table to look up email address in.
	 * @param		string		Email address to unsubscribe.
	 * @param		object		Event that email should be unsubscribed from.
	 * @param		string		Unique hash of email and event.
	 * @return		boolean		True/false whether unsubscribe was successful.
	 */
	function unsubscribeByTable($table, $email, $eventUID, $subscriptionHash) {
		$sqlSelect = 'tx_cal_event.uid, '.$table.'.crdate, '.$table.'.email';
		$sqlTable = 'tx_cal_fe_user_event_monitor_mm, tx_cal_event, '.$table;
		$sqlWhere = 'tx_cal_event.uid = '.$eventUID.' AND tx_cal_event.pid IN ('.$this->conf['pidList'].')'.$this->cObj->enableFields('tx_cal_event');
		$sqlWhere .= ' AND (tx_cal_event.uid = tx_cal_fe_user_event_monitor_mm.uid_local AND tx_cal_fe_user_event_monitor_mm.uid_foreign = '.$table.'.uid AND '.$table.'.email = "'.$email.'")';
		
		$unsubscribeUids = array();
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($sqlSelect, $sqlTable,$sqlWhere);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$md5 = md5($row['uid'].$row['email'].$row['crdate']);
			if($md5==$subscriptionHash){
				$unsubscribeUids[] = $row['uid'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		if(!empty($unsubscribeUids)){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_cal_fe_user_event_monitor_mm','tx_cal_fe_user_event_monitor_mm.uid_local IN ('.implode(',',$unsubscribeUids).') AND tx_cal_fe_user_event_monitor_mm.tablenames = "'.$table.'"');
			$returnValue = true;
		} else {
			$returnValue = false;
		}
		
		return $returnValue;
	
	}
	
	/**
	 * Attempts to subscribe an email address to a particular event if the
	 * subscription hash matches.
	 * @param		string		Email address to subscribe.
	 * @param		object		Event that email should be subscribed to.
	 * @param		string		Unique Hash of email and event.
	 * @return		boolean		True/false whether subscribe was successful.
	 * @todo 		Should we always try to subscribe as a frontend user first?
	 */
	function subscribe($email, $event, $subscriptionHash) {
		$md5 = md5($event->getUid().$email.$event->getCreationDate());
		$eventUID = $event->getUID();
		
		/* If the subscription hash matches, subscribe */
		if($md5 == $subscriptionHash) {
			$user_uid = $this->getFrontendUserUid($email);
			$user_table = 'fe_users';
			/* If we didn't find a matching frontend user, try unknown users */
			if(!$user_uid) {
				$user_uid = $this->getUnknownUserUid($email);
				$user_table = 'tx_cal_unknown_users';
			}

			/* Insert the user ID into the monitor table */
			$this->insertMMRow('tx_cal_fe_user_event_monitor_mm', $eventUID, $user_uid, $user_table, 1);
			$returnValue = true;
		} else {
			$returnValue = false;
		}

		return $returnValue;
	}
	
	/**
	 * Inserts an intermediate row for a many-to-many table.
	 * @param		string		Name of the MM table.
	 * @param		integer		Value for the uid_local field.
	 * @param		integer		Value for the uid_foreign field.
	 * @param		string		Name of the table for uid_foreign.
	 * @param		integer		Sort order.
	 * @return		integer		True/false whether a new row was inserted.
	 */
	function insertMMRow($mmTable, $uid_local, $uid_foreign, $table, $sorting) {
		$already_exists = false;
		
		/* Check if row already exists */
		$where = 'uid_local ='.$uid_local.' AND uid_foreign = '.$uid_foreign.' AND tablenames="'.$table.'"';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local', $mmTable, $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$already_exists = true;
			break;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		
		/* If the row does not exist, insert it */
		if (!$already_exists) {
			$fields_values = array (
				'uid_local' => $uid_local,
				'uid_foreign' => $uid_foreign,
				'tablenames' => $table,
				'sorting' => $sorting,
			);
			$GLOBALS['TYPO3_DB']->exec_INSERTquery($mmTable, $fields_values);
			$insertedRow = true;
		} else {
			/* Row exists so do nothing */
			$insertedRow = false;
		}
		
		return $insertedRow;
	}
	
	function getUnknownUserUid($email) {
		$already_exists = false;
		$user_uid = 0;
		$crdate = 0;
			
		$table = 'tx_cal_unknown_users';
		$select = 'uid,crdate';
		$where = 'email = "'.$email.'"';
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$already_exists = true;
			$user_uid = $row['uid'];
			break;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		
		if (!$already_exists) {
			$crdate = time();
			$fields_values = array (
				'tstamp' => time(),
				'crdate' => $crdate, 
				'email' => $email
			);
			$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $fields_values);
			$user_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		}
		
		return $user_uid;
	}
	
	function getFrontendUserUid($email) {
		$user_uid = false;
		
		$table = 'fe_users';
		$select = 'uid';
		$where = 'email = "'.$email.'"';
		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $table, $where);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$user_uid = $row['uid'];
			break;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($result);
		
		return $user_uid;		
	}
 	
	
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_subscription_manager_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_subscription_manager_view.php']);
}
?>
