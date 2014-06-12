<?php

/**
 * Example of model DB class
 * @author Jeyaganesh RANJIT
 */
class Class_Db_Uaccounts extends Class_Db_Abstract {

    protected $_name = 'uaccounts';
    protected $_adapter = 'neighbapp';

    
    /**
     * Get User Detail By Id
     * @param int $userId
     * @return array $user 
     */
    public function GetAllUsers(){
        
        $queryUser = $this->getAdapter()->select()
                ->from($this->_name);
        $user =  $this->getAdapter()->fetchAll($queryUser);
        
        return ($user)?$user:array();
    }
    
    public function deleteUser($userId){
        $this->getAdapter()->delete($this->_name, "id = $userId");
    }
    
    public function getUserById($userId){
        
        $queryUser = $this->getAdapter()->select()
                ->from($this->_name)
                ->where('id = ?',$userId);
        $user =  $this->getAdapter()->fetchRow($queryUser);
        
        return ($user)?$user:false;
    }
    
    public function updateUaccount($aData,$userId){
        $this->getAdapter()->update($this->_name, $aData, "id = $userId");
    }
    
    public function createUaccount($aData){
        return $this->createRow($aData)->save();
    }
}
