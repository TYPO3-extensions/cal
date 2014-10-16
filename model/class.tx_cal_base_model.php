<?php
/**
 * *************************************************************
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
 * *************************************************************
 */

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
class tx_cal_base_model extends tx_cal_abstract_model {
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
	var $image = Array ();
	var $imageCaption = Array ();
	var $imageTitleText = Array ();
	var $imageAltText = Array ();
	var $attachment = Array ();
	var $attachmentCaption = Array ();
	var $cachedValueArray = Array ();
	var $initializingCacheValues = false;
	var $templatePath;
	
	/**
	 * Constructor.
	 * 
	 * @param $serviceKey String
	 *        	serviceKey for this model
	 */
	function tx_cal_base_model($serviceKey) {
		$this->controller = &tx_cal_registry::Registry ('basic', 'controller');
		$this->conf = &tx_cal_registry::Registry ('basic', 'conf');
		$this->serviceKey = &$serviceKey;
	}
	
	/**
	 * Returns the image marker
	 */
	function getImageMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###IMAGE###'] = '';
		$this->initLocalCObject ();

		$sims ['###IMAGE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['image'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['image.']);
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
		if ($this->initializingCacheValues) {
			return $this->row;
		}
		
		// for now try to cache the value array. I think the values don't change during final rendering of the event.
		// if this conflicts with anything, then don't cache it.
		if (! is_array ($this->cachedValueArray) || (is_array ($this->cachedValueArray) && ! count ($this->cachedValueArray))) {
			// set locking variable
			$this->initializingCacheValues = true;
			
			$storeKey = get_class ($this);
			$cachedValues = $this->controller->cache->get ($storeKey);
			
			if ($cachedValues != '') {
				if ($this->conf ['writeCachingInfoToDevlog'] == 1) {
					t3lib_div::devLog ('CACHE HIT (' . __CLASS__ . '::' . __FUNCTION__ . ')', 'cal', - 1, array ());
				}
				$cachedValues = unserialize ($cachedValues);
				$this->classMethodVars = $cachedValues [0];
				$autoFetchTextFields = $cachedValues [1];
				$autoFetchTextSplitValue = $cachedValues [2];
			} else {
				$noAutoFetchMethods = $this->noAutoFetchMethods;
				if (is_object (parent) && count (parent::getNoAutoFetchMethods ())) {
					$noAutoFetchMethods = array_merge (parent::getNoAutoFetchMethods (), $this->getNoAutoFetchMethods ());
				}
				$cObj = &tx_cal_registry::Registry ('basic', 'cobj');
				$autoFetchTextFields = explode (',', strtolower ($this->conf ['autoFetchTextFields']));
				$autoFetchTextSplitValue = $cObj->stdWrap ($this->conf ['autoFetchTextSplitValue'], $this->conf ['autoFetchTextSplitValue.']);
				
				// new way - get everything dynamically
				if (! count ($this->classMethodVars)) {
					// get all methods of this class and search for apropriate get-methods
					$classMethods = get_class_methods ($this);
					if (count ($classMethods)) {
						$this->classMethods = array ();
						foreach ($classMethods as $methodName) {
							// check if the methods name is get method, not a getMarker method and not this method itself (a loop wouldn't be that nice)
							if (substr ($methodName, 0, 3) == "get" && substr ($methodName, strlen ($methodName) - 6) != "Marker" && $methodName != 'getValuesAsArray' && $methodName != 'getCustomValuesAsArray' && ! in_array ($methodName, $this->noAutoFetchMethods)) {
								$varName = substr ($methodName, 3);
								// as final check that the method name seems to be propper, check if there is also a setter for it
								if (method_exists ($this, 'set' . $varName)) {
									$this->classMethodVars [] = $varName;
								}
							}
						}
						unset ($varName);
					}
				}
				if ($this->conf ['writeCachingInfoToDevlog'] == 1) {
					t3lib_div::devLog ('CACHE MISS (' . __CLASS__ . '::' . __FUNCTION__ . ')', 'cal', 2, array ());
				}
				$this->controller->cache->set ($storeKey, serialize (Array (
						$this->classMethodVars,
						$autoFetchTextFields,
						$autoFetchTextSplitValue 
				)), __FUNCTION__);
			}
			
			// prepare the basic value array
			$valueArray = array ();
			$valueArray = $this->row;
			
			// process the get methods and fill the valueArray dynamically
			if (count ($this->classMethodVars)) {
				foreach ($this->classMethodVars as $varName) {
					$methodName = 'get' . $varName;
					$methodValue = $this->$methodName ();
					// convert any probable array to a comma list, except it contains objects
					if (is_array ($methodValue) && ! is_object ($methodValue [0])) {
						if (in_array (strtolower ($varName), $autoFetchTextFields)) {
							$methodValue = implode ($autoFetchTextSplitValue, $methodValue);
						} else {
							$methodValue = implode (',', $methodValue);
						}
					}
					// now fill the array, except the methods return value is a object, which can't be used in TS
					if (! is_object ($methodValue)) {
						$valueArray [strtolower ($varName)] = $methodValue;
					}
				}
			}
			
			$additionalValues = $this->getAdditionalValuesAsArray ();
			
			$mergedValues = array_merge ($valueArray, $additionalValues);
			
			$hookObjectsArr = tx_cal_functions::getHookObjectsArray ('tx_cal_base_model', 'postGetValuesAsArray', 'model');
			// Hook: postGetValuesAsArray
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists ($hookObj, 'postGetValuesAsArray')) {
					$hookObj->postGetValuesAsArray ($this, $mergedValues);
				}
			}
			
			// now cache the result to win some ms
			$this->cachedValueArray = (array) $mergedValues;
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
		return array ();
	}
	
	/**
	 * Returns the image alt text(s)
	 */
	function getImageAltText() {
		return $this->imageAltText;
	}
	
	/**
	 * Sets the image title text
	 * 
	 * @param $text Array
	 *        	title text(s)
	 */
	function setImageTitleText($text) {
		if (is_array ($text)) {
			$this->imageTitleText = $text;
		}
	}
	
	/**
	 * Returns the image title text(s)
	 */
	function getImageTitleText() {
		return $this->imageTitleText;
	}
	
	/**
	 * Sets the image caption
	 * 
	 * @param $caption Array
	 *        	caption
	 */
	function setImageCaption($caption) {
		if (is_array ($caption)) {
			$this->imageCaption = $caption;
		}
	}
	
	/**
	 * Returns the image caption
	 */
	function getImageCaption() {
		return $this->imageCaption;
	}
	
	/**
	 * Sets the images
	 * 
	 * @param $images blob
	 *        	more images
	 */
	function setImage($image) {
		if ($image != '') {
			$this->image = $image;
		}
	}
	
	/**
	 * Returns the image blob
	 */
	function getImage() {
		return $this->image;
	}
	
	/**
	 * Adds an image
	 * 
	 * @param $url String        	
	 */
	function addImage($image) {
		$this->image [] = $image;
	}
	
	/**
	 * Removes an image
	 * 
	 * @param $url String        	
	 */
	function removeImage($image) {
		for ($i = 0; $i < count ($this->image); $i ++) {
			if ($this->image [$i] == $image) {
				array_splice ($this->image, $i);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Sets the image alt text
	 * 
	 * @param $alt Array
	 *        	alt text(s)
	 */
	function setImageAltText($alt) {
		if (is_array ($alt)) {
			$this->imageAltText = $alt;
		}
	}
	
	/**
	 * Returns the attachment url
	 */
	function getAttachment() {
		return $this->attachment;
	}
	
	/**
	 * Adds an attachment url
	 * 
	 * @param $url String        	
	 */
	function addAttachment($url) {
		$this->attachment [] = $url;
	}
	
	/**
	 * Sets the attachments
	 * 
	 * @param $attachmentArray Array
	 *        	array
	 */
	function setAttachment($attachmentArray) {
		$this->attachment = $attachmentArray;
	}
	
	/**
	 * Removes an attachment url
	 * 
	 * @param $url String        	
	 */
	function removeAttachmentURL($url) {
		for ($i = 0; $i < count ($this->attachment); $i ++) {
			if ($this->attachment == $url) {
				array_splice ($this->attachment, $i);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns the attachment caption
	 */
	function getAttachmentCaption() {
		return $this->attachmentCaption;
	}
	
	/**
	 * Sets the attachment caption
	 */
	function setAttachmentCaption(&$caption) {
		return $this->attachmentCaption = $caption;
	}
	function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		return false;
	}
	function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		return false;
	}
	
	/**
	 * Returns the type value
	 * 
	 * @return Integer type.
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute.
	 * This should be the service type
	 * 
	 * @param $type String
	 *        	type
	 */
	function setType($type) {
		$this->type = $type;
	}
	function getObjectType() {
		return $this->objectType;
	}
	function setObjectType($type) {
		$this->objectType = $type;
	}
	function getUid() {
		return $this->uid;
	}
	function setUid($t) {
		$this->uid = $t;
	}
	function setPid($pid) {
		$this->pid = $pid;
	}
	function getPid() {
		return $this->pid;
	}
	
	/**
	 * Returns the hidden value.
	 * 
	 * @return Integer == true, 0 == false.
	 */
	function getHidden() {
		return $this->hidden;
	}
	
	/**
	 * Returns the hidden value.
	 * 
	 * @return Integer == true, 0 == false.
	 */
	function isHidden() {
		return $this->hidden;
	}
	
	/**
	 * Sets the hidden value.
	 * 
	 * @param $hidden Integer
	 *        	== true, 0 == false.
	 */
	function setHidden($hidden) {
		$this->hidden = $hidden;
	}
	function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		if (($view == 'ics') || ($view == 'single_ics')) {
			$description = preg_replace ('/,/', '\,', preg_replace ('/' . chr (10) . '|' . chr (13) . '/', '\r\n', html_entity_decode (preg_replace ('/&nbsp;/', ' ', strip_tags ($this->getDescription ())))));
		} else {
			$description = $this->getDescription ();
		}
		
		$this->initLocalCObject ();
		$this->local_cObj->setCurrentVal ($description);
		if ($this->striptags) {
			$sims ['###DESCRIPTION_STRIPTAGS###'] = strip_tags ($this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['description'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['description.']));
		} else {
			if ($this->isPreview) {
				$sims ['###DESCRIPTION###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['preview'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['preview.']);
			} else {
				$sims ['###DESCRIPTION###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['description'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['description.']);
			}
		}
	}
	function getHeadingMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		// controller = &tx_cal_registry::Registry('basic','controller');
		$sims ['###HEADING###'] = $this->controller->pi_getLL ('l_' . $this->getObjectType ());
	}
	function getEditPanelMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		// controller = &tx_cal_registry::Registry('basic','controller');
		$sims ['###EDIT_PANEL###'] = $this->controller->pi_getEditPanel ($this->row, 'tx_cal_' . $this->getObjectType ());
	}
	function getMarker(& $template, & $sims, & $rems, & $wrapped, $view = '', $base = 'view') {
		// controller = &tx_cal_registry::Registry('basic','controller');
		if ($view == '' && $base == 'view') {
			$view = ! empty ($this->conf ['alternateRenderingView']) && is_array ($this->conf [$base . '.'] [$this->conf ['alternateRenderingView'] . '.']) ? $this->conf ['alternateRenderingView'] : $this->conf ['view'];
		}
		preg_match_all ('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique ($match [1]);
		
		foreach ($allMarkers as $marker) {
			switch ($marker) {
				default :
					if (preg_match ('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div::makeInstanceService (substr ($marker, 8), 'module');
						if (is_object ($module)) {
							$rems ['###' . $marker . '###'] = $module->start ($this);
						}
					}
					$funcFromMarker = 'get' . str_replace (' ', '', ucwords (str_replace ('_', ' ', strtolower ($marker)))) . 'Marker';
					if (method_exists ($this, $funcFromMarker)) {
						$this->$funcFromMarker ($template, $sims, $rems, $wrapped, $view);
					}
					break;
			}
		}
		
		preg_match_all ('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique ($match [1]);
		$allSingleMarkers = array_diff ($allSingleMarkers, $allMarkers);
		
		foreach ($allSingleMarkers as $marker) {
			switch ($marker) {
				case 'ACTIONURL' :
				case 'L_ENTER_EMAIL' :
				case 'L_CAPTCHA_TEXT' :
				case 'CAPTCHA_SRC' :
				case 'IMG_PATH' :
					// do nothing
					break;
				default :
					// translation of label markers is now done in the method 'finish'.
					/*
					 * if(preg_match('/.*_LABEL/',$marker)){ $sims['###'.$marker.'###'] = $controller->pi_getLL('l_'.$this->getObjectType().'_'.strtolower(substr($marker,0,strlen($marker)-6))); continue; }
					 */
					if (preg_match ('/.*_LABEL$/', $marker) || preg_match ('/^L_.*/', $marker)) {
						continue;
					}
					$funcFromMarker = 'get' . str_replace (' ', '', ucwords (str_replace ('_', ' ', strtolower ($marker)))) . 'Marker';
					if (method_exists ($this, $funcFromMarker)) {
						$this->$funcFromMarker ($template, $sims, $rems, $wrapped, $view);
					} else if (preg_match ('/MODULE__([A-Z0-9_-|])*/', $marker)) {
						$tmp = explode ('___', substr ($marker, 8));
						$modules [$tmp [0]] [] = $tmp [1];
					} else if ($this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] [strtolower ($marker)]) {
						$current = '';
						
						// first, try to fill $current with a method of the model matching the markers name
						$functionName = 'get' . str_replace (' ', '', ucwords (str_replace ('_', ' ', strtolower ($marker))));
						if (method_exists ($this, $functionName)) {
							$tmp = $this->$functionName ();
							if (! is_object ($tmp) && ! is_array ($tmp)) {
								$current = $tmp;
							}
							unset ($tmp);
						}
						// if $current is still empty and we have a db-field matching the markers name, use this one
						if ($current == '' && $this->row [strtolower ($marker)] != '') {
							$current = $this->row [strtolower ($marker)];
						}
						
						$this->initLocalCObject ();
						$this->local_cObj->setCurrentVal ($current);
						$sims ['###' . $marker . '###'] = $this->local_cObj->cObjGetSingle ($this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] [strtolower ($marker)], $this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] [strtolower ($marker) . '.']);
					} else {
						$sims ['###' . $marker . '###'] = $this->row [strtolower ($marker)];
					}
					break;
			}
		}
		
		// alternativ way of MODULE__MARKER
		// syntax: ###MODULE__MODULENAME___MODULEMARKER###
		// collect them, call each Modul, retrieve Array of Markers and replace them
		// this allows to spread the Module-Markers over complete template instead of one time
		// also work with old way of MODULE__-Marker
		
		if (is_array ($modules)) { // MODULE-MARKER FOUND
			foreach ($modules as $themodule => $markerArray) {
				$module = t3lib_div::makeInstanceService ($themodule, 'module');
				if (is_object ($module)) {
					if ($markerArray [0] == '') {
						$sims ['###MODULE__' . $themodule . '###'] = $module->start ($this); // ld way
					} else {
						$moduleMarker = $module->start ($this); // get Markerarray from Module
						foreach ($moduleMarker as $key => $val) {
							if ($this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] ['module__' . strtolower ($themodule) . '___' . strtolower ($key)]) {
								$this->local_cObj->setCurrentVal ($val);
								$sims ['###MODULE__' . $themodule . '___' . strtoupper ($key) . '###'] = $this->local_cObj->cObjGetSingle ($this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] ['module__' . strtolower ($themodule) . '___' . strtolower ($key)], $this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] ['module__' . strtolower ($themodule) . '___' . strtolower ($key) . '.']);
							} else {
								$sims ['###MODULE__' . $themodule . '___' . strtoupper ($key) . '###'] = $val;
							}
						}
					}
				}
			}
		}
		
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray ('tx_cal_base_model', 'searchForObjectMarker', 'model');
		// Hook: postSearchForObjectMarker
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postSearchForObjectMarker')) {
				$hookObj->postSearchForObjectMarker ($this, $template, $sims, $rems, $wrapped, $view);
			}
		}
	}
	
	/**
	 * Method for post processing the rendered event
	 * 
	 * @return processed content/output
	 */
	function finish(&$content) {
		$hookObjectsArr = tx_cal_functions::getHookObjectsArray ('tx_cal_base_model', 'finishModelRendering', 'model');
		// Hook: preFinishModelRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'preFinishModelRendering')) {
				$hookObj->preFinishModelRendering ($this, $content);
			}
		}
		
		// translate output
		$this->translateLanguageMarker ($content);
		
		// Hook: postFinishModelRendering
		foreach ($hookObjectsArr as $hookObj) {
			if (method_exists ($hookObj, 'postFinishModelRendering')) {
				$hookObj->postFinishModelRendering ($this, $content);
			}
		}
		return $content;
	}
	function translateLanguageMarker(&$content) {
		// translate leftover markers
		preg_match_all ('!(###|%%%)([A-Z0-9_-|]*)\_LABEL\1!is', $content, $match);
		$allLanguageMarkers = array_unique ($match [2]);
		
		if (count ($allLanguageMarkers)) {
			// controller = &tx_cal_registry::Registry('basic','controller');
			$sims = array ();
			foreach ($allLanguageMarkers as $key => $marker) {
				$wrapper = $match [1] [$key];
				$label = $this->controller->pi_getLL ('l_' . strtolower ($this->getObjectType () . '_' . $marker));
				if ($label == '') {
					$label = $this->controller->pi_getLL ('l_event_' . strtolower ($marker));
				}
				$sims [$wrapper . $marker . '_LABEL' . $wrapper] = $label;
			}
			if (count ($sims)) {
				// $cObj = &tx_cal_registry::Registry('basic','cobj');
				$content = tx_cal_functions::substituteMarkerArrayNotCached ($content, $sims, array (), array ());
			}
		}
		return $content;
	}
	
	/**
	 * Absract method to be implemented by each class extending.
	 * 
	 * @return int => less, equals, greater
	 */
	function compareTo($object) {
		return - 1;
	}
	
	/**
	 * This one seems to be dead code.
	 * No call to this method found in whole cal source on 02-04-2008. So this can probably get removed. In the meantime I (Franz) commented this out. It's bad practice anyway if you ask me. Let the user decide how he likes to see stuff rendered.
	 */
	/*
	 * function getRTEParsedContent($content){ $controller = &tx_cal_registry::Registry('basic','controller'); return $controller->pi_RTEcssText($content); }
	 */
	
	/**
	 * Method to initialise a local content object, that can be used for customized TS rendering with own db values
	 * 
	 * @param $customData array
	 *        	key => value pairs that should be used as fake db-values for TS rendering instead of the values of the current object
	 */
	function initLocalCObject($customData = false) {
		if (! is_object ($this->local_cObj)) {
			$this->local_cObj = &tx_cal_registry::Registry ('basic', 'local_cObj');
		}
		if ($customData && is_array ($customData)) {
			$this->local_cObj->data = $customData;
		} else {
			$this->local_cObj->data = $this->getValuesAsArray ();
		}
	}
	function isSharedUser($userId, $groupIdArray) {
		if (is_array ($this->getSharedUsers ()) && in_array ($userId, $this->getSharedUsers ())) {
			return true;
		}
		foreach ($groupIdArray as $id) {
			if (is_array ($this->getSharedGroups ()) && in_array ($id, $this->getSharedGroups ())) {
				return true;
			}
		}
		
		return false;
	}
	function getIsAllowedToEdit() {
		return $this->isUserAllowedToEdit () ? 1 : 0;
	}
	function getIsAllowedToDelete() {
		return $this->isUserAllowedToDelete () ? 1 : 0;
	}
	function setIsAllowedToEdit() {
		// Dummy function to get the value filled automatically of the getIsAllowedToEdit function
	}
	function setIsAllowedToDelete() {
		// Dummy function to get the value filled automatically of the getIsAllowedToDelete function
	}
	function fillTemplate($subpartMarker) {
		$cObj = &tx_cal_registry::Registry ('basic', 'cobj');
		
		$page = $cObj->fileResource ($this->templatePath);
		
		if ($page == '') {
			return tx_cal_functions::createErrorMessage ('No ' . $this->objectType . ' template file found at: >' . $this->templatePath . '<.', 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.');
		}
		$page = $cObj->getSubpart ($page, $subpartMarker);
		
		if (! $page) {
			return tx_cal_functions::createErrorMessage ('Could not find the >' . str_replace ('###', '', $subpartMarker) . '< subpart-marker in ' . $this->templatePath, 'Please add the subpart >' . str_replace ('###', '', $subpartMarker) . '< to your ' . $this->templatePath);
		}
		$rems = array ();
		$sims = array ();
		$wrapped = array ();
		$this->getMarker ($page, $sims, $rems, $wrapped);
		return $this->finish (tx_cal_functions::substituteMarkerArrayNotCached ($page, $sims, $rems, $wrapped));
	}
}

if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_cal_base_model.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_cal_base_model.php']);
}
?>