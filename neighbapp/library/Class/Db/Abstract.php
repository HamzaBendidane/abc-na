<?php
class Class_Db_Abstract extends Zend_Db_Table{
    protected function _setupDatabaseAdapter(){
            $this->_db = Zend_Registry::get($this->_adapter);
    }

    protected function connect(){
        require_once 'Zend/Loader/Autoloader.php';
        Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
        $application = new Zend_Application(
                Cfe_Utils::getPlatform(),
                APPLICATION_CONFIG_PATH . '/application.ini'
            );
        $application->getBootstrap()->_initDbAdaptersToRegistry();
    }
    
    protected function disconnect(){
        $db = Zend_Registry::get('neighbapp');
        $db->closeConnection();
    }
}