<?php
/**
 *
 * Controller permetant de creer rapidement un server SOAP
 * example:
 * <code>
 * <?php
 * class soapController extends Cfe_Controller_Soap {}
 * </code>
 *
 * @author sk
 *
 */
require_once 'Cfe/Controller/Secured.php';

abstract class Cfe_Controller_Soap extends Cfe_Controller_Secured
{
    protected $service;
    protected $class;

    /**
     *
     * this is the list of services to expose
     * the keys are the services names the values are the Class implementing them.
     * @var array ($service => $className)
     */
    protected $services;
    /**
     * (non-PHPdoc)
     * @see libs/clean/Zend/Controller/Zend_Controller_Action::init()
     */
    public function getServices()
    {
        if(!isset($this->services)) {
            $resourceTypes = $this->getInvokeArg('bootstrap')->getResourceLoader()->getResourceTypes();
            $path = $resourceTypes['service']['path'].'/Soap';
            $handle = opendir($path);
            while (($file = readdir($handle)) !== false) {
                if(substr($file, -4) == '.php') {
                    $this->services[substr($file,0, -4)] = $resourceTypes['service']['namespace'].'_Soap_'.substr($file,0, -4);
                }
            }
        }
        return $this->services;
    }
    /**
     *
     * default Action handler
     * display the services list
     */
    public function indexAction ()
    {
        $services = array();
        foreach (array_keys($this->getServices()) as $service) {
            $services[$service] = array(
            	'wsdl' => $this->getUrl(null, null, 'wsdl',array('service' => $service)),
            	'stub' => array(
            		'PHP' => $this->getUrl(null, null, 'stub',array('service' => $service, 'language' => 'php')),
            ),
            	'doc' => $this->getUrl(null, null, 'doc',array('service' => $service)),
            	'test' => $this->getUrl(null, null, 'test',array('service' => $service)),
            );
        }
        $this->view->assign('services', $services);
    }
    /**
     *
     * return the currently selected service
     */
    protected function getService ()
    {
        if(!isset($this->service)) {
            $this->service = $this->getRequest()->getParam('service', null);
            if(is_null($this->service)) {
                if(isset($this->defaultService)) {
                    $this->service = $this->defaultService;
                } else {
                    $this->service = key($this->getServices());
                }
            }
            try {
                $className = $this->getInvokeArg('bootstrap')->getAppNamespace().'_Service_Soap_'.$this->service;
                if (!class_exists($className, true)) {
                    throw new Exception('', 0);
                }
            } catch (Exception $e) {
                throw new Zend_Exception('service ['.$this->service.'] inconnu', 0);
            }
        }
        return $this->service;
    }
    /**
     *
     * return the class name to handle the currently selected service
     * @return string
     */
    protected function getClass ()
    {
        if(!isset($this->class)) {
            $this->class = $this->getInvokeArg('bootstrap')->getAppNamespace().'_Service_Soap_'. $this->getService();
        }
        return $this->class;
    }
    /**
     *
     * display the wsdl of the selected service
     */
    public function wsdlAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        $class = $this->getClass();
        $autodiscover = new Zend_Soap_AutoDiscover(
        'Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
        $autodiscover->setClass($class);
        $autodiscover->setUri($this->getServerFullUrl());
        $this->getResponse()->setHeader('Content-Type', 'text/xml');
        $this->getResponse()->setBody($autodiscover->toXml());
        //$autodiscover->handle();
    }
    /**
     *
     * generate a stub of the selected service
     */
    public function stubAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        //$this->getResponse()->setHeader('Content-Type', 'application/x-httpd-php; charset=UTF-8');
        //$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="Cfe_Stub_'.$this->getService().'.php"');
        $class = $this->getClass();
        $language = $this->getRequest()->getParam('language', 'php');
        if ($language != 'php') {
            throw new Zend_Exception('not implemented yet', 0);
        }
        $rClass = new Zend_Reflection_Class($class);
        $codeGenerator = Cfe_CodeGenerator_Php_Class::fromReflection($rClass);
        $codeGenerator = $codeGenerator->getFlattened();
        $wsdl = 'http://'.$_SERVER['HTTP_HOST'].'/soap.php?wsdl='.$this->getService();
        try {
            list($user, $password) = $this->getAuth();
            $wsdl = "http://$user:$password@".$_SERVER['HTTP_HOST'].'/soap.php?wsdl='.$this->getService();
        } catch (Exception $e) {}
        try {
            if(strncmp(file_get_contents($wsdl), '<?xml', 5) != 0) {
                throw new Exception();
            }
            $client = new Soapclient($wsdl, array());
        } catch (Exception $e) {
            $wsdl = $this->getWsdlFullUrl();
        }
        if(strncmp(@file_get_contents($wsdl), '<?xml', 5) != 0) {
            $classMap = array();
        } else {
            $classMap = $this->getClassmap($wsdl);
        }
        $wsdl = preg_replace('/\.((ppr)|(dev)|(stg)|(int))\.([a-zA-Z0-9]+\.[a-zA-Z0-9]+\/)/', '.\'.\$_ENV[\'PLATFORM_URL\'].\'$6',$wsdl, 1);
        $wsdl = preg_replace('~(http://)[^:@/]+:[^:@/]+@~', '$1',$wsdl, 1);
        $name = 'Cfe_Stub_'.$this->getService();
        $stub = $codeGenerator->getSoapStub($wsdl, $name, $classMap);
        $this->getResponse()->setBody('<?php'."\n\n".$stub->generate());
    }
    /**
     *
     * display the documentation of the selected service
     */
    public function docAction ()
    {
        $view = $this->view;
        $services = array();
        foreach ($this->getServices() as $service => $class) {
            $services[$service] = $this->getUrl(null, null, 'doc',array('service' => $service), true);
        }
        $view->assign('services', $services);
        $service = new stdClass();
        $service->name = $this->getService();
        $service->stubPHP = $this->getStubFullUrl('php');
        $service->wsdl = $this->getWsdlFullUrl();
        $service->test = $this->getTestFullUrl();

        $view->assign('service', $service);

        $rClass = new Zend_Reflection_Class($this->getClass());
        $class = new stdClass();
        $class->name = $this->getService();
        $class->fullDescription = '';
        // @codeCoverageIgnoreStart
        // seems to be broken on Zend Framwork side, it will work as soon as getDocBlock() is corrected
        try {
            $rClassDoc = $rClass->getDocblock();
            $class->fullDescription = $rClassDoc->getShortDescription() ."<br/>\n". $rClassDoc->getLongDescription();
        } catch (Exception $e) {};
        // @codeCoverageIgnoreEnd
        foreach($rClass->getMethods() as $rMethod){
            $method = new stdClass();
            /* @var $rMethod Zend_Reflection_Method */
            if(!$rMethod->isPublic() || ($rMethod->getName() == '__construct') || $rMethod->getDocblock()->getTag('hidden')) {
                continue;
            }
            $params = array();
            foreach ($rMethod->getParameters() as $rParam) {
                /* @var $rParam Zend_Reflection_Parameter */
                $params[] = $rParam->getType().' $'.$rParam->getName().(($rParam->isOptional() && $rParam->isDefaultValueAvailable())?(' = '.$rParam->getDefaultValue()):'');
            }
            $method->name=$rMethod->getName();
            $method->fullName=$rMethod->getName().'('.implode(', ', $params).')';
            $docBlock = $rMethod->getDocblock();
            $method->fullDescriptionExt = implode("<br/>\n", array(
            $docBlock->getShortDescription(),
            $docBlock->getLongDescription(),
            ));
            $params = array();
            foreach ($docBlock->getTags('param') as $rParam2) {
                /* @var $rParam2 Zend_Reflection_Docblock_Tag_Param */
                $params[] = $rParam2->getType() . ' ' . $rParam2->getVariableName().' '. $rParam2->getDescription();
            }
            $method->paramExt = implode("<br/>\n", $params);
            /* @var $rReturn Zend_Reflection_Docblock_Tag_Return */
            $rReturn = $docBlock->getTag('return');
            $method->returnExt = $rReturn?($rReturn->getType(). ' '.$rReturn->getDescription()):'';
            $rThrows = $docBlock->getTag('throws');
            $method->throwsExt = $rThrows?$rThrows->getDescription():'';
            $method->testExt = '';
            $class->methods[] = $method;
        }
        foreach($rClass->getConstants() as $name => $value){
            $constant = new stdClass();
            $constant->name = $name;
            $constant->value = $value;
            $class->constants[] = $constant;
        }
        $this->view->assign('class', $class);
    }
    /**
     *
     * @todo
     */
    // @codeCoverageIgnoreStart
    public function testAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        echo 'not implemented yet';
    }
    // @codeCoverageIgnoreEnd

    protected function getClassmap($wsdlUrl) {
        $md5Wsdl = md5($wsdlUrl);
        $key = __CLASS__ . ':' . md5($wsdlUrl);
        if (function_exists ( 'apc_fetch' )) {
            $classMap = apc_fetch ( $key, $success );
            if ($success) {
                // @codeCoverageIgnoreStart
                // APC won't work on cli ...
                return $classMap;
                // @codeCoverageIgnoreEnd
            }
        }
        $option = array();
        if (Cfe_Utils::getPlatform() == 'dev') {
            $option['cache_wsdl'] = WSDL_CACHE_NONE;
            ini_set("soap.wsdl_cache_enabled", "0");
        }
        $classMap = array();
        if($client = new Soapclient($wsdlUrl, $option)) {
            foreach ($client->__getTypes() as $type) {
                if(strncmp($type, 'struct ',7) == 0) {
                    preg_match('/^struct\s([^\s]+)\s\{/', $type, $match);
                    $type = $match[1];
                    $classMap[$type] = $type;
                }
            }
            if(function_exists('apc_store')) {
                apc_store ( $key, $classMap, 300 );
            }
        }
        return $classMap;
    }

    /**
     *
     * handle the soap requests
     */
    public function serverAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        $class = $this->getClass();
        $option = array();
        if (Cfe_Utils::getPlatform() == 'dev') {
            $option['cache_wsdl'] = WSDL_CACHE_NONE;
            ini_set("soap.wsdl_cache_enabled", "0");
        }
        $wsdl = $this->getRequest()->getParam('wsdlUrl',$this->getWsdlFullUrl());
        try {
            list($user, $password) = $this->getAuth();
            $wsdl = preg_replace('~^http://~', "http://$user:$password@", $wsdl);
        } catch (Exception $e) {}
        $option['classmap'] = $this->getClassmap($wsdl);
       	$soap = new SoapServer($wsdl, $option);
        $soap->setClass($class);
        try {
            $soap->handle($this->getRequest()->getParam('rawRequest', file_get_contents('php://input')));
        } catch (Exception $e) {
            $soap->fault(get_class($e) . ':'.$e->getCode(), $e->getMessage(),'',$e->getTraceAsString());
        }
    }

    protected function getWsdlFullUrl() {
        return $this->getUrl(null, null, 'wsdl',array('service' => $this->getService()), true);
    }
    protected function getServerFullUrl() {
        return $this->getUrl(null, null, 'server',array('service' => $this->getService()), true);
    }
    protected function getDocFullUrl() {
        return $this->getUrl(null, null, 'doc',array('service' => $this->getService()), true);
    }
    protected function getStubFullUrl($language = 'Php') {
        return $this->getUrl(null, null, 'stub',array('service' => $this->getService(), 'language' => $language), true);
    }
    protected function getTestFullUrl() {
        return $this->getUrl(null, null, 'test',array('service' => $this->getService()), true);
    }
}