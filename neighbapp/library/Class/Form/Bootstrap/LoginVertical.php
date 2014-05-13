<?php

/**
 * Form Login Home
 */
class Class_Form_Bootstrap_LoginVertical extends Twitter_Bootstrap_Form_Vertical
{
    const NAME = 'login';
    
    public function init()
    {
        
        $this->getElement('login')->setOptions(array(
            'placeholder'   => 'E-mail',
            'prepend'       => '@',
            'class'         => 'focused'
            ))->removeDecorator('label');
        
        $this->getElement('password')->removeDecorator('label');
        
        $this->getElement('submit')->setOptions(array(
            'class'         => 'btn btn-primary'
        ));
    }
}