<?php
class Zend_View_Helper_Menugraph extends Zend_View_Helper_Abstract {
	
	/**
	 * Build Sidebar Menu
	 *
	 * Nb : indexes of parent are use for display submenu
	 */
	public function Menugraph($options  = FALSE) {
		
		return $this->view->partial ( 'partial/menugraph.phtml',array("filters" => $options));
	}
}
