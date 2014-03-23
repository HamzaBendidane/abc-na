<?php

/*
 * Class User for API
 * @author Jeyaganesh Ranjit
 */
class Api_User extends Api_Abstract {
    
    /**
     * Api For User Creation
     * @param int    $device_id
     * @param string $device_type
     * @param string $first_name
     * @param string $last_name
     * @param string $login
     * @param string $password
     * @param float  $longitude
     * @param float  $latitude
     * @param int    $rating
     * @param string $email
     * @return Array 
     */
    public function CreateUser($device_id, $device_type, $first_name, $last_name, $login, $password, $longitude, $latitude, $rating, $email){
        
        $userModel = new Class_Db_User();
        $userValidation = new Class_Validation_User();
        
        $emailExist = $userValidation->emailExist($email);
        
        if($emailExist){
            return array("message"   => "Email Allready used");
        }
        
        // on save le user
        $dataInsert = array(
            'device_id'    => $device_id,
            'device_type'  => $device_type,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'login'        => $login,
            'password'     => $password,
            'longitude'    => $longitude,
            'latitude'     => $latitude,
            'rating'       => $rating,
            'email'        => $email,
        );
        
        $createUser = $userModel->createUser($dataInsert);
        
        return array("success" => true);
    }
}