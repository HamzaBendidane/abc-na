<?php

/**
 * Form Login Home
 */
class Class_Form_Bootstrap_Push extends Twitter_Bootstrap_Form_Horizontal
{
    const NAME = 'push';
    
    public function init()
    {
        
        $this->setElementsBelongTo('data');
        $return = array();
        $apiPush = new Api_Push();
        $versions = $apiPush->GetAllPushVersion();
        foreach($versions as $version){
            $return[$version['id']] = $version['name'];
        }
        
        $this->getElement('version_id')->addMultiOptions($return);
    }
}