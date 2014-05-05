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
            $transactions = array();
            $d1 = new DateTime(date("Y-m-d H:i:s")); 
            $finalUser['distance'] = round($user['dist'], 2) * 100;
            $finalUser['picture'] = $user['picture'];
            $finalUser['first_name'] = ucfirst($user['first_name']);
            $finalUser['user_id'] = $user['id'];
            foreach ($user['requests'] as $transaction) {
                $d2 = new DateTime($transaction['end_date']); 
                $diff = $d1->diff($d2); 
                $nb_jours = $diff->d;  
                $transactionUser['duration'] = (!is_null($transaction['start_date'])) ? $nb_jours * 60 * 60 * 24 : NULL;
                $transactionUser['start_date'] = (!is_null($transaction['start_date'])) ? strtotime($transaction['start_date']) : NULL;
                $transactionUser['title'] = (!empty($transaction['title'])) ? $transaction['title'] : NULL;
                $transactionUser['short_desc'] = $transaction['short_desc'];
                $transactions[] = $transactionUser;
            }
            $finalUser['requests'] = $transactions;
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
            return array("success" => 0, "error" => 32014);
        }
        
        $return['success'] = 1;
        $return['transaction'] = $detail;
        return $return;
    }
}