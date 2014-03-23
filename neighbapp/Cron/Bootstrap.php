<?php
defined('SERVER_TIMEZONE')
|| define('SERVER_TIMEZONE', 'Europe/Paris');

defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('LIBRARY_PATH')
|| define('LIBRARY_PATH',realpath(dirname(__FILE__).'/../library'));

defined('CRON_PATH')
|| define('CRON_PATH',realpath(dirname(__FILE__).'/../Cron'));

defined('CONFIG_PATH')
|| define('CONFIG_PATH', realpath(dirname(__FILE__).'/../conf'));

defined('LOG_PATH')
|| define('LOG_PATH', realpath(dirname(__FILE__).'/../cache/logs'));

defined('APPLICATION_CONFIG_PATH')
|| define('APPLICATION_CONFIG_PATH', APPLICATION_PATH.'/configs');

set_include_path(implode(PATH_SEPARATOR, array(
realpath(dirname(__FILE__) . '/../library'),
get_include_path(),
)));
set_include_path(implode(PATH_SEPARATOR, array(
CRON_PATH,
get_include_path(),
)));



require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
$application = new Zend_Application(
        Cfe_Utils::getPlatform(),
        APPLICATION_CONFIG_PATH . '/application.ini'
    );
$application->getBootstrap()->_initDbAdaptersToRegistry();