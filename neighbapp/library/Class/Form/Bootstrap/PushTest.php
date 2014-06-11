<?php

/**
 * Form Login Home
 */
class Class_Form_Bootstrap_PushTest extends Twitter_Bootstrap_Form_Horizontal
{
    const NAME = 'push_test';
    
    public function init()
    {
        
         $ApiPush     = new Api_Push();
        
         $this->setElementsBelongTo('data');
         
         $this->getElement('device_id')->addMultiOptions($ApiPush->GetListDevice());
         
         $this->getElement('version_id')->addMultiOptions($ApiPush->GetListVersion());
              
    }
}