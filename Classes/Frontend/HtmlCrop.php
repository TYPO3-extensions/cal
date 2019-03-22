<?php
namespace TYPO3\CMS\Cal\Frontend;
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
 * includeLibs.something = EXT:cal/misc/user_htmlCrop.php
 * plugin.tx_cal_controller.view.event.event.description{
 * postUserFunc = user_htmlCrop->html_substr2PHP5 // or html_substr2PHP4
 * postUserFunc {
 * minLength = 21
 * replaceString = ...
 * }
 * }
 */
class HtmlCrop {
	
	function html_substr2PHP4($content, $conf) {
		
		// The approximate length you want the concatenated text to be
		$minimum_length = $conf ['minLength'] ? $conf ['minLength'] : 200;
		// The variation in how long the text can be
		// in this example text length will be between 200-10=190 characters
		// and the character where the last tag ends
		// $length_offset = $conf['lengthOffset'] ? $conf['lengthOffset'] : 10;
		
		$replaceString = $conf ['replaceString'] ? $conf ['replaceString'] : '...';
		// Reset tag counter & quote checker
		$doc = domxml_xmltree ('<body>' . $content . '</body>');
		$body = $doc->get_elements_by_tagname ('body');
		$length = 0;
		$content = $this->getContentAsStringPHP4 ($body [0], $length, $minimum_length, $replaceString);
		return $content;
	}
	function getContentAsStringPHP4($node, &$length, $maxLength = 200, $replaceString = '...') {
		$st = "";
		if ($length >= $maxLength)
			return $st;
		foreach ($node->child_nodes () as $cnode) {
			if ($cnode->node_type () == XML_TEXT_NODE) {
				$text = $cnode->node_value ();
				if (strlen ($text) + $length < $maxLength) {
					$st .= $text;
					$length += strlen ($text);
				} else if ($length < $maxLength) {
					$st .= substr ($text, 0, $maxLength - $length) . $replaceString;
					$length = $maxLength + 1;
					return $st;
				}
			} else {
				if ($cnode->node_type () == XML_ELEMENT_NODE) {
					$st .= "<" . $cnode->node_name ();
					if ($attribnodes = $cnode->attributes ()) {
						$st .= " ";
						foreach ($attribnodes as $anode)
							$st .= $anode->node_name () . "='" . $anode->node_value () . "'";
					}
					$nodeText = $this->getContentAsString ($cnode, $length, $maxLength, $replaceString);
					if (empty ($nodeText) && ! $attribnodes)
						$st .= " />"; // unary
					else
						$st .= ">" . $nodeText . "</" . $cnode->node_name () . ">";
				}
			}
			if ($length >= $maxLength)
				break;
		}
		return $st;
	}
	function html_substr2PHP5($content, $conf) {
		
		// The approximate length you want the concatenated text to be
		$minimum_length = $conf ['minLength'] ? $conf ['minLength'] : 200;
		// The variation in how long the text can be
		// in this example text length will be between 200-10=190 characters
		// and the character where the last tag ends
		// $length_offset = $conf['lengthOffset'] ? $conf['lengthOffset'] : 10;
		
		$replaceString = $conf ['replaceString'] ? $conf ['replaceString'] : '...';
		// Reset tag counter & quote checker
		
		$doc = new DOMDocument ('1.0', 'utf-8');
		$doc->loadHTML ('<body>' . $content . '</body>');
		$bodyTags = $doc->getElementsByTagName ('body');
		$length = 0;
		foreach ($bodyTags as $body) {
			$content = $this->getContentAsStringPHP5 ($body, $length, $minimum_length, $replaceString);
			return $content;
		}
	}
	function getContentAsStringPHP5($node, &$length, $maxLength = 200, $replaceString = '...') {
		$st = "";
		if ($length >= $maxLength)
			return $st;
		foreach ($node->childNodes as $cnode) {
			if ($cnode->nodeType == XML_TEXT_NODE) {
				$text = $cnode->nodeValue;
				if (strlen ($text) + $length < $maxLength) {
					$st .= $text;
					$length += strlen ($text);
				} else if ($length < $maxLength) {
					$st .= substr ($text, 0, $maxLength - $length) . $replaceString;
					$length = $maxLength + 1;
					return $st;
				}
			} else {
				if ($cnode->nodeType == XML_ELEMENT_NODE) {
					$st .= "<" . $cnode->nodeName;
					if ($attribnodes = $cnode->attributes) {
						$st .= " ";
						foreach ($attribnodes as $anode)
							$st .= $anode->nodeName . "='" . $anode->nodeValue . "'";
					}
					$nodeText = $this->getContentAsStringPHP5 ($cnode, $length, $maxLength, $replaceString);
					if (empty ($nodeText) && ! $attribnodes)
						$st .= " />"; // unary
					else
						$st .= ">" . $nodeText . "</" . $cnode->nodeName . ">";
				}
			}
			if ($length >= $maxLength)
				break;
		}
		return $st;
	}
}

?>