<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2004 
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
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
	
	function tx_cal_fe_editing_base_view(){
		$this->tx_cal_base_view();
	}
	
	function getTemplateSingleMarker(& $template, & $rems, & $sims) {
		preg_match_all('!\###([A-Z0-9_-|]*)\###!is', $template, $match);
		$allSingleMarkers = array_unique($match[1]);
		foreach ($allSingleMarkers as $marker) {
            switch ($marker) {
                default :
					if(preg_match('/.*_LABEL/',$marker)){
						$sims['###'.$marker.'###'] = $this->controller->pi_getLL('l_'.$this->objectString.'_'.strtolower(substr($marker,0,strlen($marker)-6)));
						continue;	
					}
					$funcFromMarker = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',strtolower($marker)))).'Marker';
					if (preg_match('/MODULE__([A-Z0-9_-])*/', $marker)) {
						$module = t3lib_div :: makeInstanceService(substr($marker, 8), 'module');
						if (is_object($module)) {
							$sims['###' . $marker . '###'] = $module->start($this);
						}
					}else if(method_exists($this,$funcFromMarker)) {
				        $this->$funcFromMarker($template, $rems, $sims);
					}else{
						if($this->isConfirm && (($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString,strtolower($marker))) || (!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString,strtolower($marker))))){
							$sims['###' . $marker . '_VALUE###'] = strip_tags($this->controller->piVars[strtolower($marker)]);
							$sims['###' . $marker . '###'] = $this->cObj->stdWrap(strip_tags($this->controller->piVars[strtolower($marker)]),$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
						}else if ($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString,$marker)) {
							/**
							 * @fixme	Quick fix for the RTE related Javascript.  
							 * If the marker hasn't been defined already and the value for the marker isn't blank, set it.  Otherwise, let the previous value stick.
							 */
							if(!($sims['###' . $marker . '###'] && !$this->object->row[$marker])){
								$sims['###' . $marker . '_VALUE###'] = $this->object->row[$marker];
								$sims['###' . $marker . '###'] = $this->cObj->stdWrap($this->object->row[strtolower($marker)],$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
							}
						}else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString,$marker)){
							$value = '';
							if(!empty($this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['allowedToCreate'.ucwords(strtolower($marker)).'.']['default'])) {
								$value = $this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['allowedToCreate'.ucwords(strtolower($marker)).'.']['default'];
							}
							/**
							 * @fixme	Quick fix for the RTE related Javascript.  
							 * If the marker hasn't been defined already and the value for the marker isn't blank, set it.  Otherwise, let the previous value stick.
							 */
							if(!($sims['###' . $marker . '###'] && $value == '')){
								$sims['###' . $marker . '###'] = $this->cObj->stdWrap($value,$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);							
							}
						}else {
							$sims['###' . $marker . '###'] = '';
							$sims['###' . $marker . '_VALUE###'] = '';
						}
					}
					 
					break;
			}
		}				
	}
	
	function getTemplateSubpartMarker(& $template, & $rems, & $sims) {
		
		preg_match_all('!\<\!--[a-zA-Z0-9 ]*###([A-Z0-9_-|]*)\###[a-zA-Z0-9 ]*-->!is', $template, $match);

		$allMarkers = array_unique($match[1]);

		foreach ($allMarkers as $marker) {
          
			switch ($marker) {

				case 'FORM_START' :
					$this->getFormStartMarker($template, $rems, $sims);
					break;
				case 'FORM_END' :
					$this->getFormEndMarker($template, $rems, $sims);
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
	
	function getFormStartMarker(& $template, & $rems, & $sims){
		$rems['###FORM_START###'] = $this->cObj->getSubpart($template, '###FORM_START###');
		
	}
	
	function getFormEndMarker(& $template, & $rems, & $sims){
		$temp = $this->cObj->getSubpart($template, '###FORM_END###');
		$temp_sims = array();
		$temp_sims['###BACK_LINK###'] = $this->controller->pi_linkTP_keepPIvars_url( $this->controller->shortenLastViewAndGetTargetViewParameters());
		$temp_sims['###L_CANCEL###'] = $this->controller->pi_getLL('l_cancel');
		$temp_sims['###L_SUBMIT###'] = $this->controller->pi_getLL('l_submit');
		$rems['###FORM_END###'] = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
	}
	
	function getHiddenMarker(& $template, & $rems, & $sims){
		$sims['###HIDDEN###'] = '';
		
		if($this->isConfirm){
			$sims['###HIDDEN_VALUE###'] = '';
			if(($this->editMode && $this->rightsObj->isAllowedTo('edit',$this->objectString, 'hidden')) || (!$this->editMode && $this->rightsObj->isAllowedTo('create',$this->objectString, 'hidden'))){
				if ($this->controller->piVars['hidden'] == 'on') {
					$value = 1;
					$label = $this->controller->pi_getLL('l_true');
				} else {
					$value = 0;
					$label = $this->controller->pi_getLL('l_false');
				}
				$sims['###HIDDEN###'] = $this->cObj->stdWrap($label, $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
				$sims['###HIDDEN_VALUE###'] = $value;
			}
		}else{
			$sims['###HIDDEN###'] = '';
			if($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString, 'hidden')){
				$hidden = '';
				if($this->conf['rights.']['edit.']['.']['fields.']['allowedToEditHidden.']['default']){
					$hidden = ' checked="checked" ';
				}
				$sims['###HIDDEN###'] = $this->cObj->stdWrap($this->object->isHidden(), $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
			} else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString, 'hidden')){
				$hidden = '';
				if($this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['allowedToCreateHidden.']['default']){
					$hidden = ' checked="checked" ';
				}
				$sims['###HIDDEN###'] = $this->cObj->stdWrap($hidden, $this->conf['view.'][$this->conf['view'].'.']['hidden_stdWrap.']);
			}
		}
	}
	
	function getImageMarker(& $template, & $rems, & $sims){
		global $TYPO3_CONF_VARS,$TCA;
		require_once (PATH_t3lib . 'class.t3lib_basicfilefunc.php');
		t3lib_div::loadTCA('tx_cal_'.$this->objectString);
		$maxImages = $TCA['tx_cal_'.$this->objectString]['columns']['image']['config']['size'];
		$marker = 'image';
		$sims['###IMAGE###'] = '';
		$sims['###IMAGE_VALUE###'] = '';
		if($this->isConfirm){
			$sims['###IMAGE###'] = '';
			if (is_array($_FILES[$this->prefixId]['name']['image'])) {
				$images = Array();
				if($this->editMode && $this->controller->piVars['image']){
					$images = $this->controller->piVars['image'];
				}

				$fileFunc = t3lib_div::makeInstance('t3lib_basicFileFunctions');
				$all_files = Array();
				$all_files['webspace']['allow'] = '*';
				$all_files['webspace']['deny'] = '';
				$fileFunc->init('', $all_files);
				$allowedExt = explode(',',$TYPO3_CONF_VARS['GFX']['imagefile_ext']);
				$removeImages = $this->controller->piVars['removeImage']?$this->controller->piVars['removeImage']:Array();
				foreach($_FILES[$this->prefixId]['name']['image'] as $id => $filename){
					$iConf = $this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.'];
					if($_FILES[$this->prefixId]['error']['image'][$id]){
						continue;
					}else{
						$theFile = t3lib_div::upload_to_tempfile($_FILES[$this->prefixId]['tmp_name']['image'][$id]);
						$fI = t3lib_div::split_fileref($filename);
						if(!in_array($fI['fileext'],$allowedExt)){
							continue;
						}
						$theDestFile = $fileFunc->getUniqueName($fileFunc->cleanFileName($fI['file']), 'typo3temp');
						t3lib_div::upload_copy_move($theFile,$theDestFile);
						$iConf['file'] = $theDestFile;
						$return = '__NEW__'.basename($theDestFile);
					}

					$temp_sims = Array();
					$temp_sims['###IMAGE_VALUE###'] = $return;
					$temp = $this->cObj->stdWrap($this->cObj->IMAGE($iConf),$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
					$sims['###IMAGE###'] .= $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
				}

				foreach($images as $image){
					$return = $image;
					$iConf['file'] = 'uploads/tx_cal/pics/'.$image;
					if($this->editMode && in_array($image,$removeImages)){
						$return = '__DELETE__'.$image;
						$iConf['file'] = '';
					}
					
					$temp_sims = Array();
					$temp_sims['###IMAGE_VALUE###'] = $return;
					$temp = $this->cObj->stdWrap($this->cObj->IMAGE($iConf),$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
					$sims['###IMAGE###'] .= $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
				}
			}
		}else{
			
			if ($this->isEditMode && $this->rightsObj->isAllowedTo('edit',$this->objectString,$marker)) {
				$sims['###IMAGE###'] = '';
				$images = t3lib_div::trimExplode(',',$this->object->row[$marker],1);
				$i = 0;
				for($i;$i < count($images) && $i < $maxImages; $i++){
					$iConf = $this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.'];
					$iConf['file'] = 'uploads/tx_cal/pics/'.$images[$i];
					$temp = $this->cObj->stdWrap('',$this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.']);
					$temp_sims = Array();
					$temp_sims['###IMAGE_VALUE###'] = $images[$i];
					$temp_sims['###IMAGE_PREVIEW###'] = $this->cObj->IMAGE($iConf);
					$temp = $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
					$sims['###IMAGE###'] .= $this->cObj->substituteMarkerArrayCached($temp, $temp_sims, array(), array ());
				}
				$upload = '';
				for($i;$i < $maxImages; $i++){
					$upload .= $this->cObj->stdWrap('',$this->conf['view.'][$this->conf['view'].'.']['imageUpload_stdWrap.']);
				}
				$sims['###IMAGE###'] .= $upload;
			}else if(!$this->isEditMode && $this->rightsObj->isAllowedTo('create',$this->objectString,$marker)){
				for($i = 0; $i < $maxImages; $i++){
					$value = '';
					if($i==0 && !empty($this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['allowedToCreateImage.']['default'])) {
						$value = $this->conf['rights.']['create.'][$this->objectString.'.']['fields.']['allowedToCreateImage.']['default'];
						//$iConf = $this->conf['view.'][$this->conf['view'].'.'][strtolower($marker).'_stdWrap.'];
						//$iConf['file'] = 'uploads/tx_cal/temp/'.$value;
						//$value = $this->cObj->IMAGE($iConf);
					}
					$upload = '';
					for($i;$i < $maxImages; $i++){
						$upload .= $this->cObj->stdWrap($value,$this->conf['view.'][$this->conf['view'].'.']['imageUpload_stdWrap.']);
						$value = '';
					}
					$sims['###IMAGE###'] .= $upload;
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_fe_editing_base_view.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/view/class.tx_cal_fe_editing_base_view.php']);
}
?>