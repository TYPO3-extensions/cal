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
	
			$page = $this->cObj->fileResource($this->conf['view.']['list.']['listTemplate']);
			if ($page == '') {
				return '<h3>calendar: no list template file found:</h3>'.$this->conf['view.']['list.']['listTemplate'];
			}
		}

		$loop[0] = $this->cObj->getSubpart($page, '###SHOWBOTTOMEVENTS_ODD###');
		$loop[1] = $this->cObj->getSubpart($page, '###SHOWBOTTOMEVENTS_EVEN###');
		$i = 0;
		
		$day_array2 = array();
		ereg('([0-9]{4})([0-9]{2})([0-9]{2})', $this->conf['getdate'], $day_array2);
		$this_day = $day_array2[3];
		$this_month = $day_array2[2];
		$this_year = $day_array2[1];
		$unix_time = gmmktime(0,0,0,$this_month,$this_day,$this_year);

		if(stristr($this->conf['view'],'search') ){
			$starttime = strtotime(($this->controller->piVars['start_day']?intval($this->controller->piVars['start_day']):'19700102')) - strtotimeOffset();
			$endtime = strtotime(($this->controller->piVars['end_day']?intval($this->controller->piVars['end_day']):'20300101')) - strtotimeOffset();
		}else if($this->conf['view']=='month'){
			$starttime = gmmktime(0,0,0,$this_month,1,$this_year);
			$endtime = gmmktime(0,0,0,$this_month+1,1,$this_year);
		}else if($this->conf['view.']['list.']['useGetdate']){
			$starttime = $unix_time;
			$endtime = $unix_time + 86400;
		}else{
			$starttime = strtotime($this->conf['view.']['list.']['starttime']) - strtotimeOffset();
			$endtime = strtotime($this->conf['view.']['list.']['endtime']) - strtotimeOffset();
		}
		
		$count = 0;
		foreach ($master_array as $cal_time => $event_times) {
			if (is_array($event_times)) {
				foreach ($event_times as $a_key => $a) {
					if (is_array($a)) {
						foreach ($a as $uid => $val) {
							$subTemplate = $loop[$i];
							if(!is_object($val)){
								continue;
							}						
							if((intval($val->getEnddate()) < $starttime || intval($val->getStartdate()) > $endtime)){
								continue;
							}
							$switch = array();
							$rems = array();
							$val->getEventMarker($subTemplate,$rems,$switch);

							$wraped['###EVENT_LINK###'] = explode('|',$this->getLinkToEvent($val, '|',$this->conf['view'], gmdate('Ymd',$val->getStarttime())));
				
							$middle .= $this->cObj->substituteMarkerArrayCached($subTemplate, $rems, $switch, $wraped);
							$i = ($i == 1) ? 0 : 1;

							$count ++;
							if ($count == $this->conf['view.']['list.']['maxEvents']) {
								$rems['###SHOWBOTTOMEVENTS###'] = $middle;
								return $this->cObj->substituteMarkerArrayCached($page, array (), $rems, array ());
							}
						}
					}
				}
			}
		}
		if(!$middle){
			$middle = $this->cObj->stdWrap($this->controller->pi_getLL('l_no_events'),$this->conf['view.']['list.']['noEventFound_stdWrap']);
		}

		$rems['###SHOWBOTTOMEVENTS###'] = $middle;
		$sims = array();
		$this->showBackLink($page, $rems, $sims);
		return $this->cObj->substituteMarkerArrayCached($page, $sims, $rems, array ());
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_listview.php']);
}
?>