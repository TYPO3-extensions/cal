<?php
/**
 * *************************************************************
 * Copyright notice
 *
 * (c) 2004-2009 Rupert Germann (rupi@gmx.li)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * *************************************************************
 */
/**
 * class.tx_cal_cache.php
 *
 * @author Mario Matzulla <mario@matzullas.de>
 */

/**
 * [ Add description ]
 *
 * @author Mario Matzulla <mario@matzullas.de>
 * @package TYPO3
 * @subpackage
 *
 */
class tx_cal_cache {
	var $cachingEngine;
	var $tx_cal_cache;
	var $lifetime = 0;
	var $ACCESS_TIME = 0;
	
	/**
	 * [Describe function...]
	 *
	 * @return [type]
	 */
	function tx_cal_cache($cachingEngine) {
		$this->cachingEngine = $cachingEngine;
		switch ($this->cachingEngine) {
			case 'cachingFramework' :
				$this->initCachingFramework ();
				break;
			
			case 'memcached' :
				$this->initMemcached ();
				break;
			
			// default = internal
		}
	}
	function initMemcached() {
		$this->tx_cal_cache = new Memcache ();
		$this->tx_cal_cache->connect ('localhost', 11211);
	}
	function initCachingFramework() {
		try {
			$GLOBALS ['typo3CacheFactory']->create ('tx_cal_cache', 'TYPO3\\CMS\\Core\\Cache\\Frontend\\StringFrontend', $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['backend'], $GLOBALS ['TYPO3_CONF_VARS'] ['SYS'] ['caching'] ['cacheConfigurations'] ['tx_cal_cache'] ['options']);
		} catch (\TYPO3\CMS\Core\Cache\Exception\DuplicateIdentifierException $e) {
			// do nothing, a cal_cache cache already exists
		}
		
		$this->tx_cal_cache = $GLOBALS ['typo3CacheManager']->getCache ('tx_cal_cache');
	}
	function set($hash, $content, $ident, $lifetime = 0) {
		if ($lifetime == 0) {
			$lifetime = $this->lifetime;
		}
		if ($this->cachingEngine == 'cachingFramework') {
			$this->tx_cal_cache->set ($hash, $content, array (
					'ident_' . $ident 
			), $lifetime);
		} elseif ($this->cachingEngine == 'memcached') {
			$this->tx_cal_cache->set ($hash, $content, false, $lifetime);
		} else {
			$table = 'tx_cal_cache';
			$fields_values = array (
					'identifier' => $hash,
					'content' => $content,
					'crdate' => $GLOBALS ['EXEC_TIME'],
					'lifetime' => $lifetime 
			);
			$GLOBALS ['TYPO3_DB']->exec_DELETEquery ($table, 'identifier=' . $GLOBALS ['TYPO3_DB']->fullQuoteStr ($hash, $table));
			$GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $fields_values);
		}
	}
	function get($hash) {
		$cacheEntry = FALSE;
		if ($this->cachingEngine == 'cachingFramework' || $this->cachingEngine == 'memcached') {
			$cacheEntry = $this->tx_cal_cache->get ($hash);
		} else {
			$select_fields = 'content';
			$from_table = 'tx_cal_cache';
			$where_clause = 'identifier=' . $GLOBALS ['TYPO3_DB']->fullQuoteStr ($hash, $from_table);
			
			// if ($period > 0) {
			$where_clause .= ' AND (crdate+lifetime>' . $this->ACCESS_TIME . ' OR lifetime=0)';
			// }
			
			$cRec = $GLOBALS ['TYPO3_DB']->exec_SELECTgetRows ($select_fields, $from_table, $where_clause);
			
			if (is_array ($cRec [0]) && $cRec [0] ['content'] != '') {
				$cacheEntry = $cRec [0] ['content'];
			}
		}
		
		return $cacheEntry;
	}
}
if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/lib/class.tx_cal_cache.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/lib/class.tx_cal_cache.php']);
}
?>