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


require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 * A concrete view for the calendar.
 * It is based on the phpicalendar project
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_monthview extends tx_cal_base_view {
	
	function tx_cal_monthview(){
		$this->tx_cal_base_view();
	}
	
	/**
	 *  Looks for month markers.
	 *  @param		$master_array	array		The event to be drawn.
	 *  @param		$getdate		integer		The date of the event
	 *	@return		string		The HTML output.
	 */
	function drawMonth(&$master_array, $getdate) {
		//Resetting viewarray, to make sure we always get the current events
		$this->viewarray = false;
		$this->_init($master_array);
		$page = '';
		if($this->conf['view.']['month.']['monthMakeMiniCal']){
			$page = $this->cObj->fileResource($this->conf['view.']['month.']['monthMiniTemplate']);
			if ($page == '') {
				$page = $this->conf['view.']['month.']['monthMiniTemplate'];
				if (!(preg_match('/(.)*###([A-Z0-9_-|]*)###(.)*/', $page))) {				
					return '<h3>calendar: no template file found:</h3>'.$this->conf['view.']['month.']['monthMiniTemplate'].'<br />Please check your template record and add both cal items at "include static (from extension)"';
				}
			}
		}else{
			$page = $this->cObj->fileResource($this->conf['view.']['month.']['monthTemplate']);
			if ($page == '') {
				return '<h3>calendar: no template file found:</h3>'.$this->conf['view.']['month.']['monthTemplate'].'<br />Please check your template record and add both cal items at "include static (from extension)"';
			}
		}
		
		$rems = array();
		return $this->finish($page, $rems);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_monthview.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_monthview.php']);
}
?>