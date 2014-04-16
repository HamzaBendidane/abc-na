<?php
/**
 * Controller for predispatch ControllerAction of every pages on the Backoffice Numbate
 *
 * Check Auth / ACL
 * Set Layout
 * Set config
 */
abstract class Class_Controller_BackofficeAction extends Zend_Controller_Action
{
    /**
     * Conf/backoffice.ini
     * Zend_Config_Ini
     */
    protected $_config;
    
    /**
     * short cut to test if user is connected
     */
    protected $_user_is_connected;
    
    /**
     * User_info
     * @var type array
     */
    protected $_user_info;
    
    /**
     * Acl
     * @var type array
     */
    protected $_acl;
    
    /**
     * Controller Global_acces restricted / public
     * String
     * @todo : Add ACL
     */    
    protected $_global_access = 'restricted';
    
    
    function init(){
    	
    	parent::init ();
    	$this->preDispatch();
    }
    /**
     * Predispatch for All controller Action
     */
    public function preDispatch()
    {      
    	
        $this->_user_is_connected = Zend_Auth::getInstance()->hasIdentity();
        // Config
        
        // Get controller and action
        $params = $this->_request->getParams();
        $controller = $params['controller'];
        $action = $params['action'];
        
        // @todo Add ACL
         if($this->_global_access != 'public'){
                                    
            if($this->_user_is_connected){
                $const = Cfe_Numbate_Rights::$groupLabel;
                $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();

                $this->_acl = $this->_user_info->acl;
                // Info User and device
                
                // Get controller and action for ACL
                $params = $this->_request->getParams();
                $controller = $params['controller'];
                $action = $params['action'];
                
                // Get All resource from ACL
                $allRessources =  $this->_acl->getResources();
                
                // Check if controller is in ACL
                if((in_array($controller,$allRessources))){
                    // Check if user have rights for this action
                    if(!$this->_acl->isAllowed($this->_user_info->type,$controller,$action)){
                    	
                        $this->_helper->redirector->gotoSimpleAndExit('index', 'login', 'Backoffice');
                    }
                    
                }else{
                    $this->_helper->redirector->gotoSimpleAndExit('index', 'login', 'Backoffice');
                }
                
             
            }
            else{
                // Redirect to index Form login
                $this->_helper->redirector->gotoSimpleAndExit('index', 'login', 'Backoffice');
            }
        }
        
        
        $this->_config = new Zend_Config_Ini(
                CONFIG_PATH .'/backoffice.ini',
                Cfe_Utils::getPlatform()
        );
                
        Zend_Layout::startMvc($this->_config->resources->layout);           
        
        $this->view->config = $this->_config;
        
        // Default title page
        $this->view->pagetitle = $controller;
    }
}
