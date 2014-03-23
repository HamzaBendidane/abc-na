<?php
class IndexController extends Cfe_Controller_Abstract
{
    public function init ()
    {
        /* Initialize action controller here */
    }
    public function indexAction ()
    {
        $modules = array();
        $front = $this->getFrontController();
        $path = $front->getModuleDirectory();
        $default = $front->getDefaultModule();
        $modulePath = dirname($path);
        $handle = opendir($modulePath);
        while (($file = readdir($handle)) !== false) {
            if(is_dir($modulePath.'/'.$file) && strncmp($file,'.',1) != 0 && $file != $default) {
                $modules[$file] = $this->getUrl($file);
            }
        }
        $this->view->modules = $modules;
    }
    public function infoAction ()
    {
        $this->_helper->viewRenderer->setNoRender();
        phpinfo();
    }
}
