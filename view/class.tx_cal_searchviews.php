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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_listview.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_searchviews extends tx_cal_listview {
	
	var $confArr = array();
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
	function drawSearchAllResult(&$master_array, $starttime, $endtime, $searchword, $locationIds) {

		$this->_init($master_array);

		$page = $this->cObj->fileResource($this->conf['view.']['search.']['searchResultAllTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>'.$this->conf['view.']['search.']['searchResultAllTemplate'];
		}

		$rems = array();


		$return = '';
		if(array_key_exists('phpicalendar_event',$master_array)){
			$sims['SEARCHEVENTRESULTS'] = $this->drawSearchEventResult($master_array['phpicalendar_event'], $starttime, $endtime, $searchword, $locationIds, false);
		}
		if(array_key_exists('location',$master_array)){
			$sims['SEARCHLOCATIONRESULTS'] = $this->drawSearchLocationResult($master_array['location'], $searchword, false);
		}
		if(array_key_exists('organizer',$master_array)){
			$sims['SEARCHORGANIZERRESULTS'] = $this->drawSearchOrganizerResult($master_array['organizer'], $searchword, false);
		}
		$sims['heading'] = $this->controller->pi_getLL('l_results');
		$page = $this->controller->replace_tags($sims, $page);
		$this->_finishSearch($page, $rems, $sims, true, 'all');
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_search_view').'"';
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
	function drawSearchEventResult($master_array, $starttime, $endtime, $searchword, $locationIds, $isOnlyResultType=true) {

		$sims = array();
		if (t3lib_extMgm::isLoaded('rlmp_dateselectlib')){
				require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');
				tx_rlmpdateselectlib::includeLib();
				
				/* Only read date selector option if rlmp_dateselectlib is installed */
				$useDateSelector = $this->conf['view.']['event.']['useDateSelector'];
		}

		$dateSelectorConf = array('calConf.' => $this->conf['view.']['search.']['rlmp_dateselectorlib_config.']);

		if($isOnlyResultType){
			$this->_init($master_array);
		}
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event').'"';

		$page = $this->cObj->fileResource($this->conf['view.']['search.']['searchResultEventTemplate']);
		if ($page == '') {
			return '<h3>calendar: no search result template file found:</h3>'.$this->conf['view.']['search.']['searchResultEventTemplate'];
		}

		$languageArray['l_category'] = $this->controller->pi_getLL('l_category');
		$languageArray['category_ids'] = '<option value="">'.$this->controller->pi_getLL('l_all').'</option>';
		$catArrayArray = $this->controller->modelObj->findAllCategories('cal_category_model','tx_cal_category',$this->conf['pidList']);
		$categoryArray = $catArrayArray[0];
		$rememberUid = array();
		$ids = $this->controller->piVars['submit']?array($this->conf['category']):array();
		foreach($categoryArray as $category){
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
		$languageArray['l_location'] = $this->controller->pi_getLL('l_location');
		$languageArray['location_ids'] = '<option  value="">'.$this->controller->pi_getLL('l_all').'</option>';
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
		$locationArray = $this->controller->modelObj->findAllLocations($this->confArr['useLocationStructure']?$this->confArr['useLocationStructure']:'tx_cal_location',$this->conf['pidList']);

		if(is_array($locationArray)){
			foreach($locationArray as $location){
				if($location->getUid()==$this->conf['location']){
					$languageArray['location_ids'] .= '<option value="'.$location->getUid().'" selected="selected">'.$location->getName().'</option>';
				}else{
					$languageArray['location_ids'] .= '<option value="'.$location->getUid().'">'.$location->getName().'</option>';
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

		$languageArray['l_event_start_day'] = $this->controller->pi_getLL('l_event_edit_startdate');
		$languageArray['start_day_selector'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_start_day',$dateSelectorConf) : '';
		$languageArray['l_event_end_day'] = $this->controller->pi_getLL('l_event_edit_enddate');
		$languageArray['end_day_selector'] = $useDateSelector ? tx_rlmpdateselectlib::getInputButton ('event_end_day',$dateSelectorConf) : '';
		$languageArray['l_search_string'] = $this->controller->pi_getLL('l_search_string');
		
		if(!$this->controller->piVars['submit']){
			$languageArray['event_start_day'] = $this->cObj->stdWrap($this->conf['view.']['search.']['event.']['defaultValues.']['start_day'],$this->conf['view.']['search.']['event.']['defaultValues.']['start_day.']);
			$languageArray['event_end_day'] = $this->cObj->stdWrap($this->conf['view.']['search.']['event.']['defaultValues.']['end_day'],$this->conf['view.']['search.']['event.']['defaultValues.']['end_day.']);
		}else{
			if(intval($this->controller->piVars['start_day']) == 0){
				$languageArray['event_start_day'] = strftime($this->conf['view.']['search.']['rlmp_dateselectorlib_config.']['inputFieldDateTimeFormat'],$starttime);
			}else{
				$languageArray['event_start_day'] = intval($this->controller->piVars['start_day']);
			}
			if(intval($this->controller->piVars['end_day']) == 0){
				$languageArray['event_end_day'] = strftime($this->conf['view.']['search.']['rlmp_dateselectorlib_config.']['inputFieldDateTimeFormat'],$endtime);
			}else{
				$languageArray['event_end_day'] = intval($this->controller->piVars['end_day']);
			}
		}
		
		$page = $this->controller->replace_tags($languageArray,$page);

		if($isOnlyResultType){
			$rems['###SEARCHEVENTFORM###'] = $this->cObj->getSubpart($page, '###SEARCHEVENTFORM###');
		}else{
			$rems['###SEARCHEVENTFORM###'] = '';
		}
        
        $rems['###SHOWBOTTOMEVENTS###'] = $this->drawList($master_array, '', $starttime, $endtime);
        $startend=gmstrftime($this->conf['view.']['list.']['eventDateFormat'],$starttime).' - '.gmstrftime($this->conf['view.']['list.']['eventDateFormat'],$endtime);
		$rems['###TITLE###'] = $this->cObj->stdWrap($this->controller->pi_getLL('l_query_for').' '.$this->controller->pi_getLL('l_event'),$this->conf['view.']['search_event.']['title.']); #." (".$startend.")";


		$this->_finishSearch($page, $rems, $sims, $isOnlyResultType, 'event');
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());

		if($isOnlyResultType){
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
		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event_location').'"';

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
						$location->getLocationMarker($subTemplate, $tempSims, $tempRems);

						$wrapped['###LOCATION_LINK###'] = explode('|',$this->getLinkToLocation($location,'|'));
						$subTemplate = $this->cObj->substituteMarkerArrayCached($subTemplate, $tempSims, $tempRems, array());
						$middle .= $this->cObj->substituteMarkerArrayCached($subTemplate, array(), array(), $wrapped);
						$i = ($i == 1) ? 0 : 1;
					}
				}
			}
		}
		$rems['###LOCATION###'] = $middle;

		$sims['###HEADING###'] = $this->controller->pi_getLL('l_query_for').' '.$this->controller->pi_getLL('l_event_location')." '$searchword'";
		$this->_finishSearch($page, $rems, $sims, $isOnlyResultType, 'location');

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

		$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_event_organizer').'"';

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
						$subTemplate = $loop[$i];
						$organizer->getLocationMarker($subTemplate, $tempSims, $tempRems);
						$wrapped['###ORGANIZER_LINK###'] = explode('|',$this->getLinkToOrganizer($organizer,'|'));
						$subTemplate = $this->cObj->substituteMarkerArrayCached($subTemplate, $tempSims, $tempRems, array());
						$middle .= $this->cObj->substituteMarkerArrayCached($subTemplate, array(), array(), $wrapped);
						$i = ($i == 1) ? 0 : 1;
					}
				}
			}
		}

		$rems['###ORGANIZER###'] = $middle;
		$sims['###HEADING###'] = $this->controller->pi_getLL('l_query_for').' '.$this->controller->pi_getLL('l_event_organizer')." '$searchword'";

		$this->_finishSearch($page, $rems, $sims, $isOnlyResultType, 'organizer');

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

	function _finishSearch(&$page, &$rems ,&$sims ,$isOnlyResultType, $searchtype='all'){
		
		
		$sims['###L_SEARCH###'] = $this->controller->pi_getLL('l_search');
		if($isOnlyResultType){
			
			
			if(count($this->conf['view.']['defaultView'])==1){
				$sims['###BACKLINK###'] = '';

			}else{
				$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_back').'"';
				if($this->conf['lastview']){
					$this->controller->addBacklink($sims);
				} else {
					$sims['###BACKLINK###'] = '';
				}
				$GLOBALS['TSFE']->ATagParams = 'title="'.$this->controller->pi_getLL('l_search').'"';
//				if (!empty ($this->conf['page_id'])) {
					$sims['###OTHERSEARCH###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_search'), array ('view' => 'search_all','lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway'], $this->conf['page_id']);
//				} else {
//					$sims['###OTHERSEARCH###'] = $this->controller->pi_linkTP_keepPIvars($this->controller->pi_getLL('l_search'), array ('view' => 'search_all','lastview' => $this->controller->extendLastView()), $this->conf['cache'], $this->conf['clear_anyway']);
//				}
			}

			$sims['###OTHERSEARCH###'] = '';
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