<?php

require_once 'Zend/CodeGenerator/Php/Class.php';
require_once 'Cfe/CodeGenerator/Php/Docblock.php';
require_once 'Cfe/CodeGenerator/Php/Property.php';
require_once 'Cfe/CodeGenerator/Php/Method.php';

class Cfe_CodeGenerator_Php_Class extends Zend_CodeGenerator_Php_Class
{

    const CONSTRUCTOR_SOAP_DOC = 'Les options suivantes sont reconnues:
\'soap_version\' (\'soapVersion\') : int
version du protocole SOAP à utiliser (SOAP_1_1 ou SOAP_1_2).
\'classmap\' (\'classMap\') : array
doit être utilisé pour faire correspondre des types WSDL à des classes PHP.
Cette option doit être un tableau avec comme clés les types WSDL et comme
valeurs les noms des classes PHP.
\'autoClassMap\' : boolean
permet de définir automatiquement les classe PHP à utiliser comme étant les
classes portant le même nom que les types WSDL
\'cache\' : boolean
permet d\'activer le cache tel que definit par l\'editeur du webservice
chaque fonction peut être ou non caché chacune avec une durée de validité
specifique selon les definitions données par les directives @cache du
service.
\'cacheBackend\' : Zend_Cache_Backend
backend a utiliser pour gerer le cache.
utilisé uniquement si \'cache\' est activé
si le cache est activé mais que le backend n\'est pas fournit c\'est celui
fournit par Cfe_Cache_Helper::getBackend() qui sera utilisé.
\'shared_signature\' : string
chaîne quelconque, les services utilisant la même chaîne partagent les mêmes données cachées
\'encoding\' : string
encodage interne des caractères (l\'encodage externe est toujours UTF-8).
\'login\' et \'password\' : string, string
login et password pour l\'authentification HTTP.
\'proxy_host\', \'proxy_port\', \'proxy_login\', et \'proxy_password\' : string, int,
                                                             string et string
utilisés pour une connexion HTTP via un proxy.
\'local_cert\' et \'passphrase\' : string, string
options d\'authentification HTTPS.
\'compression\' : int
options de compression ; c\'est une combinaison entre SOAP_COMPRESSION_ACCEPT,
SOAP_COMPRESSION_GZIP et SOAP_COMPRESSION_DEFLATE';

    const CONSTRUCTOR_SOAP_BODY = 'if(array_key_exists(\'autoClassMap\', $options) && $options[\'autoClassMap\']) {
	unset($options[\'classMap\']);
	unset($options[\'autoClassMap\']);
	$options[\'classmap\'] = #CLASS_MAP;
}
if(array_key_exists(\'utf8safe\', $options) && $options[\'utf8safe\']) {
	$this->utf8safe = true;
}
$methodsCacheTime = #METHODS_CACHE_TIME;
$this->soapClient = new SoapClient(\'#WSDL_URI\', $options);
if(!empty($methodsCacheTime) && array_key_exists(\'cache\', $options) && $options[\'cache\']) {
    if(array_key_exists(\'cacheBackend\', $options) && $options[\'cacheBackend\'] instanceof Zend_Cache_Backend_Interface) {
        $backend = $options[\'cacheBackend\'];
    } else {
        $backend = Cfe_Cache_Helper::getBackend();
    }
    
    if (array_key_exists(\'shared_signature\', $options)) {
        $frontendOptions = array(\'cache_by_default\' => false, \'cached_entity\' => $this->soapClient,
                                 \'methods_cache_time\' => $methodsCacheTime , \'shared_signature\' => $options[\'shared_signature\'] );
    } else {
        $frontendOptions = array(\'cache_by_default\' => false, \'cached_entity\' => $this->soapClient, \'methods_cache_time\' => $methodsCacheTime);
    }
    
    $cachedObject = Zend_Cache::factory(\'Cfe_Cache_Frontend_Object\', $backend, $frontendOptions, array(), true, true);
    $this->soapClient = $cachedObject;
}';

    const EXCEPTION_HANDLER_SOAP_BODY = 'if($this->utf8safe) {
	foreach($params as &$param) {
		$param = self::_utf8Encode($param);
	}
}
try {
	return $this->soapClient->__call($method, $params);
} catch (SoapFault $e) {
    if(strstr($e->faultcode, \':\')) {
        list($exceptionClass, $code) = explode(\':\', $e->faultcode);
        if(@class_exists($exception, true)) {
            throw new $exceptionClass($e->faultstring, $code);
	    }
    }
    throw $e;
}';

const UTF8ENCODE_BODY = 'if(is_string($param)) {
	if(utf8_encode(utf8_decode($param)) != $param) {
		return utf8_encode($param);
	}
} elseif(is_array($param)) {
	$result = array();
	foreach ($param as $key => &$value) {
		$result[self::_utf8Encode($key)] = self::_utf8Encode($value);
	}
	return $result;
} elseif(is_object($param)) {
	foreach (get_object_vars($param) as $key => $value) {
		$param->$key = self::_utf8Encode($value);
	}
	return $param;
}
return $param;';

    protected $_constants = array();

    /**
     *
     * generate the SOAP stub class for this class
     * @param string $wsdlUri
     * @return Cfe_CodeGenerator_Php_Class
     */
    public function getSoapStub ($wsdlUri, $name='stub', array $classMap = array()) {
        $allWarnings = array();
        $object = clone($this);

        $object->_extendedClass = '';
        $object->_implementedInterfaces = array();
        $object->_properties = array(new Cfe_CodeGenerator_Php_Property(array('name' => 'utf8safe', 'visibility' => Zend_CodeGenerator_Php_Property::VISIBILITY_PROTECTED, 'defaultValue' => false)));
        $object->_methods = array();
        $construct = new Zend_CodeGenerator_Php_Method();
        $construct->setName('__construct');
        $object->setMethod($construct);
        $object->setSourceDirty(true);
        $indent = $this->getIndentation();

        $utf8Encode = new Zend_CodeGenerator_Php_Method();
        $utf8Encode->setName('_utf8Encode');
        $utf8Encode->setParameters(array(
        new Zend_CodeGenerator_Php_Parameter(array('name' => 'param', 'passedByReference' => true))));
        $utf8Encode->setBody(str_replace("\t", $indent, self::UTF8ENCODE_BODY));
        $utf8Encode->setVisibility(Zend_CodeGenerator_Php_Method::VISIBILITY_PROTECTED);
        $utf8Encode->setStatic(true);
        $object->setMethod($utf8Encode);

        $call = new Zend_CodeGenerator_Php_Method();
        $call->setName('call');
        $call->setParameters(array(
        new Zend_CodeGenerator_Php_Parameter(array('name' => 'method')),
        new Zend_CodeGenerator_Php_Parameter(array('name' => 'params', 'type' => 'array'))));
        $call->setBody(str_replace("\t", $indent, self::EXCEPTION_HANDLER_SOAP_BODY));
        $call->setVisibility(Zend_CodeGenerator_Php_Method::VISIBILITY_PROTECTED);
        $object->setMethod($call);

        $object->setSourceDirty(true);

        $object->setName($name);

        foreach ($this->getProperties() as $property) {
            /* @var $property Cfe_CodeGenerator_Php_Property */
            if(($property->isConst() && !$property->isHidden()) || $property->isInline()) {
                $object->setProperty($property);
            }
        }
        $methodsCacheTime = array();
        $types = array();
        foreach ($this->getMethods() as $method) {
            /* @var $method Cfe_CodeGenerator_Php_Method */
            if(($method->getVisibility() == Zend_CodeGenerator_Php_Method::VISIBILITY_PUBLIC
            && strncmp($method->getName(), '__', 2) != 0) || $method->isInline()) {
                if(!$method->isInline() && ($cache=$method->getCache()) ) {
                    $methodsCacheTime[$method->getName()] = $cache;
                }
                $newMethod = clone($method);
                if($method->isHidden()) {
                    $newMethod->setVisibility(Zend_CodeGenerator_Php_Method::VISIBILITY_PROTECTED);
                }
                $newMethod->setStatic(false);
                $functionName = $method->getName();
                $list = array();
                foreach ($method->getParameters() as $parameter) {
                    /* @var $parameter Zend_CodeGenerator_Php_Parameter */
                    $list[] = '$'.$parameter->getName();
                }
                $parameters = 'array('.implode(', ', $list).')';
                if(!$method->isInline()) {
                    $body = "return \$this->call('$functionName', $parameters);";
                    $newMethod->setBody($body);
                    $newMethod->setSourceDirty(true);
                    $object->setMethod($newMethod);
                } else {
                    if($warnings = $method->getInlinableWarnings($this)) {
                        $allWarnings[] = $method->getName().'()'.PHP_EOL.implode(PHP_EOL, $warnings);
                    } else
                    $object->setMethod($newMethod);
                }
                if($returnType = $method->getReturnType()) {
                    $types[$returnType] = $returnType;
                }
                foreach ($method->getParameters() as $param) {
                    /* @var $param Zend_CodeGenerator_Php_Parameter */
                    if($type = $param->getType()) {
                        $types[$type] = $type;
                    }
                }
            }
            foreach ($types as $type) {
                if(!in_array($type, array('int', 'integer', 'bool', 'boolean', 'float', 'double', 'real', 'string', 'array', 'void'))) {
                    if(substr($type,-2) == '[]') {
                        $type = substr($type, 0, -2);
                    }
                    $classMap[$type] = $type;
                }
            }
        }
        $parameter = new Zend_CodeGenerator_Php_Parameter();
        $parameter->setName('options');
        $parameter->setType('array');
        $parameter->setDefaultValue(new Zend_CodeGenerator_Php_Parameter_DefaultValue('array()'));
        $construct->setParameter($parameter);

        $tag = new Zend_CodeGenerator_Php_Docblock_Tag_Param();
        $tag->setParamName($parameter->getName());
        $tag->setDatatype($parameter->getType());

        $docblock = new Zend_CodeGenerator_Php_Docblock();
        $docblock->setShortDescription('Constructor');
        if($allWarnings) {
            $docblock->setLongDescription(self::CONSTRUCTOR_SOAP_DOC.PHP_EOL.PHP_EOL.implode(PHP_EOL, $allWarnings));
        } else {
            $docblock->setLongDescription(self::CONSTRUCTOR_SOAP_DOC);
        }
        $docblock->setTag($tag);
        $construct->setDocblock($docblock);
        $construct->setBody(str_replace("\t",
        $indent,
        str_replace('#WSDL_URI',
        $wsdlUri,
        str_replace('#CLASS_MAP',
        var_export($classMap, true),
        str_replace('#METHODS_CACHE_TIME',
        var_export($methodsCacheTime, true),
        self::CONSTRUCTOR_SOAP_BODY)))));
        return $object;
    }

    /**
     * fromReflection() - build a Code Generation PHP Object from a Class Reflection
     *
     * @param Zend_Reflection_Class $reflectionClass
     * @return Cfe_CodeGenerator_Php_Class
     */
    public static function fromReflection(Zend_Reflection_Class $reflectionClass)
    {
        /*
         * this is Almost the same function as the parent but because the self
         * is not dynamic it has to be duplicated (static won't work in this case)
         */
        $class = new self();

        $class->setSourceContent($class->getSourceContent());
        $class->setSourceDirty(false);

        if ($reflectionClass->getDocComment() != '') {
            $class->setDocblock(Cfe_CodeGenerator_Php_Docblock::fromReflection($reflectionClass->getDocblock()));
        }

        $class->setAbstract($reflectionClass->isAbstract());
        $class->setName($reflectionClass->getName());

        if ($parentClass = $reflectionClass->getParentClass()) {
            $class->setExtendedClass($parentClass->getName());
            $interfaces = array_diff($reflectionClass->getInterfaces(), $parentClass->getInterfaces());
        } else {
            $interfaces = $reflectionClass->getInterfaces();
        }

        $interfaceNames = array();
        foreach($interfaces AS $interface) {
            $interfaceNames[] = $interface->getName();
        }

        $class->setImplementedInterfaces($interfaceNames);

        $properties = array();
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getDeclaringClass()->getName() == $class->getName()) {
                $properties[] = Cfe_CodeGenerator_Php_Property::fromReflection($reflectionProperty);
            }
        }
        $class->setProperties($properties);

        $methods = array();
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass()->getName() == $class->getName()) {
                $methods[] = Cfe_CodeGenerator_Php_Method::fromReflection($reflectionMethod);
            }
        }
        $class->setMethods($methods);

        foreach ($reflectionClass->getConstants() as $name => $value) {
            $class->setConstant($name, $value);
        }

        return $class;
    }

    /**
     * generate the code string
     *
     * @return string
     */
    public function generate()
    {
        if (!$this->isSourceDirty()) {
            return $this->getSourceContent();
        }

        $output = '';

        if (null !== ($docblock = $this->getDocblock())) {
            $docblock->setIndentation('');
            $output .= $docblock->generate();
        }

        if ($this->isAbstract()) {
            $output .= 'abstract ';
        }

        $output .= 'class ' . $this->getName();

        if ( !empty( $this->_extendedClass) ) {
            $output .= ' extends ' . $this->_extendedClass;
        }

        $implemented = $this->getImplementedInterfaces();
        if (!empty($implemented)) {
            $output .= ' implements ' . implode(', ', $implemented);
        }

        $output .= self::LINE_FEED . '{' . self::LINE_FEED . self::LINE_FEED;

        /*
         * [patch for constant]
         * added by Olivier Parmentier for constant generation
         * theoriticaly it should be handled by properties ... but it doesn't seem to work.
         */

        foreach ($this->getConstants() as $name => $value) {
            if(is_bool($value)) {
                $valueString = 'true';
            } elseif (is_string($value)) {
                $valueString = "'$value'";
            } else {
                $valueString = $value;
            }
            $output .= $this->getIndentation() . 'const ' . $name . ' = ' . $valueString . ';' . self::LINE_FEED;
        }
        $output .= self::LINE_FEED;
        /*
         * [end of patch for constant]
         */

        $properties = $this->getProperties();
        if (!empty($properties)) {
            foreach ($properties as $property) {
                $output .= $property->generate() . self::LINE_FEED . self::LINE_FEED;
            }
        }

        $methods = $this->getMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                $output .= $method->generate() . self::LINE_FEED;
            }
        }

        $output .= '}' . self::LINE_FEED;

        return $output;
    }

    /**
     *
     * add or modify a constant
     * @param string $name
     * @param scalar $value
     */
    public function setConstant($name, $value) {
        $this->_constants[$name] = $value;
    }

    /**
     *
     * redefine all the constants
     * @param array $array ($name => $value)
     */
    public function setConstants($array) {
        $this->_constants = $array;
    }

    /**
     *
     * return the value of a constant
     * @param string $name
     * @return mixed
     */
    public function getConstant($name) {
        return $this->_constants[$name];
    }

    /**
     *
     * get the list of all constants
     * @return array ($name => $value)
     */
    public function getConstants() {
        return $this->_constants;
    }

    /**
     * (non-PHPdoc)
     * @see Zend_CodeGenerator_Php_Abstract::getIndentation()
     */
    public function getIndentation() {
        $indent = parent::getIndentation();
        return is_int($indent) ? str_repeat(' ',$indent) : $indent;
    }

    public function getFlattened() {
        if($parent = $this->getExtendedClass()) {
            $parentClass = Cfe_CodeGenerator_Php_Class::fromReflection(new Zend_Reflection_Class($parent));
            $parentClass->getFlattened();
            $result = clone($this);
            $result->_extendedClass = '';
            $result->_constants = array_merge($parentClass->_constants, $result->_constants);
            foreach ($parentClass->getProperties() as $property) {
                /* @var $method Cfe_CodeGenerator_Php_Property */
                if($property->getVisibility() != Zend_CodeGenerator_Php_Property::VISIBILITY_PRIVATE
                && !array_key_exists($property->getName(), $result->_properties)) {
                    $result->setProperty($property);
                }
            }
            foreach ($parentClass->getMethods() as $method) {
                /* @var $method Cfe_CodeGenerator_Php_Method */
                if($method->getVisibility() != Cfe_CodeGenerator_Php_Method::VISIBILITY_PRIVATE
                && !array_key_exists($method->getName(), $result->_methods)) {
                    $result->setMethod($method);
                }
            }
            return $result;
        }
        return $this;
    }
}
