<?php 

class Zend_View_Helper_Topbar extends Zend_View_Helper_Abstract{
     
    /**
     * Build TopBar
     * Search input, notifications ... 
     * @return string (html)
     */

    public function Topbar() {
 
            return $this->view->partial('partial/topbar.phtml', array());
        
    }
         
}
