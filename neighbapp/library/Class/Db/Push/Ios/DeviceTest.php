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
}