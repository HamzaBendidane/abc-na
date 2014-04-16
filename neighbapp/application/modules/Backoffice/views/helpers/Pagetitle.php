<?php

class Zend_View_Helper_Pagetitle extends Zend_View_Helper_Abstract {

    /**
     * Build Page title
     *
     * Nb : indexes of parent are use for display submenu
     */
    public function Pagetitle($options = array()) {

        return $this->view->partial('partial/pagetitle.phtml', array('pagetitle' => $this->view->pagetitle
                ));
    }

}
