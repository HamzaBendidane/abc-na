<?php
/**
 * Adapater pour faire une identification avec informations Device
 * Objectif : Connect User With Device Info
 */

class Cfe_Auth_Adapter_Device implements Zend_Auth_Adapter_Interface
{

    /**
     * deviceId
     *
     * @var deviceId
     */
    protected $_deviceId;
    
    /**
     * deviceInfo
     *
     * @var array
     */
    protected $_deviceInfo;
    
    /**
     * Api Device
     */
    protected $_ApiDevice;



    /**
     * id_user crypté
     *
     * @var id_user
     */
    protected $_idUserCrypted;
    
    protected  $_user;




    /**
     * @param Int deviceId
     * @param Array deviceInf
     * @return void
     */
    public function __construct($deviceId = null, $deviceInfo = array())
    {
        
        $this->_ApiDevice = new Api_Web_Device();
        
        if($deviceId){
            $this->_deviceId = $deviceId;
        }
        
        if(!empty($deviceInfo)){
            $this->_deviceInfo = $deviceInfo;
        }
        
    }
    
 /**
     * Authenticates use Api_Web_Device::getDevice().
     * Defined by Zend_Auth_Adapter_Interface.
     *
     * @throws Zend_Auth_Adapter_Exception If answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate() {
        
        $cryp = new Cfe_Token_Aes();
        // if deviceId : getdevice by id
        
        // if deviceInfo : getdevice by macaddress ou autre
        
        if($this->_deviceId || !empty($this->_deviceInfo)){
            
            $device = $this->_ApiDevice->getDevice($this->_deviceInfo, $this->_deviceId);
            
            $this->_idUserCrypted = $cryp->encrypt($device['user_FK']);
            
            $this->_deviceInfo = $device;
            
            
             $userObj = new Class_Db_User();
             $this->_user = $userObj->getUserById($device['user_FK']);
             
             if($device['blocked'] == 1 || $this->_user['blocked'] == 1){
                 return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE,
                        null,
                        array("Authentication failed"));
             }  
            
            return new Zend_Auth_Result(
                    Zend_Auth_Result::SUCCESS,
                    $this->_idUserCrypted,
                    array("Authentication successful"));
        }
              
       return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE,
                        null,
                        array("Authentication failed"));
        
    }
    
    
    /**
     * getResultRowObject() - Returns the result row as a stdClass object
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return stdClass|boolean
     */
    public function getResultRowObject($colums = array())
    {
        if (!$this->_deviceInfo) {
            return false;
        }
        
        $user = $this->_user;
        
        $crypt = new Cfe_Token_Aes();
        
        $returnObject = new stdClass();
        
        $returnObject->id                    = $this->_idUserCrypted;
        $returnObject->earned_money          = $user['earned_money'];
        $returnObject->earned_points         = $user['earned_points'];
        $returnObject->withdrawn_benefits    = $user['withdrawn_benefits'];
        $returnObject->lastName              = $user['lastName'];
        $returnObject->firstname             = $user['firstName'];
        $returnObject->niveau_FK             = $user['niveau_FK'];
        $returnObject->email                 = $user['email'];
        $returnObject->device                = $this->_deviceInfo;
        $returnObject->sk_device             = $crypt->encrypt('id_device=' . $this->_deviceInfo['id'] . '&device_model='. $this->_deviceInfo['model'].'&redirect=#&time='.time());

        return $returnObject;
    }
}