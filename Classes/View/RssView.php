<?php
namespace TYPO3\CMS\Cal\View;
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
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class RssView extends \TYPO3\CMS\Cal\View\BaseView {
	
	var $templateCode;
	var $config;
	var $allowCaching;
	
	public function __construct() {
		parent::__construct();
	}
	
	public function drawRss(&$master_array, $getdate) {
		// $this->arcExclusive = -1; // Only latest, non archive news
		$this->allowCaching = $this->conf ['view.'] ['rss.'] ['xmlCaching'];
		$this->config ['limit'] = $this->conf ['view.'] ['rss.'] ['xmlLimit'] ? $this->conf ['view.'] ['rss.'] ['xmlLimit'] : 10;
		
		// possible format with piVars
		if ($this->controller->piVars ['xmlFormat'])
			$this->conf ['view.'] ['rss.'] ['xmlFormat'] = $this->controller->piVars ['xmlFormat'];
		
		switch ($this->conf ['view.'] ['rss.'] ['xmlFormat']) {
			case 'rss091' :
				$templateName = 'TEMPLATE_RSS091';
				$this->templateCode = $this->cObj->fileResource ($this->conf ['view.'] ['rss.'] ['rss091_tmplFile']);
				break;
			
			case 'rss2' :
				$templateName = 'TEMPLATE_RSS2';
				$this->templateCode = $this->cObj->fileResource ($this->conf ['view.'] ['rss.'] ['rss2_tmplFile']);
				break;
			
			case 'rdf' :
				$templateName = 'TEMPLATE_RDF';
				$this->templateCode = $this->cObj->fileResource ($this->conf ['view.'] ['rss.'] ['rdf_tmplFile']);
				break;
			
			case 'atom03' :
				$templateName = 'TEMPLATE_ATOM03';
				$this->templateCode = $this->cObj->fileResource ($this->conf ['view.'] ['rss.'] ['atom03_tmplFile']);
				break;
			
			case 'atom1' :
				$templateName = 'TEMPLATE_ATOM1';
				$this->templateCode = $this->cObj->fileResource ($this->conf ['view.'] ['rss.'] ['atom1_tmplFile']);
				break;
		}
		
		// get siteUrl for links in rss feeds. the 'dontInsert' option seems to be needed in some configurations depending on the baseUrl setting
		if (! $this->conf ['view.'] ['rss.'] ['dontInsertSiteUrl']) {
			$this->config ['siteUrl'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv ('TYPO3_SITE_URL');
		}
		
		// fill at least the template header
		// Init Templateparts: $t['total'] is complete template subpart (TEMPLATE_LATEST f.e.)
		$t = Array ();
		$t ['total'] = $this->getNewsSubpart ($this->templateCode, $this->spMarker ('###' . $templateName . '###'));
		// Reset:
		$subpartArray = Array ();
		$wrappedSubpartArray = Array ();
		$markerArray = Array ();
		$count = 0;
		
		foreach ($master_array as $eventDate => $eventTimeArray) {
			if (is_object ($eventTimeArray)) {
				if ($eventTimeArray->getEnd ()->format ('%Y%m%d') < $getdate) {
					break;
				}
				if ($count < $this->config ['limit']) {
					$subpartArray ['###CONTENT###'] .= $eventTimeArray->renderEventFor ('rss');
					$count ++;
				} else {
					break;
				}
			} else {
				foreach ($eventTimeArray as $key => $eventArray) {
					foreach ($eventArray as $eventUid => $event) {
						if (is_object ($event)) {
							if ($event->getEnd ()->format ('%Y%m%d') < $getdate) {
								break;
							}
							if ($count < $this->config ['limit']) {
								$subpartArray ['###CONTENT###'] .= $event->renderEventFor ('rss');
								$count ++;
							} else {
								break;
							}
						}
					}
				}
			}
		}
		
		$lastBuildTimestamp = time ();
		
		// header data
		$markerArray = $this->getXmlHeader ($lastBuildTimestamp);
		$subpartArray ['###HEADER###'] = $this->cObj->substituteMarkerArray ($this->getNewsSubpart ($t ['total'], '###HEADER###'), $markerArray);
		// substitute the xml declaration (it's not included in the subpart ###HEADER###)
		$t ['total'] = $this->cObj->substituteMarkerArray ($t ['total'], Array (
				'###XML_DECLARATION###' => $markerArray ['###XML_DECLARATION###'] 
		));
		$t ['total'] = $this->cObj->substituteMarkerArray ($t ['total'], Array (
				'###SITE_LANG###' => $markerArray ['###SITE_LANG###'] 
		));
		$t ['total'] = $this->cObj->substituteSubpart ($t ['total'], '###HEADER###', $subpartArray ['###HEADER###'], 0);
		$t ['total'] = $this->cObj->substituteSubpart ($t ['total'], '###CONTENT###', $subpartArray ['###CONTENT###'], 0);
		
		$content .= $t ['total'];
		return $content;
	}
	
	public function getRssFromEvent($eventDate, &$event, $template) {
		$eventTemplate = $this->cObj->getSubpart ($template, '###EVENT###');
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		$event->getMarker ($eventTemplate, $sims, $rems, $wrapped);
		$rssUrl = $this->config ['siteUrl'] . $event->getLinkToEvent ('', 'event', $eventDate, true);
		// replace square brackets [] in links with their URLcodes and replace the &-sign with its ASCII code
		$sims ['###LINK###'] = preg_replace (Array (
				'/\[/',
				'/\]/',
				'/&/' 
		), Array (
				'%5B',
				'%5D',
				'&#38;' 
		), $rssUrl);
		$sims ['###TITLE###'] = str_replace ('&', '&amp;', $sims ['###TITLE###']);
		
		// date should be ok
		$sims ['###CREATE_DATE###'] = date ('D, d M Y H:i:s O', $event->row ['crdate']);
		
		if ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom03' || $this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom1') {
			$sims ['###CREATE_DATE###'] = $this->getW3cDate ($event->row ['crdate']);
		}
		
		return \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($eventTemplate, $sims, $rems, Array ());
	}
	
	/**
	 * Returns a subpart from the input content stream.
	 * Enables pre-/post-processing of templates/templatefiles
	 *
	 * @param string $Content
	 *        	typically HTML template content.
	 * @param string $Marker
	 *        	typically on the form "###...###"
	 * @param array $Optional:
	 *        	active row of data - if available
	 * @return string subpart found, if found.
	 */
	public function getNewsSubpart($myTemplate, $myKey, $row = Array()) {
		return ($this->cObj->getSubpart ($myTemplate, $myKey));
	}
	
	/**
	 * Returns alternating layouts
	 *
	 * @param string $html
	 *        	of the template subpart
	 * @param integer $number
	 *        	alternatingLayouts
	 * @param string $name
	 *        	the content-markers in this template-subpart
	 * @return array code for alternating content markers
	 */
	public function getLayouts($templateCode, $alternatingLayouts, $marker) {
		$out = Array ();
		for ($a = 0; $a < $alternatingLayouts; $a ++) {
			$m = '###' . $marker . ($a ? '_' . $a : '') . '###';
			if (strstr ($templateCode, $m)) {
				$out [] = $GLOBALS ['TSFE']->cObj->getSubpart ($templateCode, $m);
			} else {
				break;
			}
		}
		return $out;
	}
	
	/**
	 * returns the subpart name.
	 * if 'altMainMarkers.' are given this name is used instead of the default marker-name.
	 *
	 * @param string $subpartMarker
	 *        	name of the subpart to be substituted
	 * @return string name of the template subpart
	 */
	public function spMarker($subpartMarker) {
		$sPBody = substr ($subpartMarker, 3, - 3);
		$altSPM = '';
		if (isset ($this->conf ['altMainMarkers.'])) {
			$altSPM = trim ($this->cObj->stdWrap ($this->conf ['altMainMarkers.'] [$sPBody], $this->conf ['altMainMarkers.'] [$sPBody . '.']));
			$GLOBALS ['TT']->setTSlogMessage ('Using alternative subpart marker for \'' . $subpartMarker . '\': ' . $altSPM, 1);
		}
		
		return $altSPM ? $altSPM : $subpartMarker;
	}
	
	/**
	 * builds the XML header (array of markers to substitute)
	 *
	 * @return array filled XML header markers
	 */
	public function getXmlHeader($lastBuildTimestamp) {
		$markerArray = Array ();
		
		$markerArray ['###SITE_TITLE###'] = $this->conf ['view.'] ['rss.'] ['xmlTitle'];
		$markerArray ['###SITE_LINK###'] = $this->config ['siteUrl'];
		$markerArray ['###SITE_DESCRIPTION###'] = $this->conf ['view.'] ['rss.'] ['xmlDesc'];
		if (! empty ($markerArray ['###SITE_DESCRIPTION###'])) {
			if ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom03') {
				$markerArray ['###SITE_DESCRIPTION###'] = '<tagline>' . $markerArray ['###SITE_DESCRIPTION###'] . '</tagline>';
			} elseif ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom1') {
				$markerArray ['###SITE_DESCRIPTION###'] = '<subtitle>' . $markerArray ['###SITE_DESCRIPTION###'] . '</subtitle>';
			}
		}
		
		$markerArray ['###SITE_LANG###'] = $this->conf ['view.'] ['rss.'] ['xmlLang'];
		if ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'rss2') {
			$markerArray ['###SITE_LANG###'] = '<language>' . $markerArray ['###SITE_LANG###'] . '</language>';
		} elseif ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom03') {
			$markerArray ['###SITE_LANG###'] = ' xml:lang="' . $markerArray ['###SITE_LANG###'] . '"';
		}
		if (empty ($this->conf ['view.'] ['rss.'] ['xmlLang'])) {
			$markerArray ['###SITE_LANG###'] = '';
		}
		
		$icon = $this->conf ['view.'] ['rss.'] ['xmlIcon'];
		if (substr ($icon, 0, 4) == 'EXT:') { // extension
			list ($extKey, $local) = explode ('/', substr ($icon, 4), 2);
			$filename = '';
			if (strcmp ($extKey, '') && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded ($extKey) && strcmp ($local, '')) {
				$icon = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath ($extKey) . $local;
			}
		}
		
		$markerArray ['###IMG###'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv ('TYPO3_REQUEST_DIR') . $icon;
		$imgFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv ('TYPO3_DOCUMENT_ROOT') . '/' . $icon;
		$imgSize = is_file ($imgFile) ? getimagesize ($imgFile) : '';
		
		$markerArray ['###IMG_W###'] = $imgSize [0];
		$markerArray ['###IMG_H###'] = $imgSize [1];
		
		$markerArray ['###NEWS_WEBMASTER###'] = $this->conf ['view.'] ['rss.'] ['xmlWebMaster'];
		$markerArray ['###NEWS_MANAGINGEDITOR###'] = $this->conf ['view.'] ['rss.'] ['xmlManagingEditor'];
		
		// optional tags
		if ($this->conf ['view.'] ['rss.'] ['xmlLastBuildDate']) {
			// date should be ok
			$markerArray ['###NEWS_LASTBUILD###'] = '<lastBuildDate>' . date ('D, d M Y H:i:s O', $lastBuildTimestamp) . '</lastBuildDate>';
		} else {
			$markerArray ['###NEWS_LASTBUILD###'] = '';
		}
		
		if ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom03' || $this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom1') {
			// TODO: $row ???
			$markerArray ['###NEWS_LASTBUILD###'] = $this->getW3cDate ($row ['maxval']);
		}
		
		if ($this->conf ['view.'] ['rss.'] ['xmlWebMaster']) {
			$markerArray ['###NEWS_WEBMASTER###'] = '<webMaster>' . $this->conf ['view.'] ['rss.'] ['xmlWebMaster'] . '</webMaster>';
		} else {
			$markerArray ['###NEWS_WEBMASTER###'] = '';
		}
		
		if ($this->conf ['view.'] ['rss.'] ['xmlManagingEditor']) {
			$markerArray ['###NEWS_MANAGINGEDITOR###'] = '<managingEditor>' . $this->conf ['view.'] ['rss.'] ['xmlManagingEditor'] . '</managingEditor>';
		} else {
			$markerArray ['###NEWS_MANAGINGEDITOR###'] = '';
		}
		
		if ($this->conf ['view.'] ['rss.'] ['xmlCopyright']) {
			if ($this->conf ['view.'] ['rss.'] ['xmlFormat'] == 'atom1') {
				$markerArray ['###NEWS_COPYRIGHT###'] = '<rights>' . $this->conf ['view.'] ['rss.'] ['xmlCopyright'] . '</rights>';
			} else {
				$markerArray ['###NEWS_COPYRIGHT###'] = '<copyright>' . $this->conf ['view.'] ['rss.'] ['xmlCopyright'] . '</copyright>';
			}
		} else {
			$markerArray ['###NEWS_COPYRIGHT###'] = '';
		}
		
		$charset = ($GLOBALS ['TSFE']->metaCharset ? $GLOBALS ['TSFE']->metaCharset : 'utf-8');
		if ($this->conf ['view.'] ['rss.'] ['xmlDeclaration']) {
			$markerArray ['###XML_DECLARATION###'] = trim ($this->conf ['view.'] ['rss.'] ['xmlDeclaration']);
		} else {
			$markerArray ['###XML_DECLARATION###'] = '<?xml version="1.0" encoding="' . $charset . '"?>';
		}
		
		// promoting TYPO3 in atom feeds, supress the subversion
		$version = explode ('.', ($GLOBALS ['TYPO3_VERSION'] ? $GLOBALS ['TYPO3_VERSION'] : $GLOBALS ['TYPO_VERSION']));
		unset ($version [2]);
		$markerArray ['###TYPO3_VERSION###'] = implode ($version, '.');
		
		return $markerArray;
	}
	
	/**
	 * Generates the date format needed for Atom feeds
	 * see: http://www.w3.org/TR/NOTE-datetime (same as ISO 8601)
	 * in php5 it would be so easy: date('c', $row['datetime']);
	 *
	 * @param
	 *        	integer		the datetime value to be converted to w3c format
	 * @return string in w3c format
	 */
	public function getW3cDate($datetime) {
		// date is only filled with crdate
		$offset = date ('Z', $datetime) / 3600;
		if ($offset < 0) {
			$offset *= - 1;
			if ($offset < 10) {
				$offset = '0' . $offset;
			}
			$offset = '-' . $offset;
		} elseif ($offset == 0) {
			$offset = '+00';
		} elseif ($offset < 10) {
			$offset = '+0' . $offset;
		} else {
			$offset = '+' . $offset;
		}
		return strftime ('%Y-%m-%dT%H:%M:%S', $datetime) . $offset . ':00';
	}
}

?>