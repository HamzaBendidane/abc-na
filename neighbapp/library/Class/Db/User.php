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
        
        try {
            $creation = $this->getAdapter()->insert($this->_name,$data);
            if($creation === 1){
                return $this->getAdapter()->lastInsertId();
            }else{
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }

        
    }
    
    /**
     * Check if login is already used or not
     * @param string login
     * @return boolean 
     */
    public function checkLogin($login){
        
        $queryLogin = $this->getAdapter()->select()
                ->from($this->_name)
                ->where('login = ?',$login);
        $loginExist =  $this->getAdapter()->fetchRow($queryLogin);
        
        return ($loginExist)?true:false;
    }
    
    /**
     * Get user by login and password
     * @param string $login
     * @param string $password
     * @return boolean 
     */
    public function GetUserByLogin($login,$password){
        
        $queryLogin = $this->getAdapter()->select()
                ->from($this->_name)
                ->where('login = ?',$login)
                ->where('password = ?',$password);
        $loginExist =  $this->getAdapter()->fetchRow($queryLogin);
        
        return ($loginExist)?$loginExist:false;
    }
    
    /**
     * Update User Information
     * @param array $aData
     * @param int $userId
     * @return boolean 
     */
    public function updateUser($aData,$userId){
        
        try {
            $where = "id = $userId";
            $update = $this->getAdapter()->update($this->_name,$aData,$where);
            if($update === 1){
                return true;
            }else{
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
    }
    
}
