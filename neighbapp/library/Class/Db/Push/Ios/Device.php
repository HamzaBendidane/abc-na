<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Device extends Class_Db_Abstract {

	protected $_name = 'push_device';
	protected $_adapter = 'iosdb';

	public function insertDevice($task,$appname,$appversion,$deviceuid,$devicetoken,$devicename,
	$devicemodel,$deviceversion,$pushbadge,$pushalert,$pushsound,$macAdress,$adid,$vendorID){

		$queryDevice = $this->getAdapter()->select()
		->from($this->_name)
		->where("mac_adress = ?",$macAdress)
		->where("appname = ?",$appname);

		$cstModel = new Class_Common_Cst();
		$model = $cstModel->getModelDevice($devicemodel);
		$device = $this->getAdapter()->fetchRow($queryDevice);
		$deviceModel = new Class_Db_Device();
		$deviceInfo = array();
		$deviceInfo['mac_adress'] = $macAdress;
		$deviceInfo['udid'] = $deviceuid;
		$deviceRow  =  $deviceModel->getDevice($deviceInfo);
		$deviceId = (isset($deviceRow['id'])?$deviceRow['id']:null);

		if(!$device){
			$insertDevice = array(
                    'appname'           => $appname,
                    'appversion'        => $appversion,
                    'deviceuid'         => $deviceuid,
                    'devicetoken'       => $devicetoken,
                    'devicename'        => $devicename,
                    'devicemodel'       => $model,
                    'deviceversion'     => $deviceversion,
                    'pushbadge'         => $pushbadge,
                    'pushalert'         => $pushalert,
                    'mac_adress'        => $macAdress,
                    'pushsound'         => $pushsound,
                    'development'       => "production",
                    'status'            => "active",
                    'created'           => Date("Y-m-d H:i:s"),
                    'modified'          => null,
                    'device_FK'         => $deviceId);

			$this->getAdapter()->insert($this->_name,$insertDevice);
		}else{
			$insertDevice = array(
                    'appname'           => $appname,
                    'appversion'        => $appversion,
                    'devicetoken'       => $devicetoken,
                    'devicename'        => $devicename,
                    'devicemodel'       => $model,
                    'deviceversion'     => $deviceversion,
                    'pushbadge'         => $pushbadge,
                    'pushalert'         => $pushalert,
                    'mac_adress'        => $macAdress,
                    'pushsound'         => $pushsound,
                    'development'       => "production",
                    'status'            => "active",
                    'modified'          => Date("Y-m-d H:i:s"),
                    'device_FK'         => $deviceId);

			$this->getAdapter()->update($this->_name, $insertDevice, "pid = ".$device['pid']);
		}

		if($deviceId){
			if($deviceRow['advertisingId'] != $adid || $deviceRow['vendorId'] != $vendorID ){
				$updateDeviceInfo = array(
                        'advertisingId' => $adid,
                        'vendorId'      => $vendorID);

				$this->getAdapter()->update("device", $updateDeviceInfo, "id = ".$deviceId);
			}
		}
		return true;
	}

	public function getBlogActivateCount($appname,$model){
		
		$sqlModel = ($model == 'ipad')?"devicemodel = 'ipad'":"devicemodel <> 'ipad'";
		$req = "SELECT count( * ) AS total
				FROM push_device
				WHERE(
				pushalert = 'enabled'
				OR pushbadge = 'enabled'
				OR pushsound = 'enabled'
				)
				AND status= 'active'
				AND appname = '$appname'
				AND $sqlModel ";

		$devicesReq = $this->getAdapter()->query($req);
		$total = $devicesReq->fetch();
		return $total['total'];
	}
	

	public function getPushBlogActivate($appname) {	
		$allDevice = array();	
		$queryDevice = $this->getAdapter()->select()
		->from($this->_name)
		->where("appname = ?",$appname)
		->where('status = "active"');
        $devices = $this->getAdapter()->query($queryDevice);

		while($device = $devices->fetch()){
			$allDevice[] = $device;
		}
		
		return $allDevice;
	}
    
    public function getByDeviceId($deviceId){
        
        $queryDevice = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("device_FK = ?",$deviceId);
        
        $device = $this->getAdapter()->fetchRow($queryDevice);
        if($device == false) return false;
        return $device;
    }
}