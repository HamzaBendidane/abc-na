<?php

/**
 * Form company Home
 * @author Jeyaganesh RANJIT
 */
class Class_Form_Bootstrap_Company extends Twitter_Bootstrap_Form_Horizontal{

    const NAME = 'company';
    public function init() {
        $list = Cfe_Numbate_Critarea::$list_Company_Type;
        
        $this->getElement('type')->addMultiOptions($list);
        $this->setElementsBelongTo('data');
        $this->getElement('submit')->setOptions(array(
            'class' => 'btn btn-inverse btn-large'
        ));
    }

}