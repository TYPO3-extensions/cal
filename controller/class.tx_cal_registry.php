<?php

class tx_cal_registry {

	/**
	 * Usage:
	 *   $myfoo = & Registry('MySpace', 'Foo');
	 *   $myfoo = 'something';
	 *
	 *   $mybar = & Registry('MySpace', 'Bar');
	 *   $mybar = new Something();
	 *
	 * @param  string $namespace  A namespace to prevent clashes
	 * @param  string $var        The variable to retrieve.
	 * @return mixed  A reference to the variable. If not set it will be null.
	 */
	function &Registry($namespace, $var) {
		static $instances = array();
		// remove to get case-insensitive namespace
		$namespace = strtolower($namespace);
		$var = strtolower($var);
		return $instances[$namespace][$var];
	}
	
	function setInstance(&$object,$namespace,$var){
		$myObject = &tx_publication_registry::Registry($namespace,$var);
		$myObject = $object;
		$object = $myObject;
	}
	
}
?>