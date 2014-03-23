<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Android_Message extends Class_Db_Abstract {

    protected $_name = 'android_message';
    protected $_adapter = 'push';
    
    public function addMessage($message,$versionId,$deviceId,$pipId,$registration_id,$start_time = null,$push_test = 0,$type,$color,$url_image){
        
    	if(!$start_time){
            $date = time();
            $start_time = date("Y-m-d H:i:s",strtotime('+3 minutes',$date));
        }
        $createMessage = array(
            'message'   => $message,
            'delivery'  => date('Y-m-d H:i:s'),
            'status'    => "queued",
            'registration_id' => $registration_id,
            'created'   => date('Y-m-d H:i:s'),
            'push_test' => $push_test,
            'modified'  => null,
            'start_date'=> $start_time,
            'device_id' => $deviceId,
            'version_id'=> $versionId,
            'pip_id'    => $pipId,
            'type_push' => $type,
            'color'     => $color,
            'url_image' => $url_image,
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
    
    public function upadateStatus($id,$status,$error,$registrationid,$oldRegistrationId){
    
    	$bind = array('status'=>$status,
    				  'ERROR' =>$error);
    	if($registrationid)
    	{
    		$deviceModel = new Class_Db_Push_Android_Device();
    		$deviceModel->updateRegistrationId($registrationid,$oldRegistrationId);
    		
    		$bind['registration_id'] = $registrationid;
    		//$this->updateRegistrationId($registrationid,$oldRegistrationId);
    	}

    	    	 
    	$this->getAdapter()->update($this->_name, $bind,'id='.$id);
    
    	return true;
    }
    
    public function getAllVersion(){
    	$queryVersion = $this->getAdapter()->select()
    	->from($this->_name,array("DISTINCT(version_id)"))
    	->where("status = ?","queued");
    	$versions = $this->getAdapter()->fetchAll($queryVersion);
    
    	return $versions;
    }
    
    public function getMessagesByVersion($versionId){
    	$date = date('Y-m-d H:i:s');
    	$queryMessage = $this->getAdapter()->select()
    	->from($this->_name)
    	->where("status = ?","queued")
    	->where("start_date <= ?",$date)
    	->where("version_id = ?",$versionId);
    	$messages = $this->getAdapter()->fetchAll($queryMessage);
    
    	return $messages;
    }
}

