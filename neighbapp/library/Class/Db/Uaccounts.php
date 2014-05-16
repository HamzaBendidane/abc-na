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
}
