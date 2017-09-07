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
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

/**
 * TCA tree data provider which considers
 */
class DatabaseTreeDataProvider extends \TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeDataProvider {

	/**
	 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected $backendUserAuthentication;
	
	protected $parentRow;
	
	protected $table;
	
	protected $field;
	
	protected $currentValue;
	
	protected $conf;
	
	protected $calConfiguration;
	
	const CALENDAR_PREFIX = 'calendar_';
	const GLOBAL_PREFIX = 'global';

	/**
	 * Required constructor
	 *
	 * @param array $configuration TCA configuration
	 */
	public function __construct (array $configuration, $table, $field, $currentValue) {
	    $this->table = $table;
	    $this->field = $field;
	    $this->conf = $configuration;
	    $this->currentValue = $currentValue;
		$this->backendUserAuthentication = $GLOBALS['BE_USER'];
		$this->calConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
	}
	
	/**
	 * Sets the list for selected nodes
	 *
	 * @param string $selectedList
	 * @return void
	 */
	public function setSelectedList($selectedList) {
		// During initialization the first set contains the parent object row.
		// Where as the second call really fills the correct values.
		if($this->selectedList == ''){
			$this->parentRow = $selectedList;
		}
		$this->selectedList = $selectedList;
	}
	
	/**
	 * Loads the tree data (all possible children)
	 *
	 * @return void
	 */
	protected function loadTreeData() {
		if($this->calConfiguration['categoryService'] == 'sys_category') {
			parent::loadTreeData();
			return;
		}
		$this->treeData->setId($this->getRootUid());
		$this->treeData->setParentNode(NULL);
		$level = 1;
		
		if ($this->levelMaximum >= $level) {
				
			$childNodes = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Tree\TreeNodeCollection::class);
				
			$this->appendGlobalCategories($level, $childNodes);
			$this->appendCalendarCategories($level, $childNodes);
				
			if ($childNodes !== NULL) {
				$this->treeData->setChildNodes($childNodes);
			}
		}
	}
	
	protected function appendGlobalCategories($level, $parentChildNodes){
		$node = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Tree\TreeNode::class);
		$node->setId(GLOBAL_PREFIX);
		
		$childNodes = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Tree\TreeNodeCollection::class);
		
		$where = 'l18n_parent = 0 and deleted = 0 and parent_category = 0 and calendar_id = 0';
		$this->appendCategories($level, $childNodes, $where);
		if ($childNodes !== NULL) {
			$node->setChildNodes($childNodes);
		}
		
		$parentChildNodes->append($node);
	}
	
	protected function appendCalendarCategories($level, $childNodes){
	    $calendarId = 0;
	    if(\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) > 8000000){
    		if(isset($this->currentValue['calendar_id'])){
    			$calendarId = $this->currentValue['calendar_id'];
    		}
	    } else {
	        if(isset($this->parentRow['calendar_id'][0])){
	            $calendarId = $this->parentRow['calendar_id'][0];
	        }
	    }
		if($calendarId > 0){
			$calres = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_calendar.uid, tx_cal_calendar.title', 'tx_cal_calendar', $this->getCalendarWhere($calendarId));
			if ($calres) {
				while ($calrow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($calres)) {
					$node = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Tree\TreeNode::class);
					$node->setId(CALENDAR_PREFIX.$calrow['uid']);
					
					if ($level < $this->levelMaximum) {
						$where = 'l18n_parent = 0 and tx_cal_category.deleted = 0 and tx_cal_category.calendar_id = '.$calrow['uid'];
						$calendarChildNodes = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Tree\TreeNodeCollection::class);
						
						$this->appendCategories($level + 1, $calendarChildNodes, $where);
						if ($calendarChildNodes !== NULL) {
							$node->setChildNodes($calendarChildNodes);
						}
					}
					$childNodes->append($node);
				}
				$GLOBALS ['TYPO3_DB']->sql_free_result ($calres);
			}
		}
	}
	
	protected function appendCategories($level, $childNodes, $where){
		$categoryResult = $GLOBALS ['TYPO3_DB']->exec_SELECTquery ('tx_cal_category.uid, tx_cal_category.title', 'tx_cal_category', $where);
		$usedCategories = [];
		if ($categoryResult) {
			while ($categoryRow = $GLOBALS ['TYPO3_DB']->sql_fetch_assoc ($categoryResult)) {
				$categoryNode = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Tree\TreeNode::class);
				$categoryNode->setId($categoryRow['uid']);
				if ($level < $this->levelMaximum) {
					$children = $this->getChildrenOf($categoryNode, $level + 1);
					if ($children !== NULL) {
					    foreach ($children as $child) {
					        $usedCategories[$child->getId()] = TRUE;
					    }
						$categoryNode->setChildNodes($children);
					}
				}
				if(!$usedCategories[$categoryRow['uid']]) {
				    $usedCategories[$categoryRow['uid']] = TRUE;
				    $childNodes->append($categoryNode);
				}
			}
			$GLOBALS ['TYPO3_DB']->sql_free_result ($categoryResult);
		}
	}
	
	protected function getCalendarWhere($calendarId){
		$calWhere = 'l18n_parent = 0  AND tx_cal_calendar.uid = '.$calendarId;
	
		if ((TYPO3_MODE == 'BE') || ($GLOBALS ['TSFE']->beUserLogin && $GLOBALS ['BE_USER']->extAdmEnabled)) {
			$calWhere .= BackendUtility::BEenableFields ('tx_cal_calendar') . ' AND tx_cal_calendar.deleted = 0';
		}
		return $calWhere;
	}
	
	/**
	 * Builds a complete node including children
	 *
	 * @param \TYPO3\CMS\Backend\Tree\TreeNode|\TYPO3\CMS\Backend\Tree\TreeNode $basicNode
	 * @param NULL|\TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeNode $parent
	 * @param integer $level
	 * @param bool $restriction
	 * @return \TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeNode node
	 */
	protected function buildRepresentationForNode (\TYPO3\CMS\Backend\Tree\TreeNode $basicNode, \TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeNode $parent = NULL, $level = 0, $restriction = FALSE) {
		/**@param $node \TYPO3\CMS\Core\Tree\TableConfiguration\DatabaseTreeNode */
		$node = GeneralUtility::makeInstance ('TYPO3\\CMS\\Core\\Tree\\TableConfiguration\\DatabaseTreeNode');
		$row = array();
		$node->setSelected (FALSE);
		$node->setExpanded (TRUE);
		$node->setSelectable(FALSE);
		
		if(strrpos($basicNode->getId (), CALENDAR_PREFIX, -strlen($basicNode->getId ())) !== FALSE) {
			$id = intval(substr($basicNode->getId (),strlen(CALENDAR_PREFIX)));
			$row = BackendUtility::getRecordWSOL ('tx_cal_calendar', $id, '*', '', FALSE);
			$iconFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconFactory');
			$icon = $iconFactory->getIconForRecord('tx_cal_calendar', $row, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
			$node->setIcon($icon);
			$node->setLabel ($row['title']);
			$node->setSortValue($id);
		} else if($basicNode->getId () === GLOBAL_PREFIX) {
			$node->setLabel ($GLOBALS['LANG']->sL ('LLL:EXT:cal/Resources/Private/Language/locallang_db.xml:tx_cal_category.global'));
			$node->setSortValue(0);
		} else if ($basicNode->getId () == 0) {
			$node->setLabel ($GLOBALS['LANG']->sL ($GLOBALS['TCA'][$this->tableName]['ctrl']['title']));
		} else {
			$row = BackendUtility::getRecordWSOL ($this->tableName, $basicNode->getId (), '*', '', FALSE);

			if ($this->getLabelField () !== '') {
				$node->setLabel($row[$this->getLabelField()]);
			} else {
				$node->setLabel ($basicNode->getId ());
			}
			$node->setSelected (GeneralUtility::inList ($this->getSelectedList (), $basicNode->getId ()));
			$node->setExpanded ($this->isExpanded ($basicNode));
			$node->setLabel ($node->getLabel ());
			$iconFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconFactory');
			$icon = $iconFactory->getIconForRecord($this->tableName, $row, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL);
			$node->setIcon($icon);
			$node->setSelectable (!GeneralUtility::inList ($this->getNonSelectableLevelList (), $level) && !in_array ($basicNode->getId (), $this->getItemUnselectableList ()));
			$node->setSortValue ($this->nodeSortValues[$basicNode->getId ()]);
		}

		$node->setId ($basicNode->getId ());

		// Break to force single category activation
		if ($parent != NULL && $level != 0 && $this->isSingleCategoryAclActivated() && !$this->isCategoryAllowed ($node)) {
			return NULL;
		}
		
		$node->setParentNode ($parent);
		if ($basicNode->hasChildNodes ()) {
			
			/** @var \TYPO3\CMS\Backend\Tree\SortedTreeNodeCollection $childNodes */
			$childNodes = GeneralUtility::makeInstance ('TYPO3\\CMS\\Backend\\Tree\\SortedTreeNodeCollection');
			$foundSomeChild = FALSE;
			foreach ($basicNode->getChildNodes () as $child) {
				// Change in custom TreeDataProvider by adding the if clause
				if ($restriction || $this->isCategoryAllowed ($child)) {
					$returnedChild = $this->buildRepresentationForNode ($child, $node, $level + 1, $restriction);

					if (!is_null ($returnedChild)) {
						$foundSomeChild = TRUE;
						$childNodes->append ($returnedChild);
					} else {
						$node->setParentNode (NULL);
						$node->setHasChildren (FALSE);
					}
				}
				// Change in custom TreeDataProvider end
			}

			if ($foundSomeChild) {
			    $node->setHasChildren (TRUE);
				$node->setChildNodes ($childNodes);
			}
		}
		return $node;
	}

	/**
	 * Check if given category is allowed by the access rights
	 *
	 * @param \TYPO3\CMS\Backend\Tree\TreeNode $child
	 * @return bool
	 */
	protected function isCategoryAllowed ($child) {
	    
		if($this->calConfiguration['categoryService'] == 'sys_category') {
    		$mounts = $this->backendUserAuthentication->getCategoryMountPoints();
    		if (empty($mounts)) {
    			return TRUE;
    		}
    
    		return in_array($child->getId(), $mounts);
	    } else {
	        if ($child->getId() === GLOBAL_PREFIX) {
	            return TRUE;
	        }
	        
	        if ($GLOBALS ['BE_USER']->user ['admin']) {
	            return TRUE;
	        }
	        
	        $be_userCategories = [];
	        $be_userCalendars = [];
	        
	        if ($GLOBALS ['BE_USER']->user ['tx_cal_enable_accesscontroll']) {
	            $be_userCategories = GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_category'], 1);
	            $be_userCalendars = GeneralUtility::trimExplode (',', $GLOBALS ['BE_USER']->user ['tx_cal_calendar'], 1);
	        } else {
	            return TRUE;
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
	        
	        if(strrpos($child->getId(), CALENDAR_PREFIX, -strlen($child->getId())) !== FALSE) {
	            $allow = in_array(substr($child->getId(), strlen(CALENDAR_PREFIX)), $be_userCalendars);
	        } else {
	            $allow = in_array($child->getId(), $be_userCategories);
	        }
	        
	        return $allow;
	    }
	}

	/**
	 *
	 * @return bool
	 */
	protected function isSingleCategoryAclActivated() {
		return FALSE;
	}

}
