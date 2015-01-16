<?php
/**
 * Class representing iCalendar files.
 *
 * $Horde: framework/iCalendar/iCalendar.php,v 1.57.4.45 2007/03/14 15:58:24 jan Exp $
 *
 * Copyright 2003-2007 Mike Cochrane <mike@graftonhall.co.nz>
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author Mike Cochrane <mike@graftonhall.co.nz>
 * @since Horde 3.0
 * @package Horde_iCalendar
 */
define ('TX_MODEL_CALICALENDAR_SCALE_GREGORIAN', 0);

define ('TX_MODEL_CALICALENDAR_RECUR_NONE', 0);
define ('TX_MODEL_CALICALENDAR_RECUR_DAILY', 1);
define ('TX_MODEL_CALICALENDAR_RECUR_WEEKLY', 2);
define ('TX_MODEL_CALICALENDAR_RECUR_DAY_OF_MONTH', 3);
define ('TX_MODEL_CALICALENDAR_RECUR_WEEK_OF_MONTH', 4);
define ('TX_MODEL_CALICALENDAR_RECUR_YEARLY', 5);

define ('TX_MODEL_CALICALENDAR_STATUS_NONE', 0);
define ('TX_MODEL_CALICALENDAR_STATUS_TENTATIVE', 1);
define ('TX_MODEL_CALICALENDAR_STATUS_CONFIRMED', 2);
define ('TX_MODEL_CALICALENDAR_STATUS_CANCELLED', 3);
define ('TX_MODEL_CALICALENDAR_STATUS_FREE', 4);

define ('TX_MODEL_CALICALENDAR_RESPONSE_NONE', 1);
define ('TX_MODEL_CALICALENDAR_RESPONSE_ACCEPTED', 2);
define ('TX_MODEL_CALICALENDAR_RESPONSE_DECLINED', 3);
define ('TX_MODEL_CALICALENDAR_RESPONSE_TENTATIVE', 4);

define ('TX_MODEL_CALICALENDAR_PART_REQUIRED', 1);
define ('TX_MODEL_CALICALENDAR_PART_OPTIONAL', 2);
define ('TX_MODEL_CALICALENDAR_PART_NONE', 3);
define ('TX_MODEL_CALICALENDAR_PART_IGNORE', 4);

define ('TX_MODEL_CALICALENDAR_ITIP_REQUEST', 1);
define ('TX_MODEL_CALICALENDAR_ITIP_CANCEL', 2);

define ('TX_MODEL_CALICALENDAR_ERROR_FB_NOT_FOUND', 1);
class tx_model_iCalendar {
	var $_calscale = '';
	
	/**
	 * The parent (containing) iCalendar object.
	 *
	 * @var Horde_iCalendar
	 */
	var $_container = false;
	
	/**
	 * The name/value pairs of attributes for this object (UID,
	 * DTSTART, etc.).
	 * Which are present depends on the object and on
	 * what kind of component it is.
	 *
	 * @var array
	 */
	var $_attributes = array ();
	
	/**
	 * Any children (contained) iCalendar components of this object.
	 *
	 * @var array
	 */
	var $_components = array ();
	
	/**
	 * According to RFC 2425, we should always use CRLF-terminated lines.
	 *
	 * @var string
	 */
	var $_newline = "\r\n";
	
	/**
	 * iCalendar format version (different behavior for 1.0 and 2.0
	 * especially with recurring events).
	 *
	 * @var string
	 */
	var $_version;
	var $defaultCharSet = 'utf-8';
	function tx_model_iCalendar($version = '2.0') {
		$this->_version = $version;
		$this->setAttribute ('VERSION', $version);
	}
	
	/**
	 * Return a reference to a new component.
	 *
	 * @param string $type
	 *        	The type of component to return
	 * @param Horde_iCalendar $container
	 *        	A container that this component
	 *        	will be associated with.
	 *        	
	 * @return object Reference to a Horde_iCalendar_* object as specified.
	 */
	function newComponent($type, &$container) {
		$type = strtolower ($type);
		$class = 'tx_iCalendar_' . $type;
		require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('cal') . 'model/iCalendar/class.' . $class . '.php';
		if (class_exists ($class)) {
			$component = new $class ();
			if ($container !== false) {
				$component->_container = &$container;
			}
		} else {
			// Should return an dummy x-unknown type class here.
			$component = false;
		}
		return $component;
	}
	
	/**
	 * Set the value of an attribute.
	 *
	 * @param string $name
	 *        	The name of the attribute.
	 * @param string $value
	 *        	The value of the attribute.
	 * @param array $params
	 *        	Array containing any addition parameters for
	 *        	this attribute.
	 * @param boolean $append
	 *        	True to append the attribute, False to replace
	 *        	the first matching attribute found.
	 * @param array $values
	 *        	Array representation of $value. For
	 *        	comma/semicolon seperated lists of values. If
	 *        	not set use $value as single array element.
	 */
	function setAttribute($name, $value, $params = array(), $append = true, $values = false) {
		// Make sure we update the internal format version if
		// setAttribute('VERSION', ...) is called.
		if ($name == 'VERSION') {
			$this->_version = $value;
			if ($this->_container !== false) {
				$this->_container->_version = $value;
			}
		}
		
		if (! $values) {
			$values = array (
					$value 
			);
		}
		$found = false;
		if (! $append) {
			$keys = array_keys ($this->_attributes);
			foreach ($keys as $key) {
				if ($this->_attributes [$key] ['name'] == String::upper ($name)) {
					$this->_attributes [$key] ['params'] = $params;
					$this->_attributes [$key] ['value'] = $value;
					$this->_attributes [$key] ['values'] = $values;
					$found = true;
					break;
				}
			}
		}
		
		if ($append || ! $found) {
			$this->_attributes [] = array (
					'name' => strtoupper ($name),
					'params' => $params,
					'value' => $value,
					'values' => $values 
			);
		}
	}
	
	/**
	 * Sets parameter(s) for an (already existing) attribute.
	 * The
	 * parameter set is merged into the existing set.
	 *
	 * @param string $name
	 *        	The name of the attribute.
	 * @param array $params
	 *        	Array containing any additional parameters for
	 *        	this attribute.
	 * @return boolean True on success, false if no attribute $name exists.
	 */
	function setParameter($name, $params) {
		$keys = array_keys ($this->_attributes);
		foreach ($keys as $key) {
			if ($this->_attributes [$key] ['name'] == $name) {
				$this->_attributes [$key] ['params'] = array_merge ($this->_attributes [$key] ['params'], $params);
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Get the value of an attribute.
	 *
	 * @param string $name
	 *        	The name of the attribute.
	 * @param boolean $params
	 *        	Return the parameters for this attribute instead
	 *        	of its value.
	 *        	
	 * @return mixed (object) PEAR_Error if the attribute does not exist.
	 *         (string) The value of the attribute.
	 *         (array) The parameters for the attribute or
	 *         multiple values for an attribute.
	 */
	function getAttribute($name, $params = false) {
		$result = array ();
		foreach ($this->_attributes as $attribute) {
			if ($attribute ['name'] == $name) {
				if ($params) {
					$result [] = $attribute ['params'];
				} else {
					$result [] = $attribute ['value'];
				}
			}
		}
		if (! count ($result)) {
			// require_once 'PEAR.php';
			return null;
		}
		if (count ($result) == 1 && ! $params) {
			return $result [0];
		} else {
			return $result;
		}
	}
	
	/**
	 * Gets the values of an attribute as an array.
	 * Multiple values
	 * are possible due to:
	 *
	 * a) multiplce occurences of 'name'
	 * b) (unsecapd) comma seperated lists.
	 *
	 * So for a vcard like 'KEY:a,b\nKEY:c' getAttributesValues('KEY')
	 * will return array('a','b','c').
	 *
	 * @param string $name
	 *        	The name of the attribute.
	 * @return mixed (object) PEAR_Error if the attribute does not exist.
	 *         (array) Multiple values for an attribute.
	 */
	function getAttributeValues($name) {
		$result = array ();
		foreach ($this->_attributes as $attribute) {
			if ($attribute ['name'] == $name) {
				if ($params) {
					$result [] = $attribute ['params'];
				} else {
					$result [] = $attribute ['value'];
				}
			}
		}
		if (! count ($result)) {
			/* @todo 	What should we do when we have an error? */
			// return PEAR::raiseError('Attribute ' . $name . ' Not Found');
		}
		if (count ($result) == 1 && ! $params) {
			return $result [0];
		} else {
			return $result;
		}
	}
	
	/**
	 * Gets the params of an parameter as an array.
	 * Multiple values
	 * are possible due to:
	 *
	 * a) multiplce occurences of 'name'
	 * b) (unsecapd) comma seperated lists.
	 *
	 * So for a vcard like 'KEY:a,b\nKEY:c' getAttributesValues('KEY')
	 * will return array('a','b','c').
	 *
	 * @param string $name
	 *        	The name of the parameter.
	 * @return mixed (object) PEAR_Error if the parameter does not exist.
	 *         (array) Multiple values for an attribute.
	 */
	function getAttributeParameters($name) {
		$result = array ();
		foreach ($this->_attributes as $attribute) {
			if ($attribute ['name'] == $name) {
				$result = array_merge ($attribute ['params'], $result);
			}
		}
		if (! count ($result)) {
			/* @todo 	What should we do when we have an error? */
			// return PEAR::raiseError('Parameter ' . $name . ' Not Found');
		}
		return $result;
	}
	
	/**
	 * Returns the value of an attribute, or a specified default value
	 * if the attribute does not exist.
	 *
	 * @param string $name
	 *        	The name of the attribute.
	 * @param mixed $default
	 *        	What to return if the attribute specified by
	 *        	$name does not exist.
	 *        	
	 * @return mixed (string) The value of $name.
	 *         (mixed) $default if $name does not exist.
	 */
	function getAttributeDefault($name, $default = '') {
		$value = $this->getAttribute ($name);
		return is_a ($value, 'PEAR_Error') ? $default : $value;
	}
	
	/**
	 * Remove all occurences of an attribute.
	 *
	 * @param string $name
	 *        	The name of the attribute.
	 */
	function removeAttribute($name) {
		$keys = array_keys ($this->_attributes);
		foreach ($keys as $key) {
			if ($this->_attributes [$key] ['name'] == $name) {
				unset ($this->_attributes [$key]);
			}
		}
	}
	
	/**
	 * Get attributes for all tags or for a given tag.
	 *
	 * @param string $tag
	 *        	Return attributes for this tag, or all attributes if
	 *        	not given.
	 *        	
	 * @return array An array containing all the attributes and their types.
	 */
	function getAllAttributes($tag = false) {
		if ($tag === false) {
			return $this->_attributes;
		}
		$result = array ();
		foreach ($this->_attributes as $attribute) {
			if ($attribute ['name'] == $tag) {
				$result [] = $attribute;
			}
		}
		return $result;
	}
	
	/**
	 * Add a vCalendar component (eg vEvent, vTimezone, etc.).
	 *
	 * @param Horde_iCalendar $component
	 *        	Component (subclass) to add.
	 */
	function addComponent($component) {
		if (is_a ($component, 'tx_model_iCalendar')) {
			$component->_container = &$this;
			$this->_components [] = &$component;
		}
	}
	
	/**
	 * Retrieve all the components.
	 *
	 * @return array Array of Horde_iCalendar objects.
	 */
	function getComponents() {
		return $this->_components;
	}
	function getType() {
		return 'vcalendar';
	}
	
	/**
	 * Return the classes (entry types) we have.
	 *
	 * @return array Hash with class names Horde_iCalendar_xxx as keys
	 *         and number of components of this class as value.
	 */
	function getComponentClasses() {
		$r = array ();
		foreach ($this->_components as $c) {
			$cn = strtolower (get_class ($c));
			if (empty ($r [$cn])) {
				$r [$cn] = 1;
			} else {
				$r [$cn] ++;
			}
		}
		
		return $r;
	}
	
	/**
	 * Number of components in this container.
	 *
	 * @return integer Number of components in this container.
	 */
	function getComponentCount() {
		return count ($this->_components);
	}
	
	/**
	 * Retrieve a specific component.
	 *
	 * @param integer $idx
	 *        	The index of the object to retrieve.
	 *        	
	 * @return mixed (boolean) False if the index does not exist.
	 *         (Horde_iCalendar_*) The requested component.
	 */
	function getComponent($idx) {
		if (isset ($this->_components [$idx])) {
			return $this->_components [$idx];
		} else {
			return false;
		}
	}
	
	/**
	 * Locates the first child component of the specified class, and
	 * returns a reference to this component.
	 *
	 * @param string $type
	 *        	The type of component to find.
	 *        	
	 * @return mixed (boolean) False if no subcomponent of the specified
	 *         class exists.
	 *         (Horde_iCalendar_*) A reference to the requested component.
	 */
	function &findComponent($childclass) {
		$childclass = 'tx_model_iCalendar_' . strtolower ($childclass);
		$keys = array_keys ($this->_components);
		foreach ($keys as $key) {
			if (is_a ($this->_components [$key], $childclass)) {
				return $this->_components [$key];
			}
		}
		
		return false;
	}
	
	/**
	 * Locates the first matching child component of the specified class, and
	 * returns a reference to it.
	 *
	 * @param string $childclass
	 *        	The type of component to find.
	 * @param string $attribute
	 *        	This attribute must be set in the component
	 *        	for it to match.
	 * @param string $value
	 *        	Optional value that $attribute must match.
	 *        	
	 * @return boolean tx_model_iCalendar_* if no matching subcomponent of
	 *         the specified class exists, or a
	 *         reference to the requested component.
	 */
	function &findComponentByAttribute($childclass, $attribute, $value = null) {
		$childclassB = 'tx_iCalendar_' . strtolower ($childclass);
		$childclass = 'tx_model_iCalendar_' . strtolower ($childclass);
		$keys = array_keys ($this->_components);
		foreach ($keys as $key) {
			if (is_a ($this->_components [$key], $childclass) || is_a ($this->_components [$key], $childclassB)) {
				$attr = $this->_components [$key]->getAttribute ($attribute);
				if (is_a ($attr, 'PEAR_Error')) {
					continue;
				}
				if ($value !== null && $value != $attr) {
					continue;
				}
				return $this->_components [$key];
			}
		}
		
		$component = false;
		return $component;
	}
	
	/**
	 * Clears the iCalendar object (resets the components and attributes
	 * arrays).
	 */
	function clear() {
		$this->_components = array ();
		$this->_attributes = array ();
	}
	
	/**
	 * Checks if entry is vcalendar 1.0, vcard 2.1 or vnote 1.1.
	 *
	 * These 'old' formats are defined by www.imc.org. The 'new' (non-old)
	 * formats icalendar 2.0 and vcard 3.0 are defined in rfc2426 and rfc2445
	 * respectively.
	 *
	 * @since Horde 3.1.2
	 */
	function isOldFormat() {
		if ($this->_container !== false) {
			return $this->_container->isOldFormat ();
		}
		if ($this->getType () == 'vcard') {
			return $this->_version < 3 ? true : false;
		}
		if ($this->getType () == 'vNote') {
			return $this->_version < 2 ? true : false;
		}
		if ($this->_version >= 2) {
			return false;
		}
		return true;
	}
	
	/**
	 * Export as vCalendar format.
	 */
	function exportvCalendar() {
		// Default values.
		$requiredAttributes ['PRODID'] = '-//The TYPO3 Project//cal extension//EN';
		$requiredAttributes ['METHOD'] = 'PUBLISH';
		
		foreach ($requiredAttributes as $name => $default_value) {
			if (is_a ($this->getattribute ($name), 'PEAR_Error')) {
				$this->setAttribute ($name, $default_value);
			}
		}
		
		return $this->_exportvData ('VCALENDAR');
	}
	
	/**
	 * Export this entry as a hash array with tag names as keys.
	 *
	 * @param boolean $paramsInKeys
	 *        	If false, the operation can be quite lossy as the
	 *        	parameters are ignored when building the array keys.
	 *        	So if you export a vcard with
	 *        	LABEL;TYPE=WORK:foo
	 *        	LABEL;TYPE=HOME:bar
	 *        	the resulting hash contains only one label field!
	 *        	If set to true, array keys look like 'LABEL;TYPE=WORK'
	 * @return array A hash array with tag names as keys.
	 */
	function toHash($paramsInKeys = false) {
		$hash = array ();
		foreach ($this->_attributes as $a) {
			$k = $a ['name'];
			if ($paramsInKeys && is_array ($a ['params'])) {
				foreach ($a ['params'] as $p => $v) {
					$k .= ';$p=$v';
				}
			}
			$hash [$k] = $a ['value'];
		}
		
		return $hash;
	}
	
	/**
	 * Parses a string containing vCalendar data.
	 *
	 * @param string $text
	 *        	The data to parse.
	 * @param string $base
	 *        	The type of the base object.
	 * @param string $charset
	 *        	The encoding charset for $text. Defaults to
	 *        	utf-8.
	 * @param boolean $clear
	 *        	If true clears the iCal object before parsing.
	 *        	
	 * @return boolean True on successful import, false otherwise.
	 */
	function parsevCalendar($text, $base = 'VCALENDAR', $charset = 'utf8', $clear = true) {
		if ($clear) {
			$this->clear ();
		}
		$matches = array ();
		if (preg_match ('/(BEGIN:' . $base . '\r?\n?)([\W\w]*)(END:' . $base . '\r?\n?)/i', $text, $matches)) {
			$vCal = $matches [2];
		} else {
			// Text isn't enclosed in BEGIN:VCALENDAR
			// .. END:VCALENDAR. We'll try to parse it anyway.
			$vCal = $text;
		}
		
		// All subcomponents.
		$matches = null;
		if (preg_match_all ('/BEGIN:([\W\w]*)(\r\n|\r|\n)([\W\w]*)END:\1(\r\n|\r|\n)/Ui', $vCal, $matches)) {
			// vTimezone components are processed first. They are
			// needed to process vEvents that may use a TZID.
			foreach ($matches [0] as $key => $data) {
				$type = trim ($matches [1] [$key]);
				if ($type != 'VTIMEZONE') {
					continue;
				}
				$component = &tx_model_iCalendar::newComponent ($type, $this);
				if ($component === false) {
					// return PEAR::raiseError("Unable to create object for type $type");
				}
				$component->parsevCalendar ($data);
				
				$this->addComponent ($component);
				
				// Remove from the vCalendar data.
				$vCal = str_replace ($data, '', $vCal);
			}
			
			// Now process the non-vTimezone components.
			foreach ($matches [0] as $key => $data) {
				$type = trim ($matches [1] [$key]);
				if ($type == 'VTIMEZONE') {
					continue;
				}
				$component = &tx_model_iCalendar::newComponent ($type, $this);
				if ($component === false) {
					// return PEAR::raiseError("Unable to create object for type $type");
				}
				$component->parsevCalendar ($data);
				
				$this->addComponent ($component);
				
				// Remove from the vCalendar data.
				$vCal = str_replace ($data, '', $vCal);
			}
		}
		
		// Unfold any folded lines.
		$vCal = preg_replace ('/[\r\n]+[ \t]/', '', $vCal);
		
		// Unfold 'quoted printable' folded lines like:
		// BODY;ENCODING=QUOTED-PRINTABLE:=
		// another=20line=
		// last=20line
		while (preg_match_all ('/^([^:]+;\s*ENCODING=QUOTED-PRINTABLE(.*=\r?\n)+(.*[^=])?\r?\n)/mU', $vCal, $matches)) {
			foreach ($matches [1] as $s) {
				$r = preg_replace ('/=\r?\n/', '', $s);
				$vCal = str_replace ($s, $r, $vCal);
			}
		}
		
		if (is_object ($GLOBALS ['LANG'])) {
			$csConvObj = &$GLOBALS ['LANG']->csConvObj;
		} elseif (is_object ($GLOBALS ['TSFE'])) {
			$csConvObj = &$GLOBALS ['TSFE']->csConvObj;
		} else {
			require_once (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath ('lang') . 'lang.php');
			$LANG = new language();
			if (TYPO3_MODE == 'BE') {
				$LANG->init ($BE_USER->uc ['lang']);
				$csConvObj = &$LANG->csConvObj;
			} else {
				$LANG->init ($GLOBALS ['TSFE']->config ['config'] ['language']);
				$csConvObj = &$GLOBALS ['TSFE']->csConvObj;
			}
		}
		$renderCharset = $csConvObj->parse_charset ($GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['forceCharset'] ? $GLOBALS ['TYPO3_CONF_VARS'] ['BE'] ['forceCharset'] : $this->defaultCharSet);
		
		// Parse the remaining attributes.
		if (preg_match_all ('/(.*):([^\r\n]*)[\r\n]+/', $vCal, $matches)) {
			foreach ($matches [0] as $attribute) {
				$parts = array ();
				preg_match ('/([^;^:]*)((;[^:]*)?):([^\r\n]*)[\r\n]*/', $attribute, $parts);
				$tag = $parts [1];
				$value = $parts [4];
				$params = array ();
				// Parse parameters.
				
				if (! empty ($parts [2])) {
					
					$param_parts = array ();
					preg_match_all ('/;(([^;=]*)(=([^;]*))?)/', $parts [2], $param_parts);
					foreach ($param_parts [2] as $key => $paramName) {
						$paramValue = $param_parts [4] [$key];
						$params [strtoupper ($paramName)] = $paramValue;
					}
				}
				
				// Charset and encoding handling.
				if ((isset ($params ['ENCODING']) && strtoupper ($params ['ENCODING']) == 'QUOTED-PRINTABLE') || isset ($params ['QUOTED-PRINTABLE'])) {
					
					$value = quoted_printable_decode ($value);
					
					$value = $csConvObj->conv ($value, $csConvObj->parse_charset [$params ['CHARSET']] ? $csConvObj->parse_charset [$params ['CHARSET']] : 'utf-8', $renderCharset, 1);
				} elseif (isset ($params ['CHARSET'])) {
					$value = $csConvObj->conv ($value, $csConvObj->parse_charset [$params ['CHARSET']], $renderCharset, 1);
				} else {
					// As per RFC 2279, assume UTF8 if we don't have an
					// explicit charset parameter.
					$value = $csConvObj->conv ($value, 'utf-8', $renderCharset, 1);
				}
				
				// Get timezone info for date fields from $params.
				$tzid = isset ($params ['TZID']) ? trim ($params ['TZID'], '\"') : false;
				
				switch ($tag) {
					// Date fields.
					case 'COMPLETED' :
					case 'CREATED' :
					case 'LAST-MODIFIED' :
						$this->setAttribute ($tag, $this->_parseDateTime ($value, $tzid), $params);
						break;
					
					case 'BDAY' :
						$this->setAttribute ($tag, $this->_parseDate ($value), $params);
						break;
					
					case 'DTEND' :
					case 'DTSTART' :
					case 'DTSTAMP' :
					case 'DUE' :
					case 'AALARM' :
					case 'RECURRENCE-ID' :
						if (isset ($params ['VALUE']) && $params ['VALUE'] == 'DATE') {
							$this->setAttribute ($tag, $this->_parseDate ($value), $params);
						} else {
							$this->setAttribute ($tag, $this->_parseDateTime ($value, $tzid), $params);
						}
						break;
					
					case 'TRIGGER' :
						if (isset ($params ['VALUE'])) {
							if ($params ['VALUE'] == 'DATE-TIME') {
								$this->setAttribute ($tag, $this->_parseDateTime ($value, $tzid), $params);
							} else {
								$this->setAttribute ($tag, $this->_parseDuration ($value), $params);
							}
						} else {
							$this->setAttribute ($tag, $this->_parseDuration ($value), $params);
						}
						break;
					
					// Comma seperated dates.
					case 'EXDATE' :
					case 'RDATE' :
						$this->setAttribute ($tag, $value, $params);
						break;
					
					// Duration fields.
					case 'DURATION' :
						$this->setAttribute ($tag, $this->_parseDuration ($value), $params);
						break;
					
					// Period of time fields.
					case 'FREEBUSY' :
						$periods = array ();
						preg_match_all ('/,([^,]*)/', ',' . $value, $values);
						foreach ($values [1] as $value) {
							$periods [] = $this->_parsePeriod ($value);
						}
						
						$this->setAttribute ($tag, isset ($periods [0]) ? $periods [0] : null, $params, true, $periods);
						break;
					
					// UTC offset fields.
					case 'TZOFFSETFROM' :
					case 'TZOFFSETTO' :
						$this->setAttribute ($tag, $this->_parseUtcOffset ($value), $params);
						break;
					
					// Integer fields.
					case 'PERCENT-COMPLETE' :
					case 'PRIORITY' :
					case 'REPEAT' :
					case 'SEQUENCE' :
						$this->setAttribute ($tag, intval ($value), $params);
						break;
					
					// Geo fields.
					case 'GEO' :
						$floats = explode (';', $value);
						$value ['latitude'] = floatval ($floats [0]);
						$value ['longitude'] = floatval ($floats [1]);
						$this->setAttribute ($tag, $value, $params);
						break;
					
					// Recursion fields.
					case 'EXRULE' :
					case 'RRULE' :
						$this->setAttribute ($tag, trim ($value), $params);
						break;
					
					// ADR, ORG and N are lists seperated by unescaped semicolons
					// with a specific number of slots.
					case 'ADR' :
					case 'N' :
					case 'ORG' :
						$value = trim ($value);
						// As of rfc 2426 2.4.2 semicolon, comma, and colon must
						// be escaped (comma is unescaped after splitting below).
						$value = str_replace (array (
								'\\n',
								'\\N',
								'\\;',
								'\\:' 
						), array (
								$this->_newline,
								$this->_newline,
								';',
								':' 
						), $value);
						
						// Split by unescaped semicolons:
						$values = preg_split ('/(?<!\\\\);/', $value);
						$value = str_replace ('\\;', ';', $value);
						$values = str_replace ('\\;', ';', $values);
						$this->setAttribute ($tag, trim ($value), $params, true, $values);
						break;
					
					// String fields.
					default :
						if ($this->isOldFormat ()) {
							// vCalendar 1.0 and vcCard 2.1 only escape
							// semicolons and use unescaped semicolons to
							// create lists.
							$value = trim ($value);
							// Split by unescaped semicolons:
							$values = preg_split ('/(?<!\\\\);/', $value);
							$value = str_replace ('\\;', ';', $value);
							$values = str_replace ('\\;', ';', $values);
							$this->setAttribute ($tag, trim ($value), $params, true, $values);
						} else {
							$value = trim ($value);
							// As of rfc 2426 2.4.2 semicolon, comma, and
							// colon must be escaped (comma is unescaped after
							// splitting below).
							$value = str_replace (array (
									'\\n',
									'\\N',
									'\\;',
									'\\:',
									'\\\\' 
							), array (
									$this->_newline,
									$this->_newline,
									';',
									':',
									'\\' 
							), $value);
							
							// Split by unescaped commas:
							$values = preg_split ('/(?<!\\\\),/', $value);
							$value = str_replace ('\\,', ',', $value);
							$values = str_replace ('\\,', ',', $values);
							
							$this->setAttribute ($tag, trim ($value), $params, true, $values);
						}
						break;
				}
			}
		}
		return true;
	}
	
	/**
	 * Export this component in vCal format.
	 *
	 * @param string $base
	 *        	The type of the base object.
	 *        	
	 * @return string vCal format data.
	 */
	function _exportvData($base = 'VCALENDAR') {
		$result = 'BEGIN:' . strtoupper ($base) . $this->_newline;
		
		// VERSION is not allowed for entries enclosed in VCALENDAR/ICALENDAR,
		// as it is part of the enclosing VCALENDAR/ICALENDAR. See rfc2445
		if ($base !== 'VEVENT' && $base !== 'VTODO' && $base !== 'VALARM' && $base !== 'VJOURNAL' && $base !== 'VFREEBUSY') {
			// Ensure that version is the first attribute.
			$result .= 'VERSION:' . $this->_version . $this->_newline;
		}
		foreach ($this->_attributes as $attribute) {
			$name = $attribute ['name'];
			if ($name == 'VERSION') {
				// Already done.
				continue;
			}
			
			$params_str = '';
			$params = $attribute ['params'];
			if ($params) {
				foreach ($params as $param_name => $param_value) {
					/* Skip CHARSET for iCalendar 2.0 data, not allowed. */
					if ($param_name == 'CHARSET' && ! $this->isOldFormat ()) {
						continue;
					}
					/* Skip VALUE=DATE for vCalendar 1.0 data, not allowed. */
					if ($this->isOldFormat () && $param_name == 'VALUE' && $param_value == 'DATE') {
						continue;
					}
					
					if ($param_value === null) {
						$params_str .= ";$param_name";
					} else {
						$params_str .= ";$param_name=$param_value";
					}
				}
			}
			
			$value = $attribute ['value'];
			switch ($name) {
				// Date fields.
				case 'COMPLETED' :
				case 'CREATED' :
				case 'DCREATED' :
				case 'LAST-MODIFIED' :
					$value = $this->_exportDateTime ($value);
					break;
				
				case 'DTEND' :
				case 'DTSTART' :
				case 'DTSTAMP' :
				case 'DUE' :
				case 'AALARM' :
				case 'RECURRENCE-ID' :
					if (isset ($params ['VALUE'])) {
						if ($params ['VALUE'] == 'DATE') {
							$value = $this->_exportDate ($value, $name == 'DTEND' ? '235959' : '000000');
						} else {
							$value = $this->_exportDateTime ($value);
						}
					} else {
						$value = $this->_exportDateTime ($value);
					}
					break;
				
				// Comma seperated dates.
				case 'EXDATE' :
				case 'RDATE' :
					$dates = array ();
					foreach ($value as $date) {
						if (isset ($params ['VALUE'])) {
							if ($params ['VALUE'] == 'DATE') {
								$dates [] = $this->_exportDate ($date, '000000');
							} elseif ($params ['VALUE'] == 'PERIOD') {
								$dates [] = $this->_exportPeriod ($date);
							} else {
								$dates [] = $this->_exportDateTime ($date);
							}
						} else {
							$dates [] = $this->_exportDateTime ($date);
						}
					}
					$value = implode (',', $dates);
					break;
				
				case 'TRIGGER' :
					if (isset ($params ['VALUE'])) {
						if ($params ['VALUE'] == 'DATE-TIME') {
							$value = $this->_exportDateTime ($value);
						} elseif ($params ['VALUE'] == 'DURATION') {
							$value = $this->_exportDuration ($value);
						}
					} else {
						$value = $this->_exportDuration ($value);
					}
					break;
				
				// Duration fields.
				case 'DURATION' :
					$value = $this->_exportDuration ($value);
					break;
				
				// Period of time fields.
				case 'FREEBUSY' :
					$value_str = '';
					foreach ($value as $period) {
						$value_str .= empty ($value_str) ? '' : ',';
						$value_str .= $this->_exportPeriod ($period);
					}
					$value = $value_str;
					break;
				
				// UTC offset fields.
				case 'TZOFFSETFROM' :
				case 'TZOFFSETTO' :
					$value = $this->_exportUtcOffset ($value);
					break;
				
				// Integer fields.
				case 'PERCENT-COMPLETE' :
				case 'PRIORITY' :
				case 'REPEAT' :
				case 'SEQUENCE' :
					$value = '$value';
					break;
				
				// Geo fields.
				case 'GEO' :
					$value = $value ['latitude'] . ',' . $value ['longitude'];
					break;
				
				// Recurrence fields.
				case 'EXRULE' :
				case 'RRULE' :
					break;
				
				case 'ATTENDEE' :
					// Kronolith creates attendee field in vcalendar2.0 format.
					// Convert to vcalendar1.0 if necessary.
					// Example of 1.0 style:
					// ATTENDEE;ROLE=OWNER;STATUS=CONFIRMED:John Smith <jsmith@host1.com>
					if ($this->isOldFormat ()) {
						$value = preg_replace ('/MAILTO:/i', '', $value);
						$value = $value . ' <' . $value . '>';
						$params_str = str_replace ('PARTSTAT=', 'STATUS=', $params_str);
						$params_str = str_replace ('STATUS=NEEDS-ACTION', 'STATUS=NEEDS ACTION', $params_str);
						$params_str = str_replace ('ROLE=REQ-PARTICIPANT', 'EXPECT=REQUIRE', $params_str);
						$params_str = str_replace ('ROLE=OPT-PARTICIPANT', 'EXPECT=REQUEST', $params_str);
						$params_str = str_replace ('ROLE=NON-PARTICIPANT', 'EXPECT=FYI', $params_str);
						$params_str = str_replace ('RSVP=TRUE', 'RSVP=YES', $params_str);
						$params_str = str_replace ('RSVP=FALSE', 'RSVP=NO', $params_str);
					}
					break;
				
				default :
					if ($this->isOldFormat ()) {
						if (is_array ($attribute ['values']) && count ($attribute ['values']) > 1) {
							$values = $attribute ['values'];
							if ($name == 'N' || $name == 'ADR' || $name == 'ORG') {
								$glue = ';';
							} else {
								$glue = ',';
							}
							$values = str_replace (';', '\\;', $values);
							$value = implode ($glue, $values);
						} else {
							/*
							 * vcard 2.1 and vcalendar 1.0 escape only semicolons
							 */
							$value = str_replace (';', '\\;', $value);
						}
						if ($name == 'STATUS') {
							// vcalendar 1.0 has STATUS:NEEDS ACTION while 2.0 has
							// STATUS:NEEDS-ACTION.
							$value = str_replace ('NEEDS-ACTION', 'NEEDS ACTION', $value);
						}
						// Text containing newlines or ASCII >= 127 must be BASE64
						// or QUOTED-PRINTABLE encoded. Currently we use
						// QUOTED-PRINTABLE as default.
						// FIXME: deal with base64 encodings!
						if (preg_match ("/[^\x20-\x7F\r\n]/", $value) && empty ($params ['ENCODING'])) {
							$params ['ENCODING'] = 'QUOTED-PRINTABLE';
							$params_str .= ';ENCODING=QUOTED-PRINTABLE';
							// Add CHARSET as well. At least the synthesis client
							// gets confused otherwise
							if (empty ($params ['CHARSET'])) {
								$params ['CHARSET'] = NLS::getCharset ();
								$params_str .= ';CHARSET=' . $params ['CHARSET'];
							}
						}
					} else {
						if (is_array ($attribute ['values']) && count ($attribute ['values']) > 1) {
							$values = $attribute ['values'];
							if ($name == 'N' || $name == 'ADR' || $name == 'ORG') {
								$glue = ';';
							} else {
								$glue = ',';
							}
							// As of rfc 2426 2.5 semicolon and comma must be
							// escaped.
							$values = str_replace (array (
									';',
									',',
									'\\' 
							), array (
									'\\;',
									'\\,',
									'\\\\' 
							), $values);
							$value = implode ($glue, $values);
						} else {
							// As of rfc 2426 2.5 semicolon and comma must be
							// escaped.
							$value = str_replace (array (
									';',
									',',
									'\\' 
							), array (
									'\\;',
									'\\,',
									'\\\\' 
							), $value);
						}
					}
					break;
			}
			
			if (! empty ($params ['ENCODING']) && $params ['ENCODING'] == 'QUOTED-PRINTABLE' && strlen (trim ($value)) > 0) {
				$value = str_replace ('\r', '', $value);
				/*
				 * quotedPrintableEncode does not escape CRLFs, but strange enough single LFs. so convert everything to LF only and replace afterwards.
				 */
				$result .= $name . $params_str . ':=' . $this->_newline . str_replace ('=0A', '=0D=0A', $this->_quotedPrintableEncode ($value)) . $this->_newline;
			} else {
				$attr_string = $name . $params_str . ':' . $value;
				$result .= $this->_foldLine ($attr_string) . $this->_newline;
			}
		}
		
		foreach ($this->_components as $component) {
			$result .= $component->exportvCalendar ();
		}
		
		return $result . 'END:' . $base . $this->_newline;
	}
	
	/**
	 * Parse a UTC Offset field.
	 */
	function _parseUtcOffset($text) {
		$offset = array ();
		$timeParts = array ();
		if (preg_match ('/(\+|-)([0-9]{2})([0-9]{2})([0-9]{2})?/', $text, $timeParts)) {
			$offset ['ahead'] = (bool) ($timeParts [1] == '+');
			$offset ['hour'] = intval ($timeParts [2]);
			$offset ['minute'] = intval ($timeParts [3]);
			if (isset ($timeParts [4])) {
				$offset ['second'] = intval ($timeParts [4]);
			}
			return $offset;
		} else {
			return false;
		}
	}
	
	/**
	 * Export a UTC Offset field.
	 */
	function _exportUtcOffset($value) {
		$offset = $value ['ahead'] ? '+' : '-';
		$offset .= sprintf ('%02d%02d', $value ['hour'], $value ['minute']);
		if (isset ($value ['second'])) {
			$offset .= sprintf ('%02d', $value ['second']);
		}
		
		return $offset;
	}
	
	/**
	 * Parse a Time Period field.
	 */
	function _parsePeriod($text) {
		$periodParts = explode ('/', $text);
		
		$start = $this->_parseDateTime ($periodParts [0]);
		
		if ($duration = $this->_parseDuration ($periodParts [1])) {
			return array (
					'start' => $start,
					'duration' => $duration 
			);
		} elseif ($end = $this->_parseDateTime ($periodParts [1])) {
			return array (
					'start' => $start,
					'end' => $end 
			);
		}
	}
	
	/**
	 * Export a Time Period field.
	 */
	function _exportPeriod($value) {
		$period = $this->_exportDateTime ($value ['start']);
		$period .= '/';
		if (isset ($value ['duration'])) {
			$period .= $this->_exportDuration ($value ['duration']);
		} else {
			$period .= $this->_exportDateTime ($value ['end']);
		}
		return $period;
	}
	
	/**
	 * Grok the TZID and return an offset in seconds from UTC for this
	 * date and time.
	 */
	function _parseTZID($date, $time, $tzid) {
		$vtimezone = $this->_container->findComponentByAttribute ('vtimezone', 'TZID', $tzid);
		if (! $vtimezone) {
			return false;
		}
		$change_times = array ();
		foreach ($vtimezone->getComponents () as $o) {
			$t = $vtimezone->parseChild ($o, $date ['year']);
			if ($t !== false) {
				$change_times [] = $t;
			}
		}
		
		if (! $change_times) {
			return false;
		}
		
		sort ($change_times);
		
		// Time is arbitrarily based on UTC for comparison.
		$t = @gmmktime ($time ['hour'], $time ['minute'], $time ['second'], $date ['month'], $date ['mday'], $date ['year']);
		
		if ($t < $change_times [0] ['time']) {
			return $change_times [0] ['from'];
		}
		
		for ($i = 0, $n = count ($change_times); $i < $n - 1; $i ++) {
			if (($t >= $change_times [$i] ['time']) && ($t < $change_times [$i + 1] ['time'])) {
				return $change_times [$i] ['to'];
			}
		}
		
		if ($t >= $change_times [$n - 1] ['time']) {
			return $change_times [$n - 1] ['to'];
		}
		
		return false;
	}
	
	/**
	 * Parse a DateTime field into a unix timestamp.
	 */
	function _parseDateTime($text, $tzid = false) {
		$dateParts = explode ('T', $text);
		if (count ($dateParts) != 2 && ! empty ($text)) {
			// Not a datetime field but may be just a date field.
			if (! $date = $this->_parseDate ($text)) {
				return $date;
			}
			$newtext = $text . 'T000000';
			$dateParts = explode ('T', $newtext);
		}
		
		if (! $date = $this->_parseDate ($dateParts [0])) {
			return $text;
		}
		if (! $time = $this->_parseTime ($dateParts [1])) {
			return $text;
		}
		
		// Get timezone info for date fields from $tzid and container.
		$tzoffset = ($time ['zone'] == 'Local' && $tzid) ? $this->_parseTZID ($date, $time, $tzid) : false;
		
		if ($time ['zone'] == 'UTC' || $tzoffset !== false) {
			$result = @gmmktime ($time ['hour'], $time ['minute'], $time ['second'], $date ['month'], $date ['mday'], $date ['year']);
			if ($tzoffset) {
				$result -= $tzoffset;
			}
		} else {
			$result = @mktime ($time ['hour'], $time ['minute'], $time ['second'], $date ['month'], $date ['mday'], $date ['year']);
		}
		return ($result !== false) ? $result : $text;
	}
	
	/**
	 * Export a DateTime field.
	 */
	function _exportDateTime($value) {
		$temp = array ();
		if (! is_object ($value) && ! is_array ($value)) {
			$tz = date ('O', $value);
			$TZOffset = (3600 * substr ($tz, 0, 3)) + (60 * substr (date ('O', $value), 3, 2));
			$value -= $TZOffset;
			
			$temp ['zone'] = 'UTC';
			$temp ['year'] = date ('Y', $value);
			$temp ['month'] = date ('n', $value);
			$temp ['mday'] = date ('j', $value);
			$temp ['hour'] = date ('G', $value);
			$temp ['minute'] = date ('i', $value);
			$temp ['second'] = date ('s', $value);
		} else {
			$dateOb = new tx_model_date ($value);
			return tx_model_iCalendar::_exportDateTime ($dateOb->timestamp ());
		}
		
		return tx_model_iCalendar::_exportDate ($temp) . 'T' . tx_model_iCalendar::_exportTime ($temp);
	}
	
	/**
	 * Parse a Time field.
	 */
	function _parseTime($text) {
		$timeParts = array ();
		if (preg_match ('/([0-9]{2})([0-9]{2})([0-9]{2})(Z)?/', $text, $timeParts)) {
			$time ['hour'] = intval ($timeParts [1]);
			$time ['minute'] = intval ($timeParts [2]);
			$time ['second'] = intval ($timeParts [3]);
			if (isset ($timeParts [4])) {
				$time ['zone'] = 'UTC';
			} else {
				$time ['zone'] = 'Local';
			}
			return $time;
		} else {
			return false;
		}
	}
	
	/**
	 * Export a Time field.
	 */
	function _exportTime($value) {
		$time = sprintf ('%02d%02d%02d', $value ['hour'], $value ['minute'], $value ['second']);
		if ($value ['zone'] == 'UTC') {
			$time .= 'Z';
		}
		return $time;
	}
	
	/**
	 * Parse a Date field.
	 */
	function _parseDate($text) {
		$parts = explode ('T', $text);
		if (count ($parts) == 2) {
			$text = $parts [0];
		}
		$match = array ();
		if (! preg_match ('/^(\d{4})-?(\d{2})-?(\d{2})$/', $text, $match)) {
			return false;
		}
		
		return array (
				'year' => $match [1],
				'month' => $match [2],
				'mday' => $match [3] 
		);
	}
	
	/**
	 * Export a Date field.
	 */
	function _exportDate($value, $autoconvert = false) {
		if (is_object ($value)) {
			$value = array (
					'year' => $value->year,
					'month' => $value->month,
					'mday' => $value->mday 
			);
		}
		if ($autoconvert !== false && $this->isOldFormat ()) {
			return sprintf ('%04d%02d%02dT%s', $value ['year'], $value ['month'], $value ['mday'], $autoconvert);
		} else {
			return sprintf ('%04d%02d%02d', $value ['year'], $value ['month'], $value ['mday']);
		}
	}
	
	/**
	 * Parse a Duration Value field.
	 */
	function _parseDuration($text) {
		$durvalue = array ();
		if (preg_match ('/([+]?|[-])P(([0-9]+W)|([0-9]+D)|)(T(([0-9]+H)|([0-9]+M)|([0-9]+S))+)?/', trim ($text), $durvalue)) {
			// Weeks.
			$duration = 7 * 86400 * intval ($durvalue [3]);
			
			if (count ($durvalue) > 4) {
				// Days.
				$duration += 86400 * intval ($durvalue [4]);
			}
			if (count ($durvalue) > 5) {
				// Hours.
				$duration += 3600 * intval ($durvalue [7]);
				
				// Mins.
				if (isset ($durvalue [8])) {
					$duration += 60 * intval ($durvalue [8]);
				}
				
				// Secs.
				if (isset ($durvalue [9])) {
					$duration += intval ($durvalue [9]);
				}
			}
			
			// Sign.
			if ($durvalue [1] == '-') {
				$duration *= - 1;
			}
			
			return $duration;
		} else {
			return false;
		}
	}
	
	/**
	 * Export a duration value.
	 */
	function _exportDuration($value) {
		$duration = '';
		if ($value < 0) {
			$value *= - 1;
			$duration .= '-';
		}
		$duration .= 'P';
		
		$weeks = floor ($value / (604800)); // 7 * 86400
		$value = $value % (604800);
		if ($weeks) {
			$duration .= $weeks . 'W';
		}
		
		$days = floor ($value / (86400));
		$value = $value % (86400);
		if ($days) {
			$duration .= $days . 'D';
		}
		
		if ($value) {
			$duration .= 'T';
			
			$hours = floor ($value / 3600);
			$value = $value % 3600;
			if ($hours) {
				$duration .= $hours . 'H';
			}
			
			$mins = floor ($value / 60);
			$value = $value % 60;
			if ($mins) {
				$duration .= $mins . 'M';
			}
			
			if ($value) {
				$duration .= $value . 'S';
			}
		}
		
		return $duration;
	}
	
	/**
	 * Return the folded version of a line.
	 */
	function _foldLine($line) {
		$line = preg_replace ('/\r\n|\n|\r/', '\n', $line);
		if (strlen ($line) > 75) {
			$foldedline = '';
			while (! empty ($line)) {
				$maxLine = substr ($line, 0, 75);
				$cutPoint = max (60, max (strrpos ($maxLine, ';'), strrpos ($maxLine, ':')) + 1);
				
				$foldedline .= (empty ($foldedline)) ? substr ($line, 0, $cutPoint) : $this->_newline . ' ' . substr ($line, 0, $cutPoint);
				
				$line = (strlen ($line) <= $cutPoint) ? '' : substr ($line, $cutPoint);
			}
			return $foldedline;
		}
		return $line;
	}
	
	/**
	 * Convert an 8bit string to a quoted-printable string according
	 * to RFC2045, section 6.7.
	 *
	 * Uses imap_8bit if available.
	 *
	 * @param string $input
	 *        	The string to be encoded.
	 *        	
	 * @return string The quoted-printable encoded string.
	 */
	function _quotedPrintableEncode($input = '') {
		// If imap_8bit() is available, use it.
		if (function_exists ('imap_8bit')) {
			return imap_8bit ($input);
		}
		
		// Rather dumb replacment: just encode everything.
		$hex = array (
				'0',
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9',
				'A',
				'B',
				'C',
				'D',
				'E',
				'F' 
		);
		
		$output = '';
		$len = strlen ($input);
		for ($i = 0; $i < $len; ++ $i) {
			$c = substr ($input, $i, 1);
			$dec = ord ($c);
			$output .= '=' . $hex [floor ($dec / 16)] . $hex [floor ($dec % 16)];
			if (($i + 1) % 25 == 0) {
				$output .= '=\r\n';
			}
		}
		return $output;
	}
}
if (defined ('TYPO3_MODE') && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_model_iCalendar.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/cal/model/class.tx_model_iCalendar.php']);
}
?>