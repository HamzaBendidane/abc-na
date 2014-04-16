<?php

/**
 * @author aris dieudop <aris@surikate.com>
 */
class Class_Form_Bootstrap_Analytics extends Twitter_Bootstrap_Form_Horizontal {

    const NAME = 'analytics';
    
    public function init() {

        $this->setName('analytics');
        $id = new Zend_Form_Element_Hidden('id');
		// 
       	$gender = new Zend_Form_Element_Radio('gender');
    	$gender->setLabel('Period:')
      	->addMultiOptions(array(
	        'yesterday' => 'YesterDay',
			'today' => 'ToDay',
			'lastweek' => 'Last Week',
			'others' => 'Others'
     	 ))
      	->setSeparator('');
       
        $optionObject = new Api_Reporting();
        $OptionArraySelectOne = $optionObject->getListCritareaSelectOne();
        $allOptions=$optionObject->getListCritarea();
        $optionsOne = new Zend_Form_Element_Select('SelectOne',array('onchange' => 'loadDeviceId();'));
        $optionsOne->setLabel('Options: ')
                ->setMultiOptions($OptionArraySelectOne);
                
        $optionsTwo = new Zend_Form_Element_Select('SelectTwo');
        $optionsTwo->setLabel('Options: ')
                ->setMultiOptions($allOptions);
        
        $campaignObject = new Api_Reporting();
        $allCampaignArray = $campaignObject->getCampaignsByPlacement();
        
        $campaign = new Zend_Form_Element_Select( ' Campagne');
        $campaign->setLabel(' Display: ')
                ->setMultiOptions($allCampaignArray);
        $optionsOthers=$optionObject->getListCritareaOptions();
		$checkbox = new Zend_Form_Element_MultiCheckbox('checkbox', array('multiOptions' => $optionsOthers));
		$checkbox->setSeparator('');
		

        $save = new Zend_Form_Element_Submit('Generate');
        $save->setAttrib('id', 'generate_button');

        $this->addElements(array($id,$gender,$optionsOne, $campaign,$optionsTwo,$checkbox, $save));
    }

}
