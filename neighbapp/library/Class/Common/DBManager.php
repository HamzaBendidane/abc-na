<?php
class Class_Common_DBManager
{
    /**
     * static variable to store unique class instance
     *
     * @var Class_Common_DBManager DBManager unique instance
     */
    protected static $instance;

    /**
     * static variable to store unique class instance
     *
     * @var array databases connections configuration
     */
    protected $aConfig;

    /**
     * static variable to store unique class instance
     *
     * @var array array of DatabaseConnection
     */

    protected $aConnection;

    /**
     *
     * @return void
     */
    private function __construct()
    {
        $this->aConfig = array();
        $this->aConnection = array();
    }

    /**
     *
     * @return Class_Common_DBManager
     */
    public static function getInstance()
    {
        if(!self::$instance instanceof self)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Retrieve a existing connection or create it
     *
     * @param string $sId Database connection id
     * @return MongoDb
     */
    public static function getConnection($sId)
    {
        if(is_string($sId))
        {
            $oManager = self::getInstance();
            if(!$oManager->isConnectionExists($sId) && !$oManager->setAConnection($sId))
                throw new Exception("Unknow database access '".$sId."'");
            return $oManager->aConnection[$sId];
        }
        throw new Exception("Unknow database access '".$sId."'");
    }
    /**
     * Verify if Database Connection exists
     * @param string $sId Database connection id
     * @return boolean TRUE if exists
     */
    public static function isConnectionExists($sId)
    {
        $bReturn = FALSE;
        if(is_string($sId) || is_int($sId))
        {
            if(isset(self::getInstance()->aConnection[$sId]))
                $bReturn = TRUE;
        }
        return $bReturn;
    }
    /**
     * Try to connect to DB with configuration data
     * @param string $sId
     * @param bool $bActiveRecord
     * @return boolean
     **/
    public static function setAConnection($sId)
    {
        $oManager = self::getInstance();
        if(is_string($sId))
        {
        	self::addConfig();
            $aConfig =& $oManager->aConfig;
         
            if(!isset($aConfig[$sId]) || !is_array($aConfig[$sId]))
                throw new Exception("database access '".$sId."' does not exist");    
            $connection = new Mongo();
            $oConnection = $connection->selectDB($aConfig[$sId]['dbname']);
            $oManager->aConnection[$sId] = $oConnection;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Add configuration to current
     * @param $id
     * @return boolean
     **/
    public static function addConfig()
    {
    	$aArrayConfig ['mongo']['host']="127.0.0.1";
    	$aArrayConfig ['mongo']['username']="";
    	$aArrayConfig ['mongo']['password']="";
    	$aArrayConfig ['mongo']['dbname']="log";
        if(is_array($aArrayConfig))
        {
            $oManager = self::getInstance();
            $oManager->aConfig = array_merge($oManager->aConfig, $aArrayConfig);
            $oManager->aConnection = array();
        }
        else
            throw new Exception($aArrayConfig.' is not an array');
    }
}