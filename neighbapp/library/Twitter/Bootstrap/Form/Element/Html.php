<?php

class Twitter_Bootstrap_Form_Element_Html extends Zend_Form_Element_Xhtml
{
     // On utilise l'aide de vue formNote
    public $helper = 'formNote';

    public function loadDefaultDecorators()
    {
        // Le seul décorateur dont on a besoin, c'est celui qui appelle formNote
        $this->addDecorator('ViewHelper');
    }
}
