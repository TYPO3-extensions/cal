<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Thomas Kowtsch
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * URI Handling class based on TYPO3s original t3lib::htmlmail class
 *
 * @author	Thomas Kowtsch
 */

class tx_cal_uriHandler {
	var $jumperURL_prefix = ''; // This is a prefix that will be added to all links in the mail. Example: 'http://www.mydomain.com/jump?userid=###FIELD_uid###&url='. if used, anything after url= is urlencoded.
	var $jumperURL_useId = 0; // If set, then the array-key of the urls are inserted instead of the url itself. Smart in order to reduce link-length
	var $mediaList = ''; // If set, this is a list of the media-files (index-keys to the array) that should be represented in the html-mail

	var $theParts = array();

	var $message = '';
	var $part = 0;
	var $image_fullpath_list = '';
	var $href_fullpath_list = '';


	/**
	 * Constructor. 
	 *
	 * @return	void
	 */
	public function __construct() {
	}


	/**
	 * Set the HTML variable without further processing
	 *
	 * @param	string		$html: the HTML text to be handled
	 * @return	void
	 */
	public function setHTML($html) {
		$this->theParts['html']['content'] = $html;
	}
	
	/**
	 * Set the PATH variable
	 *
	 * @param	string		$path: the path to be used
	 * @return	void
	 */
	public function setPATH($path) {
		$this->theParts['html']['path'] = $path;
	}
	
	/**
	 * Get the HTML variable 
	 *
	 * @param	string		$html: the HTML text to be handled
	 * @return	string		the HTML text in its curren processing status
	 */
	public function getHTML() {
		return $this->theParts['html']['content'];
	}
	
	public function modify() {
		$this->theParts['html']['content'] = str_replace('PathDebug',$this->theParts['html']['path'],$this->theParts['html']['content']);
	}

	/**
	 * Fetches the HTML-content from either url og local serverfile
	 *
	 * @param	string		$file: the file to load
	 * @return	boolean		whether the data was fetched or not
	 */
	public function fetchHTML($file) {
			// Fetches the content of the page
		$this->theParts['html']['content'] = $this->getUrl($file);
		if ($this->theParts['html']['content']) {
			$addr = $this->extParseUrl($file);
			$path = ($addr['scheme']) ? $addr['scheme'] . '://' . $addr['host'] . (($addr['port']) ? ':' . $addr['port'] : '') . (($addr['filepath']) ? $addr['filepath'] : '/') : $addr['filepath'];
			$this->theParts['html']['path'] = $path;
			return TRUE;
		} else {
			return FALSE;
		}
	}


	/**
	 * Fetches the mediafiles which are found by extractMediaLinks()
	 *
	 * @return	void
	 */
	public function fetchHTMLMedia() {
		if (!is_array($this->theParts['html']['media']) || !count($this->theParts['html']['media'])) {
			return;
		}
		foreach ($this->theParts['html']['media'] as $key => $media) {
				// fetching the content and the mime-type
			$picdata = $this->getExtendedURL($this->theParts['html']['media'][$key]['absRef']);
			if (is_array($picdata)) {
				$this->theParts['html']['media'][$key]['content'] = $picdata['content'];
				$this->theParts['html']['media'][$key]['ctype'] = $picdata['content_type'];
			}
		}
	}


	/**
	 * extracts all media-links from $this->theParts['html']['content']
	 *
	 * @return	void
	 */
	public function extractMediaLinks() {
		$html_code = $this->theParts['html']['content'];
		$attribRegex = $this->tag_regex(array('img', 'table', 'td', 'tr', 'body', 'iframe', 'script', 'input', 'embed'));

			// split the document by the beginning of the above tags
		$codepieces = preg_split($attribRegex, $html_code);
		$len = strlen($codepieces[0]);
		$pieces = count($codepieces);
		$reg = array();
		for ($i = 1; $i < $pieces; $i++) {
			$tag = strtolower(strtok(substr($html_code, $len + 1, 10), ' '));
			$len += strlen($tag) + strlen($codepieces[$i]) + 2;
			$dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
			$attributes = $this->get_tag_attributes($reg[0]); // Fetches the attributes for the tag
			$imageData = array();

				// Finds the src or background attribute
			$imageData['ref'] = ($attributes['src'] ? $attributes['src'] : $attributes['background']);
			if ($imageData['ref']) {
					// find out if the value had quotes around it
				$imageData['quotes'] = (substr($codepieces[$i], strpos($codepieces[$i], $imageData['ref']) - 1, 1) == '"') ? '"' : '';
					// subst_str is the string to look for, when substituting lateron
				$imageData['subst_str'] = $imageData['quotes'] . $imageData['ref'] . $imageData['quotes'];
				if ($imageData['ref'] && !strstr($this->image_fullpath_list, "|" . $imageData["subst_str"] . "|")) {
					$this->image_fullpath_list .= "|" . $imageData['subst_str'] . "|";
					$imageData['absRef'] = $this->absRef($imageData['ref']);
					$imageData['tag'] = $tag;
					$imageData['use_jumpurl'] = $attributes['dmailerping'] ? 1 : 0;
					$this->theParts['html']['media'][] = $imageData;
				}
			}
		}

			// Extracting stylesheets
		$attribRegex = $this->tag_regex(array('link'));
			// Split the document by the beginning of the above tags
		$codepieces = preg_split($attribRegex, $html_code);
		$pieces = count($codepieces);
		for ($i = 1; $i < $pieces; $i++) {
			$dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
				// fetches the attributes for the tag
			$attributes = $this->get_tag_attributes($reg[0]);
			$imageData = array();
			if (strtolower($attributes['rel']) == 'stylesheet' && $attributes['href']) {
					// Finds the src or background attribute
				$imageData['ref'] = $attributes['href'];
					// Finds out if the value had quotes around it
				$imageData['quotes'] = (substr($codepieces[$i], strpos($codepieces[$i], $imageData['ref']) - 1, 1) == '"') ? '"' : '';
					// subst_str is the string to look for, when substituting lateron
				$imageData['subst_str'] = $imageData['quotes'] . $imageData['ref'] . $imageData['quotes'];
				if ($imageData['ref'] && !strstr($this->image_fullpath_list, "|" . $imageData["subst_str"] . "|")) {
					$this->image_fullpath_list .= "|" . $imageData["subst_str"] . "|";
					$imageData['absRef'] = $this->absRef($imageData["ref"]);
					$this->theParts['html']['media'][] = $imageData;
				}
			}
		}

			// fixes javascript rollovers
		$codepieces = explode('.src', $html_code);
		$pieces = count($codepieces);
		$expr = '/^[^' . quotemeta('"') . quotemeta("'") . ']*/';
		for ($i = 1; $i < $pieces; $i++) {
			$temp = $codepieces[$i];
			$temp = trim(str_replace('=', '', trim($temp)));
			preg_match($expr, substr($temp, 1, strlen($temp)), $reg);
			$imageData['ref'] = $reg[0];
			$imageData['quotes'] = substr($temp, 0, 1);
				// subst_str is the string to look for, when substituting lateron
			$imageData['subst_str'] = $imageData['quotes'] . $imageData['ref'] . $imageData['quotes'];
			$theInfo = $this->split_fileref($imageData['ref']);

			switch ($theInfo['fileext']) {
				case 'gif':
				case 'jpeg':
				case 'jpg':
					if ($imageData['ref'] && !strstr($this->image_fullpath_list, "|" . $imageData["subst_str"] . "|")) {
						$this->image_fullpath_list .= "|" . $imageData['subst_str'] . "|";
						$imageData['absRef'] = $this->absRef($imageData['ref']);
						$this->theParts['html']['media'][] = $imageData;
					}
					break;
			}
		}
	}


	/**
	 * extracts all hyperlinks from $this->theParts["html"]["content"]
	 *
	 * @return	void
	 */
	public function extractHyperLinks() {
		$html_code = $this->theParts['html']['content'];
		$attribRegex = $this->tag_regex(array('a', 'form', 'area'));
		$codepieces = preg_split($attribRegex, $html_code); // Splits the document by the beginning of the above tags
		$len = strlen($codepieces[0]);
		$pieces = count($codepieces);
		for ($i = 1; $i < $pieces; $i++) {
			$tag = strtolower(strtok(substr($html_code, $len + 1, 10), " "));
			$len += strlen($tag) + strlen($codepieces[$i]) + 2;

			$dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
				// Fetches the attributes for the tag
			$attributes = $this->get_tag_attributes($reg[0]);
			$hrefData = array();
			$hrefData['ref'] = ($attributes['href'] ? $attributes['href'] : $hrefData['ref'] = $attributes['action']);
			if ($hrefData['ref']) {
					// Finds out if the value had quotes around it
				$hrefData['quotes'] = (substr($codepieces[$i], strpos($codepieces[$i], $hrefData["ref"]) - 1, 1) == '"') ? '"' : '';
					// subst_str is the string to look for, when substituting lateron
				$hrefData['subst_str'] = $hrefData['quotes'] . $hrefData['ref'] . $hrefData['quotes'];
				if ($hrefData['ref'] && substr(trim($hrefData['ref']), 0, 1) != "#" && !strstr($this->href_fullpath_list, "|" . $hrefData['subst_str'] . "|")) {
					$this->href_fullpath_list .= "|" . $hrefData['subst_str'] . "|";
					$hrefData['absRef'] = $this->absRef($hrefData['ref']);
					$hrefData['tag'] = $tag;
					$this->theParts['html']['hrefs'][] = $hrefData;
				}
			}
		}
		// Extracts TYPO3 specific links made by the openPic() JS function
		$codepieces = explode("onClick=\"openPic('", $html_code);
		$pieces = count($codepieces);
		for ($i = 1; $i < $pieces; $i++) {
			$showpic_linkArr = explode("'", $codepieces[$i]);
			$hrefData['ref'] = $showpic_linkArr[0];
			if ($hrefData['ref']) {
				$hrefData['quotes'] = "'";
					// subst_str is the string to look for, when substituting lateron
				$hrefData['subst_str'] = $hrefData['quotes'] . $hrefData['ref'] . $hrefData['quotes'];
				if ($hrefData['ref'] && !strstr($this->href_fullpath_list, "|" . $hrefData['subst_str'] . "|")) {
					$this->href_fullpath_list .= "|" . $hrefData['subst_str'] . "|";
					$hrefData['absRef'] = $this->absRef($hrefData['ref']);
					$this->theParts['html']['hrefs'][] = $hrefData;
				}
			}
		}
	}


	/**
	 * extracts all media-links from $this->theParts["html"]["content"]
	 *
	 * @return	array	two-dimensional array with information about each frame
	 */
	public function extractFramesInfo() {
		$htmlCode = $this->theParts['html']['content'];
		$info = array();
		if (strpos(' ' . $htmlCode, '<frame ')) {
			$attribRegex = $this->tag_regex('frame');
				// Splits the document by the beginning of the above tags
			$codepieces = preg_split($attribRegex, $htmlCode, 1000000);
			$pieces = count($codepieces);
			for ($i = 1; $i < $pieces; $i++) {
				$dummy = preg_match('/[^>]*/', $codepieces[$i], $reg);
					// Fetches the attributes for the tag
				$attributes = $this->get_tag_attributes($reg[0]);
				$frame = array();
				$frame['src'] = $attributes['src'];
				$frame['name'] = $attributes['name'];
				$frame['absRef'] = $this->absRef($frame['src']);
				$info[] = $frame;
			}
			return $info;
		}
	}


	/**
	 * This function substitutes the media-references in $this->theParts["html"]["content"]
	 *
	 * @param	boolean		$absolute: If TRUE, then the refs are substituted with http:// ref's indstead of Content-ID's (cid).
	 * @return	void
	 */
	public function substMediaNamesInHTML($absolute) {
		if (is_array($this->theParts['html']['media'])) {
			foreach ($this->theParts['html']['media'] as $key => $val) {
				if ($val['use_jumpurl'] && $this->jumperURL_prefix) {
					$subst = $this->jumperURL_prefix . t3lib_div::rawUrlEncodeFP($val['absRef']);
				} else {
					$subst = ($absolute) ? $val['absRef'] : 'cid:part' . $key . '.' . $this->messageid;
				}
				$this->theParts['html']['content'] = str_replace(
					$val['subst_str'],
					$val['quotes'] . $subst . $val['quotes'],
					$this->theParts['html']['content']);
			}
		}
		if (!$absolute) {
			$this->fixRollOvers();
		}
	}


	/**
	 * This function substitutes the hrefs in $this->theParts["html"]["content"]
	 *
	 * @return	void
	 */
	public function substHREFsInHTML() {
		if (!is_array($this->theParts['html']['hrefs'])) {
			return;
		}
		foreach ($this->theParts['html']['hrefs'] as $key => $val) {
				// Form elements cannot use jumpurl!
			if ($this->jumperURL_prefix && $val['tag'] != 'form') {
				if ($this->jumperURL_useId) {
					$substVal = $this->jumperURL_prefix . $key;
				} else {
					$substVal = $this->jumperURL_prefix . t3lib_div::rawUrlEncodeFP($val['absRef']);
				}
			} else {
				$substVal = $val['absRef'];
			}
			$this->theParts['html']['content'] = str_replace(
				$val['subst_str'],
				$val['quotes'] . $substVal . $val['quotes'],
				$this->theParts['html']['content']);
		}
	}

	/**
	 * JavaScript rollOvers cannot support graphics inside of mail.
	 * If these exists we must let them refer to the absolute url. By the way:
	 * Roll-overs seems to work only on some mail-readers and so far I've seen it
	 * work on Netscape 4 message-center (but not 4.5!!)
	 *
	 * @return	void
	 */
	public function fixRollOvers() {
		$newContent = '';
		$items = explode('.src', $this->theParts['html']['content']);
		if (count($items) <= 1) {
			return;
		}

		foreach ($items as $key => $part) {
			$sub = substr($part, 0, 200);
			if (preg_match('/cid:part[^ "\']*/', $sub, $reg)) {
					// The position of the string
				$thePos = strpos($part, $reg[0]);
					// Finds the id of the media...
				preg_match('/cid:part([^\.]*).*/', $sub, $reg2);
				$theSubStr = $this->theParts['html']['media'][intval($reg2[1])]['absRef'];
				if ($thePos && $theSubStr) {
						// ... and substitutes the javaScript rollover image with this instead
						// If the path is NOT and url, the reference is set to nothing
					if (!strpos(' ' . $theSubStr, 'http://')) {
						$theSubStr = 'http://';
					}
					$part = substr($part, 0, $thePos) . $theSubStr . substr($part, $thePos + strlen($reg[0]), strlen($part));
				}
			}
			$newContent .= $part . ((($key + 1) != count($items)) ? '.src' : '');
		}
		$this->theParts['html']['content'] = $newContent;
	}


	/**
	 * reads the URL or file and determines the Content-type by either guessing or opening a connection to the host
	 *
	 * @param	string		$url: the URL to get information of
	 * @return	mixed		either FALSE or the array with information
	 */
	public function getExtendedURL($url) {
		$res = array();
		$res['content'] = $this->getUrl($url);
		if (!$res['content']) {
			return FALSE;
		}
		$pathInfo = parse_url($url);
		$fileInfo = $this->split_fileref($pathInfo['path']);
		switch ($fileInfo['fileext']) {
			case 'gif':
			case 'png':
				$res['content_type'] = 'image/' . $fileInfo['fileext'];
				break;
			case 'jpg':
			case 'jpeg':
				$res['content_type'] = 'image/jpeg';
				break;
			case 'html':
			case 'htm':
				$res['content_type'] = 'text/html';
				break;
			case 'css':
				$res['content_type'] = 'text/css';
				break;
			case 'swf':
				$res['content_type'] = 'application/x-shockwave-flash';
				break;
			default:
				$res['content_type'] = $this->getMimeType($url);
		}
		return $res;
	}

	/**
	 * reads a url or file
	 *
	 * @param	string		$url: the URL to fetch
	 * @return	string		the content of the URL
	 */
	public function getUrl($url) {
		return t3lib_div::getUrl($url);
	}


	/**
	 * reads a url or file and strips the HTML-tags AND removes all
	 * empty lines. This is used to read plain-text out of a HTML-page
	 *
	 * @param	string		$url: the URL to load
	 * @return	the content
	 */
	public function getStrippedURL($url) {
		$content = '';
		if ($fd = fopen($url, "rb")) {
			while (!feof($fd)) {
				$line = fgetss($fd, 5000);
				if (trim($line)) {
					$content .= trim($line) . LF;
				}
			}
			fclose($fd);
		}
		return $content;
	}


	/**
	 * This function returns the mime type of the file specified by the url
	 *
	 * @param	string		$url: the url
	 * @return	string		$mimeType: the mime type found in the header
	 */
	public function getMimeType($url) {
		$mimeType = '';
		$headers = trim(t3lib_div::getUrl($url, 2));
		if ($headers) {
			$matches = array();
			if (preg_match('/(Content-Type:[\s]*)([a-zA-Z_0-9\/\-\.\+]*)([\s]|$)/', $headers, $matches)) {
				$mimeType = trim($matches[2]);
			}
		}
		return $mimeType;
	}


	/**
	 * Returns the absolute address of a link. This is based on
	 * $this->theParts["html"]["path"] being the root-address
	 *
	 * @param	string		$ref: address to use
	 * @return	string		the absolute address
	 */
	public function absRef($ref) {
		$ref = trim($ref);
		$info = parse_url($ref);
		if ($info['scheme']) {
			return $ref;
		} elseif (preg_match('/^\//', $ref)) {
			$addr = parse_url($this->theParts['html']['path']);
			return $addr['scheme'] . '://' . $addr['host'] . ($addr['port'] ? ':' . $addr['port'] : '') . $ref;
		} else {
				// If the reference is relative, the path is added, in order for us to fetch the content
			return $this->theParts['html']['path'] . $ref;
		}
	}

	/**
	 * Returns information about a file reference
	 *
	 * @param	string		$fileref: the file to use
	 * @return	array		path, filename, filebody, fileext
	 */
	public function split_fileref($fileref) {
		$info = array();
		if (preg_match('/(.*\/)(.*)$/', $fileref, $reg)) {
			$info['path'] = $reg[1];
			$info['file'] = $reg[2];
		} else {
			$info['path'] = '';
			$info['file'] = $fileref;
		}
		$reg = '';
		if (preg_match('/(.*)\.([^\.]*$)/', $info['file'], $reg)) {
			$info['filebody'] = $reg[1];
			$info['fileext'] = strtolower($reg[2]);
			$info['realFileext'] = $reg[2];
		} else {
			$info['filebody'] = $info['file'];
			$info['fileext'] = '';
		}
		return $info;
	}


	/**
	 * Returns an array with file or url-information
	 *
	 * @param	string		$path: url to check
	 * @return	array		information about the path / URL
	 */
	public function extParseUrl($path) {
		$res = parse_url($path);
		preg_match('/(.*\/)([^\/]*)$/', $res['path'], $reg);
		$res['filepath'] = $reg[1];
		$res['filename'] = $reg[2];
		return $res;
	}

	/**
	 * Creates a regular expression out of a list of tags
	 *
	 * @param	mixed		$tagArray: the list of tags (either as array or string if it is one tag)
	 * @return	string		the regular expression
	 */
	public function tag_regex($tags) {
		$tags = (!is_array($tags) ? array($tags) : $tags);
		$regexp = '/';
		$c = count($tags);
		foreach ($tags as $tag) {
			$c--;
			$regexp .= '<' . $tag . '[[:space:]]' . (($c) ? '|' : '');
		}
		return $regexp . '/i';
	}

	/**
	 * This function analyzes a HTML tag
	 * If an attribute is empty (like OPTION) the value of that key is just empty. Check it with is_set();
	 *
	 * @param	string		$tag: is either like this "<TAG OPTION ATTRIB=VALUE>" or
	 *				 this " OPTION ATTRIB=VALUE>" which means you can omit the tag-name
	 * @return	array		array with attributes as keys in lower-case
	 */
	public function get_tag_attributes($tag) {
		$attributes = array();
		$tag = ltrim(preg_replace('/^<[^ ]*/', '', trim($tag)));
		$tagLen = strlen($tag);
		$safetyCounter = 100;
			// Find attribute
		while ($tag) {
			$value = '';
			$reg = preg_split('/[[:space:]=>]/', $tag, 2);
			$attrib = $reg[0];

			$tag = ltrim(substr($tag, strlen($attrib), $tagLen));
			if (substr($tag, 0, 1) == '=') {
				$tag = ltrim(substr($tag, 1, $tagLen));
				if (substr($tag, 0, 1) == '"') {
						// Quotes around the value
					$reg = explode('"', substr($tag, 1, $tagLen), 2);
					$tag = ltrim($reg[1]);
					$value = $reg[0];
				} else {
						// No quotes around value
					preg_match('/^([^[:space:]>]*)(.*)/', $tag, $reg);
					$value = trim($reg[1]);
					$tag = ltrim($reg[2]);
					if (substr($tag, 0, 1) == '>') {
						$tag = '';
					}
				}
			}
			$attributes[strtolower($attrib)] = $value;
			$safetyCounter--;
			if ($safetyCounter < 0) {
				break;
			}
		}
		return $attributes;
	}
}


?>