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
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class BaseController {
	var $cObj;
	var $local_cObj;
	var $conf;
	var $rightsObj;
	var $controller;
	var $prefixId = 'tx_cal_controller';
	function BaseController() {
		$this->cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$this->local_cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'local_cobj');
		$this->controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$this->conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$this->rightsObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'rightscontroller');
	}
	function __toString() {
		return get_class ($this);
	}
}

?>