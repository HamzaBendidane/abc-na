<?php
require_once 'Cfe/Assertion/Type.php';

class Cfe_Lock_Memcache {
    protected $name;
    protected $lock = false;
    protected static $config = null;
    protected $memcache = null;

    /**
     *
     * grant a lock with a name
     * as long the object is available (not destructed) and the timeout is not expired no lock can be granted with the same name
     * @param string $name
     * @param int $timeout
     * @throws Exception
     */
    public function __construct($name, $timeout=5) {
        Cfe_Assertion_Type::assertString($name);
        Cfe_Assertion_Type::assertInt($timeout);
        $this->name = 'lock_'.$name;
        $memcache = $this->getMemcache();
        if(!($memcache->add($this->name, 1, 0, $timeout))) {
            throw new Exception("unable to acquire the lock {$this->name}");
        }
        $this->lock = true;
    }

    /**
     *
     * set the config for the memcache object
     * @param Zend_Config $config (host, port)
     */
    public static function setConfig(Zend_Config $config) {
        self::$config = $config;
    }


    protected static function getConfig() {
        if(is_null(self::$config)) {
            require_once 'Cfe/Config/Ini.php';
            require_once 'Cfe/Config/Helper.php';
            require_once 'Cfe/Utils.php';
            self::$config = new Cfe_Config_Ini(Cfe_Config_Helper::getFullPath('memcacheLock.ini'), Cfe_Utils::getPlatform());
        }
        return self::$config;
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
        Cfe_Assertion_Type::assertInt($timeout);
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