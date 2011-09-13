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

require_once(t3lib_extMgm::extPath('cal').'model/class.tx_cal_abstract_model.php');

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_base_model extends tx_cal_abstract_model{
	var $prefixId = 'tx_cal_controller';
	var $cObj;
	var $local_cObj;
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
	
	var $cachedValueArray = Array();

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

	/**
	 * Returns the image marker
	 */
	function getImageMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$sims['###IMAGE###'] = '';
		$this->initLocalCObject();
		$this->local_cObj->setCurrentVal(implode(',',$this->getImage()));
		$sims['###IMAGE###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->getObjectType().'.']['image'], $this->conf['view.'][$view.'.'][$this->getObjectType().'.']['image.']);
	}

	/**
	 * Returns the current values as merged array.
	 * This method should be adapted in every model to contain all needed values.
	 * In short - every get-method (except the getXYMarker) should be in there.
	 */
	function getValuesAsArray() {
		// check if this locking variable is set - if so, we're currently within a getValuesAsArray call and <br />
		// thus we would end up in a endless recursion. So skip in that case. This can happen, when a method called by this method
		// is initiating the local_cObj f.e.
		if($this->initializingCacheValues) {
			return $this->row;
		}
		
		// for now try to cache the value array. I think the values don't change during final rendering of the event.
		// if this conflicts with anything, then don't cache it.		
		if(!is_array($this->cachedValueArray) || (is_array($this->cachedValueArray) && !count($this->cachedValueArray))) {
			// set locking variable
			$this->initializingCacheValues = true;
			
			$noAutoFetchMethods = $this->noAutoFetchMethods;
			if(is_object(parent) && count(parent::getNoAutoFetchMethods())) {
				$noAutoFetchMethods = array_merge(parent::getNoAutoFetchMethods(),$this->getNoAutoFetchMethods());
			}
			$cObj = &tx_cal_registry::Registry('basic','cobj');
			$autoFetchTextFields = explode(',',strtolower($this->conf['autoFetchTextFields']));
			$autoFetchTextSplitValue = $cObj->stdWrap($this->conf['autoFetchTextSplitValue'],$this->conf['autoFetchTextSplitValue.']);

			// prepare the basic value array
			$valueArray = array();
			$valueArray = $this->row;

			/* oldschool way by setting each variable by hand - that has to be more flexible, see below
			/*
			// basic record data
			$valueArray['uid'] = $this->getUid();
			$valueArray['pid'] = $this->getPid();
			$valueArray['type'] = $this->getType();
			$valueArray['hidden'] = $this->getHidden();
			// detail data
			$valueArray['description'] = $this->getDescription();
			$valueArray['image'] = $this->getImage();
			$valueArray['imagecaption'] = $this->getImageCaption();
			$valueArray['imagetitletext'] = $this->getImageTitleText();
			$valueArray['imagealttext'] = $this->getImageAltText();
			$valueArray['attachment'] = $this->getAttachment();
			$valueArray['attachmentcaption'] = $this->getAttachmentCaption();
			*/
			
			// new way - get everything dynamically
			if(!count($this->classMethodVars)) {
				// get all methods of this class and search for apropriate get-methods
				$classMethods = get_class_methods($this);
				if(count($classMethods)) {
					$this->classMethods = array();
					foreach($classMethods as $methodName) {
						// check if the methods name is get method, not a getMarker method and not this method itself (a loop wouldn't be that nice)
						if(substr($methodName,0,3) == "get" && substr($methodName,strlen($methodName)-6) != "Marker" && $methodName != 'getValuesAsArray' && $methodName != 'getCustomValuesAsArray' && !in_array($methodName,$this->noAutoFetchMethods)) {
							$varName = substr($methodName,3);
							// as final check that the method name seems to be propper, check if there is also a setter for it
							if(method_exists($this,'set'.$varName) ) {
								$this->classMethodVars[] = $varName;
							}
						}
					}
					unset($varName);
				}
			}

			// process the get methods and fill the valueArray dynamically
			if(count($this->classMethodVars)) {
				foreach($this->classMethodVars as $varName) {
					$methodName = 'get'.$varName;
					$methodValue = $this->$methodName();
					// convert any probable array to a comma list, except it contains objects
					if(is_array($methodValue) && !is_object($methodValue[0])) {
						if(in_array(strtolower($varName),$autoFetchTextFields)) {
							$methodValue = implode($autoFetchTextSplitValue,$methodValue);
						} else {
							$methodValue = implode(',',$methodValue);
						}
					}
					// now fill the array, except the methods return value is a object, which can't be used in TS
					if(!is_object($methodValue)) {
						$valueArray[strtolower($varName)] = $methodValue;
					}
				}
			}

			$additionalValues = $this->getAdditionalValuesAsArray();

			$mergedValues = array_merge($valueArray,$additionalValues);

			$controller = &tx_cal_registry::Registry('basic','controller');
			$hookObjectsArr = $controller->getHookObjectsArray('postGetValuesAsArray');
			// Hook: postGetValuesAsArray
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists($hookObj, 'postGetValuesAsArray')) {
					$hookObj->postGetValuesAsArray($this, $mergedValues);
				}
			}
			
			// now cache the result to win some ms
			$this->cachedValueArray = (array)$mergedValues;
			$this->initializingCacheValues = false;
		}
		return $this->cachedValueArray;
	}
	
	/**
	 * Returns a array with fieldname => value pairs, that should be additionally added to the values of the method getValuesAsArray
	 * This method is ment to be overwritten from inside a model, whereas the method getValuesAsArray should stay untouched from inside a model.
	 * @ return		array
	 */
	function getAdditionalValuesAsArray() {
		return array();
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
		if(($view == 'ics') || ($view == 'single_ics')) {
			$description = ereg_replace(chr(10).'|'.chr(13), '\n', html_entity_decode(ereg_replace('&nbsp;', ' ',strip_tags($this->getDescription()))));
		} else {
			$description = $this->getDescription();
		}

		$this->initLocalCObject();
		$this->local_cObj->setCurrentVal($description);
		
		if($this->striptags){
			$sims['###DESCRIPTION_STRIPTAGS###'] = strip_tags($this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->getObjectType().'.']['description'],$this->conf['view.'][$view.'.'][$this->getObjectType().'.']['description.']));
		}else{
			if($this->isPreview){
				$sims['###DESCRIPTION###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->getObjectType().'.']['preview'], $this->conf['view.'][$view.'.'][$this->getObjectType().'.']['preview.']);
			} else {
				$sims['###DESCRIPTION###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->getObjectType().'.']['description'],$this->conf['view.'][$view.'.'][$this->getObjectType().'.']['description.']);
			}
		}
	}

	function getHeadingMarker(& $template, & $sims, & $rems, & $wrapped, $view){
		$controller = &tx_cal_registry::Registry('basic','controller');
		$sims['###HEADING###'] = $controller->pi_getLL('l_'.$this->getObjectType());
	}
	
	function getEditPanelMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$controller = &tx_cal_registry::Registry('basic','controller');
		$sims['###EDIT_PANEL###'] = $controller->pi_getEditPanel($this->row, 'tx_cal_'.$this->getObjectType());
	}

	function getMarker(& $template, & $sims, & $rems, & $wrapped, $view='') {
		$controller = &tx_cal_registry::Registry('basic','controller');
		if($view==''){
			$view = !empty($this->conf['alternateRenderingView']) && is_array($this->conf['view.'][$this->conf['alternateRenderingView'].'.']) ? $this->conf['alternateRenderingView'] : $this->conf['view'];
		}
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique($match[1]);
		/*if($this->getObjectType()=='event'){
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
		/*if($this->getObjectType()=='event'){
		 debug($allSingleMarkers);
		 }*/

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
					// translation of label markers is now done in the method 'finish'.
					/*
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $controller->pi_getLL('l_'.$this->getObjectType().'_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;
					}
					*/
					if(preg_match('/.*_LABEL/',$marker) || preg_match('/L_.*/',$marker)) {
						continue;
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if(method_exists($this,$funcFromMarker)) {
						$this->$funcFromMarker($template, $sims, $rems, $wrapped, $view);
					}else if (preg_match('/MODULE__([A-Z0-9_-|])*/', $marker)) {
						$tmp=explode('___',substr($marker, 8));
						$modules[$tmp[0]][]=$tmp[1];
					} else if ($this->conf['view.'][$view.'.'][$this->getObjectType().'.'][strtolower($marker)]) {
						$current = '';
						$this->initLocalCObject();
						if($this->row[strtolower($marker)]!=''){
							$current = $this->row[strtolower($marker)];
						}
						$this->local_cObj->setCurrentVal($current);
						$sims['###' . $marker . '###'] = $this->local_cObj->cObjGetSingle($this->conf['view.'][$view.'.'][$this->getObjectType().'.'][strtolower($marker)],$this->conf['view.'][$view.'.'][$this->getObjectType().'.'][strtolower($marker).'.']);
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
	* Method for post processing the rendered event
	* @return processed content/output
	*/
	function finish(&$content) {
		$controller = &tx_cal_registry::Registry('basic','controller');
		$hookObjectsArr = $controller->getHookObjectsArray('finishModelRendering');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'preFinishModelRendering')) {
				$hookObj->postSearchForObjectMarker($this, $content);
			}
		}

		// translate output
		$this->translateLanguageMarker($content);

		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'postFinishModelRendering')) {
				$hookObj->postSearchForObjectMarker($this, $content);
			}
		}
		return $content;
	}

	function translateLanguageMarker(&$content) {
		// translate leftover markers
		preg_match_all('!(###|%%%)([A-Z0-9_-|]*)\_LABEL\1!is', $content, $match);
		$allLanguageMarkers = array_unique($match[2]);

		if (count($allLanguageMarkers)) {
			$controller = &tx_cal_registry::Registry('basic','controller');
			$sims = array();
			foreach ($allLanguageMarkers as $key => $marker) {
				$wrapper = $match[1][$key];
				$sims[$wrapper.$marker.'_LABEL'.$wrapper] = $controller->pi_getLL('l_'.strtolower($this->getObjectType().'_'.$marker));
			}
			if (count($sims)) {
				//$cObj = &tx_cal_registry::Registry('basic','cobj');
				$content = substituteMarkerArrayNotCached($content, $sims, array(), array());
			}
		}
		return $content;
	}

	/**
	 * Absract method to be implemented by each class extending.
	 * @return int	-1,0,1 => less, equals, greater
	 */
	function compareTo($object){
		return -1;
	}
	
	/** 
	* This one seems to be dead code. No call to this method found in whole cal source on 02-04-2008. So this can probably get removed. In the meantime I (Franz) commented this out. It's bad practice anyway if you ask me. Let the user decide how he likes to see stuff rendered.
	*/
	/*
	function getRTEParsedContent($content){
		$controller = &tx_cal_registry::Registry('basic','controller');
		return $controller->pi_RTEcssText($content);
	}
	*/

	/**
	 * Method to initialise a local content object, that can be used for customized TS rendering with own db values
	 * @param	$customData	array	Array with key => value pairs that should be used as fake db-values for TS rendering instead of the values of the current object
	 */	
	function initLocalCObject($customData = false) {
		if (!is_object($this->local_cObj)) {
			$this->local_cObj = &tx_cal_registry::Registry('basic','local_cObj');
		}
		if ($customData && is_array($customData)) {
			$this->local_cObj->data = $customData;
		} else {
			$this->local_cObj->data = $this->getValuesAsArray();
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_base_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/model/class.tx_cal_base_model.php']);
}
?>