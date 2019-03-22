<?php
namespace TYPO3\CMS\Cal\Hooks;
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
class DateEval {
	
	/**
	 * Javascript evaluation for cal date fields.
	 * Transforms various date
	 * formats into the standard date format just like the evaluation
	 * performed on regular TYPO3 date fields.
	 *
	 * @return JavaScript code for evaluating the date field.
	 * @todo Add evaluations similar to what the backend already uses,
	 *       converting periods and slashes into dashes and taking US date
	 *       format into account.
	 */
	function returnFieldJS() {
		return '
			//Convert the date to a timstamp using standard TYPO3 methods
			value = evalFunc.input("date", value);
			//Convert the timestamp back to human readable using standard TYPO3 methods
			value = evalFunc.output("date", value, null);
			return value;
		';
	}
}

?>