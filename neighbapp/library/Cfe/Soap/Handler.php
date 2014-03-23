<?php

class Cfe_Soap_Handler {
    const AUTH_ERROR_NO_HEADERS   = 1;
    const AUTH_ERROR_NOT_BASIC    = 2;
    const AUTH_ERROR_INVALID_AUTH = 3;
    const AUTH_ERROR_INVALID_USER = 4;
    const AUTH_ERROR_INVALID_ACCREDITATION = 5;

    protected $auth = null;

    protected function getClassmap( $wsdlUrl) {
        $md5Wsdl = md5($wsdlUrl);
        $key = __CLASS__ . ':' . md5($wsdlUrl);
        if (function_exists ( 'apc_fetch' )) {
            $classMap = apc_fetch ( $key, $success );
            if ($success) {
                // @codeCoverageIgnoreStart
                // APC won't work on cli ...
                return $classMap;
                // @codeCoverageIgnoreEnd
            }
        }
        try {
            list($login, $password) = $this->getAuth();
            $option = array('login' => $login, 'password' => $password);
        } catch (Exception $e) {
            $option = array();
        }
        if (Cfe_Utils::getPlatform() == 'dev') {
            $option['cache_wsdl'] = WSDL_CACHE_NONE;
            ini_set("soap.wsdl_cache_enabled", "0");
        }
        $client = new Soapclient($wsdlUrl, $option);
        $classMap = array();
        foreach ($client->__getTypes() as $type) {
            if(strncmp($type, 'struct ',7) == 0) {
                preg_match('/^struct\s([^\s]+)\s\{/', $type, $match);
                $type = $match[1];
                $classMap[$type] = $type;
            }
        }
        if(function_exists('apc_store')) {
            apc_store ( $key, $classMap, 60 );
        }
        return $classMap;
    }

    protected function includeService($service) {
        $filepath = "../application/services/Soap/$service.php";
        if(!file_exists($filepath)) {
            header("HTTP/1.0 404 Not Found", true, 404);
            echo 'invalid service';
            exit();
        }
        require_once $filepath;
    }

    public function handle() {
        if(array_key_exists('server', $_GET)) {
            $service = $_GET['server'];
            $class = "Application_Service_Soap_$service";
            $this->includeService($service);
            $this->checkAccess($class);
            try {
                list($user, $password) = $this->getAuth();
                $wsdlUrl = "http://$user:$password@".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?wsdl=$service";
            } catch (Exception $e) {
                $wsdlUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?wsdl=$service";
            }
            $option = array('classmap' => $this->getClassmap($wsdlUrl));
            try {
                $soap = new SoapServer($wsdlUrl, $option);
                $soap->setClass($class);
                $soap->handle();
            } catch (Exception $e) {
                $soap->fault(get_class($e) . ':'.$e->getCode(), $e->getMessage(),"\n",$e->getTraceAsString());
            }
        } elseif (array_key_exists('wsdl', $_GET)) {
            $service = $_GET['wsdl'];
            $class = "Application_Service_Soap_$service";
            $this->includeService($service);
            $this->checkAccess($class);
            $serverUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?server=$service";
            $autodiscover = new Zend_Soap_AutoDiscover('Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex');
            $autodiscover->setClass($class);
            $autodiscover->setUri($serverUrl);
            $autodiscover->handle();
        } else {
            header("HTTP/1.0 404 Not Found", true, 404);
            echo 'invalid action';
            exit();
        }
    }

    /**
     * check for the login and password in the authentication header.
     *
     * @return array ($user, $password)
     * @throws Exception if anything append
     */
    protected function getAuth() {
        if(is_null($this->auth)) {
            $headers = apache_request_headers();
            if(!is_array($headers) || !array_key_exists('Authorization', $headers) || (6 > strlen(($auth = $headers['Authorization'])))) {
                throw new Exception('no Authorization header', self::AUTH_ERROR_NO_HEADERS);
            } elseif(strncmp($auth = trim($auth), 'Basic ',6) !== 0) {
                throw new Exception('not Basic Authorization', self::AUTH_ERROR_NOT_BASIC);
            } elseif(!($auth = base64_decode(substr($auth,6))) || (count($list = explode(':',$auth)) !== 2)) {
                throw new Exception('invalid Authorization string decoded', self::AUTH_ERROR_INVALID_AUTH);
            } else {
                //            list($user,$password) = $list;
                //            $acl = new Cfe_Acl();
                //            if(!$acl->checkPassword($user, $password)) {
                //                throw new Exception('invalid user/password', self::AUTH_ERROR_INVALID_USER);
                //            }
                $this->auth = $list;
            }
        }
        return $this->auth;
    }

    /**
     * get the user name from authorization header then check if he has the rights to access the service
     *
     * @return boolean
     */
    protected function checkAccess($class) {
        try {
            list($user,$password) = $this->getAuth();
            $acl = new Cfe_Acl();
            if(!$acl->isAuthorizedOnClass($user,$class)) {
                //                throw new Exception('invalid accreditation',self::AUTH_ERROR_INVALID_ACCREDITATION);
                //Cfe_Log_Helper::log('[COMPONENT ACCREDITATION ERROR] domain: '.$_SERVER['SERVER_NAME'].", user: $user, class: ".$class, Zend_Log::ERR);
            }
        } catch (Cfe_Acl_Exception $e) {
            Cfe_Log_Helper::log('[COMPONENT ACCREDITATION ERROR] domain: '.$_SERVER['SERVER_NAME'].", user: $user, class: ".$class.', message: "'.$e->getMessage().'"', Zend_Log::ERR);
        } catch (Exception $e) {
            //            if($e->getCode() == self::AUTH_ERROR_NO_HEADERS) {
            //                header("HTTP/1.0 401 Access Denied", true, 401);
            //                echo 'Access Denied';
            //            } else {
            //                header("HTTP/1.0 403 Forbidden", true, 403);
            //                echo $e->getMessage();
            //            }
            //            exit();
            Cfe_Log_Helper::log('[COMPONENT AUTHENTIFICATION ERROR] domain: '.$_SERVER['SERVER_NAME'].', class: '.$class.', message: "'.$e->getMessage().'"', Zend_Log::ERR);
        }
    }
}