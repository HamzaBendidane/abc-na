<?php
require_once 'Cfe/Controller/Secured.php';

class Cfe_Controller_Index extends Cfe_Controller_Secured
{
    public function init ()
    {
        /* Initialize action controller here */
    }
    public function indexAction ()
    {
        $controllers = array();
        $front = $this->getFrontController();
        $modulePath = $front->getModuleDirectory().'/'.$front->getModuleControllerDirectoryName();

        $handle = opendir($modulePath);
        while (($file = readdir($handle)) !== false) {
            if(!is_dir($modulePath.'/'.$file) && substr($file, -14) == 'Controller.php') {
                $controller = substr($file, 0, -14);
                if(!in_array($controller, array('Error', 'Index'))) {
                    $controllers[$controller] = $this->getUrl(null, $controller);
                }
            }
        }
        $this->view->controllers = $controllers;
    }
    public function infoAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        phpinfo();
    }
}

