<?php

/**
 * Form Login Home
 */
class Class_Form_Bootstrap_User extends Twitter_Bootstrap_Form_Horizontal
{
    const NAME = 'user';
    
    public function init()
    {
        
         $this->setElementsBelongTo('data');
         
         $this->getElement('email')->setOptions(array(
            'placeholder'   => 'E-mail',
            'prepend'       => '@',
            'class'         => 'focused'
        ));
         
         $this->getElement('gender')->addMultiOptions(array('Monsieur'=> _('Monsieur'), 'Madame' => _('Madame')));
                       
    }
}