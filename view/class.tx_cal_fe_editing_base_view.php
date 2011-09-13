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

require_once (t3lib_extMgm :: extPath('cal').'view/class.tx_cal_base_view.php');

/**
 * A service which serves as base for all fe-editing clases.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class tx_cal_fe_editing_base_view extends tx_cal_base_view {

	var $object;
	var $objectString = '';
	var $isEditMode = false;
	var $isConfirm = false;
	
	var $serviceName = 'insertServiceName';
	var $table = 'insertTableName';
	var $lastPiVars = Array();
	
	function tx_cal_fe_editing_base_view(){
		$this->tx_cal_base_view();
	}
	
	function getTemplateSingleMarker(& $template, & $sims, & $rems, $view) {
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
            $required = '';
			switch ($marker) {
                default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_'.$this->objectString.'_'.strtolower(substr($marker,0,strlen($marker)-6)));
						if($sims['###'.$marker.'###'] == ''){
							$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_'.strtolower(substr($marker,0,strlen($marker)-6)));
						}
						continue;	
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $sims, $rems, $view);
					}else{
						$functionName = 'get'.ucwords(strtolower($marker));
						if($this->isConfirm && $this->isAllowed($marker)){
							$value = '';
							if(method_exists($this->object,$functionName)){
								$value = $this->object->$functionName();
							} else {
								$value = $this->object->row[strtolower($marker)];
							}

							$sims['###' . $marker . '_VALUE###'] = $value;
							$sims['###' . $marker . '###'] = $this->applyStdWrap($value,strtolower($marker).'_stdWrap');
						}else if ($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString,$marker)) {
							/**
							 * @fixme	Quick fix for the RTE related Javascript.  
							 * If the marker hasn't been defined already and the value for the marker isn't blank, set it.  Otherwise, let the previous value stick.
							 */
							if(!($sims['###' . $marker . '###'] && !$this->object->row[$marker])){
								$sims['###' . $marker . '_VALUE###'] = $this->object->row[$marker];
								$sims['###' . $marker . '###'] = $this->applyStdWrap($this->object->row[strtolower($marker)],strtolower($marker).'_stdWrap');
							}
						}else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString,$marker)){
							$value = '';
							if(method_exists($this->object,$functionName)){
								$value = $this->object->$functionName();
							}
							if($value == '') {
								if( !empty($this->conf['rights.']['create.'][$this->objectString.'.']['fields.'][strtolower($marker).'.']['default'])) {
									$value = $this->conf['rights.']['create.'][$this->objectString.'.']['fields.'][strtolower($marker).'.']['default'];
								}else{
									$value = $this->object->row[strtolower($marker)];
								}
							}
							/**
							 * @fixme	Quick fix for the RTE related Javascript.  
							 * If the marker hasn't been defined already and the value for the marker isn't blank, set it.  Otherwise, let the previous value stick.
							 */
							if(!($sims['###' . $marker . '###'] && $value == '')){
								$sims['###' . $marker . '###'] = $this->applyStdWrap($value,strtolower($marker).'_stdWrap');
							}
							
						}else {
							$sims['###' . $marker . '###'] = '';
							$sims['###' . $marker . '_VALUE###'] = '';
						}
					}
					if(!$this->isConfirm && $this->conf['rights.'][($this->isEditMode?'edit':'create').'.'][$this->objectString.'.']['fields.'][strtolower($marker).'.']['required']){
						$required = $this->conf['view.']['required'];
					}	
					$sims['###' . $marker . '###'] = str_replace('###REQUIRED###', $required, $sims['###' . $marker . '###']);			
					break;
			}
		}				
	}
	
	function getTemplateSubpartMarker(& $template, & $sims, & $rems, & $wrapped) {
		
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

		$allMarkers = array_unique($match[1]);

		foreach ($allMarkers as $marker) {
          
			switch ($marker) {

				case 'FORM_START' :
					$this->getFormStartMarker($template, $sims, $rems, $wrapped);
					break;
				case 'FORM_END' :
					$this->getFormEndMarker($template, $sims, $rems, $wrapped);
					break;
				default :
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$rems['###' . $marker . '###'] = $module->start($this);
						}
					} 
					break;
			}
		}
	}
	
	function getFormStartMarker(& $template, & $sims, & $rems, & $wrapped){
		$rems['###FORM_START###'] = $this->cObj->getSubpart($template, '###FORM_START###');
		
	}
	
	function getFormEndMarker(& $template, & $sims, & $rems, & $wrapped){
		$temp = $this->cObj->getSubpart($template, '###FORM_END###');
		$temp_sims = array();
		$linkParams = $this->controller->shortenLastViewAndGetTargetViewParameters();
		//$linkParams = array_merge($this->lastPiVars,$linkParams);
		$preLinkParams = Array();
		foreach($linkParams as $key=>$value){
			$preLinkParams[$this->prefixId.'['.$key.']'] = $value;
		}
		$this->controller->pi_linkTP('', $preLinkParams, 0, $this->conf['clear_anyway'],$linkParams['page_id']);
		$temp_sims['###BACK_LINK###'] = $this->cObj->lastTypoLinkUrl;
		$temp_sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$temp_sims['###L_SUBMIT###'] = $this->controller->pi_getLL('l_submit');
		$temp_sims['###L_SAVE###'] = $this->controller->pi_getLL('l_save');
		$temp_sims['###L_DELETE###'] = $this->controller->pi_getLL('l_delete');
		$rems['###FORM_END###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
	}
	
	function getHiddenMarker(& $template, & $sims, & $rems, $view){
		$sims['###HIDDEN###'] = '';	
		if($this->isConfirm){
			$sims['###HIDDEN_VALUE###'] = '';
			if($this->isAllowed('hidden')){
				if ($this->object->isHidden()) {
					$value = 1;
					$label = $this->controller->pi_getLL('l_true');
				} else {
					$value = 0;
					$label = $this->controller->pi_getLL('l_false');
				}
				$sims['###HIDDEN###'] = $this->applyStdWrap($label, 'hidden_stdWrap');
				$sims['###HIDDEN_VALUE###'] = $value;
			}
		}else{
			$sims['###HIDDEN###'] = '';
			if($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString, 'hidden')){
				$hidden = '';
				if($this->conf['rights.']['edit.'][$this->objectString.'.']['fields.']['hidden.']['default']){
					$hidden = ' checked="checked" ';
				}
				$sims['###HIDDEN###'] = $this->cObj->stdWrap($this->object->isHidden(), $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
			} else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString, 'hidden')){
				$hidden = '';
				if($this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['hidden.']['default'] || $this->controller->piVars['hidden']){
					$hidden = ' checked="checked" ';
				}
				$sims['###HIDDEN###'] = $this->applyStdWrap($hidden, 'hidden_stdWrap');
			}
		}
	}
	
	function getTitleMarker(& $template, & $sims, & $rems) {
		$sims['###TITLE###'] = '';
		$sims['###TITLE_VALUE###'] = '';
		if($this->isAllowed('title')) {
			$title = strip_tags($this->object->getTitle());
			$sims['###TITLE###'] = $this->applyStdWrap($title, 'title_stdWrap');
			$sims['###TITLE_VALUE###'] = $title;
		}
	}
	
	function getDescriptionMarker(& $template, & $sims, & $rems){
		$sims['###DESCRIPTION###'] = '';
		if($this->isAllowed('description')){
			$sims['###DESCRIPTION###'] = $this->applyStdWrap($this->object->getDescription(), 'description_stdWrap');
			$sims['###DESCRIPTION_VALUE###'] = htmlspecialchars($this->object->getDescription());
		}
	}
	
	function getCalendarIdMarker(& $template, & $sims, & $rems) {
		$sims['###CALENDAR_ID###'] = '';
		if($this->isAllowed('calendar_id')) {
			$calendarID = $this->object->getCalendarUid();	
			$tempCal = $this->conf['calendar'];
			$this->conf['calendar'] = '';
			$calendarArray = $this->modelObj->findAllCalendar('tx_cal_calendar',$this->conf['pidList']);
			$this->conf['calendar'] = $tempCal;
			
			if (empty($calendarArray['tx_cal_calendar'])) {
				return '<h3>You have to create a calendar before you can create events</h3>';
			}
			
			$calendarSelect = '';
			if(!empty($calendarArray['tx_cal_calendar'])){
				if($this->objectString == 'category' && $this->rightsObj->isAllowedToCreateGeneralCategory()){
					$calendarSelect .= '<option value="0" >'.$this->controller->pi_getLL('l_global_category').'</option>';
				}else{
					$calendarSelect .= '<option value="" >'.$this->controller->pi_getLL('l_select').'</option>';
				}
			}
			foreach($calendarArray['tx_cal_calendar'] as $calendar) {
				if($this->objectString == 'calendar'){
					if($calendar->isUserAllowedToEdit() || $calendar->isUserAllowedToDelete()){
						if($calendar->getUid() == $calendarID) {
							$selected = 'selected="selected"';
						} else {
							$selected = '';
						}
						$calendarSelect .= '<option value="'.$calendar->getUid().'" '.$selected.'>'.$calendar->getTitle().'</option>';
					}
				} else if ($this->objectString == 'event'){
					if(!$this->rightsObj->isAllowedToCreatePublicEvent() && $calendar->isPublic()){
						//do nothing
					} else {
						if($calendar->getUid() == $calendarID) {
							$selected = 'selected="selected"';
						} else {
							$selected = '';
						}
						$calendarSelect .= '<option value="'.$calendar->getUid().'" '.$selected.'>'.$calendar->getTitle().'</option>';
					}
				} else {
					if($calendar->getUid() == $calendarID) {
						$selected = 'selected="selected"';
					} else {
						$selected = '';
					}
					$calendarSelect .= '<option value="'.$calendar->getUid().'" '.$selected.'>'.$calendar->getTitle().'</option>';
				}
			}
			/* Only in create */
			if(count($calendarArray['tx_cal_calendar'])==1){
				$this->conf['switch_calendar']=$calendarArray['tx_cal_calendar'][0]->getUid();
			}
			
			$sims['###CALENDAR_ID###'] = $this->applyStdWrap($calendarSelect, 'calendar_id_stdWrap');	
		}
	}
	
	function getImageMarker(& $template, & $sims, & $rems){
		$this->getFileMarker('image', $template, $sims, $rems);
	}
	
	function getImageCaptionMarker(& $template, & $sims, & $rems) {
		$sims['###IMAGE_CAPTION###'] = '';
		$sims['###IMAGE_CAPTION_VALUE###'] = '';
		if($this->isAllowed('image_caption')) {
			$caption = implode(chr(10),(Array)$this->object->getImageCaption());
			$sims['###IMAGE_CAPTION###'] = $this->applyStdWrap($caption, 'image_caption_stdWrap');
			$sims['###IMAGE_CAPTION_VALUE###'] = $caption;
		}
	}
	
	function getImageTitleMarker(& $template, & $sims, & $rems) {
		$sims['###IMAGE_TITLE###'] = '';
		$sims['###IMAGE_TITLE_VALUE###'] = '';
		if($this->isAllowed('image_title')) {
			$imageTitleText =implode(chr(10),(Array)$this->object->getImageTitleText());
			$sims['###IMAGE_TITLE###'] = $this->applyStdWrap($imageTitleText, 'image_title_stdWrap');
			$sims['###IMAGE_TITLE_VALUE###'] = $imageTitleText;
		}
	}
	
	function getImageAltMarker(& $template, & $sims, & $rems) {
		$sims['###IMAGE_ALT###'] = '';
		$sims['###IMAGE_ALT_VALUE###'] = '';
		if($this->isAllowed('image_title')) {
			$imageTitleText = implode(chr(10),(Array)$this->object->getImageAltText());
			$sims['###IMAGE_ALT###'] = $this->applyStdWrap($imageTitleText, 'image_alt_stdWrap');
			$sims['###IMAGE_ALT_VALUE###'] = $imageTitleText;
		}
	}
	
	function getAttachmentMarker(& $template, & $sims, & $rems){
		$this->getFileMarker('attachment', $template, $sims, $rems);
	}
	
	function getAttachmentCaptionMarker(& $template, & $sims, & $rems) {
		$sims['###ATTACHMENT_CAPTION###'] = '';
		$sims['###ATTACHMENT_CAPTION_VALUE###'] = '';
		if($this->isAllowed('attachment_caption')) {
			$caption = implode(chr(10),(Array)$this->object->getAttachmentCaption());
			$sims['###ATTACHMENT_CAPTION###'] = $this->applyStdWrap($caption, 'attachment_caption_stdWrap');
			$sims['###ATTACHMENT_CAPTION_VALUE###'] = $caption;
		}
	}
	
	function getIcsFileMarker(& $template, & $sims, & $rems){
		$this->getFileMarker('ics_file', $template, $sims, $rems);
	}
	
	function getFileMarker($marker, & $template, & $sims, & $rems){
		global $TYPO3_CONF_VARS,$TCA;
		
		require_once (PATH_t3lib . 'class.t3lib_basicfilefunc.php');
		t3lib_div::loadTCA('tx_cal_'.$this->objectString);
		$max = $TCA['tx_cal_'.$this->objectString]['columns'][$marker]['config']['size'];
		$sims['###'.strtoupper($marker).'###'] = '';
		$sims['###'.strtoupper($marker).'_VALUE###'] = '';
		if(!$this->isAllowed($marker)){
			return;
		}

		if($this->isConfirm){
			$sims['###'.strtoupper($marker).'###'] = '';
			
			$fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
			$all_files = Array();
			$all_files['webspace']['allow'] = '*';
			$all_files['webspace']['deny'] = '';
			$fileFunc->init('', $all_files);
			$allowedExt = array();
			$denyExt = array();
			if($marker=='image'){
				$allowedExt = explode(',',$TYPO3_CONF_VARS['GFX']['imagefile_ext']);
			}else if($marker=='attachment'){
				$allowedExt = explode(',',$TYPO3_CONF_VARS['BE']['fileExtensions']['webspace']['allow']);
				$denyExt = explode(',',$TYPO3_CONF_VARS['BE']['fileExtensions']['webspace']['deny']);
			}
			
			if (is_array($_FILES[$this->prefixId]['name'][$marker])) {
				foreach($_FILES[$this->prefixId]['name'][$marker] as $id => $filename){
					$theDestFile = '';
					$iConf = $this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.'];
					if($_FILES[$this->prefixId]['error'][$marker][$id]){
						continue;
					}else{
						$theFile = t3lib_div::upload_to_tempfile($_FILES[$this->prefixId]['tmp_name'][$marker][$id]);
						$fI = t3lib_div::split_fileref($filename);
						if(in_array($fI['fileext'],$denyExt)){
							continue;
						}else if($marker=='image' && !in_array($fI['fileext'],$allowedExt)){
							continue;
						}
						$theDestFile = $fileFunc->getUniqueName($fileFunc->cleanFileName($fI['file']), 'typo3temp');
						t3lib_div::upload_copy_move($theFile,$theDestFile);
						$iConf['file'] = $theDestFile;
						$return = '__NEW__'.basename($theDestFile);
					}

					$temp_sims = Array();
					$temp_sims['###'.strtoupper($marker).'_VALUE###'] = $return;
					$temp = '';
					if($marker=='image'){
						$temp = $this->renderImage($iConf['file'],'','','', $marker, true);
					}else if($marker=='attachment' || $marker=='ics_file'){
						$temp = $this->renderFile($iConf['file'],'', $marker, true);
					}
					$sims['###'.strtoupper($marker).'###'] .= $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
				}
			}
			
			$files = Array();
			if($this->isEditMode){
				if($marker=='image'){
					$files = $this->object->getImage();
				}else if($marker=='attachment'){
					$files = $this->object->getAttachment();
				}else{
					$files = t3lib_div::trimExplode(',',$this->object->row[$marker]);
				}
			}
			
			$removeFiles = $this->controller->piVars['remove_'.$marker]?$this->controller->piVars['remove_'.$marker]:Array();
			$files = array_diff($files, $removeFiles);

			$caption = Array();
			$title = Array();
			$alt = Array();

			switch($marker){
				case 'image': {
					$caption = $this->object->getImageCaption();
					$title = $this->object->getImageTitleText();
					$alt = $this->object->getImageAltText();
					break;
				}
				case 'attachment': {
					$caption = $this->object->getAttachmentCaption();
					break;
				}
			}
			$i = 0;
			foreach($files as $file){
				$i++;
				if($i <= $max){
					$temp = '';
					if($marker=='image'){
						$temp = $this->renderImage($file,$caption[$key], $title[$i], $alt[$i], $marker, false);
					}else if($marker=='attachment' || $marker=='ics_file'){
						$temp = $this->renderFile($file,$caption[$i], $marker, false);
					}
					$temp_sims = Array();
					$temp_sims['###'.strtoupper($marker).'_VALUE###'] = $file;
					$sims['###'.strtoupper($marker).'###'] .= $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
				}
			}
		}else{
			if ($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString,$marker)) {
				$sims['###'.strtoupper($marker).'###'] = '';
				
				$files = Array();
				$caption = Array();
				$title = Array();
				$alt = Array();
				switch($marker){
					case 'image': {
						$files = $this->object->getImage();
						$caption = $this->object->getImageCaption();
						$title = $this->object->getImageTitleText();
						$alt = $this->object->getImageAltText();
						break;
					}
					case 'attachment': {
						$files = $this->object->getAttachment();
						$caption = $this->object->getAttachmentCaption();
						break;
					}
					default: {
						$files = t3lib_div::trimExplode(',',$this->object->row[$marker],1);
						break;
					}
				}
				$i = 0;
				for($i;$i < count($files) && $i < $max; $i++){
					$temp = $this->cObj->stdWrap('',$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
					$temp_sims = Array();
					$temp_sims['###'.strtoupper($marker).'_VALUE###'] = $files[$i];
					if($marker=='image'){
						$temp_sims['###'.strtoupper($marker).'_PREVIEW###'] = $this->renderImage($files[$i],$caption[$i], $title[$i], $alt[$i],$marker,false);
					}else if($marker=='attachment' || $marker=='ics_file'){
						$temp_sims['###'.strtoupper($marker).'_PREVIEW###'] = $this->renderFile($files[$i], $caption[$i], $marker,false);
					}

					$temp = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
					$sims['###'.strtoupper($marker).'###'] .= $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
				}
				$upload = '';
				for($i;$i < $max; $i++){
					$upload .= $this->cObj->stdWrap('',$this->conf['view.'][$this->conf['view'].'.'][$marker.'Upload_stdWrap.']);
				}
				$sims['###'.strtoupper($marker).'###'] .= $upload;
			}else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString,$marker)){
				for($i = 0; $i < $max; $i++){
					$value = '';
					$upload = '';
					for($i;$i < $max; $i++){
						$upload .= $this->cObj->stdWrap($value,$this->conf['view.'][$this->conf['view'].'.'][$marker.'Upload_stdWrap.']);
						$value = '';
					}
					$sims['###'.strtoupper($marker).'###'] .= $upload;
				}
			}
		}
	}
	
	function getTranslationOptionsMarker(& $template, & $sims, & $rems){
		if($this->rightsObj->isViewEnabled('translation') && $this->isEditMode){
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_language_uid','pages_language_overlay','pid = '.$this->object->row['pid'].$this->cObj->enableFields('pages_language_overlay'),'','sys_language_uid ASC');
			$langIds = array();
			$sims['###TRANSLATION_OPTIONS###'] = '';
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$langIds[] = $row['sys_language_uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_language_uid',$this->table,'l18n_parent = '.$this->object->getUid().$this->cObj->enableFields($this->table),'','sys_language_uid ASC');
			$inUseLangIds = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
				$inUseLangIds[] = $row['sys_language_uid'];
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($result);

			$langIds = array_diff($langIds,$inUseLangIds);

			if(!empty($langIds)){
				$sims['###TRANSLATION_OPTIONS###'] .= 'Create translation:';
				foreach($langIds as $key => $langId){
					if($langId>0){
						$overrulePIvars = array ('view'=>'translation','overlay'=>$langId,'lastview' => $this->controller->extendLastView(),'servicename'=>$this->serviceName,'subtype'=>$this->objectString,'uid'=>$this->object->getUid(),'type'=>$this->object->getType());
						$piVars = $this->piVars;
						unset($piVars['DATA']);
						$overrulePIvars = t3lib_div::array_merge_recursive_overrule($piVars,$overrulePIvars);
						$sims['###TRANSLATION_OPTIONS###'] .= ' '.$this->controller->pi_linkTP($this->cObj->cObjGetSingle($this->conf['view.']['translation.']['languageMenu.'][$langId],$this->conf['view.']['translation.']['languageMenu.'][$langId.'.']),Array($this->controller->prefixId=>$overrulePIvars,Array('L'=>$langId)));
					}else{
						$sims['###TRANSLATION_OPTIONS###'] .= ' Please check your alternative language setup. This record points to the default language!';
					}
				}
			}
		}else{
			$sims['###TRANSLATION_OPTIONS###'] = '';
		}
	}
	
	function getCurrentTranslationMarker(& $template, & $sims, & $rems){
		if($this->rightsObj->isViewEnabled('translation') && $this->isEditMode){
			if($this->object->row['sys_language_uid']!=0){
				$sims['###CURRENT_TRANSLATION###'] = 'Current translation: '.$this->cObj->cObjGetSingle($this->conf['view.']['translation.']['languageMenu.'][$this->object->row['sys_language_uid']],$this->conf['view.']['translation.']['languageMenu.'][$this->object->row['sys_language_uid'].'.']).'<br/>';
			}else{
				$sims['###CURRENT_TRANSLATION###'] = 'Current translation: default<br/>';
			}
		}else{
			$sims['###CURRENT_TRANSLATION###'] = '';
		}
	}
	
	function checkRequiredFields(&$requiredFieldsSims){
		$allRequiredFieldsAreFilled = true;
		$viewParts = explode('_',$this->conf['view']);
		if(count($viewParts)==2 && is_array($this->conf['rights.'][$viewParts[0].'.'][$viewParts[1].'.']['fields.'])>0){
			foreach($this->conf['rights.'][$viewParts[0].'.'][$viewParts[1].'.']['fields.'] as $field => $fieldSetup){
				$myField = str_replace('.','',$field);
				if($this->isAllowed($myField) && $fieldSetup['required'] && !$this->controller->piVars[$myField]!='') {
					$allRequiredFieldsAreFilled = false;
					$requiredFieldsSims['###'.strtoupper($myField).'_REQUIRED###'] = $this->conf['view.']['required'];
				}else{
					$requiredFieldsSims['###'.strtoupper($myField).'_REQUIRED###'] = '';
				}
			}
		}
		if($allRequiredFieldsAreFilled && !$this->controller->piVars['formCheck']=='1'){
			$allRequiredFieldsAreFilled = false;
		}
		return $allRequiredFieldsAreFilled;
	}
	
	function getDefaultValues(){
		$defaultValues = Array();
		$viewParts = explode('_',$this->conf['view']);
		if(count($viewParts)==2 && is_array($this->conf['rights.'][$viewParts[0].'.'][$viewParts[1].'.']['fields.'])>0){
			foreach($this->conf['rights.'][$viewParts[0].'.'][$viewParts[1].'.']['fields.'] as $field => $fieldSetup){
				$myField = str_replace('.','',$field);
				if(!$this->controller->piVars[$myField]!='' && $fieldSetup['default']!='') {
					$defaultValues[$myField] = $fieldSetup['default'];
				}
			}
		}
		return $defaultValues;
	}
	
	function checkContrains(&$constrainSims){
		$defaultValues = Array();
		$noComplains = true;
		$viewParts = explode('_',$this->conf['view']);
		if(count($viewParts)==2 && is_array($this->conf['rights.'][$viewParts[0].'.'][$viewParts[1].'.']['fields.'])>0){
			foreach($this->conf['rights.'][$viewParts[0].'.'][$viewParts[1].'.']['fields.'] as $field => $fieldSetup){
				$myField = str_replace('.','',$field);
				if($fieldSetup['constrain.']!='') {
					$constrainSims['###'.strtoupper($myField).'_CONSTRAIN###'] = $this->constrainParser($myField, $fieldSetup['constrain.']);
					if($constrainSims['###'.strtoupper($myField).'_CONSTRAIN###']!=''){
						$noComplains = false;
					}
				}
			}
		}
		return $noComplains;
	}
					
	function constrainParser($field, $constrainConfig){
		$result = Array();
		foreach($constrainConfig as $rule){
			$value = $this->ruleParser($field, $rule);
			if($value!=''){
				$result[] = $value;
			}
		}
		return implode('<br/>',$result);
	}
	
	function ruleParser($field, $rule){
		$passedAny = Array();
		$rules = t3lib_div::trimExplode('|',$rule['rule'],1);
		foreach($rules as $rulePart) {
			$failed = false;
			switch ($rulePart) {
				case 'before':
				case 'less':{
					if($rule['field']){
						$functionA = 'get'.ucwords($field);
						$functionB = 'get'.ucwords($rule['field']);
						if(method_exists($this->object, $functionA) && method_exists($this->object, $functionB)){
							$a = $this->object->$functionA();
							$b = $this->object->$functionB();
							if(is_object($a) && method_exists($a, $rule['rule'])){
								$result = $a->compareTo($b);
								if($result != -1){
									$failed = true;
								}
							}else{
								if($a >= $b){
									$failed = true;
								}
							}
						}
					}
					break;
				}
				case 'after':
				case 'greater':{
					if($rule['field']){
						$functionA = 'get'.ucwords($field);
						$functionB = 'get'.ucwords($rule['field']);
						if(method_exists($this->object, $functionA) && method_exists($this->object, $functionB)){
							$a = $this->object->$functionA();
							$b = $this->object->$functionB();
							
							if(is_object($a) && method_exists($a, $rule['rule'])){
							$result = $a->compareTo($b);
								if($result != 1){
									$failed = true;
								}
							}
						}else{
							if($a <= $b){
								$failed = true;
							}
						}
					}
					break;
				}
				case 'equals':{
					if($rule['field']){
						$functionA = 'get'.ucwords($field);
						$functionB = 'get'.ucwords($rule['field']);
						if(method_exists($this->object, $functionA) && method_exists($this->object, $functionB)){
							$a = $this->object->$functionA();
							$b = $this->object->$functionB();
							
							if(is_object($a) && method_exists($a, $rule['rule'])){
								$result = $a->compareTo($b);
								if($result != 0){
									$failed = true;
								}
							}
						}else{
							if($a != $b){
								$failed = true;
							}
						}
					}
					break;
				}
				case 'regexp':{
					$functionA = 'get'.ucwords($field);
					if(method_exists($this->object, $functionA)){
						$value = $this->object->$functionA();
						if(is_string($value) && !preg_match($rule['regexp'], $value)){
							$failed = true;
						}
					}
					break;
				}
				case 'userfunc':{
					$functionA = 'get'.ucwords($field);
					if(method_exists($this->object, $functionA)){
						$value = $this->object->$functionA();
						if(!$this->cObj->callUserFunction($rules['userFunc'],$rule,$value)){
							$failed = true;
						}
					}
					break;
				}
			}
			$passedAny[] = $failed?0:1;
		}
		if(array_sum($passedAny)==0){
			return $this->cObj->cObjGetSingle($rule['message'],$rule['message.']);
		}
		return '';
	}
	
	function formCheck(){
		foreach($this->conf['rights.'][($this->isEditMode?'edit':'create').'.'][$this->objectString.'.']['fields.'] as $name => $field){
			
			if($field['required'] && $this->controller->piVars[str_replace('.','',$name)]==''){
debug('pflichtfeld nicht bef&uuml;llt: '.str_replace('.','',$name));
			}
		}
//		if([strtolower($marker).'.']['required']){
//						$required = $this->conf['view.']['required'];
//					}
	}
	
	function isAllowed($field) {
		if($this->isEditMode) {
			$action = 'edit';
		} else {
			$action = 'create';
		}
		return $this->rightsObj->isAllowedTo($action, $this->objectString, $field);
	}
	
	function applyStdWrap($value, $key) {
		
		return $this->cObj->stdWrap($value, $this->conf['view.'][$this->conf['view'].'.'][$key.'.']);
	}
	
	function renderFile($file, $caption, $marker, $isTemp=false){
		$remMedia = $this->cObj->data['media'];
		$this->cObj->data['media'] = basename($file);
		$remCaption = $this->cObj->data['imagecaption'];
		$this->cObj->data['imagecaption'] = $caption;
		$remLayout = $this->cObj->data['layout'];
		$this->cObj->data['layout'] = $this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.'][$marker.'.']['layout'];
		$remPath = $this->cObj->data['select_key'];
		if($isTemp){
			$this->cObj->data['select_key'] = 'typo3temp/';
		}else{
			global $TCA;
			$this->cObj->data['select_key'] = $TCA['tx_cal_'.$this->objectString]['columns'][$marker]['config']['uploadfolder'].'/';
		}
		$temp = $this->cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.'][$marker],$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.'][$marker.'.']);

		$this->cObj->data['media'] = $remMedia;
		$this->cObj->data['layout'] = $remLayout;
		$this->cObj->data['imagecaption'] = $remCaption;
		$this->cObj->data['select_key'] = $remPath;
		return $temp;
	}
	
	function renderImage($file, $caption, $title, $alt, $marker, $isTemp=false){
		unset($this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['imgList.']);
		$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['imgList'] = basename($file);

		t3lib_div::loadTCA('tx_cal_'.$this->objectString);
		if($isTemp){
			$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['imgPath'] = 'typo3temp/';
		}else{
			global $TCA;
			$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['imgPath'] = $TCA['tx_cal_'.$this->objectString]['columns'][$marker]['config']['uploadfolder'].'/';
		}

		$this->cObj = &tx_cal_registry::Registry('basic','cobj');
		$remLayout = $this->cObj->data['layout'];
		$this->cObj->data['layout'] = $this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['layout'];

		$remCaption = $this->cObj->data['imagecaption'];
		$this->cObj->data['imagecaption'] = $caption;
		$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['1.']['altText'] = $alt;
		$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']['1.']['titleText'] = $title;

		$temp = $this->cObj->cObjGetSingle($this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image'],$this->conf['view.'][$this->conf['view'].'.'][$this->objectString.'.']['image.']);

		$this->cObj->data['layout'] = $remLayout;
		$this->cObj->data['imagecaption'] = $remCaption;
		return $temp;
	}
	
	function getTabbedMenuMarker($template, &$sims, &$rems, $view){
		$tabbedMenuConf = $this->conf['view.'][$view.'.']['tabbedMenu.'];
		foreach((Array)$tabbedMenuConf as $id => $tab){
			if(endsWith($id,'.') && $tab['requiredFields']!=''){
				$requiredFields = t3lib_div::trimExplode(',',$tab['requiredFields'],1);
				$isAllowed = false;
				foreach($requiredFields as $field){
					if($this->isAllowed($field)){
						$isAllowed = true;
						break;
					}
				}
				if(!$isAllowed){
					unset($tabbedMenuConf[$id]);
					unset($tabbedMenuConf[$id.'.']);
				}
			}
		}

		$sims['###TABBED_MENU###'] = $this->cObj->cObjGetSingle($this->conf['view.'][$view.'.']['tabbedMenu'], $tabbedMenuConf);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_fe_editing_base_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_fe_editing_base_view.php']);
}
?>