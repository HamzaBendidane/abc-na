<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Message extends Class_Db_Abstract {

    protected $_name = 'ios_message';
    protected $_adapter = 'push';
    
    public function addMessage($message,$versionId,$deviceId,$pipId,$token,$start_time = null,$push_test = 0,$userId = null,$campaignId = null){
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
            'device_id'     => $deviceId,
            'version_id'    => $versionId,
            'pip_id'        => $pipId,
            'user_id'       => $userId,
            'campaign_id'   => $campaignId
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
}