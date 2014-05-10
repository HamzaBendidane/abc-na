<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function run() {
        // make the config available to everyone
        $config = $this->getOptions();
        date_default_timezone_set($config['system']['timezone']);
        parent::run();
    }

    protected function _initFrontModules() {

        $this->bootstrap('frontController');
        $front = $this->getResource('frontController');
        $front->addModuleDirectory(APPLICATION_PATH . '/modules');
    }

    public function _initDbAdaptersToRegistry() {
        $this->bootstrap('frontController');
        $resource = $this->getPluginResource('multidb');
        $resource->init();

        $adapter1 = $resource->getDb('neighbapp');
        Zend_Registry::set('neighbapp', $adapter1);

        Zend_Db_Table::setDefaultAdapter($adapter1);
    }

    /**
     * Clear route for each module with env "MODULE"
     */
    protected function _initRoutes() {
        
        $frontcontroller = $this->getResource('frontController');
        $route = $frontcontroller->getRouter();
        if (getenv('MODULE')) {
            $pattern = ':controller/:action/*';
            $module = getenv('MODULE');
            $controller = 'login';
            $action = 'index';
        } else {
            $pattern = '/*';
            $module = 'services';
            $controller = 'Rest';
            $action = 'server';
        }

        // default route for one module : without name of module insight URL
        $route->addRoute('default', new Zend_Controller_Router_Route(
                        $pattern, array(
                    'module' => $module,
                    'controller' => $controller,
                    'action' => $action
                        )
        ));
    }

}
