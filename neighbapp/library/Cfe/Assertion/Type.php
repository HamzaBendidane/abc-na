<?php

require_once 'Cfe/Assertion/Exception.php';

/**
 * expose methods to check the validity of parameters types
 * @author sk
 *
 */
class Cfe_Assertion_Type {

	const T_INTEGER		= "integer";
	const T_STRING		= "string";
	const T_BOOLEAN		= "boolean";
	const T_DOUBLE		= "double";
	const T_ARRAY		= "array";
	const T_NULL		= "NULL";

	protected static $TYPES = array(
			self::T_INTEGER,
			self::T_STRING,
			self::T_BOOLEAN,
			self::T_DOUBLE,
			self::T_ARRAY,
			self::T_NULL,
	);

	/**
	 * if $object is not an instance of $type throw an exception
	 * @param object $object
	 * @param string $type
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertInstanceOf($object, $type) {
		self::assertString ( $type );
		if (! ($object instanceof $type)) {
			return self::throwException ( $type , $object);
		}
	}
	/**
	 * if $object is not an object throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertObject($object) {
		if (! is_object ( $object )) {
			return self::throwException ( 'object' , $object);
		}
	}
	/**
	 * if $object is not a string throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertString($object) {
		if (! is_string ( $object )) {
			return self::throwException ( 'string' , $object);
		}
	}
	/**
	 * if $object is not an integer throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertInt($object) {
		if (! is_int ( $object )) {
			return self::throwException ( 'int' , $object);
		}
	}
	/**
	 * if $object is not a float throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertFloat($object) {
		if (! is_float ( $object )) {
			return self::throwException ( 'float' , $object);
		}
	}
	/**
	 * if $object is not a boolean throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertBool($object) {
		if (! is_bool ( $object )) {
			return self::throwException ( 'bool' , $object);
		}
	}
	/**
	 * if $object is not null throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertNull($object) {
		if (! is_null ( $object )) {
			return self::throwException ( 'null' , $object);
		}
	}
	/**
	 * if $object is not a float nor an integer nor a string that represent either a float or an integer
     * throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertNumeric($object) {
		if (! is_numeric ( $object )) {
			return self::throwException ( 'numeric' , $object);
		}
	}
	/**
	 * if $object is not an Array throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertArray($object) {
		if (! is_array ( $object )) {
			return self::throwException ( 'array' , $object);
		}
	}
	/**
	 * if $object is not a function nor a method throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertFunction($object) {
		if (! is_callable ( $object )) {
			return self::throwException ( 'function' , $object);
		}
	}
	/**
	 * if $object is not a boolean nor an integer nor a float nor a string throw an exception
	 * @param unknown_type $object
	 * @throws Cfe_Assertion_Exception
	 */
	public static function assertScalar($object) {
		if (! is_scalar ( $object )) {
			return self::throwException ( 'scalar' , $object);
		}
	}
	protected static function throwException($type, $object) {
		$param = self::getStringRepresentation($object);
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
		throw new Cfe_Assertion_Exception ( sprintf ( 'ASSERTION FAILED: %s is not %s in %s [%s:%d] called from %s [%s:%d]', $param, $type, $calleeString, $assert ['file'], $assert ['line'], $callerString, $callee ['file'], $callee ['line'] ), Cfe_Assertion_Exception::INVALID_TYPE );
	}

    protected static function getStringRepresentation($object) {
        switch(gettype($object)) {
            case 'object':
                return '#'.get_class($object);
            case 'ressource':
                return '#resource:'.get_resource_type($object);
            case 'string':
                if(strlen($object) > 30) {
                    return '"'.substr($object, 30).'..."';
                } else {
                    return '"'.$object.'"';
                }
            case 'integer':
            case 'double':
            case 'boolean':
            case 'NULL':
                return var_export($object, true);
            case 'array':
                return '#array:'.count($object);
            default:
                return '#unknownType';
        }
    }



    /**
     * Object is a valid token (only alpha-num, '-' or '_' )
     *
     * @param string $object
     * @param int $length
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertToken($object, $length){
    	if(!self::isToken($object, $length)){
    		self::throwException('token', $object);
    	}
    }

    protected function isToken($object, $length){
    	self::assertString($object);
    	self::assertInt($length);

    	Cfe_Assertion_Value::assertGreaterThan($length, 0);

    	return preg_match("~^[a-zA-Z0-9\-_]{{$length}}$~", $object);
    }

    /**
     * Object is token (see assertToken) of NULL
     *
     * @param string|NULL $object
     * @param int $length
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertTokenOrNull($object, $length){
    	if(!is_null($object) && !self::isToken($object, $length)){
    		self::throwException('valid token or NULL', $object);
    	}
    }

    /**
     * Object is a valid IPv4 address
     *
     * @param string $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv4($object){
    	if(!self::isIPv4($object))
    		self::throwException('valid IPv4 address', $object);
    }

    protected function isIPv4($object){
    	self::assertString($object);
		require_once 'Cfe/Utils/Ip/V4.php';
    	return preg_match(Cfe_Utils_Ip_V4::REGEXP, $object, $matches);
    }

    /**
     * Object is a valid IPv4 address or NULL
     * @param string|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv4OrNull($object){
    	if(!is_null($object) && !self::isIPv4($object)){
    		self::throwException('valid IPv4 address or NULL', $object);
    	}
    }

    /**
     * Object is a valid IPv6 address
     *
     * @param string $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv6($object){
    	if(!self::isIPv6($object)){
    		self::throwException('valid IPv6 address', $object);
    	}
    }

    protected function isIPv6($object){
    	self::assertString($object);

		require_once 'Cfe/Utils/Ip/V6.php';
    	return preg_match(Cfe_Utils_Ip_V6::REGEXP, $object, $matches);
    }

    /**
     * Object is a valid IPv6 address or NULL
     *
     * @param string|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv6OrNull($object){
    	if(!is_null($object) && !self::isIPv6($object)){
    		self::throwException('valid IPv6 address or NULL', $object);
    	}
    }


    /**
     * Object is a valid IP address
     *
     * @param string $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIP($object){
    	if(!self::isIPv4($object) && !self::isIPv6($object)){
    		self::throwException('valid IP address', $object);
    	}
    }

    public function assertIPOrNull($object){
    	if(!is_null($object) && !self::isIPv4($object) && !self::isIPv6($object)){
    		self::throwException('valid IP address or NULL', $object);
    	}
    }

    /**
     * Object is a valid IPv4 CIDR
     *
     * @param string $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv4CIDR($object){
    	if(!self::isIPv4CIDR($object)){
    		self::throwException('valid IPv4 CIDR address', $object);
    	}
    }

    protected function isIPv4CIDR($object){
    	self::assertString($object);

    	$parts = explode("/", $object);

    	if(preg_match(Cfe_Utils_Ip_V4::REGEXP, $parts[0], $matches)){
    		$parts[1] = '22';
    		if(isset($parts[1]) && !preg_match('~^[0-9]+$~', $parts[1])){
    			return false;
    		}

    		return (intval($parts[1]) <= 32);
    	}

    	return false;
    }

    /**
     * Object is a valid IPv4 CIDR or NULL
     *
     * @param string|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv4CIDROrNull($object){
    	if(!is_null($object) && !self::isIPv4CIDR($object)){
    		self::throwException('valid IPv6 CIDR or NULL', $object);
    	}
    }

    /**
     * Object is a valid IPv6 CIDR
     *
     * @param string $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv6CIDR($object){
    	if(!self::isIPv6CIDR($object)){
    		self::throwException('valid IPv6 CIDR address', $object);
    	}
    }

    protected function isIPv6CIDR($object){
    	self::assertString($object);

    	$parts = explode("/", $object);

    	if(preg_match(Cfe_Utils_Ip_V6::REGEXP, $parts[0], $matches)){
    		if(isset($parts[1]) && !preg_match('~^[0-9]+$~', $parts[1])){
    			return false;
    		}

    		return (intval($parts[1]) <= 128);
    	}

    	return false;
    }

    /**
     * Object is a valid IPv6 CIDR or NULL
     *
     * @param string|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPv6CIDROrNull($object){
    	if(!is_null($object) && !self::isIPv6CIDR($object)){
    		self::throwException('valid IPv6 CIDR or NULL', $object);
    	}
    }

    /**
     * Object is a valid IP CIDR
     *
     * @param string $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPCIDR($object){
    	if(!self::isIPv4CIDR($object) && !self::isIPv6CIDR($object)){
    		self::throwException('valid IP address', $object);
    	}
    }

    /**
     * Object is a valid IP CIDR or NULL
     *
     * @param string|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIPCIDROrNull($object){
    	if(!is_null($object) && !self::isIPv4CIDR($object) && !self::isIPv6CIDR($object)){
    		self::throwException('valid IPv6 CIDR or NULL', $object);
    	}
    }

    /**
     * Object is of one of the given types
     *
     * @param mixed $object
     * @param int $type
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertType($object, $types){
    	if(!self::matchType($object, $types)){
    		self::throwException(implode("|", $types), $object);
    	}
    }

    protected function matchType(&$object, &$types){
    	$type = gettype($object);
    	if($type == 'object'){
    		foreach(array_diff($types, self::$TYPES) as $class){
    			if ($object instanceof $class) {
    				return true;
    			}
    		}

    		return false;
    	}
    	return in_array($type, $types);
    }


    /**
     * Object is an integer or NULL
     * @param int|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertIntOrNull($object){
    	if(is_null($object)){
    		return;
    	}

    	if(!is_int($object)){
    		return self::throwException ( 'int' , $object);
    	}
    }

    /**
     * Object is a string or NULL
     *
     * @param string|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertStringOrNull($object){
    	if(is_null($object)){
    		return;
    	}

    	if(!is_string($object)){
    		return self::throwException ( 'string|NULL' , $object);
    	}
    }

    /**
     * Object is a boolean or NULL
     *
     * @param bool|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertBoolOrNull($object){
    	if(is_null($object)){
    		return;
    	}

    	if(!is_bool($object)){
    		return self::throwException ( 'bool|NULL' , $object);
    	}
    }

    /**
     * Object is a float or NULL
     *
     * @param float|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertFloatOrNull($object){
    	if(is_null($object)){
    		return;
    	}

    	if(!is_float($object)){
    		return self::throwException ( 'float|NULL' , $object);
    	}
    }

    /**
     * Object is an array or NULL
     *
     * @param array|NULL $object
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertArrayOrNull($object){
    	if(is_null($object)){
    		return;
    	}

    	if(!is_array($object)){
    		return self::throwException ( 'array|NULL' , $object);
    	}
    }

    /**
     * Object is instance of the given class or NULL
     *
     * @param mixed|NULL $object
     * @param string $class
     *
     * @throws Cfe_Assertion_Exception
     */
    public function assertInstanceOfOrNull($object, $class){
    	if(is_null($object)){
    		return;
    	}

    	self::assertString ( $class );
    	if (! ($object instanceof $class)) {
    		return self::throwException ( "$class|NULL" , $object);
    	}
    }
}