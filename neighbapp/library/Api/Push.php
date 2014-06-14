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
     * @param type $macAdress
     * @param type $vendorid
     * @return array 
     */
    public function apns($task,$appname,$appversion,$devicetoken,$devicename,
                           $devicemodel,$deviceversion,$pushbadge,$pushalert,
                            $pushsound,$adid = "", $macaddress =false,$vendorID=""){
        $data = array();
        
        $pushModel = new Class_Db_Push_Ios_Device();
        $return = $pushModel->insertDevice($task,$appname,$appversion,"",$devicetoken,$devicename,
                        $devicemodel,$deviceversion,$pushbadge,$pushalert,$pushsound,$macaddress,$adid,$vendorID);
 		
       
        if(is_array($return)){
            return $return;
        }else{
            return array("success" => 1);
        }
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
    
    public function GetAllPush(){
        
        $pipModel = new Class_Db_Push_Ios_Pip();
        $allPush = $pipModel->getAllPush();
        
        return $allPush;
    }
    
    public function GetPushById($pushId){
        
        $pipModel = new Class_Db_Push_Ios_Pip();
        $push = $pipModel->getPipById($pushId);
        
        return $push;
    }
    
    public function GetAllPushVersion(){
        
        $pipModel = new Class_Db_Push_Ios_Version();
        $allPush = $pipModel->getAllVersion();
        
        return $allPush;
    }
    
    public function GetAllPushTest(){
        
        $pipModel = new Class_Db_Push_Ios_Message();
        $allPush = $pipModel->getAllMessageTest();
        
        return $allPush;
    }
    
    public function GetAllPushDevice(){
        
        $pipModel = new Class_Db_Push_Ios_DeviceTest();
        $allPush = $pipModel->getAllPushDevice();
        
        return $allPush;
    }
    
    public function GetDeviceTestById($deviceId){
        
        $deviceModel = new Class_Db_Push_Ios_DeviceTest();
        $device = $deviceModel->getDeviceTestById($deviceId);
        
        return $device;
    }
    
    public function UpdateDeviceTest($aData,$deviceId){
        
        $deviceModel = new Class_Db_Push_Ios_DeviceTest();
        $device = $deviceModel->updateDeviceTest($aData,$deviceId);
        
        return $device;
    }
    
    public function GetVersionById($versionId){
        
        $versionModel = new Class_Db_Push_Ios_Version();
        $version = $versionModel->getVersionById($versionId);
        
        return $version;
    }
    
    public function UpdateVersion($aData,$versionId){
        
        $versionModel = new Class_Db_Push_Ios_Version();
        $version = $versionModel->updateVersion($aData,$versionId);
        
        return $version;
    }
    
    
    public function UpdatePipAll($aData,$pipId){
        
        $pipModel = new Class_Db_Push_Ios_Pip();
        $pipId = $pipModel->updatePipAll($aData,$pipId);
        
        return $pipId;
    }
    
    /**
     * Delete a User
     * @author Jeyaganesh Ranjit
     * @param int $idUser
     * @return Boolean
     */
    public function DeleteDevice($idDevice){
        if(!is_numeric($idDevice)){
            return false;
        }
        $deviceModel = new Class_Db_Push_Ios_DeviceTest();
        
        $result = $deviceModel->deleteDeviceTest($idDevice);
        
        return $result;
    }
    
    public function CreateDeviceTest($aData){
        $model = new Class_Db_Push_Ios_DeviceTest();
        return $model->createDevice($aData);
    }
    
    public function InsertVersion($aData){
        $model = new Class_Db_Push_Ios_Version();
        return $model->insertVersion($aData);
    }
    
    public function GetListVersion(){
        $tabVersion = array();
        $pipModel = new Class_Db_Push_Ios_Version();
        $allPush = $pipModel->getAllVersion();
        
        foreach($allPush as $version){
            $tabVersion[$version['id']] = $version['name'];
        }
        return $tabVersion;
    }
    
    public function GetListDevice(){
        $tabVersion = array();
        
        $pipModel = new Class_Db_Push_Ios_DeviceTest();
        $allPush = $pipModel->getAllPushDevice();
        
        foreach($allPush as $version){
            $tabVersion[$version['id']] = $version['name'];
        }
        
        return $tabVersion;
    }
    
    public function CreatePushTest($aData){
        $model = new Class_Db_Push_Ios_Message();
        return $model->createPushTest($aData);
    }
    
    public function CreatePushPip($aData){
        $model = new Class_Db_Push_Ios_Pip();
        return $model->createPip($aData);
    }
    
    public function GetTotalPush($version_id){
        $model = new Class_Db_Push_Ios_Device();
        return $model->getTotalPush($version_id);
    }
}