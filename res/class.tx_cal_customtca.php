<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Foundation For Evangelism (info@evangelize.org)
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

/**
 * < Insert class description here. />
 * 
 * @author Web-Empowered Church Team <developer@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_cal
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
	
	function init($PA, $fobj) {
		global $LANG;
		$LANG->includeLLFile(t3lib_extMgm::extPath('cal').'locallang_db.xml');
		
		$this->frequency = $PA['row']['freq'];
		$this->uid = $PA['row']['uid'];
		$this->row = $PA['row'];
		$this->table = $PA['table'];
		
		$this->garbageIcon = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif').' title="'.$LANG->getLL('tx_cal_event.remove_recurrence').'" alt="'.$LANG->getLL('tx_cal_event.delete_recurrence').'" />';
		$this->newIcon = '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_el.gif').' title="'.$LANG->getLL('tx_cal_event.add_recurrence').'" alt="'.$LANG->getLL('tx_cal_event.add_recurrence').'" />';
		
		$this->commonJS = '<script type="text/javascript" src="contrib/prototype/prototype.js"></script>'.chr(10).
						  '<script src="'.t3lib_extMgm::extRelPath('cal').'res/recurui.js" type="text/javascript"></script>';
						
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_customtca.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/res/class.tx_cal_customtca.php']);
}

?>
