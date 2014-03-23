<?php
require_once 'Cfe/Assertion/Type.php';
require_once 'Cfe/Assertion/Value.php';

/**
 * 
 * Helper for the config. Help to deal with location of ini files
 * @author sk
 *
 */
class Cfe_Config_Helper
{
    const DEFAULT_PATH = '../conf';
    /**
     *
     * path to locate all ini files
     * by default : ../conf
     * @var string
     */
    protected static $path = self::DEFAULT_PATH;
    /**
     *
     * set the path to look for when searching for ini files
     * @param string $path
     */
    static public function setPath ($path)
    {
        Cfe_Assertion_Type::assertString($path);
        if (substr($path, - 1) == '/') {
            $path = substr($path, 0, - 1);
        }
        if(!file_exists($path)) {
            throw new Zend_Config_Exception('invalid path : '.$path);
        }
        self::$path = $path;
    }
    /**
     *
     * get the path where we're looking for ini files
     * @return string
     */
    static public function getPath ()
    {
        return self::$path;
    }
    /**
     *
     * return the filename prefixed with the ini files path
     * @param unknown_type $filename
     * @return string
     */
    static public function getFullPath ($filename)
    {
        Cfe_Assertion_Type::assertString($filename);
        return (self::$path . '/' . $filename);
    }
}