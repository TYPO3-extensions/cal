<?php
namespace TYPO3\CMS\Cal\Backend\Modul;
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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;

/**
 * Module 'Indexer' for the 'cal' extension.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class CalIndexer extends \TYPO3\CMS\Backend\Module\BaseScriptClass {
	
	var $pageinfo;
	
	/**
	 * Adds items to the ->MOD_MENU array.
	 * Used for the function menu selector.
	 */
	function menuConfig() {
		$this->MOD_MENU = Array (
				'function' => Array (
						'1' => $GLOBALS ['LANG']->getLL ('function1'),
						'2' => $GLOBALS ['LANG']->getLL ('function2') 
				) 
		);
		parent::menuConfig ();
	}
	
	// If you chose 'web' as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module.
	 * Write the content to $this->content
	 */
	function main() {

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = \TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess ($this->id, $this->perms_clause);
		$access = is_array ($this->pageinfo) ? 1 : 0;

		$this->doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
		if (($this->id && $access) || ($GLOBALS['BE_USER']->user ['admin'] && ! $this->id)) {
			
			// Draw the header.
			$this->doc->form = '<form action="" method="POST">';
			
			// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode = '
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval ($this->id) . ';
				</script>
			';
			
			$headerSection = $this->doc->getHeader ('pages', $this->pageinfo, $this->pageinfo ['_thePath']) . '<br>' . $GLOBALS ['LANG']->sL ('LLL:EXT:lang/locallang_core.php:labels.path') . ': ' . GeneralUtility::fixed_lgd_cs ($this->pageinfo ['_thePath'], - 50);
			
			$this->content .= $this->doc->startPage ($GLOBALS ['LANG']->getLL ('title'));
			$this->content .= $this->doc->header ($GLOBALS ['LANG']->getLL ('title'));
			$this->content .= $this->doc->spacer (5);
			$this->content .= $this->doc->section ('', $this->doc->funcMenu ($headerSection, \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu ($this->id, 'SET[function]', $this->MOD_SETTINGS ['function'], $this->MOD_MENU ['function'])));
			$this->content .= $this->doc->divider (5);
			
			// Render content:
			$this->moduleContent ();
			
			// ShortCut
			if ($GLOBALS['BE_USER']->mayMakeShortcut ()) {
				$this->content .= $this->doc->spacer (20) . $this->doc->section ('', $this->doc->makeShortcutIcon ('id', implode (',', array_keys ($this->MOD_MENU)), $this->MCONF ['name']));
			}
			
			$this->content .= $this->doc->spacer (10);
		} else {
			// If no access or if ID == zero
			
			$this->content .= $this->doc->startPage ($GLOBALS ['LANG']->getLL ('title'));
			$this->content .= $this->doc->header ($GLOBALS ['LANG']->getLL ('title'));
			$this->content .= $this->doc->spacer (5);
			$this->content .= $this->doc->spacer (10);
		}
	}
	
	/**
	 * Prints out the module HTML
	 */
	function printContent() {
		$this->content .= $this->doc->endPage ();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 */
	function moduleContent() {
		switch (intval ($this->MOD_SETTINGS ['function'])) {
			case 2 :
				$postVarArray = GeneralUtility::_POST ();
				$pageIds = Array ();
				if(isset($postVarArray['pageIds']) && isset($postVarArray['tsPage'])){
					$tsPage = intval($postVarArray['tsPage']);
					foreach(explode(',',$postVarArray['pageIds']) as $pageId){
						if($tsPage > 0) {
							$pageIds [intval ($pageId)] = $tsPage;
						}
					}
				}
				if(isset($postVarArray['pageIds']) && empty($pageIds)) {
					$content = self::getMessage($GLOBALS ['LANG']->getLL ('atLeastOne'), FlashMessage::ERROR);
				}
// 				foreach ($postVarArray as $name => $value) {
// 					if (strpos ($name, 'pageId') === 0) {
// 						$valuePageArray = explode ('_', $value);
// 						preg_match ('/pageId([0-9]*)_list/', $name, $matches);
// 						$pageIds [intval ($matches [1])] = intval ($valuePageArray [1]);
// 					}
// 				}
				
				$starttime = GeneralUtility::_POST ('starttime');
				if ($starttime) {
					$starttime = intval($this->getTimeParsed($starttime)->format('%Y%m%d'));
				}
				$endtime = GeneralUtility::_POST ('endtime');
				if ($endtime) {
					$endtime = intval($this->getTimeParsed($endtime)->format('%Y%m%d'));
				}

				if (count($pageIds) > 0 && is_int ($starttime) && is_int ($endtime)) {
					$content = $GLOBALS ['LANG']->getLL ('indexing') . '<br/>';
					/** @var \TYPO3\CMS\Cal\Utility\RecurrenceGenerator $rgc */
					$rgc = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Utility\\RecurrenceGenerator', 0, $starttime, $endtime);
					foreach ($pageIds as $eventPage => $pluginPage) {
						$this->content .= $this->doc->section (sprintf($GLOBALS ['LANG']->getLL ('droppingTable'),$eventPage), $rgc->cleanIndexTable ($eventPage), 0, 1);
						$rgc->pageIDForPlugin = $pluginPage;
						$this->content .= $this->doc->section ('PID ' . $eventPage . $GLOBALS ['LANG']->getLL ('toBeIndexed'), $rgc->countRecurringEvents ($eventPage), 0, 1);
						$rgc->generateIndex ($eventPage);
						$this->content .= $this->doc->section ($GLOBALS ['LANG']->getLL ('result'), $rgc->getInfo (), 0, 1);
						$this->content .= '<input type="button" value="' . $GLOBALS ['LANG']->getLL ('back') . '" onclick="history.back();"/>';
					}
				} else {
					$extConf = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
// 					$this->content .= '<script type="text/javascript">' . $this->getJScode () . '</script>';
// 					if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger (TYPO3_version) > 4004999) {
// 						$this->content .= '<script type="text/javascript" src="jsfunc.tbe_editor.js"></script>';
// 					}

					/** @var \TYPO3\CMS\Cal\Utility\RecurrenceGenerator $rgc */
					$rgc = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Utility\\RecurrenceGenerator');
					$pages = $rgc->getRecurringEventPages();
					$selectFieldIds = Array ();
// 					$content .= '<table><tbody>';
// 					$content .= '<tr><td>';
// 					$content .= '"' . $GLOBALS ['LANG']->getLL ('tableHeader2') . ' :';
// 					$content .= '</td><td>';
// 					$content .= '<select id="pageId' . $pid . '" class="formField3 tceforms-multiselect" style="width: 150px;" name="pageId' . $pid . '_list" multiple="multiple" size="1"></select>';
// 					$content .= '<a href="#" onclick="setFormValueOpenBrowser(\'db\',\'pageId' . $pid . '|||pages\'); return false;"><img src="sysext/t3skin/icons/gfx/insert3.gif" alt="' . $GLOBALS ['LANG']->getLL ('browse') . '" title="' . $GLOBALS ['LANG']->getLL ('browse') . '" border="0" height="15" width="15"></a>';
// 					$content .= '</td></tr>';
// 					$selectFieldIds [] = 'pageId' . $pid;
// 					$content .= '<tbody></table>';
					
					if(!empty($pages)) {
						$content .= '<style>.calIndexerTable th { background-color: #EEEEEE;} .calIndexerTable td, .calIndexerTable th { border: 1px solid #999999; padding:5px; text-align: center;}</style>';
						$content .= '<table class="calIndexerTable"><thead>';
						$content .= '<tr><th>'.$GLOBALS ['LANG']->getLL ('tableHeader1').'</th><th>'.$GLOBALS ['LANG']->getLL ('tableHeader2').'</th></tr></thead><tbody>';
						
						foreach ($pages as $pageId => $pageTitle) {
							$content .= '<tr><td>';
							$content .=$pageTitle.' ['.$pageId.']<input name="pageIds" id="pageId['.$pageId.']" type="hidden" value="'.$pageId.'"><br />';
							$content .= '</td><td>';
							$content .= '<input name="tsPage" id="tsPageId['.$pageId.']" type="text" value="" size="15" maxlength="5"><br />';
							$content .= '</td></tr>';
						}
						
						$content .= '<tbody></table>';
						
						$selectFields = '';
						foreach ($selectFieldIds as $selectFieldId) {
							$selectFields .= ' var o' . $selectFieldId . ' = document.getElementById("' . $selectFieldId . '");if(o' . $selectFieldId . '.options.length > 0){o' . $selectFieldId . '.options[0].selected = "selected";} else {notComplete = 1;}';
						}
						$content .= '<script type="text/javascript">function markSelections(){ var notComplete = 0;' . $selectFields . ' if(notComplete == 1){alert("' . $GLOBALS ['LANG']->getLL ('notAllPagesAssigned') . '");return false;}return true;}</script>';
						
						$this->content .= $this->doc->section ($GLOBALS ['LANG']->getLL ('selectPage'), $content, 0, 1);
						$this->content .= $this->doc->section ($GLOBALS ['LANG']->getLL ('indexStart'), '<input name="starttime" type="text" value="' . $extConf ['recurrenceStart'] . '" size="8" maxlength="8">', 0, 1);
						$this->content .= $this->doc->section ($GLOBALS ['LANG']->getLL ('indexEnd'), '<input name="endtime" type="text" value="' . $extConf ['recurrenceEnd'] . '" size="8" maxlength="8">', 0, 1);
						$this->content .= '<br /><br /><input type="submit" value="' . $GLOBALS ['LANG']->getLL ('startIndexing') . '" onclick="return markSelections();"/>';
					} else {
						$this->content .= self::getMessage($GLOBALS ['LANG']->getLL ('nothingToDo'),FlashMessage::INFO);
					}
				}
				break;
			default :
				$this->content .= $this->doc->section ($GLOBALS ['LANG']->getLL ('notice_header'), $GLOBALS ['LANG']->getLL ('notice'), 0, 1);
				$this->content .= $this->doc->section ($GLOBALS ['LANG']->getLL ('capabilities_header'), $GLOBALS ['LANG']->getLL ('capabilities'), 0, 1);
				break;
		}
	}
	private function getJScode() {
		$forms = new \TYPO3\CMS\Backend\Form\FormEngine();
		$forms->backPath = $GLOBALS['BACK_PATH'];
	}
	
	private function getTimeParsed($timeString) {
		$dp = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Controller\\DateParser');
		$dp->parse ($timeString, 0, '');
		return $dp->getDateObjectFromStack ();
	}
	
	public static function getMessage($message, $type) {
		/** @var $flashMessage FlashMessage */
		$flashMessage = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
				htmlspecialchars($message),
				'',
				$type,
				TRUE
		);
		/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
		$flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$defaultFlashMessageQueue->enqueue($flashMessage);
		return $defaultFlashMessageQueue->renderFlashMessages();
	}
}
?>