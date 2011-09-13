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

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_base_model {
	var $prefixId = 'tx_cal_controller';
	var $cObj;
	var $conf;
	var $rightsObj;
	var $serviceKey;
	var $tempATagParam;
	var $controller;

	var $type;
	var $objectType = '';
	var $striptags = false;

	var $hidden = false;
	var $uid = 0;
	var $pid = 0;
	
	var $image = Array();
	var $imageCaption = Array();
	var $imageTitleText = Array();
	var $imageAltText = Array();

	var $attachment = Array();
	var $attachmentCaption = Array();

	/**
	 *  Constructor.
	 *  @param	$serviceKey String	The serviceKey for this model
	 */
	function tx_cal_base_model(&$serviceKey){
		//$this->controller = &tx_cal_registry::Registry('basic','controller');
		$this->conf = &tx_cal_registry::Registry('basic','conf');
		/*$this->rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$this->cObj = &tx_cal_registry::Registry('basic','cobj');
		$this->modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		*/
		$this->serviceKey = &$serviceKey;
	}

	function getImageMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###IMAGE###'] = '';
		if(!$this->getImage() || count($this->getImage())<1){
			return;
		}
		unset($this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imgList.']);
		if($this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imageCount'] < $this->getImage()){
			$imageArray = $this->getImage();
			$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imgList'] = implode(',',array_slice($imageArray,0,$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imageCount']));
		}else{
			$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imgList'] = implode(',',$this->getImage());
		}

		t3lib_div::loadTCA('tx_cal_'.$this->objectType);
		$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imgPath'] = $GLOBALS['TCA']['tx_cal_'.$this->objectType]['columns']['image']['config']['uploadfolder'].'/';
		if($this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['overridePath']){
			$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['imgPath'] = $this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['overridePath'];
		}
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$remLayout = $cObj->data['layout'];
		$cObj->data['layout'] = $this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['layout'];
		$remCaption = $cObj->data['imagecaption'];
		if($this->getImageCaption()){
			$cObj->data['imagecaption'] = implode(chr(10),$this->getImageCaption());
		}

		if($this->getImageAltText()){
			$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['1.']['altText'] = implode(chr(10),$this->getImageAltText());
		}
		if($this->getImageTitleText()){
			$this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']['1.']['titleText'] = implode(chr(10),$this->getImageTitleText());
		}
		$sims['###IMAGE###'] = $cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->objectType.'.']['image'], $this->conf['view.'][$view.'.'][$this->objectType.'.']['image.']);
	}


	/**
	 * Returns the image alt text(s)
	 */
	function getImageAltText(){
		return $this->imageAltText;
	}

	/**
	 * Sets the image title text
	 * @param	$text	Array	the image title text(s)
	 */
	function setImageTitleText($text){
		if(is_array($text)){
			$this->imageTitleText = $text;
		}
	}

	/**
	 * Returns the image title text(s)
	 */
	function getImageTitleText(){
		return $this->imageTitleText;
	}

	/**
	 * Sets the image caption
	 * @param	$caption	Array	the image caption
	 */
	function setImageCaption($caption){
		if(is_array($caption)){
			$this->imageCaption = $caption;
		}
	}

	/**
	 * Returns the image caption
	 */
	function getImageCaption(){
		return $this->imageCaption;
	}


	/**
	 * Sets the images
	 * @param	$images	blob	One or more images
	 */
	function setImage($image){
		if($image!=''){
			$this->image = $image;
		}
	}

	/**
	 * Returns the image blob
	 */
	function getImage(){
		return $this->image;
	}
	
/**
	 * Adds an image
	 * @param	$url	String	the image
	 */
	function addImage($image){
		$this->image[] = $image;
	}

	/**
	 * Removes an image
	 * @param	$url	String	the image
	 */
	function removeImage($image){
		for($i = 0; $i<count($this->image);$i++){
			if($this->image[$i] == $image){
				array_splice($this->image,$i);
				return true;
			}
		}
		return false;
	}

	/**
	 * Sets the image alt text
	 * @param	$alt	Array	the image alt text(s)
	 */
	function setImageAltText($alt){
		if(is_array($alt)){
			$this->imageAltText = $alt;
		}
	}

	/**
	 * Returns the attachment url
	 */
	function getAttachment(){
		return $this->attachment;
	}

	/**
	 * Adds an attachment url
	 * @param	$url	String	the url
	 */
	function addAttachment($url){
		$this->attachment[] = $url;
	}
	
	/**
	 * Sets the attachments
	 * @param	$attachmentArray	Array	the attachment array
	 */
	function setAttachment($attachmentArray){
		$this->attachment = $attachmentArray;
	}

	/**
	 * Removes an attachment url
	 * @param	$url	String	the url
	 */
	function removeAttachmentURL($url){
		for($i = 0; $i<count($this->attachment);$i++){
			if($this->attachment == $url){
				array_splice($this->attachment,$i);
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the attachment caption
	 */
	function getAttachmentCaption(){
		return $this->attachmentCaption;
	}

	/**
	 * Sets the attachment caption
	 */
	function setAttachmentCaption(&$caption){
		return $this->attachmentCaption = $caption;
	}

	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()){
		return false;
	}

	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()){
		return false;
	}

	/**
	 * 	Returns the type value
	 *  @return		Integer		The type.
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * Sets the type attribute. This should be the service type
	 * @param	$type	String	The service type
	 */
	function setType($type) {
		$this->type = $type;
	}

	function getObjectType(){
		return $this->objectType;
	}

	function setObjectType($type){
		$this->objectType = $type;
	}

	function getUid(){
		return $this->uid;
	}

	function setUid($t){
		$this->uid = $t;
	}
	
	function setPid($pid) {
		$this->pid = $pid;
	}
	
	function getPid() {
		return $this->pid;
	}

	/**
	 *  Returns the hidden value.
	 *  @return		Integer		1 == true, 0 == false.
	 */
	function getHidden() {
		return $this->hidden;
	}

	/**
	 *  Returns the hidden value.
	 *  @return		Integer		1 == true, 0 == false.
	 */
	function isHidden() {
		return $this->hidden;
	}

	/**
	 *  Sets the hidden value.
	 *	@param	$hidden Integer	1 == true, 0 == false.
	 */
	function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		if($this->striptags){
			$this->conf['view.'][$view.'.'][$this->objectType.'.']['description.']['value'] = $this->getDescription();
			$sims['###DESCRIPTION_STRIPTAGS###'] = strip_tags($cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->objectType.'.']['description'],$this->conf['view.'][$view.'.'][$this->objectType.'.']['description.']));
		}else{
			if($this->isPreview){
				$this->conf['view.'][$view.'.'][$this->objectType.'.']['preview.']['value'] = $this->getDescription();
				$sims['###DESCRIPTION###'] = $cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->objectType.'.']['preview'], $this->conf['view.'][$view.'.'][$this->objectType.'.']['preview.']);
			} else {
				$this->conf['view.'][$view.'.'][$this->objectType.'.']['description.']['value'] = $this->getDescription();
				$sims['###DESCRIPTION###'] = $cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->objectType.'.']['description'],$this->conf['view.'][$view.'.'][$this->objectType.'.']['description.']);
			}
		}
	}

	function getDescriptionImageMarker(&$template, &$sims, &$rems, & $wrapped, $view){
		$conf = $this->conf['view.'][$view.'.'][$this->whoAmI.'.']['descriptionImage.'];

		if(!$this->getImage()){
			$sims['###DESCRIPTION_IMAGE###'] = $this->getText($this->conf['view.'][$view.'.'][$this->whoAmI.'.']['description.']);
			return;
		}else{
			$conf['20.']['imgList'] = $this->getImage();
		}
		$conf['20.']['text.']['20.']['value'] = $this->getRTEParsedContent($this->getDescription());
		t3lib_div::loadTCA('tx_publication_entry');
		$conf['20.']['imgPath'] = $GLOBALS['TCA']['tx_publication_'.$this->whoAmI]['columns']['image']['config']['uploadfolder'].'/';
		$cObj = &tx_cal_registry::Registry('basic','cobj');
		$sims['###DESCRIPTION_IMAGE###'] = $cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->whoAmI.'.']['descriptionImage'], $conf);
	}

	function getHeadingMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$controller = &tx_cal_registry::Registry('basic','controller');
		$sims['###HEADING###'] = $controller->pi_getLL('l_'.$this->objectType);
	}
	
	function getEditPanelMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$controller = &tx_cal_registry::Registry('basic','controller');
		$sims['###EDIT_PANEL###'] = $controller->pi_getEditPanel($this->row, 'tx_cal_'.$this->objectType);
	}

	function getMarker(& $template, & $sims, & $rems, & $wrapped, $view='') {
		if($view==''){
			$view = $this->conf['view'];
		}
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		/*if($this->objectType=='event'){
		 debug($allMarkers);
		 }*/
		foreach ($allMarkers as $marker) {
			switch ($marker) {
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if(method_exists($this,$funcFromMarker)) {
						$this->$funcFromMarker($template, $sims, $rems, $wrapped, $view);
					}
					break;
			}
		}

		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		$allSingleMarkers = array_diff($allSingleMarkers, $allMarkers);
		/*if($this->objectType=='event'){
		 debug($allSingleMarkers);
		 }*/
		$controller = &tx_cal_registry::Registry('basic','controller');
		
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'ACTIONURL':
				case 'L_ENTER_EMAIL':
				case 'L_CAPTCHA_TEXT':
				case 'CAPTCHA_SRC':
				case 'IMG_PATH':
					// do nothing
					break;
				default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $controller->pi_getLL('l_'.$this->objectType.'_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
						$this->$funcFromMarker($template, $sims, $rems, $wrapped, $view);
					}else if (preg_match('/MODULE__([A-Z0-9_-|])*/', $marker)) {
						$tmp=explode('___',substr($marker, 8));
						$modules[$tmp[0]][]=$tmp[1];
					} else if ($this->conf['view.'][$view.'.'][$this->objectType.'.'][strtolower($marker)]) {
						$this->conf['view.'][$view.'.'][$this->objectType.'.'][strtolower($marker).'.']['value'] = $this->row[strtolower($marker)];
						$cObj = &tx_cal_registry::Registry('basic','cobj');
						$sims['###' . $marker . '###'] = $cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->objectType.'.'][strtolower($marker)],$this->conf['view.'][$view.'.'][$this->objectType.'.'][strtolower($marker).'.']);
					} else {
						$sims['###' . $marker . '###'] = $this->row[strtolower($marker)];
					}
					break;
			}
		}
			
		#use alternativ way of MODULE__MARKER
		#syntax: ###MODULE__MODULENAME___MODULEMARKER###
		#collect them, call each Modul, retrieve Array of Markers and replace them
		#this allows to spread the Module-Markers over complete template instead of one time
		#also work with old way of MODULE__-Marker

		if(is_array($modules)) {  #MODULE-MARKER FOUND
			foreach($modules as $themodule=>$markerArray) {
				$module = t3lib_div :: makeInstanceService($themodule, 'module');
				if (is_object($module)) {
					if($markerArray[0]=='') {
						$sims['###MODULE__'.$themodule.'###'] = $module->start($this); #old way
					} else {
						$moduleMarker= $module->start($this); # get Markerarray from Module
						foreach($moduleMarker as $key=>$val) {
							$sims['###MODULE__'.$themodule.'___'.$key.'###'] = $val;
						}
					}
				}
			}
		}

		$hookObjectsArr = $controller->getHookObjectsArray('searchForObjectMarker');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postSearchForObjectMarker')) {
				$hookObj->postSearchForObjectMarker($this, $template, $sims, $rems, $wrapped, $view);
			}
		}
	}
	
	/**
	 * Absract method to be implemented by each class extending.
	 * @return int	-1,0,1 => less, equals, greater
	 */
	function compareTo($object){
		return -1;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_base_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_base_model.php']);
}
?>