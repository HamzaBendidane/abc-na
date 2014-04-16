<?php

class Zend_View_Helper_Navbar extends Zend_View_Helper_Abstract {

    /**
     * Build Navbar Menu
     *
     * Nb : indexes of parent are use for display submenu
     */
    public function Navbar($options = array()) {

    	$moduleNavigationData = new Modules_Backoffice_Navigation_Data();
    	
    	$menu = $moduleNavigationData->getNavigationData();
    	if ($menu == FALSE) {
    		return '';
    	}
    	
	
        if (isset($options ['parent'])) {
            $menu = $menu [$options ['parent']] ['childs'];
        }

        return $this->view->partial('partial/navbar.phtml', array(
                    'menu' => $menu,
                    'user' => Zend_Auth::getInstance()->getStorage()->read()
                ));
    }

}
