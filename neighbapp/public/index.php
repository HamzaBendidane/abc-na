<?php
try{
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
        || (getenv('LOG_PATH') ? define('LOG_PATH', getenv('LOG_PATH')) : define('LOG_PATH', realpath(dirname(__FILE__) . '/../cache/logs')));
        
    defined('LANGUAGES_PATH')
        || define('LANGUAGES_PATH', realpath(dirname(__FILE__) . '/../languages'));
        
    defined('SOURCE_PATH')
        || define('SOURCE_PATH', realpath(dirname(__FILE__) . '/../'));
        
    // Ensure library/ is on include_path
    set_include_path(implode(PATH_SEPARATOR, array(
        LIBRARY_PATH,
        get_include_path(),
    )));
    /** Zend_Application */
    require_once 'Cfe/Utils.php';
    require_once 'Zend/Application.php';
    
    // Create application, bootstrap, and run
    $application = new Zend_Application(
        Cfe_Utils::getPlatform(),
        APPLICATION_CONFIG_PATH . '/application.ini'
    );
    // will use zend autoloader if no other loader works
    Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
    
    $application->bootstrap()
                ->run();
} catch (Exception $e) {
    echo $e;
}
