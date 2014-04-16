<?php

/**
 * Form Login Home
 */
class Class_Form_Bootstrap_Delete extends Twitter_Bootstrap_Form_Inline
{
    const NAME = 'delete';
    
    public function init()
    {
        
    }
    
    public function setId($id)
    {
        
        $this->getElement('id')->setValue($id);
        
        $this->getElement('valide')->setOptions(array(
            'buttonType'    => 'success',
            'icon'          => 'ok',
            'escape'        => false
        ));
        
    }
}