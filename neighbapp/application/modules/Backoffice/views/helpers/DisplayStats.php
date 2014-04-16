<?php 

class Zend_View_Helper_Displaystats extends Zend_View_Helper_Abstract{
     
    /**
     * Build Stats bar
     * @return string
     */

    public function Displaystats($statistics) {

 	$spanClass = ceil(count($statistics)/12);
            return $this->view->partial('partial/display_stats.phtml', array("statistics" => $statistics, 'spanClass'=>$spanClass));
        
    }
         
}
