<?php
namespace TYPO3\CMS\Cal\Controller;
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
 * TODO
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class EventLinkHandler {
	function main($linktxt, $conf, $linkHandlerKeyword, $linkHandlerValue, $link_param, & $pObj) {
		if ($linkHandlerKeyword != 'calendar') {
			return;
		}
		
		$pid = $pObj->data ['pid'];
		$values = explode ('|', $linkHandlerValue);
		$lconf = Array ();
		if ($values [1]) {
			$lconf ['parameter'] = $values [1];
		}
		$lconf ['additionalParams'] = '&tx_cal_controller[view]=event&tx_cal_controller[type]=tx_cal_phpicalendar&tx_cal_controller[uid]=' . $values [0];
		return $pObj->typoLink ($linktxt, $lconf);
	}
}

?>