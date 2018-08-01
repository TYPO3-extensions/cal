<?php
namespace TYPO3\CMS\Cal\TreeProvider;
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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\MathUtility;


/**
 * This function displays a selector with nested categories.
 * The original code is borrowed from the extension "Digital Asset Management" (tx_dam) author: Rene© Fritz <r.fritz@colorcube.de>
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 * @package TYPO3
 * @subpackage cal
 */

/**
 * this class displays a tree selector with nested tt_news categories.
 */
class TreeView {
	function getTreeList($uid, $recursive, &$ids) {
		if (intval ($uid) > 0 && $recursive > - 1) {
			
			array_push ($ids, $uid);
			$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('uid', 'pages', 'pid = ' . intval ($uid) . ' AND deleted = 0');
			if ($res) {
				$recursive --;
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_row ($res)) {
					$this->getTreeList ($row [0], $recursive, $ids);
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($res);
			}
		}
	}
	
	/**
	 * Generation of TCEform elements of the type "select"
	 * This will render a selector box element, or possibly a special construction with two selector boxes.
	 * That depends on configuration.
	 *
	 * @param array $PA:
	 *        	parameter array for the current field
	 * @param object $fobj:
	 *        	to the parent object
	 * @return string HTML code for the field
	 */
	function displayCalendar($PA, &$fobj) {
		$table = $PA ['table'];
		$field = $PA ['field'];
		$config = $PA ['fieldConf'] ['config'];
		$cfgArr = GeneralUtility::xml2array ($PA ['row'] ['pi_flexform']);
		$selectedCalendars = array ();
		
		if (is_array ($cfgArr) && is_array ($cfgArr ['data'] ['s_Cat'] ['lDEF']) && is_array ($cfgArr ['data'] ['s_Cat'] ['lDEF'] ['calendarSelection'])) {
			$selectedCalendars = GeneralUtility::trimExplode (',', $cfgArr ['data'] ['s_Cat'] ['lDEF'] ['calendarSelection'] ['vDEF'], 1);
		}
		
		// it seems TCE has a bug and do not work correctly with '1'
		$config ['maxitems'] = ($config ['maxitems'] == 2) ? 1 : $config ['maxitems'];
		$row = $PA ['row'];
		$pidList = array ();
		
		$isPluginFlexform = false;
		$isBeUserForm = false;
		$isCategoryForm = false;
		if ($field == 'pi_flexform') {
			$isPluginFlexform = true;
		}
		
		if ($isPluginFlexform) {
			$pagesEntry = $row ['pages'];
			$recursiveEntry = $row ['recursive'];
			
			$pagesEntryArray = array_unique (explode (',', $pagesEntry));
			
			foreach ($pagesEntryArray as $id) {
				preg_match ('/[a-zA-Z]*_([0-9]*)|[a-zA-Z0-9]*/', $id, $idArray);
				$list = array ();
				$this->getTreeList ($idArray [1], $recursiveEntry, $list);
				if ($list)
					$pidList [] = implode (',', $list);
			}
			
			$pidList = implode (',', $pidList);
			if ($pidList == '')
				$pidList = '0';
		}
		
		if ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['tx_cal']) { // get cal extConf array
			$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['tx_cal']);
		}
		if ($confArr ['useStoragePid']) {
			$TSconfig = BackendUtility::getTCEFORM_TSconfig ($table, $row);
			$storagePid = $TSconfig ['_STORAGE_PID'] ? $TSconfig ['_STORAGE_PID'] : 0;
			$SPaddWhere = ' AND tx_cal_calendar.pid IN (' . $storagePid . ')';
		}
		if ($GLOBALS ['BE_USER']->getTSConfigVal ('options.useListOfAllowedItems') && ! $GLOBALS ['BE_USER']->isAdmin ()) {
			$notAllowedItems = $this->getNotAllowedItems ($PA, $SPaddWhere);
		}
		
		// get default items
		$defItems = array ();
		if (is_array ($config ['items']) && $table == 'tt_content' && $row ['CType'] == 'list' && $row ['list_type'] == 'cal_controller' && $field == 'pi_flexform') {
			foreach ($config ['items'] as $itemName => $itemValue) {
				if ($itemValue [0]) {
					$ITitle = $this->pObj->sL ($itemValue [0]);
					$defItems [] = '<a href="#" onclick="setFormValueFromBrowseWin(\'data[' . $table . '][' . $row ['uid'] . '][' . $field . '][data][sDEF][lDEF][categorySelection][vDEF]\',' . $itemValue [1] . ',\'' . $ITitle . '\'); return false;" style="text-decoration:none;">' . $ITitle . '</a>';
				}
			}
		}
		
		$allowAllCalendars = true;
		
		$be_userCategories = array (
				0 
		);
		$be_userCalendars = array (
				0 
		);
		$calWhere = '';
		
		if (! $GLOBALS ['BE_USER']->user ['admin'] && $field == 'pi_flexform') {
			if (is_array ($GLOBALS ['BE_USER']->userGroups)) {
				if (! $GLOBALS ['BE_USER']->user ['admin']) {
					foreach ($GLOBALS ['BE_USER']->userGroups as $gid => $group) {
						if ($group ['tx_cal_enable_accesscontroll']) {
							$allowAllCalendars = false;
							if ($group ['tx_cal_calendar']) {
								$be_userCalendars [] = $group ['tx_cal_calendar'];
							}
						}
					}
				}
			}
			
			if ($GLOBALS ['BE_USER']->user ['tx_cal_enable_accesscontroll']) {
				$allowAllCalendars = false;
				if ($GLOBALS ['BE_USER']->user ['tx_cal_calendar']) {
					$be_userCalendars = array_merge ($be_userCalendars, GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_calendar'], 1));
				}
			}
			
			if (! $allowAllCalendars) {
				$calWhere == '' ? $calWhere .= ' ' : $calWhere .= ' AND';
				$calWhere .= ' uid IN (' . implode (',', $be_userCalendars) . ')';
			}
		}
		
		if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
			$enableFields = BackendUtility::BEenableFields ('tx_cal_calendar') . ' AND tx_cal_calendar.deleted = 0';
		} else {
			$enableFields = $this->cObj->enableFields ('tx_cal_calendar');
		}
		
		$calWhere .= $calWhere == '' ? '0=0' . $enableFields : $enableFields;
		if ($isPluginFlexform) {
			$calWhere .= ' AND pid IN (' . $pidList . ')';
		}
		$calWhere .= ' AND tx_cal_calendar.sys_language_uid IN (-1,0)';
		$calres = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_calendar.uid, tx_cal_calendar.title', 'tx_cal_calendar', $calWhere);
		$itemArray = array ();
		$calendarUids = array ();
		$selItems = array ();
		if ($calres) {
			while ($calrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($calres)) {
				if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
					$tempRow = BackendUtility::getRecordLocalization ('tx_cal_calendar', $calrow ['uid'], $PA ['row'] ['sys_language_uid'], '');
					if (is_array ($tempRow)) {
						$calrow = $tempRow [0];
					}
				}
				$itemArray [] = array (
						$calrow ['title'],
						$calrow ['uid'],
						'' 
				);
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($calres);
		}
		
		$test = new \TYPO3\CMS\Backend\Form\FormEngine ();
		$test->initDefaultBEmode ();
		
		$disabled = '';
		if ($this->renderReadonly || $config ['readOnly']) {
			$disabled = ' disabled="disabled"';
		}
		
		// Setting this hidden field (as a flag that JavaScript can read out)
		if (! $disabled) {
			$item .= '<input type="hidden" name="' . $PA ['itemFormElName'] . '_mul" value="' . ($config ['multiple'] ? 1 : 0) . '" />';
		}
		
		// Set max and min items:
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
			$maxitems = MathUtility::forceIntegerInRange ($config ['maxitems'], 0);
			if (! $maxitems)
				$maxitems = 100000;
			$minitems = MathUtility::forceIntegerInRange ($config ['minitems'], 0);
		} else {
			$maxitems = GeneralUtility::forceIntegerInRange ($config ['maxitems'], 0);
			if (! $maxitems)
				$maxitems = 100000;
			$minitems = GeneralUtility::forceIntegerInRange ($config ['minitems'], 0);
		}
		// Register the required number of elements:
		$test->requiredElements [$PA ['itemFormElName']] = array (
				$minitems,
				$maxitems,
				'imgName' => $table . '_' . $row ['uid'] . '_' . $field 
		);
		
		// Get "removeItems":
		$removeItems = GeneralUtility::trimExplode (',', $PA ['fieldTSConfig'] ['removeItems'], 1);
		if (! $disabled) {
			// Create option tags:
			$opt = array ();
			$styleAttrValue = '';
			foreach ($itemArray as $p) {
				if ($config ['iconsInOptionTags']) {
					$styleAttrValue = $this->optionTagStyle ($p [2]);
				}
				$opt [] = '<option value="' . htmlspecialchars ($p [1]) . '"' . ($styleAttrValue ? ' style="' . htmlspecialchars ($styleAttrValue) . '"' : '') . '>' . htmlspecialchars ($p [0]) . '</option>';
			}
			
			// Put together the selector box:
			$selector_itemListStyle = isset ($config ['itemListStyle']) ? ' style="' . htmlspecialchars ($config ['itemListStyle']) . '"' : ' style="' . $this->defaultMultipleSelectorStyle . '"';
			$size = intval ($config ['size']);
			if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
				$size = $config ['autoSizeMax'] ? MathUtility::forceIntegerInRange (count ($itemArray) + 1, MathUtility::forceIntegerInRange ($size, 1), $config ['autoSizeMax']) : $size;
			} else {
				$size = $config ['autoSizeMax'] ? GeneralUtility::forceIntegerInRange (count ($itemArray) + 1, GeneralUtility::forceIntegerInRange ($size, 1), $config ['autoSizeMax']) : $size;
			}
			if ($config ['exclusiveKeys']) {
				$sOnChange = 'setFormValueFromBrowseWin(\'' . $PA ['itemFormElName'] . '\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text,\'' . $config ['exclusiveKeys'] . '\'); ';
			} else {
				$sOnChange = 'setFormValueFromBrowseWin(\'' . $PA ['itemFormElName'] . '\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); ';
			}
			$sOnChange .= implode ('', $PA ['fieldChangeFunc']);
			
			$width = 280;
			// hardcoded: 16 is the height of the icons
			$height = $size * 16;
				
			$divStyle = 'position:relative; left:0px; top:0px; height:' . $height . 'px; width:' . $width . 'px;border:solid 1px;overflow:auto;background:#fff;margin-bottom:5px;';
			$itemsToSelect = '<div  name="' . $PA ['itemFormElName'] . '_selTree" style="' . htmlspecialchars ($divStyle) . '">';
			$itemsToSelect .= '<select name="' . $PA ['itemFormElName'] . '_sel"' . $test->insertDefStyle ('select') . ($size ? ' size="' . $size . '"' : '') . ' style="width:' . $width .'px" onchange="' . htmlspecialchars ($sOnChange) . '"' . $PA ['onFocus'] . $selector_itemListStyle . '>
					' . implode ('
					', $opt) . '
				</select>';
			$itemsToSelect .= '</div>';
		}
		
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 7000000) {
			$params = array (
					'size' => $config ['size'],
					'autoSizeMax' => MathUtility::forceIntegerInRange ($config ['autoSizeMax'], 0),
					'style' => isset ($config ['selectedListStyle']) ? ' style="' . htmlspecialchars ($config ['selectedListStyle']) . '"' : ' style="' . $this->defaultMultipleSelectorStyle . '"',
					'dontShowMoveIcons' => ($config ['maxitems'] <= 1),
					'maxitems' => $config ['maxitems'],
					'info' => '',
					'headers' => array (
							'selector' => $test->getLL ('l_selected') . ':<br />',
							'items' => $test->getLL ('l_items') . ':<br />' 
					),
					'noBrowser' => 1,
					'rightbox' => $itemsToSelect,
					'readOnly' => $disabled 
			);
		} else if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
			$params = array (
					'size' => $config ['size'],
					'autoSizeMax' => MathUtility::forceIntegerInRange ($config ['autoSizeMax'], 0),
					'style' => isset ($config ['selectedListStyle']) ? ' style="' . htmlspecialchars ($config ['selectedListStyle']) . '"' : ' style="' . $this->defaultMultipleSelectorStyle . '"',
					'dontShowMoveIcons' => ($config ['maxitems'] <= 1),
					'maxitems' => $config ['maxitems'],
					'info' => '',
					'headers' => array (
							'selector' => $test->getLL ('l_selected') . ':<br />',
							'items' => $test->getLL ('l_items') . ':<br />' 
					),
					'noBrowser' => 1,
					'thumbnails' => $itemsToSelect,
					'readOnly' => $disabled 
			);
		} else {
			$params = array (
					'size' => $config ['size'],
					'autoSizeMax' => GeneralUtility::forceIntegerInRange ($config ['autoSizeMax'], 0),
					'style' => isset ($config ['selectedListStyle']) ? ' style="' . htmlspecialchars ($config ['selectedListStyle']) . '"' : ' style="' . $this->defaultMultipleSelectorStyle . '"',
					'dontShowMoveIcons' => ($config ['maxitems'] <= 1),
					'maxitems' => $config ['maxitems'],
					'info' => '',
					'headers' => array (
							'selector' => $test->getLL ('l_selected') . ':<br />',
							'items' => $test->getLL ('l_items') . ':<br />' 
					),
					'noBrowser' => 1,
					'thumbnails' => $itemsToSelect,
					'readOnly' => $disabled 
			);
		}
		if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 7000000) {
			$treeHelperElement = new TreeHelperElement($test);
			$item .= $treeHelperElement->getDbFileIcon($PA ['itemFormElName'], '', '', $selectedCalendars, '', $params, $PA ['onFocus']);
		} else {
			$item .= $test->dbFileIcons ($PA ['itemFormElName'], '', '', $selectedCalendars, '', $params, $PA ['onFocus']);
		}
		
		return $item;
	}
	
	/**
	 * Generation of TCEform elements of the type "select"
	 * This will render a selector box element, or possibly a special construction with two selector boxes.
	 * That depends on configuration.
	 *
	 * @param array $PA:
	 *        	parameter array for the current field
	 * @param object $fobj:
	 *        	to the parent object
	 * @return string HTML code for the field
	 */
	function displayCategoryTree($PA, $fobj) {
		$table = $PA ['table'];
		$field = $PA ['field'];
		$row = $PA ['row'];
		$config = $PA ['fieldConf'] ['config'];
		
		$isPluginFlexform = false;
		$isBeUserForm = false;
		$isBeGroupsForm = false;
		$isCategoryForm = false;
		$selectedCalendars = array ();
		$calendarMode = 0;
		if ($field == 'pi_flexform') {
			$isPluginFlexform = true;
			
			if ($PA ['row'] ['pi_flexform']) {
				$cfgArr = GeneralUtility::xml2array ($PA ['row'] ['pi_flexform']);
			} else {
				$cfgArr = array ();
			}
			
			if (is_array ($cfgArr) && is_array ($cfgArr ['data'] ['s_Cat'] ['lDEF']) && is_array ($cfgArr ['data'] ['s_Cat'] ['lDEF'] ['calendarSelection'])) {
				$selectedCalendars = GeneralUtility::intExplode (',', $cfgArr ['data'] ['s_Cat'] ['lDEF'] ['calendarSelection'] ['vDEF']);
			}
			$calendarMode = $cfgArr ['data'] ['s_Cat'] ['lDEF'] ['calendarMode'] ['vDEF'];
		}
		$selectedCalendars [] = 0;
		$selectedCalendars = array_unique ($selectedCalendars);
		if ($table == 'tx_cal_category') {
			$isCategoryForm = true;
		}
		if ($table == 'be_users') {
			$isBeUserForm = true;
		}
		if ($table == 'be_groups') {
			$isBeGroupsForm = true;
		}
		
		if ($row ['calendar_id'] == 0 && ! $isCategoryForm && ! $isPluginFlexform && ! $isBeUserForm && ! $isBeGroupsForm) {
			
			/* Get the records, with access restrictions and all that good stuff applied. */
			$tempCalRes = \TYPO3\CMS\Cal\Backend\TCA\ItemsProcFunc::getSQLResource ('tx_cal_calendar', '', '', '', '1');
			if ($tempCalRes) {
				while ($calendarRow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($tempCalRes)) {
					$row ['calendar_id'] = $calendarRow ['uid'];
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($tempCalRes);
			}
		}
		
		$pidList = array ();
		
		if ($isPluginFlexform) {
			$pagesEntry = $row ['pages'];
			$recursiveEntry = $row ['recursive'];
			
			$pagesEntryArray = array_unique (explode (',', $pagesEntry));
			
			foreach ($pagesEntryArray as $id) {
				preg_match ('/[a-zA-Z]*_([0-9]*)|[a-zA-Z0-9]*/', $id, $idArray);
				$list = array ();
				$this->getTreeList ($idArray [1], $recursiveEntry, $list);
				if ($list)
					$pidList [] = implode (',', $list);
			}
			
			$pidList = implode (',', $pidList);
			if ($pidList == '')
				$pidList = '0';
		}
		
		$this->pObj = &$PA ['pObj'];
		
		// Field configuration from TCA:
		$config = $PA ['fieldConf'] ['config'];
		// it seems TCE has a bug and do not work correctly with '1'
		$config ['maxitems'] = ($config ['maxitems'] == 2) ? 1 : $config ['maxitems'];
		
		// Getting the selector box items from the system
		$selItems = $this->pObj->addSelectOptionsToItemArray ($this->pObj->initItemArray ($PA ['fieldConf']), $PA ['fieldConf'], $this->pObj->setTSconfig ($table, $row), $field);
		$selItems = $this->pObj->addItems ($selItems, $PA ['fieldTSConfig'] ['addItems.']);
		// f ($config['itemsProcFunc']) $selItems = $this->pObj->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);
		
		// Possibly remove some items:
		$removeItems = GeneralUtility::trimExplode (',', $PA ['fieldTSConfig'] ['removeItems'], 1);
		
		foreach ($selItems as $tk => $p) {
			if (in_array ($p [1], $removeItems)) {
				unset ($selItems [$tk]);
			} else if (isset ($PA ['fieldTSConfig'] ['altLabels.'] [$p [1]])) {
				$selItems [$tk] [0] = $this->pObj->sL ($PA ['fieldTSConfig'] ['altLabels.'] [$p [1]]);
			}
			
			// Removing doktypes with no access:
			if ($table . '.' . $field == 'pages.doktype') {
				if (! ($GLOBALS ['BE_USER']->isAdmin () || GeneralUtility::inList ($GLOBALS ['BE_USER']->groupData ['pagetypes_select'], $p [1]))) {
					unset ($selItems [$tk]);
				}
			}
		}
		
		// Creating the label for the "No Matching Value" entry.
		$nMV_label = isset ($PA ['fieldTSConfig'] ['noMatchingValue_label']) ? $this->pObj->sL ($PA ['fieldTSConfig'] ['noMatchingValue_label']) : '[ ' . $this->pObj->getLL ('l_noMatchingValue') . ' ]';
		$nMV_label = @sprintf ($nMV_label, $PA ['itemFormElValue']);
		
		// Prepare some values:
		$maxitems = intval ($config ['maxitems']);
		$minitems = intval ($config ['minitems']);
		$size = intval ($config ['size']);
		// If a SINGLE selector box...
		if ($maxitems <= 1 and ! $config ['treeView']) {
		} else {
			if ($row ['sys_language_uid'] && $row ['l18n_parent']) { // the current record is a translation of another record
				if ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['tx_cal']) { // get tt_news extConf array
					$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['tx_cal']);
				}
				if ($confArr ['useStoragePid']) {
					$TSconfig = BackendUtility::getTCEFORM_TSconfig ($table, $row);
					$storagePid = $TSconfig ['_STORAGE_PID'] ? $TSconfig ['_STORAGE_PID'] : 0;
					$SPaddWhere = ' AND tx_cal_category.pid IN (' . $storagePid . ')';
				}
				$errorMsg = array ();
				$notAllowedItems = array ();
				if ($GLOBALS ['BE_USER']->getTSConfigVal ('options.useListOfAllowedItems') && ! $GLOBALS ['BE_USER']->isAdmin ()) {
					$notAllowedItems = $this->getNotAllowedItems ($PA, $SPaddWhere);
				}
				// get categories of the translation original
				// $catres = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query ('tx_cal_category.uid,tx_cal_category.title,tt_news_cat_mm.sorting AS mmsorting', 'tt_news', 'tt_news_cat_mm', 'tt_news_cat', ' AND tt_news_cat_mm.uid_local='.$row['l18n_parent'].$SPaddWhere,'', 'mmsorting');
				$catres = false;
				if ($table == 'tx_cal_event') {
					$catres = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_category.uid, tx_cal_category.title', 'tx_cal_category,tx_cal_event_category_mm', 'tx_cal_category.uid = tx_cal_event_category_mm.uid_foreign and tx_cal_event_category_mm.uid_local=' . $row ['l18n_parent'] . $SPaddWhere);
				} else {
					$catres = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_category.uid, tx_cal_category.title', 'tx_cal_category', 'tx_cal_category.uid=' . $row ['l18n_parent'] . $SPaddWhere);
				}
				$categories = array ();
				$NACats = array ();
				$na = false;
				if ($catres) {
					while ($catrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($catres)) {
						if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
							$tempRow = BackendUtility::getRecordLocalization ('tx_cal_category', $catrow ['uid'], $PA ['row'] ['sys_language_uid'], '');
							if (is_array ($tempRow)) {
								$catrow = $tempRow [0];
							}
						}
						if (in_array ($catrow ['uid'], $notAllowedItems)) {
							$categories [$catrow ['uid']] = $NACats [] = '<p style="padding:0px;color:red;font-weight:bold;">- ' . $catrow ['title'] . ' <span class="typo3-dimmed"><em>[' . $catrow ['uid'] . ']</em></span></p>';
							$na = true;
						} else {
							$categories [$catrow ['uid']] = '<p style="padding:0px;">- ' . $catrow ['title'] . ' <span class="typo3-dimmed"><em>[' . $catrow ['uid'] . ']</em></span></p>';
						}
					}
					$GLOBALS ['TYPO3_DB']->sql_free_result ($catres);
				}
				
				if ($na) {
					$this->NA_Items = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">SAVING DISABLED!! <br />' . ($row ['l18n_parent'] && $row ['sys_language_uid'] ? 'The translation original of this' : 'This') . ' record has the following categories assigned that are not defined in your BE usergroup: ' . implode ($NACats, chr (10)) . '</td></tr></tbody></table>';
				}
				$item = implode ($categories, chr (10));
				
				if ($item) {
					$item = 'Categories from the translation original of this record:<br />' . $item;
				} else {
					$item = 'The translation original of this record has no categories assigned.<br />';
				}
				$item = '<div class="typo3-TCEforms-originalLanguageValue">' . $item . '</div>';
			} else { // build tree selector
				$item .= '<input type="hidden" name="' . $PA ['itemFormElName'] . '_mul" value="' . ($config ['multiple'] ? 1 : 0) . '" />';
				
				// Set max and min items:
				if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
					$maxitems = MathUtility::forceIntegerInRange ($config ['maxitems'], 0);
					if (! $maxitems)
						$maxitems = 100000;
					$minitems = MathUtility::forceIntegerInRange ($config ['minitems'], 0);
				} else {
					$maxitems = GeneralUtility::forceIntegerInRange ($config ['maxitems'], 0);
					if (! $maxitems)
						$maxitems = 100000;
					$minitems = GeneralUtility::forceIntegerInRange ($config ['minitems'], 0);
				}
				// Register the required number of elements:
				$this->pObj->requiredElements [$PA ['itemFormElName']] = array (
						$minitems,
						$maxitems,
						'imgName' => $table . '_' . $row ['uid'] . '_' . $field 
				);
				
				// *************************************************
				// ********************** START ********************
				// *************************************************
				if ($config ['treeView'] and $config ['foreign_table']) {
					
					$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
					$treeOrderBy = $confArr ['treeOrderBy'] ? $confArr ['treeOrderBy'] : 'uid';
					
					if ($GLOBALS ['BE_USER']->getTSConfigVal ('options.useListOfAllowedItems') && ! $GLOBALS ['BE_USER']->isAdmin ()) {
						
						$notAllowedItems = $this->getNotAllowedItems ($PA, $SPaddWhere);
					}
					
					if ($table == 'be_users' || $table == 'be_groups') {
						
						$allowAllCategories = true;
						$allowAllCalendars = true;
						$be_userCategories = array ();
						$be_userCalendars = array ();
						
						if ($row ['tx_cal_enable_accesscontroll'] && $row ['tx_cal_calendar']) {
							$allowedCalendarIds = GeneralUtility::intExplode (',', $row ['tx_cal_calendar']);
							$allowedCalendarIds [] = 0;
							$catWhere = ' AND tx_cal_category.calendar_id in (' . implode (',', $allowedCalendarIds) . ')';
							$calWhere = ' AND tx_cal_calendar.uid in (' . implode (',', $allowedCalendarIds) . ')';
						}
					} else {
						
						// get default items
						$defItems = array ();
						if (is_array ($config ['items']) && $table == 'tt_content' && $row ['CType'] == 'list' && $row ['list_type'] == 'cal_controller' && $field == 'pi_flexform') {
							foreach ($config ['items'] as $itemName => $itemValue) {
								if ($itemValue [0]) {
									$ITitle = $this->pObj->sL ($itemValue [0]);
									$defItems [] = '<a href="#" onclick="setFormValueFromBrowseWin(\'data[' . $table . '][' . $row ['uid'] . '][' . $field . '][data][sDEF][lDEF][categorySelection][vDEF]\',' . $itemValue [1] . ',\'' . $ITitle . '\'); return false;" style="text-decoration:none;">' . $ITitle . '</a>';
								}
							}
						}
						
						$allowAllCategories = true;
						$allowAllCalendars = true;
						$be_userCategories = array ();
						$be_userCalendars = array ();
						
						if ($row ['calendar_id'] > 0) {
							if ($isCategoryForm) {
								$catWhere = ' AND tx_cal_category.calendar_id IN (' . $row ['calendar_id'] . ')';
							} else {
								$catWhere = ' AND tx_cal_category.calendar_id IN (0,' . $row ['calendar_id'] . ')';
							}
							$calWhere = ' AND tx_cal_calendar.uid IN (0,' . $row ['calendar_id'] . ')';
							
							if ($table != 'tx_cal_event') {
								$notAllowedItems [] = $row ['uid'];
							}
							
							if (! $GLOBALS ['BE_USER']->user ['admin']) {
								if ($GLOBALS ['BE_USER']->user ['tx_cal_enable_accesscontroll']) {
									$be_userCategories = GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_category'], 1);
									$be_userCalendars = GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_calendar'], 1);
								}
								if (is_array ($GLOBALS ['BE_USER']->userGroups)) {
									foreach ($GLOBALS ['BE_USER']->userGroups as $gid => $group) {
										if ($group ['tx_cal_enable_accesscontroll']) {
											if ($group ['tx_cal_category']) {
												$groupCategories = GeneralUtility::trimExplode (',', $group ['tx_cal_category'], 1);
												$be_userCategories = array_merge ($be_userCategories, $groupCategories);
											}
											if ($group ['tx_cal_calendar']) {
												$groupCalendars = GeneralUtility::trimExplode (',', $group ['tx_cal_calendar'], 1);
												$be_userCalendars = array_merge ($be_userCalendars, $groupCalendars);
											}
										}
									}
								}
								
								if ($be_userCategories [0]) {
									$allowAllCategories = false;
								}
								
								if ($be_userCalendars [0]) {
									$allowAllCalendars = false;
								}
							}
						} else if ($isPluginFlexform && $GLOBALS ['BE_USER']->user ['tx_cal_enable_accesscontroll'] && ! $GLOBALS ['BE_USER']->user ['admin']) {
							$allowAllCalendars = false;
							$allowAllCategories = false;
							$be_userCategories = array (
									0 
							);
							if ($GLOBALS ['BE_USER']->user ['tx_cal_category']) {
								$be_userCategories = GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_category'], 1);
							}
							$be_userCalendars = array (
									0 
							);
							if ($GLOBALS ['BE_USER']->user ['tx_cal_calendar']) {
								$be_userCalendars = GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_calendar'], 1);
							}
							if (is_array ($GLOBALS ['BE_USER']->userGroups)) {
								foreach ($GLOBALS ['BE_USER']->userGroups as $gid => $group) {
									if ($group ['tx_cal_enable_accesscontroll']) {
										if ($group ['tx_cal_category']) {
											$groupCategories = GeneralUtility::trimExplode (',', $group ['tx_cal_category'], 1);
											$be_userCategories = array_merge ($be_userCategories, $groupCategories);
										}
										if ($group ['tx_cal_calendar']) {
											$groupCalendars = GeneralUtility::trimExplode (',', $group ['tx_cal_calendar'], 1);
											$be_userCalendars = array_merge ($be_userCalendars, $groupCalendars);
										}
									}
								}
							}
						} else if ($isPluginFlexform && count ($selectedCalendars) > 0) {
							if ($calendarMode == 0) {
							} else if ($calendarMode == 1) {
								$catWhere = ' AND tx_cal_category.calendar_id in (' . implode (',', $selectedCalendars) . ')';
								$calWhere = ' AND tx_cal_calendar.uid in (' . implode (',', $selectedCalendars) . ')';
							} else { // $calendarMode==2
								$selectedCalendars = array_diff ($selectedCalendars, Array (
										0 
								));
								if (count ($selectedCalendars) > 0) {
									$catWhere = ' AND tx_cal_category.calendar_id not in (' . implode (',', $selectedCalendars) . ')';
									$calWhere = ' AND tx_cal_calendar.uid not in (' . implode (',', $selectedCalendars) . ')';
								}
							}
						} else if (count ($selectedCalendars) > 0) {
							$catWhere = ' AND tx_cal_category.calendar_id in (' . implode (',', $selectedCalendars) . ')';
							$calWhere = ' AND tx_cal_calendar.uid in (' . implode (',', $selectedCalendars) . ')';
						}
					}
					$calWhere .= ' AND tx_cal_calendar.sys_language_uid IN (-1,0)';
					$catWhere .= ' AND tx_cal_category.sys_language_uid IN (-1,0)';
					if ($config ['treeViewClass'] and is_object ($treeViewObj = &GeneralUtility::getUserObj ($config ['treeViewClass'], 'user_', false))) {
					} else {
						$treeViewObj = new \TYPO3\CMS\Cal\Backend\TCA\TceFuncSelectTreeView();
					}
					
					if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
						$enableFields = BackendUtility::BEenableFields ('tx_cal_category') . ' AND tx_cal_category.deleted = 0';
					} else {
						$enableFields = $this->cObj->enableFields ('tx_cal_category');
					}
					
					$catWhere .= $enableFields;
					
					if ($isPluginFlexform) {
						$catWhere .= ' AND pid IN (' . $pidList . ')';
					}
					
					$catres = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_category.uid, tx_cal_category.title, tx_cal_category.calendar_id, tx_cal_category.parent_category', 'tx_cal_category', '1=1' . $catWhere, $treeOrderBy);
					
					$categoryById = array ();
					$categoryByCalendarId = array ();
					$categoryByParentId = array ();
					$allCategoryById = array ();
					$allCategoryByCalendarId = array ();
					$allCategoryByParentId = array ();
					if ($catres) {
						while ($catrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($catres)) {
							if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
								$tempRow = BackendUtility::getRecordLocalization ('tx_cal_category', $catrow ['uid'], $PA ['row'] ['sys_language_uid'], '');
								if (is_array ($tempRow)) {
									$catrow = $tempRow [0];
								}
							}
							
							if (($allowAllCalendars && $allowAllCategories) || (($catrow ['calendar_id'] == 0 || in_array ($catrow ['calendar_id'], $be_userCalendars)) && in_array ($catrow ['uid'], $be_userCategories))) {
								$categoryById [$catrow ['uid']] = $catrow;
								$categoryByCalendarId [$catrow ['calendar_id']] [] = $catrow;
								$categoryByParentId [$catrow ['parent_category']] [] = $catrow;
							}
							$allCategoryById [$catrow ['uid']] = $catrow;
							$allCategoryByCalendarId [$catrow ['calendar_id']] [] = $catrow;
							$allCategoryByParentId [$catrow ['parent_category']] [] = $catrow;
						}
						$GLOBALS ['TYPO3_DB']->sql_free_result ($catres);
					}
					
					$ids = array ();
					foreach ($categoryById as $catRow) {
						$ids = array_merge ($ids, $this->checkChildIds ($catRow ['uid'], $allCategoryByParentId));
					}
					$ids = array_unique ($ids);
					
					foreach ($ids as $id) {
						$categoryById [$id] = $allCategoryById [$id];
						$categoryByCalendarId [$allCategoryById [$id] ['calendar_id']] [] = $allCategoryById [$id];
						if (in_array ($allCategoryById [$id] ['parent_category'], $ids) || $categoryById [$allCategoryById [$id] ['parent_category']]) {
							$categoryByParentId [$allCategoryById [$id] ['parent_category']] [] = $allCategoryById [$id];
						}
					}
					if (! $allowAllCalendars) {
						$calWhere .= ' AND uid IN (' . implode (',', $be_userCalendars) . ')';
					}
					if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
						$enableFields = BackendUtility::BEenableFields ('tx_cal_calendar') . ' AND tx_cal_calendar.deleted = 0';
					} else {
						$enableFields = $this->cObj->enableFields ('tx_cal_calendar');
					}
					$calWhere .= $enableFields;
					
					if ($isPluginFlexform) {
						$calWhere .= ' AND pid IN (' . $pidList . ')';
					}
					$calres = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_calendar.uid, tx_cal_calendar.title', 'tx_cal_calendar', '1=1 ' . $calWhere);
					$calendars = array ();
					if ($calres) {
						while ($calrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($calres)) {
							$calendars [$calrow ['uid']] = $calrow;
						}
						$GLOBALS ['TYPO3_DB']->sql_free_result ($calres);
					}
					
					$dataArray = array ();
					
					if (count ($categoryByCalendarId [0]) > 0) {
						$treeViewObj->MOUNTS [] = 'cal:global';
						$calArray = array ();
						$calArray ['title'] = 'Global Categories:';
						$calArray ['uid'] = 'cal:global';
						foreach ($categoryByCalendarId [0] as $globalCat) {
							if ($globalCat ['parent_category'] == 0 || ($isPluginFlexform && ! $categoryById [$globalCat ['parent_category']])) {
								$childArray = array (
										'title' => $globalCat ['title'],
										'uid' => $globalCat ['uid'] 
								);
								$this->addChildren ($globalCat ['uid'], $childArray, $categoryById, $categoryByParentId, $treeViewObj->subLevelID, $notAllowedItems);
								$calArray [$treeViewObj->subLevelID] [$globalCat ['uid']] = $childArray;
							}
						}
						$dataArray ['cal:global'] = $calArray;
					}
					
					unset ($categoryByCalendarId [0]);
					foreach ($calendars as $calUid => $calendar) {
						$treeViewObj->MOUNTS [] = 'cal:' . $calUid;
						$notAllowedItems [] = 'cal:' . $calUid;
						$calArray = array ();
						$calArray ['title'] = $calendar ['title'];
						$calArray ['uid'] = 'cal:' . $calendar ['uid'];
						if ($categoryByCalendarId [$calUid]) {
							foreach ($categoryByCalendarId [$calUid] as $category) {
								if ($category ['parent_category'] == 0 || ($isPluginFlexform && ! $categoryById [$category ['parent_category']])) {
									$childArray = array (
											'title' => $category ['title'],
											'uid' => $category ['uid'] 
									);
									$this->addChildren ($category ['uid'], $childArray, $categoryById, $categoryByParentId, $treeViewObj->subLevelID, $notAllowedItems);
									$calArray [$treeViewObj->subLevelID] [$category ['uid']] = $childArray;
								}
							}
						}
						$dataArray ['cal:' . $calUid] = $calArray;
					}
					
					// ###################
					
					// $treeViewObj->table = $config['foreign_table'];
					$treeViewObj->table = 'tx_cal_category';
					$treeViewObj->init ($SPaddWhere);
					$treeViewObj->backPath = $this->pObj->backPath;
					
					$treeViewObj->setDataFromArray ($dataArray);
					// $treeViewObj->parentField = $GLOBALS['TCA'][$config['foreign_table']]['ctrl']['treeParentField'];
					// $treeViewObj->parentField = 'parent_category';
					// if($row['calendar_id']){
					// $treeViewObj->clause = ' AND calendar_id = '.$row['calendar_id'];
					// }
					$treeViewObj->expandAll = 1;
					$treeViewObj->expandFirst = 1;
					$treeViewObj->fieldArray = array (
							'uid',
							'title' 
					); // those fields will be filled to the array $treeViewObj->tree
					
					$treeViewObj->ext_IconMode = '1'; // no context menu on icons
					$treeViewObj->title = $GLOBALS ['LANG']->sL ($GLOBALS ['TCA'] [$config ['foreign_table']] ['ctrl'] ['title']);
					
					$treeViewObj->TCEforms_itemFormElName = $PA ['itemFormElName'];
					if ($table == $config ['foreign_table']) {
						$treeViewObj->TCEforms_nonSelectableItemsArray [] = $row ['uid'];
					}
					if (is_array ($notAllowedItems) && $notAllowedItems [0]) {
						foreach ($notAllowedItems as $k) {
							$treeViewObj->TCEforms_nonSelectableItemsArray [] = $k;
						}
					}
					
					// render tree html
					$lockBeUserToDBmounts = $GLOBALS['TYPO3_CONF_VARS']['BE']['lockBeUserToDBmounts'];
					$GLOBALS['TYPO3_CONF_VARS']['BE']['lockBeUserToDBmounts'] = 0;
					$treeContent = $treeViewObj->getBrowsableTree();
					$treeItemC = count($treeViewObj->dataLookup);
					$GLOBALS['TYPO3_CONF_VARS']['BE']['lockBeUserToDBmounts'] = $lockBeUserToDBmounts;
					
					/*
					 * if ($defItems[0]) { // add default items to the tree table. In this case the value [not categorized] $treeItemC += count($defItems); $treeContent .= '<table border="0" cellpadding="0" cellspacing="0"><tr> <td>'.$this->pObj->sL($config['itemsHeader']).'&nbsp;</td><td>'.implode($defItems,'<br />').'</td> </tr></table>'; }
					 */
					
					// find recursive categories or "storagePid" related errors and if there are some, add a message to the $errorMsg array.
					$errorMsg = $this->findRecursiveCategories ($PA, $row, $table, $storagePid, $treeViewObj->ids);
					
					$width = 280; // default width for the field with the category tree
					if (intval ($confArr ['categoryTreeWidth'])) { // if a value is set in extConf take this one.
						if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
							$width = MathUtility::forceIntegerInRange ($confArr ['categoryTreeWidth'], 1, 600);
						} else {
							$width = GeneralUtility::forceIntegerInRange ($confArr ['categoryTreeWidth'], 1, 600);
						}
					} elseif ($GLOBALS ['CLIENT'] ['BROWSER'] == 'msie') { // to suppress the unneeded horizontal scrollbar IE needs a width of at least 320px
						$width = 320;
					}
					
					if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
						$config ['autoSizeMax'] = MathUtility::forceIntegerInRange ($config ['autoSizeMax'], 0);
						$height = $config ['autoSizeMax'] ? MathUtility::forceIntegerInRange ($treeItemC + 2, MathUtility::forceIntegerInRange ($size, 1), $config ['autoSizeMax']) : $size;
					} else {
						$config ['autoSizeMax'] = GeneralUtility::forceIntegerInRange ($config ['autoSizeMax'], 0);
						$height = $config ['autoSizeMax'] ? GeneralUtility::forceIntegerInRange ($treeItemC + 2, GeneralUtility::forceIntegerInRange ($size, 1), $config ['autoSizeMax']) : $size;
					}
					// hardcoded: 16 is the height of the icons
					$height = $height * 16;
					
					$divStyle = 'position:relative; left:0px; top:0px; height:' . $height . 'px; width:' . $width . 'px;border:solid 1px;overflow:auto;background:#fff;margin-bottom:5px;';
					$thumbnails = '<div  name="' . $PA ['itemFormElName'] . '_selTree" style="' . htmlspecialchars ($divStyle) . '">';
					$thumbnails .= $treeContent;
					$thumbnails .= '</div>';
				} else {
					
					$sOnChange = 'setFormValueFromBrowseWin(\'' . $PA ['itemFormElName'] . '\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); ' . implode ('', $PA ['fieldChangeFunc']);
					
					// Put together the select form with selected elements:
					$selector_itemListStyle = isset ($config ['itemListStyle']) ? ' style="' . htmlspecialchars ($config ['itemListStyle']) . '"' : ' style="' . $this->pObj->defaultMultipleSelectorStyle . '"';
					if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
						$size = $config ['autoSizeMax'] ? MathUtility::forceIntegerInRange (count ($itemArray) + 1, MathUtility::forceIntegerInRange ($size, 1), $config ['autoSizeMax']) : $size;
					} else {
						$size = $config ['autoSizeMax'] ? GeneralUtility::forceIntegerInRange (count ($itemArray) + 1, GeneralUtility::forceIntegerInRange ($size, 1), $config ['autoSizeMax']) : $size;
					}
					
					$thumbnails = '<select style="width:150px;" name="' . $PA ['itemFormElName'] . '_sel"' . $this->pObj->insertDefStyle ('select') . ($size ? ' size="' . $size . '"' : '') . ' onchange="' . htmlspecialchars ($sOnChange) . '"' . $PA ['onFocus'] . $selector_itemListStyle . '>';
					// thumbnails = '<select name="'.$PA['itemFormElName'].'_sel"'.$this->pObj->insertDefStyle('select').($size?' size="'.$size.'"':'').' onchange="'.htmlspecialchars($sOnChange).'"'.$PA['onFocus'].$selector_itemListStyle.'>';
					foreach ($selItems as $p) {
						$thumbnails .= '<option value="' . htmlspecialchars ($p [1]) . '">' . htmlspecialchars ($p [0]) . '</option>';
					}
					$thumbnails .= '</select>';
				}
				
				// Perform modification of the selected items array:
				$itemArray = GeneralUtility::trimExplode (',', $PA ['itemFormElValue'], 1);
				
				foreach ($itemArray as $tk => $tv) {
					$tvP = explode ('|', $tv, 2);
					if (in_array ($tvP [0], $removeItems) && ! $PA ['fieldTSConfig'] ['disableNoMatchingValueElement']) {
						$tvP [1] = rawurlencode ($nMV_label);
					} elseif (isset ($PA ['fieldTSConfig'] ['altLabels.'] [$tvP [0]])) {
						$tvP [1] = rawurlencode ($this->pObj->sL ($PA ['fieldTSConfig'] ['altLabels.'] [$tvP [0]]));
					} else {
						$tvP [1] = rawurlencode ($this->pObj->sL (rawurldecode ($tvP [1])));
					}
					$itemArray [$tk] = implode ('|', $tvP);
				}
				$sWidth = 150; // default width for the left field of the category select
				if (intval ($confArr ['categorySelectedWidth'])) {
					if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
						$sWidth = MathUtility::forceIntegerInRange ($confArr ['categorySelectedWidth'], 1, 600);
					} else {
						$sWidth = GeneralUtility::forceIntegerInRange ($confArr ['categorySelectedWidth'], 1, 600);
					}
				}
				if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 7000000) {
					$params = array (
							'size' => $size,
							'autoSizeMax' => MathUtility::forceIntegerInRange ($config ['autoSizeMax'], 0),
							// style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"',
							'style' => ' style="width:' . $sWidth . 'px;"',
							'dontShowMoveIcons' => ($maxitems <= 1),
							'maxitems' => $maxitems,
							'info' => '',
							'headers' => array (
									'selector' => $this->pObj->getLL ('l_selected') . ':<br />',
									'items' => $this->pObj->getLL ('l_items') . ':<br />'
							),
							'noBrowser' => 1,
							'rightbox' => $thumbnails,
							'foreign_table' => 'tx_cal_category',
							'treeConfig' => Array (
									'dataProvider' => 'TYPO3\\CMS\\Cal\\TreeProvider\\MixedTreeDataProvider',
									'parentField' => 'calendar_id',
									'appearance' => Array (
											'showHeader' => TRUE,
											'allowRecursiveMode' => TRUE,
											'expandAll' => TRUE,
											'maxLevels' => 99
									)
							),
					);
				} else if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 4006000) {
					$params = array (
							'size' => $size,
							'autoSizeMax' => MathUtility::forceIntegerInRange ($config ['autoSizeMax'], 0),
							// style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"',
							'style' => ' style="width:' . $sWidth . 'px;"',
							'dontShowMoveIcons' => ($maxitems <= 1),
							'maxitems' => $maxitems,
							'info' => '',
							'headers' => array (
									'selector' => $this->pObj->getLL ('l_selected') . ':<br />',
									'items' => $this->pObj->getLL ('l_items') . ':<br />' 
							),
							'noBrowser' => 1,
							'thumbnails' => $thumbnails 
					);
				} else {
					$params = array (
							'size' => $size,
							'autoSizeMax' => GeneralUtility::forceIntegerInRange ($config ['autoSizeMax'], 0),
							// style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"',
							'style' => ' style="width:' . $sWidth . 'px;"',
							'dontShowMoveIcons' => ($maxitems <= 1),
							'maxitems' => $maxitems,
							'info' => '',
							'headers' => array (
									'selector' => $this->pObj->getLL ('l_selected') . ':<br />',
									'items' => $this->pObj->getLL ('l_items') . ':<br />' 
							),
							'noBrowser' => 1,
							'thumbnails' => $thumbnails 
					);
				}
				if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 7000000) {
					$treeHelperElement = new TreeHelperElement($this->pObj);
					$item .= $treeHelperElement->getDbFileIcon($PA ['itemFormElName'], '', '', $itemArray, '', $params, $PA ['onFocus']);
				} else {
					$item .= $this->pObj->dbFileIcons ($PA ['itemFormElName'], '', '', $itemArray, '', $params, $PA ['onFocus']);
				}
				// Wizards:
				$altItem = '<input type="hidden" name="' . $PA ['itemFormElName'] . '" value="' . htmlspecialchars ($PA ['itemFormElValue']) . '" />';
				
				if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) >= 7000000) {
					$treeHelperElement = new TreeHelperElement($this->pObj);
					$item = $treeHelperElement->getRenderWizards(array (
							$item,
							$altItem 
					), $config ['wizards'], $table, $row, $field, $PA, $PA ['itemFormElName'], $specConf);
					$item .= '<style>.t3-icon-blank {width: 18px;height: 30px;}</style>';
				} else {
					$item = $this->pObj->renderWizards (array (
							$item,
							$altItem 
					), $config ['wizards'], $table, $row, $field, $PA, $PA ['itemFormElName'], $specConf);
				}
			}
		}
		return $this->NA_Items . implode ($errorMsg, chr (10)) . $item;
	}
	function addChildren($uid, &$childArray, &$categoryById, &$categoryByParentId, $subLevelID, &$notAllowedItems) {
		if ($categoryByParentId [$uid]) {
			foreach ($categoryByParentId [$uid] as $category) {
				$childArray2 = array (
						'title' => $category ['title'],
						'uid' => $category ['uid'] 
				);
				if (in_array($uid, $notAllowedItems)) {
					$notAllowedItems [] = $category ['uid'];
				}
				$this->addChildren ($category ['uid'], $childArray2, $categoryById, $categoryByParentId, $subLevelID, $notAllowedItems);
				$childArray [$subLevelID] [$category ['uid']] = $childArray2;
			}
		}
	}
	function checkChildIds($uid, &$categoryByParentId) {
		$childIds = $categoryByParentId [$uid];
		$ids = array ();
		if ($childIds) {
			foreach ($childIds as $child) {
				$ids [] = $child ['uid'];
				$returnIds = $this->checkChildIds ($child ['uid'], $categoryByParentId);
				$ids = array_merge ($ids, $returnIds);
			}
		}
		return $ids;
	}
	
	/**
	 * This function checks if there are categories selectable that are not allowed for this BE user and if the current record has
	 * already categories assigned that are not allowed.
	 * If such categories were found they will be returned and "$this->NA_Items" is filled with an error message.
	 * The array "$itemArr" which will be returned contains the list of all non-selectable categories. This array will be added to "$treeViewObj->TCEforms_nonSelectableItemsArray". If a category is in this array the "select item" link will not be added to it.
	 *
	 * @param array $PA:
	 *        	paramter array
	 * @param string $SPaddWhere:
	 *        	string is added to the query for categories when "useStoragePid" is set.
	 * @return array with not allowed categories
	 * @see tx_ttnews_tceFunc_selectTreeView::wrapTitle()
	 */
	function getNotAllowedItems($PA, $SPaddWhere) {
		$fTable = $PA ['fieldConf'] ['config'] ['foreign_table'];
		// get list of allowed categories for the current BE user
		$allowedItemsList = $GLOBALS ['BE_USER']->getTSConfigVal ('tt_newsPerms.' . $fTable . '.allowedItems');
		
		$itemArr = array ();
		if ($allowedItemsList) {
			// get all categories
			$res = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('uid', $fTable, '1=1' . $SPaddWhere . ' AND NOT deleted');
			if ($res) {
				while ($row = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($res)) {
					if (! GeneralUtility::inList ($allowedItemsList, $row ['uid'])) { // remove all allowed categories from the category result
						$itemArr [] = $row ['uid'];
					}
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($res);
			}
			
			if (! $PA ['row'] ['sys_language_uid'] && ! $PA ['row'] ['l18n_parent']) {
				$catvals = explode (',', $PA ['row'] ['category']); // get categories from the current record
				$notAllowedCats = array ();
				foreach ($catvals as $k) {
					$c = explode ('|', $k);
					if ($c [0] && ! GeneralUtility::inList ($allowedItemsList, $c [0])) {
						$notAllowedCats [] = '<p style="padding:0px;color:red;font-weight:bold;">- ' . $c [1] . ' <span class="typo3-dimmed"><em>[' . $c [0] . ']</em></span></p>';
					}
				}
				if ($notAllowedCats [0]) {
					$this->NA_Items = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">SAVING DISABLED!! <br />This record has the following categories assigned that are not defined in your BE usergroup: ' . implode ($notAllowedCats, chr (10)) . '</td></tr></tbody></table>';
				}
			}
		}
		
		return $itemArr;
	}
	
	/**
	 * detects recursive categories and returns an error message if recursive categories where found
	 *
	 * @param array $PA:
	 *        	paramter array
	 * @param array $row:
	 *        	current row
	 * @param array $table:
	 *        	table
	 * @param integer $storagePid:
	 *        	StoragePid (pid of the category folder)
	 * @param array $treeIds:
	 *        	with the ids of the categories in the tree
	 * @return array messages
	 */
	function findRecursiveCategories($PA, $row, $table, $storagePid, $treeIds) {
		$errorMsg = array ();
		if ($table == 'tt_content' && $row ['CType'] == 'list' && $row ['list_type'] == 9) { // = tt_content element which inserts plugin tt_news
			$cfgArr = GeneralUtility::xml2array ($row ['pi_flexform']);
			if (is_array ($cfgArr) && is_array ($cfgArr ['data'] ['sDEF'] ['lDEF']) && $cfgArr ['data'] ['sDEF'] ['lDEF'] ['categorySelection']) {
				$rcList = $this->compareCategoryVals ($treeIds, $cfgArr ['data'] ['sDEF'] ['lDEF'] ['categorySelection'] ['vDEF']);
			}
		} elseif ($table == 'tt_news_cat' || $table == 'tt_news') {
			if ($table == 'tt_news_cat' && $row ['pid'] == $storagePid && intval ($row ['uid']) && ! in_array ($row ['uid'], $treeIds)) { // if the selected category is not empty and not in the array of tree-uids it seems to be part of a chain of recursive categories
				$recursionMsg = 'RECURSIVE CATEGORIES DETECTED!! <br />This record is part of a chain of recursive categories. The affected categories will not be displayed in the category tree.	You should remove the parent category of this record to prevent this.';
			}
			if ($table == 'tt_news' && $row ['category']) { // find recursive categories in the tt_news db-record
				$rcList = $this->compareCategoryVals ($treeIds, $row ['category']);
			}
			// in case of localized records this doesn't work
			if ($storagePid && $row ['pid'] != $storagePid && $table == 'tt_news_cat') { // if a storagePid is defined but the current category is not stored in storagePid
				$errorMsg [] = '<p style="padding:10px;"><img src="gfx/icon_warning.gif" class="absmiddle" alt="" height="16" width="18"><strong style="color:red;"> Warning:</strong><br />tt_news is configured to display categories only from the "General record storage page" (GRSP). The current category is not located in the GRSP and will so not be displayed. To solve this you should either define a GRSP or disable "Use StoragePid" in the extension manager.</p>';
			}
		}
		if (strlen ($rcList)) {
			$recursionMsg = 'RECURSIVE CATEGORIES DETECTED!! <br />This record has the following recursive categories assigned: ' . $rcList . '<br />Recursive categories will not be shown in the category tree and will therefore not be selectable. ';
			
			if ($table == 'tt_news') {
				$recursionMsg .= 'To solve this problem mark these categories in the left select field, click on "edit category" and clear the field "parent category" of the recursive category.';
			} else {
				$recursionMsg .= 'To solve this problem you should clear the field "parent category" of the recursive category.';
			}
		}
		if ($recursionMsg)
			$errorMsg [] = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">' . $recursionMsg . '</td></tr></tbody></table>';
		return $errorMsg;
	}
	
	/**
	 * This function compares the selected categories ($catString) with the categories from the category tree ($treeIds).
	 * If there are categories selected that are not present in the array $treeIds it assumes that those categories are
	 * parts of a chain of recursive categories returns their uids.
	 *
	 * @param array $treeIds:
	 *        	with the ids of the categories in the tree
	 * @param string $catString:
	 *        	selected categories in a string (format: uid|title,uid|title,...)
	 * @return string of recursive categories
	 */
	function compareCategoryVals($treeIds, $catString) {
		$recursiveCategories = array ();
		$showncats = implode ($treeIds, ','); // the displayed categories (tree)
		$catvals = explode (',', $catString); // categories of the current record (left field)
		foreach ($catvals as $k) {
			$c = explode ('|', $k);
			if (! GeneralUtility::inList ($showncats, $c [0])) {
				$recursiveCategories [] = $c;
			}
		}
		if ($recursiveCategories [0]) {
			$rcArr = array ();
			foreach ($recursiveCategories as $key => $cat) {
				if ($cat [0])
					$rcArr [] = $cat [1] . ' (' . $cat [0] . ')'; // format result: title (uid)
			}
			$rcList = implode ($rcArr, ', ');
		}
		return $rcList;
	}
	
	/**
	 * This functions displays the title field of a news record and checks if the record has categories assigned that are not allowed for the current BE user.
	 * If there are non allowed categories an error message will be displayed.
	 *
	 * @param array $PA:
	 *        	parameter array for the current field
	 * @param object $fobj:
	 *        	to the parent object
	 * @return string HTML code for the field and the error message
	 */
	function displayTypeFieldCheckCategories(&$PA, $fobj) {
		$table = $PA ['table'];
		$field = $PA ['field'];
		$row = $PA ['row'];
		
		if ($GLOBALS ['BE_USER']->getTSConfigVal ('options.useListOfAllowedItems') && ! $GLOBALS ['BE_USER']->isAdmin ()) {
			$notAllowedItems = array ();
			if ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['tt_news']) { // get tt_news extConf array
				$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['tt_news']);
			}
			if ($confArr ['useStoragePid']) {
				$TSconfig = BackendUtility::getTCEFORM_TSconfig ($table, $row);
				$storagePid = $TSconfig ['_STORAGE_PID'] ? $TSconfig ['_STORAGE_PID'] : 0;
				$SPaddWhere = ' AND tt_news_cat.pid IN (' . $storagePid . ')';
			}
			$notAllowedItems = $this->getNotAllowedItems ($PA, $SPaddWhere);
			if ($notAllowedItems [0]) {
				// get categories of the record in db
				$uidField = $row ['l18n_parent'] && $row ['sys_language_uid'] ? $row ['l18n_parent'] : $row ['uid'];
				$catres = $GLOBALS ['TYPO3_DB']->exec_SELECT_mm_query ('tt_news_cat.uid,tt_news_cat.title,tt_news_cat_mm.sorting AS mmsorting', 'tt_news', 'tt_news_cat_mm', 'tt_news_cat', ' AND tt_news_cat_mm.uid_local=' . $uidField . $SPaddWhere, '', 'mmsorting');
				$NACats = array ();
				if ($catres) {
					while ($catrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($catres)) {
						if ($catrow ['uid'] && $notAllowedItems [0] && in_array ($catrow ['uid'], $notAllowedItems)) {
							$NACats [] = '<p style="padding:0px;color:red;font-weight:bold;">- ' . $catrow ['title'] . ' <span class="typo3-dimmed"><em>[' . $catrow ['uid'] . ']</em></span></p>';
						}
					}
					$GLOBALS ['TYPO3_DB']->sql_free_result ($catres);
				}
				
				if ($NACats [0]) {
					$NA_Items = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">SAVING DISABLED!! <br />' . ($row ['l18n_parent'] && $row ['sys_language_uid'] ? 'The translation original of this' : 'This') . ' record has the following categories assigned that are not defined in your BE usergroup: ' . implode ($NACats, chr (10)) . '</td></tr></tbody></table>';
				}
			}
		}
		// unset foreign table to prevent adding of categories to the "type" field
		$PA ['fieldConf'] ['config'] ['foreign_table'] = '';
		$PA ['fieldConf'] ['config'] ['foreign_table_where'] = '';
		if (! $row ['l18n_parent'] && ! $row ['sys_language_uid']) { // render "type" field only for records in the default language
			$fieldHTML = $fobj->getSingleField_typeSelect ($table, $field, $row, $PA);
		}
		return $NA_Items . $fieldHTML;
	}
}

?>