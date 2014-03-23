<?php
/**
 * Cfe Framework
 *
 * @category   Cfe
 * @package    Cfe_Config
 * @copyright  Copyright (c) sk
 */

/**
 * @see Zend_Config_Writer
 */
require_once 'Zend/Config/Writer/Ini.php';

/**
 * @category   Cfe
 * @package    Cfe_Config
 * @copyright  Copyright (c) sk
 */
class Cfe_Config_Writer_Ini extends Zend_Config_Writer_Ini
{
    /**
     * Prepare a value for INI
     *
     * @param  mixed $value
     * @return string
     */
    protected function _prepareValue($value)
    {
        if (is_integer($value) || is_float($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return ($value ? 'true' : 'false');
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_string($value)) {
            return '"'.addcslashes($value, '"\\').'"';
        } else {
            // @codeCoverageIgnoreStart
            // can happen only if $value is not int, float, bool, null nor string
            // this is not possible as it is never called that way from other functions
            /** @see Zend_Config_Exception */
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception('It should not happen : preparing a value that isn\'t int, float, bool, null nor string');
            // @codeCoverageIgnoreEnd
        }
    }
}
