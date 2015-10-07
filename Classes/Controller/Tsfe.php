<?php

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

namespace TYPO3\CMS\Cal\Controller;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Tsfe
 */
class Tsfe extends TypoScriptFrontendController
{
    /**
     * @param mixed $code
     * @param string $header
     * @param string $reason
     */
    function pageNotFoundHandler($code, $header = '', $reason = '')
    {
        // do nothing
    }

    /**
     * @param string $reason
     * @param string $header
     */
    function pageNotFoundAndExit($reason = '', $header = '')
    {
        // do nothing
    }
}