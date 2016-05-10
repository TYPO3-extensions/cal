<?php
namespace TYPO3\CMS\Cal\Model;
/**
 * This file is part of the TYPO3 extension Calendar Base (cal).
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 extension Calendar Base (cal) project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */
abstract class BaseModel extends \TYPO3\CMS\Cal\Model\AbstractModel {
	var $prefixId = 'tx_cal_controller';
	var $cObj;
	var $local_cObj;
	var $conf;
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
	var $attachment = Array ();
	var $cachedValueArray = Array ();
	var $initializingCacheValues = false;
	var $templatePath;
	
	/**
	 * Constructor.
	 * 
	 * @param $serviceKey String
	 *        	serviceKey for this model
	 */
	public function __construct($serviceKey) {
		$this->controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'controller');
		$this->conf = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'conf');
		$this->serviceKey = &$serviceKey;
		
		$this->images = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}
	
	/**
	 * Returns the image marker
	 */
	public function getImageMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$sims ['###IMAGE###'] = '';
		$this->initLocalCObject ();

		$sims ['###IMAGE###'] = $this->local_cObj->cObjGetSingle ($this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['image'], $this->conf ['view.'] [$view . '.'] [$this->getObjectType () . '.'] ['image.']);
	}
	
	/**
	 * Returns the current values as merged array.
	 * This method should be adapted in every model to contain all needed values.
	 * In short - every get-method (except the getXYMarker) should be in there.
	 */
	public function getValuesAsArray() {
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
					GeneralUtility::devLog ('CACHE HIT (' . __CLASS__ . '::' . __FUNCTION__ . ')', 'cal', - 1, array ());
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
				$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
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
					GeneralUtility::devLog ('CACHE MISS (' . __CLASS__ . '::' . __FUNCTION__ . ')', 'cal', 2, array ());
				}
				$this->controller->cache->set ($storeKey, serialize (Array (
						$this->classMethodVars,
						$autoFetchTextFields,
						$autoFetchTextSplitValue 
				)), __FUNCTION__);
			}
			
			// prepare the basic value array
			$valueArray = Array ();
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
			
			$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_base_model', 'postGetValuesAsArray', 'model');
			// Hook: postGetValuesAsArray
			foreach ($hookObjectsArr as $hookObj) {
				if (method_exists ($hookObj, 'postGetValuesAsArray')) {
					$hookObj->postGetValuesAsArray ($this, $mergedValues);
				}
			}
			
			// now cache the result to win some ms
			$this->cachedValueArray = (Array) $mergedValues;
			$this->initializingCacheValues = false;
		}
		return $this->cachedValueArray;
	}
	
	/**
	 * Returns a array with fieldname => value pairs, that should be additionally added to the values of the method getValuesAsArray
	 * This method is ment to be overwritten from inside a model, whereas the method getValuesAsArray should stay untouched from inside a model.
	 * @ return		array
	 */
	public function getAdditionalValuesAsArray() {
		return array ();
	}
	
	/**
	 * Sets the images
	 * 
	 * @param $images blob
	 *        	more images
	 */
	public function setImage($image) {
		if (is_array($image)) {
			$this->image = $image;
		}
	}
	
	/**
	 * Returns the image blob
	 */
	public function getImage() {
		return $this->image;
	}
	
	public function getImages() {
		$fileRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		return $fileRepository->findByRelation('tx_cal_'.$this->getObjectType(), 'image', $this->getUid());
	}
	
	/**
	 * Adds an image
	 * 
	 * @param $url String        	
	 */
	public function addImage($image) {
		$this->image [] = $image;
	}
	
	/**
	 * Removes an image
	 * 
	 * @param $url String        	
	 */
	public function removeImage($image) {
		for ($i = 0; $i < count ($this->image); $i ++) {
			if ($this->image [$i] == $image) {
				array_splice ($this->image, $i);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns the attachment url
	 */
	public function getAttachment() {
		return $this->attachment;
	}
	
	public function getAttachments() {
		$fileRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		return $fileRepository->findByRelation('tx_cal_'.$this->getObjectType(), 'attachment', $this->getUid());
	}
	
	/**
	 * Adds an attachment url
	 * 
	 * @param $url String        	
	 */
	public function addAttachment($url) {
		$this->attachment [] = $url;
	}
	
	/**
	 * Sets the attachments
	 * 
	 * @param $attachmentArray Array
	 *        	array
	 */
	public function setAttachment($attachmentArray) {
		$this->attachment = $attachmentArray;
	}
	
	/**
	 * Removes an attachment url
	 * 
	 * @param $url String        	
	 */
	public function removeAttachmentURL($url) {
		for ($i = 0; $i < count ($this->attachment); $i ++) {
			if ($this->attachment == $url) {
				array_splice ($this->attachment, $i);
				return true;
			}
		}
		return false;
	}
	
	public function isUserAllowedToEdit($feUserUid = '', $feGroupsArray = array ()) {
		return false;
	}
	
	public function isUserAllowedToDelete($feUserUid = '', $feGroupsArray = array ()) {
		return false;
	}
	
	/**
	 * Returns the type value
	 * 
	 * @return Integer type.
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Sets the type attribute.
	 * This should be the service type
	 * 
	 * @param $type String
	 *        	type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	public function getObjectType() {
		return $this->objectType;
	}
	
	public function setObjectType($type) {
		$this->objectType = $type;
	}
	
	public function getUid() {
		return $this->uid;
	}
	
	public function setUid($t) {
		$this->uid = $t;
	}
	
	public function setPid($pid) {
		$this->pid = $pid;
	}
	
	public function getPid() {
		return $this->pid;
	}
	
	/**
	 * Returns the hidden value.
	 * 
	 * @return Integer == true, 0 == false.
	 */
	public function getHidden() {
		return $this->hidden;
	}
	
	/**
	 * Returns the hidden value.
	 * 
	 * @return Integer == true, 0 == false.
	 */
	public function isHidden() {
		return $this->hidden;
	}
	
	/**
	 * Sets the hidden value.
	 * 
	 * @param $hidden Integer
	 *        	== true, 0 == false.
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}
	
	public function getDescriptionMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
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
	
	public function getHeadingMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
		$sims ['###HEADING###'] = $this->controller->pi_getLL ('l_' . $this->getObjectType ());
	}
	
	public function getEditPanelMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
		$sims ['###EDIT_PANEL###'] = $this->controller->pi_getEditPanel ($this->row, 'tx_cal_' . $this->getObjectType ());
	}
	
	public function getMarker(& $template, & $sims, & $rems, & $wrapped, $view = '', $base = 'view') {
		// controller = &\TYPO3\CMS\Cal\Utility\Registry::Registry('basic','controller');
		if ($view == '' && $base == 'view') {
			$view = ! empty ($this->conf ['alternateRenderingView']) && is_array ($this->conf [$base . '.'] [$this->conf ['alternateRenderingView'] . '.']) ? $this->conf ['alternateRenderingView'] : $this->conf ['view'];
		}
		$match = array();
		preg_match_all ('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);
		$allMarkers = array_unique ($match [1]);
		
		foreach ($allMarkers as $marker) {
			switch ($marker) {
				default :
					if (preg_match ('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = GeneralUtility::makeInstanceService (substr ($marker, 8), 'module');
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
		$modules = array();
		
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
				$module = GeneralUtility::makeInstanceService ($themodule, 'module');
				if (is_object ($module)) {
					if ($markerArray [0] == '') {
						$sims ['###MODULE__' . $themodule . '###'] = $module->start ($this); // ld way
					} else {
						$moduleMarker = $module->start ($this, TRUE); // get Markerarray from Module
						if(is_array($moduleMarker)) {
							foreach ($markerArray as $key => $requestedKey) {
								if (array_key_exists('###'.$requestedKey.'###', $moduleMarker)) {
									$val = $moduleMarker['###'.$requestedKey.'###'];
									if ($this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] ['module__' . strtolower ($themodule) . '___' . strtolower ($requestedKey)]) {
										$this->local_cObj->setCurrentVal ($val);
										$sims ['###MODULE__' . $themodule . '___' . strtoupper ($requestedKey) . '###'] = $this->local_cObj->cObjGetSingle ($this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] ['module__' . strtolower ($themodule) . '___' . strtolower ($requestedKey)], $this->conf [$base . '.'] [$view . '.'] [$this->getObjectType () . '.'] ['module__' . strtolower ($themodule) . '___' . strtolower ($requestedKey) . '.']);
									} else {
										$sims ['###MODULE__' . $themodule . '___' . strtoupper ($requestedKey) . '###'] = $val;
									}
								} else {
									$sims ['###MODULE__' . $themodule . '___' . strtoupper ($requestedKey) . '###'] = 'Could not find the marker "'.$requestedKey.'" in the module '.$themodule.' template.';
								}
							
							}
						}
					}
				}
			}
		}
		
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_base_model', 'searchForObjectMarker', 'model');
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
	public function finish(&$content) {
		$hookObjectsArr = \TYPO3\CMS\Cal\Utility\Functions::getHookObjectsArray ('tx_cal_base_model', 'finishModelRendering', 'model');
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
	
	public function translateLanguageMarker(&$content) {
		// translate leftover markers
		$match = array();
		preg_match_all ('!(###|%%%)([A-Z0-9_-|]*)\_LABEL\1!is', $content, $match);
		$allLanguageMarkers = array_unique ($match [2]);
		if (count ($allLanguageMarkers)) {
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
				$content = \TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($content, $sims, array (), array ());
			}
		}
		return $content;
	}
	
	/**
	 * Abstract method to be implemented by each class extending.
	 * 
	 * @return int => less, equals, greater
	 */
	public function compareTo($object) {
		return - 1;
	}
	
	/**
	 * Method to initialise a local content object, that can be used for customized TS rendering with own db values
	 * 
	 * @param $customData array
	 *        	key => value pairs that should be used as fake db-values for TS rendering instead of the values of the current object
	 */
	public function initLocalCObject($customData = false) {
		if (! is_object ($this->local_cObj)) {
			$this->local_cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'local_cObj');
		}
		if ($customData && is_array ($customData)) {
			$this->local_cObj->data = $customData;
		} else {
			$this->local_cObj->data = $this->getValuesAsArray ();
		}
	}
	
	public function isSharedUser($userId, $groupIdArray) {
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
	
	public function getIsAllowedToEdit() {
		return $this->isUserAllowedToEdit () ? 1 : 0;
	}
	
	public function getIsAllowedToDelete() {
		return $this->isUserAllowedToDelete () ? 1 : 0;
	}
	
	public function setIsAllowedToEdit() {
		// Dummy function to get the value filled automatically of the getIsAllowedToEdit function
	}
	
	public function setIsAllowedToDelete() {
		// Dummy function to get the value filled automatically of the getIsAllowedToDelete function
	}
	
	public function fillTemplate($subpartMarker) {
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		
		$page = $cObj->fileResource ($this->templatePath);
		
		if ($page == '') {
			return \TYPO3\CMS\Cal\Utility\Functions::createErrorMessage ('No ' . $this->objectType . ' template file found at: >' . $this->templatePath . '<.', 'Please make sure the path is correct and that you included the static template and double-check the path using the Typoscript Object Browser.');
		}
		$page = $cObj->getSubpart ($page, $subpartMarker);
		
		if (! $page) {
			return \TYPO3\CMS\Cal\Utility\Functions::createErrorMessage ('Could not find the >' . str_replace ('###', '', $subpartMarker) . '< subpart-marker in ' . $this->templatePath, 'Please add the subpart >' . str_replace ('###', '', $subpartMarker) . '< to your ' . $this->templatePath);
		}
		$rems = array ();
		$sims = array ();
		$wrapped = array ();
		$this->getMarker ($page, $sims, $rems, $wrapped);
		return $this->finish (\TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, $rems, $wrapped));
	}
}

?>