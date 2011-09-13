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
	
	var $image;
	var $imageCation;
	var $imageTitleText;
	var $imageAltText;
	
	var $attachment = array();
	
	/**
	 *  Constructor.
	 *  @param	$controller	Object	Reference to tx_cal_controller
	 *  @param	$serviceKey String	The serviceKey for this model
	 */
	function tx_cal_base_model($controller, &$serviceKey){
		$this->controller = &tx_cal_registry::Registry('basic','controller');
		$this->conf = &tx_cal_registry::Registry('basic','conf');
		$this->rightsObj = &tx_cal_registry::Registry('basic','rightscontroller');
		$this->cObj = &tx_cal_registry::Registry('basic','cobj');
		$this->modelObj = &tx_cal_registry::Registry('basic','modelcontroller');
		$this->serviceKey = &$serviceKey;
	} 
	
	/**
	 *  Returns the image markers.
	 *  @param	$markerArray	Array	The array to add the image markers to
	 *  @param	$lConf			Array	The configuration array
	 *  @param	$isSingleView	Boolean	True = single view; default: false
	 *  @param	$absoluteUrl	Boolean True = generates absolute urls - for e.g. for xml-feeds; default: false
	 */
	function getImageMarkers(&$markerArray, $lConf, $isSingleView=false, $absoluteUrl=false) {
		// overwrite image sizes from TS with the values from the content-element if they exist.
		if ($this->conf['FFimgH'] || $this->conf['FFimgW']) {
			$lConf['image.']['file.']['maxW'] = $this->conf['FFimgW'];
			$lConf['image.']['file.']['maxH'] = $this->conf['FFimgH'];
		}

		if ($this->conf['imageMarkerFunc']) {
			$markerArray = $this->userProcess('imageMarkerFunc', array($markerArray, $lConf));
		} else {

			$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
			$imageNum = t3lib_div::intInRange($imageNum, 0, 100);
			$theImgCode = '';
			$imgs = t3lib_div::trimExplode(',', $this->getImage(), 1);
			$imgsCaptions = explode(chr(10), $this->getImageCaption());
			$imgsAltTexts = explode(chr(10), $this->getImageAltText());
			$imgsTitleTexts = explode(chr(10), $this->getImageTitleText());
			reset($imgs);

			$cc = 0;
			// remove first img from the image array in single view if the TSvar firstImageIsPreview is set
			if (count($imgs) > 1 && $this->conf['firstImageIsPreview'] && $isSingleView) {
				array_shift($imgs);
				array_shift($imgsCaptions);
				array_shift($imgsAltTexts);
				array_shift($imgsTitleTexts);
			}
			// get img array parts for single view pages
			if ($this->piVars[$this->conf['singleViewPointerName']]) {
				$spage = strip_tags($this->piVars[$this->conf['singleViewPointerName']]);
				$astart = $imageNum*$spage;
				$imgs = array_slice($imgs,$astart,$imageNum);
				$imgsCaptions = array_slice($imgsCaptions,$astart,$imageNum);
				$imgsAltTexts = array_slice($imgsAltTexts,$astart,$imageNum);
				$imgsTitleTexts = array_slice($imgsTitleTexts,$astart,$imageNum);
			}
			while (list(, $val) = each($imgs)) {
				if ($cc == $imageNum) break;
				if ($val) {
					$lConf['image.']['altText'] = $imgsAltTexts[$cc];
					$lConf['image.']['titleText'] = $imgsTitleTexts[$cc];
					if($lConf['image.']['overridePath']){
						$lConf['image.']['file'] = $lConf['image.']['overridePath'] . $val;
					}else{
						$lConf['image.']['file'] = 'uploads/tx_cal/pics/' . $val;
					}
				}
				if($absoluteUrl){
					$theImgCode .= '<img src="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->cObj->IMG_RESOURCE($lConf['image.']).'" />' . $this->cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				}else{
					$theImgCode .= $this->cObj->IMAGE($lConf['image.']) . $this->cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				}
				$cc++;
			}
			$markerArray['###IMAGE###'] = '';
			$markerArray['###ABS_IMAGE###'] = '';
			if ($cc) {
				$markerArray['###IMAGE###'] = $this->cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
			}
			if($absoluteUrl){
				$markerArray['###ABS_IMAGE###'] = $markerArray['###IMAGE###'];
			}
		}
		return $markerArray;
	}
	
	
	 /**
	  * Returns the image alt text(s)
	  */
	 function getImageAltText(){
	 	return $this->imageAltText;	
	 }
	 
	 /**
	  * Sets the image title text
	  * @param	$text	String	the image title text(s)
	  */
	 function setImageTitleText($text){
	 	if($text!=''){
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
	  * @param	$text	String	the image caption
	  */
	 function setImageCaption($text){
	 	if($text!=''){
	 		$this->imageCaption = $text;
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
	  * Sets the image alt text
	  * @param	$text	String	the image alt text(s)
	  */
	 function setImageAltText($text){
	 	if($text!=''){
	 		$this->imageAltText = $text;
	 	}
	 }
	 
	 /**
	  * Returns the attachment url
	  */
	 function getAttachmentURLs(){
	 	return $this->attachment;
	 }
	 
	 /**
	  * Adds an attachment url
	  * @param	$url	String	the url
	  */
	 function addAttachmentURL($url){
	 	$this->attachment[] = $url;
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
	 
	 function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()){
	 	return false;
	 }
	 
	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()){
	 	return false;
	 }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_base_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_base_model.php']);
}
?>