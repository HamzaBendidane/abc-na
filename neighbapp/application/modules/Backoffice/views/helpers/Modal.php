<?php

class Zend_View_Helper_Modal extends Zend_View_Helper_Abstract {

    /**
     * Build Navbar Menu
     *
     * Nb : indexes of parent are use for display submenu
     */
    public function Modal() {
            return $this->view->partial('partial/modal.phtml');
    }

}
