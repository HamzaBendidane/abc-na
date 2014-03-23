<?php
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('LIBRARY_PATH')
    || define('LIBRARY_PATH',realpath(dirname(__FILE__).'/../library'));
    
defined('APPLICATION_CONFIG_PATH')
    || define('APPLICATION_CONFIG_PATH', APPLICATION_PATH.'/configs');

defined('CONFIG_PATH')
    || define('CONFIG_PATH', realpath(dirname(__FILE__).'/../conf'));

defined('LOG_PATH')
    || define('LOG_PATH', realpath(dirname(__FILE__) . '/../cache/logs'));

set_include_path(implode(PATH_SEPARATOR, array(
    $_ENV['LIB_INCLUDE_PATH'],
    get_include_path(),
)));

set_include_path(implode(PATH_SEPARATOR, array(LIBRARY_PATH, get_include_path(),)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
Cfe_Config_Helper::setPath(CONFIG_PATH);

require_once APPLICATION_CONFIG_PATH.'/const.php';

$handler = new Cfe_Soap_Handler();
$handler->handle();