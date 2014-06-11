<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_DeviceTest extends Class_Db_Abstract {

    protected $_name = 'ios_devices_test';
    protected $_adapter = 'neighbapp';

    
    public function getDeviceTestById($deviceId){
        $queryDevice = $this->getAdapter()->select()
        ->from($this->_name)
        ->where("id = ?",$deviceId);
        $device = $this->getAdapter()->fetchRow($queryDevice);
        
        return $device;
    }
    
    public function updateDeviceTest($aData,$deviceId){
        
        $return = $this->fetchRow($this->select()
                               ->where("id = $deviceId"))->setFromArray($aData)->save();
       
    	return $return;
    }
    
    /**
     * Delete an user
     * @author Jeyaganesh Ranjit
     * @param int $idUser
     * @return boolean
     */
    public function deleteDeviceTest($idDevice){
        return $this->getAdapter()->delete($this->_name, "id = $idDevice");
    }
    
    /**
     * Creation of User
     * @param Array $data
     * @return Boolean 
     */
    public function createDevice($data){
        try {
            $aData = array(
                "token" => $data['token'],
                "name" => $data['name']
            );
            
            $creation = $this->getAdapter()->insert($this->_name,$aData);
            if($creation === 1){
                return $this->getAdapter()->lastInsertId();
            }else{
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
    }
    

    
    public function getAllPushDevice(){
        
        $return = array();
        $queryPip = $this->getAdapter()->select()
                ->from("ios_devices_test");
        $pips = $this->getAdapter()->query($queryPip);
        
        while($pip = $pips->fetch()){
            $return[] = $pip;
        }
        return $return;
    }
    
}