<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Android_Device extends Class_Db_Abstract {

    protected $_name = 'push_device';
    protected $_adapter = 'androiddb';
    
    public function insertDevice($task,$appname,$appversion,$deviceid,$sim_serial_number,$registration_id,$devicename,
                           $devicemodel,$deviceversion,$pushalert,$pushsound,$pushvibration){
        
        $queryDevice = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("device_id = ?",$deviceid);
        $device = $this->getAdapter()->fetchRow($queryDevice);
        
        $cstModel = new Class_Common_Cst();
        $model = $cstModel->getModelDevice($devicemodel);
        if(!$device){
            $insertDevice = array(
                    'ap_pname'          => $appname,
                    'app_version'       => $appversion,
                    'device_id'         => $deviceid,
                    'sim_serial_number' => $sim_serial_number,
                    'registration_id'   => $registration_id,
                    'device_name'       => $devicename,
                    'device_model'      => $model,
                    'os_version'        => $deviceversion,
                    'push_alert'        => $pushalert,
                    'push_sound'        => $pushsound,
                    'push_vibration'    => $pushvibration,
                    'status'            => "active",
                    'created'           => Date("Y-m-d H:i:s"),
                    'modified'          => null);

            $this->getAdapter()->insert($this->_name,$insertDevice);
        }else{
			$insertDevice = array(
                    'ap_pname'          => $appname,
                    'app_version'       => $appversion,
                    'device_id'         => $deviceid,
                    'sim_serial_number' => $sim_serial_number,
                    'registration_id'   => $registration_id,
                    'device_name'       => $devicename,
                    'device_model'      => $model,
                    'os_version'        => $deviceversion,
                    'push_alert'        => $pushalert,
                    'push_sound'        => $pushsound,
                    'push_vibration'    => $pushvibration,
                    'status'            => "active",
                    'modified'          => Date("Y-m-d H:i:s"));

            $this->getAdapter()->update($this->_name, $insertDevice, "pid = ".$device['pid']);
        }
        return true;
    }
    
    /**
     * 
     * @param unknown_type $registrationid
     * @param unknown_type $oldRegistrationId
     */

    public function updateRegistrationId($registrationid,$oldRegistrationId){
    	 
    	$queryPushDevice = $this->getAdapter()->select()
    	->from($this->_name)
    	->where("registration_id = ?",$oldRegistrationId);
    	$PushDevice = $this->getAdapter()->fetchRow($queryPushDevice);
    	 
    	if($PushDevice){
    		
    	$bind = array('registration_id'=>$registrationid);
    	$this->getAdapter()->update($this->_name, $bind,'id='.$PushDevice['id']);
    		
    	}
    	
    	return true;
    }
}