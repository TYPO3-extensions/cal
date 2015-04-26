<?php
namespace TYPO3\CMS\Cal\Model\ICalendar;
/**
 * Class representing vTimezones.
 *
 * $Horde: framework/iCalendar/iCalendar/vtimezone.php,v 1.8.10.4 2006/01/01 21:28:47 jan Exp $
 *
 * Copyright 2003-2006 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Mike Cochrane <mike@graftonhall.co.nz>
 * @since Horde 3.0
 * @package Horde_iCalendar
 */
class standard extends \TYPO3\CMS\Cal\Model\ICalendar {
	function getType() {
		return 'standard';
	}
	function parsevCalendar($data) {
		parent::parsevCalendar ($data, 'STANDARD');
	}
	function exportvCalendar() {
		return parent::_exportvData ('STANDARD');
	}
}

?>