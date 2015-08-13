<?php
namespace TYPO3\CMS\Cal\View;
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

/**
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class SubscriptionManagerView extends \TYPO3\CMS\Cal\View\BaseView {
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Main function to draw the subscription manager view.
	 * 
	 * @return string output of the subscription manager.
	 */
	function drawSubscriptionManager() {
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		
		$sims ['###HEADING###'] = $this->controller->pi_getLL ('l_manage_subscription');
		$sims ['###STATUS###'] = '';
		$rems ['###USER_LOGIN###'] = '';
		$rems ['###SUBSCRIPTION_CONTAINER###'] = '';
		
		/* Get the subscription manager template */
		$page = $this->cObj->fileResource ($this->conf ['view.'] ['event.'] ['subscriptionManagerTemplate']);
		if ($page == '') {
			return '<h3>calendar: no event template file found:</h3>' . $this->conf ['view.'] ['event.'] ['subscriptionManagerTemplate'];
		}
		
		$eventUID = strip_tags ($this->controller->piVars ['uid']);
		$email = strip_tags ($this->controller->piVars ['email']);
		$subscriptionHash = strip_tags ($this->controller->piVars ['sid']);
		/* If we have an event, email, and subscription id, try to subscribe or unsubscribe */
		if ($eventUID > 0 && $email && $subscriptionHash) {
			$event = $this->modelObj->findEvent ($eventUID, 'tx_cal_phpicalendar', $this->conf ['pidList']);
			
			if (is_object ($event)) {
				unset ($this->controller->piVars ['monitor']);
				unset ($this->controller->piVars ['email']);
				unset ($this->controller->piVars ['sid']);
				$local_rems = Array ();
				$local_sims = Array ();
				$local_wrapped = Array ();
				
				$status = $this->cObj->getSubpart ($page, '###STATUS###');
				switch ($this->conf ['monitor']) {
					case 'stop': /* Unsubscribe a user */		
						if ($this->unsubscribe ($email, $event, $subscriptionHash)) {
							$sims ['###STATUS###'] = sprintf ($this->controller->pi_getLL ('l_monitor_event_unsubscribe_successful'), $event->getTitle ());
						} else {
							/* No user to unsubscribe. Output a message here? */
							$sims ['###STATUS###'] = sprintf ($this->controller->pi_getLL ('l_monitor_event_unsubscribe_error'), $event->getTitle ());
						}
						
						break;
					case 'start': /* Subscribe a user */
						if ($this->subscribe ($email, $event, $subscriptionHash)) {
							$status = $this->cObj->getSubpart ($page, '###STATUS_START###');
							$sims ['###STATUS###'] = sprintf ($this->controller->pi_getLL ('l_monitor_event_subscribe_successful'), $event->getTitle ());
						} else {
							/* No user to subscribe. Output a message here? */
							$sims ['###STATUS###'] = sprintf ($this->controller->pi_getLL ('l_monitor_event_subscribe_error'), $event->getTitle ());
						}
						break;
				}
				
				// $event->getMarker($status, $local_rems, $local_sims, $local_wrapped);
				// $rems['###STATUS###'] = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached($status, $local_sims, $local_rems, $local_wrapped);
			} else {
				$noeventmessage = $this->conf ['monitor'] == 'stop' ? 'l_monitor_event_unsubscribe_noevent' : 'l_monitor_event_subscribe_noevent';
				$sims ['###STATUS###'] = sprintf ($this->controller->pi_getLL ($noeventmessage));
			}
		} else {
			/* If there's a logged in user, show the subscription container */
			if ($this->conf ['subscribeFeUser'] && $this->rightsObj->isLoggedIn ()) {
				$subscriptionContainer = $this->cObj->getSubpart ($page, '###SUBSCRIPTION_CONTAINER###');
				$return = '';
				
				$select = '*';
				$table = 'tx_cal_fe_user_event_monitor_mm, tx_cal_event';
				$where = 'tx_cal_event.uid = tx_cal_fe_user_event_monitor_mm.uid_local AND tx_cal_fe_user_event_monitor_mm.uid_foreign IN (' . $this->rightsObj->getUserId () . ')';
				$where .= ' AND tx_cal_event.deleted = 0 AND tx_cal_event.hidden = 0';
				$where .= ' AND tx_cal_event.pid IN (' . $this->conf ['pidList'] . ')';
				
				/* Save to temporary variables */
				$remUid = $this->conf ['uid'];
				$remType = $this->conf ['type'];
				
				$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
				$eventList = Array ();
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
					$local_rems = Array ();
					$local_sims = Array ();
					$local_wrapped = Array ();
					$subscriptionContainer = $this->cObj->getSubpart ($page, '###SUBSCRIPTION_CONTAINER###');
					$event = $this->modelObj->findEvent ($row ['uid'], 'tx_cal_phpicalendar', $this->conf ['pidList']);
					$this->conf ['uid'] = $row ['uid'];
					$this->conf ['type'] = $event->getType ();
					$event->getMarker ($subscriptionContainer, $local_sims, $local_rems, $local_wrapped);
					$eventList [] = '<li>' . \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($subscriptionContainer, $local_sims, $local_rems, $local_wrapped) . '</li>';
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
				
				/* Restore from temporary variables */
				$this->conf ['uid'] = $remUid;
				$this->conf ['type'] = $remType;
				
				if (empty ($eventList)) {
					$return = 'No events found.';
				} else {
					$return = '<ul>' . implode (chr (10), $eventList) . '</ul>';
				}
				
				$rems ['###SUBSCRIPTION_CONTAINER###'] = $return;
			} else { /* Otherwise, request login or captcha validation */
				$sims ['###STATUS###'] = 'You must be logged in to manage your event notifications.';
			}
		}
		$page = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, $rems, $wrapped);
		$rems = Array ();
		return $this->finish ($page, $rems);
	}
	
	/**
	 * Attempts to unsubscribe an email address from a particular event if the
	 * subscription hash matches.
	 * Check both the fe_users table and the
	 * tx_cal_unknown_users table.
	 * 
	 * @param
	 *        	string		Email address to unsubscribe.
	 * @param
	 *        	object		Event that email should be unsubscribed from.
	 * @param
	 *        	string		Unique hash of email and event.
	 * @return boolean whether unsubscribe was successful.
	 * @todo Should we always try to unsubscribe both fe users and unknown
	 *       users or just try one and stop if successful?
	 */
	function unsubscribe($email, $event, $subscriptionHash) {
		$eventUID = $event->getUID ();
		return $this->unsubscribeByTable ('fe_users', $email, $eventUID, $subscriptionHash) || $this->unsubscribeByTable ('tx_cal_unknown_users', $email, $eventUID, $subscriptionHash);
	}
	
	/**
	 * Attempts to unsubscribe an email address within a particular table from
	 * a particular event if the subscription hash matches.
	 * 
	 * @param
	 *        	string		Table to look up email address in.
	 * @param
	 *        	string		Email address to unsubscribe.
	 * @param
	 *        	object		Event that email should be unsubscribed from.
	 * @param
	 *        	string		Unique hash of email and event.
	 * @return boolean whether unsubscribe was successful.
	 */
	function unsubscribeByTable($table, $email, $eventUID, $subscriptionHash) {
		$sqlSelect = 'tx_cal_event.uid, ' . $table . '.crdate, ' . $table . '.email';
		$sqlTable = 'tx_cal_fe_user_event_monitor_mm, tx_cal_event, ' . $table;
		$sqlWhere = 'tx_cal_event.uid = ' . $eventUID . ' AND tx_cal_event.pid IN (' . $this->conf ['pidList'] . ')' . $this->cObj->enableFields ('tx_cal_event');
		$sqlWhere .= ' AND (tx_cal_event.uid = tx_cal_fe_user_event_monitor_mm.uid_local AND tx_cal_fe_user_event_monitor_mm.uid_foreign = ' . $table . '.uid AND ' . $table . '.email = "' . $email . '")';
		
		$unsubscribeUids = Array ();
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($sqlSelect, $sqlTable, $sqlWhere);
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$md5 = md5 ($row ['uid'] . $row ['email'] . $row ['crdate']);
			if ($md5 == $subscriptionHash) {
				$unsubscribeUids [] = $row ['uid'];
			}
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		
		if (! empty ($unsubscribeUids)) {
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ('tx_cal_fe_user_event_monitor_mm', 'tx_cal_fe_user_event_monitor_mm.uid_local IN (' . implode (',', $unsubscribeUids) . ') AND tx_cal_fe_user_event_monitor_mm.tablenames = "' . $table . '"');
			$returnValue = true;
		} else {
			$returnValue = false;
		}
		
		return $returnValue;
	}
	
	/**
	 * Attempts to subscribe an email address to a particular event if the
	 * subscription hash matches.
	 * 
	 * @param
	 *        	string		Email address to subscribe.
	 * @param
	 *        	object		Event that email should be subscribed to.
	 * @param
	 *        	string		Unique Hash of email and event.
	 * @return boolean whether subscribe was successful.
	 * @todo Should we always try to subscribe as a frontend user first?
	 */
	function subscribe($email, $event, $subscriptionHash) {
		$md5 = md5 ($event->getUid () . $email . $event->getCreationDate ());
		$eventUID = $event->getUID ();
		$eventPID = $event->getPID ();
		
		$offset = $this->conf ['view.'] ['event.'] ['remind.'] ['time'];
		/* If the subscription hash matches, subscribe */
		if ($md5 == $subscriptionHash) {
			$user_uid = $this->getFrontendUserUid ($email);
			$user_table = 'fe_users';
			/* If we didn't find a matching frontend user, try unknown users */
			if (! $user_uid) {
				$user_uid = $this->getUnknownUserUid ($email);
				$user_table = 'tx_cal_unknown_users';
			}
			
			/* Insert the user ID into the monitor table */
			$this->insertMMRow ('tx_cal_fe_user_event_monitor_mm', $eventUID, $user_uid, $user_table, 1, $offset, $eventPID);
			
			$pageTSConf = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig ($eventPID);
			$offset = is_numeric ($pageTSConf ['options.'] ['tx_cal_controller.'] ['view.'] ['event.'] ['remind.'] ['time']) ? $pageTSConf ['options.'] ['tx_cal_controller.'] ['view.'] ['event.'] ['remind.'] ['time'] * 60 : 0;
			$date = new  \TYPO3\CMS\Cal\Model\CalDate ($insertFields ['start_date'] . '000000');
			$date->setTZbyId ('UTC');
			$reminderTimestamp = $date->getTime () + $insertFields ['start_time'] - $offset;
			$reminderService = &\TYPO3\CMS\Cal\Utility\Functions::getReminderService ();
			$reminderService->scheduleReminder ($eventUID);
			
			return true;
		}
		return false;
	}
	
	/**
	 * Inserts an intermediate row for a many-to-many table.
	 * 
	 * @param
	 *        	string		Name of the MM table.
	 * @param
	 *        	integer		Value for the uid_local field.
	 * @param
	 *        	integer		Value for the uid_foreign field.
	 * @param
	 *        	string		Name of the table for uid_foreign.
	 * @param
	 *        	integer		Sort order.
	 * @return integer whether a new row was inserted.
	 */
	function insertMMRow($mmTable, $uid_local, $uid_foreign, $table, $sorting, $offset = 0, $eventPid = 0) {
		$already_exists = false;
		
		/* Check if row already exists */
		$where = 'uid_local =' . $uid_local . ' AND uid_foreign = ' . $uid_foreign . ' AND tablenames="' . $table . '"';
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('uid_local', $mmTable, $where);
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$already_exists = true;
			break;
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		
		/* If the row does not exist, insert it */
		if (! $already_exists) {
			$fields_values = Array (
					'uid_local' => $uid_local,
					'uid_foreign' => $uid_foreign,
					'tablenames' => $table,
					'sorting' => $sorting,
					'offset' => $offset,
					'pid' => $eventPid 
			);
			$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($mmTable, $fields_values);
			if (FALSE === $result){
				throw new \RuntimeException('Could not write '.$mmTable.' record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458161);
			}
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
		$where = 'email = "' . $email . '"';
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$already_exists = true;
			$user_uid = $row ['uid'];
			break;
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		
		if (! $already_exists) {
			$crdate = time ();
			$fields_values = Array (
					'tstamp' => time (),
					'crdate' => $crdate,
					'email' => $email,
					'pid' => $this->conf ['rights.'] ['create.'] ['event.'] ['saveEventToPid'] 
			);
			$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $fields_values);
			if (FALSE === $result){
				throw new \RuntimeException('Could not write '.$table.' record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458162);
			}
			$user_uid = $GLOBALS ['TYPO3_DB']->sql_insert_id ();
		}
		
		return $user_uid;
	}
	function getFrontendUserUid($email) {
		$user_uid = false;
		
		$table = 'fe_users';
		$select = 'uid';
		$where = 'email = "' . $email . '"';
		
		$result = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ($select, $table, $where);
		while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($result)) {
			$user_uid = $row ['uid'];
			break;
		}
		$GLOBALS ['TYPO3_DB']->sql_free_result ($result);
		
		return $user_uid;
	}
}
?>