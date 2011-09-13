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
class tx_cal_listview extends tx_cal_base_view {
	
	function drawList(&$master_array, $page='') {

		if($page==''){
			$this->_init($master_array);
	
			$page = $this->cObj->fileResource($this->cObj->conf["view."]["list."]["listTemplate"]);
			if ($page == "") {
				return "<h3>calendar: no list template file found:</h3>".$this->cObj->conf["view."]["list."]["listTemplate"];
			}
		}
		$loop[0] = $this->cObj->getSubpart($page, "###SHOWBOTTOMEVENTS_ODD###");
		$loop[1] = $this->cObj->getSubpart($page, "###SHOWBOTTOMEVENTS_EVEN###");
		$i = 0;

		if(stristr($this->cObj->conf["view"],'search' > "") ){
			$starttime = ($this->controller->piVars['start_day']?$this->controller->piVars['start_day']:$starttime);
			$endtime = ($this->controller->piVars['end_day']?$this->controller->piVars['end_day']:$endtime);
		}else if($this->cObj->conf["view"]=='month'){
			$starttime = date("Ymd", strtotime(date("1 F Y",strtotime($this->cObj->conf['getdate']))));
			$endtime = date("Ymd", strtotime(date("1 F Y",strtotime($this->cObj->conf['getdate']))." +1 month"));
		}else{
			$starttime = date("Ymd", strtotime($this->cObj->conf["view."]["list."]['starttime']));
			$endtime = date("Ymd", strtotime($this->cObj->conf["view."]["list."]['endtime']));
		}
		$count = 0;
		foreach ($this->master_array as $cal_time => $event_times) {
			if (is_array($event_times)) {
				foreach ($event_times as $a_key => $a) {
					if (is_array($a)) {
						foreach ($a as $uid => $val) {
							if(!is_object($val)){
								continue;
							}							
							if((intval($val->getEnddate()) < strtotime($starttime) || intval($val->getStartdate()) > strtotime($endtime))){
								continue;
							}
							$switch = array();
							$rems = array();
							$val->getEventMarker($loop[$i],$rems,$switch);
							$switch['###CALNAME###'] = $val->getCategoriesAsString();
							
							if ($val->getEndtime() == '0' || $val->getStarttime() == $val->getEndtime()) {
								$switch['###START_TIME###'] = $this->shared->lang('l_all_day');
								$switch['###EVENT_TEXT###'] = $this->getLinkToEvent($val, $val->renderEventForMonth(),"list", date("Ymd",$val->getStarttime()));
							} else {
								$event_start = date($this->shared->lang('l_timeFormat'), $val->getStarttime());
								$event_end = date($this->shared->lang('l_timeFormat'), $val->getEndtime());
								
								
								$switch['###START_TIME###'] = $event_start.' - '.$event_end;
								$switch['###EVENT_TEXT###'] = $this->getLinkToEvent($val, $val->renderEventForMonth(),"list", date("Ymd",$val->getStarttime()));
							}
							

							
							if ($switch['###EVENT_TEXT###'] != '') {
								$temp = $loop[$i];
								$sims = array();
								$val->getImageMarkers($sims, $this->cObj->conf['view.']['list.'],false);
								$temp = $this->cObj->substituteMarkerArrayCached($temp, $sims, array(), array());
								if($val->getStartDate()!=$val->getEndDate()){
									$switch['###DAYLINK###'] = tx_cal_calendar :: localizeDate($this->cObj->conf['view.']['list.']['dateFormat']? $this->cObj->conf['view.']['list.']['dateFormat'] : $this->shared->lang('l_dateFormat_week_list'), $val->getStartdate(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
									if ($val->getEndtime() == '0'){
										$switch['###DAYLINK###'] .= " - ".tx_cal_calendar :: localizeDate($this->cObj->conf['view.']['list.']['dateFormat']? $this->cObj->conf['view.']['list.']['dateFormat'] : $this->shared->lang('l_dateFormat_week_list'), $val->getEndtime(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
									}else{
										$switch['###DAYLINK###'] .= "  ".date($this->shared->lang('l_timeFormat'), $val->getStarttime())." -"; 
										$switch['###START_TIME###'] = tx_cal_calendar :: localizeDate($this->cObj->conf['view.']['list.']['dateFormat']? $this->cObj->conf['view.']['list.']['dateFormat'] : $this->shared->lang('l_dateFormat_week_list'), $val->getEnddate(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort()).
											"  ".date($this->shared->lang('l_timeFormat'), $val->getEndtime());
									}
									
								}else{
									$switch['###DAYLINK###'] = tx_cal_calendar :: localizeDate($this->cObj->conf['view.']['list.']['dateFormat']? $this->cObj->conf['view.']['list.']['dateFormat'] : $this->shared->lang('l_dateFormat_week_list'), $val->getStarttime(), $this->shared->getDaysOfWeek(), $this->shared->getDaysOfWeekShort(), $this->shared->getDaysOfWeekReallyShort(), $this->shared->getMonthsOfYear(), $this->shared->getMonthsOfYearShort());
								}
					
								$middle .= $this->cObj->substituteMarkerArrayCached($temp, $rems, $switch, array());
								$i = ($i == 1) ? 0 : 1;
							}
							$count ++;
							if ($count == $this->cObj->conf['view.']['list.']['maxEvents']) {
								$rems["###SHOWBOTTOMEVENTS###"] = $middle;
								return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
							}
						}
					}
				}
			}
		}
		if(!$middle){
			$middle = $this->shared->lang('l_no_events');
		}

		$rems["###SHOWBOTTOMEVENTS###"] = $middle;
		return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']);
}
?>