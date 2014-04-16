<?php

/**
 * Form Login Home
 */
class Class_Form_Bootstrap_User extends Twitter_Bootstrap_Form_Horizontal
{
    const NAME = 'user';
    
    public function init()
    {
        
         $ApiUser      = new Api_User();
         $ApiUserGroup = new Api_UserGroup();
         $ApiCompagny  = new Api_Company();
        
         $this->setElementsBelongTo('data');
         
         $this->getElement('email')->setOptions(array(
            'placeholder'   => 'E-mail',
            'prepend'       => '@',
            'class'         => 'focused'
        ));
         
         $this->getElement('type')->addMultiOptions($ApiUserGroup->getListUserGroup());
         
         $this->getElement('parent')->addMultiOptions($ApiUser->getListAllUsers());
         
         $this->getElement('gender')->addMultiOptions(array('Monsieur'=> _('Monsieur'), 'Madame' => _('Madame')));
         
         $this->getElement('company_fk')->addMultiOptions($ApiCompagny->getListAllCompany());
         
         $this->getElement('country')->addMultiOptions(array('FR' => 'FR', 'UK' => 'UK'));
              
    }
}