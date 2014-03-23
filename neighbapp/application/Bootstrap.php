<?php  
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function run()
    {
        // make the config available to everyone
        $config = $this->getOptions();
        date_default_timezone_set($config['system']['timezone']);
        parent::run();
    }

    protected function _initFrontModules ()
    {
        $this->bootstrap('frontController');
        $front = $this->getResource('frontController');
        $front->addModuleDirectory(APPLICATION_PATH . '/modules');
        
        //print_r($_SERVER);
    }

    public function _initDbAdaptersToRegistry()
    {
    	$this->bootstrap('frontController');
    	$resource = $this->getPluginResource('multidb');
    	$resource->init();
        
        $adapter1 = $resource->getDb('neighbapp');
        Zend_Registry::set('neighbapp', $adapter1);
        
        Zend_Db_Table::setDefaultAdapter($adapter1);
        
/* CONNEXION TO MULTIPLE DB EXEMPLE
    	if (APP == 'AX-IOS'){
	    	$adapter1 = $resource->getDb('pushuk');
	    	Zend_Registry::set('push', $adapter1);
                
	    	$adapter2 = $resource->getDb('iosdbuk');
	    	Zend_Registry::set('iosdb', $adapter2);
	    	
	    	Zend_Db_Table::setDefaultAdapter($adapter2);
    	}
    	else if (APP == 'AP-IOS'){
    		$adapter1 = $resource->getDb('pushfr');
    		Zend_Registry::set('push', $adapter1);
    		
    		$adapter2 = $resource->getDb('iosdbfr');
    		Zend_Registry::set('iosdb', $adapter2);    		
    		
    		$adapter3 = $resource->getDb('androiddbfr');
    		Zend_Registry::set('androiddb', $adapter3);
    		
    		Zend_Db_Table::setDefaultAdapter($adapter2);
    	}
    	else{
    		die("NO APP DEFINED");
    	}
 * 
 */
    }

    /**
     * Initialize routes selon env "module" et pour Webservice IOS
     *
     */
   protected function _initRoutes(){

       $front = $this->getResource('frontController');
       
       // ROUTE pour les Autres Modules
       
           $route = $front->getRouter();
           
           $request = new Zend_Controller_Request_Http();
           
           $action = 'server';
           $controller = 'Rest';
           
         
           $route->addRoute('default', new Zend_Controller_Router_Route(
                   '/*', array(
                           'module'      => 'services',
                           'controller' => $controller,
                           'action'     => $action
                   )
           ));
       
    }
    
    
    /**
     * Initialize Translation
     * 
     * Pour l'instant, on utilise php-Gettext: ça peut entrainer des problèmes sur des environements multi-threaded ???
     * 
     * @todo : Voir comment utiliser Zend_Translate : pb : redefinir la function _() dans toute l'application ou remplacer partout _() par $translate->_()
     *
     * @return void
     */
    public function _initTranslate()
    {    
        
        //$language = (APP == 'AX-IOS') ? 'en_UK' : 'fr_FR'; EXEMPLE FOR MULTIPLE LANGUAGE
        $language = 'fr_FR';
                 
        $locale = new Zend_Locale($language);

       
       $directory = LANGUAGES_PATH;
       $domain = 'default';
       $locale = $locale . ".utf8";
       
       // Chargement des traductions des webservices : utiles pour tous les modules
       setlocale( LC_MESSAGES, $locale);
       
       // Ajout des autres traductions selon le module
       //!\\ qu'un seul fichier de langue par application : redondance de la traduction pour les webservices
       if(getenv('MODULE')){
           textdomain(getenv('MODULE'));
           bindtextdomain(getenv('MODULE'), $directory);
           bind_textdomain_codeset(getenv('MODULE'), 'UTF-8');
          
       }else{
           textdomain('default');
           bindtextdomain('default', $directory);       
           bind_textdomain_codeset('default', 'UTF-8');
       }
    }
}