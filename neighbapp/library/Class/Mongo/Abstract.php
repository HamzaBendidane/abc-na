<?php
Class Class_Mongo_Abstract
{
    protected static $_connection;
    
    public $varsServer;
    
    public $varsCookie;
    
    public function __construct($aCookieParam = array(),$aServerParam = array()) {
        $superGlobals = new Class_Common_SuperGlobals($aCookieParam,$aServerParam);
        
        $this->varsServer = $superGlobals->getServerParams($superGlobals);
        
        $this->varsCookie = $superGlobals->getCookieParams($superGlobals);        
    }
    
    /**
     * @param string $dbAlias
     */
    protected static function _loadConnection()
    {     
        self::$_connection = Class_Common_DBManager::getConnection('mongo');
    }
    
    protected static function _find($tableName, $query = array(), $options =array())
    {
    	self::_loadConnection();
    	$collection = self::$_connection->selectCollection($tableName);
    	return $collection->find($query);
    }
    
    /**
     * Insert a record into the user_data_bt table
     * @param string $tableName
     * @param  $record
     * @return int
     */
    protected static function _insert($tableName, $record)
    {
       self::_loadConnection();
       $collection = self::$_connection->selectCollection($tableName);
       $result = $collection->insert($record);
       return $record['_id'];
    }

    /**
     * Update a record based on given id.
     *  NOTE: if the result is 1 that means 1 row affected, thus a success
     * @param string $tableName
     * @param array $properties
     * @param mixed $conditions
     * @return bool
     */
    protected static function _update($tableName,$filter, $record = array(), $options=array())
    {
       self::_loadConnection();
       $collection = self::$_connection->selectCollection($tableName);
       $result = $collection->update($filter, $record,$options);
       return $result;
    }

    /**
     * Delete a record
     *  NOTE: if the result is 1 that means 1 row affected, thus a success
     * @param string $tableName
     * @param mixed $conditions
     * @return int
     */
    protected static function _delete($tableName, $conditions)
    {
	   self::_loadConnection();
	   $collection = self::$_connection->selectCollection($tableName);
       $result = $collection->remove($conditions);
       return $result;
    }
}