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
            'company_zip' => 'compagny zip code'
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