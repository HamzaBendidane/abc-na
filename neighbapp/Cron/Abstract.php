<?php
require_once 'Bootstrap.php';
class Cron_Abstract {
    
    /**
     * Extends pour l'application web
     * @var bool
     */
    protected $web = false;
    
    /**
     * The logger used in the API.
     * @var Zend_Logger
     */
    protected $logger;
    
    /**
     * If true, we log, if false we do not
     * @var boolean
     */
    protected $logging = false;
    
    /**
     * The prefix of every log message
     * @var string
     */
    protected $logPrefix = '';
    
    /**
     * An instance of memcache to store the data of the sessions.
     * @var Zend_Cache
     */
    protected $_memcacheInstance;
    
    /**
     * Constructor of the API, construct the logger.
     * @return void
     */
    public function __construct() {
        
        if(file_exists(CONFIG_PATH.'/log.ini')) {
            Cfe_Config_Helper::setPath(realpath(CONFIG_PATH));
            
            $this->setLogger(Cfe_Log_Helper::getLogger());
            $this->setLogging(true);
        }
    }
    
    /**
     * Say if we log or not
     * 
     * @param boolean $logging
     * @return void
     */
    protected function setLogging($logging) {
        $this->logging = $logging;
    }
    
    /**
     * Set the logger
     * 
     * @param Zend_Logger $logger
     * @return void
     */
    protected function setLogger($logger) {
        $this->logger = $logger;
    }
    
    /**
     * The log function
     * 
     * @param string $str
     * @param int $level
     * @return void
     */
    protected function log($str,$level=Zend_Log::DEBUG) {
        if(!$this->logging)
            return;
        if(!isset($this->logger))
            throw new RealtimeException_Server_Permanent('No logger defined');
        
        if(isset($this->logPrefix))
            $str = $this->logPrefix.$str;
        
        $this->logger->log($str,$level);
    }

    protected function getMedia(){
        $config = new Cfe_Config_Ini(CONFIG_PATH.'/media.ini',Cfe_Utils::getPlatform());
        $config = $config->toArray();
        return $config;
    }

    protected function getCron(){
        $config = new Cfe_Config_Ini(CONFIG_PATH.'/cron.ini',Cfe_Utils::getPlatform());
        $config = $config->toArray();
        return $config;
    }
       protected function getMail(){
        $config = new Cfe_Config_Ini(CONFIG_PATH.'/mail.ini',Cfe_Utils::getPlatform());
        $config = $config->toArray();
        return $config;
    }
}