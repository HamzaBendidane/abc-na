<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Android_MessageTest extends Class_Db_Abstract {

    protected $_name = 'android_message_test';
    protected $_adapter = 'push';
    
    public function addMessage($message,$versionId,$deviceId,$pipId,$token,$start_time){
        $createMessage = array(
            'message'   => $message,
            'delivery'  => date('Y-m-d H:i:s'),
            'status'    => "queued",
            'token'     => $token,
            'created'   => date('Y-m-d H:i:s'),
            'modified'  => null,
            'start_date'=> $start_time,
            'device_id' => $deviceId,
            'version_id'=> $versionId,
            'pip_id'    => $pipId,
        );
        
        $this->getAdapter()->insert($this->_name,$createMessage);
        return true;
    }
}