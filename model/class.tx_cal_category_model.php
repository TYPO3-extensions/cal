<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_base_model.php');

/** 
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_category_model extends tx_cal_base_model {
	
	var $row = array();
	var $uid = 0;
	var $parentUid = 0;
	var $title = '';
	var $headerStyle = '';
	var $bodyStyle = '';
	var $sharedUserAllowed = false;
	var $categoryService;
	
	
	/**
	 *  Constructor.
	 */
	function tx_cal_category_model(&$controller, &$row ,$serviceKey){
		$this->tx_cal_base_model($controller, $serviceKey);
		$this->init($row);
	}
	
	function init(&$row){
		$this->row = $row;
		$this->setUid($row['uid']);
		$this->setParentUid($row['parent_category']);
		$this->setTitle($row['title']);
		$this->setHeaderStyle($row['headerstyle']);
		$this->setBodyStyle($row['bodystyle']);
		$this->setSharedUserAllowed($row['shared_user_allowed']);
	}
	
	function setUid($uid){
		$this->uid = $uid;
	}
	
	function getUid(){
		return $this->uid;
	}
	
	function setParentUid($uid){
		$this->parentUid = $uid;
	}
	
	function getParentUid(){
		return $this->parentUid;
	}
	
	function setTitle($title){
		$this->title = $title;
	}
	
	function getTitle(){
		return $this->title;
	}
	
	function setHeaderStyle($headerStyle){
		$this->headerStyle = $headerStyle;
	}
	
	function getHeaderStyle(){
		return $this->headerStyle;
	}
	
	function setBodyStyle($bodyStyle){
		$this->bodyStyle = $bodyStyle;
	}
	
	function getBodyStyle(){
		return $this->bodyStyle;
	}
	
	function setSharedUserAllowed($boolean){
		$this->sharedUserAllowed = $boolean;
	}
	
	function isSharedUserAllowed(){
		return $this->sharedUserAllowed;
	}
	
	function getCategoryMarker(& $template, & $rems, & $sims, & $wrapped){
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'HEADERSTYLE':
					$sims['###HEADERSTYLE###'] = $this->getHeaderStyle();
					break;
				case 'BODYSTYLE':
					$sims['###BODYSTYLE###'] = $this->getBodyStyle();
					break;
				case 'PARENT_UID':
					$sims['###PARENT_UID###'] = $this->getParentUid();
					break;
				case 'TITLE':
					$sims['###TITLE###'] = $this->getTitle();
					break;
				case 'UID':
					$sims['###UID###'] = $this->getUid();
					break;
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_event_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else{
						$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->row[strtolower($marker)],$this->conf['view.']['event.']['stdWrap_'.strtolower($marker)]);
					}
					break;
			}
		}
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_category_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_category_model.php']);
}
?>