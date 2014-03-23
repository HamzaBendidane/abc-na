<?php

class Cfe_Rest_Server extends Zend_Rest_Server {
    const FORMAT_XML = 1;
    const FORMAT_JSON = 2;
    const FORMAT_RAW = 3;

    protected $responseFormat = self::FORMAT_XML;
    protected $isReturnRaw = null;
    
    protected $_cacheRequest = array();

    public function setFormat($format) {
        if (!in_array($format, array(self::FORMAT_XML, self::FORMAT_JSON, self::FORMAT_RAW))) {
            throw new Zend_Rest_Server_Exception('invalid output format');
        }
        $this->responseFormat = $format;
    }

    public function getFormat() {
        return $this->responseFormat;
    }

    protected function getDocTag($tag , $docBlock ,$getValue = false) {
        $found = array();
        $regExp = ( ! $getValue ) ? '/\v\h*+\*\h++\@'.$tag.'\h++(?P<type>[a-zA-Z][a-zA-Z0-9_]*+)\h++\$(?P<name>[a-zA-Z][a-zA-Z0-9_]*+)(\h++\V*)?/'
                 : '/\v\h*+\*\h++\@'.$tag.'\h++(?P<value>[a-zA-Z0-9_]*+)(\h++\V*)?/' ;
        
        if (preg_match_all($regExp, $docBlock, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if ( ! $getValue ) {
                    $found[$match['name']] = $match['type'];
                } else {
                    $found['value'] = $match['value'];
                }
            }
        }
        return $found;
    }
    
    protected function getContentTypeHeader() {
        if ($this->_isReturnRaw()) {
            return null;
        }
        switch ($this->responseFormat) {
            case self::FORMAT_XML:
                return 'Content-Type: text/xml';
            case self::FORMAT_JSON:
                return 'Content-Type: application/json; charset="utf-8"';
            default:
                return null;
        }
    }

    protected function cast($var, $type) {
        switch (strtolower($type)) {
            case 'string':
            case 'str':
            case 'mixed':
                return (string) $var;
            case 'integer':
            case 'int':
                return intval($var);
            case 'float':
            case 'double':
                return floatval($var);
            case 'bool':
            case 'boolean':
                return (strtolower($var) === 'true' || $var === '1');
            case 'array':
                return is_array($var) ? $var : json_decode($var, true);
            case 'object':
            case 'stdClass':
                return json_decode($var);
            default:
                if (!@class_exists($type, true)) {
                    return $var;
                } elseif (method_exists($type, '__set_state')) {
                    $array = json_decode($var, true);
                    if (is_null($array)) {
                        $array = array();
                    }
                    return call_user_func(array($type, '__set_state'), $array);
                }
                return json_decode($var);
        }
    }

    protected function _handle($request) {
        $this->isReturnRaw = null;
        if (isset($request['method'])) {
            $this->_method = $request['method'];
            if (isset($this->_functions[$this->_method])) {
                if ($this->_functions[$this->_method] instanceof Zend_Server_Reflection_Function 
                        || $this->_functions[$this->_method] instanceof Zend_Server_Reflection_Method 
                        && $this->_functions[$this->_method]->isPublic()) {
                    $request_keys = array_keys($request);
                    array_walk($request_keys, array(__CLASS__, "lowerCase"));
                    $request = array_combine($request_keys, $request);

                    $func_args = $this->_functions[$this->_method]->getParameters();
                    $types = array();
                    if ($docBlock = $this->_functions[$this->_method]->getDocComment()) {
                        $types = $this->getDocTag('param' , $docBlock);
                        $this->_cacheRequest = $this->getDocTag('cache', $docBlock, true);
                    }
                    $calling_args = array();
                    $missing_args = array();
                    foreach ($func_args as $arg) {
                        $name = $arg->getName();
                        if (isset($request[strtolower($name)])) {
                            $type = array_key_exists($name, $types) ? $types[$name] : 'mixed';
                            $calling_args[] = $this->cast($request[strtolower($name)], $type);
                        } elseif ($arg->isOptional()) {
                            $calling_args[] = $arg->getDefaultValue();
                        } else {
                            $missing_args[] = $name;
                        }
                    }

                    foreach ($request as $key => $value) {
                        if (substr($key, 0, 3) == 'arg') {
                            $key = str_replace('arg', '', $key);
                            $calling_args[$key] = $value;
                            if (($index = array_search($key, $missing_args)) !== false) {
                                unset($missing_args[$index]);
                            }
                        }
                    }

                    // Sort arguments by key -- @see ZF-2279
                    ksort($calling_args);

                    $result = false;
                    if (count($calling_args) < count($func_args)) {
                        require_once 'Zend/Rest/Server/Exception.php';
                        throw new Zend_Rest_Server_Exception('Invalid Method Call to ' . $this->_method . '. Missing argument(s): ' . implode(', ', $missing_args) . '.', 400);
                    }

                    if (!$result && $this->_functions[$this->_method] instanceof Zend_Server_Reflection_Method) {
                        // Get class
                        $class = $this->_functions[$this->_method]->getDeclaringClass()->getName();

                        if ($this->_functions[$this->_method]->isStatic()) {
                            // for some reason, invokeArgs() does not work the same as
                            // invoke(), and expects the first argument to be an object.
                            // So, using a callback if the method is static.
                            $result = $this->_callStaticMethod($class, $calling_args);
                        } else {
                            // Object method
                            $result = $this->_callObjectMethod($class, $calling_args);
                        }
                    } elseif (!$result) {
                        $result = call_user_func_array($this->_functions[$this->_method]->getName(), $calling_args); //$this->_functions[$this->_method]->invokeArgs($calling_args);
                    }
                } else {
                    require_once "Zend/Rest/Server/Exception.php";
                    throw new Zend_Rest_Server_Exception("Unknown Method '$this->_method'.", 404);
                }
            } else {
                require_once "Zend/Rest/Server/Exception.php";
                throw new Zend_Rest_Server_Exception("Unknown Method '$this->_method'.", 404);
            }
        } else {
            require_once "Zend/Rest/Server/Exception.php";
            throw new Zend_Rest_Server_Exception("No Method Specified.", 404);
        }
        return $result;
    }

    protected function formatResponse($result) {
        if ($result instanceof Exception) {
            return $this->_handleException($result);
        }
        if (is_array($result) || is_object($result)) {
            return $this->_handleStruct($result);
        } else {
            return $this->_handleScalar($result);
        }
    }

    protected function cleanRequestFromMagicQuotes($request) {
        // Gestion des stripslashes pour un array passé en paramètre
        if (get_magic_quotes_gpc()) {
            foreach ($request as $key => $value) {
                $request[$key] = (!is_array($value))?stripslashes($value):array_map('stripslashes', $value);
            }
        }
        
        return $request;
    }

    /**
     * Implement Zend_Server_Interface::handle()
     *
     * @param  array $request
     * @throws Zend_Rest_Server_Exception
     * @return string|void
     */
    public function handle($request = false) {
        //		if($this->responseFormat == self::FORMAT_XML) {
        //			return parent::handle($request);
        //		}

        if (!$request) {
            $request = $_REQUEST;
        }
        $request = $this->cleanRequestFromMagicQuotes($request);
        try {
            $result = $this->_handle($request);
        } catch (Exception $e) {
            $result = $e;
        }
        $response = $this->formatResponse($result);
        
        if ( !empty($this->_cacheRequest)) {
            $this->setCacheHeaders();
        }
        
        
        $serverModel = new Zend_Controller_Request_Http();
        $server = $serverModel->getServer();
        
        if (isset ($server['HTTP_ORIGIN']))
            $serverOrigine = $server['HTTP_ORIGIN'];
        else
            $serverOrigine = null;

        if (isset ($server['HTTP_ORIGIN']))
          $filtered_url = filter_input(INPUT_SERVER, 'HTTP_ORIGIN', FILTER_SANITIZE_URL);
        else
          $filtered_url = $serverOrigine;
        
        $filtered_url = ($filtered_url)?$filtered_url:"*";
        
        if ($contentTypeHeader = $this->getContentTypeHeader()) {
            $this->_headers[] = $contentTypeHeader;
            $this->_headers[] = "Access-Control-Allow-Origin : ".$filtered_url;
            $this->_headers[] = "Access-Control-Allow-Credentials : true";
            $this->_headers[] = "Access-Control-Allow-Methods: POST";
            $this->_headers[] = "Access-Control-Request-Headers: X-Secret-Request-Header";
            $this->_headers[] = 'P3P:policyref="/w3c/p3p.xml"';
            $this->_headers[] = 'P3P:CP="NOI DSP COR NID CURa ADMa DEVa PSAa PSDa OUR BUS COM INT OTC PUR STA"';
        }
        if (!$this->returnResponse()) {
            if (!headers_sent()) {
                foreach ($this->_headers as $header) {
                    header($header);
                }
            }

            echo $response;
            return;
        }

        return $response;
    }

    /**
     *
     * @param Exception $exception
     * @return string JSON|XML
     */
    protected function _handleException($exception) {
        // Headers to send
        if ($exception instanceof Cfe_Http_Exception) {
            $this->_headers[] = $exception->getHeader();
        } elseif ($exception->getCode() === null || (404 != $exception->getCode())) {
            $this->_headers[] = 'HTTP/1.0 200 Bad Request';
        } else {
            $this->_headers[] = 'HTTP/1.0 404 File Not Found';
        }

        if ($this->responseFormat == self::FORMAT_XML) {
            if (isset($this->_functions[$this->_method])) {
                $function = $this->_functions[$this->_method];
            } elseif (isset($this->_method)) {
                $function = $this->_method;
            } else {
                $function = 'rest';
            }

            if ($function instanceof Zend_Server_Reflection_Method) {
                $class = $function->getDeclaringClass()->getName();
            } else {
                $class = false;
            }

            if ($function instanceof Zend_Server_Reflection_Function_Abstract) {
                $method = $function->getName();
            } else {
                $method = $function;
            }
            $dom = new DOMDocument('1.0', $this->getEncoding());
            if ($class) {
                $xml = $dom->createElement($class);
                $xmlMethod = $dom->createElement($method);
                $xml->appendChild($xmlMethod);
            } else {
                $xml = $dom->createElement($method);
                $xmlMethod = $xml;
            }
            $xml->setAttribute('generator', 'zend');
            $xml->setAttribute('version', '1.0');
            $dom->appendChild($xml);

            $xmlResponse = $dom->createElement('response');
            $xmlMethod->appendChild($xmlResponse);

            if ($exception instanceof Exception) {
                $element = $dom->createElement('message');
                $element->appendChild($dom->createTextNode($exception->getMessage()));
                $xmlResponse->appendChild($element);
            } elseif (($exception !== null) || 'rest' == $function) {
                $xmlResponse->appendChild($dom->createElement('message', 'An unknown error occured. Please try again.'));
            } else {
                $xmlResponse->appendChild($dom->createElement('message', 'Call to ' . $method . ' failed.'));
            }

            $xmlMethod->appendChild($xmlResponse);
            $xmlMethod->appendChild($dom->createElement('status', 'failed'));


            return $dom->saveXML();
        } elseif ($this->responseFormat == self::FORMAT_JSON) {
            return json_encode(array('status' => 0, 'message' => $exception->getMessage(), 'error' => $exception->getCode()));
        }
        return '';
    }

    /**
     * Handle an array or object result
     *
     * @param array|object $struct Result Value
     * @return string JSON|XML
     */
    protected function _handleStruct($struct) {
        if ($this->responseFormat == self::FORMAT_XML) {
            return parent::_handleStruct($struct);
        } elseif ($this->responseFormat == self::FORMAT_JSON) {
            if (is_object($struct) && method_exists($struct, 'jsonSerialize')) {
                return json_encode($struct->jsonSerialize());
            }
            return json_encode($struct);
        }
        return (string) $struct;
    }

    protected function _isReturnRaw() {
        if (is_null($this->isReturnRaw)) {
            if (!$this->_method || !array_key_exists($this->_method, $this->_functions)) {
                return false;
            }
            $this->isReturnRaw = (($docBlock = $this->_functions[$this->_method]->getDocComment())
                    && (preg_match('/\v\h*+\*\h++\@return\h++raw/', $docBlock) == 1));
        }
        return $this->isReturnRaw;
    }

    /**
     * Handle a single value
     *
     * @param string|int|boolean $value Result value
     * @return string JSON|XML
     */
    protected function _handleScalar($value) {
        if (is_string($value) && $this->_isReturnRaw()) {
            return $value;
        }
        if ($this->responseFormat == self::FORMAT_XML) {
            return parent::_handleScalar($value);
        } elseif ($this->responseFormat == self::FORMAT_JSON) {
            return json_encode($value);
        }
        return (string) $value;
    }

    /**
     * Call a static class method and return the result
     *
     * @param  string $class
     * @param  array $args
     * @return mixed
     */
    protected function _callStaticMethod($class, array $args) {
        return call_user_func_array(array($class, $this->_functions[$this->_method]->getName()), $args);
    }

    /**
     * Call an instance method of an object
     *
     * @param  string $class
     * @param  array $args
     * @return mixed
     * @throws Zend_Rest_Server_Exception For invalid class name
     */
    protected function _callObjectMethod($class, array $args) {
        try {
            if ($this->_functions[$this->_method]->getDeclaringClass()->getConstructor()) {
                $object = $this->_functions[$this->_method]->getDeclaringClass()->newInstanceArgs($this->_args);
            } else {
                $object = $this->_functions[$this->_method]->getDeclaringClass()->newInstance();
            }
        } catch (Exception $e) {
            require_once 'Zend/Rest/Server/Exception.php';
            throw new Zend_Rest_Server_Exception('Error instantiating class ' . $class .
                    ' to invoke method ' . $this->_functions[$this->_method]->getName() .
                    ' (' . $e->getMessage() . ') ',
                    500, $e);
        }
        return call_user_func_array(array($object, $this->_functions[$this->_method]->getName()), $args);
        return $this->_functions[$this->_method]->invokeArgs($object, $args);
    }

    /**
     * Sets Page header cache
     */
    protected function setCacheHeaders() {
        $this->_cacheRequest['value'] = is_numeric($this->_cacheRequest['value']) ? $this->_cacheRequest['value'] : 36000;
        $this->_headers[] = 'Cache-Control: public';
        $this->_headers[] = 'expires: '.date('r',time()+$this->_cacheRequest['value']);
        $this->_headers[] = 'Cache-Control: max-age='.$this->_cacheRequest['value'];
    }
}

