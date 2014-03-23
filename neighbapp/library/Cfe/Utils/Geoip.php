<?php

/**
 * Utils pour récupérer la localisation avec GeoIp
 */

class Cfe_Utils_Geoip {
    
    /**
     * Return le code Country à l'aide de GEO IP
     * @return string code pays à 2 caractères
     */
    
    
    static public function getCountryCodebyGeoIp (){
        
           $code_country = @geoip_country_code_by_name($_SERVER['REMOTE_ADDR']);
           
           if(!$code_country){
               return 'NR';
           }
           
           return $code_country;
    }
}
