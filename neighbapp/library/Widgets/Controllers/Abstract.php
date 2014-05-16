<?php

class Widgets_Controllers_Abstract extends Zend_View_Helper_Abstract {

    /**
     * @var Array 
     */
    protected $data;
    
    protected $_options;


    /*
     * Nombre d'instance de widgets
     */
    protected static $id_instance = 0;

    public function __construct($data, $options= array()) {

        self::$id_instance ++ ;
       $this->data = $data;
        $this->_options = $options;        

        $view = Zend_Layout::getMvcInstance()->getView();

        $this->setView($view);

        $view->addBasePath(LIBRARY_PATH .'/Widgets/views/');

    }

    protected function render() {
        $this->addCss();
        $this->addJs();
    }

    protected function addCss() {

        //@todo : selon le context : ajouter ou non le script au Header

        foreach ($this->css as $css) {
            $this->view->HeadLink()->appendStylesheet($css);
        }
    }

    protected function addJs() {

        foreach ($this->js as $js) {
            $this->view->HeadScript()->appendFile($js);
        }
    }

}
