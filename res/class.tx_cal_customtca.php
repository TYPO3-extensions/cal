<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2005-2008 Mario Matzulla
 * (c) 2005-2008 Christian Technology Ministries International Inc.
 * All rights reserved
 *
 * This file is part of the Web-Empowered Church (WEC)
 * (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries 
 * International (http://CTMIinc.org). The WEC is developing TYPO3-based
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

/**
 * Backend class for user-defined TCA type used in recurring event setup.
 * 
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_customtca {

	var $counts;
	var $weekdays;
	var $months;	
	var $commonJS;
	var $garbageIcon;
	var $newIcon;
	var $everyMonthText;
	var $selectedMonthText;
	var $rdateType;
	var $rdate;
	var $rdateValues;
	
	function init($PA, $fobj) {
		global $LANG;
		$LANG->includeLLFile(t3lib_extMgm::extPath('cal').'locallang_db.xml');
		
		$this->frequency = $PA['row']['freq'];
		$this->uid = $PA['row']['uid'];
		$this->row = $PA['row'];
		$this->table = $PA['table'];
		$this->rdateType = $this->row['rdate_type'];
		$this->rdate = $this->row['rdate'];
		$this->rdateValues = t3lib_div::trimExplode(',',$this->row['rdate'],1);
	
		$this->garbageIcon = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif').' title="'.$LANG->getLL('tx_cal_event.remove_recurrence').'" alt="'.$LANG->getLL('tx_cal_event.delete_recurrence').'" />';
		$this->newIcon = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_el.gif').' title="'.$LANG->getLL('tx_cal_event.add_recurrence').'" alt="'.$LANG->getLL('tx_cal_event.add_recurrence').'" />';
		
		$this->commonJS = '';
		if (t3lib_div::int_from_ver(TYPO3_version) < 4003000){
			$this->commonJS = '<script type="text/javascript" src="contrib/prototype/prototype.js"></script>'.chr(10);
		}
		$this->commonJS .= '<script src="'.t3lib_extMgm::extRelPath('cal').'res/recurui.js" type="text/javascript"></script>'.chr(10).
						  '<script src="'.t3lib_extMgm::extRelPath('cal').'res/url.js" type="text/javascript"></script>';
						
		$this->everyMonthText = $LANG->getLL('tx_cal_event.recurs_every_month');
		$this->selectedMonthText = $LANG->getLL('tx_cal_event.recurs_selected_months');
						
		$this->counts = $this->getCountsArray();
		
		$startDay = $this->getWeekStartDay($PA);
		$this->weekdays = $this->getWeekDaysArray($startDay);
		
		$this->months = $this->getMonthsArray();
		
	}
	
	function getWeekStartDay($PA) {
		$pageID = $PA['row']['pid'];
		$tsConfig = t3lib_BEfunc::getModTSconfig($pageID, 'options.tx_cal_controller.weekStartDay');
		$weekStartDay = strtolower($tsConfig['value']);
		
		switch($weekStartDay) {
			case 'sunday':
				$startDay = 'su';
				break;
			/* If there's any value other than sunday, assume we want Monday */
			default:
				$startDay = 'mo';
				break;
		}
		
		return $startDay;
	}
	
	function getCountsArray() {
		global $LANG;
		
		return array(
			'1' => $LANG->getLL('tx_cal_event.byday_count_first'),
			'2' => $LANG->getLL('tx_cal_event.byday_count_second'),
			'3' => $LANG->getLL('tx_cal_event.byday_count_third'),
			'4' => $LANG->getLL('tx_cal_event.byday_count_fourth'),
			'5' => $LANG->getLL('tx_cal_event.byday_count_fifth'),
			'-3' => $LANG->getLL('tx_cal_event.byday_count_thirdtolast'),
			'-2' => $LANG->getLL('tx_cal_event.byday_count_secondtolast'),
			'-1' => $LANG->getLL('tx_cal_event.byday_count_last'),
		);
	}
	
	function getWeekdaysArray($startDay) {
		global $LANG;
		$weekdays = array();

		if($startDay == 'su') {
			$weekdays['su'] = $LANG->getLL('tx_cal_event.byday_sunday');
		}
		
		$weekdays['mo'] = $LANG->getLL('tx_cal_event.byday_monday');
		$weekdays['tu'] = $LANG->getLL('tx_cal_event.byday_tuesday');
		$weekdays['we'] = $LANG->getLL('tx_cal_event.byday_wednesday');
		$weekdays['th'] = $LANG->getLL('tx_cal_event.byday_thursday');
		$weekdays['fr'] = $LANG->getLL('tx_cal_event.byday_friday');
		$weekdays['sa'] = $LANG->getLL('tx_cal_event.byday_saturday');
		
		if($startDay != 'su') {
			$weekdays['su'] = $LANG->getLL('tx_cal_event.byday_sunday');
		}
		
		return $weekdays;
	}
	
	function getMonthsArray() {
		global $LANG;
		
		return array(
			 "1" => $LANG->getLL('tx_cal_event.bymonth_january'),
			 "2" => $LANG->getLL('tx_cal_event.bymonth_february'),
			 "3" => $LANG->getLL('tx_cal_event.bymonth_march'),
			 "4" => $LANG->getLL('tx_cal_event.bymonth_april'),
			 "5" => $LANG->getLL('tx_cal_event.bymonth_may'),
			 "6" => $LANG->getLL('tx_cal_event.bymonth_june'),
			 "7" => $LANG->getLL('tx_cal_event.bymonth_july'),
			 "8" => $LANG->getLL('tx_cal_event.bymonth_august'),
			 "9" => $LANG->getLL('tx_cal_event.bymonth_september'),
			"10" => $LANG->getLL('tx_cal_event.bymonth_october'),
			"11" => $LANG->getLL('tx_cal_event.bymonth_november'),
			"12" => $LANG->getLL('tx_cal_event.bymonth_december'),
		);
	}
	
	function extUrl($PA, $fobj) {
		global $LANG;		
		$this->init($PA, $fobj);
		
		$out[] = $this->commonJS;
		$out[] = '<script type="text/javascript">';
		$out[] = 	"var extUrl = new ExtUrlUI('ext_url-container', 'data[".$this->table."][".$this->uid."][ext_url]', 'row', '".$this->getExtUrlRow()."');";
		$out[] = 	"Event.observe(window, 'load', function() { extUrl.load(); });";
		$out[] = '</script>';
		$out[] = '<input type="hidden" name="data['.$PA['table'].']['.$PA['row']['uid'].'][ext_url_notes]" id="data['.$PA['table'].']['.$PA['row']['uid'].'][ext_url_notes]" value="'.$PA['row']['ext_url_notes'].'" />';
				
		$out[] = '<div id="ext_url-container"></div>';
		$out[] = '<div style="padding: 5px 0px 5px 0px;"><a href="javascript:extUrl.addUrl();">'.$this->newIcon.$LANG->getLL('tx_cal_calendar.add_url').'</a></div>';
		$out[] = '<input type="hidden" name="data['.$this->table.']['.$this->uid.'][ext_url]" id="data['.$this->table.']['.$this->uid.'][ext_url]" value="'.$PA['row']['ext_url'].'" />';
		
		return implode(chr(10), $out);
	}
	
	function getExtUrlRow() {
		global $LANG;
		$html  = '<div class="row">';
		$html .= 	$LANG->getLL('tx_cal_calendar.ext_url_note').':<input type="text" class="exturlnote" onchange="extUrl.save()" >';
		$html .= 	$LANG->getLL('tx_cal_calendar.ext_url_url').':<input type="text" class="exturl" onchange="extUrl.save()" >';
		$html .=	'<a id="garbage" href="#" onclick="extUrl.removeUrl(this);">'.$this->garbageIcon.'</a>';
		$html .= '</div>';
				
		return $this->removeNewLines($html);
	}
	
	function byDay($PA, $fobj) {
		$this->init($PA, $fobj);
				
		switch($this->frequency) {
			case 'week':
				$html = $this->byDay_checkbox();
				break;
			case 'month':
				$row = $this->getByDayRow($this->everyMonthText);
				$html = $this->byDay_select($row);
				break;
			case 'year':
				$row = $this->getByDayRow($this->selectedMonthText);
				$html = $this->byDay_select($row);
				break;
		}
		
		$out[] = $this->commonJS;
		$out[] = $html;
		$out[] = '<input type="hidden" name="data['.$this->table.']['.$this->uid.'][byday]" id="data['.$this->table.']['.$this->uid.'][byday]" value="'.$this->row['byday'].'" />';
				
		return implode(chr(10), $out);
	}
	
	function byMonthDay($PA, $fobj) {
		$this->init($PA, $fobj);
		
		switch($this->frequency) {
			case 'week':
				$row = $this->getByMonthDayRow($this->everyMonthText);
				$html = $this->byMonthDay_select($row);
				break;
			case 'month':
				$row = $this->getByMonthDayRow($this->everyMonthText);
				$html = $this->byMonthDay_select($row);
				break;
			case 'year':
				$row = $this->getByMonthDayRow($this->selectedMonthText);
				$html = $this->byMonthDay_select($row);
				break;
		}
		
		$out[] = $this->commonJS;
		$out[] = $html;
		$out[] = '<input type="hidden" name="data['.$this->table.']['.$this->uid.'][bymonthday]" id="data['.$this->table.']['.$this->uid.'][bymonthday]" value="'.$this->row['bymonthday'].'" />';
		
		
		return implode(chr(10), $out);
	}
	
	function byMonth($PA, $fobj) {
		$this->init($PA, $fobj);		
		$out = array();
		
		$out[] = $this->commonJS;
		$out[] = '<script type="text/javascript">';
		$out[] = 	"var byMonth = new ByMonthUI('bymonth-container', 'data[".$this->table."][".$this->uid."][bymonth]', 'row');";
		$out[] = 	"Event.observe(window, 'load', function() { byMonth.load(); });";
		$out[] = '</script>';
			
		$out[] = '<div id="bymonth-container" style="margin-bottom: 5px;">';
		foreach($this->months as $value => $label) {
			$name = "bymonth_".$value;
			$out[] = '<div class="row">';
			$out[] = 	'<input style="padding: 0px; margin: 0px;" type="checkbox" name="'.$name.'" value="'.$value.'" onchange="byMonth.save();"/><label style="padding-left: 2px;" for="'.$name.'">'.$label.'</label>';
			$out[] = '</div>';
		}
		$out[] = '</div>';	
		$out[] = '<input type="hidden" name="data['.$this->table.']['.$this->uid.'][bymonth]" id="data['.$this->table.']['.$this->uid.'][bymonth]" value="'.$this->row['bymonth'].'" />';
		
		return implode(chr(10), $out);	
	}

	function rdate($PA, $fobj) {
		global $TCA,$LANG;
		$this->init($PA, $fobj);
		$this->rdateValues[] = '';
		$out = array();
		$jsDate = $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ?
			'%m-%d-%Y' : '%d-%m-%Y';
		$out[] = '<script type="text/javascript">';
		$out[] = 'var jsDate = "'.$jsDate.'";';
		$out[] = 'function rdateChanged(){';
		$out[] = 'var rdateCount = '.(count($this->rdateValues)).';';
		$out[] = 'var rdate = document.getElementById("data['.$this->table.']['.$this->uid.'][rdate]");';
		$out[] = 'rdate.value="";';
		$out[] = 'for(var i=0; i<rdateCount; i++){';
		if (t3lib_div::int_from_ver(TYPO3_version) < 4003000){
			$out[] = 'var dateFormated = document.getElementById("data_'.$this->table.'_'.$this->uid.'_rdate"+i+"_hr").value;';
		} else {
			if($this->rdateType == 'date_time' || $this->rdateType == 'period') {
				$out[] = 'var dateFormated = document.getElementById("tceforms-datetimefield-data_'.$this->table.'_'.$this->uid.'_rdate"+i+"_hr").value;';
			} else {
				$out[] = 'var dateFormated = document.getElementById("tceforms-datefield-data_'.$this->table.'_'.$this->uid.'_rdate"+i+"_hr").value;';
			}
		}
		$out[] = 'if(dateFormated!=""){';
		$out[] = 'var splittedDateTime = dateFormated.split(" ");';
		$out[] = 'var splittedTime = splittedDateTime[0].split(":");';
		$out[] = 'var splittedDate = splittedDateTime[0].split("-");';
		$out[] = 'if(splittedDateTime.length == 2) {';
		$out[] = 'splittedDate = splittedDateTime[1].split("-");';
		$out[] = '} else if(splittedDateTime.length == 1 && splittedDate.length == 2) {';
		$out[] = 'var d=new Date();';
		$out[] = 'splittedDate[2] = d.getFullYear();';
		$out[] = '}';
		$out[] = 'if(jsDate=="%d-%m-%Y"){';
		$out[] = 'dateFormated = splittedDate[2]+(parseInt(splittedDate[1],10)<10?"0":"")+parseInt(splittedDate[1],10)+(parseInt(splittedDate[0],10)<10?"0":"")+parseInt(splittedDate[0],10);';
		$out[] = '} else {';
		$out[] = 'dateFormated = splittedDate[2]+(parseInt(splittedDate[0],10)<10?"0":"")+parseInt(splittedDate[0],10)+(parseInt(splittedDate[1],10)<10?"0":"")+parseInt(splittedDate[1],10);';
		$out[] = '}';
		if($this->rdateType == 'date_time') {
			$out[] = 'dateFormated += "T"+(parseInt(splittedTime[0],10)<10?"0":"")+parseInt(splittedTime[0],10)+(parseInt(splittedTime[1],10)<10?"0":"")+parseInt(splittedTime[1],10)+"00Z";';
		}else if($this->rdateType == 'period') {
			$out[] = 'dateFormated += "T"+(parseInt(splittedTime[0],10)<10?"0":"")+parseInt(splittedTime[0],10)+(parseInt(splittedTime[1],10)<10?"0":"")+parseInt(splittedTime[1],10)+"00Z/P";';
			$out[] = 'var rdateYear = parseInt(document.getElementById("rdateYear"+i).value,10);';
			$out[] = 'var rdateMonth = parseInt(document.getElementById("rdateMonth"+i).value,10);';
			$out[] = 'var rdateWeek = parseInt(document.getElementById("rdateWeek"+i).value,10);';
			$out[] = 'var rdateDay = parseInt(document.getElementById("rdateDay"+i).value,10);';
			$out[] = 'var rdateHour = parseInt(document.getElementById("rdateHour"+i).value,10);';
			$out[] = 'var rdateMinute = parseInt(document.getElementById("rdateMinute"+i).value,10);';
			$out[] = 'dateFormated += rdateYear>0?rdateYear+"Y":"";';
			$out[] = 'dateFormated += rdateMonth>0?rdateMonth+"M":"";';
			$out[] = 'dateFormated += rdateWeek>0?rdateWeek+"W":"";';
			$out[] = 'dateFormated += rdateDay>0?rdateDay+"D":"";';
			$out[] = 'dateFormated += "T";';
			$out[] = 'dateFormated += rdateHour>0?rdateHour+"H":"";';
			$out[] = 'dateFormated += rdateMinute>0?rdateMinute+"M":"";';
		}
		$out[] = 'rdate.value += dateFormated+",";';
		
		$out[] = '}';
		$out[] = '}';
		$out[] = 'rdate.value = rdate.value.substr(0,rdate.value.length-1);';
		$out[] = '}';
		$out[] = '</script>';
		$calendarWizard = true;
		if (t3lib_div::int_from_ver(TYPO3_version) < 4003000){
			$calendarWizard = $TCA[$this->table]['columns']['start_date']['config']['wizards']['calendar'];
		}
		if($calendarWizard){
			$key = 0;
			foreach($this->rdateValues as $value){
				$formatedValue = '';
				$splittedPeriod = Array('','');
				if($value!=''){
//debug($value);
					$splittedPeriod = explode('/',$value);
					$splittedDateTime = explode('T',$splittedPeriod[0]);
					if($jsDate=='%d-%m-%Y'){
						$formatedValue = substr($splittedDateTime[0],6,2).'-'.substr($splittedDateTime[0],4,2).'-'.substr($splittedDateTime[0],0,4);
					}else if($jsDate=='%m-%d-%Y'){
						$formatedValue = substr($splittedDateTime[0],4,2).'-'.substr($splittedDateTime[0],6,2).'-'.substr($splittedDateTime[0],0,4);
					} else {
						$formatedValue = 'unknown date format';
					}
					if($this->rdateType == 'date_time' || $this->rdateType == 'period') {
//debug($splittedDateTime);
						$formatedValue = count($splittedDateTime)==2?substr($splittedDateTime[1],0,2).':'.substr($splittedDateTime[1],2,2).' '.$formatedValue:'00:00 '.$formatedValue;
//debug($formatedValue);

					}
				}
				if (t3lib_div::int_from_ver(TYPO3_version) < 4003000){
					$out[] = '<div style="float:left;"><input type="text" value="'.$formatedValue.'" name="rdate'.$key.'" id="data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" onchange="rdateChanged();"/><input type="hidden" value="'.$formatedValue.'" id="data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'" />';
				} else if (t3lib_div::int_from_ver(TYPO3_version) < 4004000){
					if($this->rdateType == 'date_time' || $this->rdateType == 'period') {
						$out[] = '<div style="float:left;"><input type="text" value="'.$formatedValue.'" class="formField tceforms-textfield tceforms-timefield" name="rdate'.$key.'" id="tceforms-datetimefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" onchange="'.
								'rdateChanged();"/>'.
								'<input type="hidden" value="'.$formatedValue.'" id="data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'" />'.
								'<img id="picker-tceforms-datetimefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" alt="" style="cursor: pointer; vertical-align: middle;" src="gfx/datepicker.gif" class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-datetime"/></div>';
					} else {
						$out[] = '<div style="float:left;"><input type="text" value="'.$formatedValue.'" class="tceforms-datefield" name="rdate'.$key.'" id="tceforms-datefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" onchange="'.
								'rdateChanged();"/>'.
								'<input type="hidden" value="'.$formatedValue.'" id="data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'" />'.
								'<img id="picker-tceforms-datefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" alt="" style="cursor: pointer; vertical-align: middle;" src="gfx/datepicker.gif" class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date"/></div>';
					}
				}else {
					if($this->rdateType == 'date_time' || $this->rdateType == 'period') {
						$out[] = '<span class="t3-form-palette-fieldclass-main5"><input type="text" value="'.$formatedValue.'" class="tceforms-datetimefield" name="rdate'.$key.'" id="tceforms-datetimefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" onchange="'.
								'rdateChanged();"/>'.
								'<input type="hidden" value="'.$formatedValue.'" id="data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'" />'.
								'<span id="picker-tceforms-datetimefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" style="cursor: pointer; vertical-align: middle;" class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date"/></span>';
					} else {
						$out[] = '<span class="t3-form-palette-fieldclass-main5"><input type="text" value="'.$formatedValue.'" class="tceforms-datefield" name="rdate'.$key.'" id="tceforms-datefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" onchange="'.
								'rdateChanged();"/>'.
								'<input type="hidden" value="'.$formatedValue.'" id="data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'" />'.
								'<span id="picker-tceforms-datefield-data_'.$this->table.'_'.$this->uid.'_rdate'.$key.'_hr" style="cursor: pointer; vertical-align: middle;" class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date"/></span>';
					}
				}
				$params = Array();
				$params['table'] = $this->table;
				$params['uid'] = $this->uid;
				$params['field'] = 'rdate'.$key;
				$params['md5ID'] = $this->table.'_'.$this->uid.'_'.'rdate'.$key;
				if($this->rdateType == 'date') {
					$params['wConf']['evalValue'] = 'date';
				} else if($this->rdateType == 'date_time' || $this->rdateType == 'period') {
					$params['wConf']['evalValue'] = 'datetime';
				}
				if (t3lib_div::int_from_ver(TYPO3_version) < 4003000){
					t3lib_div::callUserFunction($calendarWizard['userFunc'], $params, $fobj);
				}
				
				if($this->rdateType == 'period') {
					preg_match ('/P((\d+)Y)?((\d+)M)?((\d+)W)?((\d+)D)?T((\d+)H)?((\d+)M)?((\d+)S)?/',$splittedPeriod[1],$periodArray);
					$params['item'] .= '<span style="padding-left:10px;">'.$LANG->getLL('l_duration').':</span>'.
							$LANG->getLL('l_year').':<input type="text" value="'.intval($periodArray[2]).'" name="rdateYear'.$key.'" id="rdateYear'.$key.'" size="2" onchange="rdateChanged();" />'.
							$LANG->getLL('l_month').':<input type="text" value="'.intval($periodArray[4]).'" name="rdateMonth'.$key.'" id="rdateMonth'.$key.'" size="2" onchange="rdateChanged();" />'.
							$LANG->getLL('l_week').':<input type="text" value="'.intval($periodArray[6]).'" name="rdateWeek'.$key.'" id="rdateWeek'.$key.'" size="2" onchange="rdateChanged();" />'.
							$LANG->getLL('l_day').':<input type="text" value="'.intval($periodArray[8]).'" name="rdateDay'.$key.'" id="rdateDay'.$key.'" size="2" onchange="rdateChanged();" />'.
							$LANG->getLL('l_hour').':<input type="text" value="'.intval($periodArray[10]).'" name="rdateHour'.$key.'" id="rdateHour'.$key.'" size="2" onchange="rdateChanged();" />'.
							$LANG->getLL('l_minute').':<input type="text" value="'.intval($periodArray[12]).'" name="rdateMinute'.$key.'" id="rdateMinute'.$key.'" size="2" onchange="rdateChanged();" />'.
							'<br/>';
				}
				$out[] = $params['item'];
				
				$key++;
			}
		}		

		$out[] = '<input type="hidden" name="data['.$this->table.']['.$this->uid.'][rdate]" id="data['.$this->table.']['.$this->uid.'][rdate]" value="'.$this->row['rdate'].'" />';
		
		return implode(chr(10), $out);	
	}

	
	function byDay_checkbox() {
		$out[] = '<script type="text/javascript">';
		$out[] = 	"var byDay = new ByDayUI('byday-container', 'data[".$this->table."][".$this->uid."][byday]', 'row');";
		$out[] = 	"Event.observe(window, 'load', function() { byDay.load(); });";
		$out[] = '</script>';
			
		$out[] = '<div id="byday-container" style="margin-bottom: 5px;">';
		foreach($this->weekdays as $value => $label) {
			$name = "byday_".$value;
			$out[] = '<div class="row">';
			$out[] = 	'<input style="padding: 0px; margin: 0px;" type="checkbox" name="'.$name.'" value="'.$value.'" onchange="byDay.save();"/><label style="padding-left: 2px;" for="'.$name.'">'.$label.'</label>';
			$out[] = '</div>';
		}
		$out[] = '</div>';
			
		return implode(chr(10), $out);
	}
	
	function byDay_select($row) {
		global $LANG;
		
		$out[] = '<script type="text/javascript">';
		$out[] = 	"var byDay = new ByDayUI('byday-container', 'data[".$this->table."][".$this->uid."][byday]', 'row', '".$row."');";
		$out[] = 	"Event.observe(window, 'load', function() { byDay.load(); });";
		$out[] = '</script>';
		
		$out[] = '<div id="byday-container"></div>';
		$out[] = '<div style="padding: 5px 0px 5px 0px;"><a href="javascript:byDay.addRecurrence();">'.$this->newIcon.$LANG->getLL('tx_cal_event.add_recurrence').'</a></div>';	
		
		return implode(chr(10), $out);
	}

	
	function byMonthDay_select($row) {
		global $LANG;
		
		$out[] = '<script type="text/javascript">';
		$out[] = 	"var byMonthDay = new ByMonthDayUI('bymonthday-container', 'data[".$this->table."][".$this->uid."][bymonthday]', 'row', '".$row."');";
		$out[] = 	"Event.observe(window, 'load', function() { byMonthDay.load(); });";
		$out[] = '</script>';
				
		$out[] = '<div id="bymonthday-container"></div>';
		$out[] = '<div style="padding: 5px 0px 5px 0px;"><a href="javascript:byMonthDay.addRecurrence();">'.$this->newIcon.$LANG->getLL('tx_cal_event.add_recurrence').'</a></div>';	
		
		return implode(chr(10), $out);
	}


	





	
	function getByDayRow($endString) {
		
		$html  = '<div class="row">';
		
		$html .= 	'<select class="count" onchange="byDay.save()">';
		$html .=		'<option value="" />';	
		foreach($this->counts as $value => $label) {
			$html .= 	'<option value="'.$value.'">'.$label.'</option>';
		}
		$html .=	'</select>';
		
		$html .= 	'<select class="day" onchange="byDay.save()">';
		$html .=		'<option value="" />';	
		
		foreach($this->weekdays as $value => $label) {
			$html .= 	'<option value="'.$value.'">'.$label.'</option>';
		}
		$html .=	'</select>';
		
		$html .=	' '.$endString;
		$html .=	'<a id="garbage" href="#" onclick="byDay.removeRecurrence(this);">'.$this->garbageIcon.'</a>';
		$html .= '</div>';
				
		return $this->removeNewLines($html);
	}
	
	function getByMonthDayRow($endString) {
		$html  = '<div class="row">';
		
		$html .=	'Day ';
		$html .= 	'<select class="day" onchange="byMonthDay.save()">';
		$html .=		'<option value=""></option>';
		for($i=1; $i<32; $i++) {
			$html .= 	'<option value="'.$i.'">'.$i.'</option>';
		}
		$html .=	'</select>';
		
		$html .=	' '.$endString;
		$html .=	'<a id="garbage" href="#" onclick="byMonthDay.removeRecurrence(this);">'.$this->garbageIcon.'</a>';
		$html .= '</div>';
				
		return $this->removeNewLines($html);
	}
	
	/**
	 * Converts newlines to <br/> tags.
	 *
	 * @access	private
	 * @param	string		The input string to filtered.
	 * @return	string		The converted string.
	 */
	function removeNewlines($input) {
		$order  = array("\r\n", "\n", "\r", "\t");
		$replace = '';			
		$newstr = str_replace($order, $replace, $input);
		
		return $newstr;
	}
	
	function getHeaderStyles($PA, $fobj){
		return $this->getStyles($PA,'header');
	}
	
	function getBodyStyles($PA, $fobj){
		return $this->getStyles($PA,'body');
	}

	function getStyles($PA, $part){
		$table = $PA['table'];
		$pid = $PA['row']['pid'];
		$value = $PA['row'][$part.'style'];
		$html  = '<div class="row">';
		
		$pageTSConf = t3lib_befunc::getPagesTSconfig($pid);
		if($pageTSConf['options.']['tx_cal_controller.'][$part.'Styles']) {
			$html .= 	'<select class="select" name="data['.$table.']['.$PA['row']['uid'].']['.$part.'style]">';
			$html .=		'<option value=""></option>';
			
			$options = t3lib_div::trimExplode(',',$pageTSConf['options.']['tx_cal_controller.'][$part.'Styles'],1);
		
			foreach($options as $option){
				$nameAndColor = t3lib_div::trimExplode('=',$option,1);
				$selected = '';
				if($value==$nameAndColor[0]){
					$selected = ' selected="selected"';
				}
				$html .= 	'<option value="'.$nameAndColor[0].'" style="background-color:'.$nameAndColor[1].';"'.$selected.'>'.$nameAndColor[0].'</option>';
			}
			$html .=	'</select>';
		} else {
			$html .= 	'<input class="input" maxlength="30" size="20" name="data['.$table.']['.$PA['row']['uid'].']['.$part.'style]" value="'.$value.'">';
		}
		$html .= '</div>';
		return $html;		
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_customtca.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_customtca.php']);
}

?>