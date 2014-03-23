<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Class_Db_Push_Android_Version extends Class_Db_Abstract {

    protected $_name = 'android_version';
    protected $_adapter = 'push';
    
    public function getVersionById($versionId){
        $queryVersion = $this->getAdapter()->select()
                ->from($this->_name)
                ->where("id = ?",$versionId);
        $version = $this->getAdapter()->fetchRow($queryVersion);
        
        return $version;
    }
}