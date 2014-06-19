#!/usr/bin/php
<?php
require_once realpath(dirname(__FILE__)).'/PipAbstract.php';
class Cron_Push_PipIos  extends Cron_Push_PipAbstract {
    protected $_apns = '';
    protected $_type = 'ios';
    protected function getWaintingPip(){
        
         $pipModel = new Class_Db_Push_Ios_Pip();
         $pips = $pipModel->getWaitingPip();
         return $pips;
    }
    
    protected function getAllVersion(){
        
        $messageModel = new Class_Db_Push_Ios_Message();
        $versions = $messageModel->getAllVersion($this->_type);
        
        return $versions;
    }
    
    protected function getVersionById($versionId){
        
        $versionModel = new Class_Db_Push_Ios_Version();
        $version = $versionModel->getVersionById($versionId);
        return $version;
    }
    
    protected function getPipById($pipId){
        if($pipId == '')
            return false;
        
        $pipModel = new Class_Db_Push_Ios_Pip();
        $pip = $pipModel->getPipById($pipId);
        return $pip;
    }
    
    protected function getAllDevice($version_id){
        
         $deviceModel = new Class_Db_Push_Ios_Device();
         $allDevice = $deviceModel->getAllDevice($version_id);
         
         return $allDevice;
    }
    
    protected function cleanMessage(){
        $messageModel = new Class_Db_Push_Ios_Message();
        print "\n START Clean MEssages";
        try{
            $clean = $messageModel->cleanMessages();
        } catch (Exception $exc) {
             echo 'ERROR CLEAN MESSAGE '.$exc->getMessage();
        }
        print "\n TOTAL Clean MEssages : $clean";
        print "\n END Clean MEssages";
    }
    
    protected function getMessagesByVersion($versionId){
        
        $messageModel = new Class_Db_Push_Ios_Message();
        $allMessages = $messageModel->getMessagesByVersion($versionId);
        return $allMessages;
    }
    
    protected function updateVersionpip($versionId,$state,$oldState){
        $pipModel = new Class_Db_Push_Ios_Pip();
        try {
            $pipModel->updateVersionpip($versionId,$state,$oldState);
        } catch (Exception $exc) {
             echo 'ERROR UPDATE VERSION PIP '.$exc->getMessage();
        }
    }
    
    protected function addMessage($pip,$device){
         $messageModel = new Class_Db_Push_Ios_Message();
         try {
            $allDevice = $messageModel->addMessage($pip['message'], $pip['version_id'], $pip['id'],$device['devicetoken'],$pip['start_time'],0,$device['user_FK']);
         } catch (Exception $exc) {
             echo 'ERROR ADD MESSAGE '.$exc->getMessage();
         }

    }
    
    protected function updatePip($pipId,$state){
         $pipModel = new Class_Db_Push_Ios_Pip();
         try {
            $pipModel->updatePip($pipId,$state);
         } catch (Exception $exc) {
             echo 'ERROR UPDATE PIP '.$exc->getMessage();
         }
    }
    
    protected function cleanPip($versionId){
         $pipModel = new Class_Db_Push_Ios_Pip();
         try{
            $pipModel->cleanPip($versionId);
         } catch (Exception $exc) {
             echo 'ERROR CLEAN PIP '.$exc->getMessage();
         }
    }
    
    protected function initPush($version){
        print "\n START Init versionn :".print_r($version,true);
        
        $this->_apns = new Zend_Mobile_Push_Apns();
        $this->_apns->setCertificate($version['certificate_prod']);

        /*                  APNS CONNECTION                          */ 
        try {
           $this->_apns->connect(Zend_Mobile_Push_Apns::SERVER_PRODUCTION_URI);
        } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
            // you can either attempt to reconnect here or try again later
            //exit(1);
        } catch (Zend_Mobile_Push_Exception $e) {
            echo 'APNS Connection Error:' . $e->getMessage();
            //exit(1);
        }
        print "\n END Init versionn ";
    }
    protected function sendPush($message,$version){
        //print "\n send : ".$message['token']." - ".$message['message'];
        $push = new Zend_Mobile_Push_Message_Apns();
        $push->setAlert($message['message']);
        $push->setBadge(1);
        $push->setSound($version['sound']);
        //$push->setSound("PushAppXpert.wav");  
        $push->setId(time());
        $push->setToken($message['token']);

        /*                     SEND PUSH                             */ 
        try {
           $this->_apns->send($push);
        } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
            $this->statePush($message['id'],"failed", $e->getMessage());
            // you would likely want to remove the token from being sent to again
            echo $e->getMessage();
        } catch (Zend_Mobile_Push_Exception $e) {
            // all other exceptions only require action to be sent
            $this->statePush($message['id'],"failed", $e->getMessage());
            echo $e->getMessage();
        }
        //print "delivered";
        $this->statePush($message['id'],"delivered", "");
    }
    
    protected function feedbackDevice(){
        print "\n START FEEDBACK "; 
        /*                     FEEDBACK                             */ 
        $tokens = $this->_apns->feedback();
        while(list($token, $time) = each($tokens)) {
            $pushDeviceModel = new Class_Db_Push_Ios_Device();
            $pushDeviceModel->update(array("status" => "uninstalled"), "devicetoken=".$token);
            //$this->statePush($token,"failed","Application uninstalled by user");
            echo $time . "\t" . $token . PHP_EOL;
        }
        print "\n END FEEDBACK "; 
    }
    
    protected function closePush(){
        print "\n START CLOSE ";
        $this->_apns->close();
        print "\n END CLOSE ";
    }
    
    /**
     * 
     * @param string $status
     * @param string $error
     */
    protected  function statePush($id,$status,$error){
    	$messageModel = new Class_Db_Push_Ios_Message();
        try{
            $messageModel->updateStatus($id,$status,$error);
        } catch (Exception $exc) {
             echo 'ERROR UPDATE STATE MESSAGE '.$exc->getMessage();
        }
    }
    
    protected function connect(){
        require_once 'Zend/Loader/Autoloader.php';
        Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
        $application = new Zend_Application(
                Cfe_Utils::getPlatform(),
                APPLICATION_CONFIG_PATH . '/application.ini'
            );
        $application->getBootstrap()->_initDbAdaptersToRegistry();
    }
    
    protected function disconnect(){
        $db = Zend_Registry::get('neighbapp');
        $db->closeConnection();
    }
    
    protected function getCampagnById($campaignId){
        if($campaignId == '') return false;
        
        $campaignModel = new Class_Db_Campaign();
        return $campaignModel->getCampagnById($campaignId);
    }
    
    protected function alreadyDonwloadApplication($applicationId,$userId,$campaignId){
        if($applicationId == ''||$userId == ''||$campaignId == '') return false;
        
        $participationModel = new Class_Db_Participation();
        return $participationModel->alreadyDonwloadApplication($applicationId,$userId,$campaignId);
    }
    
    protected function hasParticipate($userId,$campaignId){
        if($userId == ''||$campaignId == '') return false;
        
        $participationModel = new Class_Db_Participation();
        return $participationModel->hasParticipate($userId,$campaignId);
    }
    protected function getAccesConfigRocket($certif_prod){
    	
    	// Emplacement du fichier LOG.
    	$Fichier = $certif_prod; 
    	$fp = fopen($Fichier,"r");
    	while (!feof($fp)) {
    		$page = fgets($fp, 4096);
    		if(strpos($page,"configkey:") !== false){
    			$return['configkey']=str_replace("configkey:",'',trim($page));
    		}
    		if(strpos($page,"secretkey:") !== false){
    			$return['secretkey']=str_replace("secretkey:",'',trim($page));
    		}
    	}
		return $return;		
    	
    }
  
}