<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Message extends Class_Db_Abstract {

    protected $_name = 'ios_message';
    protected $_adapter = 'neighbapp';
    
    public function addMessage($message,$versionId,$pipId,$token,$start_time = null,$push_test = 0,$userId = null){
        if(!$start_time){
            $start_time = date("Y-m-d H:i:s");
        }
        
        $createMessage = array(
            'message'       => $message,
            'delivery'      => date('Y-m-d H:i:s'),
            'status'        => "queued",
            'token'         => $token,
            'created'       => date('Y-m-d H:i:s'),
            'push_test'     => $push_test,
            'modified'      => null,
            'start_date'    => $start_time,
            'version_id'    => $versionId,
            'pip_id'        => $pipId,
            'user_id'       => $userId
        );

        $this->getAdapter()->insert($this->_name,$createMessage);
        return true;
    }
    
    
    /**
     * id massage
     * @param int $id
     * @param string $status
     * @param string $error
     */
    
    public function updateStatus($id,$status,$error){
    
    	$bind = array('status'=>$status,
                      'ERROR' =>$error);
    	$this->getAdapter()->update($this->_name, $bind,"id='$id'");
    
    	return true;
    }
        
    public function getAllVersion($type){
    	$versions=array();
    	$reQ=" SELECT DISTINCT (version_id) FROM  $this->_name JOIN ios_version on ios_version.id=ios_message.version_id  WHERE type = '".$type."' and status='queued' ";
        $queryVersion = $this->getAdapter()->query($reQ);
        while ($row =$queryVersion->fetch()){
        	$versions[]=$row;
        }
        return $versions;
    }
    
    public function getMessagesByVersion($versionId){
        $date = date('Y-m-d H:i:s');
        $queryMessage = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("status = ?","queued")
                ->where("start_date <= ?",$date)
                ->where("version_id = ?",$versionId)
                ->order("start_date ASC");
        $message = $this->getAdapter()->query($queryMessage);
        
        while($row = $message->fetch()){
            $messages[] = $row;
        }
        return $messages;
    }
    
    public function cleanMessages(){
        $today = date('Y-m-d H:i:s');
        $yesturday = date("Y-m-d H:i:s",strtotime('-1 day',  strtotime($today)));
        return $this->getAdapter()->delete($this->_name, "start_date <= '$yesturday' and status = 'delivered'");
    }
    
    
    public function getAllMessageTest(){
        
        $return = array();
        $queryPip = $this->getAdapter()->select()
                ->from($this->_name)
                ->join("ios_version", "$this->_name.version_id = ios_version.id")
                ->join("ios_devices_test", "$this->_name.device_id = ios_devices_test.id",array("name as device_name"))
                ->where("push_test = ?" , 1);
        $pips = $this->getAdapter()->query($queryPip);
        
        while($pip = $pips->fetch()){
            $return[] = $pip;
        }
        return $return;
    }
    
    public function createPushTest($aData){
        $start_time = date("Y-m-d H:i:s");
        $apiModel = new Class_Db_Push_Ios_DeviceTest();
        $device = $apiModel->getDeviceTestById($aData['device_id']);
        $createMessage = array(
            'message'       => $aData['message'],
            'delivery'      => date('Y-m-d H:i:s'),
            'status'        => "queued",
            'token'         => $device['token'],
            'created'       => date('Y-m-d H:i:s'),
            'push_test'     => 1,
            'modified'      => null,
            'start_date'    => $start_time,
            'device_id'     => $aData['device_id'],
            'version_id'    => $aData['version_id'],
            'pip_id'        => false,
            'user_id'       => false,
            'campaign_id'   => false
        );
        $this->getAdapter()->insert($this->_name,$createMessage);
        return true;
    }
}