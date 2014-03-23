<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Pip extends Class_Db_Abstract {

    protected $_name = 'ios_push_pip';
    protected $_adapter = 'push';
    
    public function getWaitingPip(){
        $date = date('Y-m-d H:i:s');
        // On récupère les push à envoyé 1H avant l'heure de lancement
        echo $req = "SELECT p.*,v.appname
                FROM  `ios_push_pip` p
                JOIN  ios_version v ON v.id = p.version_id
                WHERE start_time  <= DATE_ADD('$date', INTERVAL 1 
                MINUTE ) 
                AND state =1";
        
        $pips = $this->getAdapter()->query($req);
        return $pips->fetchAll();
    }
    
    public function updatePip($pipId,$state){
        $updateUser = array(
            'state' => $state);
        return $this->getAdapter()->update($this->_name, $updateUser, "id = $pipId");
    }
    
    public function updateVersionpip($versionId,$state,$oldState){
        $today = date("Y-m-d H:i:s");
        $updateUser = array(
            'state' => $state);
        return $this->getAdapter()->update($this->_name, $updateUser, "version_id = $versionId and start_time <= '$today' and state = $oldState");
    }
    
    public function cleanPip($versionId){
        $queryPip = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("state = 4")
                ->where("modified = ''")
                ->where("version_id = ?",$versionId);
        $pips = $this->getAdapter()->fetchAll($queryPip);
        if(is_array($pips)){
            foreach($pips as $pip){
                $this->getAdapter()->delete('ios_message', "pip_id = '".$pip['id']."' AND status ='queued'");
            }
        }
    }
    
    public function getPipById($pipId){
        $queryPip = $this->getAdapter()->select()
        ->from($this->_name)
        ->where("id = ?",$pipId);
        $pip = $this->getAdapter()->fetchRow($queryPip);
        
        return $pip;
    }
}