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


require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_fe_editing_base_view.php');
require_once (t3lib_extMgm :: extPath('cal').'controller/class.tx_cal_calendar.php');

/**
 * A service which renders a form to create / edit a phpicategory event.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_delete_category_view extends tx_cal_fe_editing_base_view {
	
	var $category;
	
	function tx_cal_delete_category_view(){
		$this->tx_cal_fe_editing_base_view();
	}
	
	/**
	 *  Draws a delete form for a calendar.
	 *  @param      boolean     True if a location should be deleted
	 *  @param		object		The object to be deleted
	 *  @param		object		The cObject of the mother-class.
	 *  @param		object		The rights object.
	 *	@return		string		The HTML output.
	 */
	function drawDeleteCategory(&$category){
		
		$page = $this->cObj->fileResource($this->conf['view.']['delete_category.']['template']);
		if ($page=='') {
			return '<h3>category: no delete category template file found:</h3>'.$this->conf['view.']['delete_category.']['template'];
		}
		
		$this->category = $category;
		
		$rems = array();
		$sims = array();
		
		$sims['###UID###'] = $this->conf['uid'];
		$sims['###TYPE###'] = $this->conf['type'];
		$sims['###VIEW###'] = 'remove_category';
		$sims['###LASTVIEW###'] = $this->controller->extendLastView();
		$sims['###L_DELETE_CATEGORY###'] = $this->controller->pi_getLL('l_delete_category');
		$sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$sims['###ACTION_URL###'] = $this->controller->pi_linkTP_keepPIvars_url( array('view'=>'remove_category'));
		$this->getTemplateSubpartMarker($page, $sims, $rems);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		$sims = array();
		$rems = array();
		$this->getTemplateSingleMarker($page, $sims, $rems);
		$page = $this->cObj->substituteMarkerArrayCached($page, array(), $rems, array ());;
		$page = $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
		return $this->cObj->substituteMarkerArrayCached($page, $sims, array(), array ());
	}
	
	function getTemplateSubpartMarker(& $template, & $sims, & $rems) {
		
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

		$allMarkers = array_unique($match[1]);

		foreach ($allMarkers as $marker) {
			switch ($marker) {
				case 'FORM_START' :
					$this->getFormStartMarker($template, $sims, $rems);
					break;
				case 'FORM_END' :
					$this->getFormEndMarker($template, $sims, $rems);
					break;
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
				    if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $sims, $rems);
				    } 
					break;
			}
		}
	}
	
	function getTemplateSingleMarker(& $template, & $sims, & $rems) {
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_calendar_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $sims, $rems);
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
					}
					break;
			}
		}
	}
	
	function getFormStartMarker(& $template, & $sims, & $rems) {
		$rems['###FORM_START###'] = $this->cObj->getSubpart($template, '###FORM_START###');
	}
	
	function getHiddenMarker(& $template, & $sims, & $rems) {
		$sims['###HIDDEN###'] = $this->cObj->stdWrap($this->category->isHidden()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
	}
	
	function getTitleMarker(& $template, & $sims, & $rems) {
		$sims['###TITLE###'] = $this->cObj->stdWrap($this->category->getTitle(), $this->conf['view.'][$this->conf['view'].'.']['title_stdWrap.']);
	}
	
	function getCalendarMarker(& $template, & $sims, & $rems) {
		$calendarUid = $this->category->getCalendarUid();
		if($calendarUid) {
			$calendar = $this->modelObj->findCalendar($calendarUid, 'tx_cal_calendar', $this->conf['pidList']);
			$calendarTitle = $calendar->getTitle();
			$sims['###CALENDAR###'] = $this->cObj->stdWrap($calendarTitle, $this->conf['view.'][$this->conf['view'].'.']['calendar_stdWrap.']);
		} else {
			$sims['###CALENDAR###'] = '';
		}
	}
	
	function getHeaderStyleMarker(& $template, & $sims, & $rems) {
		$sims['###HEADERSTYLE###'] = $this->cObj->stdWrap($this->category->getHeaderStyle(), $this->conf['view.'][$this->conf['view'].'.']['headerStyle_stdWrap.']);
	}
	
	function getBodyStyleMarker(& $template, & $sims, & $rems) {
		$sims['###BODYSTYLE###'] = $this->cObj->stdWrap($this->category->getBodyStyle(), $this->conf['view.'][$this->conf['view'].'.']['bodyStyle_stdWrap.']);
	}
	
	function getParentCategoryMarker(& $template, &$sims, & $rems) {
		$parentUid = $this->category->getParentUid();

		if($parentUid) {
			/* Get parent category title */
			$category = $this->modelObj->findCategory($parentUid,'tx_cal_category',$this->conf['pidList']);
			$parentCategory = $category->getTitle();
			$sims['###PARENT_CATEGORY###'] = $this->cObj->stdWrap($parentCategory, $this->conf['view.'][$this->conf['view'].'.']['parentCategory_stdWrap.']);
		} else {
			$sims['###PARENT_CATEGORY###'] = '';
		}
	}
	
	function getSharedUserAllowedMarker(& $template, & $sims, & $rems) {
		$sims['###SHARED_USER_ALLOWED###'] = $this->cObj->stdWrap($this->category->isSharedUserAllowed()?$this->controller->pi_getLL('l_true'):$this->controller->pi_getLL('l_false'), $this->conf['view.'][$this->conf['view'].'.']['sharedUserAllowed_stdWrap.']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_category_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_delete_category_view.php']);
}
?>