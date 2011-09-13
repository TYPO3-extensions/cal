<?php


/***************************************************************
*  Copyright notice
*
*  (c) 2004 
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_rssview extends tx_cal_base_view {
	
	var $templateCode;
	var $config;
	var $allowCaching;
	
	function tx_cal_rssview() {
		$this->tx_cal_base_service();
	}
	
	function drawRss(&$master_array, $getdate) {	
		$prefix_display = 'displayXML';
		// $this->arcExclusive = -1; // Only latest, non archive news
		$this->allowCaching = $this->conf['displayXML.']['xmlCaching'];
		$this->config['limit'] = $this->conf['displayXML.']['xmlLimit']?$this->conf['displayXML.']['xmlLimit']:
		$this->config['limit'];
        
        //possible format with piVars
        if($this->controller->piVars['xmlFormat']) $this->conf['displayXML.']['xmlFormat'] = $this->controller->piVars['xmlFormat'];
        
		switch ($this->conf['displayXML.']['xmlFormat']) {
			case 'rss091':
			$templateName = 'TEMPLATE_RSS091';
			$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss091_tmplFile']);
			break;

			case 'rss2':
			$templateName = 'TEMPLATE_RSS2';
			$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rss2_tmplFile']);
			break;

			case 'rdf':
			$templateName = 'TEMPLATE_RDF';
			$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['rdf_tmplFile']);
			break;

			case 'atom03':
			$templateName = 'TEMPLATE_ATOM03';
			$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['atom03_tmplFile']);
			break;

			case 'atom1':
			$templateName = 'TEMPLATE_ATOM1';
			$this->templateCode = $this->cObj->fileResource($this->conf['displayXML.']['atom1_tmplFile']);
			break;

		}
		
		
		// get siteUrl for links in rss feeds. the 'dontInsert' option seems to be needed in some configurations depending on the baseUrl setting
		if (!$this->conf['displayXML.']['dontInsertSiteUrl']) {
			$this->config['siteUrl'] = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}
		
		// fill at least the template header
		// Init Templateparts: $t['total'] is complete template subpart (TEMPLATE_LATEST f.e.)
		$t = array();
		$t['total'] = $this->getNewsSubpart($this->templateCode, $this->spMarker('###' . $templateName . '###'));
		// Reset:
		$subpartArray = array();
		$wrappedSubpartArray = array();
		$markerArray = array();
		// header data
		$markerArray = $this->getXmlHeader();
//debug($master_array);		
		foreach($master_array as $eventDate => $eventTimeArray){
			if(is_object($eventTimeArray)){
				$subpartArray['###CONTENT###'] .= $this->getRssFromEvent($eventTimeArray, $this->templateCode);
			}else{
				foreach ($eventTimeArray as $key => $eventArray) {
					foreach($eventArray as $eventUid => $event){
						if (is_object($event)) {
							$subpartArray['###CONTENT###'] .= $this->getRssFromEvent($event, $this->templateCode);
						}
					}
				}
			}
		}
//debug($subpartArray['###CONTENT###']);
		$subpartArray['###HEADER###'] = $this->cObj->substituteMarkerArray($this->getNewsSubpart($t['total'], '###HEADER###'), $markerArray);
		// substitute the xml declaration (it's not included in the subpart ###HEADER###)
		$t['total'] = $this->cObj->substituteMarkerArray($t['total'], array('###XML_DECLARATION###' => $markerArray['###XML_DECLARATION###']));
		$t['total'] = $this->cObj->substituteMarkerArray($t['total'], array('###SITE_LANG###' => $markerArray['###SITE_LANG###']));
		$t['total'] = $this->cObj->substituteSubpart($t['total'], '###HEADER###', $subpartArray['###HEADER###'], 0);
		$t['total'] = $this->cObj->substituteSubpart($t['total'], '###CONTENT###', $subpartArray['###CONTENT###'], 0);
		
		

		$content .= $t['total'];
		return $content;
	}
	
	function getRssFromEvent(&$event, $template){
		$eventTemplate = $this->cObj->getSubpart($template, '###EVENT###');
		$rems = array();
		$sims = array();
		$wrapped = array();
		$event->getEventMarker($eventTemplate, $rems, $sims,$wrapped);
		$rssUrl = $this->config['siteUrl'] . $this->controller->pi_linkTP_keepPIvars_url(array ('page_id' => $this->conf['view.']['event.']['eventViewPid'], 'getdate' => $this->conf['getdate'], 'view' => 'event', 'type' => $event->getType(), 'uid' => $event->getUid()), $this->conf['cache'], $this->conf['clear_anyway']);
	
		// replace square brackets [] in links with their URLcodes and replace the &-sign with its ASCII code
		$sims['###LINK###'] = preg_replace(array('/\[/', '/\]/', '/&/'), array('%5B', '%5D', '&#38;') , $rssUrl);
        $sims['###TITLE###'] = str_replace('&','&amp;',$sims['###TITLE###']);
		
		$sims['###CREATE_DATE###'] = date('D, d M Y H:i:s O', $event->row['crdate']);

		if($this->conf['displayXML.']['xmlFormat'] == 'atom03' ||
		   $this->conf['displayXML.']['xmlFormat'] == 'atom1') {
			$sims['###CREATE_DATE###'] = $this->getW3cDate($event->row['crdate']);
		}
		
		
		return $this->cObj->substituteMarkerArrayCached($eventTemplate, $sims, $rems, array ());
	}
	
		/**
	 * Returns a subpart from the input content stream.
	 * Enables pre-/post-processing of templates/templatefiles
	 *
	 * @param	string		$Content stream, typically HTML template content.
	 * @param	string		$Marker string, typically on the form "###...###"
	 * @param	array		$Optional: the active row of data - if available
	 * @return	string		The subpart found, if found.
	 */
	function getNewsSubpart($myTemplate, $myKey, $row = Array()) {
		return ($this->cObj->getSubpart($myTemplate, $myKey));
	}
	
	/**
	 * Returns alternating layouts
	 *
	 * @param	string		$html code of the template subpart
	 * @param	integer		$number of alternatingLayouts
	 * @param	string		$name of the content-markers in this template-subpart
	 * @return	array		html code for alternating content markers
	 */
	function getLayouts($templateCode, $alternatingLayouts, $marker) {
		$out = array();
		for($a = 0; $a < $alternatingLayouts; $a++) {
			$m = '###' . $marker . ($a?'_' . $a:'') . '###';
			if (strstr($templateCode, $m)) {
				$out[] = $GLOBALS['TSFE']->cObj->getSubpart($templateCode, $m);
			} else {
				break;
			}
		}
		return $out;
	}
	
	/**
	 * returns the subpart name. if 'altMainMarkers.' are given this name is used instead of the default marker-name.
	 *
	 * @param	string		$subpartMarker : name of the subpart to be substituted
	 * @return	string		new name of the template subpart
	 */
	function spMarker($subpartMarker) {
		$sPBody = substr($subpartMarker, 3, -3);
		$altSPM = '';
		if (isset($this->conf['altMainMarkers.'])) {
			$altSPM = trim($this->cObj->stdWrap($this->conf['altMainMarkers.'][$sPBody], $this->conf['altMainMarkers.'][$sPBody . '.']));
			$GLOBALS['TT']->setTSlogMessage('Using alternative subpart marker for \'' . $subpartMarker . '\': ' . $altSPM, 1);
		}

		return $altSPM?$altSPM:
		$subpartMarker;
	}
	
	/**
	 * builds the XML header (array of markers to substitute)
	 *
	 * @return	array		the filled XML header markers
	 */
	function getXmlHeader() {
		$markerArray = array();

		$markerArray['###SITE_TITLE###'] = $this->conf['displayXML.']['xmlTitle'];
		$markerArray['###SITE_LINK###'] = $this->config['siteUrl'];
		$markerArray['###SITE_DESCRIPTION###'] = $this->conf['displayXML.']['xmlDesc'];
		if(!empty($markerArray['###SITE_DESCRIPTION###'])) {
			if($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
				$markerArray['###SITE_DESCRIPTION###'] = '<tagline>'.$markerArray['###SITE_DESCRIPTION###'].'</tagline>';
			} elseif($this->conf['displayXML.']['xmlFormat'] == 'atom1') {
				$markerArray['###SITE_DESCRIPTION###'] = '<subtitle>'.$markerArray['###SITE_DESCRIPTION###'].'</subtitle>';
			}
		}

		$markerArray['###SITE_LANG###'] = $this->conf['displayXML.']['xmlLang'];
		if($this->conf['displayXML.']['xmlFormat'] == 'rss2') {
			$markerArray['###SITE_LANG###'] = '<language>'.$markerArray['###SITE_LANG###'].'</language>';
		} elseif($this->conf['displayXML.']['xmlFormat'] == 'atom03') {
			$markerArray['###SITE_LANG###'] = ' xml:lang="'.$markerArray['###SITE_LANG###'].'"';
		}
		if(empty($this->conf['displayXML.']['xmlLang'])) {
			$markerArray['###SITE_LANG###'] = '';
		}
		
		$icon = $this->conf['displayXML.']['xmlIcon'];
		if (substr($icon,0,4)=='EXT:')	{	// extension
			list($extKey,$local) = explode('/',substr($icon,4),2);
			$filename='';
			if (strcmp($extKey,'') && t3lib_extMgm::isLoaded($extKey) && strcmp($local,''))	{
				$icon = t3lib_extMgm::siteRelPath($extKey).$local;
			}
		}

		$markerArray['###IMG###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . $icon;
		$imgFile = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/' . $icon;
		$imgSize = is_file($imgFile) ? getimagesize($imgFile): '';

		$markerArray['###IMG_W###'] = $imgSize[0];
		$markerArray['###IMG_H###'] = $imgSize[1];

		$markerArray['###NEWS_WEBMASTER###'] = $this->conf['displayXML.']['xmlWebMaster'];
		$markerArray['###NEWS_MANAGINGEDITOR###'] = $this->conf['displayXML.']['xmlManagingEditor'];

		$selectConf = Array();
		$selectConf['pidInList'] = $this->conf['pidList'];
		// select only normal news (type=0) for the RSS feed. You can override this with other types with the TS-var 'xmlNewsTypes'
		$selectConf['selectFields'] = 'tstamp';
		$selectConf['orderBy'] = 'tstamp DESC';
		$res = $this->cObj->exec_getQuery('tx_cal_event', $selectConf);


		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		// optional tags
		if ($this->conf['displayXML.']['xmlLastBuildDate']) {
			$markerArray['###NEWS_LASTBUILD###'] = '<lastBuildDate>' . date('D, d M Y H:i:s O', $row['tstamp']) . '</lastBuildDate>';
		} else {
			$markerArray['###NEWS_LASTBUILD###'] = '';
		}

		if($this->conf['displayXML.']['xmlFormat'] == 'atom03' ||
		   $this->conf['displayXML.']['xmlFormat'] == 'atom1') {
			$markerArray['###NEWS_LASTBUILD###'] = $this->getW3cDate($row['maxval']);
		}

		if ($this->conf['displayXML.']['xmlWebMaster']) {
			$markerArray['###NEWS_WEBMASTER###'] = '<webMaster>' . $this->conf['displayXML.']['xmlWebMaster'] . '</webMaster>';
		} else {
			$markerArray['###NEWS_WEBMASTER###'] = '';
		}

		if ($this->conf['displayXML.']['xmlManagingEditor']) {
			$markerArray['###NEWS_MANAGINGEDITOR###'] = '<managingEditor>' . $this->conf['displayXML.']['xmlManagingEditor'] . '</managingEditor>';
		} else {
			$markerArray['###NEWS_MANAGINGEDITOR###'] = '';
		}

		if ($this->conf['displayXML.']['xmlCopyright']) {
			if($this->conf['displayXML.']['xmlFormat'] == 'atom1') {
				$markerArray['###NEWS_COPYRIGHT###'] = '<rights>' . $this->conf['displayXML.']['xmlCopyright'] . '</rights>';
			} else {
				$markerArray['###NEWS_COPYRIGHT###'] = '<copyright>' . $this->conf['displayXML.']['xmlCopyright'] . '</copyright>';
			}
		} else {
			$markerArray['###NEWS_COPYRIGHT###'] = '';
		}

		$charset = ($GLOBALS['TSFE']->metaCharset?$GLOBALS['TSFE']->metaCharset:'iso-8859-1');
		if ($this->conf['displayXML.']['xmlDeclaration']) {
			$markerArray['###XML_DECLARATION###'] = trim($this->conf['displayXML.']['xmlDeclaration']);
		} else {
			$markerArray['###XML_DECLARATION###'] = '<?xml version="1.0" encoding="'.$charset.'"?>';
		}

		// promoting TYPO3 in atom feeds, supress the subversion
		$version = explode('.',($GLOBALS['TYPO3_VERSION']?$GLOBALS['TYPO3_VERSION']:$GLOBALS['TYPO_VERSION']));
		unset($version[2]);
		$markerArray['###TYPO3_VERSION###'] = implode($version,'.');

		return $markerArray;
	}
    
         /**
	     * Generates the date format needed for Atom feeds
	     * see: http://www.w3.org/TR/NOTE-datetime (same as ISO 8601)
	     * in php5 it would be so easy: date('c', $row['datetime']);
	     *
	     * @param	integer		the datetime value to be converted to w3c format
	     * @return	string		datetime in w3c format
	     */
	    function getW3cDate($datetime) {
		    $offset = date('Z', $datetime) / 3600;
		    if($offset < 0) {
			    $offset *= -1;
			    if($offset < 10) {
				    $offset = '0'.$offset;
			    }
			    $offset = '-'.$offset;
		    } elseif ($offset == 0) {
			    $offset = '+00';
		    } elseif ($offset < 10) {
			    $offset = '+0'.$offset;
		    } else {
			    $offset = '+'.$offset;
		    }
		    return strftime('%Y-%m-%dT%H:%M:%S', $datetime).$offset.':00';
 	    }
    
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_rssview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_rssview.php']);
}
?>
