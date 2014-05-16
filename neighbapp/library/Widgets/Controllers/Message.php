<?php
class Widgets_Controllers_Message extends Widgets_Controllers_Abstract {
    
    
 
    protected $css = array(
        '/web/themes/blackbuck/tables.css'
    );
    
    protected $js  = array(
        '/web/js/jquery.dataTables.min.js',
        '/web/js/jquery.dataTables.extend.js'
    );

    public function render() {
        
        parent::render();
        
        return $this->view->partial('message.phtml',array("message" => $this->data));
    
    }
}