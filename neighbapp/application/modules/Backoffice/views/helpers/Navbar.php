<?php

class Zend_View_Helper_Navbar extends Zend_View_Helper_Abstract {

    /**
     * Build Navbar Menu
     *
     * Nb : indexes of parent are use for display submenu
     */
    public function Navbar() {

            $user = Zend_Auth::getInstance()->getStorage()->read();

            return $this->view->partial('partial/navbar.phtml', array('user' => $user));
    }

}
