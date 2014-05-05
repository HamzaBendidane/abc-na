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
     * @param string $adid
     * @return Array 
     */
    public function CreateUser($device_id, $device_type, $first_name, $last_name, $login, $password, $longitude, $latitude, $rating, $email,$adid){

        $userModel = new Class_Db_User();
        $userValidation = new Class_Validation_User();
        
        $emailExist = $userValidation->emailExist($email);
        
        if($emailExist){
            return array("success" => 0, "error" => 32001);
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
        
        if($createUser !== false){
            $deviceModel = new Class_Db_Push_Ios_Device();
            $updadeDevice = $deviceModel->updateDeviceByAdid(array('user_FK' => $createUser), $adid);
            return array("success" => 1);
        }else{
            return array("success" => 0, "error" => 32005);
        }
        
    }
    
    /**
     * User Login 
     * @param string $login
     * @param string $password
     * @param string $longitude
     * @param string $latitude 
     * @return array
     */
    public function Login($login,$password,$longitude,$latitude){
        $userModel = new Class_Db_User();
        
        /** Check if login exist in our database **/
        $checkUser = $userModel->checkLogin($login);
        if($checkUser == false){
            return array("success" => 0, "error" => 32004);
        }
        
        
        /** Check login/password in database **/
        $user = $userModel->GetUserByLogin($login,$password);
        if($user == false){
            return array("success" => 0, "error" => 32003);
        }
        
        
        /** Update user position in our database **/
        $aData = array(
            'longitude' =>  $longitude,
            'latitude'  =>  $latitude,
        );
        
        $userUpdate = $userModel->updateUser($aData,$user['id']);
        
        if($userUpdate === true){
            $user['longitude'] = $longitude;
            $user['latitude'] = $latitude;
        }else{
            return array("success" => 0, "error" => 32002);
        }
        
        return array("success" => 1, "data" => $user);
    }   
    
    /**
     * Update User Profil
     * @param string $first_name
     * @param string $last_name
     * @param string $login
     * @param string $password
     * @param string $email
     * @param int $userId
     * @return boolean 
     */
    public function UpdateUserInfo($first_name, $last_name, $login, $password, $userId, $email = false){
        $success = false;
        $userModel = new Class_Db_User();
        
        $aData = array(
          "first_name"  =>  $first_name,
          "last_name"  =>  $last_name,
          "login"      =>  $login,
          "password"   =>  $password
        );
                
        if($email){
            $checkEmail = $userModel->checkEmail($email);

            if($checkEmail == true){
                return array("success" => 0, "error" => 32001);
            }
            $aData['email'] = $email;
        }
        
        $success = $userModel->updateUser($aData,$userId);
        
        if($success === false){
            return array("success" => 0, "error" => 32002);
        }
        
        return array("success" => 1);
    }
    
    /**
     * Update User Picture
     * @param string $url
     * @param int $userId
     * @return array 
     */
    public function UpdateUserPicture($url, $userId){
        $success = false;
        $userModel = new Class_Db_User();
        
        if(!filter_var($url, FILTER_VALIDATE_URL)){
            return array("success" => 0, "error" => 32008);
        }
        
        $aData = array(
          "picture"  =>  $url
        );
        
        $success = $userModel->updateUser($aData,$userId);
        
        if($success === false){
            return array("success" => 0, "error" => 32009);
        }
        
        return array("success" => 1);
    }
    
    
    /**
     * Get User Picture
     * @param int $userId
     * @return string 
     */
    public function GetUserPicture($userId){
        $userModel = new Class_Db_User();
        $userPictur = $userModel->GetUserById($userId);
        
        if($userPictur === false){
            return array("success" => 0, "error" => 32015);
        }
        
        return array("success" => 1,"url" => $userPictur['picture']);
    }
}