<?php
require_once 'Cfe/Controller/Abstract.php';

class Cfe_Controller_Secured extends Cfe_Controller_Abstract {
    const AUTH_ERROR_NO_HEADERS   = 1;
    const AUTH_ERROR_NOT_BASIC    = 2;
    const AUTH_ERROR_INVALID_AUTH = 3;
    const AUTH_ERROR_INVALID_USER = 4;
    const ACL_ERROR_INVALID_ACCREDITATION = 5;

    private $auth;

    /**
     * check the Acl
     * Log if there's no authentification header
     * Log if there's a user and he doesn't have the right accreditation to access the service
     *
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch() {
       /* try {
            list($user,$password) = $this->getAuth();
            if(!$this->checkAccess()) {
                throw new Exception('no accreditation to access this service', self::ACL_ERROR_INVALID_ACCREDITATION);
                Cfe_Log_Helper::log('[COMPONENT ACCREDITATION ERROR] no accreditation. domain: '.$_SERVER['SERVER_NAME'].", user: $user, class: ".$this->getClass(), Zend_Log::ERR);
            }
        } catch (Cfe_Acl_Exception $e) {
            Cfe_Log_Helper::log('[COMPONENT ACCREDITATION ERROR] '.$e->getMessage().', domain: '.$_SERVER['SERVER_NAME'].", user: $user, class: ".$this->getClass(), Zend_Log::ERR);
        } catch (Exception $e) {
            //$this->_forward('access',$request->getControllerName(),$request->getModuleName(), array('error' => $e->getCode()));
            Cfe_Log_Helper::log('[COMPONENT AUTHENTIFICATION ERROR] domain: '.$_SERVER['SERVER_NAME'].', class: '.$this->getClass().', message: "'.$e->getMessage().'"', Zend_Log::ERR);
        }*/
    }

    /**
     * return the class that will handle the service (the controller in most case)
     * this is the class used to determined the roles the user need to have.
     *
     * @return string class name
     */
    protected function getClass() {
        return get_class($this);
    }

    /**
     * check for the login and password in the authentication header.
     *
     * @return array ($user, $password)
     * @throws Exception if anything append
     */
    protected function getAuth() {
        if(is_null($this->auth)) {
            $request = $this->getRequest();
            if(false === ($auth = $request->getHeader('Authorization'))) {
                throw new Exception('no Authorization header', self::AUTH_ERROR_NO_HEADERS);
            } elseif(strncmp($auth = trim($auth), 'Basic ',6) !== 0) {
                throw new Exception('not Basic Authorisation', self::AUTH_ERROR_NOT_BASIC);
            } elseif(!($auth = base64_decode(substr($auth,6))) || (count($list = explode(':',$auth)) !== 2)) {
                throw new Exception('invalid string decoded', self::AUTH_ERROR_INVALID_AUTH);
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
     * check if the user have the rights to access the service based on LDAP
     *
     * @param string $user
     * @return boolean
     */
    protected function checkAcl($user) {
        $acl = new Cfe_Acl();
        return $acl->isAuthorizedOnClass($user,$this->getClass());
    }

    /**
     * get the user name from authorization header then check if he has the rights to access the service
     *
     * @return boolean
     */
    protected function checkAccess() {
        list($user,$password) = $this->getAuth();
        return $this->checkAcl($user);
    }
}
