<?php

/**
 * Labels dans l'aplication
 *
 */
class Class_Common_Label {
    static public function getLabel($index) {

        $list_label = array(
            'id' => 'Id',
            'email' => 'E-mail',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'gender' => 'Gender',
            'actions' => 'Actions',
            'company_zip' => 'compagny zip code',
            'name'=> "Application Version",
            'message'=>    "Message",
            'start_time' => "Send Time",
            'state'=>  "State",
            'certificate_prod'=>  "Production certificate",
            'certificate_dev'=>  "Development certificate",
            'rate'=>  "Rate",
            'token'=>  "Device token",
            'device_name'=>  "Device Name",
            'total_send' => "Total send"
        );
        
        return $list_label[$index];
    }

    static public function getLabelStatusGroup($index) {
        $list_label = array(
            '1' => 'active',
            '0' => 'desactive'
        );

        return $list_label[$index];
    }
}