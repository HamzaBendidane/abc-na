<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Android_Pip extends Class_Db_Abstract {

    protected $_name = 'android_push_pip';
    protected $_adapter = 'push';
    
    public function getWaitingPip(){
        // On récupère les push à envoyé 1H avant l'heure de lancement
        $req = "SELECT * FROM `android_push_pip` 
                WHERE start_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR) 
                AND state = 1";
        $pips = $this->getAdapter()->query($req);
        return $pips->fetchAll();
    }
    
    public function updatePip($pipId,$state){
        $updateUser = array(
            'state' => $state);
        return $this->getAdapter()->update($this->_name, $updateUser, "id = $pipId");
    }
}