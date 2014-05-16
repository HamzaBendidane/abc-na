<?php
class Widgets_Controllers_Chartflot extends Widgets_Controllers_Abstract {
    
    
    /**
     * Construit les Graphs avec Jquery chart plot
     * http://www.flotcharts.org/
     * 
     * exemple de data attendu :
     * 
     * @return string
     */
    
    protected $css = array(
        
    );
    
    protected $js  = array(
        '/web/themes/blackbuck/js/jquery.flot.js',
        '/web/themes/blackbuck/js/jquery.flot.stack.js',
        '/web/themes/blackbuck/js/jquery.flot.resize.js'
    );

    public function render() {
         
        parent::render();
      
        return $this->view->partial('chartflot.phtml', array('alldata' => $this->data, 'id_instance' => self::$id_instance ));
    }
}