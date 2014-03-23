<?php
class Api_Abstract {
    
    /**
     * Extends pour l'application web
     * @var bool 
     */
    protected $web = false;
    
    protected $defineInit = array();
    
    protected $geoipInit = array();
    
    protected $varsServer = array();
    
    protected $varsCookie = array();
    
    /**
     * Extends pour les Campagnes extra
     * @var bool
     */
    protected $extra = false;
    
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
     * An instance of memcache to store the data of the sessions.
     * @var Zend_Cache
     */
    protected $lifetime;
    
    /**
     * Constructor of the API, construct the logger.
     * @return void
     */
    public function __construct($aCookieParam = array(),$aServerParam = array()) {
        $superGlobals = new Class_Common_SuperGlobals($aCookieParam,$aServerParam);
        
        $this->varsServer = $superGlobals->getServerParams($superGlobals);
        
        $this->varsCookie = $superGlobals->getCookieParams($superGlobals);
        
        $geoip = new Cfe_Config_Ini(CONFIG_PATH.'/geoip.ini');
        $this->geoipInit = $geoip->toArray();
        
        $config = new Cfe_Config_Ini(CONFIG_PATH.'/define.ini', Cfe_Utils::getPlatform());
        $this->defineInit = $config->toArray();
        
        
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
   /**
     * Get a value in memcache from a key
     * 
     * @param string $sKey the key to retrieve the value in memcache
     * @throws RealtimeException_Server_Temporary
     * @return stdClass
     */
    protected function memcacheGet($sKey) {        
        $memcache = $this->memcacheConnect();
        $sKey = hash('sha512',$sKey);
        
        $get_result = $memcache->load($sKey);
        
        return $get_result;
    }
    
    /**
     * Delete a value in memcache corresponding to a key
     * 
     * @param string $sKey the key to retrieve the value in memcache
     * @throws RealtimeException_Server_Temporary
     * @return stdClass
     */
    protected function memcacheDelete($sKey) {
        
        $memcache = $this->memcacheConnect();
        $sKey = hash('sha512',$sKey);
        
        $delete_result = $memcache->remove($sKey);
        if(!$delete_result) {
            $msg = __METHOD__." Echec de l'effacement des données sur le serveur : $sKey";
            $this->log($msg,Zend_Log::ERR);
            //throw new RealtimeException_Server_Temporary($msg);
        }
        
        return $delete_result;
    }
    
    /**
     * Function to set a value in memcache using a key to find it
     * 
     * @param string $sKey the key 
     * @param stdClass $oValue the value
     * @throws RealtimeException_Server_Temporary
     * @return boolean 
     */
    protected function memcacheSet($sKey,$oValue,$lifetime = 3600) {
        $this->lifetime = $lifetime;
        $memcache = $this->memcacheConnect();
        $sKey = hash('sha512',$sKey);
        $record_result = $memcache->save( $oValue, $sKey );
        if(!$record_result) {
            $msg = __METHOD__." Echec de la sauvegarde des données sur le serveur : $sKey";
            $this->log($msg,Zend_Log::ERR);
            throw new Exception($msg);
        }
        
        return $record_result;
    }
    
    /**
     * Connect to a Memcache server
     * 
     * @return Zend_Cache
     */
    private function memcacheConnect() {
        
            $config = new Cfe_Config_Ini(APPLICATION_CONFIG_PATH . '/application.ini',Cfe_Utils::getPlatform());
            $config = $config->memcache;
            
            $options = $config->frontendOptions->toArray();
            $options['logging'] = $this->logging;
            $options['logger'] = $this->logger;
            $options['lifetime'] = $this->lifetime;
            $this->_memcacheInstance = Zend_Cache::factory($config->frontend,
                                                           $config->backend,
                                                           $options,
                                                           $config->backendOptions->toArray(),
                                                           false,
                                                           false);
            //TODO
            //$this->_memcacheInstance->clean();
        return $this->_memcacheInstance;
    }
    
    /**
     * Connect to a Memcache server
     * 
     * @return Zend_Cache
     */
    protected function memcacheClear() {
        $memcache = $this->memcacheConnect();
        $memcache->clean();
        return true;
    }
    
}