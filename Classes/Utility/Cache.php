<?php
namespace TYPO3\CMS\Cal\Utility;
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
class Cache {
	var $cachingEngine;
	var $tx_cal_cache;
	var $lifetime = 0;
	var $ACCESS_TIME = 0;
	
	/**
	 * [Describe function...]
	 *
	 * @return [type]
	 */
	public function Cache($cachingEngine) {
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
			$result = $GLOBALS ['TYPO3_DB']->exec_INSERTquery ($table, $fields_values);
			if (FALSE === $result){
				throw new \RuntimeException('Could not write cache record to database: '.$GLOBALS ['TYPO3_DB']->sql_error(), 1431458130);
			}
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
?>