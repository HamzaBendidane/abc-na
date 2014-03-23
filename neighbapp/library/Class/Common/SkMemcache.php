<?php
class Class_Common_SkMemcache {
    protected $name;
    protected static $config = null;
    protected $memcache = null;

    
    public  function memcacheGet($sKey) {
    	$memcache = $this->getMemcache();
    	//$sKey = hash('sha512',$sKey);
        $sKey = Lock_Memcache::base64Hash( $sKey,50 );
    	$get_result = $memcache->get($sKey);
    	$memcache->set($sKey,$get_result);
    
    	return $get_result;
    }
    
    /**
     * Delete a value in memcache corresponding to a key
     *
     * @param string $sKey the key to retrieve the value in memcache
     * @throws RealtimeException_Server_Temporary
     * @return stdClass
     */
    public function memcacheDelete($sKey) {
    
    	$memcache = $this->getMemcache();
    	//$sKey = hash('sha512',$sKey);
        $sKey = Lock_Memcache::base64Hash( $sKey,50 );
    	$delete_result = $memcache->delete($sKey);
    	if(!$delete_result) {
    		$msg = __METHOD__." Echec de l'effacement des données sur le serveur : $sKey";
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
    public function memcacheSet($sKey,$oValue,$expirationTime = 5) {
    
    	$memcache = $this->getMemcache();
    	//$sKey = hash('sha512',$sKey);
        $sKey = Lock_Memcache::base64Hash( $sKey,50 );
    	$record_result = $memcache->set( $sKey,$oValue ,0,$expirationTime);
    	if(!$record_result) {
    		$msg = __METHOD__." Echec de la sauvegarde des données sur le serveur : $sKey";
    		throw new Exception($msg);
    	}
    
    	return $record_result;
    }  
   /**
     *
     * return a base64 hash Url compatible ([A-Za-z0-9_\-])
     * you can specify the number of char you need.
     * @param string $str the string to hash.
     * @param int $length the number of char you need (between 1 and 85)
     * @return string
     */
    public static function base64Hash($str, $length) {
        return strtr(substr(base64_encode(hash('sha512',$str, true)), 0, $length), '/+','_-');
    }
    /**
     *
     * grant a lock with a name
     * as long the object is available (not destructed) and the timeout is not expired no lock can be granted with the same name
     * @param string $name
     * @param int $timeout
     * @throws Exception
     */
    public function __construct($timeout=5) {
        $memcache = $this->getMemcache();
        return $memcache;
    }

    protected static function getConfig() {
        $conf = new stdClass();
        $conf->host = 'localhost';
        $conf->port = '11211';
        return $conf;         
    }

    protected function getMemcache() {
        if(is_null($this->memcache)) {
            $this->memcache = new Memcache;
            $config = self::getConfig();
            if(!$this->memcache->connect($config->host, $config->port)) {
                throw new Exception('unable to connect to memcache');
            }
        }
        return $this->memcache;
    }

    /**
     *
     * reset the liftime of the lock
     * @param int $timeout
     */
    public function extend($timeout=5) {
        if(!$this->lock) {
            throw new Exception('no lock to extend');
        }
        $memcache = self::getMemcache();
        if(!($memcache->replace($this->name, 1, 0, $timeout))) {
            throw new Exception("the lock {$this->name} is expired");
        }
    }
    /**
     *
     * Destruct the lock
     */
    public function __destruct() {
        if($this->lock) {
            $memcache = self::getMemcache();
            $memcache->delete($this->name);
            $memcache->close();
            $this->lock = false;
        }
    }
}