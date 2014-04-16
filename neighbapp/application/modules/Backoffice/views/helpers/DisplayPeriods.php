<?php 

class Zend_View_Helper_Displayperiods extends Zend_View_Helper_Abstract{
     
    /**
     * Build NavBar
     * @return string
     */

    public function Displayperiods($id=false) {
	
    
    $user = Zend_Auth::getInstance()->getStorage()->read();
 	$params = Zend_Controller_Front::getInstance ()->getRequest();
 	if(!$id){
 		$id=$params->period;
 	}
 	$periods = Class_Common_TimePeriods::getPeriodNames();
 	$period = isset($id) ? $id : 0;
            return $this->view->partial('partial/display_periods.phtml', array("selectedOption" => $period, "periods"=>$periods));
        
    }
         
}
