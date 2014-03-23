<?php
require_once 'Cfe/Log/Helper.php';

class Cfe_Utils {

    /**
     *
     * List of valid platform identifications
     * @var array
     */
    protected static $authorizedPlatforms = array('local', 'dev', 'int', 'stg', 'ppr', 'prod','cron');
    /**
     *
     * List of the testing platforms
     * @var array
     */
    protected static $testingPlatforms = array('local', 'dev', 'int');

    /**
     *
     * Give the platform identification (local, dev, int, stg, ppr, prod)
     * @return string platform
     * @throws Exception if the environement variable is not set.
     */
    public static function getPlatform() {
        if(!in_array(getenv('PLATFORM'), self::$authorizedPlatforms)) {
            throw new Exception('PLATFORM environement variable not set');
        }
        return getenv('PLATFORM');
    }

    /**
     *
     * true if the platform is one of the testing platform (local, dev, int)
     * @param string $platform if null the environement variable is used
     * @return boolean
     * @throws Exception if the environement variable is not set.
     */
    public static function isTestingPlatform($platform = null) {
    	if(is_null($platform)) {
    		$platform = self::getPlatform();
    	}
        return in_array($platform, self::$testingPlatforms);
    }

    /**
     *
     * true if the platform is one of the valids platform (local, dev, int, stg, ppr, prod)
     * @param string $platform if null the environement variable is used
     * @return boolean
     * @throws Exception if the environement variable is not set.
     */
    public static function isValidPlatform($platform = null) {
    	if(is_null($platform)) {
    		$platform = self::getPlatform();
    	}
        return in_array($platform, self::$authorizedPlatforms);
    }

    /**
     *
     * return the platform url for the specified platform
     * @param string $platform if null the environement variable is used
     * @return string
     * @throws Exception if the environement variable is not set.
     */
    public static function getPlatformUrl($platform = null) {
    	if(is_null($platform)) {
    		return getenv('PLATFORM_URL');
    	} elseif(!self::isValidPlatform($platform)) {
    		throw new Exception("invalid platform : $platform");
    	} elseif($platform == 'prod') {
        	return '';
    	}
    	return $platform.'.';
    }

    /**
     *
     * Deprecated. Use Cfe_Config_Helper::getPath()
     * @return string conf directory
     * @deprecated
     */
    public static function getConfDir() {
        // @codeCoverageIgnoreStart
        Cfe_Log_Helper::logDeprecated();
        return Cfe_Config_Helper::getPath();
        // @codeCoverageIgnoreEnd
    }

    /**
     *
     * Deprecated. Use Cfe_Config_Helper::setPath($directory)
     * @param string $directory
     * @deprecated
     */
    public static function setConfDir($directory) {
        // @codeCoverageIgnoreStart
        Cfe_Log_Helper::logDeprecated();
        return Cfe_Config_Helper::setPath($directory);
        // @codeCoverageIgnoreEnd
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
        Cfe_Assertion_Type::assertString($str);
        Cfe_Assertion_Type::assertInt($length);
        Cfe_Assertion_Value::assertRange($length, 1, 85);
        return strtr(substr(base64_encode(hash('sha512',$str, true)), 0, $length), '/+','_-');
    }
    
    /**
     * Get country for Transactions (UK,FR)
     * @return string
     */
    public static function getCountry(){
        return getenv('APP');
    }

    /**
     * Function that generates an ODIN-1
     *
     * @param  string $mac_address the MAC address to 'odinise'
     * @return string              the formated ODIN-1
     * @author Antoine Kougblenou
     **/
    public static function generateOdin($mac_address)
    {
        $valRet     = false;
        $mac_parsed = explode(":", $mac_address);
        $characters = '';
        
        foreach ($mac_parsed as $byte)
        {
            // In accordance with the ODIN-1 doc
            // the conversion by raw byte is 
            // a non-ambiguous way of making an ODIN-1
            $characters .= chr(hexdec($byte));
        }
        //the now formatted ODIN-1 will be returned
        $valRet = sha1($characters);

        return $valRet;
    }

    /**
     * Function that generates a MD5
     *
     * @param  string $udid the UDID to be MD5-ed
     * @return string              the formated MD5
     * @author Antoine Kougblenou
     **/
    public static function generatemd5($udid)
    {
        $valRet     = false;
        //the now formatted MD5 will be returned
        if($udid){
        	$valRet = md5($udid);
        }

        return $valRet;
    }

    /**
     * Function that generates a SHA1
     *
     * @param  string $param the argument to be SHA1-ed
     * @return string              the formated SHA1
     * @author Antoine Kougblenou
     **/
    public static function generateSHA($param)
    {
        $valRet     = false;
        //the now formatted MD5 will be returned
        if($param){
            $valRet = sha1($param);
        }

        return $valRet;
    }

    /**
     * directly calls a tracking clas
     * @param  string $trackingName the unformatted name of the tracking
     * @return string               the name of the Tracking type edited
     * @author Antoine Kougblenou
     */
    public static function formatTrackingName($trackingName)
    {
        $trackingNameArray      = explode('-', $trackingName);
        $trackingNameEdited     = trim($trackingNameArray[0]);              
        return $trackingNameEdited;
    }
    /**
     *  return an instance of a given tracker
     * @param string $iniFile path to the ini file
     * @param string $class
     * @return object
     */
    public static function loadConfIniClass($iniFile, $class)
    {
        //set the tracker
        $trackers   = new Cfe_Config_Ini(CONFIG_PATH.$iniFile,Cfe_Utils::getPlatform());
        $trackers   = $trackers->toArray();
        // format the class name to get the specific tracker
        $class_name = strtolower($class);
        $tracker    = $trackers[$class_name];               
        return $tracker;
    }
}