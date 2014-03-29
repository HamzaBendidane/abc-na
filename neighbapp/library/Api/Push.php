<?php

/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*
* Description of Home
*
* @author ranjit
*/
class Api_Push extends Api_Abstract{
    
	
    /**
     * Create a device in push_device
     * @param type $task
     * @param type $appname
     * @param type $appversion
     * @param type $devicetoken
     * @param type $devicename
     * @param type $devicemodel
     * @param type $deviceversion
     * @param type $pushbadge
     * @param type $pushalert
     * @param type $pushsound
     * @param type $adid
     * @param type $mac_addr
     * @param type $vendorid
     * @return array 
     */
    public function apns($task,$appname,$appversion,$devicetoken,$devicename,
                           $devicemodel,$deviceversion,$pushbadge,$pushalert,
                            $pushsound,$adid = "", $macAdress =false,$vendorID=""){
        $data = array();
        
        $pushModel = new Class_Db_Push_Ios_Device();
        $pushModel->insertDevice($task,$appname,$appversion,"",$devicetoken,$devicename,
                        $devicemodel,$deviceversion,$pushbadge,$pushalert,$pushsound,$macAdress,$adid,$vendorID);
 		
       
       
        $data['status'] = 1;
        
        return $data;
    }
    
    /**
     * Create a device in push_device Android
     * @param string $task
     * @param string $appname
     * @param string $appversion
     * @param string $deviceid
     * @param string $sim_serial_number
     * @param string $registration_id
     * @param string $devicename
     * @param string $devicemodel
     * @param string $deviceversion
     * @param string $pushalert
     * @param string $pushsound
     * @param string $pushvibration
     * @return array $data
     */
    public function gcm($task,$appname,$appversion,$deviceid,$sim_serial_number,$registration_id,$devicename,
    		$devicemodel,$deviceversion,$pushalert,$pushsound,$pushvibration){
    	$data = array();
    
    	$pushModel = new Class_Db_Push_Android_Device();
    	$pushModel->insertDevice($task,$appname,$appversion,$deviceid,$sim_serial_number,$registration_id,$devicename,
    			$devicemodel,$deviceversion,$pushalert,$pushsound,$pushvibration);
    
    	$data['status'] = 1;
    
    	return $data;
    }
    
    /**
     * Add Message in queued
     * @param string $message
     * @param string $versionId
     * @param string $deviceId
     * @param string $token
     * @return boolean
     */
    public function addMessageIos($message,$versionId,$deviceId,$token){
        $messageModel = new Class_Db_Push_Ios_Message();
        return $messageModel->addMessage($message, $versionId, $deviceId,null,$token,null,1);
    }
    
    /**
     * Add Message in queued
     * @param type $message
     * @param type $versionId
     * @param type $deviceId
     * @param type $type
     * @param type $color
     * @param type $url_image
     * @return boolean
     */
    public function addMessageAndroid($message,$versionId,$deviceId,$androidId,$type,$color = null,$url_image = null){
    	
        $messageModel = new Class_Db_Push_Android_Message();
        $deviceModel = new Class_Db_Push_Android_Device();
        
        $registration_id = $deviceModel->getRegistrationIdByAndroidId($androidId);
      
        $messageModel->addMessage($message, $versionId, $deviceId,$registration_id,null,null,1,$type,$color,$url_image);
        return true;
    }
}