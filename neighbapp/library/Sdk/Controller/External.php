<?php

class Bundles_Sdk_Controller_External extends Zend_Controller_Action
{
    private $_bundleInstance;
    
    public function init()
    {
        /* Initialize action controller here */
    }

    /**
     * This function will be given to Bouygues Telecom in order for there servers
     * to talk to ours
     * THIS FUNCTION MUST EITHER RETURN 'OK' OR 'CG'!!!!
     * IT IS ALSO ASSUMED THAT WE WILL PASS A SESSION ID AS arg1 WITH THE BILLING URL
     */
    public function callbackAction()
    {	
        //get the request
        $oRequest = $this->getRequest();
        
        $this->view->message = 'An error occured during the payment of your product. '.
                               'Please contact the support team : support@sk.fr';
        
        //if we don't have any get variables, this is a problem
        if( $oRequest->isGet() )
        {
            //get the get fields
            $aGetFields = $oRequest->getQuery();

            $this->loadBundle();
                
	        $aSession = Class_Common_SessionHandler::getSession($aGetFields['c']);
	               
	        try {
                if ($aSession['useMock'] == true ) $this->_bundleInstance->useMock = true;
                $iSubscriptionId = $this->_bundleInstance->createInitialize($aGetFields['c'],$aGetFields);
	            
		        $this->_helper->redirector->gotoUrlAndExit($aSession['linkoK']);
            }
            catch (Exception $e){
            
            	if ($aSession['linkNok']){
            	   $this->_helper->redirector->gotoUrlAndExit($aSession['linkNok']);
            	}
            }
	      }
	      
    }
       
    public function cancelAction() 
    {
        //get the request
        $oRequest = $this->getRequest();
        
        $this->view->message = 'An error occured during the payment of your product. '.
                               'Please contact the support team : support@sk.fr';
        
        //if we don't have any get variables, this is a problem
        if( $oRequest->isGet() )
        {
            //get the get fields
            $aGetFields = $oRequest->getQuery();
            
            $aSession = Class_Common_SessionHandler::getSession($aGetFields['c']);
            
            if(isset($aSession['linkNok']))
                $this->_helper->redirector->gotoUrlAndExit($aSession['linkNok']);
            
            $this->view->message = 'An error occured during the payment of your product. '.
                'Please contact the support team : support@sk.fr.<br/>'.
                'Error : '.$e->getMessage();
        
        }
    }
      
    /**
     * Load the correct bundle from the informations given by the client.
     * @return Bundles_Common_ProviderAbstract
     */
    protected function loadBundle(){
        if (is_null($this->_bundleInstance)){
            try {
                $classes = array(
                        'Sdk', // Aggregator
                        'External',   // Canal
                        ''  // Billing Operator
                );
    
                $className = implode('_',$classes);
                $bundleClass = Class_Common_Utils::loadClass($className,'Bundles_');
                $this->_bundleInstance = new $bundleClass();
                $this->initBundle($bundleClass);
            }catch (ProviderException $e){
                throw new ProviderException_Server_Permanent(
                        $e->getMessage(),
                        $e->getCode()
                );
                Class_Common_Log::error(__METHOD__.': ('.$e->getCode().')'.$e->getMessage());
            }
        }
        return $this->_bundleInstance;
    }
    
    
    /**
     * Initialize the configuration of the bundle.
     * @param string $bundleClass
     * @return void
     */
    protected function initBundle($bundleClass) {
        $confPath = LIBRARY_PATH;
        $dirs = explode('_',$bundleClass);
        $conf = array();
    
        foreach ($dirs as $dir)
        {
            $confPath = str_replace("/Conf","",$confPath);
            $confPath .= '/'.$dir."/Conf";
            if(file_exists(realpath($confPath.'/bundle.ini')))
            {
                unset($array);
                $confini = new Cfe_Config_Ini(realpath($confPath.'/bundle.ini'));
                $array = $confini->toArray();
                if (key_exists(Cfe_Utils::getPlatform(),$array))
                    $conf = array_merge_recursive($conf,$array[Cfe_Utils::getPlatform()]);
                else
                    $conf = array_merge_recursive($conf,$array);
            }
        }
    
        $this->_bundleInstance->init($conf);
    }
}