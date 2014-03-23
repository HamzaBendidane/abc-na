<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Frontend
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Class.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Cache_Core
 */
require_once 'Zend/Cache/Core.php';


/**
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Frontend
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Cfe_Cache_Frontend_Object extends Zend_Cache_Core {

    /**
     * Available options
     *
     * ====> (boolean) cache_by_default :
     * - if true, method calls will be cached by default
     *
     * ====> (array) methods_cache_time :
     * - an array of method names with associated cache time ($method => $time)
     * if time false the method wont be cached.
     *
     * @var array available options
     */
    protected $_specificOptions = array(
        'cached_entity' => null,
        'cache_by_default' => true,
        'shared_signature' => null,
        'methods_cache_time' => array(),
    );

    /**
     * Tags array
     *
     * @var array
     */
    protected $_tags = array();

    /**
     * SpecificLifetime value
     *
     * false => no specific life time
     *
     * @var int
     */
    protected $_specificLifetime = false;

    /**
     * The cached object or the name of the cached abstract class
     *
     * @var mixed
     */
    protected $_cachedEntity = null;

    /**
     * Priority (used by some particular backends)
     *
     * @var int
     */
    protected $_priority = 8;

    /**
     * Constructor
     *
     * @param  array $options Associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct(array $options = array()) {
        while (list($name, $value) = each($options)) {
            $this->setOption($name, $value);
        }
        $this->setOption('automatic_serialization', true);
        $this->validateSpecificOptions();
        $this->setCachedEntity($this->_specificOptions['cached_entity']);
    }

    /**
     * Set a specific life time
     *
     * @param  int $specificLifetime
     * @return void
     */
    public function setSpecificLifetime($specificLifetime = false) {
        $this->_specificLifetime = $specificLifetime;
    }

    /**
     * Set the priority (used by some particular backends)
     *
     * @param int $priority integer between 0 (very low priority) and 10 (maximum priority)
     */
    public function setPriority($priority) {
        $this->_priority = $priority;
    }

    /**
     * Specific method to set the cachedEntity
     *
     * if set to a class name, we will cache an abstract class and will use only static calls
     * if set to an object, we will cache this object methods
     *
     * @param mixed $cachedEntity
     */
    public function setCachedEntity($object) {
        Cfe_Assertion_Type::assertObject($object);
        $this->_cachedEntity = $object;
    }

    /**
     * Set the cache array
     *
     * @param  array $tags
     * @return void
     */
    public function setTagsArray($tags = array()) {
        $this->_tags = $tags;
    }

    /**
     * Main method : call the specified method or get the result from cache
     *
     * @param  string $name       Method name
     * @param  array  $parameters Method parameters
     * @return mixed Result
     */
    public function __call($name, $parameters) {
        if (array_key_exists($name, $this->_specificOptions['methods_cache_time'])) {
            $cache = $this->_specificOptions['methods_cache_time'][$name] !== false;
            $cachetime = $this->_specificOptions['methods_cache_time'][$name];
        } else {
            $cache = $this->_specificOptions['cache_by_default'];
            $cachetime = $this->_specificLifetime;
        }
        if (!$cache) {
            // We do not have not cache
            return call_user_func_array(array($this->_cachedEntity, $name), $parameters);
        }

        $id = $this->makeId($name, $parameters);
        if (($rs = $this->load($id)) && isset($rs[0], $rs[1])) {
            // A cache is available
            $output = $rs[0];
            $return = $rs[1];
        } else {
            // A cache is not available (or not valid for this frontend)
            ob_start();
            ob_implicit_flush(false);

            try {
                $return = call_user_func_array(array($this->_cachedEntity, $name), $parameters);
                $output = ob_get_clean();
                $data = array($output, $return);
                $this->save($data, $id, $this->_tags, $cachetime, $this->_priority);
            } catch (Exception $e) {
                ob_end_clean();
                throw $e;
            }
        }

        echo $output;
        return $return;
    }

    /**
     * Make a cache id from the method name and parameters
     *
     * @param  string $name Method name
     * @param  array  $args Method parameters
     * @return string Cache id
     */
    public function makeId($name, array $args = array()) {
        if ( !is_null($this->_specificOptions['shared_signature'])) {
            $entitySignature = get_class($this->_cachedEntity) . $this->_specificOptions['shared_signature'];
        } else {
            $entitySignature = serialize($this->_cachedEntity);
        }

        return md5($entitySignature . '__' . $name . '__' . serialize($args));
    }

    /**
     * Validates specific options
     * @throws Zend_Cache_Exception
     */
    protected function validateSpecificOptions() {
        if ($this->_specificOptions['cached_entity'] === null) {
            Zend_Cache::throwException('cached_entity must be set !');
        }
        
        if (!is_object($this->_specificOptions['cached_entity'])) {
            Zend_Cache::throwException('cached_entity must be an object !');
        }
        
        if (!is_null($this->_specificOptions['shared_signature']) && !is_string($this->_specificOptions['shared_signature'])) {
            Zend_Cache::throwException('shared_signature must be a string !');
        }
    }
}
