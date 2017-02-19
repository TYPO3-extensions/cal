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

use TYPO3\CMS\Cal\Utility\Functions;

/**
 * A concrete model for the calendar.
 *
 * @author Mario Matzulla <mario(at)matzullas.de>
 */
class TodoRecModel extends \TYPO3\CMS\Cal\Model\EventRecModel {
	
	public function __construct($todo, $start, $end) {
		parent::__construct($todo, $start, $end);
		$this->setEventType (\TYPO3\CMS\Cal\Model\Model::EVENT_TYPE_TODO);
	}
	
	public function renderEvent() {
		return $this->fillTemplate ('###TEMPLATE_TODO###');
	}
	
	public function renderEventFor($viewType) {
		if ($this->parentEvent->conf ['view.'] ['freeAndBusy.'] ['enable'] == 1) {
			$viewType .= '_FNB';
		}
		// Need to check if _ALLDAY is already in viewType since handling changed from classic to new standard rendering
		if (($this->isAllday ()) && (strpos ($viewType, '_ALLDAY') < 1)) {
			$viewType .= '_ALLDAY';
		}
		return $this->fillTemplate ('###TEMPLATE_TODO_' . strtoupper ($viewType) . '###');
	}
	
	public function renderEventPreview() {
		$this->parentEvent->isPreview = true;
		return $this->fillTemplate ('###TEMPLATE_TODO_PREVIEW###');
	}
	
	public function renderTomorrowsEvent() {
		$this->parentEvent->isTomorrow = true;
		return $this->fillTemplate ('###TEMPLATE_TODO_TOMORROW###');
	}
	
	public function fillTemplate($subpartMarker) {
		$cObj = &\TYPO3\CMS\Cal\Utility\Registry::Registry ('basic', 'cobj');
		$confArr = unserialize ($GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] ['cal']);
		$modelTemplate = $confArr ['todoSubtype'] == 'event' ? 'todoInlineModelTemplate' : 'todoSeparateModelTemplate';
		
		$page = Functions::getContent ($this->parentEvent->conf ['view.'] ['todo.'] [$modelTemplate]);
		if ($page == '') {
			return '<h3>calendar: no todo model template file found:</h3>' . $this->parentEvent->conf ['view.'] ['todo.'] [$modelTemplate];
		}
		$page = $cObj->getSubpart ($page, $subpartMarker);
		if (! $page) {
			return 'could not find the >' . str_replace ('###', '', $subpartMarker) . '< subpart-marker in ' . $this->parentEvent->conf ['view.'] ['todo.'] ['todoModelTemplate'];
		}
		$rems = Array ();
		$sims = Array ();
		$wrapped = Array ();
		$this->getMarker ($page, $sims, $rems, $wrapped, $this->parentEvent->conf ['view']);
		return $this->parentEvent->finish (\TYPO3\CMS\Cal\Utility\Functions::substituteMarkerArrayNotCached ($page, $sims, $rems, $wrapped));
	}
	
	public function getStatusMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->parentEvent->getStatusMarker ($template, $sims, $rems, $wrapped, $view);
	}
	
	public function getPriorityMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->parentEvent->getPriorityMarker ($template, $sims, $rems, $wrapped, $view);
	}
	
	public function getCompletedMarker(& $template, & $sims, & $rems, & $wrapped, $view) {
		$this->parentEvent->getCompletedMarker ($template, $sims, $rems, $wrapped, $view);
	}
}

?>