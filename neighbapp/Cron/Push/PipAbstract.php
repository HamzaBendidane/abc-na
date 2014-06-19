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
    
    public function addMessages(){
        $this->connect();
        try {
            $pips = $this->getWaintingPip();

            foreach($pips as $pip){
                
                $this->updatePip($pip['id'],2);

                $allDevice = $this->getAllDevice($pip['version_id']);
                foreach($allDevice as $device){
                    $this->addMessage($pip,$device);
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