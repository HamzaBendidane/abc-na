<?php

/**
 * Example of model DB class
 * @author Jeyaganesh RANJIT
 */
class Class_Db_User extends Class_Db_Abstract {

    protected $_name = 'user';
    protected $_adapter = 'neighbapp';

    
    /**
     * Check if email is already used or not
     * @param string $email
     * @return boolean 
     */
    public function checkEmail($email){
        
        $queryEmail = $this->getAdapter()->select()
                ->from($this->_name)
                ->where('email = ?',$email);
        $emailExist =  $this->getAdapter()->fetchRow($queryEmail);
        
        return ($emailExist)?true:false;
    }
    
    
    /**
     * Creation of User
     * @param Array $data
     * @return Boolean 
     */
    public function createUser($data){
        
        $this->getAdapter()->insert($this->_name,$data);
        
        return $this->getAdapter()->lastInsertId();
    }
    
}
