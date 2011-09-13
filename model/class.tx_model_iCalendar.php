<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 
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

define('TX_MODEL_CALICALENDAR_SCALE_GREGORIAN', 	0);

define('TX_MODEL_CALICALENDAR_RECUR_NONE',          0);
define('TX_MODEL_CALICALENDAR_RECUR_DAILY',         1);
define('TX_MODEL_CALICALENDAR_RECUR_WEEKLY',        2);
define('TX_MODEL_CALICALENDAR_RECUR_DAY_OF_MONTH',  3);
define('TX_MODEL_CALICALENDAR_RECUR_WEEK_OF_MONTH', 4);
define('TX_MODEL_CALICALENDAR_RECUR_YEARLY',        5);

define('TX_MODEL_CALICALENDAR_STATUS_NONE', 		0);
define('TX_MODEL_CALICALENDAR_STATUS_TENTATIVE', 	1);
define('TX_MODEL_CALICALENDAR_STATUS_CONFIRMED', 	2);
define('TX_MODEL_CALICALENDAR_STATUS_CANCELLED', 	3);
define('TX_MODEL_CALICALENDAR_STATUS_FREE', 		4);

define('TX_MODEL_CALICALENDAR_RESPONSE_NONE',      	1);
define('TX_MODEL_CALICALENDAR_RESPONSE_ACCEPTED',  	2);
define('TX_MODEL_CALICALENDAR_RESPONSE_DECLINED',  	3);
define('TX_MODEL_CALICALENDAR_RESPONSE_TENTATIVE', 	4);

define('TX_MODEL_CALICALENDAR_PART_REQUIRED', 		1);
define('TX_MODEL_CALICALENDAR_PART_OPTIONAL', 		2);
define('TX_MODEL_CALICALENDAR_PART_NONE',     		3);
define('TX_MODEL_CALICALENDAR_PART_IGNORE',   		4);

define('TX_MODEL_CALICALENDAR_ITIP_REQUEST', 		1);
define('TX_MODEL_CALICALENDAR_ITIP_CANCEL',  		2);

define('TX_MODEL_CALICALENDAR_ERROR_FB_NOT_FOUND', 	1);

class tx_model_iCalendar {
	
	
//	BEGIN:VCALENDAR
//CALSCALE:GREGORIAN
//X-WR-TIMEZONE;VALUE=TEXT:US/Pacific
//METHOD:PUBLISH
//PRODID:-//Apple Computer\, Inc//iCal 1.0//EN
//X-WR-CALNAME;VALUE=TEXT:Home 1234
//X-WR-RELCALID;VALUE=TEXT:99732F9A-92C7-11D7-A4A2-000A95690022
//VERSION:2.0

	var $_calscale = "";
	
    /**
     * The parent (containing) iCalendar object.
     *
     * @var Horde_iCalendar
     */
    var $_container = false;

    /**
     * The name/value pairs of attributes for this object (UID,
     * DTSTART, etc.). Which are present depends on the object and on
     * what kind of component it is.
     *
     * @var array
     */
    var $_attributes = array();

    /**
     * Any children (contained) iCalendar components of this object.
     *
     * @var array
     */
    var $_components = array();

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

    function tx_model_iCalendar($version = '2.0')
    {
        $this->_version = $version;
        $this->setAttribute('VERSION', $version);
    }

    /**
     * Return a reference to a new component.
     *
     * @param string          $type       The type of component to return
     * @param Horde_iCalendar $container  A container that this component
     *                                    will be associated with.
     *
     * @return object  Reference to a Horde_iCalendar_* object as specified.
     */
    function &newComponent($type, &$container)
    {
        $type = strtolower($type);
        $class = "tx_iCalendar_".$type;
        require_once t3lib_extMgm::extPath('cal'). 'model/iCalendar/class.' . $class . '.php';
        if (class_exists($class)) {
            $component = &new $class();
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
     * @param string $name     The name of the attribute.
     * @param string $value    The value of the attribute.
     * @param array $params    Array containing any addition parameters for
     *                         this attribute.
     * @param boolean $append  True to append the attribute, False to replace
     *                         the first matching attribute found.
     * @param array $values    Array representation of $value.  For
     *                         comma/semicolon seperated lists of values.  If
     *                         not set use $value as single array element.
     */
    function setAttribute($name, $value, $params = array(), $append = true, $values = false)
    {
        // Make sure we update the internal format version if
        // setAttribute('VERSION', ...) is called.
        if ($name == 'VERSION') {
            $this->_version = $value;
        }

        $found = $append;
        if (!$values) {
            $values = array($value);
        }
        $keys = array_keys($this->_attributes);
        foreach ($keys as $key) {
            if ($found) break;
            if ($this->_attributes[$key]['name'] == strtoupper($name)) {
                $this->_attributes[$key]['params'] = $params;
                $this->_attributes[$key]['value'] = $value;
                $this->_attributes[$key]['values'] = $values;
                $found = true;
            }
        }

        if ($append || !$found) {
            $this->_attributes[] = array(
                'name'      => strtoupper($name),
                'params'    => $params,
                'value'     => $value,
                'values'    => $values
            );
        }
    }

    /**
     * Sets parameter(s) for an (already existing) attribute.  The
     * parameter set is merged into the existing set.
     *
     * @param string $name   The name of the attribute.
     * @param array $params  Array containing any additional parameters for
     *                       this attribute.
     * @return boolean  True on success, false if no attribute $name exists.
     */
    function setParameter($name, $params)
    {
        $keys = array_keys($this->_attributes);
        foreach ($keys as $key) {
            if ($this->_attributes[$key]['name'] == $name) {
                $this->_attributes[$key]['params'] =
                    array_merge($this->_attributes[$key]['params'], $params);
                return true;
            }
        }

        return false;
    }

    /**
     * Get the value of an attribute.
     *
     * @param string $name     The name of the attribute.
     * @param boolean $params  Return the parameters for this attribute instead
     *                         of its value.
     *
     * @return mixed (object)  PEAR_Error if the attribute does not exist.
     *               (string)  The value of the attribute.
     *               (array)   The parameters for the attribute or
     *                         multiple values for an attribute.
     */
    function getAttribute($name, $params = false)
    {
        $result = array();
        foreach ($this->_attributes as $attribute) {
            if ($attribute['name'] == $name) {
                if ($params) {
                    $result[] = $attribute['params'];
                } else {
                    $result[] = $attribute['value'];
                }
            }
        }
        if (!count($result)) {
            //require_once 'PEAR.php';
            return null;
        } if (count($result) == 1 && !$params) {
            return $result[0];
        } else {
            return $result;
        }
    }

    /**
     * Gets the values of an attribute as an array.  Multiple values
     * are possible due to:
     *
     *  a) multiplce occurences of 'name'
     *  b) (unsecapd) comma seperated lists.
     *
     * So for a vcard like "KEY:a,b\nKEY:c" getAttributesValues('KEY')
     * will return array('a','b','c').
     *
     * @param string  $name    The name of the attribute.
     * @return mixed (object)  PEAR_Error if the attribute does not exist.
     *               (array)   Multiple values for an attribute.
     */
    function getAttributeValues($name)
    {
        $result = array();
        foreach ($this->_attributes as $attribute) {
            if ($attribute['name'] == $name) {
                $result = array_merge($attribute['values'], $result);
            }
        }
        if (!count($result)) {
            return PEAR::raiseError('Attribute "' . $name . '" Not Found');
        }
        return $result;
    }

    /**
     * Returns the value of an attribute, or a specified default value
     * if the attribute does not exist.
     *
     * @param string $name    The name of the attribute.
     * @param mixed $default  What to return if the attribute specified by
     *                        $name does not exist.
     *
     * @return mixed (string) The value of $name.
     *               (mixed)  $default if $name does not exist.
     */
    function getAttributeDefault($name, $default = '')
    {
        $value = $this->getAttribute($name);
        return is_a($value, 'PEAR_Error') ? $default : $value;
    }

    /**
     * Remove all occurences of an attribute.
     *
     * @param string $name  The name of the attribute.
     */
    function removeAttribute($name)
    {
        $keys = array_keys($this->_attributes);
        foreach ($keys as $key) {
            if ($this->_attributes[$key]['name'] == $name) {
                unset($this->_attributes[$key]);
            }
        }
    }

    /**
     * Get attributes for all tags or for a given tag.
     *
     * @param string $tag  Return attributes for this tag, or all attributes if
     *                     not given.
     *
     * @return array  An array containing all the attributes and their types.
     */
    function getAllAttributes($tag = false)
    {
        if ($tag === false) {
            return $this->_attributes;
        }
        $result = array();
        foreach ($this->_attributes as $attribute) {
            if ($attribute['name'] == $tag) {
                $result[] = $attribute;
            }
        }
        return $result;
    }

    /**
     * Add a vCalendar component (eg vEvent, vTimezone, etc.).
     *
     * @param Horde_iCalendar $component  Component (subclass) to add.
     */
    function addComponent($component)
    {
        if (is_a($component, 'tx_model_iCalendar')) {
            $component->_container = &$this;
            $this->_components[] = &$component;
        }
    }

    /**
     * Retrieve all the components.
     *
     * @return array  Array of Horde_iCalendar objects.
     */
    function getComponents()
    {
        return $this->_components;
    }

    /**
     * Return the classes (entry types) we have.
     *
     * @return array  Hash with class names Horde_iCalendar_xxx as keys
     *                and number of components of this class as value.
     */
    function getComponentClasses()
    {
        $r = array();
        foreach ($this->_components as $c) {
            $cn = strtolower(get_class($c));
            if (empty($r[$cn])) {
                $r[$cn] = 1;
            } else {
                $r[$cn]++;
            }
        }

        return $r;
    }

    /**
     * Number of components in this container.
     *
     * @return integer  Number of components in this container.
     */
    function getComponentCount()
    {
        return count($this->_components);
    }

    /**
     * Retrieve a specific component.
     *
     * @param integer $idx  The index of the object to retrieve.
     *
     * @return mixed    (boolean) False if the index does not exist.
     *                  (Horde_iCalendar_*) The requested component.
     */
    function getComponent($idx)
    {
        if (isset($this->_components[$idx])) {
            return $this->_components[$idx];
        } else {
            return false;
        }
    }

    /**
     * Locates the first child component of the specified class, and
     * returns a reference to this component.
     *
     * @param string $type  The type of component to find.
     *
     * @return mixed (boolean) False if no subcomponent of the specified
     *                         class exists.
     *               (Horde_iCalendar_*) A reference to the requested component.
     */
    function &findComponent($childclass)
    {
        $childclass = 'tx_model_iCalendar_' . String::lower($childclass);
        $keys = array_keys($this->_components);
        foreach ($keys as $key) {
            if (is_a($this->_components[$key], $childclass)) {
                return $this->_components[$key];
            }
        }

        return false;
    }

    /**
     * Clears the iCalendar object (resets the components and attributes
     * arrays).
     */
    function clear()
    {
        $this->_components = array();
        $this->_attributes = array();
    }

    /**
     * Export as vCalendar format.
     */
    function exportvCalendar()
    {
        // Default values.
        $requiredAttributes['PRODID'] = '-//The TYPO3 Project//cal extension//EN';
        $requiredAttributes['METHOD'] = 'PUBLISH';

        foreach ($requiredAttributes as $name => $default_value) {
            if (is_a($this->getattribute($name), 'PEAR_Error')) {
                $this->setAttribute($name, $default_value);
            }
        }

        return $this->_exportvData('VCALENDAR');
    }

    /**
     * Export this entry as a hash array with tag names as keys.
     *
     * @param boolean $paramsInKeys
     *                If false, the operation can be quite lossy as the
     *                parameters are ignored when building the array keys.
     *                So if you export a vcard with
     *                LABEL;TYPE=WORK:foo
     *                LABEL;TYPE=HOME:bar
     *                the resulting hash contains only one label field!
     *                If set to true, array keys look like 'LABEL;TYPE=WORK'
     * @return array  A hash array with tag names as keys.
     */
    function toHash($paramsInKeys = false)
    {
        $hash = array();
        foreach ($this->_attributes as $a)  {
            $k = $a['name'];
            if ($paramsInKeys && is_array($a['params'])) {
                foreach ($a['params'] as $p => $v) {
                    $k .= ";$p=$v";
                }
            }
            $hash[$k] = $a['value'];
        }

        return $hash;
    }

    /**
     * Parses a string containing vCalendar data.
     *
     * @param string $text     The data to parse.
     * @param string $base     The type of the base object.
     * @param string $charset  The encoding charset for $text. Defaults to
     *                         utf-8.
     * @param boolean $clear   If true clears the iCal object before parsing.
     *
     * @return boolean  True on successful import, false otherwise.
     */
    function parsevCalendar($text, $base = 'VCALENDAR', $charset = 'utf8',
                            $clear = true)
    {
        if ($clear) {
            $this->clear();
        }

        if (preg_match('/(BEGIN:' . $base . '\r?\n?)([\W\w]*)(END:' . $base . '\r?\n?)/i', $text, $matches)) {
            $vCal = $matches[2];
        } else {
            // Text isn't enclosed in BEGIN:VCALENDAR
            // .. END:VCALENDAR. We'll try to parse it anyway.
            $vCal = $text;
        }

        // All subcomponents.
        $matches = null;
        if (preg_match_all('/BEGIN:([\W\w]*)(\r\n|\r|\n)([\W\w]*)END:\1(\r\n|\r|\n)/Ui', $vCal, $matches)) {
            foreach ($matches[0] as $key => $data) {
                $type = $matches[1][$key];
                $component = &$this->newComponent(trim($type), $this);
                if ($component === false) {
                    return "Unable to create object for type $type";
                }
                $component->parsevCalendar($data);

                $this->addComponent($component);

                // Remove from the vCalendar data.
                $vCal = str_replace($data, '', $vCal);
            }
        }

        // Unfold any folded lines.
        $vCal = preg_replace('/[\r\n]+[ \t]/', '', $vCal);

        // Unfold "quoted printable" folded lines like:
        //  BODY;ENCODING=QUOTED-PRINTABLE:=
        //  another=20line=
        //  last=20line
        if (preg_match_all('/^([^:]+;\s*ENCODING=QUOTED-PRINTABLE(.*=\r?\n)+(.*[^=])?\r?\n)/mU', $vCal, $matches)) {
            foreach ($matches[1] as $s) {
                $r = preg_replace('/=\r?\n/', '', $s);
                $vCal = str_replace($s, $r, $vCal);
            }
        }

        // Parse the remaining attributes.
        if (preg_match_all('/(.*):([^\r\n]*)[\r\n]+/', $vCal, $matches)) {
            foreach ($matches[0] as $attribute) {
                preg_match('/([^;^:]*)((;[^:]*)?):([^\r\n]*)[\r\n]*/', $attribute, $parts);
                $tag = $parts[1];
                $value = $parts[4];
                $params = array();
                // Parse parameters.
                if (!empty($parts[2])) {
                    preg_match_all('/;(([^;=]*)(=([^;]*))?)/', $parts[2], $param_parts);
                    foreach ($param_parts[2] as $key => $paramName) {
                        $paramValue = $param_parts[4][$key];
                        $params[strtoupper($paramName)] = $paramValue;
                    }
                }
                
                // mario
                if (is_object($GLOBALS['LANG']))	{
					$csConvObj = &$GLOBALS['LANG']->csConvObj;
				} elseif (is_object($GLOBALS['TSFE']))	{
					$csConvObj = &$GLOBALS['TSFE']->csConvObj;
				} else {
					$csConvObj = NULL;
				}
		        // mario
//TODO: charset handling
                // Charset and encoding handling.
//                if ((isset($params['ENCODING'])
//                     && strtoupper($params['ENCODING']) == 'QUOTED-PRINTABLE')
//                    || isset($params['QUOTED-PRINTABLE'])) {
//
//                    $value = quoted_printable_decode($value);
//
//					// Set charset:
//					$value = $csConvObj->parse_charset($csConvObj->charSetArray[$params['CHARSET']] ? $csConvObj->charSetArray[$params['CHARSET']] : 'utf-8');
//                    
//                    // Quoted printable is normally encoded as utf-8.
////                    if (isset($params['CHARSET'])) {
////                        $value = String::convertCharset($value, $params['CHARSET']);
////                    } else {
////                        $value = String::convertCharset($value, 'utf-8');
////                    }
//                } elseif (isset($params['CHARSET'])) {
//                    $value = $csConvObj->parse_charset($csConvObj->charSetArray[$params['CHARSET']]);
//                } else {
//                    // As per RFC 2279, assume UTF8 if we don't have an
//                    // explicit charset parameter.
//                    $value = $csConvObj->parse_charset($csConvObj->charSetArray[$charset]);
//                }
//debug(&$GLOBALS['LANG']->csConvObj);
                switch ($tag) {
                // Date fields.
                case 'COMPLETED':
                case 'CREATED':
                case 'LAST-MODIFIED':
                    $this->setAttribute($tag, $this->_parseDateTime($value), $params);
                    break;

                case 'BDAY':
                    $this->setAttribute($tag, $this->_parseDate($value), $params);
                    break;

                case 'DTEND':
                case 'DTSTART':
                case 'DTSTAMP':
                case 'DUE':
                case 'AALARM':
                case 'RECURRENCE-ID':
                    if (isset($params['VALUE']) && $params['VALUE'] == 'DATE') {
                        $this->setAttribute($tag, $this->_parseDate($value), $params);
                    } else {
                        $this->setAttribute($tag, $this->_parseDateTime($value), $params);
                    }
                    break;

                case 'TRIGGER':
                    if (isset($params['VALUE'])) {
                        if ($params['VALUE'] == 'DATE-TIME') {
                            $this->setAttribute($tag, $this->_parseDateTime($value), $params);
                        } else {
                            $this->setAttribute($tag, $this->_parseDuration($value), $params);
                        }
                    } else {
                        $this->setAttribute($tag, $this->_parseDuration($value), $params);
                    }
                    break;

                // Comma seperated dates.
                case 'EXDATE':
                case 'RDATE':
                    $dates = array();
                    preg_match_all('/,([^,]*)/', ',' . $value, $values);

                    foreach ($values[1] as $value) {
                        $dates[] = $this->_parseDate($value);
                    }
                    $this->setAttribute($tag, isset($dates[0]) ? $dates[0] : null, $params, true, $dates);
                    break;

                // Duration fields.
                case 'DURATION':
                    $this->setAttribute($tag, $this->_parseDuration($value), $params);
                    break;

                // Period of time fields.
                case 'FREEBUSY':
                    $periods = array();
                    preg_match_all('/,([^,]*)/', ',' . $value, $values);
                    foreach ($values[1] as $value) {
                        $periods[] = $this->_parsePeriod($value);
                    }

                    $this->setAttribute($tag, isset($periods[0]) ? $periods[0] : null, $params, true, $periods);
                    break;

                // UTC offset fields.
                case 'TZOFFSETFROM':
                case 'TZOFFSETTO':
                    $this->setAttribute($tag, $this->_parseUtcOffset($value), $params);
                    break;

                // Integer fields.
                case 'PERCENT-COMPLETE':
                case 'PRIORITY':
                case 'REPEAT':
                case 'SEQUENCE':
                    $this->setAttribute($tag, intval($value), $params);
                    break;

                // Geo fields.
                case 'GEO':
                    $floats = explode(';', $value);
                    $value['latitude'] = floatval($floats[0]);
                    $value['longitude'] = floatval($floats[1]);
                    $this->setAttribute($tag, $value, $params);
                    break;

                // Recursion fields.
                case 'EXRULE':
                case 'RRULE':
                    $this->setAttribute($tag, trim($value), $params);
                    break;

                // ADR an N are lists seperated by unescaped semi-colons.
                case 'ADR':
                case 'N':
                    $value = trim($value);
                    // As of rfc 2426 2.4.2 semi-colon, comma, and
                    // colon must be escaped (comma is unescaped after
                    // splitting below).
                    $value = str_replace(array('\\n', '\\;', '\\:'),
                                         array($this->_newline, ';', ':'),
                                         $value);

                    // Split by unescaped semi-colons:
                    $values = preg_split('/(?<!\\\\);/',$value);
                    $value = str_replace('\\;', ';', $value);
                    $values = str_replace('\\;', ';', $values);
                    $this->setAttribute($tag, trim($value), $params, true, $values);
                    break;

                // String fields.
                default:
                    $value = trim($value);
                    // As of rfc 2426 2.4.2 semi-colon, comma, and
                    // colon must be escaped (comma is unescaped after
                    // splitting below).
                    $value = str_replace(array('\\n', '\\;', '\\:'),
                                         array($this->_newline, ';', ':'),
                                         $value);

                    // Split by unescaped commas:
                    $values = preg_split('/(?<!\\\\),/',$value);
                    $value = str_replace('\\,', ',', $value);
                    $values = str_replace('\\,', ',', $values);

                    $this->setAttribute($tag, trim($value), $params, true, $values);
                    break;
                }
            }
        }
        return true;
    }

    /**
     * Export this component in vCal format.
     *
     * @param string $base  The type of the base object.
     *
     * @return string  vCal format data.
     */
    function _exportvData($base = 'VCALENDAR')
    {
        $result = 'BEGIN:' . strtoupper($base) . $this->_newline;

        // Ensure that version is the first attribute.
        $result .= 'VERSION:' . $this->_version . $this->_newline;

        foreach ($this->_attributes as $attribute) {
            $name = $attribute['name'];
            if ($name == 'VERSION') {
                // Already done.
                continue;
            }

            $params_str = '';
            $params = $attribute['params'];
            if ($params) {
                foreach ($params as $param_name => $param_value) {
                    // Skip CHARSET for iCalendar 2.0 data, not
                    // allowed.
                    if ($param_name == 'CHARSET' && $this->_version == '2.0') {
                        continue;
                    }
                    $params_str .= ";$param_name=$param_value";
                }
            }

            $value = $attribute['value'];
            switch ($name) {
            // Date fields.
            case 'COMPLETED':
            case 'CREATED':
            case 'DCREATED':
            case 'LAST-MODIFIED':
                $value = $this->_exportDateTime($value);
                break;

            case 'DTEND':
            case 'DTSTART':
            case 'DTSTAMP':
            case 'DUE':
            case 'AALARM':
            case 'RECURRENCE-ID':
                if (isset($params['VALUE'])) {
                    if ($params['VALUE'] == 'DATE') {
                        $value = $this->_exportDate($value);
                    } else {
                        $value = $this->_exportDateTime($value);
                    }
                } else {
                    $value = $this->_exportDateTime($value);
                }
                break;

            // Comma seperated dates.
            case 'EXDATE':
            case 'RDATE':
                $dates = array();
                foreach ($value as $date) {
                    if (isset($params['VALUE'])) {
                        if ($params['VALUE'] == 'DATE') {
                            $dates[] = $this->_exportDate($date);
                        } elseif ($params['VALUE'] == 'PERIOD') {
                            $dates[] = $this->_exportPeriod($date);
                        } else {
                            $dates[] = $this->_exportDateTime($date);
                        }
                    } else {
                        $dates[] = $this->_exportDateTime($date);
                    }
                }
                $value = implode(',', $dates);
                break;

            case 'TRIGGER':
                if (isset($params['VALUE'])) {
                    if ($params['VALUE'] == 'DATE-TIME') {
                        $value = $this->_exportDateTime($value);
                    } elseif ($params['VALUE'] == 'DURATION') {
                        $value = $this->_exportDuration($value);
                    }
                } else {
                    $value = $this->_exportDuration($value);
                }
                break;

            // Duration fields.
            case 'DURATION':
                $value = $this->_exportDuration($value);
                break;

            // Period of time fields.
            case 'FREEBUSY':
                $value_str = '';
                foreach ($value as $period) {
                    $value_str .= empty($value_str) ? '' : ',';
                    $value_str .= $this->_exportPeriod($period);
                }
                $value = $value_str;
                break;

            // UTC offset fields.
            case 'TZOFFSETFROM':
            case 'TZOFFSETTO':
                $value = $this->_exportUtcOffset($value);
                break;

            // Integer fields.
            case 'PERCENT-COMPLETE':
            case 'PRIORITY':
            case 'REPEAT':
            case 'SEQUENCE':
                $value = "$value";
                break;

            // Geo fields.
            case 'GEO':
                $value = $value['latitude'] . ',' . $value['longitude'];
                break;

            // Recurrence fields.
            case 'EXRULE':
            case 'RRULE':
                break;

            default:
                // As of rfc 2426 2.4.2 semi-colon, comma, and colon
                // must be escaped. Exclude MAILTO: though. This is a hack!
                $value = str_replace(array(';', ':', ',', 'MAILTO\\:'),
                                     array('\\;', '\\:', '\\,', 'MAILTO:'),
                                     $value);
                break;
            }

            if (!empty($params['ENCODING']) &&
                $params['ENCODING'] == 'QUOTED-PRINTABLE' && strlen(trim($value)) > 0) {
                $value = str_replace("\r", '', $value);
                $result .= "$name$params_str:=" . $this->_newline
                    . $this->_quotedPrintableEncode($value)
                    . $this->_newline;
            } else {
                $attr_string = "$name$params_str:$value";
                $result .= $this->_foldLine($attr_string) . $this->_newline;
            }
        }

        foreach ($this->_components as $component) {
            $result .= $component->exportvCalendar();
        }

        return $result . 'END:' . $base . $this->_newline;
    }

    /**
     * Parse a UTC Offset field.
     */
    function _parseUtcOffset($text)
    {
        $offset = array();
        if (preg_match('/(\+|-)([0-9]{2})([0-9]{2})([0-9]{2})?/', $text, $timeParts)) {
            $offset['ahead']  = (bool)($timeParts[1] == '+');
            $offset['hour']   = intval($timeParts[2]);
            $offset['minute'] = intval($timeParts[3]);
            if (isset($timeParts[4])) {
                $offset['second'] = intval($timeParts[4]);
            }
            return $offset;
        } else {
            return false;
        }
    }

    /**
     * Export a UTC Offset field.
     */
    function _exportUtcOffset($value)
    {
        $offset = $value['ahead'] ? '+' : '-';
        $offset .= sprintf('%02d%02d',
                           $value['hour'], $value['minute']);
        if (isset($value['second'])) {
            $offset .= sprintf('%02d', $value['second']);
        }

        return $offset;
    }

    /**
     * Parse a Time Period field.
     */
    function _parsePeriod($text)
    {
        $periodParts = explode('/', $text);

        $start = $this->_parseDateTime($periodParts[0]);

        if ($duration = $this->_parseDuration($periodParts[1])) {
            return array('start' => $start, 'duration' => $duration);
        } elseif ($end = $this->_parseDateTime($periodParts[1])) {
            return array('start' => $start, 'end' => $end);
        }
    }

    /**
     * Export a Time Period field.
     */
    function _exportPeriod($value)
    {
        $period = $this->_exportDateTime($value['start']);
        $period .= '/';
        if (isset($value['duration'])) {
            $period .= $this->_exportDuration($value['duration']);
        } else {
            $period .= $this->_exportDateTime($value['end']);
        }
        return $period;
    }

    /**
     * Parse a DateTime field into a unix timestamp.
     */
    function _parseDateTime($text)
    {
        $dateParts = explode('T', $text);
        if (count($dateParts) != 2 && !empty($text)) {
            // Not a datetime field but may be just a date field.
            if (!$date = $this->_parseDate($text)) {
                return $date;
            }
            return @gmmktime(0, 0, 0, $date['month'], $date['mday'], $date['year']);
        }

        if (!$date = $this->_parseDate($dateParts[0])) {
            return $date;
        }
        if (!$time = $this->_parseTime($dateParts[1])) {
            return $time;
        }

        if ($time['zone'] == 'UTC') {
            return @gmmktime($time['hour'], $time['minute'], $time['second'],
                             $date['month'], $date['mday'], $date['year']);
        } else {
            return @mktime($time['hour'], $time['minute'], $time['second'],
                           $date['month'], $date['mday'], $date['year']);
        }
    }

    /**
     * Export a DateTime field.
     */
    function _exportDateTime($value)
    {
        $temp = array();
        if (!is_object($value) && !is_array($value)) {
            $tz = date('O', $value);
            $TZOffset = (3600 * substr($tz, 0, 3)) + (60 * substr(date('O', $value), 3, 2));
            $value -= $TZOffset;

            $temp['zone']   = 'UTC';
            $temp['year']   = date('Y', $value);
            $temp['month']  = date('n', $value);
            $temp['mday']   = date('j', $value);
            $temp['hour']   = date('G', $value);
            $temp['minute'] = date('i', $value);
            $temp['second'] = date('s', $value);
        } else {
            $dateOb = new tx_model_date($value);
            return tx_model_iCalendar::_exportDateTime($dateOb->timestamp());
        }

        return tx_model_iCalendar::_exportDate($temp) . 'T' . tx_model_iCalendar::_exportTime($temp);
    }

    /**
     * Parse a Time field.
     */
    function _parseTime($text)
    {
        if (preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})(Z)?/', $text, $timeParts)) {
            $time['hour'] = intval($timeParts[1]);
            $time['minute'] = intval($timeParts[2]);
            $time['second'] = intval($timeParts[3]);
            if (isset($timeParts[4])) {
                $time['zone'] = 'UTC';
            } else {
                $time['zone'] = 'Local';
            }
            return $time;
        } else {
            return false;
        }
    }

    /**
     * Export a Time field.
     */
    function _exportTime($value)
    {
        $time = sprintf('%02d%02d%02d',
                        $value['hour'], $value['minute'], $value['second']);
        if ($value['zone'] == 'UTC') {
            $time .= 'Z';
        }
        return $time;
    }

    /**
     * Parse a Date field.
     */
    function _parseDate($text)
    {
        $parts = explode('T', $text);
        if (count($parts) == 2) {
            $text = $parts[0];
        }

        if (!preg_match('/^(\d{4})-?(\d{2})-?(\d{2})$/', $text, $match)) {
            return false;
        }

        return array('year' => $match[1],
                     'month' => $match[2],
                     'mday' => $match[3]);
    }

    /**
     * Export a Date field.
     */
    function _exportDate($value)
    {
        if (is_object($value)) {
            $value = array('year' => $value->year, 'month' => $value->month, 'mday' => $value->mday);
        }
        return sprintf('%04d%02d%02d', $value['year'], $value['month'], $value['mday']);
    }

    /**
     * Parse a Duration Value field.
     */
    function _parseDuration($text)
    {
        if (preg_match('/([+]?|[-])P(([0-9]+W)|([0-9]+D)|)(T(([0-9]+H)|([0-9]+M)|([0-9]+S))+)?/', trim($text), $durvalue)) {
            // Weeks.
            $duration = 7 * 86400 * intval($durvalue[3]);

            if (count($durvalue) > 4) {
                // Days.
                $duration += 86400 * intval($durvalue[4]);
            }
            if (count($durvalue) > 5) {
                // Hours.
                $duration += 3600 * intval($durvalue[7]);

                // Mins.
                if (isset($durvalue[8])) {
                    $duration += 60 * intval($durvalue[8]);
                }

                // Secs.
                if (isset($durvalue[9])) {
                    $duration += intval($durvalue[9]);
                }
            }

            // Sign.
            if ($durvalue[1] == "-") {
                $duration *= -1;
            }

            return $duration;
        } else {
            return false;
        }
    }

    /**
     * Export a duration value.
     */
    function _exportDuration($value)
    {
        $duration = '';
        if ($value < 0) {
            $value *= -1;
            $duration .= '-';
        }
        $duration .= 'P';

        $weeks = floor($value / (7 * 86400));
        $value = $value % (7 * 86400);
        if ($weeks) {
            $duration .= $weeks . 'W';
        }

        $days = floor($value / (86400));
        $value = $value % (86400);
        if ($days) {
            $duration .= $days . 'D';
        }

        if ($value) {
            $duration .= 'T';

            $hours = floor($value / 3600);
            $value = $value % 3600;
            if ($hours) {
                $duration .= $hours . 'H';
            }

            $mins = floor($value / 60);
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
    function _foldLine($line)
    {
        $line = preg_replace("/\r\n|\n|\r/", '\n', $line);
        if (strlen($line) > 75) {
            $foldedline = '';
            while (!empty($line)) {
                $maxLine = substr($line, 0, 75);
                $cutPoint = max(60, max(strrpos($maxLine, ';'), strrpos($maxLine, ':')) + 1);

                $foldedline .= (empty($foldedline)) ?
                    substr($line, 0, $cutPoint) :
                    $this->_newline . ' ' . substr($line, 0, $cutPoint);

                $line = (strlen($line) <= $cutPoint) ? '' : substr($line, $cutPoint);
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
     * @param string $input  The string to be encoded.
     *
     * @return string  The quoted-printable encoded string.
     */
    function _quotedPrintableEncode($input = '')
    {
        // If imap_8bit() is available, use it.
        if (function_exists('imap_8bit')) {
            return imap_8bit($input);
        }

        // Rather dumb replacment: just encode everything.
        $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                     'A', 'B', 'C', 'D', 'E', 'F');

        $output = '';
        $len = strlen($input);
        for ($i = 0; $i < $len; ++$i) {
            $c = substr($input, $i, 1);
            $dec = ord($c);
            $output .= '=' . $hex[floor($dec / 16)] . $hex[floor($dec % 16)];
            if (($i + 1) % 25 == 0) {
                $output .= "=\r\n";
            }
        }
        return $output;
    }

}
