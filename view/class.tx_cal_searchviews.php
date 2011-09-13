<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2007 Mario Matzulla
 * (c) 2005-2007 Foundation for Evangelism
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
 * (http://evangelize.org). The WEC is developing TYPO3-based
 * (http://typo3.org) free software for churches around the world. Our desire
 * is to use the Internet to help offer new life through Jesus Christ. Please
 * see http://WebEmpoweredChurch.org/Jesus.
 *
 * You can redistribute this file and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation;
 * either version 2 of the License, or (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This file is distributed in the hope that it will be useful for ministry,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the file!
 ***************************************************************/

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_listview.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_searchviews extends tx_cal_listview {
	
	var $confArr = array();
	
	function tx_cal_searchviews(){
		$this->tx_cal_listview();
	}
	
	/**
	 *  Draws a single event.
	 *  @param		array			The events to be drawn.
	 *	 @return		string		The HTML output.
	 */
	function drawSearch(&$master_array, $getdate) {
		$this->_init($master_array);

		$page = $this->cObj->fileResource($this->conf['view.']['other.']['searchBoxTemplate']);
		if ($page == '') {
			return '<h3>calendar: no template file found:</h3>'.$this->conf['view.']['other.']['searchBoxTemplate'];
		}
		return $this->finish($page,array());
	}

	/**  Draws a search result view.
	 *  @param      object      Array of the events found ()
	 *	@return		string		The HTML output.
	 */
	function drawSearchAllResult(&$master_array, $starttime, $endtime, $searchword, $locationIds='', $organizerIds='') {

		$this->_init($master_array);

		$page = $this->cObj->fileResource($this->conf['view.']['search.']['searchResultAllTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>'.$this->conf['view.']['search.']['searchResultAllTemplate'];
		}

		$rems = array();


		$return = '';
		if(array_key_exists('phpicalendar_event',$master_array)){
			$sims['SEARCHEVENTRESULTS'] = $this->drawSearchEventResult($master_array['phpicalendar_event'], $starttime, $endtime, $searchword, $locationIds, $organizerIds, false);
		}
		if(array_key_exists('location',$master_array)){
			$sims['SEARCHLOCATIONRESULTS'] = $this->drawSearchLocationResult($master_array['location'], $searchword, false);
		}
		if(array_key_exists('organizer',$master_array)){
			$sims['SEARCHORGANIZERRESULTS'] = $this->drawSearchOrganizerResult($master_array['organizer'], $searchword, false);
		}
		$sims['heading'] = $this->controller->pi_getLL('l_results');
		$this->getBackLinkMarker($page, $sims, $rems, $wrapped = array());
		$page = $this->controller->replace_tags($sims, $page);
		$this->_finishSearch($page, $sims, $rems, true, 'all');

		$parameter = array ('view' => 'search_all', 'lastview' => $this->controller->extendLastView(), 'getdate' => $this->conf['getdate']);
		$sims['search_action_url'] = $this->controller->pi_linkTP_keepPIvars_url($parameter);
		$page = $this->controller->replace_tags($sims, $page);
		return $this->finish($page,$rems);
	}

	/**
	 *  Draws a search result view.
	 *  @param      object      The events found
	 *	@return		string		The HTML output.
	 */
	function drawSearchEventResult(&$master_array, $starttime, $endtime, $searchword, $locationIds='', $organizerIds='', $isOnlyResultType=true) {
		$sims = array();
		$useDateSelector = false;
		if (t3lib_extMgm::isLoaded('rlmp_dateselectlib')){
			require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');
			tx_rlmpdateselectlib::includeLib();
				
			/* Only read date selector option if rlmp_dateselectlib is installed */
			$useDateSelector = $this->conf['view.'][$this->conf['view'].'.']['event.']['useDateSelector'];
		}
		
		$outputFormat = getFormatStringFromConf($this->conf);

		$dateSelectorConf = array('calConf.' => $this->conf['view.'][$this->conf['view'].'.']['event.']['rlmp_dateselectorlib_config.']);

		$dateSelectorConf['calConf.']['dateTimeFormat']	= $outputFormat;
		$dateSelectorConf['calConf.']['inputFieldDateTimeFormat'] = $outputFormat;

		if($isOnlyResultType){
			$this->_init($master_array);
		}
		#$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event').'"';

		$page = $this->cObj->fileResource($this->conf['view.']['search.']['searchResultEventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>'.$this->conf['view.']['search.']['searchResultEventTemplate'];
		}

		$languageArray['l_category'] = $this->controller->pi_getLL('l_category');
		$languageArray['category_ids'] = '<option value="">'.$this->controller->pi_getLL('l_all').'</option>';
		$catArrayArray = $this->modelObj->findAllCategories('cal_category_model','tx_cal_category',$this->conf['pidList']);

		$rememberUid = array();
		$ids = array();
		if($this->controller->piVars['submit'] && $this->controller->piVars['category']){
			$ids = $this->controller->piVars['category'];
		}

		foreach($catArrayArray as $categoryArrayFromService){
			foreach($categoryArrayFromService[0][0] as $category){
				$uid = $category->getUid();
				if(!in_array($uid, $rememberUid)){
					if(in_array($uid,$ids)){
						$languageArray['category_ids'] .= '<option value="'.$uid.'" selected="selected">'.$category->getTitle().'</option>';
					}else{
						$languageArray['category_ids'] .= '<option value="'.$uid.'" >'.$category->getTitle().'</option>';
					}
					$rememberUid[] = $uid;
				}
			}
		}

		$languageArray['l_location'] = $this->controller->pi_getLL('l_location');
		$languageArray['location_ids'] = '<option  value="">'.$this->controller->pi_getLL('l_all').'</option>';
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$locationArray = $this->modelObj->findAllLocations($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location',$this->conf['pidList']);

		$locationIdArray = Array();
		if($locationIds!=''){
			$locationIdArray = t3lib_div::intExplode(',',$locationIds);
		}
		
		if(is_array($locationArray)){
			foreach($locationArray as $location){
				if(in_array($location->getUid(),$locationIdArray)){
					$languageArray['location_ids'] .= '<option value="'.$location->getUid().'" selected="selected">'.$location->getName().'</option>';
				}else{
					$languageArray['location_ids'] .= '<option value="'.$location->getUid().'">'.$location->getName().'</option>';
				}
			}
		}
		
		$languageArray['l_organizer'] = $this->controller->pi_getLL('l_organizer');
		$languageArray['organizer_ids'] = '<option  value="">'.$this->controller->pi_getLL('l_all').'</option>';
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$organizerArray = $this->modelObj->findAllOrganizer($this->confArr['useOrganizerStructure']?$this->confArr['useOrganizerStructure']:'tx_cal_organizer',$this->conf['pidList']);
		
		$organizerIdArray = Array();
		if($organizerIds!=''){
			$organizerIdArray = t3lib_div::intExplode(',',$organizerIds);
		}
		
		if(is_array($organizerArray)){
			foreach($organizerArray as $organizer){
				if(in_array($organizer->getUid(),$organizerIdArray)){
					$languageArray['organizer_ids'] .= '<option value="'.$organizer->getUid().'" selected="selected">'.$organizer->getName().'</option>';
				}else{
					$languageArray['organizer_ids'] .= '<option value="'.$organizer->getUid().'">'.$organizer->getName().'</option>';
				}
			}
		}
		$languageArray['view'] = 'search_event';

		$start_time_hour = intval($this->controller->piVars['start_hour']);
		$start_time_minute = intval($this->controller->piVars['start_minutes']);

		$end_time_hour = intval($this->controller->piVars['end_hour']);
		$end_time_minute = intval($this->controller->piVars['end_minutes']);

		for ($i=0;$i<24;$i++) {
			$value = str_pad($i, 2, '0', STR_PAD_LEFT);
			$start_hours .= '<option value="'.$value.'"'.($start_time_hour==$value?' selected="selected"':'').'>'.$value.'</option>';
			$end_hours .= '<option value="'.$value.'"'.($end_time_hour==$value?' selected="selected"':'').'>'.$value.'</option>';
		}

		$start_minutes = '';
		$end_minutes = '';
		for ($i=0;$i<60;$i++) {
			$value = str_pad($i, 2, '0', STR_PAD_LEFT);
			$start_minutes .= '<option value="'.$value.'"'.($start_time_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
			$end_minutes .= '<option value="'.$value.'"'.($end_time_minute==$value?' selected="selected"':'').'>'.$value.'</option>';
		}
		$languageArray['start_hours'] = $start_hours;
		$languageArray['end_hours'] = $end_hours;
		$languageArray['start_minutes'] = $start_minutes;
		$languageArray['end_minutes'] = $end_minutes;

		$languageArray['single_date_selector'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('single_date',$dateSelectorConf) : '';
		$languageArray['l_event_start_day'] = $this->controller->pi_getLL('l_event_edit_startdate');
		$languageArray['start_day_selector'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_start_day',$dateSelectorConf) : '';
		$languageArray['l_event_end_day'] = $this->controller->pi_getLL('l_event_edit_enddate');
		$languageArray['end_day_selector'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_end_day',$dateSelectorConf) : '';
		$languageArray['l_search_string'] = $this->controller->pi_getLL('l_search_string');
		
		$inputFormat = getFormatStringFromConf($this->conf);
		

		if(!$this->controller->piVars['submit']){
			$date = $this->controller->getListViewTime($this->conf['view.']['search.']['defaultValues.']['start_day']);
			$languageArray['event_start_day'] = $date->format($outputFormat);
			$date = $this->controller->getListViewTime($this->conf['view.']['search.']['defaultValues.']['end_day']);
			$languageArray['event_end_day'] = $date->format($outputFormat);
		}else{
			if(intval($this->controller->piVars['start_day']) == 0){
				$languageArray['event_start_day'] = $starttime->format($outputFormat);
			}else{
				$languageArray['event_start_day'] = strip_tags($this->controller->piVars['start_day']);
			}
			
			if(intval($this->controller->piVars['end_day']) == 0){
				$languageArray['event_end_day'] = $endtime->format($outputFormat);
			}else{
				$languageArray['event_end_day'] = strip_tags($this->controller->piVars['end_day']);
			}
		}

		$page = $this->controller->replace_tags($languageArray,$page);

		if($isOnlyResultType){
			$rems['###SEARCHEVENTFORM###'] = $this->cObj->getSubpart($page, '###SEARCHEVENTFORM###');
		}else{
			$rems['###SEARCHEVENTFORM###'] = '';
		}

		$rems['###LIST###'] = $this->drawList($master_array, '', $starttime, $endtime);

		if($isOnlyResultType){
			$rems['###TITLE###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_query_for').' '.$this->controller->pi_getLL('l_event'),$this->conf['view.']['search_event.']['title.']);
		} else {
			$rems['###TITLE###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_events'),$this->conf['view.']['search_event.']['title.']);
		}

		$this->_finishSearch($page, $sims, $rems, $isOnlyResultType, 'event');
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());

		if($isOnlyResultType){				
			$parameter = array ('view' => 'search_event', 'lastview' => $this->controller->extendLastView(), 'getdate' => $this->conf['getdate']);
			$urlsims['###SEARCH_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url($parameter);
			$page = $this->cObj->substituteMarkerArrayCached($page, $urlsims, array(), array ());
			$rems = array();
			return $this->finish($page,$rems);
		}

		return $page;
	}

	/**  Draws a search result view.
	 *  @param      object      The location found
	 *	@return		string		The HTML output.
	 */
	function drawSearchLocationResult(&$master_array, $searchword, $isOnlyResultType=true) {
		$sims = array ();
		if($isOnlyResultType){
			$this->_init($master_array);
		}
		#$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event_location').'"';

		if(count($master_array,1)==2) // only one object element in the array
		{
			return $this->drawLocation(array_pop(array_pop($master_array)), $this->conf['getdate']);
		}
		$page = $this->cObj->fileResource($this->conf['view.']['search.']['searchResultLocationTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>'.$this->conf['view.']['search.']['searchResultLocationTemplate'];
		}
		
		

		if($isOnlyResultType){
			$rems['###SEARCHLOCATIONFORM###'] = $this->cObj->getSubpart($page, '###SEARCHLOCATIONFORM###');
		}else{
			$rems['###SEARCHLOCATIONFORM###'] = '';
		}
		
		$loop[0] = $this->cObj->getSubpart($page, '###LOCATION_ODD###');
		$loop[1] = $this->cObj->getSubpart($page, '###LOCATION_EVEN###');
		$i = 0;
		$count = 0;
		$middle = '';
		if (is_array($master_array)) {
			foreach ($master_array as $a => $b) {
				if (is_array($b)) {
					foreach ($b as $id => $location) {
						$tempSims = array();
						$tempRems = array();
						$subTemplate = $loop[$i];
						$wrapped = array();
						$location->getMarker($subTemplate, $tempSims, $tempRems, $wrapped);
						$middle .= $this->cObj->substituteMarkerArrayCached($subTemplate, $tempSims, $tempRems, $wrapped);
						$i = ($i == 1) ? 0 : 1;
					}
				}
			}
		}
		
		if(!$middle) {
			$middle = '<li>'.$this->controller->pi_getLL('l_no_location_results').'</li>';
		}

		$rems['###LOCATION###'] = $middle;
		if($isOnlyResultType){
			$rems['###HEADING###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_query_for').' '.$this->controller->pi_getLL('l_event_location'), $this->conf['view.']['search_location.']['title.']);
			
		} else {
			$rems['###HEADING###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_locations'), $this->conf['view.']['search_location.']['title.']);
		}
		
		$this->_finishSearch($page, $sims, $rems, $isOnlyResultType, 'location');

		if($isOnlyResultType){
			$parameter = array ('view' => 'search_location', 'lastview' => $this->controller->extendLastView(), 'getdate' => $this->conf['getdate']);
			$urlsims['###SEARCH_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url($parameter);
			$rems['###SEARCHLOCATIONFORM###'] = $this->cObj->substituteMarkerArrayCached($rems['###SEARCHLOCATIONFORM###'], $urlsims, array(), array ());
			$page = $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());
			$rems = array();
			return $this->finish($page,$rems);
		}
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());
	}

	/**  Draws a search result view.
	 *  @param      object      The organizer found
	 *	@return		string		The HTML output.
	 */
	function drawSearchOrganizerResult(&$master_array, $searchword, $isOnlyResultType=true) {

		$sims = array();
		if($isOnlyResultType){
			$this->_init($master_array);
		}

		#$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event_organizer').'"';

		if(count($master_array,1)==2) // only one object element in the array
		{
			return $this->drawOrganizer(array_pop(array_pop($master_array)), $this->conf['getdate']);
		}
		$page = $this->cObj->fileResource($this->conf['view.']['search.']['searchResultOrganizerTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>'.$this->conf['view.']['search.']['searchResultOrganizerTemplate'];
		}

		if($isOnlyResultType){
			$rems['###SEARCHORGANIZERFORM###'] = $this->cObj->getSubpart($page, '###SEARCHORGANIZERFORM###');
		}else{
			$rems['###SEARCHORGANIZERFORM###'] = '';
		}

		$loop[0] = $this->cObj->getSubpart($page, '###ORGANIZER_ODD###');
		$loop[1] = $this->cObj->getSubpart($page, '###ORGANIZER_EVEN###');
		$i = 0;
		$count = 0;
		if (is_array($master_array)) {
			foreach ($master_array as $a => $b) {
				if (is_array($b)) {
					foreach ($b as $id => $organizer) {
						$tempSims = array();
						$tempRems = array();
						$wrapped = array();
						$subTemplate = $loop[$i];
						$organizer->getMarker($subTemplate, $tempSims, $tempRems,$wrapped);
						$middle .= $this->cObj->substituteMarkerArrayCached($subTemplate, $tempSims, $tempRems, $wrapped);
						$i = ($i == 1) ? 0 : 1;
					}
				}
			}
		}
		
		if(!$middle) {
			$middle = '<li>'.$this->controller->pi_getLL('l_no_organizer_results').'</li>';
		}
		
		$rems['###ORGANIZER###'] = $middle;
		if($isOnlyResultType){
			$rems['###HEADING###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_query_for').' '.$this->controller->pi_getLL('l_event_organizer'), $this->conf['view.']['search_organizer.']['title.']);
		} else {
			$rems['###HEADING###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_organizers'),$this->conf['view.']['search_organizer.']['title.']);
			
		}
		$this->_finishSearch($page, $sims, $rems, $isOnlyResultType, 'organizer');

		if($isOnlyResultType){				
			$parameter = array ('view' => 'search_organizer', 'lastview' => $this->controller->extendLastView(), 'getdate' => $this->conf['getdate']);
			$urlsims['###SEARCH_ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url($parameter);
			$rems['###SEARCHORGANIZERFORM###'] = $this->cObj->substituteMarkerArrayCached($rems['###SEARCHORGANIZERFORM###'], $urlsims, array(), array ());
			$page = $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());
			$rems = array();
			return $this->finish($page,$rems);
		}
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());
	}

	function _finishSearch(&$page, &$sims ,&$rems ,$isOnlyResultType, $searchtype='all'){
		$sims['###L_SEARCH###'] = $this->controller->pi_getLL('l_search');
		if($isOnlyResultType){

			if(count($this->conf['view.']['allowedViews']) > 1) {
				$this->initLocalCObject();
				$this->local_cObj->setCurrentVal($this->controller->pi_getLL('l_search'));
				$this->local_cObj->data['link'] = $this->controller->pi_linkTP_keepPIvars_url(array ('view' => 'search_all','lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['page_id']);
				$sims['###OTHERSEARCH###'] = $this->local_cObj->cObjGetSingle($this->conf['view.']['search.']['searchAllLink'],$this->conf['view.']['search.']['searchAllLink.']);
			} else {
				$sims['###OTHERSEARCH###'] = '';
			}
			$rems['###LINKS###'] =  $this->cObj->getSubpart($page, '###LINKS###');
			$rems['###LINKS###'] = $this->cObj->substituteMarkerArrayCached($rems['###LINKS###'], $sims, array (), array ());
		}else{
			$rems['###LINKS###'] = '';
		}
		$sims['###QUERY###'] = strip_tags($this->controller->piVars['query']);
		$sims['###GETDATE###'] = $this->conf['getdate'];
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_searchviews.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_searchviews.php']);
}
?>
