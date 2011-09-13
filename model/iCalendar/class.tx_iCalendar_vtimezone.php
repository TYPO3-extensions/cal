<?php
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
 * @author  Mike Cochrane <mike@graftonhall.co.nz>
 * @since   Horde 3.0
 * @package Horde_iCalendar
 */

class tx_iCalendar_vtimezone extends tx_model_iCalendar {

    function getType()
    {
        return 'vTimeZone';
    }

    function parsevCalendar($data)
    {
        parent::parsevCalendar($data, 'VTIMEZONE');
    }

    function exportvCalendar()
    {
        return parent::_exportvData('VTIMEZONE');
    }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/iCalendar/class.tx_iCalendar_vtimezone.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/iCalendar/class.tx_iCalendar_vtimezone.php']);
}
?>