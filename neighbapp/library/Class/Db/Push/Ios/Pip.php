<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Pip extends Class_Db_Abstract {

    protected $_name = 'ios_push_pip';
    protected $_adapter = 'neighbapp';
    
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
    
    public function getAllPush(){
        $return = array();
        $queryPip = $this->getAdapter()->select()
                ->from($this->_name)
                ->join("ios_version", "$this->_name.version_id = ios_version.id",array('name'));
        $pips = $this->getAdapter()->query($queryPip);
        
        while($pip = $pips->fetch()){
            switch ($pip['state']) {
                case 0:
                    $pip['state'] = "Not validate";
                    break;
                case 1:
                    $pip['state'] = "Validated";
                    break;
                case 0:
                    $pip['state'] = "In creation";
                    break;
                case 0:
                    $pip['state'] = "Sending";
                    break;
                case 0:
                    $pip['state'] = "Sended";
                    break;
                case 0:
                    $pip['state'] = "Rollbacked";
                    break;

                default:
                    break;
            }
            $return[] = $pip;
        }
        return $return;
    }
    
    public function updatePipAll($aData,$pipid){
        
        
        $return = $this->fetchRow($this->select()
                               ->where("id = $pipid"))->setFromArray($aData)->save();
       
    	return $return;
    }
    
    public function createPip($aData){
        $createPip = array(
            "message" => $aData['message'],
            "start_time" => $aData['start_time'],
            "version_id" => $aData['version_id'],
            "message" => $aData['message'],
        );
        $this->getAdapter()->insert($this->_name,$createPip);
    }
}