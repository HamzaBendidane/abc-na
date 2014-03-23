<?php
   // Define path to application directory
    defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
        
    defined('LIBRARY_PATH')
        || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/../library'));

    defined('CONFIG_PATH')
        || define('CONFIG_PATH', realpath(dirname(__FILE__).'/../conf'));
    
    defined('APPLICATION_CONFIG_PATH')
        || define('APPLICATION_CONFIG_PATH', APPLICATION_PATH.'/configs');
        
    defined('LOG_PATH')
        || define('LOG_PATH', realpath(dirname(__FILE__) . '/../cache/logs'));
    
    if(!defined('TEST_ROOT_PATH'))
    {
        define('TEST_ROOT_PATH', realpath(dirname(__FILE__)));
        define('TEST_BUILD_PATH', realpath(dirname(__FILE__)));
    }
        // Ensure library/ is on include_path
        set_include_path(implode(PATH_SEPARATOR, array(
        LIBRARY_PATH,
        get_include_path(),
        )));

        set_include_path(implode(PATH_SEPARATOR, array(
        TEST_ROOT_PATH,
        get_include_path(),
        )));

// Zend_Application
require_once 'Zend/Loader/Autoloader.php';
require_once 'Cfe/Utils.php';
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    Cfe_Utils::getPlatform(),
    APPLICATION_CONFIG_PATH . '/application.ini'
);
// will use zend autoloader if no other loader works
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

$application->getBootstrap()->_initDbAdaptersToRegistry();
//system time zone
$env = Cfe_Utils::getPlatform();
$conf = new Cfe_Config_Ini(APPLICATION_CONFIG_PATH."/application.ini", $env);
date_default_timezone_set($conf->system->timezone);