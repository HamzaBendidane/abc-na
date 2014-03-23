<?php
/**
 * Cfe Framework
 *
 * @category   Cfe
 * @package    Cfe_Cache
 * @copyright  Copyright (c) sk
 */


require_once 'Cfe/Assertion/Type.php';
require_once 'Cfe/Assertion/Value.php';
/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache.php';

/**
 * @see Cfe_Cache_Backend_TaggedMemcached
 */
require_once 'Cfe/Cache/Backend/TaggedMemcached.php';

/**
 * @see Zend_Cache_Frontend_Class
 */
require_once 'Zend/Cache/Frontend/Class.php';

/**
 * @see Cfe_Config_Ini
 */
require_once 'Cfe/Config/Ini.php';

/**
 * @see Cfe_Utils
 */
require_once 'Cfe/Utils.php';

/**
 * @package    Cfe_Cache
 * @copyright  Copyright (c) sk
 */
class Cfe_Cache_Helper
{
    /**
     *
     * default location of the ini file
     * @var string
     */
    static $iniFile = 'taggedMemcache.ini';

    /**
     *
     * backend object
     * @var Cfe_Cache_Backend_TaggedMemcached
     */
    static protected $backend;

    /**
     *
     * deprecated : please use Cfe_Config_Helper::setPath($path) and name your file taggedMemcache.ini
     * @param string $iniFile file location
     * @throws Zend_Cache_Exception if the file doesn't exist
     * @deprecated
     */
    public static function setIniFile($iniFile) {
        // @codeCoverageIgnoreStart
        // deprecated
        Cfe_Log_Helper::logDeprecated();
        Cfe_Assertion_Type::assertString($iniFile);
        if(file_exists($iniFile)) {
            $config = new Cfe_Config_Ini($iniFile, Cfe_Utils::getPlatform());
            self::$backend = new Cfe_Cache_Backend_TaggedMemcached($config->toArray());
        } else {
            require_once 'Zend/Cache/Exception.php';
            throw new Zend_Cache_Exception('can\'t find '.$iniFile);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     *
     * get the backend object from the ini file
     * @return Cfe_Cache_Backend_TaggedMemcached
     */
    public static function getBackend() {
        if(!isset(self::$backend)) {
            $iniFile = Cfe_Config_Helper::getFullPath(self::$iniFile);
            $config = new Cfe_Config_Ini($iniFile, Cfe_Utils::getPlatform());
            self::$backend = new Cfe_Cache_Backend_TaggedMemcached($config->toArray());
        }
        return self::$backend;
    }

    /**
     *
     * create an object that will cache function call to any method of the provided object.
     * @param object $object
     * @param array $tags list of tags to add to the records. usefull to be able to clean every records for this object.
     * @param Cfe_Cache_Lifetime $lifetime
     * @return Zend_Cache_Frontend_Class
     * @deprecated use Cfe_Cache_Helper::createCachedClass() instead.
     */
    public static function createCachedObject($object, array $tags, Cfe_Cache_Lifetime $lifetime = null) {
    	Cfe_Log_Helper::logDeprecated();
        Cfe_Assertion_Type::assertObject($object);
    	if(!is_null($lifetime)) {
        	return self::createCachedClass($object, $tags, $lifetime);
    	} else {
    		return self::createCachedClass($object, $tags);
    	}
    }

    /**
     *
     * create an object that will cache function call to any method of the provided object/class.
     * /!\ different object of the same class with different internal states will share their cached result.
     * @param object|string $entity object or classname
     * @param array $tags list of tags to add to the records. usefull to be able to clean every records for this object.
     * @param Cfe_Cache_Lifetime $lifetime
     * @return Zend_Cache_Frontend_Class
     */
    public static function createCachedClass($entity, array $tags, Cfe_Cache_Lifetime $lifetime = null) {
        $frontendOptions = array('cached_entity' => $entity);
        $cachedObject = Zend_Cache::factory('Class',self::getBackend(),$frontendOptions,array(), false, true);
        $cachedObject->setTagsArray($tags);
        if($lifetime != null) {
            $cachedObject->setSpecificLifetime($lifetime);
        }
        return $cachedObject;
    }

    /**
     *
     * create an object that will return cached data only if the cache was modified after all the files.
     * this is usefull for cache that depend on the content of files. like the result of parsing xml files.
     * @param array $masterFiles list of file's names/paths
     * @return Zend_Cache_Frontend_Class
     */
    public static function createFilesDependantCache(array $masterFiles) {
        $frontendOptions = array('master_files' => $masterFiles, 'automatic_serialization' => true);
        return Zend_Cache::factory('File',self::getBackend(),$frontendOptions,array(), false, true);
    }
    /**
     *
     * get a Core Interface to the backend. It's usefull to get access to tag cleaning and backend administration.
     * @return Zend_Cache_Core
     */
    public static function getCoreInterface() {
        return Zend_Cache::factory('Core',self::getBackend(),array(),array(), false, true);
    }
}
