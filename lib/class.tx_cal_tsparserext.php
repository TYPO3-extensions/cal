<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2004-2009 Rupert Germann <rupi@gmx.li>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */

/**
 * Class that renders fields for the extensionmanager configuration
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_tsparserext {
	
	/**
	 * [Describe function...]
	 *
	 * @return [type]
	 */
	function displayMessage(&$params, &$tsObj) {
		$out = '';
		$out .= '
		<div style="position:absolute;top:10px;right:10px; width:300px;">
			<div class="typo3-message message-information">
   				<div class="message-header">' . $GLOBALS ['LANG']->sL ('LLL:EXT:cal/locallang.xml:extmng.updatermsgHeader') . '</div>
  				<div class="message-body">
  					' . $GLOBALS ['LANG']->sL ('LLL:EXT:cal/locallang.xml:extmng.updatermsg') . '<br />
  					<a style="text-decoration:underline;" href="mod.php?id=0&amp;M=tools_em&amp;CMD[showExt]=cal&amp;SET[singleDetails]=updateModule">
  					' . $GLOBALS ['LANG']->sL ('LLL:EXT:cal/locallang.xml:extmng.updatermsgLink') . '</a>
  				</div>
  			</div>
  		</div>
  		';
		
		return $out;
	}
}
if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/lib/class.tx_cal_tsparserext.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/lib/class.tx_cal_tsparserext.php']);
}
?>