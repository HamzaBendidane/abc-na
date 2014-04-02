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
    public function ArroudMe($longitude,$latitude,$rayon){
        $users = $finalUser = array();
        $userModel = new Class_Db_User();
        $usersDb = $userModel->GetUserArroundMe($longitude, $latitude, $rayon);
        
        foreach($usersDb as $user){
            $d1 = new DateTime($user['start_date']); 
            $d2 = new DateTime($user['end_date']); 
            $diff = $d1->diff($d2); 
            $nb_jours = $diff->d; 
            $finalUser['distance'] = round($user['dist'],2) * 100;
            $finalUser['picture'] = $user['picture'];
            $finalUser['first_name'] = ucfirst($user['first_name']);
            $finalUser['start_date'] = (!is_null($user['start_date']))?date('d/m/Y',strtotime($user['start_date'])):NULL;
            $finalUser['day_left'] = (!is_null($user['start_date']))?$nb_jours:NULL;
            $finalUser['title'] = (!empty($user['title']))?$user['title']:NULL;
            
            $users[] = $finalUser;
        }
        return $users;
    }
}