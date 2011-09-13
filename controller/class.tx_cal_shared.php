<?php
	 
/***************************************************************
* Copyright notice
*
* (c) 2005 Mario Matzulla (mario(at)matzullas.de)
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

require_once (PATH_tslib.'class.tslib_pibase.php');

/**
 * This class serves as a base for classes without pibase relation
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_shared extends tslib_pibase {
	
	var $cObj;
	var $prefixId = 'tx_cal_controller';
	var $controller;
	
	function tx_cal_shared($cObj){
		$this->cObj = &$cObj;
		$this->controller = &$this->cObj->conf[$this->prefixId];
	}

	function encode($string) {
		// encode the uid with the secret word to ensure that this is coming from the
		// forum. -- this isn't perfect; needs to be thought about a bit more.
		$tmp = array('key' => md5($string.$this->cObj->conf['forum_pw']), 'value' => $string);
		$tmp = serialize($tmp);
		$tmp = base64_encode($tmp);
		return $tmp;
	}

 /**
	* Makes a hash out of string -- used to obscure variable data passed through URL.
	* Not really doing much at this point, but might be in the future.
	*
	* @param string $string:  string that gets hashed.
	* @return string hashed $string.
	*/
	function makeHash($string) {
		#$hash = base64_encode(serialize($string));
		#return $hash;
		return $string;
	}
		
 /** 
	* Unmakes hash created by previous function -- used to decypher hashed variables passed through URL.
	* Not really doing much at this point, but might be in the future.
	*
	* @param string $hash:  string that gets de-hashed.
	* @return string de-hashed $hash.
	*/
	function unmakeHash($hash) {
		#$string = unserialize(base64_decode($hash));
		#return $string;
		return $hash;
	}


 /**		 
	* Wrapper for typo3 pi_getLL function. Returns value from local_lang file corresponding to $key.
	*
	* @param string $key:  the key in the locallang file to look up.
	* @return string the text from the local_lang file.
	*/
	function lang($key) {
		if (!$this->LOCAL_LANG) 
			$this->LOCAL_LANG = $this->cObj->ux_language;
		$this->LLkey = $this->cObj->ux_llkey;			
		return $this->pi_getLL("$key");
	}

 /**
	* Makes a link to the current page with the post vars $params and text $title.
	*
	* @param array  $params:  an array of parameters for the link. eg. 'view' => 'single_conf', 'conf_uid' => 3, etc.
	* @param string  $title:  the link text.
	* @param string  $attr:  any attributes that should be inserted into the link HTML.
	* @return string the HTML for the link
	*/
	function makeLink($params = false, $title = false, $attr = false, $url_only = false) {
		if ($this->cObj->conf) {
			$pid = $this->cObj->data[pid];
		} else {
			$this->cObj->conf = $this->cObj->conf;
			$pid = $this->cObj->data[pid];
		}

		// add URL parameters sent via typoscript. Used for forum / tt_news
		// integration. Thanks Rupi!
		if ($this->cObj->conf['chcAddParams']) {
			$params = array_merge($params,tx_chcforum_shared::getAddParams($this->cObj->conf['chcAddParams']));
		}

		$url = htmlspecialchars($this->cObj->getTypoLink_URL($pid,$params)); // run it through special chars for XHTML compliancy
		$out = '<a href="'.$url.'" '.$attr.'>'.$title.'</a>' ;
		if ($url_only == true) {
			return $url;
		} else {
			return $out;
		}
	}
	 
	/**
	* Returns the HTML needed for a linked image.
	*
	* @param array  $params: an array of parameters for the link. eg. 'view' => 'single_conf', 'conf_uid' => 3, etc.
	* @param string  $img_file_path: the path to the image -- relative or absolute.
	* @param string  $attr: any attributes that should be inserted into the link HTML.
	* @return string the HTML for the image link
	*/
	function makeImageLink($params, $img_file_path, $attr = false, $alt = false) {
		$img_html = '<img alt="'.$alt.'" title="imagelink" class="tx-chcforum-pi1-buttonPadding" border="0" src="'.$img_file_path.'" />';
		$link = tx_chcforum_shared::makeLink($params, $img_html, $attr);
		return $link;
	}
	 
	/**
	* Used to get the template path from fconf table. Should be called before accessing
	* any template via tpower -- put it in an if statemet, so that if $this->tmpl_path is
	* alread set, this won't get called again.
	* eg.: if (!this->tmpl_path) $this->tmpl_path = tx_chcforum_shared::setTemplatePath();
	*
	* @return string  correct path to the template file
	*/
	function setTemplatePath() {
		if ($this->fconf['tmpl_path'] && t3lib_div::validPathStr($this->fconf['tmpl_path'])) {
			$tmpl_path = t3lib_div::getFileAbsFileName(t3lib_div::fixWindowsFilePath($this->fconf['tmpl_path']));
			if (!file_exists($tmpl_path)) $tmpl_path = t3lib_extMgm::extPath('cal').'/templates/';
		} else {
			$tmpl_path = t3lib_extMgm::extPath('cal').'/templates/';
		}
		return $tmpl_path;
	}	
		
	/**
 	* Returns an array with additional Link parameters
	* 
	* @param string  $addParamsList: comma-seperated list of parameters (from TS-setup) that will be added to all forum links.
	* @return array additional link parameters in an array
	*/
	 function getAddParams($addParamsList){
	 	$queryString = explode('&', t3lib_div::implodeArrayForUrl('', $GLOBALS['_GET'])) ;
		if ($queryString) {
			while (list(, $val) = each($queryString)) {
				$tmp = explode('=', $val); 
				$paramArray[$tmp[0]] = $tmp[1];
			} 
			while (list($pk, $pv) = each($paramArray)) {
				if (t3lib_div::inList($addParamsList, $pk)) {
					$addParamArray[$pk]=$pv ;
				} 
			} 
		}
		return $addParamArray;
	 }
	
	/**
	 * Link a string to some page.
	 * Like pi_getPageLink() but takes a string as first parameter which will in turn be wrapped with the URL including target attribute
	 * Simple example: $this->pi_linkToPage('My link', 123) to get something like <a href="index.php?id=123&type=1">My link</a> (or <a href="123.1.html">My link</a> if simulateStaticDocuments is set)
	 *
	 * @param	string		The content string to wrap in <a> tags
	 * @param	array		Additional URL parameters to set (key/value pairs)
	 * @param	integer		Page id
	 * @param	string		Target value to use. Affects the &type-value of the URL, defaults to current.
	 * @return	string		The input string wrapped in <a> tags with the URL and target set.
	 * @see pi_getPageLink(), tslib_cObj::getTypoLink()
	 */
//	function pi_linkToPage($str,$urlParameters=array(),$cache=0, $altPageId=0)	{
//		return $this->controller->pi_linkTP_keepPIvars($str,$urlParameters,$cache,0,$altPageId);	// ?$target:$GLOBALS['TSFE']->sPre
//	}
	
		function getFormat_recur_lang_($var) {
		$return = array();
		$return[0] = $this->shared->lang('l_format_recur_lang_'.$var.'_single');
		$return[1] = $this->shared->lang('l_format_recur_lang_'.$var.'_multiple');
		return $return;	
	}
	
	function getDaysOfWeek() {
		$return = array();
		$return[0] = $this->lang('l_daysofweek_lang_sunday');
		$return[1] = $this->lang('l_daysofweek_lang_monday');
		$return[2] = $this->lang('l_daysofweek_lang_tuesday');
		$return[3] = $this->lang('l_daysofweek_lang_wednesday');
		$return[4] = $this->lang('l_daysofweek_lang_thursday');
		$return[5] = $this->lang('l_daysofweek_lang_friday');
		$return[6] = $this->lang('l_daysofweek_lang_saturday');
		return $return;
	}
	
	function getDaysOfWeekShort() {
		$return = array();
		$return[0] = $this->lang('l_daysofweekshort_lang_sun');
		$return[1] = $this->lang('l_daysofweekshort_lang_mon');
		$return[2] = $this->lang('l_daysofweekshort_lang_tue');
		$return[3] = $this->lang('l_daysofweekshort_lang_wed');
		$return[4] = $this->lang('l_daysofweekshort_lang_thu');
		$return[5] = $this->lang('l_daysofweekshort_lang_fri');
		$return[6] = $this->lang('l_daysofweekshort_lang_sat');
		return $return;
	}
	
	function getDaysOfWeekReallyShort() {
		$return = array();
		$return[0] = $this->lang('l_daysofweekreallyshort_lang_sun');
		$return[1] = $this->lang('l_daysofweekreallyshort_lang_mon');
		$return[2] = $this->lang('l_daysofweekreallyshort_lang_tue');
		$return[3] = $this->lang('l_daysofweekreallyshort_lang_wed');
		$return[4] = $this->lang('l_daysofweekreallyshort_lang_thu');
		$return[5] = $this->lang('l_daysofweekreallyshort_lang_fri');
		$return[6] = $this->lang('l_daysofweekreallyshort_lang_sat');
		return $return;
	}
	
	function getMonthsOfYear() {
		$return = array();
		$return[0] = $this->lang('l_monthsofyear_lang_January');
		$return[1] = $this->lang('l_monthsofyear_lang_February');
		$return[2] = $this->lang('l_monthsofyear_lang_March');
		$return[3] = $this->lang('l_monthsofyear_lang_April');
		$return[4] = $this->lang('l_monthsofyear_lang_May');
		$return[5] = $this->lang('l_monthsofyear_lang_June');
		$return[6] = $this->lang('l_monthsofyear_lang_July');
		$return[7] = $this->lang('l_monthsofyear_lang_August');
		$return[8] = $this->lang('l_monthsofyear_lang_September');
		$return[9] = $this->lang('l_monthsofyear_lang_October');
		$return[10] = $this->lang('l_monthsofyear_lang_November');
		$return[11] = $this->lang('l_monthsofyear_lang_December');
		return $return;
	}
	
	function getMonthsOfYearShort() {
		$return = array();
		$return[0] = $this->lang('l_monthsofyearshort_lang_Jan');
		$return[1] = $this->lang('l_monthsofyearshort_lang_Feb');
		$return[2] = $this->lang('l_monthsofyearshort_lang_Mar');
		$return[3] = $this->lang('l_monthsofyearshort_lang_Apr');
		$return[4] = $this->lang('l_monthsofyearshort_lang_May');
		$return[5] = $this->lang('l_monthsofyearshort_lang_Jun');
		$return[6] = $this->lang('l_monthsofyearshort_lang_Jul');
		$return[7] = $this->lang('l_monthsofyearshort_lang_Aug');
		$return[8] = $this->lang('l_monthsofyearshort_lang_Sep');
		$return[9] = $this->lang('l_monthsofyearshort_lang_Oct');
		$return[10] = $this->lang('l_monthsofyearshort_lang_Nov');
		$return[11] = $this->lang('l_monthsofyearshort_lang_Dec');
		return $return;
	}
	
	
	function replace_tags($tags = array(), $page) 
	{
		if (sizeof($tags) > 0) 
		{
			$sims = array();
			foreach ($tags as $tag => $data) 
			{	
				// This replaces any tags
				$sims['###' . strtoupper($tag) . '###'] = $this->cObj->substituteMarkerArrayCached($data,'###' . strtoupper($tag) . '###', array(),array());			
			}

			$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array());

		}
		else
		{
			//die('No tags designated for replacement.');
		}
		return $page;
		
	}
	function getLinkvarsForLink($prefixId){
		$linkVars = t3lib_div :: GPvar($prefixId);
		$parameter = array();
		if(is_array($linkVars)){
			foreach($linkVars as $key => $value){
				$parameter[$prefixId."[".$key."]"] = $value;
			}
		}
		return $parameter;
	}
	
	function getHourFromTime($time) {
		$time = str_replace(':', '', $time);
		if ($time) {
			$retVal = substr($time, 0, 2);
		}
		return $retVal;
	}
	function getMinutesFromTime($time) {
		$time = str_replace(':', '', $time);
		if ($time) {
			$retVal = substr($time, 2, 2);
		}
		return $retVal;
	}
}

	

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_shared.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/controller/class.tx_cal_shared.php']);
}
	 
?>