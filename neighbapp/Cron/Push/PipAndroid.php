<?php
require_once realpath(dirname(__FILE__)).'/PipAbstract.php';
class Cron_Push_PipAndroid  extends Cron_Push_PipAbstract {
    
    protected function getWaintingPip(){
        
         $pipModel = new Class_Db_Push_Android_Pip();
         $pips = $pipModel->getWaitingPip();
         return $pips;
    }
    
    protected function getAllVersion(){
        
        $messageModel = new Class_Db_Push_Android_Message();
        $versions = $messageModel->getAllVersion();
        return $versions;
    }
    
    protected function getVersionById($versionId){
        
        $versionModel = new Class_Db_Push_Android_Version();
        $version = $versionModel->getVersionById($versionId);
        return $version;
    }
    
    protected function getAllDevice($campaignId){
        
         $deviceModel = new Class_Db_Android_Device();
         $allDevice = $deviceModel->getPushActivate($campaignId);
         return $allDevice;
    }
    
    protected function getMessagesByVersion($versionId){
        
        $messageModel = new Class_Db_Push_Android_Message();
        $allMessages = $messageModel->getMessagesByVersion($versionId);
        return $allMessages;
    }
    
    protected function updateVersionpip($versionId,$state){
        $pipModel = new Class_Db_Push_Android_Pip();
        $pipModel->updateVersionpip($versionId,$state);
    }
    
    protected function addMessage($pip,$device){
         $messageModel = new Class_Db_Push_Android_Message();
         $allDevice = $messageModel->addMessage($pip['message'], $pip['version_id'], $device['id'], $pip['id'],$device['registration_id'],$pip['start_time'],0,$pip['type_push'],$pip['color'],$pip['url_image']);
    }
    
    protected function updatePip($pipId,$state){
         $pipModel = new Class_Db_Push_Android_Pip();
         $pipModel->updatePip($pipId,$state);
    }
    
    protected function sendPush($message,$version){
        $registration = $message['registration_id'];
        $messageId = $message['id'];
        $param = array(
    			'message'       =>      $message['message'],
    			'type' 		=> 	$message['type_push'],
    			'color' 	=> 	$message['color'],
    	);
    	//send gcm message
    	$message = new Zend_Mobile_Push_Message_Gcm();
    	$message->setId(time());
    	$message->addToken($registration);
    	$message->setData($param);
        $gcm = new Zend_Mobile_Push_Gcm();
    	$gcm->setApiKey('AIzaSyDbhI6nKXf1yAglUN0jOUen1eSUlvpWtF4');
    	
    	try {
    		$response = $gcm->send($message);
    	} catch (Zend_Mobile_Push_Exception $e) {
    		
    		$this->statePush($messageId,"bug", $e->getMessage());
    		// all other exceptions only require action to be sent or implementation of exponential backoff.
    		die($e->getMessage());
    	}
    	
    	// handle all errors and registration_id's
     	foreach ($response->getResults() as $k => $v) {
    		if (isset($v['registration_id'])) {
    			
    			$this->statePush($messageId,"delivered","",$v['registration_id'],$registration);

    			//printf("%s has a new registration id of: %s\r\n", $k, $v['registration_id']);
    		}
    		if (isset($v['error'])) {
    			
    			$this->statePush($messageId,"failed",$v['error']);

    			//printf("%s had an error of: %s\r\n", $k, $v['error']);
    		}
    		if (isset($v['message_id'])) {
    			
    			$this->statePush($messageId,"delivered", "");

    			//printf("%s was successfully sent the message, message id is: %s", $k, $v['message_id']);
    		}
    	} 
    }
    
    protected  function statePush ($messageId,$status,$error, $registrationid=null, $oldRegistrationId=null){
    	
    	$messageModel = new Class_Db_Push_Android_Message();
    	$messageModel->upadateStatus($messageId,$status,$error,$registrationid,$oldRegistrationId);
    }
    
}

$cron = new Cron_Push_PipAndroid();
//$cron->addMessages();
$cron->sendMessages();