<?php
/**
 * @category   Cfe
 * @package    Cfe_Log
 */
require_once 'Cfe/Assertion/Type.php';
require_once 'Cfe/Assertion/Value.php';
require_once 'Cfe/Config/Helper.php';
require_once 'Cfe/Config/Ini.php';
require_once 'Cfe/Utils.php';
require_once 'Zend/Log.php';

/**
 *
 * @category   Cfe
 * @package    Cfe_Log
 * @author sk
 *
 */
class Cfe_Log_Helper {

    /**
     *
     * logger object
     * @var Zend_Log
     */
    static protected $logger;

    static $iniFile = 'log.ini';

    /**
     *
     * get the default Logger
     * @return Zend_Log
     */
    static public function getLogger() {
        if(!isset(self::$logger)) {
            $iniFile = Cfe_Config_Helper::getFullPath(self::$iniFile);
            $config = new Cfe_Config_Ini($iniFile, Cfe_Utils::getPlatform());
            self::$logger = Zend_Log::factory($config);
        }
        return self::$logger;
    }

    /**
     * Log a message at a priority
     *
     * @param  string   $message   Message to log
     * @param  integer  $priority  Priority of message
     * @param  mixed    $extras    Extra information to log in event
     * @return void
     * @throws Zend_Log_Exception
     */
    static public function log($message, $priority, $extra = null) {
        // @codeCoverageIgnoreStart
        if(!isset(self::$logger)) {
            self::getLogger();
        }
        // @codeCoverageIgnoreEnd
        self::$logger->log($message, $priority, $extra);
    }

    static public function logDeprecated() {
        $calls = debug_backtrace();
        array_shift($calls);
        $deprecated = array_shift($calls);
        $caller = array_shift($calls);
        // @codeCoverageIgnoreStart
        // can't be tested in phpunit
        if(is_null($caller)) {
            throw new Zend_Log_Exception(__CLASS__.'::'.__FUNCTION__.' called outside a function/method');
        }
        // @codeCoverageIgnoreEnd
        $deprecatedString = (array_key_exists('class', $deprecated)?($deprecated['class'].'::'):'').$deprecated['function'];
        $callerString = (array_key_exists('class', $caller)?($caller['class'].'::'):'').$caller['function'];
        self::log(sprintf('DEPRECATED : %s called from %s in %s line %d',$deprecatedString,$callerString, $deprecated['file'], $deprecated['line']), Zend_Log::WARN);
    }
}