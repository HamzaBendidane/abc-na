<?php

require_once 'Cfe/Assertion/Type.php';
require_once 'Cfe/Assertion/Exception.php';

/**
 * expose methods to check the validity of parameters values
 *
 */
class Cfe_Assertion_Value {
    /**
     * if $object < $min or  $object > $max throw an exception
     * @param numeric $object
     * @param numeric $min
     * @param numeric $max must be > $min
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertRange($object, $min, $max) {
        Cfe_Assertion_Type::assertNumeric($object);
        Cfe_Assertion_Type::assertNumeric($min);
        Cfe_Assertion_Type::assertNumeric($max);
        self::assertLowerThan($min,$max);
        if ($object < $min) {
            return self::throwException("$object < $min");
        }
        if ($object > $max) {
            return self::throwException("$object > $max");
        }
    }
    /**
     * if $object is not <= $value throw an exception
     * @param numeric $object
     * @param numeric $value
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertLowerThanOrEquals($object, $value) {
        Cfe_Assertion_Type::assertNumeric($object);
        Cfe_Assertion_Type::assertNumeric($value);
        if ($object > $value) {
            return self::throwException("$object > $value");
        }
    }
    /**
     * if $object != true throw an exception with the given message as explanation
     * @param bool $object
     * @param string $message
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertTrue($object, $message) {
        Cfe_Assertion_Type::assertBool($object);
        Cfe_Assertion_Type::assertString($message);
        if (!$object) {
            return self::throwException($message);
        }
    }
    /**
     * if $object != false throw an exception with the given message as explanation
     * @param bool $object
     * @param string $message
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertFalse($object, $message) {
        Cfe_Assertion_Type::assertBool($object);
        Cfe_Assertion_Type::assertString($message);
        if ($object) {
            return self::throwException($message);
        }
    }
    /**
     * if $object >= $value throw an exception
     * @param numeric $object
     * @param numeric $value
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertLowerThan($object, $value) {
        Cfe_Assertion_Type::assertNumeric($object);
        Cfe_Assertion_Type::assertNumeric($value);
        if ($object >= $value) {
            return self::throwException("$object >= $value");
        }
    }
    /**
     * if $object < $value throw an exception
     * @param numeric $object
     * @param numeric $value
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertGreaterThanOrEquals($object, $value) {
        Cfe_Assertion_Type::assertNumeric($object);
        Cfe_Assertion_Type::assertNumeric($value);
        if ($object < $value) {
            return self::throwException("$object < $value");
        }
    }
    /**
     * if $object <= $value throw an exception
     * @param numeric $object
     * @param numeric $value
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertGreaterThan($object, $value) {
        Cfe_Assertion_Type::assertNumeric($object);
        Cfe_Assertion_Type::assertNumeric($value);
        if ($object <= $value) {
            return self::throwException("$object <= $value");
        }
    }
    /**
     * if $object != $value throw an exception
     * @param mixed $object
     * @param mixed $value
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertEquals($object, $value) {
        if ($object != $value) {
            return self::throwException("$object != $value");
        }
    }
    /**
     * if $object == $value throw an exception
     * @param mixed $object
     * @param mixed $value
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertNotEquals($object, $value) {
        if ($object == $value) {
            return self::throwException("$object == $value");
        }
    }
    /**
     * if $function($object) == false throw an exception
     * @param mixed $object
     * @param function $function
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertCallback($object, $function) {
        Cfe_Assertion_Type::assertFunction($function);
        if (!call_user_func($function, $object)) {
            is_callable($function, false, $functionName);
            return self::throwException(''.var_export($object, true)." not validated by $functionName");
        }
    }
    /**
     * if $validator->isValid($object) == false throw an exception
     * @param mixed $object
     * @param Zend_Validate_Interface $validator
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertValidate($object, Zend_Validate_Interface $validator) {
        if(!$validator->isValid($object)) {
            return self::throwException(implode(', ', $validator->getMessages()));
        }
    }
    /**
     * if $pattern is not found in $object throw an exception
     * @param string $object
     * @param string $pattern
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertRegexp($object, $pattern) {
        Cfe_Assertion_Type::assertString($object);
        Cfe_Assertion_Type::assertString($pattern);
        $matches = @preg_match($pattern, $object);
        if($matches === false) {
            return self::throwExceptionUpper("invalid pattern $pattern");
        }
        if($matches === 0) {
            return self::throwException("$object doesn't match against $pattern");
        }
    }
    /**
     * if $object is not a valid email according to RFC2822 throw an exception
     * @param string $object
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertEmailRFC2822($object) {
        Cfe_Assertion_Type::assertString($object);
        $pattern = '/(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/';
        $matches = preg_match($pattern, $object);
        if($matches == 0) {
            return self::throwException("$object doesn't match against RFC 2822 Email pattern");
        }
    }
    /**
     * if $object is not a standard valid email throw an exception
     * @param string $object
	 * @throws Cfe_Assertion_Exception
     */
    public static function assertEmailStandard($object) {
        Cfe_Assertion_Type::assertString($object);
        $pattern = '/[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|name|aero|asia|jobs|museum|xxx)\b/';
        $matches = preg_match($pattern, $object);
        if($matches == 0) {
            return self::throwException("$object doesn't match against Classical Email pattern");
        }
    }
    /**
     * throw an exception if it's not an integer or a string representing an integer
     *
     * @param mixed $object
     * @throws Cfe_Assertion_Exception
     */
    public static function assertInt($object) {
        if(!is_int($object)) {
            if(!is_string($object) || ((string)intval($object))!==$object) {
                return self::throwException("$object is not an integer");
            }
        }
    }

    /**
     * throw an exception if the key doesn't exist
     *
     * @param mixed $key
     * @param array $array
     * @throws Cfe_Assertion_Exception
     */
    public static function assertKeyExists($key, $array) {
    	Cfe_Assertion_Type::assertArray($array);
    	if(!array_key_exists($key, $array)) {
            return self::throwException("$key is not a key in ".str_replace("\n", '', print_r($array, true)));
        }
    }

    protected static function throwExceptionUpper($message) {
        return self::throwException($message);
    }
    protected static function throwException($message) {
		$calls = debug_backtrace ();
		array_shift ( $calls );
		$assert = array_shift ( $calls );
		$callee = array_shift ( $calls );
		$caller = array_shift ( $calls );
		if($callee['file'] == '' && $callee['line'] == 0) {
		    $callee['file'] = $caller['file'];
		    $callee['line'] = $caller['line'];
		}
		if($caller['file'] == '' && $caller['line'] == 0) {
		    $tmp = array_shift ( $calls );
		    $caller['file'] = $tmp['file'];
		    $caller['line'] = $tmp['line'];
		}
		$calleeString = (array_key_exists ( 'class', $callee ) ? ($callee ['class'] . '::') : '') . $callee ['function'];
		$callerString = (array_key_exists ( 'class', $caller ) ? ($caller ['class'] . '::') : '') . $caller ['function'];
		throw new Cfe_Assertion_Exception ( sprintf ( 'ASSERTION FAILED: %s in %s [%s:%d] called from %s [%s:%d]', $message, $calleeString, $assert ['file'], $assert ['line'], $callerString, $callee ['file'], $callee ['line'] ), Cfe_Assertion_Exception::INVALID_VALUE );
    }
}