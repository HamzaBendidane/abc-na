<?php
require_once 'Zend/Application.php';
require_once 'Cfe/Config/Ini.php';

class Cfe_Application extends Zend_Application {
	/**
     * Load configuration file of options
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
    protected function _loadConfig($file)
    {
        $environment = $this->getEnvironment();
        $suffix      = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		if($suffix == 'ini') {
			$config = new Cfe_Config_Ini($file, $environment);
			return $config->toArray();
		}
		return parent::_loadConfig($file);
    }
}

