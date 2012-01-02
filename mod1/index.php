<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Mario Matzulla (mario(at)matzullas.de)
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
/** 
 * Module 'cal_recurrence_generator' for the 'cal' extension.
 *
 * @author	Mario Matzulla <mario(at)matzullas.de>
 */


	// DEFAULT initialization of a module [BEGIN]
#unset($MCONF);

// include my own class file for database copying
include_once('class.tx_cal_recurrence_generator.php');
include_once(t3lib_extMgm::extPath('cal').'controller/class.tx_cal_functions.php');
#require ('conf.php');
#require ($BACK_PATH.'init.php');
#require ($BACK_PATH.'template.php');

$GLOBALS['LANG']->includeLLFile('EXT:cal/mod1/locallang.xml');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
// include this to enable accessing the database via tce
require_once (PATH_t3lib.'class.t3lib_tcemain.php');
require_once (PATH_t3lib.'class.t3lib_loaddbgroup.php');
require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_cal_recurrence_generator_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * 
	 */
	function init()	{
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[]=PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	function menuConfig()	{
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $GLOBALS['LANG']->getLL('function1'),
				'2' => $GLOBALS['LANG']->getLL('function2'),
			)
		);
		parent::menuConfig();
	}

		// If you chose 'web' as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
	
				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'],-50);

			$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			
			// Render content:
			$this->moduleContent();

			
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
		
			$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		switch(intval($this->MOD_SETTINGS['function']))	{
			case 2:
				$postVarArray = t3lib_div::_POST();
				$pageIds = Array();
				foreach($postVarArray as $name => $value){
					if(strpos($name, 'pageId') === 0){
						$valuePageArray = explode('_',$value);
						preg_match('/pageId([0-9]*)_list/',$name,$matches);
						$pageIds[intval($matches[1])] = intval($valuePageArray[1]);
					}
				}
				
				$starttime = t3lib_div::_POST('starttime');
				if($starttime){
					$starttime = intval($starttime);
				}
				$endtime = t3lib_div::_POST('endtime');
				if($endtime){
					$endtime = intval($endtime);
				}
				if(!empty($pageIds) && is_int($starttime) && is_int($endtime)){
					$content=$GLOBALS['LANG']->getLL('indexing').'<br/>';
					$rgc = &tx_cal_functions::makeInstance('tx_cal_recurrence_generator',0,$starttime,$endtime);
					$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('droppingTable'),$rgc->cleanIndexTable(),0,1);
					foreach($pageIds as $eventPage => $pluginPage){
						$rgc->pageIDForPlugin = $pluginPage;
						$this->content.=$this->doc->section('PID '.$eventPage.$GLOBALS['LANG']->getLL('toBeIndexed'),$rgc->countRecurringEvents($eventPage),0,1);
						$rgc->generateIndex($eventPage);
						$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('result'),$rgc->getInfo(),0,1);
					}
				} else {
					$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cal']);
					$this->content.='<script type="text/javascript">'.$this->getJScode().'</script>';
					if (t3lib_div::int_from_ver(TYPO3_version) > 4004999){
						$this->content.='<script type="text/javascript" src="jsfunc.tbe_editor.js"></script>';
					}
					
					$selectFieldIds = Array();
					$content.='<table><tbody>';
					$content.='<tr><td>';
					$content.='"'.$GLOBALS['LANG']->getLL('tableHeader2').' :';
					$content.='</td><td>';
					$content.='<select id="pageId'.$pid.'" class="formField3 tceforms-multiselect" style="width: 150px;" name="pageId'.$pid.'_list" multiple="multiple" size="1"></select>';
					$content.='<a href="#" onclick="setFormValueOpenBrowser(\'db\',\'pageId'.$pid.'|||pages\'); return false;"><img src="sysext/t3skin/icons/gfx/insert3.gif" alt="'.$GLOBALS['LANG']->getLL('browse').'" title="'.$GLOBALS['LANG']->getLL('browse').'" border="0" height="15" width="15"></a>';
					$content.='</td></tr>';
					$selectFieldIds[] = 'pageId'.$pid; 
					$content.='<tbody></table>';
					//$content.='<input name="pageId_list" id="pageId" type="text" value="" size="5" maxlength="5"><br />';
					$scontent.='<input type="submit" value="'.$GLOBALS['LANG']->getLL('submit').'" onclick="return markSelections();"/>';
					
					$selectFields = '';
					foreach($selectFieldIds as $selectFieldId){
						$selectFields .= ' var o'.$selectFieldId.' = document.getElementById("'.$selectFieldId.'");if(o'.$selectFieldId.'.options.length > 0){o'.$selectFieldId.'.options[0].selected = "selected";} else {notComplete = 1;}';
					}
					$content .= '<script type="text/javascript">function markSelections(){ var notComplete = 0;'.$selectFields.' if(notComplete == 1){alert("'.$GLOBALS['LANG']->getLL('notAllPagesAssigned').'");return false;}return true;}</script>';
					
					$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('selectPage'),$content,0,1);
					$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('indexStart'),'<input name="starttime" type="text" value="'.$extConf['recurrenceStart'].'" size="8" maxlength="8">',0,1);
					$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('indexEnd'),'<input name="endtime" type="text" value="'.$extConf['recurrenceEnd'].'" size="8" maxlength="8">',0,1);
					$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('startIndexing'),$scontent,0,1);
				}
			break;
			default:
				$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('notice_header'),$GLOBALS['LANG']->getLL('notice'),0,1);
				$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('capabilities_header'),$GLOBALS['LANG']->getLL('capabilities'),0,1);
			break;
		} 
	}
	
	private function getJScode(){
		$t3lib_TCEforms = t3lib_div::makeInstance("t3lib_TCEforms");
		$t3lib_TCEforms->backPath = $GLOBALS["BACK_PATH"];
		return $t3lib_TCEforms->dbFileCon(); 
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cal/mod1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_cal_recurrence_generator_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();
?>