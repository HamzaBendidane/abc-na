<?php
/**
 *
 * Controller permetant de creer rapidement un server Rest
 * example:
 * <code>
 * <?php
 * class RestController extends Cfe_Controller_Rest {}
 * </code>
 *
 * @author sk
 *
 */
require_once 'Cfe/Controller/Secured.php';

abstract class Cfe_Controller_Rest extends Cfe_Controller_Secured
{
    protected $service;
    protected $class;
    protected $_cache = 0;

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
            $path = $resourceTypes['service']['path'].'/Rest';
            $handle = opendir($path);
            while (($file = readdir($handle)) !== false) {
                if(substr($file, -4) == '.php') {
                    $this->services[substr($file,0, -4)] = $resourceTypes['service']['namespace'].'_Rest_'.substr($file,0, -4);
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
            	'wadl' => $this->getUrl(null, null, 'wadl',array('service' => $service)),
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
                $className = $this->getInvokeArg('bootstrap')->getAppNamespace().'_Service_Rest_'.$this->service;
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
            $this->class = $this->getInvokeArg('bootstrap')->getAppNamespace().'_Service_Rest_'. $this->getService();
        }
        return $this->class;
    }
    /**
     *
     * display the wsdl of the selected service
     */
    public function wadlAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        echo 'not implemented yet';
    }
    /**
     *
     * generate a stub of the selected service
     */
    public function stubAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        echo 'not implemented yet';
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
        $service->wadl = $this->getWadlFullUrl();
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
            $docBlock = $rMethod->getDocblock();
            $paramsDoc = array();
            foreach ($docBlock->getTags('param') as $rParam2) {
                /* @var $rParam2 Zend_Reflection_Docblock_Tag_Param */
                if(strpos($rParam2->getDescription(), '@hidden') === false) {
                $paramsDoc[$rParam2->getVariableName()] = $rParam2->getType() . ' ' . $rParam2->getVariableName().' '. $rParam2->getDescription();
                }
            }
            $method->paramExt = implode("<br/>\n", $paramsDoc);

            $params = array();
        	foreach ($rMethod->getParameters() as $rParam) {
                /* @var $rParam Zend_Reflection_Parameter */
                if (array_key_exists('$'.$rParam->getName(), $paramsDoc)) {
                    $params[] = $rParam->getType().' $'.$rParam->getName().(($rParam->isOptional() && $rParam->isDefaultValueAvailable())?(' = '.var_export($rParam->getDefaultValue(),true)):'');
                }
            }
            $method->name=$rMethod->getName();
            $method->fullName=$rMethod->getName().'('.implode(', ', $params).')';
            $method->fullDescriptionExt = implode("<br/>\n", array(
            $docBlock->getShortDescription(),
            $docBlock->getLongDescription(),
            ));
            /* @var $rReturn Zend_Reflection_Docblock_Tag_Return */
            $rReturn = $docBlock->getTag('return');
            $method->returnExt = $rReturn?($rReturn->getType(). ' '.$rReturn->getDescription()):'';
            $rThrows = $docBlock->getTag('throws');
            $method->throwsExt = $rThrows?$rThrows->getDescription():'';
            $rTest = $docBlock->getTag('test');       
            $method->testExt = $rTest?$this->getUrl(null, null, $rTest->getDescription(),array(), true):'';
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

    /**
     *
     * handle the REST requests
     */
    public function serverAction ()
    {
       
        $this->_helper->viewRenderer->setNoRender();
        $class = $this->getClass();

        $this->getMethodTags();

        $option = array();
        $server = new Cfe_Rest_Server();
        $server->setFormat($this->getFormat());
        $server->setClass($class);
		$request = $this->getRequest()->getParams();
		//if content-type is json we add the decode json of raw post data to the variables to handle
		$request_content_type = strtolower($this->getRequest()->getHeader('Content-Type'));
		if(strpos($request_content_type,'application/json') !== false){
			$json_var = json_decode(file_get_contents('php://input'), true);
			if(is_array($json_var)){
				$request = array_merge($request, $json_var);
			}
		}
                $server->handle($request);
               
    }

    protected function getFormat() {
        return Cfe_Rest_Server::FORMAT_XML;
    }

    protected function getWadlFullUrl() {
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

    /**
     * Gets Method Tags
     */
    protected function getMethodTags() {
        $params = $this->getRequest()->getParams();
        $method = $params['method'];

        $reflected = new Zend_Reflection_Method($this->getClass(), $method);

        $docblock = $reflected->getDocblock();

        if ($cacheTag = $docblock->getTag('cache')) {
            $cacheValue = trim($cacheTag->getDescription());
            $this->_cache = is_numeric($cacheValue) ? $cacheValue : 36000;
            $this->setCacheHeaders();
        }
    }

    /**
     * Sets Page header cache
     */
    protected function setCacheHeaders() {
        $this->getResponse()->setHeader('Cache-Control', 'public', true);
        $this->getResponse()->setHeader('expires', date('r',time()+$this->_cache) );
        $this->getResponse()->setHeader('Cache-Control', 'max-age=' . $this->_cache);
    }
}
