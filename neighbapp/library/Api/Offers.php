<?php

/*
 * Class Offers for API
 * @author Jeyaganesh Ranjit
 */
class Api_Offers extends Api_Abstract {
    
    /**
     * Get all users Arround me who have offers
     * @param string $longitude
     * @param string $latitude
     * @param int $rayon
     * @return array 
     */
    public function ArroundMe($longitude,$latitude,$rayon){
        $users = $return = $finalUser = array();
        $userModel = new Class_Db_User();
        
        if(!is_numeric($longitude)|| !is_numeric($latitude) || !is_numeric($rayon)){
            return array("success" => 0, "error" => 32007);
        }
        
        $usersDb = $userModel->GetUserArroundMe($longitude, $latitude, $rayon);
        
        foreach($usersDb as $user){
            $d1 = new DateTime(date("Y-m-d H:i:s")); 
            $d2 = new DateTime($user['end_date']); 
            $diff = $d1->diff($d2); 
            $nb_jours = $diff->d; 
            $finalUser['distance'] = round($user['dist'],2) * 100;
            $finalUser['picture'] = $user['picture'];
            $finalUser['first_name'] = ucfirst($user['first_name']);
            $finalUser['start_date'] = (!is_null($user['start_date']))?date('d/m/Y',strtotime($user['start_date'])):NULL;
            $finalUser['day_left'] = (!is_null($user['start_date']))?$nb_jours:NULL;
            $finalUser['title'] = (!empty($user['title']))?$user['title']:NULL;
            $finalUser['user_id'] = $user['id'];
            
            $users[] = $finalUser;
        }
        
        $return['success'] = 1;
        $return['users'] = $users;
        return $return;
    }
    
    /**
     * Get Transaction Detail
     * @param int $transactionId
     * @return array 
     */
    public function GetDemandDetail($transactionId){
        $detail = $return = array();
        $transactionModel = new Class_Db_Transaction();

        if($transactionId == 0){
            return array("success" => 0, "error" => 32011);
        }
        
        $detail = $transactionModel->getDetailTransaction($transactionId);
        
        if($detail == false){
            return array("success" => 0, "error" => 32012);
        }
        
        $return['success'] = 1;
        $return['transaction'] = $detail;
        return $return;
    }
}