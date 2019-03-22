<?php
namespace TYPO3\CMS\Cal\View\Module;
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
class Example extends \TYPO3\CMS\Cal\View\BaseView {
	public function start(&$moduleCaller) {
		return 'text from another module';
	}
}
?>