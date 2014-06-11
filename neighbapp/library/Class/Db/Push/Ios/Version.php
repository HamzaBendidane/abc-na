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
    
    public function getAllVersion(){
        $return = array();
        $queryVersion = $this->getAdapter()->select()
                ->from($this->_name);
        $versions = $this->getAdapter()->query($queryVersion);
        
        while($version = $versions->fetch()){
           $return[] = $version; 
        }
        
        return $return;
    }
    
    
    public function updateVersion($aData,$versionId){
        
        $return = $this->fetchRow($this->select()
                               ->where("id = $versionId"))->setFromArray($aData)->save();
       
    	return $return;
    }
    
    
    
    /**
     * Creation of User
     * @param Array $data
     * @return Boolean 
     */
    public function insertVersion($data){
        try {
            $aData = array(
                "rate" => $data['rate'],
                "name" => $data['name']
            );
            
            $creation = $this->getAdapter()->insert($this->_name,$aData);
            if($creation === 1){
                return $this->getAdapter()->lastInsertId();
            }else{
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
    }
}