<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Device extends Class_Db_Abstract {

    protected $_name = 'push_device';
    protected $_adapter = 'neighbapp';

    public function insertDevice($task, $appname, $appversion, $deviceuid, $devicetoken, $devicename, $devicemodel, $deviceversion, $pushbadge, $pushalert, $pushsound, $macAdress, $adid, $vendorID) {

        try {
            $queryDevice = $this->getAdapter()->select()
                    ->from($this->_name)
                    ->where("advertisingId = ?", $adid)
                    ->where("appname = ?", $appname);

            $device = $this->getAdapter()->fetchRow($queryDevice);

            $cstModel = new Class_Common_Cst();
            $model = $cstModel->getModelDevice($devicemodel);

            if (!$device) {
                $insertDevice = array(
                    'appname' => $appname,
                    'appversion' => $appversion,
                    'deviceuid' => $deviceuid,
                    'devicetoken' => $devicetoken,
                    'devicename' => $devicename,
                    'devicemodel' => $model,
                    'deviceversion' => $deviceversion,
                    'pushbadge' => $pushbadge,
                    'pushalert' => $pushalert,
                    'mac_adress' => $macAdress,
                    'pushsound' => $pushsound,
                    'advertisingId' => $adid,
                    'vendorId' => $vendorID,
                    'development' => "production",
                    'status' => "active",
                    'created' => Date("Y-m-d H:i:s"),
                    'modified' => null);

                return $this->getAdapter()->insert($this->_name, $insertDevice);
            } else {
                $insertDevice = array(
                    'appname' => $appname,
                    'appversion' => $appversion,
                    'devicetoken' => $devicetoken,
                    'devicename' => $devicename,
                    'devicemodel' => $model,
                    'deviceversion' => $deviceversion,
                    'pushbadge' => $pushbadge,
                    'pushalert' => $pushalert,
                    'mac_adress' => $macAdress,
                    'pushsound' => $pushsound,
                    'advertisingId' => $adid,
                    'vendorId' => $vendorID,
                    'development' => "production",
                    'status' => "active",
                    'modified' => Date("Y-m-d H:i:s"));

                return $this->getAdapter()->update($this->_name, $insertDevice, "pid = " . $device['pid']);
            }
        } catch (Exception $e) {
            $return = array(
              'success' =>  0,  
              'message' =>  32006  
            );
            return $return;
        }

    }
    
    
    /**
     * Update Device by adid
     * @param type $aData
     * @param type $adid
     * @return boolean 
     */
    public function updateDeviceByAdid($aData,$adid){
        
        try {
            $where = "advertisingId = '$adid'";
            $update = $this->getAdapter()->update($this->_name,$aData,$where);
            if($update === 1){
                return true;
            }else{
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
    }
    
    /**
     * Delete an user
     * @author Jeyaganesh Ranjit
     * @param int $idUser
     * @return boolean
     */
    public function deleteDevice($idDevice){
        return $this->getAdapter()->delete($this->_name, "id = $idDevice");
    }
    
    public function getTotalPush($version_id){
        $queryDevice = $this->getAdapter()->select()
                ->from($this->_name,'count(*) as total')
                ->where("version_id = ?", $version_id)
                ->where("pushbadge = 'enabled' OR pushalert = 'enabled' OR pushsound = 'enabled'");
            
        $device = $this->getAdapter()->fetchRow($queryDevice);        
        
        return $device;
    }
    
    
    public function getAllDevice($version_id){
        
        $return = array();
        $query = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("version_id = ?",$version_id)
                ->where("pushbadge = 'enabled' OR pushalert = 'enabled' OR  pushsound = 'enabled'")
                ->where("status = 'active'");
        $query = $this->getAdapter()->query($query);
        
        while($device = $query->fetch()){
            $return[] = $device;
        }
        return $return;
    }
}