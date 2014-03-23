<?php

/*
 * Class User for API
 * @author Jeyaganesh Ranjit
 */
class Api_User extends Api_Abstract {
    
    /**
     * Api For User Creation
     * @param string $device_id
     * @param string $device_type
     * @param string $first_name
     * @param string $last_name
     * @param string $login
     * @param string $password
     * @param string $longitude
     * @param string $latitude
     * @param string $rating
     * @param string $email
     * @return Array 
     */
    public function CreateUser($device_id, $device_type, $first_name, $last_name, $login, $password, $longitude, $latitude, $rating, $email){
        
        
        return array();
    }
    
    
    /**
     *
     * @param type $ok
     * @return type 
     */
    public function Test($ok){
        return array($ok);
    }
}