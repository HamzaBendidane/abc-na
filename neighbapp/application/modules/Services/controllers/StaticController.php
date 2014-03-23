<?php
/**
 * Controller Action pour WebViews Statiques Ios
 * @todo switch entre le conteu français et Anglais à faire
 */
class Services_StaticController extends Zend_Controller_Action
{
   
    public function init()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $options = $bootstrap->getOptions();
        $layout = $options['ios']['resources']['layout'];
        Zend_Layout::startMvc($layout);
        
        // Plateform
        $plateform = (getenv("APP") == 'AX-IOS') ? 'appxpert' : 'appliprivee';
        $this->view->plateform = $plateform;
        $this->view->lang = (getenv('APP') == 'AX-IOS') ? 'en' : 'fr';
    }

    /*
     * Proxy vers les pages statiques
     * Passage obligé à cause du router dans boostrapp
     */
    public function proxyAction(){
        $static_action = $this->_request->getParam('static') . 'Action';
        
       $this->_helper->viewRenderer->setNoRender();
        
        if(method_exists($this, $static_action)) {
            return $this->$static_action();
        }
        else {
            die('404');
        } 
    }
   
    
    /*
     * Proxy vers les pages EXTRA
    */
    public function extraAction(){
        
        $request = $this->getRequest();
        //@todo $request->isPost ne marche pas avec les données venant de l'application
        if($request->getparam('userid')){
        	
            $response = $this->getResponse();
        
            $extraActionController = new Class_Campaign_Extra_Controller($request, $response);
            $extraActionController->getExtra();
        }else {
            throw new Exception('No access');
        }
    }

    /**
     * Affiche les CGU
     */
    protected function cguAction()
    {        
        $this->view->title_page = _('CGU');
        $this->renderScript('static/'. APP .'/cgu.phtml');
    }
    
    /**
     * Affiche le tuto
     */
    protected function tutoAction()
    {
        /* @temporaire */

        if(APP == "AP-IOS"){
          // $this->renderScript('static/'. APP .'/tuto.phtml');
            $this->_helper->redirector->setGotoUrl('https://surikatesk.zendesk.com/home');      
        }
        else {
            $this->_helper->redirector->setGotoUrl('http://appxpert.zendesk.com/forums/21006593-FAQ');
        }
        
        
    }
    
    /**
     * Affiche le support
     */
    protected function supportAction()
    {
        /* @temporaire */
        if(APP == "AP-IOS"){
            //$this->renderScript('static/'.APP.'/contact.phtml');
            
            $this->_helper->redirector->setGotoUrl('https://surikatesk.zendesk.com/forums');
        }
        else {
            $this->_helper->redirector->setGotoUrl('https://appxpert.zendesk.com/forums/21006593-FAQ');
        }
        
    	/*$request = $this->getRequest();
    	if($request->isPost()){
    		$this->view->issend = true;
    	}
    	
    	$this->renderScript('static/contact.phtml');
    	*/
    }
    
    /**
     * Page contact : provisoir
     */
    protected function contactAction()
    {
        if(APP == "AP-IOS"){
            /*$request = $this->getRequest();
            if($request->isPost()){
                $this->view->issend = true;
            }
            
            $this->renderScript('static/'.APP.'/contact.phtml');
             */
             $this->_helper->redirector->setGotoUrl('https://surikatesk.zendesk.com/home');
        }
        else {
            $this->_helper->redirector->setGotoUrl('https://appxpert.zendesk.com/home');
        }                
    } 
    /**
     * Page contact
     */
    protected function withdrawnAction()
    {
     
        
        $this->renderScript('static/'.APP.'/withdrawn.phtml');
    
    
        //$this->_helper->redirector->setGotoUrl('https://appxpert.zendesk.com/home');
    } 

    /**
    * redirect
    */
    protected function redirectAction()
    {
    	  if(APP == "AP-IOS"){
               $this->_helper->redirector->setGotoUrl('appliprivee://myaccount');
          }else
          {
              $this->_helper->redirector->setGotoUrl('appxpert://myaccount');
          }
    }  
    
    /**
     * 
     * Ce controlleur static sert de proxy, elle recupère la requête, recupère les données (données lié à l'appli et les images) via le controlleur class/Appduweek/controlleur
     * elle charge ensuite le layout et affiche la vue
     * @param $id_display qui est ici l'id app du week end
     * @return une vue uk ou fr
     * @author Aris Dieudop
     */    
    protected function appduweekAction(){
        
        $classAppDuWeek = new Class_Appduweek_Controller();      
        $request = $this->getRequest();         
        $id_display = $request->getParam('display');
        
        $config = new Zend_Config_Ini(
                CONFIG_PATH . '/' . APP . '/media.ini',
                Cfe_Utils::getPlatform()
        );
        // Layout
        $layout = Zend_Layout::getMvcInstance();
        $layout->setLayout('appduweek');       
        
        $dataAppduweek = $classAppDuWeek->display($id_display);
        $logos = $classAppDuWeek->displaylogo($id_display);
       
        $data = array_merge($dataAppduweek,$logos);
        
        $this->view->config = $config;
        
        $this->view->data = $data;
        
        //rendu en htmlentities
        if($request->getParam('entities')){
        	$this->view->entities = true;
        }

        //Notre View en fonction du pays
        $this->renderScript('appduweek/'.APP.'/display.phtml');
             
    }
}