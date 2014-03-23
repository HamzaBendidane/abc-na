<?php

/**
 * Class validation for User
 * @author Jeyaganesh RANJIT
 */
class Class_Validation_User extends Api_Abstract {
    
    /**
     * Check if email is already used or not
     * @param string $email
     * @return boolean 
     */
    public function emailExist($email){
        $userModel = new Class_Db_User();
        return $userModel->checkEmail($email);
    }   
}
