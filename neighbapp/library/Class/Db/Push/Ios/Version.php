<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Ios_Version extends Class_Db_Abstract {

    protected $_name = 'ios_version';
    protected $_adapter = 'neighbapp';
    
    public function getVersionById($versionId){
        $queryVersion = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("id = ?",$versionId);
        $version = $this->getAdapter()->fetchRow($queryVersion);
        
        return $version;
    }
    
    public function getAllPush(){
        $return = array();
        $queryVersion = $this->getAdapter()->select()
                ->from($this->_name);
        $versions = $this->getAdapter()->query($queryVersion);
        
        while($version = $versions->fetch()){
           $return[] = $version; 
        }
        
        return $return;
    }
}