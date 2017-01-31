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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Module 'Indexer' for the 'cal' extension.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class CalIndexer extends \TYPO3\CMS\Backend\Module\BaseScriptClass {
	
	/**
     * Array containing submitted data when editing or adding a task
     *
     * @var array
     */
    protected $submittedData = [];

    /**
     * Array containing all messages issued by the application logic
     * Contains the error's severity and the message itself
     *
     * @var array
     */
    protected $messages = [];

    /**
     * @var string Key of the CSH file
     */
    protected $cshKey;

    /**
     * @var string
     */
    protected $backendTemplatePath = '';

    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView
     */
    protected $view;

    /**
     * The name of the module
     *
     * @var string
     */
    protected $moduleName = 'tools_txcalM1';

    /**
     * @var string Base URI of scheduler module
     */
    protected $moduleUri;

    /**
     * ModuleTemplate Container
     *
     * @var ModuleTemplate
     */
    protected $moduleTemplate;
	
	var $pageinfo;
	
	/**
	 * @return \TYPO3\CMS\Cal\Backend\Modul\CalIndexer
	 */
	public function __construct()
	{
		$this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
		$this->getLanguageService()->includeLLFile('EXT:cal/Resources/Private/Language/locallang_indexer.xml');
		$this->MCONF = [
				'name' => $this->moduleName,
		];
		$this->cshKey = '_MOD_' . $this->moduleName;
		$this->backendTemplatePath = ExtensionManagementUtility::extPath('cal') . 'Resources/Private/Templates/Backend/IndexerModule/';
		$this->view = GeneralUtility::makeInstance(\TYPO3\CMS\Fluid\View\StandaloneView::class);
		$this->view->getRequest()->setControllerExtensionName('cal');
		$this->view->setPartialRootPaths([ExtensionManagementUtility::extPath('cal') . 'Resources/Private/Templates/Backend/IndexerModule/Partials/']);
		$this->moduleUri = BackendUtility::getModuleUrl($this->moduleName);
	
		$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/Modal');
		$pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/SplitButtons');
	}
	
	/**
	 * Adds items to the ->MOD_MENU array.
	 * Used for the function menu selector.
	 */
	public function menuConfig() {
		$this->MOD_MENU = Array (
				'function' => Array (
						'1' => $this->getLanguageService()->getLL ('function1'),
						'2' => $this->getLanguageService()->getLL ('function2') 
				) 
		);
		parent::menuConfig ();
	}
	
	/**
	 * Injects the request object for the current request or subrequest
	 * Simply calls main() and init() and outputs the content
	 *
	 * @param ServerRequestInterface $request the current request
	 * @param ResponseInterface $response
	 * @return ResponseInterface the response with the content
	 */
	public function mainAction(ServerRequestInterface $request, ResponseInterface $response) {
		$GLOBALS['SOBE'] = $this;
		$this->init();
		$this->main();
		
		$this->moduleTemplate->setContent($this->content);
		$response->getBody()->write($this->moduleTemplate->renderContent());
		return $response;
	}
	
	/**
	 * Generates the action menu
	 */
	protected function getModuleMenu()
	{
		$menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
		$menu->setIdentifier('CalJumpMenu');
	
		foreach ($this->MOD_MENU['function'] as $controller => $title) {
			
			$item = $menu
			->makeMenuItem()
			->setHref(
				BackendUtility::getModuleUrl(
						$this->moduleName,
						[
								'id' => $this->id,
								'SET' => [
										'function' => $controller
								]
						]
						)
				)
				->setTitle($title);
					
			if (intval($controller) == intval($this->MOD_SETTINGS['function'])) {
				$item->setActive(true);
			}
			$menu->addMenuItem($item);
		}
		$this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
	}
	
	// If you chose 'web' as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module.
	 * Write the content to $this->content
	 */
	protected function main() {
		
		// Access check!
		// The page will show only if user has admin rights
		if ($this->getBackendUser()->isAdmin()) {
			// Set the form
			$this->content = '<form name="tx_cal_form" id="tx_cal_form" method="post" action="">';
		
			// Prepare main content
			$this->content .= '<h1>' . $this->getLanguageService()->getLL('function.' . $this->MOD_SETTINGS['function']) . '</h1>';
			$this->content .= $this->getModuleContent();
			$this->content .= '</form>';
		} else {
			// If no access, only display the module's title
			$this->content = '<h1>' . $this->getLanguageService()->getLL('title.') . '</h1>';
			$this->content .='<div style="padding-top: 5px;"></div>';
		}
		
		$this->getModuleMenu();
	}
	
	/**
	 * Generates the module content
	 */
	protected function getModuleContent() {
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
					$content .= self::getMessage($this->getLanguageService()->getLL ('atLeastOne'), FlashMessage::ERROR);
				}
				
				$starttime = GeneralUtility::_POST ('starttime');
				if ($starttime) {
					$starttime = intval($this->getTimeParsed($starttime)->format('%Y%m%d'));
				}
				$endtime = GeneralUtility::_POST ('endtime');
				if ($endtime) {
					$endtime = intval($this->getTimeParsed($endtime)->format('%Y%m%d'));
				}

				if (count($pageIds) > 0 && is_int ($starttime) && is_int ($endtime)) {
					$content = $this->getLanguageService()->getLL ('indexing') . '<br/>';
					/** @var \TYPO3\CMS\Cal\Utility\RecurrenceGenerator $rgc */
					$rgc = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Utility\\RecurrenceGenerator', 0, $starttime, $endtime);
					foreach ($pageIds as $eventPage => $pluginPage) {
						$content .= sprintf($this->getLanguageService()->getLL ('droppingTable'),$eventPage);
						$content .= $rgc->cleanIndexTable ($eventPage);
						$content .= '<br />';
						$rgc->pageIDForPlugin = $pluginPage;
						$content .= 'PID ' . $eventPage . ' '. $this->getLanguageService()->getLL ('toBeIndexed');
						$content .= ' '.$rgc->countRecurringEvents ($eventPage);
						$content .= '<br />';
						
						$rgc->generateIndex ($eventPage);
						$content .= $this->getLanguageService()->getLL ('result');
						$content .= $rgc->getInfo ();
					}
				} else {
					$extConf = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);

					/** @var \TYPO3\CMS\Cal\Utility\RecurrenceGenerator $rgc */
					$rgc = GeneralUtility::makeInstance('TYPO3\\CMS\\Cal\\Utility\\RecurrenceGenerator');
					$pages = $rgc->getRecurringEventPages();
					$selectFieldIds = Array ();
					// Load necessary JavaScript
					$this->getPageRenderer()->loadJquery();
					$this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');
					
					if(!empty($pages)) {
						
						$label1 = '<label>' . $this->getLanguageService()->getLL('tableHeader1') . '</label>';
						$label2 = '<label>' . $this->getLanguageService()->getLL('tableHeader2') . '</label>';
						
						$table[] =
							'<div class="form-group col-sm-6" id="pageIds_col">'
								. $label1 . '</div>';
						$table[] =
							'<div class="form-group col-sm-6" id="pageIds_colId">'
								. $label2 . '</div>';
						
						foreach ($pages as $pageId => $pageTitle) {
							
							$table[] =
							'<div class="form-group col-sm-6" id="pageIds_col'.$pageId.'">'
									. '<div class="form-control-wrap">'
											. '<div class="input-group" id="tceforms-pageIds_col'.$pageId.'_row-wrapper">'
													. $pageTitle.' ['.$pageId.'] '
															. '</div>'
																	. '</div>'
																			. '</div>';
							$table[] =
							'<div class="form-group col-sm-6" id="pageIds_colId'.$pageId.'">'
									. '<div class="form-control-wrap">'
											. '<div class="input-group" id="pageIds_colId'.$pageId.'_row-wrapper">'
													. '<input name="pageIds" value="' . $value . '" class="form-control  t3js-clearable" data-date-type="date" data-date-offset="0" type="text" id="tceforms-pageIds_colId'.$pageId.'_row">'
															. '</div>'
																	. '</div>'
																			. '</div>';
							
						}
						
						
						$selectFields = '';
						foreach ($selectFieldIds as $selectFieldId) {
							$selectFields .= ' var o' . $selectFieldId . ' = document.getElementById("' . $selectFieldId . '");if(o' . $selectFieldId . '.options.length > 0){o' . $selectFieldId . '.options[0].selected = "selected";} else {notComplete = 1;}';
						}
						$content .= '<script type="text/javascript">function markSelections(){ var notComplete = 0;' . $selectFields . ' if(notComplete == 1){alert("' . $this->getLanguageService()->getLL ('notAllPagesAssigned') . '");return false;}return true;}</script>';
						
						$content .= $this->getLanguageService()->getLL ('selectPage');
						$content .= '<br /><br />';
						
						
						$dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? '%H:%M %m-%d-%Y' : '%H:%M %d-%m-%Y';
	
						$label = '<label>' . $this->getLanguageService()->getLL('indexStart') . '</label>';
						$value = $extConf ['recurrenceStart'];
						$table[] =
						'<div class="form-section"><div class="row"><div class="form-group col-sm-6" id="index_start_col">'
								. $label
								. '<div class="form-control-wrap">'
										. '<div class="input-group" id="tceforms-datetimefield-starttime_row-wrapper">'
												. '<input name="starttime" value="' . $value . '" class="form-control t3js-clearable" data-date-type="date" data-date-offset="0" type="text" id="tceforms-datetimefield-starttime_row">'
																		. '</div>'
																				. '</div>'
																						. '</div>';
						
						// End date/time field
						// NOTE: datetime fields need a special id naming scheme
						$value = $extConf ['recurrenceEnd'];
						$label = '<label>' . $this->getLanguageService()->getLL('indexEnd') . '</label>';
						$table[] =
						'<div class="form-group col-sm-6" id="index_end_col">'
								. $label
								. '<div class="form-control-wrap">'
										. '<div class="input-group" id="tceforms-datetimefield-endtime_row-wrapper">'
												. '<input name="endtime" value="' . $value . '" class="form-control  t3js-clearable" data-date-type="date" data-date-offset="0" type="text" id="tceforms-datetimefield-endtime_row">'
																		. '</div>'
																				. '</div>'
																						. '</div></div></div>';
						$content .= implode(LF, $table);
						//$content .= $this->getLanguageService()->getLL ('indexStart');
						//$content .= '<input name="starttime" type="text" value="' . $extConf ['recurrenceStart'] . '" size="8" maxlength="8">';
						//$content .= '<br />';
						//$content .= $this->getLanguageService()->getLL ('indexEnd');
						//$content .= '<input name="endtime" type="text" value="' . $extConf ['recurrenceEnd'] . '" size="8" maxlength="8">';
						$content .= '<br /><br /><input type="submit" value="' . $this->getLanguageService()->getLL ('startIndexing') . '" onclick="return markSelections();"/>';
					} else {
						$content .= self::getMessage($this->getLanguageService()->getLL ('nothingToDo'),FlashMessage::INFO);
					}
				}
				break;
			default :
				$content .= '<h2>'.$this->getLanguageService()->getLL ( 'notice_header' ).'</h2>';
				$content .= '<p>'.$this->getLanguageService()->getLL ( 'notice' ).'</p>';
				$content .= '<h2>'.$this->getLanguageService()->getLL ( 'capabilities_header' ).'</h2>';
				$content .= '<p>'.$this->getLanguageService()->getLL ( 'capabilities' ).'</p>';
				break;
		}
		return $content;
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