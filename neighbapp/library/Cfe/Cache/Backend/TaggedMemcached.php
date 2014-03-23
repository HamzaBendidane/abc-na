<?php
/**
 * Cfe Framework
 *
 * @category   Cfe
 * @package    Cfe_Cache
 * @subpackage Cfe_Cache_Backend
 * @copyright  Copyright (c) sk
 */


/**
 * @see Zend_Cache_Backend_Interface
 */
require_once 'Zend/Cache/Backend/Memcached.php';

/**
 * 
 * a backend that partially support tags
 * @author sk
 *
 */
class Cfe_Cache_Backend_TaggedMemcached extends Zend_Cache_Backend_Memcached
{
    const TAG_PREFIX = 'tags/';

    static protected $_tags_timestamps = array();

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $tmp = $this->_memcache->get($id);
        if(is_array($tmp) && ($doNotTestCacheValidity || $this->isStillValid($tmp[1], $tmp[2], $tmp[3]))){
            return $tmp[0];
        }
        return false;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id Cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $tmp = $this->_memcache->get($id);
        if (is_array($tmp) && $this->isStillValid($tmp[1], $tmp[2], $tmp[3])) {
            return $tmp[1];
        }
        return false;
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        if (!is_string($id)) {
            $this->_log(__CLASS__.'..'.__METHOD__.' : id must be a string');
            return false;
        }
        if (!is_array($tags)) {
            $this->_log(__CLASS__.'..'.__METHOD__.' : tags must be an array');
            return false;
        }
        foreach($tags as $tag) {
            if (!is_string($tag)) {
                $this->_log(__CLASS__.'..'.__METHOD__.' : tags must be an array of strings');
                return false;
            }
        }
        $lifetime = $this->getLifetime($specificLifetime);
        if($lifetime instanceof Cfe_Cache_Lifetime) {
            $lifetime = $lifetime->getValue();
        }
        if ($this->_options['compression']) {
            $flag = MEMCACHE_COMPRESSED;
        } else {
            $flag = 0;
        }
        // #ZF-5702 : we try add() first becase set() seems to be slower
        // @codeCoverageIgnoreStart
        // can never comme here if add is working
        if (!($result = $this->_memcache->add($id, array($data, microtime(true), $lifetime, $tags), $flag, $lifetime))) {
            $result = $this->_memcache->set($id, array($data, microtime(true), $lifetime, $tags), $flag, $lifetime);
        }
        // @codeCoverageIgnoreEnd
        return $result;
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => unsupported
     * 'old'            => unsupported
     * 'matchingTag'    => invalidate the keys associated with this tag (limited to 1 tag).
     * 'notMatchingTag' => unsupported
     * 'matchingAnyTag' => invalidate the keys associated with any of those tags
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        switch ($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                return $this->_memcache->flush();
                break;
            case Zend_Cache::CLEANING_MODE_OLD:
                $this->_log(__CLASS__.'..'.__METHOD__.' : CLEANING_MODE_OLD is unsupported by the TaggedMemcached backend');
                return false;
                break;
            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                if (count($tags) > 1) {
                    $this->_log(__CLASS__.'..'.__METHOD__.' : CLEANING_MODE_MATCHING_TAG you can specify only one tag with the TaggedMemcached backend');
                    return false;
                }
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                if (count($tags) < 1) {
                    $this->_log(__CLASS__.'..'.__METHOD__.' : CLEANING_MODE_MATCHING_ANY_TAG you must at least specify one tag');
                    return true;
                }
                $now = microtime(true);
                $result = true;
                foreach($tags as $tag) {
                    if (!is_string($tag)) {
                        $result = false;
                        $this->_log(__CLASS__.'..'.__METHOD__.' : tags must be strings');
                    } else {
                        $this->invalidateTag($tag, $now);
                    }
                }
                return $result;
                break;
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                $this->_log(__CLASS__.'..'.__METHOD__.' : tags are only partially by the TaggedMemcached backend');
                return false;
                break;
            default:
                Zend_Cache::throwException('Invalid mode for clean() method');
                return false;
                break;
        }
    }

    /**
     * Set the frontend directives
     *
     * @param  array $directives Assoc of directives
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setDirectives($directives)
    {
        parent::setDirectives($directives);
        $lifetime = $this->getLifetime(false);
        if($lifetime instanceof Cfe_Cache_Lifetime) {
            if($lifetime->getMax() > 2592000) {
                // #ZF-3490 : For the memcached backend, there is a lifetime limit of 30 days (2592000 seconds)
                $this->_log(__CLASS__.'..'.__METHOD__.' : memcached backend has a limit of 30 days (2592000 seconds) for the lifetime');
            }
        } else if ($lifetime > 2592000) {
            // #ZF-3490 : For the memcached backend, there is a lifetime limit of 30 days (2592000 seconds)
            $this->_log(__CLASS__.'..'.__METHOD__.' : memcached backend has a limit of 30 days (2592000 seconds) for the lifetime');
        } else if ($lifetime === null) {
            // #ZF-4614 : we tranform null to zero to get the maximal lifetime
            parent::setDirectives(array('lifetime' => 0));
        }
    }

    /**
     * Return an array of metadatas for the given cache id
     *
     * The array must include these keys :
     * - expire : the expire timestamp
     * - tags : a string array of tags
     * - mtime : timestamp of last modification time
     *
     * @param string $id cache id
     * @return array array of metadatas (false if the cache id is not found)
     */
    public function getMetadatas($id)
    {
        $tmp = $this->_memcache->get($id);
        if (is_array($tmp)) {
            $data = $tmp[0];
            $mtime = floor($tmp[1]);
            if (!isset($tmp[2])) {
                // because this record is only with 1.7 release
                // if old cache records are still there...
                return false;
            }
            $lifetime = $tmp[2];
            return array(
                'expire' => floor($mtime + $lifetime),
                'tags' => $tmp[3],
                'mtime' => $mtime
            );
        }
        return false;
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     */
    public function touch($id, $extraLifetime)
    {
        if ($this->_options['compression']) {
            $flag = MEMCACHE_COMPRESSED;
        } else {
            $flag = 0;
        }
        $tmp = $this->_memcache->get($id);
        if (is_array($tmp)) {
            $data = $tmp[0];
            $mtime = $tmp[1];
            if (!isset($tmp[2])) {
                // because this record is only with 1.7 release
                // if old cache records are still there...
                return false;
            }
            $lifetime = $tmp[2];
            $microtime = microtime(true);
            $newLifetime = $lifetime  + $extraLifetime - floor($microtime - $mtime);
            if ($newLifetime <=0.) {
                return false;
            }
            // #ZF-5702 : we try replace() first becase set() seems to be slower
            if (!($result = $this->_memcache->replace($id, array($data, $microtime, $newLifetime, $tmp[3]), $flag, $newLifetime))) {
                $result = $this->_memcache->set($id, array($data, $microtime, $newLifetime, $tmp[3]), $flag, $newLifetime);
            }
            return $result;
        }
        return false;
    }

    /**
     * invalidate all the keys associated to the specific tag (the keys must have the tag as a prefix)
     *
     * @param string $tag
     * @param int $timestamp
     * @return boolean true if ok
     */
    protected function invalidateTag($tag, $timestamp = null)
    {
        if($timestamp === null) {
            $timestamp = microtime(true);
        }
        self::$_tags_timestamps[$tag] = $timestamp;
        if (!($result = $this->_memcache->add(self::TAG_PREFIX.$tag, floatval($timestamp), 0, 0))) {
            $result = $this->_memcache->set(self::TAG_PREFIX.$tag, floatval($timestamp), 0, 0);
        }
        return $result;
    }

    /**
     * get the max timestamp of the tags list
     *
     * @param string $tag
     * @return int the max timestamp of this list of tag
     */
    protected function getTagsMaxTimestamp($tags)
    {
        $maxTimestamp = 0.;
        foreach($tags as $tag)
        {
            if(!array_key_exists($tag, self::$_tags_timestamps)) {
                $timestamp = $this->_memcache->get(self::TAG_PREFIX.$tag);
                if(!is_float($timestamp)) {
                    $timestamp = 0.;
                }
                self::$_tags_timestamps[$tag] = $timestamp;
            }
            $maxTimestamp = max($maxTimestamp, self::$_tags_timestamps[$tag]);
        }
        return $maxTimestamp;
    }

    /**
     * check if this id's tag has been invalidated
     *
     * @param string $id
     * @param int $timestamp
     * @return boolean true if this id is still valid
     */
    protected function isStillValid($timestamp, $lifetime, $tags)
    {
        return  ($lifetime > (microtime(true) - $timestamp)) && ($this->getTagsMaxTimestamp($tags) <= $timestamp);
    }
}
