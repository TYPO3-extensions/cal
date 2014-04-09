<?php
/**
 * Class representing vNotes.
 *
 * $Horde: framework/iCalendar/iCalendar/vnote.php,v 1.3.10.5 2006/03/03 09:07:31 jan Exp $
 *
 * Copyright 2003-2006 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Karsten Fourmont <fourmont@gmx.de>
 * @package Horde_iCalendar
 */
class tx_iCalendar_vnote extends tx_model_iCalendar {
	function tx_iCalendar_vnote($version = '1.1') {
		return parent::tx_model_iCalendar ($version);
	}
	function getType() {
		return 'vNote';
	}
	function parsevCalendar($data) {
		return parent::parsevCalendar ($data, 'VNOTE');
	}
	
	/**
	 * Unlike vevent and vtodo, a vnote is normally not enclosed in an
	 * iCalendar container.
	 * (BEGIN..END)
	 */
	function exportvCalendar() {
		$requiredAttributes ['BODY'] = '';
		$requiredAttributes ['VERSION'] = '1.1';
		
		foreach ($requiredAttributes as $name => $default_value) {
			if (is_a ($this->getattribute ($name), 'PEAR_Error')) {
				$this->setAttribute ($name, $default_value);
			}
		}
		
		return $this->_exportvData ('VNOTE');
	}
}
if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/iCalendar/class.tx_iCalendar_vnote.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/iCalendar/class.tx_iCalendar_vnote.php']);
}
?>