<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PipAbstract
 *
 * @author ranjit
 */
require_once realpath(dirname(__FILE__)).'/../Abstract.php';
class Cron_Push_PipAbstract  extends Cron_Abstract {
    
    protected $_tab = array('3applis','appoclock','appdusoir');
    public function addMessages(){
        $this->connect();
        try {
            die('ok');
            $pips = $this->getWaintingPip();
            foreach($pips as $pip){
                $this->updatePip($pip['id'],2);
                $version = $this->getVersionById($pip['version_id']);
                // On récupère tout les device pushable
                //$relance = ($pip['relance_push'] == 1 )?true:false;
                /*
                 * Add Abdel 
                 * diff relance !!!
                 */
                
                switch ($pip['relance_push']) {
                    case '1':
                        $relance = 1;
                        break;
                    case '2':
                        $relance = 2;
                        break;
                    default:
                        $relance = 0;
                        break;
                }
                $allDevice = $this->getAllDevice($pip['campaign_FK'],$relance,$version['appname'],$version['type']);
                $campaign = $this->getCampagnById($pip['campaign_FK']);
                foreach($allDevice as $device){
                	 
                	if (!in_array($version['appname'], $this->_tab)) {
                		$alreadyDonwload = false;

                		if($relance == 0){ //PUSH ALL
                			$this->addMessage($pip,$device);
                		}elseif($relance == 1){ //PUSH RELANCE
                			$participate = $this->hasParticipate($device['user_FK'],$pip['campaign_FK']);
                			$alreadyDonwload = $this->alreadyDonwloadApplication($campaign['application_FK'],$device['user_FK'],$pip['campaign_FK']);
                			if(!$participate && !$alreadyDonwload)
                			$this->addMessage($pip,$device);
                		}else{ //PUSH EN COURS
                			$participate = $this->hasParticipate($device['user_FK'],$pip['campaign_FK']);
                			if($participate['status'] != 1 && $participate)
                			$this->addMessage($pip,$device);
                		}
                	}else{
                		$device['id'] = $device['device_FK'];
                		$device['user_FK'] = ''; //PAS DE USER DANS NOTRE BASE DE DONNEE POUR APPDUSOIR, 3APPLIS, APPOCLOCK
                		$this->addMessage($pip,$device);
                	}
                }
            }
            if(date('H') == 00)
                $this->cleanMessage(); // Supprime les messages envoyé la veille en status 'delivered'
        } catch (Exception $e){
            echo $e->getTraceAsString();
        }
        $this->disconnect();
    }
    
    public function sendMessages(){     
        $this->connect();
        
        try {
            $versions = $this->getAllVersion();
            foreach($versions as $version){
                $version = $this->getVersionById($version['version_id']);
                $sleep = 0;
                $allMessages = $this->getMessagesByVersion($version['id']);
				
                $this->updateVersionpip($version['id'],3,2);

                $this->initPush($version);
                if (is_array($allMessages)){
                    foreach($allMessages as $message){
                        $participation = false;
                        if($sleep == $version['rate']){
                            sleep(1);
                            $sleep = 0;
                        }
                        $pip = $this->getPipById($message['pip_id']);
                        
                        // send
                        if(!is_null($message['user_id']) && !is_null($message['campaign_id']) && $pip != false && $pip['relance_push'] != 2)
                            $participation = $this->hasParticipate($message['user_id'], $message['campaign_id']);
                        
                        if($participation == false){
                            $this->sendPush($message,$version);
                            $sleep ++;
                        }else{
                            $this->statePush($message['id'],"participation", "Partcipation Error");
                        }
                    }
                    $this->cleanPip($version['id']); // Suppression des messages non envoyé (status = 'queued') 
                }
                $this->feedbackDevice();
                $this->closePush();
            }
        } catch (Exception $e){
            echo $e->getTraceAsString();
        }
        $this->disconnect();
    }
}

?>